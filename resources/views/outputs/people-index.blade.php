@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <style>
            .people-shell {
                background:
                    radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 28%),
                    linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            }

            .people-table {
                width: 100%;
                min-width: 1480px;
                table-layout: auto;
            }

            .people-table-scroll {
                max-height: calc(100vh - 180px);
                overflow: auto;
            }

            .people-table th {
                position: sticky;
                top: 0;
                z-index: 20;
                background: rgba(248, 250, 252, 0.98);
                box-shadow: inset 0 -1px 0 #e2e8f0;
                white-space: nowrap;
                font-size: 12px;
                color: #475569;
                letter-spacing: 0.02em;
            }

            .people-table td {
                vertical-align: top;
            }

            .people-table tbody tr {
                transition: background-color 0.18s ease;
            }

            .people-table tbody tr:hover {
                background: #f8fbff;
            }

            .people-person-cell {
                min-width: 320px;
            }

            .people-search-actions {
                flex: 0 0 auto;
            }

            @media (max-width: 768px) {
                .people-shell {
                    background: #fff;
                }

                .people-table-scroll {
                    max-height: none;
                    overflow-x: visible;
                    overflow-y: visible;
                }

                .people-table,
                .people-table thead,
                .people-table tbody,
                .people-table tr,
                .people-table td {
                    display: block;
                    width: 100%;
                }

                .people-table {
                    min-width: 0;
                }

                .people-table thead {
                    display: none;
                }

                .people-table th {
                    position: static;
                    box-shadow: none;
                }

                .people-table tbody {
                    padding: 0.75rem;
                }

                .people-table tbody tr {
                    margin-bottom: 0.9rem;
                    border: 1px solid #e2e8f0;
                    border-radius: 18px;
                    background: #fff;
                    overflow: hidden;
                }

                .people-table tbody tr:last-child {
                    margin-bottom: 0;
                }

                .people-table td {
                    padding: 0.9rem 1rem;
                    border-top: 1px solid #e2e8f0;
                }

                .people-table td:first-child {
                    border-top: 0;
                }

                .people-table td[data-label]::before {
                    content: attr(data-label);
                    display: block;
                    margin-bottom: 0.35rem;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                    color: #64748b;
                }

                .people-person-cell {
                    min-width: 0;
                }
            }
        </style>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold text-slate-500">Outputs รายบุคคล</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900 md:text-3xl">รายชื่อบุคลากรและผลงาน</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">สรุปบทบาทในโครงการและ output หลักของแต่ละคน</p>
                </div>

                <form method="GET" action="{{ route('outputs.people') }}" class="w-full max-w-xl">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input
                            id="peopleSearchInput"
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            autocomplete="off"
                            placeholder="ค้นหาชื่อไทย ชื่ออังกฤษ อีเมล หรือ position"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-100"
                        >
                        <div class="people-search-actions flex gap-2">
                            <button type="submit" class="min-w-20 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                                ค้นหา
                            </button>
                            @if ($search !== '')
                                <a href="{{ route('outputs.people') }}" class="min-w-20 rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900">
                                    ล้าง
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5">
                    จำนวนรายชื่อ <span data-people-visible-count>{{ number_format($people->count()) }}</span> คน
                </span>
                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5">
                    สิทธิ์การดู: {{ $canViewAll ? 'ดูได้ทุกคน' : 'ดูได้เฉพาะตัวเอง' }}
                </span>
            </div>
        </section>

        <section class="people-shell overflow-hidden rounded-2xl border border-slate-200 bg-white">
            @if ($people->isEmpty())
                <div class="p-8 text-center text-slate-500">
                    ไม่พบรายชื่อที่ตรงกับเงื่อนไข
                </div>
            @else
                <div class="people-table-scroll">
                    <table class="people-table text-sm">
                        <thead class="bg-slate-50/90">
                            <tr>
                                <th class="w-[28%] px-5 py-4 text-left font-semibold">บุคลากร</th>
                                <th class="w-[14%] px-4 py-4 text-left font-semibold">Projects</th>
                                <th class="w-[10%] px-4 py-4 text-left font-semibold">Research</th>
                                <th class="w-[10%] px-4 py-4 text-left font-semibold">Presentation</th>
                                <th class="w-[10%] px-4 py-4 text-left font-semibold">Textbook</th>
                                <th class="w-[10%] px-4 py-4 text-left font-semibold">Award</th>
                                <th class="w-[8%] px-4 py-4 text-left font-semibold">อื่นๆ</th>
                                <th class="w-[10%] px-5 py-4 text-left font-semibold">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($people as $person)
                                @php
                                    $user = $person->user;
                                    $stats = $person->stats;
                                    $personRouteKey = $user->full_name;
                                @endphp
                                <tr class="border-t border-slate-200" data-people-row data-search-text="{{ collect([
                                    $person->display_name,
                                    $person->display_name_en,
                                    $user->email,
                                    $user->position,
                                    $user->role,
                                    $stats['total_projects'] ?? 0,
                                    $stats['research_outputs'] ?? 0,
                                    $stats['presentation_outputs'] ?? 0,
                                    $stats['textbook_outputs'] ?? 0,
                                    $stats['award_outputs'] ?? 0,
                                    $stats['other_outputs'] ?? 0,
                                ])->filter(fn ($value) => filled($value))->implode(' ') }}">
                                    <td class="people-person-cell px-5 py-4" data-label="บุคลากร">
                                        <div class="flex items-start gap-3">
                                            <img
                                                src="{{ $person->profile_photo_url }}"
                                                alt="{{ $person->display_name }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                                class="h-12 w-12 rounded-2xl border border-slate-200 object-cover"
                                            >
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="font-semibold text-slate-900">{{ $person->display_name }}</p>
                                                </div>
                                                @if ($person->display_name_en)
                                                    <p class="mt-1 text-xs text-slate-500">{{ $person->display_name_en }}</p>
                                                @endif
                                                @if (filled($user->position))
                                                    <p class="mt-1 text-xs text-slate-600">{{ $user->position }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-700" data-label="Projects">
                                        <p class="text-lg font-bold text-slate-900">{{ number_format($stats['total_projects']) }}</p>
                                        <div class="mt-2 space-y-1 text-xs text-slate-500">
                                            <p>PI {{ $stats['pi_projects'] }}</p>
                                            <p>Co-PI {{ $stats['co_pi_projects'] }}</p>
                                            <p>Co-Researcher {{ $stats['co_research_projects'] }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4" data-label="Research">
                                        <p class="text-lg font-bold text-slate-900">{{ number_format($stats['research_outputs']) }}</p>
                                        <p class="mt-2 text-xs text-slate-500">ผลงานวิจัย / paper</p>
                                    </td>
                                    <td class="px-4 py-4" data-label="Presentation">
                                        <p class="text-lg font-bold text-slate-900">{{ number_format($stats['presentation_outputs']) }}</p>
                                        <p class="mt-2 text-xs text-slate-500">การนำเสนอวิชาการ</p>
                                    </td>
                                    <td class="px-4 py-4" data-label="Textbook">
                                        <p class="text-lg font-bold text-slate-900">{{ number_format($stats['textbook_outputs']) }}</p>
                                        <p class="mt-2 text-xs text-slate-500">ผลงานหนังสือ / บทในหนังสือ</p>
                                    </td>
                                    <td class="px-4 py-4" data-label="Award">
                                        <p class="text-lg font-bold text-slate-900">{{ number_format($stats['award_outputs']) }}</p>
                                        <p class="mt-2 text-xs text-slate-500">รางวัลและการยกย่อง</p>
                                    </td>
                                    <td class="px-4 py-4" data-label="อื่นๆ">
                                        <p class="text-lg font-bold text-slate-900">{{ number_format($stats['other_outputs'] ?? 0) }}</p>
                                        <p class="mt-2 text-xs text-slate-500">อื่นๆ</p>
                                    </td>
                                    <td class="px-5 py-4" data-label="จัดการ">
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ route('outputs.people.show', $personRouteKey) }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                                                ดูรายละเอียด
                                            </a>
                                            <a href="{{ route('outputs.people.pdf', $personRouteKey) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900">
                                                Export PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="hidden border-t border-slate-200" data-people-empty-row>
                                <td colspan="8" class="px-5 py-8 text-center text-slate-500">
                                    ไม่พบรายชื่อที่ตรงกับเงื่อนไข
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <script>
            (() => {
                const input = document.getElementById('peopleSearchInput');
                const rows = Array.from(document.querySelectorAll('[data-people-row]'));
                const countBadge = document.querySelector('[data-people-visible-count]');
                const emptyRow = document.querySelector('[data-people-empty-row]');
                if (!input || rows.length === 0) return;

                const normalize = (value) => String(value || '').toLowerCase().replace(/เเ/g, 'แ').replace(/\s+/g, ' ').trim();
                const rowTexts = new Map(rows.map((row) => [row, normalize(row.dataset.searchText)]));

                const applyFilter = () => {
                    const tokens = normalize(input.value).split(' ').filter(Boolean);
                    let visible = 0;

                    rows.forEach((row) => {
                        const haystack = rowTexts.get(row) || '';
                        const matched = tokens.length === 0 || tokens.every((token) => haystack.includes(token));
                        row.classList.toggle('hidden', !matched);
                        if (matched) visible += 1;
                    });

                    if (countBadge) {
                        countBadge.textContent = new Intl.NumberFormat('th-TH').format(visible);
                    }

                    emptyRow?.classList.toggle('hidden', visible > 0);
                };

                input.addEventListener('input', applyFilter);
                applyFilter();
            })();
        </script>
    </div>
@endsection
