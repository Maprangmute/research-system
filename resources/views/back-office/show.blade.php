@extends('layouts.app')

@section('content')
    @php
        $canManageBackOffice = auth()->user()?->hasRole(\App\Models\User::ROLE_ADMIN) ?? false;
        $formatDateBE = function ($value) {
            if (!$value) {
                return '-';
            }
            try {
                $d = \Carbon\Carbon::createFromFormat('d-m-Y', $value)->locale('th');
                return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
            } catch (\Exception $e) {
                try {
                    $d = \Carbon\Carbon::parse($value)->locale('th');
                    return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
                } catch (\Exception $e) {
                    return $value ?: '-';
                }
            }
        };
        $formatDateTimeThai = function ($value) {
            if (!$value) {
                return '';
            }
            try {
                $d = \Carbon\Carbon::parse($value)->setTimezone('Asia/Bangkok')->locale('th');
                return $d->translatedFormat('j F') . ' ' . ($d->year + 543) . ' ' . $d->format('H:i') . ' น.';
            } catch (\Exception $e) {
                return (string) $value;
            }
        };
        $updatedUser = $task->updated_by ? $task->updater : $task->creator;
        $updatedBy = $updatedUser ? trim(($updatedUser->prefix ?? '') . ' ' . ($updatedUser->name ?? '')) : '-';
        $createdBy = $task->creator ? trim(($task->creator->prefix ?? '') . ' ' . ($task->creator->name ?? '')) : '-';
        $updatedAtThai = $formatDateTimeThai($task->updated_at);
        $createdAtThai = $formatDateTimeThai($task->created_at);
        $openQuickEntry = old('_quick_add_entry') || $errors->hasAny(['person_name', 'category', 'job_name', 'job_detail', 'start_date', 'end_date', 'hours']);
        $openQuickOutput = old('_quick_add_output') || $errors->hasAny([
            'title',
            'type',
            'project_output',
            'year',
            'journal',
            'quartile',
            'impact_factor',
            'book_title',
            'publisher_name',
            'chapter_pages',
            'isbn',
            'journal_author_name',
            'journal_author_role',
            'book_author_name',
            'conference_name',
            'conference_presentation_type',
            'conference_location',
            'conference_note',
        ]);
    @endphp

    <style>
        .back-office-shell {
            --bo-table-sticky-top: 0px;
            background:
                linear-gradient(180deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.55) 100%);
        }

        .back-office-panel {
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
        }

        .back-office-hero {
            overflow: hidden;
            position: relative;
        }

        .back-office-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.12), transparent 24%);
            pointer-events: none;
        }

        .back-office-hero__layout {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1.15rem;
            padding: 1.35rem 1.65rem;
        }

        .back-office-hero__content {
            min-width: 0;
            max-width: none;
        }

        .back-office-hero__actions {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: flex-end;
            gap: 0.65rem;
        }

        .back-office-hero__eyebrow {
            margin-top: 0.75rem;
            color: #0369a1;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .back-office-hero__title {
            margin-top: 0.45rem;
            color: #0f172a;
            font-size: clamp(1.45rem, 2.25vw, 2.15rem);
            font-weight: 850;
            line-height: 1.22;
            letter-spacing: -0.02em;
        }

        .back-office-hero__intro {
            margin-top: 1rem;
            max-width: min(100%, 110rem);
            color: #475569;
            font-size: 0.98rem;
            line-height: 1.75;
            overflow-wrap: anywhere;
            text-align: left;
        }

        .back-office-hero__intro-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #0f172a;
            font-weight: 800;
            text-align: left;
        }

        .back-office-hero__intro-text {
            display: block;
            white-space: pre-wrap;
            text-align: left;
        }

        .back-office-meta-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.75rem;
            margin-top: 1.15rem;
            max-width: 64rem;
        }

        .back-office-meta-item {
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.72);
            padding: 0.85rem 0.95rem;
        }

        .back-office-meta-item__label {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .back-office-meta-item__value {
            margin-top: 0.28rem;
            color: #1e293b;
            font-size: 0.94rem;
            font-weight: 650;
            line-height: 1.55;
        }

        .back-office-audit {
            margin-top: 1rem;
            display: grid;
            gap: 0.28rem;
            color: #64748b;
            font-size: 0.78rem;
            font-style: italic;
            line-height: 1.55;
        }

        .back-office-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            min-height: 2.8rem;
            min-width: 4.8rem;
            padding: 0.55rem 0.9rem;
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            box-shadow: 0 14px 26px rgba(15, 23, 42, 0.08);
            transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        }

        .back-office-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
        }

        .back-office-action--edit {
            border: 1px solid #fcd34d;
            background: linear-gradient(135deg, #fff7cc 0%, #fde68a 100%);
            color: #92400e;
        }

        .back-office-action--edit:hover {
            background: #fde68a;
        }

        .back-office-action--add {
            gap: 0.55rem;
            border: 1px solid #bfdbfe;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.12);
        }

        .back-office-action--add:hover {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-color: #93c5fd;
        }

        .back-office-action--pdf {
            gap: 0.5rem;
            border: 1px solid #86efac;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            color: #065f46;
            box-shadow: 0 16px 30px rgba(16, 185, 129, 0.18);
            min-width: 8.8rem;
        }

        .back-office-action--pdf:hover {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #4ade80;
        }

        .back-office-action--delete {
            border: 1px solid #fecaca;
            background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%);
            color: #b91c1c;
        }

        .back-office-action--delete:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .back-office-action__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.7rem;
            height: 1.7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.72);
            font-size: 0.86rem;
        }

        .back-office-quick-card {
            border: 1px solid #dbeafe;
            border-radius: 24px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
        }

        .back-office-quick-card--spotlight {
            animation: backOfficeQuickCardPulse 2.2s ease-in-out 3;
            border-color: #38bdf8;
            box-shadow:
                0 0 0 7px rgba(56, 189, 248, 0.2),
                0 22px 42px rgba(14, 165, 233, 0.22);
        }

        @keyframes backOfficeQuickCardPulse {
            0%, 100% {
                transform: translateY(0);
                box-shadow:
                    0 0 0 0 rgba(56, 189, 248, 0),
                    0 14px 28px rgba(15, 23, 42, 0.08);
            }
            25% {
                transform: translateY(-3px);
                box-shadow:
                    0 0 0 12px rgba(56, 189, 248, 0.24),
                    0 26px 46px rgba(14, 165, 233, 0.24);
            }
            55% {
                transform: translateY(0);
                box-shadow:
                    0 0 0 6px rgba(56, 189, 248, 0.16),
                    0 18px 36px rgba(14, 165, 233, 0.16);
            }
            80% {
                transform: translateY(-1px);
                box-shadow:
                    0 0 0 10px rgba(56, 189, 248, 0.18),
                    0 24px 40px rgba(14, 165, 233, 0.18);
            }
        }

        .back-office-quick-card input,
        .back-office-quick-card select,
        .back-office-quick-card textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            padding: 0.82rem 0.95rem;
            font-size: 0.95rem;
            color: #0f172a;
            background: #ffffff;
        }

        .back-office-quick-card textarea {
            min-height: 7rem;
            resize: vertical;
        }

        .back-office-quick-card input:focus,
        .back-office-quick-card select:focus,
        .back-office-quick-card textarea:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.25);
        }

        .bo-required {
            color: #dc2626;
            font-weight: 700;
            margin-left: 0.2rem;
        }

        .bo-field-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.9) !important;
            background: #fff5f5 !important;
        }

        .bo-date-invalid > span:first-child {
            color: #b91c1c;
        }

        .bo-date-invalid input:not([type="hidden"]):not(.flatpickr-hidden) {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.75) !important;
            background: #fff5f5 !important;
        }

        .bo-date-alt-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.75) !important;
            background: #fff5f5 !important;
        }

        .bo-field-error {
            margin-top: 0.35rem;
            color: #dc2626;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .back-office-text-danger {
            color: #dc2626;
            font-weight: 700;
        }

        .back-office-text-danger:hover {
            color: #b91c1c;
        }

        .back-office-section {
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            scroll-margin-top: 1rem;
        }

        .back-office-table-wrap {
            display: none;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #ffffff;
        }

        .back-office-data-table {
            width: 100%;
            min-width: 900px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.86rem;
        }

        .back-office-data-table th {
            position: sticky;
            top: var(--bo-table-sticky-top);
            z-index: 42;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            box-shadow: 0 1px 0 #e2e8f0, 0 8px 14px rgba(15, 23, 42, 0.05);
            padding: 0.78rem 0.85rem;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 850;
            letter-spacing: 0.04em;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .back-office-data-table td {
            border-bottom: 1px solid #edf2f7;
            padding: 1rem 0.95rem;
            color: #334155;
            vertical-align: top;
        }

        .back-office-data-table tbody tr:hover td {
            background: #fbfdff;
        }

        .back-office-data-table tbody tr.back-office-output-special-row:hover td {
            background: #ffffff;
        }

        .back-office-data-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .back-office-output-table {
            table-layout: fixed;
        }

        .back-office-output-table__index {
            width: 2.75rem;
        }

        .back-office-output-table__title {
            width: 36%;
        }

        .back-office-output-table__type {
            width: 17%;
        }

        .back-office-output-table__people {
            width: 16rem;
        }

        .back-office-output-table__actions {
            width: 7.5rem;
        }

        .back-office-output-title-cell {
            min-width: 0;
        }

        .back-office-output-title-main,
        .back-office-output-title-meta {
            display: block;
            max-width: 100%;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .back-office-entry-title-cell,
        .back-office-entry-detail-cell {
            min-width: 0;
            max-width: 26rem;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .back-office-entry-detail-cell {
            white-space: pre-wrap;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 650;
            line-height: 1.65;
        }

        .back-office-table-pill {
            display: inline-flex;
            max-width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            padding: 0.28rem 0.55rem;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 750;
            line-height: 1.35;
        }

        .back-office-table-pill--tone-0 { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
        .back-office-table-pill--tone-1 { border-color: #bae6fd; background: #f0f9ff; color: #0369a1; }
        .back-office-table-pill--tone-2 { border-color: #99f6e4; background: #f0fdfa; color: #0f766e; }
        .back-office-table-pill--tone-3 { border-color: #bbf7d0; background: #f0fdf4; color: #15803d; }
        .back-office-table-pill--tone-4 { border-color: #fde68a; background: #fffbeb; color: #b45309; }
        .back-office-table-pill--tone-5 { border-color: #fed7aa; background: #fff7ed; color: #c2410c; }
        .back-office-table-pill--tone-6 { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
        .back-office-table-pill--tone-7 { border-color: #fbcfe8; background: #fdf2f8; color: #be185d; }
        .back-office-table-pill--tone-8 { border-color: #ddd6fe; background: #f5f3ff; color: #6d28d9; }
        .back-office-table-pill--tone-9 { border-color: #c7d2fe; background: #eef2ff; color: #4338ca; }
        .back-office-table-pill--tone-10 { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
        .back-office-table-pill--tone-11 { border-color: #d6d3d1; background: #fafaf9; color: #57534e; }
        .back-office-table-pill--tone-default { border-color: #e2e8f0; background: #f8fafc; color: #475569; }

        .back-office-table-index {
            width: 3.5rem;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 850;
        }

        .back-office-output-special-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
            gap: 0.45rem;
        }

        .back-office-output-special-row td {
            border-bottom-color: #e2e8f0;
            padding-top: 0;
            padding-left: 0.95rem;
            padding-right: 0.95rem;
        }

        .back-office-output-special-inline {
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #f8fbff;
            padding: 0.75rem;
        }

        .back-office-output-special-inline--split {
            padding: 0.85rem;
        }

        .back-office-output-special-inline--ea101 {
            border-color: #bfdbfe;
            background: #f8fbff;
        }

        .back-office-output-special-inline--ea103 {
            border-color: #fcd34d;
            background: #fffbeb;
        }

        .back-office-output-special-inline--ea118 {
            border-color: #ddd6fe;
            background: #faf5ff;
        }

        .back-office-output-special-inline--ea119 {
            border-color: #a7f3d0;
            background: #ecfdf5;
        }

        .back-office-output-special-title {
            margin-bottom: 0.55rem;
            color: #0369a1;
            font-size: 0.72rem;
            font-weight: 850;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .back-office-output-special-inline--ea103 .back-office-output-special-title {
            color: #b45309;
        }

        .back-office-output-special-inline--ea118 .back-office-output-special-title {
            color: #6d28d9;
        }

        .back-office-output-special-inline--ea119 .back-office-output-special-title {
            color: #047857;
        }

        .back-office-output-special-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            padding: 0.56rem 0.65rem;
        }

        .back-office-output-special-item--compact {
            max-width: none;
        }

        .back-office-output-special-split-grid {
            display: grid;
            grid-template-columns: minmax(15rem, 0.42fr) minmax(26rem, 1fr);
            gap: 0.65rem;
            align-items: start;
        }

        .back-office-output-special-inline--single-detail .back-office-output-special-split-grid {
            grid-template-columns: 1fr;
        }

        .back-office-output-special-inline--single-detail .back-office-output-special-left {
            display: none;
        }

        .back-office-output-special-left {
            display: grid;
            gap: 0.5rem;
        }

        .back-office-output-special-left .back-office-output-special-item--full {
            grid-column: 1 / -1;
        }

        .back-office-output-special-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
        }

        .back-office-output-special-item--authors {
            min-height: auto;
        }

        .back-office-output-special-list {
            margin-top: 0.3rem;
            display: grid;
            gap: 0.22rem;
            color: #0f172a;
            font-size: 0.8rem;
            font-weight: 800;
            line-height: 1.45;
            list-style: decimal;
            padding-left: 1.15rem;
        }

        .back-office-output-special-list--inline {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
            gap: 0.38rem 0.85rem;
            list-style: none;
            padding-left: 0;
        }

        .back-office-output-special-list--inline li {
            display: flex;
            gap: 0.2rem;
            align-items: baseline;
            min-width: 0;
        }

        .back-office-output-special-list__number {
            flex: 0 0 auto;
            color: #64748b;
            font-weight: 850;
        }

        .back-office-output-special-list--inline li::before {
            content: none;
        }

        .back-office-output-special-list--inline li + li {
            margin-left: 0;
        }

        .back-office-output-special-item span {
            display: block;
            color: #64748b;
            font-size: 0.66rem;
            font-weight: 850;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .back-office-output-special-item strong {
            display: block;
            margin-top: 0.2rem;
            color: #0f172a;
            font-size: 0.8rem;
            font-weight: 800;
            line-height: 1.45;
        }

        .back-office-output-special-item:not(.back-office-output-special-item--compact):has(strong) {
            min-width: 0;
        }

        .back-office-output-empty-special {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            padding: 0.75rem;
            color: #94a3b8;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .back-office-table-actions {
            display: inline-flex;
            flex-wrap: nowrap;
            justify-content: center;
            align-items: center;
            gap: 0;
            min-width: 0;
            overflow: hidden;
            border: 1px solid #dbeafe;
            border-radius: 6px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .back-office-table-actions button {
            border: 0;
            border-radius: 0;
            background: transparent;
            padding: 0.36rem 0.58rem;
            color: #1d4ed8;
            font-size: 0.74rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .back-office-table-actions button.danger {
            border-left: 1px solid #fee2e2;
            color: #dc2626;
        }

        .back-office-table-actions form {
            margin: 0;
        }

        .back-office-data-table th:last-child,
        .back-office-data-table td:last-child {
            text-align: center;
        }

        @media (min-width: 768px) {
            .back-office-table-wrap {
                display: block;
                overflow: visible;
            }

            .back-office-detail-card {
                display: none;
            }

            .back-office-detail-card.bo-card-expanded,
            .back-office-detail-card:has([data-back-office-entry-edit-card]:not(.hidden)),
            .back-office-detail-card:has([data-back-office-output-edit-card]:not(.hidden)) {
                display: block;
            }

            .back-office-inline-edit-row > td {
                border-bottom-color: #dbeafe;
                background: #fff;
                padding: 0 0.9rem 1rem;
                text-align: left;
            }

            .back-office-detail-card.bo-edit-inline-mode {
                display: block;
                margin: 0;
                border: 0;
                background: transparent;
                padding: 0;
                box-shadow: none;
            }

            .back-office-detail-card.bo-edit-inline-mode > .back-office-record-card__top,
            .back-office-detail-card.bo-edit-inline-mode > [data-bo-card-detail] {
                display: none !important;
            }

            .back-office-detail-card.bo-edit-inline-mode [data-back-office-entry-edit-card],
            .back-office-detail-card.bo-edit-inline-mode [data-back-office-output-edit-card] {
                margin-top: 0;
                text-align: left;
            }

            .back-office-detail-card.bo-edit-inline-mode label,
            .back-office-detail-card.bo-edit-inline-mode span,
            .back-office-detail-card.bo-edit-inline-mode div {
                text-align: left;
            }
        }

        .back-office-section:has(.project-search-select__menu:not(.hidden)) {
            position: relative;
            z-index: 90;
        }

        .back-office-section .project-search-select__menu {
            z-index: 120;
        }

        .back-office-section__head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.35rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .back-office-section__head--entry {
            background: linear-gradient(90deg, #eff6ff 0%, #f8fbff 100%);
            border-left: 6px solid #2563eb;
        }

        .back-office-section__head--output {
            background: linear-gradient(90deg, #eefcf7 0%, #f8fffc 100%);
            border-left: 6px solid #059669;
        }

        .back-office-section__title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .back-office-section__desc {
            margin-top: 0.2rem;
            font-size: 0.92rem;
            color: #64748b;
        }

        .back-office-section__count {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: rgba(255,255,255,0.82);
            padding: 0.35rem 0.7rem;
            font-size: 0.76rem;
            font-weight: 700;
            color: #475569;
            white-space: nowrap;
        }

        .back-office-section__body {
            padding: 1.15rem;
        }

        .back-office-record-card {
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            background: #ffffff;
            padding: 1.2rem;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.045);
        }

        .back-office-record-card + .back-office-record-card {
            margin-top: 0.9rem;
        }

        .back-office-record-card__top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: start;
        }

        .back-office-record-card__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .back-office-record-card__detail-grid {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
            gap: 1rem;
        }

        .back-office-stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.045);
            padding: 0.85rem 1rem;
        }

        .back-office-stat-card__label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
        }

        .back-office-stat-card__value {
            margin-top: 0.3rem;
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }

        .back-office-dock {
            position: fixed;
            left: 50%;
            bottom: 1rem;
            z-index: 70;
            transform: translateX(-50%);
            width: fit-content;
            max-width: calc(100vw - 2rem);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(10px);
        }

        .back-office-dock__toggle {
            display: none;
        }

        .back-office-dock__grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.5rem;
            width: fit-content;
            max-width: calc(100vw - 2rem);
        }

        .back-office-dock__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.4rem;
            border-radius: 14px;
            border: 1px solid transparent;
            background: transparent;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 600;
            flex: 0 0 auto;
            padding: 0 1rem;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .back-office-dock__button:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
        }

        .back-office-dock__button--entry {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .back-office-dock__button--output {
            background: #f0f9ff;
            color: #0369a1;
        }

        .back-office-dock__button--edit {
            background: #fffbeb;
            color: #92400e;
        }

        .back-office-dock__button--pdf {
            background: #ecfdf5;
            color: #065f46;
        }

        .back-office-dock__button--delete {
            background: #fef2f2;
            color: #b91c1c;
        }

        @media (max-width: 768px) {
            .back-office-panel {
                border-radius: 20px;
            }

            .back-office-hero__layout {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 1.15rem;
            }

            .back-office-hero__actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
            }

            .back-office-hero__actions .back-office-action,
            .back-office-hero__actions form {
                width: 100%;
            }

            .back-office-meta-grid {
                grid-template-columns: 1fr;
            }

            .back-office-section__head {
                align-items: stretch;
                flex-direction: column;
                padding: 1rem;
            }

            .back-office-output-special-split-grid {
                grid-template-columns: 1fr;
            }

            .back-office-section__head > div:last-child {
                justify-content: stretch;
            }

            .back-office-section__head button,
            .back-office-section__count {
                width: 100%;
                justify-content: center;
            }

            .back-office-section__body {
                padding: 0.9rem;
            }

            .back-office-stat-card {
                border-radius: 16px;
                padding: 0.75rem 0.85rem;
            }

            .back-office-stat-card__label {
                font-size: 0.68rem;
                letter-spacing: 0.04em;
            }

            .back-office-stat-card__value {
                font-size: 1.2rem;
                overflow-wrap: anywhere;
            }

            .back-office-record-card {
                border-radius: 20px;
                padding: 1rem;
            }

            .back-office-record-card__top,
            .back-office-record-card__detail-grid {
                grid-template-columns: 1fr;
            }

            .back-office-record-card__actions {
                justify-content: stretch;
            }

            .back-office-record-card__actions > *,
            .back-office-record-card__actions button,
            .back-office-record-card__actions a {
                width: 100%;
            }

            .back-office-dock {
                left: 50%;
                right: auto;
                bottom: calc(0.75rem + env(safe-area-inset-bottom));
                transform: translateX(-50%);
                width: auto;
                border-radius: 999px;
                overflow: hidden;
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
            }

            .back-office-dock.is-open {
                left: 0.75rem;
                right: 0.75rem;
                transform: none;
                width: auto;
                border-radius: 24px;
            }

            .back-office-dock__toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.45rem;
                min-width: 7.25rem;
                min-height: 2.85rem;
                padding: 0 1rem;
                border: 0;
                border-radius: 999px;
                background: #0f172a;
                color: #ffffff;
                font-size: 0.92rem;
                font-weight: 800;
            }

            .back-office-dock.is-open .back-office-dock__toggle {
                width: 100%;
                justify-content: space-between;
                border-radius: 18px;
                background: #1e293b;
            }

            .back-office-dock__grid {
                display: none;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.5rem;
                padding: 0 0.6rem 0.6rem;
            }

            .back-office-dock.is-open .back-office-dock__grid {
                display: grid;
            }

            .back-office-dock__button {
                min-height: 2.7rem;
                font-size: 0.88rem;
                flex: none;
            }
        }
    </style>

    <div class="back-office-shell min-h-screen">
        <div class="mx-auto mt-7 max-w-[1760px] space-y-6 px-3 pb-32 sm:px-4 xl:px-6 2xl:px-8 md:mt-10 md:pb-40">
            <section class="back-office-panel back-office-hero">
                <div class="back-office-hero__layout">
                    <div class="back-office-hero__content">
                        <a href="{{ route('back_office.index') }}" class="text-sm text-sky-700 hover:underline">← กลับไปยังรายการงาน</a>
                        <h1 class="back-office-hero__title">{{ $task->title }}</h1>

                        @php
                            $purposeText = trim((string) $task->purpose);
                        @endphp
                        @if ($purposeText !== '')
                            <div class="back-office-hero__intro">
                                <span class="back-office-hero__intro-label">จุดประสงค์:</span>
                                <div class="back-office-hero__intro-text">{{ $purposeText }}</div>
                            </div>
                        @endif

                        <div class="back-office-meta-grid">
                            <div class="back-office-meta-item">
                                <div class="back-office-meta-item__label">หัวหน้างานผู้รับผิดชอบ</div>
                                <div class="back-office-meta-item__value">{{ !empty($task->leaders) ? implode(', ', $task->leaders) : '-' }}</div>
                            </div>
                        </div>

                        <div class="back-office-audit">
                            <div>
                                ข้อมูลล่าสุดโดย {{ $updatedBy }}
                                @if ($updatedAtThai)
                                    | อัปเดทเมื่อ {{ $updatedAtThai }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="back-office-hero__actions">
                        @if ($canManageBackOffice)
                            <a href="{{ route('back_office.edit', $task) }}"
                                class="back-office-action back-office-action--edit"
                                data-back-office-global-edit>
                                แก้ไข
                            </a>
                        @endif
                        <a href="{{ route('back_office.pdf', $task) }}" target="_blank" rel="noopener"
                            class="back-office-action back-office-action--pdf"
                            data-back-office-global-pdf>
                            <span class="back-office-action__icon" aria-hidden="true">🖨️</span>
                            <span>ดาวน์โหลด PDF</span>
                        </a>
                        @if ($canManageBackOffice)
                            <form action="{{ route('back_office.destroy', $task) }}" method="POST" class="inline-flex"
                                data-back-office-delete-form
                                data-record-id="{{ $task->id }}"
                                data-record-title="{{ $task->title }}"
                                data-record-creator="{{ trim((string) ($task->creator?->name ?? '-')) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="back-office-action back-office-action--delete border-0"
                                    data-back-office-global-delete>
                                    ลบ
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </section>

            <div id="back-office-detail-content">
                @include('back-office.partials.detail-content', [
                    'task' => $task,
                    'summary' => $summary,
                    'drawerMode' => false,
                    'personList' => $personList,
                    'thaiPersonOptions' => $thaiPersonOptions ?? collect(),
                    'researchPersonList' => $researchPersonList ?? collect(),
                    'categoryOptions' => $categoryOptions,
                    'openQuickEntry' => $openQuickEntry,
                    'openQuickOutput' => $openQuickOutput,
                ])
            </div>
        </div>
    </div>

    <aside class="back-office-dock" aria-label="เมนูด่วน Back Office">
        <button type="button" class="back-office-dock__toggle" data-back-office-dock-toggle aria-expanded="false">
            <span>เมนูด่วน</span>
            <span aria-hidden="true">＋</span>
        </button>
        <div class="back-office-dock__grid">
            <button type="button" class="back-office-dock__button back-office-dock__button--entry" data-back-office-dock-entry>
                เพิ่มภาระงาน
            </button>
            <button type="button" class="back-office-dock__button back-office-dock__button--output" data-back-office-dock-output>
                เพิ่มผลงาน
            </button>
            @if ($canManageBackOffice)
                <button type="button" class="back-office-dock__button back-office-dock__button--edit" data-back-office-dock-edit>
                    แก้ไขงาน
                </button>
            @endif
            <button type="button" class="back-office-dock__button back-office-dock__button--pdf" data-back-office-dock-pdf>
                ดาวน์โหลด PDF
            </button>
            @if ($canManageBackOffice)
                <button type="button" class="back-office-dock__button back-office-dock__button--delete" data-back-office-dock-delete>
                    ลบงาน
                </button>
            @endif
        </div>
    </aside>

    <script>
        window.__backOfficePeople = @json(($personList ?? collect())->values());
        window.__backOfficeThaiPeople = @json(($thaiPersonOptions ?? collect())->values());
        window.__backOfficeResearchPeople = @json(($researchPersonList ?? collect())->values());
        window.__backOfficeEscapeHtml = window.__backOfficeEscapeHtml || ((value) => String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;'));

        (() => {
            const closeBackOfficeSearchDropdowns = (except = null) => {
                document.querySelectorAll('[data-project-search-select-menu]').forEach((menu) => {
                    if (except && menu === except) return;
                    menu.classList.add('hidden');
                });
            };

            const ensureBackOfficeSearchDropdown = (select, config = {}) => {
                if (!(select instanceof HTMLSelectElement)) return null;

                const staleWrapper = select.nextElementSibling;
                if (staleWrapper instanceof HTMLElement && staleWrapper.matches('.project-search-select') && !select.tomselect) {
                    staleWrapper.remove();
                    delete select.dataset.projectSearchDropdown;
                }

                if (select.dataset.projectSearchDropdown === '1' && select.tomselect) {
                    select.tomselect.refreshOptions?.();
                    return select.tomselect;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'project-search-select';
                wrapper.dataset.projectSearchDropdown = '1';
                wrapper.innerHTML = `
                    <button type="button" class="project-search-select__button" data-project-search-select-toggle>
                        <span class="project-search-select__label" data-project-search-select-label></span>
                        <span class="project-search-select__icon">▾</span>
                    </button>
                    <div class="project-search-select__menu hidden" data-project-search-select-menu>
                        <input type="text" class="project-search-select__search" data-project-search-select-search>
                        <div class="project-search-select__options" data-project-search-select-options></div>
                        <div class="project-search-select__empty hidden" data-project-search-select-empty>ไม่พบข้อมูล</div>
                    </div>
                `;

                select.classList.add('project-search-select__native');
                select.insertAdjacentElement('afterend', wrapper);

                const toggle = wrapper.querySelector('[data-project-search-select-toggle]');
                const label = wrapper.querySelector('[data-project-search-select-label]');
                const menu = wrapper.querySelector('[data-project-search-select-menu]');
                const searchInput = wrapper.querySelector('[data-project-search-select-search]');
                const optionsWrap = wrapper.querySelector('[data-project-search-select-options]');
                const emptyEl = wrapper.querySelector('[data-project-search-select-empty]');

                if (!(toggle instanceof HTMLButtonElement) ||
                    !(label instanceof HTMLElement) ||
                    !(menu instanceof HTMLElement) ||
                    !(searchInput instanceof HTMLInputElement) ||
                    !(optionsWrap instanceof HTMLElement) ||
                    !(emptyEl instanceof HTMLElement)) {
                    wrapper.remove();
                    return null;
                }

                const placeholder = String(
                    config.placeholder ||
                    select.dataset.placeholder ||
                    select.querySelector('option[value=""]')?.textContent?.trim() ||
                    'กรุณาเลือกชื่อ'
                ).trim();
                searchInput.placeholder = String(
                    config.searchPlaceholder ||
                    select.dataset.searchPlaceholder ||
                    'พิมพ์เพื่อค้นหา'
                ).trim();

                const optionItems = () => Array.from(select.options)
                    .filter((option) => option.value !== '')
                    .map((option) => ({
                        value: String(option.value),
                        label: String(option.textContent || '').trim(),
                    }));

                const syncLabel = () => {
                    const selected = Array.from(select.options).find((option) => option.value === select.value);
                    label.textContent = selected && selected.value ? String(selected.textContent || '').trim() : placeholder;
                };

                const renderOptions = () => {
                    const term = String(searchInput.value || '').toLowerCase().trim();
                    const items = optionItems().filter((item) => term === '' || item.label.toLowerCase().includes(term));
                    optionsWrap.innerHTML = '';
                    if (!items.length) {
                        emptyEl.classList.remove('hidden');
                        return;
                    }
                    emptyEl.classList.add('hidden');
                    items.forEach((item) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'project-search-select__option';
                        button.textContent = item.label;
                        if (item.value === String(select.value || '')) button.classList.add('is-selected');
                        button.addEventListener('mousedown', (event) => {
                            event.preventDefault();
                            event.stopPropagation();
                        });
                        button.addEventListener('click', () => {
                            select.value = item.value;
                            syncLabel();
                            renderOptions();
                            menu.classList.add('hidden');
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                        optionsWrap.appendChild(button);
                    });
                };

                toggle.addEventListener('click', () => {
                    if (select.disabled) return;
                    const willOpen = menu.classList.contains('hidden');
                    closeBackOfficeSearchDropdowns(willOpen ? menu : null);
                    menu.classList.toggle('hidden', !willOpen);
                    if (willOpen) {
                        searchInput.value = '';
                        renderOptions();
                        searchInput.focus();
                    }
                });

                searchInput.addEventListener('input', renderOptions);
                document.addEventListener('click', (event) => {
                    if (!wrapper.contains(event.target) && event.target !== select) {
                        menu.classList.add('hidden');
                    }
                });
                select.addEventListener('change', () => {
                    syncLabel();
                    renderOptions();
                });

                const api = {
                    wrapper,
                    refreshOptions() {
                        renderOptions();
                        syncLabel();
                    },
                    clear(silent = false) {
                        select.value = '';
                        syncLabel();
                        renderOptions();
                        if (!silent) select.dispatchEvent(new Event('change', { bubbles: true }));
                    },
                    setValue(value, silent = false) {
                        select.value = String(value ?? '');
                        syncLabel();
                        renderOptions();
                        if (!silent) select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                };

                syncLabel();
                renderOptions();
                select.dataset.projectSearchDropdown = '1';
                select.tomselect = api;
                return api;
            };

            window.initBackOfficePersonSelect = function(scope = document) {
                scope.querySelectorAll('[data-back-office-person-select]').forEach((select) => {
                    if (!(select instanceof HTMLSelectElement)) return;
                    const placeholder = select.dataset.placeholder || select.querySelector('option[value=""]')?.textContent?.trim() || 'กรุณาเลือกชื่อ';
                    const searchPlaceholder = select.dataset.searchPlaceholder || 'พิมพ์เพื่อค้นหาชื่อ';
                    const dropdown = ensureBackOfficeSearchDropdown(select, {
                        placeholder,
                        searchPlaceholder,
                    });
                    dropdown?.refreshOptions?.();
                });
            };

            window.initBackOfficePersonSelect(document);
        })();

        (() => {
            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const getDateDisplayField = (input) => {
                if (!(input instanceof HTMLElement) || !input.classList.contains('js-date-be')) return null;

                const altInput = input._flatpickr?.altInput;
                if (altInput instanceof HTMLElement) return altInput;

                const siblingAltInput = input.nextElementSibling;
                if (
                    siblingAltInput instanceof HTMLInputElement
                    && !siblingAltInput.classList.contains('js-date-be')
                ) {
                    return siblingAltInput;
                }

                const wrapperAltInput = input.parentElement?.querySelector?.('input:not(.js-date-be):not([type="hidden"])');
                if (wrapperAltInput instanceof HTMLElement) return wrapperAltInput;

                return null;
            };

            const getDateDisplayFields = (input) => {
                if (!(input instanceof HTMLElement) || !input.classList.contains('js-date-be')) return [];
                const fields = [
                    input._flatpickr?.altInput,
                    input.nextElementSibling,
                    input.previousElementSibling,
                    ...Array.from(input.closest('label')?.querySelectorAll('input') || []),
                    ...Array.from(input.parentElement?.querySelectorAll('input') || []),
                ];

                return [...new Set(fields)]
                    .filter((field) => field instanceof HTMLInputElement && field !== input && field.type !== 'hidden' && !field.classList.contains('flatpickr-hidden'));
            };

            const setDateInvalidInlineStyle = (input, invalid) => {
                getDateDisplayFields(input).forEach((field) => {
                    if (invalid) {
                        field.style.setProperty('border-color', '#ef4444', 'important');
                        field.style.setProperty('box-shadow', '0 0 0 4px rgba(254, 202, 202, 0.75)', 'important');
                        field.style.setProperty('background', '#fff5f5', 'important');
                    } else {
                        field.style.removeProperty('border-color');
                        field.style.removeProperty('box-shadow');
                        field.style.removeProperty('background');
                    }
                });
            };

            const getVisibleField = (input) => {
                if (!(input instanceof HTMLElement)) return null;
                const dateDisplayField = getDateDisplayField(input);
                if (dateDisplayField) return dateDisplayField;
                const searchButton = input.tomselect?.wrapper?.querySelector?.('[data-project-search-select-toggle]');
                if (searchButton instanceof HTMLElement) return searchButton;
                return input;
            };

            const isHiddenByDrawerOrPanel = (input) => {
                if (!(input instanceof HTMLElement)) return true;
                return Boolean(input.closest('.hidden'));
            };

            const getFieldLabel = (input) => {
                const raw = input.closest('label')?.querySelector('span')?.textContent
                    || input.parentElement?.querySelector('span')?.textContent
                    || input.dataset.placeholder
                    || input.placeholder
                    || input.name
                    || 'ช่องนี้';

                return String(raw)
                    .replace(/\*/g, '')
                    .replace(/\s+/g, ' ')
                    .trim() || 'ช่องนี้';
            };

            const getInvalidSummaryLabel = (field) => {
                if (!(field instanceof HTMLElement)) return 'ช่องข้อมูลที่จำเป็น';
                if (field.dataset?.invalidSummary) return field.dataset.invalidSummary;
                if (field.matches?.('#quick-output-ea101, [data-inline-output-ea101]')) return 'รายชื่อผู้เขียน';
                if (field.matches?.('#quick-output-ea103, [data-inline-output-ea103]')) return 'รายชื่อผู้นำเสนอ';
                if (field.matches?.('#quick-output-ea118, [data-inline-output-ea118]')) return 'ผู้ได้รับรางวัล';
                if (field.matches?.('#quick-output-ea119, [data-inline-output-ea119]')) return 'รายชื่อผู้เขียนหนังสือ';
                if (field.name === 'year') return 'วันที่เผยแพร่';
                return getFieldLabel(field);
            };

            const clearFieldInvalid = (input) => {
                const field = getVisibleField(input);
                const dateWrap = input?.closest?.('[data-bo-output-date-field]');
                field?.classList.remove('bo-field-invalid');
                input?.classList?.remove('bo-field-invalid');
                getDateDisplayField(input)?.classList.remove('bo-field-invalid');
                getDateDisplayField(input)?.classList.remove('bo-date-alt-invalid');
                setDateInvalidInlineStyle(input, false);
                dateWrap?.classList.remove('bo-date-invalid');
                const errorNode = field?.parentElement?.querySelector('.bo-field-error');
                if (errorNode) errorNode.remove();
                const dateErrorNode = dateWrap?.querySelector(':scope > .bo-field-error');
                if (dateErrorNode) dateErrorNode.remove();
            };

            const markFieldInvalid = (input, message) => {
                const field = getVisibleField(input);
                const dateWrap = input?.closest?.('[data-bo-output-date-field]');
                field?.classList.add('bo-field-invalid');
                input?.classList?.add('bo-field-invalid');
                getDateDisplayField(input)?.classList.add('bo-field-invalid');
                getDateDisplayField(input)?.classList.add('bo-date-alt-invalid');
                setDateInvalidInlineStyle(input, true);

                if (dateWrap) {
                    dateWrap.classList.add('bo-date-invalid');
                    let dateErrorNode = dateWrap.querySelector(':scope > .bo-field-error');
                    if (!dateErrorNode) {
                        dateErrorNode = document.createElement('div');
                        dateErrorNode.className = 'bo-field-error';
                        dateWrap.appendChild(dateErrorNode);
                    }
                    dateErrorNode.textContent = message || 'กรุณาตรวจสอบข้อมูลนี้';
                    return;
                }

                if (!field?.parentElement) return;
                let errorNode = field.parentElement.querySelector('.bo-field-error');
                if (!errorNode) {
                    errorNode = document.createElement('div');
                    errorNode.className = 'bo-field-error';
                    field.parentElement.appendChild(errorNode);
                }
                errorNode.textContent = message || 'กรุณาตรวจสอบข้อมูลนี้';
            };

            const shouldSkipRequiredField = (input) => {
                if (!(input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement)) return true;
                if (input.disabled || input.closest('template') || isHiddenByDrawerOrPanel(input)) return true;
                if (input.type === 'hidden' && !input.classList.contains('js-date-be')) return true;

                const name = String(input.name || '').trim();
                if (!name && !input.matches('[data-person-search-select],[data-person-manual-input]')) return true;
                if (['_token', '_method', '_quick_add_output', '_edit_entry_id', '_edit_output_id'].includes(name)) return true;
                if (name === 'purpose') return true;
                if (name === 'project_output' || name.endsWith('[project_output]')) return true;

                return false;
            };

            const isOptionalOutputMetricField = (input) => {
                const name = String(input?.name || '').trim();
                return name === 'quartile' || name === 'impact_factor' || name.endsWith('[quartile]') || name.endsWith('[impact_factor]');
            };

            const validateSingleField = (input) => {
                clearFieldInvalid(input);
                if (shouldSkipRequiredField(input)) return true;

                const dateDisplayField = getDateDisplayField(input);
                const value = String(
                    input.classList.contains('js-date-be')
                        ? (input.value || dateDisplayField?.value || input._flatpickr?.altInput?.value || '')
                        : (input.value ?? '')
                ).trim();
                if (value === '') {
                    if (isOptionalOutputMetricField(input)) return true;
                    input.dataset.invalidSummary = getFieldLabel(input);
                    markFieldInvalid(input, `กรุณากรอก${getFieldLabel(input)}`);
                    return false;
                }

                if (input.classList.contains('js-date-be') && typeof parseDateInputValue === 'function' && !parseDateInputValue(input.value || value)) {
                    input.dataset.invalidSummary = getFieldLabel(input);
                    markFieldInvalid(input, `${getFieldLabel(input)}ไม่ถูกต้อง`);
                    return false;
                }

                if (typeof input.checkValidity === 'function' && !input.checkValidity()) {
                    input.dataset.invalidSummary = getFieldLabel(input);
                    markFieldInvalid(input, input.validationMessage || `${getFieldLabel(input)}ไม่ถูกต้อง`);
                    return false;
                }

                return true;
            };

            const validateConditionalPersonRows = (form) => {
                const invalid = [];

                form.querySelectorAll('[data-conditional-person-row]').forEach((row) => {
                    if (row.closest('.hidden')) return;

                    const typeSelect = row.querySelector('[data-person-type-select]');
                    const searchSelect = row.querySelector('[data-person-search-select]');
                    const manualInput = row.querySelector('[data-person-manual-input]');
                    const hiddenInput = row.querySelector('[data-person-name-hidden]');
                    const mode = String(typeSelect?.value || '').trim();

                    if (typeSelect instanceof HTMLSelectElement && !validateSingleField(typeSelect)) {
                        invalid.push(typeSelect);
                        return;
                    }

                    if (!['internal', 'external', 'unit'].includes(mode)) return;
                    if (mode === 'unit') return;

                    const displayInput = mode === 'internal' ? searchSelect : manualInput;
                    const value = String(displayInput?.value || hiddenInput?.value || '').trim();
                    if (!value && displayInput instanceof HTMLElement) {
                        displayInput.dataset.invalidSummary = getFieldLabel(displayInput);
                        markFieldInvalid(displayInput, `กรุณากรอก${getFieldLabel(displayInput)}`);
                        invalid.push(displayInput);
                    }
                });

                return invalid;
            };

            const validateRequiredRepeatRows = (form) => {
                const invalid = [];
                const rules = [
                    ['#quick-output-ea101:not(.hidden), [data-inline-output-ea101]:not(.hidden)', '[data-author-row]', 'กรุณาเพิ่มผู้เขียนอย่างน้อย 1 คน'],
                    ['#quick-output-ea103:not(.hidden), [data-inline-output-ea103]:not(.hidden)', '[data-conference-presenter-row]', 'กรุณาเพิ่มผู้นำเสนออย่างน้อย 1 คน'],
                    ['#quick-output-ea118:not(.hidden), [data-inline-output-ea118]:not(.hidden)', '[data-award-recipient-row]', 'กรุณาเพิ่มผู้ได้รับรางวัลอย่างน้อย 1 คน'],
                    ['#quick-output-ea119:not(.hidden), [data-inline-output-ea119]:not(.hidden)', '[data-book-author-row]', 'กรุณาเพิ่มผู้เขียนอย่างน้อย 1 คน'],
                ];

                rules.forEach(([sectionSelector, rowSelector, message]) => {
                    form.querySelectorAll(sectionSelector).forEach((section) => {
                        section.classList.remove('bo-field-invalid');
                        section.querySelector(':scope > .bo-field-error')?.remove();

                        if (section.querySelector(rowSelector)) return;

                        section.classList.add('bo-field-invalid');
                        const error = document.createElement('div');
                        error.className = 'bo-field-error';
                        error.textContent = message;
                        section.appendChild(error);
                        section.dataset.invalidSummary = message.replace(/^กรุณา/, '');
                        invalid.push(section);
                    });
                });

                return invalid;
            };

            const validateOutputDateField = (form) => {
                if (!form.querySelector('[name="_quick_add_output"], [name="_edit_output_id"]')) return [];
                const yearInput = form.querySelector('input[name="year"]');
                if (!(yearInput instanceof HTMLInputElement)) return [];

                clearFieldInvalid(yearInput);

                const displayField = getDateDisplayField(yearInput);
                const value = String(yearInput.value || displayField?.value || yearInput._flatpickr?.altInput?.value || '').trim();
                const parsed = typeof parseDateInputValue === 'function' ? parseDateInputValue(yearInput.value || value) : value;
                if (value && parsed) return [];

                yearInput.dataset.invalidSummary = value ? 'วันที่เผยแพร่ไม่ถูกต้อง' : 'วันที่เผยแพร่';
                markFieldInvalid(yearInput, value ? 'วันที่เผยแพร่ไม่ถูกต้อง' : 'กรุณากรอกวันที่เผยแพร่');
                return [yearInput];
            };

            const validateBackOfficeInlineForm = (form) => {
                const fields = Array.from(form.querySelectorAll('input, select, textarea'));
                const invalid = [];

                fields.forEach((field) => {
                    if (!validateSingleField(field)) invalid.push(field);
                });

                invalid.push(...validateOutputDateField(form));
                invalid.push(...validateConditionalPersonRows(form));
                invalid.push(...validateRequiredRepeatRows(form));

                return invalid;
            };

            const revealInvalidField = (field) => {
                const target = field?.closest?.('[data-bo-output-date-field]') || getVisibleField(field) || field;
                const hiddenCard = target?.closest?.('[data-back-office-entry-edit-card].hidden, [data-back-office-output-edit-card].hidden, #quick-entry-card.hidden, #quick-output-card.hidden');
                hiddenCard?.classList.remove('hidden');

                window.setTimeout(() => {
                    target?.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
                    target?.focus?.({ preventScroll: true });
                }, 80);
            };

            const showRequiredAlert = (invalid = []) => {
                const labels = [...new Set(
                    invalid
                        .map(getInvalidSummaryLabel)
                        .map((label) => String(label || '').replace(/\*/g, '').replace(/\s+/g, ' ').trim())
                        .filter(Boolean)
                )].slice(0, 8);
                const moreCount = Math.max(0, invalid.length - labels.length);
                const detailHtml = labels.length
                    ? `<div style="margin-top:14px;text-align:left"><div style="font-weight:700;margin-bottom:6px">ยังขาด:</div><ul style="margin:0;padding-left:1.2rem">${labels.map((label) => `<li>${escapeHtml(label)}</li>`).join('')}${moreCount ? `<li>และอีก ${moreCount} รายการ</li>` : ''}</ul></div>`
                    : '';

                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรอกข้อมูลไม่ครบ',
                        html: `กรุณากรอกข้อมูลให้ครบ และตรวจสอบช่องสีแดง${detailHtml}`,
                        confirmButtonText: 'ไปกรอกข้อมูล',
                        confirmButtonColor: '#2563eb',
                    });
                    return;
                }

                window.alert(`กรุณากรอกข้อมูลให้ครบ และตรวจสอบช่องสีแดง${labels.length ? `\n\nยังขาด:\n- ${labels.join('\n- ')}` : ''}`);
            };

            document.addEventListener('input', (event) => {
                if (event.target instanceof HTMLElement) clearFieldInvalid(event.target);
            });

            document.addEventListener('change', (event) => {
                if (event.target instanceof HTMLElement) clearFieldInvalid(event.target);
            });

            document.addEventListener('submit', (event) => {
                const form = event.target?.closest?.('form[data-back-office-required-form]');
                if (!form) return;

                const invalid = validateBackOfficeInlineForm(form);
                event.preventDefault();

                if (invalid.length) {
                    showRequiredAlert(invalid);
                    revealInvalidField(invalid[0]);
                    return;
                }

                window.__backOfficeSubmitInlineForm?.(form);
            }, true);
        })();

        (() => {
            const showSuccess = (message) => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: message || 'บันทึกเรียบร้อย',
                        timer: 1200,
                        showConfirmButton: false,
                    });
                    return;
                }

                if (message) window.alert(message);
            };

            const refreshDetailContent = async () => {
                const response = await fetch(window.location.href, {
                    headers: {
                        'Accept': 'text/html',
                        'X-BackOffice-Refresh': '1',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Refresh failed');
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.querySelector('#back-office-detail-content');
                const current = document.querySelector('#back-office-detail-content');

                if (!next || !current) {
                    throw new Error('Detail content not found');
                }

                current.replaceWith(next);
                window.__backOfficeAfterDetailRefresh?.(next);
                window.initBackOfficePersonSelect?.(next);
                document.dispatchEvent(new CustomEvent('backoffice:detail-refreshed', { detail: { root: next } }));
            };

            window.__backOfficeSubmitInlineForm = async (form) => {
                if (!(form instanceof HTMLFormElement) || form.dataset.boSubmitting === '1') return;

                const submitButton = form.querySelector('button[type="submit"]');
                const originalText = submitButton?.textContent;
                form.dataset.boSubmitting = '1';
                submitButton?.setAttribute('disabled', 'disabled');
                if (submitButton) submitButton.textContent = 'กำลังบันทึก...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                        credentials: 'same-origin',
                    });

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.ok === false) {
                        throw new Error(data.message || 'Save failed');
                    }

                    await refreshDetailContent();
                    showSuccess(data.message || 'บันทึกเรียบร้อย');
                } catch (error) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'บันทึกไม่สำเร็จ',
                            text: 'กรุณาลองใหม่อีกครั้ง',
                            confirmButtonColor: '#dc2626',
                        });
                    } else {
                        window.alert('บันทึกไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
                    }
                } finally {
                    form.dataset.boSubmitting = '0';
                    submitButton?.removeAttribute('disabled');
                    if (submitButton && originalText) submitButton.textContent = originalText;
                }
            };
        })();

        (() => {
            if (window.__backOfficeDeleteConfirmBound) return;
            window.__backOfficeDeleteConfirmBound = true;

            const escapeHtml = window.__backOfficeEscapeHtml;
            const setSectionCount = (sectionSelector, count) => {
                if (!Number.isFinite(Number(count))) return;
                const countNode = document.querySelector(`${sectionSelector} .back-office-section__count`);
                if (countNode) countNode.textContent = `รวม ${Number(count).toLocaleString('th-TH')} รายการ`;
            };

            document.addEventListener('submit', (event) => {
                const form = event.target?.closest('form[data-back-office-delete-form]');
                if (!form) return;

                event.preventDefault();

                const recordId = form.dataset.recordId || '-';
                const title = form.dataset.recordTitle || '-';
                const creator = form.dataset.recordCreator || '-';

                const fallbackText = `ยืนยันลบ Record #${recordId}\nงาน: ${title}\nผู้บันทึก: ${creator}`;

                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ยืนยันการลบงาน Back Office',
                        html: `Record #${escapeHtml(recordId)}<br>งาน: ${escapeHtml(title)}<br>ผู้บันทึก: ${escapeHtml(creator)}`,
                        showCancelButton: true,
                        confirmButtonText: 'ลบรายการนี้',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: '#dc2626'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                    return;
                }

                if (window.confirm(fallbackText)) {
                    form.submit();
                }
            });
        })();

        (() => {
            const escapeHtml = window.__backOfficeEscapeHtml;
            const cssEscape = (value) => window.CSS?.escape ? CSS.escape(String(value)) : String(value).replace(/["\\]/g, '\\$&');
            const setSectionCount = (sectionSelector, count) => {
                if (!Number.isFinite(Number(count))) return;
                const countNode = document.querySelector(`${sectionSelector} .back-office-section__count`);
                if (countNode) countNode.textContent = `รวม ${Number(count).toLocaleString('th-TH')} รายการ`;
            };

            document.addEventListener('submit', (event) => {
                const form = event.target?.closest('form[data-back-office-entry-delete-form]');
                if (!form) return;

                event.preventDefault();

                const person = form.dataset.entryPerson || '-';
                const job = form.dataset.entryJob || '-';
                const category = form.dataset.entryCategory || '-';
                const hours = form.dataset.entryHours || '0';
                const start = form.dataset.entryStart || '-';
                const end = form.dataset.entryEnd || '-';

                const fallbackText = `ยืนยันลบภาระงาน\nผู้รับผิดชอบ: ${person}\nภาระงาน: ${job}\nหมวดงาน: ${category}\nชั่วโมง: ${hours}\nช่วงเวลา: ${start} ถึง ${end}`;

                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ยืนยันการลบภาระงาน',
                        html: `ผู้รับผิดชอบ: ${escapeHtml(person)}<br>ภาระงาน: ${escapeHtml(job)}<br>หมวดงาน: ${escapeHtml(category)}<br>ชั่วโมง: ${escapeHtml(hours)}<br>ช่วงเวลา: ${escapeHtml(start)} ถึง ${escapeHtml(end)}`,
                        showCancelButton: true,
                        confirmButtonText: 'ลบภาระงานนี้',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: '#dc2626'
                    }).then((result) => {
                        if (result.isConfirmed) submitEntryDelete(form);
                    });
                    return;
                }

                if (window.confirm(fallbackText)) {
                    submitEntryDelete(form);
                }
            });

            const submitEntryDelete = async (form) => {
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton?.setAttribute('disabled', 'disabled');

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error('Delete failed');
                    }
                    const payload = await response.json().catch(() => ({}));

                    const entryId = form.dataset.entryId || form.closest('[data-back-office-entry-card]')?.id?.replace('entry-', '');
                    if (entryId) {
                        document.querySelectorAll(`[data-back-office-entry-id="${cssEscape(entryId)}"]`).forEach((row) => row.remove());
                        document.getElementById(`entry-${entryId}`)?.remove();
                    }
                    const card = form.closest('[data-back-office-entry-card]');
                    const list = document.querySelector('[data-back-office-entry-list]');
                    card?.remove();
                    document.querySelectorAll('.back-office-inline-edit-row').forEach((row) => {
                        if (!row.querySelector('.back-office-detail-card')) row.remove();
                    });
                    setSectionCount('#back-office-entry-section', payload.remaining_entries);

                    const hasCards = !!list?.querySelector('[data-back-office-entry-card]');
                    const hasEmpty = !!list?.querySelector('[data-back-office-entry-empty]');

                    if (list && !hasCards && !hasEmpty) {
                        const empty = document.createElement('div');
                        empty.className = 'rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-400';
                        empty.setAttribute('data-back-office-entry-empty', '1');
                        empty.textContent = 'ไม่มีข้อมูลภาระงาน';
                        list.appendChild(empty);
                    }

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบภาระงานเรียบร้อย',
                            timer: 1400,
                            showConfirmButton: false,
                        });
                    }
                } catch (error) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'ลบไม่สำเร็จ',
                            text: 'กรุณาลองใหม่อีกครั้ง',
                            confirmButtonColor: '#dc2626',
                        });
                    } else {
                        window.alert('ลบไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
                    }
                    submitButton?.removeAttribute('disabled');
                }
            };
        })();

        (() => {
            const people = Array.isArray(window.__backOfficePeople) ? window.__backOfficePeople : [];
            const thaiPeople = Array.isArray(window.__backOfficeThaiPeople) ? window.__backOfficeThaiPeople : [];
            const researchPeople = Array.isArray(window.__backOfficeResearchPeople) ? window.__backOfficeResearchPeople : [];
            const SPECIAL_UNIT_NAME = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
            const fieldClassInline = 'mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100';
            const optionHtml = people.map((name) => `<option value="${String(name).replace(/"/g, '&quot;')}">${String(name).replace(/</g, '&lt;').replace(/>/g, '&gt;')}</option>`).join('');
            const thaiPersonOptionHtml = thaiPeople.map((person) => {
                const id = String(person?.id || '').replace(/"/g, '&quot;');
                const name = String(person?.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return id && name ? `<option value="${id}">${name}</option>` : '';
            }).join('');
            const researchOptionHtml = researchPeople.map((name) => `<option value="${String(name).replace(/"/g, '&quot;')}">${String(name).replace(/</g, '&lt;').replace(/>/g, '&gt;')}</option>`).join('');

            const syncConditionalPersonRow = (row) => {
                const type = row.querySelector('[data-person-type-select]')?.value || '';
                const manualWrap = row.querySelector('[data-person-manual-wrap]');
                const manualInput = row.querySelector('[data-person-manual-input]');
                const selectWrap = row.querySelector('[data-person-select-wrap]');
                const select = row.querySelector('[data-person-search-select]');
                const hidden = row.querySelector('[data-person-name-hidden]');
                if (!hidden) return;
                const isInternal = type === 'internal';
                const isExternal = type === 'external';
                const isUnit = type === 'unit';
                const showIdleSelect = type === '' && row.hasAttribute('data-show-idle-person-select');
                manualWrap?.classList.toggle('hidden', !isExternal && !isUnit);
                selectWrap?.classList.toggle('hidden', !isInternal && !showIdleSelect);
                if (manualInput) {
                    const unitLabel = manualInput.dataset.unitPlaceholder || SPECIAL_UNIT_NAME;
                    manualInput.disabled = !isExternal && !isUnit;
                    manualInput.readOnly = false;
                    if (isExternal) {
                        manualInput.placeholder = manualInput.dataset.externalPlaceholder || 'ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)';
                    } else if (isUnit) {
                        manualInput.readOnly = true;
                        manualInput.placeholder = unitLabel;
                        manualInput.value = unitLabel;
                        hidden.value = unitLabel;
                    } else {
                        manualInput.placeholder = manualInput.dataset.idlePlaceholder || 'เลือกประเภทผู้เขียนก่อน';
                    }
                    if (!isExternal && !isUnit) {
                        manualInput.value = '';
                    }
                }
                if (select) {
                    select.disabled = !isInternal;
                    select.tomselect?.wrapper?.classList.toggle('is-disabled', !isInternal);
                    const searchButton = select.tomselect?.wrapper?.querySelector?.('[data-project-search-select-toggle]');
                    if (searchButton instanceof HTMLButtonElement) {
                        searchButton.disabled = !isInternal;
                    }
                    if (!isInternal) {
                        try {
                            select.tomselect?.clear?.(true);
                        } catch (_) {}
                        select.value = '';
                        select.tomselect?.refreshOptions?.();
                    }
                }
                if (isUnit) hidden.value = (manualInput?.dataset.unitPlaceholder || SPECIAL_UNIT_NAME);
                else if (isInternal) hidden.value = select?.value || '';
                else if (isExternal) hidden.value = manualInput?.value || '';
                else hidden.value = '';
            };

            const bindConditionalPersonRow = (row) => {
                if (!row || row.dataset.personBound === '1') return;
                row.dataset.personBound = '1';
                window.initBackOfficePersonSelect?.(row);
                row.querySelector('[data-person-type-select]')?.addEventListener('change', () => syncConditionalPersonRow(row));
                row.querySelector('[data-person-manual-input]')?.addEventListener('input', () => syncConditionalPersonRow(row));
                row.querySelector('[data-person-search-select]')?.addEventListener('change', () => syncConditionalPersonRow(row));
                syncConditionalPersonRow(row);
            };

            window.bindBackOfficeConditionalPersonRow = bindConditionalPersonRow;

            document.querySelectorAll('[data-conditional-person-row]').forEach((row) => window.bindBackOfficeConditionalPersonRow?.(row));

            const reindexNamedRows = (list, rowSelector, prefixes) => {
                list.querySelectorAll(rowSelector).forEach((row, index) => {
                    prefixes.forEach((prefix) => {
                        row.querySelectorAll(`[name^="${prefix}["]`).forEach((field) => {
                            field.name = field.name.replace(new RegExp(`${prefix}\\[\\d+\\]`), `${prefix}[${index}]`);
                        });
                    });
                });
            };

            const buildAuthorRow = (index, mode = 'quick') => {
                const f = mode === 'inline' ? fieldClassInline : '';
                return `<div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,180px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-author-row data-conditional-person-row>
                    <label class="block"><span class="text-xs font-semibold text-slate-600">บทบาท <span class="bo-required">*</span></span><select name="authors[${index}][role]" class="${f}"><option value="">เลือกบทบาท</option><option value="first">First-author</option><option value="co">Co-author</option><option value="corresponding">Corresponding author</option></select></label>
                    <label class="block"><span class="text-xs font-semibold text-slate-600">ประเภทผู้เขียน <span class="bo-required">*</span></span><select name="authors[${index}][person_type]" class="${f}" data-person-type-select><option value="">กรุณาเลือก</option><option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option><option value="external">คนนอก</option></select></label>
                    <div class="hidden" data-person-select-wrap><span class="text-xs font-semibold text-slate-600">เลือกชื่อผู้เขียน <span class="bo-required">*</span></span><select class="${f}" data-back-office-person-select data-person-search-select data-placeholder="กรุณาเลือกชื่อผู้เขียน" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้เขียน"><option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>${researchOptionHtml}</select></div>
                    <label data-person-manual-wrap><span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ) <span class="bo-required">*</span></span><input class="${f}" placeholder="เลือกประเภทผู้เขียนก่อน" data-idle-placeholder="เลือกประเภทผู้เขียนก่อน" data-external-placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)" data-person-manual-input disabled></label>
                    <input type="hidden" name="authors[${index}][name]" data-person-name-hidden>
                    <div class="flex items-end justify-end"><button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-author>ลบผู้เขียน</button></div>
                </div>`;
            };

            const buildOutputContributorRow = (index, mode = 'quick') => {
                const f = mode === 'inline' ? fieldClassInline : '';
                return `<div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_auto]" data-output-contributor-row>
                    <label class="block"><span class="text-xs font-semibold text-slate-600">ชื่อผู้มีส่วนร่วม <span class="bo-required">*</span></span><select name="contributors[${index}][user_id]" required class="${f}" data-back-office-person-select data-placeholder="กรุณาเลือกผู้มีส่วนร่วม" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้มีส่วนร่วม"><option value="">กรุณาเลือกผู้มีส่วนร่วม</option>${thaiPersonOptionHtml}</select></label>
                    <div class="flex items-end justify-end"><button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-output-contributor>ลบ</button></div>
                </div>`;
            };

            const buildConferencePresenterRow = (index, mode = 'quick') => {
                const f = mode === 'inline' ? fieldClassInline : '';
                return `<div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-conference-presenter-row data-conditional-person-row data-show-idle-person-select>
                    <label class="block"><span class="text-xs font-semibold text-slate-600">ประเภทผู้นำเสนอ <span class="bo-required">*</span></span><select name="conference_presenters[${index}][person_type]" class="${f}" data-person-type-select><option value="">กรุณาเลือก</option><option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option><option value="external">คนนอก</option></select></label>
                    <div class="hidden" data-person-select-wrap><span class="text-xs font-semibold text-slate-600">เลือกผู้นำเสนอ <span class="bo-required">*</span></span><select class="${f}" data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้นำเสนอ" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ"><option value="">เลือกผู้นำเสนอ</option>${optionHtml}</select></div>
                    <label data-person-manual-wrap><span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้นำเสนอ <span class="bo-required">*</span></span><input class="${f}" placeholder="เลือกประเภทผู้นำเสนอก่อน" data-idle-placeholder="เลือกประเภทผู้นำเสนอก่อน" data-external-placeholder="ชื่อ - นามสกุล ผู้นำเสนอ" data-person-manual-input disabled></label>
                    <input type="hidden" name="conference_presenters[${index}][name]" data-person-name-hidden>
                    <div class="flex items-end justify-end"><button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-conference-presenter>ลบผู้นำเสนอ</button></div>
                </div>`;
            };

            const buildAwardRecipientRow = (index, mode = 'quick') => {
                const f = mode === 'inline' ? fieldClassInline : '';
                return `<div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-award-recipient-row data-conditional-person-row data-show-idle-person-select>
                    <label class="block"><span class="text-xs font-semibold text-slate-600">คนในหน่วย / คนนอก <span class="bo-required">*</span></span><select name="award_recipients[${index}][person_type]" class="${f}" data-person-type-select><option value="">กรุณาเลือก</option><option value="unit">หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option><option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option><option value="external">คนนอก</option></select></label>
                    <div class="hidden" data-person-select-wrap><span class="text-xs font-semibold text-slate-600">ชื่อผู้ได้รับรางวัล <span class="bo-required">*</span></span><select class="${f}" data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้ได้รับรางวัล" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล"><option value="">เลือกผู้ได้รับรางวัล</option>${optionHtml}</select></div>
                    <label class="hidden" data-person-manual-wrap><span class="text-xs font-semibold text-slate-600">ชื่อผู้ได้รับรางวัล <span class="bo-required">*</span></span><input class="${f}" placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน" data-idle-placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน" data-external-placeholder="ชื่อผู้ได้รับรางวัล" data-unit-placeholder="หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ" data-person-manual-input disabled></label>
                    <input type="hidden" name="award_recipients[${index}][name]" data-person-name-hidden>
                    <div class="flex items-end justify-end"><button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-award-recipient>ลบรายชื่อ</button></div>
                </div>`;
            };

            const buildBookAuthorRow = (index, mode = 'quick') => {
                const f = mode === 'inline' ? fieldClassInline : '';
                return `<div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-book-author-row data-conditional-person-row data-show-idle-person-select>
                    <label class="block"><span class="text-xs font-semibold text-slate-600">ประเภทผู้เขียน <span class="bo-required">*</span></span><select name="book_authors[${index}][person_type]" class="${f}" data-person-type-select><option value="">กรุณาเลือก</option><option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option><option value="external">คนนอก</option></select></label>
                    <div class="hidden" data-person-select-wrap><span class="text-xs font-semibold text-slate-600">เลือกชื่อผู้เขียน <span class="bo-required">*</span></span><select class="${f}" data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้เขียน" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้เขียน"><option value="">เลือกผู้เขียน</option>${optionHtml}</select></div>
                    <label class="hidden" data-person-manual-wrap><span class="text-xs font-semibold text-slate-600">ชื่อผู้เขียน <span class="bo-required">*</span></span><input class="${f}" placeholder="เลือกประเภทผู้เขียนก่อน" data-idle-placeholder="เลือกประเภทผู้เขียนก่อน" data-external-placeholder="ชื่อผู้เขียน" data-person-manual-input disabled></label>
                    <input type="hidden" name="book_authors[${index}][name]" data-person-name-hidden>
                    <div class="flex items-end justify-end"><button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-book-author>ลบ</button></div>
                </div>`;
            };

            const reindexAwardRecipientRows = (list) => {
                list.querySelectorAll('[data-award-recipient-row]').forEach((row, index) => {
                    row.querySelectorAll('input[name^="award_recipients["], select[name^="award_recipients["]').forEach((field) => {
                        field.name = field.name.replace(/award_recipients\[\d+\]/, `award_recipients[${index}]`);
                    });
                });
            };

            document.addEventListener('click', (event) => {
                const addContributor = event.target.closest('[data-add-output-contributor]');
                if (addContributor) {
                    const list = addContributor.closest('div')?.parentElement?.querySelector('[data-output-contributor-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-output-contributor-row]').length;
                    const mode = addContributor.closest('[data-back-office-output-edit-card]') ? 'inline' : 'quick';
                    list.insertAdjacentHTML('beforeend', buildOutputContributorRow(index, mode));
                    window.initBackOfficePersonSelect?.(list.lastElementChild);
                    return;
                }

                const removeContributor = event.target.closest('[data-remove-output-contributor]');
                if (removeContributor) {
                    const list = removeContributor.closest('[data-output-contributor-list]');
                    removeContributor.closest('[data-output-contributor-row]')?.remove();
                    if (list && !list.querySelector('[data-output-contributor-row]')) {
                        list.insertAdjacentHTML('beforeend', buildOutputContributorRow(0, removeContributor.closest('[data-back-office-output-edit-card]') ? 'inline' : 'quick'));
                        window.initBackOfficePersonSelect?.(list.lastElementChild);
                    }
                    if (list) reindexNamedRows(list, '[data-output-contributor-row]', ['contributors']);
                    return;
                }

                const addQuickAuthor = event.target.closest('[data-add-quick-author]');
                if (addQuickAuthor) {
                    const list = addQuickAuthor.closest('#quick-output-ea101')?.querySelector('[data-quick-author-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-author-row]').length;
                    list.insertAdjacentHTML('beforeend', buildAuthorRow(index, 'quick'));
                    window.bindBackOfficeConditionalPersonRow?.(list.lastElementChild);
                    return;
                }

                const addInlineAuthor = event.target.closest('[data-add-inline-author]');
                if (addInlineAuthor) {
                    const list = addInlineAuthor.closest('[data-inline-output-ea101]')?.querySelector('[data-inline-author-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-author-row]').length;
                    list.insertAdjacentHTML('beforeend', buildAuthorRow(index, 'inline'));
                    window.bindBackOfficeConditionalPersonRow?.(list.lastElementChild);
                    return;
                }

                const addQuickConferencePresenter = event.target.closest('[data-add-quick-conference-presenter]');
                if (addQuickConferencePresenter) {
                    const list = addQuickConferencePresenter.closest('#quick-output-ea103')?.querySelector('[data-quick-conference-presenter-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-conference-presenter-row]').length;
                    list.insertAdjacentHTML('beforeend', buildConferencePresenterRow(index, 'quick'));
                    window.bindBackOfficeConditionalPersonRow?.(list.lastElementChild);
                    return;
                }

                const addInlineConferencePresenter = event.target.closest('[data-add-inline-conference-presenter]');
                if (addInlineConferencePresenter) {
                    const list = addInlineConferencePresenter.closest('[data-inline-output-ea103]')?.querySelector('[data-inline-conference-presenter-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-conference-presenter-row]').length;
                    list.insertAdjacentHTML('beforeend', buildConferencePresenterRow(index, 'inline'));
                    window.bindBackOfficeConditionalPersonRow?.(list.lastElementChild);
                    return;
                }

                const addQuick = event.target.closest('[data-add-quick-award-recipient]');
                if (addQuick) {
                    const list = addQuick.closest('#quick-output-ea118')?.querySelector('[data-quick-award-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-award-recipient-row]').length;
                    list.insertAdjacentHTML('beforeend', buildAwardRecipientRow(index, 'quick'));
                    window.bindBackOfficeConditionalPersonRow?.(list.lastElementChild);
                    return;
                }

                const addInline = event.target.closest('[data-add-inline-award-recipient]');
                if (addInline) {
                    const list = addInline.closest('[data-inline-output-ea118]')?.querySelector('[data-inline-award-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-award-recipient-row]').length;
                    list.insertAdjacentHTML('beforeend', buildAwardRecipientRow(index, 'inline'));
                    window.bindBackOfficeConditionalPersonRow?.(list.lastElementChild);
                    return;
                }

                const addQuickBookAuthor = event.target.closest('[data-add-quick-book-author]');
                if (addQuickBookAuthor) {
                    const list = addQuickBookAuthor.closest('#quick-output-ea119')?.querySelector('[data-quick-book-author-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-book-author-row]').length;
                    list.insertAdjacentHTML('beforeend', buildBookAuthorRow(index, 'quick'));
                    window.bindBackOfficeConditionalPersonRow?.(list.lastElementChild);
                    return;
                }

                const addInlineBookAuthor = event.target.closest('[data-add-inline-book-author]');
                if (addInlineBookAuthor) {
                    const list = addInlineBookAuthor.closest('[data-inline-output-ea119]')?.querySelector('[data-inline-book-author-list]');
                    if (!list) return;
                    const index = list.querySelectorAll('[data-book-author-row]').length;
                    list.insertAdjacentHTML('beforeend', buildBookAuthorRow(index, 'inline'));
                    window.bindBackOfficeConditionalPersonRow?.(list.lastElementChild);
                    return;
                }

                const removeAuthor = event.target.closest('[data-remove-author]');
                if (removeAuthor) {
                    const list = removeAuthor.closest('[data-quick-author-list], [data-inline-author-list]');
                    removeAuthor.closest('[data-author-row]')?.remove();
                    if (list) reindexNamedRows(list, '[data-author-row]', ['authors']);
                    return;
                }

                const removeConferencePresenter = event.target.closest('[data-remove-conference-presenter]');
                if (removeConferencePresenter) {
                    const list = removeConferencePresenter.closest('[data-quick-conference-presenter-list], [data-inline-conference-presenter-list]');
                    removeConferencePresenter.closest('[data-conference-presenter-row]')?.remove();
                    if (list) reindexNamedRows(list, '[data-conference-presenter-row]', ['conference_presenters']);
                    return;
                }

                const removeBtn = event.target.closest('[data-remove-award-recipient]');
                if (removeBtn) {
                    const list = removeBtn.closest('[data-quick-award-list], [data-inline-award-list]');
                    removeBtn.closest('[data-award-recipient-row]')?.remove();
                    if (list) {
                        reindexAwardRecipientRows(list);
                    }
                    return;
                }

                const removeBookAuthor = event.target.closest('[data-remove-book-author]');
                if (removeBookAuthor) {
                    const list = removeBookAuthor.closest('[data-quick-book-author-list], [data-inline-book-author-list]');
                    removeBookAuthor.closest('[data-book-author-row]')?.remove();
                    if (list) reindexNamedRows(list, '[data-book-author-row]', ['book_authors']);
                }
            });
        })();

        (() => {
            const getInlineEditStash = () => {
                let stash = document.getElementById('back-office-inline-edit-stash');
                if (!stash) {
                    stash = document.createElement('div');
                    stash.id = 'back-office-inline-edit-stash';
                    stash.hidden = true;
                    document.body.appendChild(stash);
                }
                return stash;
            };

            const clearInlineEditRows = (activeCard = null) => {
                document.querySelectorAll('.back-office-inline-edit-row').forEach((row) => {
                    if (activeCard && row.contains(activeCard)) return;
                    row.remove();
                });
                document.querySelectorAll('[data-output-special-for].hidden').forEach((row) => {
                    row.classList.remove('hidden');
                });
            };

            const insertInlineEditRow = (sourceRow, card, targetId) => {
                if (!sourceRow || !card) return null;

                clearInlineEditRows(card);

                let editRow = sourceRow.nextElementSibling?.matches?.('.back-office-inline-edit-row')
                    ? sourceRow.nextElementSibling
                    : null;

                if (!editRow) {
                    editRow = document.createElement('tr');
                    editRow.className = 'back-office-inline-edit-row';
                    const cell = document.createElement('td');
                    cell.colSpan = sourceRow.children.length || 1;
                    editRow.appendChild(cell);
                    sourceRow.parentNode.insertBefore(editRow, sourceRow.nextSibling);
                }

                const cell = editRow.querySelector('td');
                cell.colSpan = sourceRow.children.length || cell.colSpan || 1;
                cell.appendChild(card);

                const safeTargetId = window.CSS?.escape
                    ? CSS.escape(targetId)
                    : String(targetId).replace(/["\\]/g, '\\$&');
                const specialRow = document.querySelector(`[data-output-special-for="${safeTargetId}"]`);
                specialRow?.classList.add('hidden');

                return editRow;
            };

            const revealCardById = (id, mode = 'edit', sourceRow = null) => {
                const card = id ? document.getElementById(id) : null;
                if (!card) return;

                const inlineRow = mode === 'edit' && sourceRow
                    ? insertInlineEditRow(sourceRow, card, id)
                    : null;

                card.classList.toggle('bo-edit-inline-mode', Boolean(inlineRow));
                card.classList.add('bo-card-expanded');
                card.classList.remove('hidden');

                if (mode === 'edit') {
                    const editCard = card.querySelector('[data-back-office-entry-edit-card], [data-back-office-output-edit-card]');
                    if (editCard) {
                        editCard.classList.remove('hidden');
                        window.initBackOfficePersonSelect?.(editCard);
                        editCard.querySelectorAll('[data-conditional-person-row]').forEach((row) => window.bindBackOfficeConditionalPersonRow?.(row));
                        window.__backOfficeInitDatepickers?.(editCard);
                        editCard.querySelector('input, select, textarea')?.focus?.();
                    } else {
                        const editToggle = card.querySelector('[data-back-office-entry-edit-toggle], [data-back-office-output-edit-toggle]');
                        editToggle?.click();
                    }
                    (inlineRow || card).scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    clearInlineEditRows();
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            };

            window.__backOfficeCloseInlineEditCard = (editCard) => {
                if (!(editCard instanceof HTMLElement)) return false;

                const recordCard = editCard.closest('.back-office-detail-card');
                const inlineRow = recordCard?.closest('.back-office-inline-edit-row');
                if (!recordCard || !inlineRow) return false;

                editCard.classList.add('hidden');
                recordCard.classList.remove('bo-edit-inline-mode', 'bo-card-expanded');
                recordCard.classList.add('hidden');

                if (recordCard.id) {
                    const safeTargetId = window.CSS?.escape
                        ? CSS.escape(recordCard.id)
                        : String(recordCard.id).replace(/["\\]/g, '\\$&');
                    document.querySelector(`[data-output-special-for="${safeTargetId}"]`)?.classList.remove('hidden');
                }

                getInlineEditStash().appendChild(recordCard);
                inlineRow.remove();
                return true;
            };

            document.addEventListener('click', (event) => {
                const edit = event.target.closest('[data-bo-table-edit]');
                if (edit) {
                    revealCardById(edit.dataset.targetCard, 'edit', edit.closest('tr'));
                }
            });
        })();

        (() => {
            const spotlightQuickCard = (card) => {
                if (!(card instanceof HTMLElement)) return;
                card.classList.remove('back-office-quick-card--spotlight');
                void card.offsetWidth;
                card.classList.add('back-office-quick-card--spotlight');
                window.clearTimeout(card.__spotlightTimer);
                card.__spotlightTimer = window.setTimeout(() => {
                    card.classList.remove('back-office-quick-card--spotlight');
                }, 3800);
            };

            const revealQuickCard = (card, offset = 132) => {
                if (!(card instanceof HTMLElement)) return;
                const scrollToTarget = () => {
                    const rect = card.getBoundingClientRect();
                    const top = Math.max(window.scrollY + rect.top - offset, 0);
                    window.scrollTo({ top, behavior: 'smooth' });
                };
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => {
                        scrollToTarget();
                        window.setTimeout(scrollToTarget, 240);
                        spotlightQuickCard(card);
                        window.setTimeout(() => {
                            card.querySelector('input, select, textarea')?.focus?.();
                        }, 360);
                    });
                });
            };

            const revealQuickOutputCardFromDock = (card) => {
                if (!(card instanceof HTMLElement)) return;
                const scrollToTarget = () => {
                    const rect = card.getBoundingClientRect();
                    const top = Math.max(window.scrollY + rect.top - 124, 0);
                    window.scrollTo({ top, behavior: 'smooth' });
                };
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => {
                        scrollToTarget();
                        window.setTimeout(scrollToTarget, 220);
                        window.setTimeout(scrollToTarget, 520);
                        spotlightQuickCard(card);
                        window.setTimeout(() => {
                            card.querySelector('input, select, textarea')?.focus?.();
                        }, 380);
                    });
                });
            };

            const card = document.getElementById('quick-output-card');
            const openBtn = document.getElementById('open-quick-output');
            const closeBtn = document.getElementById('close-quick-output');
            const cancelBtn = document.getElementById('cancel-quick-output');
            const typeSelect = document.getElementById('quick-output-type');
            const ea101 = document.getElementById('quick-output-ea101');
            const ea103 = document.getElementById('quick-output-ea103');
            const ea118 = document.getElementById('quick-output-ea118');
            const ea119 = document.getElementById('quick-output-ea119');
            if (!card || !openBtn) return;

            const syncPanels = () => {
                const type = (typeSelect?.value || '').trim();
                ea101?.classList.toggle('hidden', !type.startsWith('EA101'));
                ea103?.classList.toggle('hidden', !type.startsWith('EA103'));
                ea118?.classList.toggle('hidden', !type.startsWith('EA118'));
                ea119?.classList.toggle('hidden', !type.startsWith('EA119'));
            };

            const openCard = (event) => {
                event?.preventDefault?.();
                card.classList.remove('hidden');
                syncPanels();
                card.querySelectorAll('[data-conditional-person-row]').forEach((row) => window.bindBackOfficeConditionalPersonRow?.(row));
                revealQuickCard(card, 36);
            };

            const openCardFromDock = (event) => {
                event?.preventDefault?.();
                card.classList.remove('hidden');
                syncPanels();
                card.querySelectorAll('[data-conditional-person-row]').forEach((row) => window.bindBackOfficeConditionalPersonRow?.(row));
                revealQuickOutputCardFromDock(card);
            };

            const closeCard = () => {
                card.classList.add('hidden');
            };

            window.__backOfficeOpenQuickOutput = openCard;
            window.__backOfficeOpenQuickOutputFromDock = openCardFromDock;
            window.__backOfficeCloseQuickOutput = closeCard;

            openBtn.addEventListener('click', openCard);
            closeBtn?.addEventListener('click', closeCard);
            cancelBtn?.addEventListener('click', closeCard);
            typeSelect?.addEventListener('change', syncPanels);
            syncPanels();
        })();

        (() => {
            const dock = document.querySelector('.back-office-dock');
            const toggle = document.querySelector('[data-back-office-dock-toggle]');
            const setDockOpen = (open) => {
                dock?.classList.toggle('is-open', open);
                toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
                const icon = toggle?.querySelector('span[aria-hidden="true"]');
                if (icon) icon.textContent = open ? '×' : '＋';
            };

            toggle?.addEventListener('click', (event) => {
                event.preventDefault();
                setDockOpen(!dock?.classList.contains('is-open'));
            });

            document.querySelector('[data-back-office-dock-entry]')?.addEventListener('click', (event) => {
                event.preventDefault();
                setDockOpen(false);
                window.__backOfficeOpenQuickEntry?.();
            });

            document.querySelector('[data-back-office-dock-output]')?.addEventListener('click', (event) => {
                event.preventDefault();
                setDockOpen(false);
                window.__backOfficeOpenQuickOutputFromDock?.();
            });

            document.querySelector('[data-back-office-dock-edit]')?.addEventListener('click', (event) => {
                event.preventDefault();
                setDockOpen(false);
                document.querySelector('[data-back-office-global-edit]')?.click();
            });

            document.querySelector('[data-back-office-dock-pdf]')?.addEventListener('click', (event) => {
                event.preventDefault();
                setDockOpen(false);
                document.querySelector('[data-back-office-global-pdf]')?.click();
            });

            document.querySelector('[data-back-office-dock-delete]')?.addEventListener('click', (event) => {
                event.preventDefault();
                setDockOpen(false);
                document.querySelector('[data-back-office-global-delete]')?.click();
            });
        })();

        (() => {
            const spotlightQuickCard = (card) => {
                if (!(card instanceof HTMLElement)) return;
                card.classList.remove('back-office-quick-card--spotlight');
                void card.offsetWidth;
                card.classList.add('back-office-quick-card--spotlight');
                window.clearTimeout(card.__spotlightTimer);
                card.__spotlightTimer = window.setTimeout(() => {
                    card.classList.remove('back-office-quick-card--spotlight');
                }, 3800);
            };

            const revealQuickCard = (card) => {
                if (!(card instanceof HTMLElement)) return;
                const scrollToTarget = () => {
                    const rect = card.getBoundingClientRect();
                    const top = Math.max(window.scrollY + rect.top - 132, 0);
                    window.scrollTo({ top, behavior: 'smooth' });
                };
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => {
                        scrollToTarget();
                        window.setTimeout(scrollToTarget, 220);
                        spotlightQuickCard(card);
                        window.setTimeout(() => {
                            card.querySelector('input, select, textarea')?.focus?.();
                        }, 320);
                    });
                });
            };

            const card = document.getElementById('quick-entry-card');
            const openBtn = document.getElementById('open-quick-entry');
            const closeBtn = document.getElementById('close-quick-entry');
            const cancelBtn = document.getElementById('cancel-quick-entry');
            if (!card || !openBtn) return;

            const openCard = (event) => {
                event?.preventDefault?.();
                card.classList.remove('hidden');
                window.initBackOfficePersonSelect?.(card);
                revealQuickCard(card);
            };

            const closeCard = () => {
                card.classList.add('hidden');
            };

            window.__backOfficeOpenQuickEntry = openCard;
            window.__backOfficeCloseQuickEntry = closeCard;

            openBtn.addEventListener('click', openCard);
            closeBtn?.addEventListener('click', closeCard);
            cancelBtn?.addEventListener('click', closeCard);
        })();

        (() => {
            document.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-back-office-entry-edit-toggle]');
                if (toggle) {
                    const card = toggle.closest('[data-back-office-entry-card]')?.querySelector('[data-back-office-entry-edit-card]');
                    if (!card) return;
                    card.classList.remove('hidden');
                    window.initBackOfficePersonSelect?.(card);
                    window.__backOfficeInitDatepickers?.(card);
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.querySelector('input, select, textarea')?.focus?.();
                    return;
                }

                const closeBtn = event.target.closest('[data-back-office-entry-edit-close]');
                if (closeBtn) {
                    const card = closeBtn.closest('[data-back-office-entry-edit-card]');
                    if (!window.__backOfficeCloseInlineEditCard?.(card)) {
                        card?.classList.add('hidden');
                    }
                }
            });
        })();

        (() => {
            const spotlightQuickCard = (card) => {
                if (!(card instanceof HTMLElement)) return;
                card.classList.remove('back-office-quick-card--spotlight');
                void card.offsetWidth;
                card.classList.add('back-office-quick-card--spotlight');
                window.clearTimeout(card.__spotlightTimer);
                card.__spotlightTimer = window.setTimeout(() => {
                    card.classList.remove('back-office-quick-card--spotlight');
                }, 3800);
            };

            const revealQuickCard = (card, offset = 132) => {
                if (!(card instanceof HTMLElement)) return;
                const scrollToTarget = () => {
                    const rect = card.getBoundingClientRect();
                    const top = Math.max(window.scrollY + rect.top - offset, 0);
                    window.scrollTo({ top, behavior: 'smooth' });
                };

                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => {
                        scrollToTarget();
                        window.setTimeout(scrollToTarget, 220);
                        window.setTimeout(scrollToTarget, 520);
                        spotlightQuickCard(card);
                        window.setTimeout(() => {
                            card.querySelector('input, select, textarea')?.focus?.();
                        }, 360);
                    });
                });
            };

            const syncQuickOutputPanels = () => {
                const card = document.getElementById('quick-output-card');
                const type = document.getElementById('quick-output-type')?.value?.trim() || '';
                if (!card) return;
                card.querySelector('#quick-output-ea101')?.classList.toggle('hidden', !type.startsWith('EA101'));
                card.querySelector('#quick-output-ea103')?.classList.toggle('hidden', !type.startsWith('EA103'));
                card.querySelector('#quick-output-ea118')?.classList.toggle('hidden', !type.startsWith('EA118'));
                card.querySelector('#quick-output-ea119')?.classList.toggle('hidden', !type.startsWith('EA119'));
            };

            const openQuickOutput = (event, offset = 36) => {
                event?.preventDefault?.();
                const card = document.getElementById('quick-output-card');
                if (!card) return;
                card.classList.remove('hidden');
                syncQuickOutputPanels();
                window.initBackOfficePersonSelect?.(card);
                card.querySelectorAll('[data-conditional-person-row]').forEach((row) => window.bindBackOfficeConditionalPersonRow?.(row));
                window.__backOfficeInitDatepickers?.(card);
                revealQuickCard(card, offset);
            };

            const closeQuickOutput = () => {
                document.getElementById('quick-output-card')?.classList.add('hidden');
            };

            const openQuickEntry = (event) => {
                event?.preventDefault?.();
                const card = document.getElementById('quick-entry-card');
                if (!card) return;
                card.classList.remove('hidden');
                window.initBackOfficePersonSelect?.(card);
                window.__backOfficeInitDatepickers?.(card);
                revealQuickCard(card, 132);
            };

            const closeQuickEntry = () => {
                document.getElementById('quick-entry-card')?.classList.add('hidden');
            };

            window.__backOfficeOpenQuickOutput = (event) => openQuickOutput(event, 36);
            window.__backOfficeOpenQuickOutputFromDock = (event) => openQuickOutput(event, 124);
            window.__backOfficeCloseQuickOutput = closeQuickOutput;
            window.__backOfficeOpenQuickEntry = openQuickEntry;
            window.__backOfficeCloseQuickEntry = closeQuickEntry;
            window.__backOfficeAfterDetailRefresh = (root = document) => {
                window.initBackOfficePersonSelect?.(root);
                root.querySelectorAll?.('[data-conditional-person-row]').forEach((row) => window.bindBackOfficeConditionalPersonRow?.(row));
                syncQuickOutputPanels();
                window.__backOfficeInitDatepickers?.(root);
                window.__backOfficeApplyListSearch?.();
            };

            document.addEventListener('click', (event) => {
                if (event.defaultPrevented) return;

                if (event.target.closest('#open-quick-output')) {
                    openQuickOutput(event, 36);
                    return;
                }

                if (event.target.closest('#close-quick-output') || event.target.closest('#cancel-quick-output')) {
                    event.preventDefault();
                    closeQuickOutput();
                    return;
                }

                if (event.target.closest('#open-quick-entry')) {
                    openQuickEntry(event);
                    return;
                }

                if (event.target.closest('#close-quick-entry') || event.target.closest('#cancel-quick-entry')) {
                    event.preventDefault();
                    closeQuickEntry();
                }
            });

            document.addEventListener('change', (event) => {
                if (event.target.closest('#quick-output-type')) {
                    syncQuickOutputPanels();
                }
            });
        })();

        (() => {
            const syncInlineOutputPanels = (scope) => {
                const root = scope instanceof HTMLElement ? scope : document;
                root.querySelectorAll('[data-back-office-output-edit-card]').forEach((card) => {
                    const type = card.querySelector('[data-inline-output-type]')?.value?.trim() || '';
                    card.querySelector('[data-inline-output-ea101]')?.classList.toggle('hidden', !type.startsWith('EA101'));
                    card.querySelector('[data-inline-output-ea103]')?.classList.toggle('hidden', !type.startsWith('EA103'));
                    card.querySelector('[data-inline-output-ea118]')?.classList.toggle('hidden', !type.startsWith('EA118'));
                    card.querySelector('[data-inline-output-ea119]')?.classList.toggle('hidden', !type.startsWith('EA119'));
                });
            };

            document.addEventListener('change', (event) => {
                if (event.target.matches('[data-inline-output-type]')) {
                    syncInlineOutputPanels(event.target.closest('[data-back-office-output-edit-card]'));
                }
            });

            document.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-back-office-output-edit-toggle]');
                if (toggle) {
                    const card = toggle.closest('[data-back-office-output-card]')?.querySelector('[data-back-office-output-edit-card]');
                    if (!card) return;
                    card.classList.remove('hidden');
                    syncInlineOutputPanels(card);
                    card.querySelectorAll('[data-conditional-person-row]').forEach((row) => window.bindBackOfficeConditionalPersonRow?.(row));
                    window.__backOfficeInitDatepickers?.(card);
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.querySelector('input, select, textarea')?.focus?.();
                    return;
                }

                const closeBtn = event.target.closest('[data-back-office-output-edit-close]');
                if (closeBtn) {
                    const card = closeBtn.closest('[data-back-office-output-edit-card]');
                    if (!window.__backOfficeCloseInlineEditCard?.(card)) {
                        card?.classList.add('hidden');
                    }
                }
            });

            syncInlineOutputPanels(document);
        })();

        (() => {
            const escapeHtml = window.__backOfficeEscapeHtml;
            const cssEscape = (value) => window.CSS?.escape ? CSS.escape(String(value)) : String(value).replace(/["\\]/g, '\\$&');
            const setSectionCount = (sectionSelector, count) => {
                if (!Number.isFinite(Number(count))) return;
                const countNode = document.querySelector(`${sectionSelector} .back-office-section__count`);
                if (countNode) countNode.textContent = `รวม ${Number(count).toLocaleString('th-TH')} รายการ`;
            };

            document.addEventListener('submit', (event) => {
                const form = event.target?.closest('form[data-back-office-output-delete-form]');
                if (!form) return;

                event.preventDefault();

                const title = form.dataset.outputTitle || '-';
                const type = form.dataset.outputType || '-';
                const year = form.dataset.outputYear || '-';
                const fallbackText = `ยืนยันลบผลงาน\nชื่อผลงาน: ${title}\nประเภท: ${type}\nวันที่เผยแพร่: ${year}`;

                const submitOutputDelete = async () => {
                    const submitButton = form.querySelector('button[type="submit"]');
                    submitButton?.setAttribute('disabled', 'disabled');

                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });
                        if (!response.ok) {
                            throw new Error('Delete failed');
                        }
                        const payload = await response.json().catch(() => ({}));

                        const outputId = form.dataset.outputId || form.closest('[data-back-office-output-card]')?.id?.replace('output-', '');
                        if (outputId) {
                            document.querySelectorAll(`[data-back-office-output-id="${cssEscape(outputId)}"]`).forEach((row) => row.remove());
                            document.querySelectorAll(`[data-output-special-for="output-${cssEscape(outputId)}"]`).forEach((row) => row.remove());
                            document.getElementById(`output-${outputId}`)?.remove();
                        }
                        const card = form.closest('[data-back-office-output-card]');
                        card?.remove();
                        document.querySelectorAll('.back-office-inline-edit-row').forEach((row) => {
                            if (!row.querySelector('.back-office-detail-card')) row.remove();
                        });
                        setSectionCount('#back-office-output-section', payload.remaining_outputs);

                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'ลบผลงานเรียบร้อย',
                                timer: 1400,
                                showConfirmButton: false,
                            });
                        }
                    } catch (error) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'ลบไม่สำเร็จ',
                                text: 'กรุณาลองใหม่อีกครั้ง',
                                confirmButtonColor: '#dc2626',
                            });
                        } else {
                            window.alert('ลบไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
                        }
                        submitButton?.removeAttribute('disabled');
                    }
                };

                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ยืนยันการลบผลงาน',
                        html: `ชื่อผลงาน: ${escapeHtml(title)}<br>ประเภท: ${escapeHtml(type)}<br>วันที่เผยแพร่: ${escapeHtml(year)}`,
                        showCancelButton: true,
                        confirmButtonText: 'ลบผลงานนี้',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: '#dc2626'
                    }).then((result) => {
                        if (result.isConfirmed) submitOutputDelete();
                    });
                    return;
                }

                if (window.confirm(fallbackText)) {
                    submitOutputDelete();
                }
            });
        })();

        (() => {
            const syncBackOfficeDatePlaceholders = () => {
                document.querySelectorAll('.js-date-be[placeholder]').forEach((input) => {
                    const placeholder = input.getAttribute('placeholder') || '';
                    const altInput = input._flatpickr?.altInput;
                    if (!altInput || !placeholder) return;
                    altInput.placeholder = placeholder;
                    altInput.setAttribute('placeholder', placeholder);
                    if (!altInput.value) altInput.value = '';
                });
            };
            window.__backOfficeSyncDatePlaceholders = syncBackOfficeDatePlaceholders;

            let tries = 0;
            const timer = window.setInterval(() => {
                syncBackOfficeDatePlaceholders();
                tries += 1;
                if (tries >= 20) window.clearInterval(timer);
            }, 250);

            window.setTimeout(syncBackOfficeDatePlaceholders, 50);
            window.setTimeout(syncBackOfficeDatePlaceholders, 500);
        })();

        (() => {
            const formatBackOfficeBeText = (date) => {
                if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
                const day = date.getDate();
                const month = date.toLocaleDateString('th-TH', { month: 'long' });
                const year = date.getFullYear() + 543;
                return `${day} ${month} ${year}`;
            };

            const forceBackOfficeBeDisplay = (inst) => {
                const altInput = inst?.altInput;
                const selectedDate = inst?.selectedDates?.[0];
                if (!(altInput instanceof HTMLInputElement) || !(selectedDate instanceof Date) || Number.isNaN(selectedDate.getTime())) return;
                altInput.value = formatBackOfficeBeText(selectedDate);
            };

            const forceBackOfficeBeYear = (inst) => {
                const cal = inst?.calendarContainer;
                if (!cal) return;

                const yearInput = cal.querySelector('.flatpickr-current-month input.cur-year');
                if (yearInput) {
                    const year = parseInt(yearInput.value || '0', 10);
                    if (!Number.isNaN(year) && year > 0 && year < 2400) {
                        yearInput.value = String(year + 543);
                    }
                }

                ['.flatpickr-prev-month', '.flatpickr-next-month'].forEach((selector) => {
                    const btn = cal.querySelector(selector);
                    if (!btn || btn.dataset.boBeBound === '1') return;
                    btn.dataset.boBeBound = '1';
                    btn.addEventListener('click', () => {
                        requestAnimationFrame(() => forceBackOfficeBeYear(inst));
                        window.setTimeout(() => forceBackOfficeBeYear(inst), 0);
                    });
                });
            };

            const bindBackOfficeBeDatepickers = (scope = document) => {
                scope.querySelectorAll('.js-date-be').forEach((input) => {
                    const inst = input?._flatpickr;
                    if (!inst || input.dataset.boBeInit === '1') return;
                    input.dataset.boBeInit = '1';

                    const sync = () => {
                        forceBackOfficeBeDisplay(inst);
                        requestAnimationFrame(() => forceBackOfficeBeYear(inst));
                        window.setTimeout(() => forceBackOfficeBeYear(inst), 0);
                    };

                    inst.altInput?.addEventListener('focus', sync);
                    inst.altInput?.addEventListener('click', sync);
                    inst.altInput?.addEventListener('input', sync);
                    inst.altInput?.addEventListener('blur', sync);
                    sync();
                });
            };
            window.__backOfficeBindBeDatepickers = bindBackOfficeBeDatepickers;

            window.__backOfficeInitDatepickers = (scope = document) => {
                const root = scope instanceof HTMLElement ? scope : document;
                if (typeof initDateBE === 'function') {
                    initDateBE(root);
                }
                window.__backOfficeSyncDatePlaceholders?.();
                bindBackOfficeBeDatepickers(root);
                window.setTimeout(() => {
                    window.__backOfficeSyncDatePlaceholders?.();
                    bindBackOfficeBeDatepickers(root);
                }, 50);
            };

            let tries = 0;
            const timer = window.setInterval(() => {
                window.__backOfficeInitDatepickers(document);
                tries += 1;
                if (tries >= 20) window.clearInterval(timer);
            }, 250);

            window.setTimeout(() => window.__backOfficeInitDatepickers(document), 50);
            window.setTimeout(() => window.__backOfficeInitDatepickers(document), 500);
        })();

        (() => {
            const shell = document.querySelector('.back-office-shell');
            const nav = document.getElementById('main-nav');
            if (!shell) return;

            let ticking = false;
            const syncStickyTop = () => {
                ticking = false;
                const navVisible = nav instanceof HTMLElement && !nav.classList.contains('nav-hidden');
                const top = navVisible ? Math.ceil(nav.getBoundingClientRect().height) : 0;
                shell.style.setProperty('--bo-table-sticky-top', `${top}px`);
            };

            const schedule = () => {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(syncStickyTop);
            };

            syncStickyTop();
            window.addEventListener('scroll', schedule, { passive: true });
            window.addEventListener('resize', schedule);
            if (nav instanceof HTMLElement) {
                new MutationObserver(schedule).observe(nav, { attributes: true, attributeFilter: ['class', 'style'] });
            }
        })();

    </script>
@endsection
