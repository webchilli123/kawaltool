<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends BaseModel
{
    use SoftDeletes;

    public array $child_model_class = [
     
        Product::class => [
            "foreignKey" => "item_id",
            "preventDelete" => true
        ],
    ];

    public const ITEM_SPARE    = 0;
    public const ITEM_FINISHED = 1;
    public const ITEM_PART     = 2;

    public $appends = [
        "display_name"
    ];

    public function getDisplayName()
    {
        $name =  $this->name;

        return $name;
    }

    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }
}
