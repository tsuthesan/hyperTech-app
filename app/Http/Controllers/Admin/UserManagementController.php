<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{

    public function index()
    {
        $users = User::query()->with('roles')->orderBy('name')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store (Request $request)
    {
        $data = $request->validate([
            'name' => ['required|string|max:255'],
            'email' => ['required|string|email|max:255|unique:users'],
            'password' => ['required|string|min:8|confirmed'],
            'role' => ['required', Rule::exits('roles', 'name')->where('guard_name', 'web'),
            ],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($data['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users','email')->ignore($user->id),
                ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web'),
                ],
        ]);

        if(
            $user->hasRole('admin')
            && $data['role'] !== 'admin'
            && User::role('admin')->count() <= 1)
        {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'There must be at least one admin user.',
                    ]);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

}
