<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get statistics based on user role
        $stats = $this->getDashboardStats($user);
        
        return view('dashboard.index', compact('stats', 'user'));
    }
    
    /**
     * Get dashboard statistics based on user role
     */
    private function getDashboardStats($user)
    {
        $stats = [];
        
        if ($user->isAdmin()) {
            // Admin can see all statistics
            $stats = [
                'total_products' => Product::count(),
                'total_users' => User::count(),
                'total_penjualan' => Transaction::where('jenis', 'penjualan')->sum('total'),
                'total_pembelian' => Transaction::where('jenis', 'pembelian')->sum('total'),
                'low_stock_products' => Product::where('stok', '<=', 10)->count(),
                'recent_transactions' => Transaction::with(['user', 'details.product'])
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        } elseif ($user->isKasir()) {
            // Kasir can see sales-related statistics
            $stats = [
                'total_products' => Product::count(),
                'today_sales' => Transaction::where('jenis', 'penjualan')
                    ->whereDate('created_at', today())
                    ->sum('total'),
                'monthly_sales' => Transaction::where('jenis', 'penjualan')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total'),
                'low_stock_products' => Product::where('stok', '<=', 10)->count(),
                'recent_sales' => Transaction::with(['user', 'details.product'])
                    ->where('jenis', 'penjualan')
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        } else {
            // Pelanggan can see their own transactions
            $stats = [
                'my_transactions' => Transaction::with(['details.product'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get(),
                'total_spent' => Transaction::where('user_id', $user->id)
                    ->where('jenis', 'penjualan')
                    ->sum('total'),
            ];
        }
        
        return $stats;
    }
}
