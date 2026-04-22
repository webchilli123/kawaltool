<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ArrayHelper;
use App\Helpers\DateUtility;
use App\Models\AutoIncreament;
use App\Models\Complaint;
use App\Models\Item;
use App\Models\JobCardItem;
use App\Models\Product;
use App\Models\StockIssue;
use App\Models\StockIssueItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class StockIssueController extends BackendController
{
    public String $routePrefix = "stock-issue";

    public $modelClass = StockIssue::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $records = $this->getPaginagteRecords(
            $this->modelClass::where($conditions)
                ->with([
                    'complaint.complaintItems.product',
                    'warehouse',
                    'stockReceiver',
                    'issueItems.product'
                ])
                // ->withSum('items as total_issue_qty', 'qty')
                ->orderBy('id', 'desc'),
            Route::currentRouteName()
        );

        $this->setForView(compact('records'));

        return $this->viewIndex(__FUNCTION__);
    }


    private function _get_conditions($cache_key)
    {
        return $this->getConditions($cache_key, [
            ['field' => 'issue_no', 'type' => 'string', 'view_field' => 'issue_no'],
            ['field' => 'job_card_id', 'type' => 'int', 'view_field' => 'job_card_id'],
            ['field' => 'warehouse_id', 'type' => 'int', 'view_field' => 'warehouse_id'],
        ]);
    }

    public function create()
    {
        $model = new $this->modelClass();
        $model->issue_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_STOCK_ISSUE);
        $model->issue_date = date(DateUtility::DATE_OUT_FORMAT);

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);
        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }

    private function _set_list_for_form($model)
    {
        $complaintList = Complaint::getList("id", "complaint_no");
        $userList = User::getList('id', 'name');
        $warehouseList = Warehouse::getList('id', 'name');

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->issueItems) {
            foreach ($model->issueItems as $billItem) {
                $conditions["or_id"][] = $billItem->product_id;
            }
        }

        $itemList = Product::getList("id", "display_name", $conditions);

        $this->setForView(compact("warehouseList", "complaintList", "itemList", "userList"));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'complaint_id' => ['required'],
            'warehouse_id' => ['required'],
            'receiver_id' => ['required'],
            'issue_date' => ['required', 'date'],
            'remarks' => "",
            'items' => ['required', 'array'],
            "items.*.id" => "",
            'items.*.product_id' => ['required'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            'items.*.product_id.required' => 'Item required',
            'items.*.qty.required' => 'Issue qty required',
        ];
    }

    public function store(Request $request)
    {   

        
        $validatedData = $request->validate(
            $this->_get_comman_validation_rules(),
            $this->_get_comman_validation_messages()
            );
            
            try {
                
                DB::beginTransaction();
                
                $arry_helper = new ArrayHelper($validatedData);
                
                $save_data = $arry_helper->ignoreKeys([
                    "items",
                    ]);
                    
                    $save_data['issue_no'] = AutoIncreament::getNextCounter(AutoIncreament::TYPE_STOCK_ISSUE);

                    $model = $this->modelClass::create($save_data);
                    if (!$model) {
                throw_exception("Fail To Save");
                }
                
                
                $this->_afterSave($validatedData, $model);
                // dd($validatedData);

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_STOCK_ISSUE);

            DB::commit();

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', 'Stock Issued Successfully');
        } catch (Exception $e) {

            DB::rollBack();

            return back()->withInput()->with('fail', $e->getMessage());
        }
    }

    private function _afterSave($validatedData, StockIssue $model)
    {
        if (!$validatedData['items']) {
            throw new Exception("Items are required");
        }

        $warehouse_id = $model->warehouse_id;

        $not_saved_list = $model->issueItems()
            ->pluck('qty', 'id')
            ->toArray();

        foreach ($validatedData['items'] as $arr) {

            if ($arr['qty'] <= 0) {
                continue;
            }

            $issue_item = $model->issueItems()->find($arr['id']);
            if ($issue_item) {
                $old_qty = $issue_item->qty;
                $new_qty = $arr['qty'];

                $issue_item->update($arr);

                $diff = $new_qty - $old_qty;

                if ($diff != 0) {
                    WarehouseStock::updateQty(
                        $warehouse_id,
                        $issue_item->product_id,
                        -$diff
                    );
                }

                unset($not_saved_list[$arr['id']]);
            } else {

                $stockIssueItem = new StockIssueItem();
                $stockIssueItem->fill($arr);
                $issue_item = $model->issueItems()->save($stockIssueItem);

                WarehouseStock::updateQty(
                    $warehouse_id,
                    $issue_item->product_id,
                    -$arr['qty']
                );
            }
        }

        if ($not_saved_list) {

            $not_saved_bill_items = $model->issueItems()
                ->whereIn('id', array_keys($not_saved_list))
                ->get();

            foreach ($not_saved_bill_items as $not_saved_bill_item) {

                WarehouseStock::updateQty(
                    $warehouse_id,
                    $not_saved_bill_item->product_id,
                    $not_saved_list[$not_saved_bill_item->id]
                );

                $not_saved_bill_item->delete();
            }
        }
    }

    public function jobCardItems($jobCardId)
    {
        $materials = JobCardItem::with('rawItem')
            ->where('job_card_id', $jobCardId)
            ->get();

        $data = [];

        foreach ($materials as $row) {

            $issuedQty = StockIssueItem::where('item_id', $row->raw_item_id)
                ->whereHas('issue', function ($q) use ($jobCardId) {
                    $q->where('job_card_id', $jobCardId);
                })
                ->sum('qty');

            $requiredQty = $row->required_qty;
            $pendingQty  = $requiredQty - $issuedQty;

            $data[] = [
                'item_id'      => $row->raw_item_id,
                'item_name'    => $row->rawItem?->sku,
                'required_qty' => $requiredQty,
                'issued_qty'   => $issuedQty,
                'pending_qty'  => $pendingQty
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }
}
