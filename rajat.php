Route::middleware(['auth', 'role.permission'])->group(function () {

});

<!-- register middleware -->
$middleware->alias([
            'role.permission' => \App\Http\Middleware\RolePermission::class,
        ]);