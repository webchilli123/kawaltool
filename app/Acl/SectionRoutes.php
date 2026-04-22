<?php

namespace App\Acl;

class SectionRoutes
{
    /**
     * list of routes which are allowed for any login user
     */
    public const ALLOW_ROUTES_FOR_ANY_LOGIN_USER = [
        "dashboard",
        "user.my-profile",
        "user.change-password"
    ];

    /**
     * list of routes for system admin role
     */
    public const ALLOW_ROUTES_FOR_SYSTEM_ADMIN = [
        'permissions.index',
        'permissions.assign',
        'permissions.assign_to_many',
        'permissions.ajax_delete',
        'permissions.ajax_get_permissions',
    ];

    /**
     * function to retrive all sections
     */
    public static function get()
    {
        $sections = [];

        $sections["Auto increaments"] = self::auto_increaments();
        $sections["Home"] = self::home();
        $sections["Department"] = self::department();
        $sections["Complaint"] = self::complaint();
        $sections["Designation"] = self::designation();
        $sections["Performa Invoice"] = self::proformainvoice();
        $sections["Employee"] = self::employee();
        $sections["Companies"] = self::companies();
        $sections["Cities"] = self::cities();
        $sections["Staff-type"] = self::stafftypes();
        $sections["Setting"] = self::settings();
        $sections["User"] = self::user();
        $sections["Role"] = self::role();
        $sections["Item"] = self::item();
        $sections["Product"] = self::product();
        $sections["Purchase Bill"] = self::purchasebill();
        $sections["Sale Bill"] = self::salebill();
        $sections["Parties"] = self::parties();
        $sections["Party Product"] = self::partyproduct();
        $sections["Lead"] = self::lead();
        $sections["Source"] = self::source();
        $sections["Stock Issue"] = self::stockissue();
        $sections["Lead"] = self::lead();
        $sections["Source"] = self::source();
        $sections["Brand"] = self::brand();
        $sections["Warehouse"] = self::warehouse();
        $sections["Developer"] = self::developer();
        $sections["Report"] = self::report();

        return $sections;
    }

    protected static function commonRoutes($routePrefix)
    {
        $routes =  [
            "Summary" => [$routePrefix . ".index"],
            "Add" => [
                $routePrefix . ".create",
                $routePrefix . ".store"
            ],
            "Edit" => [
                $routePrefix . ".edit",
                $routePrefix . ".update"
            ],
            "Delete" => [$routePrefix . ".destroy"]
        ];

        return $routes;
    }

    private static function home()
    {
        $routes =  [
            "Dashbaord" => ["dashboard"],
        ];

        return $routes;
    }

    private static function designation()
    {
        $routePrefix = "designation";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function department()
    {
        $routePrefix = "department";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function complaint()
    {
        $routePrefix = "complaint";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function employee()
    {
        $routePrefix = "employee";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function companies()
    {
        $routePrefix = "companies";

        $routes = [];

        $routes["Summary"] = [$routePrefix . ".index"];
        $routes["Update"] = [$routePrefix . ".update"];

        return $routes;
    }

    private static function cities()
    {
        $routePrefix = "cities";

        $routes = self::commonRoutes($routePrefix);
        $routes["Option"] = [$routePrefix . ".options"];


        return $routes;
    }

    private static function stafftypes()
    {
        $routePrefix = "staff-type";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function settings()
    {
        $routePrefix = "settings";

        $routes = [];

        $routes["General"] = [$routePrefix . ".general"];

        return $routes;
    }

     private static function auto_increaments()
    {
        $routePrefix = "auto-increaments";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function user()
    {
        $routePrefix = "user";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function role()
    {
        $routePrefix = "role";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function product()
    {
        $routePrefix = "product";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

      private static function purchasebill()
    {
        $routePrefix = "purchase-bill";

        $routes = self::commonRoutes($routePrefix);
        $routes["Csv"] = [$routePrefix . ".csv"];
        $routes["PDF"] = [$routePrefix . ".pdf"];

        return $routes;
    }
   
    private static function salebill()
    {
        $routePrefix = "sale-bill";

        $routes = self::commonRoutes($routePrefix);
        $routes["Csv"] = [$routePrefix . ".csv"];
        $routes["PDF"] = [$routePrefix . ".pdf"];

        return $routes;
    }

    private static function proformainvoice()
    {
        $routePrefix = "proforma-invoice";

        $routes = self::commonRoutes($routePrefix);
        $routes["Csv"] = [$routePrefix . ".csv"];
        $routes["PDF"] = [$routePrefix . ".pdf"];

        return $routes;
    }

     private static function parties()
    {
        $routePrefix = "party";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function partyproduct()
    {
        $routePrefix = "party-product";
     
        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function lead()
    {
        $routePrefix = "lead";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function source()
    {
        $routePrefix = "source";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function stockissue()
    {
        $routePrefix = "stock-issue";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function item()
    {
        $routePrefix = "item";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }
  
    private static function brand()
    {
        $routePrefix = "brand";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function warehouse()
    {
        $routePrefix = "warehouse";

        $routes = self::commonRoutes($routePrefix);

        return $routes;
    }

    private static function developer()
    {
        $routePrefix = "developer";

        $routes = [];

        $routes["SQL Log Summary"] = [$routePrefix . ".sql_log"];
        $routes["Laravel Routes Summary"] = [$routePrefix . ".laravel_routes_index"];

        return $routes;
    }

     private static function report()
    {
        $routes = [];

        $routes["Inventory"] = [
            "reports.inventory"
        ];

        $routes["Stock Movement"] = [
            "reports.stock_movement"
        ];

        $routes["Party Product"] = [
            "reports.party_products"
        ];

        return $routes;
    }
}
