<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Warehouse extends BaseModel
{
    public $appends = [
        "display_name",
    ];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public array $child_model_class = [
        WarehouseStock::class => [
            "foreignKey" => "warehouse_id",
            "preventDelete" => true
        ]
    ];

    public function state()
    {
        return $this->belongsTo(State::class, "state_id");
    }

    public function city()
    {
        return $this->belongsTo(City::class, "city_id");
    }

    public function warehouseStock()
    {
        return $this->hasMany(WarehouseStock::class, "warehouse_id");
    }
    
    public function product()
    {
        return $this->hasMany(Product::class, "warehouse_id");
    }

    public function getDisplayName()
    {
        $name = $this->name;

        return $name;
    }

    // protected static function _getList(Builder $builder, String $id, String $value)
    // {
    //     $builder->with([
    //         "party" => function ($q) {
    //             $q->select("id", "name");
    //         }
    //     ]);

    //     $records = $builder->get();

    //     // d($records->toArray());

    //     $list = [];

    //     foreach ($records as $record) {
    //         if ($value == "display_name") {
    //             $list[$record->{$id}] = $record->getDisplayName();
    //         } else {
    //             $list[$record->{$id}] = $record->{$value};
    //         }
    //     }

    //     return $list;
    // }

    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }
}
