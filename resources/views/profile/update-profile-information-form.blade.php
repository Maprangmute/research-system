<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        ข้อมูลโปรไฟล์
    </x-slot>

    <x-slot name="description">
        แก้ไขข้อมูลส่วนตัวและอีเมลสำหรับเข้าสู่ระบบ
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6">
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="flex items-center gap-6">
                        <!-- Current Profile Photo -->
                        <div class="mt-2" x-show="! photoPreview">
                            <img src="{{ $this->user->profile_photo_path ? asset('storage/' . $this->user->profile_photo_path) : asset('images/default-avatar.png') }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                alt="{{ $this->user->name }}"
                                class="rounded-full w-32 h-32 object-cover ring-4 ring-blue-100 shadow-sm">
                        </div>

                        <div>
                            <x-label for="photo" value="รูปโปรไฟล์" />
                            <p class="text-xs text-gray-500 mt-1">ติดต่อผู้ดูแลระบบเพื่อเปลี่ยนรูปโปรไฟล์</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Name (readonly) -->
        <div class="col-span-6 sm:col-span-3">
            <x-label for="name" value="ชื่อ-นามสกุล" />
            <div class="mt-1 w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700">
                {{ $this->user->name }}
            </div>
            <input id="name" type="hidden" wire:model="state.name" />
            <p class="mt-2 text-xs text-gray-500">ระบบไม่อนุญาตให้แก้ไขชื่อ-นามสกุล</p>
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-3">
            <x-label for="email" value="อีเมล" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="text-sm mt-2">
                    อีเมลนี้ยังไม่ได้ยืนยัน

                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" wire:click.prevent="sendEmailVerification">
                        คลิกเพื่อส่งอีเมลยืนยันอีกครั้ง
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600">
                        ระบบได้ส่งลิงก์ยืนยันใหม่ไปยังอีเมลของคุณแล้ว
                    </p>
                @endif
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            บันทึกแล้ว
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            บันทึก
        </x-button>
    </x-slot>
</x-form-section>
