<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'details.product']);
        
        // Filter by transaction type
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Search by transaction number or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $transactions = $query->latest()->paginate(15);
        
        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $jenis = $request->get('jenis', 'penjualan');
        $products = Product::where('stok', '>', 0)->get();
        
        return view('transactions.create', compact('jenis', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:penjualan,pembelian',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.harga' => 'required|numeric|min:0',
        ]);
        
        DB::transaction(function () use ($request) {
            // Generate transaction number
            $nomorTransaksi = Transaction::generateTransactionNumber($request->jenis);
            
            // Calculate total
            $total = 0;
            foreach ($request->products as $productData) {
                $total += $productData['qty'] * $productData['harga'];
            }
            
            // Create transaction
            $transaction = Transaction::create([
                'nomor_transaksi' => $nomorTransaksi,
                'user_id' => auth()->id(),
                'jenis' => $request->jenis,
                'total' => $total,
            ]);
            
            // Create transaction details and update stock
            foreach ($request->products as $productData) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $productData['product_id'],
                    'qty' => $productData['qty'],
                    'harga' => $productData['harga'],
                ]);
                
                // Update product stock
                $product = Product::find($productData['product_id']);
                if ($request->jenis === 'penjualan') {
                    $product->stok -= $productData['qty'];
                } else {
                    $product->stok += $productData['qty'];
                }
                $product->save();
            }
        });
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'details.product']);
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        // Only allow editing if transaction is recent (within 24 hours)
        if ($transaction->created_at->diffInHours(now()) > 24) {
            return redirect()->route('transactions.index')
                ->with('error', 'Transaksi tidak dapat diubah setelah 24 jam.');
        }
        
        $transaction->load('details.product');
        $products = Product::all();
        
        return view('transactions.edit', compact('transaction', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Only allow editing if transaction is recent (within 24 hours)
        if ($transaction->created_at->diffInHours(now()) > 24) {
            return redirect()->route('transactions.index')
                ->with('error', 'Transaksi tidak dapat diubah setelah 24 jam.');
        }
        
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.harga' => 'required|numeric|min:0',
        ]);
        
        DB::transaction(function () use ($request, $transaction) {
            // Restore original stock
            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($transaction->jenis === 'penjualan') {
                    $product->stok += $detail->qty;
                } else {
                    $product->stok -= $detail->qty;
                }
                $product->save();
            }
            
            // Delete old details
            $transaction->details()->delete();
            
            // Calculate new total
            $total = 0;
            foreach ($request->products as $productData) {
                $total += $productData['qty'] * $productData['harga'];
            }
            
            // Update transaction
            $transaction->update(['total' => $total]);
            
            // Create new details and update stock
            foreach ($request->products as $productData) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $productData['product_id'],
                    'qty' => $productData['qty'],
                    'harga' => $productData['harga'],
                ]);
                
                // Update product stock
                $product = Product::find($productData['product_id']);
                if ($transaction->jenis === 'penjualan') {
                    $product->stok -= $productData['qty'];
                } else {
                    $product->stok += $productData['qty'];
                }
                $product->save();
            }
        });
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        // Only allow deletion if transaction is very recent (within 1 hour)
        if ($transaction->created_at->diffInHours(now()) > 1) {
            return redirect()->route('transactions.index')
                ->with('error', 'Transaksi tidak dapat dihapus setelah 1 jam.');
        }
        
        DB::transaction(function () use ($transaction) {
            // Restore stock
            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($transaction->jenis === 'penjualan') {
                    $product->stok += $detail->qty;
                } else {
                    $product->stok -= $detail->qty;
                }
                $product->save();
            }
            
            $transaction->delete();
        });
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
