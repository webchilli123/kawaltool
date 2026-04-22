<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Helpers\Util;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\LedgerAccount;
use App\Models\Party;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ReportController extends BackendController
{
    public String $routePrefix = "reports";

    public function ledger(Request $request)
    {
        $accountList = LedgerAccount::getList('id', 'name');
        $today_date = DateUtility::getDate(null, DateUtility::DATE_OUT_FORMAT);
        $last_month_date = DateUtility::change($today_date, -1, DateUtility::MONTHS, DateUtility::DATE_OUT_FORMAT);

        $conditions = $this->getConditions(Route::currentRouteName(), [
            ["field" => "LT.main_account_id", "type" => "int", "view_field" => "main_account_id"],

            ["field" => "voucher_date", "type" => "from_date", "view_field" => "from_date", "default" => $last_month_date],
            ["field" => "voucher_date", "type" => "to_date", "view_field" => "to_date", "default" => $today_date],
        ], true);

        // d($conditions); exit;

        if ($conditions && isset($conditions['LT.main_account_id'])) {
            $where_list = [];

            foreach ($conditions as $field => $val) {
                if (str_check_char_array_exist($field, ["=", "<", ">", "!"])) {
                    $where_list[] = "$field'" . $val . "'";
                } else {
                    $where_list[] = "$field='" . $val . "'";
                }
            }

            $where = "";

            if ($where_list) {
                $where = "WHERE " . implode(" AND ", $where_list);
            }

            $q = "
                SELECT 
                    COUNT(1) AS C
                FROM 
                    ledger_transactions LT
                $where
                
            ";

            $data = DB::select($q);

            if (isset($data[0]->C) && $data[0]->C > DEFAULT_EXPORT_CSV_JS_LIMIT) {
                abort(\ACTION_NOT_PROCEED, "Too Much Records Found, Please apply more conditions to reduce record count");
            }

            $q = "
                SELECT 
                    LT.*
                FROM 
                    ledger_transactions LT
                $where
                ORDER BY
                    LT.id ASC
                LIMIT 50000
            ";

            $records = DB::select($q);

            $current_amount = 0;
            foreach ($records as $k => $record) {

                $record->other_account = $accountList[$record->other_account_id] ?? "";

                $current_amount += $record->amount;

                $records[$k] = Util::objToArray($record);
            }

            $conditions3 = $conditions;

            // d($conditions3);
            $where_list = [];

            unset($conditions3['voucher_date <=']);

            if (isset($conditions3['voucher_date >='])) {
                $where_list[] = "voucher_date <= '" . $conditions['voucher_date >='] . "'";
                unset($conditions3['voucher_date >=']);
            }

            foreach ($conditions3 as $field => $val) {
                if (str_check_char_array_exist($field, ["=", "<", ">", "!"])) {
                    $where_list[] = "$field'" . $val . "'";
                } else {
                    $where_list[] = "$field='" . $val . "'";
                }
            }

            $where = "";

            if ($where_list) {
                $where = "WHERE " . implode(" AND ", $where_list);
            }

            $q = "
                SELECT 
                    SUM(LT.amount) as sum_amount
                FROM 
                    ledger_transactions LT
                $where                
            ";

            // d($q);

            $temp = DB::select($q);

            // d($temp); exit;

            $ledgerAccount = LedgerAccount::find($conditions['LT.main_account_id']);

            $opening_amount = $ledgerAccount->getOpeningBalance();

            if ($temp[0]->sum_amount) {
                $opening_amount += $temp[0]->sum_amount;
            }

            $conditions3 = $conditions;

            // d($conditions3);
            $where_list = [];

            unset($conditions3['voucher_date >=']);

            foreach ($conditions3 as $field => $val) {
                if (str_check_char_array_exist($field, ["=", "<", ">", "!"])) {
                    $where_list[] = "$field'" . $val . "'";
                } else {
                    $where_list[] = "$field='" . $val . "'";
                }
            }

            $where = "";

            if ($where_list) {
                $where = "WHERE " . implode(" AND ", $where_list);
            }

            $q = "
                SELECT 
                    SUM(LT.amount) as sum_amount
                FROM 
                    ledger_transactions LT
                $where
            ";

            // d($q);

            $temp = DB::select($q);

            // d($temp); exit;

            $closing_amount = $opening_amount;

            if ($temp[0]->sum_amount) {
                $closing_amount += $temp[0]->sum_amount;
            }

            $this->setForView(compact("records", "current_amount", "opening_amount", "closing_amount"));
        }

        $this->setForView(compact("accountList"));

        return $this->view(__FUNCTION__);
    }

    public function current_stock(Request $request)
    {
        $item_list = Item::getListCache("id", "name");
        $warehouse_list = Warehouse::getListCache();

        $conditions = $this->_get_conditions(Route::currentRouteName());

        $records = WarehouseStock::where($conditions)->with([
            "product"
        ])->limit(50000)->get();

        $this->setForView(compact("warehouse_list", "item_list", "records"));

        return $this->view(__FUNCTION__);
    }

    public function stock_movement(Request $request)
    {
        $item_list = Product::getListCache("id", "display_name");

        $warehouse_list = Warehouse::getListCache();

        $warehouse_id = $request->warehouse_id;
        $product_id   = $request->product_id;
        $from_date    = $request->from_date;
        $to_date      = $request->to_date;

        $opening = DB::table('warehouse_stocks')
            ->selectRaw("
            created_at as movement_date,
            warehouse_id,
            product_id,
            'Opening Stock' as type,
            opening_qty as qty_in,
            0 as qty_out,
            price,
            NULL as voucher_no,
            NULL as bill_id,
            NULL as module
        ")->where('opening_qty', '>', 0);

        $purchase = DB::table('purchase_bill_items')
            ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
            ->selectRaw("
            purchase_bills.created_at as movement_date,
            purchase_bills.warehouse_id,
            purchase_bill_items.product_id,
            'Purchase' as type,
            purchase_bill_items.qty as qty_in,
            0 as qty_out,
            purchase_bill_items.rate as price,
            purchase_bills.voucher_no as voucher_no,
            purchase_bills.id as bill_id,
            'purchase' as module
        ");

        $sale = DB::table('sale_bill_items')
            ->join('sale_bills', 'sale_bills.id', '=', 'sale_bill_items.sale_bill_id')
            ->selectRaw("
            sale_bills.created_at as movement_date,
            sale_bills.warehouse_id,
            sale_bill_items.product_id,
            'Sale' as type,
            0 as qty_in,
            sale_bill_items.qty as qty_out,
            sale_bill_items.rate as price,
            sale_bills.voucher_no as voucher_no,
            sale_bills.id as bill_id,
            'sale' as module
        ");

        foreach ([$opening, $purchase, $sale] as $query) {

            if ($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            }

            if ($product_id) {
                $query->where('product_id', $product_id);
            }

            if ($from_date) {
                $query->whereDate('movement_date', '>=', $from_date);
            }

            if ($to_date) {
                $query->whereDate('movement_date', '<=', $to_date);
            }
        }

        $records = $opening
            ->unionAll($purchase)
            ->unionAll($sale)
            ->orderBy('movement_date', 'ASC')
            ->get();

        $balance = 0;
        foreach ($records as $row) {
            $balance += $row->qty_in;
            $balance -= $row->qty_out;
            $row->balance = $balance;
        }

        $groupedRecords = collect($records)->groupBy(function ($row) {
            return $row->warehouse_id . '_' . $row->product_id;
        });

        $this->setForView(compact(
            "groupedRecords",
            "item_list",
            "warehouse_list"
        ));
        return $this->view(__FUNCTION__);
    }


    public function party_products(Request $request)
    {
        $party_list = Party::getListCache("id", "name");
        $product_list = Product::getListCache("id", "display_name");

        $query = Party::with(['products.item']);

        // Optional filters
        if ($request->party_id) {
            $query->where('id', $request->party_id);
        }

        if ($request->product_id) {
            $query->whereHas('products', function ($q) use ($request) {
                $q->where('products.id', $request->product_id);
            });
        }

        $records = $query->get();

        $this->setForView(compact('party_list', 'product_list', 'records'));

        return $this->view(__FUNCTION__);
    }


    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "item_id", "type" => "int"],
            ["field" => "warehouse_id", "type" => "int"]
        ]);

        return $conditions;
    }
}
