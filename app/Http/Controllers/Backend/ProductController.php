<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\CsvUtility;
use App\Helpers\DateUtility;
use App\Helpers\FileUtility;
use App\Helpers\Util;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Unit;
use App\Models\ItemBrand;
use App\Models\ItemGroup;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarehouseStock;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\Storage;

class ProductController extends BackendController
{
    public String $routePrefix = "product";
    public $modelClass = Product::class;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $records = $this->getPaginagteRecords($this->_getBuilder(), Route::currentRouteName());

        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _getBuilder()
    {
        $cache_key = Route::currentRouteName();

        $conditions = $this->getConditions($cache_key . ".1", [
            ["field" => "name", "type" => "string"],
            ["field" => "sku", "type" => "string"],
            ["field" => "is_active", "type" => "int"],
        ]);

        $builder = $this->modelClass::where($conditions)->with([
            "brand",
            "item",
            "warehouse"
        ])
            ->orderBy('id', 'DESC');

        return $builder;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new $this->modelClass();

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $model->is_active = 1;

        $this->_set_form_list(null);

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _set_form_list($model)
    {

        $finishedConditions = [];

        if ($model && $model->product_type == 1) {
            $finishedConditions['or_id'] = $model->item_id;
        }

        $finishedItemList = Item::getList(
            'id',
            'name',
            array_merge($finishedConditions, ['product_type' => 1])
        );

        $spareConditions = [];

        if ($model && $model->product_type == 0) {
            $spareConditions['or_id'] = $model->item_id;
        }

        $spareItemList = Item::getList(
            'id',
            'name',
            array_merge($spareConditions, ['product_type' => 0])
        );

        $partConditions = [];

        if ($model && $model->product_type == 2) {
            $partConditions['or_id'] = $model->item_id;
        }

        $partItemList = Item::getList(
            'id',
            'name',
            array_merge($partConditions, ['product_type' => 2])
        );

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->brand_id) {
            $conditions["or_id"] = $model->brand_id;
        }

        $brandList = Brand::getList("id", "name", $conditions);
        $warehouseList = Warehouse::getList("id", "name", $conditions);

        $item_sku_pattern = Setting::getValueOrFail("item_sku_pattern");

        $this->setForView(compact('finishedItemList', 'spareItemList', 'partItemList', 'brandList', 'warehouseList', 'item_sku_pattern'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'item_id' => 'required',
            'brand_id' => 'required',
            'sku' => 'required',
            'capacity' => '',
            'batch' => '',
            'specification' => '',
            'material_type' => '',
            'opening_stock' => '',
            'warehouse_id'  => 'required_with:opening_stock',
            'min_stock' => '',
            'max_stock' => '',
            "purchase_price" => "",
            "selling_price" => "",
            "gst" => "",
            "discount" => "",
            "rack_location" => "",
            "is_active" => "nullable|in:0,1",
            "is_returnable" => "nullable|in:0,1",
            'product_type' => 'required|in:0,1,2',
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            // 'name.unique' => 'Item Name is unique with in category and specification',
        ];
    }

    private function generateBarcode()
    {
        do {
            $barcode = '89' . rand(10000000000, 99999999999);
        } while ($this->modelClass::where('barcode', $barcode)->exists());

        return $barcode;
    }

    private function generateBarcodeImage($barcode, $productId)
    {
        $dns1d = new DNS1D();

        $png = $dns1d->getBarcodePNG($barcode, 'C128');

        $folder = "products/{$productId}";
        $fileName = $barcode . ".png";
        $path = $folder . "/" . $fileName;

        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        Storage::disk('public')->put($path, base64_decode($png));

        return $path;
    }


    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();
        $messages = $this->_get_comman_validation_messages();

        $rules = array_merge($rules, [
            'sku' => 'required|min:2|max:255|unique:' . $this->tableName,
        ]);

        $validatedData = $request->validate($rules, $messages);

        try {

            $barcode = $this->generateBarcode();
            $validatedData['barcode'] = $barcode;

            $product = $this->modelClass::create($validatedData);

            $barcodeImage = $this->generateBarcodeImage(
                $product->barcode,
                $product->id
            );

            $product->update([
                'barcode_image' => $barcodeImage
            ]);

            if (!empty($request->opening_stock) && $request->opening_stock > 0) {
                WarehouseStock::updateQty(
                    $request->warehouse_id,
                    $product->id,
                    $request->opening_stock,
                    $request->purchase_price ?? null,
                    true
                );
            }

            return back()->with('success', 'Record created successfully');
        } catch (Exception $ex) {
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $this->_set_form_list($model);

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }


    public function update(Request $request, $id)
    {
        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'sku' => [
                "required",
                "min:2",
                "max:255",
                Rule::unique($this->tableName)->where(function ($query) use ($request, $id) {
                    return $query
                        ->where('id',  '<>', $id)
                        ->where('sku', $request->input('sku'));
                })
            ]
        ]));

        try {
            $model = $this->modelClass::findOrFail($id);

            $model->fill($validatedData);
            $model->save();

            if (!$model->barcode) {
                $barcode = $this->generateBarcode();
                $model->barcode = $barcode;

                $barcodeImage = $this->generateBarcodeImage($barcode, $model->id);
                $model->barcode_image = $barcodeImage;

                $model->save();
            } else {
                if (!$model->barcode_image || !Storage::disk('public')->exists($model->barcode_image)) {
                    $barcodeImage = $this->generateBarcodeImage($model->barcode, $model->id);
                    $model->barcode_image = $barcodeImage;
                    $model->save();
                }
            }

            return redirect()->route($this->routePrefix . ".index")->with('success', 'Record updated successfully');
        } catch (Exception $ex) {
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->_destroy($id);
    }

    public function set_warehouse_opening_qty($id, Request $request)
    {
        $item = $this->modelClass::with("unit")->findOrFail($id);

        if ($request->isMethod("post")) {
            // dd($request->all());

            $validateData = $request->validate([
                'warehouse_stocks.*.warehouse_id' => 'required',
                'warehouse_stocks.*.opening_qty' => 'required|numeric|min:0'
            ], [
                "warehouse_stocks.*.warehouse_id.required" => "Warehouse is required",
                "warehouse_stocks.*.opening_qty.required" => "Opening Qty is required",
                "warehouse_stocks.*.opening_qty.numeric" => "Opening Qty should be numeric",
                "warehouse_stocks.*.opening_qty.min" => "Opening Qty should be more than or equal to 0",
            ]);

            try {
                $counters = [
                    "Warehouse Stock" => [
                        "Insert" => 0,
                        "Update" => 0,
                    ],
                ];

                $fail_msg_list = [];

                foreach ($validateData['warehouse_stocks'] as $warehouse_stock) {
                    $warehouse_stock['item_id'] = $item->id;

                    $warehouseStockModel = new WarehouseStock();
                    $id = $warehouseStockModel->getUniqueId($warehouse_stock);

                    $is_insert = null;
                    $is_update = null;

                    if ($id) {
                        $warehouseStockModel = WarehouseStock::with("warehouse")->find($id)->first();
                        $warehouseStockModel->fill($warehouse_stock);
                        if ($warehouseStockModel->getAvailabilitQty() < 0) {
                            $qty = abs($warehouseStockModel->qty);
                            $name = $warehouseStockModel->warehouse->getDisplayName();
                            $fail_msg_list[] = "There are some inventory transactions of Warehouse $name, So can not go below $qty";
                        } else {
                            if ($warehouseStockModel->isDirty()) {
                                $warehouseStockModel->save();

                                $counters["Warehouse Stock"]['Update']++;
                            }
                        }
                    } else {
                        $warehouseStockModel->fill($warehouse_stock);
                        $warehouseStockModel->save();
                        $counters["Warehouse Stock"]['Insert']++;
                    }

                    if ($is_insert) {
                    }

                    if ($is_update) {
                    }
                }

                if ($fail_msg_list) {
                    $msg = implode(", ", $fail_msg_list);

                    Session::flash("fail", $msg);

                    return back();
                }

                $msg_list = [];
                foreach ($counters as $title => $arr) {
                    foreach ($arr as $db_op => $counter) {
                        if ($counter > 0) {
                            $msg_list[] = $title . " " . $db_op . " : " . $counter;
                        }
                    }
                }

                if ($msg_list) {
                    $msg = implode(", ", $msg_list);

                    Session::flash("success", $msg);
                }
            } catch (Exception $ex) {
                Session::flash("fail", $ex->getMessage());
            }

            return back();
        }

        $warehouses = Warehouse::with([
            "warehouseStock" => function ($q) use ($id) {
                $q->where("item_id", $id);
            }
        ])->get();

        // dd($warehouses);

        $this->setForView(compact("item", "warehouses"));

        return $this->view(__FUNCTION__);
    }

    public function ajax_get($id)
    {
        $response = ["status" => 1];
        try {
            $model = $this->modelClass::findOrFail($id);

            $response['data'] = $model->toArray();
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    public function csv()
    {
        $builder = $this->_getBuilder();
        $count = $builder->count();

        $this->beforeCSVExport($builder);

        $records = $builder->get()->toArray();



        // d($records); exit;

        $csv_records = [];

        // $item_category_list = Item::getTreeList();

        $yes_no_list = config('constant.yes_no');
        $user_list = User::getListCache();

        foreach ($records as $record) {
            $csv_records[] = [
                'ID' => $record['id'],
                'Item' => $record['item']['name'] ?? "",
                'Brand' => $record['brand']['name'] ?? "",
                'Specification' => $record['specification'] ?? "",
                'Sku' => $record['sku'] ?? "",
                'Capacity' => $record['capacity'] ?? "",
                'Material Type' => $record['material_type'] ?? "",
                'Opening Stock' => $record['opening_stock'] ?? "",
                'Warehouse' => $record['warehouse']['name'] ?? "",
                'Min Stock' => $record['min_stock'] ?? "",
                'Max Stock' => $record['max_stock'] ?? "",
                'Rack Location' => $record['rack_location'] ?? "",
                'Batch' => $record['batch'] ?? "",
                'Purchase Price' => $record['purchase_price'] ?? "",
                'Selling Price' => $record['selling_price'] ?? "",
                'Dicount' => $record['discount'] ?? "",
                'GST Percentage' => $record['gst'] ?? "",
                // 'Unit' => $record['unit']['name'] ?? "",
                // 'HSN' => $record['hsn_code'] ?? "",
                // 'Purchase Rate' => $record['purchase_rate'] ?? "",
                // 'Sale Rate' => $record['sale_rate'] ?? "",
                // 'Finished' => $yes_no_list[$record['is_finished_item']] ?? "",
                // 'Active' => $yes_no_list[$record['is_active']] ?? "",
                // 'Created' => if_date_time($record['created_at']),
                // 'Created By' => $user_list[$record['created_by']] ?? "",
                // 'Updated' => if_date_time($record['updated_at']),
                // 'Updated By' => $user_list[$record['updated_by']] ?? "",
            ];
        }

        $path = config('constant.path.temp');
        FileUtility::createFolder($path);
        $file = $path . $this->tableName .  "_" . date(DateUtility::DATETIME_OUT_FORMAT_FILE) . ".csv";

        $csvUtility = new CsvUtility($file);
        $csvUtility->write($csv_records);

        download_start($file, "application/octet-stream");
    }

    public function print($id)
    {
        // $record = Product::findOrFail($id);
        // return view('Backend.Product.print', compact('record'));
        $product = Product::findOrFail($id);
        
        $totalLabels = 24; // 👈 how many times you want
        return view('backend.Product.print', compact('product','totalLabels'));
    }
}
