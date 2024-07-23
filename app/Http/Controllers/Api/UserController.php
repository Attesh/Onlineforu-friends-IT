<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function manageUsers(Request $request)
    {
        // Logic for manage users route
        return response()->json(['message' => 'Manage users method executed successfully']);
    }
}
