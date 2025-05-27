<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * แสดงรายชื่อผู้ใช้ทั้งหมด เพื่อจัดการสิทธิ์
     */
    public function manage()
    {
        $users = User::all();
        return view('users.manage', compact('users'));
    }

    /**
     * อัปเดตสิทธิ์ของผู้ใช้งาน (admin/user)
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user->role = $request->role;
        $user->save();

        return redirect()->route('users.manage')->with('success', 'อัปเดตสิทธิ์เรียบร้อยแล้ว');
    }

    /**
     * แสดงฟอร์มสร้างผู้ใช้งานใหม่
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * บันทึกข้อมูลผู้ใช้งานใหม่
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'position' => 'nullable|string|max:255',
            'role' => 'required|in:user,admin',
            'password' => 'required|string|min:4|confirmed',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = $request->file('profile_picture')->store('profiles', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.manage')->with('success', 'สร้างผู้ใช้งานเรียบร้อย');
    }

    public function index()
{
    $users = User::orderBy('name')->get();
    return view('users.index', compact('users'));
}

}
