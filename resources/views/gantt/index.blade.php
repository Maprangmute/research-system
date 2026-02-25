@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --border: #d8dee9;
            --text-main: #0f172a;
            --text-soft: #475569;
            --brand: #0f766e;
            --brand-strong: #115e59;
            --bg-soft: #f8fafc;
        }

        .gantt-page {
            font-family: "IBM Plex Sans Thai", "IBM Plex Sans", "Noto Sans Thai", sans-serif;
            color: var(--text-main);
        }

        .gantt-orientation-guard {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(130deg, #0f172a 0%, #1e293b 45%, #334155 100%);
        }

        html.gantt-device-unsupported .gantt-page {
            display: none !important;
        }

        html.gantt-device-unsupported .gantt-orientation-guard {
            display: flex;
        }

        .gantt-orientation-card {
            width: min(520px, 100%);
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 45px rgba(2, 6, 23, 0.28);
            padding: 20px 18px;
            text-align: center;
        }

        .gantt-orientation-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
        }

        .gantt-orientation-desc {
            margin: 10px 0 14px;
            font-size: 0.92rem;
            line-height: 1.5;
            color: #334155;
        }

        .gantt-orientation-status {
            margin: 0 0 14px;
            font-size: 0.82rem;
            color: #64748b;
        }

        .gantt-orientation-actions {
            display: flex;
            justify-content: center;
        }

        .gantt-fullbleed {
            width: 100vw;
            margin-left: calc(50% - 50vw);
            padding: 10px 12px;
        }

        .gantt-shell {
            background: #fff;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .gantt-head {
            align-items: flex-start;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            background: linear-gradient(120deg, #eef2ff 0%, #ecfeff 55%, #f0fdf4 100%);
            border-bottom: 1px solid var(--border);
        }

        .gantt-title {
            margin: 0;
            font-size: 1.6rem;
            line-height: 1.2;
            letter-spacing: -0.01em;
        }

        .gantt-subtitle {
            margin: 4px 0 0;
            font-size: 0.88rem;
            color: var(--text-soft);
        }

        .gantt-back {
            border: 1px solid var(--border);
            color: #1f2937;
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 600;
            padding: 6px 11px;
            background: #fff;
            white-space: nowrap;
        }

        .gantt-back:hover {
            background: #f8fafc;
        }

        .gantt-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            background: #fff;
        }

        .gantt-tools,
        .gantt-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .gantt-zoom-group {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 4px;
            border: 1px solid var(--border);
            background: #fff;
        }

        .gantt-zoom-value {
            min-width: 48px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 700;
            color: #334155;
        }

        .gantt-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            padding: 8px 16px;
            background: #fff;
        }

        .gantt-mobile-tools {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            padding: 8px 10px;
            background: #fff;
        }

        .gantt-mobile-scale {
            min-width: 108px;
        }

        .gantt-nav-mobile {
            display: none;
        }

        .gantt-nav-btn {
            width: 34px;
            min-width: 34px;
            height: 30px;
            border: 1px solid var(--border);
            background: #fff;
            color: #1f2937;
            font-weight: 700;
            cursor: pointer;
            line-height: 1;
        }

        .gantt-nav-btn:hover {
            background: #f8fafc;
        }

        .gantt-nav-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .gantt-nav-range {
            flex: 1 1 auto;
            width: 100%;
            accent-color: var(--brand);
        }

        .gantt-nav-meta {
            min-width: 84px;
            text-align: right;
            color: #64748b;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .gantt-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
        }

        .gantt-input,
        .gantt-select,
        .gantt-btn {
            height: 34px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 0.84rem;
            color: #0f172a;
            padding: 0 10px;
        }

        .gantt-input {
            min-width: 230px;
        }

        .gantt-select {
            min-width: 120px;
        }

        .gantt-btn {
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .gantt-btn:hover {
            background: #f8fafc;
        }

        .gantt-btn-primary {
            border-color: var(--brand);
            background: var(--brand);
            color: #fff;
        }

        .gantt-btn-primary:hover {
            background: var(--brand-strong);
            border-color: var(--brand-strong);
        }

        .gantt-btn-soft {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .gantt-btn-soft:hover {
            background: #dbeafe;
        }

        .gantt-state {
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            background: #fff;
            padding: 8px 16px;
            min-height: 38px;
        }

        #save-state {
            font-size: 0.83rem;
            font-weight: 700;
        }

        #save-meta {
            color: #64748b;
            font-size: 0.78rem;
        }

        .state-idle { color: #475569; }
        .state-dirty { color: #b45309; }
        .state-saving { color: #0369a1; }
        .state-saved { color: #0f766e; }
        .state-error { color: #b91c1c; }

        #gantt_here {
            width: 100%;
            height: calc(100vh - 290px);
            min-height: 780px;
        }

        .gantt_task_line.gantt-task-fte {
            background: #e0e7ff !important;
            border-color: #818cf8 !important;
            color: #312e81 !important;
        }

        .gantt_task_line.gantt-task-fte .gantt_task_progress {
            background: #a5b4fc !important;
        }

        .gantt_task_line.gantt-task-completed {
            background: #bbf7d0 !important;
            border-color: #34d399 !important;
            color: #14532d !important;
        }

        .gantt_task_line.gantt-task-completed .gantt_task_progress {
            background: #86efac !important;
        }

        .gantt_task_line.gantt-task-in-progress {
            background: #fef3c7 !important;
            border-color: #fbbf24 !important;
            color: #92400e !important;
        }

        .gantt_task_line.gantt-task-in-progress .gantt_task_progress {
            background: #fde68a !important;
        }

        .gantt_task_line.gantt-task-delayed {
            background: #fee2e2 !important;
            border-color: #f87171 !important;
            color: #991b1b !important;
        }

        .gantt_task_line.gantt-task-delayed .gantt_task_progress {
            background: #fca5a5 !important;
        }

        .gantt_task_line.gantt-task-starting {
            background: #dbeafe !important;
            border-color: #60a5fa !important;
            color: #1e3a8a !important;
        }

        .gantt_task_line.gantt-task-starting .gantt_task_progress {
            background: #93c5fd !important;
        }

        .gantt_task_line.gantt-task-paused {
            background: #f3e8ff !important;
            border-color: #c084fc !important;
            color: #6b21a8 !important;
        }

        .gantt_task_line.gantt-task-paused .gantt_task_progress {
            background: #d8b4fe !important;
        }

        .gantt_task_line.gantt-task-not-started {
            background: #e2e8f0 !important;
            border-color: #94a3b8 !important;
            color: #334155 !important;
        }

        .gantt_task_line.gantt-task-not-started .gantt_task_progress {
            background: #cbd5e1 !important;
        }

        .gantt_task_line {
            color: #0f172a !important;
        }

        .gantt_task_line .gantt_task_content,
        .gantt_task_line .gantt_task_progress .gantt_task_content {
            color: inherit !important;
            font-weight: 600;
            text-shadow: none !important;
        }

        .gantt-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border: 1px solid transparent;
        }

        .gantt-status-in-progress {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #9a3412;
        }

        .gantt-status-completed {
            background: #dcfce7;
            border-color: #86efac;
            color: #166534;
        }

        .gantt-status-delayed {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
        }

        .gantt-status-starting {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1e3a8a;
        }

        .gantt-status-paused {
            background: #f3e8ff;
            border-color: #d8b4fe;
            color: #6b21a8;
        }

        .gantt-status-not-started {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: #334155;
        }

        .gantt-status-fte {
            background: #e0e7ff;
            border-color: #c7d2fe;
            color: #3730a3;
        }

        .gantt-progress-pill {
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 1px 7px;
        }

        .gantt-icon-btn {
            border: 0;
            background: transparent;
            padding: 0;
            margin: 0;
            width: 20px;
            height: 20px;
            cursor: pointer;
            color: #334155;
            opacity: 0.88;
            vertical-align: middle;
        }

        .gantt-icon-btn svg {
            width: 20px;
            height: 20px;
            display: block;
            stroke: currentColor;
        }

        .gantt-icon-btn:hover {
            opacity: 1;
        }

        .gantt-icon-add {
            color: #0f766e;
        }

        .gantt-icon-delete {
            color: #b91c1c;
        }

        .gantt-task-cell-wrap {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            line-height: 1.15;
            gap: 1px;
            padding: 2px 0;
            width: 100%;
            min-height: 100%;
            height: 100%;
        }

        .gantt-task-title {
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gantt-task-owner {
            font-size: 11px;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gantt-inline-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 3px;
            min-height: 14px;
            width: 100%;
        }

        .gantt-inline-action {
            border: 0;
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
            color: #0f766e;
            cursor: pointer;
            white-space: nowrap;
        }

        .gantt-inline-action:hover {
            text-decoration: underline;
        }

        .gantt-inline-action.subtask {
            color: #1d4ed8;
        }

        .gantt-inline-sep {
            color: #94a3b8;
            font-size: 11px;
            line-height: 1;
            user-select: none;
        }

        .gantt_tree_content {
            white-space: nowrap !important;
            line-height: 1.15;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 100%;
            height: 100%;
        }

        .gantt_grid_data .gantt_cell {
            display: flex !important;
            align-items: center !important;
            height: 100% !important;
            min-height: 100% !important;
            line-height: normal !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            box-sizing: border-box;
        }

        .gantt_grid_data .gantt_cell > * {
            min-height: 100%;
            box-sizing: border-box;
        }

        .gantt_grid_data .gantt_cell[data-column-name="text"] {
            align-items: center !important;
            justify-content: flex-start;
        }

        .gantt_grid_data .gantt_cell[data-column-name="text"] .gantt_tree_content {
            width: 100%;
            height: 100%;
            min-height: 100%;
            display: flex !important;
            align-items: center !important;
        }

        .gantt_grid_data .gantt_cell[data-column-name="workflow_status"],
        .gantt_grid_data .gantt_cell[data-column-name="progress_percent"],
        .gantt_grid_data .gantt_cell[data-column-name="start_date"],
        .gantt_grid_data .gantt_cell[data-column-name="end_date"],
        .gantt_grid_data .gantt_cell[data-column-name="quick_add"],
        .gantt_grid_data .gantt_cell[data-column-name="quick_delete"] {
            justify-content: center !important;
        }

        .gantt-cell-center {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            min-height: 100%;
        }

        .status-color-select {
            font-weight: 700;
        }

        .status-color-select.status-not-started {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: #334155;
        }

        .status-color-select.status-starting {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1e3a8a;
        }

        .status-color-select.status-in-progress {
            background: #fef3c7;
            border-color: #fcd34d;
            color: #92400e;
        }

        .status-color-select.status-paused {
            background: #f3e8ff;
            border-color: #d8b4fe;
            color: #6b21a8;
        }

        .status-color-select.status-delayed {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
        }

        .status-color-select.status-completed {
            background: #dcfce7;
            border-color: #86efac;
            color: #166534;
        }

        #drawer-status option[value="not_started"],
        .gantt_grid_editor select option[value="not_started"] {
            background: #e2e8f0;
            color: #334155;
        }

        #drawer-status option[value="starting"],
        .gantt_grid_editor select option[value="starting"] {
            background: #dbeafe;
            color: #1e3a8a;
        }

        #drawer-status option[value="in_progress"],
        .gantt_grid_editor select option[value="in_progress"] {
            background: #fef3c7;
            color: #92400e;
        }

        #drawer-status option[value="paused"],
        .gantt_grid_editor select option[value="paused"] {
            background: #f3e8ff;
            color: #6b21a8;
        }

        #drawer-status option[value="delayed"],
        .gantt_grid_editor select option[value="delayed"] {
            background: #fee2e2;
            color: #991b1b;
        }

        #drawer-status option[value="completed"],
        .gantt_grid_editor select option[value="completed"] {
            background: #dcfce7;
            color: #166534;
        }

        #task-drawer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.38);
            z-index: 1800;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease;
        }

        #task-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: min(470px, 95vw);
            height: 100vh;
            z-index: 1801;
            background: #fff;
            border-left: 1px solid var(--border);
            transform: translateX(100%);
            transition: transform 0.24s ease;
            display: flex;
            flex-direction: column;
        }

        #task-drawer.open {
            transform: translateX(0);
        }

        #task-drawer-backdrop.open {
            opacity: 1;
            visibility: visible;
        }

        .drawer-head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--bg-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .drawer-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .drawer-close {
            border: 1px solid var(--border);
            background: #fff;
            width: 32px;
            height: 32px;
            cursor: pointer;
        }

        .drawer-body {
            padding: 16px;
            overflow: auto;
            flex: 1;
            padding-bottom: 20px;
        }

        .drawer-field {
            margin-bottom: 13px;
        }

        .drawer-field label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
        }

        .drawer-field input,
        .drawer-field select,
        .drawer-field textarea {
            width: 100%;
            border: 1px solid var(--border);
            min-height: 37px;
            padding: 7px 10px;
            font-size: 0.88rem;
            color: #0f172a;
            background: #fff;
        }

        .drawer-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .drawer-foot {
            padding: 12px 16px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            background: #fff;
            position: sticky;
            bottom: 0;
            z-index: 2;
        }

        .drawer-foot .gantt-actions {
            margin-left: auto;
        }

        #drawer-save,
        #drawer-save-inline {
            background: #0f766e !important;
            border-color: #0f766e !important;
            color: #ffffff !important;
        }

        #drawer-save:hover,
        #drawer-save-inline:hover {
            background: #115e59 !important;
            border-color: #115e59 !important;
        }

        .danger-link {
            color: #b91c1c;
            font-weight: 700;
            border: 0;
            background: none;
            cursor: pointer;
            font-size: 0.84rem;
        }

        #task-action-drawer {
            position: fixed;
            top: 0;
            left: 0;
            width: min(440px, calc(100vw - 24px));
            background: #ffffff;
            border: 1px solid #dbe2ea;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
            z-index: 1751;
            transform: translateY(-6px) scale(0.96);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: transform 0.14s ease, opacity 0.14s ease;
        }

        #task-action-drawer.open {
            transform: translateY(0) scale(1);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .task-action-head {
            display: none;
        }

        .task-action-body {
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .task-action-btn {
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            color: #0f172a;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.2;
            cursor: pointer;
            padding: 8px 12px;
            margin: 0;
            border-radius: 10px;
            transition: background 0.14s ease, border-color 0.14s ease, color 0.14s ease;
        }

        .task-action-btn:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .task-action-sep {
            color: #cbd5e1;
            font-size: 12px;
            line-height: 1;
        }

        @media (max-width: 1024px) {
            #gantt_here {
                height: calc(100vh - 320px);
                min-height: 640px;
            }
        }

        @media (max-width: 720px) {
            .gantt-title {
                font-size: 1.3rem;
            }

            .gantt-input {
                min-width: 170px;
            }

            .gantt-mobile-tools {
                display: flex;
            }

            .gantt-nav-desktop {
                display: none;
            }

            .gantt-nav-mobile {
                display: flex;
            }

            .gantt-nav {
                gap: 6px;
                padding: 7px 10px;
            }

            .gantt-nav-meta {
                display: none;
            }

            .drawer-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="gantt-page gantt-fullbleed">
        <div class="gantt-shell">
            <div class="gantt-head">
                <div>
                    <h2 class="gantt-title">Gantt Board: {{ $project->project_name_th }}</h2>
                    <p class="gantt-subtitle">จัดการงานง่ายขึ้นด้วย autosave, filter แบบ realtime และซิงก์ข้อมูลอัตโนมัติ</p>
                </div>
                <a class="gantt-back" href="javascript:history.back()">ย้อนกลับ</a>
            </div>

            <div class="gantt-toolbar">
                <div class="gantt-tools">
                    <span class="gantt-label">มุมมอง</span>
                    <select id="scale-select" class="gantt-select">
                        <option value="year" selected>รายปี</option>
                        <option value="month">รายเดือน</option>
                        <option value="week">รายสัปดาห์</option>
                        <option value="day">รายวัน</option>
                    </select>

                    <input id="filter-search" class="gantt-input" type="text" placeholder="ค้นหา task / owner / id...">

                    <select id="filter-status" class="gantt-select">
                        <option value="all">ทุกสถานะ</option>
                        <option value="not_started">ยังไม่เริ่มทำ</option>
                        <option value="starting">กำลังเริ่ม</option>
                        <option value="in_progress">กำลังดำเนินการ</option>
                        <option value="paused">พักการทำ</option>
                        <option value="delayed">ล้าช้า</option>
                        <option value="completed">เสร็จสิ้น</option>
                        <option value="fte">งานภาระงาน (จาก FTE)</option>
                    </select>

                    <input id="filter-owner" class="gantt-input" type="text" placeholder="กรองตามผู้รับผิดชอบ...">
                </div>

                <div class="gantt-actions">
                    <div class="gantt-zoom-group" role="group" aria-label="zoom controls">
                        <button id="zoom-out" type="button" class="gantt-btn" title="ซูมออก">-</button>
                        <span id="zoom-level" class="gantt-zoom-value">100%</span>
                        <button id="zoom-in" type="button" class="gantt-btn" title="ซูมเข้า">+</button>
                    </div>
                    <button id="btn-add" type="button" class="gantt-btn gantt-btn-soft">+ เพิ่มงาน</button>
                </div>
            </div>

            <div class="gantt-state">
                <span id="save-state" class="state-idle">พร้อมใช้งาน</span>
                <span id="save-meta"></span>
            </div>

            <div class="gantt-mobile-tools" aria-label="mobile quick controls">
                <select id="scale-select-mobile" class="gantt-select gantt-mobile-scale">
                    <option value="year" selected>รายปี</option>
                    <option value="month">รายเดือน</option>
                    <option value="week">รายสัปดาห์</option>
                    <option value="day">รายวัน</option>
                </select>
                <div class="gantt-zoom-group" role="group" aria-label="mobile zoom controls">
                    <button id="zoom-out-mobile" type="button" class="gantt-btn" title="ซูมออก">-</button>
                    <span id="zoom-level-mobile" class="gantt-zoom-value">100%</span>
                    <button id="zoom-in-mobile" type="button" class="gantt-btn" title="ซูมเข้า">+</button>
                </div>
            </div>

            <div class="gantt-nav gantt-nav-desktop" aria-label="chart horizontal navigation">
                <button id="nav-left" type="button" class="gantt-nav-btn" title="เลื่อนไปซ้าย" aria-label="เลื่อนไปซ้าย">◀</button>
                <input id="nav-scroll-x" type="range" class="gantt-nav-range" min="0" max="0" value="0" step="1" aria-label="เลื่อนซ้ายขวา chart">
                <button id="nav-right" type="button" class="gantt-nav-btn" title="เลื่อนไปขวา" aria-label="เลื่อนไปขวา">▶</button>
                <span id="nav-meta" class="gantt-nav-meta">0%</span>
            </div>

            <div class="gantt-nav gantt-nav-mobile" aria-label="mobile chart horizontal navigation">
                <button id="nav-left-mobile" type="button" class="gantt-nav-btn" title="เลื่อนไปซ้าย" aria-label="เลื่อนไปซ้าย">◀</button>
                <input id="nav-scroll-x-mobile" type="range" class="gantt-nav-range" min="0" max="0" value="0" step="1" aria-label="เลื่อนซ้ายขวา chart">
                <button id="nav-right-mobile" type="button" class="gantt-nav-btn" title="เลื่อนไปขวา" aria-label="เลื่อนไปขวา">▶</button>
                <span id="nav-meta-mobile" class="gantt-nav-meta">0%</span>
            </div>

            <div id="gantt_here"></div>
        </div>
    </div>

    <section id="orientation-guard" class="gantt-orientation-guard" aria-hidden="true" aria-live="polite">
        <div class="gantt-orientation-card">
            <h3 class="gantt-orientation-title">ยังไม่รองรับการใช้งานบนโทรศัพท์</h3>
            <p class="gantt-orientation-desc">หน้าจอ Gantt รองรับเฉพาะแท็บเล็ตหรือคอมพิวเตอร์เท่านั้น</p>
            <p id="orientation-status" class="gantt-orientation-status">กรุณาเปิดผ่านอุปกรณ์หน้าจอใหญ่</p>
            <div class="gantt-orientation-actions">
                <button id="orientation-back-btn" type="button" class="gantt-btn">ย้อนกลับ</button>
            </div>
        </div>
    </section>

    <aside id="task-action-drawer" aria-hidden="true">
        <div class="task-action-body">
            <button id="task-action-add" type="button" class="task-action-btn">+ เพิ่มงานหลัก</button>
            <span class="task-action-sep">|</span>
            <button id="task-action-add-subtask" type="button" class="task-action-btn">+ เพิ่มงานรอง</button>
        </div>
    </aside>

    <div id="task-drawer-backdrop"></div>
    <aside id="task-drawer" aria-hidden="true">
        <div class="drawer-head">
            <h3 class="drawer-title">จัดการงาน</h3>
            <button id="drawer-close" class="drawer-close" type="button">✕</button>
        </div>

        <div class="drawer-body">
            <input id="drawer-task-id" type="hidden" value="">

            <div class="drawer-field">
                <label for="drawer-title">ชื่องาน</label>
                <textarea id="drawer-title" rows="3" placeholder="ระบุชื่องาน"></textarea>
            </div>

            <div class="drawer-field">
                <label for="drawer-parent">งานหลัก</label>
                <select id="drawer-parent"></select>
            </div>

            <div class="drawer-row">
                <div class="drawer-field">
                    <label for="drawer-start">เริ่มต้น</label>
                    <input id="drawer-start" type="text" class="js-gantt-date-th" autocomplete="off" placeholder="วว-ดด-ปปปป">
                </div>

                <div class="drawer-field">
                    <label for="drawer-end">สิ้นสุด</label>
                    <input id="drawer-end" type="text" class="js-gantt-date-th" autocomplete="off" placeholder="วว-ดด-ปปปป">
                </div>
            </div>

            <div class="drawer-row">
                <div class="drawer-field">
                    <label for="drawer-status">สถานะ</label>
                    <select id="drawer-status" class="status-color-select">
                        <option value="not_started">ยังไม่เริ่มทำ</option>
                        <option value="starting">กำลังเริ่ม</option>
                        <option value="in_progress">กำลังดำเนินการ</option>
                        <option value="paused">พักการทำ</option>
                        <option value="delayed">ล้าช้า</option>
                        <option value="completed">เสร็จสิ้น</option>
                    </select>
                </div>

                <div class="drawer-field">
                    <label for="drawer-progress">ความคืบหน้า (%)</label>
                    <select id="drawer-progress">
                        <option value="0">0%</option>
                        <option value="25">25%</option>
                        <option value="50">50%</option>
                        <option value="100">100%</option>
                    </select>
                </div>
            </div>

            <div class="drawer-field">
                <label for="drawer-owner">ผู้รับผิดชอบ</label>
                <input id="drawer-owner" type="text" placeholder="ชื่อผู้รับผิดชอบ">
            </div>

            <div class="drawer-field">
                <button id="drawer-save-inline" type="button" class="gantt-btn gantt-btn-primary" style="width:100%;">เพิ่มงาน</button>
            </div>
        </div>

        <div class="drawer-foot">
            <button id="drawer-delete" type="button" class="danger-link" style="display: none;">ลบงาน</button>

            <div class="gantt-actions">
                <button id="drawer-cancel" type="button" class="gantt-btn">ยกเลิก</button>
                <button id="drawer-save" type="button" class="gantt-btn gantt-btn-primary">บันทึก</button>
            </div>
        </div>
    </aside>

    <script>
        (() => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const projectId = @json((int) $project->id);
            const projectRef = @json((string) $project->getRouteKey());

            const tasksUrl = `/projects/${encodeURIComponent(projectRef)}/tasks`;
            const linksUrl = `/projects/${encodeURIComponent(projectRef)}/task-links`;
            const fteTasksUrl = `/projects/${encodeURIComponent(projectRef)}/fte-tasks`;
            const saveUrl = '/tasks/bulk-save';

            const FTE_ROOT_ID = 'fte-root';
            const FTE_PREFIX = 'fte-';
            const FTE_ORDER_BREAK = 1000000;
            const AUTO_SAVE_DELAY = 600;
            const AUTO_FETCH_INTERVAL = 0;
            const ENABLE_FOCUS_REFETCH = false;

            const STATUS_CONFIG = {
                not_started: { label: 'ยังไม่เริ่มทำ', badge: 'gantt-status-not-started', taskClass: 'gantt-task-not-started' },
                starting: { label: 'กำลังเริ่ม', badge: 'gantt-status-starting', taskClass: 'gantt-task-starting' },
                in_progress: { label: 'กำลังดำเนินการ', badge: 'gantt-status-in-progress', taskClass: 'gantt-task-in-progress' },
                paused: { label: 'พักการทำ', badge: 'gantt-status-paused', taskClass: 'gantt-task-paused' },
                delayed: { label: 'ล้าช้า', badge: 'gantt-status-delayed', taskClass: 'gantt-task-delayed' },
                completed: { label: 'เสร็จสิ้น', badge: 'gantt-status-completed', taskClass: 'gantt-task-completed' },
                fte: { label: 'งานภาระงาน (จาก FTE)', badge: 'gantt-status-fte', taskClass: 'gantt-task-fte' },
            };

            const WORKFLOW_STATUS_KEYS = ['not_started', 'starting', 'in_progress', 'paused', 'delayed', 'completed'];
            const PROGRESS_STEPS = [0, 25, 50, 100];
            const ZOOM_PERCENT_LEVELS = [60, 75, 90, 100, 115, 130, 150, 175, 200];
            const THAI_MONTH_FULL = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
            const THAI_MONTH_SHORT = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            const THAI_DAY_FULL = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
            const THAI_DAY_SHORT = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];

            const saveStateEl = document.getElementById('save-state');
            const saveMetaEl = document.getElementById('save-meta');

            const filterState = {
                search: '',
                status: 'all',
                owner: '',
            };

            let loadedTaskIds = [];
            let hasPendingChanges = false;
            let isSaving = false;
            let isHydrating = false;
            let isNormalizingUpdate = false;
            let autosaveTimer = null;
            let autoFetchTimer = null;
            let isGanttReady = false;
            let createTaskLockUntil = 0;
            let actionTargetTaskId = null;
            let lastServerSignature = '';
            let clickSuppressUntil = 0;
            let pointerDragGuardBound = false;
            let pointerDownPoint = null;
            let pointerDragged = false;
            let lastPointerDragAt = 0;
            const scaleSelectEl = document.getElementById('scale-select');
            const scaleSelectMobileEl = document.getElementById('scale-select-mobile');
            let currentScale = scaleSelectEl?.value || scaleSelectMobileEl?.value || 'year';
            let zoomLevelIndex = Math.max(0, ZOOM_PERCENT_LEVELS.indexOf(100));
            let navSyncFrame = 0;
            let orientationBindingsReady = false;
            let drawerDatepickerReady = false;

            const taskActionDrawer = document.getElementById('task-action-drawer');
            const taskActionAdd = document.getElementById('task-action-add');
            const taskActionAddSubtask = document.getElementById('task-action-add-subtask');
            const orientationGuardEl = document.getElementById('orientation-guard');
            const orientationStatusEl = document.getElementById('orientation-status');
            const orientationBackBtn = document.getElementById('orientation-back-btn');

            const zoomInBtn = document.getElementById('zoom-in');
            const zoomOutBtn = document.getElementById('zoom-out');
            const zoomLevelEl = document.getElementById('zoom-level');
            const zoomInMobileBtn = document.getElementById('zoom-in-mobile');
            const zoomOutMobileBtn = document.getElementById('zoom-out-mobile');
            const zoomLevelMobileEl = document.getElementById('zoom-level-mobile');
            const navLeftBtn = document.getElementById('nav-left');
            const navRightBtn = document.getElementById('nav-right');
            const navScrollXEl = document.getElementById('nav-scroll-x');
            const navMetaEl = document.getElementById('nav-meta');
            const navLeftMobileBtn = document.getElementById('nav-left-mobile');
            const navRightMobileBtn = document.getElementById('nav-right-mobile');
            const navScrollXMobileEl = document.getElementById('nav-scroll-x-mobile');
            const navMetaMobileEl = document.getElementById('nav-meta-mobile');

            const drawer = document.getElementById('task-drawer');
            const drawerBackdrop = document.getElementById('task-drawer-backdrop');
            const drawerTitleText = drawer?.querySelector('.drawer-title');
            const drawerTaskId = document.getElementById('drawer-task-id');
            const drawerTitle = document.getElementById('drawer-title');
            const drawerParent = document.getElementById('drawer-parent');
            const drawerStart = document.getElementById('drawer-start');
            const drawerEnd = document.getElementById('drawer-end');
            const drawerStatus = document.getElementById('drawer-status');
            const drawerProgress = document.getElementById('drawer-progress');
            const drawerOwner = document.getElementById('drawer-owner');
            const drawerSave = document.getElementById('drawer-save');
            const drawerSaveInline = document.getElementById('drawer-save-inline');
            const drawerDelete = document.getElementById('drawer-delete');

            function isNumericId(value) {
                if (value === null || value === undefined || value === '') {
                    return false;
                }

                const number = Number(value);
                return Number.isInteger(number) && number > 0;
            }

            function toClock(date) {
                return date.toLocaleTimeString('th-TH', {
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }

            function toGanttProgress(raw) {
                const value = Number(raw ?? 0);
                if (!Number.isFinite(value)) {
                    return 0;
                }

                return value > 1 ? Math.max(0, Math.min(1, value / 100)) : Math.max(0, Math.min(1, value));
            }

            function toBackendProgress(raw) {
                const value = Number(raw ?? 0);
                if (!Number.isFinite(value)) {
                    return 0;
                }

                const percent = value <= 1 ? value * 100 : value;
                return Math.max(0, Math.min(100, Math.round(percent)));
            }

            function normalizeProgressStep(raw) {
                const normalized = toBackendProgress(raw);
                return PROGRESS_STEPS.reduce((closest, value) => (
                    Math.abs(value - normalized) < Math.abs(closest - normalized) ? value : closest
                ), PROGRESS_STEPS[0]);
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#39;');
            }

            function toStatusClass(status) {
                const normalized = String(status || '').trim().toLowerCase().replaceAll('_', '-');
                return `status-${normalized}`;
            }

            function applyStatusSelectClass(selectEl, status) {
                if (!(selectEl instanceof HTMLSelectElement)) {
                    return;
                }

                selectEl.classList.add('status-color-select');
                [
                    'status-not-started',
                    'status-starting',
                    'status-in-progress',
                    'status-paused',
                    'status-delayed',
                    'status-completed',
                ].forEach((className) => selectEl.classList.remove(className));
                selectEl.classList.add(toStatusClass(status));
            }

            function applyThaiLocale() {
                if (!gantt?.locale?.date) {
                    return;
                }

                gantt.locale.date.month_full = THAI_MONTH_FULL;
                gantt.locale.date.month_short = THAI_MONTH_SHORT;
                gantt.locale.date.day_full = THAI_DAY_FULL;
                gantt.locale.date.day_short = THAI_DAY_SHORT;
            }

            function normalizeLinkType(rawType) {
                if (rawType === null || rawType === undefined || rawType === '') {
                    return 0;
                }

                if (isNumericId(rawType) || String(rawType) === '0') {
                    const typeNum = Number(rawType);
                    if (Number.isInteger(typeNum) && typeNum >= 0 && typeNum <= 3) {
                        return typeNum;
                    }
                }

                const typeText = String(rawType).trim().toLowerCase();
                const map = { e2s: 0, s2s: 1, e2e: 2, s2e: 3 };
                return map[typeText] ?? 0;
            }

            function toIsoDate(value) {
                const date = value instanceof Date ? value : new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }

                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function toDmyDate(value) {
                const date = value instanceof Date ? value : new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }

                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${day}-${month}-${year}`;
            }

            function toThaiDateBE(value, includeDay = true) {
                const date = value instanceof Date ? value : new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return '-';
                }

                const day = String(date.getDate()).padStart(2, '0');
                const month = THAI_MONTH_SHORT[date.getMonth()];
                const yearBE = date.getFullYear() + 543;
                return includeDay ? `${day} ${month} ${yearBE}` : `${month} ${yearBE}`;
            }

            function parseApiDate(raw) {
                if (raw === null || raw === undefined || raw === '') {
                    return null;
                }

                if (raw instanceof Date) {
                    const copy = new Date(raw.getTime());
                    return Number.isNaN(copy.getTime()) ? null : copy;
                }

                const toDate = (yearRaw, monthRaw, dayRaw) => {
                    let year = Number(yearRaw);
                    const month = Number(monthRaw);
                    const day = Number(dayRaw);

                    if (!Number.isInteger(year) || !Number.isInteger(month) || !Number.isInteger(day)) {
                        return null;
                    }

                    if (year > 2400) {
                        year -= 543;
                    }

                    const date = new Date(year, month - 1, day);
                    if (Number.isNaN(date.getTime())) {
                        return null;
                    }

                    if (date.getFullYear() !== year || (date.getMonth() + 1) !== month || date.getDate() !== day) {
                        return null;
                    }

                    date.setHours(0, 0, 0, 0);
                    return date;
                };

                const text = String(raw).trim();
                if (!text) {
                    return null;
                }

                let match = text.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
                if (match) {
                    return toDate(match[1], match[2], match[3]);
                }

                match = text.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
                if (match) {
                    return toDate(match[3], match[2], match[1]);
                }

                match = text.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
                if (match) {
                    return toDate(match[1], match[2], match[3]);
                }

                const value = /^\d{4}-\d{2}-\d{2}$/.test(text)
                    ? `${text}T00:00:00`
                    : text;
                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? null : date;
            }

            function toThaiDatePickerLabel(date) {
                if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
                    return '';
                }

                const day = date.getDate();
                const month = date.toLocaleDateString('th-TH', { month: 'long' });
                const yearBE = date.getFullYear() + 543;
                return `${day} ${month} ${yearBE}`;
            }

            function syncDrawerDateAlt(inst, selectedDates = []) {
                if (!inst?.altInput) {
                    return;
                }

                const selectedDate = selectedDates[0];
                inst.altInput.value = selectedDate ? toThaiDatePickerLabel(selectedDate) : '';
            }

            function syncDrawerDateAltStyle(originalInput, inst) {
                if (!originalInput || !inst?.altInput) {
                    return;
                }

                inst.altInput.className = originalInput.className;

                const placeholder = originalInput.getAttribute('placeholder') || '';
                if (placeholder) {
                    inst.altInput.placeholder = placeholder;
                }

                inst.altInput.readOnly = true;
                originalInput.classList.add('flatpickr-hidden');
                originalInput.setAttribute('aria-hidden', 'true');
                originalInput.tabIndex = -1;
                originalInput.style.display = 'none';
            }

            function initDrawerThaiDatepickers() {
                if (drawerDatepickerReady) {
                    return;
                }

                if (typeof flatpickr !== 'function') {
                    return;
                }

                const bindPicker = (input) => {
                    if (!(input instanceof HTMLInputElement) || input._flatpickr) {
                        return;
                    }

                    flatpickr(input, {
                        locale: 'th',
                        dateFormat: 'd-m-Y',
                        altInput: true,
                        altFormat: 'j F Y',
                        disableMobile: true,
                        allowInput: false,
                        onReady(selectedDates, _, inst) {
                            syncDrawerDateAltStyle(input, inst);
                            syncDrawerDateAlt(inst, selectedDates);
                            if (typeof forceHeaderBE === 'function') {
                                forceHeaderBE(inst);
                            }
                        },
                        onOpen(_, __, inst) {
                            if (typeof forceHeaderBE === 'function') {
                                forceHeaderBE(inst);
                            }
                        },
                        onMonthChange(_, __, inst) {
                            if (typeof forceHeaderBE === 'function') {
                                setTimeout(() => forceHeaderBE(inst), 0);
                            }
                        },
                        onYearChange(_, __, inst) {
                            if (typeof forceHeaderBE === 'function') {
                                setTimeout(() => forceHeaderBE(inst), 0);
                            }
                        },
                        onChange(selectedDates, _, inst) {
                            syncDrawerDateAlt(inst, selectedDates);
                            if (typeof forceHeaderBE === 'function') {
                                forceHeaderBE(inst);
                            }
                        },
                    });
                };

                bindPicker(drawerStart);
                bindPicker(drawerEnd);
                drawerDatepickerReady = Boolean(drawerStart?._flatpickr || drawerEnd?._flatpickr);
            }

            function setDrawerDateValue(input, value) {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                const parsed = parseApiDate(value);
                const instance = input._flatpickr;

                if (instance) {
                    if (parsed) {
                        instance.setDate(parsed, true, 'd-m-Y');
                    } else {
                        instance.clear();
                        input.value = '';
                    }
                    return;
                }

                input.value = parsed ? toDmyDate(parsed) : '';
            }

            function buildTimelineRange(scale) {
                if (!isGanttReady || typeof gantt.getSubtaskDates !== 'function') {
                    return null;
                }

                const range = gantt.getSubtaskDates();
                if (!range?.start_date || !range?.end_date) {
                    return null;
                }

                const start = new Date(range.start_date);
                const end = new Date(range.end_date);
                if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
                    return null;
                }

                start.setHours(0, 0, 0, 0);
                end.setHours(0, 0, 0, 0);

                let timelineStart = new Date(start);
                let timelineEnd = new Date(end);

                if (scale === 'year') {
                    timelineStart = new Date(start.getFullYear(), 0, 1);
                    timelineEnd = new Date(end.getFullYear() + 1, 0, 1);
                } else if (scale === 'month') {
                    timelineStart = new Date(start.getFullYear(), start.getMonth(), 1);
                    timelineEnd = new Date(end.getFullYear(), end.getMonth() + 1, 1);
                } else if (scale === 'week') {
                    const startWeekday = timelineStart.getDay();
                    const backToMonday = startWeekday === 0 ? 6 : startWeekday - 1;
                    timelineStart.setDate(timelineStart.getDate() - backToMonday);

                    const endWeekday = timelineEnd.getDay();
                    const forwardToSunday = endWeekday === 0 ? 0 : 7 - endWeekday;
                    timelineEnd.setDate(timelineEnd.getDate() + forwardToSunday + 1);
                } else {
                    timelineEnd.setDate(timelineEnd.getDate() + 1);
                }

                return { start: timelineStart, end: timelineEnd };
            }

            function isFteTask(task) {
                if (!task) {
                    return false;
                }

                if (task.is_fte || task.readonly) {
                    return true;
                }

                const id = String(task.id ?? '');
                return id === FTE_ROOT_ID || id.startsWith(FTE_PREFIX);
            }

            function normalizeWorkflowStatus(rawStatus, context = {}) {
                const input = String(rawStatus || '').trim().toLowerCase();
                if (WORKFLOW_STATUS_KEYS.includes(input)) {
                    return input;
                }

                if (input === 'done' || input === 'completed') {
                    return 'completed';
                }

                if (input === 'ongoing') {
                    return 'in_progress';
                }

                const progress = toBackendProgress(context.progress ?? 0);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const start = parseApiDate(context.start);
                const end = parseApiDate(context.end);

                if (progress >= 100) {
                    return 'completed';
                }

                if (end && end < today) {
                    return 'delayed';
                }

                if (start && start > today) {
                    return 'not_started';
                }

                if (progress > 0 && progress < 15) {
                    return 'starting';
                }

                return 'in_progress';
            }

            function statusBadgeHtml(task) {
                if (isFteTask(task)) {
                    return `<span class="gantt-status-badge ${STATUS_CONFIG.fte.badge}">${STATUS_CONFIG.fte.label}</span>`;
                }

                const workflowStatus = normalizeWorkflowStatus(task.workflow_status || task.status, {
                    progress: task.progress_percent ?? task.progress,
                    start: task.start_date,
                    end: task.end_date,
                });

                const conf = STATUS_CONFIG[workflowStatus] || STATUS_CONFIG.in_progress;
                return `<span class="gantt-status-badge ${conf.badge}">${conf.label}</span>`;
            }

            function isInlineEditableColumn(columnName) {
                return ['text', 'workflow_status', 'progress_percent', 'start_date', 'end_date'].includes(String(columnName || ''));
            }

            function startInlineEdit(taskId, columnName) {
                if (!isInlineEditableColumn(columnName)) {
                    return false;
                }

                if (!gantt.ext?.inlineEditors?.startEdit) {
                    return false;
                }

                const tryStart = () => {
                    try {
                        const started = gantt.ext.inlineEditors.startEdit(taskId, columnName);
                        const inlineState = gantt.ext.inlineEditors.getState?.();
                        const isOpened = inlineState && String(inlineState.id) === String(taskId);
                        return started === true || isOpened;
                    } catch (error) {
                        console.warn('Inline editor failed', error);
                        return false;
                    }
                };

                if (tryStart()) {
                    return true;
                }

                setTimeout(() => {
                    tryStart();
                }, 0);

                return true;
            }

            function closeTaskActionDrawer() {
                if (!taskActionDrawer) {
                    return;
                }

                taskActionDrawer.classList.remove('open');
                taskActionDrawer.setAttribute('aria-hidden', 'true');
                actionTargetTaskId = null;
            }

            function getTaskRowAnchor(taskId, fallbackEl = null) {
                if (fallbackEl instanceof Element) {
                    return fallbackEl;
                }

                const safeId = String(taskId ?? '').replaceAll('"', '\\"');
                if (!safeId) {
                    return null;
                }

                return document.querySelector(`.gantt_grid_data .gantt_row[task_id="${safeId}"]`)
                    || document.querySelector(`.gantt_row[task_id="${safeId}"]`);
            }

            function openTaskActionDrawer(task, anchorEl = null, clickPoint = null) {
                if (!taskActionDrawer || !task) {
                    return;
                }

                actionTargetTaskId = task.id;

                let resolvedAnchor = getTaskRowAnchor(task.id, anchorEl);
                let anchorRect = resolvedAnchor?.getBoundingClientRect?.() || null;
                if (anchorRect && (anchorRect.width < 6 || anchorRect.height < 6)) {
                    resolvedAnchor = getTaskRowAnchor(task.id);
                    anchorRect = resolvedAnchor?.getBoundingClientRect?.() || null;
                }

                const viewportPadding = 10;
                let drawerWidth = 360;
                if (anchorRect) {
                    drawerWidth = Math.max(280, anchorRect.width - 22);
                }
                drawerWidth = Math.min(520, drawerWidth);
                drawerWidth = Math.min(drawerWidth, window.innerWidth - (viewportPadding * 2));
                taskActionDrawer.style.width = `${drawerWidth}px`;

                const drawerHeight = taskActionDrawer.offsetHeight || 60;
                const gap = 4;

                let left = viewportPadding;
                let top = viewportPadding;

                if (anchorRect) {
                    left = anchorRect.left + ((anchorRect.width - drawerWidth) / 2);
                    top = anchorRect.bottom - gap;

                    if (top + drawerHeight > window.innerHeight - viewportPadding) {
                        top = anchorRect.top - drawerHeight + gap;
                    }
                } else if (clickPoint && Number.isFinite(clickPoint.x) && Number.isFinite(clickPoint.y)) {
                    left = clickPoint.x - (drawerWidth / 2);
                    top = clickPoint.y + gap;
                }

                left = Math.max(viewportPadding, Math.min(left, window.innerWidth - drawerWidth - viewportPadding));
                top = Math.max(viewportPadding, Math.min(top, window.innerHeight - drawerHeight - viewportPadding));
                taskActionDrawer.style.left = `${left}px`;
                taskActionDrawer.style.top = `${top}px`;

                taskActionDrawer.classList.add('open');
                taskActionDrawer.setAttribute('aria-hidden', 'false');
            }

            function initTaskActionDrawer() {
                if (!taskActionDrawer || !taskActionAdd || !taskActionAddSubtask) {
                    return;
                }

                taskActionAdd.addEventListener('click', () => {
                    if (!actionTargetTaskId || !gantt.isTaskExists(actionTargetTaskId)) {
                        return;
                    }

                    createTaskInline(actionTargetTaskId, 'main');
                    closeTaskActionDrawer();
                });

                taskActionAddSubtask.addEventListener('click', () => {
                    if (!actionTargetTaskId || !gantt.isTaskExists(actionTargetTaskId)) {
                        return;
                    }

                    createTaskInline(actionTargetTaskId, 'sub');
                    closeTaskActionDrawer();
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeTaskActionDrawer();
                    }
                });

                document.addEventListener('pointerdown', (event) => {
                    if (!taskActionDrawer.classList.contains('open')) {
                        return;
                    }

                    const target = event.target;
                    const isInDrawer = target instanceof Element && taskActionDrawer.contains(target);
                    const isOnGridCell = target instanceof Element && Boolean(target.closest('.gantt_cell'));
                    if (!isInDrawer && !isOnGridCell) {
                        closeTaskActionDrawer();
                    }
                });

                const ganttRoot = document.getElementById('gantt_here');
                if (ganttRoot) {
                    ganttRoot.addEventListener('scroll', closeTaskActionDrawer, true);
                }

                window.addEventListener('resize', closeTaskActionDrawer);
            }

            function maybeStyleStatusSelect(selectEl) {
                if (!(selectEl instanceof HTMLSelectElement)) {
                    return;
                }

                const optionValues = Array.from(selectEl.options).map((opt) => String(opt.value || '').trim());
                const hasAllStatuses = WORKFLOW_STATUS_KEYS.every((key) => optionValues.includes(key));
                if (!hasAllStatuses) {
                    return;
                }

                applyStatusSelectClass(selectEl, selectEl.value);

                if (!selectEl.dataset.statusColorBind) {
                    selectEl.addEventListener('change', () => {
                        applyStatusSelectClass(selectEl, selectEl.value);
                    });
                    selectEl.dataset.statusColorBind = '1';
                }
            }

            function normalizeParent(parent) {
                if (!isNumericId(parent)) {
                    return 0;
                }

                const parentId = Number(parent);
                if (!gantt.isTaskExists(parentId)) {
                    return 0;
                }

                const parentTask = gantt.getTask(parentId);
                return isFteTask(parentTask) ? 0 : parentId;
            }

            function suppressGridClick(ms = 220) {
                clickSuppressUntil = Date.now() + Math.max(0, Number(ms) || 0);
            }

            function wasRecentPointerDrag(ms = 900) {
                return (Date.now() - lastPointerDragAt) < Math.max(0, Number(ms) || 0);
            }

            function initPointerDragGuard() {
                if (pointerDragGuardBound) {
                    return;
                }

                const ganttRoot = document.getElementById('gantt_here');
                if (!(ganttRoot instanceof HTMLElement)) {
                    return;
                }

                const startTracking = (event) => {
                    if (event.button !== 0) {
                        return;
                    }

                    pointerDownPoint = { x: Number(event.clientX), y: Number(event.clientY) };
                    pointerDragged = false;
                };

                const moveTracking = (event) => {
                    if (!pointerDownPoint) {
                        return;
                    }

                    const dx = Math.abs(Number(event.clientX) - pointerDownPoint.x);
                    const dy = Math.abs(Number(event.clientY) - pointerDownPoint.y);
                    if ((dx + dy) > 6) {
                        pointerDragged = true;
                    }
                };

                const endTracking = () => {
                    if (pointerDownPoint && pointerDragged) {
                        lastPointerDragAt = Date.now();
                        suppressGridClick(900);
                    }

                    pointerDownPoint = null;
                    pointerDragged = false;
                };

                ganttRoot.addEventListener('pointerdown', startTracking, true);
                ganttRoot.addEventListener('pointermove', moveTracking, true);
                ganttRoot.addEventListener('pointerup', endTracking, true);
                ganttRoot.addEventListener('pointercancel', endTracking, true);

                pointerDragGuardBound = true;
            }

            function isPhoneLikeViewport() {
                const userAgent = (navigator.userAgent || '').toLowerCase();
                const phoneByUa = /iphone|ipod|android.+mobile|windows phone|opera mini|blackberry|iemobile/.test(userAgent);
                const narrow = window.matchMedia('(max-width: 767px)').matches;
                const coarse = window.matchMedia('(pointer: coarse)').matches;
                return phoneByUa || (narrow && coarse);
            }

            function isLandscapeRequiredBlocked() {
                return isPhoneLikeViewport();
            }

            function setOrientationStatus(message) {
                if (orientationStatusEl) {
                    orientationStatusEl.textContent = message;
                }
            }

            function updateLandscapeGuardState() {
                const blocked = isLandscapeRequiredBlocked();
                document.documentElement.classList.toggle('gantt-device-unsupported', blocked);

                if (orientationGuardEl) {
                    orientationGuardEl.setAttribute('aria-hidden', blocked ? 'false' : 'true');
                }

                if (blocked) {
                    closeTaskActionDrawer();
                    setOrientationStatus('กรุณาเปิดผ่านแท็บเล็ตหรือคอมพิวเตอร์');
                } else {
                    setOrientationStatus('พร้อมใช้งานแล้ว');
                }

                return blocked;
            }

            function bindOrientationGuardOnce() {
                if (orientationBindingsReady) {
                    return;
                }

                const handleViewportChange = () => {
                    const blocked = updateLandscapeGuardState();
                    if (!blocked) {
                        if (!hasBooted) {
                            bootGanttPage();
                            return;
                        }

                        if (isGanttReady) {
                            requestAnimationFrame(() => {
                                applyResponsiveGridLayout();
                                gantt.setSizes();
                                gantt.render();
                                queueHorizontalNavSync();
                            });
                        }
                    }
                };

                window.addEventListener('resize', handleViewportChange);
                window.addEventListener('orientationchange', handleViewportChange);

                if (orientationBackBtn) {
                    orientationBackBtn.addEventListener('click', () => {
                        if (window.history.length > 1) {
                            window.history.back();
                            return;
                        }

                        window.location.href = '/rm';
                    });
                }

                orientationBindingsReady = true;
            }

            function setSaveState(type, message, meta = '') {
                saveStateEl.textContent = message;
                saveMetaEl.textContent = meta;

                saveStateEl.classList.remove('state-idle', 'state-dirty', 'state-saving', 'state-saved', 'state-error');

                if (type === 'dirty') {
                    saveStateEl.classList.add('state-dirty');
                    return;
                }

                if (type === 'saving') {
                    saveStateEl.classList.add('state-saving');
                    return;
                }

                if (type === 'saved') {
                    saveStateEl.classList.add('state-saved');
                    return;
                }

                if (type === 'error') {
                    saveStateEl.classList.add('state-error');
                    return;
                }

                saveStateEl.classList.add('state-idle');
            }

            function getZoomFactor() {
                return (ZOOM_PERCENT_LEVELS[zoomLevelIndex] || 100) / 100;
            }

            function updateZoomUi() {
                const zoomValue = ZOOM_PERCENT_LEVELS[zoomLevelIndex] || 100;

                if (zoomLevelEl) {
                    zoomLevelEl.textContent = `${zoomValue}%`;
                }
                if (zoomLevelMobileEl) {
                    zoomLevelMobileEl.textContent = `${zoomValue}%`;
                }

                if (zoomOutBtn) {
                    zoomOutBtn.disabled = zoomLevelIndex <= 0;
                }
                if (zoomOutMobileBtn) {
                    zoomOutMobileBtn.disabled = zoomLevelIndex <= 0;
                }

                if (zoomInBtn) {
                    zoomInBtn.disabled = zoomLevelIndex >= (ZOOM_PERCENT_LEVELS.length - 1);
                }
                if (zoomInMobileBtn) {
                    zoomInMobileBtn.disabled = zoomLevelIndex >= (ZOOM_PERCENT_LEVELS.length - 1);
                }
            }

            function getHorizontalScrollMetrics() {
                const scrollState = (isGanttReady && typeof gantt.getScrollState === 'function')
                    ? gantt.getScrollState()
                    : { x: 0, y: 0 };

                const taskArea = document.querySelector('#gantt_here .gantt_task');
                const taskData = document.querySelector('#gantt_here .gantt_task_data');
                const taskBg = document.querySelector('#gantt_here .gantt_task_bg');
                const scaleLine = document.querySelector('#gantt_here .gantt_scale_line');

                const viewportWidth = Number(taskArea?.clientWidth || taskData?.clientWidth || 0);
                const contentWidth = Math.max(
                    Number(taskBg?.scrollWidth || 0),
                    Number(taskData?.scrollWidth || 0),
                    Number(scaleLine?.scrollWidth || 0),
                );

                const stateMaxX = Number(scrollState?.max_x ?? 0);
                const maxX = Math.max(0, stateMaxX, contentWidth - viewportWidth);

                return {
                    x: Math.max(0, Number(scrollState?.x || 0)),
                    y: Math.max(0, Number(scrollState?.y || 0)),
                    maxX: Math.max(0, Number.isFinite(maxX) ? maxX : 0),
                    viewportWidth: Math.max(0, viewportWidth),
                };
            }

            function syncHorizontalNav() {
                const hasDesktopNav = Boolean(navScrollXEl);
                const hasMobileNav = Boolean(navScrollXMobileEl);
                if (!hasDesktopNav && !hasMobileNav) {
                    return;
                }

                const metrics = getHorizontalScrollMetrics();
                const maxX = Math.max(0, Math.round(metrics.maxX));
                const x = Math.max(0, Math.min(maxX, Math.round(metrics.x)));
                const percent = maxX > 0 ? Math.round((x / maxX) * 100) : 0;
                const navSet = [
                    { range: navScrollXEl, left: navLeftBtn, right: navRightBtn, meta: navMetaEl },
                    { range: navScrollXMobileEl, left: navLeftMobileBtn, right: navRightMobileBtn, meta: navMetaMobileEl },
                ];

                navSet.forEach((item) => {
                    if (!item.range) {
                        return;
                    }

                    item.range.max = String(maxX);
                    item.range.value = String(x);
                    item.range.disabled = maxX <= 0;

                    if (item.left) {
                        item.left.disabled = maxX <= 0 || x <= 0;
                    }

                    if (item.right) {
                        item.right.disabled = maxX <= 0 || x >= maxX;
                    }

                    if (item.meta) {
                        item.meta.textContent = `${percent}%`;
                    }
                });
            }

            function queueHorizontalNavSync() {
                if (navSyncFrame) {
                    cancelAnimationFrame(navSyncFrame);
                }

                navSyncFrame = requestAnimationFrame(() => {
                    navSyncFrame = 0;
                    syncHorizontalNav();
                });
            }

            function scrollTimelineToX(rawX) {
                if (!isGanttReady) {
                    return;
                }

                const metrics = getHorizontalScrollMetrics();
                const nextX = Math.max(0, Math.min(metrics.maxX, Math.round(Number(rawX) || 0)));
                gantt.scrollTo(nextX, metrics.y);
                queueHorizontalNavSync();
            }

            function scrollTimelineBy(deltaX) {
                const metrics = getHorizontalScrollMetrics();
                scrollTimelineToX(metrics.x + Number(deltaX || 0));
            }

            function setZoomLevel(nextIndex) {
                const clamped = Math.max(0, Math.min(ZOOM_PERCENT_LEVELS.length - 1, Number(nextIndex) || 0));
                if (clamped === zoomLevelIndex) {
                    return;
                }

                zoomLevelIndex = clamped;
                updateZoomUi();
                setScale(currentScale);
            }

            function resolveResponsiveGridWidth() {
                const ganttHost = document.getElementById('gantt_here');
                const hostWidth = Math.max(320, Number(ganttHost?.clientWidth || window.innerWidth || 0));
                const phoneLike = isPhoneLikeViewport();

                const preferredGrid = phoneLike
                    ? Math.round(hostWidth * 0.46)
                    : Math.round(hostWidth * 0.40);

                const minTimelineWidth = phoneLike ? 240 : 320;
                const maxGridAllowed = Math.max(220, hostWidth - minTimelineWidth);

                return Math.max(220, Math.min(maxGridAllowed, preferredGrid));
            }

            function applyResponsiveGridLayout() {
                if (!gantt?.config) {
                    return;
                }

                const nextGridWidth = resolveResponsiveGridWidth();
                if (Number(gantt.config.grid_width) !== Number(nextGridWidth)) {
                    gantt.config.grid_width = nextGridWidth;
                }

                gantt.config.min_grid_column_width = 70;
            }

            function setScale(scale) {
                applyThaiLocale();
                currentScale = scale;
                applyResponsiveGridLayout();

                const previousScrollState = (isGanttReady && typeof gantt.getScrollState === 'function')
                    ? gantt.getScrollState()
                    : null;

                [scaleSelectEl, scaleSelectMobileEl].forEach((scaleInput) => {
                    if (scaleInput && scaleInput.value !== scale) {
                        scaleInput.value = scale;
                    }
                });

                let scales = [];
                let minColumnWidth = 44;

                if (scale === 'year') {
                    scales = [
                        {
                            unit: 'year',
                            step: 1,
                            format: (date) => `พ.ศ. ${date.getFullYear() + 543}`,
                        },
                        {
                            unit: 'month',
                            step: 1,
                            format: (date) => THAI_MONTH_SHORT[date.getMonth()],
                        },
                    ];
                    minColumnWidth = 54;
                } else if (scale === 'day') {
                    scales = [
                        {
                            unit: 'day',
                            step: 1,
                            format: (date) => {
                                const day = String(date.getDate()).padStart(2, '0');
                                return `${day} ${THAI_MONTH_SHORT[date.getMonth()]} ${date.getFullYear() + 543}`;
                            },
                        },
                    ];
                    minColumnWidth = 86;
                } else if (scale === 'week') {
                    scales = [
                        {
                            unit: 'week',
                            step: 1,
                            format: (date) => {
                                const week = gantt.date.getISOWeek(date);
                                return `สัปดาห์ ${week} ${THAI_MONTH_SHORT[date.getMonth()]} ${date.getFullYear() + 543}`;
                            },
                        },
                        {
                            unit: 'day',
                            step: 1,
                            format: (date) => {
                                const dayNumber = String(date.getDate()).padStart(2, '0');
                                return `${THAI_DAY_SHORT[date.getDay()]} ${dayNumber}`;
                            },
                        },
                    ];
                    minColumnWidth = 44;
                } else {
                    scales = [
                        {
                            unit: 'month',
                            step: 1,
                            format: (date) => `${THAI_MONTH_SHORT[date.getMonth()]} ${date.getFullYear() + 543}`,
                        },
                        {
                            unit: 'day',
                            step: 1,
                            format: (date) => `${String(date.getDate()).padStart(2, '0')} ${THAI_DAY_SHORT[date.getDay()]}`,
                        },
                    ];
                    minColumnWidth = 36;
                }

                const primaryScale = scales[0];
                const subScales = scales.slice(1);

                gantt.config.scales = scales;
                gantt.config.scale_unit = primaryScale.unit;
                gantt.config.step = primaryScale.step ?? 1;
                gantt.config.subscales = subScales.map((item) => ({
                    unit: item.unit,
                    step: item.step ?? 1,
                    date: item.format,
                }));
                gantt.config.min_column_width = Math.max(20, Math.round(minColumnWidth * getZoomFactor()));
                gantt.config.scale_offset_minimal = false;
                gantt.templates.date_scale = primaryScale.format;
                gantt.config.fit_tasks = false;

                const timelineRange = buildTimelineRange(scale);
                if (timelineRange) {
                    gantt.config.start_date = timelineRange.start;
                    gantt.config.end_date = timelineRange.end;
                } else {
                    gantt.config.start_date = null;
                    gantt.config.end_date = null;
                }

                if (isGanttReady) {
                    gantt.render();
                    gantt.setSizes();
                    gantt.refreshData();

                    if (previousScrollState) {
                        requestAnimationFrame(() => {
                            const metrics = getHorizontalScrollMetrics();
                            const restoreX = Math.max(0, Math.min(metrics.maxX, Number(previousScrollState.x || 0)));
                            const restoreY = Math.max(0, Number(previousScrollState.y || 0));
                            gantt.scrollTo(restoreX, restoreY);
                            queueHorizontalNavSync();
                        });
                    } else {
                        queueHorizontalNavSync();
                    }
                }

                updateZoomUi();
            }

            function captureViewState() {
                if (!isGanttReady) {
                    return null;
                }

                const scroll = gantt.getScrollState();
                return {
                    x: scroll?.x || 0,
                    y: scroll?.y || 0,
                    selected: gantt.getSelectedId(),
                    opened: gantt.getTaskByTime().filter((task) => task.$open).map((task) => task.id),
                };
            }

            function restoreViewState(state) {
                if (!state) {
                    return;
                }

                gantt.batchUpdate(() => {
                    (state.opened || []).forEach((id) => {
                        if (gantt.isTaskExists(id)) {
                            gantt.open(id);
                        }
                    });

                    gantt.scrollTo(state.x || 0, state.y || 0);

                    if (state.selected && gantt.isTaskExists(state.selected)) {
                        gantt.selectTask(state.selected);
                    }
                });
            }

            function taskMatchesFilter(task) {
                const search = filterState.search;
                const ownerFilter = filterState.owner;
                const statusFilter = filterState.status;

                if (search) {
                    const haystack = `${task.text || ''} ${task.owner || ''} ${task.id || ''}`.toLowerCase();
                    if (!haystack.includes(search)) {
                        return false;
                    }
                }

                if (ownerFilter) {
                    const owner = String(task.owner || '').toLowerCase();
                    if (!owner.includes(ownerFilter)) {
                        return false;
                    }
                }

                if (statusFilter === 'all') {
                    return true;
                }

                if (statusFilter === 'fte') {
                    return isFteTask(task);
                }

                if (isFteTask(task)) {
                    return false;
                }

                const workflowStatus = normalizeWorkflowStatus(task.workflow_status || task.status, {
                    progress: task.progress_percent ?? task.progress,
                    start: task.start_date,
                    end: task.end_date,
                });

                return workflowStatus === statusFilter;
            }

            function applyFilters() {
                if (!isGanttReady) {
                    return;
                }

                gantt.refreshData();
            }

            function resetFiltersToRevealTask(task) {
                const statusInput = document.getElementById('filter-status');
                const searchInput = document.getElementById('filter-search');
                const ownerInput = document.getElementById('filter-owner');

                let changed = false;
                if (filterState.status !== 'all') {
                    filterState.status = 'all';
                    if (statusInput) {
                        statusInput.value = 'all';
                    }
                    changed = true;
                }

                const haystack = `${task?.text || ''} ${task?.owner || ''} ${task?.id || ''}`.toLowerCase();
                if (filterState.search && !haystack.includes(filterState.search)) {
                    filterState.search = '';
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    changed = true;
                }

                const owner = String(task?.owner || '').toLowerCase();
                if (filterState.owner && !owner.includes(filterState.owner)) {
                    filterState.owner = '';
                    if (ownerInput) {
                        ownerInput.value = '';
                    }
                    changed = true;
                }

                if (changed) {
                    applyFilters();
                }
            }

            function scheduleAutosave(immediate = false) {
                if (autosaveTimer) {
                    clearTimeout(autosaveTimer);
                    autosaveTimer = null;
                }

                if (immediate) {
                    if (isSaving) {
                        autosaveTimer = setTimeout(() => {
                            autosaveTimer = null;
                            persistGanttData({ manual: false });
                        }, 40);
                        return;
                    }

                    persistGanttData({ manual: false });
                    return;
                }

                autosaveTimer = setTimeout(() => {
                    autosaveTimer = null;
                    persistGanttData({ manual: false });
                }, AUTO_SAVE_DELAY);
            }

            function markDirty(immediate = false) {
                if (isHydrating) {
                    return;
                }

                hasPendingChanges = true;
                setSaveState('dirty', immediate ? 'มีการเปลี่ยนแปลง กำลังบันทึกทันที...' : 'มีการเปลี่ยนแปลง กำลังรอบันทึกอัตโนมัติ...');
                scheduleAutosave(immediate);
            }

            function collectTasksForSave() {
                const tasks = [];
                let beforeFteOrder = 0;
                let afterFteOrder = 0;

                const orderedTaskIds = [];
                let fteRootRowIndex = -1;
                const rowIndexByTaskId = new Map();
                if (typeof gantt.serialize === 'function') {
                    try {
                        const snapshot = gantt.serialize();
                        if (Array.isArray(snapshot?.data)) {
                            snapshot.data.forEach((raw, rowIndex) => {
                                const id = raw?.id;
                                if (id !== undefined && id !== null && gantt.isTaskExists(id)) {
                                    orderedTaskIds.push(id);
                                    rowIndexByTaskId.set(String(id), rowIndex);
                                    if (String(id) === FTE_ROOT_ID) {
                                        fteRootRowIndex = rowIndex;
                                    }
                                }
                            });
                        }
                    } catch (error) {
                        console.warn('serialize failed, fallback to eachTask', error);
                    }
                }

                const iterate = orderedTaskIds.length > 0
                    ? (visitor) => orderedTaskIds.forEach((id) => visitor(gantt.getTask(id)))
                    : (visitor) => gantt.eachTask((task) => visitor(task));

                iterate((task) => {
                    if (isFteTask(task)) {
                        return;
                    }

                    const progressPercent = normalizeProgressStep(task.progress_percent ?? task.progress);
                    const workflowStatus = normalizeWorkflowStatus(task.workflow_status || task.status, {
                        progress: progressPercent,
                        start: task.start_date,
                        end: task.end_date,
                    });

                    const rowIndex = rowIndexByTaskId.get(String(task.id));
                    const isAfterFte = fteRootRowIndex >= 0
                        && Number.isInteger(rowIndex)
                        && rowIndex > fteRootRowIndex;
                    const order = isAfterFte
                        ? (FTE_ORDER_BREAK + afterFteOrder++)
                        : beforeFteOrder++;

                    tasks.push({
                        id: task.id,
                        text: String(task.text || '').trim(),
                        start_date: toIsoDate(task.start_date),
                        end_date: toIsoDate(task.end_date),
                        parent: normalizeParent(task.parent || 0),
                        progress: progressPercent,
                        workflow_status: workflowStatus,
                        status: workflowStatus === 'completed' ? 'done' : 'ongoing',
                        owner: String(task.owner || '').trim(),
                        order,
                    });
                });

                return tasks.filter((task) => task.text !== '' && task.start_date && task.end_date);
            }

            function collectLinksForSave() {
                const links = Array.isArray(gantt.getLinks()) ? gantt.getLinks() : [];

                return links
                    .filter((link) => {
                        if (!link || !link.source || !link.target) {
                            return false;
                        }

                        if (!gantt.isTaskExists(link.source) || !gantt.isTaskExists(link.target)) {
                            return false;
                        }

                        const sourceTask = gantt.getTask(link.source);
                        const targetTask = gantt.getTask(link.target);

                        return !isFteTask(sourceTask) && !isFteTask(targetTask) && link.source !== link.target;
                    })
                    .map((link) => ({
                        id: isNumericId(link.id) ? Number(link.id) : undefined,
                        source: link.source,
                        target: link.target,
                        type: normalizeLinkType(link.type),
                    }));
            }

            async function persistGanttData({ manual = false } = {}) {
                if (isSaving) {
                    return;
                }

                if (!manual && !hasPendingChanges) {
                    return;
                }

                const tasks = collectTasksForSave();
                const links = collectLinksForSave();

                const currentTaskIds = new Set(
                    tasks.map((task) => Number(task.id)).filter((id) => Number.isInteger(id) && id > 0)
                );

                const deletedIds = loadedTaskIds.filter((id) => !currentTaskIds.has(id));

                const formData = new FormData();
                formData.append('project_id', String(projectId));
                formData.append('tasks', JSON.stringify(tasks));
                formData.append('links', JSON.stringify(links));
                formData.append('deleted_ids', JSON.stringify(deletedIds));

                hasPendingChanges = false;
                isSaving = true;
                setSaveState('saving', manual ? 'กำลังบันทึก...' : 'กำลังบันทึกอัตโนมัติ...');

                try {
                    const response = await fetch(saveUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    const payload = await response.json();
                    if (!response.ok || payload.status !== 'success') {
                        throw new Error(payload.message || 'Save failed');
                    }

                    const hasNonNumericId = tasks.some((task) => !isNumericId(task.id));
                    const hasIdMap = payload.id_map && Object.keys(payload.id_map).length > 0;

                    if (hasNonNumericId || hasIdMap) {
                        await reloadGanttData({ silent: true });
                    } else {
                        loadedTaskIds = Array.from(currentTaskIds);
                    }

                    const saveTime = payload.saved_at ? new Date(payload.saved_at) : new Date();
                    setSaveState('saved', 'บันทึกเรียบร้อย', `เวลา ${toClock(saveTime)}`);
                } catch (error) {
                    console.error(error);
                    setSaveState('error', 'บันทึกไม่สำเร็จ ระบบจะลองใหม่อัตโนมัติ');
                    hasPendingChanges = true;
                } finally {
                    isSaving = false;
                    if (!manual && hasPendingChanges) {
                        scheduleAutosave(true);
                    }
                }
            }

            function mapTaskFromApi(raw) {
                const start = parseApiDate(raw.start_date) || new Date();
                const end = parseApiDate(raw.end_date) || start;
                const rawProgress = toBackendProgress(raw.progress);
                const isFte = Boolean(raw.readonly || raw.is_fte);
                const progressPercent = isFte ? rawProgress : normalizeProgressStep(rawProgress);
                const workflowStatus = normalizeWorkflowStatus(raw.workflow_status || raw.status, {
                    progress: progressPercent,
                    start,
                    end,
                });

                return {
                    id: raw.id,
                    text: String(raw.text || '').trim() || 'Untitled Task',
                    start_date: start,
                    end_date: end,
                    parent: raw.parent ?? 0,
                    progress: toGanttProgress(progressPercent),
                    progress_percent: progressPercent,
                    workflow_status: workflowStatus,
                    status: workflowStatus === 'completed' ? 'done' : 'ongoing',
                    owner: raw.owner || '',
                    order: Number(raw.order || 0),
                    open: raw.open !== false,
                    readonly: isFte,
                    is_fte: Boolean(raw.is_fte),
                    type: raw.type || (raw.is_fte ? 'fte' : 'task'),
                };
            }

            function mapLinkFromApi(raw) {
                return {
                    id: raw.id,
                    source: raw.source,
                    target: raw.target,
                    type: normalizeLinkType(raw.type),
                };
            }

            function normalizeTaskForSignature(raw) {
                return {
                    id: String(raw?.id ?? ''),
                    text: String(raw?.text ?? ''),
                    start_date: String(raw?.start_date ?? ''),
                    end_date: String(raw?.end_date ?? ''),
                    parent: String(raw?.parent ?? 0),
                    progress: String(raw?.progress ?? ''),
                    workflow_status: String(raw?.workflow_status ?? ''),
                    status: String(raw?.status ?? ''),
                    owner: String(raw?.owner ?? ''),
                    order: String(raw?.order ?? ''),
                    open: raw?.open === false ? 0 : 1,
                    readonly: raw?.readonly || raw?.is_fte ? 1 : 0,
                    type: String(raw?.type ?? ''),
                };
            }

            function normalizeLinkForSignature(raw) {
                return {
                    id: String(raw?.id ?? ''),
                    source: String(raw?.source ?? ''),
                    target: String(raw?.target ?? ''),
                    type: String(normalizeLinkType(raw?.type)),
                };
            }

            function buildServerSignature(taskRows, fteRows, linkRows) {
                const tasks = (Array.isArray(taskRows) ? taskRows : []).map(normalizeTaskForSignature);
                const ftes = (Array.isArray(fteRows) ? fteRows : []).map(normalizeTaskForSignature);
                const links = (Array.isArray(linkRows) ? linkRows : []).map(normalizeLinkForSignature);

                tasks.sort((a, b) => `${a.id}`.localeCompare(`${b.id}`, 'en', { numeric: true }));
                ftes.sort((a, b) => `${a.id}`.localeCompare(`${b.id}`, 'en', { numeric: true }));
                links.sort((a, b) => `${a.source}|${a.target}|${a.type}|${a.id}`.localeCompare(`${b.source}|${b.target}|${b.type}|${b.id}`));

                return JSON.stringify({ tasks, ftes, links });
            }

            async function fetchJson(url) {
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message || `Request failed: ${url}`);
                }

                return payload;
            }

            async function reloadGanttData({ silent = false } = {}) {
                if (isSaving) {
                    return;
                }

                const viewState = captureViewState();

                if (!silent) {
                    setSaveState('saving', 'กำลังโหลดข้อมูลล่าสุด...');
                }

                isHydrating = true;

                try {
                    const [taskPayload, linkPayload, ftePayload] = await Promise.all([
                        fetchJson(tasksUrl),
                        fetchJson(linksUrl),
                        fetchJson(fteTasksUrl),
                    ]);

                    const normalTaskRows = Array.isArray(taskPayload.data) ? taskPayload.data : [];
                    const fteTaskRows = Array.isArray(ftePayload.data) ? ftePayload.data : [];
                    const linkRows = Array.isArray(linkPayload.data) ? linkPayload.data : [];

                    const currentSignature = buildServerSignature(normalTaskRows, fteTaskRows, linkRows);
                    if (
                        silent
                        && !hasPendingChanges
                        && lastServerSignature
                        && currentSignature === lastServerSignature
                    ) {
                        return;
                    }

                    const normalTasks = normalTaskRows.map(mapTaskFromApi);
                    const fteTasks = fteTaskRows.map(mapTaskFromApi);
                    const links = linkRows.map(mapLinkFromApi);
                    const normalBeforeFte = normalTasks.filter((task) => Number(task.order || 0) < FTE_ORDER_BREAK);
                    const normalAfterFte = normalTasks.filter((task) => Number(task.order || 0) >= FTE_ORDER_BREAK);

                    closeTaskActionDrawer();

                    loadedTaskIds = normalTasks
                        .map((task) => Number(task.id))
                        .filter((id) => Number.isInteger(id) && id > 0);

                    gantt.clearAll();
                    if (fteTasks.length > 0) {
                        gantt.parse({ data: [...normalBeforeFte, ...fteTasks, ...normalAfterFte], links });
                    } else {
                        gantt.parse({ data: normalTasks, links });
                    }
                    setScale(document.getElementById('scale-select')?.value || 'year');
                    lastServerSignature = currentSignature;

                    restoreViewState(viewState);
                    applyFilters();
                    queueHorizontalNavSync();

                    if (!hasPendingChanges) {
                        setSaveState('saved', 'ซิงก์ข้อมูลล่าสุดแล้ว', `เวลา ${toClock(new Date())}`);
                    }
                } catch (error) {
                    console.error(error);
                    setSaveState('error', 'โหลดข้อมูลไม่สำเร็จ');
                } finally {
                    isHydrating = false;
                }
            }

            function refreshParentOptions(currentId, selectedParent) {
                const rows = [];

                gantt.eachTask((task) => {
                    if (isFteTask(task)) {
                        return;
                    }

                    if (currentId && String(task.id) === String(currentId)) {
                        return;
                    }

                    rows.push({ id: task.id, text: task.text || '(ไม่มีชื่อ)' });
                });

                rows.sort((a, b) => String(a.text).localeCompare(String(b.text), 'th'));

                drawerParent.innerHTML = `<option value="0">(งานหลัก)</option>` + rows
                    .map((row) => `<option value="${row.id}">${row.id} - ${String(row.text).replace(/</g, '&lt;')}</option>`)
                    .join('');

                const parentValue = isNumericId(selectedParent) ? Number(selectedParent) : 0;
                drawerParent.value = String(parentValue);
            }

            function openDrawer(task = null, parentHint = 0) {
                const now = new Date();
                const in7Days = new Date(now.getTime() + (7 * 24 * 60 * 60 * 1000));

                if (task && isFteTask(task)) {
                    return;
                }

                const taskId = task ? task.id : '';
                drawerTaskId.value = taskId || '';
                drawerTitle.value = task ? (task.text || '') : '';
                setDrawerDateValue(drawerStart, task ? task.start_date : now);
                setDrawerDateValue(drawerEnd, task ? task.end_date : in7Days);
                if (drawerTitleText) {
                    drawerTitleText.textContent = task ? 'แก้ไขงาน' : 'เพิ่มงานใหม่';
                }
                if (drawerSave) {
                    drawerSave.textContent = task ? 'บันทึกการแก้ไข' : 'เพิ่มงาน';
                }
                if (drawerSaveInline) {
                    drawerSaveInline.textContent = task ? 'บันทึกการแก้ไข' : 'เพิ่มงาน';
                }

                const progressPercent = task ? normalizeProgressStep(task.progress_percent ?? task.progress) : 0;
                drawerProgress.value = String(progressPercent);

                const workflowStatus = task
                    ? normalizeWorkflowStatus(task.workflow_status || task.status, {
                        progress: progressPercent,
                        start: task.start_date,
                        end: task.end_date,
                    })
                    : 'not_started';

                drawerStatus.value = workflowStatus;
                drawerOwner.value = task ? (task.owner || '') : '';
                maybeStyleStatusSelect(drawerStatus);

                const selectedParent = task ? normalizeParent(task.parent || 0) : normalizeParent(parentHint || 0);
                refreshParentOptions(taskId, selectedParent);

                drawerDelete.style.display = task && task.id ? 'inline-block' : 'none';

                drawer.classList.add('open');
                drawerBackdrop.classList.add('open');
                drawer.setAttribute('aria-hidden', 'false');
            }

            function closeDrawer() {
                drawer.classList.remove('open');
                drawerBackdrop.classList.remove('open');
                drawer.setAttribute('aria-hidden', 'true');
            }

            function syncTaskComputedFields(task) {
                if (isFteTask(task)) {
                    return;
                }

                const progressPercent = normalizeProgressStep(task.progress_percent ?? task.progress);
                task.progress_percent = progressPercent;
                task.progress = toGanttProgress(progressPercent);

                task.workflow_status = normalizeWorkflowStatus(task.workflow_status || task.status, {
                    progress: progressPercent,
                    start: task.start_date,
                    end: task.end_date,
                });

                if (task.workflow_status === 'completed' && progressPercent < 100) {
                    task.progress_percent = 100;
                    task.progress = 1;
                }

                if (task.workflow_status === 'not_started' && progressPercent > 0) {
                    task.progress_percent = 0;
                    task.progress = 0;
                }

                task.status = task.workflow_status === 'completed' ? 'done' : 'ongoing';
            }

            function resolveInsertContext(targetTaskId = null, mode = 'main') {
                const fallback = { parent: 0, index: undefined };

                if (!targetTaskId || !gantt.isTaskExists(targetTaskId)) {
                    return fallback;
                }

                const targetTask = gantt.getTask(targetTaskId);

                if (mode === 'sub' && !isFteTask(targetTask)) {
                    const parent = normalizeParent(targetTaskId);
                    return { parent, index: 0 };
                }

                const parent = normalizeParent(targetTask.parent || 0);
                const children = gantt.getChildren(parent);

                let anchorId = targetTaskId;
                if (!children.some((id) => String(id) === String(anchorId)) && isFteTask(targetTask)) {
                    if (gantt.isTaskExists(FTE_ROOT_ID) && children.some((id) => String(id) === String(FTE_ROOT_ID))) {
                        anchorId = FTE_ROOT_ID;
                    }
                }

                const anchorIndex = children.findIndex((id) => String(id) === String(anchorId));
                return {
                    parent,
                    index: anchorIndex >= 0 ? anchorIndex + 1 : children.length,
                };
            }

            function createTaskInline(targetTaskId = null, mode = 'main') {
                if (!isGanttReady) {
                    return;
                }

                if (wasRecentPointerDrag(900)) {
                    return;
                }

                const nowTime = Date.now();
                if (nowTime < createTaskLockUntil) {
                    return;
                }
                createTaskLockUntil = nowTime + 500;

                const now = new Date();
                now.setHours(0, 0, 0, 0);
                const in7Days = new Date(now);
                in7Days.setDate(in7Days.getDate() + 7);

                const insertContext = resolveInsertContext(targetTaskId, mode);
                const parent = insertContext.parent;
                const payload = {
                    text: 'งานใหม่',
                    start_date: now,
                    end_date: in7Days,
                    workflow_status: 'not_started',
                    status: 'ongoing',
                    progress_percent: 0,
                    progress: 0,
                    owner: '',
                    parent,
                };

                const prevSelectedId = gantt.getSelectedId();
                const newId = gantt.addTask(payload, parent, insertContext.index);

                if (!gantt.isTaskExists(newId)) {
                    return;
                }

                const newTask = gantt.getTask(newId);
                syncTaskComputedFields(newTask);
                gantt.updateTask(newId);

                if (prevSelectedId && String(prevSelectedId) !== String(newId) && gantt.isTaskExists(prevSelectedId)) {
                    gantt.refreshTask(prevSelectedId);
                }

                if (parent && gantt.isTaskExists(parent)) {
                    gantt.open(parent);
                }

                resetFiltersToRevealTask(newTask);
                gantt.selectTask(newId);
                gantt.showTask(newId);
                gantt.refreshTask(newId);
            }

            function initDrawerActions() {
                initDrawerThaiDatepickers();

                document.getElementById('btn-add').addEventListener('click', () => createTaskInline(null, 'main'));

                document.getElementById('drawer-close').addEventListener('click', closeDrawer);
                document.getElementById('drawer-cancel').addEventListener('click', closeDrawer);
                drawerBackdrop.addEventListener('click', closeDrawer);

                drawerStatus.addEventListener('change', () => {
                    if (drawerStatus.value === 'completed') {
                        drawerProgress.value = '100';
                    }

                    if (drawerStatus.value === 'not_started') {
                        drawerProgress.value = '0';
                    }

                    maybeStyleStatusSelect(drawerStatus);
                });

                maybeStyleStatusSelect(drawerStatus);

                const handleDrawerSave = () => {
                    const id = drawerTaskId.value;
                    const text = String(drawerTitle.value || '').trim();
                    const start = parseApiDate(drawerStart.value);
                    const end = parseApiDate(drawerEnd.value);
                    const owner = String(drawerOwner.value || '').trim();
                    const progressPercent = normalizeProgressStep(drawerProgress.value);
                    const workflowStatus = normalizeWorkflowStatus(drawerStatus.value, {
                        progress: progressPercent,
                        start,
                        end,
                    });
                    const parent = normalizeParent(drawerParent.value || 0);

                    if (!text || !start || !end) {
                        alert('กรุณากรอกชื่อและช่วงวันที่ให้ครบ');
                        return;
                    }

                    if (start > end) {
                        alert('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด');
                        return;
                    }

                    const payload = {
                        text,
                        start_date: start,
                        end_date: end,
                        workflow_status: workflowStatus,
                        status: workflowStatus === 'completed' ? 'done' : 'ongoing',
                        progress_percent: progressPercent,
                        progress: toGanttProgress(progressPercent),
                        owner,
                        parent,
                    };

                    if (id) {
                        if (!gantt.isTaskExists(id)) {
                            closeDrawer();
                            return;
                        }

                        const task = gantt.getTask(id);
                        if (isFteTask(task)) {
                            closeDrawer();
                            return;
                        }

                        Object.assign(task, payload);
                        syncTaskComputedFields(task);
                        gantt.updateTask(id);
                        resetFiltersToRevealTask(task);
                        gantt.selectTask(id);
                        gantt.showTask(id);
                    } else {
                        const newId = gantt.addTask(payload);

                        if (gantt.isTaskExists(newId)) {
                            const newTask = gantt.getTask(newId);
                            syncTaskComputedFields(newTask);
                            gantt.updateTask(newId);
                            resetFiltersToRevealTask(newTask);
                            gantt.open(newTask.id);
                            gantt.selectTask(newTask.id);
                            gantt.showTask(newTask.id);
                        }
                    }

                    closeDrawer();
                    markDirty(true);
                };

                document.getElementById('drawer-save').addEventListener('click', handleDrawerSave);
                if (drawerSaveInline) {
                    drawerSaveInline.addEventListener('click', handleDrawerSave);
                }

                drawerDelete.addEventListener('click', () => {
                    const id = drawerTaskId.value;
                    if (!id || !gantt.isTaskExists(id)) {
                        return;
                    }

                    const task = gantt.getTask(id);
                    if (isFteTask(task)) {
                        return;
                    }

                    if (!confirm('ยืนยันลบงานนี้?')) {
                        return;
                    }

                    gantt.deleteTask(id);
                    closeDrawer();
                    markDirty(true);
                });
            }

            function initToolbarActions() {
                [scaleSelectEl, scaleSelectMobileEl].forEach((scaleInput) => {
                    if (!scaleInput) {
                        return;
                    }

                    scaleInput.addEventListener('change', (event) => {
                        setScale(event.target.value);
                    });
                });

                if (zoomOutBtn) {
                    zoomOutBtn.addEventListener('click', () => {
                        setZoomLevel(zoomLevelIndex - 1);
                    });
                }
                if (zoomOutMobileBtn) {
                    zoomOutMobileBtn.addEventListener('click', () => {
                        setZoomLevel(zoomLevelIndex - 1);
                    });
                }

                if (zoomInBtn) {
                    zoomInBtn.addEventListener('click', () => {
                        setZoomLevel(zoomLevelIndex + 1);
                    });
                }
                if (zoomInMobileBtn) {
                    zoomInMobileBtn.addEventListener('click', () => {
                        setZoomLevel(zoomLevelIndex + 1);
                    });
                }

                [navScrollXEl, navScrollXMobileEl].forEach((navInput) => {
                    if (!navInput) {
                        return;
                    }

                    navInput.addEventListener('input', (event) => {
                        scrollTimelineToX(event.target.value);
                    });
                });

                [navLeftBtn, navLeftMobileBtn].forEach((navLeft) => {
                    if (!navLeft) {
                        return;
                    }

                    navLeft.addEventListener('click', () => {
                        const metrics = getHorizontalScrollMetrics();
                        const step = Math.max(120, Math.round(metrics.viewportWidth * 0.45));
                        scrollTimelineBy(-step);
                    });
                });

                [navRightBtn, navRightMobileBtn].forEach((navRight) => {
                    if (!navRight) {
                        return;
                    }

                    navRight.addEventListener('click', () => {
                        const metrics = getHorizontalScrollMetrics();
                        const step = Math.max(120, Math.round(metrics.viewportWidth * 0.45));
                        scrollTimelineBy(step);
                    });
                });

                document.getElementById('filter-search').addEventListener('input', (event) => {
                    filterState.search = String(event.target.value || '').trim().toLowerCase();
                    applyFilters();
                });

                document.getElementById('filter-status').addEventListener('change', (event) => {
                    filterState.status = event.target.value || 'all';
                    applyFilters();
                });

                document.getElementById('filter-owner').addEventListener('input', (event) => {
                    filterState.owner = String(event.target.value || '').trim().toLowerCase();
                    applyFilters();
                });
            }

            function initGantt() {
                gantt.plugins({
                    marker: true,
                    tooltip: true,
                    inline_editors: true,
                });
                applyThaiLocale();

                gantt.config.xml_date = '%Y-%m-%d';
                gantt.config.date_format = '%Y-%m-%d';
                gantt.config.lightbox = false;
                gantt.config.details_on_dblclick = false;
                gantt.config.details_on_create = false;
                gantt.config.drag_links = true;
                gantt.config.show_links = true;
                gantt.config.drag_move = true;
                gantt.config.drag_resize = true;
                gantt.config.drag_progress = true;
                gantt.config.readonly_property = 'readonly';
                gantt.config.autosize = false;
                gantt.config.autoscroll = true;
                gantt.config.row_height = 62;
                gantt.config.task_height = 28;
                gantt.config.show_chart = true;
                gantt.config.grid_width = resolveResponsiveGridWidth();
                gantt.config.min_grid_column_width = 70;
                gantt.config.scale_height = 62;
                gantt.config.smart_rendering = true;
                gantt.config.order_branch = true;
                gantt.config.order_branch_free = true;
                gantt.config.inline_editors = true;

                gantt.serverList('workflowStatuses', [
                    { key: 'not_started', label: STATUS_CONFIG.not_started.label },
                    { key: 'starting', label: STATUS_CONFIG.starting.label },
                    { key: 'in_progress', label: STATUS_CONFIG.in_progress.label },
                    { key: 'paused', label: STATUS_CONFIG.paused.label },
                    { key: 'delayed', label: STATUS_CONFIG.delayed.label },
                    { key: 'completed', label: STATUS_CONFIG.completed.label },
                ]);

                gantt.serverList('progressValues', [
                    { key: 0, label: '0%' },
                    { key: 25, label: '25%' },
                    { key: 50, label: '50%' },
                    { key: 100, label: '100%' },
                ]);

                gantt.addMarker({
                    start_date: new Date(),
                    css: 'today',
                    text: 'วันนี้',
                    title: 'วันนี้',
                });

                gantt.templates.grid_row_class = (start, end, task) => (isFteTask(task) ? 'gantt-row-fte' : '');

                gantt.templates.task_class = (start, end, task) => {
                    if (isFteTask(task)) {
                        return STATUS_CONFIG.fte.taskClass;
                    }

                    const workflowStatus = normalizeWorkflowStatus(task.workflow_status || task.status, {
                        progress: task.progress_percent ?? task.progress,
                        start: task.start_date,
                        end: task.end_date,
                    });

                    return STATUS_CONFIG[workflowStatus]?.taskClass || STATUS_CONFIG.in_progress.taskClass;
                };

                gantt.templates.tooltip_text = (start, end, task) => {
                    const statusLabel = isFteTask(task)
                        ? STATUS_CONFIG.fte.label
                        : (STATUS_CONFIG[normalizeWorkflowStatus(task.workflow_status || task.status, {
                            progress: task.progress_percent ?? task.progress,
                            start,
                            end,
                        })]?.label || STATUS_CONFIG.in_progress.label);

                    return `
                        <b>${task.text || '-'}</b><br/>
                        สถานะ: ${statusLabel}<br/>
                        ผู้รับผิดชอบ: ${task.owner || '-'}<br/>
                        เริ่ม: ${toThaiDateBE(start, true)}<br/>
                        สิ้นสุด: ${toThaiDateBE(end, true)}<br/>
                        คืบหน้า: ${toBackendProgress(task.progress_percent ?? task.progress)}%
                    `;
                };

                gantt.config.columns = [
                    {
                        name: 'text',
                        label: 'ชื่องาน',
                        tree: true,
                        width: '*',
                        resize: true,
                        template: (task) => {
                            const title = escapeHtml(task.text || '-');
                            const owner = String(task.owner || '').trim();
                            if (!owner) {
                                return `<div class="gantt-task-cell-wrap"><span class="gantt-task-title">${title}</span></div>`;
                            }

                            return `<div class="gantt-task-cell-wrap"><span class="gantt-task-title">${title}</span><span class="gantt-task-owner">ผู้รับผิดชอบ: ${escapeHtml(owner)}</span></div>`;
                        },
                        editor: { type: 'text', map_to: 'text' },
                    },
                    {
                        name: 'workflow_status',
                        label: 'สถานะ',
                        width: 140,
                        align: 'center',
                        template: (task) => `<div class="gantt-cell-center">${statusBadgeHtml(task)}</div>`,
                        editor: {
                            type: 'select',
                            map_to: 'workflow_status',
                            options: gantt.serverList('workflowStatuses'),
                        },
                    },
                    {
                        name: 'progress_percent',
                        label: 'Progress',
                        width: 80,
                        align: 'center',
                        template: (task) => `<div class="gantt-cell-center"><span class="gantt-progress-pill">${toBackendProgress(task.progress_percent ?? task.progress)}%</span></div>`,
                        editor: {
                            type: 'select',
                            map_to: 'progress_percent',
                            options: gantt.serverList('progressValues'),
                        },
                    },
                    {
                        name: 'start_date',
                        label: 'เริ่ม (พ.ศ.)',
                        width: 110,
                        align: 'center',
                        template: (task) => `<div class="gantt-cell-center">${toThaiDateBE(task.start_date, true)}</div>`,
                        editor: { type: 'date', map_to: 'start_date' },
                    },
                    {
                        name: 'end_date',
                        label: 'สิ้นสุด (พ.ศ.)',
                        width: 110,
                        align: 'center',
                        template: (task) => `<div class="gantt-cell-center">${toThaiDateBE(task.end_date, true)}</div>`,
                        editor: { type: 'date', map_to: 'end_date' },
                    },
                    {
                        name: 'quick_add',
                        label: '',
                        width: 36,
                        align: 'center',
                        template: () => '<div class="gantt-cell-center"><button class="gantt-icon-btn gantt-icon-add" type="button" title="เพิ่มงานใต้รายการนี้" aria-label="เพิ่มงานใต้รายการนี้"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg></button></div>',
                    },
                    {
                        name: 'quick_delete',
                        label: '',
                        width: 36,
                        align: 'center',
                        template: (task) => {
                            if (isFteTask(task)) {
                                return '';
                            }

                            return '<div class="gantt-cell-center"><button class="gantt-icon-btn gantt-icon-delete" type="button" title="ลบงาน" aria-label="ลบงาน"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button></div>';
                        },
                    },
                ];

                gantt.attachEvent('onBeforeTaskDisplay', (id, task) => taskMatchesFilter(task));

                gantt.attachEvent('onTaskDblClick', (id, event) => {
                    const task = gantt.getTask(id);
                    closeTaskActionDrawer();

                    if (isFteTask(task)) {
                        setSaveState('idle', 'รายการนี้มาจาก FTE แก้ไขตรงนี้ไม่ได้');
                        return false;
                    }

                    if (event?.shiftKey) {
                        openDrawer(task, task.parent || 0);
                        return false;
                    }

                    const cell = event?.target?.closest?.('.gantt_cell');
                    const columnName = cell?.getAttribute?.('data-column-name') || 'text';
                    if (startInlineEdit(id, columnName)) {
                        return false;
                    }

                    openDrawer(task, task.parent || 0);
                    return false;
                });

                gantt.attachEvent('onTaskClick', (id, event) => {
                    if (Date.now() < clickSuppressUntil) {
                        return false;
                    }

                    const task = gantt.getTask(id);
                    const cell = event?.target?.closest?.('.gantt_cell');
                    const columnName = cell?.getAttribute?.('data-column-name');
                    const isDoubleClick = Number(event?.detail || 1) > 1;

                    if (columnName === 'quick_add') {
                        if (wasRecentPointerDrag(900)) {
                            return false;
                        }

                        const addBtn = event?.target?.closest?.('.gantt-icon-add');
                        if (!addBtn) {
                            return false;
                        }
                        createTaskInline(id, 'sub');
                        closeTaskActionDrawer();
                        return false;
                    }

                    if (columnName === 'quick_delete') {
                        if (wasRecentPointerDrag(900)) {
                            return false;
                        }

                        const deleteBtn = event?.target?.closest?.('.gantt-icon-delete');
                        if (!deleteBtn) {
                            return false;
                        }

                        if (isFteTask(task)) {
                            setSaveState('idle', 'รายการนี้มาจาก FTE ลบไม่ได้');
                            return false;
                        }

                        if (!confirm('ยืนยันลบงานนี้?')) {
                            return false;
                        }

                        gantt.deleteTask(id);
                        closeTaskActionDrawer();
                        return false;
                    }

                    if (columnName && isDoubleClick) {
                        return true;
                    }

                    if (columnName) {
                        const treeIcon = event?.target?.closest?.('.gantt_tree_icon');
                        if (treeIcon) {
                            return true;
                        }

                        const selectedId = gantt.getSelectedId();
                        if (selectedId && String(selectedId) !== String(id) && gantt.isTaskExists(selectedId)) {
                            gantt.refreshTask(selectedId);
                        }

                        gantt.selectTask(id);
                        gantt.refreshTask(id);

                        if (!isDoubleClick) {
                            const rowAnchor = cell?.closest?.('.gantt_row') || event?.target?.closest?.('.gantt_row') || cell;
                            const clickPoint = {
                                x: Number(event?.clientX),
                                y: Number(event?.clientY),
                            };
                            openTaskActionDrawer(task, rowAnchor, clickPoint);
                        }

                        return false;
                    }

                    closeTaskActionDrawer();
                    return true;
                });

                gantt.attachEvent('onEmptyClick', () => {
                    closeTaskActionDrawer();
                    return true;
                });

                gantt.attachEvent('onTaskSelected', (id) => {
                    if (id && gantt.isTaskExists(id)) {
                        gantt.refreshTask(id);
                    }
                    return true;
                });

                gantt.attachEvent('onTaskUnselected', (id) => {
                    if (id && gantt.isTaskExists(id)) {
                        gantt.refreshTask(id);
                    }
                    return true;
                });

                gantt.attachEvent('onGanttScroll', () => {
                    queueHorizontalNavSync();
                    return true;
                });

                gantt.attachEvent('onDataRender', () => {
                    queueHorizontalNavSync();
                    return true;
                });

                gantt.attachEvent('onBeforeTaskDrag', (id) => {
                    const task = gantt.getTask(id);
                    closeTaskActionDrawer();
                    suppressGridClick(900);
                    return !isFteTask(task);
                });

                gantt.attachEvent('onBeforeTaskDelete', (id) => {
                    const task = gantt.getTask(id);
                    return !isFteTask(task);
                });

                gantt.attachEvent('onBeforeLinkAdd', (id, link) => {
                    if (!link || !link.source || !link.target || link.source === link.target) {
                        return false;
                    }

                    if (!gantt.isTaskExists(link.source) || !gantt.isTaskExists(link.target)) {
                        return false;
                    }

                    const sourceTask = gantt.getTask(link.source);
                    const targetTask = gantt.getTask(link.target);
                    return !isFteTask(sourceTask) && !isFteTask(targetTask);
                });

                const watchEvents = [
                    'onAfterTaskAdd',
                    'onAfterTaskUpdate',
                    'onAfterTaskDelete',
                    'onAfterTaskDrag',
                    'onAfterTaskMove',
                    'onRowDragEnd',
                    'onAfterLinkAdd',
                    'onAfterLinkUpdate',
                    'onAfterLinkDelete',
                ];

                watchEvents.forEach((eventName) => {
                    gantt.attachEvent(eventName, () => {
                        if (eventName === 'onAfterTaskDrag' || eventName === 'onAfterTaskMove' || eventName === 'onRowDragEnd') {
                            lastPointerDragAt = Date.now();
                            suppressGridClick(900);
                        }
                        markDirty(true);
                        return true;
                    });
                });

                gantt.attachEvent('onAfterTaskUpdate', (id) => {
                    if (isNormalizingUpdate) {
                        return true;
                    }

                    if (!gantt.isTaskExists(id)) {
                        return true;
                    }

                    const task = gantt.getTask(id);
                    if (isFteTask(task)) {
                        return true;
                    }

                    isNormalizingUpdate = true;
                    syncTaskComputedFields(task);

                    gantt.refreshTask(id);
                    isNormalizingUpdate = false;
                    return true;
                });

                setScale(document.getElementById('scale-select').value || 'year');
                gantt.init('gantt_here');
                isGanttReady = true;
                gantt.render();
                queueHorizontalNavSync();
            }

            let hasBooted = false;
            let sharedPageBindingsReady = false;

            function bindSharedPageEventsOnce() {
                if (sharedPageBindingsReady) {
                    return;
                }

                document.addEventListener('focusin', (event) => {
                    maybeStyleStatusSelect(event.target);
                });

                document.addEventListener('change', (event) => {
                    maybeStyleStatusSelect(event.target);
                });

                window.addEventListener('beforeunload', (event) => {
                    if (!hasPendingChanges && !isSaving) {
                        return;
                    }

                    event.preventDefault();
                    event.returnValue = '';
                });

                sharedPageBindingsReady = true;
            }

            function bootGanttPage() {
                bindOrientationGuardOnce();
                const blocked = updateLandscapeGuardState();

                if (blocked) {
                    return;
                }

                bindSharedPageEventsOnce();

                if (hasBooted) {
                    if (isGanttReady) {
                        requestAnimationFrame(() => {
                            applyResponsiveGridLayout();
                            gantt.setSizes();
                            gantt.render();
                            queueHorizontalNavSync();
                        });
                    }
                    return;
                }

                hasBooted = true;

                initTaskActionDrawer();
                initDrawerActions();
                initToolbarActions();
                initGantt();
                initPointerDragGuard();
                reloadGanttData({ silent: false });

                if (AUTO_FETCH_INTERVAL > 0) {
                    autoFetchTimer = setInterval(() => {
                        if (!isSaving && !hasPendingChanges) {
                            reloadGanttData({ silent: true });
                        }
                    }, AUTO_FETCH_INTERVAL);
                }

                if (ENABLE_FOCUS_REFETCH) {
                    window.addEventListener('focus', () => {
                        if (!isSaving && !hasPendingChanges) {
                            reloadGanttData({ silent: true });
                        }
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootGanttPage, { once: true });
            } else {
                bootGanttPage();
            }

            document.addEventListener('turbo:load', bootGanttPage);
        })();
    </script>
@endsection
