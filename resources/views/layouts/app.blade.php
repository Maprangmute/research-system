<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Research Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="font-bold">Research Dashboard</div>

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

      onReady(_, __, inst) {
        syncAltStyle(input, inst);
        forceHeaderBE(inst);
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
</style>

<script>
(function () {

  function updateIndex(container) {
    container.querySelectorAll('[data-repeatable-index]')
      .forEach((el, i) => el.textContent = i + 1);
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

    container.appendChild(row);
    updateIndex(container);
    highlightRow(row);

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
    document.querySelectorAll('[data-repeatable]').forEach(group => {
      const container = document.getElementById(group.dataset.container);
      updateIndex(container);
      container.querySelectorAll('[data-repeatable-row]')
        .forEach(row => bindRemove(row));
    });
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

  // console.log('hr=', hr, 'fte(raw)=', fte, 'fte(fixed)=', fte.toFixed(5));

  fteInput.value = fte.toFixed(8);
});
