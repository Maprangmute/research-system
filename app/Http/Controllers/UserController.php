<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function manageList()
    {
        $users = User::all();
        return view('users.manage', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:user,admin']);
        $user->role = $request->role;
        $user->save();

        return redirect()->route('users.manage')->with('success', 'อัปเดตสิทธิ์เรียบร้อยแล้ว');
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'position' => 'nullable|string|max:255',
            'role' => 'required|in:user,admin',
            'password' => 'required|string|min:4|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_photo_path'] = $request->file('profile_picture')->store('profiles', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('users.index')->with('success', 'สร้างผู้ใช้งานเรียบร้อย');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'prefix' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'position' => 'nullable|string|max:255',
            'role' => 'required|in:user,admin',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['profile_photo_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'อัปเดตข้อมูลผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'ไม่สามารถลบผู้ใช้งานของคุณเองได้');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }
}
