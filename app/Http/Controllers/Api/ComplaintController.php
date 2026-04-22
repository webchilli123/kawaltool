<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoIncreament;
use App\Models\Complaint;
use App\Models\ComplaintAssignment;
use App\Models\ComplaintItem;
use App\Models\Party;
use App\Models\Source;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    public function create()
    {
        $parties = Party::select('id', 'name')->get();
        $status = config('constant.complaintstatus');
        $level = config('constant.level');
        $users = User::select('id', 'name')->get();
        $paymentStatuses = config('constant.paymentstatus');
        $paymentModes = config('constant.paymentmode');

        return response()->json([
            'status' => true,
            'data' => [
                'parties'  => $parties,
                'statuses' => $status,
                'levels'   => $level,
                'users'    => $users,
                'paymentStatuses'    => $paymentStatuses,
                'paymentModes'    => $paymentModes,
            ]
        ]);
    }

    public function index(Request $request)
    {
        try {

            $query = Complaint::query();

            $query->with([
                'complaintItems.product.item',
                'user',
                'assignments:id,complaint_id,assign_to,assigned_by',
                'assignments.assignedUser:id,name',
                'assignments.assignedByUser:id,name'
            ])->orderBy('id', 'desc');

            if (
                !auth()->user()->roles->contains('name', 'System Admin') &&
                !auth()->user()->roles->contains('name', 'SALES MANAGER')
            ) {
                $query->where('assign_to', auth()->id());
            }

            $complaints = $query->orderBy('id', 'desc')->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Complaint list fetched successfully',
                'data' => $complaints
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch complaints',
                'error' => $e->getMessage()
            ], 500);
        }
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

            $complaintItems = $data['complaint_items'] ?? [];
            unset($data['complaint_items']);

            $data['status'] = $data['status'] ?? 'pending';

            $data['complaint_no'] = AutoIncreament::getNextCounter(
                AutoIncreament::TYPE_COMPLAINT
            );

            $complaint = Complaint::create($data);

            ComplaintAssignment::create([
                'complaint_id' => $complaint->id,
                'assign_to'    => $data['assign_to'],
                'assigned_by'  => Auth::id(),
            ]);

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

            return response()->json([
                'status' => true,
                'message' => 'Complaint recorded successfully',
                'data' => $complaint->load([
                    'complaintItems.product.item',
                    'user',
                    'assignments:id,complaint_id,assign_to,assigned_by',
                    'assignments.assignedUser:id,name',
                    'assignments.assignedByUser:id,name'
                ])
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Complaint creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {

            $complaint = Complaint::with([
                'complaintItems.product.item',
                'user',
                'assignments:id,complaint_id,assign_to,assigned_by',
                'assignments.assignedUser:id,name',
                'assignments.assignedByUser:id,name'
            ])->findOrFail($id);

            if (!$complaint) {
                return response()->json([
                    'status' => false,
                    'message' => 'Complaint not found'
                ], 404);
            }

            $parties = Party::select('id', 'name')->get();
            $status = config('constant.complaintstatus');
            $level = config('constant.level');
            $users = User::select('id', 'name')->get();
            $paymentStatuses = config('constant.paymentstatus');
            $paymentModes = config('constant.paymentmode');

            return response()->json([
                'status' => true,
                'message' => 'Complaint fetched successfully',
                'data' => [
                    'complaint'  => $complaint,
                    'parties'  => $parties,
                    'statuses' => $status,
                    'levels'   => $level,
                    'users'    => $users,
                    'users'    => $users,
                    'paymentStatuses'    => $paymentStatuses,
                    'paymentModes'    => $paymentModes,
                ]

                // $complaint
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Complaint not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    private function _update_validation_rules()
    {
        return [
            'date' => 'sometimes|date',
            'party_id' => 'sometimes|integer',

            'complainant_mobile' => 'nullable|string|max:180',
            'contact_number' => 'sometimes|string|max:180',
            'contact_person' => 'sometimes|string|max:180',
            'remarks' => 'nullable|string',

            'level' => 'sometimes|in:hot,warm,cold',
            'is_under_warranty' => 'sometimes|boolean',

            'payment_status' => 'nullable|in:pending,received',
            'payment_mode' => 'nullable|in:cash,g_pay,bank,cheque,other',
            'amount' => 'nullable|integer|min:1',

            'assign_to' => 'sometimes|integer',

            'sale_bill_no' => 'nullable|integer',
            'is_free' => 'nullable|integer',
            'is_new_party' => 'nullable|integer',

            // ✅ ITEMS
            'complaint_items' => 'sometimes|array',
            'complaint_items.*.id' => 'sometimes|integer|exists:complaint_items,id',
            'complaint_items.*.product_id' => 'sometimes|integer|exists:products,id',
            'complaint_items.*.remarks' => 'nullable|string|max:500',
            'complaint_items.*.reading' => 'nullable|string|max:500',
        ];
    }

    public function update(Request $request, $id)
    {
        $rules = $this->_update_validation_rules();
        $messages = $this->_common_validation_messages();

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

            $complaint = Complaint::findOrFail($id);

            $complaintItems = $data['complaint_items'] ?? null;
            unset($data['complaint_items']);

            // ✅ Store old assignment
            $oldAssignTo = $complaint->assign_to;

            // ✅ Update only provided fields
            if (!empty($data)) {
                $complaint->update($data);
            }

            // ✅ Assignment history (only if sent & changed)
            if ($request->has('assign_to') && $oldAssignTo != $request->assign_to) {
                ComplaintAssignment::create([
                    'complaint_id' => $complaint->id,
                    'assign_to'    => $request->assign_to,
                    'assigned_by'  => Auth::id(),
                ]);
            }

            if (!is_null($complaintItems)) {

                $existingIds = $complaint->complaintItems()->pluck('id')->toArray();
                $submittedIds = [];

                foreach ($complaintItems as $item) {

                    if (!empty($item['id'])) {

                        $complaintItem = ComplaintItem::where('id', $item['id'])
                            ->where('complaint_id', $complaint->id)
                            ->first();

                        if ($complaintItem) {

                            $complaintItem->update([
                                'product_id' => $item['product_id'] ?? $complaintItem->product_id,
                                'remarks'    => $item['remarks'] ?? $complaintItem->remarks,
                                'reading'    => $item['reading'] ?? $complaintItem->reading,
                            ]);

                            $submittedIds[] = $complaintItem->id;
                        }
                    } else {

                        if (empty($item['product_id'])) {
                            throw new \Exception("Product ID is required for new item");
                        }

                        $newItem = ComplaintItem::create([
                            'complaint_id' => $complaint->id,
                            'product_id'   => $item['product_id'],
                            'remarks'      => $item['remarks'] ?? null,
                            'reading'      => $item['reading'] ?? null,
                        ]);

                        $submittedIds[] = $newItem->id;
                    }
                }

                $deleteIds = array_diff($existingIds, $submittedIds);

                if (!empty($deleteIds)) {
                    ComplaintItem::whereIn('id', $deleteIds)->delete();
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Complaint updated successfully',
                'data' => $complaint->load([
                    'complaintItems.product.item',
                    'user',
                    'assignments:id,complaint_id,assign_to,assigned_by',
                    'assignments.assignedUser:id,name',
                    'assignments.assignedByUser:id,name'
                ])
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Complaint update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $complaint = Complaint::findOrFail($id);

            if (!$complaint) {
                return response()->json([
                    'status' => false,
                    'message' => 'Complaint not found'
                ], 404);
            }

            $complaint->complaintItems()->delete();
            $complaint->assignments()->delete();

            $complaint->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Complaint deleted successfully'
            ]);
        } catch (\Exception $ex) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Complaint deletion failed',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function getPartyProducts($partyId)
    {
        try {

            $products = Party::findOrFail($partyId)
                ->products()
                ->select('products.id', 'products.sku')
                ->get();

            if (!$products) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Products Found For Selected Party'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Complaint list fetched successfully',
                'data' => $products
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch Products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
