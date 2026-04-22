<?php

namespace App\Http\Controllers\Backend;

use App\Models\PartyProduct;
use App\Models\Party;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PartyProductController extends BackendController
{
    public String $routePrefix = "party-product";

    public $modelClass = PartyProduct::class;

    // public function index()
    // {
    //     $conditions = $this->_get_conditions(Route::currentRouteName());

    //     $query = Party::where($conditions)
    //     ->whereHas('partyProducts')
    //     ->with([
    //         'partyProducts.product.item'
    //     ])
    //     ->orderBy('name', 'asc');

    //     $records = $this->getPaginagteRecords(
    //         $query,
    //         Route::currentRouteName()
    //     );

    //     $partyList = Party::getList("id", "name", $conditions);

    //     $this->setForView(compact('records', 'partyList'));

    //     return $this->viewIndex(__FUNCTION__);
    // }

    // private function _get_conditions($cahe_key)
    // {
    //     $conditions = $this->getConditions($cahe_key, [
    //         ["field" => "party_id", "type" => "int", "view_field" => "party_id"],
    //     ]);

    //     return $conditions;
    // }

    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $query = Party::query();

        // ✅ APPLY CONDITIONS PROPERLY
        foreach ($conditions as $condition) {
            if (count($condition) === 3) {
                [$field, $operator, $value] = $condition;

                if (strtoupper($operator) === 'IN') {
                    $query->whereIn($field, explode(',', $value));
                } else {
                    $query->where($field, $operator, $value);
                }
            }
        }

        $query->whereHas('partyProducts')
            ->with(['partyProducts.product.item'])
            ->orderBy('name', 'asc');

        $records = $this->getPaginagteRecords(
            $query,
            Route::currentRouteName()
        );

        $partyList = Party::getList("id", "name", ['is_active' => 1]);

        $this->setForView(compact('records', 'partyList'));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        return $this->getConditions($cahe_key, [
            [
                "field" => "id",
                "type" => "int",
                "view_field" => "party_id",
            ],
        ]);
    }

    public function create()
    {
        $model = new $this->modelClass();

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $itemList = Product::getList(
            "id",
            "display_name",
            ['product_type' => 1]
        );

        $this->_set_list_for_form($model);

        $this->setForView(compact("model", 'form', 'itemList'));

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

        $this->setForView(compact('partyList'));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'party_id' => ['required'],

            'party_products' => ['required', 'array'],
            'party_products.*.product_id' => ['required'],
            'party_products.*.start_date' => ['required', 'date'],
            'party_products.*.end_date' => ['required', 'date', 'after_or_equal:party_products.*.start_date'],
            'party_products.*.remarks' => ['nullable', 'string'],
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            'party_id.required' => 'Party is required',

            'party_products.*.product_id.required' => 'Item is required',
            'party_products.*.start_date.required' => 'Start date is required',
            'party_products.*.start_date.date' => 'Invalid start date',
            'party_products.*.end_date.date' => 'Invalid end date',
        ];
    }

    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();
        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $partyId = $validatedData['party_id'];

            $partyProducts = $validatedData['party_products'];

            foreach ($partyProducts as $row) {

                $exists = PartyProduct::where('party_id', $partyId)
                    ->where('product_id', $row['product_id'])
                    ->exists();

                if ($exists) {
                    throw_exception('This product already exists for selected party');
                }

                PartyProduct::create([
                    'party_id'   => $partyId,
                    'product_id' => $row['product_id'],
                    'start_date' => $row['start_date'],
                    'end_date'   => $row['end_date'] ?? null,
                    'remarks'    => $row['remarks'] ?? null,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Party products saved successfully');
        } catch (Exception $ex) {
            DB::rollBack();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function edit($partyId)
    {
        $model = Party::with('partyProducts')->findOrFail($partyId);

        $model->party_id = $partyId;

        $form = [
            'url' => route($this->routePrefix . '.update', $partyId),
            'method' => 'PUT',
        ];

        $party_products = $model->partyProducts->map(function ($pp) {
            return [
                'id'         => $pp->id,
                'product_id' => $pp->product_id,
                'start_date' => $pp->start_date,
                'end_date'  => $pp->end_date,
                'remarks'   => $pp->remarks,
            ];
        })->toArray();

        $itemList = Product::getList(
            "id",
            "display_name",
            ['product_type' => 1]
        );

        $this->_set_list_for_form($model);

        $this->setForView(compact(
            'model',
            'form',
            'party_products',
            'itemList'
        ));

        return $this->view("form");
    }

    public function update(Request $request, $partyId)
    {
        $rules = $this->_get_comman_validation_rules();
        $messages = $this->_get_comman_validation_messages();

        $validatedData = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            Party::findOrFail($partyId);

            $productIds = collect($validatedData['party_products'])
                ->pluck('product_id')
                ->toArray();

            if (count($productIds) !== count(array_unique($productIds))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'party_products' => 'Same product cannot be added multiple times'
                ]);
            }

            PartyProduct::where('party_id', $partyId)->delete();

            foreach ($validatedData['party_products'] as $row) {
                PartyProduct::create([
                    'party_id'   => $partyId,
                    'product_id' => $row['product_id'],
                    'start_date' => $row['start_date'],
                    'end_date'   => $row['end_date'] ?? null,
                    'remarks'    => $row['remarks'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', 'Party products updated successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);
        return $this->_destroy($model);
    }
}
