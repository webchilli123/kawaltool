<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Backend\AutoIncreamentController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\CityController;
use App\Http\Controllers\Backend\CompanyController;
use App\Http\Controllers\Backend\ComplaintController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\DesignationController;
use App\Http\Controllers\Backend\DeveloperController;
use App\Http\Controllers\Backend\EmployeeController;
use App\Http\Controllers\Backend\ItemController;
use App\Http\Controllers\Backend\PartyController;
use App\Http\Controllers\Backend\PartyProductController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\LeadController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProformaInvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\PublicController;
use App\Http\Controllers\Backend\PurchaseBillController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SaleBillController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\SourceController;
use App\Http\Controllers\Backend\StateController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Backend\StaffTypeController;
use App\Http\Controllers\Backend\StockIssueController;
use App\Http\Controllers\Backend\WarehouseController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use App\Models\City;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route("home");
});

Route::get('/phpinfo', function () {
    phpinfo();
});

Auth::routes();

Route::get('/', [HomeController::class, 'index']);
Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('/theme', [HomeController::class, 'theme']);
Route::get('/developer-components', [HomeController::class, 'developer_components']);
Route::get('/test', [HomeController::class, 'test']);



Route::group(['middleware' => ['auth']], function () {

    $name = "dashboard";
    Route::get($name, [DashboardController::class, 'index'])->name($name);

    Route::get('dashboard-ajax_admin_role_counters/{date_type}', [DashboardController::class, "ajax_admin_role_counters"])->name('dashboard.ajax_admin_role_counters');
    Route::get('dashboard-ajax_sales_manager_role_counters/{date_type}', [DashboardController::class, "ajax_sales_manager_role_counters"])->name('dashboard.ajax_sales_manager_role_counters');


    $route_prefix = "user";
    $controllerClass = UserController::class;
    Route::resource($route_prefix, $controllerClass)->except("show");
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {

        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);

        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);

        Route::get("my-profile", [$controllerClass, 'my_profile'])->name("my.profile");

        Route::any("change-password", [$controllerClass, 'change_password'])->name("change.password");
    });

    $route_prefix = "role";
    $controllerClass = RoleController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {

        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);

        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });

    $route_prefix = "state";
    $controllerClass = StateController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {});

    // cities
    $controller_prefix = "cities";
    $controllerClass = CityController::class;
    Route::resource($controller_prefix, $controllerClass)->except(['show']);
    $name = "ajax_get";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_list";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);

    Route::get('cities/options', function () {
        $cities = App\Models\City::where('state_id', request()->state_id)->pluck('name', 'id');
        return view('cities.options', compact('cities'));
    })->name('cities.options');

    // Department
    $route_prefix = "department";
    $controllerClass = DepartmentController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {

        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);

        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });

       $controller_prefix = "reports";
    $controllerClass = ReportController::class;
    $name = "ledger";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // item-stock
    Route::get('item-stock', [ReportController::class, 'item_stock'])->name('reports.itemStock');
    Route::get('/item-stock-detail/{item_id}', [ReportController::class, 'itemStockDetail'])->name('item.stock.detail');


    Route::get('reports-inventory', [ReportController::class, 'current_stock'])->name('reports.inventory');
    Route::get('reports-stock-movement', [ReportController::class, 'stock_movement'])->name('reports.stock_movement');
    Route::get('party-products', [ReportController::class, 'party_products'])->name('reports.party_products');
    Route::get('reports-joborder', [ReportController::class, 'job_order'])->name('reports.joborder');
    Route::get('reports-joborderreceive', [ReportController::class, 'job_order_receive'])->name('reports.joborderreceive');

    // product
    $controller_prefix = "product";
    $controllerClass = ProductController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "print";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);

    // Complaint    
    Route::resource('complaint', ComplaintController::class);
    $controller_prefix = "complaint";
    $controllerClass = ComplaintController::class;
    $name = "ajax_get";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    Route::get('/get-customer-details/{id}', [ComplaintController::class, 'getCustomerDetails']);
    Route::get('/get-party-products/{party}', [ComplaintController::class, 'getPartyProducts']);

    // party
    $controller_prefix = "party";
    $controllerClass = PartyController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // party-products
    $controller_prefix = "party-product";
    $controllerClass = PartyProductController::class;
    Route::resource($controller_prefix, $controllerClass);
    
    // proforma-invoice
    $controller_prefix = "proforma-invoice";
    $controllerClass = ProformaInvoiceController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "pdf";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "print";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_items";
    Route::get($controller_prefix . "-" . $name . "/{party_id}/{purchase_order_ids}/{id?}/", [$controllerClass, $name])->name($controller_prefix . "." . $name);

    $controller_prefix = "purchase-bill";
    $controllerClass = PurchaseBillController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "pdf";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_items";
    Route::get($controller_prefix . "-" . $name . "/{party_id}/{purchase_order_ids}/{id?}/", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    // $name = "return_items";
    // Route::any($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    
    // salebills
    $controller_prefix = "sale-bill";
    $controllerClass = SaleBillController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "pdf";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "print";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "ajax_get_items";
    Route::get($controller_prefix . "-" . $name . "/{party_id}/{purchase_order_ids}/{id?}/", [$controllerClass, $name])->name($controller_prefix . "." . $name);

    // item
    $controller_prefix = "item";
    $controllerClass = ItemController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
 
    // brand
    $controller_prefix = "brand";
    $controllerClass = BrandController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);

    // Lead 
    $controller_prefix = "lead";
    $controllerClass = LeadController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    Route::post('/lead/import', [LeadController::class, 'import'])->name('lead.import');
    Route::post('/lead/update-missed', [LeadController::class, 'updateMissed'])->name('lead.updateMissed');

    // stock issue
    $controllerClass = StockIssueController::class;
    Route::resource("stock-issue", $controllerClass);

    // Source
    $controller_prefix = "source";
    $controllerClass = SourceController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);

    // warehouse
    $controller_prefix = "warehouse";
    $controllerClass = WarehouseController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "csv";
    Route::get($controller_prefix . "-" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);

    // auto-increaments
    $controller_prefix = "auto-increaments";
    $controllerClass = AutoIncreamentController::class;
    Route::resource($controller_prefix, $controllerClass);

    // Designation
    $route_prefix = "designation";
    $controllerClass = DesignationController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {

        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);

        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });

    // Employee
    $route_prefix = "employee";
    $controllerClass = EmployeeController::class;
    Route::resource($route_prefix, $controllerClass);
    Route::group(['prefix' => $route_prefix, 'as' => $route_prefix . '.'], function () use ($controllerClass) {

        $name = "activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);

        $name = "de_activate";
        Route::get($name . "/{id}", [$controllerClass, $name])->name($name);
    });

    // Staff Type

    $controller_prefix = "staff-type";
    $controllerClass = StaffTypeController::class;
    Route::resource($controller_prefix, $controllerClass);
    $name = "ajax_get";
    Route::get($controller_prefix . "_" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);
    $name = "de_activate";
    Route::get($controller_prefix . "-" . $name . "/{id}", [$controllerClass, $name])->name($controller_prefix . "." . $name);

    Route::group(['prefix' => 'permissions', 'as' => 'permissions.'], function () {

        $name = "index";
        Route::get($name, [PermissionController::class, $name])->name($name);

        $name = "assign";
        Route::any($name, [PermissionController::class, $name])->name($name);

        $name = "assign_to_many";
        Route::any($name, [PermissionController::class, $name])->name($name);

        $name = "ajax_get_permissions";
        Route::get("$name/{id}", [PermissionController::class, $name])->name($name);

        $name = "ajax_delete";
        Route::post($name, [PermissionController::class, $name])->name($name);

        $name = "ajax_request_access";
        Route::any($name . "/{route_name}", [PermissionController::class, $name])->name($name);
    });

    Route::group(['prefix' => 'logs', 'as' => 'logs.'], function () {
        $name = "email";
        Route::get($name, [DeveloperController::class, $name])->name($name);
    });

    Route::group(['prefix' => "developer", 'as' => "developer."], function () {

        $name = "sql_log";
        Route::get($name, [DeveloperController::class, $name])->name($name);

        $name = "laravel_routes_index";
        Route::get($name, [DeveloperController::class, $name])->name($name);
    });

    $controller_prefix = "settings";
    $controllerClass = SettingController::class;
    $name = "general";
    Route::any($controller_prefix . "/" . $name, [$controllerClass, $name])->name($controller_prefix . "." . $name);

    Route::get('/phpinfo', function () {
        phpinfo();
    });

    Route::resource('companies', CompanyController::class)->only(['index', 'update']);
});

