@extends('layouts.app')
@section('content')
<style>
    .user-guide-main section {
        scroll-margin-top: 7rem;
    }

    .user-guide-toc {
        max-height: calc(100vh - 8.5rem);
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .user-guide-toc a {
        display: block;
        padding: 0.2rem 0.4rem;
        border-radius: 0.4rem;
    }

    .user-guide-toc a:hover {
        background: #eff6ff;
        text-decoration: none;
    }
</style>

<div class="max-w-7xl mx-auto">
    <div class="mb-6 rounded-xl bg-gradient-to-r from-blue-50 via-sky-50 to-white border border-blue-100 p-6">
        <div class="text-sm text-blue-600 font-semibold">คู่มือการใช้งาน</div>
        <h1 class="text-3xl font-bold text-gray-800">Research Management System</h1>
        <p class="text-gray-600 mt-2">ภาพรวมระบบ โฟลว์การทำงาน และคำแนะนำแบบละเอียดสำหรับทุกหน้า</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-3">
            <div class="sticky top-24 space-y-3">
                <div class="bg-white border rounded-lg p-4">
                    <div class="font-semibold text-gray-800 mb-2">สารบัญ</div>
                    <ul class="user-guide-toc text-sm text-blue-700 space-y-1">
                        <li><a class="hover:underline" href="#overview">ภาพรวมระบบ</a></li>
                        <li><a class="hover:underline" href="#roles">บทบาทและสิทธิ์</a></li>
                        <li><a class="hover:underline" href="#flow">โฟลว์การทำงาน</a></li>
                        <li><a class="hover:underline" href="#structure">โครงสร้าง (STC)</a></li>
                        <li><a class="hover:underline" href="#quickstart">Quick Start</a></li>
                        <li><a class="hover:underline" href="#autosave">Auto Save</a></li>
                        <li><a class="hover:underline" href="#fte-calc">การคิด FTE</a></li>
                        <li><a class="hover:underline" href="#fte-detail">กรอก FTE แบบละเอียด</a></li>
                        <li><a class="hover:underline" href="#fte-monthly-split">FTE รายคน/รายโปรเจค แบ่งรายเดือนอย่างไร</a></li>
                        <li><a class="hover:underline" href="#outputs-detail">Outputs: EA101/EA103</a></li>
                        <li><a class="hover:underline" href="#logs-detail">Project Logs (Grouped)</a></li>
                        <li><a class="hover:underline" href="#pages">การใช้งานแต่ละหน้า</a></li>
                        <li><a class="hover:underline" href="#otp">ลืมรหัสผ่าน (OTP)</a></li>
                        <li><a class="hover:underline" href="#process">กระบวนการบันทึกแต่ละส่วน</a></li>
                        <li><a class="hover:underline" href="#deep-usage">คู่มือปฏิบัติละเอียด</a></li>
                        <li><a class="hover:underline" href="#role-guide">คู่มือแยกตามบทบาท</a></li>
                        <li><a class="hover:underline" href="#troubleshoot-deep">แก้ปัญหาแบบละเอียด</a></li>
                        <li><a class="hover:underline" href="#wfa-guide">คู่มือการใช้งาน WFA</a></li>
                        <li><a class="hover:underline" href="#dev">สำหรับนักพัฒนา (Dev)</a></li>
                        <li><a class="hover:underline" href="#faq">คำถามที่พบบ่อย</a></li>
                    </ul>
                </div>
            </div>
        </aside>

        <main class="user-guide-main lg:col-span-9 space-y-10">
            <section id="overview" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">ภาพรวมระบบ</h2>
                <p class="text-gray-700">
                    ระบบนี้ใช้สำหรับบริหารโครงการวิจัยแบบครบวงจร ตั้งแต่สร้างโครงการ กำหนดผู้มีส่วนร่วม (FTE)
                    บันทึกผลผลิต ไปจนถึงติดตามประวัติการเปลี่ยนแปลงและดูภาพรวมผ่าน Dashboard
                </p>
                <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">บริหารโครงการ</div>
                        <p class="text-sm text-gray-600 mt-1">สร้าง/แก้ไข/ลบ และจัดเก็บข้อมูลสำคัญครบถ้วน</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">บันทึก FTE & Output</div>
                        <p class="text-sm text-gray-600 mt-1">จัดการบุคลากรและผลผลิตแบบเป็นระบบ</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">Log & Dashboard</div>
                        <p class="text-sm text-gray-600 mt-1">ติดตามการเปลี่ยนแปลงและภาพรวมเชิงสถิติ</p>
                    </div>
                </div>
            </section>

            <section id="roles" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">บทบาทและสิทธิ์</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">user</div>
                        <p class="text-sm text-gray-600 mt-1">เข้าถึงเฉพาะโครงการของตนเอง</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">admin</div>
                        <p class="text-sm text-gray-600 mt-1">เข้าถึงทุกข้อมูล จัดการผู้ใช้ และดู Logs</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">user_all</div>
                        <p class="text-sm text-gray-600 mt-1">เข้าถึงข้อมูลทั้งหมด (คล้าย admin)</p>
                    </div>
                </div>
            </section>

            <section id="flow" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">โฟลว์การทำงานหลัก</h2>
                <div class="mt-2">
                    <div class="hidden md:grid md:grid-cols-5 gap-4">
                        <div class="relative">
                            <div class="h-full rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm">
                                <div class="text-xs uppercase tracking-wide text-blue-600 font-semibold">Step 1</div>
                                <div class="mt-1 text-lg font-semibold text-slate-800">Login</div>
                                <div class="mt-1 text-sm text-slate-600">อีเมล/รหัสผ่าน</div>
                            </div>
                            <div class="absolute top-1/2 -right-3 -translate-y-1/2 text-slate-300 text-2xl">→</div>
                        </div>
                        <div class="relative">
                            <div class="h-full rounded-xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white p-4 shadow-sm">
                                <div class="text-xs uppercase tracking-wide text-sky-600 font-semibold">Step 2</div>
                                <div class="mt-1 text-lg font-semibold text-slate-800">รายการโปรเจค</div>
                                <div class="mt-1 text-sm text-slate-600"><code>/rm</code></div>
                            </div>
                            <div class="absolute top-1/2 -right-3 -translate-y-1/2 text-slate-300 text-2xl">→</div>
                        </div>
                        <div class="relative">
                            <div class="h-full rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white p-4 shadow-sm">
                                <div class="text-xs uppercase tracking-wide text-indigo-600 font-semibold">Step 3</div>
                                <div class="mt-1 text-lg font-semibold text-slate-800">สร้าง/แก้ไข</div>
                                <div class="mt-1 text-sm text-slate-600"><code>/rm/create</code> หรือ <code>/rm/edit</code></div>
                            </div>
                            <div class="absolute top-1/2 -right-3 -translate-y-1/2 text-slate-300 text-2xl">→</div>
                        </div>
                        <div class="relative">
                            <div class="h-full rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
                                <div class="text-xs uppercase tracking-wide text-emerald-600 font-semibold">Step 4</div>
                                <div class="mt-1 text-lg font-semibold text-slate-800">FTE / Outputs</div>
                                <div class="mt-1 text-sm text-slate-600">ข้อมูลประกอบโครงการ</div>
                            </div>
                            <div class="absolute top-1/2 -right-3 -translate-y-1/2 text-slate-300 text-2xl">→</div>
                        </div>
                        <div>
                            <div class="h-full rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm">
                                <div class="text-xs uppercase tracking-wide text-amber-700 font-semibold">Step 5</div>
                                <div class="mt-1 text-lg font-semibold text-slate-800">รายละเอียดโครงการ</div>
                                <div class="mt-1 text-sm text-slate-600"><code>/rm/projects/{id}</code></div>
                            </div>
                        </div>
                    </div>

                    <div class="md:hidden space-y-3">
                        <div class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-blue-600 font-semibold">Step 1</div>
                            <div class="mt-1 text-lg font-semibold text-slate-800">Login</div>
                            <div class="mt-1 text-sm text-slate-600">อีเมล/รหัสผ่าน</div>
                        </div>
                        <div class="text-center text-slate-300 text-2xl">↓</div>

                        <div class="rounded-xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white p-4 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-sky-600 font-semibold">Step 2</div>
                            <div class="mt-1 text-lg font-semibold text-slate-800">รายการโปรเจค</div>
                            <div class="mt-1 text-sm text-slate-600"><code>/rm</code></div>
                        </div>
                        <div class="text-center text-slate-300 text-2xl">↓</div>

                        <div class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white p-4 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-indigo-600 font-semibold">Step 3</div>
                            <div class="mt-1 text-lg font-semibold text-slate-800">สร้าง/แก้ไข</div>
                            <div class="mt-1 text-sm text-slate-600"><code>/rm/create</code> หรือ <code>/rm/edit</code></div>
                        </div>
                        <div class="text-center text-slate-300 text-2xl">↓</div>

                        <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-emerald-600 font-semibold">Step 4</div>
                            <div class="mt-1 text-lg font-semibold text-slate-800">FTE / Outputs</div>
                            <div class="mt-1 text-sm text-slate-600">ข้อมูลประกอบโครงการ</div>
                        </div>
                        <div class="text-center text-slate-300 text-2xl">↓</div>

                        <div class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-amber-700 font-semibold">Step 5</div>
                            <div class="mt-1 text-lg font-semibold text-slate-800">รายละเอียดโครงการ</div>
                            <div class="mt-1 text-sm text-slate-600"><code>/rm/projects/{id}</code></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">โฟลว์การสร้างโครงการ (ละเอียด)</div>
                        <ol class="text-sm text-gray-600 mt-2 list-decimal list-inside space-y-1">
                            <li>เข้าเมนู “รายการโปรเจค” → กด “เพิ่มโครงการ”</li>
                            <li>กรอกข้อมูลหลัก (ชื่อ, ประเภท, Theme, ปี)</li>
                            <li>กรอก Funding (แหล่งทุน/ชื่อทุน)</li>
                            <li>กรอก IRB / COPI / Co‑Researcher (ถ้ามี)</li>
                            <li>กรอก FTE (ชั่วโมง → ระบบคิด FTE อัตโนมัติ)</li>
                            <li>กรอก Outputs (เลือกประเภท → ระบบเปิดฟอร์มเฉพาะ EA101/EA103)</li>
                            <li>กด “บันทึกข้อมูล” เพื่อสร้างโครงการ</li>
                        </ol>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">โฟลว์การแก้ไขโครงการ</div>
                        <ol class="text-sm text-gray-600 mt-2 list-decimal list-inside space-y-1">
                            <li>เข้า “รายการโปรเจค” → เลือกโครงการ</li>
                            <li>กด “แก้ไข” เพื่อปรับข้อมูล</li>
                            <li>ระบบจะบันทึก Log เฉพาะฟิลด์ที่เปลี่ยน</li>
                            <li>กลับหน้า “รายละเอียดโครงการ” เพื่อตรวจสอบ</li>
                        </ol>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">โฟลว์การติดตาม (admin)</div>
                        <ol class="text-sm text-gray-600 mt-2 list-decimal list-inside space-y-1">
                            <li>เข้า “Project Logs”</li>
                            <li>ดูเหตุการณ์แบบ grouped ตามเวลา</li>
                            <li>คลิกแถวเพื่อดูรายละเอียด</li>
                            <li>ใช้ตัวกรองเพื่อเจาะเฉพาะประเภท/เหตุการณ์</li>
                        </ol>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">โฟลว์ลืมรหัสผ่าน (OTP)</div>
                        <ol class="text-sm text-gray-600 mt-2 list-decimal list-inside space-y-1">
                            <li>ไปหน้า “ลืมรหัสผ่าน”</li>
                            <li>กรอกอีเมล → ระบบส่ง OTP</li>
                            <li>กรอก OTP + ตั้งรหัสใหม่</li>
                            <li>กลับไปหน้า Login</li>
                        </ol>
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    หลังบันทึกข้อมูล ระบบจะสร้าง Log อัตโนมัติ และสามารถดูภาพรวมผ่าน Dashboard ได้
                </div>
            </section>

            <section id="structure" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">โครงสร้าง (STC)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">Auth & Profile</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>Login/Logout</li>
                            <li>Forgot Password (OTP)</li>
                            <li>แก้ไขอีเมล/รหัสผ่าน</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">Project Core</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>รายการโปรเจค</li>
                            <li>สร้าง/แก้ไข</li>
                            <li>รายละเอียด + PDF</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">FTE / Outputs</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>FTE รายบุคคล</li>
                            <li>FTE รายโปรเจค</li>
                            <li>Outputs + Authors + Conference</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">Logs / Dashboard</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>Project Logs (admin)</li>
                            <li>Dashboard ภาพรวม</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="quickstart" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Quick Start</h2>
                <ol class="list-decimal list-inside text-gray-700 space-y-2">
                    <li>เข้าสู่ระบบด้วยอีเมลและรหัสผ่าน</li>
                    <li>ไปที่เมนู “รายการโปรเจค” แล้วกด “เพิ่มโครงการ”</li>
                    <li>กรอกข้อมูลโครงการหลักให้ครบ แล้วบันทึก</li>
                    <li>เพิ่ม FTE และ Outputs ให้ครบ</li>
                    <li>ตรวจสอบข้อมูลในหน้า “รายละเอียดโครงการ”</li>
                </ol>
            </section>

            <section id="autosave" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Auto Save (การบันทึกอัตโนมัติ)</h2>
                <p class="text-gray-700">
                    หน้าสร้างโครงการ (`/rm/create`) มีระบบ Auto Save เพื่อป้องกันข้อมูลหาย หากปิดหน้าโดยยังไม่บันทึก
                    ระบบจะเก็บข้อมูลไว้ชั่วคราวและแสดงแถบแจ้งเตือนเมื่อกลับมาอีกครั้ง
                </p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">เมื่อพบข้อมูลค้าง</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>แสดง banner ว่าพบข้อมูลที่ยังไม่ได้บันทึก</li>
                            <li>เลือก “กู้คืน” หรือ “ทิ้งข้อมูล” ได้</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">กดบันทึกสำเร็จ</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>Auto Save จะถูกล้างอัตโนมัติ</li>
                            <li>ข้อมูลจะถูกบันทึกลงฐานข้อมูลจริง</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="fte-calc" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">การคิด FTE</h2>
                <p class="text-gray-700">
                    ในหน้ากรอก FTE ระบบจะคำนวณค่า FTE จากจำนวนชั่วโมงที่กรอกอัตโนมัติ
                </p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">สูตรคำนวณ</div>
                        <div class="text-sm text-gray-600 mt-2">
                            <span class="font-semibold">FTE = ชั่วโมง × 0.000621</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">
                            ตัวอย่าง: 160 ชั่วโมง → 0.09936 FTE
                        </div>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">วิธีใช้งาน</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>กรอกชั่วโมงในช่อง “hours”</li>
                            <li>ระบบคำนวณ FTE ให้ทันทีในช่อง “fte_value”</li>
                            <li>ถ้าต้องการแก้ไขค่าจริง ให้ปรับที่ชั่วโมง</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="fte-detail" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">กรอก FTE แบบละเอียดทีละช่อง</h2>
                <p class="text-gray-700">
                    หน้า FTE อยู่ในหน้าสร้าง/แก้ไขโครงการ ส่วนนี้ใช้บันทึกผู้มีส่วนร่วมและเวลาทำงาน
                    ระบบจะคำนวณค่า FTE อัตโนมัติจากชั่วโมงที่กรอก
                </p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">ช่องที่ต้องกรอกหลัก</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>ชื่อ‑นามสกุล</li>
                            <li>กลุ่ม (role_group)</li>
                            <li>ตำแหน่งงาน (job_detail)</li>
                            <li>วันเริ่มต้น / วันสิ้นสุด</li>
                            <li>จำนวนชั่วโมง (hours)</li>
                            <li>รายละเอียดงาน (task_detail)</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">ช่องที่ระบบคำนวณ</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>FTE Value จะคำนวณจาก “hours” อัตโนมัติ</li>
                            <li>ถ้าอยากปรับ ให้แก้ที่ hours แล้วค่า FTE จะเปลี่ยน</li>
                        </ul>
                        <div class="text-xs text-gray-500 mt-2">
                            สูตร: FTE = hours × 0.000621
                        </div>
                    </div>
                </div>
                <div class="mt-4 border rounded-lg p-4">
                    <div class="font-semibold text-gray-800">ตัวอย่างการกรอก</div>
                    <div class="text-sm text-gray-600 mt-2">
                        สมมติกรอก hours = 160 → ระบบจะคำนวณ FTE ≈ 0.09936
                    </div>
                </div>
            </section>

            <section id="fte-monthly-split" class="bg-white border rounded-xl p-6 space-y-4">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">FTE รายคน/รายโปรเจค ระบบแบ่งรายเดือนอย่างไร</h2>
                <p class="text-sm text-gray-700">
                    หน้า <code>FTE รายบุคคล</code> และ <code>FTE รายโปรเจค</code> ใช้หลักเดียวกัน: เอา FTE ทั้งก้อนของแต่ละรายการมาเฉลี่ยต่อวัน
                    แล้วกระจายตามจำนวนวันที่ทับในแต่ละเดือน (นับวันแบบรวมวันเริ่มและวันสิ้นสุด)
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">ตัวแปรในระบบ (ภาษาคนอ่านง่าย)</div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="border px-3 py-2 text-left">ชื่อในระบบ</th>
                                        <th class="border px-3 py-2 text-left">ความหมายจริง</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700">
                                    <tr><td class="border px-3 py-2"><code>hours</code></td><td class="border px-3 py-2">ชั่วโมงที่กรอกในฟอร์ม</td></tr>
                                    <tr><td class="border px-3 py-2"><code>fte_value</code></td><td class="border px-3 py-2">FTE รวมของรายการนั้น (คำนวณจากชั่วโมง)</td></tr>
                                    <tr><td class="border px-3 py-2"><code>start_date / end_date</code></td><td class="border px-3 py-2">วันเริ่มและวันสิ้นสุดของงาน</td></tr>
                                    <tr><td class="border px-3 py-2"><code>total_days</code></td><td class="border px-3 py-2">จำนวนวันทั้งหมดของช่วงงาน (นับรวมทุกวัน)</td></tr>
                                    <tr><td class="border px-3 py-2"><code>fte_per_day</code></td><td class="border px-3 py-2">FTE เฉลี่ยต่อวัน</td></tr>
                                    <tr><td class="border px-3 py-2"><code>days_in_month</code></td><td class="border px-3 py-2">จำนวนวันที่งานทับกับเดือนนั้น</td></tr>
                                    <tr><td class="border px-3 py-2"><code>fte_month</code></td><td class="border px-3 py-2">FTE ของเดือนนั้น</td></tr>
                                    <tr><td class="border px-3 py-2"><code>hours_in_month</code></td><td class="border px-3 py-2">ชั่วโมงของเดือนนั้น</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">หลักคำนวณที่ระบบใช้จริง</div>
                        <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                            <li>เอา <span class="font-semibold">ชั่วโมงที่กรอก</span> มาคูณ <code>0.000621</code> เพื่อได้ <span class="font-semibold">FTE รวมของงาน</span></li>
                            <li>นับจำนวนวันตั้งแต่วันเริ่มถึงวันสิ้นสุดแบบ <span class="font-semibold">นับวันแรกและวันสุดท้ายด้วย</span></li>
                            <li>เอา FTE รวม หารด้วยจำนวนวันทั้งหมด เพื่อได้ <span class="font-semibold">FTE เฉลี่ยต่อวัน</span></li>
                            <li>ในแต่ละเดือน ระบบจะดูว่าเดือนนั้นมีวันทำงานของรายการนี้กี่วัน (เฉพาะวันที่ทับกันจริง)</li>
                            <li>เอา <span class="font-semibold">จำนวนวันที่ทับในเดือน</span> คูณ <span class="font-semibold">FTE เฉลี่ยต่อวัน</span> = <span class="font-semibold">FTE ของเดือนนั้น</span></li>
                            <li>เอา <span class="font-semibold">ชั่วโมงรวม</span> เฉลี่ยต่อวัน แล้วคูณจำนวนวันที่ทับในเดือน = <span class="font-semibold">ชั่วโมงของเดือนนั้น</span></li>
                        </ol>
                        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            ระบบนับ “วันปฏิทินจริงทั้งหมด” รวม <span class="font-semibold">เสาร์-อาทิตย์ และวันหยุด</span> ด้วย
                            (ไม่ได้หักออกอัตโนมัติ) เพราะบางงานมีการทำงานจริงในวันดังกล่าว
                        </div>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-900 mb-2">ตัวอย่างตารางเข้าใจง่าย: 1610 ชั่วโมง (ประมาณ 1 FTE)</div>
                    <div class="text-sm text-gray-700 space-y-1">
                        <div>กำหนดช่วงงาน: <code>01/01/2026 - 31/12/2026</code> (365 วัน)</div>
                        <div>FTE รวมของงาน = <code>1610 × 0.000621 = 0.999810</code> (ประมาณ <code>1.00 FTE</code>)</div>
                        <div>FTE เฉลี่ยต่อวัน = <code>0.999810 ÷ 365 = 0.002739205</code></div>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-sm border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border px-3 py-2 text-left">เดือน</th>
                                    <th class="border px-3 py-2 text-center">จำนวนวันที่ทับในเดือน</th>
                                    <th class="border px-3 py-2 text-right">ชั่วโมงของเดือนนั้น</th>
                                    <th class="border px-3 py-2 text-right">FTE ของเดือนนั้น</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr><td class="border px-3 py-2">มกราคม 2026</td><td class="border px-3 py-2 text-center">31</td><td class="border px-3 py-2 text-right">136.74</td><td class="border px-3 py-2 text-right">0.084915</td></tr>
                                <tr><td class="border px-3 py-2">กุมภาพันธ์ 2026</td><td class="border px-3 py-2 text-center">28</td><td class="border px-3 py-2 text-right">123.51</td><td class="border px-3 py-2 text-right">0.076698</td></tr>
                                <tr><td class="border px-3 py-2">มีนาคม 2026</td><td class="border px-3 py-2 text-center">31</td><td class="border px-3 py-2 text-right">136.74</td><td class="border px-3 py-2 text-right">0.084915</td></tr>
                                <tr><td class="border px-3 py-2">เมษายน 2026</td><td class="border px-3 py-2 text-center">30</td><td class="border px-3 py-2 text-right">132.33</td><td class="border px-3 py-2 text-right">0.082176</td></tr>
                                <tr><td class="border px-3 py-2">พฤษภาคม 2026</td><td class="border px-3 py-2 text-center">31</td><td class="border px-3 py-2 text-right">136.74</td><td class="border px-3 py-2 text-right">0.084915</td></tr>
                                <tr><td class="border px-3 py-2">มิถุนายน 2026</td><td class="border px-3 py-2 text-center">30</td><td class="border px-3 py-2 text-right">132.33</td><td class="border px-3 py-2 text-right">0.082176</td></tr>
                                <tr><td class="border px-3 py-2">กรกฎาคม 2026</td><td class="border px-3 py-2 text-center">31</td><td class="border px-3 py-2 text-right">136.74</td><td class="border px-3 py-2 text-right">0.084915</td></tr>
                                <tr><td class="border px-3 py-2">สิงหาคม 2026</td><td class="border px-3 py-2 text-center">31</td><td class="border px-3 py-2 text-right">136.74</td><td class="border px-3 py-2 text-right">0.084915</td></tr>
                                <tr><td class="border px-3 py-2">กันยายน 2026</td><td class="border px-3 py-2 text-center">30</td><td class="border px-3 py-2 text-right">132.33</td><td class="border px-3 py-2 text-right">0.082176</td></tr>
                                <tr><td class="border px-3 py-2">ตุลาคม 2026</td><td class="border px-3 py-2 text-center">31</td><td class="border px-3 py-2 text-right">136.74</td><td class="border px-3 py-2 text-right">0.084915</td></tr>
                                <tr><td class="border px-3 py-2">พฤศจิกายน 2026</td><td class="border px-3 py-2 text-center">30</td><td class="border px-3 py-2 text-right">132.33</td><td class="border px-3 py-2 text-right">0.082176</td></tr>
                                <tr><td class="border px-3 py-2">ธันวาคม 2026</td><td class="border px-3 py-2 text-center">31</td><td class="border px-3 py-2 text-right">136.74</td><td class="border px-3 py-2 text-right">0.084915</td></tr>
                                <tr class="bg-blue-50 font-semibold">
                                    <td class="border px-3 py-2">รวม</td>
                                    <td class="border px-3 py-2 text-center">365</td>
                                    <td class="border px-3 py-2 text-right">1610.00</td>
                                    <td class="border px-3 py-2 text-right">0.999810</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        หมายเหตุ: ถ้าต้องการให้ได้ <code>1.000000 FTE</code> เป๊ะตามสูตรระบบนี้ จะอยู่ประมาณ <code>1610.31 ชั่วโมง</code>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-900 mb-2">ผลของตัวกรองช่วงเวลาในหน้า FTE</div>
                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                        <li><code>month</code>: คิดเฉพาะช่วงที่ทับกับเดือนปัจจุบัน</li>
                        <li><code>year</code>: คิดเฉพาะช่วงที่ทับกับปีที่เลือก</li>
                        <li><code>custom</code>: คิดเฉพาะช่วงวันที่เริ่ม-สิ้นสุดที่ผู้ใช้ระบุ</li>
                        <li>ระบบจะตัดช่วงวันที่ให้เหลือเฉพาะส่วนที่ทับกับตัวกรองก่อน แล้วค่อยแตกเป็นรายเดือน</li>
                    </ul>
                </div>
            </section>

            <section id="outputs-detail" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Outputs: EA101 / EA103 แบบละเอียด</h2>
                <p class="text-gray-700">
                    Outputs คือผลผลิตวิจัย ระบบรองรับหลายประเภท แต่ที่มีฟอร์มพิเศษคือ EA101 และ EA103
                </p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">EA101 (Research Paper / Journal)</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>เมื่อเลือกประเภท EA101 ระบบจะเปิดส่วน Authors</li>
                            <li>กรอกชื่อผู้เขียน แยกบทบาท (first / co / corresponding)</li>
                            <li>มีฟิลด์ Journal, Quartile, Impact Factor</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">EA103 (Conference)</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>เมื่อเลือก EA103 ระบบจะเปิดส่วน Conference</li>
                            <li>กรอกชื่อเวที/Conference</li>
                            <li>ประเภทการนำเสนอ (Oral/Poster/Abstract/Panel/Keynote/World café/Moderator)</li>
                            <li>สถานที่และหมายเหตุ</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="logs-detail" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">วิธีอ่าน Project Logs แบบ Grouped</h2>
                <p class="text-gray-700">
                    หน้า Project Logs จะแสดงเหตุการณ์หลายรายการที่เกิดเวลาเดียวกันเป็น 1 แถว เพื่อให้อ่านง่าย
                </p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">การอ่านแถว</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>คอลัมน์ “เหตุการณ์” จะรวมหลายเหตุการณ์ในช่วงเวลาเดียวกัน</li>
                            <li>สีของแท็กบอกประเภทเหตุการณ์ (สร้าง/แก้ไข/ลบ)</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-800">ดูรายละเอียด</div>
                        <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                            <li>คลิกแถวเพื่อเปิดลิ้นชัก</li>
                            <li>แต่ละเหตุการณ์มีรายละเอียดแยกเป็นการ์ด</li>
                            <li>จะเห็นค่าเดิม → ค่าใหม่ ชัดเจน</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="pages" class="bg-white border rounded-xl p-6 space-y-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">การใช้งานแต่ละหน้า</h2>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-800">Dashboard</div>
                    <p class="text-sm text-gray-600">ดูสรุปภาพรวมโครงการและสถิติต่าง ๆ</p>
                    <div class="mt-3">
                        <svg viewBox="0 0 400 140" class="w-full h-32">
                            <rect x="10" y="10" width="120" height="120" rx="10" fill="#e0f2fe"/>
                            <rect x="150" y="30" width="240" height="20" rx="6" fill="#bae6fd"/>
                            <rect x="150" y="60" width="200" height="20" rx="6" fill="#bae6fd"/>
                            <rect x="150" y="90" width="160" height="20" rx="6" fill="#bae6fd"/>
                        </svg>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-800">รายการโปรเจค</div>
                    <p class="text-sm text-gray-600">แสดงรายชื่อโครงการที่เข้าถึงได้ คลิกเพื่อดูรายละเอียด</p>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-800">สร้าง/แก้ไขโครงการ</div>
                    <p class="text-sm text-gray-600">กรอกข้อมูลโครงการ, Funding, IRB, COPI, Co‑Researcher, FTE และ Outputs</p>
                    <p class="text-xs text-gray-500 mt-2">ระบบมี Auto Save หากยังไม่กดบันทึก</p>
                    <div class="mt-3 text-sm text-gray-600">
                        ส่วนสำคัญที่ต้องกรอก:
                        <ul class="list-disc list-inside mt-1">
                            <li>ชื่อโครงการ (ไทย/อังกฤษ), ประเภท, Theme, ปีโปรเจค</li>
                            <li>Funding (แหล่งทุน/ชื่อทุน)</li>
                            <li>IRB / COPI / Co‑Researcher (ถ้ามี)</li>
                            <li>FTE (ผู้มีส่วนร่วม)</li>
                            <li>Outputs (ผลผลิต)</li>
                        </ul>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-800">รายละเอียดโครงการ</div>
                    <p class="text-sm text-gray-600">รวมข้อมูลทั้งหมดของโครงการ และทางลัดไปยัง Logs รายโครงการ</p>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-800">FTE</div>
                    <p class="text-sm text-gray-600">ดู FTE รายบุคคลและรายโปรเจคได้จากเมนู FTE</p>
                    <p class="text-xs text-gray-500 mt-2">ค่าคำนวณ FTE จะอัปเดตจากชั่วโมงที่กรอก</p>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-800">Outputs</div>
                    <p class="text-sm text-gray-600">จัดการผลผลิตวิจัย พร้อมผู้เขียนและ Conference (กรณี EA101/EA103)</p>
                    <div class="mt-2 text-xs text-gray-500">
                        หากเลือก EA101 ระบบจะเปิดส่วนกรอก Authors, หากเลือก EA103 จะเปิดส่วน Conference อัตโนมัติ
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-800">Project Logs (admin)</div>
                    <p class="text-sm text-gray-600">ดูประวัติการเปลี่ยนแปลงแบบ grouped และกดดูรายละเอียดเป็นลิ้นชัก</p>
                </div>
            </section>

            <section id="process" class="bg-white border rounded-xl p-6 space-y-4">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">กระบวนการบันทึกแต่ละส่วน & สิ่งที่เกิดขึ้นหลังบันทึก</h2>
                <p class="text-sm text-gray-700">ทำตามลำดับนี้จะครบทุกส่วนของโครงการ และรู้ว่าระบบทำอะไรให้เพิ่มเติมอัตโนมัติ</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900">1) ข้อมูลโครงการ</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>กรอกชื่อ (ไทย/อังกฤษ), รหัสโครงการ, ประเภท, Theme, ปี</li>
                            <li>ผลลัพธ์: สร้างระเบียน Project หลัก + log “create/update”</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900">2) Funding</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>แหล่งทุน, ชื่อทุน, งบประมาณ, ประเภททุน</li>
                            <li>ผลลัพธ์: เก็บใน Project และไปแสดงสรุปใน Dashboard</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900">3) IRB</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>รหัส IRB, หน่วยงานออก, วันหมดอายุ</li>
                            <li>ผลลัพธ์: ใช้คำนวณ “IRB ใกล้หมดอายุ” ใน Dashboard + log ส่วน IRB</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900">4) COPI / Co‑Researcher</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>เพิ่มรายชื่อและบทบาทผู้ร่วมวิจัย</li>
                            <li>ผลลัพธ์: แสดงในหน้ารายละเอียดโครงการ + log</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900">5) FTE</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>กรอกชื่อ, กลุ่ม, job_detail, task_detail, ชั่วโมง, วันที่เริ่ม/สิ้นสุด</li>
                            <li>ผลลัพธ์: ระบบกระจายช่วงวันที่เป็นรายเดือน คำนวณ FTE/วัน และ FTE ต่อเดือนให้</li>
                            <li>แสดงในหน้า FTE รายบุคคล/โครงการ + PDF + Dashboard</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900">6) Outputs</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>เลือกประเภท (EA101/EA103) → ระบบเปิดฟิลด์เฉพาะ</li>
                            <li>กรอกผู้เขียน, Journal/Conference</li>
                            <li>ผลลัพธ์: แสดงในหน้า Outputs, นับสถิติใน Dashboard</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900">7) Back Office (งานนอกโครงการ)</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>งานสนับสนุนที่ไม่ผูกโครงการ แต่มีชั่วโมง/รายละเอียด</li>
                            <li>ผลลัพธ์: รวมใน FTE รายบุคคล (ป้าย “งานนอกโครงการ”)</li>
                        </ul>
                    </div>
                </div>

                <div class="border rounded-lg p-4 text-sm text-gray-700">
                    <div class="font-semibold text-gray-900">สิ่งที่ระบบทำอัตโนมัติหลังบันทึก</div>
                    <ul class="list-disc list-inside space-y-1 mt-2">
                        <li>สร้าง Project Logs พร้อม diff ฟิลด์ที่เปลี่ยน (admin ดูที่ Project Logs)</li>
                        <li>ล้างข้อมูล Auto Save ของฟอร์มเมื่อบันทึกสำเร็จ</li>
                        <li>คำนวณ FTE/เดือนใหม่จากช่วงวันที่และชั่วโมงที่กรอก</li>
                        <li>อัปเดตข้อมูลที่ใช้ใน Dashboard (FTE summary, Outputs, IRB ใกล้หมดอายุ ฯลฯ)</li>
                        <li>สามารถดาวน์โหลด PDF FTE (รายคน/รายโครงการ) ได้ทันที</li>
                    </ul>
                </div>
            </section>


            <section id="otp" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">ลืมรหัสผ่าน (OTP)</h2>
                <ol class="list-decimal list-inside text-gray-700 space-y-2">
                    <li>ไปที่หน้า “ลืมรหัสผ่าน”</li>
                    <li>กรอกอีเมล → ระบบส่ง OTP</li>
                    <li>กรอก OTP 6 หลัก และตั้งรหัสผ่านใหม่</li>
                </ol>
            </section>

            <section id="deep-usage" class="bg-white border rounded-xl p-6 space-y-5">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">คู่มือปฏิบัติละเอียด (Step-by-Step)</h2>
                <p class="text-sm text-gray-600">
                    ส่วนนี้เพิ่มรายละเอียดเชิงปฏิบัติจริงในระดับช่องข้อมูล เพื่อให้ใช้งานได้ครบตั้งแต่สร้างโครงการจนถึงปิดงาน
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">Step 1: เตรียมข้อมูลก่อนกด “เพิ่มโครงการ”</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>ชื่อโครงการภาษาไทย (ควรตรวจชื่อซ้ำก่อน)</li>
                            <li>ประเภทโครงการ, Theme, ปีโปรเจค</li>
                            <li>ข้อมูลทุน: ประเภททุน/แหล่งทุน/ชื่อทุน/งบประมาณ</li>
                            <li>รายชื่อทีม: PI, COPI, Co-Researcher</li>
                            <li>ช่วงวันเริ่มและสิ้นสุดของโครงการ</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">Step 2: วิธีกรอกให้ผ่าน Validation รอบเดียว</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>ทุกช่องที่มี <span class="text-red-500">*</span> ต้องมีค่า</li>
                            <li>แถว IRB ที่มีรหัส ต้องมีวันหมดอายุ</li>
                            <li>FTE ต้องมีชื่อ + กลุ่ม + ภาระงาน + ชั่วโมง + ช่วงวันที่</li>
                            <li>Output ควรมีอย่างน้อย: ชื่อผลงาน + ประเภท + วันที่เผยแพร่</li>
                            <li>ถ้าฟอร์มยาว ให้บันทึกเป็นช่วงและตรวจจากหน้ารายละเอียดทันที</li>
                        </ul>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-gray-900 mb-2">Step 3: กรอกหน้าสร้างโครงการแบบละเอียดทีละส่วน</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <div class="font-semibold mb-1">A) ข้อมูลโครงการ</div>
                            <ul class="list-disc list-inside space-y-1">
                                <li>ชื่อไทย/อังกฤษของโครงการ</li>
                                <li>ประเภทโครงการ, Theme, ลักษณะโครงการ</li>
                                <li>ขนาดโครงการ, รหัส R, ปีโปรเจค</li>
                                <li>ลิงก์ Drive/Shortcut (ถ้ามี)</li>
                            </ul>
                        </div>
                        <div>
                            <div class="font-semibold mb-1">B) ระยะเวลาดำเนินโครงการ</div>
                            <ul class="list-disc list-inside space-y-1">
                                <li>วันที่เริ่ม-สิ้นสุดของทีมวิจัย</li>
                                <li>BA001, BC101, BC102, BC103</li>
                                <li>Project Status ตามขั้นตอนโครงการ</li>
                            </ul>
                        </div>
                        <div>
                            <div class="font-semibold mb-1">C) Roles and Responsibilities</div>
                            <ul class="list-disc list-inside space-y-1">
                                <li>PI: ชื่อ + สังกัด</li>
                                <li>COPI: เพิ่มได้หลายคน (ชื่อ/สังกัด)</li>
                                <li>Co-Researcher: ชื่อ/สังกัด/ภายใน-ภายนอก</li>
                            </ul>
                        </div>
                        <div>
                            <div class="font-semibold mb-1">D) IRB</div>
                            <ul class="list-disc list-inside space-y-1">
                                <li>รหัส IRB</li>
                                <li>หน่วยงานที่ออกเอกสาร</li>
                                <li>วันหมดอายุ IRB</li>
                            </ul>
                        </div>
                        <div>
                            <div class="font-semibold mb-1">E) FTE</div>
                            <ul class="list-disc list-inside space-y-1">
                                <li>กรอกชั่วโมงเพื่อให้ระบบคำนวณ FTE อัตโนมัติ</li>
                                <li>สูตร: <code>FTE = ชั่วโมง × 0.000621</code></li>
                                <li>ช่อง FTE ของหัวหน้าโครงการ เป็นค่าประเมินเพิ่มเติม</li>
                            </ul>
                        </div>
                        <div>
                            <div class="font-semibold mb-1">F) Outputs</div>
                            <ul class="list-disc list-inside space-y-1">
                                <li>EA101: เพิ่ม Journal / Quartile / IF / Authors</li>
                                <li>EA103: เพิ่ม Conference / Presentation / Location</li>
                                <li>ประเภทอื่น: กรอกข้อมูลพื้นฐานของผลงาน</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">Step 4: หลังบันทึกต้องตรวจอะไร</div>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>เปิดหน้า “รายละเอียดโครงการ” เพื่อตรวจ A-H</li>
                            <li>ตรวจ IRB ว่าแสดงวันที่หมดอายุถูกต้อง</li>
                            <li>ตรวจตาราง FTE และกราฟ FTE รายเดือน</li>
                            <li>ตรวจ Outputs ว่าแยก EA101/EA103 ถูกส่วน</li>
                            <li>ทดลองเปิด PDF โครงการ</li>
                        </ol>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">Step 5: งานที่ต้องทำต่อหลังสร้างโครงการ</div>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>สร้าง/อัปเดต Progress Reports ตามรอบงาน</li>
                            <li>วางแผน Gantt และกด “บันทึกทั้งหมด” ทุกครั้ง</li>
                            <li>อัปเดต FTE รายเดือนให้ครบ</li>
                            <li>ติดตาม Outputs ให้สอดคล้องกับปีเผยแพร่</li>
                            <li>ย้อนเช็ก Dashboard เพื่อดูภาพรวม</li>
                        </ol>
                    </div>
                </div>

                <div class="border rounded-lg p-4 text-sm text-gray-700">
                    <div class="font-semibold text-gray-900 mb-2">Auto Save ที่ควรรู้ (ป้องกันข้อมูลหาย)</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>หน้าสร้างโครงการมี Auto Save เป็นระยะ</li>
                        <li>กลับเข้าหน้าเดิม ระบบถามว่าจะกู้หรือทิ้ง draft</li>
                        <li>ถ้าบันทึกสำเร็จ Draft จะถูกล้างอัตโนมัติ</li>
                        <li>ถ้ากรอกแล้วรีโหลดหน้าโดยไม่บันทึก ให้ตรวจแบนเนอร์กู้ข้อมูลก่อนเริ่มกรอกใหม่</li>
                    </ul>
                </div>
            </section>

            <section id="role-guide" class="bg-white border rounded-xl p-6 space-y-4">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">คู่มือแยกตามบทบาท</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 text-sm text-gray-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">user</div>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>เข้า `/rm` เลือกโครงการที่เกี่ยวข้อง</li>
                            <li>กรอก FTE ของตนเองให้ครบ</li>
                            <li>เพิ่ม Output ที่ตนรับผิดชอบ</li>
                            <li>ติดตาม Progress Report ในโครงการ</li>
                            <li>แก้ไขโปรไฟล์และรหัสผ่านที่ `/user/profile`</li>
                        </ol>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">admin</div>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>ดูภาพรวมที่ Dashboard และกรองประเด็นเสี่ยง</li>
                            <li>จัดการผู้ใช้ที่ `/user`</li>
                            <li>ตรวจ Project Logs ที่ `/project-logs`</li>
                            <li>ลบ log เก่ากว่า 3 เดือนตามนโยบาย</li>
                            <li>ตรวจข้อมูล FTE/IRB/Outputs ให้ครบทั้งระบบ</li>
                        </ol>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">user_all</div>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>เข้าถึงโครงการและรายงานได้ทั้งระบบ</li>
                            <li>ช่วยติดตามงานข้ามทีมได้</li>
                            <li>ตรวจ FTE/Outputs ของหลายโครงการในรอบเดียว</li>
                            <li>ใช้ตัวกรองใน `/rm` และ `/rm/outputs` ให้เต็มที่</li>
                            <li>ประสานงานกับ admin เมื่อพบข้อมูลผิดปกติ</li>
                        </ol>
                    </div>
                </div>
            </section>

            <section id="troubleshoot-deep" class="bg-white border rounded-xl p-6 space-y-4">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">แก้ปัญหาแบบละเอียด</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border px-3 py-2 text-left">อาการที่พบ</th>
                                <th class="border px-3 py-2 text-left">สาเหตุที่พบบ่อย</th>
                                <th class="border px-3 py-2 text-left">วิธีแก้แนะนำ</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <tr>
                                <td class="border px-3 py-2">บันทึกโครงการไม่ผ่าน</td>
                                <td class="border px-3 py-2">ช่องบังคับ (*) ยังไม่ครบ หรือแถวซ้ำกรอกไม่ครบ</td>
                                <td class="border px-3 py-2">เลื่อนไปช่องที่กรอบแดง กรอกให้ครบทุกแถว IRB/FTE/Outputs</td>
                            </tr>
                            <tr>
                                <td class="border px-3 py-2">ไม่เห็น Project Logs</td>
                                <td class="border px-3 py-2">สิทธิ์ผู้ใช้ไม่ใช่ admin</td>
                                <td class="border px-3 py-2">ให้ admin ปรับ role หรือใช้งานผ่านบัญชี admin</td>
                            </tr>
                            <tr>
                                <td class="border px-3 py-2">FTE ดูผิด/ไม่ขึ้นกราฟ</td>
                                <td class="border px-3 py-2">วันเริ่ม-สิ้นสุดไม่ถูก หรือชั่วโมงเป็น 0</td>
                                <td class="border px-3 py-2">แก้ช่วงวันที่และชั่วโมงในแถว FTE แล้วบันทึกใหม่</td>
                            </tr>
                            <tr>
                                <td class="border px-3 py-2">เลือก EA101/EA103 แล้วข้อมูลไม่ครบ</td>
                                <td class="border px-3 py-2">ยังไม่เพิ่ม Authors/Conference ในส่วนพิเศษ</td>
                                <td class="border px-3 py-2">กดเพิ่มแถวผู้เขียนหรือ conference ให้ครบ แล้วบันทึกอีกครั้ง</td>
                            </tr>
                            <tr>
                                <td class="border px-3 py-2">แก้ Gantt แล้วข้อมูลหาย</td>
                                <td class="border px-3 py-2">กดเฉพาะลงตาราง แต่ยังไม่กดบันทึกทั้งหมด</td>
                                <td class="border px-3 py-2">หลังจัดงานทุกครั้ง ต้องกด “บันทึกทั้งหมด” เสมอ</td>
                            </tr>
                            <tr>
                                <td class="border px-3 py-2">OTP ไม่ผ่าน/หมดอายุ</td>
                                <td class="border px-3 py-2">เกินเวลารหัส OTP หรือพิมพ์ผิด</td>
                                <td class="border px-3 py-2">ขอ OTP ใหม่ที่หน้า “ลืมรหัสผ่าน” แล้วกรอกทันที</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="wfa-guide" class="bg-white border rounded-xl p-6 space-y-5">
                <div>
                    <div class="text-sm font-semibold text-blue-600">Work From Anywhere</div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">คู่มือการใช้งานรายงานภาระงาน (WFA)</h2>
                    <p class="text-sm text-gray-700">
                        หน้านี้ใช้สำหรับบันทึกผลลัพธ์ของงานรายเดือน ดูรายการที่บันทึกไว้ และดาวน์โหลดรายงานเป็น PDF หรือ Word
                        โดยระบบจะดึงข้อมูลภาระงาน/FTE มาแสดงในรายงานให้อัตโนมัติ
                    </p>
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                    <div class="font-semibold">สิ่งสำคัญเรื่องเดือน</div>
                    <p class="mt-1">
                        ช่อง “WFA ประจำเดือน” คือเดือนของรอบรายงาน ส่วนข้อความในช่อง Output จะอ้างอิงเดือนก่อนหน้า 1 เดือนโดยอัตโนมัติ
                        เช่น เลือก WFA ประจำเดือน มิถุนายน 2569 ระบบจะแสดงข้อความ placeholder ว่าเป็น Output ในเดือน พฤษภาคม 2569
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">ขั้นตอนเพิ่มรายการใหม่</div>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>เข้าเมนู <span class="font-semibold">WFA</span></li>
                            <li>กด <span class="font-semibold">เพิ่มผลลัพธ์ของงาน (Output) รายเดือน</span></li>
                            <li>เลือก <span class="font-semibold">WFA ประจำเดือน</span></li>
                            <li>กรอกข้อมูลในช่องรายละเอียด Output</li>
                            <li>กด <span class="font-semibold">บันทึก</span></li>
                            <li>เลือกดาวน์โหลด PDF หรือ Word เมื่อระบบถามหลังบันทึก หรือกดจากรายการภายหลัง</li>
                        </ol>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">สิทธิ์การใช้งาน</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>ผู้ใช้ทั่วไป: เพิ่ม ดู แก้ไข ดาวน์โหลด และลบรายการของตนเอง</li>
                            <li>ผู้บริหารหน่วย: ดูรายงานของบุคลากรในภาพรวมตามสิทธิ์ที่ได้รับ</li>
                            <li>Admin/SU Admin: จัดการรายงาน WFA ได้ทุกคน</li>
                            <li>ถ้าไม่มีสิทธิ์แก้ไข ระบบจะแสดงรายการให้ดูเท่านั้น และซ่อนปุ่มบันทึก/ลบ</li>
                        </ul>
                    </div>
                </div>

                <div class="border rounded-lg p-4 text-sm text-gray-700">
                    <div class="font-semibold text-gray-900 mb-3">ความหมายของช่องและตัวเลือกในหน้าต่างเพิ่ม/แก้ไข</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border px-3 py-2 text-left">ส่วนที่เลือก/ปุ่ม</th>
                                    <th class="border px-3 py-2 text-left">ใช้ทำอะไร</th>
                                    <th class="border px-3 py-2 text-left">ควรรู้อะไร</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">รายงานภาระงาน (WFA)</td>
                                    <td class="border px-3 py-2">ชื่อรายงานที่ระบบสร้างให้อัตโนมัติ</td>
                                    <td class="border px-3 py-2">ระบบตั้งชื่อจากชื่อผู้ใช้ เดือน WFA และเดือนผลงานรายเดือน</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">WFA ประจำเดือน</td>
                                    <td class="border px-3 py-2">เลือกเดือนรอบรายงานที่ต้องการส่ง</td>
                                    <td class="border px-3 py-2">เมื่อเลือกเดือนนี้ ช่อง Output จะอ้างอิงเดือนก่อนหน้า 1 เดือน</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">ช่องรายละเอียด Output</td>
                                    <td class="border px-3 py-2">กรอกผลลัพธ์ของงานรายเดือน</td>
                                    <td class="border px-3 py-2">ข้อความ placeholder เป็นคำแนะนำเท่านั้น และจะไม่ถูกบันทึกเป็นข้อมูล</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">แถบเครื่องมือ B / I / U / รายการ / จัดแนว / ล้างรูปแบบ</td>
                                    <td class="border px-3 py-2">จัดรูปแบบข้อความในช่องรายละเอียด</td>
                                    <td class="border px-3 py-2">ใช้เมื่อต้องการทำตัวหนา ตัวเอียง ขีดเส้นใต้ ทำรายการ จัดแนว หรือล้างรูปแบบที่ติดมาจากการวางข้อความ</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">บันทึก</td>
                                    <td class="border px-3 py-2">บันทึกรายการใหม่หรือบันทึกการแก้ไข</td>
                                    <td class="border px-3 py-2">หลังบันทึก ระบบจะถามว่าต้องการ export เป็น PDF/Word หรือไม่</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">ลบรายการนี้</td>
                                    <td class="border px-3 py-2">ลบ WFA ที่เปิดอยู่</td>
                                    <td class="border px-3 py-2">ระบบจะถามยืนยันก่อนลบ และจะแสดงปุ่มนี้เฉพาะรายการที่มีสิทธิ์ลบ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border rounded-lg p-4 text-sm text-gray-700">
                    <div class="font-semibold text-gray-900 mb-3">ความหมายของตัวเลือกในตารางรายการ WFA</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border px-3 py-2 text-left">ส่วนที่เลือก/ปุ่ม</th>
                                    <th class="border px-3 py-2 text-left">ใช้ทำอะไร</th>
                                    <th class="border px-3 py-2 text-left">ควรรู้อะไร</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">ค้นหา</td>
                                    <td class="border px-3 py-2">ค้นหาจากรหัส ชื่อรายงาน เดือน หรือข้อความใน Output</td>
                                    <td class="border px-3 py-2">ใช้เมื่อต้องการหารายการเก่าหรือรายการของเดือนที่ต้องการอย่างรวดเร็ว</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">เรียงรายการ</td>
                                    <td class="border px-3 py-2">เปลี่ยนลำดับการแสดงผลในตาราง</td>
                                    <td class="border px-3 py-2">เลือกได้ เช่น วันที่บันทึกล่าสุด เดือนล่าสุด เดือนเก่าสุด หรือเรียงตามชื่อรายงาน</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">ชื่อรายงาน</td>
                                    <td class="border px-3 py-2">เปิดดูหรือแก้ไขรายละเอียดรายการนั้น</td>
                                    <td class="border px-3 py-2">บนหน้าจอขนาดใหญ่จะเปิด modal ส่วนบางหน้าจออาจเปิดเป็นหน้าแก้ไขโดยตรง</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">PDF</td>
                                    <td class="border px-3 py-2">ดาวน์โหลด/เปิดรายงานรูปแบบ PDF</td>
                                    <td class="border px-3 py-2">เหมาะสำหรับส่งอ่านหรือตรวจสอบแบบคงรูปแบบ</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">Word</td>
                                    <td class="border px-3 py-2">ดาวน์โหลดรายงานรูปแบบ Word</td>
                                    <td class="border px-3 py-2">เหมาะเมื่อจำเป็นต้องนำไฟล์ไปแก้ไขหรือจัดหน้าเพิ่มเติม</td>
                                </tr>
                                <tr>
                                    <td class="border px-3 py-2 font-semibold">ลบ</td>
                                    <td class="border px-3 py-2">ลบรายการจากตาราง</td>
                                    <td class="border px-3 py-2">ระบบจะถามยืนยันก่อนลบ และปุ่มจะแสดงเฉพาะรายการที่มีสิทธิ์ลบ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">ก่อนบันทึกควรตรวจอะไร</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>เลือกเดือน WFA ถูกต้อง</li>
                            <li>ชื่อรายงานที่ระบบสร้างให้ตรงกับรอบรายงาน</li>
                            <li>ช่องรายละเอียดมีข้อมูลที่ต้องการบันทึกจริง ไม่ใช่ข้อความ placeholder</li>
                            <li>ไม่มีข้อความทดลองหรือข้อมูลที่ไม่ต้องการส่ง</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-gray-900 mb-2">หลังดาวน์โหลดไฟล์ควรตรวจอะไร</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>ชื่อรายงานและเดือนถูกต้อง</li>
                            <li>ภาระงาน/FTE แสดงครบตามข้อมูลที่มีในระบบ</li>
                            <li>ไฟล์ PDF/Word เป็นข้อมูลล่าสุดหลังจากกดบันทึก</li>
                            <li>ถ้าแก้ไขข้อมูลภายหลัง ต้องดาวน์โหลดไฟล์ใหม่อีกครั้ง</li>
                        </ul>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border px-3 py-2 text-left">สิ่งที่พบ</th>
                                <th class="border px-3 py-2 text-left">ควรทำอย่างไร</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <tr>
                                <td class="border px-3 py-2">เดือนในช่อง Output ไม่ตรงกับเดือน WFA</td>
                                <td class="border px-3 py-2">เป็นการทำงานปกติของระบบ เพราะ Output อ้างอิงเดือนก่อนหน้าของเดือน WFA</td>
                            </tr>
                            <tr>
                                <td class="border px-3 py-2">ไม่เห็นปุ่มบันทึกหรือลบ</td>
                                <td class="border px-3 py-2">รายการนั้นอาจเป็นโหมดดูอย่างเดียว หรือผู้ใช้ไม่มีสิทธิ์แก้ไขรายการนั้น</td>
                            </tr>
                            <tr>
                                <td class="border px-3 py-2">ไฟล์ที่ดาวน์โหลดไม่ใช่ข้อมูลล่าสุด</td>
                                <td class="border px-3 py-2">กดบันทึกข้อมูลก่อน แล้วดาวน์โหลด PDF/Word ใหม่</td>
                            </tr>
                            <tr>
                                <td class="border px-3 py-2">ภาระงาน/FTE ไม่แสดงในรายงาน</td>
                                <td class="border px-3 py-2">ตรวจว่ามีข้อมูล FTE ของผู้ใช้และช่วงเดือนที่เกี่ยวข้องในระบบแล้วหรือไม่</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="dev" class="bg-white border rounded-xl p-6 space-y-3">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">สำหรับนักพัฒนา (Dev)</h2>
                <div class="text-sm text-gray-700 space-y-2">
                    <p>จุดที่ปรับบ่อยในระบบนี้มักอยู่ในฟอร์มโครงการ, การคำนวณ FTE, และหน้ารายงาน</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Routes หลักอยู่ที่ <code>routes/web.php</code></li>
                        <li>การจัดการโครงการอยู่ที่ <code>app/Http/Controllers/ProjectController.php</code></li>
                        <li>การคำนวณ/กระจาย FTE อยู่ที่ <code>app/Http/Controllers/FteController.php</code></li>
                        <li>หน้า UI คู่มือนี้อยู่ที่ <code>resources/views/user_guide.blade.php</code></li>
                        <li>เอกสารฉบับเต็มอยู่ที่ <code>USER_GUIDE_TH.md</code></li>
                    </ul>
                </div>
            </section>

            <section id="faq" class="bg-white border rounded-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">คำถามที่พบบ่อย</h2>
                <div class="space-y-3 text-sm text-gray-700">
                    <div>
                        <div class="font-semibold">Q: ทำไมไม่เห็น Project Logs?</div>
                        <div>A: เฉพาะ admin เท่านั้นที่เข้าได้</div>
                    </div>
                    <div>
                        <div class="font-semibold">Q: ลืมรหัสผ่านทำอย่างไร?</div>
                        <div>A: ใช้ OTP ผ่านอีเมลจากหน้า “ลืมรหัสผ่าน”</div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
@endsection
