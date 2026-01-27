<x-action-section>
    <x-slot name="title">
        ลบบัญชีผู้ใช้
    </x-slot>

    <x-slot name="description">
        ลบบัญชีของคุณอย่างถาวร
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            เมื่อบัญชีถูกลบ ข้อมูลทั้งหมดจะถูกลบถาวร โปรดสำรองข้อมูลที่ต้องการเก็บไว้ก่อนดำเนินการ
        </div>

        <div class="mt-5">
            <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                ลบบัญชี
            </x-danger-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                ลบบัญชี
            </x-slot>

            <x-slot name="content">
                คุณแน่ใจหรือไม่ว่าต้องการลบบัญชี เมื่อยืนยันแล้วข้อมูลทั้งหมดจะถูกลบถาวร กรุณากรอกรหัสผ่านเพื่อยืนยัน

                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4"
                                autocomplete="current-password"
                                placeholder="รหัสผ่าน"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="deleteUser" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
