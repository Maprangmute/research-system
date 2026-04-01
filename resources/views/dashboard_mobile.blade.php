@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600&display=swap');

        :root {
            --lux-ink: #0f172a;
            --lux-ink-soft: #334155;
            --lux-gold: #c7a76a;
            --lux-emerald: #0f766e;
            --lux-emerald-dark: #0b4f4b;
            --lux-ivory: #f6f1e8;
            --lux-mist: #fbfaf7;
        }

        html,
        body {
            height: 100%;
            overscroll-behavior-y: none;
            scroll-behavior: smooth;
        }

        body {
            overflow-y: auto;
            overflow-x: hidden;
            background: #f3f4f6;
        }

        .lux-page {
            font-family: 'IBM Plex Sans Thai', sans-serif;
            color: var(--lux-ink);
            background: none;
            min-height: 100%;
            position: relative;
            overflow-x: hidden;
        }

        .lux-title {
            font-family: 'Playfair Display', serif;
            letter-spacing: 0.02em;
        }

        .lux-page::before {
            content: '';
            position: fixed;
            inset: -20% -10% auto auto;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18), transparent 70%);
            z-index: 0;
            pointer-events: none;
        }

        .lux-card {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(10px);
        }

        .lux-page .bg-white {
            background: rgba(255, 255, 255, 0.92);
        }

        .lux-page .shadow-sm {
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
        }

        .lux-page .rounded-xl {
            border-radius: 18px;
        }

        .lux-hero {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.14), rgba(249, 250, 251, 0.9));
            border: 1px solid rgba(15, 118, 110, 0.2);
        }

        .lux-page canvas[id^="chart"] {
            cursor: pointer;
            display: block;
            width: 100% !important;
            max-width: 100%;
            height: var(--chart-height, 240px) !important;
            min-height: var(--chart-height, 240px) !important;
            max-height: var(--chart-height, 240px) !important;
        }

        .fte-avatar-chart-shell {
            position: relative;
        }

        .fte-avatar-chart-shell canvas {
            position: relative;
            z-index: 2;
        }

        .fte-avatar-layer {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            overflow: visible;
        }

        .fte-avatar-marker {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translate(-50%, -50%);
            border-radius: 9999px;
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, 0.96);
            background: linear-gradient(135deg, var(--fte-accent, #0f766e), #0f172a);
            box-shadow:
                0 16px 30px rgba(15, 23, 42, 0.18),
                0 0 0 2px var(--fte-accent, #0f766e);
        }

        .fte-avatar-marker img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            background: #f8fafc;
        }

        .fte-avatar-marker__fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
            letter-spacing: 0.02em;
            background:
                radial-gradient(circle at 30% 25%, rgba(255, 255, 255, 0.26), transparent 38%),
                linear-gradient(135deg, var(--fte-accent, #0f766e), #0f172a);
        }

        .fte-avatar-marker.has-photo .fte-avatar-marker__fallback {
            display: none;
        }

        .fte-avatar-marker.is-photo-missing img {
            display: none;
        }

        .fte-avatar-marker.is-photo-missing .fte-avatar-marker__fallback {
            display: flex;
        }

        #dashboardFabStack {
            position: fixed;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 55;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        #dashboardFabStack.is-hidden-by-chat,
        #dashboardFabStack.is-hidden-by-pagination {
            opacity: 0;
            pointer-events: none;
            transform: translate(-18px, -50%);
        }

        .dashboard-fab-btn {
            width: 88px;
            border: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, 0.94);
            border-radius: 24px;
            padding: 0.85rem 0.7rem;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.45rem;
            color: #0f172a;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(14px);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .dashboard-fab-btn:hover {
            transform: translateY(-2px);
            border-color: #67e8f9;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.16);
        }

        .dashboard-fab-btn__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 18px;
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .dashboard-fab-btn__icon--filter {
            background: linear-gradient(135deg, #0f172a 0%, #155e75 100%);
        }

        .dashboard-fab-btn__icon--export {
            background: linear-gradient(135deg, #0f766e 0%, #0891b2 100%);
        }

        .dashboard-fab-btn__title {
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1;
        }

        .dashboard-fab-btn__meta {
            font-size: 0.66rem;
            color: #64748b;
            line-height: 1.15;
        }

        .dashboard-fab-btn__badge {
            position: absolute;
            top: -0.35rem;
            right: -0.35rem;
            min-width: 1.5rem;
            height: 1.5rem;
            padding: 0 0.35rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            border: 2px solid #fff;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 800;
            line-height: 1;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }

        .dashboard-fab-btn__badge.is-show {
            display: inline-flex;
        }

        #filterDrawerOverlay,
        #exportPopupOverlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(2px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            z-index: 56;
        }

        #filterDrawerOverlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        #exportPopupOverlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        #filterDrawer {
            position: fixed;
            inset: 0 auto 0 0;
            width: min(92vw, 380px);
            height: 100dvh;
            transform: translateX(-105%);
            transition: transform 0.25s ease;
            z-index: 57;
        }

        #filterDrawer.is-open {
            transform: translateX(0);
        }

        #exportPopup {
            position: fixed;
            left: 6.75rem;
            top: 50%;
            width: min(92vw, 400px);
            transform: translate(-16px, -50%);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.22s ease, transform 0.22s ease;
            z-index: 57;
        }

        #exportPopup.is-open {
            opacity: 1;
            pointer-events: auto;
            transform: translate(0, -50%);
        }

        .dashboard-status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            border: 1px solid transparent;
        }

        .dashboard-status-pill.is-active {
            background: #ecfeff;
            border-color: #a5f3fc;
            color: #155e75;
        }

        .dashboard-status-pill.is-idle {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #475569;
        }

        .dashboard-filter-pill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .dashboard-filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            padding: 0.38rem 0.72rem;
            font-size: 0.75rem;
            color: #334155;
            line-height: 1.1;
        }

        .dashboard-filter-pill__label {
            color: #0891b2;
            font-weight: 700;
        }

        .dashboard-filter-empty {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            padding: 0.38rem 0.72rem;
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.1;
        }

        @media (max-width: 1024px), (hover: none) and (pointer: coarse) {
            #dashboardFabStack {
                left: 0.75rem;
                top: auto;
                bottom: calc(0.75rem + env(safe-area-inset-bottom));
                transform: none;
                gap: 0.5rem;
            }

            #dashboardFabStack.is-hidden-by-chat,
            #dashboardFabStack.is-hidden-by-pagination {
                transform: translateY(14px);
            }

            .dashboard-fab-btn {
                width: 3.4rem;
                height: 3.4rem;
                padding: 0;
                border-radius: 999px;
                justify-content: center;
                gap: 0;
            }

            .dashboard-fab-btn__icon {
                width: 2.35rem;
                height: 2.35rem;
                border-radius: 0.95rem;
            }

            .dashboard-fab-btn__title,
            .dashboard-fab-btn__meta {
                display: none;
            }

            .dashboard-fab-btn__badge {
                top: -0.2rem;
                right: -0.2rem;
                min-width: 1.35rem;
                height: 1.35rem;
                font-size: 0.68rem;
            }

            #filterDrawer {
                width: min(94vw, 360px);
            }

            #exportPopup {
                left: 50%;
                top: auto;
                bottom: calc(1rem + env(safe-area-inset-bottom));
                width: min(94vw, 360px);
                transform: translate(-50%, 18px);
            }

            #exportPopup.is-open {
                transform: translate(-50%, 0);
            }
        }

        @media (max-width: 640px) {
            .lux-chart-scroll {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                overflow: visible;
                gap: 1rem;
                padding-bottom: 0.75rem;
            }

            .lux-chart-card {
                min-width: 0;
            }

            .lux-chart-scroll::-webkit-scrollbar {
                height: 6px;
            }

            .lux-chart-scroll::-webkit-scrollbar-thumb {
                background: rgba(148, 163, 184, 0.6);
                border-radius: 999px;
            }

            .lux-swipe-tip {
                display: none;
            }

            .lux-chart-card h3 {
                font-size: 0.95rem;
            }

            #chartModalBody .lux-modal-table thead {
                display: none;
            }

            #chartModalBody .lux-modal-table,
            #chartModalBody .lux-modal-table tbody,
            #chartModalBody .lux-modal-table tr,
            #chartModalBody .lux-modal-table td {
                display: block;
                width: 100%;
            }

            #chartModalBody .lux-modal-table tr {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 10px 12px;
                margin-bottom: 10px;
                background: #ffffff;
            }

            #chartModalBody .lux-modal-table td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 6px 0;
            }

            #chartModalBody .lux-modal-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
            }

            #chartModal .chart-modal-header {
                align-items: flex-start;
            }

            #chartModal .chart-modal-actions {
                width: 100%;
                justify-content: space-between;
            }

            #chartModal .chart-modal-sort {
                flex: 1 1 auto;
                min-width: 0;
            }

            #chartModal .chart-modal-sort select {
                max-width: 100%;
            }

            .lux-mobile-table thead {
                display: none;
            }

            .lux-mobile-table,
            .lux-mobile-table tbody,
            .lux-mobile-table tr,
            .lux-mobile-table td {
                display: block;
                width: 100%;
            }

            .lux-mobile-table tr {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 10px 12px;
                margin-bottom: 10px;
                background: #ffffff;
            }

            .lux-mobile-table td {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
                padding: 8px 0;
                word-break: break-word;
            }

            .lux-mobile-table td::before {
                content: attr(data-label);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.03em;
                text-transform: uppercase;
                color: #94a3b8;
            }

            .rt-type-summary-table tr {
                background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                border-color: #dbeafe;
                box-shadow: 0 10px 18px rgba(15, 23, 42, 0.04);
            }

            .rt-type-summary-table td.type-metric-cell {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                border-top: 1px dashed #e2e8f0;
            }

            .rt-type-summary-table .type-metric-value {
                text-align: right;
                font-weight: 700;
                color: #0f172a;
            }
        }

        .lux-muted {
            color: var(--lux-ink-soft);
        }

        .lux-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(15, 118, 110, 0.25), transparent);
        }

        .lux-quick-nav {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .lux-quick-nav a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            border-radius: 14px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            text-align: center;
            line-height: 1.25;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06);
        }

        .lux-quick-nav a[data-nav-tone='projects'] {
            border: 1px solid rgba(37, 99, 235, 0.22);
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.98), rgba(219, 234, 254, 0.96));
            color: #1d4ed8;
        }

        .lux-quick-nav a[data-nav-tone='create'] {
            border: 1px solid rgba(5, 150, 105, 0.22);
            background: linear-gradient(135deg, rgba(236, 253, 245, 0.98), rgba(209, 250, 229, 0.96));
            color: #047857;
        }

        .lux-quick-nav a[data-nav-tone='fte-person'] {
            border: 1px solid rgba(147, 51, 234, 0.2);
            background: linear-gradient(135deg, rgba(250, 245, 255, 0.98), rgba(243, 232, 255, 0.96));
            color: #7e22ce;
        }

        .lux-quick-nav a[data-nav-tone='fte-project'] {
            border: 1px solid rgba(234, 88, 12, 0.22);
            background: linear-gradient(135deg, rgba(255, 247, 237, 0.98), rgba(254, 215, 170, 0.96));
            color: #c2410c;
        }

        .lux-summary-scroll {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .lux-summary-card {
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08);
            padding: 14px;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 12px;
            align-items: center;
        }

        .lux-summary-card[data-tone='emerald'] {
            border-color: rgba(5, 150, 105, 0.35);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(255, 255, 255, 0.96));
        }

        .lux-summary-card[data-tone='amber'] {
            border-color: rgba(217, 119, 6, 0.35);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.18), rgba(255, 255, 255, 0.96));
        }

        .lux-summary-card[data-tone='sky'] {
            border-color: rgba(14, 116, 144, 0.35);
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.18), rgba(255, 255, 255, 0.96));
        }

        .lux-summary-card[data-tone='violet'] {
            border-color: rgba(124, 58, 237, 0.3);
            background: linear-gradient(135deg, rgba(167, 139, 250, 0.18), rgba(255, 255, 255, 0.96));
        }

        .lux-summary-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: rgba(15, 23, 42, 0.06);
        }

        .lux-summary-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #0f172a;
        }

        .lux-summary-label {
            font-size: 0.8rem;
            color: #475569;
        }

        .lux-summary-sub {
            font-size: 0.7rem;
            color: #64748b;
        }

        .lux-chart-scroll {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.5rem;
            align-items: start;
        }

        @media (min-width: 1536px) {
            .lux-chart-scroll {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lux-summary-scroll {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 1535px) {
            .lux-chart-scroll {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }

            .lux-summary-scroll {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 900px) and (max-width: 1280px) {
            .lux-chart-scroll {
                grid-template-columns: 1fr !important;
            }
        }

        .lux-chart-card {
            scroll-snap-align: start;
            height: auto;
        }

        .lux-swipe-tip {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px dashed rgba(59, 130, 246, 0.4);
            background: rgba(59, 130, 246, 0.08);
            color: #1e3a8a;
            border-radius: 14px;
            padding: 10px 12px;
            font-size: 12px;
        }

        .lux-swipe-tip button {
            border: none;
            background: transparent;
            color: #475569;
            font-size: 12px;
            cursor: pointer;
        }

        #chartModalBody .lux-modal-table {
            width: 100%;
            border-collapse: collapse;
        }

        #chartModalBody .lux-modal-table th,
        #chartModalBody .lux-modal-table td {
            padding: 8px;
        }

        #chartModal .chart-modal-panel {
            position: absolute;
            left: 50%;
            top: 50%;
            width: min(92vw, 80rem);
            max-height: 80vh;
            overflow: auto;
            transform: translate(-50%, -50%);
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.24);
        }

        #chartModal .chart-modal-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.9rem 1rem;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 5;
        }

        #chartModal .chart-modal-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        #chartModal .chart-modal-sort {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        #chartModal .chart-modal-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #475569;
            font-size: 1.35rem;
            line-height: 1;
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        #chartModal .chart-modal-close:hover {
            background: #f8fafc;
            color: #dc2626;
            transform: rotate(90deg);
        }

        #chartModal .chart-modal-close:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.14);
        }

        .lux-mobile-table {
            width: 100%;
            border-collapse: collapse;
        }

        .rt-type-summary-table .type-name-cell {
            font-weight: 700;
            color: #0f172a;
            line-height: 1.45;
        }

        .dashboard-inline-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }

        .dashboard-inline-legend__item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 12px;
            line-height: 1.35;
            color: #334155;
        }

        .dashboard-inline-legend__swatch {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            flex: 0 0 12px;
            margin-top: 2px;
        }

        .lux-chip {
            border: 1px solid rgba(15, 118, 110, 0.25);
            background: rgba(15, 118, 110, 0.08);
            color: var(--lux-emerald-dark);
        }

        .lux-grid-stagger > * {
            animation: lux-rise 0.6s ease both;
        }

        .lux-grid-stagger > *:nth-child(2) {
            animation-delay: 0.06s;
        }

        .lux-grid-stagger > *:nth-child(3) {
            animation-delay: 0.12s;
        }

        .lux-grid-stagger > *:nth-child(4) {
            animation-delay: 0.18s;
        }

        .lux-grid-stagger > *:nth-child(5) {
            animation-delay: 0.24s;
        }

        .lux-grid-stagger > *:nth-child(6) {
            animation-delay: 0.3s;
        }

        @keyframes lux-rise {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 640px) {
            .lux-page {
                background: linear-gradient(180deg, #f1f5f9 0%, #ffffff 40%, #f8fafc 100%);
            }

            .lux-page::before {
                width: 240px;
                height: 240px;
                inset: -10% -20% auto auto;
            }

            .lux-summary-scroll {
                display: flex;
                overflow-x: auto;
                gap: 12px;
                scroll-snap-type: x mandatory;
                padding-bottom: 6px;
            }

            .lux-summary-card {
                min-width: 82vw;
                scroll-snap-align: start;
            }

            .lux-summary-scroll::-webkit-scrollbar {
                height: 6px;
            }

            .lux-summary-scroll::-webkit-scrollbar-thumb {
                background: rgba(148, 163, 184, 0.5);
                border-radius: 999px;
            }
        }

        /* Final chart guardrail (must stay last in this style block) */
        .lux-page .lux-chart-scroll {
            align-items: start !important;
        }

        .lux-page .lux-chart-card {
            height: auto !important;
            min-height: 0 !important;
        }

        .lux-page .lux-chart-card canvas[id^="chart"] {
            width: 100% !important;
            height: var(--chart-height, 240px) !important;
            min-height: var(--chart-height, 240px) !important;
            max-height: var(--chart-height, 240px) !important;
        }
    </style>

    <div class="lux-page">
        <div class="max-w-7xl mx-auto px-4 py-8 space-y-8">
        <div id="dashboardFabStack">
            <button id="filterDrawerOpen" type="button" aria-label="เปิดฟิลเตอร์" class="dashboard-fab-btn focus:outline-none focus:ring-4 focus:ring-cyan-100">
                <span id="filterFabBadge" class="dashboard-fab-btn__badge" aria-hidden="true"></span>
                <span class="dashboard-fab-btn__icon dashboard-fab-btn__icon--filter">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 6h16M7 12h10m-7 6h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </span>
                <span class="dashboard-fab-btn__title">ฟิลเตอร์</span>
                <span class="dashboard-fab-btn__meta">ตั้งค่าเงื่อนไข</span>
            </button>

            <button id="exportPopupOpen" type="button" aria-label="เปิดเมนูดาวน์โหลด" class="dashboard-fab-btn focus:outline-none focus:ring-4 focus:ring-cyan-100">
                <span class="dashboard-fab-btn__icon dashboard-fab-btn__icon--export">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 4v9m0 0 4-4m-4 4-4-4M5 18h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="dashboard-fab-btn__title">ดาวน์โหลด</span>
                <span class="dashboard-fab-btn__meta">Excel หรือ CSV</span>
            </button>
        </div>

        <div id="filterDrawerOverlay"></div>
        <div id="exportPopupOverlay"></div>

        <aside id="filterDrawer">
            <div class="flex h-full flex-col border-r border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">คัดกรองข้อมูล</h3>
                        <p class="mt-1 text-xs text-slate-500">ปรับฟิลเตอร์แล้วข้อมูลบนหน้า dashboard จะอัปเดตทันที</p>
                    </div>
                    <button id="filterDrawerClose" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                        <span class="sr-only">ปิดฟิลเตอร์</span>
                        <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center rounded-full bg-cyan-50 border border-cyan-200 px-3 py-1 text-xs font-semibold text-cyan-700">
                            ปรับฟิลเตอร์แล้วผลจะอัปเดตทันที
                        </span>
                        <button id="filterReset" type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            ล้างฟิลเตอร์
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">ปีเริ่มต้น</label>
                            <select id="filterYear" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">ทั้งหมด</option>
                                @for ($y = now()->year + 543; $y >= 2557; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">Theme</label>
                            <select id="filterTheme" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">ทั้งหมด</option>
                                @foreach ($themes as $theme)
                                    <option value="{{ $theme }}">{{ $theme }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">ประเภททุน</label>
                            <select id="filterFunding" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">ทั้งหมด</option>
                                <option value="ภายใน">ภายใน</option>
                                <option value="ภายนอก">ภายนอก</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">ประเภทโครงการ</label>
                            <select id="filterType" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">ทั้งหมด</option>
                                @foreach ($typeOptions as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">สถานะโครงการ</label>
                            <select id="filterStatus" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">ทั้งหมด</option>
                                @foreach ($statusOptions as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">แหล่งทุน</label>
                            <select id="filterSource" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">ทั้งหมด</option>
                                @foreach ($fundingSources as $source)
                                    <option value="{{ $source }}">{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">PI โครงการ</label>
                            <input type="text" id="filterPi" list="filterPiSuggestions" class="w-full border rounded px-2 py-1 text-sm"
                                placeholder="ชื่อผู้วิจัยหลัก">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">สังกัดผู้วิจัยหลัก</label>
                            <input type="text" id="filterPiAffiliation" list="filterPiAffiliationSuggestions" class="w-full border rounded px-2 py-1 text-sm"
                                placeholder="สังกัดผู้วิจัยหลัก">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block">ค้นหาโครงการ</label>
                            <input type="text" id="filterName" list="filterNameSuggestions" class="w-full border rounded px-2 py-1 text-sm"
                                placeholder="ชื่อโครงการ">
                        </div>
                    </div>

                    <datalist id="filterPiSuggestions"></datalist>
                    <datalist id="filterPiAffiliationSuggestions"></datalist>
                    <datalist id="filterNameSuggestions"></datalist>
                    <datalist id="tableSearchSuggestions"></datalist>
                </div>
            </div>
        </aside>

        <div id="exportPopup" aria-hidden="true">
            <div class="rounded-[28px] border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-900/15">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">ดาวน์โหลดข้อมูล</h3>
                        <p class="mt-1 text-xs text-slate-500">ส่งออกข้อมูลตามฟิลเตอร์ที่กำลังใช้อยู่</p>
                    </div>
                    <button id="exportPopupClose" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                        <span class="sr-only">ปิดหน้าต่างดาวน์โหลด</span>
                        <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                    </button>
                </div>

                <div class="mt-4 rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-white p-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span id="exportPopupState" class="dashboard-status-pill is-idle">ยังไม่ได้ใช้ฟิลเตอร์</span>
                        <span id="exportPopupCount" class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">0 โครงการ</span>
                    </div>
                    <p id="exportPopupSummary" class="mt-2 text-xs text-slate-600">ระบบจะดาวน์โหลดข้อมูลทั้งหมดในระบบ</p>
                    <div id="exportPopupFilterList" class="dashboard-filter-pill-list mt-3"></div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <button id="exportExcelAction" type="button"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200">
                        Excel (.xlsx)
                    </button>
                    <button id="exportCsvAction" type="button"
                        class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm font-semibold text-cyan-900 shadow-sm transition hover:border-cyan-300 hover:bg-cyan-100 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                        CSV (.csv)
                    </button>
                </div>
            </div>
        </div>

        <div class="lux-card p-4">
            <div class="lux-quick-nav">
                <a href="/rm" data-nav-tone="projects">รายการโปรเจค</a>
                <a href="/rm/create" data-nav-tone="create">สร้างรายการโปรเจค</a>
                <a href="{{ route('fte.index') }}" data-nav-tone="fte-person">FTE รายบุคคล</a>
                <a href="{{ route('fte.project.index') }}" data-nav-tone="fte-project">FTE รายโปรเจค</a>
            </div>
        </div>

        <!-- 2) Realtime Summary -->
        <div class="lux-card p-4 md:p-5" id="section-filters">
            <div class="rounded-xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-white p-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2">
                    <div>
                        <h2 class="lux-title text-2xl md:text-3xl text-slate-900">ภาพรวมโครงการวิจัย</h2>
                        <h4 class="text-sm font-bold text-slate-800">เอกสารสรุปข้อมูลทั้งหมด (Realtime)</h4>
                        <p id="rtSummaryCaption" class="text-xs text-slate-500">สรุปตามฟิลเตอร์ปัจจุบัน</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        <span id="rtFilterBadge" class="inline-flex items-center rounded-full bg-cyan-50 border border-cyan-200 px-3 py-1 text-xs font-semibold text-cyan-700">0 โครงการ</span>
                        <span class="text-xs text-slate-500">เปิดฟิลเตอร์จากปุ่มด้านซ้ายเพื่อปรับเงื่อนไข</span>
                    </div>
                </div>

                <div id="section-summary" class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @php
                $summaryItems = [
                    ['count' => $projects->count(), 'label' => 'โครงการ', 'icon' => '📁', 'sub' => 'ทั้งหมดในระบบ', 'value_id' => 'heroSummaryProjects'],
                    ['count' => $projects->pluck('pi_name')->unique()->count(), 'label' => 'หัวหน้าโครงการ', 'icon' => '👨‍🔬', 'sub' => 'PI ทั้งหมด', 'value_id' => 'heroSummaryPi'],
                    ['count' => $copis->count(), 'label' => 'ผู้วิจัยร่วม', 'icon' => '👥', 'sub' => 'Co-PI', 'value_id' => 'heroSummaryCopi'],
                    ['count' => $coResearches->count(), 'label' => 'ผู้ร่วมวิจัย', 'icon' => '🤝', 'sub' => 'Co-Research', 'value_id' => 'heroSummaryCoResearch'],
                    ['count' => number_format($fteRoles->sum('fte_value'), 2), 'label' => 'FTE รวม', 'icon' => '📈', 'sub' => 'รวมทุกปี', 'value_id' => 'heroSummaryFte'],
                    ['count' => $outputs->count(), 'label' => 'ผลงานวิจัย', 'icon' => '📝', 'sub' => 'Outputs', 'value_id' => 'heroSummaryOutputs'],
                ];
            @endphp
                    @foreach ($summaryItems as $item)
                        <div class="rounded-xl border border-cyan-100 bg-white/90 p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-lg">{{ $item['icon'] }}</div>
                                <div class="min-w-0">
                                    <div class="text-3xl font-extrabold leading-none text-slate-900" id="{{ $item['value_id'] ?? '' }}">{{ $item['count'] }}</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-700">{{ $item['label'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $item['sub'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 rounded-2xl border border-slate-200 bg-white/90 p-3">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-col gap-2 lg:flex-row lg:items-center">
                            <span id="dashboardFilterState" class="dashboard-status-pill is-idle">ยังไม่ได้ใช้ฟิลเตอร์</span>
                            <p id="dashboardFilterDetail" class="text-xs text-slate-500">กำลังแสดงข้อมูลทั้งหมดในระบบ</p>
                        </div>
                        <span class="text-xs text-slate-500">ปุ่มลอยด้านซ้ายใช้สำหรับเปิดฟิลเตอร์หรือดาวน์โหลดข้อมูล</span>
                    </div>
                    <div id="dashboardFilterList" class="dashboard-filter-pill-list mt-3"></div>
                </div>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-lg border border-cyan-100 bg-white/90 p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">งบประมาณรวม</div>
                        <div id="rtSummaryBudget" class="mt-1 text-lg lg:text-2xl font-extrabold text-slate-900">0 บ.</div>
                    </div>
                    <div class="rounded-lg border border-cyan-100 bg-white/90 p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">งบเฉลี่ย/โครงการ</div>
                        <div id="rtSummaryAvgBudget" class="mt-1 text-lg lg:text-2xl font-extrabold text-slate-900">0 บ.</div>
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                    <table class="min-w-full text-xs md:text-sm lux-mobile-table rt-type-summary-table">
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="px-3 py-2 text-left">ประเภทโครงการ</th>
                                <th class="px-3 py-2 text-right">จำนวนโครงการ</th>
                                <th class="px-3 py-2 text-right">สัดส่วน</th>
                                <th class="px-3 py-2 text-right">งบรวม</th>
                            </tr>
                        </thead>
                        <tbody id="rtTypeSummaryBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3) Charts -->
        <div id="section-charts">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">สรุปสถานะและแนวโน้มเชิงกลยุทธ์</h3>
                    <p class="text-xs text-slate-500">รวมกราฟสำคัญที่ช่วยอ่านภาพรวมได้ในมุมเดียว</p>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                        <span class="lux-chip px-3 py-1 rounded-full">แตะกราฟเพื่อดูรายละเอียด</span>
                        <span class="lux-chip px-3 py-1 rounded-full">แตะตารางใน Pop-up เพื่อดูข้อมูลครบ</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btnExportPdf"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg shadow-sm">
                        Export Dashboard เป็น PDF
                    </button>
                </div>
            </div>
            <div id="chartSwipeTip" class="lux-swipe-tip mb-3">
                <span>เลื่อนซ้ายขวาเพื่อดูกราฟต่อไป • แตะกราฟเพื่อดูข้อมูลเพิ่ม</span>
                <button type="button" data-close="chartSwipeTip">ปิด</button>
            </div>
            <div class="lux-chart-scroll mb-6">
                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🔴 RED ZONE: ย้อนหลัง 3 ปี</h3>
                <canvas id="chartRedZoneTimeline" height="380"></canvas>
            </div>
            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🔥 ภาพรวมสถานะโครงการตามสัญญา</h3> <canvas
                    id="chartOverdueProjects" height="380"></canvas>
                <div id="chartOverdueLegend" class="dashboard-inline-legend"></div>
            </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4 md:col-span-2 lux-drawer"
                data-lux-drawer="overdue">
                <button type="button"
                    class="w-full flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-left"
                    data-drawer-toggle aria-expanded="false">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-700">⏱️ รายการโครงการเกินกำหนดที่ยังต้องติดตาม</h3>
                        <span id="overdueCountBadge"
                            class="text-xs font-semibold px-3 py-1 rounded-full lux-chip">0 โครงการ</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>กดเพื่อดู/ซ่อน</span>
                        <span class="drawer-chevron transition-transform">▾</span>
                    </div>
                </button>
                <div class="lux-drawer-body hidden mt-3" data-drawer-body>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border lux-mobile-table" id="overdueTable">
                            <thead class="bg-gray-50 text-xs text-gray-700">
                                <tr>
                                    <th class="p-2 text-left">ชื่อโครงการ</th>
                                    <th class="p-2 text-center">วันที่สิ้นสุดสัญญา (พ.ศ.)</th>
                                    <th class="p-2 text-center">วันที่สิ้นสุดการทำงานทีม</th>
                                    <th class="p-2 text-center">เกินกำหนด (วัน)</th>
                                    <th class="p-2 text-center">สถานะโครงการ</th>
                                    <th class="p-2 text-center">PI</th>
                                </tr>
                            </thead>
                            <tbody id="overdueTableBody">
                                <tr class="border-t">
                                    <td colspan="6" class="p-3 text-center text-gray-500">กำลังประมวลผลข้อมูล</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between mt-3 text-xs text-gray-600">
                        <button id="overduePrev" class="border rounded px-3 py-1 hover:bg-gray-50">ก่อนหน้า</button>
                        <div id="overduePagination">หน้า 1 / 1</div>
                        <button id="overdueNext" class="border rounded px-3 py-1 hover:bg-gray-50">ถัดไป</button>
                    </div>
                </div>
            </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4 md:col-span-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📍 จำนวน Co-Research ตามหน่วยงาน (3/6/12 เดือน)</h3>
                <canvas id="chartCoResearchByUnitWindow" height="200"></canvas>
            </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 จำนวนโครงการรายปี (รวม)</h3>
                    <canvas id="chartProjectsPerYear" height="420"></canvas>
                </div>
                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4 ">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📅 โครงการทั้งหมด โครงการใหม่ เเละ
                        โครงการกำลังดำเนินการ เเละ ปิด/ยกเลิกโปรเจค (ต่อปี)</h3>
                    <canvas id="chartYearlyNewVsTotal" height="420"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">🏷️ สัดส่วนทุน ภายใน/ภายนอก (รายปี)</h3>
                    <canvas id="chartFundingSplit" height="250"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">💰 งบประมาณที่ได้รับ (รวมรายปี)</h3>
                    <canvas id="chartBudgetPerYear" height="250"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 สัดส่วนทุนภายนอกที่ได้รับตามแหล่งทุน</h3>
                    <canvas id="chartExternalBySource" height="250"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏛️ สัดส่วนทุนภายในที่ได้รับตามแหล่งทุน</h3>
                    <canvas id="chartInternalBySource" height="250"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 ทุนภายนอกตามแหล่งทุน: แนวโน้มรายปี</h3>
                    <canvas id="chartExternalBySourceYear" height="380"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏛️ ทุนภายในตามแหล่งทุน: แนวโน้มรายปี</h3>
                    <canvas id="chartInternalBySourceYear" height="380"></canvas>
                </div>



                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📝 Output (รายปี)</h3>
                    <canvas id="chartOutputStacked" height="380"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📚 Publication รายปี</h3>
                    <canvas id="chartPublicationPerYear" height="280"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4 mt-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">✍️ บทบาทผู้เขียน (First/Co/Corresponding) รายปี</h3>
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-gray-500">ปี (พ.ศ.):
                                <select id="yearFilter" class="ml-1 border rounded px-2 py-1 text-sm"></select>
                            </label>
                            <button id="btnPrevYear" class="border rounded px-2 py-1 text-xs">◀</button>
                            <button id="btnNextYear" class="border rounded px-2 py-1 text-xs">▶</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-gray-500">ค้นหาชื่อ:
                                <input id="authorSearch" type="text" class="ml-1 border rounded px-2 py-1 text-sm"
                                    placeholder="พิมพ์บางส่วนของชื่อ">
                            </label>
                            <label class="text-xs text-gray-500">Top N:
                                <select id="topN" class="ml-1 border rounded px-2 py-1 text-sm">
                                    <option>5</option>
                                    <option selected>12</option>
                                    <option>20</option>
                                    <option>30</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <canvas id="chartAuthorRolePerYear" height="320"></canvas>
                </div>


                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">🧩 สถานะของโครงการ รายปี</h3>
                    <canvas id="chartStatusYear" height="380"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">🤝 สัดส่วนโครงการ (นอกคณะ ในคณะ เเละ ในหน่วย)</h3>
                    <canvas id="chartCollabBreakdown" height="380"></canvas>
                </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">👨‍🔬 จำนวนงานของ PI ในหน่วยงาน</h3>
                <canvas id="chartPIsPerUnit" height="300"></canvas>
            </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 การกระจาย FTE</h3>
                <div class="fte-avatar-chart-shell">
                    <canvas id="chartFteSummary" height="250"></canvas>
                    <div class="fte-avatar-layer" id="chartFteSummaryAvatarLayer" aria-hidden="true"></div>
                </div>
            </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📑 IRB ใกล้หมดอายุ (6 เดือนข้างหน้า)</h3>
                <canvas id="chartIRBExpiry" height="250"></canvas>
            </div>
        </div>

            <!-- IRB List -->
            <div class="bg-white shadow-sm border rounded-xl p-4 mb-6 lux-drawer" id="section-irb"
                data-lux-drawer="irb">
                <button type="button"
                    class="w-full flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-left"
                    data-drawer-toggle aria-expanded="false">
                    <h3 class="text-sm font-semibold text-gray-700">📑 รายการ IRB</h3>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>กดเพื่อดู/ซ่อน</span>
                        <span class="drawer-chevron transition-transform">▾</span>
                    </div>
                </button>
                <div class="lux-drawer-body hidden mt-3" data-drawer-body>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                        <div class="text-xs text-gray-500">ตัวกรอง:</div>
                        <label class="text-xs text-gray-500">
                            <select id="irbDashFilter" class="ml-1 border rounded px-2 py-1 text-xs">
                                <option value="exp_3m">IRB จะหมดอายุใน 3 เดือน</option>
                                <option value="latest">IRB ใหม่สุด → เก่าสุด</option>
                            </select>
                        </label>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border lux-mobile-table" id="irbDashTable">
                            <thead class="bg-gray-50 text-xs text-gray-700">
                                <tr>
                                    <th class="p-2 text-left">โครงการ</th>
                                    <th class="p-2 text-left">รหัส IRB</th>
                                    <th class="p-2 text-left">หน่วยงาน</th>
                                    <th class="p-2 text-center">วันหมดอายุ</th>
                                </tr>
                            </thead>
                            <tbody id="irbDashBody">
                                <tr class="border-t">
                                    <td colspan="4" class="p-3 text-center text-gray-500">กำลังประมวลผลข้อมูล</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between mt-3 text-xs text-gray-600">
                        <button id="irbDashPrev" class="border rounded px-3 py-1 hover:bg-gray-50">ก่อนหน้า</button>
                        <div id="irbDashPagination">หน้า 1 / 1</div>
                        <button id="irbDashNext" class="border rounded px-3 py-1 hover:bg-gray-50">ถัดไป</button>
                    </div>
                </div>
            </div>
        </div>

            <!-- 4) Co-Research (จาก COPIs) Summary -->
            <div class="bg-white shadow-sm border rounded-xl p-4 mb-6 lux-drawer" id="section-copi"
                data-lux-drawer="copi">
                <button type="button"
                    class="w-full flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-left mb-3"
                    data-drawer-toggle aria-expanded="false">
                    <h3 class="text-sm font-semibold text-gray-700">🤝 รายชื่อผู้วิจัยร่วม CO-PI</h3>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>กดเพื่อดู/ซ่อน</span>
                        <span class="drawer-chevron transition-transform">▾</span>
                    </div>
                </button>
                <div class="lux-drawer-body hidden" data-drawer-body>

                @php
                    // รวมตาม "ชื่อ|หน่วยงาน" เพื่อกันกรณีชื่อซ้ำคนละสังกัด
                    $grouped = $copis->groupBy(function ($c) {
                        $name = trim($c->name ?? '');
                        $aff = trim($c->affiliation ?? ($c->unit ?? ($c->department ?? ''))); // fallback
                        return $name . '|' . $aff;
                    });
                    $copiRows = $grouped->map(function ($rows, $key) {
                        [$name, $aff] = explode('|', $key);
                        $projectCount = $rows->pluck('project_id')->filter()->unique()->count();
                        return [
                            'name' => $name ?: 'ไม่ระบุ',
                            'affiliation' => $aff ?: 'ไม่ระบุ',
                            'project_count' => $projectCount,
                        ];
                    })->values();
                @endphp

                <table class="w-full text-sm border lux-mobile-table">
                    <thead class="bg-gray-50 text-xs text-gray-700">
                        <tr>
                            <th class="p-2 text-left">ชื่อ</th>
                            <th class="p-2 text-left">หน่วยงาน</th>
                            <th class="p-2 text-center">จำนวนโครงการ</th>
                        </tr>
                    </thead>
                    <tbody id="copiTableBody">
                        <tr class="border-t">
                            <td colspan="3" class="p-3 text-center text-gray-500">กำลังประมวลผลข้อมูล</td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex items-center justify-between mt-3 text-xs text-gray-600">
                    <button id="copiPrev" class="border rounded px-3 py-1 hover:bg-gray-50">ก่อนหน้า</button>
                    <div id="copiPagination">หน้า 1 / 1</div>
                    <button id="copiNext" class="border rounded px-3 py-1 hover:bg-gray-50">ถัดไป</button>
                </div>
                </div>
            </div>

            <!-- 5) Project Table -->
            <div class="bg-white shadow-sm border rounded-xl p-4" id="section-projects">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📋 รายการโครงการทั้งหมด</h3>
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                        <span id="tableCount">รวม 0 รายการ</span>
                        <span class="lux-chip text-xs font-semibold px-3 py-1 rounded-full">แสดงหน้าละ 10 รายการ</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs text-gray-500">ค้นหาในตาราง:
                            <input id="tableSearch" type="text"
                                list="tableSearchSuggestions"
                                class="ml-1 border rounded px-2 py-1 text-sm" placeholder="ชื่อโครงการ / PI / รหัสโครงการ">
                        </label>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border lux-mobile-table" id="projectTable">
                        <thead class="bg-gray-100 text-xs text-gray-700">
                            <tr>
                                <th class="p-2 text-left">ชื่อโครงการ</th>
                                <th class="p-2">ปี</th>
                                <th class="p-2">PI</th>
                                <th class="p-2">Co-PI</th>
                                <th class="p-2">CoResearch</th>
                                <th class="p-2">FTE รวม</th>
                                <th class="p-2">Output</th>
                                <th class="p-2">IRB ล่าสุด</th>
                            </tr>
                        </thead>
                        <tbody id="projectTableBody">
                            <tr class="border-t">
                                <td colspan="8" class="p-3 text-center text-gray-500">กำลังประมวลผลข้อมูล</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="projectTablePaginationWrap" class="flex items-center justify-between mt-4 text-xs text-gray-600">
                    <button id="tablePrev"
                        class="border rounded px-3 py-1 hover:bg-gray-50">ก่อนหน้า</button>
                    <div id="tablePagination">หน้า 1 / 1</div>
                    <button id="tableNext"
                        class="border rounded px-3 py-1 hover:bg-gray-50">ถัดไป</button>
                </div>
            </div>

        </div> <!-- end container -->

        <!-- Modal สำหรับ Drill-down -->
        <div id="chartModal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40" onclick="closeModal()"></div>
            <div class="chart-modal-panel">
                <div class="chart-modal-header">
                    <h3 id="chartModalTitle" class="font-semibold text-gray-800">รายละเอียด</h3>
                    <div class="chart-modal-actions">
                        <label class="chart-modal-sort">Sort:
                            <select id="chartModalSort" class="ml-1 border rounded px-2 py-1 text-xs">
                                <option value="name_asc">ชื่อ (ก-ฮ)</option>
                                <option value="name_desc">ชื่อ (ฮ-ก)</option>
                                <option value="pi_asc">PI (ก-ฮ)</option>
                                <option value="pi_desc">PI (ฮ-ก)</option>
                                <option value="year_desc">ปี (ใหม่→เก่า)</option>
                                <option value="year_asc">ปี (เก่า→ใหม่)</option>
                                <option value="funding_asc">แหล่งทุน (ก-ฮ)</option>
                                <option value="funding_desc">แหล่งทุน (ฮ-ก)</option>
                                <option value="budget_desc">งบ (มาก→น้อย)</option>
                                <option value="budget_asc">งบ (น้อย→มาก)</option>
                            </select>
                        </label>
                        <button type="button" class="chart-modal-close" aria-label="ปิดหน้าต่าง" onclick="closeModal()">&times;</button>
                    </div>
                </div>
                <div id="chartModalBody" class="p-4 text-sm"></div>
            </div>
        </div>

        <!-- PDF Export workspace (hidden from UI) -->
        <style>
            #pdfExportContainer {
                position: absolute;
                left: -9999px;
                top: 0;
                width: 1200px;
                padding: 0;
                background: #f8fafc;
                font-family: 'Sarabun', 'Prompt', 'Inter', 'Tahoma', sans-serif;
                color: #0f172a;
            }

            .pdf-page {
                width: 1200px;
                height: 820px;
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
                padding: 22px;
                box-sizing: border-box;
                margin-bottom: 24px;
                overflow: hidden;
            }

            .pdf-header {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                margin-bottom: 14px;
            }

            .pdf-title {
                font-size: 18px;
                font-weight: 700;
                letter-spacing: 0.01em;
            }

            .pdf-subtitle {
                font-size: 12px;
                color: #475569;
            }

            .pdf-meta {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                align-items: center;
                font-size: 11px;
                color: #1f2937;
                margin-top: 4px;
            }

            .pdf-grid-2 {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }

            .pdf-chart-card,
            .pdf-table-card {
                border: none;
                border-radius: 0;
                padding: 8px;
                background: #ffffff;
                box-shadow: none;
                height: 100%;
                box-sizing: border-box;
            }

            .pdf-chart-title,
            .pdf-table-title {
                font-size: 13px;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 8px;
            }

            .pdf-chart-card img {
                width: 100%;
                height: auto;
                display: block;
                border-radius: 8px;
                background: #f8fafc;
            }

            .pdf-table-card table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                color: #0f172a;
                table-layout: fixed;
            }

            .pdf-table-card th,
            .pdf-table-card td {
                border: 1px solid #e2e8f0;
                padding: 6px 8px;
                text-align: left;
                word-break: break-word;
                white-space: normal;
                vertical-align: top;
            }

            .pdf-table-card th {
                background: #f1f5f9;
                font-weight: 700;
            }

            .pdf-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 10px;
                font-size: 11px;
                border-radius: 999px;
                background: #ecfeff;
                color: #0ea5e9;
                border: 1px solid #bae6fd;
            }

            @page {
                size: A4 landscape;
                margin: 12mm;
            }
        </style>
        <div id="pdfExportContainer" class="hidden"></div>
        <div id="pdfExportOverlay"
            class="hidden fixed inset-0 z-[60] bg-black/30 backdrop-blur-sm flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-lg px-4 py-3 text-sm text-slate-700 flex items-center gap-2">
                <div class="h-4 w-4 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <div>กำลังสร้างไฟล์ PDF...</div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
        <script>
            window.currentUserName = @json(auth()->user()->name ?? 'ไม่ระบุ');
            window.loginLogoUrl = @json(asset('images/sihp-logo.png'));
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const tip = document.getElementById('chartSwipeTip');
                if (!tip) return;
                const key = 'dashboardSwipeTipDismissed';
                if (localStorage.getItem(key)) {
                    tip.style.display = 'none';
                    return;
                }
                const closeBtn = tip.querySelector('[data-close="chartSwipeTip"]');
                const hideTip = () => {
                    tip.style.display = 'none';
                    localStorage.setItem(key, '1');
                };
                closeBtn?.addEventListener('click', hideTip);
                setTimeout(() => {
                    if (window.innerWidth <= 640) hideTip();
                }, 7000);
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawers = Array.from(document.querySelectorAll('[data-lux-drawer]'));
                if (!drawers.length) return;

                const closeDrawer = (drawer) => {
                    const body = drawer.querySelector('[data-drawer-body]');
                    const toggle = drawer.querySelector('[data-drawer-toggle]');
                    const chevron = drawer.querySelector('.drawer-chevron');
                    body?.classList.add('hidden');
                    toggle?.setAttribute('aria-expanded', 'false');
                    chevron?.classList.remove('rotate-180');
                };

                const openDrawer = (drawer) => {
                    const body = drawer.querySelector('[data-drawer-body]');
                    const toggle = drawer.querySelector('[data-drawer-toggle]');
                    const chevron = drawer.querySelector('.drawer-chevron');
                    body?.classList.remove('hidden');
                    toggle?.setAttribute('aria-expanded', 'true');
                    chevron?.classList.add('rotate-180');
                };

                drawers.forEach(drawer => {
                    const toggle = drawer.querySelector('[data-drawer-toggle]');
                    const body = drawer.querySelector('[data-drawer-body]');
                    if (!toggle || !body) return;
                    toggle.addEventListener('click', (event) => {
                        event.stopPropagation();
                        const isOpen = !body.classList.contains('hidden');
                        drawers.forEach(d => {
                            if (d !== drawer) closeDrawer(d);
                        });
                        if (isOpen) {
                            closeDrawer(drawer);
                        } else {
                            openDrawer(drawer);
                        }
                    });
                    body.addEventListener('click', (event) => {
                        event.stopPropagation();
                    });
                });

                document.addEventListener('click', (event) => {
                    if (!event.target.closest('[data-lux-drawer]')) {
                        drawers.forEach(closeDrawer);
                    }
                });
            });
        </script>
        <script>
            /* ---------- Data from backend ---------- */

            const projects = @json($dashboardProjectsJs);
            const flatProjectRelation = (key) => projects.flatMap((project) => {
                if (!(project && Array.isArray(project[key]))) return [];
                return project[key]
                    .filter(Boolean)
                    .map((item) => ({
                        ...item,
                        project_id: item?.project_id ?? project?.id ?? null,
                    }));
            });
            const outputs = flatProjectRelation('outputs');
            const ftes = flatProjectRelation('fte_roles');
            const irbs = flatProjectRelation('irbs');
            const copisSummary = @json($copiRows);
            const fteProfileUrls = @json($fteProfileUrls ?? []);
            window.outputs = outputs;



            /* ---------- Helpers ---------- */
            const toNumber = v => isNaN(+v) ? 0 : +v;
            const thaiYear = y => y; // ถ้า start_year เก็บเป็น พ.ศ. อยู่แล้วก็คืนตามเดิม
            const getYear = (p) => p.start_year || 'ไม่ระบุ';
            const getEndYear = (p) => p.end_year || p.finish_year || null; // ปรับตามฟิลด์จริง
            const getEndDateRaw = (p) => p.end_date || p.team_end_date || p.finish_date || p.endDate || null;
            const getContractEndDateRaw = (p) => p.end_date || p.endDate || null;
            const getTeamEndDateRaw = (p) => p.team_end_date || p.teamEndDate || null;
            const getBudget = (p) => toNumber(p.total_budget ?? p.budget ?? 0);
            const getFundingType = (p) => p.funding_type || 'ไม่ระบุ';
            const getFundingSource = (p) => p.funding_source || 'ไม่ระบุ';
            const getUnit = (p) => p.unit_name || p.department || 'ไม่ระบุ';

            const outputYear = (o) => o.year ?? o.export_year ?? 'ไม่ระบุ';
            const outputType = (o) => (o.type || 'อื่นๆ').trim();

            function parseProjectDate(raw) {
                if (!raw) return null;
                const s = String(raw).trim();
                const direct = new Date(s);
                if (!isNaN(direct)) return direct;

                const m1 = s.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})$/);
                if (m1) {
                    const day = parseInt(m1[1], 10);
                    const month = parseInt(m1[2], 10);
                    let year = parseInt(m1[3], 10);
                    if (year < 100) year += 2000;
                    const d = new Date(year, month - 1, day);
                    return isNaN(d) ? null : d;
                }

                const m2 = s.match(/^(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})$/);
                if (m2) {
                    const year = parseInt(m2[1], 10);
                    const month = parseInt(m2[2], 10);
                    const day = parseInt(m2[3], 10);
                    const d = new Date(year, month - 1, day);
                    return isNaN(d) ? null : d;
                }

                return null;
            }

            const MS_PER_DAY = 1000 * 60 * 60 * 24;

            function startOfLocalDay(date) {
                if (!(date instanceof Date) || isNaN(date)) return null;
                return new Date(date.getFullYear(), date.getMonth(), date.getDate());
            }

            function daysBetweenLocal(laterDate, earlierDate) {
                const later = startOfLocalDay(laterDate);
                const earlier = startOfLocalDay(earlierDate);
                if (!later || !earlier) return null;
                return Math.floor((later.getTime() - earlier.getTime()) / MS_PER_DAY);
            }

            function getContractEndDate(p) {
                return parseProjectDate(getContractEndDateRaw(p));
            }

            function getTeamEndDate(p) {
                return parseProjectDate(getTeamEndDateRaw(p));
            }

            function getProjectStatusLabel(p) {
                return [p?.planning_status, p?.closure_status, p?.execution_status]
                    .map(v => String(v || '').trim())
                    .find(v => v) || 'ไม่ระบุ';
            }

            function getStatusYearChartLabel(p) {
                const statuses = [
                    'Project Planing (เริ่มต้น - submit IRB)',
                    'เก็บข้อมูล',
                    'วิเคราะห์ข้อมูล',
                    'เขียนรายงานและpublication',
                    'ปิดโปรเจค',
                    'ยกเลิกโปรเจค',
                    'ไม่ระบุสถานะ'
                ];
                const rawStatus = String(p?.planning_status || '').trim();
                return statuses.includes(rawStatus) ? rawStatus : 'ไม่ระบุสถานะ';
            }

            function buildOverdueRecord(p, referenceDate = new Date()) {
                const contractEndDate = getContractEndDate(p);
                if (!contractEndDate) return null;

                const teamEndDate = getTeamEndDate(p);
                const statusKey = projectLifecycleStatusKey(p);
                if (statusKey === 'cancelled') return null;

                const isCompleted = statusKey === 'completed';
                let overdueDays = null;
                let overdueSource = '';
                let sourceCategory = '';

                if (isCompleted) {
                    if (!teamEndDate) return null;
                    overdueDays = daysBetweenLocal(teamEndDate, contractEndDate);
                    overdueSource = 'team_end_date';
                    sourceCategory = 'closed_late';
                } else {
                    if (teamEndDate) {
                        overdueDays = daysBetweenLocal(teamEndDate, contractEndDate);
                        overdueSource = 'team_end_date';
                        sourceCategory = 'open_team_late';
                    } else {
                        overdueDays = daysBetweenLocal(referenceDate, contractEndDate);
                        overdueSource = 'today';
                        sourceCategory = 'open_system_late';
                    }
                }

                if (!Number.isFinite(overdueDays) || overdueDays <= 0) return null;

                const bucketKey = overdueDays <= 30 ? '1m' : (overdueDays <= 90 ? '3m' : '6m');
                const bucketLabel = bucketKey === '1m' ? '1 เดือน' : (bucketKey === '3m' ? '3 เดือน' : '6 เดือน+');

                return {
                    project: p,
                    contractEndDate,
                    teamEndDate,
                    overdueDays,
                    overdueSource,
                    sourceCategory,
                    statusLabel: getProjectStatusLabel(p),
                    contractEndYearCE: contractEndDate.getFullYear(),
                    bucketKey,
                    bucketLabel,
                };
            }

            function buildOverdueRecords(rows, referenceDate = new Date()) {
                return (Array.isArray(rows) ? rows : [])
                    .map(project => buildOverdueRecord(project, referenceDate))
                    .filter(Boolean);
            }

            function buildActiveOverdueRecords(rows, referenceDate = new Date()) {
                return buildOverdueRecords(rows, referenceDate)
                    .filter(record => record.sourceCategory === 'open_system_late' || record.sourceCategory === 'open_team_late');
            }

            function isActiveProjectForOverdue(p) {
                const statusKey = projectLifecycleStatusKey(p);
                return statusKey !== 'completed' && statusKey !== 'cancelled';
            }

            function classifyProjectForOverview(p, referenceDate = new Date()) {
                const statusKey = projectLifecycleStatusKey(p);
                const contractEndDate = getContractEndDate(p);
                const teamEndDate = getTeamEndDate(p);
                const overdueRecord = buildOverdueRecord(p, referenceDate);

                if (!contractEndDate) {
                    return { key: 'incomplete_data', label: 'ข้อมูลไม่ครบ' };
                }

                if (overdueRecord) {
                    return {
                        key: overdueRecord.sourceCategory,
                        label: overdueSourceLabel(overdueRecord),
                        record: overdueRecord
                    };
                }

                if (statusKey === 'cancelled') {
                    return { key: 'cancelled', label: 'ยกเลิกโปรเจค' };
                }

                if (statusKey === 'completed') {
                    if (!teamEndDate) {
                        return { key: 'incomplete_data', label: 'ข้อมูลไม่ครบ' };
                    }
                    return { key: 'completed_on_time', label: 'ปิดโปรเจคแล้วและไม่เกินสัญญา' };
                }

                if (teamEndDate) {
                    return { key: 'team_finished_not_closed', label: 'ทีมสิ้นสุดแล้วแต่ยังไม่ปิดโปรเจค' };
                }

                return { key: 'active_not_due', label: 'ยังไม่ครบกำหนดสัญญา' };
            }

            const OVERDUE_OVERVIEW_CATEGORY_DEFS = [
                { key: 'open_system_late', label: 'ครบกำหนดแต่ยังไม่ลงปิดโปรเจคในระบบ', color: '#dc2626', recordMode: true },
                { key: 'open_team_late', label: 'ทำงานเกินสัญญาและยังไม่ปิดโปรเจค', color: '#d97706', recordMode: true },
                { key: 'closed_late', label: 'ทำงานเกินสัญญาแต่ปิดโปรเจคแล้ว', color: '#7c3aed', recordMode: true },
                { key: 'active_not_due', label: 'ยังไม่ครบกำหนดสัญญา', color: '#0ea5e9' },
                { key: 'team_finished_not_closed', label: 'ทีมสิ้นสุดแล้วแต่ยังไม่ปิดโปรเจค', color: '#14b8a6' },
                { key: 'completed_on_time', label: 'ปิดโปรเจคแล้วและไม่เกินสัญญา', color: '#22c55e' },
                { key: 'cancelled', label: 'ยกเลิกโปรเจค', color: '#64748b' },
                { key: 'incomplete_data', label: 'ข้อมูลไม่ครบ', color: '#f59e0b' },
            ];

            function buildOverdueOverviewSummary(rows, referenceDate = new Date()) {
                const projectsList = Array.isArray(rows) ? rows : [];
                const groups = new Map(OVERDUE_OVERVIEW_CATEGORY_DEFS.map((def) => [def.key, {
                    ...def,
                    count: 0,
                    rows: [],
                    projects: [],
                }]));

                projectsList.forEach((project) => {
                    const classification = classifyProjectForOverview(project, referenceDate);
                    const group = groups.get(classification.key);
                    if (!group) return;
                    group.count += 1;
                    if (group.recordMode && classification.record) {
                        group.rows.push(classification.record);
                    } else {
                        group.projects.push(project);
                    }
                });

                return OVERDUE_OVERVIEW_CATEGORY_DEFS.map((def) => groups.get(def.key)).filter(Boolean);
            }

            function createDoughnutCalloutPlugin(id) {
                return {
                    id,
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                left,
                                right,
                                top,
                                bottom
                            }
                        } = chart;
                        const meta = chart.getDatasetMeta(0);
                        const ds = chart.data.datasets?.[0];
                        if (!meta || !ds) return;

                        const total = (Array.isArray(ds.data) ? ds.data : [])
                            .reduce((sum, v) => sum + (Number(v) || 0), 0) || 1;

                        ctx.save();
                        ctx.font = '700 11px "IBM Plex Sans Thai", sans-serif';
                        ctx.textBaseline = 'middle';
                        ctx.lineWidth = 1.2;

                        const leftItems = [];
                        const rightItems = [];

                        meta.data.forEach((arc, i) => {
                            const v = Number(ds.data[i] || 0);
                            if (!isFinite(v) || v <= 0) return;

                            const angle = (arc.startAngle + arc.endAngle) / 2;
                            const cos = Math.cos(angle);
                            const sin = Math.sin(angle);
                            const pct = ((v / total) * 100).toFixed(1);
                            const label = `${v} โครงการ (${pct}%)`;
                            const textWidth = ctx.measureText(label).width;

                            const item = {
                                arc,
                                cos,
                                sin,
                                label,
                                textWidth,
                                color: Array.isArray(ds.backgroundColor) ? ds.backgroundColor[i] : '#64748b',
                                sx: arc.x + cos * (arc.outerRadius - 1),
                                sy: arc.y + sin * (arc.outerRadius - 1),
                                mx: arc.x + cos * (arc.outerRadius + 12),
                                my: arc.y + sin * (arc.outerRadius + 12),
                            };

                            if (cos >= 0) rightItems.push(item);
                            else leftItems.push(item);
                        });

                        const resolveSide = (items, side) => {
                            const minY = top + 12;
                            const maxY = bottom - 12;
                            const gap = 16;
                            items.sort((a, b) => a.my - b.my);

                            items.forEach((item, index) => {
                                let y = Math.max(item.my, minY);
                                if (index > 0) {
                                    y = Math.max(y, items[index - 1].resolvedY + gap);
                                }
                                item.resolvedY = y;
                            });

                            for (let i = items.length - 1; i >= 0; i -= 1) {
                                const next = items[i + 1];
                                let y = Math.min(items[i].resolvedY, maxY);
                                if (next) {
                                    y = Math.min(y, next.resolvedY - gap);
                                }
                                items[i].resolvedY = y;
                            }

                            items.forEach((item) => {
                                let tx = item.mx + (side === 'right' ? 16 : -16);
                                if (side === 'right') {
                                    const maxX = right - item.textWidth - 4;
                                    if (tx > maxX) tx = maxX;
                                    if (tx < left + 4) tx = left + 4;
                                    item.textAlign = 'left';
                                    item.lineEndX = tx - 4;
                                } else {
                                    const minX = left + item.textWidth + 4;
                                    if (tx < minX) tx = minX;
                                    if (tx > right - 4) tx = right - 4;
                                    item.textAlign = 'right';
                                    item.lineEndX = tx + 4;
                                }
                                item.textX = tx;
                            });
                        };

                        resolveSide(leftItems, 'left');
                        resolveSide(rightItems, 'right');

                        [...leftItems, ...rightItems].forEach((item) => {
                            ctx.strokeStyle = item.color || '#64748b';
                            ctx.fillStyle = '#0f172a';
                            ctx.textAlign = item.textAlign;

                            ctx.beginPath();
                            ctx.moveTo(item.sx, item.sy);
                            ctx.lineTo(item.mx, item.resolvedY);
                            ctx.lineTo(item.lineEndX, item.resolvedY);
                            ctx.stroke();

                            ctx.fillText(item.label, item.textX, item.resolvedY);
                        });

                        ctx.restore();
                    }
                };
            }

            function createDoughnutValueLabelPlugin(id) {
                return {
                    id,
                    afterDatasetsDraw(chart) {
                        const meta = chart.getDatasetMeta(0);
                        const ds = chart.data.datasets?.[0];
                        const { ctx, chartArea } = chart;
                        if (!meta || !ds || !chartArea) return;

                        ctx.save();
                        ctx.font = '700 11px "IBM Plex Sans Thai", sans-serif';
                        ctx.fillStyle = '#0f172a';
                        ctx.strokeStyle = '#94a3b8';
                        ctx.lineWidth = 1.2;
                        ctx.textBaseline = 'middle';

                        meta.data.forEach((arc, index) => {
                            const value = Number(ds.data[index] || 0);
                            if (!isFinite(value) || value <= 0) return;

                            const angle = (arc.startAngle + arc.endAngle) / 2;
                            const sweep = Math.abs((arc.endAngle || 0) - (arc.startAngle || 0));
                            const cos = Math.cos(angle);
                            const sin = Math.sin(angle);
                            const label = String(value);
                            const textWidth = ctx.measureText(label).width;
                            const innerRadius = Number(arc.innerRadius || 0);
                            const outerRadius = Number(arc.outerRadius || 0);
                            const middleRadius = innerRadius + ((outerRadius - innerRadius) * 0.62);
                            const insideX = arc.x + cos * middleRadius;
                            const insideY = arc.y + sin * middleRadius;
                            const arcSpan = middleRadius * sweep;
                            const hasInsideRoom = arcSpan >= (textWidth + 10);
                            const fitsInside =
                                hasInsideRoom &&
                                insideX - (textWidth / 2) >= chartArea.left + 2 &&
                                insideX + (textWidth / 2) <= chartArea.right - 2 &&
                                insideY >= chartArea.top + 10 &&
                                insideY <= chartArea.bottom - 10;

                            if (fitsInside) {
                                ctx.textAlign = 'center';
                                ctx.fillText(label, insideX, insideY);
                                return;
                            }

                            const startX = arc.x + cos * (outerRadius - 2);
                            const startY = arc.y + sin * (outerRadius - 2);
                            const elbowX = arc.x + cos * (outerRadius + 14);
                            const elbowY = arc.y + sin * (outerRadius + 14);
                            const rightSide = cos >= 0;
                            const lineReach = Math.max(18, Math.min(28, textWidth + 10));
                            const endX = rightSide
                                ? Math.min(chartArea.right - textWidth - 6, elbowX + lineReach)
                                : Math.max(chartArea.left + textWidth + 6, elbowX - lineReach);
                            const textY = Math.min(chartArea.bottom - 12, Math.max(chartArea.top + 12, elbowY));
                            const textX = rightSide ? endX + 4 : endX - 4;

                            ctx.beginPath();
                            ctx.moveTo(startX, startY);
                            ctx.lineTo(elbowX, elbowY);
                            ctx.lineTo(endX, textY);
                            ctx.stroke();

                            ctx.textAlign = rightSide ? 'left' : 'right';
                            ctx.fillText(label, textX, textY);
                        });

                        ctx.restore();
                    }
                };
            }

            function overdueRecordYearLabel(record) {
                if (record?.contractEndYearCE) return yearLabelTH(record.contractEndYearCE);
                return 'ไม่ระบุ';
            }

            function overdueRecordEndDateLabel(record) {
                return formatDateTH(record?.contractEndDate, '-');
            }

            function overdueRecordTeamEndDateLabel(record) {
                return formatDateTH(record?.teamEndDate, '-');
            }

            function overdueSourceLabel(record) {
                if (record?.sourceCategory === 'closed_late') return 'ทำงานเกินสัญญาแต่ปิดโปรเจคแล้ว';
                if (record?.sourceCategory === 'open_team_late') return 'ทำงานเกินสัญญาและยังไม่ปิดโปรเจค';
                if (record?.sourceCategory === 'open_system_late') return 'ครบกำหนดแต่ยังไม่ลงปิดโปรเจคในระบบ';
                return '-';
            }

            function getRedZoneRecentYears(referenceDate = new Date()) {
                const currentYear = referenceDate.getFullYear();
                return [currentYear - 2, currentYear - 1, currentYear];
            }

            function applyOverdueRecordSort(items, mode) {
                const list = Array.isArray(items) ? items : [];
                const safe = (v) => String(v || '').trim();
                const num = (v) => Number.isFinite(Number(v)) ? Number(v) : 0;
                const getSource = (r) => getFundingSource(r?.project || {});
                const by = {
                    name_asc: (a, b) => safe(a?.project?.project_name_th).localeCompare(safe(b?.project?.project_name_th), 'th'),
                    name_desc: (a, b) => safe(b?.project?.project_name_th).localeCompare(safe(a?.project?.project_name_th), 'th'),
                    pi_asc: (a, b) => safe(a?.project?.pi_name).localeCompare(safe(b?.project?.pi_name), 'th'),
                    pi_desc: (a, b) => safe(b?.project?.pi_name).localeCompare(safe(a?.project?.pi_name), 'th'),
                    year_desc: (a, b) => num(b?.contractEndYearCE) - num(a?.contractEndYearCE),
                    year_asc: (a, b) => num(a?.contractEndYearCE) - num(b?.contractEndYearCE),
                    funding_asc: (a, b) => safe(getSource(a)).localeCompare(safe(getSource(b)), 'th'),
                    funding_desc: (a, b) => safe(getSource(b)).localeCompare(safe(getSource(a)), 'th'),
                    budget_desc: (a, b) => num(getBudget(b?.project || {})) - num(getBudget(a?.project || {})),
                    budget_asc: (a, b) => num(getBudget(a?.project || {})) - num(getBudget(b?.project || {})),
                };
                const sorter = by[mode] || by.year_desc;
                return [...list].sort(sorter);
            }

            const formatDateTH = (d, fallback = '-') =>
                d instanceof Date && !isNaN(d) ?
                d.toLocaleDateString('th-TH', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                }) :
                fallback;

            const norm = (v) => String(v ?? '').toLowerCase().trim();
            const cleanText = (v) => String(v ?? '').trim();
            const projectById = new Map(projects.map(p => [p.id, p]));
            const fteAvatarPalette = ['#0f766e', '#2563eb', '#7c3aed', '#ea580c', '#0891b2', '#65a30d', '#db2777', '#dc2626'];
            const fteAvatarCache = new Map();

            function stripPersonHonorifics(value) {
                let text = String(value ?? '').trim();
                const pattern = /^(?:นายแพทย์หญิง|นายแพทย์|แพทย์หญิง|แพทย์|ทันตแพทย์หญิง|ทันตแพทย์|เภสัชกรหญิง|เภสัชกร|ศ\.คลินิก|ศ\.นพ\.|ศ\.พญ\.|รศ\.นพ\.|รศ\.พญ\.|ผศ\.นพ\.|ผศ\.พญ\.|ศ\.|รศ\.|ผศ\.|อ\.|ดร\.?|ภกญ\.|ภญ\.|ภก\.|พญ\.|นพ\.|ทพ\.ญ\.|ทพ\.|นาย|นางสาว|นาง)\s*/u;
                while (text && pattern.test(text)) {
                    text = text.replace(pattern, '').trim();
                }
                return text.replace(/\s+/gu, ' ').trim();
            }

            function normalizePersonKey(value) {
                return stripPersonHonorifics(value).replace(/\s+/gu, ' ').trim().toLowerCase();
            }

            function buildPersonLookupKeys(value) {
                const raw = String(value ?? '').trim();
                const stripped = stripPersonHonorifics(raw);
                const tokens = normalizePersonKey(stripped).split(/\s+/u).filter(Boolean);
                const variants = [
                    raw,
                    stripped,
                    raw.replace(/\s+/gu, ''),
                    stripped.replace(/\s+/gu, ''),
                    tokens[0] || '',
                    tokens.length > 1 ? `${tokens[0]} ${tokens[tokens.length - 1]}` : '',
                    tokens.length > 1 ? `${tokens[0]}${tokens[tokens.length - 1]}` : ''
                ];

                return Array.from(new Set(
                    variants
                        .map((candidate) => String(candidate ?? '').replace(/\s+/gu, ' ').trim().toLowerCase())
                        .concat(
                            variants.map((candidate) => String(candidate ?? '').replace(/\s+/gu, '').trim().toLowerCase())
                        )
                        .filter(Boolean)
                ));
            }

            function hashString(value) {
                const text = normalizePersonKey(value);
                let hash = 0;
                for (const char of text) {
                    hash = ((hash << 5) - hash) + char.codePointAt(0);
                    hash |= 0;
                }
                return Math.abs(hash);
            }

            function fteAccentFor(name) {
                return fteAvatarPalette[hashString(name) % fteAvatarPalette.length];
            }

            function hexToRgba(hex, alpha = 1) {
                let raw = String(hex ?? '').replace('#', '').trim();
                if (raw.length === 3) raw = raw.split('').map((char) => char + char).join('');
                const color = Number.parseInt(raw, 16);
                if (!Number.isFinite(color)) return `rgba(15, 23, 42, ${alpha})`;
                const r = (color >> 16) & 255;
                const g = (color >> 8) & 255;
                const b = color & 255;
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            function personInitials(name) {
                const tokens = String(name ?? '').trim().split(/\s+/u).filter(Boolean);
                if (!tokens.length) return '?';
                if (tokens.length === 1) return Array.from(tokens[0]).slice(0, 2).join('');
                return [
                    Array.from(tokens[0])[0] || '',
                    Array.from(tokens[tokens.length - 1])[0] || ''
                ].join('');
            }

            function resolveFteAvatar(name, accentColor) {
                const key = normalizePersonKey(name);
                const cacheKey = `${key}|${accentColor}`;
                if (fteAvatarCache.has(cacheKey)) return fteAvatarCache.get(cacheKey);

                const entry = {
                    accentColor,
                    initials: personInitials(name),
                    photoUrl: buildPersonLookupKeys(name)
                        .map((candidate) => fteProfileUrls[candidate] || null)
                        .find(Boolean) || null,
                };

                fteAvatarCache.set(cacheKey, entry);
                return entry;
            }

            function getFteAvatarLayer(target) {
                const canvas = target?.canvas || target;
                if (!canvas || canvas.id !== 'chartFteSummary') return null;
                return document.getElementById('chartFteSummaryAvatarLayer');
            }

            function clearFteAvatarLayer(target) {
                const layer = getFteAvatarLayer(target);
                if (!layer) return;
                layer.innerHTML = '';
                delete layer.dataset.signature;
            }

            function createFteAvatarMarker(record) {
                const marker = document.createElement('div');
                marker.className = `fte-avatar-marker${record.photoUrl ? ' has-photo' : ' is-photo-missing'}`;
                marker.style.left = `${record.x}px`;
                marker.style.top = `${record.y}px`;
                marker.style.width = `${record.diameter}px`;
                marker.style.height = `${record.diameter}px`;
                marker.style.zIndex = String(Math.max(1, Math.round(record.radius)));
                marker.style.setProperty('--fte-accent', record.accent);

                if (record.photoUrl) {
                    const img = document.createElement('img');
                    img.alt = record.label;
                    img.src = record.photoUrl;
                    img.decoding = 'async';
                    img.loading = 'eager';
                    img.addEventListener('error', () => {
                        marker.classList.remove('has-photo');
                        marker.classList.add('is-photo-missing');
                    }, { once: true });
                    marker.appendChild(img);
                }

                const fallback = document.createElement('span');
                fallback.className = 'fte-avatar-marker__fallback';
                fallback.textContent = record.initials;
                fallback.style.fontSize = `${Math.max(11, record.radius * 0.8)}px`;
                marker.appendChild(fallback);

                return marker;
            }

            function scheduleFteAvatarLayerSync(chart) {
                if (!chart?.canvas || chart.canvas.id !== 'chartFteSummary') return;
                if (chart._fteAvatarSyncFrame) {
                    cancelAnimationFrame(chart._fteAvatarSyncFrame);
                }

                chart._fteAvatarSyncFrame = requestAnimationFrame(() => {
                    chart._fteAvatarSyncFrame = null;
                    syncFteAvatarLayer(chart);
                });
            }

            function syncFteAvatarLayer(chart) {
                const layer = getFteAvatarLayer(chart);
                const area = chart?.chartArea;
                if (!layer || !area) {
                    clearFteAvatarLayer(chart);
                    return;
                }

                const records = chart.data.datasets.map((dataset, datasetIndex) => {
                    const point = chart.getDatasetMeta(datasetIndex)?.data?.[0];
                    const raw = dataset?.data?.[0];
                    const radius = Number(raw?.r || point?.options?.radius || 0);
                    const x = Number(point?.x);
                    const y = Number(point?.y);

                    if (!Number.isFinite(radius) || !Number.isFinite(x) || !Number.isFinite(y)) {
                        return null;
                    }

                    const accent = dataset._fteAccent || fteAccentFor(dataset.label);
                    const avatar = resolveFteAvatar(dataset.label, accent);
                    return {
                        label: dataset.label,
                        x,
                        y,
                        radius,
                        diameter: Math.max(28, radius * 2),
                        accent,
                        initials: avatar.initials,
                        photoUrl: avatar.photoUrl,
                    };
                }).filter(Boolean);

                const signature = records.map((record) => [
                    record.label,
                    record.x.toFixed(1),
                    record.y.toFixed(1),
                    record.radius.toFixed(1),
                    record.photoUrl || ''
                ].join('|')).join('||');

                if (layer.dataset.signature === signature) return;

                layer.dataset.signature = signature;
                layer.innerHTML = '';
                records.forEach((record) => {
                    layer.appendChild(createFteAvatarMarker(record));
                });
            }

            function computeFteBubbleRadius(totalFte) {
                const value = Math.max(0, Number(totalFte) || 0);
                return Math.max(14, Math.min(34, 12 + Math.sqrt(value) * 8));
            }

            function buildFteSummaryDatasets(items) {
                const grouped = {};
                (items || []).forEach((item) => {
                    const name = item?.name || 'ไม่ระบุ';
                    grouped[name] = grouped[name] || { fte: 0, projectSet: new Set() };
                    grouped[name].fte += toNumber(item?.fte_value || item?.fte || 0);
                    if (item?.project_id !== null && item?.project_id !== undefined) {
                        grouped[name].projectSet.add(item.project_id);
                    }
                });

                return Object.entries(grouped)
                    .sort(([, left], [, right]) => right.fte - left.fte)
                    .map(([name, data]) => {
                        const accent = fteAccentFor(name);
                        return {
                            label: name,
                            clip: false,
                            borderWidth: 0,
                            borderColor: 'rgba(255,255,255,0)',
                            backgroundColor: 'rgba(255,255,255,0)',
                            hoverBorderColor: 'rgba(255,255,255,0)',
                            hoverBackgroundColor: 'rgba(255,255,255,0)',
                            _fteAccent: accent,
                            data: [{
                                x: data.projectSet.size,
                                y: data.fte,
                                r: computeFteBubbleRadius(data.fte)
                            }]
                        };
                    });
            }

            const fteAvatarBubblePlugin = {
                id: 'fteAvatarBubblePlugin',
                afterDatasetsDraw(chart) {
                    if (chart?.canvas?.id !== 'chartFteSummary') return;
                    scheduleFteAvatarLayerSync(chart);
                },
                resize(chart) {
                    scheduleFteAvatarLayerSync(chart);
                },
                afterDestroy(chart) {
                    if (chart?._fteAvatarSyncFrame) {
                        cancelAnimationFrame(chart._fteAvatarSyncFrame);
                        chart._fteAvatarSyncFrame = null;
                    }
                    clearFteAvatarLayer(chart);
                }
            };

            function fillDatalist(id, values, limit = 250) {
                const datalist = document.getElementById(id);
                if (!datalist) return;

                const escapeAttr = (value) => String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');

                const uniqueValues = Array.from(new Map(
                    values
                        .map(cleanText)
                        .filter(Boolean)
                        .map((value) => [value.toLowerCase(), value])
                ).values())
                    .sort((a, b) => a.localeCompare(b, 'th'))
                    .slice(0, limit);

                datalist.innerHTML = uniqueValues
                    .map((value) => `<option value="${escapeAttr(value)}"></option>`)
                    .join('');
            }

            function projectSearchIndex(p) {
                return [
                    p?.project_code,
                    p?.project_name_th,
                    p?.project_name_en,
                    p?.pi_name,
                    p?.pi_affiliation,
                    p?.theme,
                    p?.project_type,
                    p?.project_list,
                    p?.funding_type,
                    p?.funding_source,
                    p?.planning_status,
                    p?.execution_status,
                    p?.review_status,
                    p?.closure_status,
                    p?.start_year,
                ].map(norm).filter(Boolean).join(' ');
            }

            function initializeFilterSuggestions() {
                fillDatalist('filterPiSuggestions', projects.map((p) => p?.pi_name));
                fillDatalist('filterPiAffiliationSuggestions', projects.map((p) => p?.pi_affiliation));
                fillDatalist('filterNameSuggestions', projects.map((p) => p?.project_name_th));
                fillDatalist('tableSearchSuggestions', projects.flatMap((p) => ([
                    p?.project_code,
                    p?.project_name_th,
                    p?.project_name_en,
                    p?.pi_name,
                    p?.theme,
                    p?.project_type,
                    p?.project_list,
                    p?.funding_source,
                    p?.planning_status,
                ])));
            }

            initializeFilterSuggestions();

            const dashboardFilterFields = [
                { id: 'filterYear', label: 'ปีเริ่มต้น' },
                { id: 'filterTheme', label: 'Theme' },
                { id: 'filterFunding', label: 'ประเภททุน' },
                { id: 'filterType', label: 'ประเภทโครงการ' },
                { id: 'filterStatus', label: 'สถานะโครงการ' },
                { id: 'filterSource', label: 'แหล่งทุน' },
                { id: 'filterPi', label: 'PI โครงการ' },
                { id: 'filterPiAffiliation', label: 'สังกัดผู้วิจัยหลัก' },
                { id: 'filterName', label: 'ชื่อโครงการ' },
                { id: 'tableSearch', label: 'ค้นหาในตาราง' },
            ];

            function escapeHtmlText(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function getActiveFilterEntries() {
                return dashboardFilterFields
                    .map(({ id, label }) => {
                        const value = cleanText(document.getElementById(id)?.value);
                        return value ? { id, label, value } : null;
                    })
                    .filter(Boolean);
            }

            function renderFilterPills(container, entries, emptyText) {
                if (!container) return;

                if (!entries.length) {
                    container.innerHTML = `<span class="dashboard-filter-empty">${escapeHtmlText(emptyText)}</span>`;
                    return;
                }

                container.innerHTML = entries.map((entry) => `
                    <span class="dashboard-filter-pill">
                        <span class="dashboard-filter-pill__label">${escapeHtmlText(entry.label)}</span>
                        <span>${escapeHtmlText(entry.value)}</span>
                    </span>
                `).join('');
            }

            function renderDashboardFilterState(totalCount = null) {
                const activeFilters = getActiveFilterEntries();
                const allCount = Array.isArray(projects) ? projects.length : 0;
                const filteredCount = typeof totalCount === 'number'
                    ? totalCount
                    : (typeof getFilteredProjects === 'function' ? getFilteredProjects().length : allCount);
                const isActive = activeFilters.length > 0;
                const stateText = isActive ? `กำลังใช้ ${activeFilters.length} ฟิลเตอร์` : 'ยังไม่ได้ใช้ฟิลเตอร์';
                const detailText = isActive
                    ? `กรองแล้ว ${filteredCount.toLocaleString('th-TH')} / ${allCount.toLocaleString('th-TH')} โครงการ`
                    : `กำลังแสดงข้อมูลทั้งหมด ${allCount.toLocaleString('th-TH')} โครงการ`;
                const fabBadgeEl = document.getElementById('filterFabBadge');

                if (fabBadgeEl) {
                    if (isActive) {
                        fabBadgeEl.textContent = String(activeFilters.length);
                        fabBadgeEl.classList.add('is-show');
                        fabBadgeEl.setAttribute('aria-hidden', 'false');
                    } else {
                        fabBadgeEl.textContent = '';
                        fabBadgeEl.classList.remove('is-show');
                        fabBadgeEl.setAttribute('aria-hidden', 'true');
                    }
                }

                const stateEl = document.getElementById('dashboardFilterState');
                if (stateEl) {
                    stateEl.textContent = stateText;
                    stateEl.className = `dashboard-status-pill ${isActive ? 'is-active' : 'is-idle'}`;
                }

                const detailEl = document.getElementById('dashboardFilterDetail');
                if (detailEl) detailEl.textContent = detailText;

                renderFilterPills(
                    document.getElementById('dashboardFilterList'),
                    activeFilters,
                    'กำลังแสดงข้อมูลทั้งหมด'
                );

                const exportStateEl = document.getElementById('exportPopupState');
                if (exportStateEl) {
                    exportStateEl.textContent = stateText;
                    exportStateEl.className = `dashboard-status-pill ${isActive ? 'is-active' : 'is-idle'}`;
                }

                const exportCountEl = document.getElementById('exportPopupCount');
                if (exportCountEl) exportCountEl.textContent = `${filteredCount.toLocaleString('th-TH')} โครงการ`;

                const exportSummaryEl = document.getElementById('exportPopupSummary');
                if (exportSummaryEl) {
                    exportSummaryEl.textContent = isActive
                        ? `จะดาวน์โหลด ${filteredCount.toLocaleString('th-TH')} จาก ${allCount.toLocaleString('th-TH')} โครงการตามฟิลเตอร์ปัจจุบัน`
                        : `จะดาวน์โหลดข้อมูลทั้งหมด ${allCount.toLocaleString('th-TH')} โครงการ`;
                }

                renderFilterPills(
                    document.getElementById('exportPopupFilterList'),
                    activeFilters,
                    'ดาวน์โหลดข้อมูลทั้งหมด'
                );
            }

            if (window.Chart) {
                // บังคับพฤติกรรมเดียวกันทุกกราฟตอน responsive
                Chart.defaults.responsive = true;
                Chart.defaults.maintainAspectRatio = false;
                Chart.defaults.layout = Chart.defaults.layout || {};
                Chart.defaults.layout.padding = {
                    top: 20,
                    right: 12,
                    bottom: 8,
                    left: 8
                };
                Chart.defaults.scales = Chart.defaults.scales || {};
                Chart.defaults.scales.linear = Chart.defaults.scales.linear || {};
                Chart.defaults.scales.linear.grace = '16%';
                Chart.defaults.scales.logarithmic = Chart.defaults.scales.logarithmic || {};
                Chart.defaults.scales.logarithmic.grace = '16%';
            }

            function ensureChartBaseHeights(root = document) {
                root.querySelectorAll('canvas[id^="chart"]').forEach((canvas) => {
                    if (canvas.dataset.baseHeight) return;
                    const declared = Number.parseInt(canvas.getAttribute('height') || '', 10);
                    const fallback = 260;
                    const base = Number.isFinite(declared) && declared > 0 ? declared : fallback;
                    canvas.dataset.baseHeight = String(base);
                });
            }

            function chartHeightScale(vw) {
                if (vw <= 430) return 0.7;
                if (vw <= 640) return 0.64;
                if (vw <= 1024) return 0.72;
                if (vw <= 1366) return 0.8;
                if (vw <= 1536) return 0.9;
                return 1;
            }

            function isPhoneViewport() {
                return (window.innerWidth || document.documentElement.clientWidth || 0) <= 640;
            }

            function isSmallPhoneViewport() {
                return (window.innerWidth || document.documentElement.clientWidth || 0) <= 430;
            }

            function truncateMobileText(value, limit = 12) {
                const text = String(value ?? '').trim();
                if (!text || text.length <= limit) return text;
                return `${text.slice(0, Math.max(1, limit - 1))}…`;
            }

            function buildMobileLegend(limit = 14) {
                return {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: isSmallPhoneViewport() ? 8 : 10,
                        boxHeight: isSmallPhoneViewport() ? 8 : 10,
                        padding: isSmallPhoneViewport() ? 10 : 14,
                        font: {
                            size: isSmallPhoneViewport() ? 10 : 11
                        },
                        generateLabels(chart) {
                            return Chart.defaults.plugins.legend.labels.generateLabels(chart).map((item) => ({
                                ...item,
                                text: truncateMobileText(item.text, isSmallPhoneViewport() ? limit : limit + 4),
                            }));
                        }
                    }
                };
            }

            function buildMobileCategoryTicks(limit = 8, maxTicks = 4) {
                return {
                    autoSkip: true,
                    maxTicksLimit: isPhoneViewport() ? maxTicks : 8,
                    minRotation: 0,
                    maxRotation: 0,
                    padding: isSmallPhoneViewport() ? 6 : 8,
                    callback(value) {
                        const raw = typeof this.getLabelForValue === 'function' ? this.getLabelForValue(value) : value;
                        return truncateMobileText(raw, isSmallPhoneViewport() ? limit : limit + 4);
                    }
                };
            }

            function buildMobileHorizontalTicks(labels, limit = 16) {
                return {
                    autoSkip: false,
                    padding: isSmallPhoneViewport() ? 6 : 8,
                    callback(value, index) {
                        const raw = labels?.[index] ?? (typeof this.getLabelForValue === 'function' ? this.getLabelForValue(value) : value);
                        return truncateMobileText(raw, isSmallPhoneViewport() ? limit : limit + 6);
                    }
                };
            }

            function renderInlineLegend(containerId, items) {
                const container = document.getElementById(containerId);
                if (!container) return;
                const list = Array.isArray(items) ? items.filter(Boolean) : [];
                if (!list.length) {
                    container.innerHTML = '';
                    return;
                }

                const escape = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

                container.innerHTML = list.map((item) => `
                    <div class="dashboard-inline-legend__item">
                        <span class="dashboard-inline-legend__swatch" style="background:${item.color || '#94a3b8'}"></span>
                        <span>${escape(item.label || '')}</span>
                    </div>
                `).join('');
            }

            function isDenseChart(canvasId) {
                const denseIds = new Set([
                    'chartProjectsPerYear',
                    'chartYearlyNewVsTotal',
                    'chartExternalBySourceYear',
                    'chartInternalBySourceYear',
                    'chartOutputStacked',
                    'chartStatusYear',
                    'chartCollabBreakdown',
                    'chartPIsPerUnit',
                    'chartAuthorRolePerYear',
                ]);
                return denseIds.has(String(canvasId || ''));
            }

            // คุมความสูงแบบสัดส่วนตามค่าเดิมของแต่ละกราฟ (ไม่สูงเพี้ยน และยังคงสัดส่วนเดิม)
            function applyChartCanvasSizing(root = document) {
                ensureChartBaseHeights(root);
                const vw = window.innerWidth || document.documentElement.clientWidth || 1366;
                const scale = chartHeightScale(vw);

                root.querySelectorAll('canvas[id^="chart"]').forEach((canvas) => {
                    const base = Number.parseInt(canvas.dataset.baseHeight || '', 10) || 260;
                    const boost = isDenseChart(canvas.id)
                        ? (vw <= 430 ? 1.26 : (vw <= 1366 ? 1.15 : 1.05))
                        : 1;
                    const minHeight = vw <= 430 ? 220 : 170;
                    const maxHeight = vw <= 430 ? 400 : 460;
                    const target = Math.round(Math.min(maxHeight, Math.max(minHeight, base * scale * boost)));
                    const h = `${target}px`;
                    canvas.style.setProperty('--chart-height', h);
                    canvas.style.width = '100%';
                    canvas.style.maxWidth = '100%';
                });
            }

            window.applyChartCanvasSizing = applyChartCanvasSizing;
            applyChartCanvasSizing();
            setTimeout(() => applyChartCanvasSizing(), 0);
            window.addEventListener('load', () => applyChartCanvasSizing());

            function forceDashboardChartResize(root = document) {
                root.querySelectorAll('canvas[id^="chart"]').forEach((canvas) => {
                    const chart = typeof Chart !== 'undefined' ? Chart.getChart(canvas) : null;
                    if (chart) chart.resize();
                });
            }

            let dashboardLayoutBucket = '';
            function getDashboardLayoutBucket() {
                const vw = window.innerWidth || document.documentElement.clientWidth || 1366;
                if (vw <= 640) return 'phone';
                if (vw <= 900) return 'compact';
                if (vw <= 1280) return 'single';
                if (vw <= 1535) return 'wide';
                return 'xl';
            }

            function syncDashboardResponsiveLayout() {
                const nextBucket = getDashboardLayoutBucket();
                const bucketChanged = dashboardLayoutBucket && dashboardLayoutBucket !== nextBucket;
                dashboardLayoutBucket = nextBucket;
                applyChartCanvasSizing();
                forceDashboardChartResize();
                if (bucketChanged) {
                    requestAnimationFrame(() => {
                        applyChartCanvasSizing();
                        forceDashboardChartResize();
                    });
                }
            }

            let chartCanvasResizeTimer = null;
            window.addEventListener('resize', () => {
                clearTimeout(chartCanvasResizeTimer);
                chartCanvasResizeTimer = setTimeout(syncDashboardResponsiveLayout, 120);
            });
            window.addEventListener('orientationchange', () => {
                setTimeout(syncDashboardResponsiveLayout, 180);
            });
            window.addEventListener('load', () => {
                dashboardLayoutBucket = getDashboardLayoutBucket();
                syncDashboardResponsiveLayout();
            });

            function uniqueProjects(list) {
                const seen = new Map();
                list.forEach(p => {
                    if (p?.id != null && !seen.has(p.id)) seen.set(p.id, p);
                });
                return Array.from(seen.values());
            }

            function endYearLabel(p) {
                const endDate = getContractEndDate(p);
                if (endDate) return yearLabelTH(endDate.getFullYear());
                const start = yearCE(p.start_year);
                if (start) return yearLabelTH(start);
                return 'ไม่ระบุ';
            }

            function truncateText(value, max = 36) {
                const text = String(value || '').trim();
                if (!text) return '';
                if (text.length <= max) return text;
                return `${text.slice(0, max - 1)}…`;
            }

            function coResearchUnitLabel(c) {
                const raw = c.unit_status || c.affiliation || c.unit || c.department || 'ไม่ระบุ';
                const text = String(raw || '').trim();
                return text || 'ไม่ระบุ';
            }

            function coResearchKey(c) {
                const name = String(c?.name || '').trim();
                const unit = coResearchUnitLabel(c);
                return `${name}||${unit}`;
            }

            function uniqueCoResearch(list) {
                const map = new Map();
                list.forEach(c => {
                    const key = coResearchKey(c);
                    if (!key || key === '||') return;
                    if (!map.has(key)) map.set(key, c);
                });
                return Array.from(map.values());
            }

            function getCoResearchList(p) {
                const list = p.co_researches || p.coResearches || p.co_research || [];
                return Array.isArray(list) ? list : [];
            }

            function coResearchMembers(p) {
                const list = uniqueCoResearch(getCoResearchList(p));
                return list.map(c => {
                    const name = c.name || '-';
                    const unitLabel = coResearchUnitLabel(c);
                    const unit = c.affiliation || unitLabel || '';
                    return {
                        name,
                        unit,
                        unitLabel
                    };
                });
            }

            function applyProjectSort(items, mode) {
                const safe = (v) => (v ?? '').toString();
                const num = (v) => Number(v || 0);
                const getSource = (p) => getFundingSource(p) || 'ไม่ระบุ';
                const by = {
                    name_asc: (a, b) => safe(a.project_name_th).localeCompare(safe(b.project_name_th), 'th'),
                    name_desc: (a, b) => safe(b.project_name_th).localeCompare(safe(a.project_name_th), 'th'),
                    pi_asc: (a, b) => safe(a.pi_name).localeCompare(safe(b.pi_name), 'th'),
                    pi_desc: (a, b) => safe(b.pi_name).localeCompare(safe(a.pi_name), 'th'),
                    year_desc: (a, b) => num(b.start_year) - num(a.start_year),
                    year_asc: (a, b) => num(a.start_year) - num(b.start_year),
                    funding_asc: (a, b) => safe(getSource(a)).localeCompare(safe(getSource(b)), 'th'),
                    funding_desc: (a, b) => safe(getSource(b)).localeCompare(safe(getSource(a)), 'th'),
                    budget_desc: (a, b) => num(getBudget(b)) - num(getBudget(a)),
                    budget_asc: (a, b) => num(getBudget(a)) - num(getBudget(b)),
                };
                const sorter = by[mode] || by.name_asc;
                return [...items].sort(sorter);
            }

            function renderProjectModal(state, mode) {
                const {
                    title,
                    rows,
                    includeFunding,
                    modalType
                } = state;
                if (!rows.length) {
                    showModal(title, `<div class="text-center text-gray-500">ไม่มีข้อมูลโครงการ</div>`);
                    return;
                }

                if (modalType === 'redzone' || modalType === 'overdue') {
                    const pageSize = state.pageSize || 10;
                    const yearOptions = Array.from(new Set(rows.map(overdueRecordYearLabel))).sort((a, b) => {
                        const yearA = yearCE(a);
                        const yearB = yearCE(b);
                        if (Number.isFinite(yearA) && Number.isFinite(yearB)) return yearA - yearB;
                        return String(a).localeCompare(String(b), 'th');
                    });
                    const activeYear = state.yearFilter || '';
                    const diffHeader = 'เกินกำหนด (วัน)';

                    const filtered = activeYear ? rows.filter(record => overdueRecordYearLabel(record) === activeYear) : rows.slice();
                    const sorted = applyOverdueRecordSort(filtered, mode || 'year_desc');
                    const totalPages = Math.max(Math.ceil(sorted.length / pageSize), 1);
                    state.page = Math.min(Math.max(state.page || 1, 1), totalPages);
                    const start = (state.page - 1) * pageSize;
                    const pageRows = sorted.slice(start, start + pageSize);

                    let html = `
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                            <div class="text-xs text-slate-500">ทั้งหมด ${sorted.length} รายการ</div>
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-gray-500">ปี:
                                    <select id="modalYearFilter" class="ml-1 border rounded px-2 py-1 text-xs">
                                        <option value="">ทั้งหมด</option>
                                        ${yearOptions.map(y => `<option value="${y}" ${activeYear === y ? 'selected' : ''}>${y}</option>`).join('')}
                                    </select>
                                </label>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="lux-modal-table table-auto w-full border text-sm">
                                <thead>
                                    <tr class="bg-gray-100 text-xs text-gray-700">
                                        <th class="p-2 text-center">#</th>
                                        <th class="p-2 text-left">ชื่อโครงการ</th>
                                        <th class="p-2 text-center">วันที่สิ้นสุดสัญญา (พ.ศ.)</th>
                                        <th class="p-2 text-center">วันที่สิ้นสุดการทำงานทีม</th>
                                        <th class="p-2 text-center">${diffHeader}</th>
                                        <th class="p-2 text-center">สถานะโครงการ</th>
                                        <th class="p-2 text-center">PI</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    if (!pageRows.length) {
                        html += `<tr class="border-t"><td colspan="7" class="p-3 text-center text-gray-500">ไม่มีข้อมูล</td></tr>`;
                    } else {
                        pageRows.forEach((record, idx) => {
                            const project = record.project || {};
                            html += `
                                <tr class="border-t">
                                    <td class="p-2 text-center" data-label="#">${start + idx + 1}</td>
                                    <td class="p-2 text-left text-blue-700 hover:underline" data-label="ชื่อโครงการ">
                                        <a href="/rm/projects/${project.id}" target="_blank" rel="noopener">${escapeHtmlText(project.project_name_th || '-')}</a>
                                        <div class="mt-1 text-[11px] font-normal text-slate-500">${escapeHtmlText(overdueSourceLabel(record))}</div>
                                    </td>
                                    <td class="p-2 text-center" data-label="วันที่สิ้นสุดสัญญา (พ.ศ.)">${escapeHtmlText(overdueRecordEndDateLabel(record))}</td>
                                    <td class="p-2 text-center" data-label="วันที่สิ้นสุดการทำงานทีม">${escapeHtmlText(overdueRecordTeamEndDateLabel(record))}</td>
                                    <td class="p-2 text-center text-red-600 font-semibold" data-label="${diffHeader}">
                                        <div>${record.overdueDays}</div>
                                    </td>
                                    <td class="p-2 text-center" data-label="สถานะโครงการ">${escapeHtmlText(record.statusLabel || '-')}</td>
                                    <td class="p-2 text-center" data-label="PI">${escapeHtmlText(project.pi_name || '-')}</td>
                                </tr>
                            `;
                        });
                    }

                    html += `
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between mt-3 text-xs text-gray-600">
                            <button id="modalPrev" class="border rounded px-3 py-1 hover:bg-gray-50">ก่อนหน้า</button>
                            <div id="modalPagination">หน้า ${state.page} / ${totalPages}</div>
                            <button id="modalNext" class="border rounded px-3 py-1 hover:bg-gray-50">ถัดไป</button>
                        </div>
                    `;

                    showModal(title, html);

                    document.getElementById('modalYearFilter')?.addEventListener('change', (e) => {
                        state.yearFilter = e.target.value;
                        state.page = 1;
                        renderProjectModal(state, mode);
                    });
                    document.getElementById('modalPrev')?.addEventListener('click', () => {
                        state.page = Math.max((state.page || 1) - 1, 1);
                        renderProjectModal(state, mode);
                    });
                    document.getElementById('modalNext')?.addEventListener('click', () => {
                        state.page = (state.page || 1) + 1;
                        renderProjectModal(state, mode);
                    });
                    return;
                }

                if (modalType === 'incomplete_data') {
                    const pageSize = state.pageSize || 10;
                    const sorted = applyProjectSort(rows, mode || 'name_asc');
                    const totalPages = Math.max(Math.ceil(sorted.length / pageSize), 1);
                    state.page = Math.min(Math.max(state.page || 1, 1), totalPages);
                    const start = (state.page - 1) * pageSize;
                    const pageRows = sorted.slice(start, start + pageSize);

                    let html = `
                        <div class="mb-3 text-xs text-slate-500">ทั้งหมด ${sorted.length} รายการ</div>
                        <div class="overflow-x-auto">
                            <table class="lux-modal-table table-auto w-full border text-sm">
                                <thead>
                                    <tr class="bg-gray-100 text-xs text-gray-700">
                                        <th class="p-2 text-center">#</th>
                                        <th class="p-2 text-left">ชื่อโครงการ</th>
                                        <th class="p-2 text-center">ปีโปรเจค</th>
                                        <th class="p-2 text-center">วันที่สิ้นสุดสัญญา</th>
                                        <th class="p-2 text-center">วันที่สิ้นสุดการทำงานทีม</th>
                                        <th class="p-2 text-center">สถานะโปรเจค</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    if (!pageRows.length) {
                        html += `<tr class="border-t"><td colspan="6" class="p-3 text-center text-gray-500">ไม่มีข้อมูล</td></tr>`;
                    } else {
                        pageRows.forEach((project, idx) => {
                            html += `
                                <tr class="border-t">
                                    <td class="p-2 text-center" data-label="#">${start + idx + 1}</td>
                                    <td class="p-2 text-left text-blue-700 hover:underline" data-label="ชื่อโครงการ">
                                        <a href="/rm/projects/${project.id}" target="_blank" rel="noopener">${escapeHtmlText(project.project_name_th || '-')}</a>
                                    </td>
                                    <td class="p-2 text-center" data-label="ปีโปรเจค">${escapeHtmlText(project.start_year || '-')}</td>
                                    <td class="p-2 text-center" data-label="วันที่สิ้นสุดสัญญา">${escapeHtmlText(formatDateTH(getContractEndDate(project), '-'))}</td>
                                    <td class="p-2 text-center" data-label="วันที่สิ้นสุดการทำงานทีม">${escapeHtmlText(formatDateTH(getTeamEndDate(project), '-'))}</td>
                                    <td class="p-2 text-center" data-label="สถานะโปรเจค">${escapeHtmlText(getProjectStatusLabel(project))}</td>
                                </tr>
                            `;
                        });
                    }

                    html += `
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between mt-3 text-xs text-gray-600">
                            <button id="modalPrev" class="border rounded px-3 py-1 hover:bg-gray-50">ก่อนหน้า</button>
                            <div id="modalPagination">หน้า ${state.page} / ${totalPages}</div>
                            <button id="modalNext" class="border rounded px-3 py-1 hover:bg-gray-50">ถัดไป</button>
                        </div>
                    `;

                    showModal(title, html);
                    document.getElementById('modalPrev')?.addEventListener('click', () => {
                        state.page = Math.max((state.page || 1) - 1, 1);
                        renderProjectModal(state, mode);
                    });
                    document.getElementById('modalNext')?.addEventListener('click', () => {
                        state.page = (state.page || 1) + 1;
                        renderProjectModal(state, mode);
                    });
                    return;
                }

                if (modalType === 'coresearch') {
                    const sorted = applyProjectSort(rows, mode || 'name_asc');
                    const filterUnit = state.unit || '';
                    const allMembers = [];
                    sorted.forEach(p => {
                        coResearchMembers(p).filter(m => !filterUnit || m.unitLabel === filterUnit).forEach(m => {
                            if (m.name && m.name !== '-') allMembers.push(m);
                        });
                    });
                    const memberKey = (m) => `${m.name}||${m.unit || ''}`;
                    const uniqueMembers = Array.from(new Map(allMembers.map(m => [memberKey(m), m])).values())
                        .sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''), 'th'));
                    const totalMembers = allMembers.length;

                    let html = `
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                            <div class="text-xs text-slate-500">ทั้งหมด ${sorted.length} โครงการ • Co-Research ${totalMembers} คน (ไม่ซ้ำ ${uniqueMembers.length})</div>
                            <button id="toggleAllMembers" class="border rounded px-3 py-1 text-xs hover:bg-gray-50">ดูรายชื่อทั้งหมด</button>
                        </div>
                        <div id="allMembersPanel" class="hidden mb-3 border rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                            ${uniqueMembers.length ? uniqueMembers.map((m, i) => `${i + 1}. ${truncateText(m.name)}${m.unit ? ` (${truncateText(m.unit)})` : ''}`).join('<br>') : 'ไม่มีข้อมูลรายชื่อ'}
                        </div>
                        <div class="overflow-x-auto">
                            <table class="lux-modal-table table-auto w-full border text-sm">
                                <thead>
                                    <tr class="bg-gray-100 text-xs text-gray-700">
                                        <th class="p-2 text-center">#</th>
                                        <th class="p-2 text-left">ชื่อโครงการ</th>
                                        <th class="p-2 text-center">PI</th>
                                        <th class="p-2 text-center">ปี</th>
                                        <th class="p-2 text-center">จำนวน Co-Research</th>
                                        <th class="p-2 text-center">รายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    if (!sorted.length) {
                        html += `<tr class="border-t"><td colspan="6" class="p-3 text-center text-gray-500">ไม่มีข้อมูล</td></tr>`;
                    } else {
                        sorted.forEach((p, idx) => {
                            const members = coResearchMembers(p)
                                .filter(m => !filterUnit || m.unitLabel === filterUnit)
                                .sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''), 'th'));
                            const memberList = members.length ?
                                members.map((m, i) => `${i + 1}. ${truncateText(m.name)}${m.unit ? ` (${truncateText(m.unit)})` : ''}`)
                                .join('<br>') :
                                'ไม่มีข้อมูล';
                            const rowId = `coresearchRow_${idx}`;
                            html += `
                                <tr class="border-t">
                                    <td class="p-2 text-center" data-label="#">${idx + 1}</td>
                                    <td class="p-2 text-left text-blue-700 hover:underline" data-label="ชื่อโครงการ">
                                        <a href="/rm/projects/${p.id}" target="_blank" rel="noopener">${p.project_name_th || '-'}</a>
                                    </td>
                                    <td class="p-2 text-center" data-label="PI">${p.pi_name || '-'}</td>
                                    <td class="p-2 text-center" data-label="ปี">${p.start_year || '-'}</td>
                                    <td class="p-2 text-center" data-label="จำนวน Co-Research">${members.length}</td>
                                    <td class="p-2 text-center" data-label="รายละเอียด">
                                        <button class="toggleMembers border rounded px-2 py-1 text-xs hover:bg-gray-50" data-target="${rowId}">ดูรายละเอียด</button>
                                    </td>
                                </tr>
                                <tr id="${rowId}" class="hidden border-t bg-slate-50">
                                    <td colspan="6" class="p-2 text-xs text-slate-600">
                                        ${memberList}
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    showModal(title, html);
                    document.getElementById('toggleAllMembers')?.addEventListener('click', () => {
                        document.getElementById('allMembersPanel')?.classList.toggle('hidden');
                    });
                    document.querySelectorAll('.toggleMembers').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const target = e.currentTarget?.getAttribute('data-target');
                            const row = target ? document.getElementById(target) : null;
                            row?.classList.toggle('hidden');
                        });
                    });
                    return;
                }

                const sorted = applyProjectSort(rows, mode || 'name_asc');
                let html =
                    `<table class="lux-modal-table table-auto w-full border text-sm"><thead><tr><th class="p-2 text-center">#</th><th class="p-2 text-left">ชื่อโครงการ</th><th class="p-2 text-center">PI</th><th class="p-2 text-center">ปี</th>`;
                if (includeFunding) {
                    html += `<th class="p-2 text-center">แหล่งทุน</th><th class="p-2 text-right">งบที่ได้รับ</th>`;
                }
                html += `</tr></thead><tbody>`;
                sorted.forEach((p, idx) => {
                    html += `<tr class="border-t"><td class="p-2 text-center" data-label="#">${idx + 1}</td><td class="p-2 text-blue-700 hover:underline" data-label="ชื่อโครงการ"><a href="/rm/projects/${p.id}" target="_blank" rel="noopener">${p.project_name_th || '-'}</a></td><td class="p-2 text-center" data-label="PI">${p.pi_name || '-'}</td><td class="p-2 text-center" data-label="ปี">${p.start_year || '-'}</td>`;
                    if (includeFunding) {
                        const source = getFundingSource(p) || 'ไม่ระบุ';
                        const budget = Number(getBudget(p) || 0);
                        html += `<td class="p-2 text-center" data-label="แหล่งทุน">${source}</td><td class="p-2 text-right" data-label="งบที่ได้รับ">${budget.toLocaleString('th-TH')}</td>`;
                    }
                    html += `</tr>`;
                });
                html += `</tbody></table>`;
                showModal(title, html);
            }

            function showProjectList(title, list, options = {}) {
                const preserveRows = options.preserveRows === true;
                const rows = preserveRows ? (Array.isArray(list) ? list.slice() : []) : uniqueProjects(list);
                const includeFunding = options.includeFunding === true;
                window.chartModalState = {
                    title,
                    rows,
                    includeFunding,
                    modalType: options.modalType || null,
                    unit: options.unit || '',
                    windowMonths: options.windowMonths || null,
                    yearFilter: options.yearFilter || '',
                    page: 1,
                    pageSize: 10
                };
                const sortSelect = document.getElementById('chartModalSort');
                renderProjectModal(window.chartModalState, sortSelect?.value);
            }

            document.getElementById('chartModalSort')?.addEventListener('change', (e) => {
                if (!window.chartModalState) return;
                renderProjectModal(window.chartModalState, e.target.value);
            });

            function normalizeOutputYear(raw) {
                if (!raw) return 'ไม่ระบุ';
                const str = String(raw).trim();
                if (!str) return 'ไม่ระบุ';
                const match = str.match(/(\d{4})/);
                if (match) {
                    let y = parseInt(match[1], 10);
                    if (y > 2400) y -= 543;
                    if (y >= 1900 && y <= 2600) return String(y);
                }
                const d = new Date(str);
                if (!isNaN(d)) return String(d.getFullYear());
                return 'ไม่ระบุ';
            }

            function outputYearKey(o) {
                const direct = normalizeOutputYear(o?.year ?? o?.export_year ?? o?.date ?? o?.created_at);
                if (direct !== 'ไม่ระบุ') return direct;
                const p = projectById.get(o?.project_id);
                const pYear = yearCE(p?.start_year);
                if (pYear) return String(pYear);
                return 'ไม่ระบุ';
            }

            function outputYearLabelForKey(key) {
                const text = String(key ?? '').trim();
                if (/^\d+$/.test(text)) return yearLabelTH(Number(text));
                if (text === 'ไม่ระบุ') return 'ไม่ระบุปี';
                return text || 'ไม่ระบุปี';
            }

            function normalizeYear(raw) {
                if (!raw) return null;
                const m = String(raw).match(/(\d{4})/);
                if (!m) return null;
                let y = parseInt(m[1], 10);
                if (y > 2400) y -= 543;
                return String(y);
            }

            const toBE = ce => (ce && /^\d{4}$/.test(String(ce))) ? String(Number(ce) + 543) : ce;

            function yearCE(raw) {
                const m = String(raw ?? '').match(/\d{4}/);
                if (!m) return null;
                let y = parseInt(m[0], 10);
                if (y > 2400) y -= 543;
                return y;
            }
            const yearLabelTH = ce => ce ? String(ce + 543) : '-';

            function irbExpiryBucketKey(date) {
                if (!(date instanceof Date) || isNaN(date)) return null;
                const month = String(date.getMonth() + 1).padStart(2, '0');
                return `${date.getFullYear()}-${month}`;
            }

            function irbExpiryBucketLabel(bucketKey) {
                const match = String(bucketKey ?? '').match(/^(\d{4})-(\d{2})$/);
                if (!match) return String(bucketKey ?? '');
                return `${Number(match[2])}/${toBE(match[1])}`;
            }

            function parseYearCEFromAny(raw) {
                if (raw == null || raw === '') return null;
                const y = yearCE(raw);
                if (Number.isFinite(y)) return y;
                const d = new Date(String(raw).trim());
                if (!isNaN(d)) return d.getFullYear();
                return null;
            }

            function projectLifecycleStatusKey(p) {
                const statusRaw = [p?.planning_status, p?.closure_status, p?.execution_status]
                    .map(v => String(v || '').trim())
                    .find(v => v) || '';
                const s = norm(statusRaw);
                if (
                    s.includes('ยกเลิก') ||
                    s.includes('ยุติ') ||
                    s.includes('ระงับ') ||
                    s.includes('cancel') ||
                    s.includes('terminate') ||
                    s.includes('abandon') ||
                    s.includes('withdraw')
                ) return 'cancelled';
                if (
                    s.includes('ปิดโปรเจค') ||
                    s.includes('ปิดโครงการ') ||
                    s.includes('เสร็จ') ||
                    s.includes('สิ้นสุด') ||
                    s.includes('closed') ||
                    s.includes('complete') ||
                    s.includes('completed') ||
                    s.includes('finish') ||
                    s.includes('finished') ||
                    s.includes('done')
                ) return 'completed';
                return 'in_progress';
            }

            function projectLifecycleStartYearCE(p) {
                const startYear = yearCE(p?.start_year);
                if (Number.isFinite(startYear)) return startYear;
                const byDate = parseYearCEFromAny(p?.start_date || p?.team_start_date || p?.created_at || p?.updated_at);
                if (Number.isFinite(byDate)) return byDate;
                return null;
            }

            function projectLifecycleEventYearCE(p) {
                const byField = parseYearCEFromAny(getEndYear(p));
                if (Number.isFinite(byField)) return byField;
                const endDate = parseProjectDate(getEndDateRaw(p));
                if (endDate && !isNaN(endDate)) return endDate.getFullYear();
                const byUpdatedAt = parseYearCEFromAny(p?.updated_at);
                if (Number.isFinite(byUpdatedAt)) return byUpdatedAt;
                return projectLifecycleStartYearCE(p);
            }

            function projectLifecycleTerminalYearCE(p, horizonCE) {
                const startY = projectLifecycleStartYearCE(p);
                if (!Number.isFinite(startY)) return null;
                const key = projectLifecycleStatusKey(p);
                if (key === 'completed' || key === 'cancelled') {
                    const eventY = projectLifecycleEventYearCE(p);
                    return Number.isFinite(eventY) ? Math.max(startY, eventY) : startY;
                }
                const h = Number.isFinite(horizonCE) ? horizonCE : startY;
                return Math.max(startY, h);
            }

            function projectExistsInYear(p, yCE, horizonCE) {
                const startY = projectLifecycleStartYearCE(p);
                const terminalY = projectLifecycleTerminalYearCE(p, horizonCE);
                const Y = Number(yCE);
                if (!(Number.isFinite(startY) && Number.isFinite(terminalY) && Number.isFinite(Y))) return false;
                return startY <= Y && Y <= terminalY;
            }

            function projectIsInProgressInYear(p, yCE, horizonCE) {
                const Y = Number(yCE);
                const startY = projectLifecycleStartYearCE(p);
                if (!Number.isFinite(startY) || Y < startY) return false;
                if (!projectExistsInYear(p, Y, horizonCE)) return false;
                const key = projectLifecycleStatusKey(p);
                if (key === 'in_progress') return true;
                const terminalY = projectLifecycleTerminalYearCE(p, horizonCE);
                return Y < terminalY;
            }

            function projectLifecycleBucketAtYear(p, yCE, horizonCE) {
                const Y = Number(yCE);
                const startY = projectLifecycleStartYearCE(p);
                if (!(Number.isFinite(Y) && Number.isFinite(startY)) || startY > Y) return null;

                const key = projectLifecycleStatusKey(p);
                const terminalY = projectLifecycleTerminalYearCE(p, horizonCE);
                if (key === 'cancelled' && Number.isFinite(terminalY) && terminalY <= Y) return 'cancelled';
                if (key === 'completed' && Number.isFinite(terminalY) && terminalY <= Y) return 'completed';
                if (startY === Y) return 'new';
                if (projectIsInProgressInYear(p, Y, horizonCE)) return 'in_progress';
                return null;
            }

            function matchYearLabel(p, label) {
                const labelStr = String(label ?? '').trim();
                if (!labelStr) return false;
                const yearRaw = String(p.start_year ?? '').trim();
                if (yearRaw === labelStr) return true;
                const ce = yearCE(yearRaw);
                if (ce && (String(ce) === labelStr || yearLabelTH(ce) === labelStr)) return true;
                return labelStr === 'ไม่ระบุ' && !yearRaw;
            }

            function matchFundingYearLabel(p, label) {
                const labelStr = String(label ?? '').trim();
                if (!labelStr) return false;
                const yearRaw = p?.start_year ?? p?.year ?? p?.funding_year ?? null;
                const ce = yearCE(yearRaw);
                if (Number.isFinite(ce)) {
                    return labelStr === String(ce) || labelStr === yearLabelTH(ce);
                }
                return matchYearLabel(p, labelStr);
            }

            function isPiInPolicyUnit(project) {
                const fields = [
                    project?.project_list,
                    project?.pi_affiliation,
                    project?.unit_name,
                    project?.department,
                ]
                    .map((value) => String(value || '').trim())
                    .filter(Boolean);

                if (!fields.length) return false;

                return fields.some((raw) => {
                    const normalized = raw
                        .replace(/\s+/g, ' ')
                        .replace(/[()]/g, ' ')
                        .trim()
                        .toLowerCase();

                    return (
                        (normalized.includes('ภายในหน่วยวิจัย') && normalized.includes('สุขภา')) ||
                        normalized.includes('หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ') ||
                        normalized.includes('siriraj health policy') ||
                        normalized.includes('policy-driven health')
                    );
                });
            }

            function getFilteredProjects() {
                const y = norm(document.getElementById('filterYear')?.value);
                const t = norm(document.getElementById('filterTheme')?.value);
                const f = norm(document.getElementById('filterFunding')?.value);
                const type = norm(document.getElementById('filterType')?.value);
                const status = norm(document.getElementById('filterStatus')?.value);
                const source = norm(document.getElementById('filterSource')?.value);
                const pi = norm(document.getElementById('filterPi')?.value);
                const piAffiliation = norm(document.getElementById('filterPiAffiliation')?.value);
                const name = norm(document.getElementById('filterName')?.value);
                const search = norm(document.getElementById('tableSearch')?.value);

                return projects.filter(p => {
                    const year = norm(p.start_year);
                    const theme = norm(p.theme);
                    const funding = norm(p.funding_type);
                    const projectType = norm(p.project_type);
                    const projectStatus = norm(p.planning_status);
                    const fundingSource = norm(p.funding_source);
                    const principalInvestigator = norm(p.pi_name);
                    const principalInvestigatorAffiliation = norm(p.pi_affiliation);
                    const nameTh = norm(p.project_name_th);
                    const searchIndex = projectSearchIndex(p);

                    if (y && year !== y) return false;
                    if (t && theme !== t) return false;
                    if (f && funding !== f) return false;
                    if (type && projectType !== type) return false;
                    if (status && projectStatus !== status) return false;
                    if (source && fundingSource !== source) return false;
                    if (pi && !principalInvestigator.includes(pi)) return false;
                    if (piAffiliation && !principalInvestigatorAffiliation.includes(piAffiliation)) return false;
                    if (name && !nameTh.includes(name)) return false;
                    if (search && !searchIndex.includes(search)) return false;
                    return true;
                });
            }

            function sanitizeFileName(raw) {
                return String(raw || 'user')
                    .trim()
                    .replace(/[\\/:*?"<>|]+/g, '')
                    .replace(/\s+/g, '_');
            }

            function exportDateTime() {
                const now = new Date();
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const ss = String(now.getSeconds()).padStart(2, '0');
                return `${y}${m}${d}_${hh}${mm}:${ss}`;
            }

            function exportFilterSuffix() {
                const filterValues = [
                    document.getElementById('filterYear')?.value,
                    document.getElementById('filterTheme')?.value,
                    document.getElementById('filterFunding')?.value,
                    document.getElementById('filterType')?.value,
                    document.getElementById('filterStatus')?.value,
                    document.getElementById('filterSource')?.value,
                    document.getElementById('filterPi')?.value,
                    document.getElementById('filterPiAffiliation')?.value,
                    document.getElementById('filterName')?.value,
                    document.getElementById('tableSearch')?.value
                ]
                    .map(v => sanitizeFileName(v))
                    .filter(v => v && v.toLowerCase() !== 'user');

                return filterValues.length ? filterValues.join('_') : 'all';
            }

            function exportBaseName() {
                const datetime = exportDateTime();
                const suffix = exportFilterSuffix();
                return `${datetime}_sihp_researchData_${suffix}`;
            }


            function categorizeCollab(p) {
                // ถ้ามี p.collab_type ให้ใช้เลย เช่น: 'outside_faculty' | 'cross_dept_in_med' | 'outside_university'
                if (p.collab_type) {
                    const map = {
                        outside_faculty: 'นอกคณะ',
                        cross_dept_in_med: 'ข้ามหน่วย (ในคณะแพทย์)',
                        outside_university: 'นอกมหาวิทยาลัย'
                    };
                    return map[p.collab_type] || 'ไม่ระบุ';
                }
                // ถ้าไม่มีฟิลด์ ให้คืน 'ไม่ระบุ' (หรือเพิ่ม logic จาก affiliation ได้)
                return 'ไม่ระบุ';
            }

            /* ---------- ฟังก์ชันย่อเงินแบบไทย (พัน/หมื่น/แสน/ล้าน) ---------- */
            function formatThaiMoney(num) {
                const n = Number(num) || 0;
                return (n / 1_000_000).toFixed(2).replace(/\.00$/, '') + ' ล้านบ.';
            }


            function showModal(title, htmlBody) {
                document.getElementById('chartModalTitle').innerText = title;
                document.getElementById('chartModalBody').innerHTML = htmlBody;
                document.getElementById('chartModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('chartModal').classList.add('hidden');
            }

            function normalizeProjectList(raw) {
                const s = (raw ?? '').toString().trim();
                if (!s) return null;
                if (s.includes('ภายในหน่วยวิจัย') && s.includes('สุขภา')) return 'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
                if (s.includes('นอกหน่วย') && s.includes('คณะ')) return 'โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ';
                if (s.includes('วิจัยนอกคณะ')) return 'โครงการวิจัยนอกคณะ';
                return null;
            }

            function projectsFromOutputs(filterFn) {
                const ids = new Set();
                outputs.filter(filterFn).forEach(o => {
                    if (o.project_id != null) ids.add(o.project_id);
                });
                return Array.from(ids).map(id => projectById.get(id)).filter(Boolean);
            }

            function projectsFromFtes(filterFn) {
                const ids = new Set();
                ftes.filter(filterFn).forEach(f => {
                    if (f.project_id != null) ids.add(f.project_id);
                });
                return Array.from(ids).map(id => projectById.get(id)).filter(Boolean);
            }

            function resolveProjectsForChart(chart, point) {
                const id = chart?.canvas?.id;
                const label = chart?.data?.labels?.[point.index];
                const datasetLabel = chart?.data?.datasets?.[point.datasetIndex]?.label;

                if (!id) return null;

                // ใช้ชุดข้อมูลที่ผ่านฟิลเตอร์ปัจจุบัน เพื่อให้ drill-down สอดคล้องกับหน้าจอ
                const projects = typeof getFilteredProjects === 'function'
                    ? getFilteredProjects()
                    : Array.from(projectById.values());
                const filteredProjectIds = new Set((projects || []).map(p => p?.id).filter(v => v != null));
                const keepFilteredProjects = (list) => (Array.isArray(list) ? list.filter(p => p && filteredProjectIds.has(p.id)) : []);
                const projectsFromOutputsScoped = (filterFn) => keepFilteredProjects(projectsFromOutputs(filterFn));
                const projectsFromFtesScoped = (filterFn) => keepFilteredProjects(projectsFromFtes(filterFn));

                if (id === 'chartProjectsPerYear') {
                    const labelText = String(label ?? '').trim();
                    const isUnknownBucket = labelText === 'ไม่ระบุปี';
                    const Y = Number(yearCE(labelText) || toNumber(labelText));
                    return {
                        title: `โครงการที่เริ่มในปี ${labelText}`,
                        projects: projects.filter(p => {
                            const startY = projectLifecycleStartYearCE(p);
                            if (isUnknownBucket) return !Number.isFinite(startY);
                            return Number.isFinite(startY) && Number.isFinite(Y) && startY === Y;
                        })
                    };
                }

                if (id === 'chartYearlyNewVsTotal') {
                    const labelText = String(label ?? '').trim();
                    const type = String(datasetLabel || '').trim();
                    const map = window.yearlyNewVsTotalLabelMap || {};
                    const rawBucket = map[labelText];
                    const isUnknownBucket = rawBucket === '__unknown__' || labelText === 'ไม่ระบุปี';
                    const Y = Number(rawBucket || yearCE(labelText) || toNumber(labelText));
                    const horizonCE = Number(window.yearlyLifecycleHorizonCE) || new Date().getFullYear();
                    let list = [];
                    if (type === 'ทั้งหมด') {
                        list = isUnknownBucket
                            ? projects.filter(p => !Number.isFinite(projectLifecycleStartYearCE(p)))
                            : projects.filter(p => {
                                const startY = projectLifecycleStartYearCE(p);
                                if (Number.isFinite(startY)) return startY <= Y;
                                return Y === horizonCE;
                            });
                    } else if (type === 'ใหม่') {
                        list = isUnknownBucket ? [] : projects.filter(p => projectLifecycleBucketAtYear(p, Y, horizonCE) === 'new');
                    } else if (type === 'กำลังทำ' || type === 'กำลังดำเนินการ') {
                        list = isUnknownBucket
                            ? projects.filter(p => !Number.isFinite(projectLifecycleStartYearCE(p)) && projectLifecycleStatusKey(p) === 'in_progress')
                            : projects.filter(p => projectLifecycleBucketAtYear(p, Y, horizonCE) === 'in_progress');
                    } else if (type === 'เสร็จสิ้น' || type === 'เสร็จสิ้นแล้ว' || type === 'ปิดโปรเจค') {
                        list = isUnknownBucket
                            ? projects.filter(p => !Number.isFinite(projectLifecycleStartYearCE(p)) && projectLifecycleStatusKey(p) === 'completed')
                            : projects.filter(p => projectLifecycleBucketAtYear(p, Y, horizonCE) === 'completed');
                    } else if (type === 'ยกเลิก' || type === 'ยกเลิกโปรเจค') {
                        list = isUnknownBucket
                            ? projects.filter(p => !Number.isFinite(projectLifecycleStartYearCE(p)) && projectLifecycleStatusKey(p) === 'cancelled')
                            : projects.filter(p => projectLifecycleBucketAtYear(p, Y, horizonCE) === 'cancelled');
                    }
                    return {
                        title: `โครงการ${type} ปี ${labelText}`,
                        projects: list
                    };
                }

                if (id === 'chartFundingSplit') {
                    return {
                        title: `ทุน${datasetLabel || ''} ปี ${label}`,
                        projects: projects.filter(p => matchYearLabel(p, label) && getFundingType(p) === datasetLabel),
                        includeFunding: true
                    };
                }

                if (id === 'chartBudgetPerYear') {
                    return {
                        title: `โครงการปี ${label}`,
                        projects: projects.filter(p => matchYearLabel(p, label)),
                        includeFunding: true
                    };
                }

                if (id === 'chartExternalBySource') {
                    const groups = window.fundingSourceGroups?.external;
                    if (label === 'อื่นๆ' && groups?.others?.length) {
                        return {
                            title: `ทุนภายนอก: อื่นๆ`,
                            projects: projects.filter(p =>
                                getFundingType(p) === 'ภายนอก' &&
                                groups.others.includes((getFundingSource(p) || 'ไม่ระบุ'))
                            ),
                            includeFunding: true
                        };
                    }
                    return {
                        title: `ทุนภายนอก: ${label}`,
                        projects: projects.filter(p => getFundingType(p) === 'ภายนอก' && (getFundingSource(p) || 'ไม่ระบุ') === label),
                        includeFunding: true
                    };
                }

                if (id === 'chartInternalBySource') {
                    const groups = window.fundingSourceGroups?.internal;
                    if (label === 'อื่นๆ' && groups?.others?.length) {
                        return {
                            title: `ทุนภายใน: อื่นๆ`,
                            projects: projects.filter(p =>
                                getFundingType(p) === 'ภายใน' &&
                                groups.others.includes((getFundingSource(p) || 'ไม่ระบุ'))
                            ),
                            includeFunding: true
                        };
                    }
                    return {
                        title: `ทุนภายใน: ${label}`,
                        projects: projects.filter(p => getFundingType(p) === 'ภายใน' && (getFundingSource(p) || 'ไม่ระบุ') === label),
                        includeFunding: true
                    };
                }

                if (id === 'chartExternalBySourceYear' || id === 'chartInternalBySourceYear') {
                    const type = id === 'chartExternalBySourceYear' ? 'ภายนอก' : 'ภายใน';
                    if (datasetLabel === 'อื่นๆ') {
                        const group = window.fundingSourceYearGroups?.[id];
                        const others = group?.others || [];
                        return {
                            title: `ทุน${type} อื่นๆ ปี ${label}`,
                            projects: projects.filter(p =>
                                getFundingType(p) === type &&
                                matchFundingYearLabel(p, label) &&
                                others.includes((getFundingSource(p) || 'ไม่ระบุ'))
                            ),
                            includeFunding: true
                        };
                    }
                    return {
                        title: `ทุน${type} ${datasetLabel || ''} ปี ${label}`,
                        projects: projects.filter(p =>
                            getFundingType(p) === type &&
                            matchFundingYearLabel(p, label) &&
                            (getFundingSource(p) || 'ไม่ระบุ') === datasetLabel
                        ),
                        includeFunding: true
                    };
                }

                if (id === 'chartOutputStacked') {
                    const clickedLabel = String(label ?? '');
                    const map = window.outputYearLabelKeyMap || {};
                    const year = map[clickedLabel] || (clickedLabel === 'ไม่ระบุปี' ? 'ไม่ระบุ' : normalizeOutputYear(clickedLabel));
                    const type = String(datasetLabel ?? '');
                    return {
                        title: `Outputs ${type} ปี ${clickedLabel}`,
                        projects: projectsFromOutputsScoped(o => outputYearKey(o) === year && outputType(o) === type)
                    };
                }

                if (id === 'chartPublicationPerYear') {
                    const yearLabel = String(label ?? '');
                    return {
                        title: `Publication ปี ${yearLabel}`,
                        projects: projectsFromOutputsScoped(o => {
                            if (!isPublicationType(o.type)) return false;
                            const ce = normalizeYear(o.year);
                            const be = ce ? toBE(ce) : 'ไม่ทราบปี';
                            return String(be) === yearLabel;
                        })
                    };
                }

                if (id === 'chartStatusYear') {
                    return {
                        title: `สถานะ ${datasetLabel || ''} ปี ${label}`,
                        projects: projects.filter(p => matchYearLabel(p, label) && getStatusYearChartLabel(p) === datasetLabel)
                    };
                }

                if (id === 'chartCollabBreakdown') {
                    return {
                        title: `${datasetLabel || ''} ปี ${label}`,
                        projects: projects.filter(p => matchYearLabel(p, label) && normalizeProjectList(p.project_list) === datasetLabel)
                    };
                }

                if (id === 'chartPIsPerUnit') {
                    return {
                        title: `PI ${label}`,
                        projects: projects.filter(p =>
                            isPiInPolicyUnit(p) &&
                            (p.pi_name || '').trim() === String(label ?? '').trim()
                        )
                    };
                }

                if (id === 'chartFteSummary') {
                    return {
                        title: `ผู้รับผิดชอบ ${datasetLabel || ''}`,
                        projects: projectsFromFtesScoped(f => (f.name || 'ไม่ระบุ') === datasetLabel)
                    };
                }

                if (id === 'chartRedZoneTimeline') {
                    const yearLabel = String(label || '');
                    const dataset = chart?.data?.datasets?.[point.datasetIndex] || {};
                    const records = buildOverdueRecords(projects);
                    const list = records.filter(record =>
                        overdueRecordYearLabel(record) === yearLabel &&
                        record.sourceCategory === dataset.redzoneSourceCategory
                    );
                    const sourceText = dataset.redzoneSourceCategory === 'closed_late'
                        ? 'ทำงานเกินสัญญาแต่ปิดโปรเจคแล้ว'
                        : (dataset.redzoneSourceCategory === 'open_team_late'
                            ? 'ทำงานเกินสัญญาและยังไม่ปิดโปรเจค'
                            : 'ครบกำหนดแต่ยังไม่ลงปิดโปรเจคในระบบ');
                    return {
                        title: `${yearLabel} • ${sourceText}`,
                        rows: list,
                        modalType: 'redzone'
                    };
                }

                if (id === 'chartOverdueProjects') {
                    const categoryKey = chart?.$overdueOverviewKeys?.[point.index] || null;
                    const groups = buildOverdueOverviewSummary(projects);
                    const group = groups.find((entry) => entry.key === categoryKey)
                        || groups.find((entry) => entry.label === String(label || ''));
                    if (!group) return null;
                    return {
                        title: group.label,
                        rows: group.recordMode ? group.rows : null,
                        projects: group.recordMode ? null : group.projects,
                        modalType: group.recordMode ? 'overdue' : (group.key === 'incomplete_data' ? 'incomplete_data' : null)
                    };
                }

                if (id === 'chartCoResearchByUnitWindow') {
                    const now = new Date();
                    const windowLabel = String(datasetLabel || '');
                    const unit = String(label || '');
                    let maxMonths = 12;
                    if (windowLabel.includes('3')) maxMonths = 3;
                    else if (windowLabel.includes('6')) maxMonths = 6;
                    else if (windowLabel.includes('1')) maxMonths = 12;

                    const list = projects.filter(p => {
                        const endDate = parseProjectDate(getEndDateRaw(p));
                        if (!endDate) return false;
                        const diffMonths = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24 * 30.4375);
                        if (diffMonths < 0 || diffMonths > maxMonths) return false;
                        const coList = getCoResearchList(p);
                        return coList.some(c => {
                            const raw = c.unit_status || c.affiliation || c.unit || c.department || 'ไม่ระบุ';
                            return String(raw || '').trim() === unit;
                        });
                    });
                    return {
                        title: `${unit} (${windowLabel})`,
                        projects: list,
                        modalType: 'coresearch',
                        unit,
                        windowMonths: maxMonths
                    };
                }

                if (id === 'chartIRBExpiry') {
                    const bucketKey = chart?.$irbExpiryKeys?.[point.index] || null;
                    const labelText = String(label || '');
                    const list = projects.filter(p => {
                        const list = p.irbs || [];
                        return list.some(irb => {
                            const d = parseProjectDate(irb.exp_date);
                            if (!d) return false;
                            const key = irbExpiryBucketKey(d);
                            if (!key) return false;
                            return bucketKey ? key === bucketKey : irbExpiryBucketLabel(key) === labelText || key === labelText;
                        });
                    });
                    return {
                        title: `IRB ใกล้หมดอายุ ${bucketKey ? irbExpiryBucketLabel(bucketKey) : labelText}`,
                        projects: list
                    };
                }

                if (id === 'chartAuthorRolePerYear') {
                    const yearCEValue = document.getElementById('yearFilter')?.value;
                    const roleMap = {
                        'First-author': 'first',
                        'Co-author': 'co',
                        'Corresponding': 'corresponding'
                    };
                    const role = roleMap[datasetLabel] || String(datasetLabel || '').toLowerCase();
                    const name = String(label || '').trim();
                    const list = projectsFromOutputsScoped(o => {
                        if (!isPublicationType(o.type)) return false;
                        const y = normalizeYear(o.year);
                        if (!y || String(y) !== String(yearCEValue)) return false;
                        const authors = Array.isArray(o.authors) ? o.authors : [];
                        return authors.some(a => (a.name || '').trim() === name && (a.role || '').toLowerCase() === role);
                    });
                    return {
                        title: `บทบาท ${datasetLabel || ''} : ${name}`,
                        projects: list
                    };
                }

                return null;
            }

            function handleChartClick(evt, chart) {
                const isYearlyLifecycle = chart?.canvas?.id === 'chartYearlyNewVsTotal';
                let points = chart.getElementsAtEventForMode(evt, 'nearest', {
                    intersect: true,
                    axis: 'xy'
                }, true);
                if ((!points || !points.length) && isYearlyLifecycle) {
                    points = chart.getElementsAtEventForMode(evt, 'index', {
                        intersect: false,
                        axis: 'x'
                    }, true);
                }
                if (!points || !points.length) return;
                let picked = points[0];
                if (isYearlyLifecycle && points.length > 1) {
                    const px = Number(evt?.offsetX);
                    const py = Number(evt?.offsetY);
                    if (Number.isFinite(px) && Number.isFinite(py)) {
                        let minDist = Number.POSITIVE_INFINITY;
                        points.forEach(pt => {
                            const el = chart.getDatasetMeta(pt.datasetIndex)?.data?.[pt.index];
                            const ex = Number(el?.x);
                            const ey = Number(el?.y);
                            if (!Number.isFinite(ex) || !Number.isFinite(ey)) return;
                            const d = ((ex - px) ** 2) + ((ey - py) ** 2);
                            if (d < minDist) {
                                minDist = d;
                                picked = pt;
                            }
                        });
                    }
                }
                const hitKey = `${chart?.canvas?.id || 'chart'}:${picked.datasetIndex}:${picked.index}`;
                const lastHit = window.__dashboardChartLastHit;
                const nowTs = Date.now();
                if (lastHit && lastHit.key === hitKey && (nowTs - lastHit.at) < 180) return;
                window.__dashboardChartLastHit = {
                    key: hitKey,
                    at: nowTs
                };
                const result = resolveProjectsForChart(chart, picked);
                if (result) showProjectList(result.title, result.rows || result.projects, {
                    includeFunding: result.includeFunding,
                    modalType: result.modalType,
                    preserveRows: Array.isArray(result.rows),
                    unit: result.unit,
                    windowMonths: result.windowMonths
                });
            }

            if (window.Chart) {
                Chart.defaults.onClick = (evt, _elements, chart) => {
                    if (!chart?.canvas?.id || !chart.canvas.id.startsWith('chart')) return;
                    handleChartClick(evt?.native || evt, chart);
                };
            }

            function forwardDashboardChartEvent(evt) {
                const canvas = evt.target;
                if (!canvas || canvas.tagName !== 'CANVAS') return;
                if (!canvas.id || !canvas.id.startsWith('chart')) return;
                const chart = window.Chart?.getChart(canvas);
                if (!chart) return;
                handleChartClick(evt, chart);
            }

            document.addEventListener('click', forwardDashboardChartEvent);

            /* ---------- 1) จำนวนโครงการรายปี (Bar + ตัวเลขบนแท่ง | กันเลขตกขอบ) ---------- */
            (function() {
                // นับตามปีเริ่มต้นจริง และแยกข้อมูลที่ไม่ระบุปีออกเป็น bucket เฉพาะ
                const startYearsCE = projects
                    .map(p => projectLifecycleStartYearCE(p))
                    .filter(Number.isFinite)
                    .sort((a, b) => a - b);
                const unknownProjects = projects.filter((p) => !Number.isFinite(projectLifecycleStartYearCE(p)));
                if (!startYearsCE.length && !unknownProjects.length) return;

                const yearsCE = Array.from(new Set(startYearsCE));
                const buckets = [...yearsCE, ...(unknownProjects.length ? ['__unknown__'] : [])];
                const labels = buckets.map((bucket) => bucket === '__unknown__' ? 'ไม่ระบุปี' : yearLabelTH(bucket));
                const data = buckets.map((bucket) => {
                    if (bucket === '__unknown__') return unknownProjects.length;
                    return projects.filter(p => {
                        const startY = projectLifecycleStartYearCE(p);
                        return Number.isFinite(startY) && startY === bucket;
                    }).length;
                });
                const colors = labels.map((_, i) => `hsl(${(i * 37) % 360} 70% 60%)`);

                // ทำลายกราฟเดิมถ้ามี
                const el = document.getElementById('chartProjectsPerYear');
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                // ปลั๊กอินวาดตัวเลขบนแท่ง + กันตกขอบบน
                const barValuePlugin = {
                    id: 'barValuePlugin',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                top,
                                right
                            },
                            scales: {
                                x,
                                y
                            }
                        } = chart;
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillStyle = '#374151';

                        const meta = chart.getDatasetMeta(0);
                        const ds = chart.data.datasets[0];

                        meta.data.forEach((bar, i) => {
                            const v = ds.data[i];
                            if (v == null || !isFinite(v) || v <= 0) return;

                            let drawX = bar.x;
                            let drawY = bar.y - 6; // วางเหนือแท่ง

                            // กันตกขอบบน
                            const minY = top + 8;
                            if (drawY < minY) {
                                // ถ้าแนบขอบจริง ๆ ให้เลื่อนลงมาให้อยู่ในกราฟ
                                drawY = minY;
                            }
                            // กันชิดขอบขวา
                            if (drawX > right - 2) drawX = right - 2;

                            ctx.fillText(String(v), drawX, drawY);
                        });

                        ctx.restore();
                    }
                };

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'จำนวนโครงการ',
                            data,
                            backgroundColor: colors,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 14
                            }
                        }, // เผื่อพื้นที่ด้านบนเพิ่ม
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ` ${ctx.raw} โครงการ`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grace: '12%', // headroom กันเลขชนขอบ
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'ปี (พ.ศ.)'
                                }
                            }
                        }
                    },
                    plugins: [barValuePlugin]
                });
            })();

            /* ---------- CO-PI Table: pagination (10 rows) ---------- */
            (function() {
                const rows = Array.isArray(copisSummary) ? copisSummary : [];
                const tbody = document.getElementById('copiTableBody');
                const prevBtn = document.getElementById('copiPrev');
                const nextBtn = document.getElementById('copiNext');
                const pageEl = document.getElementById('copiPagination');
                const pageSize = 10;
                let page = 1;

                function render() {
                    if (!tbody) return;
                    const total = rows.length;
                    const totalPages = Math.max(Math.ceil(total / pageSize), 1);
                    page = Math.min(Math.max(page, 1), totalPages);
                    const start = (page - 1) * pageSize;
                    const pageRows = rows.slice(start, start + pageSize);

                    tbody.innerHTML = '';
                    if (!pageRows.length) {
                        tbody.innerHTML =
                            `<tr class="border-t"><td colspan="3" class="p-3 text-center text-gray-500">ไม่มีข้อมูลผู้ร่วมวิจัย</td></tr>`;
                    } else {
                        pageRows.forEach(r => {
                            const row = document.createElement('tr');
                            row.className = 'border-t';
                            row.innerHTML = `
                                <td class="p-2" data-label="ชื่อ">${r.name || 'ไม่ระบุ'}</td>
                                <td class="p-2" data-label="หน่วยงาน">${r.affiliation || 'ไม่ระบุ'}</td>
                                <td class="p-2 text-center" data-label="จำนวนโครงการ">${r.project_count ?? 0}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    }

                    if (pageEl) pageEl.textContent = `หน้า ${page} / ${totalPages}`;
                    if (prevBtn) prevBtn.disabled = page <= 1;
                    if (nextBtn) nextBtn.disabled = page >= totalPages;
                }

                prevBtn?.addEventListener('click', () => {
                    page = Math.max(page - 1, 1);
                    render();
                });
                nextBtn?.addEventListener('click', () => {
                    page = page + 1;
                    render();
                });

                render();
            })();

            // 2) สัดส่วนทุน ภายใน/ภายนอก (รายปี) + Data Label + กันเลขตกขอบ + เช็ค toggle
            (function() {
                const years = [...new Set(projects.map(getYear))].sort();
                const types = ['ภายใน', 'ภายนอก'];

                const matrix = {};
                years.forEach(y => (matrix[y] = {
                    'ภายใน': 0,
                    'ภายนอก': 0
                }));
                projects.forEach(p => {
                    const y = getYear(p);
                    const t = getFundingType(p);
                    if (matrix[y] && (t === 'ภายใน' || t === 'ภายนอก')) matrix[y][t] += 1;
                });

                const datasets = types.map(t => ({
                    label: t,
                    data: years.map(y => matrix[y][t]),
                    backgroundColor: t === 'ภายใน' ? '#60a5fa' : '#34d399',
                    stack: 'fund'
                }));

                // ปลั๊กอินโชว์ตัวเลขบนแท่ง + เช็คซ่อน/แสดง + กันตกขอบบน/ขวา
                const barValuePlugin = {
                    id: 'barValueFunding',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                top,
                                right
                            }
                        } = chart;
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        chart.data.datasets.forEach((dataset, dsIndex) => {
                            // ถ้าชุดถูกซ่อนจาก legend ให้ข้าม
                            if (!chart.isDatasetVisible(dsIndex)) return;

                            const meta = chart.getDatasetMeta(dsIndex);
                            meta.data.forEach((bar, index) => {
                                if (!bar || bar.hidden) return;

                                const value = dataset.data[index];
                                if (!isFinite(value) || value <= 0) return;

                                let drawX = bar.x;
                                let drawY = bar.y - 4; // วางเหนือแท่งเล็กน้อย

                                // clamp กันตกขอบบน
                                const minY = top + 8;
                                if (drawY < minY) drawY = minY;

                                // กันชิดขอบขวา
                                if (drawX > right - 2) drawX = right - 2;

                                ctx.fillText(String(value), drawX, drawY);
                            });
                        });

                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartFundingSplit');
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: years,
                        datasets
                    },
                    options: {
                        responsive: true,
                        layout: {
                            padding: {
                                top: 14
                            }
                        }, // เผื่อพื้นที่ด้านบนให้ label
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'ปี'
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grace: '12%', // headroom กันเลขชนขอบ
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            }
                        }
                    },
                    plugins: [barValuePlugin]
                });
            })();

            /* ---------- 3) งบประมาณรวมรายปี (Bar + Data Label หน่วย พัน/หมื่น/แสน/ล้าน | กันเลขตกขอบ) ---------- */
            (function() {
                // รวมงบประมาณต่อปี (บังคับเลขเสมอ)
                const sumByYear = new Map();
                projects.forEach(p => {
                    const y = (getYear(p) ?? '').toString().trim() || 'ไม่ระบุ';
                    // บังคับเป็น number (กันงบประมาณเป็นสตริง/ค่าว่าง)
                    const v = Number(getBudget ? getBudget(p) : (p.total_budget ?? p.budget ?? 0)) || 0;
                    sumByYear.set(y, (sumByYear.get(y) || 0) + v);
                });

                // เรียงปี: ตัวเลขก่อน คำทีหลัง
                const isNum = s => /^\d+$/.test(s);
                const allYears = [...sumByYear.keys()];
                const years = allYears
                    .filter(isNum).map(Number).sort((a, b) => a - b).map(String)
                    .concat(allYears.filter(y => !isNum(y)).sort());

                const data = years.map(y => sumByYear.get(y) || 0);

                // ฟอร์แมตหน่วยเงินไทย (พัน/หมื่น/แสน/ล้าน) + ตัด .00
                // function formatMoneyTHB(v) {
                //     const n = Number(v) || 0;
                //     const trim = (x) => Number(x).toFixed(2).replace(/\.00$/, '');
                //     if (n >= 1_000_000) return `${trim(n / 1_000_000)} ล้านบาท`;
                //     if (n >= 100_000) return `${trim(n /   100_000)} แสนบาท`;
                //     if (n >= 10_000) return `${trim(n /    10_000)} หมื่นบาท`;
                //     if (n >= 1_000) return `${trim(n /     1_000)} พันบาท`;
                //     return `${n.toLocaleString('th-TH')} บาท`;
                // }
                function formatMoneyTHB(v) {
                    const n = Number(v) || 0;
                    return `${(n / 1_000_000).toFixed(2)} ล้าน`;
                }


                // ปลั๊กอินวาดเลขบนแท่ง + เช็คซ่อน + กันตกขอบบน/ขวา
                const budgetValueLabelPlugin = {
                    id: 'budgetValueLabel',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                top,
                                right
                            }
                        } = chart;
                        // ถ้าชุดข้อมูลถูกซ่อนจาก legend → ไม่วาด
                        if (!chart.isDatasetVisible(0)) return;

                        const meta = chart.getDatasetMeta(0);
                        const ds = chart.data.datasets[0];
                        if (!meta || !ds) return;

                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillStyle = '#111827';

                        meta.data.forEach((bar, i) => {
                            if (!bar || bar.hidden) return;
                            const v = Number(ds.data[i] ?? 0);
                            if (!(isFinite(v) && v > 0)) return;

                            const label = formatMoneyTHB(v);
                            let drawX = bar.x;
                            let drawY = bar.y - 6;

                            // clamp กันตกขอบบน/ขวา
                            const minY = top + 8;
                            if (drawY < minY) drawY = minY;
                            if (drawX > right - 2) drawX = right - 2;

                            ctx.fillText(label, drawX, drawY);
                        });

                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartBudgetPerYear');
                if (!el) return;
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: years,
                        datasets: [{
                            label: 'งบประมาณ (รวม)',
                            data,
                            backgroundColor: '#fbbf24',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        layout: {
                            padding: {
                                top: 20
                            }
                        }, // เผื่อพื้นที่ label ด้านบน
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => formatMoneyTHB(ctx.raw)
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'ปี'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grace: '12%', // headroom กันเลขชนขอบ
                                ticks: {
                                    // กัน error ถ้าค่า tick แปลก
                                    callback: (value) => {
                                        try {
                                            return formatMoneyTHB(value);
                                        } catch {
                                            return value;
                                        }
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'งบประมาณ'
                                }
                            }
                        }
                    },
                    plugins: [budgetValueLabelPlugin]
                });
            })();



            /* ---------- 4) Output Stacked by Type / Year (กันเลขตกขอบ + เช็ค toggle legend) ---------- */
            (function() {
                const rows = Array.isArray(outputs) ? outputs : [];
                const types = [...new Set(rows.map(outputType).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'th'));
                const matrix = {};

                rows.forEach((o) => {
                    const y = outputYearKey(o);
                    const t = outputType(o) || 'อื่นๆ';
                    matrix[y] = matrix[y] || {};
                    matrix[y][t] = (matrix[y][t] || 0) + 1;
                });

                const years = Object.keys(matrix);
                const isNum = (s) => /^\d+$/.test(String(s || ''));
                const sortedYears = years
                    .filter(isNum).map(Number).sort((a, b) => a - b).map(String)
                    .concat(years.filter((y) => !isNum(y)).sort((a, b) => String(a).localeCompare(String(b), 'th')));
                const displayLabels = sortedYears.map((y) => outputYearLabelForKey(y));

                window.outputYearLabelKeyMap = {};
                displayLabels.forEach((label, idx) => {
                    window.outputYearLabelKeyMap[label] = sortedYears[idx];
                });

                const datasets = types.map((t, i) => ({
                    label: t,
                    data: sortedYears.map((y) => Number(matrix[y]?.[t] || 0)),
                    backgroundColor: `hsl(${(i * 53) % 360} 70% 60%)`,
                    stack: 'out'
                }));

                // แสดงเฉพาะ "ยอดรวมต่อปี" บนสุด เพื่อลดความรกของเลขหลายชั้น
                const totalLabelPlugin = {
                    id: 'outputTotalLabel',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                top,
                                right
                            }
                        } = chart;
                        ctx.save();
                        ctx.font = '700 11px "IBM Plex Sans Thai", sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        chart.data.labels.forEach((_, i) => {
                            let sum = 0;
                            chart.data.datasets.forEach((ds, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                sum += Number(ds.data?.[i] || 0);
                            });
                            if (sum <= 0) return;

                            let topBar = null;
                            chart.data.datasets.forEach((ds, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                const el = chart.getDatasetMeta(dsIndex).data[i];
                                if (el && (!topBar || el.y < topBar.y)) topBar = el;
                            });
                            if (!topBar) return;

                            let { x, y } = topBar.tooltipPosition();
                            y -= 4;
                            if (y < top + 8) y = top + 8;
                            if (x > right - 2) x = right - 2;
                            ctx.fillText(String(sum), x, y);
                        });

                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartOutputStacked');
                if (!el) return;
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                if (!sortedYears.length || !datasets.length) {
                    const c = el.getContext('2d');
                    c.clearRect(0, 0, el.width, el.height);
                    c.font = '14px sans-serif';
                    c.fillStyle = '#6b7280';
                    c.textAlign = 'center';
                    c.textBaseline = 'middle';
                    c.fillText('ไม่มีข้อมูล', el.width / 2, el.height / 2);
                    return;
                }

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: displayLabels,
                        datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 16
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    footer: (items) => {
                                        const total = (items || []).reduce((sum, it) => sum + (it.parsed?.y || 0), 0);
                                        return `รวมปีนี้: ${total}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'ปี (พ.ศ.)'
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grace: '12%',
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนผลงาน'
                                }
                            }
                        }
                    },
                    plugins: [totalLabelPlugin]
                });
            })();



            /* ---------- 5) Publication per Year ---------- */
            (function() {
                const pubs = outputs.filter(o => (o.type || '').toLowerCase().includes('pub'));
                const byYear = {};
                pubs.forEach(o => {
                    const y = outputYear(o);
                    byYear[y] = (byYear[y] || 0) + 1;
                });
                const years = Object.keys(byYear).sort();
                new Chart(document.getElementById('chartPublicationPerYear'), {
                    type: 'line',
                    data: {
                        labels: years,
                        datasets: [{
                            label: 'Publication',
                            data: years.map(y => byYear[y]),
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: buildMobileLegend(14)
                        },
                        scales: {
                            x: {
                                ticks: buildMobileCategoryTicks(7, 4)
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            })();

            /* ---------- 6) สถานะโครงการ (Stacked) + กันเลขตกขอบ ---------- */
            (function() {
                const ctx = document.getElementById('chartStatusYear').getContext('2d');
                const statuses = [
                    'Project Planing (เริ่มต้น - submit IRB)',
                    'เก็บข้อมูล',
                    'วิเคราะห์ข้อมูล',
                    'เขียนรายงานและpublication',
                    'ปิดโปรเจค',
                    'ยกเลิกโปรเจค',
                    'ไม่ระบุสถานะ'
                ];

                const statusData = {}; // year → status → count
                projects.forEach(p => {
                    const year = getYear(p);
                    const status = getStatusYearChartLabel(p);
                    if (!statusData[year]) statusData[year] = Object.fromEntries(statuses.map(s => [s, 0]));
                    statusData[year][status]++;
                });

                // เรียงปีให้ถูก: ตัวเลขก่อน คำทีหลัง
                const isNum = s => /^\d+$/.test(String(s));
                const years = Object.keys(statusData);
                const sortedYears = years.filter(isNum).map(Number).sort((a, b) => a - b).map(String)
                    .concat(years.filter(y => !isNum(y)).sort());

                const datasets = statuses.map((s, i) => ({
                    label: s,
                    data: sortedYears.map(y => statusData[y]?.[s] ?? 0),
                    backgroundColor: `hsl(${(i*46)%360} 70% 60%)`,
                    stack: 'st'
                }));

                // ปลั๊กอินแสดง "ยอดรวมต่อปี" เท่านั้น เพื่อลดการซ้อนทับของตัวเลข
                const dataLabelPlugin = {
                    id: 'dataLabelPluginStatus',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                top,
                                right
                            }
                        } = chart;
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillStyle = '#111';

                        const labels = chart.data.labels || [];
                        labels.forEach((_, index) => {
                            let total = 0;
                            let topBar = null;
                            chart.data.datasets.forEach((dataset, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                const value = Number(dataset.data[index] || 0);
                                if (!(value > 0)) return;
                                total += value;
                                const bar = chart.getDatasetMeta(dsIndex)?.data?.[index];
                                if (!bar) return;
                                if (!topBar || bar.y < topBar.y) topBar = bar;
                            });

                            if (!(total > 0) || !topBar) return;
                            let drawX = topBar.x;
                            let drawY = topBar.y - 4;
                            const minY = top + 10;
                            if (drawY < minY) drawY = minY;
                            if (drawX > right - 2) drawX = right - 2;
                            ctx.fillText(String(total), drawX, drawY);
                        });

                        ctx.restore();
                    }
                };

                const prev = Chart.getChart(ctx.canvas);
                if (prev) prev.destroy();

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: sortedYears,
                        datasets
                    },
                    options: {
                        responsive: true,
                        layout: {
                            padding: {
                                top: 16
                            }
                        }, // เผื่อพื้นที่ด้านบนสำหรับ label
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'ปี'
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grace: '12%', // headroom กันเลขชนขอบ
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            }
                        }
                    },
                    plugins: [dataLabelPlugin] // ใส่เฉพาะกราฟนี้
                });
            })();


            /* ---------- 7) IRB Expiry (6 เดือนข้างหน้า) ---------- */
            (function() {
                const now = new Date();
                const group = {};
                irbs.forEach(irb => {
                    const exp = parseProjectDate(irb.exp_date);
                    if (!exp) return;
                    const monthsAhead = (exp - now) / (1000 * 60 * 60 * 24 * 30);
                    if (monthsAhead >= 0 && monthsAhead <= 6) {
                        const key = irbExpiryBucketKey(exp);
                        if (!key) return;
                        group[key] = (group[key] || 0) + 1;
                    }
                });
                const bucketKeys = Object.keys(group).sort();
                const labels = bucketKeys.map(irbExpiryBucketLabel);
                const chart = new Chart(document.getElementById('chartIRBExpiry'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'IRB ใกล้หมดอายุ',
                            data: bucketKeys.map((key) => group[key]),
                            backgroundColor: '#ef4444'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
                chart.$irbExpiryKeys = bucketKeys;
            })();



            // 8) การร่วมมือ (Collaboration) แบ่งตามหน่วยงาน — robust + data labels + เช็ค toggle legend
            (function() {
                // --- หมวดหมู่ ---
                const CATS = [
                    'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
                    'โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ',
                    'โครงการวิจัยนอกคณะ'
                ];

                // --- helper: ปีจาก project ---
                function getYear(p) {
                    return p?.start_year ? String(p.start_year) : 'ไม่ระบุ';
                }

                // --- normalize project_list -> category ---
                function normalizeUnitStatus(raw) {
                    const s = (raw ?? '').toString().trim();
                    if (!s) return null;
                    if (s.includes('ภายในหน่วยวิจัย') && s.includes('สุขภา')) return CATS[0];
                    if (s.includes('นอกหน่วย') && s.includes('คณะ')) return CATS[1];
                    if (s.includes('วิจัยนอกคณะ')) return CATS[2];
                    return null;
                }

                // --- list ปี ---
                const rawYears = [...new Set(projects.map(getYear))];
                const isNum = v => /^\d+$/.test(v);
                const years = rawYears
                    .filter(isNum).map(Number).sort((a, b) => a - b).map(String)
                    .concat(rawYears.filter(y => !isNum(y)).sort());

                // --- เตรียม object เก็บนับ ---
                const byYearCat = {};
                years.forEach(y => byYearCat[y] = Object.fromEntries(CATS.map(c => [c, 0])));

                // --- loop projects ---
                projects.forEach(p => {
                    const y = getYear(p);
                    const cat = normalizeUnitStatus(p.project_list);
                    if (!cat) return;
                    if (!byYearCat[y]) byYearCat[y] = Object.fromEntries(CATS.map(c => [c, 0]));
                    byYearCat[y][cat] += 1;
                });

                // --- datasets ---
                const datasets = CATS.map((c, i) => ({
                    label: c,
                    data: years.map(y => byYearCat[y]?.[c] ?? 0),
                    backgroundColor: `hsl(${(i*60)%360} 70% 60%)`,
                    stack: 'collab'
                }));

                // --- plugin แสดง data label ---
                const dataLabelPlugin = {
                    id: 'dataLabelPluginCollab',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                top,
                                right
                            }
                        } = chart;
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillStyle = '#111827';

                        chart.data.datasets.forEach((ds, dsIndex) => {
                            // ข้ามถ้า legend toggle ปิดอยู่
                            if (!chart.isDatasetVisible(dsIndex)) return;
                            const meta = chart.getDatasetMeta(dsIndex);
                            meta.data.forEach((bar, i) => {
                                if (!bar || bar.hidden) return;
                                const v = Number(ds.data[i] || 0);
                                if (!isFinite(v) || v <= 0) return;
                                let x = bar.x;
                                let y = bar.y - 3;
                                if (y < chart.chartArea.top + 8) y = chart.chartArea.top + 8;
                                if (x > right - 2) x = right - 2;
                                ctx.fillText(String(v), x, y);
                            });
                        });

                        ctx.restore();
                    }
                };

                // --- render chart ---
                const el = document.getElementById('chartCollabBreakdown');
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: years,
                        datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 16
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    footer: (items) => {
                                        try {
                                            const i = items[0].dataIndex;
                                            const sum = CATS.reduce((s, c) => s + (byYearCat[years[i]]?.[c] ??
                                                0), 0);
                                            return `รวมปีนี้: ${sum}`;
                                        } catch {
                                            return '';
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'ปี'
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grace: '12%',
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            }
                        }
                    },
                    plugins: [dataLabelPlugin]
                });
            })();


            /* ---------- 9) จำนวน PI ต่อหน่วยงาน (Chart.js bar แนวนอน + กันตัวเลขปลายแท่งโดนตัด) ---------- */
            (function() {
                const TOP_N = 20;
                const PI_COLORS = ['#4f7ac8', '#4fbf5f', '#d4a153', '#ef4444', '#7b5ac8', '#3cb9c8', '#0ea5e9'];
                const PI_LABEL_LIMIT = 32;

                function shorten(text, max = PI_LABEL_LIMIT) {
                    const s = String(text || '').trim();
                    return s.length > max ? `${s.slice(0, max - 1)}…` : s;
                }

                const valueLabelRight = {
                    id: 'valueLabelRightPi',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                left,
                                right,
                                top,
                                bottom
                            },
                            scales: {
                                x: scaleX
                            }
                        } = chart;
                        const ds = chart.data.datasets?.[0];
                        const meta = chart.getDatasetMeta(0);
                        if (!ds || !meta?.data?.length) return;

                        ctx.save();
                        ctx.font = '700 11px "IBM Plex Sans Thai", sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textBaseline = 'middle';
                        ctx.textAlign = 'left';

                        meta.data.forEach((bar, i) => {
                            const v = Number(ds.data?.[i] || 0);
                            if (!(v > 0)) return;

                            const text = String(v);
                            const textWidth = ctx.measureText(text).width;
                            let drawX = scaleX.getPixelForValue(v) + 8;
                            const maxX = right - textWidth - 2;
                            if (drawX > maxX) drawX = maxX;
                            if (drawX < left + 2) drawX = left + 2;

                            let drawY = bar.y;
                            if (drawY < top + 10) drawY = top + 10;
                            if (drawY > bottom - 10) drawY = bottom - 10;

                            ctx.fillText(text, drawX, drawY);
                        });

                        ctx.restore();
                    }
                };

                function renderPiByUnitChart(rows) {
                    const sourceRows = Array.isArray(rows) ? rows : (Array.isArray(window.__dashboardCurrentRows) ? window.__dashboardCurrentRows : projects);
                    const filteredRows = sourceRows.filter((p) => isPiInPolicyUnit(p));
                    const countByPI = {};
                    filteredRows.forEach((p) => {
                        const name = (p.pi_name || '').toString().trim();
                        if (!name || name === 'ไม่ระบุ') return;
                        countByPI[name] = (countByPI[name] || 0) + 1;
                    });

                    const top = Object.entries(countByPI)
                        .sort((a, b) => b[1] - a[1])
                        .slice(0, TOP_N);

                    const labels = top.map(([pi]) => pi).reverse();
                    const values = top.map(([, count]) => count).reverse();
                    const chartLabels = labels.length ? labels : ['ไม่มีข้อมูล'];
                    const chartValues = values.length ? values : [0];

                    const canvas = document.getElementById('chartPIsPerUnit');
                    if (!canvas) return;
                    if (!values.length || !values.some((value) => Number(value) > 0)) {
                        drawEmptyChart(canvas);
                        return;
                    }
                    const targetHeight = Math.min(360, Math.max(240, chartLabels.length * 20));
                    canvas.setAttribute('height', String(targetHeight));
                    window.applyChartCanvasSizing?.();

                    const prev = Chart.getChart(canvas);
                    if (prev) prev.destroy();

                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                label: 'จำนวนโครงการ',
                                data: chartValues,
                                backgroundColor: chartLabels.map((_, i) => PI_COLORS[i % PI_COLORS.length]),
                                borderRadius: 8,
                                barThickness: 22,
                                categoryPercentage: 0.74,
                                barPercentage: 0.92
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 6,
                                    bottom: 6,
                                    right: 76
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` จำนวนโครงการ: ${ctx.raw || 0}`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grace: '18%',
                                    grid: {
                                        color: 'rgba(148,163,184,0.22)'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        autoSkip: false,
                                        padding: 8,
                                        callback: (value, index) => shorten(chartLabels[index] || value)
                                    }
                                }
                            }
                        },
                        plugins: [valueLabelRight]
                    });
                }

                window.renderPiByUnitChart = renderPiByUnitChart;
                renderPiByUnitChart(typeof getFilteredProjects === 'function' ? getFilteredProjects() : projects);
            })();


            /* ---------- 10) ทุนแยกตามแหล่งทุน (Top 5 + อื่นๆ) แบบโดนัท รายละเอียดเหมือนเดิม ---------- */
            (function() {
                const TOP_N_SOURCES = 5;
                const FUNDING_COLORS = ['#dc4b4b', '#d4a153', '#9acb4b', '#4fbf5f', '#3cb9c8', '#5f88d6', '#7b5ac8'];

                function summarizeFundingSources(rows, targetType) {
                    const countBySrc = {};
                    const budgetBySrc = {};

                    (rows || []).forEach((p) => {
                        if ((getFundingType(p) || '').trim() !== targetType) return;
                        const src = (getFundingSource(p) || 'ไม่ระบุ').toString().trim();
                        const budget = Number(getBudget(p)) || 0;
                        countBySrc[src] = (countBySrc[src] || 0) + 1;
                        budgetBySrc[src] = (budgetBySrc[src] || 0) + budget;
                    });

                    const entries = Object.keys(countBySrc).map((label) => ({
                        label,
                        count: countBySrc[label],
                        budget: budgetBySrc[label]
                    })).sort((a, b) => (b.count - a.count) || (b.budget - a.budget));

                    const top = entries.slice(0, TOP_N_SOURCES);
                    const others = entries.slice(TOP_N_SOURCES);
                    if (others.length) {
                        top.push({
                            label: 'อื่นๆ',
                            count: others.reduce((sum, e) => sum + e.count, 0),
                            budget: others.reduce((sum, e) => sum + e.budget, 0),
                        });
                    }

                    return {
                        labels: top.map((e) => e.label),
                        counts: top.map((e) => e.count),
                        budgets: top.map((e) => e.budget),
                        others: others.map((e) => e.label),
                        topOnly: entries.slice(0, TOP_N_SOURCES).map((e) => e.label)
                    };
                }

                function buildFundingDetailPlugin(budgets, totalCount) {
                    return {
                        id: `fundingDetail_${Math.random().toString(36).slice(2, 8)}`,
                        afterDatasetsDraw(chart) {
                            const dataset = chart.data.datasets?.[0];
                            const meta = chart.getDatasetMeta(0);
                            if (!dataset || !meta?.data?.length) return;

                            const {
                                ctx,
                                chartArea: {
                                    left,
                                    right,
                                    top,
                                    bottom
                                }
                            } = chart;

                            ctx.save();
                            ctx.font = '700 11px "IBM Plex Sans Thai", sans-serif';
                            ctx.fillStyle = '#0f172a';
                            ctx.strokeStyle = 'rgba(100,116,139,0.5)';
                            ctx.lineWidth = 1;
                            ctx.textBaseline = 'middle';

                            meta.data.forEach((arc, i) => {
                                const value = Number(dataset.data?.[i] || 0);
                                if (!(value > 0)) return;

                                const labelText = `${value} (${formatThaiMoney(budgets[i] || 0)})`;
                                const textWidth = ctx.measureText(labelText).width;
                                const angle = (arc.startAngle + arc.endAngle) / 2;
                                const isRight = Math.cos(angle) >= 0;

                                const sx = arc.x + Math.cos(angle) * (arc.outerRadius - 1);
                                const sy = arc.y + Math.sin(angle) * (arc.outerRadius - 1);
                                const mx = arc.x + Math.cos(angle) * (arc.outerRadius + 10);
                                let my = arc.y + Math.sin(angle) * (arc.outerRadius + 10);
                                let tx = mx + (isRight ? 12 : -12);

                                if (isRight) {
                                    if (tx + textWidth > right - 4) tx = right - textWidth - 4;
                                    if (tx < left + 4) tx = left + 4;
                                    ctx.textAlign = 'left';
                                } else {
                                    if (tx - textWidth < left + 4) tx = left + textWidth + 4;
                                    if (tx > right - 4) tx = right - 4;
                                    ctx.textAlign = 'right';
                                }

                                if (my < top + 10) my = top + 10;
                                if (my > bottom - 10) my = bottom - 10;

                                ctx.beginPath();
                                ctx.moveTo(sx, sy);
                                ctx.lineTo(mx, my);
                                ctx.lineTo(tx + (isRight ? -4 : 4), my);
                                ctx.stroke();

                                ctx.fillText(labelText, tx, my);
                            });

                            const center = meta.data[0];
                            if (center) {
                                ctx.textAlign = 'center';
                                ctx.fillStyle = '#334155';
                                ctx.font = '700 12px "IBM Plex Sans Thai", sans-serif';
                                ctx.fillText(`รวม ${totalCount}`, center.x, center.y);
                            }

                            ctx.restore();
                        }
                    };
                }

                function renderFundingSourceChart(chartId, targetType, groupKey, rows) {
                    const canvas = document.getElementById(chartId);
                    if (!canvas) return;

                    const sourceRows = Array.isArray(rows) ? rows : [];
                    const summary = summarizeFundingSources(sourceRows, targetType);
                    const totalCount = summary.counts.reduce((sum, n) => sum + n, 0);

                    window.fundingSourceGroups = window.fundingSourceGroups || {};
                    window.fundingSourceGroups[groupKey] = {
                        top: summary.topOnly,
                        others: summary.others
                    };

                    const prev = Chart.getChart(canvas);
                    if (prev) prev.destroy();

                    if (!summary.labels.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    new Chart(canvas, {
                        type: 'doughnut',
                        data: {
                            labels: summary.labels,
                            datasets: [{
                                data: summary.counts,
                                backgroundColor: summary.labels.map((_, i) => FUNDING_COLORS[i % FUNDING_COLORS.length]),
                                borderColor: '#ffffff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '55%',
                            layout: {
                                padding: {
                                    left: 24,
                                    right: 24,
                                    top: 12,
                                    bottom: 12
                                }
                            },
                            plugins: {
                                legend: buildMobileLegend(12),
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const i = ctx.dataIndex;
                                            const count = Number(summary.counts[i] || 0);
                                            const budget = Number(summary.budgets[i] || 0);
                                            const pct = totalCount ? ((count / totalCount) * 100).toFixed(1) : '0.0';
                                            return ` ${ctx.label}: ${count} งาน | ${formatThaiMoney(budget)} (${pct}%)`;
                                        }
                                    }
                                }
                            }
                        },
                        plugins: [buildFundingDetailPlugin(summary.budgets, totalCount)]
                    });
                }

                function getRowsForRender() {
                    if (Array.isArray(window.__dashboardCurrentRows)) return window.__dashboardCurrentRows;
                    if (typeof getFilteredProjects === 'function') return getFilteredProjects();
                    return projects;
                }

                function renderAllFundingSourceCharts(rows) {
                    const baseRows = Array.isArray(rows) ? rows : getRowsForRender();
                    renderFundingSourceChart('chartExternalBySource', 'ภายนอก', 'external', baseRows);
                    renderFundingSourceChart('chartInternalBySource', 'ภายใน', 'internal', baseRows);
                }

                window.renderFundingSourceCharts = renderAllFundingSourceCharts;
                renderAllFundingSourceCharts(getRowsForRender());
            })();

            // special graphs below here
            /* ---- Helper: คุมสีตามแหล่งทุน ---- */
            const colorCache = new Map();

            function colorForKey(key) {
                if (colorCache.has(key)) return colorCache.get(key);
                let h = 0;
                for (const ch of String(key)) h = (h * 31 + ch.charCodeAt(0)) >>> 0;
                const hue = h % 360;
                const c = `hsl(${hue} 70% 60%)`;
                colorCache.set(key, c);
                return c;
            }

            // ✅ ปลั๊กอิน: แสดงยอดรวมบนสุดของแต่ละปี (stack bar)
            const stackTotalLabelPlugin = {
                id: 'stackTotalLabelPlugin',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            top,
                            right
                        }
                    } = chart;
                    const isFundingYearChart = ['chartExternalBySourceYear', 'chartInternalBySourceYear'].includes(chart?.canvas?.id);
                    const rotateFundingYearLabel = isFundingYearChart && isPhoneViewport();
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.fillStyle = '#111';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';

                    chart.data.labels.forEach((label, i) => {
                        // 1) รวมค่าของทุก dataset ใน stack ที่ตำแหน่ง i
                        let sum = 0;
                        chart.data.datasets.forEach((ds, dsIndex) => {
                            if (!chart.isDatasetVisible(dsIndex)) return;
                            sum += Number(ds.data[i] || 0);
                        });
                        if (sum <= 0) return;

                        // 2) หาแท่งบนสุดของ stack
                        let topBar = null;
                        chart.data.datasets.forEach((ds, dsIndex) => {
                            if (!chart.isDatasetVisible(dsIndex)) return;
                            const el = chart.getDatasetMeta(dsIndex).data[i];
                            if (el && (!topBar || el.y < topBar.y)) {
                                topBar = el;
                            }
                        });

                        // 3) วาดผลรวมเหนือแท่งบนสุด
                        if (topBar) {
                            let {
                                x,
                                y
                            } = topBar.tooltipPosition();
                            y -= 4;
                            if (y < top + 8) y = top + 8;
                            if (x > right - 2) x = right - 2;
                            const label = formatThaiMoney(sum);
                            if (rotateFundingYearLabel) {
                                ctx.save();
                                ctx.translate(x, y);
                                ctx.rotate(-Math.PI / 2);
                                ctx.textAlign = 'left';
                                ctx.textBaseline = 'middle';
                                ctx.fillText(label, 0, 0);
                                ctx.restore();
                            } else {
                                ctx.fillText(label, x, y);
                            }
                        }
                    });

                    ctx.restore();
                }
            };


            /* ---- กราฟแท่งรายปี (ทุนภายนอก/ภายใน) ---- */
            (function() {
                const agg = {
                    external: {},
                    internal: {}
                };

                projects.forEach(p => {
                    const type = (getFundingType(p) || '').trim();
                    const src = (getFundingSource(p) || 'ไม่ระบุ').trim();
                    const amt = Number(getBudget(p)) || 0;
                    const y = yearCE(p.start_year ?? p.year ?? p.funding_year);
                    if (!y) return;

                    const bucket = type === 'ภายนอก' ? agg.external : (type === 'ภายใน' ? agg.internal : null);
                    if (!bucket) return;
                    bucket[y] ??= {};
                    bucket[y][src] ??= {
                        budget: 0
                    };
                    bucket[y][src].budget += amt;
                });

                // ✅ ปลั๊กอินวาด data label บนแท่ง (ใช้ formatThaiMoney)
                const dataLabelPluginBudget = {
                    id: 'dataLabelPluginBudget',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                top,
                                right
                            }
                        } = chart;
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        chart.data.datasets.forEach((dataset, dsIndex) => {
                            // เคารพ legend toggle: ถ้าชุดนี้ถูกซ่อน ให้ข้าม
                            if (!chart.isDatasetVisible(dsIndex)) return;

                            const meta = chart.getDatasetMeta(dsIndex);
                            meta.data.forEach((bar, i) => {
                                if (!bar || bar.hidden) return;
                                const val = Number(dataset.data?.[i] || 0);
                                if (!(isFinite(val) && val > 0)) return;

                                // ตำแหน่งเหนือปลายแท่งเล็กน้อย
                                let {
                                    x,
                                    y
                                } = bar.tooltipPosition();
                                y -= 4;

                                // กันตกขอบบน/ขวา (เหมือนกราฟอื่น ๆ)
                                const minY = top + 8;
                                if (y < minY) y = minY;
                                if (x > right - 2) x = right - 2;

                                ctx.fillText(formatThaiMoney(val), x, y);
                            });
                        });

                        ctx.restore();
                    }
                };

                function buildStacked(canvasId, dataByYear) {
                    const el = document.getElementById(canvasId);
                    if (!el) return;

                    const years = Object.keys(dataByYear).map(Number).sort((a, b) => a - b);
                    if (!years.length) {
                        const c = el.getContext('2d');
                        c.font = '14px sans-serif';
                        c.fillStyle = '#6b7280';
                        c.textAlign = 'center';
                        c.textBaseline = 'middle';
                        c.fillText('ไม่มีข้อมูล', el.width / 2, el.height / 2);
                        return;
                    }

                    const allSources = Array.from(new Set(years.flatMap(y => Object.keys(dataByYear[y]))));
                    const totals = allSources.map(src => ({
                        src,
                        total: years.reduce((s, y) => s + (dataByYear[y][src]?.budget || 0), 0)
                    }));
                    totals.sort((a, b) => b.total - a.total);

                    const TOP_N_SOURCES = 8;
                    const topSources = totals.slice(0, TOP_N_SOURCES).map(t => t.src);
                    const otherSources = totals.slice(TOP_N_SOURCES).map(t => t.src);

                    const datasets = topSources.map(src => ({
                        label: src,
                        data: years.map(y => dataByYear[y][src]?.budget || 0),
                        backgroundColor: colorForKey(src),
                        stack: 'budget',
                    }));

                    if (otherSources.length) {
                        datasets.push({
                            label: 'อื่นๆ',
                            data: years.map(y => otherSources.reduce((s, src) => s + (dataByYear[y][src]?.budget || 0), 0)),
                            backgroundColor: '#94a3b8',
                            stack: 'budget'
                        });
                    }

                    window.fundingSourceYearGroups = window.fundingSourceYearGroups || {};
                    window.fundingSourceYearGroups[canvasId] = {
                        top: topSources,
                        others: otherSources
                    };

                    const prev = Chart.getChart(el);
                    if (prev) prev.destroy();

                    new Chart(el, {
                        type: 'bar',
                        data: {
                            labels: years.map(yearLabelTH),
                            datasets
                        },
                        options: {
                            responsive: true,
                            layout: {
                                padding: {
                                    top: isPhoneViewport() ? 54 : 18
                                }
                            },
                            plugins: {
                                legend: buildMobileLegend(12),
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const yCE = years[ctx.dataIndex];
                                            const money = ctx.parsed.y || 0;
                                            const total = Object.values(dataByYear[yCE]).reduce((s, v) => s + (v
                                                ?.budget || 0), 0);
                                            const pct = total ? ((money / total) * 100).toFixed(1) : '0.0';
                                            return ` ${ctx.dataset.label}: ${formatThaiMoney(money)} (${pct}%)`;
                                        },
                                        footer: (items) => {
                                            if (!items?.length) return '';
                                            const yCE = years[items[0].dataIndex];
                                            const sum = Object.values(dataByYear[yCE]).reduce((s, v) => s + (v
                                                ?.budget || 0), 0);
                                            return `รวมปี ${yearLabelTH(yCE)}: ${formatThaiMoney(sum)}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: buildMobileCategoryTicks(8, 4)
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    ticks: {
                                        callback: v => formatThaiMoney(v)
                                    }
                                }
                            }
                        },
                        plugins: [stackTotalLabelPlugin] // ✅ ใส่ปลั๊กอินตรงนี้
                    });
                }

                buildStacked('chartExternalBySourceYear', agg.external);
                buildStacked('chartInternalBySourceYear', agg.internal);
            })();



            // emd special graphs

            /* ---------- 11) โครงการทั้งหมด/ใหม่/กำลังดำเนินการ/ปิดโปรเจค/ยกเลิก (ต่อปี) ---------- */
            (function() {
                const unknownBucketKey = '__unknown__';
                const unknownBucketLabel = 'ไม่ระบุปี';
                const unknownProjects = projects.filter((p) => !Number.isFinite(projectLifecycleStartYearCE(p)));
                const startYears = projects
                    .map((p) => projectLifecycleStartYearCE(p))
                    .filter((y) => Number.isFinite(y));
                if (!startYears.length && !unknownProjects.length) return;

                const eventYears = projects
                    .filter((p) => {
                        const key = projectLifecycleStatusKey(p);
                        return key === 'completed' || key === 'cancelled';
                    })
                    .map((p) => projectLifecycleEventYearCE(p))
                    .filter((y) => Number.isFinite(y));

                const currentYearCE = new Date().getFullYear();
                const maxYearCE = startYears.length
                    ? Math.max(...startYears, ...eventYears, currentYearCE)
                    : currentYearCE;
                window.yearlyLifecycleHorizonCE = maxYearCE;

                const yearsCE = [];
                if (startYears.length) {
                    const minYearCE = Math.min(...startYears);
                    for (let y = minYearCE; y <= maxYearCE; y += 1) yearsCE.push(y);
                }
                const buckets = [...yearsCE, ...(unknownProjects.length ? [unknownBucketKey] : [])];
                const yearLabels = buckets.map((bucket) => bucket === unknownBucketKey ? unknownBucketLabel : yearLabelTH(bucket));
                window.yearlyNewVsTotalLabelMap = {};
                yearLabels.forEach((label, idx) => {
                    window.yearlyNewVsTotalLabelMap[label] = buckets[idx];
                });

                const totalPerYear = {};
                const newPerYear = {};
                const inProgressPerYear = {};
                const completedPerYear = {};
                const cancelledPerYear = {};

                yearsCE.forEach(y => {
                    totalPerYear[y] = projects.filter(p => {
                        const startY = projectLifecycleStartYearCE(p);
                        if (Number.isFinite(startY)) return startY <= y;
                        return y === maxYearCE;
                    }).length;
                    newPerYear[y] = projects.filter(p => projectLifecycleBucketAtYear(p, y, maxYearCE) === 'new').length;
                    inProgressPerYear[y] = projects.filter(p => projectLifecycleBucketAtYear(p, y, maxYearCE) === 'in_progress').length;
                    completedPerYear[y] = projects.filter(p => projectLifecycleBucketAtYear(p, y, maxYearCE) === 'completed').length;
                    cancelledPerYear[y] = projects.filter(p => projectLifecycleBucketAtYear(p, y, maxYearCE) === 'cancelled').length;
                });
                if (unknownProjects.length) {
                    totalPerYear[unknownBucketKey] = unknownProjects.length;
                    newPerYear[unknownBucketKey] = 0;
                    inProgressPerYear[unknownBucketKey] = unknownProjects.filter((p) => projectLifecycleStatusKey(p) === 'in_progress').length;
                    completedPerYear[unknownBucketKey] = unknownProjects.filter((p) => projectLifecycleStatusKey(p) === 'completed').length;
                    cancelledPerYear[unknownBucketKey] = unknownProjects.filter((p) => projectLifecycleStatusKey(p) === 'cancelled').length;
                }

                // ปลั๊กอินโชว์เลข + เช็คการซ่อน + กันตกขอบบน/ขวา
                const valueLabelBar = {
                    id: 'valueLabelBar',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                top,
                                right
                            }
                        } = chart;
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        const totalIndex = chart.data.datasets.findIndex(d => String(d.label || '').trim() === 'ทั้งหมด');
                        if (totalIndex >= 0 && chart.isDatasetVisible(totalIndex)) {
                            const dataset = chart.data.datasets[totalIndex];
                            const meta = chart.getDatasetMeta(totalIndex);
                            meta.data.forEach((point, index) => {
                                if (!point || point.hidden) return;
                                const val = dataset.data[index];
                                if (!isFinite(val) || val <= 0) return;

                                let drawX = point.x;
                                let drawY = point.y - 10;

                                const minY = top + 10;
                                if (drawY < minY) drawY = minY;
                                if (drawX > right - 6) drawX = right - 6;

                                ctx.fillText(val, drawX, drawY);
                            });
                        }

                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartYearlyNewVsTotal');
                if (!el) return;
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: yearLabels,
                        datasets: [{
                                label: 'ทั้งหมด',
                                data: buckets.map((bucket) => totalPerYear[bucket] || 0),
                                type: 'line',
                                borderColor: '#60a5fa',
                                backgroundColor: '#60a5fa',
                                pointBackgroundColor: '#60a5fa',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                pointRadius: 4,
                                pointHoverRadius: 7,
                                pointHitRadius: 16,
                                borderWidth: 3,
                                tension: 0.25,
                                fill: false,
                                order: 0,
                                stack: 'totalLine'
                            },
                            {
                                label: 'ใหม่',
                                data: buckets.map((bucket) => newPerYear[bucket] || 0),
                                backgroundColor: '#34d399',
                                stack: 'status',
                                order: 1
                            },
                            {
                                label: 'กำลังดำเนินการ',
                                data: buckets.map((bucket) => inProgressPerYear[bucket] || 0),
                                backgroundColor: '#fbbf24',
                                stack: 'status',
                                order: 1
                            },
                            {
                                label: 'ปิดโปรเจค',
                                data: buckets.map((bucket) => completedPerYear[bucket] || 0),
                                backgroundColor: '#ef4444',
                                stack: 'status',
                                order: 1
                            },
                            {
                                label: 'ยกเลิกโปรเจค',
                                data: buckets.map((bucket) => cancelledPerYear[bucket] || 0),
                                backgroundColor: '#f97316',
                                stack: 'status',
                                order: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        datasets: {
                            bar: {
                                barPercentage: 0.72,
                                categoryPercentage: 0.74,
                                maxBarThickness: 34
                            }
                        },
                        layout: {
                            padding: {
                                top: 16,
                                right: 16
                            }
                        }, // ← เผื่อพื้นที่ด้านบนให้ label
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 18,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                stacked: true,
                                grace: '12%', // ← headroom กันเลขชนขอบบน
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            },
                            x: {
                                stacked: true,
                                offset: true,
                                grid: {
                                    drawBorder: false
                                },
                                ticks: {
                                    maxRotation: 0,
                                    minRotation: 0,
                                    padding: 8
                                },
                                title: {
                                    display: true,
                                    text: 'ปี (พ.ศ.)'
                                }
                            }
                        }
                    },
                    plugins: [valueLabelBar]
                });
            })();


            /* ---------- 12) FTE Bubble (ชื่อ → โครงการ/รวม FTE) ---------- */
            (function() {
                const datasets = buildFteSummaryDatasets(ftes);
                new Chart(document.getElementById('chartFteSummary'), {
                    type: 'bubble',
                    data: {
                        datasets
                    },
                    options: {
                        responsive: true,
                        layout: {
                            padding: {
                                top: 18,
                                right: 18,
                                bottom: 12,
                                left: 12
                            }
                        },
                        plugins: {
                            tooltip: {
                                displayColors: false,
                                callbacks: {
                                    label: (ctx) =>
                                        `${ctx.dataset.label}: โครงการ ${ctx.raw.x} / FTE ${ctx.raw.y.toFixed(2)}`
                                }
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'FTE รวม'
                                }
                            }
                        }
                    },
                    plugins: [fteAvatarBubblePlugin]
                });
            })();

            /* ---------- NEW: 13) Red Zone Timeline + Overdue Table + Co-Research Window ---------- */
            (function() {
                const now = new Date();
                const overdueProjects = buildOverdueRecords(projects, now);
                const activeOverdueProjects = overdueProjects.filter(record =>
                    record.sourceCategory === 'open_system_late' || record.sourceCategory === 'open_team_late'
                );
                const redZoneYears = getRedZoneRecentYears(now);
                const labels = redZoneYears.map(year => yearLabelTH(year));
                const timelineDatasets = [
                    {
                        label: 'ครบกำหนดแต่ยังไม่ลงปิดโปรเจคในระบบ',
                        redzoneSourceCategory: 'open_system_late',
                        data: redZoneYears.map((year) => overdueProjects.filter((record) =>
                            record.contractEndYearCE === year &&
                            record.sourceCategory === 'open_system_late'
                        ).length),
                        backgroundColor: '#dc2626',
                    },
                    {
                        label: 'ทำงานเกินสัญญาและยังไม่ปิดโปรเจค',
                        redzoneSourceCategory: 'open_team_late',
                        data: redZoneYears.map((year) => overdueProjects.filter((record) =>
                            record.contractEndYearCE === year &&
                            record.sourceCategory === 'open_team_late'
                        ).length),
                        backgroundColor: '#d97706',
                    },
                    {
                        label: 'ทำงานเกินสัญญาแต่ปิดโปรเจคแล้ว',
                        redzoneSourceCategory: 'closed_late',
                        data: redZoneYears.map((year) => overdueProjects.filter((record) =>
                            record.contractEndYearCE === year &&
                            record.sourceCategory === 'closed_late'
                        ).length),
                        backgroundColor: '#7c3aed',
                    }
                ];

                const elTimeline = document.getElementById('chartRedZoneTimeline');
                if (elTimeline) {
                    Chart.getChart(elTimeline)?.destroy();
                    const hasTimelineData = timelineDatasets.some((dataset) =>
                        Array.isArray(dataset.data) && dataset.data.some((value) => Number(value) > 0)
                    );
                    if (!hasTimelineData) {
                        drawEmptyChart(elTimeline);
                    } else {
                    const barValuePlugin = {
                        id: 'barValueRedZoneStacked',
                        afterDatasetsDraw(chart) {
                            const {
                                ctx,
                                chartArea: {
                                    top,
                                    right
                                }
                            } = chart;
                            ctx.save();
                            ctx.font = 'bold 11px sans-serif';
                            ctx.fillStyle = '#111827';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';

                            chart.data.datasets.forEach((ds, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                chart.getDatasetMeta(dsIndex)?.data?.forEach((bar, i) => {
                                    const value = Number(ds.data?.[i] || 0);
                                    if (!bar || !isFinite(value) || value <= 0) return;
                                    let { x, y } = bar.tooltipPosition();
                                    y -= 4;
                                    if (y < top + 8) y = top + 8;
                                    if (x > right - 2) x = right - 2;
                                    ctx.fillText(String(value), x, y);
                                });
                            });

                            ctx.restore();
                        }
                    };
                    new Chart(elTimeline, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: timelineDatasets
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                title: {
                                    display: true,
                                    text: 'ย้อนหลัง 3 ปี: แยกตามประเภทโครงการเกินกำหนด'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw} โครงการ`
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grace: '12%',
                                    title: {
                                        display: true,
                                        text: 'จำนวนโครงการ'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'ปีสิ้นสุดสัญญา (BC103)'
                                    }
                                }
                            }
                        },
                        plugins: [barValuePlugin]
                    });
                    }
                }

                // --- Chart 2: Overdue Projects (Gauge) ---
                const elOverdue = document.getElementById('chartOverdueProjects');
                if (elOverdue) {
                    const overviewGroups = buildOverdueOverviewSummary(projects, now);
                    const chartGroups = overviewGroups.filter((group) => group.count > 0);
                    const totalProjectsRaw = chartGroups.reduce((sum, group) => sum + group.count, 0);
                    const totalProjects = totalProjectsRaw || 1;
                    elOverdue.$overdueOverviewKeys = chartGroups.map((group) => group.key);
                    renderInlineLegend('chartOverdueLegend', chartGroups);

                    // วาดเส้นกิ่งชี้ + ตัวเลขด้านนอกโดนัท
                    const overdueCalloutPlugin = createDoughnutCalloutPlugin('doughnutCalloutOverdue');
                    const overdueValuePlugin = createDoughnutValueLabelPlugin('doughnutValueOverdue');

                    new Chart(elOverdue, {
                        type: 'doughnut',
                        data: {
                            labels: chartGroups.map((group) => group.label),
                            datasets: [{
                                data: chartGroups.map((group) => group.count),
                                backgroundColor: chartGroups.map((group) => group.color),
                                borderColor: '#ffffff',
                                borderWidth: 2,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            cutout: '64%',
                            layout: {
                                padding: {
                                    left: 36,
                                    right: 36,
                                    top: 36,
                                    bottom: 44
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${ctx.label}: ${ctx.parsed || 0} โครงการ`
                                    }
                                }
                            }
                        },
                        plugins: isPhoneViewport() ? [overdueValuePlugin] : [overdueCalloutPlugin]
                    });
                }

                // --- Overdue Table ---
                const overdueBadge = document.getElementById('overdueCountBadge');
                if (overdueBadge) {
                    overdueBadge.textContent = `${activeOverdueProjects.length} โครงการ`;
                }

                const tbody = document.getElementById('overdueTableBody');
                const overduePrev = document.getElementById('overduePrev');
                const overdueNext = document.getElementById('overdueNext');
                const overduePagination = document.getElementById('overduePagination');
                let overduePage = 1;
                const overduePageSize = 10;

                function renderOverdueTable() {
                    if (!tbody) return;
                    const sorted = activeOverdueProjects.slice().sort((a, b) => {
                        if (b.overdueDays !== a.overdueDays) return b.overdueDays - a.overdueDays;
                        return a.contractEndDate - b.contractEndDate;
                    });
                    const total = sorted.length;
                    const totalPages = Math.max(Math.ceil(total / overduePageSize), 1);
                    overduePage = Math.min(Math.max(overduePage, 1), totalPages);

                    const start = (overduePage - 1) * overduePageSize;
                    const pageRows = sorted.slice(start, start + overduePageSize);

                    tbody.innerHTML = '';
                    if (!pageRows.length) {
                        tbody.innerHTML =
                            `<tr class="border-t"><td colspan="6" class="p-3 text-center text-gray-500">ไม่มีโครงการที่เกินกำหนด</td></tr>`;
                    } else {
                        pageRows.forEach(item => {
                            const row = document.createElement('tr');
                            row.className = 'border-t';
                            row.innerHTML = `
                                <td class="p-2 text-left text-blue-700 hover:underline" data-label="โครงการ">
                                    <a href="/rm/projects/${item.project.id}" target="_blank" rel="noopener">${item.project.project_name_th || '-'}</a>
                                    <div class="mt-1 text-[11px] font-normal text-slate-500">${overdueSourceLabel(item)}</div>
                                </td>
                                <td class="p-2 text-center" data-label="วันที่สิ้นสุดสัญญา (พ.ศ.)">${overdueRecordEndDateLabel(item)}</td>
                                <td class="p-2 text-center" data-label="วันที่สิ้นสุดการทำงานทีม">${overdueRecordTeamEndDateLabel(item)}</td>
                                <td class="p-2 text-center text-red-600 font-semibold" data-label="เกินกำหนด (วัน)">
                                    <div>${item.overdueDays}</div>
                                </td>
                                <td class="p-2 text-center" data-label="สถานะโครงการ">${item.statusLabel || '-'}</td>
                                <td class="p-2 text-center" data-label="PI">${item.project.pi_name || '-'}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    }

                    if (overduePagination) overduePagination.textContent = `หน้า ${overduePage} / ${totalPages}`;
                    if (overduePrev) overduePrev.disabled = overduePage <= 1;
                    if (overdueNext) overdueNext.disabled = overduePage >= totalPages;
                }

                overduePrev?.addEventListener('click', () => {
                    overduePage = Math.max(overduePage - 1, 1);
                    renderOverdueTable();
                });
                overdueNext?.addEventListener('click', () => {
                    overduePage = overduePage + 1;
                    renderOverdueTable();
                });

                renderOverdueTable();

                // --- Co-Research by Unit within 3/6/12 months ---
                const unitLabel = (c) => coResearchUnitLabel(c);

                const unitLabels = new Set();
                projects.forEach(p => {
                    const endDate = parseProjectDate(getEndDateRaw(p));
                    if (!endDate) return;
                    const diffMonths = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24 * 30.4375);
                    if (diffMonths < 0 || diffMonths > 12) return;
                    const list = uniqueCoResearch(getCoResearchList(p));
                    list.forEach(c => unitLabels.add(unitLabel(c)));
                });
                const unitList = [...unitLabels].sort();

                const countByUnitWithin = (unit, maxMonths) => {
                    let total = 0;
                    projects.forEach(p => {
                        const endDate = parseProjectDate(getEndDateRaw(p));
                        if (!endDate) return;
                        const diffMonths = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24 * 30.4375);
                        if (diffMonths < 0 || diffMonths > maxMonths) return;
                        const members = coResearchMembers(p).filter(m => m.unitLabel === unit);
                        if (members.length) total += 1;
                    });
                    return total;
                };

                const elCoResearch = document.getElementById('chartCoResearchByUnitWindow');
                if (elCoResearch && unitList.length) {
                    const barValuePlugin = {
                        id: 'barValueCoResearch',
                        afterDatasetsDraw(chart) {
                            const {
                                ctx,
                                chartArea: {
                                    top,
                                    right
                                }
                            } = chart;
                            ctx.save();
                            ctx.font = 'bold 11px sans-serif';
                            ctx.fillStyle = '#111827';
                            const horizontal = chart.options?.indexAxis === 'y';
                            ctx.textAlign = horizontal ? 'left' : 'center';
                            ctx.textBaseline = horizontal ? 'middle' : 'bottom';
                            chart.data.datasets.forEach((ds, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                const meta = chart.getDatasetMeta(dsIndex);
                                meta.data.forEach((bar, i) => {
                                    const v = Number(ds.data[i] || 0);
                                    if (!isFinite(v) || v <= 0) return;
                                    let x = horizontal ? bar.x + 6 : bar.x;
                                    let y = horizontal ? bar.y : bar.y - 3;
                                    if (!horizontal && y < top + 8) y = top + 8;
                                    if (x > right - 2) x = right - 2;
                                    ctx.fillText(String(v), x, y);
                                });
                            });
                            ctx.restore();
                        }
                    };
                    new Chart(elCoResearch, {
                        type: 'bar',
                        data: {
                            labels: unitList,
                            datasets: [{
                                    label: 'ภายใน 3 เดือน',
                                    data: unitList.map(u => countByUnitWithin(u, 3)),
                                    backgroundColor: '#0f766e'
                                },
                                {
                                    label: 'ภายใน 6 เดือน',
                                    data: unitList.map(u => countByUnitWithin(u, 6)),
                                    backgroundColor: '#c0841a'
                                },
                                {
                                    label: 'ภายใน 1 ปี',
                                    data: unitList.map(u => countByUnitWithin(u, 12)),
                                    backgroundColor: '#475569'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            indexAxis: isPhoneViewport() ? 'y' : 'x',
                            plugins: {
                                legend: buildMobileLegend(14)
                            },
                            scales: {
                                y: {
                                    ticks: isPhoneViewport() ? buildMobileHorizontalTicks(unitList, 16) : {
                                        autoSkip: false
                                    },
                                    title: {
                                        display: !isPhoneViewport()
                                    }
                                },
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    },
                                    title: {
                                        display: true,
                                        text: 'จำนวน Co-Research'
                                    }
                                }
                            }
                        },
                        plugins: [barValuePlugin]
                    });
                }
            })();

            /* ---------- IRB List (Dashboard) ---------- */
            (function() {
                const filterEl = document.getElementById('irbDashFilter');
                const tbody = document.getElementById('irbDashBody');
                const prevBtn = document.getElementById('irbDashPrev');
                const nextBtn = document.getElementById('irbDashNext');
                const pageEl = document.getElementById('irbDashPagination');
                const pageSize = 10;
                let page = 1;

                const rows = [];
                projects.forEach(p => {
                    const list = p.irbs || [];
                    list.forEach(irb => {
                        const expDate = parseProjectDate(irb.exp_date);
                        rows.push({
                            project: p,
                            code: irb.code || '-',
                            source: irb.source || '-',
                            expDate
                        });
                    });
                });

                function formatDate(d) {
                    return formatDateTH(d, '-');
                }

                function applyFilter() {
                    const mode = filterEl?.value || 'exp_3m';
                    const now = new Date();
                    let filtered = rows.slice();

                    if (mode === 'exp_3m') {
                        filtered = filtered.filter(r => {
                            if (!r.expDate) return false;
                            const diff = (r.expDate - now) / (1000 * 60 * 60 * 24);
                            return diff >= 0 && diff <= 90;
                        }).sort((a, b) => (a.expDate || 0) - (b.expDate || 0));
                    } else if (mode === 'latest') {
                        filtered = filtered
                            .filter(r => r.expDate)
                            .sort((a, b) => (b.expDate || 0) - (a.expDate || 0));
                    }

                    return filtered;
                }

                function render() {
                    if (!tbody) return;
                    const filtered = applyFilter();
                    const total = filtered.length;
                    const totalPages = Math.max(Math.ceil(total / pageSize), 1);
                    page = Math.min(Math.max(page, 1), totalPages);
                    const start = (page - 1) * pageSize;
                    const pageRows = filtered.slice(start, start + pageSize);

                    tbody.innerHTML = '';
                    if (!pageRows.length) {
                        tbody.innerHTML =
                            `<tr class="border-t"><td colspan="4" class="p-3 text-center text-gray-500">ไม่มีข้อมูล</td></tr>`;
                    } else {
                        pageRows.forEach(r => {
                            const row = document.createElement('tr');
                            row.className = 'border-t';
                            row.innerHTML = `
                                <td class="p-2 text-left text-blue-700 hover:underline" data-label="โครงการ">
                                    <a href="/rm/projects/${r.project.id}" target="_blank" rel="noopener">${r.project.project_name_th || '-'}</a>
                                </td>
                                <td class="p-2 text-left" data-label="รหัส IRB">${r.code}</td>
                                <td class="p-2 text-left" data-label="หน่วยงาน">${r.source}</td>
                                <td class="p-2 text-center" data-label="วันหมดอายุ">${formatDate(r.expDate)}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    }

                    if (pageEl) pageEl.textContent = `หน้า ${page} / ${totalPages}`;
                    if (prevBtn) prevBtn.disabled = page <= 1;
                    if (nextBtn) nextBtn.disabled = page >= totalPages;
                }

                filterEl?.addEventListener('change', () => {
                    page = 1;
                    render();
                });
                prevBtn?.addEventListener('click', () => {
                    page = Math.max(page - 1, 1);
                    render();
                });
                nextBtn?.addEventListener('click', () => {
                    page = page + 1;
                    render();
                });

                render();
            })();

            /* ---------- Export CSV/Excel (ตามตัวกรอง) ---------- */
            function csvEscape(val) {
                if (val == null) return '';
                const s = String(val).replace(/\r?\n/g, ' ').trim();
                if (/[",]/.test(s)) return `"${s.replace(/"/g, '""')}"`;
                return s;
            }

            function collectBaseKeys(list) {
                const keys = new Set();
                list.forEach(p => {
                    Object.entries(p || {}).forEach(([k, v]) => {
                        if (Array.isArray(v)) return;
                        if (v && typeof v === 'object') return;
                        keys.add(k);
                    });
                });
                return Array.from(keys);
            }

            function buildBaseKeyOrder(list) {
                const keys = collectBaseKeys(list);
                const preferred = [
                    'project_name_th',
                    'project_type',
                    'project_code',
                    'project_list',
                    'start_year',
                    'start_date',
                    'end_date',
                    'team_start_date',
                    'team_end_date',
                    'pi_name',
                    'pi_affiliation',
                    'planning_status',
                    'theme',
                    'budget',
                    'funding_type',
                    'funding_source'
                ];
                const strategyKeys = keys.filter(k => k.startsWith('strategy_')).sort();
                const core = keys.filter(k => !k.startsWith('strategy_') && k !== 'id' && k !== 'project_id' && k !== 'project_code');
                const ordered = [];

                preferred.forEach(k => {
                    if (core.includes(k)) ordered.push(k);
                });
                core.sort().forEach(k => {
                    if (!ordered.includes(k)) ordered.push(k);
                });
                return ['project_code', ...ordered, ...strategyKeys];
            }

            const detailColumns = {
                irb: ['irb_id', 'irb_code', 'irb_exp_date', 'irb_source'],
                copi: ['copi_id', 'copi_name', 'copi_affiliation'],
                coresearch: ['coresearch_id', 'coresearch_name', 'coresearch_unit_status', 'coresearch_affiliation'],
                fte: ['fte_id', 'fte_name', 'fte_role_group', 'fte_job_detail', 'fte_value', 'fte_hours',
                    'fte_task_detail', 'fte_start_date', 'fte_end_date'
                ],
                output: ['output_id', 'output_type', 'output_title', 'output_year']
            };

            function buildCsvRows(list) {
                const baseKeys = buildBaseKeyOrder(list);
                const detailHeaders = [
                    ...detailColumns.irb,
                    ...detailColumns.copi,
                    ...detailColumns.coresearch,
                    ...detailColumns.fte,
                    ...detailColumns.output
                ];
                const header = [...baseKeys, 'detail_type', ...detailHeaders];
                const rows = [header];

                list.forEach(p => {
                    const base = baseKeys.map(k => (k === 'project_code' ? (p.project_code ?? '') : (p[k] ?? '')));
                    let baseWritten = false;
                    const irbs = p.irbs || [];
                    const copis = p.copis || [];
                    const coResearches = p.co_researches || p.coResearches || [];
                    const fteRoles = p.fte_roles || p.fteRoles || [];
                    const outputsList = p.outputs || [];

                    function pushDetail(detailType, detailValues) {
                        const baseCells = baseWritten ? Array(base.length).fill('') : base;
                        rows.push([...baseCells, detailType, ...detailValues]);
                        baseWritten = true;
                    }

                    irbs.forEach(irb => {
                        pushDetail('irb', [
                            irb.id ?? '', irb.code ?? '', irb.exp_date ?? '', irb.source ?? '',
                            ...Array(detailColumns.copi.length + detailColumns.coresearch.length + detailColumns.fte.length + detailColumns.output.length).fill('')
                        ]);
                    });

                    copis.forEach(copi => {
                        pushDetail('copi', [
                            ...Array(detailColumns.irb.length).fill(''),
                            copi.id ?? '', copi.name ?? '', copi.affiliation ?? '',
                            ...Array(detailColumns.coresearch.length + detailColumns.fte.length + detailColumns.output.length).fill('')
                        ]);
                    });

                    coResearches.forEach(cr => {
                        pushDetail('coresearch', [
                            ...Array(detailColumns.irb.length + detailColumns.copi.length).fill(''),
                            cr.id ?? '', cr.name ?? '', cr.unit_status ?? '', cr.affiliation ?? '',
                            ...Array(detailColumns.fte.length + detailColumns.output.length).fill('')
                        ]);
                    });

                    fteRoles.forEach(f => {
                        pushDetail('fte', [
                            ...Array(detailColumns.irb.length + detailColumns.copi.length + detailColumns.coresearch.length).fill(''),
                            f.id ?? '', f.name ?? '', f.role_group ?? '', f.job_detail ?? '', f.fte_value ?? '',
                            f.hours ?? '', f.task_detail ?? '', f.start_date ?? '', f.end_date ?? '',
                            ...Array(detailColumns.output.length).fill('')
                        ]);
                    });

                    outputsList.forEach(o => {
                        pushDetail('output', [
                            ...Array(detailColumns.irb.length + detailColumns.copi.length + detailColumns.coresearch.length + detailColumns.fte.length).fill(''),
                            o.id ?? '', o.type ?? '', o.title ?? '', o.year ?? ''
                        ]);
                    });

                    if (!baseWritten) {
                        rows.push([
                            ...base, 'project',
                            ...Array(detailHeaders.length).fill('')
                        ]);
                    }
                });

                return rows;
            }

            function downloadCsv(rows, filename) {
                const content = "\uFEFF" + rows.map(r => r.map(csvEscape).join(",")).join("\n");
                const blob = new Blob([content], {
                    type: "text/csv;charset=utf-8;"
                });
                const url = URL.createObjectURL(blob);
                const link = Object.assign(document.createElement("a"), {
                    href: url,
                    download: filename
                });
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function exportProjectsAsCsv() {
                const filtered = getFilteredProjects();
                const rows = buildCsvRows(filtered);
                downloadCsv(rows, `${exportBaseName()}_projects.csv`);
            }

            function exportProjectsAsExcel() {
                if (typeof XLSX === 'undefined') {
                    alert('ไม่พบโมดูลสำหรับสร้างไฟล์ Excel');
                    return;
                }

                const filtered = getFilteredProjects();
                const baseKeys = buildBaseKeyOrder(filtered);

                const projectSheetRows = filtered.map(p => {
                    const row = {};
                    baseKeys.forEach(k => {
                        row[k] = k === 'project_code' ? (p.project_code ?? '') : (p[k] ?? '');
                    });
                    row.irb_count = (p.irbs || []).length;
                    row.copi_count = (p.copis || []).length;
                    row.coresearch_count = (p.co_researches || p.coResearches || []).length;
                    row.fte_count = (p.fte_roles || p.fteRoles || []).length;
                    row.output_count = (p.outputs || []).length;
                    return row;
                });

                const irbRows = filtered.flatMap(p => (p.irbs || []).map(irb => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th,
                    pi_name: p.pi_name,
                    start_year: p.start_year,
                    irb_code: irb.code ?? '',
                    irb_exp_date: irb.exp_date ?? '',
                    irb_source: irb.source ?? ''
                })));

                const coResearchRows = filtered.flatMap(p => (p.co_researches || p.coResearches || []).map(cr => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th,
                    pi_name: p.pi_name,
                    start_year: p.start_year,
                    name: cr.name ?? '',
                    unit_status: cr.unit_status ?? '',
                    affiliation: cr.affiliation ?? ''
                })));

                const copiRows = filtered.flatMap(p => (p.copis || []).map(copi => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th,
                    pi_name: p.pi_name,
                    start_year: p.start_year,
                    copi_name: copi.name ?? '',
                    copi_affiliation: copi.affiliation ?? ''
                })));

                const fteRows = filtered.flatMap(p => (p.fte_roles || p.fteRoles || []).map(f => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th,
                    pi_name: p.pi_name,
                    start_year: p.start_year,
                    name: f.name ?? '',
                    role_group: f.role_group ?? '',
                    job_detail: f.job_detail ?? '',
                    fte_value: f.fte_value ?? '',
                    hours: f.hours ?? '',
                    task_detail: f.task_detail ?? '',
                    start_date: f.start_date ?? '',
                    end_date: f.end_date ?? ''
                })));

                const outputRows = filtered.flatMap(p => (p.outputs || []).map(o => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th,
                    pi_name: p.pi_name,
                    start_year: p.start_year,
                    output_type: o.type ?? '',
                    output_title: o.title ?? '',
                    output_year: o.year ?? ''
                })));

                const projectSheetProjectIds = filtered.map(p => p.id ?? null);
                const irbProjectIds = filtered.flatMap(p => (p.irbs || []).map(() => p.id ?? null));
                const copiProjectIds = filtered.flatMap(p => (p.copis || []).map(() => p.id ?? null));
                const coResearchProjectIds = filtered.flatMap(p => (p.co_researches || p.coResearches || []).map(() => p.id ?? null));
                const fteProjectIds = filtered.flatMap(p => (p.fte_roles || p.fteRoles || []).map(() => p.id ?? null));
                const outputProjectIds = filtered.flatMap(p => (p.outputs || []).map(() => p.id ?? null));

                function toLinkedSheet(rows, projectIds) {
                    const ws = XLSX.utils.json_to_sheet(rows);
                    if (!rows.length) return ws;

                    const headers = Object.keys(rows[0] || {});
                    const codeCol = headers.indexOf('project_code');
                    if (codeCol === -1) return ws;

                    rows.forEach((_, idx) => {
                        const projectId = projectIds[idx];
                        if (!projectId) return;
                        const cellAddr = XLSX.utils.encode_cell({
                            c: codeCol,
                            r: idx + 1
                        });
                        const cell = ws[cellAddr];
                        if (!cell || !cell.v) return;
                        cell.l = {
                            Target: `${window.location.origin}/rm/projects/${projectId}`,
                            Tooltip: 'เปิดหน้าโครงการ'
                        };
                    });

                    return ws;
                }

                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, toLinkedSheet(projectSheetRows, projectSheetProjectIds), 'Projects');
                XLSX.utils.book_append_sheet(wb, toLinkedSheet(irbRows, irbProjectIds), 'IRB');
                XLSX.utils.book_append_sheet(wb, toLinkedSheet(copiRows, copiProjectIds), 'COPI');
                XLSX.utils.book_append_sheet(wb, toLinkedSheet(coResearchRows, coResearchProjectIds), 'CoResearch');
                XLSX.utils.book_append_sheet(wb, toLinkedSheet(fteRows, fteProjectIds), 'FTE');
                XLSX.utils.book_append_sheet(wb, toLinkedSheet(outputRows, outputProjectIds), 'Outputs');

                XLSX.writeFile(wb, `${exportBaseName()}_projects.xlsx`);
            }

            (function() {
                const drawerOpenBtn = document.getElementById('filterDrawerOpen');
                const drawerCloseBtn = document.getElementById('filterDrawerClose');
                const drawerOverlay = document.getElementById('filterDrawerOverlay');
                const drawer = document.getElementById('filterDrawer');
                const exportOpenBtn = document.getElementById('exportPopupOpen');
                const exportCloseBtn = document.getElementById('exportPopupClose');
                const exportOverlay = document.getElementById('exportPopupOverlay');
                const exportPopup = document.getElementById('exportPopup');
                const exportExcelAction = document.getElementById('exportExcelAction');
                const exportCsvAction = document.getElementById('exportCsvAction');
                const fabStack = document.getElementById('dashboardFabStack');
                const paginationWrap = document.getElementById('projectTablePaginationWrap');
                let scrollLockY = 0;
                let scrollLocked = false;
                let fabVisibilityFrame = null;
                const desktopMedia = window.matchMedia('(min-width: 1025px)');
                const shouldLockPageScroll = () => !desktopMedia.matches;

                const lockPageScroll = () => {
                    if (scrollLocked) return;
                    scrollLockY = window.scrollY || window.pageYOffset || 0;
                    const scrollbarWidth = Math.max(window.innerWidth - document.documentElement.clientWidth, 0);
                    document.documentElement.style.overflow = 'hidden';
                    document.body.style.overflow = 'hidden';
                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${scrollLockY}px`;
                    document.body.style.left = '0';
                    document.body.style.right = '0';
                    document.body.style.width = '100%';
                    if (scrollbarWidth > 0) {
                        document.body.style.paddingRight = `${scrollbarWidth}px`;
                    }
                    scrollLocked = true;
                };

                const unlockPageScroll = () => {
                    if (!scrollLocked) return;
                    document.documentElement.style.overflow = '';
                    document.body.style.overflow = '';
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.left = '';
                    document.body.style.right = '';
                    document.body.style.width = '';
                    document.body.style.paddingRight = '';
                    window.scrollTo(0, scrollLockY);
                    scrollLocked = false;
                };

                const syncScrollLock = () => {
                    const shouldLock = drawer?.classList.contains('is-open') || exportPopup?.classList.contains('is-open');
                    if (!shouldLockPageScroll()) {
                        unlockPageScroll();
                        return;
                    }
                    if (shouldLock) {
                        lockPageScroll();
                    } else {
                        unlockPageScroll();
                    }
                };

                desktopMedia.addEventListener?.('change', syncScrollLock);

                const syncFabVisibility = () => {
                    fabVisibilityFrame = null;
                    if (!fabStack) return;
                    const paginationVisible = (() => {
                        if (!paginationWrap) return false;
                        const rect = paginationWrap.getBoundingClientRect();
                        return rect.top < window.innerHeight - 110 && rect.bottom > 0;
                    })();
                    fabStack.classList.toggle('is-hidden-by-pagination', paginationVisible);
                };

                const queueFabVisibilitySync = () => {
                    if (fabVisibilityFrame !== null) return;
                    fabVisibilityFrame = window.requestAnimationFrame(syncFabVisibility);
                };

                window.updateDashboardFabVisibility = queueFabVisibilitySync;

                window.addEventListener('scroll', queueFabVisibilitySync, { passive: true });
                window.addEventListener('resize', queueFabVisibilitySync);
                window.addEventListener('orientationchange', queueFabVisibilitySync);

                const toggleDrawer = (open) => {
                    if (!drawer || !drawerOverlay) return;
                    drawerOverlay.classList.toggle('is-open', open);
                    drawer.classList.toggle('is-open', open);
                    if (open) {
                        exportOverlay?.classList.remove('is-open');
                        exportPopup?.classList.remove('is-open');
                        exportPopup?.setAttribute('aria-hidden', 'true');
                    }
                    syncScrollLock();
                };

                const toggleExportPopup = (open) => {
                    if (!exportPopup || !exportOverlay) return;
                    exportOverlay.classList.toggle('is-open', open);
                    exportPopup.classList.toggle('is-open', open);
                    exportPopup.setAttribute('aria-hidden', open ? 'false' : 'true');
                    if (open) {
                        drawerOverlay?.classList.remove('is-open');
                        drawer?.classList.remove('is-open');
                        renderDashboardFilterState(typeof getFilteredProjects === 'function' ? getFilteredProjects().length : projects.length);
                    }
                    syncScrollLock();
                };

                window.closeDashboardFloatingPanels = () => {
                    toggleDrawer(false);
                    toggleExportPopup(false);
                };

                drawerOpenBtn?.addEventListener('click', () => toggleDrawer(true));
                drawerCloseBtn?.addEventListener('click', () => toggleDrawer(false));
                drawerOverlay?.addEventListener('click', () => toggleDrawer(false));

                exportOpenBtn?.addEventListener('click', () => toggleExportPopup(true));
                exportCloseBtn?.addEventListener('click', () => toggleExportPopup(false));
                exportOverlay?.addEventListener('click', () => toggleExportPopup(false));

                exportExcelAction?.addEventListener('click', () => {
                    toggleExportPopup(false);
                    exportProjectsAsExcel();
                });

                exportCsvAction?.addEventListener('click', () => {
                    toggleExportPopup(false);
                    exportProjectsAsCsv();
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        toggleDrawer(false);
                        toggleExportPopup(false);
                    }
                });

                queueFabVisibilitySync();
            })();

            /* ---------- Project Table: pagination + filter + search ---------- */
            (function() {
                const pageSize = 10;
                let currentPage = 1;

                const tbody = document.getElementById('projectTableBody');
                const countEl = document.getElementById('tableCount');
                const pageEl = document.getElementById('tablePagination');
                const prevBtn = document.getElementById('tablePrev');
                const nextBtn = document.getElementById('tableNext');

                const filterYear = document.getElementById('filterYear');
                const filterTheme = document.getElementById('filterTheme');
                const filterFunding = document.getElementById('filterFunding');
                const filterType = document.getElementById('filterType');
                const filterStatus = document.getElementById('filterStatus');
                const filterSource = document.getElementById('filterSource');
                const filterPi = document.getElementById('filterPi');
                const filterPiAffiliation = document.getElementById('filterPiAffiliation');
                const filterName = document.getElementById('filterName');
                const tableSearch = document.getElementById('tableSearch');

                function getLatestIrbDate(p) {
                    const list = p.irbs || [];
                    if (!list.length) return '❌';
                    const sorted = [...list].sort((a, b) => {
                        const da = parseProjectDate(a.exp_date);
                        const db = parseProjectDate(b.exp_date);
                        return (db?.getTime?.() || 0) - (da?.getTime?.() || 0);
                    });
                    const top = sorted[0];
                    return formatDateTH(parseProjectDate(top?.exp_date), top?.exp_date || '❌');
                }

                function getFteSum(p) {
                    const roles = p.fte_roles || p.fteRoles || [];
                    const sum = roles.reduce((acc, r) => acc + toNumber(r.fte_value || r.fte || 0), 0);
                    return sum.toFixed(2);
                }

                function renderTable() {
                    if (!tbody) return;
                    const rows = getFilteredProjects();
                    const total = rows.length;
                    const totalPages = Math.max(Math.ceil(total / pageSize), 1);
                    currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                    const start = (currentPage - 1) * pageSize;
                    const pageRows = rows.slice(start, start + pageSize);

                    tbody.innerHTML = '';
                    if (!pageRows.length) {
                        tbody.innerHTML =
                            `<tr class="border-t"><td colspan="8" class="p-3 text-center text-gray-500">ไม่มีข้อมูลตามเงื่อนไข</td></tr>`;
                    } else {
                        pageRows.forEach(p => {
                            const copis = (p.copis || []).length;
                            const coResearches = (p.co_researches || p.coResearches || []).length;
                            const outputsCount = (p.outputs || []).length;
                            const irb = getLatestIrbDate(p);
                            const row = document.createElement('tr');
                            row.className = 'border-t';
                            row.innerHTML = `
                                <td class="p-2 text-blue-700 hover:underline" data-label="ชื่อโครงการ"><a href="/rm/projects/${p.id}">${p.project_name_th || '-'}</a></td>
                                <td class="p-2 text-center" data-label="ปี">${p.start_year || '-'}</td>
                                <td class="p-2 text-center" data-label="PI">${p.pi_name || '-'}</td>
                                <td class="p-2 text-center" data-label="Co-PI">${copis}</td>
                                <td class="p-2 text-center" data-label="CoResearch">${coResearches}</td>
                                <td class="p-2 text-center" data-label="FTE รวม">${getFteSum(p)}</td>
                                <td class="p-2 text-center" data-label="Output">${outputsCount}</td>
                                <td class="p-2 text-center" data-label="IRB ล่าสุด">${irb}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    }

                    if (countEl) countEl.textContent = `รวม ${total.toLocaleString('th-TH')} รายการ`;
                    if (pageEl) pageEl.textContent = `หน้า ${currentPage} / ${totalPages}`;
                    if (prevBtn) prevBtn.disabled = currentPage <= 1;
                    if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
                    window.updateDashboardFabVisibility?.();
                }

                function resetAndRender() {
                    currentPage = 1;
                    renderTable();
                }

                prevBtn?.addEventListener('click', () => {
                    currentPage = Math.max(currentPage - 1, 1);
                    renderTable();
                });
                nextBtn?.addEventListener('click', () => {
                    currentPage = currentPage + 1;
                    renderTable();
                });

                [filterYear, filterTheme, filterFunding, filterType, filterStatus, filterSource].forEach(el => el?.addEventListener('change', resetAndRender));
                [filterPi, filterPiAffiliation, filterName, tableSearch].forEach(el => el?.addEventListener('input', resetAndRender));

                renderTable();
            })();
        </script>

        <script>
            /* ========= Helpers ========= */
            const ROLES = ['first', 'co', 'corresponding'];

            function isPublicationType(t = '') {
                const s = String(t ?? '').toLowerCase();
                return s.startsWith('ea101') || s.includes('research paper') ||
                    s.includes('journal article') || s.includes('publication') || s.includes('pub');
            }

            /* ========= Plugins: data label นอกแท่ง ========= */
            // 1) แนวตั้ง: label เหนือหัวแท่ง (อยู่นอกแท่ง) + กันตกขอบบน
            const labelOutsideTop = {
                id: 'labelOutsideTop',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            top,
                            right
                        }
                    } = chart;
                    const meta = chart.getDatasetMeta(0);
                    const ds = chart.data.datasets[0];
                    if (!meta || !ds) return;

                    ctx.save();
                    // วาดได้ทั้ง canvas
                    ctx.beginPath();
                    ctx.rect(0, 0, chart.width, chart.height);
                    ctx.clip();
                    ctx.font = 'bold 12px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillStyle = '#111827';

                    const OFFSET = 12; // ระยะ “1 ช่อง”
                    meta.data.forEach((bar, i) => {
                        const v = Number(ds.data[i] ?? 0);
                        if (!isFinite(v) || v <= 0) return;
                        let x = bar.x;
                        let y = bar.y - OFFSET; // วางนอกหัวแท่ง
                        if (y < top + 8) y = top + 8; // กันชนขอบบน
                        if (x > right - 2) x = right - 2; // กันหลุดขวา
                        ctx.fillText(String(v), x, y);
                    });
                    ctx.restore();
                }
            };

            // 2) แนวนอน stacked: วาด “ผลรวมต่อแถว” นอกปลายแท่งรวมด้านขวา
            const stackedTotalRight = {
                id: 'stackedTotalRight',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            right
                        },
                        scales: {
                            x
                        }
                    } = chart;
                    const labels = chart.data.labels || [];
                    const datasets = chart.data.datasets || [];
                    const totals = labels.map((_, idx) =>
                        datasets.reduce((s, ds) => s + (Number(ds.data[idx] || 0) || 0), 0)
                    );

                    const meta0 = chart.getDatasetMeta(0);
                    if (!meta0) return;

                    ctx.save();
                    ctx.beginPath();
                    ctx.rect(0, 0, chart.width, chart.height);
                    ctx.clip();
                    ctx.font = 'bold 12px sans-serif';
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#111827';

                    const OFFSET = 12;
                    totals.forEach((tot, i) => {
                        if (!isFinite(tot) || tot <= 0) return;
                        const yPix = meta0.data?.[i]?.y;
                        if (typeof yPix !== 'number') return;
                        let tx = x.getPixelForValue(tot) + OFFSET; // นอกปลายแท่ง
                        if (tx > right - 2) tx = right - 2; // กันหลุดขวา
                        ctx.fillText(String(tot), tx, yPix);
                    });
                    ctx.restore();
                }
            };

            /* ========= 1) Publication รายปี (แท่งแนวตั้ง + label นอกแท่ง) ========= */
            (function() {
                const outputs = Array.isArray(window.outputs) ? window.outputs : [];
                const pubs = outputs.filter(o => isPublicationType(o.type));

                const byYearCE = {};
                pubs.forEach(o => {
                    const y = normalizeYear(o.year) || 'ไม่ทราบปี';
                    byYearCE[y] = (byYearCE[y] || 0) + 1;
                });

                const knownCE = Object.keys(byYearCE).filter(y => y !== 'ไม่ทราบปี')
                    .map(Number).sort((a, b) => a - b).map(String);
                const labelsCE = byYearCE['ไม่ทราบปี'] ? [...knownCE, 'ไม่ทราบปี'] : knownCE;
                const labelsBE = labelsCE.map(ce => ce === 'ไม่ทราบปี' ? ce : toBE(ce));
                const data = labelsCE.map(ce => byYearCE[ce] || 0);

                const el = document.getElementById('chartPublicationPerYear');
                if (!el) return;
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                // เพิ่ม headroom ให้ label นอกแท่งไม่ชนขอบ
                const maxV = Math.max(0, ...data);
                const suggestedMax = maxV > 0 ? Math.ceil(maxV * 1.2) : undefined;

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: labelsBE,
                        datasets: [{
                            label: 'Publications',
                            data,
                            backgroundColor: labelsBE.map((_, i) => `hsl(${(i*33)%360} 70% 60%)`),
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        layout: {
                            padding: {
                                top: 36
                            }
                        }, // เว้นบนให้เห็นเลขชัด
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (c) => ` ${c.raw} เรื่อง`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grace: '20%', // เผื่อหัวกราฟเพิ่ม
                                suggestedMax, // บีบ headroom เพิ่มจากข้อมูลจริง
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนเรื่อง'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'ปี (พ.ศ.)'
                                }
                            }
                        }
                    },
                    plugins: [labelOutsideTop]
                });
            })();

            /* ========= 2) Publications × Role ต่อบุคคล (แนวนอน stacked + label นอกแท่ง) ========= */
            (function() {
                const outputs = Array.isArray(window.outputs) ? window.outputs : [];

                const authorRoleByYear = {};
                const yearSetCE = new Set();

                outputs.filter(o => isPublicationType(o.type)).forEach(o => {
                    const y = normalizeYear(o.year);
                    if (!y) return;
                    yearSetCE.add(y);
                    const authors = Array.isArray(o.authors) ? o.authors : [];
                    authorRoleByYear[y] = authorRoleByYear[y] || {};
                    authors.forEach(a => {
                        const name = (a.name || '').trim();
                        const role = (a.role || '').toLowerCase().trim();
                        if (!name || !ROLES.includes(role)) return;
                        authorRoleByYear[y][name] = authorRoleByYear[y][name] || {
                            first: 0,
                            co: 0,
                            corresponding: 0
                        };
                        authorRoleByYear[y][name][role] += 1;
                    });
                });

                const yearsCE = Array.from(yearSetCE).map(Number).sort((a, b) => a - b).map(String);
                const yearsBE = yearsCE.map(toBE);

                // Controls
                const selYear = document.getElementById('yearFilter');
                const btnPrev = document.getElementById('btnPrevYear');
                const btnNext = document.getElementById('btnNextYear');
                const inpSearch = document.getElementById('authorSearch');
                const selTopN = document.getElementById('topN');
                let authorChart = null;

                // เติมปี (value = CE, text = BE)
                if (selYear) {
                    yearsCE.forEach((yCE, idx) => {
                        const opt = document.createElement('option');
                        opt.value = yCE;
                        opt.textContent = yearsBE[idx];
                        selYear.appendChild(opt);
                    });
                    if (yearsCE.length) selYear.value = yearsCE[yearsCE.length - 1];
                }

                function render(yCE) {
                    const target = authorRoleByYear[yCE] || {};
                    const term = String(inpSearch?.value || '').trim().toLowerCase();
                    const TOP_N = parseInt(selTopN?.value || '12', 10);

                    let rows = Object.entries(target).map(([name, roles]) => {
                        const total = (roles.first || 0) + (roles.co || 0) + (roles.corresponding || 0);
                        return {
                            name,
                            ...roles,
                            total
                        };
                    });

                    if (term) {
                        rows = rows.filter(r => r.name.toLowerCase().includes(term));
                    }
                    rows.sort((a, b) => b.total - a.total);
                    const top = rows.slice(0, TOP_N);

                    const labels = top.map(r => r.name);
                    const datasets = [{
                            key: 'first',
                            label: 'First-author'
                        },
                        {
                            key: 'co',
                            label: 'Co-author'
                        },
                        {
                            key: 'corresponding',
                            label: 'Corresponding'
                        }
                    ].map((cfg, i) => ({
                        label: cfg.label,
                        data: top.map(r => r[cfg.key] || 0),
                        backgroundColor: `hsl(${(i*48)%360} 70% 60%)`,
                        stack: 'roles',
                        borderWidth: 1
                    }));

                    const el = document.getElementById('chartAuthorRolePerYear');
                    if (!el) return;
                    el.height = Math.min(Math.max(280, labels.length * 20), 420);

                    if (authorChart) authorChart.destroy();
                    authorChart = new Chart(el, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            layout: {
                                padding: {
                                    right: 36
                                }
                            }, // เผื่อขวาให้ label รวม
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                title: {
                                    display: true,
                                    text: `ปี ${toBE(yCE)} (พ.ศ.)`
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        footer: (items) => {
                                            const sum = items.reduce((s, it) => s + (it.parsed.x || 0), 0);
                                            return 'รวมต่อคน: ' + sum;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grace: '15%', // headroom ปลายแท่ง
                                    ticks: {
                                        precision: 0
                                    },
                                    title: {
                                        display: true,
                                        text: 'จำนวนผลงาน (authorship entries)'
                                    }
                                },
                                y: {
                                    stacked: true
                                }
                            }
                        },
                        plugins: [stackedTotalRight]
                    });
                }

                // First draw + events
                if (selYear && yearsCE.length) {
                    render(selYear.value);
                }
                selYear?.addEventListener('change', () => render(selYear.value));
                selTopN?.addEventListener('change', () => render(selYear.value));
                inpSearch?.addEventListener('input', () => render(selYear.value));
                btnPrev?.addEventListener('click', () => {
                    const i = yearsCE.indexOf(selYear.value);
                    if (i > 0) {
                        selYear.value = yearsCE[i - 1];
                        render(selYear.value);
                    }
                });
                btnNext?.addEventListener('click', () => {
                    const i = yearsCE.indexOf(selYear.value);
                    if (i < yearsCE.length - 1) {
                        selYear.value = yearsCE[i + 1];
                        render(selYear.value);
                    }
                });
            })();

            /* ---------- PDF Export (charts + tables) ---------- */
            (function setupPdfExport() {
                const btn = document.getElementById('btnExportPdf');
                const overlay = document.getElementById('pdfExportOverlay');
                const container = document.getElementById('pdfExportContainer');
                if (!btn || !container) return;

                const chartTitles = {
                    chartRedZoneTimeline: 'RED ZONE: ย้อนหลัง 3 ปี',
                    chartOverdueProjects: 'ภาพรวมสถานะโครงการตามสัญญา',
                    chartCoResearchByUnitWindow: 'Co-Research ตามหน่วยงาน (3/6/12 เดือน)',
                    chartProjectsPerYear: 'จำนวนโครงการรายปี',
                    chartYearlyNewVsTotal: 'โครงการทั้งหมด/ใหม่/กำลังดำเนินการ/ปิดโปรเจค/ยกเลิกโปรเจค (รายปี)',
                    chartFundingSplit: 'สัดส่วนทุน ภายใน/ภายนอก (รายปี)',
                    chartBudgetPerYear: 'งบประมาณรวม (รายปี)',
                    chartExternalBySource: 'สัดส่วนทุนภายนอกที่ได้รับตามแหล่งทุน',
                    chartInternalBySource: 'สัดส่วนทุนภายในที่ได้รับตามแหล่งทุน',
                    chartExternalBySourceYear: 'ทุนภายนอกตามแหล่งทุน: แนวโน้มรายปี',
                    chartInternalBySourceYear: 'ทุนภายในตามแหล่งทุน: แนวโน้มรายปี',
                    chartOutputStacked: 'Output แยกประเภท (รายปี)',
                    chartPublicationPerYear: 'Publication รายปี',
                    chartAuthorRolePerYear: 'บทบาทผู้เขียน (First/Co/Corresponding) รายปี + Top N',
                    chartStatusYear: 'สถานะโครงการ (รายปี)',
                    chartCollabBreakdown: 'ความร่วมมือ (นอกคณะ/ในคณะ/ในหน่วย)',
                    chartPIsPerUnit: 'จำนวนงานของ PI ตามหน่วยงาน',
                    chartFteSummary: 'Bubble FTE รวมต่อคน',
                    chartIRBExpiry: 'IRB ใกล้หมดอายุ (6 เดือน)'
                };

                const chartOrder = [
                    'chartRedZoneTimeline', 'chartOverdueProjects',
                    'chartCoResearchByUnitWindow', 'chartProjectsPerYear',
                    'chartYearlyNewVsTotal', 'chartFundingSplit',
                    'chartBudgetPerYear', 'chartExternalBySource',
                    'chartInternalBySource', 'chartExternalBySourceYear',
                    'chartInternalBySourceYear', 'chartOutputStacked',
                    'chartPublicationPerYear', 'chartAuthorRolePerYear',
                    'chartStatusYear', 'chartCollabBreakdown',
                    'chartPIsPerUnit', 'chartFteSummary',
                    'chartIRBExpiry'
                ];

                const wait = (ms = 0) => new Promise(res => setTimeout(res, ms));
                const chunk = (arr, size) => {
                    const out = [];
                    for (let i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
                    return out;
                };
                const safe = (v, fallback = '-') => (v ?? '').toString().trim() || fallback;
                const generatedAt = new Date();
                const generatedText = generatedAt.toLocaleString('th-TH', {
                    timeZone: 'Asia/Bangkok',
                    hour12: false
                });
                const generatedBy = window.currentUserName || 'ผู้ใช้ไม่ระบุ';
                const docTitle = 'Siriraj Health Policy Database Research';
                const docSubtitle = 'Dashboard Export: กราฟและตารางจากหน้าจอ Dashboard ปัจจุบัน';

                const buildOverdueRows = () => {
                    const scopedProjects = typeof getFilteredProjects === 'function' ? getFilteredProjects() : projects;
                    const now = new Date();
                    return buildActiveOverdueRecords(scopedProjects, now)
                        .map((record) => ({
                            project: `${record.project?.project_name_th || '-'} (${overdueSourceLabel(record)})`,
                            contractEndDateTH: overdueRecordEndDateLabel(record),
                            teamEndDateTH: overdueRecordTeamEndDateLabel(record),
                            overdueDays: record.overdueDays,
                            overdueSourceLabel: overdueSourceLabel(record),
                            status: record.statusLabel || '-',
                            pi: record.project?.pi_name || '-',
                            contractEndDate: record.contractEndDate,
                        }))
                        .sort((a, b) => {
                            if (b.overdueDays !== a.overdueDays) return b.overdueDays - a.overdueDays;
                            return a.contractEndDate - b.contractEndDate;
                        });
                };

                const buildIrbRows = () => {
                    const rows = (Array.isArray(irbs) ? irbs : []).map(irb => {
                        const project = projectById.get(irb.project_id) || {};
                        const expDate = parseProjectDate(irb.exp_date);
                        return {
                            project: project.project_name_th || '-',
                            code: irb.code || '-',
                            source: irb.source || irb.irb_source || '-',
                            expDate,
                            expDateStr: formatDateTH(expDate, irb.exp_date || '-')
                        };
                    });
                    return rows.sort((a, b) => (a.expDate?.getTime?.() || 0) - (b.expDate?.getTime?.() || 0));
                };

                const buildCopiRows = () => {
                    const list = Array.isArray(copisSummary) ? copisSummary : [];
                    return list.map(r => ({
                        name: safe(r.name),
                        affiliation: safe(r.affiliation),
                        project_count: r.project_count || 0
                    })).sort((a, b) => b.project_count - a.project_count);
                };

                const buildProjectRows = () => {
                    const list = typeof getFilteredProjects === 'function' ? getFilteredProjects() : projects;
                    const num = (v) => Number(v || 0) || 0;
                    const latestIrb = (p) => {
                        const list = p.irbs || [];
                        if (!list.length) return '-';
                        const sorted = [...list].sort((a, b) => (parseProjectDate(b.exp_date)?.getTime?.() || 0) - (parseProjectDate(a.exp_date)?.getTime?.() || 0));
                        return formatDateTH(parseProjectDate(sorted[0].exp_date), sorted[0].exp_date || '-');
                    };
                    return list.map(p => ({
                        project: p.project_name_th || '-',
                        year: p.start_year || '-',
                        pi: p.pi_name || '-',
                        copis: (p.copis || []).length,
                        coResearch: (p.co_researches || p.coResearches || []).length,
                        fte: (p.fte_roles || p.fteRoles || []).reduce((s, r) => s + num(r.fte_value || r.fte), 0).toFixed(2),
                        outputs: (p.outputs || []).length,
                        irb: latestIrb(p)
                    }));
                };

                function createPage(title, subtitle = '') {
                    const page = document.createElement('div');
                    page.className = 'pdf-page';
                    const header = document.createElement('div');
                    header.className = 'pdf-header';
                    const h1 = document.createElement('div');
                    h1.className = 'pdf-title';
                    h1.textContent = title;
                    header.appendChild(h1);
                    if (subtitle) {
                        const sub = document.createElement('div');
                        sub.className = 'pdf-subtitle';
                        sub.textContent = subtitle;
                        header.appendChild(sub);
                    }
                    const meta = document.createElement('div');
                    meta.className = 'pdf-meta';
                    meta.innerHTML = `
                        <span>ผู้สร้าง: <strong>${generatedBy}</strong></span>
                        <span>สร้างเมื่อ: ${generatedText}</span>
                    `;
                    page.appendChild(header);
                    page.appendChild(meta);
                    return page;
                }

                function addCoverPage() {
                    const page = document.createElement('div');
                    page.className = 'pdf-page';
                    const header = document.createElement('div');
                    header.className = 'pdf-header';
                    const h1 = document.createElement('div');
                    h1.className = 'pdf-title';
                    h1.textContent = docTitle;
                    header.appendChild(h1);
                    const sub = document.createElement('div');
                    sub.className = 'pdf-subtitle';
                    sub.textContent = `${docSubtitle} • สร้างเมื่อ ${generatedText}`;
                    header.appendChild(sub);
                    page.appendChild(header);

                    const meta = document.createElement('div');
                    meta.className = 'pdf-meta';
                    meta.innerHTML = `
                        <span>ผู้สร้าง: <strong>${generatedBy}</strong></span>
                        <span>เวลารัน: ${generatedText}</span>
                        <span>กราฟทั้งหมด: ${chartOrder.length} รายการ</span>
                    `;
                    page.appendChild(meta);

                    const list = document.createElement('div');
                    list.className = 'mt-4 text-sm text-slate-700';
                    list.innerHTML = `
                        <div class="font-semibold mb-2">รายการที่รวมในไฟล์:</div>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>กราฟกลยุทธ์ 2 กราฟต่อหน้า (รวม ${chartOrder.length} กราฟ)</li>
                            <li>ตารางโครงการเกินกำหนดที่ยังต้องติดตาม</li>
                            <li>ตาราง IRB</li>
                            <li>Co-PI / ผู้วิจัยร่วม</li>
                            <li>รายการโครงการตามตัวกรองปัจจุบัน</li>
                        </ul>
                    `;
                    if (window.loginLogoUrl) {
                        const logoBox = document.createElement('div');
                        logoBox.className = 'mt-6 flex items-center gap-3';
                        const img = document.createElement('img');
                        img.src = window.loginLogoUrl;
                        img.alt = 'Siriraj Health Policy Database Logo';
                        img.style.height = '110px';
                        img.style.width = 'auto';
                        img.style.objectFit = 'contain';
                        img.style.filter = 'drop-shadow(0 4px 8px rgba(0,0,0,0.08))';
                        logoBox.appendChild(img);
                        const txt = document.createElement('div');
                        txt.className = 'text-sm text-slate-600';
                        txt.textContent = 'ข้อมูลและกราฟจากระบบ Siriraj Health Policy Database';
                        logoBox.appendChild(txt);
                        page.appendChild(logoBox);
                    }
                    page.appendChild(list);
                    container.appendChild(page);
                }

                function createChartCard(id) {
                    const card = document.createElement('div');
                    card.className = 'pdf-chart-card';
                    const title = document.createElement('div');
                    title.className = 'pdf-chart-title';
                    title.textContent = chartTitles[id] || id;
                    card.appendChild(title);

                    const chartEl = document.getElementById(id);
                    const chartObj = Chart.getChart(chartEl);
                    let imageSrc = '';
                    if (chartObj && typeof chartObj.toBase64Image === 'function') {
                        imageSrc = chartObj.toBase64Image();
                    } else if (chartEl instanceof HTMLCanvasElement && chartEl.width > 0 && chartEl.height > 0) {
                        try {
                            imageSrc = chartEl.toDataURL('image/png', 0.92);
                        } catch (error) {
                            imageSrc = '';
                        }
                    }

                    if (imageSrc) {
                        const img = document.createElement('img');
                        img.alt = chartTitles[id] || id;
                        img.src = imageSrc;
                        card.appendChild(img);
                    } else {
                        const empty = document.createElement('div');
                        empty.className = 'text-sm text-slate-500';
                        empty.textContent = 'ไม่มีข้อมูลกราฟ';
                        card.appendChild(empty);
                    }
                    return card;
                }

                function createTableCard(title, columns, rows, badgeText = '') {
                    const card = document.createElement('div');
                    card.className = 'pdf-table-card';
                    const head = document.createElement('div');
                    head.className = 'pdf-table-title flex items-center justify-between gap-2';
                    const label = document.createElement('div');
                    label.textContent = title;
                    head.appendChild(label);
                    if (badgeText) {
                        const badge = document.createElement('span');
                        badge.className = 'pdf-badge';
                        badge.textContent = badgeText;
                        head.appendChild(badge);
                    }
                    card.appendChild(head);

                    const table = document.createElement('table');
                    const thead = document.createElement('thead');
                    const trHead = document.createElement('tr');
                    columns.forEach(col => {
                        const th = document.createElement('th');
                        th.textContent = col.label;
                        if (col.align) th.style.textAlign = col.align;
                        trHead.appendChild(th);
                    });
                    thead.appendChild(trHead);
                    table.appendChild(thead);

                    const tbody = document.createElement('tbody');
                    if (!rows.length) {
                        const tr = document.createElement('tr');
                        const td = document.createElement('td');
                        td.colSpan = columns.length;
                        td.className = 'text-center text-slate-500';
                        td.textContent = 'ไม่มีข้อมูล';
                        tr.appendChild(td);
                        tbody.appendChild(tr);
                    } else {
                        rows.forEach(r => {
                            const tr = document.createElement('tr');
                            columns.forEach(col => {
                                const td = document.createElement('td');
                                const value = typeof col.format === 'function' ? col.format(r[col.key], r) : r[col.key];
                                td.textContent = safe(value, '-');
                                if (col.align) td.style.textAlign = col.align;
                                tr.appendChild(td);
                            });
                            tbody.appendChild(tr);
                        });
                    }
                    table.appendChild(tbody);
                    card.appendChild(table);
                    return card;
                }

                function addChartPages() {
                    const pairs = chunk(chartOrder, 2);
                    pairs.forEach((pair, idx) => {
                        const page = createPage('สรุปกราฟสำคัญ', `หน้า ${idx + 1} : 2 กราฟ/หน้า`);
                        const grid = document.createElement('div');
                        grid.className = 'pdf-grid-2';
                        pair.forEach(id => grid.appendChild(createChartCard(id)));
                        page.appendChild(grid);
                        container.appendChild(page);
                    });
                }

                function addTablePages() {
                        const tableSets = [{
                            title: 'ตารางโครงการเกินกำหนดที่ยังต้องติดตาม',
                            rows: buildOverdueRows(),
                        columns: [{
                                key: 'project',
                                label: 'โครงการ'
                                },
                                {
                                    key: 'contractEndDateTH',
                                    label: 'วันที่สิ้นสุดสัญญา (พ.ศ.)',
                                    align: 'center'
                                },
                                {
                                    key: 'teamEndDateTH',
                                    label: 'วันที่สิ้นสุดการทำงานทีม',
                                    align: 'center'
                                },
                                {
                                    key: 'overdueDays',
                                    label: 'เกินกำหนด (วัน)',
                                    align: 'center'
                                },
                                {
                                    key: 'overdueSourceLabel',
                                    label: 'ประเภทการเกินกำหนด',
                                    align: 'center'
                                },
                                {
                                    key: 'status',
                                    label: 'สถานะโครงการ',
                                    align: 'center'
                                },
                                {
                                    key: 'pi',
                                    label: 'PI',
                                    align: 'center'
                                }
                            ],
                            perPage: 12
                        },
                        {
                            title: 'ตาราง IRB',
                            rows: buildIrbRows(),
                            columns: [{
                                    key: 'project',
                                    label: 'โครงการ'
                                },
                                {
                                    key: 'code',
                                    label: 'รหัส IRB',
                                    align: 'center'
                                },
                                {
                                    key: 'source',
                                    label: 'หน่วยงาน/แหล่ง',
                                    align: 'left'
                                },
                                {
                                    key: 'expDateStr',
                                    label: 'วันหมดอายุ',
                                    align: 'center'
                                }
                            ],
                            perPage: 12
                        },
                        {
                            title: 'Co-PI / ผู้วิจัยร่วม',
                            rows: buildCopiRows(),
                            columns: [{
                                    key: 'name',
                                    label: 'ชื่อ'
                                },
                                {
                                    key: 'affiliation',
                                    label: 'หน่วยงาน'
                                },
                                {
                                    key: 'project_count',
                                    label: 'จำนวนโครงการ',
                                    align: 'center'
                                }
                            ],
                            perPage: 15
                        },
                        {
                            title: 'โครงการทั้งหมด (ตามตัวกรองปัจจุบัน)',
                            rows: buildProjectRows(),
                            columns: [{
                                    key: 'project',
                                    label: 'ชื่อโครงการ'
                                },
                                {
                                    key: 'year',
                                    label: 'ปี',
                                    align: 'center'
                                },
                                {
                                    key: 'pi',
                                    label: 'PI',
                                    align: 'center'
                                },
                                {
                                    key: 'copis',
                                    label: 'Co-PI',
                                    align: 'center'
                                },
                                {
                                    key: 'coResearch',
                                    label: 'Co-Research',
                                    align: 'center'
                                },
                                {
                                    key: 'fte',
                                    label: 'FTE รวม',
                                    align: 'center'
                                },
                                {
                                    key: 'outputs',
                                    label: 'Output',
                                    align: 'center'
                                },
                                {
                                    key: 'irb',
                                    label: 'IRB ล่าสุด',
                                    align: 'center'
                                }
                            ],
                            perPage: 8
                        }
                    ];

                    tableSets.forEach(set => {
                        const rows = set.rows || [];
                        const total = rows.length;
                        const perPage = set.perPage || total || 1;
                        if (!total) {
                            const page = createPage(set.title, 'ไม่มีข้อมูล');
                            page.appendChild(createTableCard(set.title, set.columns, []));
                            container.appendChild(page);
                            return;
                        }
                        chunk(rows, perPage).forEach((pageRows, idx) => {
                            const page = createPage(set.title, `หน้าตาราง ${idx + 1}`);
                            page.appendChild(createTableCard(set.title + (rows.length > perPage ? ` (ชุด ${idx + 1})` : ''), set.columns, pageRows, `${total.toLocaleString('th-TH')} รายการ`));
                            container.appendChild(page);
                        });
                    });
                }

                function nextFrame() {
                    return new Promise((resolve) => requestAnimationFrame(() => resolve()));
                }

                async function settleChartsForPdf() {
                    const charts = chartOrder
                        .map((id) => Chart.getChart(document.getElementById(id)))
                        .filter(Boolean);

                    const originals = charts.map((chart) => ({
                        chart,
                        animation: chart.options?.animation
                    }));

                    originals.forEach(({ chart }) => {
                        if (!chart.options) return;
                        chart.options.animation = false;
                        chart.update('none');
                    });

                    await nextFrame();
                    await nextFrame();
                    await wait(120);

                    return () => {
                        originals.forEach(({ chart, animation }) => {
                            if (!chart.options) return;
                            chart.options.animation = animation;
                            chart.update('none');
                        });
                    };
                }

                async function renderAndDownload() {
                    if (typeof html2canvas === 'undefined' || !window.jspdf) {
                        alert('ไม่พบตัวช่วยสร้าง PDF (html2canvas / jsPDF)');
                        return;
                    }
                    overlay?.classList.remove('hidden');
                    container.classList.remove('hidden');
                    container.innerHTML = '';
                    let restoreCharts = null;

                    try {
                        window.refreshRealtimeDashboard?.();
                        await wait(240);
                        restoreCharts = await settleChartsForPdf();
                        addCoverPage();
                        addChartPages();
                        addTablePages();
                        await wait(140); // เผื่อเวลาให้ layout และภาพกราฟนิ่งก่อน capture

                        const pages = Array.from(container.querySelectorAll('.pdf-page'));
                        const { jsPDF } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'landscape',
                            unit: 'px',
                            format: 'a4'
                        });

                        for (let i = 0; i < pages.length; i++) {
                            const canvas = await html2canvas(pages[i], {
                                scale: 1.2,
                                useCORS: true,
                                allowTaint: true,
                                windowWidth: pages[i].offsetWidth,
                                windowHeight: pages[i].offsetHeight
                            });
                            const imgData = canvas.toDataURL('image/png', 0.90);
                            const pageW = pdf.internal.pageSize.getWidth();
                            const pageH = pdf.internal.pageSize.getHeight();
                            const ratio = Math.min(pageW / canvas.width, pageH / canvas.height);
                            const w = canvas.width * ratio;
                            const h = canvas.height * ratio;
                            pdf.addImage(imgData, 'PNG', 0, 0, w, h);
                            if (i < pages.length - 1) pdf.addPage('a4', 'landscape');
                        }

                        const now = new Date();
                        const stamp = `${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}_${String(now.getHours()).padStart(2, '0')}${String(now.getMinutes()).padStart(2, '0')}`;
                        const userSlug = (generatedBy || 'user').replace(/\\s+/g, '');
                        const name = `SiHealthpolicy_dashboard_${userSlug}_${stamp}.pdf`;
                        const blobUrl = pdf.output('bloburl');
                        window.open(blobUrl, '_blank');
                    } catch (err) {
                        console.error('PDF export failed', err);
                        alert('สร้าง PDF ไม่สำเร็จ กรุณาลองอีกครั้ง');
                    } finally {
                        restoreCharts?.();
                        container.classList.add('hidden');
                        container.innerHTML = '';
                        overlay?.classList.add('hidden');
                    }
                }

                btn.addEventListener('click', renderAndDownload);
            })();
        </script>
        <script>
            (function() {
                if (window.__dashboardRealtimeBound) return;
                window.__dashboardRealtimeBound = true;

                const fmtNum = new Intl.NumberFormat('th-TH');
                const fmtMoney = new Intl.NumberFormat('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                const el = {
                    filterYear: document.getElementById('filterYear'),
                    filterTheme: document.getElementById('filterTheme'),
                    filterFunding: document.getElementById('filterFunding'),
                    filterType: document.getElementById('filterType'),
                    filterStatus: document.getElementById('filterStatus'),
                    filterSource: document.getElementById('filterSource'),
                    filterPi: document.getElementById('filterPi'),
                    filterPiAffiliation: document.getElementById('filterPiAffiliation'),
                    filterName: document.getElementById('filterName'),
                    tableSearch: document.getElementById('tableSearch'),
                    filterReset: document.getElementById('filterReset'),
                    badge: document.getElementById('rtFilterBadge'),
                    caption: document.getElementById('rtSummaryCaption'),
                    totalBudget: document.getElementById('rtSummaryBudget'),
                    avgBudget: document.getElementById('rtSummaryAvgBudget'),
                    typeBody: document.getElementById('rtTypeSummaryBody'),
                    heroProjects: document.getElementById('heroSummaryProjects'),
                    heroPi: document.getElementById('heroSummaryPi'),
                    heroCopi: document.getElementById('heroSummaryCopi'),
                    heroCoResearch: document.getElementById('heroSummaryCoResearch'),
                    heroFte: document.getElementById('heroSummaryFte'),
                    heroOutputs: document.getElementById('heroSummaryOutputs'),
                };

                const safeText = (v, fallback = '') => {
                    const s = String(v ?? '').trim();
                    return s || fallback;
                };
                const safeNum = (v) => {
                    const n = Number(v);
                    return Number.isFinite(n) ? n : 0;
                };
                const getBudget = (p) => safeNum(p?.total_budget ?? p?.budget ?? 0);
                const getType = (p) => safeText(p?.project_type, 'ไม่ระบุประเภท');
                const getYearLabel = (p) => safeText(p?.start_year, 'ไม่ระบุปี');
                const escapeHtml = (value) => String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

                const allProjects = Array.isArray(projects) ? projects : [];

                function getRows() {
                    if (typeof getFilteredProjects === 'function') return getFilteredProjects();
                    return allProjects.slice();
                }

                function parseYearKey(label) {
                    const m = String(label ?? '').match(/\d{4}/);
                    return m ? Number(m[0]) : NaN;
                }

                function sortYearLabels(labels) {
                    return labels.slice().sort((a, b) => {
                        const ay = parseYearKey(a);
                        const by = parseYearKey(b);
                        if (Number.isFinite(ay) && Number.isFinite(by)) return ay - by;
                        if (Number.isFinite(ay)) return -1;
                        if (Number.isFinite(by)) return 1;
                        return String(a).localeCompare(String(b), 'th');
                    });
                }

                function summarizeByType(rows) {
                    const map = new Map();
                    rows.forEach((p) => {
                        const type = getType(p);
                        if (!map.has(type)) map.set(type, {
                            count: 0,
                            budget: 0
                        });
                        const item = map.get(type);
                        item.count += 1;
                        item.budget += getBudget(p);
                    });

                    return Array.from(map.entries())
                        .map(([type, value]) => ({
                            type,
                            ...value
                        }))
                        .sort((a, b) => b.count - a.count);
                }

                function updateSummary(rows) {
                    const total = rows.length;
                    const uniquePi = new Set(rows.map(p => safeText(p?.pi_name)).filter(Boolean)).size;
                    const totalBudget = rows.reduce((sum, p) => sum + getBudget(p), 0);
                    const avgBudget = total > 0 ? (totalBudget / total) : 0;
                    const totalCopi = rows.reduce((sum, p) => sum + ((Array.isArray(p?.copis) ? p.copis.length : 0)), 0);
                    const totalCoResearch = rows.reduce((sum, p) => {
                        const list = Array.isArray(p?.co_researches) ? p.co_researches : (Array.isArray(p?.coResearches) ? p.coResearches : []);
                        return sum + list.length;
                    }, 0);
                    const totalFte = rows.reduce((sum, p) => {
                        const list = Array.isArray(p?.fte_roles) ? p.fte_roles : [];
                        const val = list.reduce((inner, role) => inner + safeNum(role?.fte_value), 0);
                        return sum + val;
                    }, 0);
                    const totalOutputs = rows.reduce((sum, p) => {
                        const list = Array.isArray(p?.outputs) ? p.outputs : [];
                        return sum + list.length;
                    }, 0);

                    if (el.badge) el.badge.textContent = `${fmtNum.format(total)} โครงการ`;
                    if (el.caption) el.caption.textContent = `สรุปตามฟิลเตอร์ปัจจุบัน ${fmtNum.format(total)} / ${fmtNum.format(allProjects.length)} โครงการ`;
                    if (el.totalBudget) el.totalBudget.textContent = `${fmtMoney.format(totalBudget)} บ.`;
                    if (el.avgBudget) el.avgBudget.textContent = `${fmtMoney.format(avgBudget)} บ.`;
                    if (el.heroProjects) el.heroProjects.textContent = fmtNum.format(total);
                    if (el.heroPi) el.heroPi.textContent = fmtNum.format(uniquePi);
                    if (el.heroCopi) el.heroCopi.textContent = fmtNum.format(totalCopi);
                    if (el.heroCoResearch) el.heroCoResearch.textContent = fmtNum.format(totalCoResearch);
                    if (el.heroFte) el.heroFte.textContent = Number(totalFte).toFixed(2);
                    if (el.heroOutputs) el.heroOutputs.textContent = fmtNum.format(totalOutputs);
                    renderDashboardFilterState(total);
                }

                function updateTypeTable(rows) {
                    if (!el.typeBody) return;
                    const summary = summarizeByType(rows);
                    const total = rows.length || 1;
                    el.typeBody.innerHTML = '';

                    if (!summary.length) {
                        el.typeBody.innerHTML = `<tr><td colspan="4" class="px-3 py-3 text-center text-slate-500">ไม่พบข้อมูลตามเงื่อนไข</td></tr>`;
                        return;
                    }

                    summary.slice(0, 8).forEach((row) => {
                        const ratio = ((row.count / total) * 100).toFixed(1);
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td data-label="ประเภทโครงการ" class="px-3 py-2 text-left text-slate-700 type-name-cell">${escapeHtml(row.type)}</td>
                            <td data-label="จำนวนโครงการ" class="px-3 py-2 text-right text-slate-700 type-metric-cell"><span class="type-metric-value">${fmtNum.format(row.count)}</span></td>
                            <td data-label="สัดส่วน" class="px-3 py-2 text-right text-slate-700 type-metric-cell"><span class="type-metric-value">${ratio}%</span></td>
                            <td data-label="งบรวม" class="px-3 py-2 text-right text-slate-700 type-metric-cell"><span class="type-metric-value">${fmtMoney.format(row.budget)}</span></td>
                        `;
                        el.typeBody.appendChild(tr);
                    });
                }

                function gradient(canvasId, start, end) {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas) return start;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) return start;
                    const g = ctx.createLinearGradient(0, 0, 0, canvas.height || 260);
                    g.addColorStop(0, start);
                    g.addColorStop(1, end);
                    return g;
                }

                function createBarValueLabelPlugin(formatter, options = {}) {
                    const minValue = Number(options.minValue ?? 0);
                    return {
                        id: `rtBarValue_${Math.random().toString(36).slice(2, 8)}`,
                        afterDatasetsDraw(chart) {
                            const {
                                ctx,
                                chartArea: {
                                    top,
                                    right,
                                    left
                                }
                            } = chart;
                            ctx.save();
                            ctx.font = '700 11px sans-serif';
                            ctx.fillStyle = '#111827';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';

                            chart.data.datasets.forEach((ds, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                const meta = chart.getDatasetMeta(dsIndex);
                                meta.data.forEach((bar, i) => {
                                    if (!bar || bar.hidden) return;
                                    const v = Number(ds.data?.[i] ?? 0);
                                    if (!isFinite(v) || v <= 0) return;
                                    if (v < minValue) return;
                                    const txt = typeof formatter === 'function' ? formatter(v) : String(v);
                                    let x = bar.x;
                                    let y = bar.y - 4;
                                    if (y < top + 8) y = top + 8;
                                    if (x > right - 2) x = right - 2;
                                    if (x < left + 2) x = left + 2;
                                    ctx.fillText(txt, x, y);
                                });
                            });

                            ctx.restore();
                        }
                    };
                }

                function createStackTotalLabelPlugin(formatter) {
                    return {
                        id: `rtStackTotal_${Math.random().toString(36).slice(2, 8)}`,
                        afterDatasetsDraw(chart) {
                            const {
                                ctx,
                                chartArea: {
                                    top,
                                    right,
                                    left
                                }
                            } = chart;
                            ctx.save();
                            ctx.font = '700 11px sans-serif';
                            ctx.fillStyle = '#0f172a';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';

                            const labels = chart.data.labels || [];
                            labels.forEach((_, i) => {
                                let total = 0;
                                let topElement = null;

                                chart.data.datasets.forEach((ds, dsIndex) => {
                                    if (!chart.isDatasetVisible(dsIndex)) return;
                                    const v = Number(ds.data?.[i] ?? 0);
                                    if (!isFinite(v) || v <= 0) return;
                                    total += v;
                                    const el = chart.getDatasetMeta(dsIndex)?.data?.[i];
                                    if (el && (!topElement || el.y < topElement.y)) topElement = el;
                                });

                                if (!(total > 0) || !topElement) return;
                                const txt = typeof formatter === 'function' ? formatter(total) : String(total);
                                let x = topElement.x;
                                let y = topElement.y - 6;
                                if (y < top + 10) y = top + 10;
                                if (x > right - 2) x = right - 2;
                                if (x < left + 2) x = left + 2;
                                ctx.fillText(txt, x, y);
                            });

                            ctx.restore();
                        }
                    };
                }

                function renderProjectsPerYear(rows) {
                    const sourceRows = Array.isArray(rows) ? rows : [];
                    const startYearsCE = sourceRows
                        .map((p) => projectLifecycleStartYearCE(p))
                        .filter(Number.isFinite)
                        .sort((a, b) => a - b);
                    const unknownProjects = sourceRows.filter((p) => !Number.isFinite(projectLifecycleStartYearCE(p)));

                    const canvas = document.getElementById('chartProjectsPerYear');
                    if (!canvas) return;
                    Chart.getChart(canvas)?.destroy();

                    if (!startYearsCE.length && !unknownProjects.length) {
                        new Chart(canvas, {
                            type: 'bar',
                            data: {
                                labels: ['ไม่มีข้อมูล'],
                                datasets: [{
                                    label: 'จำนวนโครงการ',
                                    data: [0],
                                    backgroundColor: gradient('chartProjectsPerYear', 'rgba(14,165,233,0.9)', 'rgba(14,165,233,0.2)'),
                                    borderColor: '#0284c7',
                                    borderWidth: 1.5,
                                    borderRadius: 10,
                                    maxBarThickness: 42,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                        return;
                    }

                    const yearsCE = Array.from(new Set(startYearsCE));
                    const buckets = [...yearsCE, ...(unknownProjects.length ? ['__unknown__'] : [])];
                    const labels = buckets.map((bucket) => bucket === '__unknown__' ? 'ไม่ระบุปี' : yearLabelTH(bucket));
                    const data = buckets.map((bucket) => {
                        if (bucket === '__unknown__') return unknownProjects.length;
                        return sourceRows.filter((p) => {
                            const startY = projectLifecycleStartYearCE(p);
                            return Number.isFinite(startY) && startY === bucket;
                        }).length;
                    });
                    const colors = labels.map((_, index) => `hsl(${(index * 37) % 360} 70% 60%)`);

                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'จำนวนโครงการรายปี',
                                data,
                                backgroundColor: colors,
                                borderRadius: 10,
                                maxBarThickness: 42,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 350
                            },
                            layout: {
                                padding: {
                                    top: 14
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${fmtNum.format(ctx.raw || 0)} โครงการ`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: buildMobileCategoryTicks(8, 4),
                                    title: {
                                        display: true,
                                        text: 'ปี (พ.ศ.)'
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grace: '12%',
                                    title: {
                                        display: true,
                                        text: 'จำนวนโครงการ'
                                    },
                                    ticks: {
                                        precision: 0
                                    },
                                    grid: {
                                        color: 'rgba(148,163,184,0.2)'
                                    }
                                }
                            }
                        },
                        plugins: [createBarValueLabelPlugin((v) => fmtNum.format(v))]
                    });
                }

                function renderFundingSplit(rows) {
                    const labels = sortYearLabels(Array.from(new Set(rows.map(getYearLabel))));
                    const internal = labels.map((y) => rows.filter((p) => getYearLabel(p) === y && safeText(p?.funding_type) === 'ภายใน').length);
                    const external = labels.map((y) => rows.filter((p) => getYearLabel(p) === y && safeText(p?.funding_type) === 'ภายนอก').length);

                    const canvas = document.getElementById('chartFundingSplit');
                    if (!canvas) return;
                    Chart.getChart(canvas)?.destroy();
                    const totalProjects = internal.reduce((sum, value) => sum + value, 0) + external.reduce((sum, value) => sum + value, 0);
                    if (!totalProjects) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: labels.length ? labels : ['ไม่มีข้อมูล'],
                            datasets: [{
                                    label: 'ภายใน',
                                    data: internal.length ? internal : [0],
                                    backgroundColor: gradient('chartFundingSplit', 'rgba(16,185,129,0.95)', 'rgba(16,185,129,0.4)'),
                                    maxBarThickness: 40,
                                    borderRadius: 8,
                                },
                                {
                                    label: 'ภายนอก',
                                    data: external.length ? external : [0],
                                    backgroundColor: gradient('chartFundingSplit', 'rgba(249,115,22,0.95)', 'rgba(249,115,22,0.4)'),
                                    maxBarThickness: 40,
                                    borderRadius: 8,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 350
                            },
                            layout: {
                                padding: {
                                    top: 14
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        footer: (items) => {
                                            const sum = items.reduce((acc, it) => acc + (Number(it.parsed?.y || 0)), 0);
                                            return `รวม: ${fmtNum.format(sum)} โครงการ`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grace: '12%',
                                    ticks: {
                                        precision: 0
                                    },
                                    grid: {
                                        color: 'rgba(148,163,184,0.2)'
                                    }
                                }
                            }
                        },
                        plugins: [createStackTotalLabelPlugin((v) => fmtNum.format(v))]
                    });
                }

                function renderBudgetPerYear(rows) {
                    const labels = sortYearLabels(Array.from(new Set(rows.map(getYearLabel))));
                    const budgets = labels.map((y) => rows
                        .filter((p) => getYearLabel(p) === y)
                        .reduce((sum, p) => sum + getBudget(p), 0));

                    const canvas = document.getElementById('chartBudgetPerYear');
                    if (!canvas) return;
                    Chart.getChart(canvas)?.destroy();
                    if (!budgets.length || !budgets.some((value) => Number(value) > 0)) {
                        drawEmptyChart(canvas);
                        return;
                    }
                    clearEmptyChartState(canvas);
                    const maxBudget = Math.max(0, ...budgets);
                    const minLabelBudget = maxBudget > 0 ? Math.max(maxBudget * 0.03, 1_000_000) : 0;

                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: labels.length ? labels : ['ไม่มีข้อมูล'],
                            datasets: [{
                                label: 'งบประมาณรวม',
                                data: budgets.length ? budgets : [0],
                                backgroundColor: gradient('chartBudgetPerYear', 'rgba(37,99,235,0.9)', 'rgba(37,99,235,0.35)'),
                                borderColor: '#2563eb',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                maxBarThickness: 42,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 350
                            },
                            layout: {
                                padding: {
                                    top: 24
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${fmtMoney.format(ctx.raw || 0)} บาท`,
                                        footer: (items) => {
                                            const v = Number(items?.[0]?.parsed?.y ?? 0);
                                            return `ประมาณ ${formatThaiMoney(v)}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grace: '14%',
                                    ticks: {
                                        callback: (value) => `${(Number(value) / 1_000_000).toLocaleString('th-TH', { maximumFractionDigits: 0 })} ล.`
                                    },
                                    title: {
                                        display: true,
                                        text: 'ล้านบาท'
                                    },
                                    grid: {
                                        color: 'rgba(148,163,184,0.2)'
                                    }
                                }
                            }
                        },
                        plugins: [createBarValueLabelPlugin((v) => formatThaiMoney(v), { minValue: minLabelBudget })]
                    });
                }

                function getRelatedOutputs(rows) {
                    return (Array.isArray(rows) ? rows : []).flatMap((project) => {
                        const list = Array.isArray(project?.outputs) ? project.outputs : [];
                        return list
                            .filter(Boolean)
                            .map((item) => ({
                                ...item,
                                project_id: item?.project_id ?? project?.id ?? null,
                            }));
                    });
                }

                function getRelatedFtes(rows) {
                    return (Array.isArray(rows) ? rows : []).flatMap((project) => {
                        const list = Array.isArray(project?.fte_roles) ? project.fte_roles
                            : (Array.isArray(project?.fteRoles) ? project.fteRoles : []);
                        return list
                            .filter(Boolean)
                            .map((item) => ({
                                ...item,
                                project_id: item?.project_id ?? project?.id ?? null,
                            }));
                    });
                }

                function getRelatedIrbs(rows) {
                    return (Array.isArray(rows) ? rows : []).flatMap((project) => {
                        const list = Array.isArray(project?.irbs) ? project.irbs : [];
                        return list
                            .filter(Boolean)
                            .map((item) => ({
                                ...item,
                                project_id: item?.project_id ?? project?.id ?? null,
                            }));
                    });
                }

                function drawEmptyChart(canvas, text = 'ไม่มีข้อมูล') {
                    if (!canvas) return;
                    Chart.getChart(canvas)?.destroy();
                    if (canvas.id === 'chartFteSummary') {
                        clearFteAvatarLayer(canvas);
                    }
                    const card = canvas.closest('.lux-chart-card');
                    const shouldHideCard = typeof dashboardHasActiveFilters === 'function' && dashboardHasActiveFilters();
                    if (card && shouldHideCard) {
                        card.classList.add('hidden');
                    }
                    const parent = canvas.parentElement;
                    if (parent) {
                        if (getComputedStyle(parent).position === 'static') {
                            parent.style.position = 'relative';
                        }
                        let overlay = parent.querySelector('.chart-empty-state');
                        if (!overlay) {
                            overlay = document.createElement('div');
                            overlay.className = 'chart-empty-state';
                            overlay.style.position = 'absolute';
                            overlay.style.inset = '0';
                            overlay.style.display = 'flex';
                            overlay.style.alignItems = 'center';
                            overlay.style.justifyContent = 'center';
                            overlay.style.color = '#64748b';
                            overlay.style.fontSize = '14px';
                            overlay.style.pointerEvents = 'none';
                            overlay.style.textAlign = 'center';
                            overlay.style.padding = '1rem';
                            parent.appendChild(overlay);
                        }
                        overlay.textContent = text;
                        overlay.style.display = shouldHideCard ? 'none' : 'flex';
                    }
                    canvas.style.visibility = shouldHideCard ? 'hidden' : 'visible';
                }

                function clearEmptyChartState(canvas) {
                    if (!canvas) return;
                    canvas.style.visibility = '';
                    const card = canvas.closest('.lux-chart-card');
                    if (card) {
                        card.classList.remove('hidden');
                    }
                    const parent = canvas.parentElement;
                    const overlay = parent?.querySelector('.chart-empty-state');
                    if (overlay) overlay.remove();
                }

                function resetDashboardChartCanvases() {
                    document.querySelectorAll('canvas[id^="chart"]').forEach((canvas) => {
                        Chart.getChart(canvas)?.destroy();
                        clearEmptyChartState(canvas);
                        const replacement = canvas.cloneNode(false);
                        canvas.replaceWith(replacement);
                    });
                    clearFteAvatarLayer(document.getElementById('chartFteSummary'));
                }

                function renderOutputStackedFiltered(rows) {
                    const outputRows = getRelatedOutputs(rows);
                    const types = [...new Set(outputRows.map(outputType).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'th'));
                    const matrix = {};

                    outputRows.forEach((item) => {
                        const yearKey = outputYearKey(item);
                        const typeKey = outputType(item) || 'อื่นๆ';
                        matrix[yearKey] = matrix[yearKey] || {};
                        matrix[yearKey][typeKey] = (matrix[yearKey][typeKey] || 0) + 1;
                    });

                    const yearKeys = Object.keys(matrix);
                    const isNum = (value) => /^\d+$/.test(String(value || ''));
                    const sortedYears = yearKeys
                        .filter(isNum).map(Number).sort((a, b) => a - b).map(String)
                        .concat(yearKeys.filter((value) => !isNum(value)).sort((a, b) => String(a).localeCompare(String(b), 'th')));
                    const displayLabels = sortedYears.map((key) => outputYearLabelForKey(key));

                    window.outputYearLabelKeyMap = {};
                    displayLabels.forEach((label, index) => {
                        window.outputYearLabelKeyMap[label] = sortedYears[index];
                    });

                    const canvas = document.getElementById('chartOutputStacked');
                    if (!canvas) return;

                    if (!sortedYears.length || !types.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    const totalLabelPlugin = {
                        id: `outputTotal_${sortedYears.join('_')}`.slice(0, 48),
                        afterDatasetsDraw(chart) {
                            const { ctx, chartArea: { top, right } } = chart;
                            ctx.save();
                            ctx.font = '700 11px sans-serif';
                            ctx.fillStyle = '#111827';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';

                            chart.data.labels.forEach((_, index) => {
                                let total = 0;
                                let topBar = null;

                                chart.data.datasets.forEach((dataset, dsIndex) => {
                                    if (!chart.isDatasetVisible(dsIndex)) return;
                                    total += Number(dataset.data?.[index] || 0);
                                    const bar = chart.getDatasetMeta(dsIndex)?.data?.[index];
                                    if (bar && (!topBar || bar.y < topBar.y)) topBar = bar;
                                });

                                if (!(total > 0) || !topBar) return;
                                let { x, y } = topBar.tooltipPosition();
                                y -= 4;
                                if (y < top + 8) y = top + 8;
                                if (x > right - 2) x = right - 2;
                                ctx.fillText(String(total), x, y);
                            });

                            ctx.restore();
                        }
                    };

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: displayLabels,
                            datasets: types.map((type, index) => ({
                                label: type,
                                data: sortedYears.map((year) => Number(matrix[year]?.[type] || 0)),
                                backgroundColor: `hsl(${(index * 53) % 360} 70% 60%)`,
                                stack: 'output'
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 16
                                }
                            },
                            plugins: {
                                legend: buildMobileLegend(12),
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: buildMobileCategoryTicks(8, 4)
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grace: '12%',
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        },
                        plugins: [totalLabelPlugin]
                    });
                }

                function renderFundingSourceYearChartsFiltered(rows) {
                    const agg = { external: {}, internal: {} };

                    rows.forEach((project) => {
                        const type = (getFundingType(project) || '').trim();
                        const source = (getFundingSource(project) || 'ไม่ระบุ').trim();
                        const amount = Number(getBudget(project)) || 0;
                        const year = yearCE(project.start_year ?? project.year ?? project.funding_year);
                        if (!year) return;

                        const bucket = type === 'ภายนอก' ? agg.external : (type === 'ภายใน' ? agg.internal : null);
                        if (!bucket) return;
                        bucket[year] ??= {};
                        bucket[year][source] ??= { budget: 0 };
                        bucket[year][source].budget += amount;
                    });

                    const buildStacked = (canvasId, dataByYear) => {
                        const canvas = document.getElementById(canvasId);
                        if (!canvas) return;

                        const years = Object.keys(dataByYear).map(Number).sort((a, b) => a - b);
                        if (!years.length) {
                            drawEmptyChart(canvas);
                            return;
                        }

                        const allSources = Array.from(new Set(years.flatMap((year) => Object.keys(dataByYear[year] || {}))));
                        const totals = allSources.map((source) => ({
                            src: source,
                            total: years.reduce((sum, year) => sum + (dataByYear[year]?.[source]?.budget || 0), 0)
                        })).sort((a, b) => b.total - a.total);

                        const topSources = totals.slice(0, 8).map((item) => item.src);
                        const otherSources = totals.slice(8).map((item) => item.src);
                        const datasets = topSources.map((source) => ({
                            label: source,
                            data: years.map((year) => dataByYear[year]?.[source]?.budget || 0),
                            backgroundColor: colorForKey(source),
                            stack: 'budget'
                        }));

                        if (otherSources.length) {
                            datasets.push({
                                label: 'อื่นๆ',
                                data: years.map((year) => otherSources.reduce((sum, source) => sum + (dataByYear[year]?.[source]?.budget || 0), 0)),
                                backgroundColor: '#94a3b8',
                                stack: 'budget'
                            });
                        }

                        window.fundingSourceYearGroups = window.fundingSourceYearGroups || {};
                        window.fundingSourceYearGroups[canvasId] = { top: topSources, others: otherSources };

                        Chart.getChart(canvas)?.destroy();
                        new Chart(canvas, {
                            type: 'bar',
                            data: {
                                labels: years.map(yearLabelTH),
                                datasets
                            },
                            options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: isPhoneViewport() ? 54 : 18
                                }
                            },
                            plugins: {
                                legend: buildMobileLegend(12)
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: buildMobileCategoryTicks(8, 4)
                                },
                                    y: {
                                        stacked: true,
                                        beginAtZero: true,
                                        ticks: {
                                            callback: (value) => formatThaiMoney(value)
                                        }
                                    }
                                }
                            },
                            plugins: [stackTotalLabelPlugin]
                        });
                    };

                    buildStacked('chartExternalBySourceYear', agg.external);
                    buildStacked('chartInternalBySourceYear', agg.internal);
                }

                function renderPublicationPerYearFiltered(rows) {
                    const canvas = document.getElementById('chartPublicationPerYear');
                    if (!canvas) return;

                    const publicationRows = getRelatedOutputs(rows).filter((item) => isPublicationType(item.type));
                    const byYearCE = {};
                    publicationRows.forEach((item) => {
                        const year = normalizeYear(item.year) || 'ไม่ทราบปี';
                        byYearCE[year] = (byYearCE[year] || 0) + 1;
                    });

                    const knownYears = Object.keys(byYearCE).filter((year) => year !== 'ไม่ทราบปี')
                        .map(Number).sort((a, b) => a - b).map(String);
                    const yearKeys = byYearCE['ไม่ทราบปี'] ? [...knownYears, 'ไม่ทราบปี'] : knownYears;

                    if (!yearKeys.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    const labels = yearKeys.map((year) => year === 'ไม่ทราบปี' ? year : toBE(year));
                    const data = yearKeys.map((year) => byYearCE[year] || 0);
                    const maxValue = Math.max(0, ...data);

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Publications',
                                data,
                                backgroundColor: labels.map((_, index) => `hsl(${(index * 33) % 360} 70% 60%)`),
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 36
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grace: '20%',
                                    suggestedMax: maxValue > 0 ? Math.ceil(maxValue * 1.2) : undefined,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        },
                        plugins: [labelOutsideTop]
                    });
                }

                function renderAuthorRolePerYearFiltered(rows) {
                    const canvas = document.getElementById('chartAuthorRolePerYear');
                    const yearSelect = document.getElementById('yearFilter');
                    const prevButton = document.getElementById('btnPrevYear');
                    const nextButton = document.getElementById('btnNextYear');
                    const searchInput = document.getElementById('authorSearch');
                    const topNSelect = document.getElementById('topN');
                    if (!canvas) return;

                    if (!window.__dashboardAuthorRoleFilteredBound) {
                        [yearSelect, topNSelect].forEach((node) => node?.addEventListener('change', () => {
                            window.setTimeout(() => renderAuthorRolePerYearFiltered(getRows()), 0);
                        }));
                        searchInput?.addEventListener('input', () => {
                            window.setTimeout(() => renderAuthorRolePerYearFiltered(getRows()), 0);
                        });
                        prevButton?.addEventListener('click', () => {
                            window.setTimeout(() => renderAuthorRolePerYearFiltered(getRows()), 0);
                        });
                        nextButton?.addEventListener('click', () => {
                            window.setTimeout(() => renderAuthorRolePerYearFiltered(getRows()), 0);
                        });
                        window.__dashboardAuthorRoleFilteredBound = true;
                    }

                    const publicationRows = getRelatedOutputs(rows).filter((item) => isPublicationType(item.type));
                    const authorRoleByYear = {};
                    const yearSetCE = new Set();

                    publicationRows.forEach((item) => {
                        const year = normalizeYear(item.year);
                        if (!year) return;
                        yearSetCE.add(year);
                        const authors = Array.isArray(item.authors) ? item.authors : [];
                        authorRoleByYear[year] = authorRoleByYear[year] || {};
                        authors.forEach((author) => {
                            const name = (author.name || '').trim();
                            const role = (author.role || '').toLowerCase().trim();
                            if (!name || !ROLES.includes(role)) return;
                            authorRoleByYear[year][name] = authorRoleByYear[year][name] || { first: 0, co: 0, corresponding: 0 };
                            authorRoleByYear[year][name][role] += 1;
                        });
                    });

                    const yearsCE = Array.from(yearSetCE).map(Number).sort((a, b) => a - b).map(String);
                    const previousValue = yearSelect?.value || '';

                    if (yearSelect) {
                        yearSelect.innerHTML = '';
                        yearsCE.forEach((year) => {
                            const option = document.createElement('option');
                            option.value = year;
                            option.textContent = toBE(year);
                            yearSelect.appendChild(option);
                        });
                        yearSelect.value = yearsCE.includes(previousValue) ? previousValue : (yearsCE[yearsCE.length - 1] || '');
                    }

                    if (!yearsCE.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    const selectedYear = yearSelect?.value || yearsCE[yearsCE.length - 1];
                    const searchTerm = String(searchInput?.value || '').trim().toLowerCase();
                    const topN = parseInt(topNSelect?.value || '12', 10);
                    const source = authorRoleByYear[selectedYear] || {};

                    let authorRows = Object.entries(source).map(([name, roles]) => ({
                        name,
                        ...roles,
                        total: (roles.first || 0) + (roles.co || 0) + (roles.corresponding || 0)
                    }));

                    if (searchTerm) {
                        authorRows = authorRows.filter((item) => item.name.toLowerCase().includes(searchTerm));
                    }

                    authorRows.sort((a, b) => b.total - a.total);
                    const topRows = authorRows.slice(0, topN);

                    if (!topRows.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    clearEmptyChartState(canvas);
                    canvas.height = Math.min(Math.max(280, topRows.length * 20), 420);
                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: topRows.map((item) => item.name),
                            datasets: [
                                { key: 'first', label: 'First-author' },
                                { key: 'co', label: 'Co-author' },
                                { key: 'corresponding', label: 'Corresponding' }
                            ].map((config, index) => ({
                                label: config.label,
                                data: topRows.map((item) => item[config.key] || 0),
                                backgroundColor: `hsl(${(index * 48) % 360} 70% 60%)`,
                                stack: 'roles',
                                borderWidth: 1
                            }))
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            layout: {
                                padding: {
                                    right: 36
                                }
                            },
                            plugins: {
                                legend: buildMobileLegend(12),
                                title: {
                                    display: true,
                                    text: `ปี ${toBE(selectedYear)} (พ.ศ.)`
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grace: '15%',
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                y: {
                                    stacked: true,
                                    ticks: buildMobileHorizontalTicks(topRows.map((item) => item.name), 14)
                                }
                            }
                        },
                        plugins: [stackedTotalRight]
                    });
                }

                function renderStatusYearFiltered(rows) {
                    const canvas = document.getElementById('chartStatusYear');
                    if (!canvas) return;

                    const statuses = [
                        'Project Planing (เริ่มต้น - submit IRB)',
                        'เก็บข้อมูล',
                        'วิเคราะห์ข้อมูล',
                        'เขียนรายงานและpublication',
                        'ปิดโปรเจค',
                        'ยกเลิกโปรเจค',
                        'ไม่ระบุสถานะ'
                    ];

                    const statusData = {};
                    rows.forEach((project) => {
                        const year = getYearLabel(project);
                        const status = getStatusYearChartLabel(project);
                        statusData[year] = statusData[year] || Object.fromEntries(statuses.map((item) => [item, 0]));
                        statusData[year][status] += 1;
                    });

                    const years = sortYearLabels(Object.keys(statusData));
                    if (!years.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    const dataLabelPlugin = {
                        id: `statusLabel_${years.join('_')}`.slice(0, 48),
                        afterDatasetsDraw(chart) {
                            const { ctx, chartArea: { top, right } } = chart;
                            ctx.save();
                            ctx.font = 'bold 11px sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            ctx.fillStyle = '#111';

                            const labels = chart.data.labels || [];
                            labels.forEach((_, index) => {
                                let total = 0;
                                let topBar = null;
                                chart.data.datasets.forEach((dataset, dsIndex) => {
                                    if (!chart.isDatasetVisible(dsIndex)) return;
                                    const value = Number(dataset.data[index] || 0);
                                    if (!(value > 0)) return;
                                    total += value;
                                    const bar = chart.getDatasetMeta(dsIndex)?.data?.[index];
                                    if (!bar) return;
                                    if (!topBar || bar.y < topBar.y) topBar = bar;
                                });

                                if (!(total > 0) || !topBar) return;
                                let drawX = topBar.x;
                                let drawY = topBar.y - 4;
                                if (drawY < top + 10) drawY = top + 10;
                                if (drawX > right - 2) drawX = right - 2;
                                ctx.fillText(String(total), drawX, drawY);
                            });

                            ctx.restore();
                        }
                    };

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: years,
                            datasets: statuses.map((status, index) => ({
                                label: status,
                                data: years.map((year) => statusData[year]?.[status] ?? 0),
                                backgroundColor: `hsl(${(index * 46) % 360} 70% 60%)`,
                                stack: 'status'
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 16
                                }
                            },
                            plugins: {
                                legend: buildMobileLegend(12)
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: buildMobileCategoryTicks(8, 4)
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grace: '12%',
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        },
                        plugins: [dataLabelPlugin]
                    });
                }

                function renderCollabBreakdownFiltered(rows) {
                    const canvas = document.getElementById('chartCollabBreakdown');
                    if (!canvas) return;

                    const categories = [
                        'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
                        'โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ',
                        'โครงการวิจัยนอกคณะ'
                    ];
                    const normalizeUnitStatus = (raw) => {
                        const text = (raw ?? '').toString().trim();
                        if (!text) return null;
                        if (text.includes('ภายในหน่วยวิจัย') && text.includes('สุขภา')) return categories[0];
                        if (text.includes('นอกหน่วย') && text.includes('คณะ')) return categories[1];
                        if (text.includes('วิจัยนอกคณะ')) return categories[2];
                        return null;
                    };

                    const years = sortYearLabels(Array.from(new Set(rows.map(getYearLabel))));
                    if (!years.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    const byYearCategory = {};
                    years.forEach((year) => {
                        byYearCategory[year] = Object.fromEntries(categories.map((category) => [category, 0]));
                    });

                    rows.forEach((project) => {
                        const year = getYearLabel(project);
                        const category = normalizeUnitStatus(project.project_list);
                        if (!category) return;
                        byYearCategory[year] = byYearCategory[year] || Object.fromEntries(categories.map((item) => [item, 0]));
                        byYearCategory[year][category] += 1;
                    });
                    const hasCollabData = years.some((year) => categories.some((category) => Number(byYearCategory[year]?.[category] ?? 0) > 0));
                    if (!hasCollabData) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    const dataLabelPlugin = {
                        id: `collabLabel_${years.join('_')}`.slice(0, 48),
                        afterDatasetsDraw(chart) {
                            const { ctx, chartArea: { top, right } } = chart;
                            ctx.save();
                            ctx.font = 'bold 11px sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            ctx.fillStyle = '#111827';

                            chart.data.datasets.forEach((dataset, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                const meta = chart.getDatasetMeta(dsIndex);
                                meta.data.forEach((bar, index) => {
                                    const value = Number(dataset.data[index] || 0);
                                    if (!(value > 0)) return;
                                    let x = bar.x;
                                    let y = bar.y - 3;
                                    if (y < top + 8) y = top + 8;
                                    if (x > right - 2) x = right - 2;
                                    ctx.fillText(String(value), x, y);
                                });
                            });

                            ctx.restore();
                        }
                    };

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: years,
                            datasets: categories.map((category, index) => ({
                                label: category,
                                data: years.map((year) => byYearCategory[year]?.[category] ?? 0),
                                backgroundColor: `hsl(${(index * 60) % 360} 70% 60%)`,
                                stack: 'collab'
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 16
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: buildMobileCategoryTicks(8, 4)
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grace: '12%',
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        },
                        plugins: [dataLabelPlugin]
                    });
                }

                function renderYearlyLifecycleFiltered(rows) {
                    const canvas = document.getElementById('chartYearlyNewVsTotal');
                    if (!canvas) return;

                    const unknownBucketKey = '__unknown__';
                    const unknownBucketLabel = 'ไม่ระบุปี';
                    const unknownProjects = (rows || []).filter((project) => !Number.isFinite(projectLifecycleStartYearCE(project)));
                    const startYears = rows.map((project) => projectLifecycleStartYearCE(project)).filter(Number.isFinite);
                    if (!startYears.length && !unknownProjects.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    const eventYears = rows
                        .filter((project) => {
                            const key = projectLifecycleStatusKey(project);
                            return key === 'completed' || key === 'cancelled';
                        })
                        .map((project) => projectLifecycleEventYearCE(project))
                        .filter(Number.isFinite);

                    const currentYearCE = new Date().getFullYear();
                    const maxYearCE = startYears.length
                        ? Math.max(...startYears, ...eventYears, currentYearCE)
                        : currentYearCE;
                    window.yearlyLifecycleHorizonCE = maxYearCE;

                    const yearsCE = [];
                    if (startYears.length) {
                        const minYearCE = Math.min(...startYears);
                        for (let year = minYearCE; year <= maxYearCE; year += 1) yearsCE.push(year);
                    }
                    const buckets = [...yearsCE, ...(unknownProjects.length ? [unknownBucketKey] : [])];
                    const yearLabels = buckets.map((bucket) => bucket === unknownBucketKey ? unknownBucketLabel : yearLabelTH(bucket));
                    window.yearlyNewVsTotalLabelMap = {};
                    yearLabels.forEach((label, index) => {
                        window.yearlyNewVsTotalLabelMap[label] = buckets[index];
                    });

                    const totalPerYear = {};
                    const newPerYear = {};
                    const inProgressPerYear = {};
                    const completedPerYear = {};
                    const cancelledPerYear = {};

                    yearsCE.forEach((year) => {
                        totalPerYear[year] = rows.filter((project) => {
                            const startYear = projectLifecycleStartYearCE(project);
                            if (Number.isFinite(startYear)) return startYear <= year;
                            return year === maxYearCE;
                        }).length;
                        newPerYear[year] = rows.filter((project) => projectLifecycleBucketAtYear(project, year, maxYearCE) === 'new').length;
                        inProgressPerYear[year] = rows.filter((project) => projectLifecycleBucketAtYear(project, year, maxYearCE) === 'in_progress').length;
                        completedPerYear[year] = rows.filter((project) => projectLifecycleBucketAtYear(project, year, maxYearCE) === 'completed').length;
                        cancelledPerYear[year] = rows.filter((project) => projectLifecycleBucketAtYear(project, year, maxYearCE) === 'cancelled').length;
                    });
                    if (unknownProjects.length) {
                        totalPerYear[unknownBucketKey] = unknownProjects.length;
                        newPerYear[unknownBucketKey] = 0;
                        inProgressPerYear[unknownBucketKey] = unknownProjects.filter((project) => projectLifecycleStatusKey(project) === 'in_progress').length;
                        completedPerYear[unknownBucketKey] = unknownProjects.filter((project) => projectLifecycleStatusKey(project) === 'completed').length;
                        cancelledPerYear[unknownBucketKey] = unknownProjects.filter((project) => projectLifecycleStatusKey(project) === 'cancelled').length;
                    }

                    const valueLabelPlugin = {
                        id: `lifecycleLabel_${yearsCE.join('_')}`.slice(0, 48),
                        afterDatasetsDraw(chart) {
                            const { ctx, chartArea: { top, right } } = chart;
                            ctx.save();
                            ctx.font = 'bold 11px sans-serif';
                            ctx.fillStyle = '#111827';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';

                            const totalIndex = chart.data.datasets.findIndex((dataset) => String(dataset.label || '').trim() === 'ทั้งหมด');
                            if (totalIndex >= 0 && chart.isDatasetVisible(totalIndex)) {
                                const dataset = chart.data.datasets[totalIndex];
                                const meta = chart.getDatasetMeta(totalIndex);
                                meta.data.forEach((point, index) => {
                                    const value = Number(dataset.data[index] || 0);
                                    if (!(value > 0)) return;
                                    let x = point.x;
                                    let y = point.y - 10;
                                    if (y < top + 10) y = top + 10;
                                    if (x > right - 6) x = right - 6;
                                    ctx.fillText(String(value), x, y);
                                });
                            }

                            ctx.restore();
                        }
                    };

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: yearLabels,
                            datasets: [
                                {
                                    label: 'ทั้งหมด',
                                    data: buckets.map((bucket) => totalPerYear[bucket] || 0),
                                    type: 'line',
                                    borderColor: '#60a5fa',
                                    backgroundColor: '#60a5fa',
                                    pointBackgroundColor: '#60a5fa',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 1.5,
                                    pointRadius: 4,
                                    pointHoverRadius: 7,
                                    borderWidth: 3,
                                    tension: 0.25,
                                    fill: false,
                                    order: 0,
                                    stack: 'totalLine'
                                },
                                {
                                    label: 'ใหม่',
                                    data: buckets.map((bucket) => newPerYear[bucket] || 0),
                                    backgroundColor: '#34d399',
                                    stack: 'status',
                                    order: 1
                                },
                                {
                                    label: 'กำลังดำเนินการ',
                                    data: buckets.map((bucket) => inProgressPerYear[bucket] || 0),
                                    backgroundColor: '#fbbf24',
                                    stack: 'status',
                                    order: 1
                                },
                                {
                                    label: 'ปิดโปรเจค',
                                    data: buckets.map((bucket) => completedPerYear[bucket] || 0),
                                    backgroundColor: '#ef4444',
                                    stack: 'status',
                                    order: 1
                                },
                                {
                                    label: 'ยกเลิกโปรเจค',
                                    data: buckets.map((bucket) => cancelledPerYear[bucket] || 0),
                                    backgroundColor: '#f97316',
                                    stack: 'status',
                                    order: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            layout: {
                                padding: {
                                    top: 16,
                                    right: 16
                                }
                            },
                            plugins: {
                                legend: buildMobileLegend(12)
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: buildMobileCategoryTicks(8, 4)
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grace: '12%',
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        },
                        plugins: [valueLabelPlugin]
                    });
                }

                function renderFteSummaryFiltered(rows) {
                    const canvas = document.getElementById('chartFteSummary');
                    if (!canvas) return;

                    const fteRows = getRelatedFtes(rows);
                    const datasets = buildFteSummaryDatasets(fteRows);

                    if (!datasets.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bubble',
                        data: {
                            datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 18,
                                    right: 18,
                                    bottom: 12,
                                    left: 12
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    displayColors: false,
                                    callbacks: {
                                        label: (ctx) => `${ctx.dataset.label}: โครงการ ${ctx.raw.x} / FTE ${Number(ctx.raw.y || 0).toFixed(2)}`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true
                                },
                                y: {
                                    beginAtZero: true
                                }
                            }
                        },
                        plugins: [fteAvatarBubblePlugin]
                    });
                }

                function renderRedZoneAndOverdueFiltered(rows) {
                    const now = new Date();
                    const overdueProjects = buildOverdueRecords(rows, now);
                    const activeOverdueProjects = overdueProjects.filter(record =>
                        record.sourceCategory === 'open_system_late' || record.sourceCategory === 'open_team_late'
                    );
                    const redZoneYears = getRedZoneRecentYears(now);
                    const timelineCanvas = document.getElementById('chartRedZoneTimeline');
                    if (timelineCanvas) {
                        Chart.getChart(timelineCanvas)?.destroy();
                        const timelineDatasets = [
                            {
                                label: 'ครบกำหนดแต่ยังไม่ลงปิดโปรเจคในระบบ',
                                redzoneSourceCategory: 'open_system_late',
                                data: redZoneYears.map((year) => overdueProjects.filter((record) =>
                                    record.contractEndYearCE === year &&
                                    record.sourceCategory === 'open_system_late'
                                ).length),
                                backgroundColor: '#dc2626',
                            },
                            {
                                label: 'ทำงานเกินสัญญาและยังไม่ปิดโปรเจค',
                                redzoneSourceCategory: 'open_team_late',
                                data: redZoneYears.map((year) => overdueProjects.filter((record) =>
                                    record.contractEndYearCE === year &&
                                    record.sourceCategory === 'open_team_late'
                                ).length),
                                backgroundColor: '#d97706',
                            },
                            {
                                label: 'ทำงานเกินสัญญาแต่ปิดโปรเจคแล้ว',
                                redzoneSourceCategory: 'closed_late',
                                data: redZoneYears.map((year) => overdueProjects.filter((record) =>
                                    record.contractEndYearCE === year &&
                                    record.sourceCategory === 'closed_late'
                                ).length),
                                backgroundColor: '#7c3aed',
                            }
                        ];
                        const hasTimelineData = timelineDatasets.some((dataset) =>
                            Array.isArray(dataset.data) && dataset.data.some((value) => Number(value) > 0)
                        );
                        if (!hasTimelineData) {
                            drawEmptyChart(timelineCanvas);
                        } else {
                        const barValuePlugin = {
                            id: 'barValueRedZoneStackedFiltered',
                            afterDatasetsDraw(chart) {
                                const {
                                    ctx,
                                    chartArea: {
                                        top,
                                        right
                                    }
                                } = chart;
                                ctx.save();
                                ctx.font = 'bold 11px sans-serif';
                                ctx.fillStyle = '#111827';
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'bottom';

                                chart.data.datasets.forEach((ds, dsIndex) => {
                                    if (!chart.isDatasetVisible(dsIndex)) return;
                                    chart.getDatasetMeta(dsIndex)?.data?.forEach((bar, i) => {
                                        const value = Number(ds.data?.[i] || 0);
                                        if (!bar || !isFinite(value) || value <= 0) return;
                                        let { x, y } = bar.tooltipPosition();
                                        y -= 4;
                                        if (y < top + 8) y = top + 8;
                                        if (x > right - 2) x = right - 2;
                                        ctx.fillText(String(value), x, y);
                                    });
                                });

                                ctx.restore();
                            }
                        };
                        new Chart(timelineCanvas, {
                            type: 'bar',
                            data: {
                                labels: redZoneYears.map(year => yearLabelTH(year)),
                                datasets: timelineDatasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            },
                            plugins: [barValuePlugin]
                        });
                        }
                    }

                    const overdueCanvas = document.getElementById('chartOverdueProjects');
                    if (overdueCanvas) {
                        const overviewGroups = buildOverdueOverviewSummary(rows, now);
                        const chartGroups = overviewGroups.filter((group) => group.count > 0);
                        const totalProjects = chartGroups.reduce((sum, group) => sum + group.count, 0);
                        Chart.getChart(overdueCanvas)?.destroy();
                    if (!totalProjects) {
                        drawEmptyChart(overdueCanvas);
                        renderInlineLegend('chartOverdueLegend', []);
                    } else {
                        overdueCanvas.$overdueOverviewKeys = chartGroups.map((group) => group.key);
                        renderInlineLegend('chartOverdueLegend', chartGroups);
                        const overdueCalloutPlugin = createDoughnutCalloutPlugin('doughnutCalloutOverdueFiltered');
                        const overdueValuePlugin = createDoughnutValueLabelPlugin('doughnutValueOverdueFiltered');
                        new Chart(overdueCanvas, {
                            type: 'doughnut',
                            data: {
                                labels: chartGroups.map((group) => group.label),
                                datasets: [{
                                    data: chartGroups.map((group) => group.count),
                                    backgroundColor: chartGroups.map((group) => group.color),
                                    borderColor: '#ffffff',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '64%',
                                layout: {
                                    padding: {
                                        left: 36,
                                        right: 36,
                                        top: 36,
                                        bottom: 44
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => totalProjects ? ` ${ctx.label}: ${ctx.parsed || 0} โครงการ` : ' ไม่มีข้อมูล'
                                        }
                                    }
                                }
                            },
                            plugins: isPhoneViewport() ? [overdueValuePlugin] : [overdueCalloutPlugin]
                        });
                    }
                    }

                    const overdueBadge = document.getElementById('overdueCountBadge');
                    if (overdueBadge) overdueBadge.textContent = `${activeOverdueProjects.length} โครงการ`;
                    const overdueDrawerCard = document.querySelector('[data-lux-drawer="overdue"]');
                    if (overdueDrawerCard) {
                        overdueDrawerCard.classList.toggle('hidden', activeOverdueProjects.length === 0);
                    }

                    const state = window.filteredOverdueTableState || { page: 1, pageSize: 10, bound: false };
                    state.rows = activeOverdueProjects.slice().sort((a, b) => {
                        if (b.overdueDays !== a.overdueDays) return b.overdueDays - a.overdueDays;
                        return a.contractEndDate - b.contractEndDate;
                    });
                    window.filteredOverdueTableState = state;

                    const tbody = document.getElementById('overdueTableBody');
                    const prevButton = document.getElementById('overduePrev');
                    const nextButton = document.getElementById('overdueNext');
                    const pagination = document.getElementById('overduePagination');

                    const renderOverdueTable = () => {
                        if (!tbody) return;
                        const totalPages = Math.max(Math.ceil(state.rows.length / state.pageSize), 1);
                        state.page = Math.min(Math.max(state.page, 1), totalPages);
                        const start = (state.page - 1) * state.pageSize;
                        const pageRows = state.rows.slice(start, start + state.pageSize);

                        tbody.innerHTML = '';
                        if (!pageRows.length) {
                            tbody.innerHTML = `<tr class="border-t"><td colspan="6" class="p-3 text-center text-gray-500">ไม่มีโครงการที่เกินกำหนด</td></tr>`;
                        } else {
                            pageRows.forEach((item) => {
                                const row = document.createElement('tr');
                                row.className = 'border-t';
                                row.innerHTML = `
                                    <td class="p-2 text-left text-blue-700 hover:underline" data-label="โครงการ">
                                        <a href="/rm/projects/${item.project.id}" target="_blank" rel="noopener">${escapeHtml(item.project.project_name_th || '-')}</a>
                                        <div class="mt-1 text-[11px] font-normal text-slate-500">${escapeHtml(overdueSourceLabel(item))}</div>
                                    </td>
                                    <td class="p-2 text-center" data-label="วันที่สิ้นสุดสัญญา (พ.ศ.)">${escapeHtml(overdueRecordEndDateLabel(item))}</td>
                                    <td class="p-2 text-center" data-label="วันที่สิ้นสุดการทำงานทีม">${escapeHtml(overdueRecordTeamEndDateLabel(item))}</td>
                                    <td class="p-2 text-center text-red-600 font-semibold" data-label="เกินกำหนด (วัน)">
                                        <div>${item.overdueDays}</div>
                                    </td>
                                    <td class="p-2 text-center" data-label="สถานะโครงการ">${escapeHtml(item.statusLabel || '-')}</td>
                                    <td class="p-2 text-center" data-label="PI">${escapeHtml(item.project.pi_name || '-')}</td>
                                `;
                                tbody.appendChild(row);
                            });
                        }

                        if (pagination) pagination.textContent = `หน้า ${state.page} / ${totalPages}`;
                        if (prevButton) prevButton.disabled = state.page <= 1;
                        if (nextButton) nextButton.disabled = state.page >= totalPages;
                    };

                    if (!state.bound) {
                        prevButton?.addEventListener('click', () => {
                            state.page = Math.max(state.page - 1, 1);
                            renderOverdueTable();
                        });
                        nextButton?.addEventListener('click', () => {
                            state.page += 1;
                            renderOverdueTable();
                        });
                        state.bound = true;
                    }

                    renderOverdueTable();

                    const coResearchCanvas = document.getElementById('chartCoResearchByUnitWindow');
                    if (coResearchCanvas) {
                        const unitLabels = new Set();
                        rows.forEach((project) => {
                            const endDate = parseProjectDate(getEndDateRaw(project));
                            if (!endDate) return;
                            const diffMonths = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24 * 30.4375);
                            if (diffMonths < 0 || diffMonths > 12) return;
                            uniqueCoResearch(getCoResearchList(project)).forEach((member) => {
                                unitLabels.add(coResearchUnitLabel(member));
                            });
                        });

                        const unitList = [...unitLabels].sort((a, b) => a.localeCompare(b, 'th'));
                        if (!unitList.length) {
                            drawEmptyChart(coResearchCanvas);
                            return;
                        }

                        const countByUnitWithin = (unit, maxMonths) => rows.reduce((total, project) => {
                            const endDate = parseProjectDate(getEndDateRaw(project));
                            if (!endDate) return total;
                            const diffMonths = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24 * 30.4375);
                            if (diffMonths < 0 || diffMonths > maxMonths) return total;
                            const members = coResearchMembers(project).filter((member) => member.unitLabel === unit);
                            return total + (members.length ? 1 : 0);
                        }, 0);
                        const coResearchDatasets = [
                            { label: 'ภายใน 3 เดือน', data: unitList.map((unit) => countByUnitWithin(unit, 3)), backgroundColor: '#0f766e' },
                            { label: 'ภายใน 6 เดือน', data: unitList.map((unit) => countByUnitWithin(unit, 6)), backgroundColor: '#c0841a' },
                            { label: 'ภายใน 1 ปี', data: unitList.map((unit) => countByUnitWithin(unit, 12)), backgroundColor: '#475569' }
                        ];
                        const hasCoResearchData = coResearchDatasets.some((dataset) => dataset.data.some((value) => Number(value) > 0));
                        if (!hasCoResearchData) {
                            drawEmptyChart(coResearchCanvas);
                            return;
                        }

                        Chart.getChart(coResearchCanvas)?.destroy();
                        const barValuePlugin = {
                            id: 'barValueCoResearchFiltered',
                            afterDatasetsDraw(chart) {
                                const {
                                    ctx,
                                    chartArea: {
                                        top,
                                        right
                                    }
                                } = chart;
                                ctx.save();
                                ctx.font = 'bold 11px sans-serif';
                                ctx.fillStyle = '#111827';
                                const horizontal = chart.options?.indexAxis === 'y';
                                ctx.textAlign = horizontal ? 'left' : 'center';
                                ctx.textBaseline = horizontal ? 'middle' : 'bottom';
                                chart.data.datasets.forEach((ds, dsIndex) => {
                                    if (!chart.isDatasetVisible(dsIndex)) return;
                                    const meta = chart.getDatasetMeta(dsIndex);
                                    meta.data.forEach((bar, i) => {
                                        const v = Number(ds.data[i] || 0);
                                        if (!isFinite(v) || v <= 0) return;
                                        let x = horizontal ? bar.x + 6 : bar.x;
                                        let y = horizontal ? bar.y : bar.y - 3;
                                        if (!horizontal && y < top + 8) y = top + 8;
                                        if (x > right - 2) x = right - 2;
                                        ctx.fillText(String(v), x, y);
                                    });
                                });
                                ctx.restore();
                            }
                        };
                        new Chart(coResearchCanvas, {
                            type: 'bar',
                            data: {
                                labels: unitList,
                                datasets: coResearchDatasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: isPhoneViewport() ? 'y' : 'x',
                                plugins: {
                                    legend: buildMobileLegend(14)
                                },
                                scales: {
                                    y: {
                                        ticks: isPhoneViewport() ? buildMobileHorizontalTicks(unitList, 16) : {
                                            autoSkip: false
                                        }
                                    },
                                    x: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            },
                            plugins: [barValuePlugin]
                        });
                    }
                }

                function renderIrbExpiryFiltered(rows) {
                    const canvas = document.getElementById('chartIRBExpiry');
                    if (!canvas) return;

                    const group = {};
                    getRelatedIrbs(rows).forEach((irbItem) => {
                        const expDate = parseProjectDate(irbItem.exp_date);
                        if (!expDate) return;
                        const monthsAhead = (expDate - new Date()) / (1000 * 60 * 60 * 24 * 30);
                        if (monthsAhead >= 0 && monthsAhead <= 6) {
                            const key = irbExpiryBucketKey(expDate);
                            if (!key) return;
                            group[key] = (group[key] || 0) + 1;
                        }
                    });

                    const bucketKeys = Object.keys(group).sort();
                    if (!bucketKeys.length) {
                        drawEmptyChart(canvas);
                        return;
                    }

                    const labels = bucketKeys.map(irbExpiryBucketLabel);
                    Chart.getChart(canvas)?.destroy();
                    const chart = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'IRB ใกล้หมดอายุ',
                                data: bucketKeys.map((key) => group[key]),
                                backgroundColor: '#ef4444'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 14
                                }
                            },
                            plugins: {
                                legend: buildMobileLegend(14)
                            },
                            scales: {
                                x: {
                                    ticks: buildMobileCategoryTicks(7, 4)
                                },
                                y: {
                                    beginAtZero: true,
                                    grace: '12%',
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                    chart.$irbExpiryKeys = bucketKeys;
                }

                function renderFundingSourceTopChartsFiltered(rows) {
                    const TOP_N_SOURCES = 5;
                    const FUNDING_COLORS = ['#dc4b4b', '#d4a153', '#9acb4b', '#4fbf5f', '#3cb9c8', '#5f88d6', '#7b5ac8'];

                    const renderSingle = (canvasId, targetType, groupKey) => {
                        const canvas = document.getElementById(canvasId);
                        if (!canvas) return;

                        const countBySource = {};
                        const budgetBySource = {};
                        (Array.isArray(rows) ? rows : []).forEach((project) => {
                            if ((getFundingType(project) || '').trim() !== targetType) return;
                            const source = (getFundingSource(project) || 'ไม่ระบุ').toString().trim();
                            const budget = Number(getBudget(project)) || 0;
                            countBySource[source] = (countBySource[source] || 0) + 1;
                            budgetBySource[source] = (budgetBySource[source] || 0) + budget;
                        });

                        const entries = Object.keys(countBySource).map((label) => ({
                            label,
                            count: countBySource[label],
                            budget: budgetBySource[label]
                        })).sort((a, b) => (b.count - a.count) || (b.budget - a.budget));

                        const topEntries = entries.slice(0, TOP_N_SOURCES);
                        const otherEntries = entries.slice(TOP_N_SOURCES);
                        if (otherEntries.length) {
                            topEntries.push({
                                label: 'อื่นๆ',
                                count: otherEntries.reduce((sum, item) => sum + item.count, 0),
                                budget: otherEntries.reduce((sum, item) => sum + item.budget, 0),
                            });
                        }

                        window.fundingSourceGroups = window.fundingSourceGroups || {};
                        window.fundingSourceGroups[groupKey] = {
                            top: entries.slice(0, TOP_N_SOURCES).map((item) => item.label),
                            others: otherEntries.map((item) => item.label)
                        };

                        Chart.getChart(canvas)?.destroy();
                        if (!topEntries.length) {
                            drawEmptyChart(canvas);
                            return;
                        }
                        clearEmptyChartState(canvas);

                        const totalCount = topEntries.reduce((sum, item) => sum + item.count, 0);
                        const detailPlugin = {
                            id: `${canvasId}_detail_plugin`,
                            afterDatasetsDraw(chart) {
                                const dataset = chart.data.datasets?.[0];
                                const meta = chart.getDatasetMeta(0);
                                if (!dataset || !meta?.data?.length) return;
                                const { ctx, chartArea: { left, right, top, bottom } } = chart;
                                ctx.save();
                                ctx.font = '700 11px "IBM Plex Sans Thai", sans-serif';
                                ctx.fillStyle = '#0f172a';
                                ctx.strokeStyle = 'rgba(100,116,139,0.5)';
                                ctx.lineWidth = 1;
                                ctx.textBaseline = 'middle';

                                meta.data.forEach((arc, index) => {
                                    const value = Number(dataset.data?.[index] || 0);
                                    if (!(value > 0)) return;
                                    const labelText = `${value} (${formatThaiMoney(topEntries[index]?.budget || 0)})`;
                                    const textWidth = ctx.measureText(labelText).width;
                                    const angle = (arc.startAngle + arc.endAngle) / 2;
                                    const isRight = Math.cos(angle) >= 0;
                                    const sx = arc.x + Math.cos(angle) * (arc.outerRadius - 1);
                                    const sy = arc.y + Math.sin(angle) * (arc.outerRadius - 1);
                                    const mx = arc.x + Math.cos(angle) * (arc.outerRadius + 10);
                                    let my = arc.y + Math.sin(angle) * (arc.outerRadius + 10);
                                    let tx = mx + (isRight ? 12 : -12);

                                    if (isRight) {
                                        if (tx + textWidth > right - 4) tx = right - textWidth - 4;
                                        if (tx < left + 4) tx = left + 4;
                                        ctx.textAlign = 'left';
                                    } else {
                                        if (tx - textWidth < left + 4) tx = left + textWidth + 4;
                                        if (tx > right - 4) tx = right - 4;
                                        ctx.textAlign = 'right';
                                    }

                                    if (my < top + 10) my = top + 10;
                                    if (my > bottom - 10) my = bottom - 10;

                                    ctx.beginPath();
                                    ctx.moveTo(sx, sy);
                                    ctx.lineTo(mx, my);
                                    ctx.lineTo(tx + (isRight ? -4 : 4), my);
                                    ctx.stroke();
                                    ctx.fillText(labelText, tx, my);
                                });

                                const center = meta.data[0];
                                if (center) {
                                    ctx.textAlign = 'center';
                                    ctx.fillStyle = '#334155';
                                    ctx.font = '700 12px "IBM Plex Sans Thai", sans-serif';
                                    ctx.fillText(`รวม ${totalCount}`, center.x, center.y);
                                }

                                ctx.restore();
                            }
                        };

                        new Chart(canvas, {
                            type: 'doughnut',
                            data: {
                                labels: topEntries.map((item) => item.label),
                                datasets: [{
                                    data: topEntries.map((item) => item.count),
                                    backgroundColor: topEntries.map((_, index) => FUNDING_COLORS[index % FUNDING_COLORS.length]),
                                    borderColor: '#ffffff',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '55%',
                                layout: {
                                    padding: {
                                        left: 24,
                                        right: 24,
                                        top: 12,
                                        bottom: 12
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => {
                                                const item = topEntries[ctx.dataIndex];
                                                const count = Number(item?.count || 0);
                                                const budget = Number(item?.budget || 0);
                                                const pct = totalCount ? ((count / totalCount) * 100).toFixed(1) : '0.0';
                                                return ` ${ctx.label}: ${count} งาน | ${formatThaiMoney(budget)} (${pct}%)`;
                                            }
                                        }
                                    }
                                }
                            },
                            plugins: [detailPlugin]
                        });
                    };

                    renderSingle('chartExternalBySource', 'ภายนอก', 'external');
                    renderSingle('chartInternalBySource', 'ภายใน', 'internal');
                }

                function renderPiByUnitChartFiltered(rows) {
                    const TOP_N = 20;
                    const PI_COLORS = ['#4f7ac8', '#4fbf5f', '#d4a153', '#ef4444', '#7b5ac8', '#3cb9c8', '#0ea5e9'];
                    const shorten = (text, max = 32) => {
                        const value = String(text || '').trim();
                        return value.length > max ? `${value.slice(0, max - 1)}…` : value;
                    };
                    const valueLabelRight = {
                        id: 'valueLabelRightPiFiltered',
                        afterDatasetsDraw(chart) {
                            const {
                                ctx,
                                chartArea: {
                                    left,
                                    right,
                                    top,
                                    bottom
                                },
                                scales: {
                                    x: scaleX
                                }
                            } = chart;
                            const ds = chart.data.datasets?.[0];
                            const meta = chart.getDatasetMeta(0);
                            if (!ds || !meta?.data?.length) return;

                            ctx.save();
                            ctx.font = '700 11px "IBM Plex Sans Thai", sans-serif';
                            ctx.fillStyle = '#111827';
                            ctx.textBaseline = 'middle';
                            ctx.textAlign = 'left';

                            meta.data.forEach((bar, index) => {
                                const value = Number(ds.data?.[index] || 0);
                                if (!(value > 0)) return;

                                const text = String(value);
                                const textWidth = ctx.measureText(text).width;
                                let drawX = scaleX.getPixelForValue(value) + 8;
                                const maxX = right - textWidth - 2;
                                if (drawX > maxX) drawX = maxX;
                                if (drawX < left + 2) drawX = left + 2;

                                let drawY = bar.y;
                                if (drawY < top + 10) drawY = top + 10;
                                if (drawY > bottom - 10) drawY = bottom - 10;

                                ctx.fillText(text, drawX, drawY);
                            });

                            ctx.restore();
                        }
                    };

                    const sourceRows = Array.isArray(rows) ? rows : [];
                    const filteredRows = sourceRows.filter((project) => isPiInPolicyUnit(project));
                    const countByPI = {};
                    filteredRows.forEach((project) => {
                        const name = (project.pi_name || '').toString().trim();
                        if (!name || name === 'ไม่ระบุ') return;
                        countByPI[name] = (countByPI[name] || 0) + 1;
                    });

                    const top = Object.entries(countByPI)
                        .sort((a, b) => b[1] - a[1])
                        .slice(0, TOP_N);

                    const labels = top.map(([name]) => name).reverse();
                    const values = top.map(([, count]) => count).reverse();
                    const canvas = document.getElementById('chartPIsPerUnit');
                    if (!canvas) return;

                    if (!values.length || !values.some((value) => Number(value) > 0)) {
                        drawEmptyChart(canvas);
                        return;
                    }
                    clearEmptyChartState(canvas);

                    const targetHeight = Math.min(360, Math.max(240, labels.length * 20));
                    canvas.setAttribute('height', String(targetHeight));
                    window.applyChartCanvasSizing?.();
                    Chart.getChart(canvas)?.destroy();

                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'จำนวนโครงการ',
                                data: values,
                                backgroundColor: labels.map((_, index) => PI_COLORS[index % PI_COLORS.length]),
                                borderRadius: 8,
                                barThickness: 22,
                                categoryPercentage: 0.74,
                                barPercentage: 0.92
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 6,
                                    bottom: 6,
                                    right: 76
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` จำนวนโครงการ: ${ctx.raw || 0}`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grace: '18%',
                                    grid: {
                                        color: 'rgba(148,163,184,0.22)'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: buildMobileHorizontalTicks(labels, 14)
                                }
                            }
                        },
                        plugins: [valueLabelRight]
                    });
                }

                function refreshRealtimeDashboard() {
                    const rows = getRows();
                    window.__dashboardCurrentRows = Array.isArray(rows) ? rows.slice() : [];
                    resetDashboardChartCanvases();
                    updateSummary(rows);
                    updateTypeTable(rows);
                    renderProjectsPerYear(rows);
                    renderFundingSplit(rows);
                    renderBudgetPerYear(rows);
                    renderOutputStackedFiltered(rows);
                    renderFundingSourceYearChartsFiltered(rows);
                    renderPublicationPerYearFiltered(rows);
                    renderAuthorRolePerYearFiltered(rows);
                    renderStatusYearFiltered(rows);
                    renderCollabBreakdownFiltered(rows);
                    renderYearlyLifecycleFiltered(rows);
                    renderFteSummaryFiltered(rows);
                    renderRedZoneAndOverdueFiltered(rows);
                    renderIrbExpiryFiltered(rows);
                    renderFundingSourceTopChartsFiltered(rows);
                    renderPiByUnitChartFiltered(rows);
                    window.applyChartCanvasSizing?.();
                }

                function dashboardHasActiveFilters() {
                    return Boolean(
                        el.filterYear?.value ||
                        el.filterTheme?.value ||
                        el.filterFunding?.value ||
                        el.filterType?.value ||
                        el.filterStatus?.value ||
                        el.filterSource?.value ||
                        el.filterPi?.value ||
                        el.filterPiAffiliation?.value ||
                        el.filterName?.value ||
                        el.tableSearch?.value
                    );
                }

                let chartInteractionRefreshTimer = null;
                const queueFilteredChartRefresh = () => {
                    if (!dashboardHasActiveFilters()) return;
                    clearTimeout(chartInteractionRefreshTimer);
                    chartInteractionRefreshTimer = setTimeout(() => {
                        window.refreshRealtimeDashboard?.();
                    }, 180);
                };

                document.querySelectorAll('canvas[id^="chart"]').forEach((canvas) => {
                    canvas.addEventListener('mouseenter', queueFilteredChartRefresh);
                    canvas.addEventListener('mousemove', queueFilteredChartRefresh);
                    canvas.addEventListener('touchstart', queueFilteredChartRefresh, { passive: true });
                });

                let debounceTimer = null;
                const refreshDebounced = () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(refreshRealtimeDashboard, 120);
                };

                [el.filterYear, el.filterTheme, el.filterFunding, el.filterType, el.filterStatus, el.filterSource].forEach((node) => {
                    node?.addEventListener('change', refreshRealtimeDashboard);
                });
                [el.filterPi, el.filterPiAffiliation, el.filterName, el.tableSearch].forEach((node) => {
                    node?.addEventListener('input', refreshDebounced);
                });

                el.filterReset?.addEventListener('click', () => {
                    if (el.filterYear) el.filterYear.value = '';
                    if (el.filterTheme) el.filterTheme.value = '';
                    if (el.filterFunding) el.filterFunding.value = '';
                    if (el.filterType) el.filterType.value = '';
                    if (el.filterStatus) el.filterStatus.value = '';
                    if (el.filterSource) el.filterSource.value = '';
                    if (el.filterPi) el.filterPi.value = '';
                    if (el.filterPiAffiliation) el.filterPiAffiliation.value = '';
                    if (el.filterName) el.filterName.value = '';
                    if (el.tableSearch) el.tableSearch.value = '';

                    el.filterYear?.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    el.filterTheme?.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    el.filterFunding?.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    el.filterType?.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    el.filterStatus?.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    el.filterSource?.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    el.filterPi?.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    el.filterPiAffiliation?.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    el.filterName?.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    el.tableSearch?.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));

                    refreshRealtimeDashboard();
                });

                window.refreshRealtimeDashboard = refreshRealtimeDashboard;
                const initialRows = getRows();
                window.__dashboardCurrentRows = Array.isArray(initialRows) ? initialRows.slice() : [];
                refreshRealtimeDashboard();
            })();
        </script>
    </div>
@endsection
