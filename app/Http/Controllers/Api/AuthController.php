<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use OwenIt\Auditing\Models\Audit;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 1, // Automatically set to regular user
        ]);

        // Create token
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;

        $latestAudit = Audit::where('auditable_type', User::class)
        ->where('auditable_id', $user->id)
        ->latest()
        ->first();

        if ($latestAudit) {
        $latestAudit->update([
        'user_type' => $user->user_type,
        'user_id' => $user->id,
        'event' => 'register',
        
        ]);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            // Check if user exists
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // Audit failed login attempt due to invalid password
                $this->createAuditRecord($user, 'login_failed_invalid_password');
            } else {
                // Audit failed login attempt due to email not found
                Audit::create([
                    'user_type' => null, // Unknown at this point
                    'user_id' => 0, // Unknown at this point
                    'event' => 'login_failed_email_not_found',
                    'auditable_type' => User::class,
                    'auditable_id' => 0,
                    'old_values' => json_encode([]), // No previous values
                    'new_values' => json_encode(['email' => $request->email]), // Failed login email
                    'url' => request()->url(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                    'tags' => json_encode([]), // Convert to JSON
                ]);
            }

            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = Auth::user();

        // Create audit record for successful login
        $this->createAuditRecord($user, 'login');

        // Check if user is admin and grant all permissions
        if ($user->user_type === 0) {
           
            $permissions = Permission::all();
            $user->syncPermissions($permissions);
        }

        // Create token
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    private function createAuditRecord(User $user, string $event)
    {
        Audit::create([
            'user_type' => $user->user_type,
            'user_id' => $user->id,
            'event' => $event,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => json_encode([]), // Convert to JSON
            'new_values' => json_encode($user->getAttributes()), // Convert to JSON
            'url' => request()->url(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'tags' => json_encode([]), // Convert to JSON
        ]);
    }
}
