<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\DateUtility;
use App\Helpers\CsvUtility;
use App\Helpers\FileUtility;
use App\Models\Item;
use App\Models\Lead;
use App\Models\LeadItem;
use App\Models\Party;
use App\Models\Source;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Imports\LeadsImport;
use App\Models\Followup;
use App\Models\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class LeadController extends BackendController
{
    public String $routePrefix = "lead";
    public $modelClass = Lead::class;

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $query = $this->modelClass::where($conditions)
            ->with(["leadItem.product.item", "party", "user", "assignedUser", "followups" => function ($q) {
                $q->orderBy('id', 'desc');
            }])->orderBy('id', 'desc');

        if (
            !auth()->user()->roles->contains('name', 'System Admin') &&
            !auth()->user()->roles->contains('name', 'SALES MANAGER')
        ) {
            $query->where('assigned_user_id', auth()->id());
        }

        // Missed Followup Filter
        if (request()->missed_followup == 1) {
            $query->whereDate('follow_up_date', '<', Carbon::today())->whereNotIn('status', ['not_intersted', 'mature']);
        }

        $records = $this->getPaginagteRecords($query, Route::currentRouteName());

        $partyList = Party::getListCache();
        $sourceList = Source::pluck('resources', 'id')->toArray();
        $userList = User::getList("id");

        $this->setForView(compact("records", "partyList", "userList", "sourceList"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ['field' => 'is_new', 'type' => 'int'],
            ['field' => 'source_id', 'type' => 'int'],
            ['field' => 'party_id', 'type' => ''],
            ['field' => 'customer_number', 'type' => 'string'],
            ['field' => 'customer_address', 'type' => 'string'],
            ['field' => 'level', 'type' => ''],
            ['field' => 'status', 'type' => ''],
            ['field' => 'follow_up_user_id', 'type' => ''],
            ['field' => 'assigned_user_id', 'type' => 'int'],
            ['field' => 'follow_up_date', 'type' => 'date'],
            ['field' => 'date', 'type' => 'date'],
            ['field' => 'follow_up_type', 'type' => ''],
            ['field' => 'comments', 'type' => 'string'],
            ['field' => 'customer_name', 'type' => 'string'],
            ["field" => "date", "type" => "from_date", "view_field" => "from_date"],
            ["field" => "date", "type" => "to_date", "view_field" => "to_date"],
        ]);

        return $conditions;
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new $this->modelClass();
        $model->date = date(DateUtility::DATE_OUT_FORMAT);

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $this->_set_list_for_form($model);

        $sourceList = Source::pluck('resources', 'id')->toArray();

        $this->setForView(compact("model", 'form', 'sourceList'));

        return $this->view("form");
    }

    private function _set_list_for_form($model)
    {
        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->party_id) {
            $conditions["or_id"] = $model->party_id;
        }

        $partyList = Party::getList("id", "name", $conditions);

        // $itemList = Item::getList("id", "name", $conditions);
        $itemList  = Product::getList("id", "display_name");
        $userList = User::getList("id");


        $this->setForView(compact('partyList', 'itemList', 'userList'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function _common_validation_rules()
    {
        return [
            'date' => 'required|date',
            'level' => 'required|string',
            'party_id' => 'nullable|integer',
            'is_new' => 'nullable|integer',
            'assigned_user_id' => 'nullable|integer|exists:users,id',
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'firm_name' => 'nullable|string',
            'customer_number' => 'nullable|numeric',
            'alternate_number' => 'nullable|numeric',
            'customer_website' => 'nullable|string',
            'customer_address' => 'nullable|string',

            'status' => 'nullable|string',
            'source_id' => 'required|integer|exists:sources,id',
            'not_in_interested_reason' => 'nullable|string',

            'follow_up_date' => 'nullable|date',
            'follow_up_type' => 'nullable|string',
            'follow_up_user_id' => 'nullable|integer|exists:users,id',
            'mature_action_type' => 'nullable|string',
            'comments' => 'nullable|string',

            'is_include_items' => 'nullable|integer',
            'lead_items' => 'nullable|array',
            'lead_items.product_id.*' => 'nullable|integer',
            'lead_items.qty.*' => 'nullable|numeric|min:1',
        ];
    }


    private function _common_validation_messages()
    {
        return [
            'date.required' => 'Lead date is required',
            'date.date' => 'Invalid lead date',

            'level.required' => 'Lead level is required',

            'source_id.required' => 'Lead source is required',

            'customer_email.email' => 'Invalid email address',
            'assigned_user_id.exists' => 'Selected assigned user does not exist',

            'lead_items.qty.*.min' => 'Quantity must be at least 1',
        ];
    }

    public function store(Request $request)
    {
        $this->beforeCreate();
        $rules = $this->_common_validation_rules();
        $messages = $this->_common_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            $leadItems = $validatedData['lead_items'] ?? [];
            unset($validatedData['lead_items']);

            if (!empty($validatedData['follow_up_date']) && !empty($validatedData['follow_up_type'])) {
                if (empty($validatedData['follow_up_user_id'])) {
                    $validatedData['follow_up_user_id'] = Auth::id();
                }
            } else {
                unset(
                    $validatedData['follow_up_date'],
                    $validatedData['follow_up_type'],
                    $validatedData['follow_up_user_id']
                );
            }

            // Create Lead
            $lead = Lead::create($validatedData);

            // Save Lead Items
            if (!empty($leadItems)) {
                foreach ($leadItems['product_id'] as $index => $itemId) {
                    LeadItem::create([
                        'lead_id' => $lead->id,
                        'product_id' => $itemId ?? null,
                        'qty' => $leadItems['qty'][$index] ?? null,
                    ]);
                }
            }

            // Save Follow-up (only if any follow-up data exists)
            if (
                !empty($validatedData['follow_up_date']) &&
                !empty($validatedData['follow_up_type'])
            ) {
                Followup::create([
                    'lead_id' => $lead->id,
                    'follow_up_date' => !empty($validatedData['follow_up_date'])
                        ? Carbon::parse($validatedData['follow_up_date'])->format('Y-m-d')
                        : now()->format('Y-m-d'),
                    'follow_up_type' => $validatedData['follow_up_type'] ?? null,
                    'comments' => $validatedData['comments'] ?? null,
                    'follow_up_user_id' => $validatedData['follow_up_user_id'],
                ]);
            }

            DB::commit();

            return back()->with('success', 'Lead created successfully');
        } catch (\Exception $ex) {
            DB::rollBack();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = $this->modelClass::with([
            "party",
            "leadItem"
        ])->findOrFail($id);
        // ś
        // dd($model->party->name);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $lead_items = $model->leadItem->toArray();
        // dd($lead_items);


        $this->_set_list_for_form($model);

        $sourceList = Source::pluck('resources', 'id')->toArray();
        $partyList = Party::getList('id');


        $this->setForView(compact("model", "form", "lead_items", "sourceList", "partyList"));


        return $this->view("form");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->beforeCreate();

        $lead = Lead::findOrFail($id);

        $rules = $this->_common_validation_rules();
        $messages = $this->_common_validation_messages();

        // extra rule only for update
        $rules['status'] = 'required|string';

        $validatedData = $request->validate($rules, $messages);

        if (empty($validatedData['follow_up_user_id'])) {
            $validatedData['follow_up_user_id'] = Auth::id();
        }

        DB::beginTransaction();

        try {
            // Separate lead items
            $leadItems = $validatedData['lead_items'] ?? [];
            unset($validatedData['lead_items']);

            // Update Lead
            $lead->update($validatedData);

            /*
        |--------------------------------------------------------------------------
        | Handle Lead Items (Update / Create / Delete)
        |--------------------------------------------------------------------------
        */
            $existingItems = LeadItem::where('lead_id', $lead->id)
                ->get()
                ->keyBy('id');

            if (!empty($leadItems['product_id']) && is_array($leadItems['product_id'])) {
                foreach ($leadItems['product_id'] as $index => $itemId) {

                    if (empty($itemId)) {
                        continue;
                    }

                    $qty = $leadItems['qty'][$index] ?? 0;
                    $itemRowId = $leadItems['id'][$index] ?? null;

                    if ($itemRowId && isset($existingItems[$itemRowId])) {
                        // Update existing
                        $existingItems[$itemRowId]->update([
                            'product_id' => $itemId,
                            'qty'     => $qty,
                        ]);

                        unset($existingItems[$itemRowId]);
                    } else {
                        // Create new
                        LeadItem::create([
                            'lead_id' => $lead->id,
                            'product_id' => $itemId,
                            'qty'     => $qty,
                        ]);
                    }
                }
            }

            // Delete removed items
            if ($existingItems->isNotEmpty()) {
                LeadItem::destroy($existingItems->keys());
            }

            /*
        |--------------------------------------------------------------------------
        | Follow-up (Create new entry only)
        |--------------------------------------------------------------------------
        */

            $lastFollowup = $lead->latestFollowUp;

            $newDate = !empty($validatedData['follow_up_date'])
                ? Carbon::parse($validatedData['follow_up_date'])->format('Y-m-d')
                : null;

            $newType = $validatedData['follow_up_type'] ?? null;

            $lastDate = $lastFollowup
                ? Carbon::parse($lastFollowup->follow_up_date)->format('Y-m-d')
                : null;

            $lastType = $lastFollowup?->follow_up_type;
            if (
                $newDate && $newType &&
                (!$lastFollowup || $lastDate !== $newDate || $lastType !== $newType)
            ) {
                Followup::create([
                    'lead_id' => $lead->id,
                    'follow_up_date' => $newDate,
                    'follow_up_type' => $newType,
                    'comments' => $validatedData['comments'] ?? null,
                    'follow_up_user_id' => $validatedData['follow_up_user_id'],
                    // 'follow_up_user_id' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', 'Lead updated successfully');
        } catch (\Exception $ex) {

            DB::rollBack();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function updateMissed(Request $request)
    {
        $validate_data = $request->validate([
            'id' => 'required|exists:leads,id',
            'status' => 'required|string',
            'follow_up_date' => 'nullable|date',
            'follow_up_type' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($request->id);

        try {

            $lead->update([
                'status' => $validate_data['status'],
                'follow_up_date' => $validate_data['follow_up_date'],
                'follow_up_type' => $validate_data['follow_up_type'],
                'comments' => $validate_data['comments'],
            ]);

            if (!empty($validate_data['follow_up_date']) || !empty($validate_data['follow_up_type']) || !empty($validate_data['comments'])) {
                Followup::create([
                    'lead_id' => $lead->id,
                    'follow_up_date' => $validate_data['follow_up_date'] ?? now(),
                    'follow_up_type' => $validate_data['follow_up_type'] ?? null,
                    'comments' => $validate_data['comments'] ?? null,
                    'follow_up_user_id' => Auth::id(),
                ]);
            }

            // return redirect()->route($this->routePrefix . ".index")->with('success', 'Lead updated successfully');
            return redirect()->back()->with('success', 'Lead updated successfully!');
        } catch (\Exception $ex) {
            Log::error("Error updating lead follow-up", [
                'lead_id' => $request->id,
                'user_id' => Auth::id(),
                'error' => $ex->getMessage(),
                'input' => $request->all(),
            ]);
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $lead = Lead::findOrFail($id);

            $leadItems = LeadItem::where('lead_id', $id);

            if ($leadItems->exists()) {
                $leadItems->delete();
            }

            $lead->delete();

            return back()->with('success', 'Lead deleted successfully');
        } catch (Exception $ex) {
            return back()->with('fail', 'Error: ' . $ex->getMessage());
        }
    }

    protected function beforeViewRender()
    {
        parent::beforeViewRender();

        $levelList = config('constant.level');
        $statusList = config('constant.status');
        $followtypeList = config('constant.followuptype');
        $maturefieldList = config('constant.maturefield');
        $quotationstatusList = config('constant.newquotationstatus');

        $this->setForView(compact(
            'levelList',
            'statusList',
            'followtypeList',
            'maturefieldList',
            'quotationstatusList'
        ));
    }

    // get lead ajax

    public function getLead(Request $request)
    {

        $lead = Lead::with(['leadItem.Item', 'party'])->where('id', $request->lead_id)->first();

        if (!$lead) {
            return response()->json(['error' => 'No Records Found'], 404);
        }

        $leadItems = $lead->leadItem->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'qty' => $item->qty,
                'item_name' => $item->Item->name ?? '',
            ];
        });

        return response()->json([
            'party' => [
                'id' => $lead->party->id,
                'name' => $lead->party->name,
            ],
            'items' => $leadItems,
        ]);
    }

    public function csv()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $query = $this->modelClass::where($conditions)
            ->with([
                "leadItem.product.item",
                "user",
                "assignedUser",
                "followups" => function ($q) {
                    $q->orderBy('id', 'desc');
                }
            ])
            ->orderBy('id', 'desc');

        // ✅ ROLE LOGIC
        if (
            !auth()->user()->roles->contains('name', 'System Admin') &&
            !auth()->user()->roles->contains('name', 'SALES MANAGER')
        ) {
            $query->where('assigned_user_id', auth()->id());
        }

        // ✅ FILTER
        if (request()->missed_followup == 1) {
            $query->whereDate('follow_up_date', '<', \Carbon\Carbon::today())
                ->where('status', '!=', 'not_intersted');
        }

        // ✅ GET DATA (NO PAGINATION)
        $records = $query->get()->toArray();

        // ✅ SOURCE LIST
        $sourceList = \App\Models\Source::pluck('resources', 'id')->toArray();

        $csv_records = [];

        foreach ($records as $record) {

            // ✅ LAST FOLLOWUP
            $last_followup = $record['followups'][0] ?? null;

            // ✅ MULTIPLE PRODUCTS HANDLE
            $product_names = [];
            if (!empty($record['lead_item'])) {
                foreach ($record['lead_item'] as $item) {
                    $product_names[] = $item['product']['item']['name'] ?? '';
                }
            }
            $product_string = implode(', ', array_filter($product_names));

            $csv_records[] = [
                'ID' => $record['id'] ?? '',

                // ✅ DIRECT FIELDS
                'Customer Name' => $record['customer_name'] ?? '',
                'Mobile' => $record['customer_number'] ?? '',
                'Alternate Number' => $record['alternate_number'] ?? '',

                // ✅ PRODUCT
                'Product' => $product_string,

                // ✅ SOURCE
                'Source' => $sourceList[$record['source_id']] ?? '',

                // ✅ USER
                'Assigned To' => $record['assigned_user']['name'] ?? '',

                // ✅ STATUS / LEVEL
                'Level' => strtoupper($record['level'] ?? ''),
                'Status' => $record['status'] ?? '',

                // ✅ FOLLOWUP
                'Followup Date' => $record['follow_up_date'] ?? '',
                'Followup Type' => $record['follow_up_type'] ?? '',
                // 'Last Followup Remark' => $last_followup['remark'] ?? '',

                // ✅ COMMENTS
                'Comments' => $record['comments'] ?? '',

                // ✅ DATE
                'Created Date' => isset($record['created_at'])
                    ? date('d-m-Y', strtotime($record['created_at']))
                    : '',
            ];
        }

        // ✅ FILE CREATE
        $path = config('constant.path.temp');
        FileUtility::createFolder($path);

        $file = $path . "leads_" . date('Ymd_His') . ".csv";

        $csvUtility = new CsvUtility($file);
        $csvUtility->write($csv_records);

        return download_start($file, "application/octet-stream");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        Excel::import(new LeadsImport, $request->file('file'));

        return back()->with('success', 'Leads imported successfully!');
    }
}
