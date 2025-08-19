<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'stok',
        'harga_beli',
        'harga_jual',
    ];
    
    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
        ];
    }
    
    /**
     * Get transaction details for this product
     */
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
    
    /**
     * Get transactions through transaction details
     */
    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, TransactionDetail::class);
    }
    
    /**
     * Calculate profit margin
     */
    public function getMarginAttribute(): float
    {
        if ($this->harga_beli == 0) return 0;
        return (($this->harga_jual - $this->harga_beli) / $this->harga_beli) * 100;
    }
}
