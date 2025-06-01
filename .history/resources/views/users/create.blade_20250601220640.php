@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto mt-10 bg-white shadow p-8 rounded-lg">
     <div class="mb-4">
    <a href="javascript:history.back()" class="text-blue-600 hover:underline text-base font-semibold">
        <<< ย้อนกลับ
    </a>
</div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">➕ เพิ่มผู้ใช้งานใหม่</h2>
        <a href="{{ route('users.index') }}" class="text-sm text-purple-600 hover:underline">⬅️ กลับไปยังรายชื่อ</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="flex items-center gap-6">
            <div class="shrink-0">
                <div class="h-24 w-24 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center overflow-hidden">
                    <img id="avatarPreview" class="h-full w-full object-cover" 
                         src="https://ui-avatars.com/api/?name=Preview&background=random&size=128"
                         alt="avatar preview">
                </div>
            </div>
            <div class="flex-1">
                <label class="block text-sm text-gray-700 font-medium mb-1">อัปโหลดรูปโปรไฟล์</label>
                <input type="file" name="profile_picture" id="profile_picture" class="text-sm border rounded p-2 w-full">
                <p class="text-xs text-gray-500 mt-1">ไม่จำเป็นต้องใส่ก็ได้</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700 font-medium">คำนำหน้า</label>
                <select name="prefix" class="w-full border p-2 rounded" required>
                    <option value="">เลือกคำนำหน้า</option>
                    <option value="นาย">นาย</option>
                    <option value="นางสาว">นางสาว</option>
                    <option value="นาง">นาง</option>
                    <option value="คุณ">คุณ</option>
                    <option value="ดร.">ดร.</option>
                    <option value="ศ.">ศ.</option>
                    <option value="รศ.">รศ.</option>
                    <option value="ผศ.">ผศ.</option>
                    <option value="ศ.ดร.">ศ.ดร.</option>
                    <option value="รศ.ดร.">รศ.ดร.</option>
                    <option value="ผศ.ดร.">ผศ.ดร.</option>
                    <option value="อาจารย์">อาจารย์</option>
                    <option value="อาจารย์แพทย์">อาจารย์แพทย์</option>
                    <option value="แพทย์หญิง">แพทย์หญิง</option>
                    <option value="นายแพทย์">นายแพทย์</option>
                    <option value="เภสัชกร">เภสัชกร</option>
                    <option value="ทันตแพทย์">ทันตแพทย์</option>
                    <option value="พยาบาลวิชาชีพ">พยาบาลวิชาชีพ</option>
                    <option value="ผู้ช่วยศาสตราจารย์">ผู้ช่วยศาสตราจารย์</option>
                    <option value="รองศาสตราจารย์">รองศาสตราจารย์</option>
                    <option value="ศาสตราจารย์">ศาสตราจารย์</option>
                    <option value="อื่นๆ">อื่นๆ</option>
                </select>
            </div>

             <div>
                <label class="block text-sm text-gray-700 font-medium">ชื่อ - นามสกุล</label>
                <input type="text" name="name" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-700 font-medium">ตำแหน่ง</label>
                <input type="text" name="position" class="w-full border p-2 rounded">
            </div>


            <div>
                <label class="block text-sm text-gray-700 font-medium">อีเมล</label>
                <input type="email" name="email" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-700 font-medium">รหัสผ่าน</label>
                <input type="password" name="password" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm text-gray-700 font-medium">ยืนยันรหัสผ่าน</label>
                <input type="password" name="password_confirmation" class="w-full border p-2 rounded" required>
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-700 font-medium">สิทธิ์ผู้ใช้งาน</label>
            <select name="role" class="w-full border p-2 rounded" required>
                <option value="user">user (เข้าถึงข้อมูลของตนเอง)</option>
                <option value="admin">admin (เข้าถึงทุกข้อมูล)</option>
                {{-- <option value="exp">exp (สิทธิ์พิเศษเฉพาะบางกรณี)</option> --}}
            </select>
        </div>

        <div class="pt-4 text-right">
            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700">
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
