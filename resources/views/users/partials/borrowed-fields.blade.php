@php
    $borrowedStatusRaw = old('is_borrowed', isset($user) && $user->is_borrowed ? '1' : '0');
    $isBorrowedSelected = filter_var($borrowedStatusRaw, FILTER_VALIDATE_BOOLEAN);
    $departmentValue = old('borrowed_department', $user->borrowed_department ?? '');
    $labelClass = $labelClass ?? 'block text-sm text-gray-700 font-medium';
    $inputClass = $inputClass ?? 'w-full border p-2 rounded';
    $textClass = $textClass ?? 'text-sm';
@endphp

<div class="space-y-2" data-borrowed-fields>
    <div>
        <label class="{{ $labelClass }}">สถานะการยืมตัว</label>
        <select name="is_borrowed" class="{{ $inputClass }} {{ $textClass }}" data-borrowed-status>
            <option value="0" @selected(! $isBorrowedSelected)>ไม่ยืมตัว</option>
            <option value="1" @selected($isBorrowedSelected)>ยืมตัว</option>
        </select>
    </div>

    <div class="{{ $isBorrowedSelected ? '' : 'hidden' }}" data-borrowed-department-wrap>
        <label class="{{ $labelClass }}">ส่วนงานต้นสังกัด</label>
        <div class="relative" data-borrowed-department-dropdown>
            <input
                type="hidden"
                name="borrowed_department"
                value="{{ $departmentValue }}"
                data-borrowed-department-value
                @disabled(! $isBorrowedSelected)>
            <button
                type="button"
                class="{{ $inputClass }} {{ $textClass }} bg-white text-left flex items-center justify-between gap-2 focus:border-purple-300 focus:ring-purple-200 focus:ring-2"
                data-borrowed-department-toggle>
                <span class="truncate" data-borrowed-department-label>{{ $departmentValue ?: 'เลือกส่วนงานต้นสังกัด' }}</span>
                <span class="text-gray-400">▾</span>
            </button>
            <div
                class="hidden absolute z-40 mt-1 w-full rounded-lg border border-gray-200 bg-white p-2 shadow-lg"
                data-borrowed-department-menu>
                <input
                    type="text"
                    class="w-full border border-gray-200 rounded-lg p-2 text-sm"
                    placeholder="พิมพ์ค้นหาส่วนงาน..."
                    data-borrowed-department-search>
                <div class="mt-2 max-h-64 overflow-y-auto space-y-1" data-borrowed-department-options>
                    @foreach (\App\Models\User::BORROWED_DEPARTMENTS as $department)
                        <button
                            type="button"
                            class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-purple-50"
                            data-borrowed-department-option
                            data-value="{{ $department }}">
                            {{ $department }}
                        </button>
                    @endforeach
                    <div class="hidden px-2 py-1.5 text-xs text-gray-500" data-borrowed-department-empty>ไม่พบข้อมูล</div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        (() => {
            const queryAll = (selector, root = document) => Array.from(root.querySelectorAll(selector));
            const placeholder = 'เลือกส่วนงานต้นสังกัด';

            const closeAllBorrowedDepartmentMenus = (except = null) => {
                queryAll('[data-borrowed-department-menu]').forEach((menu) => {
                    if (except && menu === except) return;
                    menu.classList.add('hidden');
                });
            };

            const setDepartmentLabel = (dropdown) => {
                const valueField = dropdown.querySelector('[data-borrowed-department-value]');
                const label = dropdown.querySelector('[data-borrowed-department-label]');
                if (!valueField || !label) return;
                const selected = queryAll('[data-borrowed-department-option]', dropdown)
                    .find((option) => (option.dataset.value || '') === (valueField.value || ''));
                label.textContent = selected ? selected.textContent.trim() : (valueField.value || placeholder);
            };

            const initBorrowedFields = (root) => {
                if (root.dataset.borrowedBound === 'true') return;
                root.dataset.borrowedBound = 'true';

                const status = root.querySelector('[data-borrowed-status]');
                const wrap = root.querySelector('[data-borrowed-department-wrap]');
                const dropdown = root.querySelector('[data-borrowed-department-dropdown]');
                const valueField = root.querySelector('[data-borrowed-department-value]');
                const toggle = root.querySelector('[data-borrowed-department-toggle]');
                const menu = root.querySelector('[data-borrowed-department-menu]');
                const searchInput = root.querySelector('[data-borrowed-department-search]');
                const empty = root.querySelector('[data-borrowed-department-empty]');
                const options = queryAll('[data-borrowed-department-option]', root);
                if (!status || !wrap || !dropdown || !valueField || !toggle || !menu || !searchInput || !options.length) return;

                const filterOptions = () => {
                    const term = (searchInput.value || '').toLowerCase().trim();
                    let visible = 0;
                    options.forEach((option) => {
                        const show = term === '' || (option.textContent || '').toLowerCase().includes(term);
                        option.classList.toggle('hidden', !show);
                        if (show) visible += 1;
                    });
                    empty?.classList.toggle('hidden', visible > 0);
                };

                const syncStatus = () => {
                    const isBorrowed = status.value === '1';
                    wrap.classList.toggle('hidden', !isBorrowed);
                    valueField.disabled = !isBorrowed;
                    valueField.required = isBorrowed;
                    if (!isBorrowed) {
                        valueField.value = '';
                        menu.classList.add('hidden');
                    }
                    setDepartmentLabel(dropdown);
                };

                toggle.addEventListener('click', () => {
                    if (valueField.disabled) return;
                    const willOpen = menu.classList.contains('hidden');
                    closeAllBorrowedDepartmentMenus(willOpen ? menu : null);
                    menu.classList.toggle('hidden', !willOpen);
                    if (willOpen) {
                        searchInput.value = '';
                        filterOptions();
                        searchInput.focus();
                    }
                });

                searchInput.addEventListener('input', filterOptions);
                status.addEventListener('change', syncStatus);

                options.forEach((option) => {
                    option.addEventListener('click', () => {
                        valueField.value = option.dataset.value || '';
                        setDepartmentLabel(dropdown);
                        menu.classList.add('hidden');
                    });
                });

                syncStatus();
            };

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-borrowed-department-dropdown]')) {
                    closeAllBorrowedDepartmentMenus();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAllBorrowedDepartmentMenus();
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                queryAll('[data-borrowed-fields]').forEach(initBorrowedFields);
            });
        })();
    </script>
@endonce
