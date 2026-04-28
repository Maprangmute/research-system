<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\UserDisplayNameSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function authorizeUserManagement(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->canAccessUserManagement()) {
            abort(403);
        }
    }

    private function allowedRoles(): array
    {
        return [
            User::ROLE_USER,
            User::ROLE_ADMIN,
            User::ROLE_SU_ADMIN,
            User::ROLE_USER_ALL,
            User::ROLE_RESEARCH_EXECUTIVE,
            User::ROLE_RESIGNED,
            User::ROLE_UNIT_EXECUTIVE,
        ];
    }

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
        $this->authorizeUserManagement();

        $query = User::query()->select([
            'id',
            'prefix',
            'prefix_en',
            'name',
            'name_en',
            'email',
            'position',
            'role',
            'profile_photo_path',
            'created_at',
            'updated_at',
        ]);

        $search = trim((string) $request->input('q'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('prefix_en', 'like', "%{$search}%")
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

        $totalUsers = User::count();
        $matchedUsers = (clone $query)->count();
        $roleCountsQuery = clone $query;
        $roleCountsQuery->getQuery()->columns = null;
        $roleCounts = $roleCountsQuery
            ->selectRaw('role, COUNT(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role')
            ->all();

        $users = $query->orderBy($sortField, $sortDir)
            ->paginate(25)
            ->withQueryString();

        $activeFilterLabels = [];
        if ($search !== '') {
            $activeFilterLabels[] = ['label' => 'ค้นหา', 'value' => $search];
        }
        if ($role !== '' && $role !== 'all') {
            $activeFilterLabels[] = ['label' => 'สิทธิ์', 'value' => $role];
        }
        if ($sort !== '' && $sort !== 'name_asc') {
            $activeFilterLabels[] = ['label' => 'เรียงตาม', 'value' => $sort];
        }

        $stats = [
            'total_users' => $totalUsers,
            'matched_users' => $matchedUsers,
            'admin_users' => (int) ($roleCounts[User::ROLE_ADMIN] ?? 0),
            'all_user_like' => (int) (($roleCounts[User::ROLE_SU_ADMIN] ?? 0) + ($roleCounts[User::ROLE_USER_ALL] ?? 0) + ($roleCounts[User::ROLE_RESEARCH_EXECUTIVE] ?? 0) + ($roleCounts[User::ROLE_UNIT_EXECUTIVE] ?? 0)),
            'resigned_users' => (int) ($roleCounts[User::ROLE_RESIGNED] ?? 0),
        ];

        $view = $this->resolveView($request, 'users.index');
        if ($request->ajax()) {
            return view('users.partials.index-results', compact('users', 'search', 'role', 'sort', 'activeFilterLabels', 'stats'));
        }

        return view($view, compact('users', 'search', 'role', 'sort', 'activeFilterLabels', 'stats'));
    }

    public function manageList(Request $request)
    {
        $this->authorizeUserManagement();

        $users = User::all();
        $view = $this->resolveView($request, 'users.manage');
        return view($view, compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorizeUserManagement();

        $request->validate([
            'role' => 'required|in:' . implode(',', $this->allowedRoles()),
        ]);

        $user->role = $request->role;
        $user->save();

        return redirect()->route('users.manage')->with('success', 'อัปเดตสิทธิ์เรียบร้อยแล้ว');
    }

    public function create(Request $request)
    {
        $this->authorizeUserManagement();

        $view = $this->resolveView($request, 'users.create');
        return view($view);
    }

    public function store(Request $request)
    {
        $this->authorizeUserManagement();

        $validated = $request->validate([
            'prefix'   => 'nullable|string|max:50',
            'prefix_en' => 'nullable|string|max:50',
            'name'     => 'required|string|max:255',
            'name_en'  => 'nullable|string|max:255',
            'email'    => 'required|email|unique:users',
            'position' => 'nullable|string|max:255',
            'role'     => 'required|in:' . implode(',', $this->allowedRoles()),
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
        $this->authorizeUserManagement();

        $user = User::findOrFail($id);
        $view = $this->resolveView($request, 'users.edit');
        return view($view, compact('user'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeUserManagement();

        $user = User::findOrFail($id);
        $oldPrefix = (string) ($user->prefix ?? '');
        $oldName = (string) ($user->name ?? '');

        $validated = $request->validate([
            'prefix'   => 'required|string|max:50',
            'prefix_en' => 'nullable|string|max:50',
            'name'     => 'required|string|max:255',
            'name_en'  => 'nullable|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'position' => 'nullable|string|max:255',
            'role'     => 'required|in:' . implode(',', $this->allowedRoles()),
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
        UserDisplayNameSync::syncAfterResponse(
            $oldPrefix,
            $oldName,
            (string) ($user->prefix ?? ''),
            (string) ($user->name ?? ''),
            $user->id
        );

        return redirect()->route('users.index')->with('success', 'อัปเดตข้อมูลผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        $this->authorizeUserManagement();

        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'ไม่สามารถลบผู้ใช้งานของคุณเองได้');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }
}
