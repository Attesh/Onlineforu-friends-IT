<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function assignRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $role = $request->input('role');

        $user->assignRole($role);

        return response()->json(['message' => 'Role assigned successfully'], 200);
    }

    public function givePermission(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $permission = $request->input('permission');

        $user->givePermissionTo($permission);

        return response()->json(['message' => 'Permission assigned successfully'], 200);
    }
}
