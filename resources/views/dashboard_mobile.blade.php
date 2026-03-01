@extends('layouts.app')

@section('content')
    <style>
        html,
        body {
            height: auto;
            min-height: 100%;
            background: transparent;
            overscroll-behavior: auto;
            overflow-y: auto;
        }

        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Mitr:wght@400;600&family=Plus+Jakarta+Sans:wght@400;600&display=swap');

        :root {
            --lux-ink: #0f172a;
            --lux-ink-soft: #334155;
            --lux-gold: #c7a76a;
            --lux-emerald: #0f766e;
            --lux-emerald-dark: #0b4f4b;
            --lux-ivory: #f6f1e8;
            --lux-mist: #fbfaf7;
        }

        .lux-page {
            font-family: 'IBM Plex Sans Thai', sans-serif;
            color: var(--lux-ink);
            background: none;
            min-height: 100vh;
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
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        #dashboardFabStack.is-hidden-by-chat,
        #dashboardFabStack.is-hidden-by-pagination {
            opacity: 0;
            pointer-events: none;
            transform: translateY(-50%) translateX(-18px);
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
                display: flex;
                overflow-x: auto;
                gap: 6px;
                scroll-snap-type: x mandatory;
                align-items: flex-start;
                padding-bottom: 0.75rem;
                -webkit-overflow-scrolling: touch;
            }

            .lux-chart-card {
                min-width: 88vw;
            }

            .lux-chart-scroll::-webkit-scrollbar {
                height: 6px;
            }

            .lux-chart-scroll::-webkit-scrollbar-thumb {
                background: rgba(148, 163, 184, 0.6);
                border-radius: 999px;
            }

            .lux-swipe-tip {
                display: flex;
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
                justify-content: space-between;
                gap: 12px;
                padding: 6px 0;
            }

            .lux-mobile-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
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
            display: none;
        }

        .lux-quick-nav a {
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            background: rgba(255, 255, 255, 0.9);
            padding: 6px 12px;
            font-size: 12px;
            color: #0f172a;
            white-space: nowrap;
        }

        .lux-quick-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-top: 12px;
        }

        .lux-quick-tile {
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: rgba(255, 255, 255, 0.96);
            padding: 8px 10px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 4px;
            color: #0f172a;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .lux-quick-icon {
            font-size: 16px;
        }

        .lux-quick-title {
            font-size: 12px;
            font-weight: 700;
        }

        .lux-quick-sub {
            font-size: 10px;
            color: #64748b;
        }

        .lux-chart-meta {
            margin-top: 8px;
            padding: 8px 10px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px dashed rgba(148, 163, 184, 0.5);
            font-size: 12px;
            color: #334155;
            line-height: 1.4;
        }

        .lux-scroll-hint {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.92);
            color: #ffffff;
            font-size: 11px;
            z-index: 5;
            pointer-events: none;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.22);
        }

        .lux-scroll-hint.blink {
            animation: lux-blink 1.2s ease-in-out infinite;
        }

        @keyframes lux-blink {
            0%,
            100% {
                opacity: 0.35;
            }
            50% {
                opacity: 1;
            }
        }

        .lux-scroll-hint .arrow {
            font-size: 14px;
        }

        .lux-scroll-hint.is-hide {
            display: none;
        }

        .lux-summary-scroll {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .lux-summary-tiles {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .lux-summary-tile {
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            padding: 10px 12px;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
            display: grid;
            gap: 6px;
        }

        .lux-summary-tile[data-tone='emerald'] {
            border-color: rgba(5, 150, 105, 0.35);
        }

        .lux-summary-tile[data-tone='amber'] {
            border-color: rgba(217, 119, 6, 0.35);
        }

        .lux-summary-tile[data-tone='sky'] {
            border-color: rgba(14, 116, 144, 0.35);
        }

        .lux-summary-tile[data-tone='violet'] {
            border-color: rgba(124, 58, 237, 0.3);
        }

        .lux-summary-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .lux-summary-icon {
            width: 28px;
            height: 28px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            background: rgba(15, 23, 42, 0.06);
        }

        .lux-summary-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .lux-summary-label {
            font-size: 0.8rem;
            color: #0f172a;
            font-weight: 600;
        }

        .lux-summary-sub {
            font-size: 0.7rem;
            color: #64748b;
        }

        .lux-chart-scroll {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .lux-summary-scroll {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .lux-chart-card {
            scroll-snap-align: start;
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

        @media (max-width: 1024px), (hover: none) and (pointer: coarse) {
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
                align-items: flex-start;
                flex-wrap: wrap;
                word-break: break-word;
            }

            #chartModalBody .lux-modal-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
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
                padding: 8px 10px;
                margin-bottom: 10px;
                background: #ffffff;
            }

            .lux-mobile-table td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 4px 0;
                align-items: flex-start;
                flex-wrap: wrap;
                word-break: break-word;
            }

            .lux-mobile-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
            }
        }

        @media (max-width: 640px) {
            .lux-page {
                background: transparent;
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

        .lux-page {
            font-family: 'Plus Jakarta Sans', 'IBM Plex Sans Thai', sans-serif;
            background: transparent;
            font-size: 17px;
            line-height: 1.65;
        }

        .lux-title {
            font-family: 'Mitr', 'Playfair Display', serif;
        }

        .lux-card {
            border-radius: 22px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        }

        .lux-hero {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.18), rgba(255, 255, 255, 0.95));
            border-color: rgba(14, 116, 144, 0.25);
        }

        .lux-quick-nav a {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(94, 234, 212, 0.4);
            color: #0f172a;
        }

        .lux-top-nav {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .lux-top-nav a {
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
            padding: 8px 10px;
            font-size: 0.8rem;
            line-height: 1.2;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .lux-summary-scroll {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .lux-summary-card {
            grid-template-columns: 1fr;
            text-align: left;
        }

        .lux-summary-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .lux-summary-value {
            font-size: 1.75rem;
        }

            .lux-chart-scroll {
                display: flex;
                overflow-x: auto;
                gap: 1rem;
                scroll-snap-type: x mandatory;
                align-items: flex-start;
                padding-bottom: 0.75rem;
                -webkit-overflow-scrolling: touch;
            }

        .lux-chart-card {
            min-width: 86vw;
            scroll-snap-align: start;
            overflow: hidden;
            padding: 14px !important;
        }

        .lux-chart-card canvas {
            display: block;
            width: 100% !important;
            height: 260px !important;
            max-width: 100% !important;
        }

        .lux-chart-card h3 {
            font-size: 1rem;
            line-height: 1.3;
        }

        @media (max-width: 640px) {
            .lux-card {
                padding: 16px;
            }

            .lux-title {
                font-size: 1.8rem;
            }

            .lux-chip {
                font-size: 0.8rem;
            }

            .lux-summary-label {
                font-size: 0.9rem;
            }

            .lux-summary-sub {
                font-size: 0.75rem;
            }

            .lux-chart-card canvas {
                height: clamp(260px, 70vw, 420px) !important;
            }
        }

        .lux-page .text-xs {
            font-size: 0.9rem;
        }

        .lux-page .text-sm {
            font-size: 1rem;
        }

        .lux-page .text-lg {
            font-size: 1.15rem;
        }

        .lux-page .text-xl {
            font-size: 1.3rem;
        }

        .lux-chart-scroll {
            padding: 0 4px 0.75rem;
        }

        .lux-page .lux-summary-scroll {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 14px;
            align-items: stretch;
            overflow-x: hidden;
            padding: 2px 2px 6px;
        }

        .lux-page .lux-summary-card {
            padding: 8px;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
            height: auto;
            min-height: 58px;
            align-content: center;
            row-gap: 4px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .lux-page .lux-summary-value {
            font-size: 1.35rem;
        }

        .lux-page .lux-summary-label {
            font-size: 0.82rem;
        }

        .lux-page .lux-summary-sub {
            font-size: 0.7rem;
            line-height: 1.2;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        @media (max-width: 380px) {
            .lux-page .lux-summary-scroll {
                grid-template-columns: 1fr;
            }

            .lux-page .lux-summary-card {
                padding: 12px;
            }
        }

        @media (max-width: 430px) {
            .lux-page .lux-summary-scroll {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
            }

            .lux-page .lux-summary-card {
                padding: 6px;
                min-height: 52px;
            }

            .lux-page .lux-summary-icon {
                width: 30px;
                height: 30px;
                font-size: 14px;
            }

            .lux-page .lux-summary-value {
                font-size: 1.2rem;
            }

            .lux-page .lux-summary-label {
                font-size: 0.78rem;
            }

            .lux-page .lux-summary-sub {
                font-size: 0.62rem;
                display: none;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .lux-page .lux-chart-scroll {
                display: flex !important;
                flex-direction: column;
                gap: 1rem;
                overflow: visible;
                align-items: stretch;
            }

            .lux-page .lux-chart-card {
                min-width: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                box-sizing: border-box;
                margin: 0;
            }

            .lux-page .lux-chart-card canvas {
                width: 100% !important;
                height: 230px !important;
                max-width: 100% !important;
            }

            .lux-page .lux-swipe-tip {
                display: none !important;
            }
        }

        @media (max-width: 900px) {
            .lux-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .lux-filter-actions {
                flex-direction: column;
            }

            .lux-filter-actions button {
                width: 100%;
            }
        }

        @media (min-width: 900px) and (max-width: 1180px) and (orientation: landscape) {
            .lux-page .lux-mobile-container {
                max-width: 60rem;
            }

            .lux-page .lux-summary-scroll {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .lux-page .lux-chart-card canvas {
                height: 240px !important;
            }

            .lux-page #chartOverdueProjects,
            .lux-page #chartExternalBySource,
            .lux-page #chartInternalBySource {
                height: 240px !important;
            }
        }

        /* Let chart cards size to content on mobile */
        @media (max-width: 640px) {
            .lux-chart-card {
                height: auto !important;
                min-height: 0 !important;
            }

            .lux-page .lux-chart-card canvas {
                width: 100% !important;
                height: 180px !important;
                min-height: 180px !important;
                max-height: 180px !important;
            }
        }

        /* Summary tiles override (final) */
        .lux-summary-tiles {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
            overflow: hidden !important;
        }

        .lux-summary-tiles .lux-summary-tile {
            min-width: 0 !important;
        }

        @media (max-width: 640px) {
            .lux-summary-tiles {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 380px) {
            .lux-summary-tiles {
                grid-template-columns: 1fr !important;
            }
        }

        .lux-page .text-xs {
            font-size: 0.95rem;
        }

        .lux-page .text-sm {
            font-size: 1.05rem;
        }

        .lux-page .text-lg {
            font-size: 1.2rem;
        }

        .lux-page .text-xl {
            font-size: 1.35rem;
        }

        /* Summary cards: compact, consistent on small screens */
        @media (max-width: 380px) {
            .lux-summary-tiles {
                grid-template-columns: 1fr;
            }
        }

        .lux-page .lux-chart-card {
            padding: 12px !important;
            min-width: 88vw;
            scroll-snap-align: start;
        }

        .lux-page .lux-chart-card h3 {
            font-size: 1.05rem;
        }

        .lux-page .lux-chart-card canvas {
            height: 260px !important;
            max-height: 280px !important;
        }

        .lux-page #chartRedZoneTimeline {
            width: 100% !important;
            height: 220px !important;
        }

        .lux-page #chartOverdueProjects {
            width: 100% !important;
            height: 220px !important;
        }

        .lux-mobile-container {
            max-width: 60rem;
            margin: 0 auto;
            background: transparent;
        }

        /* Mobile polish */
        .lux-page *,
        .lux-page *::before,
        .lux-page *::after {
            box-sizing: border-box;
        }

        @media (max-width: 640px) {
            .lux-mobile-container {
                padding-left: 14px;
                padding-right: 14px;
            }

            .lux-card,
            .lux-hero {
                padding: 16px !important;
            }

            .lux-summary-scroll {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .lux-summary-card {
                display: grid;
                grid-template-columns: 32px 1fr;
                align-items: center;
                gap: 10px;
                padding: 10px;
            }

            .lux-summary-icon {
                width: 32px;
                height: 32px;
                font-size: 14px;
                border-radius: 10px;
            }

            .lux-summary-value {
                font-size: 1.1rem;
            }

            .lux-summary-label {
                font-size: 0.75rem;
            }

            .lux-chart-card canvas {
                max-width: 100% !important;
            }

            .lux-filter-grid {
                grid-template-columns: 1fr !important;
            }

            .lux-mobile-table td {
                align-items: flex-start;
                flex-wrap: wrap;
                word-break: break-word;
            }

            .lux-quick-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lux-mobile-table tr {
                padding: 8px 10px;
            }

            .lux-mobile-table td {
                padding: 4px 0;
            }
        }

        /* Final chart guardrail (must stay last in this style block) */
        .lux-page .lux-chart-scroll {
            display: flex !important;
            flex-direction: column !important;
            overflow: visible !important;
            gap: 1rem !important;
            align-items: stretch !important;
        }

        .lux-page .lux-chart-card {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            height: auto !important;
            min-height: 0 !important;
        }

        .lux-page .lux-chart-card canvas[id^="chart"] {
            width: 100% !important;
            height: var(--chart-height, 220px) !important;
            min-height: var(--chart-height, 220px) !important;
            max-height: var(--chart-height, 220px) !important;
        }

        @media (min-width: 900px) and (max-width: 1280px) {
            .lux-page .lux-chart-scroll {
                display: flex !important;
                flex-direction: column !important;
                gap: 1rem !important;
                overflow: visible !important;
            }

            .lux-page .mobile-chart-group-body {
                display: grid !important;
                grid-template-columns: 1fr !important;
            }

            .lux-page .lux-chart-card {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
            }
        }

        /* Phone: grouped layout for easier reading */
        @media (max-width: 640px) {
            .lux-page .lux-chart-scroll {
                display: block !important;
                overflow: visible !important;
                padding-bottom: 0 !important;
            }

            .lux-page .lux-chart-card {
                min-width: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                flex: none !important;
                margin: 0 !important;
            }

            .mobile-chart-nav {
                display: flex;
                gap: 8px;
                overflow-x: auto;
                padding: 2px 0 10px;
                margin-bottom: 8px;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-chart-nav::-webkit-scrollbar {
                height: 4px;
            }

            .mobile-chart-nav-chip {
                border: 1px solid #cbd5e1;
                background: #ffffff;
                color: #0f172a;
                border-radius: 999px;
                font-size: 12px;
                padding: 6px 10px;
                white-space: nowrap;
            }

            .mobile-chart-group {
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                padding: 10px;
                margin-bottom: 12px;
                box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            }

            .mobile-chart-group-toggle {
                width: 100%;
                border: none;
                background: transparent;
                text-align: left;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }

            .mobile-chart-group-title {
                font-size: 13px;
                font-weight: 700;
                color: #0f172a;
            }

            .mobile-chart-group-sub {
                display: block;
                font-size: 11px;
                color: #64748b;
                margin-top: 2px;
            }

            .mobile-chart-group-chevron {
                color: #64748b;
                transition: transform 0.2s ease;
            }

            .mobile-chart-group.is-open .mobile-chart-group-chevron {
                transform: rotate(180deg);
            }

            .mobile-chart-group-body {
                display: grid;
                gap: 10px;
                margin-top: 10px;
            }
        }
    </style>

    <div class="lux-page">
        <div class="lux-mobile-container mx-auto px-4 py-8 space-y-6">
            <div class="lux-top-nav">
                <a href="/dashboard">📊 Dashboard</a>
                <a href="/rm">📋 รายการโปรเจค</a>
                <a href="{{ route('back_office.index') }}">🧾 Back Office</a>
                <a href="/rm/create">➕ เพิ่มโครงการ</a>
                <a href="{{ route('outputs.index') }}">📑 outputs</a>
                <a href="{{ route('fte.index') }}">🔥 FTE รายบุคคล</a>
                <a href="{{ route('fte.project.index') }}">🔥 FTE รายโปรเจค</a>
            </div>
            <div class="lux-card lux-hero p-6 md:p-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="lux-title text-3xl md:text-4xl text-slate-900">ภาพรวมโครงการวิจัย</h2>

                    </div>
                    {{-- <div class="flex flex-wrap gap-2">
                        <span class="lux-chip text-xs font-semibold px-3 py-1 rounded-full">อัปเดตล่าสุด
                            {{ now()->format('d/m/Y') }}</span>
                        <span class="lux-chip text-xs font-semibold px-3 py-1 rounded-full">ทั้งหมด
                            {{ $projects->count() }} โครงการ</span>
                        <span class="lux-chip text-xs font-semibold px-3 py-1 rounded-full">PI
                            {{ $projects->pluck('pi_name')->unique()->count() }} คน</span>
                    </div> --}}
                </div>
                <div class="lux-divider mt-6"></div>

                {{-- <div class="lux-quick-grid md:hidden">
                    <a href="#section-summary" class="lux-quick-tile">
                        <span class="lux-quick-icon">🧭</span>
                        <span class="lux-quick-title">สรุปภาพรวม</span>
                        <span class="lux-quick-sub">ตัวเลขหลัก</span>
                    </a>
                    <a href="#section-charts" class="lux-quick-tile">
                        <span class="lux-quick-icon">📈</span>
                        <span class="lux-quick-title">กราฟ</span>
                        <span class="lux-quick-sub">แนวโน้ม</span>
                    </a>
                    <a href="#section-irb" class="lux-quick-tile">
                        <span class="lux-quick-icon">🧾</span>
                        <span class="lux-quick-title">IRB</span>
                        <span class="lux-quick-sub">ติดตาม</span>
                    </a>
                    <a href="#section-copi" class="lux-quick-tile">
                        <span class="lux-quick-icon">👥</span>
                        <span class="lux-quick-title">Co-PI</span>
                        <span class="lux-quick-sub">ผู้ร่วม</span>
                    </a>
                    <a href="#section-projects" class="lux-quick-tile">
                        <span class="lux-quick-icon">📁</span>
                        <span class="lux-quick-title">โครงการ</span>
                        <span class="lux-quick-sub">รายการ</span>
                    </a>
                </div> --}}

                <!-- 1) Summary Cards -->
                <div id="section-summary"></div>
               
                <div class="lux-summary-scroll mt-2 lux-grid-stagger lux-summary-tiles">
            @php
                $summaryItems = [
                    ['count' => $projects->count(), 'label' => 'โครงการ', 'icon' => '📁', 'tone' => 'emerald', 'sub' => 'ทั้งหมดในระบบ'],
                    ['count' => $projects->pluck('pi_name')->unique()->count(), 'label' => 'หัวหน้าโครงการ', 'icon' => '👨‍🔬', 'tone' => 'sky', 'sub' => 'PI ทั้งหมด'],
                    ['count' => $copis->count(), 'label' => 'ผู้วิจัยร่วม', 'icon' => '👥', 'tone' => 'violet', 'sub' => 'Co-PI'],
                    ['count' => $coResearches->count(), 'label' => 'ผู้ร่วมวิจัย', 'icon' => '🤝', 'tone' => 'emerald', 'sub' => 'Co-Research'],
                    ['count' => number_format($fteRoles->sum('fte_value'), 2), 'label' => 'FTE รวม', 'icon' => '📈', 'tone' => 'amber', 'sub' => 'รวมทุกปี'],
                    ['count' => $outputs->count(), 'label' => 'ผลงานวิจัย', 'icon' => '📝', 'tone' => 'sky', 'sub' => 'Outputs'],
                ];
            @endphp
                    @foreach ($summaryItems as $item)
                        <div class="lux-summary-tile" data-tone="{{ $item['tone'] }}">
                            <div class="lux-summary-top">
                                <span class="lux-summary-icon">{{ $item['icon'] }}</span>
                                <span class="lux-summary-value">{{ $item['count'] }}</span>
                            </div>
                            <div class="lux-summary-label">{{ $item['label'] }}</div>
                            <div class="lux-summary-sub">{{ $item['sub'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

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
                            ฟิลเตอร์เปลี่ยนแล้วข้อมูลจะอัปเดตทันที
                        </span>
                        <button id="filterReset" type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            ล้างฟิลเตอร์
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 lux-filter-grid">
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
                            <label class="text-xs font-semibold text-gray-700 block">ค้นหาโครงการ</label>
                            <input type="text" id="filterName" list="filterNameSuggestions" class="w-full border rounded px-2 py-1 text-sm"
                                placeholder="ชื่อโครงการ">
                        </div>
                    </div>

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

                <div class="mt-4 grid grid-cols-1 gap-2">
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

        <!-- 2) Filter Status -->
        <div class="lux-card p-4 md:p-5" id="section-filters">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">ฟิลเตอร์ข้อมูล</h3>
                        <p class="text-xs text-slate-500">เปิดจากปุ่มลอยด้านซ้ายเพื่อคัดกรองหรือดาวน์โหลด</p>
                    </div>
                    <span id="filterResultBadge"
                        class="inline-flex items-center rounded-full bg-cyan-50 border border-cyan-200 px-3 py-1 text-xs font-semibold text-cyan-700">0 โครงการ</span>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                    <div class="flex flex-col gap-2">
                        <span id="dashboardFilterState" class="dashboard-status-pill is-idle">ยังไม่ได้ใช้ฟิลเตอร์</span>
                        <p id="dashboardFilterDetail" class="text-xs text-slate-500">กำลังแสดงข้อมูลทั้งหมดในระบบ</p>
                    </div>
                    <div id="dashboardFilterList" class="dashboard-filter-pill-list mt-3"></div>
                </div>
            </div>
        </div>

        <!-- 🗨️ Floating Chat Agent (ADD-ON) -->

        <!-- 3) Charts -->
        <div id="section-charts">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">สรุปสถานะและแนวโน้มเชิงกลยุทธ์</h3>
                    <p class="text-xs text-slate-500">รวมกราฟสำคัญที่ช่วยอ่านภาพรวมได้ในมุมเดียว</p>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                        <span class="lux-chip px-3 py-1 rounded-full">แตะกราฟเพื่อดูรายละเอียด</span>
                        <span class="lux-chip px-3 py-1 rounded-full">แตะตารางใน Pop-up เพื่อดูข้อมูลครบ</span>
                    </div>
                </div>
                {{-- <div class="flex items-center gap-2">
                    <button id="btnExportPdfMobile"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-2 rounded-lg shadow-sm">
                        Export Overview แบบ PDF
                    </button>
                </div> --}}
            </div>
        <div id="chartSwipeTip" class="lux-swipe-tip mb-3">
            <span>เลื่อนซ้ายขวาเพื่อดูกราฟต่อไป • แตะกราฟเพื่อดูข้อมูลเพิ่ม</span>
            <button type="button" data-close="chartSwipeTip">ปิด</button>
        </div>
            <div class="lux-chart-scroll mb-6 relative">
                <div id="chartScrollHint" class="lux-scroll-hint blink md:hidden" role="status" aria-live="polite">
                    <span>เลื่อน →</span>
                </div>
                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🔴 RED ZONE: โครงการใกล้สิ้นสุด / เกินกำหนด</h3>
                <canvas id="chartRedZoneTimeline" height="320"></canvas>
            </div>
            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🔥 โครงการที่เกินกำหนดแล้ว (Overdue)</h3> <canvas
                    id="chartOverdueProjects" height="250"></canvas>
            </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4 md:col-span-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📍 จำนวน Co-Research ตามหน่วยงาน (3/6/12 เดือน)</h3>
                <canvas id="chartCoResearchByUnitWindow" height="200"></canvas>
            </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 จำนวนโครงการรายปี (รวม)</h3>
                    <canvas id="chartProjectsPerYear" height="250"></canvas>
                </div>
                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4 ">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📅 โครงการทั้งหมด โครงการใหม่ เเละ
                        โครงการกำลังดำเนินการ (ต่อปี)</h3>
                    <canvas id="chartYearlyNewVsTotal" height="250"></canvas>
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
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 ทุนภายนอก แยกตามแหล่งทุน (Top 8)</h3>
                    <canvas id="chartExternalBySource" height="250"></canvas>
                    <div id="externalFundingMeta" class="lux-chart-meta"></div>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 ทุนภายใน แยกตามแหล่งทุน (Top 8)</h3>
                    <canvas id="chartInternalBySource" height="250"></canvas>
                    <div id="internalFundingMeta" class="lux-chart-meta"></div>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 ทุนภายนอก แยกตามแหล่งทุน (ต่อปี | Top 8)</h3>
                    <canvas id="chartExternalBySourceYear" height="250"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏛️ ทุนภายใน แยกตามแหล่งทุน (ต่อปี | Top 8)</h3>
                    <canvas id="chartInternalBySourceYear" height="250"></canvas>
                </div>



                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📝 Output (รายปี)</h3>
                    <canvas id="chartOutputStacked" height="250"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📚 Publication รายปี (แท่งแนวตั้ง)</h3>
                    <canvas id="chartPublicationPerYear" height="280"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4 mt-6">
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
                    <canvas id="chartStatusYear" height="250"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">🤝 สัดส่วนโครงการ (นอกคณะ ในคณะ เเละ ในหน่วย)</h3>
                    <canvas id="chartCollabBreakdown" height="250"></canvas>
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">👨‍🔬 จำนวนงานของ PI ในหน่วยงาน</h3>
                    <canvas id="chartPIsPerUnit" height="250"></canvas>
                </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 การกระจาย FTE</h3>
                <canvas id="chartFteSummary" height="250"></canvas>
            </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📑 IRB ใกล้หมดอายุ (6 เดือนข้างหน้า)</h3>
                <canvas id="chartIRBExpiry" height="250"></canvas>
            </div>
        </div>

            <div class="bg-white shadow-sm border rounded-xl p-4 mb-6 lux-drawer" data-lux-drawer="overdue">
                <button type="button"
                    class="w-full flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-left"
                    data-drawer-toggle aria-expanded="false">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-700">⏱️ รายการโครงการที่เกินกำหนด (ลงรายละเอียด)</h3>
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
                                    <th class="p-2 text-center">วันสิ้นสุด</th>
                                    <th class="p-2 text-center">เกินกำหนด (วัน)</th>
                                    <th class="p-2 text-center">PI</th>
                                </tr>
                            </thead>
                            <tbody id="overdueTableBody">
                                <tr class="border-t">
                                    <td colspan="4" class="p-3 text-center text-gray-500">กำลังประมวลผลข้อมูล</td>
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

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            if (window.Chart) {
                const isCompactMobile = window.innerWidth <= 640;
                const uiFontSize = isCompactMobile ? 14 : 13;
                Chart.defaults.responsive = true;
                Chart.defaults.maintainAspectRatio = false;
                Chart.defaults.layout = Chart.defaults.layout || {};
                Chart.defaults.layout.padding = 8;
                Chart.defaults.font.family = "'Plus Jakarta Sans','IBM Plex Sans Thai',sans-serif";
                Chart.defaults.font.size = uiFontSize;
                if (Chart.defaults.plugins?.legend?.labels) {
                    Chart.defaults.plugins.legend.labels.boxWidth = 10;
                    Chart.defaults.plugins.legend.labels.padding = 10;
                    Chart.defaults.plugins.legend.labels.font = {
                        size: uiFontSize,
                        family: "'Plus Jakarta Sans','IBM Plex Sans Thai',sans-serif",
                    };
                }
                if (Chart.defaults.plugins?.tooltip) {
                    Chart.defaults.plugins.tooltip.bodyFont = { size: uiFontSize };
                    Chart.defaults.plugins.tooltip.titleFont = { size: uiFontSize };
                }
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
        <script>
            function ensureChartBaseHeights(root = document) {
                root.querySelectorAll('canvas[id^="chart"]').forEach((canvas) => {
                    if (canvas.dataset.baseHeight) return;
                    const declared = Number.parseInt(canvas.getAttribute('height') || '', 10);
                    const base = Number.isFinite(declared) && declared > 0 ? declared : 240;
                    canvas.dataset.baseHeight = String(base);
                });
            }

            function chartHeightScale(vw) {
                if (vw <= 430) return 1.1;
                if (vw <= 640) return 1.02;
                if (vw <= 1024) return 0.9;
                if (vw <= 1366) return 0.82;
                if (vw <= 1536) return 0.9;
                return 1;
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

            function applyChartCanvasSizing(root = document) {
                ensureChartBaseHeights(root);
                const vw = window.innerWidth || document.documentElement.clientWidth || 1024;
                const scale = chartHeightScale(vw);
                const minHeight = vw <= 640 ? 240 : (vw <= 1024 ? 220 : 190);
                const maxHeight = vw <= 640 ? 520 : 460;
                root.querySelectorAll('canvas[id^="chart"]').forEach((canvas) => {
                    const base = Number.parseInt(canvas.dataset.baseHeight || '', 10) || 240;
                    const boost = isDenseChart(canvas.id) ? (vw <= 640 ? 1.25 : 1.15) : 1;
                    const target = Math.round(Math.min(maxHeight, Math.max(minHeight, base * scale * boost)));
                    canvas.style.setProperty('--chart-height', `${target}px`);
                    canvas.style.width = '100%';
                    canvas.style.maxWidth = '100%';
                });
            }

            window.applyChartCanvasSizing = applyChartCanvasSizing;
            applyChartCanvasSizing();
            setTimeout(() => applyChartCanvasSizing(), 0);
            document.addEventListener('DOMContentLoaded', () => applyChartCanvasSizing());
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
            const flatProjectRelation = (key) => projects.flatMap((project) => (project && Array.isArray(project[key])) ? project[key] : []);
            const outputs = flatProjectRelation('outputs');
            const ftes = flatProjectRelation('fte_roles');
            const irbs = flatProjectRelation('irbs');
            const copisSummary = @json($copiRows);
            window.outputs = outputs;
            const isMobileView = window.innerWidth <= 640;
            const isTabletLandscape = window.matchMedia(
                '(min-width: 900px) and (max-width: 1180px) and (orientation: landscape)'
            ).matches;

            function initMobileChartLayout() {
                if (!isMobileView) return;
                const scroll = document.querySelector('#section-charts .lux-chart-scroll');
                if (!scroll || scroll.dataset.mobileGrouped === '1') return;
                scroll.dataset.mobileGrouped = '1';

                document.getElementById('chartSwipeTip')?.remove();
                document.getElementById('chartScrollHint')?.remove();

                const cards = Array.from(scroll.querySelectorAll('.lux-chart-card'));
                const cardByChartId = new Map();
                cards.forEach((card) => {
                    const id = card.querySelector('canvas[id^="chart"]')?.id;
                    if (id) cardByChartId.set(id, card);
                });

                const groups = [
                    {
                        key: 'urgent',
                        title: 'เร่งด่วน',
                        subtitle: 'โครงการใกล้สิ้นสุดและเกินกำหนด',
                        charts: ['chartRedZoneTimeline', 'chartOverdueProjects', 'chartCoResearchByUnitWindow'],
                    },
                    {
                        key: 'project_fund',
                        title: 'โครงการและทุน',
                        subtitle: 'ภาพรวมโครงการ งบประมาณ และแหล่งทุน',
                        charts: [
                            'chartProjectsPerYear', 'chartYearlyNewVsTotal', 'chartFundingSplit', 'chartBudgetPerYear',
                            'chartExternalBySource', 'chartInternalBySource', 'chartExternalBySourceYear',
                            'chartInternalBySourceYear'
                        ],
                    },
                    {
                        key: 'output_people',
                        title: 'ผลงานและบุคลากร',
                        subtitle: 'Outputs, authors, FTE และ IRB',
                        charts: [
                            'chartOutputStacked', 'chartPublicationPerYear', 'chartAuthorRolePerYear', 'chartStatusYear',
                            'chartCollabBreakdown', 'chartPIsPerUnit', 'chartFteSummary', 'chartIRBExpiry'
                        ],
                    },
                ];

                const sectionNodes = [];
                const nav = document.createElement('div');
                nav.className = 'mobile-chart-nav';

                const closeAllSections = () => {
                    sectionNodes.forEach((s) => {
                        s.classList.remove('is-open');
                        const body = s.querySelector('.mobile-chart-group-body');
                        const btn = s.querySelector('.mobile-chart-group-toggle');
                        if (body) body.hidden = true;
                        btn?.setAttribute('aria-expanded', 'false');
                    });
                };

                const openSection = (node, {
                    toggle = false
                } = {}) => {
                    const nodeBody = node?.querySelector('.mobile-chart-group-body');
                    const isOpen = !!nodeBody && !nodeBody.hidden;
                    if (toggle && isOpen) {
                        closeAllSections();
                        return;
                    }
                    sectionNodes.forEach((s) => {
                        const isActive = s === node;
                        s.classList.toggle('is-open', isActive);
                        const body = s.querySelector('.mobile-chart-group-body');
                        const btn = s.querySelector('.mobile-chart-group-toggle');
                        if (body) body.hidden = !isActive;
                        btn?.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                    });
                };

                const renderGroup = (group, isFirst = false) => {
                    const section = document.createElement('section');
                    section.className = `mobile-chart-group${isFirst ? ' is-open' : ''}`;
                    section.id = `mobileChartGroup_${group.key}`;

                    const toggle = document.createElement('button');
                    toggle.type = 'button';
                    toggle.className = 'mobile-chart-group-toggle';
                    toggle.setAttribute('aria-expanded', isFirst ? 'true' : 'false');
                    toggle.innerHTML = `
                        <div>
                            <div class="mobile-chart-group-title">${group.title}</div>
                            <span class="mobile-chart-group-sub">${group.subtitle}</span>
                        </div>
                        <span class="mobile-chart-group-chevron">▾</span>
                    `;

                    const body = document.createElement('div');
                    body.className = 'mobile-chart-group-body';
                    body.hidden = !isFirst;

                    group.charts.forEach((chartId) => {
                        const card = cardByChartId.get(chartId);
                        if (card) {
                            body.appendChild(card);
                            cardByChartId.delete(chartId);
                        }
                    });

                    toggle.addEventListener('click', () => openSection(section, {
                        toggle: true
                    }));

                    const navChip = document.createElement('button');
                    navChip.type = 'button';
                    navChip.className = 'mobile-chart-nav-chip';
                    navChip.textContent = group.title;
                    navChip.addEventListener('click', () => {
                        openSection(section);
                        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                    nav.appendChild(navChip);

                    section.appendChild(toggle);
                    section.appendChild(body);
                    sectionNodes.push(section);
                    return section;
                };

                scroll.innerHTML = '';
                scroll.appendChild(nav);

                groups.forEach((group, idx) => {
                    scroll.appendChild(renderGroup(group, false));
                });

                if (cardByChartId.size) {
                    const restGroup = {
                        key: 'more',
                        title: 'กราฟเพิ่มเติม',
                        subtitle: 'รายการอื่น ๆ',
                        charts: Array.from(cardByChartId.keys()),
                    };
                    scroll.appendChild(renderGroup(restGroup, false));
                }

                closeAllSections();
            }

            initMobileChartLayout();

            // Mobile readability defaults
            if (window.Chart && isMobileView) {
                Chart.defaults.font.size = 12;
                Chart.defaults.layout.padding = 6;
                if (Chart.defaults.plugins?.legend) {
                    Chart.defaults.plugins.legend.position = 'bottom';
                    Chart.defaults.plugins.legend.labels.boxWidth = 8;
                    Chart.defaults.plugins.legend.labels.padding = 6;
                }
                Chart.defaults.scales = Chart.defaults.scales || {};
                Chart.defaults.scales.x = Object.assign({}, Chart.defaults.scales.x, {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 4,
                        maxRotation: 0,
                        minRotation: 0
                    },
                    grid: {
                        display: false
                    }
                });
                Chart.defaults.scales.y = Object.assign({}, Chart.defaults.scales.y, {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 5
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.25)'
                    }
                });
            }
            const truncateLegendLabel = (text, max = 16) => {
                const value = String(text ?? '');
                if (!isMobileView || value.length <= max) return value;
                return value.slice(0, max - 1) + '…';
            };

            const buildLegendLabels = (chart) => {
                const base = Chart.defaults.plugins.legend.labels.generateLabels;
                return base(chart).map(item => ({
                    ...item,
                    text: truncateLegendLabel(item.text),
                }));
            };



            /* ---------- Helpers ---------- */
            const toNumber = v => isNaN(+v) ? 0 : +v;
            const thaiYear = y => y; // ถ้า start_year เก็บเป็น พ.ศ. อยู่แล้วก็คืนตามเดิม
            const getYear = (p) => p.start_year || 'ไม่ระบุ';
            const getEndYear = (p) => p.end_year || p.finish_year || null; // ปรับตามฟิลด์จริง
            const getEndDateRaw = (p) => p.end_date || p.team_end_date || p.finish_date || p.endDate || null;
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

            function uniqueProjects(list) {
                const seen = new Map();
                list.forEach(p => {
                    if (p?.id != null && !seen.has(p.id)) seen.set(p.id, p);
                });
                return Array.from(seen.values());
            }

            function endYearLabel(p) {
                const endDate = parseProjectDate(getEndDateRaw(p));
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

                if (modalType === 'redzone' || modalType === 'overdue' || modalType === 'remaining') {
                    const now = new Date();
                    const pageSize = state.pageSize || 10;
                    const yearOptions = Array.from(new Set(rows.map(endYearLabel))).sort();
                    const activeYear = state.yearFilter || '';
                    const diffHeader = modalType === 'overdue'
                        ? 'เกินกำหนด (วัน)'
                        : (modalType === 'remaining' ? 'คงเหลือ (วัน)' : 'คงเหลือ/เกิน (วัน)');

                    const filtered = activeYear ? rows.filter(p => endYearLabel(p) === activeYear) : rows.slice();
                    const sorted = applyProjectSort(filtered, mode || 'name_asc');
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
                                        <th class="p-2 text-center">ปีสิ้นสุด</th>
                                        <th class="p-2 text-center">${diffHeader}</th>
                                        <th class="p-2 text-center">PI</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    if (!pageRows.length) {
                        html += `<tr class="border-t"><td colspan="5" class="p-3 text-center text-gray-500">ไม่มีข้อมูล</td></tr>`;
                    } else {
                        pageRows.forEach((p, idx) => {
                            const endDate = parseProjectDate(getEndDateRaw(p));
                            const diffDays = endDate ? Math.ceil((endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24)) : null;
                            let diffLabel = '-';
                            let diffClass = 'text-amber-600 font-semibold';
                            if (diffDays != null) {
                                if (modalType === 'overdue') {
                                    diffLabel = `${Math.abs(diffDays)}`;
                                    diffClass = 'text-red-600 font-semibold';
                                } else if (modalType === 'remaining') {
                                    diffLabel = `${Math.max(diffDays, 0)}`;
                                    diffClass = 'text-emerald-600 font-semibold';
                                } else {
                                    diffLabel = diffDays < 0 ? `${Math.abs(diffDays)}` : `${diffDays}`;
                                    diffClass = diffDays < 0 ? 'text-red-600 font-semibold' :
                                        'text-amber-600 font-semibold';
                                }
                            }
                            html += `
                                <tr class="border-t">
                                    <td class="p-2 text-center" data-label="#">${start + idx + 1}</td>
                                    <td class="p-2 text-left text-blue-700 hover:underline" data-label="ชื่อโครงการ">
                                        <a href="/rm/projects/${p.id}" target="_blank" rel="noopener">${p.project_name_th || '-'}</a>
                                    </td>
                                    <td class="p-2 text-center" data-label="ปีสิ้นสุด">${formatDateTH(endDate, '-')}</td>
                                    <td class="p-2 text-center ${diffClass}" data-label="${diffHeader}">${diffLabel}</td>
                                    <td class="p-2 text-center" data-label="PI">${p.pi_name || '-'}</td>
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
                const rows = uniqueProjects(list);
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
                if (/^\d{4}$/.test(str)) return str;
                const match = str.match(/(25\d{2}|20\d{2}|19\d{2})/);
                if (match) return match[1];
                const d = new Date(str);
                if (!isNaN(d)) return String(d.getFullYear());
                return 'ไม่ระบุ';
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
                if (s.includes('ยกเลิก') || s.includes('cancel')) return 'cancelled';
                if (s.includes('ปิดโปรเจค') || s.includes('closed') || s.includes('complete')) return 'completed';
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
                if (!Number.isFinite(startY) || Y <= startY) return false;
                if (!projectExistsInYear(p, Y, horizonCE)) return false;
                const key = projectLifecycleStatusKey(p);
                if (key === 'in_progress') return true;
                const terminalY = projectLifecycleTerminalYearCE(p, horizonCE);
                return Y < terminalY;
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

            function getFilteredProjects() {
                const y = norm(document.getElementById('filterYear')?.value);
                const t = norm(document.getElementById('filterTheme')?.value);
                const f = norm(document.getElementById('filterFunding')?.value);
                const type = norm(document.getElementById('filterType')?.value);
                const status = norm(document.getElementById('filterStatus')?.value);
                const source = norm(document.getElementById('filterSource')?.value);
                const name = norm(document.getElementById('filterName')?.value);
                const search = norm(document.getElementById('tableSearch')?.value);

                return projects.filter(p => {
                    const year = norm(p.start_year);
                    const theme = norm(p.theme);
                    const funding = norm(p.funding_type);
                    const projectType = norm(p.project_type);
                    const projectStatus = norm(p.planning_status);
                    const fundingSource = norm(p.funding_source);
                    const nameTh = norm(p.project_name_th);
                    const piName = norm(p.pi_name);
                    const searchIndex = projectSearchIndex(p);

                    if (y && year !== y) return false;
                    if (t && theme !== t) return false;
                    if (f && funding !== f) return false;
                    if (type && projectType !== type) return false;
                    if (status && projectStatus !== status) return false;
                    if (source && fundingSource !== source) return false;
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

                if (id === 'chartProjectsPerYear') {
                    const labelText = String(label ?? '').trim();
                    const Y = Number(yearCE(labelText) || toNumber(labelText));
                    const startYears = projects
                        .map(p => projectLifecycleStartYearCE(p))
                        .filter(Number.isFinite);
                    const horizonCE = startYears.length ? Math.max(...startYears) : (new Date().getFullYear());
                    return {
                        title: `โครงการทั้งหมดสะสมถึงปี ${labelText}`,
                        projects: projects.filter(p => {
                            const startY = projectLifecycleStartYearCE(p);
                            if (Number.isFinite(startY) && Number.isFinite(Y)) return startY <= Y;
                            return Number.isFinite(Y) && Y === horizonCE;
                        })
                    };
                }

                if (id === 'chartYearlyNewVsTotal') {
                    const labelText = String(label ?? '').trim();
                    const type = String(datasetLabel || '').trim();
                    const map = window.yearlyNewVsTotalLabelMap || {};
                    const Y = Number(map[labelText] || yearCE(labelText) || toNumber(labelText));
                    const horizonCE = Number(window.yearlyLifecycleHorizonCE) || new Date().getFullYear();
                    let list = [];
                    if (type === 'ทั้งหมด') {
                        list = projects.filter(p => {
                            const startY = projectLifecycleStartYearCE(p);
                            if (Number.isFinite(startY)) return startY <= Y;
                            return Y === horizonCE;
                        });
                    } else if (type === 'ใหม่') {
                        list = projects.filter(p => projectLifecycleStartYearCE(p) === Y);
                    } else if (type === 'กำลังทำ' || type === 'กำลังดำเนินการ') {
                        list = projects.filter(p => projectIsInProgressInYear(p, Y, horizonCE));
                    } else if (type === 'ปิดโปรเจค') {
                        list = projects.filter(p =>
                            projectLifecycleStatusKey(p) === 'completed' &&
                            Number(projectLifecycleTerminalYearCE(p, horizonCE)) === Y
                        );
                    } else if (type === 'ยกเลิกโปรเจค') {
                        list = projects.filter(p =>
                            projectLifecycleStatusKey(p) === 'cancelled' &&
                            Number(projectLifecycleTerminalYearCE(p, horizonCE)) === Y
                        );
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
                                matchYearLabel(p, label) &&
                                others.includes((getFundingSource(p) || 'ไม่ระบุ'))
                            ),
                            includeFunding: true
                        };
                    }
                    return {
                        title: `ทุน${type} ${datasetLabel || ''} ปี ${label}`,
                        projects: projects.filter(p =>
                            getFundingType(p) === type &&
                            matchYearLabel(p, label) &&
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
                        projects: projectsFromOutputs(o => outputYearKey(o) === year && outputType(o) === type)
                    };
                }

                if (id === 'chartPublicationPerYear') {
                    const yearLabel = String(label ?? '');
                    return {
                        title: `Publication ปี ${yearLabel}`,
                        projects: projectsFromOutputs(o => {
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
                        projects: projects.filter(p => matchYearLabel(p, label) && (p.planning_status || 'ไม่ระบุ') === datasetLabel)
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
                            (p.pi_affiliation || '').trim() === 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ' &&
                            (p.pi_name || '').trim() === String(label ?? '').trim()
                        )
                    };
                }

                if (id === 'chartFteSummary') {
                    return {
                        title: `ผู้รับผิดชอบ ${datasetLabel || ''}`,
                        projects: projectsFromFtes(f => (f.name || 'ไม่ระบุ') === datasetLabel)
                    };
                }

                if (id === 'chartRedZoneTimeline') {
                    const now = new Date();
                    const bucket = String(label || '');
                    const list = projects.filter(p => {
                        const endDate = parseProjectDate(getEndDateRaw(p));
                        if (!endDate) return false;
                        const diffDays = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24);
                        const diffMonths = diffDays / 30.4375;
                        if (bucket === 'เกินกำหนด') return diffDays < 0;
                        if (bucket === '3 เดือน') return diffDays >= 0 && diffMonths <= 3;
                        if (bucket === '6 เดือน') return diffMonths > 3 && diffMonths <= 6;
                        if (bucket === '9 เดือน') return diffMonths > 6 && diffMonths <= 9;
                        return false;
                    });
                    return {
                        title: `โครงการ ${bucket}`,
                        projects: list,
                        modalType: 'redzone'
                    };
                }

                if (id === 'chartOverdueProjects') {
                    const now = new Date();
                    const labelText = String(label || '');
                    const isOverdue = labelText.includes('เกินกำหนด');
                    const list = projects.filter(p => {
                        const endDate = parseProjectDate(getEndDateRaw(p));
                        if (!endDate) return false;
                        const diffDays = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24);
                        if (isOverdue) return diffDays < 0;
                        return diffDays >= 0;
                    });
                    return {
                        title: labelText,
                        projects: list,
                        modalType: isOverdue ? 'overdue' : 'remaining'
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
                    const labelText = String(label || '');
                    const list = projects.filter(p => {
                        const list = p.irbs || [];
                        return list.some(irb => {
                            const d = parseProjectDate(irb.exp_date);
                            if (!d) return false;
                            const key = `${d.getMonth() + 1}/${d.getFullYear()}`;
                            return key === labelText;
                        });
                    });
                    return {
                        title: `IRB ใกล้หมดอายุ ${labelText}`,
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
                    const list = projectsFromOutputs(o => {
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
                let points = chart.getElementsAtEventForMode(evt, 'nearest', {
                    intersect: true,
                    axis: 'xy'
                }, true);
                if (!points || !points.length) return;
                const picked = points[0];
                const hitKey = `${chart?.canvas?.id || 'chart'}:${picked.datasetIndex}:${picked.index}`;
                const lastHit = window.__dashboardChartLastHit;
                const nowTs = Date.now();
                if (lastHit && lastHit.key === hitKey && (nowTs - lastHit.at) < 180) return;
                window.__dashboardChartLastHit = {
                    key: hitKey,
                    at: nowTs
                };
                const result = resolveProjectsForChart(chart, picked);
                if (result) showProjectList(result.title, result.projects, {
                    includeFunding: result.includeFunding,
                    modalType: result.modalType,
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
                const startYearsCE = projects
                    .map(p => projectLifecycleStartYearCE(p))
                    .filter(Number.isFinite)
                    .sort((a, b) => a - b);
                if (!startYearsCE.length) return;

                const yearsCE = Array.from(new Set(startYearsCE));
                const maxYearCE = yearsCE[yearsCE.length - 1];
                const labels = yearsCE.map(y => yearLabelTH(y));
                const data = yearsCE.map(y =>
                    projects.filter(p => {
                        const startY = projectLifecycleStartYearCE(p);
                        if (Number.isFinite(startY)) return startY <= y;
                        return y === maxYearCE;
                    }).length
                );
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
                        ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
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
                        layout: {
                            padding: {
                                top: isMobileView ? 12 : 14
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
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: isMobileView ? 4 : 10,
                                    maxRotation: 0,
                                    minRotation: 0
                                },
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
                        ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
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
                                top: isMobileView ? 12 : 14
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
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: isMobileView ? 4 : 10,
                                    maxRotation: 0,
                                    minRotation: 0
                                },
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
                        ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
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
                                top: isMobileView ? 12 : 20
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
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: isMobileView ? 4 : 10,
                                    maxRotation: 0,
                                    minRotation: 0
                                },
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
                        ctx.font = isMobileView ? '700 10px "IBM Plex Sans Thai", sans-serif' : '700 11px "IBM Plex Sans Thai", sans-serif';
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
                        layout: {
                            padding: {
                                top: 16
                            }
                        }, // เผื่อพื้นที่ด้านบน
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
                                grace: '12%', // headroom กันเลขชนขอบ
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
                            legend: {
                                position: 'bottom'
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
                    'ยกเลิกโปรเจค'
                ];

                const statusData = {}; // year → status → count
                projects.forEach(p => {
                    const year = getYear(p);
                    const status = p.planning_status || 'ไม่ระบุ';
                    if (!statusData[year]) statusData[year] = Object.fromEntries(statuses.map(s => [s, 0]));
                    if (statuses.includes(status)) statusData[year][status]++;
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

                // ปลั๊กอินแสดงตัวเลขบนแท่ง + กันตกขอบบน/ขวา
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

                        chart.data.datasets.forEach((dataset, dsIndex) => {
                            const meta = chart.getDatasetMeta(dsIndex);
                            meta.data.forEach((bar, index) => {
                                const value = Number(dataset.data[index] || 0);
                                if (!isFinite(value) || value <= 0) return;

                                let drawX = bar.x;
                                let drawY = bar.y - 3; // วางเหนือแท่ง

                                // กันตกขอบบน
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
                const canvas = document.getElementById('chartIRBExpiry');
                if (!canvas) return;

                function drawIrbEmptyState() {
                    Chart.getChart(canvas)?.destroy();
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.font = '14px sans-serif';
                    ctx.fillStyle = '#6b7280';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('ไม่มีข้อมูล IRB', canvas.width / 2, canvas.height / 2);
                }

                function renderIrbExpiryChart(rows = projects) {
                    const group = {};
                    (rows || []).forEach((project) => {
                        (project.irbs || []).forEach((irb) => {
                            const expDate = parseProjectDate(irb.exp_date);
                            if (!expDate) return;
                            const monthsAhead = (expDate - new Date()) / (1000 * 60 * 60 * 24 * 30);
                            if (monthsAhead >= 0 && monthsAhead <= 6) {
                                const key = `${expDate.getMonth() + 1}/${expDate.getFullYear()}`;
                                group[key] = (group[key] || 0) + 1;
                            }
                        });
                    });

                    const labels = Object.keys(group).sort((a, b) => new Date(a) - new Date(b));
                    if (!labels.length) {
                        drawIrbEmptyState();
                        return;
                    }

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'IRB ใกล้หมดอายุ',
                                data: labels.map((label) => group[label]),
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
                }

                window.renderDashboardIrbExpiryChart = renderIrbExpiryChart;
                renderIrbExpiryChart(projects);
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


            /* ---------- 9) จำนวน PI ต่อหน่วยงาน (เฉพาะ theme = T101_Research...) ---------- */
            (function() {
                // กรองเฉพาะโครงการ theme = T101_Research...
                const filtered = projects.filter(p =>
                    (p.pi_affiliation || '').trim() === 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ'
                );

                // นับจำนวนโครงการต่อ PI
                const countByPI = {};
                filtered.forEach(p => {
                    const name = (p.pi_name || '').toString().trim();
                    if (!name || name === 'ไม่ระบุ') return;
                    countByPI[name] = (countByPI[name] || 0) + 1;
                });

                // เรียงมาก→น้อย และเลือก Top N
                const TOP_N = isMobileView ? 10 : 20;
                const sorted = Object.entries(countByPI).sort((a, b) => b[1] - a[1]);
                const top = sorted.slice(0, TOP_N);

                const labels = top.map(([pi]) => pi);
                const data = top.map(([, count]) => count);

                const el = document.getElementById('chartPIsPerUnit');
                if (!el) return;

                el.height = Math.min(Math.max(250, labels.length * 28), 420);

                // plugin แสดงตัวเลขขวาสุด
                const valueLabelRight = {
                    id: 'valueLabelRight',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                right
                            }
                        } = chart;
                        const meta = chart.getDatasetMeta(0);
                        const scaleX = chart.scales.x;
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textBaseline = 'middle';
                        ctx.textAlign = 'right';
                        chart.data.datasets[0].data.forEach((v, i) => {
                            if (!isFinite(v) || v <= 0) return;
                            const y = meta.data[i].y;
                            let x = scaleX.getPixelForValue(v) + 8;
                            if (x > right - 2) x = right - 2;
                            ctx.fillText(String(v), x, y);
                        });
                        ctx.restore();
                    }
                };

                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'จำนวนโครงการ',
                            data,
                            backgroundColor: labels.map((_, i) => `hsl(${(i*33)%360} 70% 60%)`),
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
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
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'PI'
                                }
                            }
                        }
                    },
                    plugins: [valueLabelRight]
                });
            })();


            /* ---------- 10) ทุนภายนอกตามแหล่งทุน (จำนวน + ยอดเงินย่อ) ---------- */
            (function() {
                // ย่อเงินไทย: พัน/หมื่น/แสน/ล้าน บาท
                function formatThaiMoney(num) {
                    const n = Number(num) || 0;
                    return (n / 1_000_000).toFixed(2).replace(/\.00$/, '') + ' ล้านบ.';
                }

                // สะสม "จำนวนโครงการ" และ "ยอดเงินรวม" ต่อแหล่งทุน (เฉพาะทุนภายนอก)
                const countBySrc = {};
                const budgetBySrc = {};
                projects.forEach(p => {
                    if ((getFundingType(p) || '').trim() === 'ภายนอก') {
                        const src = (getFundingSource(p) || 'ไม่ระบุ').toString().trim();
                        const amt = Number(p.budget) || 0;
                        countBySrc[src] = (countBySrc[src] || 0) + 1;
                        budgetBySrc[src] = (budgetBySrc[src] || 0) + amt;
                    }
                });

                const entries = Object.keys(countBySrc).map(label => ({
                    label,
                    count: countBySrc[label],
                    budget: budgetBySrc[label]
                }));
                entries.sort((a, b) => (b.count - a.count) || (b.budget - a.budget));

                const TOP_N_SOURCES = isMobileView ? 5 : 8;
                const top = entries.slice(0, TOP_N_SOURCES);
                const others = entries.slice(TOP_N_SOURCES);

                const labels = top.map(e => e.label);
                const counts = top.map(e => e.count);
                const budgets = top.map(e => e.budget);

                if (others.length) {
                    labels.push('อื่นๆ');
                    counts.push(others.reduce((s, e) => s + e.count, 0));
                    budgets.push(others.reduce((s, e) => s + e.budget, 0));
                }

                window.fundingSourceGroups = window.fundingSourceGroups || {};
                window.fundingSourceGroups.external = {
                    top: top.map(e => e.label),
                    others: others.map(e => e.label)
                };
                const totalBudget = budgets.reduce((s, v) => s + v, 0) || 1;

                // data label บนชิ้นโดนัท: "จำนวน (ยอดเงินย่อ)"
                const valueLabelCenter = {
                    id: 'valueLabelCenterExt',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                right
                            }
                        } = chart;
                        const meta = chart.getDatasetMeta(0);
                        ctx.save();
                        ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = isMobileView ? 'left' : 'center';
                        ctx.textBaseline = 'middle';
                        meta.data.forEach((el, i) => {
                            const c = counts[i];
                            const m = budgets[i];
                            if (!c) return;
                            let {
                                x,
                                y
                            } = el.tooltipPosition();
                            if (isMobileView) {
                                x = Math.min(x + 6, right - 6);
                                if (x >= right - 18) {
                                    ctx.textAlign = 'right';
                                    x = right - 4;
                                } else {
                                    ctx.textAlign = 'left';
                                }
                                ctx.fillText(String(c), x, y);
                                return;
                            }
                            ctx.fillText(`${c} (${formatThaiMoney(m)})`, x, y);
                        });
                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartExternalBySource');
                if (!el) return;
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                const externalType = isMobileView ? 'bar' : 'doughnut';
                const externalChart = new Chart(el, {
                    type: externalType,
                    data: {
                        labels,
                        datasets: [{
                            label: 'จำนวนโครงการ',
                            data: counts,
                            backgroundColor: labels.map((_, i) => `hsl(${(i*37)%360} 70% 60%)`),
                            borderRadius: isMobileView ? 8 : 0,
                            barThickness: isMobileView ? 16 : undefined
                        }]
                    },
                    options: isMobileView ? {
                        indexAxis: 'y',
                        responsive: true,
                        layout: {
                            padding: {
                                right: 22
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const i = ctx.dataIndex;
                                        const c = counts[i];
                                        const m = budgets[i];
                                        const pct = ((m / totalBudget) * 100).toFixed(1);
                                        return ` ${ctx.label}: ${c} โครงการ, ${formatThaiMoney(m)} (${pct}%)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            },
                            y: {
                                ticks: {
                                    callback: (value, index) => truncateLegendLabel(labels[index], 14)
                                }
                            }
                        }
                    } : {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    generateLabels: buildLegendLabels
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const i = ctx.dataIndex;
                                        const c = counts[i];
                                        const m = budgets[i];
                                        const pct = ((m / totalBudget) * 100).toFixed(1);
                                        return ` ${ctx.label}: ${c} โครงการ, ${formatThaiMoney(m)} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    },
                    plugins: [valueLabelCenter]
                });
                if (isTabletLandscape) {
                    el.style.width = '100%';
                    externalChart.resize();
                }

                const meta = document.getElementById('externalFundingMeta');
                if (meta) {
                    const topText = top.slice(0, 3).map(e => `${e.label} (${e.count})`).join(' • ') || '-';
                    const totalAll = entries.reduce((s, e) => s + (e.budget || 0), 0);
                    meta.textContent = `Top แหล่งทุน: ${topText} · งบรวม ${formatThaiMoney(totalAll)}`;
                }
            })();



            /* ---------- ทุนภายในตามแหล่งทุน (จำนวน + ยอดเงิน) ---------- */
            (function() {
                const mapCount = {};
                const mapBudget = {};

                projects.forEach(p => {
                    if ((getFundingType(p) || '').trim() === 'ภายใน') {
                        const src = (getFundingSource(p) || 'ไม่ระบุ').toString().trim();
                        const amt = Number(p.budget) || 0;
                        mapCount[src] = (mapCount[src] || 0) + 1;
                        mapBudget[src] = (mapBudget[src] || 0) + amt;
                    }
                });

                const entries = Object.keys(mapCount).map(label => ({
                    label,
                    count: mapCount[label],
                    budget: mapBudget[label]
                }));
                entries.sort((a, b) => (b.count - a.count) || (b.budget - a.budget));

                const TOP_N_SOURCES = isMobileView ? 5 : 8;
                const top = entries.slice(0, TOP_N_SOURCES);
                const others = entries.slice(TOP_N_SOURCES);

                const labels = top.map(e => e.label);
                const counts = top.map(e => e.count);
                const budgets = top.map(e => e.budget);

                if (others.length) {
                    labels.push('อื่นๆ');
                    counts.push(others.reduce((s, e) => s + e.count, 0));
                    budgets.push(others.reduce((s, e) => s + e.budget, 0));
                }

                window.fundingSourceGroups = window.fundingSourceGroups || {};
                window.fundingSourceGroups.internal = {
                    top: top.map(e => e.label),
                    others: others.map(e => e.label)
                };
                const totalBudget = budgets.reduce((s, v) => s + v, 0) || 1;

                const dataLabelPlugin = {
                    id: 'dataLabelPluginInternal',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                right
                            }
                        } = chart;
                        ctx.save();
                        ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = isMobileView ? 'left' : 'center';
                        ctx.textBaseline = 'middle';

                        const meta = chart.getDatasetMeta(0);
                        meta.data.forEach((el, i) => {
                            const count = counts[i];
                            const money = budgets[i];
                            if (!count) return;
                            let {
                                x,
                                y
                            } = el.tooltipPosition();
                            if (isMobileView) {
                                x = Math.min(x + 6, right - 6);
                                if (x >= right - 18) {
                                    ctx.textAlign = 'right';
                                    x = right - 4;
                                } else {
                                    ctx.textAlign = 'left';
                                }
                                ctx.fillText(String(count), x, y);
                                return;
                            }
                            if (!money) return;
                            ctx.fillText(`${count} (${formatThaiMoney(money)})`, x, y);
                        });
                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartInternalBySource');
                if (!el) return;
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                const internalType = isMobileView ? 'bar' : 'doughnut';
                const internalChart = new Chart(el, {
                    type: internalType,
                    data: {
                        labels,
                        datasets: [{
                            label: 'โครงการ',
                            data: counts,
                            backgroundColor: labels.map((_, i) => `hsl(${(i*37)%360} 70% 60%)`),
                            borderRadius: isMobileView ? 8 : 0,
                            barThickness: isMobileView ? 16 : undefined
                        }]
                    },
                    options: isMobileView ? {
                        indexAxis: 'y',
                        responsive: true,
                        layout: {
                            padding: {
                                right: 22
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const idx = ctx.dataIndex;
                                        const c = counts[idx];
                                        const m = budgets[idx];
                                        const pct = ((m / totalBudget) * 100).toFixed(1);
                                        return ` ${ctx.label}: ${c} โครงการ, ${formatThaiMoney(m)} (${pct}%)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'จำนวนโครงการ'
                                }
                            },
                            y: {
                                ticks: {
                                    callback: (value, index) => truncateLegendLabel(labels[index], 14)
                                }
                            }
                        }
                    } : {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    generateLabels: buildLegendLabels
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const idx = ctx.dataIndex;
                                        const c = counts[idx];
                                        const m = budgets[idx];
                                        const pct = ((m / totalBudget) * 100).toFixed(1);
                                        return ` ${ctx.label}: ${c} โครงการ, ${formatThaiMoney(m)} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    },
                    plugins: [dataLabelPlugin]
                });
                if (isTabletLandscape) {
                    el.style.width = '100%';
                    internalChart.resize();
                }

                const meta = document.getElementById('internalFundingMeta');
                if (meta) {
                    const topText = top.slice(0, 3).map(e => `${e.label} (${e.count})`).join(' • ') || '-';
                    const totalAll = entries.reduce((s, e) => s + (e.budget || 0), 0);
                    meta.textContent = `Top แหล่งทุน: ${topText} · งบรวม ${formatThaiMoney(totalAll)}`;
                }
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
                    ctx.save();
                    ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
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
                            ctx.fillText(formatThaiMoney(sum), x, y);
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
                    const amt = Number(p.budget) || 0;
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
                        ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
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

                    const TOP_N_SOURCES = isMobileView ? 4 : 8;
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
                                    top: isMobileView ? 12 : 16
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        generateLabels: buildLegendLabels
                                    }
                                },
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
                                    ticks: {
                                        autoSkip: true,
                                        maxTicksLimit: isMobileView ? 4 : 10,
                                        maxRotation: 0,
                                        minRotation: 0
                                    }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grace: '12%',
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

            /* ---------- 11) โครงการทั้งหมด vs ใหม่ vs ค้างจากปีก่อน (ต่อปี) + Data Label + กันเลขตกขอบ ---------- */
            (function() {
                const startYears = projects
                    .map((project) => projectLifecycleStartYearCE(project))
                    .filter(Number.isFinite);
                if (!startYears.length) return;

                const eventYears = projects
                    .filter((project) => {
                        const key = projectLifecycleStatusKey(project);
                        return key === 'completed' || key === 'cancelled';
                    })
                    .map((project) => projectLifecycleEventYearCE(project))
                    .filter(Number.isFinite);

                const minYearCE = Math.min(...startYears);
                const currentYearCE = new Date().getFullYear();
                const maxYearCE = Math.max(...startYears, ...eventYears, currentYearCE);
                window.yearlyLifecycleHorizonCE = maxYearCE;

                const yearsCE = [];
                for (let year = minYearCE; year <= maxYearCE; year += 1) yearsCE.push(year);
                const yearLabels = yearsCE.map((year) => yearLabelTH(year));
                window.yearlyNewVsTotalLabelMap = {};
                yearLabels.forEach((label, index) => {
                    window.yearlyNewVsTotalLabelMap[label] = yearsCE[index];
                });

                const totalPerYear = {};
                const newPerYear = {};
                const inProgressPerYear = {};
                const completedPerYear = {};
                const cancelledPerYear = {};

                yearsCE.forEach((year) => {
                    totalPerYear[year] = projects.filter((project) => {
                        const startYear = projectLifecycleStartYearCE(project);
                        if (Number.isFinite(startYear)) return startYear <= year;
                        return year === maxYearCE;
                    }).length;
                    newPerYear[year] = projects.filter((project) => projectLifecycleStartYearCE(project) === year).length;
                    inProgressPerYear[year] = projects.filter((project) => projectIsInProgressInYear(project, year, maxYearCE)).length;
                    completedPerYear[year] = projects.filter((project) =>
                        projectLifecycleStatusKey(project) === 'completed' &&
                        Number(projectLifecycleTerminalYearCE(project, maxYearCE)) === year
                    ).length;
                    cancelledPerYear[year] = projects.filter((project) =>
                        projectLifecycleStatusKey(project) === 'cancelled' &&
                        Number(projectLifecycleTerminalYearCE(project, maxYearCE)) === year
                    ).length;
                });

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
                        ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
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
                                data: yearsCE.map((year) => totalPerYear[year]),
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
                                data: yearsCE.map((year) => newPerYear[year]),
                                backgroundColor: '#34d399',
                                stack: 'status',
                                order: 1
                            },
                            {
                                label: 'กำลังดำเนินการ',
                                data: yearsCE.map((year) => inProgressPerYear[year]),
                                backgroundColor: '#fbbf24',
                                stack: 'status',
                                order: 1
                            },
                            {
                                label: 'ปิดโปรเจค',
                                data: yearsCE.map((year) => completedPerYear[year]),
                                backgroundColor: '#ef4444',
                                stack: 'status',
                                order: 1
                            },
                            {
                                label: 'ยกเลิกโปรเจค',
                                data: yearsCE.map((year) => cancelledPerYear[year]),
                                backgroundColor: '#f97316',
                                stack: 'status',
                                order: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
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
                                top: isMobileView ? 12 : 16,
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
                                    autoSkip: true,
                                    maxTicksLimit: isMobileView ? 4 : 10,
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
                const fteData = {};
                ftes.forEach(f => {
                    const name = f.name || 'ไม่ระบุ';
                    if (!fteData[name]) fteData[name] = {
                        fte: 0,
                        projectSet: new Set()
                    };
                    fteData[name].fte += toNumber(f.fte_value || f.fte || 0);
                    fteData[name].projectSet.add(f.project_id);
                });
                const datasets = Object.entries(fteData).map(([name, data]) => ({
                    label: name,
                    data: [{
                        x: data.projectSet.size,
                        y: data.fte,
                        r: Math.max(5, data.fte * 10)
                    }]
                }));
                new Chart(document.getElementById('chartFteSummary'), {
                    type: 'bubble',
                    data: {
                        datasets
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
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
                    }
                });
            })();

            /* ---------- NEW: 13) Red Zone Timeline + Overdue Table + Co-Research Window ---------- */
            (function() {
                const now = new Date();
                const overdueProjects = [];
                const dueSoon = {
                    '3': 0,
                    '6': 0,
                    '9': 0
                };

                projects.forEach(p => {
                    const endDate = parseProjectDate(getEndDateRaw(p));
                    if (!endDate) return;

                    const diffDays = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24);
                    const diffMonths = diffDays / 30.4375;

                    if (diffDays < 0) {
                        overdueProjects.push({
                            project: p,
                            endDate,
                            diffDays
                        });
                    } else if (diffMonths <= 3) {
                        dueSoon['3']++;
                    } else if (diffMonths <= 6) {
                        dueSoon['6']++;
                    } else if (diffMonths <= 9) {
                        dueSoon['9']++;
                    }
                });

                // --- Chart 1: Red Zone Timeline (Overdue + 3/6/9 months) ---
                const labels = ['เกินกำหนด', '3 เดือน', '6 เดือน', '9 เดือน'];
                const data = [overdueProjects.length, dueSoon['3'], dueSoon['6'], dueSoon['9']];
                const colors = ['#b91c1c', '#d97706', '#f59e0b', '#fcd34d'];

                const elTimeline = document.getElementById('chartRedZoneTimeline');
                if (elTimeline) {
                    const barValuePlugin = {
                        id: 'barValueRedZone',
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
                            ctx.font = 'bold 11px sans-serif';
                            ctx.fillStyle = '#111827';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            meta.data.forEach((bar, i) => {
                                const v = Number(ds.data[i] || 0);
                                if (!isFinite(v) || v <= 0) return;
                                let x = bar.x;
                                let y = bar.y - 4;
                                if (y < top + 8) y = top + 8;
                                if (x > right - 2) x = right - 2;
                                ctx.fillText(String(v), x, y);
                            });
                            ctx.restore();
                        }
                    };
                    new Chart(elTimeline, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'จำนวนโครงการ',
                                data,
                                backgroundColor: colors
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: 'สถานะโครงการเทียบวันสิ้นสุด'
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
                                    title: {
                                        display: true,
                                        text: 'จำนวนโครงการ'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        },
                        plugins: [barValuePlugin]
                    });
                }

                // --- Chart 2: Overdue Projects (Gauge) ---
                const elOverdue = document.getElementById('chartOverdueProjects');
                if (elOverdue) {
                    const projectsWithEndDate = projects.filter(p => parseProjectDate(getEndDateRaw(p)));
                    const totalProjects = projectsWithEndDate.length || 1;
                    const overdueCount = overdueProjects.length;
                    const remainingCount = Math.max(totalProjects - overdueCount, 0);

                    const doughnutValuePlugin = {
                        id: 'doughnutValueOverdue',
                        afterDatasetsDraw(chart) {
                            const {
                                ctx
                            } = chart;
                            const meta = chart.getDatasetMeta(0);
                            const ds = chart.data.datasets[0];
                            if (!meta || !ds) return;
                            ctx.save();
                            ctx.font = 'bold 12px sans-serif';
                            ctx.fillStyle = '#111827';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            meta.data.forEach((arc, i) => {
                                const v = Number(ds.data[i] || 0);
                                if (!isFinite(v) || v <= 0) return;
                                const pos = arc.tooltipPosition();
                                ctx.fillText(String(v), pos.x, pos.y);
                            });
                            ctx.restore();
                        }
                    };

                    const overdueChart = new Chart(elOverdue, {
                        type: 'doughnut',
                        data: {
                            labels: ['เกินกำหนดแล้ว', 'โครงการที่ยังดำเนินการ'],
                            datasets: [{
                                data: [overdueCount, remainingCount],
                                backgroundColor: ['#b91c1c', '#0f766e'],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            cutout: '62%',
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const value = ctx.parsed || 0;
                                            const pct = ((value / totalProjects) * 100).toFixed(1);
                                            return ` ${ctx.label}: ${value} โครงการ (${pct}%)`;
                                        }
                                    }
                                }
                            }
                        },
                        plugins: [doughnutValuePlugin]
                    });
                    if (isTabletLandscape) {
                        elOverdue.style.width = '100%';
                        overdueChart.resize();
                    }
                }

                // --- Overdue Table ---
                const overdueBadge = document.getElementById('overdueCountBadge');
                if (overdueBadge) {
                    overdueBadge.textContent = `${overdueProjects.length} โครงการ`;
                }

                const tbody = document.getElementById('overdueTableBody');
                const overduePrev = document.getElementById('overduePrev');
                const overdueNext = document.getElementById('overdueNext');
                const overduePagination = document.getElementById('overduePagination');
                let overduePage = 1;
                const overduePageSize = 10;

                function renderOverdueTable() {
                    if (!tbody) return;
                    const sorted = overdueProjects.sort((a, b) => a.endDate - b.endDate);
                    const total = sorted.length;
                    const totalPages = Math.max(Math.ceil(total / overduePageSize), 1);
                    overduePage = Math.min(Math.max(overduePage, 1), totalPages);

                    const start = (overduePage - 1) * overduePageSize;
                    const pageRows = sorted.slice(start, start + overduePageSize);

                    tbody.innerHTML = '';
                    if (!pageRows.length) {
                        tbody.innerHTML =
                            `<tr class="border-t"><td colspan="4" class="p-3 text-center text-gray-500">ไม่มีโครงการที่เกินกำหนด</td></tr>`;
                    } else {
                        pageRows.forEach(item => {
                            const overdueDays = Math.ceil(Math.abs(item.diffDays));
                            const row = document.createElement('tr');
                            row.className = 'border-t';
                            row.innerHTML = `
                                <td class="p-2 text-left text-blue-700 hover:underline" data-label="โครงการ">
                                    <a href="/rm/projects/${item.project.id}" target="_blank" rel="noopener">${item.project.project_name_th || '-'}</a>
                                </td>
                                <td class="p-2 text-center" data-label="วันสิ้นสุด">${formatDateTH(item.endDate, item.project.end_date || '-')}</td>
                                <td class="p-2 text-center text-red-600 font-semibold" data-label="เกินกำหนด (วัน)">${overdueDays}</td>
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
                    const unitRows = unitList.map((u) => ({
                        unit: u,
                        m3: countByUnitWithin(u, 3),
                        m6: countByUnitWithin(u, 6),
                        m12: countByUnitWithin(u, 12),
                    }));
                    unitRows.sort((a, b) => b.m12 - a.m12);
                    const unitRowsLimited = isMobileView ? unitRows.slice(0, 6) : unitRows;

                    const labels = unitRowsLimited.map(r => r.unit);
                    const within3 = unitRowsLimited.map(r => r.m3);
                    const within6 = unitRowsLimited.map(r => r.m6);
                    const within12 = unitRowsLimited.map(r => r.m12);

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
                            ctx.font = isMobileView ? 'bold 10px sans-serif' : 'bold 11px sans-serif';
                            ctx.fillStyle = '#111827';
                            ctx.textAlign = isMobileView ? 'left' : 'center';
                            ctx.textBaseline = isMobileView ? 'middle' : 'bottom';
                            chart.data.datasets.forEach((ds, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                const meta = chart.getDatasetMeta(dsIndex);
                                meta.data.forEach((bar, i) => {
                                    const v = Number(ds.data[i] || 0);
                                    if (!isFinite(v) || v <= 0) return;
                                    let x = bar.x;
                                    let y = bar.y - 3;
                                    if (isMobileView) {
                                        x = Math.min(bar.x + 6, right - 6);
                                        y = bar.y;
                                        if (x >= right - 18) {
                                            ctx.textAlign = 'right';
                                            x = right - 4;
                                        } else {
                                            ctx.textAlign = 'left';
                                        }
                                        ctx.fillText(String(v), x, y);
                                        return;
                                    }
                                    if (y < top + 8) y = top + 8;
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
                            labels,
                            datasets: [{
                                    label: 'ภายใน 3 เดือน',
                                    data: within3,
                                    backgroundColor: '#0f766e'
                                },
                                {
                                    label: 'ภายใน 6 เดือน',
                                    data: within6,
                                    backgroundColor: '#c0841a'
                                },
                                {
                                    label: 'ภายใน 1 ปี',
                                    data: within12,
                                    backgroundColor: '#475569'
                                }
                            ]
                        },
                        options: isMobileView ? {
                            indexAxis: 'y',
                            responsive: true,
                            layout: {
                                padding: {
                                    right: 14,
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10,
                                        padding: 8,
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw || 0} คน`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0,
                                        maxTicksLimit: 5
                                    },
                                    title: {
                                        display: true,
                                        text: 'จำนวน Co-Research'
                                    }
                                },
                                y: {
                                    ticks: {
                                        callback: function(value) {
                                            return truncateLegendLabel(this.getLabelForValue(value), 18);
                                        }
                                    }
                                }
                            }
                        } : {
                            responsive: true,
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
                                    },
                                    title: {
                                        display: true,
                                        text: 'จำนวน Co-Research'
                                    }
                                },
                                x: {
                                    ticks: {
                                        autoSkip: false
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
                let scopedProjects = projects.slice();

                function formatDate(d) {
                    return formatDateTH(d, '-');
                }

                function applyFilter() {
                    const mode = filterEl?.value || 'exp_3m';
                    const now = new Date();
                    const rows = [];
                    scopedProjects.forEach(p => {
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

                function render(rows = scopedProjects) {
                    scopedProjects = Array.isArray(rows) ? rows.slice() : [];
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

                window.renderDashboardIrbList = (rows) => {
                    page = 1;
                    render(rows);
                };

                render(projects);
            })();

            /* ---------- Export CSV/Excel (ตามตัวกรอง) ---------- */
            const isCompactDashboardViewport = window.innerWidth <= 1024;

            function drawDashboardMobileEmptyChart(canvas, text = 'ไม่มีข้อมูล') {
                if (!canvas) return;
                Chart.getChart(canvas)?.destroy();
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.save();
                ctx.font = '14px sans-serif';
                ctx.fillStyle = '#6b7280';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(text, canvas.width / 2, canvas.height / 2);
                ctx.restore();
            }

            function sortDashboardYearKeys(values) {
                const isNum = (value) => /^\d+$/.test(String(value ?? '').trim());
                const uniqueValues = Array.from(new Set((values || [])
                    .map((value) => String(value ?? '').trim())
                    .filter(Boolean)));
                return uniqueValues
                    .filter(isNum)
                    .map(Number)
                    .sort((a, b) => a - b)
                    .map(String)
                    .concat(uniqueValues.filter((value) => !isNum(value)).sort((a, b) => String(a).localeCompare(String(b), 'th')));
            }

            function createDashboardMobileBarValuePlugin(id, formatter = (value) => String(value), options = {}) {
                const minValue = Number(options.minValue ?? 1);
                return {
                    id,
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
                        ctx.font = isCompactDashboardViewport ? '700 10px sans-serif' : '700 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        chart.data.datasets.forEach((dataset, datasetIndex) => {
                            if (!chart.isDatasetVisible(datasetIndex)) return;
                            const meta = chart.getDatasetMeta(datasetIndex);
                            meta.data.forEach((bar, index) => {
                                if (!bar || bar.hidden) return;
                                const value = Number(dataset.data?.[index] ?? 0);
                                if (!isFinite(value) || value < minValue) return;
                                const label = typeof formatter === 'function' ? formatter(value) : String(value);
                                let x = bar.x;
                                let y = bar.y - 5;
                                if (y < top + 9) y = top + 9;
                                if (x > right - 3) x = right - 3;
                                if (x < left + 3) x = left + 3;
                                ctx.fillText(label, x, y);
                            });
                        });

                        ctx.restore();
                    }
                };
            }

            function createDashboardMobileStackTotalPlugin(id, formatter = (value) => String(value)) {
                return {
                    id,
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
                        ctx.font = isCompactDashboardViewport ? '700 10px sans-serif' : '700 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        (chart.data.labels || []).forEach((_, index) => {
                            let total = 0;
                            let topElement = null;

                            chart.data.datasets.forEach((dataset, datasetIndex) => {
                                if (!chart.isDatasetVisible(datasetIndex)) return;
                                const value = Number(dataset.data?.[index] ?? 0);
                                if (!isFinite(value) || value <= 0) return;
                                total += value;
                                const element = chart.getDatasetMeta(datasetIndex)?.data?.[index];
                                if (element && (!topElement || element.y < topElement.y)) {
                                    topElement = element;
                                }
                            });

                            if (!(total > 0) || !topElement) return;

                            const label = typeof formatter === 'function' ? formatter(total) : String(total);
                            let x = topElement.x;
                            let y = topElement.y - 6;
                            if (y < top + 10) y = top + 10;
                            if (x > right - 3) x = right - 3;
                            if (x < left + 3) x = left + 3;
                            ctx.fillText(label, x, y);
                        });

                        ctx.restore();
                    }
                };
            }

            function formatDashboardMobileMoney(value) {
                const amount = Number(value) || 0;
                return `${(amount / 1_000_000).toFixed(2).replace(/\.00$/, '')} ล้าน`;
            }

            function getDashboardRowsOutputs(rows) {
                return (rows || []).flatMap((project) => Array.isArray(project?.outputs) ? project.outputs : []);
            }

            (function() {
                function renderProjectsPerYear(rows = projects) {
                    const canvas = document.getElementById('chartProjectsPerYear');
                    if (!canvas) return;

                    const startYearsCE = (rows || [])
                        .map((project) => projectLifecycleStartYearCE(project))
                        .filter(Number.isFinite)
                        .sort((a, b) => a - b);

                    if (!startYearsCE.length) {
                        drawDashboardMobileEmptyChart(canvas);
                        return;
                    }

                    const yearsCE = Array.from(new Set(startYearsCE));
                    const maxYearCE = yearsCE[yearsCE.length - 1];
                    const labels = yearsCE.map((year) => yearLabelTH(year));
                    const data = yearsCE.map((year) =>
                        rows.filter((project) => {
                            const startYear = projectLifecycleStartYearCE(project);
                            if (Number.isFinite(startYear)) return startYear <= year;
                            return year === maxYearCE;
                        }).length
                    );

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'จำนวนโครงการ',
                                data,
                                backgroundColor: labels.map((_, index) => `hsl(${(index * 37) % 360} 70% 60%)`),
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
                            },
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
                                x: {
                                    ticks: {
                                        autoSkip: true,
                                        maxTicksLimit: isCompactDashboardViewport ? 4 : 8,
                                        maxRotation: 0,
                                        minRotation: 0
                                    },
                                    title: {
                                        display: true,
                                        text: 'ปี (พ.ศ.)'
                                    }
                                },
                                y: {
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
                        plugins: [createDashboardMobileBarValuePlugin('mobileProjectsPerYearLabel')]
                    });
                }

                function renderYearlyLifecycle(rows = projects) {
                    const canvas = document.getElementById('chartYearlyNewVsTotal');
                    if (!canvas) return;

                    const startYears = (rows || [])
                        .map((project) => projectLifecycleStartYearCE(project))
                        .filter(Number.isFinite);

                    if (!startYears.length) {
                        drawDashboardMobileEmptyChart(canvas);
                        return;
                    }

                    const eventYears = rows
                        .filter((project) => {
                            const key = projectLifecycleStatusKey(project);
                            return key === 'completed' || key === 'cancelled';
                        })
                        .map((project) => projectLifecycleEventYearCE(project))
                        .filter(Number.isFinite);

                    const minYearCE = Math.min(...startYears);
                    const currentYearCE = new Date().getFullYear();
                    const maxYearCE = Math.max(...startYears, ...eventYears, currentYearCE);

                    const yearsCE = [];
                    for (let year = minYearCE; year <= maxYearCE; year += 1) yearsCE.push(year);
                    const yearLabels = yearsCE.map((year) => yearLabelTH(year));
                    window.yearlyNewVsTotalLabelMap = {};
                    yearLabels.forEach((label, index) => {
                        window.yearlyNewVsTotalLabelMap[label] = yearsCE[index];
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
                        newPerYear[year] = rows.filter((project) => projectLifecycleStartYearCE(project) === year).length;
                        inProgressPerYear[year] = rows.filter((project) => projectIsInProgressInYear(project, year, maxYearCE)).length;
                        completedPerYear[year] = rows.filter((project) =>
                            projectLifecycleStatusKey(project) === 'completed' &&
                            Number(projectLifecycleTerminalYearCE(project, maxYearCE)) === year
                        ).length;
                        cancelledPerYear[year] = rows.filter((project) =>
                            projectLifecycleStatusKey(project) === 'cancelled' &&
                            Number(projectLifecycleTerminalYearCE(project, maxYearCE)) === year
                        ).length;
                    });

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: yearLabels,
                            datasets: [{
                                    label: 'ทั้งหมด',
                                    data: yearsCE.map((year) => totalPerYear[year]),
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
                                    data: yearsCE.map((year) => newPerYear[year]),
                                    backgroundColor: '#34d399',
                                    stack: 'status',
                                    order: 1
                                },
                                {
                                    label: 'กำลังดำเนินการ',
                                    data: yearsCE.map((year) => inProgressPerYear[year]),
                                    backgroundColor: '#fbbf24',
                                    stack: 'status',
                                    order: 1
                                },
                                {
                                    label: 'ปิดโปรเจค',
                                    data: yearsCE.map((year) => completedPerYear[year]),
                                    backgroundColor: '#ef4444',
                                    stack: 'status',
                                    order: 1
                                },
                                {
                                    label: 'ยกเลิกโปรเจค',
                                    data: yearsCE.map((year) => cancelledPerYear[year]),
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
                                    top: 14,
                                    right: 16
                                }
                            },
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
                                x: {
                                    stacked: true
                                },
                                y: {
                                    beginAtZero: true,
                                    stacked: true,
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
                        plugins: [createDashboardMobileBarValuePlugin('mobileLifecycleLabel')]
                    });
                }

                function renderFundingSplit(rows = projects) {
                    const canvas = document.getElementById('chartFundingSplit');
                    if (!canvas) return;

                    const years = sortDashboardYearKeys((rows || []).map((project) => getYear(project)));
                    if (!years.length) {
                        drawDashboardMobileEmptyChart(canvas);
                        return;
                    }

                    const types = ['ภายใน', 'ภายนอก'];
                    const matrix = {};
                    years.forEach((year) => {
                        matrix[year] = { 'ภายใน': 0, 'ภายนอก': 0 };
                    });

                    rows.forEach((project) => {
                        const year = String(getYear(project) ?? '').trim() || 'ไม่ระบุ';
                        const fundingType = getFundingType(project);
                        if (!matrix[year] || !types.includes(fundingType)) return;
                        matrix[year][fundingType] += 1;
                    });

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: years,
                            datasets: types.map((type) => ({
                                label: type,
                                data: years.map((year) => matrix[year][type]),
                                backgroundColor: type === 'ภายใน' ? '#60a5fa' : '#34d399',
                                stack: 'fund'
                            }))
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
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        footer: (items) => {
                                            const total = (items || []).reduce((sum, item) => sum + (Number(item.parsed?.y || 0)), 0);
                                            return `รวม: ${total} โครงการ`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: {
                                        autoSkip: true,
                                        maxTicksLimit: isCompactDashboardViewport ? 4 : 8,
                                        maxRotation: 0,
                                        minRotation: 0
                                    },
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
                        plugins: [createDashboardMobileStackTotalPlugin('mobileFundingSplitTotal')]
                    });
                }

                function renderBudgetPerYear(rows = projects) {
                    const canvas = document.getElementById('chartBudgetPerYear');
                    if (!canvas) return;

                    const years = sortDashboardYearKeys((rows || []).map((project) => getYear(project)));
                    if (!years.length) {
                        drawDashboardMobileEmptyChart(canvas);
                        return;
                    }

                    const data = years.map((year) => rows
                        .filter((project) => String(getYear(project) ?? '').trim() === year)
                        .reduce((sum, project) => sum + (Number(getBudget(project)) || 0), 0));

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
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
                            maintainAspectRatio: false,
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
                                    callbacks: {
                                        label: (ctx) => ` ${formatDashboardMobileMoney(ctx.raw)}`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        autoSkip: true,
                                        maxTicksLimit: isCompactDashboardViewport ? 4 : 8,
                                        maxRotation: 0,
                                        minRotation: 0
                                    },
                                    title: {
                                        display: true,
                                        text: 'ปี'
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grace: '12%',
                                    ticks: {
                                        callback: (value) => formatDashboardMobileMoney(value)
                                    },
                                    title: {
                                        display: true,
                                        text: 'งบประมาณ'
                                    }
                                }
                            }
                        },
                        plugins: [createDashboardMobileBarValuePlugin('mobileBudgetPerYearLabel', (value) => formatDashboardMobileMoney(value), { minValue: 0.01 })]
                    });
                }

                function renderOutputStacked(rows = projects) {
                    const canvas = document.getElementById('chartOutputStacked');
                    if (!canvas) return;

                    const outputRows = getDashboardRowsOutputs(rows);
                    const types = [...new Set(outputRows.map(outputType).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'th'));
                    const matrix = {};

                    outputRows.forEach((output) => {
                        const yearKey = outputYearKey(output);
                        const type = outputType(output) || 'อื่นๆ';
                        matrix[yearKey] = matrix[yearKey] || {};
                        matrix[yearKey][type] = (matrix[yearKey][type] || 0) + 1;
                    });

                    const sortedYears = sortDashboardYearKeys(Object.keys(matrix));
                    if (!sortedYears.length || !types.length) {
                        drawDashboardMobileEmptyChart(canvas);
                        return;
                    }

                    const displayLabels = sortedYears.map((year) => outputYearLabelForKey(year));
                    window.outputYearLabelKeyMap = {};
                    displayLabels.forEach((label, index) => {
                        window.outputYearLabelKeyMap[label] = sortedYears[index];
                    });

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: displayLabels,
                            datasets: types.map((type, index) => ({
                                label: type,
                                data: sortedYears.map((year) => Number(matrix[year]?.[type] || 0)),
                                backgroundColor: `hsl(${(index * 53) % 360} 70% 60%)`,
                                stack: 'out'
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
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        footer: (items) => {
                                            const total = (items || []).reduce((sum, item) => sum + (Number(item.parsed?.y || 0)), 0);
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
                        plugins: [createDashboardMobileStackTotalPlugin('mobileOutputStackedTotal')]
                    });
                }

                function renderStatusYear(rows = projects) {
                    const canvas = document.getElementById('chartStatusYear');
                    if (!canvas) return;

                    const statuses = [
                        'Project Planing (เริ่มต้น - submit IRB)',
                        'เก็บข้อมูล',
                        'วิเคราะห์ข้อมูล',
                        'เขียนรายงานและpublication',
                        'ปิดโปรเจค',
                        'ยกเลิกโปรเจค'
                    ];

                    const statusData = {};
                    rows.forEach((project) => {
                        const year = String(getYear(project) ?? '').trim() || 'ไม่ระบุ';
                        const status = project.planning_status || 'ไม่ระบุ';
                        if (!statusData[year]) statusData[year] = Object.fromEntries(statuses.map((item) => [item, 0]));
                        if (statuses.includes(status)) statusData[year][status] += 1;
                    });

                    const sortedYears = sortDashboardYearKeys(Object.keys(statusData));
                    if (!sortedYears.length) {
                        drawDashboardMobileEmptyChart(canvas);
                        return;
                    }

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: sortedYears,
                            datasets: statuses.map((status, index) => ({
                                label: status,
                                data: sortedYears.map((year) => statusData[year]?.[status] ?? 0),
                                backgroundColor: `hsl(${(index * 46) % 360} 70% 60%)`,
                                stack: 'st'
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 18
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
                                            const total = (items || []).reduce((sum, item) => sum + (Number(item.parsed?.y || 0)), 0);
                                            return `รวมปีนี้: ${total} โครงการ`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: {
                                        autoSkip: true,
                                        maxTicksLimit: isCompactDashboardViewport ? 4 : 8,
                                        maxRotation: 0,
                                        minRotation: 0
                                    },
                                    title: {
                                        display: true,
                                        text: 'ปี'
                                    }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grace: '14%',
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
                        plugins: [createDashboardMobileStackTotalPlugin('mobileStatusYearTotal')]
                    });
                }

                function renderIrbExpiry(rows = projects) {
                    const canvas = document.getElementById('chartIRBExpiry');
                    if (!canvas) return;

                    const group = {};
                    (rows || []).forEach((project) => {
                        (project.irbs || []).forEach((irb) => {
                            const expDate = parseProjectDate(irb.exp_date);
                            if (!expDate) return;
                            const monthsAhead = (expDate - new Date()) / (1000 * 60 * 60 * 24 * 30);
                            if (monthsAhead >= 0 && monthsAhead <= 6) {
                                const key = `${expDate.getMonth() + 1}/${expDate.getFullYear()}`;
                                group[key] = (group[key] || 0) + 1;
                            }
                        });
                    });

                    const labels = Object.keys(group).sort((a, b) => new Date(a) - new Date(b));
                    if (!labels.length) {
                        drawDashboardMobileEmptyChart(canvas, 'ไม่มีข้อมูล IRB');
                        return;
                    }

                    Chart.getChart(canvas)?.destroy();
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'IRB ใกล้หมดอายุ',
                                data: labels.map((label) => group[label]),
                                backgroundColor: '#ef4444',
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
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${ctx.raw} รายการ`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        autoSkip: true,
                                        maxTicksLimit: isCompactDashboardViewport ? 4 : 6,
                                        maxRotation: 0,
                                        minRotation: 0
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grace: '12%',
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        },
                        plugins: [createDashboardMobileBarValuePlugin('mobileIrbExpiryLabel')]
                    });
                }

                function refreshRealtimeDashboardMobile(rows = getFilteredProjects()) {
                    renderProjectsPerYear(rows);
                    renderYearlyLifecycle(rows);
                    renderFundingSplit(rows);
                    renderBudgetPerYear(rows);
                    renderOutputStacked(rows);
                    renderStatusYear(rows);
                    renderIrbExpiry(rows);
                }

                window.renderDashboardProjectsPerYearChart = renderProjectsPerYear;
                window.renderDashboardYearlyLifecycleChart = renderYearlyLifecycle;
                window.renderDashboardFundingSplitChart = renderFundingSplit;
                window.renderDashboardBudgetPerYearChart = renderBudgetPerYear;
                window.renderDashboardOutputStackedChart = renderOutputStacked;
                window.renderDashboardStatusYearChart = renderStatusYear;
                window.renderDashboardIrbExpiryChart = renderIrbExpiry;
                window.refreshRealtimeDashboardMobile = refreshRealtimeDashboardMobile;
            })();

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
                const filterName = document.getElementById('filterName');
                const tableSearch = document.getElementById('tableSearch');
                const filterReset = document.getElementById('filterReset');
                const filterResultBadge = document.getElementById('filterResultBadge');

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
                    if (filterResultBadge) filterResultBadge.textContent = `${total.toLocaleString('th-TH')} โครงการ`;
                    if (pageEl) pageEl.textContent = `หน้า ${currentPage} / ${totalPages}`;
                    if (prevBtn) prevBtn.disabled = currentPage <= 1;
                    if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
                    window.refreshRealtimeDashboardMobile?.(rows);
                    window.renderDashboardIrbList?.(rows);
                    renderDashboardFilterState(total);
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
                [filterName, tableSearch].forEach(el => el?.addEventListener('input', resetAndRender));
                filterReset?.addEventListener('click', () => {
                    if (filterYear) filterYear.value = '';
                    if (filterTheme) filterTheme.value = '';
                    if (filterFunding) filterFunding.value = '';
                    if (filterType) filterType.value = '';
                    if (filterStatus) filterStatus.value = '';
                    if (filterSource) filterSource.value = '';
                    if (filterName) filterName.value = '';
                    if (tableSearch) tableSearch.value = '';
                    resetAndRender();
                });

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
                    const requestedTopN = parseInt(selTopN?.value || '12', 10);
                    const TOP_N = isMobileView ? Math.min(requestedTopN, 8) : requestedTopN;

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
                    el.height = Math.min(Math.max(320, labels.length * 30), 460);

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
        </script>
        <script>
            // Hide chart scroll hint once user scrolls
            document.addEventListener('DOMContentLoaded', () => {
                const scroller = document.querySelector('.lux-chart-scroll');
                const hint = document.getElementById('chartScrollHint');
                if (!scroller || !hint) return;

                const hide = () => hint.classList.add('is-hide');
                scroller.addEventListener('scroll', hide, { once: true });
                scroller.addEventListener('touchmove', hide, { once: true });
            });
        </script>

        <script>
            // Ensure charts resize on mobile orientation/viewport changes
            const resizeCharts = () => {
                window.applyChartCanvasSizing?.();
                document.querySelectorAll('.lux-chart-card canvas').forEach((c) => {
                    const chart = typeof Chart !== 'undefined' ? Chart.getChart(c) : null;
                    if (chart) chart.resize();
                });
            };
            window.addEventListener('resize', () => {
                clearTimeout(window.__chartResizeTimer);
                window.__chartResizeTimer = setTimeout(resizeCharts, 150);
            });
            window.addEventListener('orientationchange', () => {
                setTimeout(resizeCharts, 200);
            });
        </script>

    </div>

    <!-- PDF Export workspace (mobile) -->
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
        .pdf-header { margin-bottom: 8px; }
        .pdf-title { font-size: 18px; font-weight: 700; letter-spacing: 0.01em; }
        .pdf-subtitle { font-size: 12px; color: #475569; }
        .pdf-meta { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; font-size: 11px; color: #1f2937; margin-top: 4px; }
        .pdf-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .pdf-chart-card, .pdf-table-card { border: none; border-radius: 0; padding: 8px; background: #ffffff; box-shadow: none; height: 100%; box-sizing: border-box; }
        .pdf-chart-title, .pdf-table-title { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .pdf-chart-card img { width: 100%; height: auto; display: block; border-radius: 8px; background: #f8fafc; }
        .pdf-table-card table { width: 100%; border-collapse: collapse; font-size: 11px; color: #0f172a; table-layout: fixed; }
        .pdf-table-card th, .pdf-table-card td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; word-break: break-word; white-space: normal; vertical-align: top; }
        .pdf-table-card th { background: #f1f5f9; font-weight: 700; }
    </style>
    <div id="pdfExportContainer" class="hidden"></div>
    <div id="pdfExportOverlay"
        class="hidden fixed inset-0 z-[60] bg-black/30 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg px-4 py-3 text-sm text-slate-700 flex items-center gap-2">
            <div class="h-4 w-4 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
            <div>กำลังสร้างไฟล์ PDF...</div>
        </div>
    </div>
@endsection
