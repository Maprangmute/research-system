@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8 bg-white shadow p-6 rounded">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">👥 รายชื่อผู้ใช้งาน</h2>

    <table class="w-full table-auto border text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-4 py-2 border">ชื่อ</th>
                <th class="px-4 py-2 border">อีเมล</th>
                <th class="px-4 py-2 border">ตำแหน่ง</th>
                <th class="px-4 py-2 border">สิทธิ์</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border">{{ $user->prefix }}{{ $user->name }}</td>
                <td class="px-4 py-2 border">{{ $user->email }}</td>
                <td class="px-4 py-2 border">{{ $user->position }}</td>
                <td class="px-4 py-2 border">{{ $user->role }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
