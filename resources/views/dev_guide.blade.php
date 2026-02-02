@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="rounded-2xl bg-gradient-to-r from-indigo-50 via-sky-50 to-white border border-indigo-100 p-6 shadow-sm">
        <div class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">Admin Only</div>
        <h1 class="text-3xl font-bold text-slate-900 mt-1">คู่มือสำหรับนักพัฒนา (Full)</h1>
        <p class="text-sm text-slate-600 mt-2">สถาปัตยกรรม, โฟลว์หลัก, วิธีตั้งค่า, จุดเชื่อมต่อสำคัญ, และเคล็ดลับดูแลระบบ Research Management</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-3">
            <div class="sticky top-6 space-y-3">
                <div class="bg-white border rounded-xl p-4 shadow-sm">
                    <div class="font-semibold text-slate-900 mb-2">สารบัญ</div>
                    <ul class="text-sm text-indigo-700 space-y-1">
                        <li><a class="hover:underline" href="#stack">Stack & Runtime</a></li>
                        <li><a class="hover:underline" href="#setup">ตั้งค่า Dev (Step-by-step)</a></li>
                        <li><a class="hover:underline" href="#env">Environment</a></li>
                        <li><a class="hover:underline" href="#structure">โครงสร้างโค้ด</a></li>
                        <li><a class="hover:underline" href="#db">ฐานข้อมูลหลัก</a></li>
                        <li><a class="hover:underline" href="#flows">Data Flow หลัก</a></li>
                        <li><a class="hover:underline" href="#pages">หน้าต่าง & ฟังก์ชัน</a></li>
                        <li><a class="hover:underline" href="#fte">FTE Notes</a></li>
                        <li><a class="hover:underline" href="#logs">Project Logs & Audit</a></li>
                        <li><a class="hover:underline" href="#ui">UI พิเศษ</a></li>
                        <li><a class="hover:underline" href="#pdf">PDF/Reports</a></li>
                        <li><a class="hover:underline" href="#tests">Testing & Checklist</a></li>
                        <li><a class="hover:underline" href="#troubleshoot">Troubleshooting</a></li>
                    </ul>
                </div>
                <div class="hidden lg:block bg-white border rounded-xl p-4 shadow-sm">
                    <div class="font-semibold text-slate-900 mb-2">ภาพรวมระบบ (Diagram)</div>
                    <svg viewBox="0 0 320 220" class="w-full h-auto">
                        <rect x="10" y="20" width="90" height="50" rx="8" fill="#e0f2fe" stroke="#93c5fd"/>
                        <text x="55" y="50" text-anchor="middle" font-size="10" fill="#0f172a">Frontend</text>
                        <rect x="120" y="20" width="90" height="50" rx="8" fill="#ede9fe" stroke="#c4b5fd"/>
                        <text x="165" y="50" text-anchor="middle" font-size="10" fill="#0f172a">Laravel</text>
                        <rect x="230" y="20" width="80" height="50" rx="8" fill="#dcfce7" stroke="#86efac"/>
                        <text x="270" y="50" text-anchor="middle" font-size="10" fill="#0f172a">DB</text>
                        <line x1="100" y1="45" x2="120" y2="45" stroke="#475569" marker-end="url(#arrow)"/>
                        <line x1="210" y1="45" x2="230" y2="45" stroke="#475569" marker-end="url(#arrow)"/>
                        <rect x="60" y="110" width="200" height="70" rx="10" fill="#fff7ed" stroke="#fdba74"/>
                        <text x="160" y="135" text-anchor="middle" font-size="11" fill="#0f172a">Domains: Project / FTE / Outputs / Logs / Auth</text>
                        <text x="160" y="155" text-anchor="middle" font-size="10" fill="#0f172a">PDF (mpdf/dompdf/snappy), Queue (optional), OTP Email</text>
                        <defs>
                            <marker id="arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                <path d="M0 0 L10 5 L0 10 z" fill="#475569" />
                            </marker>
                        </defs>
                    </svg>
                </div>
            </div>
        </aside>

        <main class="lg:col-span-9 space-y-8">
            <section id="stack" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">Stack & Runtime</h2>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    <li>PHP 8.2+, Laravel 12, Jetstream + Livewire 3</li>
                    <li>Blade + Tailwind + Alpine.js; Chart.js เฉพาะบางหน้าเก่า</li>
                    <li>PDF: mpdf, dompdf, laravel-snappy (wkhtmltopdf)</li>
                    <li>DB: ปกติ MySQL/MariaDB (dev script รองรับ SQLite)</li>
                </ul>
            </section>

            <section id="setup" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">ตั้งค่า Dev (Step-by-step)</h2>
                <ol class="list-decimal list-inside text-sm text-slate-700 space-y-2">
                    <li>ติดตั้ง dependencies: <code class="bg-slate-100 px-1.5 py-0.5 rounded">composer install</code> และ <code class="bg-slate-100 px-1.5 py-0.5 rounded">npm install</code></li>
                    <li>สร้างไฟล์ env: <code class="bg-slate-100 px-1.5 py-0.5 rounded">cp .env.example .env</code> แล้วตั้งค่า DB/MAIL</li>
                    <li>คีย์ & DB: <code class="bg-slate-100 px-1.5 py-0.5 rounded">php artisan key:generate</code>, <code class="bg-slate-100 px-1.5 py-0.5 rounded">php artisan migrate</code></li>
                    <li>รัน dev พร้อม queue+logs+vite: <code class="bg-slate-100 px-1.5 py-0.5 rounded">composer run dev</code></li>
                    <li>ทดสอบ: <code class="bg-slate-100 px-1.5 py-0.5 rounded">composer test</code> | Build asset: <code class="bg-slate-100 px-1.5 py-0.5 rounded">npm run build</code></li>
                    <li>ถ้าต้องใช้ข้อมูลตัวอย่าง: import dump SQL ในรากโปรเจค (ไม่ commit)</li>
                </ol>
            </section>

            <section id="env" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">Environment ที่ต้องตั้ง</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">Database</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD</li>
                            <li>dev ทดสอบ SQLite: แก้ .env เป็น <code>DB_CONNECTION=sqlite</code> (มี script สร้างไฟล์ใน composer post-create)</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">Mail (OTP)</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION</li>
                            <li>MAIL_FROM_ADDRESS, MAIL_FROM_NAME</li>
                            <li>ถ้าใช้ queue ส่งเมล: ตั้ง QUEUE_CONNECTION และรัน worker</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">PDF (snappy)</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>SNAPPY_PDF_BINARY, SNAPPY_IMAGE_BINARY → ชี้ไป wkhtmltopdf/wkhtmltoimage</li>
                            <li>ถ้าไม่ตั้งจะ fallback dompdf/mpdf ตาม controller</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">Storage/Link</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>หลัง deploy รัน <code>php artisan storage:link</code> เพื่อให้รูปโปรไฟล์/ไฟล์เปิดได้</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="structure" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">โครงสร้างโค้ด</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">Controllers (app/Http/Controllers)</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>FteController — index/detail/project index/detail, PDF</li>
                            <li>ProjectController — CRUD โครงการ, dashboard data</li>
                            <li>ProjectLogController — logs admin</li>
                            <li>OutputController — outputs + authors/conference</li>
                            <li>BackOfficeController — งานนอกโครงการ</li>
                            <li>TaskController/TaskLinkController/GanttController — Gantt & tasks</li>
                            <li>Auth\OtpPasswordResetController — OTP reset</li>
                            <li>AiDashboardController, AgentController — ฟีเจอร์ AI</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">Models (app/Models)</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>Project (SoftDeletes, boot log create/update/delete/restore)</li>
                            <li>ProjectLog (action, changes JSON, project_name_th สำรอง)</li>
                            <li>FteRole, BackOfficeEntry, BackOfficeTask, Output, OutputAuthor</li>
                            <li>User (Jetstream), Task, TaskLink ฯลฯ</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">Views (resources/views)</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>layouts/app.blade.php (navbar fixed auto-hide)</li>
                            <li>fte/* (index, detail, project-index, project-detail, pdf)</li>
                            <li>project_logs/* (index, show, _changes)</li>
                            <li>rm/* (project list/create/edit/detail, ai_dashboard)</li>
                            <li>back_office/*, outputs/*</li>
                            <li>user_guide.blade.php, dev_guide.blade.php</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">Routes & Config</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>routes/web.php — auth guard, dev-guide(admin), project-logs(admin)</li>
                            <li>config/* — queue, mail, filesystems (storage link)</li>
                            <li>tailwind.config.js, postcss.config.js, vite.config.js</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">Assets & Scripts</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>public/ — images, compiled assets</li>
                            <li>resources/css|js — ถ้ามีเพิ่มเติม (ส่วนใหญ่ใช้ CDN Tailwind/Alpine)</li>
                            <li>package.json / composer.json — scripts dev/test/build</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="db" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">ฐานข้อมูลหลัก (สรุปคอลัมน์สำคัญ)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">projects</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>ชื่อ: project_name_th, project_name_en, project_code</li>
                            <li>ช่วงเวลา: start_date, end_date, start_year</li>
                            <li>สถานะ: planning_status, execution_status, review_status, closure_status</li>
                            <li>Funding: funding_type, funding_source, funding_name, budget</li>
                            <li>SoftDeletes: deleted_at (ใช้ withTrashed ใน log/report)</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">fte_roles</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>บุคคล: name, role_group</li>
                            <li>รายละเอียด: job_detail, task_detail</li>
                            <li>ชั่วโมง/FTE: hours, fte_value, start_date, end_date, fte_value_research (ถ้ามี)</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">project_logs</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>project_id, user_id, action (create/update/delete/restore/...)</li>
                            <li>changes (JSON diff), project_name_th (สำรองเมื่อ project ถูกลบถาวร)</li>
                            <li>timestamp ใช้จัดกลุ่มใน index</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">back_office_entries / outputs</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>งานนอกโครงการ / ผลผลิต, เชื่อมกับ tasks/conferences/authors</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">outputs & authors</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>outputs: title, type (EA101/EA103), journal/conference fields, file path</li>
                            <li>output_authors: output_id, name, role (first/co/corresponding)</li>
                        </ul>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold text-slate-900">tasks / task_links</div>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>tasks: project_id, text/title, start_date, end_date, progress</li>
                            <li>task_links: source, target, type (gantt dependency)</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="flows" class="bg-white border rounded-xl p-6 shadow-sm space-y-4">
                <h2 class="text-xl font-bold text-slate-900">Data Flow หลัก</h2>
                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-slate-900 mb-2">ProjectController Flow (CRUD + Relations)</div>
                    <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                        <li><code>index</code>: โหลด projects พร้อม filter/search สำหรับรายการ</li>
                        <li><code>create/edit</code>: preload master (IRB codes, COPI/CoResearch options, Funding list) ส่งไป Blade</li>
                        <li><code>store/update</code>: รับฟอร์มเดียวที่รวม Project + IRB + COPI + CoResearch + FTE + Outputs + BackOffice → บันทึกทีละส่วน, cascade log ผ่าน boot events</li>
                        <li><code>show</code>: รวม relations (fteRoles, outputs, irbs, copis, coresearch, backOffice, progress reports) เพื่อ render detail/PDF</li>
                        <li>ทุก action บันทึก ProjectLog (action create/update/delete/restore) พร้อม diff ในฟิลด์ changes</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-slate-900 mb-2">FTE Pipeline</div>
                    <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                        <li>รับ hours + start_date/end_date → คำนวณ days, fte_per_day, fte_month</li>
                        <li>แตกช่วงรายเดือน (overlap) → เก็บใน collection (month key) → paginate 10 เดือนใน detail/project-detail</li>
                        <li>ไม่แสดง start/end date หรือ progress bar ใน view index/detail/project-detail</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="font-semibold text-slate-900 mb-2">Logs</div>
                    <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                        <li>บันทึก action: create/update/delete/restore (+ entity_* เช่น irb, output, fte)</li>
                        <li>หน้า index group ตาม timestamp+project+user</li>
                        <li>ดึง project ด้วย <code class="bg-slate-100 px-1 py-0.5 rounded">withTrashed()</code>; ถ้าไม่พบใช้ชื่อสำรองจาก log</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 space-y-3">
                    <div class="font-semibold text-slate-900 mb-2">ภาพประกอบ Flow (Project → Relations)</div>
                    <svg viewBox="0 0 640 200" class="w-full h-auto">
                        <rect x="20" y="70" width="120" height="50" rx="8" fill="#e0f2fe" stroke="#93c5fd"/>
                        <text x="80" y="100" text-anchor="middle" font-size="11" fill="#0f172a">ProjectController</text>

                        <rect x="170" y="20" width="100" height="40" rx="8" fill="#fef9c3" stroke="#fde68a"/>
                        <text x="220" y="45" text-anchor="middle" font-size="10" fill="#0f172a">Project</text>

                        <rect x="170" y="80" width="100" height="40" rx="8" fill="#dcfce7" stroke="#86efac"/>
                        <text x="220" y="105" text-anchor="middle" font-size="10" fill="#0f172a">IRB</text>

                        <rect x="170" y="140" width="100" height="40" rx="8" fill="#ffe4e6" stroke="#f9a8d4"/>
                        <text x="220" y="165" text-anchor="middle" font-size="10" fill="#0f172a">COPI / CoResearch</text>

                        <rect x="310" y="20" width="110" height="40" rx="8" fill="#ede9fe" stroke="#c4b5fd"/>
                        <text x="365" y="45" text-anchor="middle" font-size="10" fill="#0f172a">FTE Roles</text>

                        <rect x="310" y="80" width="110" height="40" rx="8" fill="#c7d2fe" stroke="#a5b4fc"/>
                        <text x="365" y="105" text-anchor="middle" font-size="10" fill="#0f172a">Outputs</text>

                        <rect x="310" y="140" width="110" height="40" rx="8" fill="#f1f5f9" stroke="#cbd5e1"/>
                        <text x="365" y="165" text-anchor="middle" font-size="10" fill="#0f172a">BackOffice</text>

                        <rect x="460" y="70" width="90" height="50" rx="8" fill="#dcfce7" stroke="#86efac"/>
                        <text x="505" y="95" text-anchor="middle" font-size="10" fill="#0f172a">Logs</text>

                        <rect x="570" y="70" width="50" height="50" rx="8" fill="#ede9fe" stroke="#c4b5fd"/>
                        <text x="595" y="98" text-anchor="middle" font-size="10" fill="#0f172a">DB</text>

                        <line x1="140" y1="95" x2="170" y2="40" stroke="#475569" marker-end="url(#arrow)"/>
                        <line x1="140" y1="95" x2="170" y2="100" stroke="#475569" marker-end="url(#arrow)"/>
                        <line x1="140" y1="95" x2="170" y2="160" stroke="#475569" marker-end="url(#arrow)"/>

                        <line x1="270" y1="40" x2="310" y2="40" stroke="#475569" marker-end="url(#arrow)"/>
                        <line x1="270" y1="100" x2="310" y2="100" stroke="#475569" marker-end="url(#arrow)"/>
                        <line x1="270" y1="160" x2="310" y2="160" stroke="#475569" marker-end="url(#arrow)"/>

                        <line x1="420" y1="40" x2="460" y2="95" stroke="#475569" marker-end="url(#arrow)"/>
                        <line x1="420" y1="100" x2="460" y2="95" stroke="#475569" marker-end="url(#arrow)"/>
                        <line x1="420" y1="160" x2="460" y2="95" stroke="#475569" marker-end="url(#arrow)"/>

                        <line x1="550" y1="95" x2="570" y2="95" stroke="#475569" marker-end="url(#arrow)"/>

                        <defs>
                            <marker id="arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                <path d="M0 0 L10 5 L0 10 z" fill="#475569" />
                            </marker>
                        </defs>
                    </svg>
                    <div class="text-xs text-slate-700 leading-relaxed">
                        ลำดับการไหล:
                        1) ผู้ใช้กรอกฟอร์มโครงการ (create/edit) → ส่ง HTTP มาที่ ProjectController. <br>
                        2) Controller แตก payload ออกเป็นส่วนย่อย แล้วบันทึกลงตารางย่อยแต่ละอัน: Project (ข้อมูลหลัก/สถานะ/ทุน), IRB (รหัส/วันหมดอายุ), COPI & CoResearch (ผู้ร่วมวิจัย), FTE Roles (ชั่วโมง/บทบาท), Outputs (ผลงาน + ผู้เขียน), BackOffice (งานนอกโครงการ). <br>
                        3) หลังบันทึก ProjectController จัดคิวไปยัง ProjectLog เพื่อสร้าง log (action + changes) โดย logs จะอ้างอิง project_id; ถ้า project ถูกลบภายหลังยังสามารถอ่าน log ได้เพราะเก็บ project_name_th สำรอง. <br>
                        4) ทุก relation เขียนลง DB แล้วสัมพันธ์ด้วย project_id; view detail ใช้ withTrashed กับ project เพื่อให้เปิดโครงการที่ลบไปแล้วได้. <br>
                        5) เมื่อเปิดหน้า detail หรือ PDF ระบบโหลด relations ทั้งหมด (FTE/IRB/COPI/CoResearch/Outputs/BackOffice) เพื่อแสดงหรือส่งออก.
                    </div>
                </div>
            </section>

            <section id="pages" class="bg-white border rounded-xl p-6 shadow-sm space-y-4">
                <h2 class="text-xl font-bold text-slate-900">หน้าต่าง & ฟังก์ชัน (Controller → View → Data)</h2>
                <div class="border rounded-lg p-4 text-sm text-slate-700 space-y-2">
                    <div class="font-semibold text-slate-900">Dashboard</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Route: <code>/dashboard</code> → <code>DashboardController@index</code> → view <code>dashboard.blade.php</code></li>
                        <li>Data: รวม projects, outputs, fte summary (สำหรับ widget/graph)</li>
                        <li>ไม่มีฟอร์มบันทึก ส่งออกไปเฉพาะ render</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 text-sm text-slate-700 space-y-2">
                    <div class="font-semibold text-slate-900">Project List / CRUD</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>List: <code>/rm</code> → <code>ProjectController@index</code> → view <code>projects/index.blade.php</code></li>
                        <li>Create: <code>/rm/create</code> → <code>create</code> view; Submit POST <code>/rm</code> → <code>store</code></li>
                        <li>Edit: <code>/rm/edit/{id}</code> → <code>edit</code> view; PUT <code>/rm/update/{id}</code> → <code>update</code></li>
                        <li>Detail: <code>/rm/projects/{id}</code> → <code>show</code>; includes partials FTE/Outputs/Logs links</li>
                        <li>Data out: JSON forms -> Request validated -> Project model save; Logs trigger via Model boot</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 text-sm text-slate-700 space-y-2">
                    <div class="font-semibold text-slate-900">FTE</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Summary บุคคล: <code>/rm/fte</code> → <code>FteController@index</code> → view <code>fte/index.blade.php</code> (ลิ้นชักรายเดือน)</li>
                        <li>Detail บุคคล: <code>/rm/fte/{person}/detail</code> → <code>FteController@detail</code> (paginate 10 เดือน, ไม่มีวันที่/กราฟ)</li>
                        <li>Summary โครงการ: <code>/rm/fte/projects</code> → <code>projectIndex</code>; Detail โครงการ: <code>/rm/fte/project/{id}</code></li>
                        <li>ส่งออก PDF: <code>/rm/fte/{person}/pdf</code>, <code>/rm/fte/project/{id}/pdf</code></li>
                        <li>Data: FteRole + BackOfficeEntry ถูกกระจายรายเดือน (buildFteDetailData/buildFteProjectData)</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 text-sm text-slate-700 space-y-2">
                    <div class="font-semibold text-slate-900">Outputs</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>List: <code>/rm/outputs</code> → <code>OutputController@index</code></li>
                        <li>Create/Edit (ภายในหน้ารายละเอียดโครงการ) — ฟอร์มส่ง authors/conference ตาม type EA101/EA103</li>
                        <li>Data: outputs + output_authors + conference fields</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 text-sm text-slate-700 space-y-2">
                    <div class="font-semibold text-slate-900">Back Office (งานนอกโครงการ)</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>List/Create/Edit: <code>/rm/back-office</code> (BackOfficeController)</li>
                        <li>เชื่อมกับ FTE detail (task_detail จะโชว์เป็น “งานนอกโครงการ”)</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 text-sm text-slate-700 space-y-2">
                    <div class="font-semibold text-slate-900">Project Logs (admin)</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Index: <code>/project-logs</code> → <code>ProjectLogController@index</code> (group by time+project+user)</li>
                        <li>Show: <code>/project-logs/{id}</code> → <code>show</code>; Project-specific: <code>/projects/{project}/logs</code></li>
                        <li>Data: ProjectLog withTrashed project; changes JSON render via <code>project_logs/_changes</code></li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 text-sm text-slate-700 space-y-2">
                    <div class="font-semibold text-slate-900">Gantt / Tasks</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>View: <code>/rm/projects/{project}/gantt</code> → <code>GanttController@index</code></li>
                        <li>Data endpoints: <code>/projects/{project}/tasks</code> (GET), <code>/tasks</code> POST/PUT/DELETE, <code>/task-links</code></li>
                        <li>ใช้ DHTMLX Gantt + custom Task/TaskLink controller</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 text-sm text-slate-700 space-y-2">
                    <div class="font-semibold text-slate-900">Auth & OTP</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Forgot/Reset Password ด้วย OTP: <code>/forgot-password</code>, <code>/reset-password</code> → <code>OtpPasswordResetController</code></li>
                        <li>Mail ใช้ค่าจาก .env; สามารถต่อ queue ได้</li>
                    </ul>
                </div>
            </section>

            <section id="fte" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">FTE Notes (อัปเดต)</h2>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    <li>หน้า index/detail/project-detail ตัดการแสดงวันที่เริ่ม/สิ้นสุด และ progress bar</li>
                    <li>แสดงรายละเอียดงาน: `job_detail` แถวหลัก + `task_detail` แถว/แท็กย่อย</li>
                    <li>แบ่งหน้า 10 เดือน/หน้า (ทั้ง detail และ project-detail) เรียงล่าสุดก่อน</li>
                    <li>สูตร FTE dev: hours × 0.000621 (JS คำนวณในหน้า edit_mobile/edit; Controller คำนวณซ้ำตามจำนวนวัน)</li>
                    <li>การ parse วันที่: ตรวจรูปแบบ d-m-Y หรือ d/m/Y ก่อน <code class="bg-slate-100 px-1 py-0.5 rounded">Carbon::createFromFormat</code></li>
                </ul>
                <div class="mt-3 text-xs text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3">
                    โค้ดอ้างอิง: <code>app/Http/Controllers/FteController.php</code> ฟังก์ชัน <code>buildFteDetailData</code> / <code>buildFteProjectData</code>
                </div>
            </section>

            <section id="logs" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">Project Logs & Audit</h2>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    <li>เฉพาะ admin; route `/project-logs` และ `/projects/{id}/logs`</li>
                    <li>โหลด project ด้วย <code class="bg-slate-100 px-1 py-0.5 rounded">withTrashed()</code>; ถ้าไม่พบ ใช้ชื่อสำรองจากฟิลด์ <code class="bg-slate-100 px-1 py-0.5 rounded">project_name_th</code> ใน log</li>
                    <li>กลุ่มตาม timestamp+project+user; ลิ้นชักแสดง diff รายฟิลด์ใน partial `_changes`</li>
                </ul>
                <div class="mt-3 text-xs text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3">
                    โค้ดอ้างอิง: <code>app/Http/Controllers/ProjectLogController.php</code>, <code>app/Models/Project.php::booted</code>, <code>resources/views/project_logs/_changes.blade.php</code>
                </div>
            </section>

            <section id="ui" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">UI / Behavior พิเศษ</h2>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    <li>Navbar fixed-top, auto-hide เมื่อเลื่อนลง และโชว์เมื่อเลื่อนขึ้น</li>
                    <li>Dropdown FTE ใช้ click (ไม่ใช้ hover) เพื่อไม่หาย</li>
                    <li>ลิ้นชัก FTE index/project-detail ใช้ toggle ด้วยปุ่ม “ดูรายละเอียด”</li>
                </ul>
            </section>

            <section id="pdf" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">PDF / Reports</h2>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    <li>FTE รายคน: `/fte/{person}/pdf`</li>
                    <li>FTE รายโครงการ: `/fte/project/{project}/pdf`</li>
                    <li>ใช้ mpdf/dompdf/snappy ตาม view ที่ controller เรียก</li>
                </ul>
            </section>

            <section id="tests" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">Testing & Checklist</h2>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    <li>`composer test`</li>
                    <li>`npm run build` เมื่อแก้ asset</li>
                    <li>ตรวจหน้า Project Logs ว่าอ่าน log โครงการที่ลบแล้วได้</li>
                    <li>ตรวจ FTE pages: pagination 10 เดือน/หน้า, ลิ้นชัก, ไม่มี progress bar/วันที่</li>
                    <li>เช็ก OTP: ลอง flow forgot/reset password บน SMTP ที่ตั้งไว้</li>
                    <li>PDF: เปิด `/fte/.../pdf` และ `/fte/project/.../pdf` ตรวจฟอนต์/ภาษาไทย</li>
                </ul>
            </section>

            <section id="troubleshoot" class="bg-white border rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-3">Troubleshooting</h2>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    <li>PDF ไม่ออก: เช็ก wkhtmltopdf (snappy) หรือเลือก dompdf/mpdf ให้ตรง view</li>
                    <li>OTP ไม่ส่ง: ตรวจ MAIL_* ใน .env และ queue ถ้ามี</li>
                    <li>FTE คำนวณผิด: ตรวจรูปแบบวันที่ (`d-m-Y` / `d/m/Y`) และสูตรใน JS</li>
                    <li>Navbar ซ่อนผิดจังหวะ: ดูสคริปต์ท้าย `layouts/app.blade.php` ส่วน scroll</li>
                    <li>Project Logs ว่าง: ตรวจ role admin และการใช้ withTrashed ใน controller</li>
                </ul>
            </section>
        </main>
    </div>
</div>
@endsection
