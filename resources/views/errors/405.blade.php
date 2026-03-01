<!DOCTYPE html>
<html lang="th">
    @php
        $homeUrl = auth()->check() ? url('/dashboard') : route('login');
    @endphp
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>405 | วิธีเรียกใช้งานไม่ถูกต้อง</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,#fef3c7_0%,#fff7ed_38%,#f8fafc_100%)] text-slate-900">
        <main class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-4 py-10">
            <section class="w-full max-w-3xl overflow-hidden rounded-[28px] border border-white/70 bg-white/90 shadow-2xl shadow-slate-300/40 backdrop-blur">
                <div class="grid gap-0 md:grid-cols-[1.1fr_0.9fr]">
                    <div class="border-b border-slate-200/80 p-8 md:border-b-0 md:border-r">
                        <div class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold tracking-wide text-amber-700">
                            Error 405
                        </div>

                        <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                            วิธีเรียกใช้งานหน้านี้ไม่ถูกต้อง
                        </h1>

                        <p class="mt-4 text-sm leading-7 text-slate-600 md:text-base">
                            ระบบพบว่ามีการเรียก URL นี้ด้วยวิธีที่ไม่รองรับ เช่น เปิดหน้าที่รับเฉพาะการส่งฟอร์ม
                            หรือเรียกด้วยคำสั่งที่ไม่ตรงกับ route ที่ระบบกำหนดไว้
                        </p>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                            ลองกลับไปยังหน้าก่อนหน้าแล้วเปิดผ่านปุ่มหรือเมนูของระบบแทนการพิมพ์ URL ตรง
                        </div>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <button type="button"
                                onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ $homeUrl }}'; }"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                กลับหน้าก่อนหน้า
                            </button>
                            <a href="{{ $homeUrl }}"
                                class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-600">
                                กลับหน้าหลัก
                            </a>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-center p-8">
                        <div class="absolute inset-0 bg-[linear-gradient(160deg,rgba(245,158,11,0.12),rgba(15,23,42,0.04))]"></div>
                        <div class="relative text-center">
                            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[24px] bg-amber-100 text-amber-600 shadow-inner shadow-amber-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0118 8.414V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="mt-6 text-6xl font-black tracking-tight text-slate-200">405</div>
                            <p class="mt-3 text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">
                                Method Not Allowed
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
