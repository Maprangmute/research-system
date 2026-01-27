<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function resolveView(Request $request, string $base): string
    {
        $view = $base;
        $forceView = $request->query('view');
        if ($forceView === 'mobile') {
            $view = $base . '_mobile';
        } elseif ($forceView !== 'desktop') {
            $ua = (string) $request->header('User-Agent', '');
            $isMobile = (bool) preg_match(
                '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i',
                $ua
            );
            if ($isMobile) {
                $view = $base . '_mobile';
            }
        }
        return $view;
    }

    public function index(Request $request)
    {
        $query = User::query();

        $search = trim((string) $request->input('q'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('prefix', 'like', "%{$search}%");
            });
        }

        $role = (string) $request->input('role', 'all');
        if ($role !== '' && $role !== 'all') {
            $query->where('role', $role);
        }

        $sort = (string) $request->input('sort', 'name_asc');
        $sortMap = [
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'email_asc' => ['email', 'asc'],
            'email_desc' => ['email', 'desc'],
        ];
        [$sortField, $sortDir] = $sortMap[$sort] ?? $sortMap['name_asc'];

        $users = $query->orderBy($sortField, $sortDir)->get();
        $view = $this->resolveView($request, 'users.index');
        return view($view, compact('users', 'search', 'role', 'sort'));
    }

    public function manageList(Request $request)
    {
        $users = User::all();
        $view = $this->resolveView($request, 'users.manage');
        return view($view, compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin,user_all', // ✅ เพิ่ม user_all
        ]);

        $user->role = $request->role;
        $user->save();

        return redirect()->route('users.manage')->with('success', 'อัปเดตสิทธิ์เรียบร้อยแล้ว');
    }

    public function create(Request $request)
    {
        $view = $this->resolveView($request, 'users.create');
        return view($view);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix'   => 'nullable|string|max:50',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'position' => 'nullable|string|max:255',
            'role'     => 'required|in:user,admin,user_all', // ✅ เพิ่ม user_all
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

    public function edit(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $view = $this->resolveView($request, 'users.edit');
        return view($view, compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'prefix'   => 'required|string|max:50',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'position' => 'nullable|string|max:255',
            'role'     => 'required|in:user,admin,user_all', // ✅ เพิ่ม user_all
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['profile_photo_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
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
