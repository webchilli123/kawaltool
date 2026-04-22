<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Models\AutoIncreament;
use App\Models\Complaint;
use App\Helpers\CsvUtility;
use App\Helpers\FileUtility;
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
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $query = $this->modelClass::where($conditions)
            ->with([
                'complaintItems.product.item',
                'user',
                'assignments.assignedUser',
                'assignments.assignedByUser'
            ])->orderBy('id', 'desc');

              if (
                !auth()->user()->roles->contains('name', 'System Admin') &&
                !auth()->user()->roles->contains('name', 'SALES MANAGER')
            ) {
                $query->where('assign_to', auth()->id());
            }

        $records = $this->getPaginagteRecords($query, Route::currentRouteName());

        $partyList = Party::getListCache();
        $complaintstatusList = config('constant.complaintstatus');
        $paymentStatusList = config('constant.paymentstatus');
        $paymentModeList = config('constant.paymentmode');

        $this->setForView(compact("records", "partyList", "complaintstatusList", "paymentStatusList", "paymentModeList"));


        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
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

        return $conditions;
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

        $this->setForView(compact('paymentModeList', 'paymentStatusList', 'partyList', 'userList', 'complaintstatusList', 'levelList', 'itemList'));
        // $this->setForView(compact('partyList', 'userList', 'complaintstatusList', 'itemList'));
    }

    public function getCustomerDetails($id)
    {
        $party = Party::select('mobile','name','address')->find($id);

        if ($party) {
            return response()->json([
                'contact_number' => $party->mobile,
                'contact_person' => $party->name,
                'address' => $party->address
            ]);
        }

        return response()->json([], 404);
    }

    private function _common_validation_rules()
    {
        return [
            'date' => 'required|date',
            'party_id' => 'required|integer',
            'complainant_mobile' => 'nullable|string|max:180',
            'contact_number' => 'required|string|max:180',
            'contact_person' => 'required|string|max:180',
            'remarks' => 'nullable|string',
            'status' => 'nullable|string',
            'level' => 'required|in:hot,warm,cold',
            'is_under_warranty' => 'sometimes|boolean',
            'payment_status' => 'required_unless:is_under_warranty,1|nullable|in:pending,received',
            'payment_mode' => 'required_unless:is_under_warranty,1|nullable|in:cash,g_pay,bank,cheque,other',
            'amount' => 'required_unless:is_under_warranty,1|nullable|integer|min:1',
            'assign_to' => 'required',
            'sale_bill_no' => 'required_if:is_free,1|nullable|integer',
            'is_free' => 'nullable|integer',
            'is_new_party' => 'nullable|integer',

            'complaint_items' => 'nullable|array',
            'complaint_items.*.product_id' => 'required|integer|exists:products,id',
            'complaint_items.*.remarks' => 'nullable|string|max:500',
            'complaint_items.*.reading' => 'nullable|string|max:500',
        ];
    }


    private function _common_validation_messages()
    {
        return [
            'amount.required_if' => 'The amount field is required',
            'complaint_items.*.product_id.required' => 'Product is required',
        ];
    }

    public function store(Request $request)
    {
        $rules = $this->_common_validation_rules();
        $messages = $this->_common_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try {

            $complaintItems = $validatedData['complaint_items'] ?? [];
            unset($validatedData['complaint_items']);

            $validatedData['status'] = $validatedData['status'] ?? 'pending';

            $validatedData['complaint_no'] = AutoIncreament::getNextCounter(
                AutoIncreament::TYPE_COMPLAINT
            );

            // ✅ CREATE COMPLAINT
            $complaint = Complaint::create($validatedData);

            ComplaintAssignment::create([
                'complaint_id' => $complaint->id,
                'assign_to'    => $validatedData['assign_to'],
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

        $rules = $this->_common_validation_rules();
        $messages = $this->_common_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            $complaintItems = $validatedData['complaint_items'] ?? [];
            unset($validatedData['complaint_items']);

            // store old assignment
            $oldAssignTo = $complaint->assign_to;

            $complaint->update($validatedData);

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
            $model = $this->modelClass::with("party.city.state","user", "complaintItems.product.item")->findOrFail($id);
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
        // ✅ SAME AS LEADS
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $query = $this->modelClass::where($conditions)
            ->with([
                'complaintItems.product.item',
                'user',
                'party',
                'assignments.assignedUser',
                'assignments.assignedByUser'
            ])
            ->orderBy('id', 'desc');

        // ✅ GET DATA (NO PAGINATION)
        $records = $query->get()->toArray();

        $csv_records = [];

        foreach ($records as $record) {

            // ✅ MULTIPLE ITEMS HANDLE
            $product_names = [];
            $item_remarks = [];

            if (!empty($record['complaint_items'])) {
                foreach ($record['complaint_items'] as $item) {

                    $product_names[] = $item['product']['item']['name']
                        ?? ($item['product']['sku'] ?? '');

                    $item_remarks[] = $item['remarks'] ?? '';
                }
            }

            $product_string = implode(', ', array_filter($product_names));
            $remarks_string = implode(', ', array_filter($item_remarks));

            $csv_records[] = [

                'ID' => $record['id'] ?? '',
                'Date' => $record['date'] ?? '',
                'Complaint No' => $record['complaint_no'] ?? '',

                'Contact Number' => $record['contact_number'] ?? '',
                'Contact Person' => $record['contact_person'] ?? '',

                'Status' => $record['status'] ?? '',
                'Level' => strtoupper($record['level'] ?? ''),

                'Payment Mode' => $record['payment_mode'] ?? '',
                'Payment Status' => $record['payment_status'] ?? '',

                'Is New Party' => $record['is_new_party'] ?? '',
                'Is Free' => $record['is_free'] ?? '',

                'Party' => $record['party']['name'] ?? '',
                'Assign To' => $record['user']['name'] ?? '',

                'Amount' => $record['amount'] ?? '',
                'Sale Bill No' => $record['sale_bill_no'] ?? '',

                'Remarks' => $record['remarks'] ?? '',

                // ✅ ITEMS
                'Products' => $product_string,
                'Item Remarks' => $remarks_string,

                // ✅ DATE FORMAT
                'Created Date' => isset($record['created_at'])
                    ? date('d-m-Y', strtotime($record['created_at']))
                    : '',
            ];
        }

        // ✅ FILE CREATE (same as leads)
        $path = config('constant.path.temp');
        FileUtility::createFolder($path);

        $file = $path . "complaints_" . date('Ymd_His') . ".csv";

        $csvUtility = new CsvUtility($file);
        $csvUtility->write($csv_records);

        return download_start($file, "application/octet-stream");
    }
}
