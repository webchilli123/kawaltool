<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function product()
    {
        $productList  = Product::getList("id", "display_name");

        return response()->json([
            'status' => true,
            'data' => $productList
        ]);
    }
}
