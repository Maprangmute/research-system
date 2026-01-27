@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-xl mx-auto mt-6 px-4 space-y-4">
    <div class="flex items-center justify-between">
        <a href="javascript:history.back()" class="text-blue-600 hover:underline text-sm font-semibold">
            <<< ย้อนกลับ
        </a>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center px-3 py-2 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700">
            เพิ่มผู้เข้าใช้งาน
        </a>
    </div>
    <h2 class="text-xl font-bold text-gray-800">รายชื่อผู้เข้าใช้ระบบ</h2>

    <form action="{{ route('users.index') }}" method="GET" class="bg-white border rounded-lg p-3 space-y-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">ค้นหา</label>
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="ชื่อ/อีเมล/ตำแหน่ง"
                class="border rounded px-3 py-2 text-sm w-full">
        </div>
        <div class="grid grid-cols-1 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">สิทธิ์</label>
                <select name="role" class="border rounded px-3 py-2 text-sm w-full">
                    <option value="all" @selected(($role ?? 'all') === 'all')>ทั้งหมด</option>
                    <option value="admin" @selected(($role ?? '') === 'admin')>admin</option>
                    <option value="user" @selected(($role ?? '') === 'user')>user</option>
                    <option value="user_all" @selected(($role ?? '') === 'user_all')>user_all</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">เรียงตาม</label>
                <select name="sort" class="border rounded px-3 py-2 text-sm w-full">
                    <option value="name_asc" @selected(($sort ?? 'name_asc') === 'name_asc')>ชื่อ (ก-ฮ)</option>
                    <option value="name_desc" @selected(($sort ?? '') === 'name_desc')>ชื่อ (ฮ-ก)</option>
                    <option value="newest" @selected(($sort ?? '') === 'newest')>ใหม่ → เก่า</option>
                    <option value="oldest" @selected(($sort ?? '') === 'oldest')>เก่า → ใหม่</option>
                    <option value="email_asc" @selected(($sort ?? '') === 'email_asc')>อีเมล (ก-ฮ)</option>
                    <option value="email_desc" @selected(($sort ?? '') === 'email_desc')>อีเมล (ฮ-ก)</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">ค้นหา</button>
            <a href="{{ route('users.index') }}" class="flex-1 px-3 py-2 rounded bg-gray-100 text-gray-700 text-sm text-center hover:bg-gray-200">ล้าง</a>
        </div>
    </form>

    @if(session('success'))
        <div class="p-3 bg-green-100 text-green-700 rounded text-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="p-3 bg-red-100 text-red-700 rounded text-sm">{{ session('error') }}</div>
    @endif

    @foreach($users as $user)
        @php
            $roleColor = match($user->role) {
                'admin' => 'bg-blue-100 text-blue-800',
                'exp' => 'bg-red-100 text-red-800',
                default => 'bg-green-100 text-green-800'
            };
        @endphp
        <div class="bg-white border rounded-xl shadow-sm p-4">
            <div class="flex gap-3">
                <img class="h-12 w-12 rounded-full object-cover"
                    src="{{ asset('storage/' . $user->profile_photo_path) }}?v={{ time() }}"
                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                    alt="{{ $user->name }}">
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900">{{ $user->prefix }} {{ $user->name }}</div>
                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $user->position ?? '-' }}</div>
                </div>
                <span class="px-2 h-fit inline-flex text-xs leading-5 font-semibold rounded-full {{ $roleColor }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
            <div class="flex items-center justify-end gap-3 mt-3 text-sm">
                <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">แก้ไข</a>
                <button onclick="confirmDelete({{ $user->id }}, '{{ $user->prefix }}{{ $user->name }}')"
                        class="text-red-600 hover:text-red-800">ลบ</button>
                <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    @endforeach
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