Route::get('verify-otp/{email}', [UserController::class, 'otp_verified_view'])->name('verify.otp')->middleware('auth');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// show cities based on selected state //
Route::get('/cities/{stateId}', function ($stateId) {

    $cities = City::where('state_id', $stateId)->pluck('name', 'id');

    return response()->json(['cities' => $cities]);
});
// // show cities based on selected state //

// // show cities based on Saved Records in edit case //
Route::get('/cityname/{cityId}', function ($cityId) {

    $cities = City::where('id', $cityId)->pluck('name', 'id');

    return response()->json(['cities' => $cities]);
});
// show cities based on Saved Records in edit case //
Route::group(['prefix' => 'public', 'as' => 'public.'], function () {
    Route::post('ajax_upload', [PublicController::class, 'ajax_upload']);
    Route::post('ajax_upload_base64', [PublicController::class, 'ajax_upload_base64']);
});


Route::get('/storage-test', function () {
    Storage::disk('public')->put('test.txt', 'OK');
    return Storage::url('test.txt');
});

Route::get('complaints/export-csv', [ComplaintController::class, 'exportCsv'])->name('complaints.exportCsv');

// get-lead-ajax

Route::get('get-lead', [LeadController::class, 'getLead'])->name('get-lead');

// item create pop up in product create ajax

Route::post('/items/store-ajax', [ItemController::class, 'storeAjax'])->name('items.store.ajax');

// brand create pop up in product create ajax

Route:: post('/brands/store-ajax', [BrandController::class, 'storeAjax'])->name('brands.store.ajax');

// warehouse create pop up in product create ajax

Route::post('/warehouses/store-ajax', [WarehouseController::class, 'storeAjax'])->name('warehouses.store.ajax');