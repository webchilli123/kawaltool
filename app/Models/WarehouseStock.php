<?php

namespace App\Models;

use Exception;

class WarehouseStock extends BaseModel
{
    protected static array $unique_fields = [
        "product_id",
        "warehouse_id"
    ];

    protected $table = 'warehouse_stocks';
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'opening_qty',
        'qty',
        'price'
    ];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public array $child_model_class = [];

    public function product()
    {
        return $this->belongsTo(Product::class, "product_id");
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, "warehouse_id");
    }

    public function city()
    {
        return $this->belongsTo(City::class, "city_id");
    }

    public function getAvailabilitQty()
    {
        return $this->qty;
    }

    public static function updateQty($warehouse_id, $product_id, $qty, $price = null, $isOpening = false)
    {
        $model = self::where("warehouse_id", $warehouse_id)->where("product_id", $product_id)->first();


        if ($qty == 0 && $price !== null && $model && $model->price != $price) {
            $model->price = $price;
            $model->save();
            return;
        }

        if ($qty < 0) {
            $ware_house_name = Warehouse::findOrFail($warehouse_id);
            $ware_house_name = $ware_house_name->getDisplayName();
            $product_name = Product::findOrFail($product_id);
            $product_name = $product_name->getDisplayName();

            if (!$model) {
                throw new Exception("Warehouse $ware_house_name have no any Item $product_name");
            }

            $available_qty = $model->getAvailabilitQty();

            if ($available_qty < abs($qty)) {
                throw new Exception("Warehouse $ware_house_name has $available_qty qty of $product_name");
            }

            $model->qty += $qty;

            $model->save();
        } else if ($qty > 0) {
            if (!$model) {
                $warehouse_arr = [
                    "warehouse_id" => $warehouse_id,
                    "product_id" => $product_id,
                    "opening_qty" => $isOpening ? $qty : 0,
                    "qty" => $qty,
                    "price" => $price,
                ];

                WarehouseStock::create($warehouse_arr);
            } else {

                if ($isOpening) {
                    $model->opening_qty += $qty;
                }

                $model->qty += $qty;

                if ($price !== null) {
                    $model->price = $price;
                }

                $model->save();
            }
        }
    }
}
