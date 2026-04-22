<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Models\AutoIncreament;
use App\Models\Complaint;
use App\Models\ComplaintAssignment;
use App\Models\ComplaintItem;
use App\Models\Item;
use App\Models\NewComplaint;
use App\Models\NewComplaintItem;
use App\Models\Party;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

class ComplaintController extends BackendController
{
    public String $routePrefix = "complaint";

    public $modelClass = Complaint::class;

    public function index()
    {
        $builder = $this->_getBuilder();

        // $builder = $builder->orderBy('id', 'desc');

        $records = $this->getPaginagteRecords(
            $builder,
            Route::currentRouteName()
        );

        // dd($records->toArray());

        $partyList = Party::getListCache();
        $complaintstatusList = config('constant.complaintstatus');
        $paymentStatusList = config('constant.paymentstatus');
        $paymentModeList = config('constant.paymentmode');

        $this->setForView(compact("records", "partyList", "complaintstatusList","paymentStatusList","paymentModeList"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _getBuilder()
    {
        $cache_key = Route::currentRouteName();

        $conditions = $this->getConditions($cache_key, [
            ["field" => "date", "type" => "date",],
            ["field" => "party_id", "type" => "int"],
            ["field" => "complaint_no", "type" => "string"],
            ["field" => "contact_number", "type" => "int"],
            ["field" => "contact_person", "type" => "string"],
            ["field" => "status", "type" => "string"],
            ["field" => "payment_mode", "type" => "string"],
            ["field" => "payment_status", "type" => "string"],
            ["field" => "date", "type" => "from_date", "view_field" => "from_date"],
            ["field" => "date", "type" => "to_date", "view_field" => "to_date"],
        ]);

        $builder = $this->modelClass::where($conditions)->with([
            'complaintItems.product.item',
            'user',
            'assignments.assignedUser',
            'assignments.assignedByUser'
        ]);

        return $builder;
    }

    public function create()
    {
        $model = new $this->modelClass();
        $model->complaint_no = AutoIncreament::getNextCounter(AutoIncreament::TYPE_COMPLAINT);
        $model->date = date(DateUtility::DATE_OUT_FORMAT);

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);
        $model->status = 'pending';
        $model->payment_status = 'pending';

        $this->setForView(compact("model", 'form'));


        return $this->view("form");
    }

    private function _set_list_for_form($model)
    {
        $conditions = [
            "is_customer" => 1,
            "or_id" => []
        ];

        if ($model && $model->party_id) {
            $conditions["or_id"] = $model->party_id;
        }

        $partyList = Party::getList("id", "name", $conditions);


        $conditions = [
            "or_id" => []
        ];
        $userList = User::getList("id");
        $itemList  = Product::getList("id", "display_name");
        $complaintstatusList = config('constant.complaintstatus');
        $paymentStatusList = config('constant.paymentstatus');
        $paymentModeList = config('constant.paymentmode');
        $levelList = config('constant.level');

        $this->setForView(compact('paymentModeList','paymentStatusList', 'partyList', 'userList', 'complaintstatusList', 'levelList', 'itemList'));
        // $this->setForView(compact('partyList', 'userList', 'complaintstatusList', 'itemList'));
    }

    public function getCustomerDetails($id)
    {
        $party = Party::find($id);

        if ($party) {
            return response()->json([
                'contact_number' => $party->mobile,
                'contact_person' => $party->name,
                'address' => $party->address
            ]);
        }

        return response()->json([], 404);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'date' => 'required|date',
            'party_id' => 'required|integer',
            'complainant_mobile' => 'nullable|string|max:180',
            'contact_number' => 'required|string|max:180',
            'contact_person' => 'required|string|max:180',
            'remarks' => 'nullable|string',
            'level' => 'required|in:hot,warm,cold',
            'is_under_warranty' => 'sometimes|boolean',
            'payment_status' => 'required_unless:is_under_warranty,1|nullable|in:pending,received',
            'payment_mode' => 'required_unless:is_under_warranty,1|nullable|in:cash,g_pay,bank,cheque,other',
            'amount' => 'required_unless:is_under_warranty,1|nullable|integer|min:1',
            // 'is_under_warranty' => 'nullable|boolean',
            // 'payment_mode' => 'nullable|in:cash,g_pay,bank,cheque,other|required_if:payment_status,received',
            // 'payment_status' => 'nullable|in:pending,received',
            // 'amount' => 'nullable|integer|min:1|required_if:payment_status,received',
            'assign_to' => 'required',
            'sale_bill_no' => 'required_if:is_free,1|nullable|integer',
            'is_free' => 'nullable|integer',
            'is_new_party' => 'nullable|integer',

            'complaint_items' => 'nullable|array',
            'complaint_items.*.product_id' => 'required|integer|exists:products,id',
            'complaint_items.*.remarks' => 'nullable|string|max:500',
            'complaint_items.*.reading' => 'nullable|string|max:500',
        ], [
            'amount.required_if' => 'The amount field is required',
            'complaint_items.*.product_id.required' => 'Product is required',
        ]);

        DB::beginTransaction();

        try {
            $complaintItems = $request->complaint_items ?? [];

            unset($validated['complaint_items']);

            $validated['status'] = $validated['status'] ?? 'pending';
            $validated['complaint_no'] = AutoIncreament::getNextCounter(
                AutoIncreament::TYPE_COMPLAINT
            );

            // ✅ CREATE COMPLAINT
            $complaint = Complaint::create($validated);

            ComplaintAssignment::create([
                'complaint_id' => $complaint->id,
                'assign_to'    => $request->assign_to,
                'assigned_by'  => auth()->id(),
            ]);

            // ✅ SAVE COMPLAINT ITEMS
            foreach ($complaintItems as $item) {
                ComplaintItem::create([
                    'complaint_id' => $complaint->id,
                    'product_id'   => $item['product_id'],
                    'remarks'      => $item['remarks'] ?? null,
                    'reading'      => $item['reading'] ?? null,
                ]);
            }

            AutoIncreament::increaseCounter(AutoIncreament::TYPE_COMPLAINT);
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Complaint recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('fail', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $model = $this->modelClass::with("complaintItems")->findOrFail($id);
        // dd($model);
        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $complaint_items = $model->complaintItems->toArray();

        $this->_set_list_for_form($model);

        $this->setForView(compact('model', 'form', 'complaint_items'));

        return $this->view('form');
    }

    public function update(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'party_id' => 'required|integer',
            'complainant_mobile' => 'nullable|string|max:180',
            'contact_number' => 'required|string|max:180',
            'contact_person' => 'required|string|max:180',
            'remarks' => 'nullable|string',
            'level' => 'required|in:hot,warm,cold',
            'is_under_warranty' => 'sometimes|boolean',
            'payment_status' => 'required_unless:is_under_warranty,1|nullable|in:pending,received',
            'payment_mode' => 'required_unless:is_under_warranty,1|nullable|in:cash,g_pay,bank,cheque,other',
            'amount' => 'required_unless:is_under_warranty,1|nullable|integer|min:1',
            // 'payment_mode' => 'nullable|in:cash,g_pay,bank,cheque,other|required_if:payment_status,received',
            // 'payment_status' => 'required|in:pending,received',
            // 'amount' => 'nullable|integer|min:1|required_if:payment_status,received',
            'status' => 'required|in:pending,in_progress,hold,done',
            'assign_to' => 'required',
            'sale_bill_no' => 'required_if:is_free,1|nullable|integer',
            'is_free' => 'nullable|integer',
            'is_new_party' => 'nullable|integer',

            'complaint_items' => 'nullable|array',
            'complaint_items.*.id' => 'nullable|integer|exists:complaint_items,id',
            'complaint_items.*.product_id' => 'required|integer|exists:products,id',
            'complaint_items.*.remarks' => 'nullable|string|max:500',
            'complaint_items.*.reading' => 'nullable|string|max:500',
        ], [
            'amount.required_if' => 'The amount field is required',
            'complaint_items.*.product_id.required' => 'Product is required',
        ]);

        DB::beginTransaction();

        try {
            $complaintItems = $request->complaint_items ?? [];

            unset($validated['complaint_items']);

            // store old assignment
            $oldAssignTo = $complaint->assign_to;

            $complaint->update($validated);

           // ✅ SAVE ASSIGNMENT HISTORY IF CHANGED
            if ($oldAssignTo != $request->assign_to) {
                ComplaintAssignment::create([
                    'complaint_id' => $complaint->id,
                    'assign_to' => $request->assign_to,
                    'assigned_by' => auth()->id(),
                ]);
            }

            // Existing item IDs
            $existingIds = $complaint->complaintItems()->pluck('id')->toArray();
            $submittedIds = [];

            // ✅ CREATE / UPDATE ITEMS
            foreach ($complaintItems as $item) {

                // UPDATE
                if (!empty($item['id'])) {
                    $complaintItem = ComplaintItem::where('complaint_id', $complaint->id)
                        ->where('id', $item['id'])
                        ->first();

                    if ($complaintItem) {
                        $complaintItem->update([
                            'product_id' => $item['product_id'],
                            'remarks' => $item['remarks'] ?? null,
                            'reading' => $item['reading'] ?? null,
                        ]);
                        $submittedIds[] = $complaintItem->id;
                    }
                }
                // CREATE
                else {
                    $newItem = ComplaintItem::create([
                        'complaint_id' => $complaint->id,
                        'product_id' => $item['product_id'],
                        'remarks' => $item['remarks'] ?? null,
                        'reading' => $item['reading'] ?? null,
                    ]);
                    $submittedIds[] = $newItem->id;
                }
            }

            // ✅ DELETE REMOVED ITEMS
            $deleteIds = array_diff($existingIds, $submittedIds);
            if (!empty($deleteIds)) {
                ComplaintItem::whereIn('id', $deleteIds)->delete();
            }

            DB::commit();

            return redirect()
                ->route($this->routePrefix . ".index")
                ->with('success', 'Complaint updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('fail', $e->getMessage());
        }
    }


    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return $this->_destroy($model);
    }

