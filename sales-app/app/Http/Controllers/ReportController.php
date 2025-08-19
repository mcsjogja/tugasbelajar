<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display sales report
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        
        // Sales summary
        $salesSummary = Transaction::where('jenis', 'penjualan')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(total) as total_sales,
                AVG(total) as average_transaction
            ')
            ->first();
        
        // Daily sales
        $dailySales = Transaction::where('jenis', 'penjualan')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Top selling products
        $topProducts = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.jenis', 'penjualan')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->selectRaw('
                products.nama,
                products.kode,
                SUM(transaction_details.qty) as total_qty,
                SUM(transaction_details.subtotal) as total_revenue
            ')
            ->groupBy('products.id', 'products.nama', 'products.kode')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();
        
        return view('reports.sales', compact('salesSummary', 'dailySales', 'topProducts', 'startDate', 'endDate'));
    }
    
    /**
     * Display purchase report
     */
    public function purchases(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        
        // Purchase summary
        $purchaseSummary = Transaction::where('jenis', 'pembelian')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(total) as total_purchases,
                AVG(total) as average_transaction
            ')
            ->first();
        
        // Daily purchases
        $dailyPurchases = Transaction::where('jenis', 'pembelian')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Most purchased products
        $topPurchases = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.jenis', 'pembelian')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->selectRaw('
                products.nama,
                products.kode,
                SUM(transaction_details.qty) as total_qty,
                SUM(transaction_details.subtotal) as total_cost
            ')
            ->groupBy('products.id', 'products.nama', 'products.kode')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();
        
        return view('reports.purchases', compact('purchaseSummary', 'dailyPurchases', 'topPurchases', 'startDate', 'endDate'));
    }
    
    /**
     * Display inventory report
     */
    public function inventory()
    {
        // Stock levels
        $lowStock = Product::where('stok', '<=', 10)->get();
        $outOfStock = Product::where('stok', 0)->get();
        
        // Category wise stock
        $categoryStock = Product::selectRaw('
                kategori,
                COUNT(*) as total_products,
                SUM(stok) as total_stock,
                SUM(stok * harga_beli) as total_value
            ')
            ->groupBy('kategori')
            ->get();
        
        // Most valuable products
        $valuableProducts = Product::selectRaw('
                *,
                (stok * harga_beli) as stock_value
            ')
            ->orderByDesc('stock_value')
            ->take(10)
            ->get();
        
        return view('reports.inventory', compact('lowStock', 'outOfStock', 'categoryStock', 'valuableProducts'));
    }
    
    /**
     * Display profit report
     */
    public function profit(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        
        // Profit calculation
        $profitData = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.jenis', 'penjualan')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->selectRaw('
                SUM(transaction_details.subtotal) as total_revenue,
                SUM(transaction_details.qty * products.harga_beli) as total_cost,
                SUM(transaction_details.subtotal - (transaction_details.qty * products.harga_beli)) as total_profit
            ')
            ->first();
        
        // Daily profit
        $dailyProfit = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.jenis', 'penjualan')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->selectRaw('
                DATE(transactions.created_at) as date,
                SUM(transaction_details.subtotal) as revenue,
                SUM(transaction_details.qty * products.harga_beli) as cost,
                SUM(transaction_details.subtotal - (transaction_details.qty * products.harga_beli)) as profit
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Most profitable products
        $profitableProducts = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.jenis', 'penjualan')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->selectRaw('
                products.nama,
                products.kode,
                SUM(transaction_details.qty) as total_qty,
                SUM(transaction_details.subtotal) as total_revenue,
                SUM(transaction_details.qty * products.harga_beli) as total_cost,
                SUM(transaction_details.subtotal - (transaction_details.qty * products.harga_beli)) as total_profit
            ')
            ->groupBy('products.id', 'products.nama', 'products.kode')
            ->orderByDesc('total_profit')
            ->take(10)
            ->get();
        
        return view('reports.profit', compact('profitData', 'dailyProfit', 'profitableProducts', 'startDate', 'endDate'));
    }
}
