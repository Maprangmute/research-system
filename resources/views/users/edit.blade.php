@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto mt-10 bg-white shadow p-8 rounded-lg">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">✏️ แก้ไขข้อมูลผู้ใช้งาน</h2>
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

        <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-6">
                <div class="shrink-0">
                    <div
                        class="h-24 w-24 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center overflow-hidden">
                        <img id="avatarPreview" class="h-full w-full object-cover"
                            src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->prefix . ' ' . $user->name) . '&background=random&size=128' }}"
                            alt="avatar preview">
                    </div>
                </div>
                <div class="flex-1">
                    <label class="block text-sm text-gray-700 font-medium mb-1">อัปโหลดรูปโปรไฟล์</label>
                    <input type="file" name="avatar" id="profile_picture" class="text-sm border rounded p-2 w-full">
                    <p class="text-xs text-gray-500 mt-1">สามารถเปลี่ยนได้ตามต้องการ</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 font-medium">คำนำหน้า</label>
                    <input type="text" name="prefix" value="{{ old('prefix', $user->prefix) }}"
                        class="w-full border p-2 rounded" required>
                    {{-- <select name="prefix" class="w-full border p-2 rounded" required>
                    @php
                        $prefixes = [
                            'นาย','นางสาว','นาง','คุณ','ดร.','ศ.','รศ.','ผศ.','ศ.ดร.','รศ.ดร.','ผศ.ดร.',
                            'อาจารย์','อาจารย์แพทย์','แพทย์หญิง','นายแพทย์','เภสัชกร','ทันตแพทย์','พยาบาลวิชาชีพ',
                            'ผู้ช่วยศาสตราจารย์','รองศาสตราจารย์','ศาสตราจารย์','อื่นๆ'
                        ];
                    @endphp
                    <option value="">เลือกคำนำหน้า</option>
                    @foreach ($prefixes as $prefix)
                        <option value="{{ $prefix }}" {{ old('prefix', $user->prefix) == $prefix ? 'selected' : '' }}>{{ $prefix }}</option>
                    @endforeach
                </select> --}}
                </div>

                <div>
                    <label class="block text-sm text-gray-700 font-medium">ตำแหน่ง</label>
                    <input type="text" name="position" value="{{ old('position', $user->position) }}"
                        class="w-full border p-2 rounded">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 font-medium">ชื่อ - นามสกุล</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full border p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-sm text-gray-700 font-medium">อีเมล</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full border p-2 rounded" required>
                </div>

                <div>
                    <label class="block text-sm text-gray-700 font-medium">สิทธิ์ผู้ใช้งาน</label>
                    <select name="role" class="w-full border p-2 rounded" required>
                        <option value="user" @selected($user->role === 'user')>
                            user (เข้าถึงข้อมูลของตนเอง)
                        </option>
                        <option value="admin" @selected($user->role === 'admin')>
                            admin (เข้าถึงทุกข้อมูล)
                        </option>
                        <option value="user_all" @selected($user->role === 'user_all')>
                            user_all (เข้าถึงทุกข้อมูลเหมือน admin)
                        </option>
                        <option value="research_executive" @selected($user->role === 'research_executive')>
                            research_executive (เหมือน user_all)
                        </option>
                        <option value="unit_executive" @selected($user->role === 'unit_executive')>
                            unit_executive (เหมือน admin แต่เข้า /user ไม่ได้)
                        </option>
                        <option value="resigned" @selected($user->role === 'resigned')>
                            resigned (ไม่สามารถ login ได้)
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-700 font-medium">รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)</label>
                    <input type="password" name="password" class="w-full border p-2 rounded" autocomplete="new-password">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 font-medium">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="password_confirmation" class="w-full border p-2 rounded" autocomplete="new-password">
                </div>

            </div>

            <div class="pt-4 text-right">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    💾 บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('avatarPreview').src = event.target.result;
            }
            if (e.target.files[0]) {
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
@endsection