    public function getPartyProducts($partyId)
    {
        $products = Party::findOrFail($partyId)
            ->products()
            ->select('products.id', 'products.sku')
            ->get();

        return response()->json($products);
    }

     public function ajax_get($id)
    {
        $response = ["status" => 1];
        try {
            $model = $this->modelClass::with("party.city.state", "complaintItems.product.item")->findOrFail($id);
            // dd($model);
            $response['data'] = $model->toArray();
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }

    // export csv


public function exportCsv()
{
    // Get all complaints with related items
    $complaints = \App\Models\Complaint::with('complaintItems','user', 'party') // assuming relationship is `complaintItems()`
        ->orderBy('id', 'desc')
        ->get();

    $filename = 'complaints_export_' . date('Y-m-d_H-i-s') . '.csv';
    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=\"$filename\"",
    ];

    $columns = [
        'ID',
        'Date',
        'Complaint No',
        'Contact Number',
        'Contact Person',
        'Status',
        'Level',
        'Is New Party',
        'Is Free',
        'Party',
        'Assign To',
        'Amount',
        'Sale Bill No',
        'Remarks',
        'Item Product IDs',
        'Item Remarks'
    ];

    $callback = function () use ($complaints, $columns) {
        $file = fopen('php://output', 'w');

        // Header row
        fputcsv($file, $columns);

        foreach ($complaints as $c) {
            // $itemProductIds = $c->complaintItems->pluck('product_id')->implode('|');
            $itemProductNames = $c->complaintItems->map(function($complaintItems){
                return $complaintItems->product->sku ?? $complaintItems->product_id;
            })->implode('|');
            $itemRemarks = $c->complaintItems->pluck('remarks')->implode('|');

            $row = [
                $c->id,
                $c->date,
                $c->complaint_no,
                $c->contact_number,
                $c->contact_person,
                $c->status,
                $c->level,
                $c->is_new_party,
                $c->is_free,
                $c->party->name ?? $c->party_id,   // party name
                $c->user->name ?? $c->assign_to,
                $c->amount,
                $c->sale_bill_no,
                $c->remarks,
                $itemProductNames,
                $itemRemarks,
            ];

            fputcsv($file, $row);
        }

        fclose($file);
    };

    return Response::stream($callback, 200, $headers);
}

}
