<?php

namespace App\Http\Controllers\Backend;

use App\Models\Item;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;

class ItemController extends BackendController
{
    public String $routePrefix = "item";
    public $modelClass = Item::class;

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
            ["field" => "is_active", "type" => "int"],
            ["field" => "product_type", "type" => "int"],
        ]);

        $builder = $this->modelClass::where($conditions);

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

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }

    private function _get_comman_validation_rules()
    {
        return [
            "is_active" => 'required|in:0,1',
            'product_type' => 'required|in:0,1,2',
            "description" => 'nullable',
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            'name.unique' => 'Item Name is unique',
            'is_active.required' => 'Please select Active or Inactive.',
            'is_active.in'       => 'Invalid value. Only Active (1) or Inactive (0) is allowed.',
        ];
    }

    public function store(Request $request)
    {
        $rules = $this->_get_comman_validation_rules();
        $messages = $this->_get_comman_validation_messages();

        $rules = array_merge($rules, [
            'name' => [
                'required',
                "min:2",
                "max:180",
                Rule::unique($this->tableName)->where(function ($query) use ($request) {
                    $builder = $query
                        ->where('name', $request->input('name'));

                    return $builder;
                })
            ]
        ]);

        $validatedData = $request->validate($rules, $messages);
        try {

            $this->modelClass::create($validatedData);

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

        $this->setForView(compact("model", "form"));

        return $this->view("form");
    }


    public function update(Request $request, $id)
    {
        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => [
                "required",
                "min:2",
                "max:180",
                Rule::unique($this->tableName)->where(function ($query) use ($request, $id) {

                    $builder = $query->where('id',  '<>', $id)
                        ->where('name', $request->input('name'));

                    return $builder;
                })
            ]
        ]));

        try {
            $model = $this->modelClass::findOrFail($id);

            $validatedData = array_make_all_values_zero_if_null($validatedData);

            $model->fill($validatedData);
            $model->save();

            $this->saveSqlLog();

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
        $model = $this->modelClass::findOrFail($id);
        if (!$model) {
            return response()->json(['status' => 0, 'msg' => 'Record not found.']);
        }

        return $this->_destroy($model);
    }



// item create pop up function in product create page

public function storeAjax(Request $request)
{
    $request->validate([
        'name' => 'required|unique:items,name',
        'product_type' => 'required'
    ]);

    $item = \App\Models\Item::create([
        'name' => $request->name,
        'is_active' => 1,
        'product_type' => $request->product_type
    ]);

    return response()->json([
        'success' => true,
        'item' => $item
    ]);
}
}