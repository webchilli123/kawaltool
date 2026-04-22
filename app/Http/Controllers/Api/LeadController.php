<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Followup;
use App\Models\Lead;
use App\Models\User;
use App\Models\LeadItem;
use App\Models\Party;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{

    public function create()
    {
        $source = Source::select('id', 'resources')->get();
        $parties = Party::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        $level = config('constant.level');
        $status = config('constant.status');
        $followUpTypes = config('constant.followuptype');
        return response()->json([
            'status' => true,
            'data' => [
                'sources'  => $source,
                'parties'  => $parties,
                'users'    => $users,
                'levels'   => $level,
                'statuses' => $status,
                'followUpTypes' => $followUpTypes,
            ]
        ]);
    }

    public function index(Request $request)
    {
        try {

            $query = Lead::query();

            // ✅ Filters (same as your system)
            if ($request->filled('is_new')) {
                $query->where('is_new', $request->is_new);
            }

            if ($request->filled('source_id')) {
                $query->where('source_id', $request->source_id);
            }

            if ($request->filled('party_id')) {
                $query->where('party_id', $request->party_id);
            }

            if ($request->filled('customer_number')) {
                $query->where('customer_number', 'like', '%' . $request->customer_number . '%');
            }

            // ✅ Load relations (same as web)
            $query->with([
                'leadItem.product.item',
                'party',
                'user',
                'assignedUser',
                'followups' => function ($q) {
                    $q->orderBy('id', 'desc');
                }
            ]);

            if (
                !auth()->user()->roles->contains('name', 'System Admin') &&
                !auth()->user()->roles->contains('name', 'SALES MANAGER')
            ) {
                $query->where('assigned_user_id', auth()->id());
            }

            $leads = $query->orderBy('id', 'desc')->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Lead list fetched successfully',
                'data' => $leads
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch leads',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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

            // 'lead_items' => 'nullable|array',
            // 'lead_items.product_id.*' => 'nullable|integer',
            // 'lead_items.qty.*' => 'nullable|numeric|min:1',
            'is_include_items' => 'nullable|integer',

            'lead_items' => 'required_if:is_include_items,1|array',
            'lead_items.*.product_id' => 'required|integer|exists:products,id',
            'lead_items.*.qty' => 'required|numeric|min:1',
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
        $rules = $this->_common_validation_rules();
        $messages = $this->_common_validation_messages();

        $data = $request->validate($rules, $messages);

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        DB::beginTransaction();

        try {
            $leadItems = $data['lead_items'] ?? [];
            unset($data['lead_items']);

            // ✅ Follow-up logic
            if (!empty($data['follow_up_date']) && !empty($data['follow_up_type'])) {

                if (empty($data['follow_up_user_id'])) {
                    $data['follow_up_user_id'] = Auth::id();
                }
            } else {
                unset(
                    $data['follow_up_date'],
                    $data['follow_up_type'],
                    $data['follow_up_user_id']
                );
            }

            // ✅ Create Lead
            $lead = Lead::create($data);

            // ✅ Save Items (better structure)
            foreach ($leadItems as $item) {
                LeadItem::create([
                    'lead_id' => $lead->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }

            // ✅ Save Follow-up
            if (!empty($data['follow_up_date']) && !empty($data['follow_up_type'])) {

                Followup::create([
                    'lead_id' => $lead->id,
                    'follow_up_date' => Carbon::parse($data['follow_up_date'])->format('Y-m-d'),
                    'follow_up_type' => $data['follow_up_type'],
                    'comments' => $data['comments'] ?? null,
                    'follow_up_user_id' => $data['follow_up_user_id'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Lead created successfully',
                'data' => $lead->load([
                    'leadItem.product.item',
                    'party',
                    'user',
                    'assignedUser',
                    'followups'
                ])
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Lead creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {

            $lead = Lead::with([
                'leadItem.product.item',
                'party',
                'user',
                'assignedUser',
                'followups'
            ])->findOrFail($id);

            if (!$lead) {
                return response()->json([
                    'status' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Lead fetched successfully',
                'data' => $lead
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Lead not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    private function _common_validation_rules_update()
    {
        return [
            'date' => 'sometimes|required|date',
            'level' => 'sometimes|required|string',
            'source_id' => 'sometimes|required|integer|exists:sources,id',

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
            'not_in_interested_reason' => 'nullable|string',

            'follow_up_date' => 'nullable|date',
            'follow_up_type' => 'nullable|string',
            'follow_up_user_id' => 'nullable|integer|exists:users,id',
            'mature_action_type' => 'nullable|string',
            'comments' => 'nullable|string',

            'is_include_items' => 'nullable|integer',

            'lead_items' => 'sometimes|array|required_if:is_include_items,1',

            'lead_items.*.id' => 'sometimes|integer|exists:lead_items,id',
            'lead_items.*.product_id' => 'required_with:lead_items|integer|exists:products,id',
            'lead_items.*.qty' => 'required_with:lead_items|numeric|min:1',
        ];
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            $this->_common_validation_rules_update(),
            $this->_common_validation_messages()
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        // ✅ Handle followup user ONLY if followup is being updated
        if (
            (isset($validatedData['follow_up_date']) || isset($validatedData['follow_up_type'])) &&
            empty($validatedData['follow_up_user_id'])
        ) {
            $validatedData['follow_up_user_id'] = Auth::id();
        }

        DB::beginTransaction();

        try {

            // ✅ Check if lead_items key exists
            $hasItems = array_key_exists('lead_items', $validatedData);
            $leadItems = $validatedData['lead_items'] ?? [];

            unset($validatedData['lead_items']);

            // ✅ Update lead
            $lead->update($validatedData);

            /*
        |------------------------------------------------------------------
        | Handle Lead Items (SAFE SYNC)
        |------------------------------------------------------------------
        */

            if ($hasItems) {

                $existingItems = $lead->leadItem()->get()->keyBy('id');

                foreach ($leadItems as $item) {

                    $itemId = $item['product_id'];
                    $qty    = $item['qty'];
                    $rowId  = $item['id'] ?? null;

                    if ($rowId && isset($existingItems[$rowId])) {

                        // ✅ Update existing
                        $existingItems[$rowId]->update([
                            'product_id' => $itemId,
                            'qty' => $qty,
                        ]);

                        unset($existingItems[$rowId]);
                    } else {

                        // ✅ Create new
                        LeadItem::create([
                            'lead_id' => $lead->id,
                            'product_id' => $itemId,
                            'qty' => $qty,
                        ]);
                    }
                }

                // ✅ Delete ONLY remaining items (safe sync)
                if ($existingItems->isNotEmpty()) {
                    LeadItem::destroy($existingItems->keys());
                }
            }

            /*
        |------------------------------------------------------------------
        | Follow-up logic
        |------------------------------------------------------------------
        */

            $lastFollowup = $lead->latestFollowUp;

            $newDate = isset($validatedData['follow_up_date'])
                ? Carbon::parse($validatedData['follow_up_date'])->format('Y-m-d')
                : null;

            $newType = $validatedData['follow_up_type'] ?? null;

            $lastDate = $lastFollowup
                ? Carbon::parse($lastFollowup->follow_up_date)->format('Y-m-d')
                : null;

            $lastType = $lastFollowup?->follow_up_type;

            if (
                ($newDate || $newType) &&
                (!$lastFollowup || $lastDate !== $newDate || $lastType !== $newType)
            ) {
                Followup::create([
                    'lead_id' => $lead->id,
                    'follow_up_date' => $newDate,
                    'follow_up_type' => $newType,
                    'comments' => $validatedData['comments'] ?? null,
                    'follow_up_user_id' => $validatedData['follow_up_user_id'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Lead updated successfully',
                'data' => $lead->load([
                    'leadItem.product.item',
                    'party',
                    'user',
                    'assignedUser',
                    'followups'
                ])
            ]);
        } catch (\Exception $ex) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Lead update failed',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function updateFollowupApi(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string',
            'follow_up_date' => 'required|date',
            'follow_up_type' => 'required|string',
            'comments' => 'nullable|string',
        ]);


        // 🔥 Safe comparison
        $isSame =
            \Carbon\Carbon::parse($lead->follow_up_date)->format('Y-m-d') === $validated['follow_up_date'] &&
            $lead->follow_up_type === $validated['follow_up_type'] &&
            ($lead->comments ?? '') === ($validated['comments'] ?? '');

        if ($isSame) {
            return response()->json([
                'status' => false,
                'message' => 'No changes detected. Update at least one field.'
            ], 422);
        }

        // ✅ Update
        $lead->update([
            'status' => $validated['status'],
            'follow_up_date' => $validated['follow_up_date'],
            'follow_up_type' => $validated['follow_up_type'],
            'comments' => $validated['comments'] ?? null,
        ]);

        // ✅ Save history
        Followup::create([
            'lead_id' => $lead->id,
            'follow_up_date' => $validated['follow_up_date'],
            'follow_up_type' => $validated['follow_up_type'],
            'comments' => $validated['comments'] ?? null,
            'follow_up_user_id' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Lead updated successfully'
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $lead = Lead::findOrFail($id);

            if (!$lead) {
                return response()->json([
                    'status' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            $lead->leadItem()->delete();
            $lead->followups()->delete();

            $lead->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Lead deleted successfully'
            ]);
        } catch (\Exception $ex) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Lead deletion failed',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
