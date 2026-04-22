<?php

namespace App\Models;

class PurchaseBillItem extends BaseModel
{
    public function purchaseBill()
    {
        return $this->belongsTo(PurchaseBill::class, 'purchase_bill_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

