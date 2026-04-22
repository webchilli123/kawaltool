<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintItem extends BaseModel
{
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
