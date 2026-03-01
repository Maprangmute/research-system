<!DOCTYPE html>
<html lang="th">
    @php
        $homeUrl = auth()->check() ? url('/dashboard') : route('login');
    @endphp
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>404 | ไม่พบหน้าที่ต้องการ</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,#e0f2fe_0%,#f8fafc_42%,#e2e8f0_100%)] text-slate-900">
        <main class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-4 py-10">
            <section class="w-full max-w-3xl overflow-hidden rounded-[28px] border border-white/70 bg-white/90 shadow-2xl shadow-slate-300/40 backdrop-blur">
                <div class="grid gap-0 md:grid-cols-[1.1fr_0.9fr]">
                    <div class="border-b border-slate-200/80 p-8 md:border-b-0 md:border-r">
                        <div class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold tracking-wide text-sky-700">
                            Error 404
                        </div>

                        <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                            ไม่พบหน้าที่คุณต้องการ
                        </h1>

                        <p class="mt-4 text-sm leading-7 text-slate-600 md:text-base">
                            ลิงก์ที่คุณเปิดอาจไม่ถูกต้อง หน้านี้อาจถูกย้าย ถูกลบ หรือ URL ที่กรอกไม่ตรงกับที่มีอยู่ในระบบ
                        </p>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                            ลองตรวจสอบ URL อีกครั้ง หรือกลับไปยังหน้าหลักเพื่อเริ่มใช้งานจากเมนูหลักของระบบ
                        </div>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <button type="button"
                                onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ $homeUrl }}'; }"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                กลับหน้าก่อนหน้า
                            </button>
                            <a href="{{ $homeUrl }}"
                                class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700">
                                กลับหน้าหลัก
                            </a>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-center p-8">
                        <div class="absolute inset-0 bg-[linear-gradient(160deg,rgba(14,165,233,0.12),rgba(15,23,42,0.04))]"></div>
                        <div class="relative text-center">
                            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[24px] bg-sky-100 text-sky-600 shadow-inner shadow-sky-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.172 9a4 4 0 115.656 5.656L12 17.485l-2.828-2.829A4 4 0 019.172 9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 15l4 4" />
                                </svg>
                            </div>
                            <div class="mt-6 text-6xl font-black tracking-tight text-slate-200">404</div>
                            <p class="mt-3 text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">
                                Not Found
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
