<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product','product_trade','product_scientific','category_id','supplier_id',
        'cost_price','quantity','expiry_date',
        'image','packet_size','packet_quantity','loose_sheets','expiry_alert_days','low_stock_alert_threshold'
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function purchaseProduct(){
        return $this->hasOne(Product::class);
    }

    /**
     * Get total sheets (packets * packet_size + loose_sheets)
     */
    public function getTotalSheetsAttribute()
    {
        return ($this->packet_quantity * $this->packet_size) + $this->loose_sheets;
    }

    /**
     * Get formatted inventory display (e.g., "9 packets + 6 sheets")
     */
    public function getFormattedInventoryAttribute()
    {
        if ($this->packet_quantity > 0 && $this->loose_sheets > 0) {
            return "{$this->packet_quantity} packets + {$this->loose_sheets} sheets";
        } elseif ($this->packet_quantity > 0) {
            return "{$this->packet_quantity} packets";
        } elseif ($this->loose_sheets > 0) {
            return "{$this->loose_sheets} sheets";
        } else {
            return "0";
        }
    }
}
