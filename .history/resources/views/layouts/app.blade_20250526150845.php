<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Research Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 text-gray-800">

    <nav class="bg-white shadow p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="font-bold">🧠 Research Dashboard</div>

            <div class="flex items-center gap-4">
                <a href="/dashboard" class="text-blue-600 hover:underline">📊 Dashboard</a>
                <a href="/rm" class="text-blue-600 hover:underline">📋 รายการโปรเจค</a>
                <a href="/rm/create" class="text-green-600 hover:underline">➕ เพิ่มโครงการ</a>
                <a href="{{ route('fte.index') }}" class="text-gray-700 hover:text-blue-600"> FTE รายบุคคล</a>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-700 hover:bg-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-md z-50">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🚪 ออกจากระบบ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="p-6">
        @yield('content')
    </main>

</body>
</html>
