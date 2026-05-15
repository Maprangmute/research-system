@extends('layouts.app')

@php
    $roleOptions = [
        'user' => ['label' => 'user', 'help' => 'เข้าถึงข้อมูลของตนเอง'],
        'admin' => ['label' => 'admin', 'help' => 'เข้าถึงทุกข้อมูล'],
        'su_admin' => ['label' => 'su_admin', 'help' => 'เหมือน admin ยกเว้น Back Office'],
        'user_all' => ['label' => 'user_all', 'help' => 'เข้าถึงทุกข้อมูลเหมือน admin'],
        'research_executive' => ['label' => 'research_executive', 'help' => 'เหมือน user_all'],
        'unit_executive' => ['label' => 'unit_executive', 'help' => 'เหมือน admin แต่เข้า /user ไม่ได้'],
        'resigned' => ['label' => 'resigned', 'help' => 'ไม่สามารถ login ได้'],
    ];
@endphp

@section('content')
    <style>
        .user-create-page {
            background: #f8fafc;
        }

        .user-create-card {
            border: 1px solid #dbe5f0;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.06);
        }

        .user-create-section {
            border: 1px solid #dbe5f0;
            border-radius: 22px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .user-create-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 800;
            color: #475569;
        }

        .user-create-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: #fff;
            padding: 0.78rem 0.9rem;
            font-size: 0.95rem;
            color: #0f172a;
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }

        .user-create-input:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.24);
        }

        .user-create-action {
            display: inline-flex;
            min-height: 2.9rem;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            padding: 0.78rem 1rem;
            font-size: 0.92rem;
            font-weight: 800;
        }

        .user-create-action--save {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            color: #fff;
            box-shadow: 0 14px 24px rgba(37, 99, 235, 0.18);
        }

        .user-create-action--back {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
        }

        @media (min-width: 768px) and (max-width: 1180px) {
            .user-create-page .user-create-shell {
                max-width: 100%;
            }

            .user-create-page .user-create-card,
            .user-create-page .user-create-section {
                border-radius: 20px;
            }
        }
    </style>

    <div class="user-create-page min-h-screen">
        <div class="user-create-shell mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-slate-900">
                        ← กลับไปยังรายชื่อ
                    </a>
                    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 md:text-3xl">เพิ่มผู้ใช้งานใหม่</h1>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        กรอกข้อมูลบัญชี สายงาน สังกัด และสิทธิ์ผู้ใช้ให้ครบในหน้าเดียว
                    </p>
                </div>
                <div class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 md:block">
                    New user
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
                    <div class="font-bold">ตรวจสอบข้อมูลอีกครั้ง</div>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <section class="user-create-card p-4 md:p-5">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[18rem_minmax(0,1fr)]">
                        <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center gap-4 lg:flex-col lg:items-start">
                                <div class="h-24 w-24 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                                    <img id="avatarPreview" class="h-full w-full object-cover"
                                        src="https://ui-avatars.com/api/?name=New%20User&background=0F172A&color=fff&size=160"
                                        onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                        alt="avatar preview">
                                </div>
                                <div class="min-w-0">
                                    <div class="text-lg font-extrabold text-slate-900">บัญชีใหม่</div>
                                    <div class="mt-1 text-sm leading-6 text-slate-500">
                                        รูปโปรไฟล์ไม่จำเป็น แต่ช่วยให้ค้นหาและแยกผู้ใช้ได้ง่ายขึ้น
                                    </div>
                                </div>
                            </div>

                            <label class="mt-5 block">
                                <span class="user-create-label">อัปโหลดรูปโปรไฟล์</span>
                                <input type="file" name="profile_picture" id="profile_picture" accept="image/png,image/jpeg"
                                    class="user-create-input mt-1 text-sm">
                            </label>
                            <p class="mt-2 text-xs leading-5 text-slate-500">รองรับ JPG/PNG ขนาดไม่เกิน 2MB</p>
                        </aside>

                        <div class="grid grid-cols-1 gap-5">
                            <section class="user-create-section p-4">
                                <div class="mb-4">
                                    <h2 class="text-lg font-extrabold text-slate-900">ข้อมูลชื่อและการแสดงผล</h2>
                                    <p class="mt-1 text-sm text-slate-500">ชื่อไทยใช้ในระบบหลัก ส่วนชื่ออังกฤษใช้กับงานวิจัยและผลงาน</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <label>
                                        <span class="user-create-label">คำนำหน้า</span>
                                        <input type="text" name="prefix" value="{{ old('prefix') }}"
                                            class="user-create-input mt-1" required>
                                    </label>
                                    <label>
                                        <span class="user-create-label">ชื่อ - นามสกุล</span>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                            class="user-create-input mt-1" required>
                                    </label>
                                    <label>
                                        <span class="user-create-label">คำนำหน้าอังกฤษ</span>
                                        <input type="text" name="prefix_en" value="{{ old('prefix_en') }}"
                                            class="user-create-input mt-1" placeholder="เช่น Dr.">
                                    </label>
                                    <label>
                                        <span class="user-create-label">ชื่อแสดงผลภาษาอังกฤษ</span>
                                        <input type="text" name="name_en" value="{{ old('name_en') }}"
                                            class="user-create-input mt-1" placeholder="เช่น Jane Smith">
                                    </label>
                                </div>
                            </section>

                            <section class="user-create-section p-4">
                                <div class="mb-4">
                                    <h2 class="text-lg font-extrabold text-slate-900">งาน สายงาน และสังกัด</h2>
                                    <p class="mt-1 text-sm text-slate-500">ระบุ field ใหม่ให้ครบเพื่อให้หน้า FTE และ output อ่านข้อมูลได้ตรงกัน</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <label>
                                        <span class="user-create-label">ตำแหน่ง</span>
                                        <input type="text" name="position" value="{{ old('position') }}"
                                            class="user-create-input mt-1">
                                    </label>
                                    <label>
                                        <span class="user-create-label">สายงาน</span>
                                        <select name="skill_role" class="user-create-input mt-1">
                                            @foreach (\App\Models\User::SKILL_ROLES as $skillRole)
                                                <option value="{{ $skillRole }}" @selected(old('skill_role') === $skillRole)>{{ $skillRole }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <div class="md:col-span-2">
                                        @include('users.partials.borrowed-fields', [
                                            'labelClass' => 'user-create-label',
                                            'inputClass' => 'user-create-input',
                                            'textClass' => '',
                                        ])
                                    </div>
                                </div>
                            </section>

                            <section class="user-create-section p-4">
                                <div class="mb-4">
                                    <h2 class="text-lg font-extrabold text-slate-900">บัญชี สิทธิ์ และรหัสผ่าน</h2>
                                    <p class="mt-1 text-sm text-slate-500">กำหนด email, role และรหัสผ่านเริ่มต้นสำหรับการเข้าสู่ระบบ</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <label>
                                        <span class="user-create-label">อีเมล</span>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                            class="user-create-input mt-1" required>
                                    </label>
                                    <label>
                                        <span class="user-create-label">สิทธิ์ผู้ใช้งาน</span>
                                        <select name="role" class="user-create-input mt-1" required>
                                            @foreach ($roleOptions as $value => $option)
                                                <option value="{{ $value }}" @selected(old('role', 'user') === $value)>
                                                    {{ $option['label'] }} - {{ $option['help'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label>
                                        <span class="user-create-label">รหัสผ่าน</span>
                                        <input type="password" name="password" class="user-create-input mt-1" autocomplete="new-password" required>
                                    </label>
                                    <label>
                                        <span class="user-create-label">ยืนยันรหัสผ่าน</span>
                                        <input type="password" name="password_confirmation" class="user-create-input mt-1" autocomplete="new-password" required>
                                    </label>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>

                <div class="user-create-card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm leading-6 text-slate-500">
                        หลังสร้างผู้ใช้ ระบบจะกลับไปยังหน้ารายชื่อผู้ใช้งาน
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:flex sm:justify-end">
                        <a href="{{ route('users.index') }}" class="user-create-action user-create-action--back">ยกเลิก</a>
                        <button type="submit" class="user-create-action user-create-action--save">สร้างผู้ใช้</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const input = document.getElementById('profile_picture');
            const preview = document.getElementById('avatarPreview');
            if (!input || !preview) return;

            input.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (readerEvent) => {
                    preview.src = readerEvent.target.result;
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endsection
