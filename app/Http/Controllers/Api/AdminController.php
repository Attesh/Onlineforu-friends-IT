<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function adminMethod(Request $request)
    {
        // Logic for admin route
        return response()->json(['message' => 'Admin method executed successfully']);
    }
}
