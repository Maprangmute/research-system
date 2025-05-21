<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Research Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

    <nav class="bg-white shadow p-4">
        <div class="max-w-7xl mx-auto flex justify-between">
            <div class="font-bold">🧠 Research Dashboard</div>
            <div>
                <a href="/rm" class="mr-4 text-blue-600">📊 Dashboard</a>
                <a href="/rm/create" class="text-green-600">➕ เพิ่มโครงการ</a>
            </div>
            <a href="{{ route('fte.index') }}" class="text-gray-700 hover:text-blue-600">
                📋 FTE
            </a>
            
        </div>
    </nav>

    <main class="p-6">
        @yield('content')
    </main>

</body>
</html>
