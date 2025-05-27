@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" />

    @if (session('error'))
        <script>
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 7000,
                timerProgressBar: true
            });
        </script>
    @endif

    <div class="container mx-auto px-4 mt-11">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">📊 รายการโครงการวิจัย</h2>
            <a href="{{ route('projects.create') }}"
                class="px-4 py-2 bg-purple-600 text-white text-sm rounded hover:bg-purple-700">
                ➕ เพิ่มโครงการ
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('projects.index') }}"
            class="bg-white p-4 rounded-lg shadow mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 w-full">
            <input type="text" name="search" placeholder="ค้นหาชื่อโครงการ" value="{{ request('search') }}"
                class="w-full border p-2 rounded">
            <input type="text" name="type" placeholder="ประเภทโครงการ" value="{{ request('type') }}"
                class="w-full border p-2 rounded">
            <input type="text" name="year" placeholder="ปี (พ.ศ.)" value="{{ request('year') }}"
                class="w-full border p-2 rounded">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded w-full md:w-auto">🔍 ค้นหา</button>
        </form>

        <!-- Table -->
        <div class="bg-white shadow rounded-lg overflow-auto">
            <table class="min-w-full table-fixed divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-12 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="w-32 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">PROJECT ID</th>
                        <th class="w-1/3 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ชื่อโครงการ</th>
                        <th class="w-1/4 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ประเภท</th>
                        <th class="w-1/4 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Theme</th>
                        <th class="w-20 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ปี</th>
                        <th class="w-1/6 px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse ($projects as $project)
                        <tr>
                            <td class="px-4 py-2">{{ $project->id }}</td>
                            <td class="px-4 py-2">{{ $project->project_code }}</td>
                            <td class="px-4 py-2 whitespace-normal break-words">
                                <a href="{{ route('projects.show', $project->id) }}" class="text-blue-600 hover:underline">
                                    {{ $project->project_name_th }}
                                </a>
                            </td>
                            <td class="px-4 py-2">{{ $project->project_type }}</td>
                            <td class="px-4 py-2 whitespace-normal break-words">{{ $project->theme }}</td>
                            <td class="px-4 py-2">{{ $project->start_year }}</td>
                            <td class="px-4 py-2 text-right space-x-4">
                                <a href="{{ route('projects.edit', $project->id) }}"
                                    class="text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 transition-all duration-200 ease-in-out px-2 py-1 rounded cursor-pointer inline-block transform hover:scale-105">
                                    ✏️ แก้ไข
                                </a>

                                <button type="button"
                                    onclick="confirmDelete({{ $project->id }}, '{{ $project->project_name_th }}')"
                                    class="text-red-600 hover:text-red-800 hover:bg-red-50 transition-all duration-200 ease-in-out px-2 py-1 rounded cursor-pointer inline-block transform hover:scale-105">
                                    🗑️ ลบ
                                </button>

                                <form id="delete-form-{{ $project->id }}"
                                    action="{{ route('projects.destroy', $project->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    </div>

    <script>
        function confirmDelete(id, projectName) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "คุณกำลังจะลบโครงการ: " + projectName,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection
