<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nomor_transaksi',
        'user_id',
        'jenis',
        'total',
    ];
    
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }
    
    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get transaction details for this transaction
     */
    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }
    
    /**
     * Get products through transaction details
     */
    public function products()
    {
        return $this->hasManyThrough(Product::class, TransactionDetail::class);
    }
    
    /**
     * Generate transaction number
     */
    public static function generateTransactionNumber(string $jenis): string
    {
        $prefix = $jenis === 'penjualan' ? 'PJ' : 'PB';
        $date = date('Ymd');
        $lastTransaction = self::where('nomor_transaksi', 'like', $prefix . $date . '%')
            ->orderBy('nomor_transaksi', 'desc')
            ->first();
            
        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->nomor_transaksi, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
