<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionDetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'harga',
        'subtotal',
    ];
    
    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }
    
    /**
     * Get the transaction that owns the transaction detail
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
    
    /**
     * Get the product that owns the transaction detail
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Calculate subtotal automatically
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            $model->subtotal = $model->qty * $model->harga;
        });
    }
}
