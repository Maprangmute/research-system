@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white shadow-md p-6 rounded">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">➕ เพิ่มผู้ใช้งานใหม่</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700">คำนำหน้า</label>
                <input type="text" name="prefix" class="w-full border p-2 rounded" placeholder="ดร., นาย, นาง">
            </div>

            <div>
                <label class="block text-sm text-gray-700">ตำแหน่ง</label>
                <input type="text" name="position" class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block text-sm text-gray-700">ชื่อ - นามสกุล</label>
                <input type="text" name="name" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-700">อีเมล</label>
                <input type="email" name="email" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-700">รหัสผ่าน</label>
                <input type="password" name="password" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-700">ยืนยันรหัสผ่าน</label>
                <input type="password" name="password_confirmation" class="w-full border p-2 rounded" required>
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-700">สิทธิ์ผู้ใช้งาน</label>
            <select name="role" class="w-full border p-2 rounded" required>
                <option value="user">user (เข้าถึงข้อมูลของตนเอง)</option>
                <option value="admin">admin (เข้าถึงทุกข้อมูล)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-700">รูปโปรไฟล์ (ไม่จำเป็น)</label>
            <input type="file" name="profile_picture" class="w-full border p-2 rounded">
        </div>

        <div class="pt-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ✅ สร้างผู้ใช้
            </button>
        </div>
    </form>
</div>
@endsection
