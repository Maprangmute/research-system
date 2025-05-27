@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">🔐 จัดการสิทธิ์ผู้ใช้งาน</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full table-auto border text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-4 py-2 border">ชื่อ</th>
                <th class="px-4 py-2 border">อีเมล</th>
                <th class="px-4 py-2 border">ตำแหน่ง</th>
                <th class="px-4 py-2 border">สิทธิ์</th>
                <th class="px-4 py-2 border">เปลี่ยนสิทธิ์</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td class="px-4 py-2 border">{{ $user->prefix }}{{ $user->name }}</td>
                <td class="px-4 py-2 border">{{ $user->email }}</td>
                <td class="px-4 py-2 border">{{ $user->position }}</td>
                <td class="px-4 py-2 border">{{ $user->role }}</td>
                <td class="px-4 py-2 border">
                    <form action="{{ route('users.updateRole', $user->id) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        @method('PUT')
                        <select name="role" class="border p-1 rounded">
                            <option value="user" @selected($user->role === 'user')>user</option>
                            <option value="admin" @selected($user->role === 'admin')>admin</option>
                        </select>
                        <button class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">อัปเดต</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
