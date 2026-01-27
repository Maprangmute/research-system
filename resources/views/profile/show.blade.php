<x-app-layout>
    <x-slot name="header">
        {{-- <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            แก้ไขข้อมูลส่วนตัว
        </h2> --}}
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-8">
        <div class="bg-gradient-to-r from-blue-50 to-sky-50 border border-blue-100 rounded-xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="text-sm text-blue-600 font-semibold">โปรไฟล์ผู้ใช้งาน</div>
                <div class="text-xl font-bold text-gray-800">
                    {{ Auth::user()->prefix }} {{ Auth::user()->name }}
                </div>
                <div class="mt-1 text-sm text-gray-600">ตำแหน่ง: {{ Auth::user()->position }}</div>
                <div class="mt-1 text-sm text-gray-500">ระดับการเข้าถึง: {{ Auth::user()->role }}</div>
            </div>
            <div class="text-xs text-gray-500">
                อัปเดตข้อมูลและรหัสผ่านของคุณในหน้านี้
            </div>
        </div>
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            <div class="bg-white shadow rounded-lg p-6">
                @livewire('profile.update-profile-information-form')
            </div>
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <div class="bg-white shadow rounded-lg p-6">
                @livewire('profile.update-password-form')
            </div>
        @endif

        <div class="bg-white shadow rounded-lg p-6">
            @livewire('profile.logout-other-browser-sessions-form')
        </div>
    </div>
</x-app-layout>
