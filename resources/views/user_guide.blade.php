@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 rounded-xl bg-gradient-to-r from-blue-50 via-sky-50 to-white border border-blue-100 p-6">
        <div class="text-sm text-blue-600 font-semibold">คู่มือการใช้งาน</div>
        <h1 class="text-3xl font-bold text-gray-800">Research Management System</h1>
        <p class="text-gray-600 mt-2">ภาพรวมระบบ โฟลว์การทำงาน และคำแนะนำแบบละเอียดสำหรับทุกหน้า</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-3">
            <div class="sticky top-6 space-y-3">
                <div class="bg-white border rounded-lg p-4">
                    <div class="font-semibold text-gray-800 mb-2">สารบัญ</div>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li><a class="hover:underline" href="#overview">ภาพรวมระบบ</a></li>
                        <li><a class="hover:underline" href="#roles">บทบาทและสิทธิ์</a></li>
                        <li><a class="hover:underline" href="#flow">โฟลว์การทำงาน</a></li>
                        <li><a class="hover:underline" href="#structure">โครงสร้าง (STC)</a></li>
                        <li><a class="hover:underline" href="#quickstart">Quick Start</a></li>
                        <li><a class="hover:underline" href="#autosave">Auto Save</a></li>
                        <li><a class="hover:underline" href="#fte-calc">การคิด FTE</a></li>
                        <li><a class="hover:underline" href="#fte-detail">กรอก FTE แบบละเอียด</a></li>
                        <li><a class="hover:underline" href="#outputs-detail">Outputs: EA101/EA103</a></li>
                        <li><a class="hover:underline" href="#logs-detail">Project Logs (Grouped)</a></li>
                        <li><a class="hover:underline" href="#pages">การใช้งานแต่ละหน้า</a></li>
                        <li><a class="hover:underline" href="#otp">ลืมรหัสผ่าน (OTP)</a></li>
                        <li><a class="hover:underline" href="#process">กระบวนการบันทึกแต่ละส่วน</a></li>
                        <li><a class="hover:underline" href="#dev">สำหรับนักพัฒนา (Dev)</a></li>
                        <li><a class="hover:underline" href="#faq">คำถามที่พบบ่อย</a></li>
                    </ul>
                </div>
            </div>
        </aside>

        <main class="lg:col-span-9 space-y-10">
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
                <div class="overflow-x-auto">
                    <div class="min-w-[920px] grid grid-cols-9 gap-3 items-center">
                        <div class="border rounded-lg p-3 text-center bg-blue-50">
                            <div class="font-semibold">Login</div>
                            <div class="text-xs text-gray-600">อีเมล/รหัสผ่าน</div>
                        </div>
                        <div class="text-center text-gray-400">→</div>
                        <div class="border rounded-lg p-3 text-center bg-sky-50">
                            <div class="font-semibold">รายการโปรเจค</div>
                            <div class="text-xs text-gray-600">/rm</div>
                        </div>
                        <div class="text-center text-gray-400">→</div>
                        <div class="border rounded-lg p-3 text-center bg-indigo-50">
                            <div class="font-semibold">สร้าง/แก้ไข</div>
                            <div class="text-xs text-gray-600">/rm/create | /rm/edit</div>
                        </div>
                        <div class="text-center text-gray-400">→</div>
                        <div class="border rounded-lg p-3 text-center bg-emerald-50">
                            <div class="font-semibold">FTE/Outputs</div>
                            <div class="text-xs text-gray-600">ข้อมูลประกอบ</div>
                        </div>
                        <div class="text-center text-gray-400">→</div>
                        <div class="border rounded-lg p-3 text-center bg-amber-50">
                            <div class="font-semibold">รายละเอียดโครงการ</div>
                            <div class="text-xs text-gray-600">/rm/projects/{id}</div>
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
                            <li>ประเภทการนำเสนอ (Oral/Poster/Panel/Invited)</li>
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
