<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 | ไม่มีสิทธิ์เข้าถึงหน้านี้</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai+Looped:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* body { font-family: 'IBM Plex Sans Thai Looped', sans-serif; } */
    </style>
</head>
<body class="bg-gray-50 flex flex-col items-center justify-center min-h-screen text-center px-4">

    <div class="max-w-md bg-white p-10 rounded-2xl shadow-lg border border-slate-200">
        <div class="mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-20 h-20 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636L5.636 18.364M5.636 5.636l12.728 12.728" />
            </svg>
        </div>

        <p class="text-lg font-semibold text-gray-800 mb-2">ไม่มีสิทธิ์เข้าถึงหน้านี้</p>
        <p class="text-gray-500 mb-6">ขออภัย คุณไม่มีสิทธิ์ในการดูข้อมูล หรือ แก้ไขข้อมููล ของโครงการนี้<br>กรุณาติดต่อผู้ดูแลระบบหากคิดว่ามีข้อผิดพลาด</p>

        <div class="flex justify-center space-x-4">
            <a href="{{ url()->previous() }}" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                ⬅ กลับหน้าก่อนหน้า
            </a>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700 transition">
                ไปหน้าแรก
            </a>
        </div>
    </div>

    <footer class="mt-10 text-sm text-gray-400">
        © {{ date('Y') }} Research Management System
    </footer>
</body>
</html>
