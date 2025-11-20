<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่พื้นที่เอกสาร</title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai+Looped:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'IBM Plex Sans Thai Looped', sans-serif; }
  </style>
</head>
<body class="min-h-screen bg-white text-slate-800 flex items-center justify-center p-6">
  <form method="GET" action="{{ route('docs.login') }}" class="bg-white p-8 rounded-2xl shadow-xl w-96 text-center">
    <div class="mb-6 flex justify-center">
      <img src="{{ asset('images/BANNER/Banner_FALL07-07.png') }}" class="max-w-full h-auto object-contain" alt="Research Access">
    </div>

  @if(isset($error))
  <p class="text-red-600 text-sm text-center mb-3">{{ $error }}</p>
@endif


    <input type="password" name="password" required placeholder="ใส่รหัสผ่าน"
      class="border rounded-lg w-full p-3 mb-2 focus:ring-2 focus:ring-sky-400 focus:border-sky-400" autocomplete="off">

    <p class="py-2 text-slate-500">โปรดใส่รหัสผ่านที่ได้รับจากนักวิจัย</p>

    <button type="submit"
      class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3 rounded-lg shadow transition">
      เข้าสู่ระบบ
    </button>
  </form>
</body>
</html>
