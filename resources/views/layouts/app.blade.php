<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Research Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireStyles
<script src="//unpkg.com/alpinejs" defer></script>



    <!-- ✅ jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ jQuery Gantt Plugin (Full) -->
<link href="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/css/style.css" rel="stylesheet"/>
<script src="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/js/jquery.fn.gantt.js"></script>

<!-- CSS + JS DHTMLX Gantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>



</head>

<body class="bg-gray-100 text-gray-800">

    <!-- ✅ เปลี่ยนจาก max-w-7xl เป็น container -->
    <nav class="bg-white shadow" x-data="{ openNav: false }">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between gap-4">
                <div class="font-bold">Research Dashboard</div>

                <div class="hidden md:flex items-center gap-4">
                    <a href="/dashboard" class="text-blue-600 hover:underline">📊 Dashboard</a>
                    <a href="/rm" class="text-blue-600 hover:underline">📋 รายการโปรเจค</a>
                    <a href="{{ route('back_office.index') }}" class="text-blue-600 hover:underline">🧾 Back Office</a>
                    <a href="/rm/create" class="text-green-600 hover:underline">➕ เพิ่มโครงการ</a>
                    <a href="{{ route('outputs.index') }}" class="text-green-600 hover:underline">📑 outputs</a>
                    <div class="relative" x-data="{ openFte: false, fteTimer: null }">
                        <button type="button"
                            @click="openFte = !openFte"
                            @mouseenter="clearTimeout(fteTimer); fteTimer = setTimeout(() => { openFte = true; }, 180);"
                            @mouseleave="clearTimeout(fteTimer); fteTimer = setTimeout(() => { openFte = false; }, 280);"
                            class="text-gray-700 hover:text-blue-600 inline-flex items-center gap-1">
                            🔥 FTE
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div x-show="openFte" x-cloak
                            @mouseenter="clearTimeout(fteTimer); openFte = true;"
                            @mouseleave="clearTimeout(fteTimer); fteTimer = setTimeout(() => { openFte = false; }, 280);"
                            class="absolute left-0 mt-2 w-52 bg-white border rounded shadow-md z-50">
                            <a href="{{ route('fte.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                FTE รายบุคคล
                            </a>
                            <a href="{{ route('fte.project.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                FTE รายโปรเจค
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
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
                            <a href="{{ route('user_guide') }}" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">📘 คู่มือ</a>

                            @if (Auth::user()->role === 'admin')
                                <a href="/user" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                    👥 จัดการผู้ใช้
                                </a>
                            @endif

                           @if (Auth::user()->role === 'admin')
                                <a href="/project-logs" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                    📝 Project log
                                </a>
                            @endif
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                👤 แก้ไขข้อมูลส่วนตัว
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    🚪 ออกจากระบบ
                                </button>
                            </form>
                        </div>
                    </div>

                    <button type="button" class="md:hidden p-2 rounded-lg border text-gray-600 hover:bg-gray-50"
                        @click="openNav = !openNav" aria-label="Toggle navigation">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="md:hidden" x-show="openNav" x-cloak>
                <div class="pt-3 pb-2 border-t mt-3 flex flex-col gap-2">
                    <a href="/dashboard" class="text-blue-600 hover:underline">📊 Dashboard</a>
                    <a href="/rm" class="text-blue-600 hover:underline">📋 รายการโปรเจค</a>
                    <a href="{{ route('back_office.index') }}" class="text-blue-600 hover:underline">🧾 Back Office</a>
                    <a href="/rm/create" class="text-green-600 hover:underline">➕ เพิ่มโครงการ</a>
                    <a href="{{ route('outputs.index') }}" class="text-green-600 hover:underline">📑 outputs</a>
                    <div class="text-gray-700 font-medium">🔥 FTE</div>
                    <a href="{{ route('fte.index') }}" class="text-gray-600 hover:text-blue-600">FTE รายบุคคล</a>
                    <a href="{{ route('fte.project.index') }}" class="text-gray-600 hover:text-blue-600">FTE รายโปรเจค</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ✅ main ก็ใช้ container เช่นกัน -->
    <main class="container mx-auto px-4 py-6">
        @if (isset($header))
            <div class="mb-4">{{ $header }}</div>
        @endif
        @if (isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main>

    @livewireScripts
</body>
</html>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<script>
/* =========================================================
   DATE-BE + REPEATABLE (FINAL / PRODUCTION SAFE)
   ========================================================= */

/* ---------- DATE BE INIT ---------- */
function initDateBE(scope = document) {

  scope.querySelectorAll('.js-date-be').forEach(input => {

    // ❗ กัน init ซ้ำ
    if (input._flatpickr) return;

    const fp = flatpickr(input, {
      locale: 'th',
      dateFormat: 'd-m-Y',   // ✅ ค่าจริง (ค.ศ.)
      altInput: true,
      altFormat: 'j F Y',    // ใช้ตอน init เท่านั้น
      disableMobile: true,

      onReady(selectedDates, __, inst) {
        syncAltStyle(input, inst);
        forceHeaderBE(inst);
        if (inst.altInput && selectedDates && selectedDates[0]) {
          const d = selectedDates[0];
          const day   = d.getDate();
          const month = d.toLocaleDateString('th-TH', { month: 'long' });
          const year  = d.getFullYear() + 543;
          inst.altInput.value = `${day} ${month} ${year}`;
        }
      },

      onOpen(_, __, inst) {
        forceHeaderBE(inst);
      },

      onMonthChange(_, __, inst) {
        setTimeout(() => forceHeaderBE(inst), 0);
      },

      onYearChange(_, __, inst) {
        setTimeout(() => forceHeaderBE(inst), 0);
      },

      // ✅ จุดสำคัญอยู่ตรงนี้
      onChange(selectedDates, dateStr, inst) {
        forceHeaderBE(inst);

        if (!inst.altInput || !selectedDates[0]) return;

        const d = selectedDates[0];

        const day   = d.getDate();
        const month = d.toLocaleDateString('th-TH', { month: 'long' });
        const year  = d.getFullYear() + 543;

        // 🔥 บังคับค่าแสดงผลเป็น พ.ศ.
        inst.altInput.value = `${day} ${month} ${year}`;
      }
    });

    /* ✅ ปุ่ม 📅 เปิด input ของตัวเอง */
    const btn = scope.querySelector(
      `[data-open-picker][data-target="${input.id}"]`
    );

    if (btn) {
      btn.addEventListener('click', () => fp.open());
    }
  });
}


/* ---------- ทำให้ altInput หน้าตาเหมือน input เดิม ---------- */
function syncAltStyle(originalInput, inst) {
  if (!inst.altInput) return;

  // copy class
  inst.altInput.className = originalInput.className;

  // placeholder
  const ph = originalInput.getAttribute('placeholder') || originalInput.dataset.placeholder;
  if (ph) inst.altInput.placeholder = ph;

  inst.altInput.readOnly = true;
}

/* ---------- บังคับ header เป็น พ.ศ. ทุกกรณี ---------- */
function forceHeaderBE(inst) {
  const cal = inst.calendarContainer;
  if (!cal) return;

  // year input
  const yearInput = cal.querySelector('.flatpickr-current-month input.cur-year');
  if (yearInput) {
    const ce = parseInt(yearInput.value || '0', 10);
    if (ce && ce < 2400) yearInput.value = ce + 543;

    if (!yearInput._beBound) {
      yearInput._beBound = true;
      yearInput.addEventListener('blur', () => {
        const be = parseInt(yearInput.value || '0', 10);
        if (be > 2400) {
          inst.currentYear = be - 543;
          inst.redraw();
          setTimeout(() => forceHeaderBE(inst), 0);
        }
      });
    }
  }

  // prev / next
  ['.flatpickr-prev-month','.flatpickr-next-month'].forEach(sel => {
    const btn = cal.querySelector(sel);
    if (!btn || btn._beBound) return;
    btn._beBound = true;
    btn.addEventListener('click', () =>
      setTimeout(() => forceHeaderBE(inst), 0)
    );
  });
}
</script>

<style>
/* highlight แถวที่เพิ่มล่าสุด */
.repeatable-highlight {
  animation: flash-bg 1.2s ease-out;
  background-color: #eff6ff;
}
@keyframes flash-bg {
  0% { background-color: #dbeafe; }
  100% { background-color: transparent; }
}

/* collapse rows when adding new entries */
.row-collapsible {
  position: relative;
}
.row-collapse-toggle {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  z-index: 5;
  font-size: 0.75rem;
  line-height: 1rem;
  color: #1d4ed8;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  padding: 0.15rem 0.6rem;
  border-radius: 9999px;
}
.row-collapsed > :not(.row-collapse-toggle) {
  display: none !important;
}
.row-collapsed > .row-collapse-summary {
  display: block !important;
}
.row-collapse-summary {
  width: 100%;
  display: none;
  white-space: normal;
  overflow: hidden;
  text-overflow: clip;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  word-break: break-word;
  padding-right: 6.5rem;
  grid-column: 1 / -1;
}
.row-collapse-summary .summary-chip {
  display: inline-flex;
  align-items: center;
  max-width: 100%;
  overflow-wrap: anywhere;
  margin-right: 0.75rem;
  color: #0f172a;
}
.row-collapse-summary .summary-muted {
  color: #64748b;
}
.row-collapsed {
  min-height: 2.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  background: #f8fafc;
  box-sizing: border-box;
}

/* project create/edit modern buttons */
.project-form button[data-repeatable-add],
.project-form .add-author-btn,
.project-form .add-conf-btn,
.project-form button[onclick*="addIRBCode"],
.project-form button[onclick*="addCopiRow"],
.project-form button[onclick*="addCoResearchRow"],
.project-form button[onclick*="addFTERow"],
.project-form button[onclick*="addOutputRow"] {
  border-radius: 0.75rem;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.project-form button[data-repeatable-add]:hover,
.project-form .add-author-btn:hover,
.project-form .add-conf-btn:hover,
.project-form button[onclick*="addIRBCode"]:hover,
.project-form button[onclick*="addCopiRow"]:hover,
.project-form button[onclick*="addCoResearchRow"]:hover,
.project-form button[onclick*="addFTERow"]:hover,
.project-form button[onclick*="addOutputRow"]:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
}

.project-form button[type="submit"] {
  border-radius: 0.9rem;
  box-shadow: 0 10px 26px rgba(37, 99, 235, 0.2);
}
.row-collapse-summary {
  padding: 0.4rem 0.8rem;
}

@media (max-width: 640px) {
  .row-collapse-summary {
    padding-right: 3.5rem;
  }
  .row-collapse-toggle {
    top: 0.35rem;
    right: 0.35rem;
  }
}
</style>

<script>
(function () {
  const COLLAPSIBLE_SELECTOR = '[data-repeatable-row], .irb-row, .copi-row, .coresearch-row, .fte-row, .output-row';

  function getSiblings(row) {
    if (!row) return [];
    const repeatable = row.closest('[data-repeatable]');
    if (repeatable) {
      const container = document.getElementById(repeatable.dataset.container);
      if (!container) return [];
      return Array.from(container.querySelectorAll(COLLAPSIBLE_SELECTOR));
    }

    const customContainer = row.closest(
      '#irb-code-container, #copi-container, #coresearch-container, #fte-container, #output-container'
    );
    if (customContainer) {
      return Array.from(customContainer.querySelectorAll(COLLAPSIBLE_SELECTOR));
    }

    const parent = row.parentElement;
    if (!parent) return [];
    return Array.from(parent.querySelectorAll(COLLAPSIBLE_SELECTOR))
      .filter(el => el.parentElement === parent);
  }

  function updateToggleLabel(row) {
    const btn = row.querySelector('.row-collapse-toggle');
    if (!btn) return;
    const isCollapsed = row.dataset.collapsed === '1';
    btn.textContent = isCollapsed ? 'ขยายแถว' : 'ย่อแถว';
  }

  function setCollapsed(row, collapsed) {
    updateSummary(row);
    row.dataset.collapsed = collapsed ? '1' : '0';
    row.classList.toggle('row-collapsed', collapsed);
    updateToggleLabel(row);
  }

  function ensureToggle(row) {
    if (row.querySelector('.row-collapse-toggle')) return;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'row-collapse-toggle';
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      setCollapsed(row, row.dataset.collapsed !== '1');
    });
    row.classList.add('row-collapsible');
    row.appendChild(btn);
    ensureSummary(row);
    updateToggleLabel(row);
  }

  function ensureSummary(row) {
    if (row.querySelector('.row-collapse-summary')) return;
    const summary = document.createElement('div');
    summary.className = 'row-collapse-summary text-sm text-slate-700 mt-2 hidden';
    row.appendChild(summary);
  }

  function getFieldValue(row, selectors) {
    for (const sel of selectors) {
      const el = row.querySelector(sel);
      if (!el) continue;
      if (el._flatpickr?.altInput?.value) {
        const altVal = el._flatpickr.altInput.value.trim();
        if (altVal) return altVal;
      }
      if (el.tagName === 'SELECT') {
        const text = el.options[el.selectedIndex]?.textContent?.trim();
        if (text) return text;
      }
      const value = el.value?.trim();
      if (value) return value;
    }
    return '';
  }

  function hasName(row, matcher) {
    return Array.from(row.querySelectorAll('[name]')).some(el => matcher(el.name, el));
  }

  function getValueFromEl(el) {
    if (!el) return '';
    if (el._flatpickr?.altInput?.value) {
      const altVal = el._flatpickr.altInput.value.trim();
      if (altVal) return altVal;
    }
    if (el.tagName === 'SELECT') {
      const text = el.options[el.selectedIndex]?.textContent?.trim();
      if (text) return text;
    }
    return (el.value ?? '').toString().trim();
  }

  function matchKey(name) {
    const m = name.match(/^([a-zA-Z_]+)\[(\d+)\]\[([^\]]+)\]/);
    if (!m) return null;
    return { root: m[1], index: m[2], field: m[3] };
  }

  function getIndexFromId(row, prefix) {
    const id = row.getAttribute('id') || '';
    const m = id.match(new RegExp(`^${prefix}-(\\d+)$`));
    return m ? m[1] : null;
  }

  function getGroupValues(row, root, index) {
    const result = {};
    row.querySelectorAll('[name]').forEach(el => {
      const meta = matchKey(el.name);
      if (!meta || meta.root !== root || meta.index !== index) return;
      result[meta.field] = getValueFromEl(el);
    });
    return result;
  }

  function getFieldValueByName(row, matcher) {
    const els = row.querySelectorAll('[name]');
    for (const el of els) {
      if (matcher(el.name, el)) {
        const val = getValueFromEl(el);
        if (val) return val;
      }
    }
    return '';
  }

  function updateSummary(row) {
    const summary = row.querySelector('.row-collapse-summary');
    if (!summary) return;

    const siblings = getSiblings(row);
    const index = Math.max(1, siblings.indexOf(row) + 1);
    const prefix = `#${index}`;

    if (row.classList.contains('irb-row') || hasName(row, (n) => n.startsWith('irbs['))) {
      let idx = getIndexFromId(row, 'irb');
      if (idx === null) {
        row.querySelectorAll('[name]').forEach(el => {
          const meta = matchKey(el.name);
          if (meta && meta.root === 'irbs') idx = meta.index;
        });
      }
      const values = idx !== null ? getGroupValues(row, 'irbs', idx) : {};
      const code = values.code || '-';
      const source = values.source || '-';
      const exp = values.exp_date || '-';
      summary.innerHTML = `
        <span class="summary-chip">${prefix} • IRB รหัส ${code}</span>
        <span class="summary-chip summary-muted">หน่วยงาน ${source}</span>
        <span class="summary-chip summary-muted">หมดอายุ ${exp}</span>
      `;
      return;
    }

    if (row.classList.contains('copi-row') || hasName(row, (n) => n.startsWith('copis['))) {
      let idx = getIndexFromId(row, 'copi');
      if (idx === null) {
        row.querySelectorAll('[name]').forEach(el => {
          const meta = matchKey(el.name);
          if (meta && meta.root === 'copis') idx = meta.index;
        });
      }
      const values = idx !== null ? getGroupValues(row, 'copis', idx) : {};
      const name = values.name || 'ไม่ระบุชื่อ';
      const aff = values.affiliation || '-';
      summary.innerHTML = `
        <span class="summary-chip">${prefix} • COPI ชื่อ ${name}</span>
        <span class="summary-chip summary-muted">สังกัด ${aff}</span>
      `;
      return;
    }

    if (row.classList.contains('coresearch-row') || hasName(row, (n) => n.startsWith('coresearch['))) {
      let idx = getIndexFromId(row, 'coresearch');
      if (idx === null) {
        row.querySelectorAll('[name]').forEach(el => {
          const meta = matchKey(el.name);
          if (meta && meta.root === 'coresearch') idx = meta.index;
        });
      }
      const values = idx !== null ? getGroupValues(row, 'coresearch', idx) : {};
      const name = values.name || 'ไม่ระบุชื่อ';
      const aff = values.affiliation || '-';
      const status = values.unit_status || '-';
      summary.innerHTML = `
        <span class="summary-chip">${prefix} • ${name}</span>
        <span class="summary-chip summary-muted">${aff}</span>
        <span class="summary-chip summary-muted">${status}</span>
      `;
      return;
    }

    if (row.classList.contains('fte-row') || hasName(row, (n) => n.startsWith('fte_roles['))) {
      const name = getFieldValueByName(row, (n) => n.startsWith('fte_roles[') && n.endsWith('[name]')) || 'ไม่ระบุชื่อ';
      const task = getFieldValueByName(row, (n) =>
        n.startsWith('fte_roles[') && (n.endsWith('[job_detail]') || n.endsWith('[task_detail]'))
      ) || 'ไม่ระบุภาระงาน';
      const start = getFieldValueByName(row, (n) => n.startsWith('fte_roles[') && n.endsWith('[start_date]'));
      const end = getFieldValueByName(row, (n) => n.startsWith('fte_roles[') && n.endsWith('[end_date]'));
      const fte = getFieldValueByName(row, (n) =>
        n.startsWith('fte_roles[') && (n.endsWith('[fte_value]') || n.endsWith('[fte]'))
      );
      const dateText = (start || end) ? `${start || '-'} ถึง ${end || '-'}` : 'ยังไม่ระบุช่วงเวลา';
      const fteText = fte ? `FTE ${fte}` : 'FTE -';
      summary.innerHTML = `
        <span class="summary-chip">${prefix} • ${name}</span>
        <span class="summary-chip summary-muted">${task}</span>
        <span class="summary-chip summary-muted">${dateText}</span>
        <span class="summary-chip summary-muted">${fteText}</span>
      `;
      return;
    }

    if (row.classList.contains('output-row') || hasName(row, (n) => n.startsWith('outputs['))) {
      const title = getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[title]')) || 'ไม่ระบุชื่อผลงาน';
      const type = getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[type]')) || '-';
      const publish = getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[year]')) || '-';
      const projectOutput = getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[project_output]')) || '';
      summary.innerHTML = `
        <span class="summary-chip">${prefix} • ${title}</span>
        <span class="summary-chip summary-muted">${type}${projectOutput ? ` • ${projectOutput}` : ''}</span>
        <span class="summary-chip summary-muted">เผยแพร่ ${publish}</span>
      `;
      return;
    }

    const name = getFieldValueByName(row, (n) => n.endsWith('[name]') || n.endsWith('[person_name]')) || 'ไม่ระบุชื่อ';
    const task = getFieldValueByName(row, (n) => n.endsWith('[job_detail]') || n.endsWith('[task_detail]')) || 'ไม่ระบุภาระงาน';
    const start = getFieldValueByName(row, (n) => n.endsWith('[start_date]') || n.endsWith('[start]'));
    const end = getFieldValueByName(row, (n) => n.endsWith('[end_date]') || n.endsWith('[end]'));
    const fte = getFieldValueByName(row, (n) => n.endsWith('[fte_value]') || n.endsWith('[fte]'));
    const dateText = (start || end) ? `${start || '-'} ถึง ${end || '-'}` : 'ยังไม่ระบุช่วงเวลา';
    const fteText = fte ? `FTE ${fte}` : 'FTE -';

    summary.innerHTML = `
      <span class="summary-chip">${prefix} • ${name}</span>
      <span class="summary-chip summary-muted">${task}</span>
      <span class="summary-chip summary-muted">${dateText}</span>
      <span class="summary-chip summary-muted">${fteText}</span>
    `;
  }

  function prepareRow(row) {
    if (!row || row.dataset.collapseReady === '1') return;
    row.dataset.collapseReady = '1';
    ensureToggle(row);
    updateSummary(row);
    row.querySelectorAll('input, select, textarea').forEach(el => {
      el.addEventListener('input', () => updateSummary(row));
      el.addEventListener('change', () => updateSummary(row));
      const altInput = el._flatpickr?.altInput;
      if (altInput) {
        altInput.addEventListener('input', () => updateSummary(row));
        altInput.addEventListener('change', () => updateSummary(row));
      }
    });
  }

  document.addEventListener('input', (e) => {
    const row = e.target?.closest?.(COLLAPSIBLE_SELECTOR);
    if (row) updateSummary(row);
  });

  document.addEventListener('change', (e) => {
    const row = e.target?.closest?.(COLLAPSIBLE_SELECTOR);
    if (row) updateSummary(row);
  });

  function rowHasData(row) {
    const fields = row.querySelectorAll('input, select, textarea');
    for (const el of fields) {
      const val = getValueFromEl(el);
      if (val && val !== '-') return true;
    }
    return false;
  }

  document.addEventListener('click', (e) => {
    const row = e.target?.closest?.(COLLAPSIBLE_SELECTOR);
    if (!row) {
      document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(el => setCollapsed(el, true));
      return;
    }
    if (e.target.closest('.row-collapse-toggle')) return;
    if (row.dataset.collapsed === '1') {
      setCollapsed(row, false);
      const siblings = getSiblings(row);
      siblings.forEach(el => {
        if (el !== row) setCollapsed(el, true);
      });
    }
  });

  document.addEventListener('focusin', (e) => {
    const row = e.target?.closest?.(COLLAPSIBLE_SELECTOR);
    if (!row) return;
    if (row.dataset.collapsed === '1') setCollapsed(row, false);
    const siblings = getSiblings(row);
    siblings.forEach(el => {
      if (el !== row) setCollapsed(el, true);
    });
  });

  function collapseSiblings(row) {
    const siblings = getSiblings(row);
    siblings.forEach(el => setCollapsed(el, el !== row));
  }

  function updateIndex(container) {
    container.querySelectorAll('[data-repeatable-index]')
      .forEach((el, i) => el.textContent = i + 1);
    container.querySelectorAll(COLLAPSIBLE_SELECTOR)
      .forEach(row => updateToggleLabel(row));
  }

  function replaceIndex(root, index) {
    root.querySelectorAll('[name]').forEach(el => {
      el.name = el.name.replaceAll('__i__', index);
    });
  }

  function highlightRow(row) {
    row.classList.add('repeatable-highlight');
    setTimeout(() => row.classList.remove('repeatable-highlight'), 1200);
  }

  function confirmRemove(callback) {
    if (window.Swal) {
      Swal.fire({
        icon: 'warning',
        title: 'ยืนยันการลบ',
        text: 'ต้องการลบรายการนี้ใช่หรือไม่?',
        showCancelButton: true,
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#dc2626'
      }).then(r => r.isConfirmed && callback());
    } else {
      if (confirm('ต้องการลบรายการนี้ใช่หรือไม่?')) callback();
    }
  }

  function bindRemove(row) {
    row.querySelectorAll('[data-repeatable-remove]').forEach(btn => {
      btn.onclick = () => {
        const group = row.closest('[data-repeatable]');
        const container = document.getElementById(group.dataset.container);

        confirmRemove(() => {
          row.remove();
          updateIndex(container);
        });
      };
    });
  }

  function addRow(group) {
    const container = document.getElementById(group.dataset.container);
    const tpl = document.getElementById(group.dataset.template);
    let index = parseInt(group.dataset.index || '1', 10);

    if (!container || !tpl) return;

    const fragment = tpl.content.cloneNode(true);
    const row = fragment.querySelector('[data-repeatable-row]');
    if (!row) return;

    replaceIndex(row, index);
    bindRemove(row);
    prepareRow(row);

    container.appendChild(row);
    updateIndex(container);
    highlightRow(row);
    collapseSiblings(row);

    group.dataset.index = index + 1;

    if (window.initDateBE) initDateBE(row);
  }

  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-repeatable-add]');
    if (!btn) return;

    const id = btn.getAttribute('data-repeatable-add');
    const group = document.querySelector(`[data-repeatable][data-id="${id}"]`);
    if (group) addRow(group);
  });

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(row => prepareRow(row));
    document.querySelectorAll('[data-repeatable]').forEach(group => {
      const container = document.getElementById(group.dataset.container);
      updateIndex(container);
      container.querySelectorAll('[data-repeatable-row]')
        .forEach(row => {
          bindRemove(row);
          prepareRow(row);
        });
    });

    // collapse pre-filled rows on edit screens only
    const isEdit = document.querySelector('input[name="_method"][value="PUT"], input[name="_method"][value="PATCH"]');
    if (isEdit) {
      document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(row => {
        if (rowHasData(row)) setCollapsed(row, true);
      });
    }
  });

  const observer = new MutationObserver((mutations) => {
    mutations.forEach(mutation => {
      mutation.addedNodes.forEach(node => {
        if (!(node instanceof HTMLElement)) return;
        if (node.matches && node.matches(COLLAPSIBLE_SELECTOR)) {
          prepareRow(node);
          collapseSiblings(node);
          return;
        }
        node.querySelectorAll?.(COLLAPSIBLE_SELECTOR).forEach(row => {
          prepareRow(row);
          collapseSiblings(row);
        });
      });
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    observer.observe(document.body, { childList: true, subtree: true });
  });

})();
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 🔥 บังคับ init ทุก js-date-be ที่ยังไม่ถูก init
    if (typeof initDateBE === 'function') {
        initDateBE(document);
    }
});
</script>


<script>
/* ===============================
   AUTO FTE (REPEATABLE SAFE)
   สูตร: hours × 0.00745 0.000621
   =============================== */
document.addEventListener('input', function (e) {
  if (!e.target.classList.contains('fte-hours')) return;

  const row = e.target.closest('.fte-row');
  const fteInput = row?.querySelector('.fte-value');
  if (!fteInput) return;

  const hr = parseFloat(e.target.value) || 0;
  // const fte = hr * 0.00745;
  const fte = hr * 0.000621;

  console.log('hr=', hr, 'fte(raw)=', fte, 'fte(fixed)=', fte.toFixed(5));

  fteInput.value = fte.toFixed(8);
});
</script>
