<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends BaseModel
{
    protected $appends = [
        "max_gst_per",
        "display_name"
    ];

    protected $with = ['item'];

    // public array $child_model_class = [
    //     WarehouseStock::class => [
    //         "foreignKey" => "item_id",
    //         "preventDelete" => false
    //     ],
    //     PurchaseOrderItem::class => [
    //         "foreignKey" => "item_id",
    //         "preventDelete" => true
    //     ],
    //     PurchaseBillItem::class => [
    //         "foreignKey" => "item_id",
    //         "preventDelete" => true
    //     ],
    //     EnquiryItem::class => [
    //         "foreignKey" => "product_id",
    //         "preventDelete" => true
    //     ],
    //     QuotationItem::class => [
    //         "foreignKey" => "product_id",
    //         "preventDelete" => true
    //     ],
    // ];

    // public function unit()
    // {
    //     return $this->belongsTo(Unit::class, "unit_id");
    // }

    // public function itemCategory()
    // {
    //     return $this->belongsTo(ItemCategory::class, "item_category_id");
    // }

    // public function itemGroup()
    // {
    //     return $this->belongsTo(ItemGroup::class, "item_group_id");
    // }

    public function brand()
    {
        return $this->belongsTo(Brand::class, "brand_id");
    }
    
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, "warehouse_id");
    }

    public function item()
    {
        return $this->belongsTo(Item::class, "item_id");
    }

    public function getMaxGstPerAttribute()
    {
        return $this->tax_rate > 0 ? $this->tax_rate : 40;
    }

    protected static function _getList(Builder $builder, String $id, String $value)
    {
        if (!$id) {
            $id = "id";
        }

        if (!$value) {
            $value = "display_name_with_category";
        }

        $records = $builder->get();

        $list = [];

        foreach ($records as $record) {
            if ($value == "display_name") {
                $list[$record->{$id}] = $record->getDisplayName();
            } else {
                $list[$record->{$id}] = $record->{$value};
            }
        }

        return $list;
    }

    public function parties()
    {
        return $this->belongsToMany(Party::class, 'party_products')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

     public function party()
    {
        return $this->belongsToMany(
            Party::class,
            'party_products',
            'product_id',
            'party_id'
        );
    }


    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }


    public function getDisplayName()
    {
        if (!$this->relationLoaded('item')) {
            $this->load('item');
        }

        if ($this->item) {
            return $this->item->name . ' - ' . $this->sku;
        }

        return $this->sku;
    }

    public static function getUnitList()
    {
        $records = static::select("id", "unit_id")->with([
            "unit"
        ])->get();

        $list = [];

        foreach ($records as $record) {
            $list[$record->id] = $record->unit->code;
        }

        return $list;
    }
}
