<?php

namespace App\Helpers;

use App\Acl\AccessControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Menu
{
    private static $menus = [];
    private static $current_route_name = "";

    public static function setCurrentRouteName(String $current_route_name)
    {
        self::$current_route_name = strtolower(trim($current_route_name));
        BaseMenu::setCurrentRouteName(self::$current_route_name);
    }

    public static function get($auth_user_id)
    {
        self::$menus = [];

        // if ( Config::get('app.will_menu_cache') )
        // {
            $acccessControl = AccessControl::init();

            $cache_key = $acccessControl->getMenuCacheKey($auth_user_id);

            self::$menus = Cache::get($cache_key);

            if (!empty(self::$menus))
            {
                return self::checkForActive(self::$menus);;
            }
        // }

        self::$menus[] = (new HomeMenu)->get();
        self::$menus[] = (new MemberMenu)->get();
        self::$menus[] = (new LeadMenu)->get();
        self::$menus[] = (new ComplaintMenu)->get();
        self::$menus[] = (new EmployeeMenu)->get();
        self::$menus[] = (new ProformaInvoice)->get();
        self::$menus[] = (new SaleAndPurchaseMenu)->get();
        self::$menus[] = (new Employee)->get();
        self::$menus[] = (new GeneralMenu)->get();
        self::$menus[] = (new StoreMenu)->get();
        self::$menus[] = (new ReportMenu)->get();
        self::$menus[] = (new SystemMenu)->get();
        self::$menus[] = (new LogMenu)->get();
        self::$menus[] = (new DeveloperMenu)->get();

        //d(self::$menus); exit;

        if (isset($cache_key)) {
            self::_filterMenuForUser();

            Cache::put($cache_key, self::$menus, laravel_constant("cache_time.menu"));
        }

        return self::checkForActive(self::$menus);;
    }

    public static function getBreadcums($menus)
    {
        $breadcums = self::findBreadCum($menus);

        return $breadcums;
    }

    public static function checkForActive($menus)
    {
        foreach ($menus as $k => $menu) {
            if (isset($menu['route_name'])) {
                $menus[$k]['is_active'] = self::isActiveLink($menu);
            } else if (isset($menu['links'])) {
                $menus[$k]['links'] = self::checkForActive($menu['links']);
            }
        }

        return $menus;
    }

    public static function isActiveLink(array $link)
    {
        if ($link['route_name'] == self::$current_route_name) {
            return true;
        } else if (isset($link["related_links"]) && is_array($link["related_links"])) {
            foreach ($link["related_links"] as $related_link) {
                $is_active = self::isActiveLink($related_link);

                if ($is_active) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function findBreadCum(array $menus, array $parents = [])
    {
        foreach ($menus as $menu) {
            $aray_helper = new ArrayHelper($menu);
            $parent = $aray_helper->getOnlyWhichHaveKeys(["title", "route_name"]);

            if (isset($menu['links'])) {
                $temp = $parents;
                $temp[] = $parent;
                $ret = self::findBreadCum($menu['links'], $temp);

                if ($ret) {
                    return $ret;
                }
            }

            if (isset($menu['related_links'])) {
                foreach ($menu['related_links'] as $related_link) {
                    if (isset($related_link['route_name'])) {
                        if ($related_link['route_name'] == self::$current_route_name) {
                            $parents[] = $parent;

                            $aray_helper = new ArrayHelper($related_link);
                            $temp = $aray_helper->getOnlyWhichHaveKeys(["title", "route_name"]);

                            $parents[] = $temp;

                            return $parents;
                        }
                    }
                }
            }

            if (isset($menu['route_name'])) {
                if ($menu['route_name'] == self::$current_route_name) {
                    $parents[] = $parent;
                    return $parents;
                }
            }
        }

        return [];
    }

    public static function getList(array $menus, String $prefix = "")
    {
        $list = [];
        foreach ($menus as $menu) {
            if (isset($menu['route_name'])) {
                $list[] = [
                    "title" => $prefix . $menu['title'],
                    "url" => route($menu['route_name'])
                ];
            } else if (isset($menu["links"])) {
                $list = array_merge($list, self::getList($menu["links"], $prefix . $menu['title'] . " -> "));
            }
        }

        return $list;
    }

    private static function _filterMenuForUser()
    {
        $role_id_list = [];
        foreach (Auth::user()->userRole->toArray() as $user_role) {
            $role_id_list[] = $user_role['role_id'];
        }

        $acccessControl = AccessControl::init();
        $allowed_route_name_list = $acccessControl->getListOfAllowedRouteNames($role_id_list);
        //d($allowed_route_name_list);

        foreach (self::$menus as $k => $sub_menu) {
            if (isset($sub_menu['links'])) {
                foreach ($sub_menu['links'] as $k2 => $sub_menu2) {
                    if (isset($sub_menu2['links'])) {
                        foreach ($sub_menu2['links'] as $k3 => $sub_menu3) {
                            if (isset($sub_menu3['route_name'])) {
                                if (!in_array($sub_menu3['route_name'], $allowed_route_name_list)) {
                                    unset($sub_menu2['links'][$k3]);
                                }
                            }
                        }

                        if (empty($sub_menu2['links'])) {
                            unset($sub_menu['links'][$k2]);
                        } else {
                            $sub_menu['links'][$k2] = $sub_menu2;
                        }
                    } else if (isset($sub_menu2['route_name'])) {
                        if (!in_array($sub_menu2['route_name'], $allowed_route_name_list)) {
                            unset($sub_menu['links'][$k2]);
                        }
                    }
                }
            } else if (isset($sub_menu['route_name'])) {
                if (!in_array($sub_menu['route_name'], $allowed_route_name_list)) {
                    unset(self::$menus[$k]);
                }
            }

            if (empty($sub_menu['links'])) {
                unset(self::$menus[$k]);
            } else {
                self::$menus[$k] = $sub_menu;
            }
        }
    }
}

class BaseMenu
{
    const ICON_MENU_ROOT = 'fas fa-layer-group';
    const ICON_MENU_ROOT_CHILD = 'fas fa-cube';
    const ICON_MENU_SUMMARY = 'fas fa-table';
    const ICON_MENU_CREATE = 'fas fa-plus-circle';
    const ICON_REPORT = 'fas fa-file-text';

    private static $current_route_name = "";

    const LINK_TYPE_SUMMARY = "summary";
    const LINK_TYPE_ADD = "add";
    const LINK_TYPE_EDIT = "edit";
    const LINK_TYPE_DELETE = "add";

    public static function setCurrentRouteName(String $current_route_name)
    {
        self::$current_route_name = strtolower(trim($current_route_name));
    }

    public static function get(): array
    {
        return [];
    }

    public static function getModule(String $title, $icon, array $links = [])
    {
        if (!$icon) {
            $icon = self::ICON_MENU_ROOT;
        }

        return [
            'title' => $title,
            'icon' => $icon,
            "links" => $links
        ];
    }


    public static function getLink(String $route_name, $title, String $icon, array $related_links = [], String $link_type = "")
    {
        if (!$title) {
            $title = self::getLinkTitleFromRouteName($route_name, $link_type);
        }

        $link = [
            "title" => $title,
            "icon" => "child-menu-icon " . $icon,
            "route_name" => trim($route_name),
            "related_links" => $related_links
        ];

        $link['is_active'] = self::isActiveLink($link);

        return $link;
    }

    public static function addRelatedLink(String $route_name, String $title, array $related_links = [])
    {
        $link = [
            "title" => $title,
            "route_name" => trim($route_name),
            "related_links" => $related_links
        ];

        $link['is_active'] = self::isActiveLink($link);

        return $link;
    }

    public static function isActiveLink(array $link)
    {
        if ($link['route_name'] == self::$current_route_name) {
            return true;
        } else if (isset($link["related_links"]) && is_array($link["related_links"])) {
            foreach ($link["related_links"] as $related_link) {
                $is_active = self::isActiveLink($related_link);

                if ($is_active) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getControllerDefaultLinks(String $routePrefix, String $title, String $icon = "")
    {
        $links = [
            "title" => $title,
            "icon" => $icon,
            "links" => [
                self::getLink($routePrefix . ".index", "Summary", self::ICON_MENU_SUMMARY, [
                    self::addRelatedLink($routePrefix . ".edit", "Edit"),
                    self::addRelatedLink($routePrefix . ".view", "View"),
                ]),
                self::getLink($routePrefix . ".create", "Create", self::ICON_MENU_CREATE),
            ],
        ];

        return $links;
    }

    public static function getLinkTitleFromRouteName(String $route_name, String $link_type)
    {
        $title = $route_name;

        $arr = explode(".", $route_name);

        if (count($arr) > 1) {
            $title = end($arr);
        }

        $title = str_replace("_", " ", $title);

        switch ($link_type) {
            case self::LINK_TYPE_SUMMARY:
                $title = str_replace("index", "summary", $title);
                break;
        }

        $title = ucwords($title);

        return $title;
    }
}

class HomeMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::getLink("dashboard", "Dashboard", 'fa-solid fa-gauge');

        return self::getModule("Home", 'fas fa-home', $links);
    }
}

class SystemMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::permission();
        $links[] = self::settings();
        $links[] = self::ledger_category();
        $links[] = self::auto_increaments();

        return self::getModule("System Manager", 'fas fa-cogs', $links);
    }

    public static function permission()
    {
        $routePrefix = "permissions";

        $links = [
            self::getLink($routePrefix . ".index", "Summary", self::ICON_MENU_SUMMARY),
            self::getLink($routePrefix . ".assign", "Assign", 'fa-solid fa-gear'),
            // self::getLink($routePrefix . ".assign_to_many", "Assign To Many", 'bx bx-grid-alt'),
        ];

        return self::getModule("Permissions", self::ICON_MENU_ROOT_CHILD, $links);
    }

    private static function settings()
    {
        $routePrefix = "settings";

        $links = [
            self::getLink($routePrefix . ".general", "General", self::ICON_MENU_CREATE),
        ];

        return self::getModule("Settings", self::ICON_MENU_ROOT_CHILD, $links);
    }
    private static function ledger_category()
    {
        $routePrefix = "ledger-category";

        $links = [
            "title" => "Ledger Category",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink($routePrefix . ".index", "Summary", self::ICON_MENU_SUMMARY, [
                    self::addRelatedLink($routePrefix . ".edit", "Edit"),
                ]),
            ],
        ];

        return $links;
    }
    private static function auto_increaments()
    {
        $routePrefix = "auto-increaments";

        $links = self::getControllerDefaultLinks($routePrefix, "Auto Increament", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class LogMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $routePrefix = "logs";

        $links = [
            // self::getLink($routePrefix . ".sql", "SQL", self::ICON_MENU_SUMMARY)
        ];

        return self::getModule("Logs", null, $links);
    }
}


class DeveloperMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $routePrefix = "developer";

        $links[] = self::getLink($routePrefix . ".sql_log", null, self::ICON_MENU_SUMMARY, [], self::LINK_TYPE_SUMMARY);
        $links[] = self::getLink($routePrefix . ".laravel_routes_index", null, self::ICON_MENU_SUMMARY, [], self::LINK_TYPE_SUMMARY);

        return self::getModule("Developer", null, $links);
    }
}

class MemberMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::role();
        $links[] = self::user();

        return self::getModule("Member Manager", 'fas fa-users', $links);
    }

    private static function user()
    {
        $routePrefix = "user";

        $links = self::getControllerDefaultLinks($routePrefix, "Users", "fas fa-users");

        return $links;
    }

    private static function role()
    {
        $routePrefix = "role";

        $links = self::getControllerDefaultLinks($routePrefix, "Roles", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class LeadMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::lead();

        return self::getModule("Lead", 'fas fa-users', $links);
    }

    private static function lead()
    {
        $routePrefix = "lead";

        $links = self::getControllerDefaultLinks($routePrefix, "Lead", "fas fa-users");

        return $links;
    }

}

class ComplaintMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::complaint();

        return self::getModule("Complaint", 'fas fa-users', $links);
    }

    private static function complaint()
    {
        $routePrefix = "complaint";

        $links = self::getControllerDefaultLinks($routePrefix, "Complaint", "fas fa-users");

        return $links;
    }
}


class EmployeeMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::department();
        $links[] = self::designation();

        return self::getModule("Employee Manager", 'fas fa-users', $links);
    }

    private static function department()
    {
        $routePrefix = "department";

        $links = self::getControllerDefaultLinks($routePrefix, "Department", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function designation()
    {
        $routePrefix = "designation";

        $links = self::getControllerDefaultLinks($routePrefix, "Designation", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class ProformaInvoice extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::proformaInvoice();

        return self::getModule("Proforma Invoice", 'fas fa-users', $links);
    }

    private static function proformaInvoice()
    {
        $routePrefix = "proforma-invoice";

        $links = self::getControllerDefaultLinks($routePrefix, "Proforma Invoice", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class ReportMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::inventory();
        $links[] = self::inventoryMovement();
        $links[] = self::customerProducts();
        return self::getModule("Reports", null, $links);
    }

    private static function inventory()
    {
        return [
            "title" => "Inventory",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink("reports.inventory", "Current Stock", self::ICON_REPORT),
            ],
        ];
    }
    
    private static function inventoryMovement()
    {
        return [
            "title" => "Stock Movement",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink("reports.stock_movement", "Stock Movement", self::ICON_REPORT),
            ],
        ];
    }
  
    private static function customerProducts()
    {
        return [
            "title" => "Party Products",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink("reports.party_products", "Party Products", self::ICON_REPORT),
            ],
        ];
    }

}

class GeneralMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::getLink("companies.index", "Company", 'fas fa-users');
        $links[] = self::state();
        $links[] = self::brand();
        $links[] = self::item();
        $links[] = self::product();
        $links[] = self::source();
        $links[] = self::party();
        $links[] = self::partyProduct();
        $links[] = self::warehouse();
        $links[] = self::cities();
        $links[] = self::staffType();

        return self::getModule("General Menu", 'fas fa-users', $links);
    }

    private static function state()
    {
        $routePrefix = "state";

        $links = self::getControllerDefaultLinks($routePrefix, "State", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function cities()
    {
        $routePrefix = "cities";

        $links = self::getControllerDefaultLinks($routePrefix, "City", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    
    private static function brand()
    {
        $routePrefix = "brand";

        $links = self::getControllerDefaultLinks($routePrefix, "Brand", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    
    private static function item()
    {
        $routePrefix = "item";

        $links = self::getControllerDefaultLinks($routePrefix, "Item", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    private static function product()
    {
        $routePrefix = "product";

        $links = self::getControllerDefaultLinks($routePrefix, "Product", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    private static function source()
    {
        $routePrefix = "source";

        $links = self::getControllerDefaultLinks($routePrefix, "Source", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

     private static function party()
    {
        $routePrefix = "party";

        $links = self::getControllerDefaultLinks($routePrefix, "Party", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    private static function partyProduct()
    {
        $routePrefix = "party-product";

        $links = self::getControllerDefaultLinks($routePrefix, "Party Products", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    
    private static function warehouse()
    {
        $routePrefix = "warehouse";

        $links = self::getControllerDefaultLinks($routePrefix, "Warehouse", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    private static function staffType()
    {
        $routePrefix = "staff-type";

        $links = self::getControllerDefaultLinks($routePrefix, "Staff Type", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

}

class StoreMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::stockIssue();

        return self::getModule("Store", 'fas fa-users', $links);
    }

    public static function stockIssue(): array
    {
        $routePrefix = "stock-issue";

        $links = self::getControllerDefaultLinks($routePrefix, "Against Complaint", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class SaleAndPurchaseMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::purchase_bill();
        $links[] = self::sale_bill();

        return self::getModule("Inward And Outward", null, $links);
    }

    public static function purchase_bill(): array
    {
        $routePrefix = "purchase-bill";

        $links = self::getControllerDefaultLinks($routePrefix, "Inward", self::ICON_MENU_ROOT_CHILD);
        
        return $links;
    }

    public static function sale_bill(): array
    {
        $routePrefix = "sale-bill";
        
        $links = self::getControllerDefaultLinks($routePrefix, "Challan", self::ICON_MENU_ROOT_CHILD);
        
        return $links;
    }

    // public static function sale_bill(): array
    // {
    //     $routePrefix = "sale-bills";

    //     $links = self::getControllerDefaultLinks($routePrefix, "Sale Bill", self::ICON_MENU_ROOT_CHILD);
    //     $links["links"][] = self::getLink($routePrefix . ".create_with_so", "Create With SO", self::ICON_MENU_CREATE);

    //     $routePrefix = "sale-bill-item-movement";
    //     $links["links"][0]['related_links'][] = self::getLink($routePrefix . ".index", "Item Movement", self::ICON_MENU_ROOT_CHILD);

    //     return $links;
    // }

}

class Employee extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::type();

        return self::getModule("Employee", 'fas fa-users', $links);
    }

    private static function type()
    {
        $routePrefix = "employee";

        $links = self::getControllerDefaultLinks($routePrefix, "Employee", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}
