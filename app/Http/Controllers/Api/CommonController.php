<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Source;
use App\Models\User;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function source()
    {
        $source = Source::select('id', 'resources')->get();
        return response()->json([
            'status' => true,
            'data' => $source
        ]);
    }

    public function party()
    {

        $parties = Party::select('id', 'name')->get();

        return response()->json([
            'status' => true,
            'data' => $parties
        ]);
    }

    public function user()
    {

        $users = User::select('id', 'name')->get();

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }

    public function level()
    {

        $level = config('constant.level');

        return response()->json([
            'status' => true,
            'data' => $level
        ]);
    }

    public function status()
    {

        $status = config('constant.status');

        return response()->json([
            'status' => true,
            'data' => $status
        ]);
    }

    public function followUpTypes()
    {
        $followUpTypes = config('constant.followuptype');

        return response()->json([
            'status' => true,
            'data' => $followUpTypes
        ]);
    }
}
