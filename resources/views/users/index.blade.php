@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-6xl mx-auto mt-10 px-4 sm:px-6 lg:px-8">
     <div class="mb-4">
    <a href="javascript:history.back()" class="text-blue-600 hover:underline text-base font-semibold">
        <<< ย้อนกลับ
    </a>
</div>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">รายชื่อผู้เข้าใช้ระบบ</h2>
            {{-- <p class="text-sm text-gray-500">A list of all the users in your account including their name, title, email and role.</p> --}}
        </div>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded hover:bg-purple-700">
            เพิ่มผู้เข้าใช้งาน
        </a>
    </div>

    <form action="{{ route('users.index') }}" method="GET" class="mb-4 bg-white border rounded-lg p-3 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">ค้นหา</label>
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="ชื่อ/อีเมล/ตำแหน่ง"
                class="border rounded px-3 py-1.5 text-sm w-56">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">สิทธิ์</label>
            <select name="role" class="border rounded px-3 py-1.5 text-sm w-40">
                <option value="all" @selected(($role ?? 'all') === 'all')>ทั้งหมด</option>
                <option value="admin" @selected(($role ?? '') === 'admin')>admin</option>
                <option value="user" @selected(($role ?? '') === 'user')>user</option>
                <option value="user_all" @selected(($role ?? '') === 'user_all')>user_all</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">เรียงตาม</label>
            <select name="sort" class="border rounded px-3 py-1.5 text-sm w-44">
                <option value="name_asc" @selected(($sort ?? 'name_asc') === 'name_asc')>ชื่อ (ก-ฮ)</option>
                <option value="name_desc" @selected(($sort ?? '') === 'name_desc')>ชื่อ (ฮ-ก)</option>
                <option value="newest" @selected(($sort ?? '') === 'newest')>ใหม่ → เก่า</option>
                <option value="oldest" @selected(($sort ?? '') === 'oldest')>เก่า → ใหม่</option>
                <option value="email_asc" @selected(($sort ?? '') === 'email_asc')>อีเมล (ก-ฮ)</option>
                <option value="email_desc" @selected(($sort ?? '') === 'email_desc')>อีเมล (ฮ-ก)</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">ค้นหา</button>
            <a href="{{ route('users.index') }}" class="px-3 py-1.5 rounded bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">ล้าง</a>
        </div>
    </form>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ตำแหน่งงาน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สิทธิ์</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap flex items-center">
                        <img class="h-10 w-10 rounded-full object-cover mr-4"
                             src="{{ asset('storage/' . $user->profile_photo_path) }}?v={{ time() }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                             alt="{{ $user->name }}">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $user->prefix }} {{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $user->position ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $user->position ? 'Department' : '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $roleColor = match($user->role) {
                                'admin' => 'bg-blue-100 text-blue-800',
                                'exp' => 'bg-red-100 text-red-800',
                                default => 'bg-green-100 text-green-800'
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $roleColor }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <button onclick="confirmDelete({{ $user->id }}, '{{ $user->prefix }}{{ $user->name }}')"
                                class="text-red-600 hover:text-red-800">Delete</button>
                        <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณกำลังจะลบผู้ใช้: " + name,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endsection
