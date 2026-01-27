@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-6 px-4 space-y-4">
    <h2 class="text-xl font-bold text-gray-800">🔐 จัดการสิทธิ์ผู้ใช้งาน</h2>

    @if(session('success'))
        <div class="p-3 bg-green-100 text-green-700 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    @foreach($users as $user)
        <div class="bg-white border rounded-xl shadow-sm p-4 space-y-2">
            <div class="text-sm font-semibold text-gray-900">{{ $user->prefix }}{{ $user->name }}</div>
            <div class="text-xs text-gray-500">{{ $user->email }}</div>
            <div class="text-xs text-gray-500">{{ $user->position ?? '-' }}</div>
            <div class="text-xs text-gray-600">สิทธิ์ปัจจุบัน: <span class="font-semibold">{{ $user->role }}</span></div>
            <form action="{{ route('users.updateRole', $user->id) }}" method="POST" class="flex items-center gap-2 pt-2">
                @csrf
                @method('PUT')
                <select name="role" class="border p-2 rounded text-sm flex-1">
                    <option value="user" @selected($user->role === 'user')>user</option>
                    <option value="admin" @selected($user->role === 'admin')>admin</option>
                </select>
                <button class="bg-blue-600 text-white px-3 py-2 rounded text-xs hover:bg-blue-700">อัปเดต</button>
            </form>
        </div>
    @endforeach
</div>
@endsection
