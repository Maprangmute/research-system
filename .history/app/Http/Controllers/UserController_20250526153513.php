<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function manage()
    {
        $users = User::all();
        return view('users.manage', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin'
        ]);

        $user->update(['role' => $request->role]);

        return redirect()->route('users.manage')->with('success', 'อัปเดตสิทธิ์เรียบร้อยแล้ว');
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
            'prefix' => 'nullable|string',
            'position' => 'nullable|string',
            'role' => 'required|in:user,admin',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = $request->file('profile_picture')->store('profiles', 'public');
        }

        $validated['password'] = \Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.manage')->with('success', 'สร้างผู้ใช้ใหม่เรียบร้อย');
    }
}
