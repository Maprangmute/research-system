<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่ระบบเอกสาร</title>

  <!-- ✅ Font + Tailwind -->
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai+Looped:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      font-family: 'IBM Plex Sans Thai Looped', sans-serif;
    }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-sky-50 to-indigo-50 flex items-center justify-center">

  <form method="POST" action="{{ route('docs.login') }}" class="bg-white p-8 rounded-2xl shadow-xl w-96 text-center">
    @csrf

    <!-- 🔹 โลโก้แทนข้อความ -->
    <div class="mb-6 flex justify-center">
      <img src="{{ asset('images/BANNER/Banner_FALL07-07.png') }}"  class=" object-contain">
    </div>

    @if(session('error'))
      <p class="text-red-600 text-sm text-center mb-3">{{ session('error') }}</p>
    @endif

    <input type="password" name="password" required placeholder="ใส่รหัสผ่าน"
      class="border rounded-lg w-full p-3 mb-4 focus:ring-2 focus:ring-sky-400 focus:border-sky-400">

    <button type="submit"
      class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3 rounded-lg shadow transition">
      เข้าสู่ระบบ
    </button>
  </form>

</body>
</html>
