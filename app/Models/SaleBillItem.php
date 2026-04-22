<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleBillItem extends BaseModel
{
 public array $child_model_class = [
        
    ];

    // public static function boot()
    // {
    //     parent::boot();

    //     self::deleted(function ($model) {
    //         $model->afterDelete();
    //     });
    // }

    // public function afterDelete()
    // {
    //     $sale_order_item = $this->saleOrderItem()->first();
    //     if ($sale_order_item)
    //     {
    //         $sale_order_item->sent_qty -= $this->qty;
    //         $sale_order_item->save();
    //     }
    // }

    public function saleBill()
    {
        return $this->belongsTo(SaleBill::class, 'sale_bill_id');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
