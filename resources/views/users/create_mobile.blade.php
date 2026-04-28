@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-6 bg-white shadow p-5 rounded-lg">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">➕ เพิ่มผู้ใช้งานใหม่</h2>
        <a href="{{ route('users.index') }}" class="text-xs text-purple-600 hover:underline">⬅️ กลับไปยังรายชื่อ</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="flex items-center gap-4">
            <div class="shrink-0">
                <div class="h-20 w-20 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center overflow-hidden">
                    <img id="avatarPreview" class="h-full w-full object-cover"
                         src="https://ui-avatars.com/api/?name=Preview&background=random&size=128"
                         alt="avatar preview">
                </div>
            </div>
            <div class="flex-1">
                <label class="block text-xs text-gray-700 font-medium mb-1">อัปโหลดรูปโปรไฟล์</label>
                <input type="file" name="profile_picture" id="profile_picture" class="text-xs border rounded p-2 w-full">
                <p class="text-xs text-gray-500 mt-1">ไม่จำเป็นต้องใส่ก็ได้</p>
            </div>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs text-gray-700 font-medium">คำนำหน้า</label>
                <input type="text" name="prefix" class="w-full border p-2 rounded text-sm" required>
            </div>
            <div>
                <label class="block text-xs text-gray-700 font-medium">ชื่อ - นามสกุล</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border p-2 rounded text-sm" required>
            </div>
            <div>
                <label class="block text-xs text-gray-700 font-medium">ชื่อแสดงผลภาษาอังกฤษ</label>
                <div class="space-y-2">
                    <input type="text" name="prefix_en" value="{{ old('prefix_en') }}" class="w-full border p-2 rounded text-sm" placeholder="เช่น Dr.">
                    <input type="text" name="name_en" value="{{ old('name_en') }}" class="w-full border p-2 rounded text-sm" placeholder="เช่น Jane Smith">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-700 font-medium">ตำแหน่ง</label>
                <input type="text" name="position" value="{{ old('position') }}" class="w-full border p-2 rounded text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-700 font-medium">อีเมล</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border p-2 rounded text-sm" required>
            </div>
            <div>
                <label class="block text-xs text-gray-700 font-medium">รหัสผ่าน</label>
                <input type="password" name="password" class="w-full border p-2 rounded text-sm" required>
            </div>
            <div>
                <label class="block text-xs text-gray-700 font-medium">ยืนยันรหัสผ่าน</label>
                <input type="password" name="password_confirmation" class="w-full border p-2 rounded text-sm" required>
            </div>
        </div>

        <div>
            <label class="block text-xs text-gray-700 font-medium">สิทธิ์ผู้ใช้งาน</label>
            <select name="role" class="w-full border p-2 rounded text-sm" required>
                <option value="user">user (เข้าถึงข้อมูลของตนเอง)</option>
                <option value="admin">admin (เข้าถึงทุกข้อมูล)</option>
                <option value="su_admin">su_admin (เหมือน admin ยกเว้น Back Office)</option>
                <option value="user_all">user_all (เข้าถึงทุกข้อมูลเหมือน admin)</option>
                <option value="research_executive">research_executive (เหมือน user_all)</option>
                <option value="unit_executive">unit_executive (เหมือน admin แต่เข้า /user ไม่ได้)</option>
                <option value="resigned">resigned (ไม่สามารถ login ได้)</option>
            </select>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded text-sm hover:bg-purple-700">
                ✅ สร้างผู้ใช้
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('profile_picture').addEventListener('change', function (e) {
        const reader = new FileReader();
        reader.onload = function (event) {
            document.getElementById('avatarPreview').src = event.target.result;
        }
        if (e.target.files[0]) {
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
@endsection
