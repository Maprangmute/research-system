<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Research Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ✅ jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ jQuery Gantt Plugin (Full) -->
<link href="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/css/style.css" rel="stylesheet"/>
<script src="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/js/jquery.fn.gantt.js"></script>


</head>

<body class="bg-gray-100 text-gray-800">

    <!-- ✅ เปลี่ยนจาก max-w-7xl เป็น container -->
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="font-bold">🧠 Research Dashboard</div>

            <div class="flex items-center gap-4">
                <a href="/dashboard" class="text-blue-600 hover:underline">📊 Dashboard</a>
                <a href="/rm" class="text-blue-600 hover:underline">📋 รายการโปรเจค</a>
                <a href="/rm/create" class="text-green-600 hover:underline">➕ เพิ่มโครงการ</a>
                <a href="{{ route('fte.index') }}" class="text-gray-700 hover:text-blue-600">🔥 FTE</a>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="w-10 h-10 bg-gray-200 rounded-full overflow-hidden focus:outline-none">
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}?v={{ time() }}"
                            onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                            alt="Profile" class="w-full h-full object-cover">
                    </button>

                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-72 bg-white border rounded shadow-md z-50">
                        <div class="px-4 py-2 text-sm text-gray-800 border-b leading-tight">
                            <div class="font-medium">
                                {{ Auth::user()->prefix }} {{ Auth::user()->name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                ตำแหน่ง: {{ Auth::user()->position }} ({{ Auth::user()->role }})
                            </div>
                        </div>

                        @if (Auth::user()->role === 'admin')
                            <a href="/user" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                👥 จัดการผู้ใช้
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                🚪 ออกจากระบบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- ✅ main ก็ใช้ container เช่นกัน -->
    <main class="container mx-auto px-4 py-6">
        @yield('content')
    </main>

</body>
</html>
