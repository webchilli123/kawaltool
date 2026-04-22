<?php

namespace App\Http\Controllers\Backend;

use App\Models\Party;
use App\Models\City;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Requests\PartyRequest;
use App\Models\Product;
use Exception;
use App\Models\State;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

class PartyController extends BackendController
{
    public String $routePrefix = "party";

    public $modelClass = Party::class;

    public function __construct()
    {
        parent::__construct();
        $this->viewPrefix = "parties";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());

        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "city"
        ]), Route::currentRouteName());

        $this->_set_common_form_list(null);
        $this->setForView(compact("records"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ["field" => "name", "type" => "string", "view_field" => "name"],
            ["field" => "is_active", "type" => "int", "view_field" => "is_active"],
            ["field" => "city_id", "type" => "int", "view_field" => "city_id"],
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

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $state_list = State::getList("id", "name");

        $this->_set_common_form_list(null);
        $this->setForView(compact("model", 'form', 'state_list'));

        return $this->view("form");
    }

    private function _set_common_form_list($model)
    {
        $cities = City::getListCache('id', 'name');

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->item_id) {
            $conditions["or_id"] = $model->item_id;
        }

        $users = User::getListCache();

        $this->setForView(compact("cities", "users"));
    }

    private function _get_comman_validation_rules()
    {
        return [
            'address' => 'nullable|string|max:200',
            'gstin' => 'nullable|string|max:50',
            'state_id' => 'nullable|integer|exists:states,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'phone'  => 'nullable|digits:10',
            'mobile' => 'required|digits:10',
            'email' => 'nullable|email|max:100',
            'fax' => 'nullable|string|max:50',
            'url' => 'nullable|url|max:50',
            'tin_number' => 'nullable|string|max:50',
            "is_supplier" => "",
            "is_customer" => "",
            "is_active" => "nullable|in:0,1",
        ];
    }

    private function _get_comman_validation_messages()
    {
        return [
            // 'name.unique' => 'Item Name is unique with in category and specification',
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
            ],
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

        $state_list = State::getList("id", "name");

        $this->_set_common_form_list($model);

        $this->setForView(compact("model", "form", "state_list"));

        return $this->view("form");
    }
    
    public function update(Request $request, $id)
    {
        $rules = $this->_get_comman_validation_rules();

        $validatedData = $request->validate(array_merge($rules, [
            'name' => [
                'required',
                'min:2',
                'max:180',
                Rule::unique($this->tableName)->where(function ($query) use ($request, $id) {
                    return $query
                        ->where('id', '<>', $id)
                        ->where('name', $request->input('name'));
                }),
            ],
        ]));

        try {
            $model = $this->modelClass::findOrFail($id);

            $model->fill($validatedData);
            $model->save();

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', 'Record updated successfully');
        } catch (Exception $ex) {
            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Party  $party
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

    public function ajax_get($id)
    {
        $response = ["status" => 1];
        try {
            $model = $this->modelClass::with("city.state")->findOrFail($id);

            $response['data'] = $model->toArray();
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['msg'] = $ex->getMessage();
        }

        return $this->responseJson($response);
    }
}
