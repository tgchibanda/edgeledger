<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CategorySeederService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        return response()->json(User::withCount(['tradeDatabases','journals'])->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role'     => 'in:superuser,user',
        ]);
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'] ?? 'user',
            'is_active' => true,
        ]);
        CategorySeederService::seedForUser($user->id);
        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user->loadCount(['tradeDatabases','journals']));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:100',
            'email'    => 'sometimes|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:8',
            'role'     => 'sometimes|in:superuser,user',
        ]);
        if (isset($data['password'])) $data['password'] = Hash::make($data['password']);
        $user->update($data);
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        if ($user->isSuperuser()) {
            return response()->json(['message'=>'Cannot delete a superuser account.'], 422);
        }
        $user->delete();
        return response()->json(['message'=>'User deleted.']);
    }

    public function toggleActive(User $user)
    {
        if ($user->isSuperuser()) {
            return response()->json(['message'=>'Cannot disable a superuser account.'], 422);
        }
        $user->update(['is_active' => !$user->is_active]);
        return response()->json($user);
    }
}
