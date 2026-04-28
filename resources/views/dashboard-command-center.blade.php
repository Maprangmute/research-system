@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap');

        :root {
            --lux-ink: #0f172a;
            --lux-ink-soft: #475569;
            --lux-line: rgba(203, 213, 225, 0.78);
            --lux-gold: #a16207;
            --lux-emerald: #0f766e;
            --lux-emerald-deep: #115e59;
            --lux-ivory: #f8fafc;
            --lux-mist: #f8fafc;
            --tone-focus: #0f766e;
            --tone-healthy: #15803d;
            --tone-warning: #d97706;
            --tone-critical: #dc2626;
            --tone-neutral: #64748b;
            --glass: rgba(255, 255, 255, 0.94);
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
            background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        }

        .lux-page {
            position: relative;
            min-height: 100%;
            overflow-x: hidden;
            color: var(--lux-ink);
            font-family: 'IBM Plex Sans Thai', sans-serif;
        }

        .lux-title,
        .cc-display {
            font-family: 'IBM Plex Sans Thai', sans-serif;
            letter-spacing: 0;
        }

        .cc-shell {
            position: relative;
            z-index: 1;
            max-width: 1320px;
        }

        .lux-card,
        .cc-section-panel,
        .cc-panel {
            border: 1px solid rgba(226, 232, 240, 0.92);
            border-radius: 10px;
            background: var(--glass);
            box-shadow: 0 16px 38px rgba(15, 23, 42, 0.06);
            backdrop-filter: blur(8px);
        }

        .lux-hero {
            padding: 1.35rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(248, 250, 252, 0.94));
            border-color: rgba(148, 163, 184, 0.28);
        }

        .lux-kicker {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--lux-emerald-deep);
        }

        .lux-body {
            color: var(--lux-ink-soft);
            line-height: 1.75;
        }

        .cc-chip,
        .cc-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            border: 1px solid rgba(15, 118, 110, 0.12);
            background: rgba(255, 255, 255, 0.9);
            color: var(--lux-emerald-deep);
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.52rem 0.92rem;
            text-decoration: none;
            box-shadow: none;
        }

        .cc-pill {
            cursor: pointer;
        }

        .cc-pill:hover {
            border-color: rgba(15, 118, 110, 0.24);
        }

        .cc-hero-grid,
        .cc-stat-grid,
        .cc-filter-grid,
        .cc-kpi-grid,
        .cc-chart-grid,
        .cc-delivery-grid,
        .cc-support-grid,
        .cc-hero-side-grid,
        .cc-story-band,
        .cc-matrix-grid,
        .cc-intel-grid,
        .cc-pressure-grid,
        .cc-command-strip {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .cc-command-strip {
            margin-top: 1.15rem;
        }

        .cc-command-item {
            border-left: 3px solid var(--item-tone, var(--tone-focus));
            border-radius: 8px;
            background: rgba(248, 250, 252, 0.9);
            padding: 0.85rem 0.95rem;
        }

        .cc-command-item strong {
            display: block;
            color: var(--lux-ink);
            font-size: 0.95rem;
        }

        .cc-command-item span {
            display: block;
            margin-top: 0.22rem;
            color: var(--lux-ink-soft);
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .cc-hero-copy {
            min-width: 0;
        }

        .cc-hero-card,
        .cc-kpi,
        .cc-chart-card,
        .cc-table-card,
        .cc-note-card,
        .cc-alert-card,
        .cc-signal-card {
            position: relative;
            min-width: 0;
            overflow: hidden;
            border-radius: 10px;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(255, 255, 255, 0.86);
        }

        .cc-hero-card,
        .cc-note-card,
        .cc-table-card,
        .cc-chart-card,
        .cc-kpi {
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.045);
        }

        .cc-signal-card,
        .cc-kpi,
        .cc-chart-card,
        .cc-table-card,
        .cc-note-card {
            padding: 1.1rem 1.15rem;
        }

        .cc-signal-card::before,
        .cc-kpi::before,
        .cc-chart-card::before,
        .cc-table-card::before,
        .cc-note-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: var(--card-tone, rgba(148, 163, 184, 0.45));
        }

        .cc-tone-focus {
            --card-tone: var(--tone-focus);
        }

        .cc-tone-healthy {
            --card-tone: var(--tone-healthy);
        }

        .cc-tone-warning {
            --card-tone: var(--tone-warning);
        }

        .cc-tone-critical {
            --card-tone: var(--tone-critical);
        }

        .cc-tone-neutral {
            --card-tone: var(--tone-neutral);
        }

        .cc-signal-value {
            margin-top: 0.55rem;
            font-size: clamp(1.7rem, 2vw, 2.35rem);
            line-height: 1;
        }

        .cc-signal-meta {
            margin-top: 0.6rem;
            font-size: 0.92rem;
            color: var(--lux-ink-soft);
            line-height: 1.65;
        }

        .cc-hero-callout {
            padding: 1.1rem 1.15rem;
            border-radius: 10px;
            border: 1px solid rgba(15, 118, 110, 0.16);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.85), rgba(246, 241, 232, 0.7));
        }

        .cc-semantic-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-top: 1rem;
        }

        .cc-semantic-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.79rem;
            color: var(--lux-ink-soft);
        }

        .cc-semantic-badge::before {
            content: '';
            width: 0.72rem;
            height: 0.72rem;
            border-radius: 999px;
            background: var(--badge-color, #94a3b8);
            box-shadow: 0 0 0 5px rgba(255, 255, 255, 0.92);
        }

        .cc-semantic-badge[data-tone='focus']::before {
            --badge-color: var(--tone-focus);
        }

        .cc-semantic-badge[data-tone='healthy']::before {
            --badge-color: var(--tone-healthy);
        }

        .cc-semantic-badge[data-tone='warning']::before {
            --badge-color: var(--tone-warning);
        }

        .cc-semantic-badge[data-tone='critical']::before {
            --badge-color: var(--tone-critical);
        }

        .cc-semantic-badge[data-tone='neutral']::before {
            --badge-color: var(--tone-neutral);
        }

        .lux-quick-nav {
            position: sticky;
            top: 0.8rem;
            z-index: 15;
            display: flex;
            gap: 0.7rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .lux-quick-nav a {
            white-space: nowrap;
            text-decoration: none;
            border-radius: 999px;
            border: 1px solid rgba(203, 213, 225, 0.88);
            background: rgba(255, 255, 255, 0.94);
            color: var(--lux-ink-soft);
            font-size: 0.84rem;
            font-weight: 600;
            padding: 0.7rem 1rem;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .cc-section-panel {
            padding: 1.25rem;
        }

        .cc-section-head {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: start;
            margin-bottom: 1.2rem;
        }

        .cc-section-copy h2 {
            margin-top: 0.55rem;
            font-size: clamp(1.55rem, 2vw, 2.05rem);
            line-height: 1.1;
            color: var(--lux-ink);
        }

        .cc-story-band {
            margin-top: 1rem;
        }

        .cc-story-tag {
            padding: 0.95rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(248, 250, 252, 0.82);
        }

        .cc-story-tag .cc-story-title {
            font-size: 0.96rem;
            font-weight: 700;
            color: var(--lux-ink);
        }

        .cc-story-tag .cc-story-body {
            margin-top: 0.4rem;
            font-size: 0.9rem;
            line-height: 1.65;
            color: var(--lux-ink-soft);
        }

        .cc-kpi {
            min-height: 0;
        }

        .cc-kpi__value {
            margin-top: 0.8rem;
            font-size: clamp(1.6rem, 1.8vw, 2.2rem);
            line-height: 1.08;
            color: var(--lux-ink);
            overflow-wrap: anywhere;
        }

        .cc-kpi__meta {
            margin-top: 0.85rem;
            font-size: 0.93rem;
            line-height: 1.62;
            color: var(--lux-ink-soft);
            overflow-wrap: anywhere;
        }

        .cc-label {
            display: block;
            margin-bottom: 0.42rem;
            font-size: 0.73rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--lux-ink-soft);
        }

        .cc-input,
        .cc-select {
            width: 100%;
            border-radius: 8px;
            border: 1px solid rgba(203, 213, 225, 0.95);
            background: rgba(255, 255, 255, 0.95);
            color: var(--lux-ink);
            padding: 0.84rem 0.96rem;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .cc-input:focus,
        .cc-select:focus {
            outline: none;
            border-color: rgba(15, 118, 110, 0.38);
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.11);
        }

        .cc-chart-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .cc-chart-head h3,
        .cc-table-card h3,
        .cc-note-card h3 {
            font-size: 1.08rem;
            font-weight: 700;
            color: var(--lux-ink);
        }

        .cc-chart-shell {
            position: relative;
            min-height: 320px;
        }

        .cc-chart-shell canvas {
            width: 100% !important;
            height: 320px !important;
        }

        .cc-delivery-stack {
            display: grid;
            gap: 1rem;
        }

        .cc-quote {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
        }

        .cc-quote-mark {
            flex: 0 0 auto;
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.1);
            color: var(--lux-emerald-deep);
            font-size: 1.1rem;
            font-weight: 700;
        }

        .cc-spotlight-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.9rem;
        }

        .cc-spotlight {
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid rgba(226, 232, 240, 0.88);
            background: rgba(248, 250, 252, 0.88);
        }

        .cc-spotlight-value {
            margin-top: 0.45rem;
            font-size: 1.02rem;
            font-weight: 650;
            line-height: 1.58;
            color: var(--lux-ink);
        }

        .cc-mini-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cc-mini-table th {
            padding: 0 0 0.75rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #64748b;
            text-align: left;
        }

        .cc-mini-table td {
            padding: 0.9rem 0;
            vertical-align: top;
            font-size: 0.88rem;
            color: var(--lux-ink-soft);
            border-top: 1px solid rgba(226, 232, 240, 0.92);
        }

        .cc-mini-table th:first-child,
        .cc-mini-table td:first-child {
            width: 48%;
            padding-right: 1rem;
        }

        .cc-mini-table tbody tr:first-child td {
            border-top: 1px solid rgba(226, 232, 240, 0.92);
        }

        .cc-cell-title {
            display: block;
            color: var(--lux-ink);
            font-weight: 600;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .cc-cell-meta {
            display: block;
            margin-top: 0.18rem;
            font-size: 0.82rem;
            color: #64748b;
        }

        .cc-link {
            color: #0b6e79;
            text-decoration: none;
        }

        .cc-link:hover {
            text-decoration: underline;
        }

        .cc-note-list {
            display: grid;
            gap: 0.85rem;
        }

        .cc-note-item {
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid rgba(226, 232, 240, 0.88);
            background: rgba(248, 250, 252, 0.84);
        }

        .cc-note-item strong {
            color: var(--lux-ink);
        }

        .cc-alert-card {
            padding: 1rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .cc-alert-card[data-severity="critical"] {
            border-color: rgba(220, 38, 38, 0.26);
            background: linear-gradient(180deg, rgba(254, 242, 242, 0.85), rgba(255, 255, 255, 0.95));
        }

        .cc-alert-card[data-severity="high"] {
            border-color: rgba(217, 119, 6, 0.24);
            background: linear-gradient(180deg, rgba(255, 247, 237, 0.85), rgba(255, 255, 255, 0.95));
        }

        .cc-alert-card[data-severity="medium"] {
            border-color: rgba(15, 118, 110, 0.2);
            background: linear-gradient(180deg, rgba(240, 253, 250, 0.82), rgba(255, 255, 255, 0.95));
        }

        .cc-alert-card[data-severity="low"] {
            border-color: rgba(37, 99, 235, 0.18);
            background: linear-gradient(180deg, rgba(239, 246, 255, 0.82), rgba(255, 255, 255, 0.95));
        }

        .cc-empty {
            padding: 1.1rem;
            border-radius: 8px;
            border: 1px dashed rgba(148, 163, 184, 0.5);
            background: rgba(248, 250, 252, 0.84);
            color: #64748b;
            text-align: center;
        }

        .cc-section-panel--flush {
            padding: 0;
            overflow: hidden;
        }

        .cc-intel-header {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            padding: 1.25rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.72);
        }

        .cc-map {
            position: relative;
            min-height: 340px;
            border-radius: 10px;
            border: 1px solid rgba(203, 213, 225, 0.82);
            background:
                linear-gradient(90deg, rgba(203, 213, 225, 0.38) 1px, transparent 1px),
                linear-gradient(180deg, rgba(203, 213, 225, 0.38) 1px, transparent 1px),
                linear-gradient(135deg, #ffffff, #f8fafc);
            background-size: 25% 100%, 100% 25%, auto;
            overflow: hidden;
        }

        .cc-map::before,
        .cc-map::after {
            content: '';
            position: absolute;
            background: rgba(15, 23, 42, 0.16);
            pointer-events: none;
        }

        .cc-map::before {
            left: 50%;
            top: 0;
            width: 1px;
            height: 100%;
        }

        .cc-map::after {
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
        }

        .cc-map-axis {
            position: absolute;
            z-index: 2;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .cc-map-axis--x {
            right: 0.9rem;
            bottom: 0.75rem;
        }

        .cc-map-axis--y {
            left: 0.8rem;
            top: 0.85rem;
        }

        .cc-map-help {
            display: grid;
            gap: 0.55rem;
            margin-bottom: 0.8rem;
            color: var(--lux-ink-soft);
            font-size: 0.88rem;
            line-height: 1.65;
        }

        .cc-map-help strong {
            color: var(--lux-ink);
        }

        .cc-map-dot {
            position: absolute;
            z-index: 4;
            display: inline-flex;
            width: var(--dot-size, 15px);
            height: var(--dot-size, 15px);
            margin-left: calc(var(--dot-size, 15px) / -2);
            margin-top: calc(var(--dot-size, 15px) / -2);
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.92);
            background: var(--dot-color, var(--tone-focus));
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
            transition: transform 0.16s ease, box-shadow 0.16s ease;
        }

        .cc-map-dot:hover {
            transform: scale(1.45);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.24);
        }

        .cc-map-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 0.9rem;
        }

        .cc-lane {
            min-height: 112px;
            border-radius: 8px;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(248, 250, 252, 0.84);
            padding: 0.95rem;
        }

        .cc-lane strong {
            display: block;
            color: var(--lux-ink);
            font-size: 1.6rem;
            line-height: 1;
            margin-top: 0.55rem;
        }

        .cc-lane span {
            display: block;
            margin-top: 0.42rem;
            color: var(--lux-ink-soft);
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .cc-pressure-card {
            border-radius: 8px;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(255, 255, 255, 0.92);
            padding: 0.9rem;
        }

        .cc-pressure-meter {
            height: 0.5rem;
            margin-top: 0.75rem;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .cc-pressure-meter span {
            display: block;
            height: 100%;
            width: var(--meter, 0%);
            border-radius: inherit;
            background: linear-gradient(90deg, #0f766e, #d97706);
        }

        .cc-modal-backdrop {
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(5px);
        }

        .cc-modal-card {
            max-height: 84vh;
        }

        .cc-shell > section,
        .cc-shell > nav {
            scroll-margin-top: 5.2rem;
        }

        .lux-grid-stagger > * {
            animation: lux-rise 0.55s ease both;
        }

        .lux-grid-stagger > *:nth-child(2) {
            animation-delay: 0.05s;
        }

        .lux-grid-stagger > *:nth-child(3) {
            animation-delay: 0.1s;
        }

        .lux-grid-stagger > *:nth-child(4) {
            animation-delay: 0.15s;
        }

        .lux-grid-stagger > *:nth-child(5) {
            animation-delay: 0.2s;
        }

        .lux-grid-stagger > *:nth-child(6) {
            animation-delay: 0.25s;
        }

        .lux-reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }

        .lux-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes lux-rise {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (min-width: 768px) {
            .lux-hero {
                padding: 2rem;
            }

            .cc-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .cc-filter-grid,
            .cc-kpi-grid,
            .cc-chart-grid,
            .cc-support-grid,
            .cc-story-band,
            .cc-spotlight-grid,
            .cc-command-strip,
            .cc-pressure-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .cc-hero-grid {
                grid-template-columns: 1fr;
                align-items: start;
            }

            .cc-filter-grid {
                grid-template-columns: 1.4fr repeat(4, minmax(0, 1fr));
            }

            .cc-delivery-grid {
                grid-template-columns: minmax(0, 1.08fr) minmax(380px, 0.92fr);
                align-items: start;
            }

            .cc-section-head {
                grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.65fr);
            }

            .cc-hero-side-grid {
                grid-template-columns: 1fr;
            }

            .cc-intel-header {
                grid-template-columns: minmax(0, 1.2fr) minmax(360px, 0.8fr);
                align-items: center;
            }

            .cc-matrix-grid {
                grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.65fr);
                padding: 1.25rem;
            }
        }

        @media (min-width: 1280px) {
            .cc-kpi-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .cc-support-grid.cc-support-grid--three {
                grid-template-columns: 0.8fr 1.45fr 1fr;
            }

            .cc-stat-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }

            .cc-story-band {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .cc-intel-grid,
            .cc-pressure-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .cc-matrix-grid aside .cc-pressure-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .cc-chart-shell,
            .cc-chart-shell canvas {
                min-height: 280px;
                height: 280px !important;
            }

            .lux-quick-nav {
                top: 0.45rem;
            }
        }
    </style>

    <div class="lux-page command-center">
        <div class="cc-shell mx-auto px-4 py-6 sm:px-6 lg:px-8 space-y-6">
            <section class="lux-card lux-hero lux-reveal">
                <div class="cc-hero-grid">
                    <div class="cc-hero-copy">
                        <div class="lux-kicker">ภาพรวมสำหรับผู้บริหาร</div>
                        <h1 class="lux-title mt-3 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">
                            ศูนย์ควบคุมงานวิจัย
                        </h1>
                        <p class="lux-body mt-4 max-w-3xl text-sm md:text-base">
                            อ่านสถานการณ์จาก 4 เรื่องหลัก: จำนวนโครงการ งบประมาณ กำลังคน และสัญญาณที่ต้องตามต่อ
                            สีแดงคือเรื่องเร่งด่วน สีส้มคือเรื่องที่ควรติดตาม สีเขียวคือสถานะที่ยังควบคุมได้
                        </p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <span class="cc-chip">อัปเดตล่าสุด {{ $generatedAt }}</span>
                            <span class="cc-chip">มุมมองงานวิจัย + ภาระงาน</span>
                            <a href="{{ route('dashboard') }}" class="cc-chip">กลับหน้า dashboard เดิม</a>
                        </div>
                        <div class="cc-semantic-row">
                            <span class="cc-semantic-badge" data-tone="healthy">สถานะปกติ</span>
                            <span class="cc-semantic-badge" data-tone="focus">ประเด็นสำคัญ</span>
                            <span class="cc-semantic-badge" data-tone="warning">เริ่มต้องติดตาม</span>
                            <span class="cc-semantic-badge" data-tone="critical">ต้องเร่งตัดสินใจ</span>
                        </div>
                    </div>

                    <div class="cc-hero-side-grid">
                        <div class="cc-stat-grid lux-grid-stagger">
                            <article class="cc-signal-card cc-tone-focus">
	                                <div class="lux-kicker">โครงการ</div>
                                <div class="cc-display cc-signal-value">{{ number_format($summary['projects_total']) }}</div>
	                                <div class="cc-signal-meta">กำลังดำเนินการ {{ number_format($summary['projects_active']) }} จากทั้งหมด</div>
                            </article>
                            <article class="cc-signal-card cc-tone-warning">
	                                <div class="lux-kicker">งบประมาณ</div>
                                <div class="cc-display cc-signal-value">{{ number_format($summary['budget_total'], 0) }}</div>
                                <div class="cc-signal-meta">งบรวมที่ระบบอ่านได้จากฐานข้อมูล</div>
                            </article>
                            <article class="cc-signal-card cc-tone-healthy">
	                                <div class="lux-kicker">กำลังคน</div>
                                <div class="cc-display cc-signal-value">{{ number_format($summary['fte_total'], 2) }}</div>
                                <div class="cc-signal-meta">{{ number_format($summary['fte_people_total']) }} คน | {{ number_format($summary['fte_hours_total'], 2) }} ชั่วโมง</div>
                            </article>
                            <article class="cc-signal-card cc-tone-focus">
	                                <div class="lux-kicker">ผลงาน</div>
                                <div class="cc-display cc-signal-value">{{ number_format($summary['internal_outputs_total'] + $summary['external_outputs_total']) }}</div>
                                <div class="cc-signal-meta">รวมผลงานภายในและภายนอกที่ระบบเก็บได้</div>
                            </article>
                            <article class="cc-signal-card cc-tone-neutral">
	                                <div class="lux-kicker">งานสนับสนุน</div>
                                <div class="cc-display cc-signal-value">{{ number_format($summary['backoffice_total']) }}</div>
                                <div class="cc-signal-meta">{{ number_format($summary['backoffice_hours_total'], 2) }} ชั่วโมงสะสม</div>
                            </article>
                            <article class="cc-signal-card cc-tone-critical">
	                                <div class="lux-kicker">ต้องติดตาม</div>
                                <div class="cc-display cc-signal-value">{{ number_format($summary['alert_total']) }}</div>
                                <div class="cc-signal-meta">ประเด็นที่ควรหยิบไปคุยกับผู้บริหารทันที</div>
                            </article>
                        </div>

                        <div class="cc-hero-callout">
	                            <div class="lux-kicker">สัญญาณสรุป</div>
                            <div class="cc-command-strip">
                                <div class="cc-command-item" style="--item-tone: var(--tone-focus)">
                                    <strong id="heroSignalPortfolio">สถานะโครงการ</strong>
                                    <span>กำลังดำเนินการ / วางแผน / เกินกำหนด / เสร็จสิ้น</span>
                                </div>
                                <div class="cc-command-item" style="--item-tone: var(--tone-warning)">
                                    <strong id="heroSignalResource">ทรัพยากร</strong>
                                    <span>งบประมาณรวมและ FTE ที่ใช้ใน lens ปัจจุบัน</span>
                                </div>
                                <div class="cc-command-item" style="--item-tone: var(--tone-critical)">
                                    <strong id="heroSignalRisk">เรื่องต้องตาม</strong>
                                    <span>IRB, วันสิ้นสุด, progress และ output ที่ยังขาด</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <nav class="lux-quick-nav">
                <a href="#section-filter">ตัวกรอง</a>
                <a href="#section-performance">ภาพรวมผลงาน</a>
                <a href="#section-capacity">งบและกำลังคน</a>
                <a href="#section-matrix">แผนที่โครงการ</a>
                <a href="#section-delivery">ส่งมอบและความเสี่ยง</a>
            </nav>

            <section id="section-filter" class="cc-section-panel lux-reveal">
                <div class="cc-section-head">
                    <div class="cc-section-copy">
	                        <div class="lux-kicker">ตัวกรองข้อมูล</div>
	                        <h2 class="lux-title">เลือกมุมมองที่ต้องการดู</h2>
                        <p class="lux-body mt-3 text-sm">
                            ฟิลเตอร์ด้านล่างจะคุม KPI, กราฟ, รายการเฝ้าระวัง และตารางประกอบพร้อมกัน
                            เพื่อให้เวลา present กับลูกค้า ข้อมูลทุกบล็อกยังเล่าเรื่องเดียวกันตลอดหน้า
                        </p>
                    </div>
                    <div class="cc-note-card cc-tone-focus">
	                        <div class="lux-kicker">ข้อมูลที่กำลังแสดง</div>
	                        <div id="ccFilterCount" class="cc-display mt-3 text-2xl text-slate-900">0 รายการ</div>
                        <div class="lux-body mt-2 text-sm">จำนวนรายการรวมใน lens ปัจจุบัน</div>
                        <button id="ccResetFilters" type="button" class="cc-pill mt-4">ล้างตัวกรอง</button>
                    </div>
                </div>

                <div class="cc-filter-grid">
                    <div>
	                        <label class="cc-label" for="ccSearch">ค้นหา</label>
                        <input id="ccSearch" type="text" class="cc-input" placeholder="โครงการ, output, back office, PI">
                    </div>
                    <div>
	                        <label class="cc-label" for="ccYear">ปี</label>
                        <select id="ccYear" class="cc-select">
                            <option value="">ทุกปี</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
	                        <label class="cc-label" for="ccProjectType">ประเภทโครงการ</label>
                        <select id="ccProjectType" class="cc-select">
                            <option value="">ทุกประเภท</option>
                            @foreach ($projectTypeOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
	                        <label class="cc-label" for="ccFundingType">ประเภททุน</label>
                        <select id="ccFundingType" class="cc-select">
                            <option value="">ทุกประเภททุน</option>
                            @foreach ($fundingTypeOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
	                        <label class="cc-label" for="ccProjectStatus">สถานะโครงการ</label>
                        <select id="ccProjectStatus" class="cc-select">
                            <option value="">ทุกสถานะ</option>
                            @foreach ($projectStatusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section id="section-performance" class="cc-section-panel lux-reveal">
                <div class="cc-section-head">
                    <div class="cc-section-copy">
	                        <div class="lux-kicker">ส่วนที่ 1</div>
	                        <h2 class="lux-title">ภาพรวมผลงาน</h2>
                        <p class="lux-body mt-3 text-sm">
                            ส่วนนี้ตั้งใจให้ใช้เปิดประชุมได้เลย ว่าพอร์ตวิจัยตอนนี้มีขนาดเท่าไร,
                            จังหวะของผลงานและรายงานความก้าวหน้ากำลังเร่งหรือชะลอ,
                            และสถานะรวมเริ่มเบี่ยงจากแผนหรือยัง
                        </p>
                    </div>
                    <div class="cc-note-card cc-tone-neutral">
                        <div class="cc-quote">
                            <div class="cc-quote-mark">i</div>
                            <div>
	                                <div class="lux-kicker">อ่านอย่างไร</div>
                                <div class="lux-body mt-2 text-sm">
                                    เปิดด้วย KPI สี่ใบก่อน แล้วค่อยชี้ไปที่ trend radar เพื่อพาผู้ฟังเข้าใจว่า
                                    การเปลี่ยนแปลงของ portfolio มาจากงานส่วนไหน
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cc-kpi-grid lux-grid-stagger">
                    <article class="cc-kpi cc-tone-focus">
	                        <div class="lux-kicker">โครงการในมุมมองนี้</div>
                        <div id="kpiProjects" class="cc-display cc-kpi__value">0</div>
                        <div id="kpiProjectsMeta" class="cc-kpi__meta">-</div>
                    </article>
                    <article class="cc-kpi cc-tone-healthy">
	                        <div class="lux-kicker">ผลงานภายใน</div>
                        <div id="kpiInternalOutputs" class="cc-display cc-kpi__value">0</div>
                        <div id="kpiInternalOutputsMeta" class="cc-kpi__meta">-</div>
                    </article>
                    <article class="cc-kpi cc-tone-focus">
	                        <div class="lux-kicker">ผลงานภายนอก</div>
                        <div id="kpiExternalOutputs" class="cc-display cc-kpi__value">0</div>
                        <div id="kpiExternalOutputsMeta" class="cc-kpi__meta">-</div>
                    </article>
                    <article class="cc-kpi cc-tone-critical">
	                        <div class="lux-kicker">เรื่องต้องตาม</div>
                        <div id="kpiAlerts" class="cc-display cc-kpi__value">0</div>
                        <div id="kpiAlertsMeta" class="cc-kpi__meta">-</div>
                    </article>
                </div>

                <div class="cc-chart-grid lux-grid-stagger mt-4">
                    <article class="cc-chart-card cc-tone-focus">
                        <div class="cc-chart-head">
                            <div>
	                                <div class="lux-kicker">แนวโน้ม</div>
	                                <h3 class="mt-2">การเคลื่อนไหวรายเดือน</h3>
                                <p class="lux-body mt-2 text-sm">เส้นเวลาเดียวที่รวม project, output, back office และ progress report เพื่อดูโมเมนตัมทั้งระบบ</p>
                            </div>
                            <span class="cc-chip">คลิกเพื่อดูรายการจริง</span>
                        </div>
                        <div class="cc-chart-shell"><canvas id="ccTrendChart"></canvas></div>
                    </article>
                    <article class="cc-chart-card cc-tone-neutral">
                        <div class="cc-chart-head">
                            <div>
	                                <div class="lux-kicker">สถานะพอร์ต</div>
	                                <h3 class="mt-2">สัดส่วนสถานะโครงการ</h3>
	                                <p class="lux-body mt-2 text-sm">ช่วยตอบทันทีว่าพอร์ตตอนนี้หนักไปทางกำลังดำเนินการ วางแผน เกินกำหนด หรือเสร็จสิ้น</p>
                            </div>
                            <span class="cc-chip">คลิกเพื่อดูรายการจริง</span>
                        </div>
                        <div class="cc-chart-shell"><canvas id="ccStatusChart"></canvas></div>
                    </article>
                </div>

                <div class="cc-support-grid cc-support-grid--three lux-grid-stagger mt-4">
                    <article class="cc-note-card cc-tone-focus">
	                        <div class="lux-kicker">สรุปสั้น</div>
	                        <h3 class="mt-2">ประโยคเปิดประชุม</h3>
                        <div class="cc-spotlight-grid mt-4">
                            <div class="cc-spotlight">
	                                <div class="lux-kicker">ภาพรวม</div>
                                <div id="overviewNarrativePrimary" class="cc-spotlight-value">-</div>
                            </div>
                            <div class="cc-spotlight">
	                                <div class="lux-kicker">จุดที่ควรถามต่อ</div>
                                <div id="overviewNarrativeSecondary" class="cc-spotlight-value">-</div>
                            </div>
                        </div>
                    </article>

                    <article class="cc-table-card cc-tone-focus">
	                        <div class="lux-kicker">รายการสำคัญ</div>
	                        <h3 class="mt-2">โครงการที่ควรหยิบมาดูก่อน</h3>
                        <p class="lux-body mt-2 text-sm">โครงการที่น่าหยิบขึ้นมาคุยเชิงลึกก่อน จากน้ำหนักงบ สถานะ และภาระติดตาม</p>
                        <div class="mt-4 overflow-auto">
                            <table class="cc-mini-table">
                                <thead>
                                    <tr>
	                                        <th>โครงการ</th>
	                                        <th>สถานะ</th>
	                                        <th>การส่งมอบ</th>
                                    </tr>
                                </thead>
                                <tbody id="ccTopProjectsBody"></tbody>
                            </table>
                        </div>
                    </article>

                    <article class="cc-note-card cc-tone-neutral">
	                        <div class="lux-kicker">ข้อสังเกต</div>
	                        <h3 class="mt-2">สรุปที่ระบบอ่านได้</h3>
                        <p class="lux-body mt-2 text-sm">ข้อความที่ควรใช้ประกอบการพูดในห้องประชุม มากกว่าการปล่อยให้คนอ่านกราฟเอง</p>
                        <div id="ccInsightNotes" class="cc-note-list mt-4"></div>
                    </article>
                </div>
            </section>

            <section id="section-capacity" class="cc-section-panel lux-reveal">
                <div class="cc-section-head">
                    <div class="cc-section-copy">
                        <div class="lux-kicker">Section 2</div>
                        <h2 class="lux-title">Capacity + Funding</h2>
                        <p class="lux-body mt-3 text-sm">
                            เมื่อลูกค้าถามว่าทรัพยากรลงอยู่ตรงไหน ส่วนนี้ควรตอบได้ทันทีว่า
                            งบประมาณกระจุกกับงานประเภทใด, role group ใดถือ FTE สูง,
                            และมีโครงการไหนที่ใช้ทั้งงบและกำลังคนสูงจนควรตามดูใกล้ชิด
                        </p>
                    </div>
                    <div class="cc-note-card cc-tone-warning">
                        <div class="cc-quote">
                            <div class="cc-quote-mark">B</div>
                            <div>
                                <div class="lux-kicker">Boardroom Angle</div>
                                <div class="lux-body mt-2 text-sm">
                                    ถ้าอยากตอบคำถามเรื่อง resource allocation ให้เริ่มจาก funding stack,
                                    จากนั้นชี้ FTE by role group เพื่อเชื่อม “เงิน” กับ “คน” ในภาพเดียวกัน
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cc-kpi-grid lux-grid-stagger">
                    <article class="cc-kpi cc-tone-warning">
                        <div class="lux-kicker">งบในมุมมองนี้</div>
                        <div id="kpiBudget" class="cc-display cc-kpi__value">0</div>
                        <div id="kpiBudgetMeta" class="cc-kpi__meta">-</div>
                    </article>
                    <article class="cc-kpi cc-tone-healthy">
                        <div class="lux-kicker">FTE รวม</div>
                        <div id="kpiFte" class="cc-display cc-kpi__value">0</div>
                        <div id="kpiFteMeta" class="cc-kpi__meta">-</div>
                    </article>
                    <article class="cc-kpi cc-tone-neutral">
                        <div class="lux-kicker">ชั่วโมงงานสนับสนุน</div>
                        <div id="kpiBackOffice" class="cc-display cc-kpi__value">0</div>
                        <div id="kpiBackOfficeMeta" class="cc-kpi__meta">-</div>
                    </article>
                    <article class="cc-kpi cc-tone-focus">
                        <div class="lux-kicker">คุณภาพผลงาน</div>
                        <div id="kpiQuality" class="cc-display cc-kpi__value">0</div>
                        <div id="kpiQualityMeta" class="cc-kpi__meta">-</div>
                    </article>
                </div>

                <div class="cc-chart-grid lux-grid-stagger mt-4">
                    <article class="cc-chart-card cc-tone-warning">
                        <div class="cc-chart-head">
                            <div>
                                <div class="lux-kicker">Funding</div>
                                <h3 class="mt-2">Funding Stack</h3>
                                <p class="lux-body mt-2 text-sm">ใช้ตอบเรื่อง budget distribution ว่าพอร์ตงานวันนี้พึ่งพาทุนประเภทใดมากที่สุด</p>
                            </div>
                            <span class="cc-chip">คลิกเพื่อดูรายการจริง</span>
                        </div>
                        <div class="cc-chart-shell"><canvas id="ccFundingChart"></canvas></div>
                    </article>
                    <article class="cc-chart-card cc-tone-healthy">
                        <div class="cc-chart-head">
                            <div>
                                <div class="lux-kicker">Capacity</div>
                                <h3 class="mt-2">FTE by Role Group</h3>
                                <p class="lux-body mt-2 text-sm">ดู workload balance และจุดที่ FTE ไปรวมตัวอยู่กับ role group เดียวมากเกินไป</p>
                            </div>
                            <span class="cc-chip">คลิกเพื่อเปิดรายชื่อจริง</span>
                        </div>
                        <div class="cc-chart-shell"><canvas id="ccFteChart"></canvas></div>
                    </article>
                </div>

                <div class="cc-support-grid lux-grid-stagger mt-4">
                    <article class="cc-note-card cc-tone-warning">
                        <div class="lux-kicker">สรุปสั้น</div>
                        <h3 class="mt-2">งบและกำลังคน</h3>
                        <div class="cc-spotlight-grid mt-4">
                            <div class="cc-spotlight">
                                <div class="lux-kicker">งบประมาณ</div>
                                <div id="financeNarrativePrimary" class="cc-spotlight-value">-</div>
                            </div>
                            <div class="cc-spotlight">
                                <div class="lux-kicker">ภาระงาน</div>
                                <div id="financeNarrativeSecondary" class="cc-spotlight-value">-</div>
                            </div>
                        </div>
                    </article>

                    <article class="cc-table-card cc-tone-warning">
                        <div class="lux-kicker">Watchlist</div>
                        <h3 class="mt-2">Finance + FTE Watch</h3>
                        <p class="lux-body mt-2 text-sm">มุมมองรวมสำหรับโครงการที่มีทั้ง budget สูง และใช้กำลังคนสูงในเวลาเดียวกัน</p>
                        <div class="mt-4 overflow-auto">
                            <table class="cc-mini-table">
                                <thead>
                                    <tr>
                                        <th>โครงการ</th>
                                        <th>งบ</th>
                                        <th>FTE</th>
                                    </tr>
                                </thead>
                                <tbody id="ccFinanceFteBody"></tbody>
                            </table>
                        </div>
                    </article>

                    <article class="cc-table-card cc-tone-neutral">
                        <div class="lux-kicker">Operations</div>
                        <h3 class="mt-2">Back Office Load</h3>
                        <p class="lux-body mt-2 text-sm">งานสนับสนุนที่กินชั่วโมงสูงหรือยังไม่เชื่อมกับ output ควรถูกเห็นในส่วนนี้ ไม่ใช่หลุดไปท้ายหน้า</p>
                        <div class="mt-4 overflow-auto">
                            <table class="cc-mini-table">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Owner</th>
                                        <th>Load</th>
                                    </tr>
                                </thead>
                                <tbody id="ccBackOfficeBody"></tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </section>

            <section id="section-matrix" class="cc-section-panel cc-section-panel--flush lux-reveal">
                <div class="cc-intel-header">
                    <div class="cc-section-copy">
	                        <div class="lux-kicker">ส่วนที่ 3</div>
	                        <h2 class="lux-title">แผนที่โครงการและภาระงาน</h2>
                        <p class="lux-body mt-3 text-sm">
	                            แต่ละจุดคือ 1 โครงการ แกนนอนคือขนาดงบ แกนตั้งคือ FTE ที่ใช้ สีบอกสถานะโครงการ
	                            ใช้ดูว่าโครงการใดใช้งบหรือคนสูง และมีความเสี่ยงค้างส่งหรือไม่
                        </p>
                    </div>
                    <div class="cc-intel-grid">
                        <div class="cc-lane">
	                            <div class="lux-kicker">งบ/ผลงานสูง</div>
                            <strong id="matrixHighValue">0</strong>
                            <span>งบหรือ output สูงใน lens นี้</span>
                        </div>
                        <div class="cc-lane">
	                            <div class="lux-kicker">ภาระสูง</div>
                            <strong id="matrixPressure">0</strong>
                            <span>FTE หรือชั่วโมงสูง</span>
                        </div>
                        <div class="cc-lane">
	                            <div class="lux-kicker">ยังขาดส่งมอบ</div>
                            <strong id="matrixGap">0</strong>
                            <span>กำลังดำเนินการแต่ยังไม่มี progress/output</span>
                        </div>
                    </div>
                </div>

                <div class="cc-matrix-grid">
	                    <article>
	                        <div class="cc-map-help">
	                            <div><strong>วิธีอ่าน:</strong> จุดที่อยู่ขวา = งบสูง, จุดที่อยู่บน = ใช้ FTE สูง, จุดสีแดง = เกินกำหนด</div>
	                            <div>คลิกจุดเพื่อเปิดรายละเอียดโครงการ ขนาดจุดใหญ่ขึ้นเมื่อมี output หรือ task risk มากขึ้น</div>
	                        </div>
	                        <div class="cc-map" id="ccPortfolioMap">
	                            <span class="cc-map-axis cc-map-axis--y">FTE สูง</span>
	                            <span class="cc-map-axis cc-map-axis--x">งบสูง</span>
	                        </div>
	                        <div class="cc-map-legend">
	                            <span class="cc-semantic-badge" data-tone="healthy">เสร็จสิ้น</span>
	                            <span class="cc-semantic-badge" data-tone="focus">กำลังดำเนินการ</span>
	                            <span class="cc-semantic-badge" data-tone="warning">วางแผน</span>
	                            <span class="cc-semantic-badge" data-tone="critical">เกินกำหนด</span>
	                        </div>
                    </article>

                    <aside class="space-y-4">
                        <article class="cc-note-card cc-tone-focus">
	                            <div class="lux-kicker">วงจรงาน</div>
	                            <h3 class="mt-2">สถานะการเดินงาน</h3>
                            <div id="ccLifecycleGrid" class="cc-intel-grid mt-4"></div>
                        </article>

                        <article class="cc-note-card cc-tone-warning">
	                            <div class="lux-kicker">ภาระคน</div>
	                            <h3 class="mt-2">คนที่ถือภาระสูง</h3>
                            <div id="ccPeoplePressure" class="cc-pressure-grid mt-4"></div>
                        </article>
                    </aside>
                </div>
            </section>

            <section id="section-delivery" class="cc-section-panel lux-reveal">
                <div class="cc-section-head">
                    <div class="cc-section-copy">
                        <div class="lux-kicker">Section 4</div>
                        <h2 class="lux-title">การส่งมอบและความเสี่ยง</h2>
                        <p class="lux-body mt-3 text-sm">
                            ส่วนนี้โฟกัสเฉพาะผลงานวิจัย ความคืบหน้า และสัญญาณค้างส่ง
                            เพื่อแยกว่าโครงการไหนกำลังส่งมอบจริง และโครงการไหนต้องถูกตามก่อนกระทบแผน
                        </p>
                    </div>
                    <div class="cc-note-card cc-tone-critical">
                        <div class="cc-quote">
                            <div class="cc-quote-mark">!</div>
                            <div>
                                <div class="lux-kicker">Decision Cue</div>
                                <div class="lux-body mt-2 text-sm">
                                    อ่าน output coverage คู่กับ alert center เสมอ เพราะ output สูงแต่ progress หรือ IRB หลุด
                                    ยังเป็นภาระบริหารที่ต้องเคลียร์ก่อนปิดรอบรายงาน
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cc-delivery-grid lux-grid-stagger">
                    <div class="cc-delivery-stack">
                        <article class="cc-chart-card cc-tone-focus">
                            <div class="cc-chart-head">
                                <div>
                                    <div class="lux-kicker">ผลงาน</div>
                                    <h3 class="mt-2">ภาพรวมผลงาน</h3>
                                    <p class="lux-body mt-2 text-sm">ช่วยดูว่าผลงานหนักไปทาง internal หรือ external และประเภทใดมีน้ำหนักมากที่สุด</p>
                                </div>
                                <span class="cc-chip">คลิกเพื่อดูรายการจริง</span>
                            </div>
                            <div class="cc-chart-shell"><canvas id="ccOutputMixChart"></canvas></div>
                        </article>

                        <article class="cc-note-card cc-tone-focus">
                            <div class="lux-kicker">สรุปสั้น</div>
                            <h3 class="mt-2">การส่งมอบ</h3>
                            <div class="cc-spotlight-grid mt-4">
                                <div class="cc-spotlight">
                                    <div class="lux-kicker">ผลงาน</div>
                                    <div id="outputNarrativePrimary" class="cc-spotlight-value">-</div>
                                </div>
                                <div class="cc-spotlight">
                                    <div class="lux-kicker">Risk Angle</div>
                                    <div id="outputNarrativeSecondary" class="cc-spotlight-value">-</div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="cc-delivery-stack">
                        <article class="cc-table-card cc-tone-critical">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="lux-kicker">Risk Center</div>
                                    <h3 class="mt-2">Alert Center</h3>
                                    <p class="lux-body mt-2 text-sm">มุมที่ผู้บริหารควรมองเห็นก่อน หากระบบเริ่มมีภาระค้างหรือสัญญาณคุณภาพข้อมูลตกหล่น</p>
                                </div>
                                <span id="ccAlertCountBadge" class="cc-chip">0 เรื่อง</span>
                            </div>
                            <div id="ccAlertsGrid" class="mt-4 grid grid-cols-1 gap-3"></div>
                        </article>

                        <article class="cc-table-card cc-tone-warning">
                            <div class="lux-kicker">ช่องว่างการส่งมอบ</div>
                            <h3 class="mt-2">รายการที่ควรตามต่อ</h3>
                            <p class="lux-body mt-2 text-sm">รายการที่ควรตามต่อ เพราะมีช่องว่างระหว่างการดำเนินงาน รายงานความก้าวหน้า และผลงานที่ถูกบันทึก</p>
                            <div class="mt-4 overflow-auto">
                                <table class="cc-mini-table">
                                    <thead>
                                        <tr>
                                            <th>โครงการ / ผลงาน</th>
                                            <th>สัญญาณ</th>
                                            <th>สิ่งที่ต้องดู</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ccOutputGapBody"></tbody>
                                </table>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>

        <div id="ccModal" class="fixed inset-0 z-[80] hidden">
            <div id="ccModalOverlay" class="cc-modal-backdrop absolute inset-0"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="cc-modal-card w-full max-w-5xl overflow-hidden rounded-[28px] border border-white/40 bg-white shadow-2xl">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                        <div>
                            <h3 id="ccModalTitle" class="text-lg font-bold text-slate-900">รายละเอียด</h3>
                            <p id="ccModalMeta" class="mt-1 text-sm text-slate-500">0 รายการ</p>
                        </div>
                        <button id="ccModalClose" type="button" class="cc-pill">ปิด</button>
                    </div>
                    <div id="ccModalBody" class="max-h-[calc(84vh-88px)] overflow-auto p-5"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (() => {
            'use strict';

            const raw = {
                projects: @json($projectsData, JSON_UNESCAPED_UNICODE),
                internalOutputs: @json($internalOutputsData, JSON_UNESCAPED_UNICODE),
                externalOutputs: @json($externalOutputsData, JSON_UNESCAPED_UNICODE),
                fte: @json($fteData, JSON_UNESCAPED_UNICODE),
                backOffice: @json($backOfficeData, JSON_UNESCAPED_UNICODE),
                progressReports: @json($progressReportsData, JSON_UNESCAPED_UNICODE),
            };

            const todayIso = @json(now()->toDateString());
            const today = new Date(`${todayIso}T00:00:00`);
            const projectById = new Map(raw.projects.map((project) => [Number(project.id), project]));

            const elements = {
                search: document.getElementById('ccSearch'),
                year: document.getElementById('ccYear'),
                projectType: document.getElementById('ccProjectType'),
                fundingType: document.getElementById('ccFundingType'),
                projectStatus: document.getElementById('ccProjectStatus'),
                reset: document.getElementById('ccResetFilters'),
                filterCount: document.getElementById('ccFilterCount'),
                kpiProjects: document.getElementById('kpiProjects'),
                kpiProjectsMeta: document.getElementById('kpiProjectsMeta'),
                kpiBudget: document.getElementById('kpiBudget'),
                kpiBudgetMeta: document.getElementById('kpiBudgetMeta'),
                kpiInternalOutputs: document.getElementById('kpiInternalOutputs'),
                kpiInternalOutputsMeta: document.getElementById('kpiInternalOutputsMeta'),
                kpiExternalOutputs: document.getElementById('kpiExternalOutputs'),
                kpiExternalOutputsMeta: document.getElementById('kpiExternalOutputsMeta'),
                kpiBackOffice: document.getElementById('kpiBackOffice'),
                kpiBackOfficeMeta: document.getElementById('kpiBackOfficeMeta'),
                kpiFte: document.getElementById('kpiFte'),
                kpiFteMeta: document.getElementById('kpiFteMeta'),
                kpiQuality: document.getElementById('kpiQuality'),
                kpiQualityMeta: document.getElementById('kpiQualityMeta'),
                kpiAlerts: document.getElementById('kpiAlerts'),
                kpiAlertsMeta: document.getElementById('kpiAlertsMeta'),
                overviewNarrativePrimary: document.getElementById('overviewNarrativePrimary'),
                overviewNarrativeSecondary: document.getElementById('overviewNarrativeSecondary'),
                financeNarrativePrimary: document.getElementById('financeNarrativePrimary'),
                financeNarrativeSecondary: document.getElementById('financeNarrativeSecondary'),
                outputNarrativePrimary: document.getElementById('outputNarrativePrimary'),
                outputNarrativeSecondary: document.getElementById('outputNarrativeSecondary'),
                heroSignalPortfolio: document.getElementById('heroSignalPortfolio'),
                heroSignalResource: document.getElementById('heroSignalResource'),
                heroSignalRisk: document.getElementById('heroSignalRisk'),
                portfolioMap: document.getElementById('ccPortfolioMap'),
                lifecycleGrid: document.getElementById('ccLifecycleGrid'),
                peoplePressure: document.getElementById('ccPeoplePressure'),
                matrixHighValue: document.getElementById('matrixHighValue'),
                matrixPressure: document.getElementById('matrixPressure'),
                matrixGap: document.getElementById('matrixGap'),
                alertsGrid: document.getElementById('ccAlertsGrid'),
                alertCountBadge: document.getElementById('ccAlertCountBadge'),
                insightNotes: document.getElementById('ccInsightNotes'),
                topProjectsBody: document.getElementById('ccTopProjectsBody'),
                backOfficeBody: document.getElementById('ccBackOfficeBody'),
                outputGapBody: document.getElementById('ccOutputGapBody'),
                financeFteBody: document.getElementById('ccFinanceFteBody'),
                modal: document.getElementById('ccModal'),
                modalOverlay: document.getElementById('ccModalOverlay'),
                modalClose: document.getElementById('ccModalClose'),
                modalTitle: document.getElementById('ccModalTitle'),
                modalMeta: document.getElementById('ccModalMeta'),
                modalBody: document.getElementById('ccModalBody'),
            };

            const charts = {
                trend: null,
                status: null,
                outputMix: null,
                funding: null,
                fte: null,
            };

            const drilldowns = {
                trend: [],
                status: [],
                outputMix: [],
                funding: [],
                fte: [],
            };

            const numberFormat = new Intl.NumberFormat('th-TH');
            const moneyFormat = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 2, minimumFractionDigits: 0 });
            const preciseMoneyFormat = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 2, minimumFractionDigits: 2 });

            const palette = {
                coral: '#c2410c',
                sea: '#0f766e',
                ink: '#334155',
                gold: '#a16207',
                forest: '#166534',
                rose: '#b91c1c',
                slate: '#64748b',
                blue: '#1d4ed8',
                pink: '#be185d',
                purple: '#6d28d9',
            };

            const statusColor = (status) => {
                if (status === 'เสร็จสิ้น') return '#15803d';
                if (status === 'เกินกำหนด') return '#dc2626';
                if (status === 'วางแผน') return '#d97706';
                return '#0f766e';
            };

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const normalizeText = (value) => String(value ?? '').trim().toLowerCase();
            const asNumber = (value) => {
                const num = Number(value);
                return Number.isFinite(num) ? num : 0;
            };
            const formatMoney = (value) => `${moneyFormat.format(asNumber(value))} บาท`;
            const formatCompactMoney = (value) => `${moneyFormat.format(asNumber(value))}`;
            const parseDate = (value) => {
                if (!value) return null;
                const date = new Date(String(value).length <= 10 ? `${value}T00:00:00` : value);
                return Number.isNaN(date.getTime()) ? null : date;
            };
            const daysBetween = (future, base) => Math.round((future.getTime() - base.getTime()) / 86400000);
            const monthKey = (value) => {
                const date = parseDate(value);
                if (!date) return null;
                return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
            };
            const monthLabel = (key) => {
                const [year, month] = String(key).split('-');
                return `${month}/${year}`;
            };

            const getFilterState = () => ({
                search: normalizeText(elements.search.value),
                year: elements.year.value ? Number(elements.year.value) : null,
                projectType: elements.projectType.value,
                fundingType: elements.fundingType.value,
                projectStatus: elements.projectStatus.value,
            });

            const matchesProjectLens = (project, state) => {
                if (!project) return false;
                if (state.year && Number(project.start_year) !== state.year) return false;
                if (state.projectType && project.project_type !== state.projectType) return false;
                if (state.fundingType && project.funding_type !== state.fundingType) return false;
                if (state.projectStatus && project.derived_status !== state.projectStatus) return false;
                return true;
            };

            const matchesSearch = (row, terms) => terms === '' || normalizeText(row).includes(terms);

            const getFilteredData = () => {
                const state = getFilterState();

                const projects = raw.projects.filter((project) => {
                    if (!matchesProjectLens(project, state)) return false;
                    const haystack = [
                        project.code, project.name, project.pi_name, project.project_type,
                        project.funding_type, project.funding_source, project.department, project.unit_name,
                    ].join(' ');
                    return matchesSearch(haystack, state.search);
                });

                const projectIds = new Set(projects.map((project) => Number(project.id)));

                const internalOutputs = raw.internalOutputs.filter((output) => {
                    const relatedProject = projectById.get(Number(output.project_id));
                    if ((state.projectType || state.fundingType || state.projectStatus) && (!relatedProject || !projectIds.has(Number(output.project_id)))) {
                        return false;
                    }
                    if (state.year && Number(output.year) !== state.year) return false;
                    const haystack = [output.code, output.title, output.type, output.project_code, output.project_name, output.journal].join(' ');
                    return matchesSearch(haystack, state.search);
                });

                const externalOutputs = raw.externalOutputs.filter((output) => {
                    if (state.year && Number(output.publish_year) !== state.year) return false;
                    const haystack = [output.code, output.title, output.type, output.job_category, output.journal, output.note].join(' ');
                    return matchesSearch(haystack, state.search);
                });

                const backOffice = raw.backOffice.filter((task) => {
                    if (state.year && Number(task.year) !== state.year) return false;
                    const haystack = [task.code, task.title, task.description, task.purpose, task.creator_name].join(' ');
                    return matchesSearch(haystack, state.search);
                });

                const fte = raw.fte.filter((row) => {
                    const relatedProject = projectById.get(Number(row.project_id));
                    if ((state.projectType || state.fundingType || state.projectStatus) && (!relatedProject || !projectIds.has(Number(row.project_id)))) {
                        return false;
                    }
                    if (state.year && Number(row.year) !== state.year) return false;
                    const haystack = [
                        row.project_code, row.project_name, row.name, row.role_group,
                        row.job_detail, row.task_detail, row.project_type, row.funding_type,
                    ].join(' ');
                    return matchesSearch(haystack, state.search);
                });

                const progressReports = raw.progressReports.filter((report) => {
                    const relatedProject = projectById.get(Number(report.project_id));
                    if ((state.projectType || state.fundingType || state.projectStatus) && (!relatedProject || !projectIds.has(Number(report.project_id)))) {
                        return false;
                    }
                    if (state.year && Number(report.year) !== state.year) return false;
                    const haystack = [report.project_code, report.project_name, report.title].join(' ');
                    return matchesSearch(haystack, state.search);
                });

                return { state, projects, internalOutputs, externalOutputs, fte, backOffice, progressReports };
            };

            const buildAlertGroups = (filtered) => {
                const groups = [];

                const expiredIrb = filtered.projects
                    .filter((project) => project.latest_irb?.exp_date && parseDate(project.latest_irb.exp_date) < today)
                    .map((project) => ({
                        title: project.name,
                        meta: `${project.code} | IRB ${project.latest_irb?.code || '-'}`,
                        detail: `หมดอายุ ${project.latest_irb?.exp_date || '-'}`,
                        url: project.url,
                    }));

                const endingSoon = filtered.projects
                    .filter((project) => {
                        if (project.derived_status === 'เสร็จสิ้น' || !project.end_date) return false;
                        const end = parseDate(project.end_date);
                        if (!end) return false;
                        const days = daysBetween(end, today);
                        return days >= 0 && days <= 45;
                    })
                    .map((project) => ({
                        title: project.name,
                        meta: `${project.code} | PI ${project.pi_name}`,
                        detail: `ครบกำหนด ${project.end_date}`,
                        url: project.url,
                    }));

                const noProgress = filtered.projects
                    .filter((project) => {
                        if (project.derived_status !== 'กำลังดำเนินการ' || Number(project.progress_reports_count) > 0) return false;
                        const start = parseDate(project.start_date);
                        return start && daysBetween(today, start) >= 90;
                    })
                    .map((project) => ({
                        title: project.name,
                        meta: `${project.code} | ${project.project_type}`,
                        detail: 'ยังไม่มี progress report',
                        url: project.url,
                    }));

                const noOutput = filtered.projects
                    .filter((project) => {
                        if (project.derived_status !== 'กำลังดำเนินการ' || Number(project.outputs_count) > 0) return false;
                        const start = parseDate(project.start_date);
                        return start && daysBetween(today, start) >= 180;
                    })
                    .map((project) => ({
                        title: project.name,
                        meta: `${project.code} | ${project.pi_name}`,
                        detail: 'ยังไม่มี output เชื่อมกับโครงการ',
                        url: project.url,
                    }));

                const backOfficeNoOutput = filtered.backOffice
                    .filter((task) => task.end_date && parseDate(task.end_date) < today && Number(task.outputs_count) === 0)
                    .map((task) => ({
                        title: task.title,
                        meta: `${task.code} | ${task.creator_name}`,
                        detail: `สิ้นสุด ${task.end_date} | ยังไม่มี output`,
                        url: task.url,
                    }));

                const externalMissingMeta = filtered.externalOutputs
                    .filter((output) => !output.publish_date || normalizeText(output.note) === '')
                    .map((output) => ({
                        title: output.title,
                        meta: `${output.code} | ${output.type}`,
                        detail: !output.publish_date ? 'ยังไม่มีวันที่เผยแพร่' : 'ยังไม่มี note / รายละเอียด',
                        url: output.url,
                    }));

                groups.push({ key: 'expiredIrb', title: 'IRB หมดอายุแล้ว', severity: 'critical', rows: expiredIrb });
                groups.push({ key: 'endingSoon', title: 'โครงการใกล้ครบกำหนด', severity: 'high', rows: endingSoon });
                groups.push({ key: 'noProgress', title: 'โครงการไม่มี progress report', severity: 'medium', rows: noProgress });
                groups.push({ key: 'noOutput', title: 'โครงการ active แต่ยังไม่มี output', severity: 'medium', rows: noOutput });
                groups.push({ key: 'backofficeNoOutput', title: 'Back Office ยังไม่ผูก output', severity: 'medium', rows: backOfficeNoOutput });
                groups.push({ key: 'externalMissingMeta', title: 'External output metadata ไม่ครบ', severity: 'low', rows: externalMissingMeta });

                return groups;
            };

            const openModal = (title, rows) => {
                const list = Array.isArray(rows) ? rows : [];
                elements.modalTitle.textContent = title;
                elements.modalMeta.textContent = `${numberFormat.format(list.length)} รายการ`;

                if (!list.length) {
                    elements.modalBody.innerHTML = '<div class="cc-empty">ไม่พบข้อมูลในช่วงนี้</div>';
                } else {
                    elements.modalBody.innerHTML = `
                        <div class="grid grid-cols-1 gap-3">
                            ${list.map((row) => `
                                <article class="rounded-3xl border border-slate-200 bg-slate-50/80 p-4">
                                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <div class="text-base font-bold text-slate-900">${escapeHtml(row.title || '-')}</div>
                                            <div class="mt-1 text-sm text-slate-500">${escapeHtml(row.meta || '-')}</div>
                                            <div class="mt-2 text-sm leading-6 text-slate-700">${escapeHtml(row.detail || '-')}</div>
                                        </div>
                                        ${row.url ? `<a href="${escapeHtml(row.url)}" class="cc-pill whitespace-nowrap">เปิดรายละเอียด</a>` : ''}
                                    </div>
                                </article>
                            `).join('')}
                        </div>
                    `;
                }

                elements.modal.classList.remove('hidden');
            };

            const closeModal = () => elements.modal.classList.add('hidden');

            const bindChartClick = (chart, key) => {
                if (!chart?.canvas || chart.canvas.dataset.boundClick === 'true') return;
                chart.canvas.dataset.boundClick = 'true';
                chart.canvas.addEventListener('click', (event) => {
                    const point = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true)?.[0];
                    if (!point) return;
                    const target = drilldowns[key]?.[point.datasetIndex]?.[point.index];
                    if (!target) return;
                    openModal(target.title, target.rows);
                });
            };

            const ensureChart = (name, canvasId, config) => {
                if (!charts[name]) {
                    charts[name] = new Chart(document.getElementById(canvasId), config);
                    bindChartClick(charts[name], name);
                }
                return charts[name];
            };

            const renderTrendChart = (filtered) => {
                const allDates = []
                    .concat(filtered.projects.map((row) => row.created_at))
                    .concat(filtered.internalOutputs.map((row) => row.created_at))
                    .concat(filtered.externalOutputs.map((row) => row.created_at))
                    .concat(filtered.backOffice.map((row) => row.created_at))
                    .concat(filtered.progressReports.map((row) => row.submit_date || row.created_at))
                    .map(monthKey)
                    .filter(Boolean);

                let labels = [...new Set(allDates)].sort();
                if (!labels.length) {
                    for (let i = 11; i >= 0; i -= 1) {
                        const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
                        labels.push(`${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`);
                    }
                }
                if (labels.length > 12) labels = labels.slice(-12);

                const makeSeries = (sourceRows, dateField, seriesTitle) => {
                    const rowsByMonth = labels.map((label) => {
                        const rows = sourceRows.filter((row) => monthKey(row[dateField]) === label).map((row) => ({
                            title: row.title || row.name || row.subject || '-',
                            meta: row.code || row.project_code || row.meta || row.type || row.source || '-',
                            detail: row.project_name || row.detail || row.actor || '-',
                            url: row.url || null,
                        }));
                        return {
                            title: `${seriesTitle} | ${monthLabel(label)}`,
                            rows,
                        };
                    });

                    return {
                        counts: rowsByMonth.map((entry) => entry.rows.length),
                        drilldown: rowsByMonth,
                    };
                };

                const projectSeries = makeSeries(filtered.projects, 'created_at', 'Projects Created');
                const internalSeries = makeSeries(filtered.internalOutputs, 'created_at', 'Internal Outputs');
                const externalSeries = makeSeries(filtered.externalOutputs, 'created_at', 'External Outputs');
                const backOfficeSeries = makeSeries(filtered.backOffice, 'created_at', 'Back Office');
                const progressSeries = makeSeries(filtered.progressReports, 'submit_date', 'Progress Reports');

                drilldowns.trend = [
                    projectSeries.drilldown,
                    internalSeries.drilldown,
                    externalSeries.drilldown,
                    backOfficeSeries.drilldown,
                    progressSeries.drilldown,
                ];

                const chart = ensureChart('trend', 'ccTrendChart', {
                    type: 'line',
                    data: { labels: [], datasets: [] },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'nearest', intersect: true },
                        plugins: { legend: { position: 'bottom' } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { grid: { display: false } },
                        },
                    },
                });

                chart.data.labels = labels.map(monthLabel);
                chart.data.datasets = [
                    { label: 'Projects', data: projectSeries.counts, borderColor: palette.ink, backgroundColor: palette.ink, tension: 0.28, pointRadius: 4 },
                    { label: 'Internal Outputs', data: internalSeries.counts, borderColor: palette.sea, backgroundColor: palette.sea, tension: 0.28, pointRadius: 4 },
                    { label: 'External Outputs', data: externalSeries.counts, borderColor: palette.coral, backgroundColor: palette.coral, tension: 0.28, pointRadius: 4 },
                    { label: 'Back Office', data: backOfficeSeries.counts, borderColor: palette.gold, backgroundColor: palette.gold, tension: 0.28, pointRadius: 4 },
                    { label: 'Progress Reports', data: progressSeries.counts, borderColor: palette.forest, backgroundColor: palette.forest, tension: 0.28, pointRadius: 4 },
                ];
                chart.update();
            };

            const renderStatusChart = (filtered) => {
                const statuses = ['กำลังดำเนินการ', 'วางแผน', 'เกินกำหนด', 'เสร็จสิ้น'];
                const groups = statuses.map((status) => {
                    const rows = filtered.projects
                        .filter((project) => project.derived_status === status)
                        .map((project) => ({
                            title: project.name,
                            meta: `${project.code} | ${project.pi_name}`,
                            detail: `${project.project_type} | งบ ${formatMoney(project.budget)}`,
                            url: project.url,
                        }));
                    return { label: status, rows };
                }).filter((group) => group.rows.length > 0);

                drilldowns.status = [groups.map((group) => ({ title: `สถานะ ${group.label}`, rows: group.rows }))];

                const chart = ensureChart('status', 'ccStatusChart', {
                    type: 'doughnut',
                    data: { labels: [], datasets: [{ data: [], backgroundColor: [] }] },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                    },
                });

                chart.data.labels = groups.map((group) => group.label);
                chart.data.datasets[0].data = groups.map((group) => group.rows.length);
                chart.data.datasets[0].backgroundColor = [palette.sea, palette.gold, palette.rose, palette.forest];
                chart.update();
            };

            const renderOutputMixChart = (filtered) => {
                const combined = []
                    .concat(filtered.internalOutputs.map((row) => ({ ...row, domain: 'Internal Output' })))
                    .concat(filtered.externalOutputs.map((row) => ({ ...row, domain: 'External Output' })));

                const groupedMap = new Map();
                combined.forEach((row) => {
                    const key = row.type || 'ไม่ระบุประเภท';
                    if (!groupedMap.has(key)) groupedMap.set(key, []);
                    groupedMap.get(key).push(row);
                });

                const groups = [...groupedMap.entries()]
                    .map(([label, rows]) => ({
                        label,
                        rows: rows.map((row) => ({
                            title: row.title,
                            meta: `${row.code} | ${row.domain}`,
                            detail: row.project_name ? `${row.project_name} | ปี ${row.year || row.publish_year || '-'}` : `${row.job_category || '-'} | ปี ${row.publish_year || '-'}`,
                            url: row.url,
                        })),
                    }))
                    .sort((a, b) => b.rows.length - a.rows.length)
                    .slice(0, 8);

                drilldowns.outputMix = [groups.map((group) => ({ title: `Output Type ${group.label}`, rows: group.rows }))];

                const chart = ensureChart('outputMix', 'ccOutputMixChart', {
                    type: 'bar',
                    data: { labels: [], datasets: [{ data: [], backgroundColor: [] }] },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { grid: { display: false } },
                        },
                    },
                });

                chart.data.labels = groups.map((group) => group.label);
                chart.data.datasets[0].data = groups.map((group) => group.rows.length);
                chart.data.datasets[0].backgroundColor = [palette.coral, palette.sea, palette.gold, palette.ink, palette.forest, palette.purple, palette.pink, palette.blue];
                chart.update();
            };

            const renderFundingChart = (filtered) => {
                const groups = [...filtered.projects.reduce((map, project) => {
                    const key = project.funding_type || 'ไม่ระบุประเภททุน';
                    if (!map.has(key)) map.set(key, []);
                    map.get(key).push(project);
                    return map;
                }, new Map()).entries()]
                    .map(([label, rows]) => ({
                        label,
                        budget: rows.reduce((sum, row) => sum + asNumber(row.budget), 0),
                        rows: rows.map((row) => ({
                            title: row.name,
                            meta: `${row.code} | ${row.funding_source}`,
                            detail: `งบ ${formatMoney(row.budget)} | ${row.pi_name}`,
                            url: row.url,
                        })),
                    }))
                    .sort((a, b) => b.budget - a.budget)
                    .slice(0, 8);

                drilldowns.funding = [groups.map((group) => ({ title: `Funding ${group.label}`, rows: group.rows }))];

                const chart = ensureChart('funding', 'ccFundingChart', {
                    type: 'bar',
                    data: { labels: [], datasets: [{ data: [], backgroundColor: [] }] },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true },
                            x: { grid: { display: false } },
                        },
                    },
                });

                chart.data.labels = groups.map((group) => group.label);
                chart.data.datasets[0].data = groups.map((group) => Math.round(group.budget));
                chart.data.datasets[0].backgroundColor = [palette.gold, palette.coral, palette.sea, palette.ink, palette.forest, palette.purple, palette.pink, palette.blue];
                chart.update();
            };

            const renderFteChart = (filtered) => {
                const groups = [...filtered.fte.reduce((map, row) => {
                    const key = row.role_group || 'ไม่ระบุกลุ่ม';
                    if (!map.has(key)) map.set(key, []);
                    map.get(key).push(row);
                    return map;
                }, new Map()).entries()]
                    .map(([label, rows]) => ({
                        label,
                        fte: rows.reduce((sum, row) => sum + asNumber(row.fte_value), 0),
                        rows: rows.map((row) => ({
                            title: row.name,
                            meta: `${row.project_code} | ${row.project_name}`,
                            detail: `${row.role_group} | FTE ${preciseMoneyFormat.format(row.fte_value)} | ${preciseMoneyFormat.format(row.hours)} ชั่วโมง`,
                            url: row.url,
                        })),
                    }))
                    .sort((a, b) => b.fte - a.fte)
                    .slice(0, 10);

                drilldowns.fte = [groups.map((group) => ({ title: `FTE Group ${group.label}`, rows: group.rows }))];

                const chart = ensureChart('fte', 'ccFteChart', {
                    type: 'bar',
                    data: { labels: [], datasets: [{ data: [], backgroundColor: [] }] },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true },
                            y: { grid: { display: false } },
                        },
                    },
                });

                chart.data.labels = groups.map((group) => group.label);
                chart.data.datasets[0].data = groups.map((group) => Number(group.fte.toFixed(3)));
                chart.data.datasets[0].backgroundColor = [palette.sea, palette.forest, palette.ink, palette.coral, palette.gold, palette.purple, palette.pink, '#0f766e', palette.blue, '#e11d48'];
                chart.update();
            };

            const topLabel = (rows, field) => {
                const map = new Map();
                rows.forEach((row) => {
                    const key = row[field] || 'ไม่ระบุ';
                    map.set(key, (map.get(key) || 0) + 1);
                });
                return [...map.entries()].sort((a, b) => b[1] - a[1])[0]?.[0] || '-';
            };

            const topEntry = (rows, field) => {
                const map = new Map();
                rows.forEach((row) => {
                    const key = row[field] || 'ไม่ระบุ';
                    map.set(key, (map.get(key) || 0) + 1);
                });
                return [...map.entries()].sort((a, b) => b[1] - a[1])[0] || ['-', 0];
            };

            const topBudgetEntry = (rows, field) => {
                const map = new Map();
                rows.forEach((row) => {
                    const key = row[field] || 'ไม่ระบุ';
                    map.set(key, (map.get(key) || 0) + asNumber(row.budget));
                });
                return [...map.entries()].sort((a, b) => b[1] - a[1])[0] || ['-', 0];
            };

            const renderKpis = (filtered, alerts) => {
                const totalBudget = filtered.projects.reduce((sum, project) => sum + asNumber(project.budget), 0);
                const totalOutput = filtered.internalOutputs.length + filtered.externalOutputs.length;
                const totalBackOfficeHours = filtered.backOffice.reduce((sum, task) => sum + asNumber(task.hours), 0);
                const totalFte = filtered.fte.reduce((sum, row) => sum + asNumber(row.fte_value), 0);
                const totalFteHours = filtered.fte.reduce((sum, row) => sum + asNumber(row.hours), 0);
                const uniqueFtePeople = new Set(filtered.fte.map((row) => normalizeText(row.name)).filter(Boolean)).size;
                const qualityOutputs = filtered.internalOutputs.filter((row) => /^Q[1-4]$/i.test(String(row.quartile || ''))).length
                    + filtered.externalOutputs.filter((row) => /^Q[1-4]$/i.test(String(row.quartile || ''))).length;
                const alertCount = alerts.reduce((sum, group) => sum + group.rows.length, 0);
                const recordCount = filtered.projects.length + totalOutput + filtered.backOffice.length + filtered.progressReports.length + filtered.fte.length;

                elements.filterCount.textContent = `${numberFormat.format(recordCount)} รายการ`;
                elements.kpiProjects.textContent = numberFormat.format(filtered.projects.length);
                elements.kpiProjectsMeta.textContent = `${numberFormat.format(filtered.projects.filter((row) => row.derived_status === 'กำลังดำเนินการ').length)} กำลังดำเนินการ | ${numberFormat.format(filtered.progressReports.length)} progress`;
                elements.kpiBudget.textContent = formatCompactMoney(totalBudget);
                elements.kpiBudgetMeta.textContent = filtered.projects.length ? `เฉลี่ย ${preciseMoneyFormat.format(totalBudget / filtered.projects.length)} บาท / โครงการ` : 'ไม่มีข้อมูลใน lens';
                elements.kpiInternalOutputs.textContent = numberFormat.format(filtered.internalOutputs.length);
                elements.kpiInternalOutputsMeta.textContent = filtered.internalOutputs.length ? `ประเภทหลัก ${topLabel(filtered.internalOutputs, 'type')}` : 'ยังไม่มีผลงานภายใน';
                elements.kpiExternalOutputs.textContent = numberFormat.format(filtered.externalOutputs.length);
                elements.kpiExternalOutputsMeta.textContent = filtered.externalOutputs.length ? `ประเภทหลัก ${topLabel(filtered.externalOutputs, 'type')}` : 'ยังไม่มีผลงานภายนอก';
                elements.kpiBackOffice.textContent = preciseMoneyFormat.format(totalBackOfficeHours);
                elements.kpiBackOfficeMeta.textContent = `${numberFormat.format(filtered.backOffice.length)} งาน | FTE ${preciseMoneyFormat.format(filtered.backOffice.reduce((sum, task) => sum + asNumber(task.fte), 0))}`;
                elements.kpiFte.textContent = preciseMoneyFormat.format(totalFte);
                elements.kpiFteMeta.textContent = `${numberFormat.format(uniqueFtePeople)} คน | ${preciseMoneyFormat.format(totalFteHours)} ชั่วโมง`;
                elements.kpiQuality.textContent = numberFormat.format(qualityOutputs);
                elements.kpiQualityMeta.textContent = `ผลงาน Q1-Q4 | IF รวม ${preciseMoneyFormat.format(filtered.internalOutputs.reduce((sum, row) => sum + asNumber(row.impact_factor), 0) + filtered.externalOutputs.reduce((sum, row) => sum + asNumber(row.impact_factor), 0))}`;
                elements.kpiAlerts.textContent = numberFormat.format(alertCount);
                elements.kpiAlertsMeta.textContent = `${numberFormat.format(alerts.filter((group) => group.rows.length > 0).length)} กลุ่มสัญญาณ`;
            };

            const renderAlerts = (alerts) => {
                const nonEmpty = alerts.filter((group) => group.rows.length > 0);
                const totalRows = nonEmpty.reduce((sum, group) => sum + group.rows.length, 0);
                elements.alertCountBadge.textContent = `${numberFormat.format(totalRows)} เรื่อง`;

                if (!nonEmpty.length) {
                    elements.alertsGrid.innerHTML = '<div class="cc-empty md:col-span-2">ไม่มี alert ตาม lens ปัจจุบัน</div>';
                    return;
                }

                elements.alertsGrid.innerHTML = nonEmpty.map((group) => `
                    <article class="cc-alert-card" data-severity="${escapeHtml(group.severity)}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">${escapeHtml(group.severity)}</div>
                                <h4 class="mt-1 text-base font-bold text-slate-900">${escapeHtml(group.title)}</h4>
                            </div>
                            <div class="cc-display text-2xl font-bold text-slate-900">${numberFormat.format(group.rows.length)}</div>
                        </div>
                        <div class="mt-3 text-sm leading-6 text-slate-600">${escapeHtml(group.rows[0]?.detail || 'ต้องติดตาม')}</div>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <div class="text-xs text-slate-500">${escapeHtml(group.rows[0]?.title || '-')}</div>
                            <button type="button" class="cc-pill" data-open-alert="${escapeHtml(group.key)}">ดูทั้งหมด</button>
                        </div>
                    </article>
                `).join('');

                elements.alertsGrid.querySelectorAll('[data-open-alert]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const group = nonEmpty.find((item) => item.key === button.getAttribute('data-open-alert'));
                        if (group) openModal(group.title, group.rows);
                    });
                });
            };

            const renderInsights = (filtered, alerts) => {
                const notes = [];
                const topType = topLabel(filtered.projects, 'project_type');
                const topFunding = topLabel(filtered.projects, 'funding_type');
                const overdueCount = filtered.projects.filter((project) => project.derived_status === 'เกินกำหนด').length;
                const missingProgress = alerts.find((group) => group.key === 'noProgress')?.rows.length || 0;
                const missingOutput = alerts.find((group) => group.key === 'noOutput')?.rows.length || 0;
                const backOfficeOutputGap = alerts.find((group) => group.key === 'backofficeNoOutput')?.rows.length || 0;
                const totalFte = filtered.fte.reduce((sum, row) => sum + asNumber(row.fte_value), 0);
                const topFteGroup = topLabel(filtered.fte, 'role_group');

                notes.push({
                    title: 'รูปทรงพอร์ต',
                    detail: filtered.projects.length
                        ? `ประเภทโครงการเด่นคือ ${topType} และประเภททุนเด่นคือ ${topFunding}`
                        : 'ยังไม่มีโครงการตาม lens ปัจจุบัน',
                });
                notes.push({
                    title: 'ความเสี่ยงการส่งมอบ',
                    detail: overdueCount || missingProgress
                        ? `มีโครงการเกินกำหนด ${numberFormat.format(overdueCount)} รายการ และไม่มี progress report ${numberFormat.format(missingProgress)} รายการ`
                        : 'ยังไม่พบสัญญาณ delay เด่นจาก lens ปัจจุบัน',
                });
                notes.push({
                    title: 'การผูกผลงาน',
                    detail: missingOutput || backOfficeOutputGap
                        ? `มีโครงการ active ที่ยังไม่มี output ${numberFormat.format(missingOutput)} รายการ และ back office ที่ยังไม่ผูก output ${numberFormat.format(backOfficeOutputGap)} รายการ`
                        : 'การเชื่อม output กับโครงการและ back office อยู่ในเกณฑ์ดี',
                });
                notes.push({
                    title: 'งบและ FTE',
                    detail: filtered.projects.length
                        ? `งบรวม ${formatMoney(filtered.projects.reduce((sum, row) => sum + asNumber(row.budget), 0))} และมี FTE รวม ${preciseMoneyFormat.format(totalFte)} โดยกลุ่มเด่นคือ ${topFteGroup}`
                        : 'ยังไม่มีข้อมูลการเงินและ FTE ใน lens ปัจจุบัน',
                });

                elements.insightNotes.innerHTML = notes.map((note) => `
                    <article class="cc-note-item">
                        <strong>${escapeHtml(note.title)}</strong>
                        <div class="mt-2 text-sm leading-7 text-slate-700">${escapeHtml(note.detail)}</div>
                    </article>
                `).join('');
            };

            const renderNarratives = (filtered, alerts) => {
                const activeCount = filtered.projects.filter((project) => project.derived_status === 'กำลังดำเนินการ').length;
                const overdueCount = filtered.projects.filter((project) => project.derived_status === 'เกินกำหนด').length;
                const [topProjectType, topProjectTypeCount] = topEntry(filtered.projects, 'project_type');
                const [topFundingType, topFundingBudget] = topBudgetEntry(filtered.projects, 'funding_type');
                const [topFteGroup, topFteGroupCount] = topEntry(filtered.fte, 'role_group');
                const totalFte = filtered.fte.reduce((sum, row) => sum + asNumber(row.fte_value), 0);
                const internalCount = filtered.internalOutputs.length;
                const externalCount = filtered.externalOutputs.length;
                const qualityCount = filtered.internalOutputs.filter((row) => /^Q[1-4]$/i.test(String(row.quartile || ''))).length
                    + filtered.externalOutputs.filter((row) => /^Q[1-4]$/i.test(String(row.quartile || ''))).length;
                const alertCount = alerts.reduce((sum, group) => sum + group.rows.length, 0);
                const criticalCount = alerts.find((group) => group.key === 'expiredIrb')?.rows.length || 0;

                elements.overviewNarrativePrimary.textContent = filtered.projects.length
                    ? `กำลังดำเนินการ ${numberFormat.format(activeCount)} จาก ${numberFormat.format(filtered.projects.length)} โครงการ`
                    : 'ยังไม่มีโครงการตาม lens นี้';
                elements.overviewNarrativeSecondary.textContent = filtered.projects.length
                    ? `กลุ่มใหญ่สุดคือ ${topProjectType} (${numberFormat.format(topProjectTypeCount)} โครงการ) | เกินกำหนด ${numberFormat.format(overdueCount)}`
                    : 'ยังไม่มี portfolio signal';
                elements.financeNarrativePrimary.textContent = filtered.projects.length
                    ? `งบก้อนใหญ่สุดอยู่ที่ ${topFundingType} มูลค่า ${formatMoney(topFundingBudget)}`
                    : 'ยังไม่มีข้อมูลงบใน lens นี้';
                elements.financeNarrativeSecondary.textContent = filtered.fte.length
                    ? `${topFteGroup} ถือ FTE สูงสุด ${numberFormat.format(topFteGroupCount)} รายการ | FTE รวม ${preciseMoneyFormat.format(totalFte)}`
                    : 'ยังไม่มีข้อมูล FTE ใน lens นี้';
                elements.outputNarrativePrimary.textContent = internalCount || externalCount
                    ? `Internal ${numberFormat.format(internalCount)} | External ${numberFormat.format(externalCount)} | Q1-Q4 ${numberFormat.format(qualityCount)}`
                    : 'ยังไม่มี output ใน lens นี้';
                elements.outputNarrativeSecondary.textContent = alertCount
                    ? `มี ${numberFormat.format(alertCount)} alert และ IRB หมดอายุ ${numberFormat.format(criticalCount)} รายการ`
                    : 'ยังไม่พบสัญญาณเสี่ยงเด่นใน lens นี้';
                elements.heroSignalPortfolio.textContent = `${numberFormat.format(activeCount)} กำลังดำเนินการ / ${numberFormat.format(filtered.projects.length)} โครงการ`;
                elements.heroSignalResource.textContent = `งบ ${formatCompactMoney(filtered.projects.reduce((sum, row) => sum + asNumber(row.budget), 0))} บาท / FTE ${preciseMoneyFormat.format(totalFte)}`;
                elements.heroSignalRisk.textContent = `${numberFormat.format(alertCount)} เรื่องต้องตาม / ${numberFormat.format(overdueCount)} เกินกำหนด`;
            };

            const renderResearchMatrix = (filtered, alerts) => {
                const fteByProject = filtered.fte.reduce((map, row) => {
                    const key = Number(row.project_id);
                    if (!map.has(key)) map.set(key, { fte: 0, hours: 0 });
                    const bucket = map.get(key);
                    bucket.fte += asNumber(row.fte_value);
                    bucket.hours += asNumber(row.hours);
                    return map;
                }, new Map());

                const enrichedProjects = filtered.projects.map((project) => ({
                    ...project,
                    total_fte: fteByProject.get(Number(project.id))?.fte || 0,
                    total_hours: fteByProject.get(Number(project.id))?.hours || 0,
                }));

                const maxBudget = Math.max(...enrichedProjects.map((project) => asNumber(project.budget)), 1);
                const maxFte = Math.max(...enrichedProjects.map((project) => asNumber(project.total_fte)), 1);
                const maxHours = Math.max(...enrichedProjects.map((project) => asNumber(project.total_hours)), 1);
                const highBudgetThreshold = maxBudget * 0.6;
                const highFteThreshold = maxFte * 0.6;
                const deliveryGapCount = enrichedProjects.filter((project) => (
                    project.derived_status === 'กำลังดำเนินการ'
                    && (Number(project.progress_reports_count) === 0 || Number(project.outputs_count) === 0)
                )).length;

                elements.matrixHighValue.textContent = numberFormat.format(enrichedProjects.filter((project) => (
                    asNumber(project.budget) >= highBudgetThreshold || Number(project.outputs_count) >= 2
                )).length);
                elements.matrixPressure.textContent = numberFormat.format(enrichedProjects.filter((project) => (
                    asNumber(project.total_fte) >= highFteThreshold || asNumber(project.total_hours) >= maxHours * 0.6
                )).length);
                elements.matrixGap.textContent = numberFormat.format(deliveryGapCount);

                const mapBase = `
                    <span class="cc-map-axis cc-map-axis--y">FTE สูง</span>
                    <span class="cc-map-axis cc-map-axis--x">งบสูง</span>
                `;
                const mapRows = enrichedProjects
                    .sort((a, b) => (asNumber(b.budget) + asNumber(b.total_hours) + Number(b.outputs_count) * 1000) - (asNumber(a.budget) + asNumber(a.total_hours) + Number(a.outputs_count) * 1000))
                    .slice(0, 36);

                elements.portfolioMap.innerHTML = mapRows.length
                    ? mapBase + mapRows.map((project) => {
                        const x = 8 + (asNumber(project.budget) / maxBudget) * 84;
                        const y = 92 - (asNumber(project.total_fte) / maxFte) * 84;
                        const size = Math.min(28, 12 + Number(project.outputs_count || 0) * 3 + Number(project.task_risk_count || 0) * 2);
                        const title = `${project.code} | ${project.name}\nBudget ${formatMoney(project.budget)}\nFTE ${preciseMoneyFormat.format(project.total_fte)} | outputs ${numberFormat.format(project.outputs_count)} | ${project.derived_status}`;
                        return `<a class="cc-map-dot" href="${escapeHtml(project.url)}" title="${escapeHtml(title)}" style="left:${x.toFixed(2)}%; top:${y.toFixed(2)}%; --dot-size:${size}px; --dot-color:${statusColor(project.derived_status)}"></a>`;
                    }).join('')
                    : `${mapBase}<div class="cc-empty absolute left-4 right-4 top-1/2 -translate-y-1/2">ไม่มีโครงการใน lens นี้</div>`;

                const lifecycleRows = [
                    ['วางแผน', filtered.projects.filter((project) => project.derived_status === 'วางแผน').length, 'ยังไม่เริ่มหรืออยู่ช่วงตั้งต้น'],
                    ['กำลังดำเนินการ', filtered.projects.filter((project) => project.derived_status === 'กำลังดำเนินการ').length, 'กำลังเดินงานวิจัย'],
                    ['เกินกำหนด', filtered.projects.filter((project) => project.derived_status === 'เกินกำหนด').length, 'ต้องดูวันสิ้นสุดและผลงาน'],
                    ['มี progress', filtered.projects.filter((project) => Number(project.progress_reports_count) > 0).length, 'มีรายงานความก้าวหน้า'],
                    ['มี output', filtered.projects.filter((project) => Number(project.outputs_count) > 0).length, 'ผูกผลงานกับโครงการแล้ว'],
                    ['เสร็จสิ้น', filtered.projects.filter((project) => project.derived_status === 'เสร็จสิ้น').length, 'ปิดงานตามสถานะระบบ'],
                ];

                elements.lifecycleGrid.innerHTML = lifecycleRows.map(([label, value, meta]) => `
                    <div class="cc-lane">
                        <div class="lux-kicker">${escapeHtml(label)}</div>
                        <strong>${numberFormat.format(value)}</strong>
                        <span>${escapeHtml(meta)}</span>
                    </div>
                `).join('');

                const peopleRows = [...filtered.fte.reduce((map, row) => {
                    const key = normalizeText(row.name) || 'ไม่ระบุชื่อ';
                    if (!map.has(key)) {
                        map.set(key, { name: row.name || 'ไม่ระบุชื่อ', fte: 0, hours: 0, projects: new Set(), role: row.role_group || '-' });
                    }
                    const person = map.get(key);
                    person.fte += asNumber(row.fte_value);
                    person.hours += asNumber(row.hours);
                    person.projects.add(row.project_code || row.project_id);
                    return map;
                }, new Map()).values()]
                    .sort((a, b) => (b.fte + b.hours * 0.01) - (a.fte + a.hours * 0.01))
                    .slice(0, 6);
                const maxPersonLoad = Math.max(...peopleRows.map((row) => row.fte + row.hours * 0.01), 1);

                elements.peoplePressure.innerHTML = peopleRows.length
                    ? peopleRows.map((row) => {
                        const meter = Math.min(100, ((row.fte + row.hours * 0.01) / maxPersonLoad) * 100);
                        return `
                            <article class="cc-pressure-card">
                                <div class="cc-cell-title">${escapeHtml(row.name)}</div>
	                                <div class="cc-cell-meta">${escapeHtml(row.role)} | ${numberFormat.format(row.projects.size)} โครงการ</div>
                                <div class="cc-pressure-meter"><span style="--meter:${meter.toFixed(0)}%"></span></div>
                                <div class="cc-cell-meta mt-2">FTE ${preciseMoneyFormat.format(row.fte)} | ${preciseMoneyFormat.format(row.hours)} ชั่วโมง</div>
                            </article>
                        `;
                    }).join('')
                    : '<div class="cc-empty">ไม่มีข้อมูล FTE ใน lens นี้</div>';
            };

            const renderTopProjects = (filtered) => {
                const rows = [...filtered.projects]
                    .sort((a, b) => {
                        const aScore = asNumber(a.budget) + (a.task_risk_count * 1000) + (a.outputs_count * 100);
                        const bScore = asNumber(b.budget) + (b.task_risk_count * 1000) + (b.outputs_count * 100);
                        return bScore - aScore;
                    })
                    .slice(0, 6);

                if (!rows.length) {
                    elements.topProjectsBody.innerHTML = '<tr><td colspan="3"><div class="cc-empty">ไม่มีโครงการในช่วงนี้</div></td></tr>';
                    return;
                }

                elements.topProjectsBody.innerHTML = rows.map((project) => `
                    <tr>
                        <td>
                            <a href="${escapeHtml(project.url)}" class="cc-link cc-cell-title">${escapeHtml(project.name)}</a>
                            <span class="cc-cell-meta">${escapeHtml(project.code)} | ${escapeHtml(project.pi_name)}</span>
                        </td>
                        <td>
                            <span class="cc-cell-title">${escapeHtml(project.derived_status)}</span>
                            <span class="cc-cell-meta">${escapeHtml(project.project_type)} | งบ ${escapeHtml(formatMoney(project.budget))}</span>
                        </td>
                        <td>
	                            <span class="cc-cell-title">ผลงาน ${numberFormat.format(project.outputs_count)} | progress ${numberFormat.format(project.progress_reports_count)}</span>
	                            <span class="cc-cell-meta">งานเสี่ยง ${numberFormat.format(project.task_risk_count)} | IRB ${numberFormat.format(project.irbs_count)}</span>
                        </td>
                    </tr>
                `).join('');
            };

            const renderBackOffice = (filtered) => {
                const rows = [...filtered.backOffice]
                    .sort((a, b) => (asNumber(b.hours) + asNumber(b.fte)) - (asNumber(a.hours) + asNumber(a.fte)))
                    .slice(0, 6);

                if (!rows.length) {
                    elements.backOfficeBody.innerHTML = '<tr><td colspan="3"><div class="cc-empty">ไม่มีงาน back office ในช่วงนี้</div></td></tr>';
                    return;
                }

                elements.backOfficeBody.innerHTML = rows.map((task) => `
                    <tr>
                        <td>
                            <a href="${escapeHtml(task.url)}" class="cc-link cc-cell-title">${escapeHtml(task.title)}</a>
	                            <span class="cc-cell-meta">${escapeHtml(task.code)} | ผลงาน ${numberFormat.format(task.outputs_count)}</span>
                        </td>
                        <td>
                            <span class="cc-cell-title">${escapeHtml(task.creator_name)}</span>
                            <span class="cc-cell-meta">${escapeHtml(task.start_date || '-')} ถึง ${escapeHtml(task.end_date || '-')}</span>
                        </td>
                        <td>
                            <span class="cc-cell-title">${preciseMoneyFormat.format(task.hours)} ชั่วโมง</span>
	                            <span class="cc-cell-meta">FTE ${preciseMoneyFormat.format(task.fte)} | รายการ ${numberFormat.format(task.entries_count)}</span>
                        </td>
                    </tr>
                `).join('');
            };

            const renderOutputGaps = (filtered, alerts) => {
                const alertRows = ['noProgress', 'noOutput', 'externalMissingMeta']
                    .flatMap((key) => (alerts.find((group) => group.key === key)?.rows || []).map((row) => ({
                        ...row,
                        signal: key === 'noProgress' ? 'ไม่มี progress report' : key === 'noOutput' ? 'ยังไม่มี output' : 'metadata ไม่ครบ',
                    })));

                const projectRows = filtered.projects
                    .filter((project) => Number(project.outputs_count) === 0 || Number(project.progress_reports_count) === 0)
                    .map((project) => ({
                        title: project.name,
                        meta: `${project.code} | ${project.pi_name}`,
                        detail: `progress ${numberFormat.format(project.progress_reports_count)} | output ${numberFormat.format(project.outputs_count)}`,
                        signal: project.derived_status,
                        url: project.url,
                    }));

                const rows = [...alertRows, ...projectRows]
                    .filter((row, index, list) => list.findIndex((item) => item.title === row.title && item.meta === row.meta) === index)
                    .slice(0, 8);

                if (!rows.length) {
                    elements.outputGapBody.innerHTML = '<tr><td colspan="3"><div class="cc-empty">ยังไม่พบช่องว่าง output/progress ใน lens นี้</div></td></tr>';
                    return;
                }

                elements.outputGapBody.innerHTML = rows.map((row) => `
                    <tr>
                        <td>
                            <a href="${escapeHtml(row.url || '#')}" class="cc-link cc-cell-title">${escapeHtml(row.title || '-')}</a>
                            <span class="cc-cell-meta">${escapeHtml(row.meta || '-')}</span>
                        </td>
                        <td>
                            <span class="cc-cell-title">${escapeHtml(row.signal || '-')}</span>
                            <span class="cc-cell-meta">${escapeHtml(row.detail || '-')}</span>
                        </td>
                        <td>
                            <span class="cc-cell-title">ตรวจ deliverable</span>
                            <span class="cc-cell-meta">progress, output, IRB</span>
                        </td>
                    </tr>
                `).join('');
            };

            const renderFinanceFte = (filtered) => {
                const fteByProject = filtered.fte.reduce((map, row) => {
                    const key = Number(row.project_id);
                    if (!map.has(key)) {
                        map.set(key, { fte: 0, hours: 0 });
                    }
                    const bucket = map.get(key);
                    bucket.fte += asNumber(row.fte_value);
                    bucket.hours += asNumber(row.hours);
                    return map;
                }, new Map());

                const rows = filtered.projects
                    .map((project) => ({
                        ...project,
                        total_fte: fteByProject.get(Number(project.id))?.fte || 0,
                        total_hours: fteByProject.get(Number(project.id))?.hours || 0,
                    }))
                    .sort((a, b) => ((asNumber(b.budget) * 0.0001) + b.total_fte + (b.total_hours * 0.01)) - ((asNumber(a.budget) * 0.0001) + a.total_fte + (a.total_hours * 0.01)))
                    .slice(0, 8);

                if (!rows.length) {
                    elements.financeFteBody.innerHTML = '<tr><td colspan="3"><div class="cc-empty">ไม่มีข้อมูล budget/FTE ในช่วงนี้</div></td></tr>';
                    return;
                }

                elements.financeFteBody.innerHTML = rows.map((project) => `
                    <tr>
                        <td>
                            <a href="${escapeHtml(project.url)}" class="cc-link cc-cell-title">${escapeHtml(project.name)}</a>
                            <span class="cc-cell-meta">${escapeHtml(project.code)} | ${escapeHtml(project.project_type)}</span>
                        </td>
                        <td>
                            <span class="cc-cell-title">${escapeHtml(formatMoney(project.budget))}</span>
                            <span class="cc-cell-meta">${escapeHtml(project.funding_type)} | ${escapeHtml(project.funding_source)}</span>
                        </td>
                        <td>
                            <span class="cc-cell-title">FTE ${preciseMoneyFormat.format(project.total_fte)}</span>
                            <span class="cc-cell-meta">${preciseMoneyFormat.format(project.total_hours)} ชั่วโมง | status ${escapeHtml(project.derived_status)}</span>
                        </td>
                    </tr>
                `).join('');
            };

            const render = () => {
                const filtered = getFilteredData();
                const alerts = buildAlertGroups(filtered);

                renderKpis(filtered, alerts);
                renderTrendChart(filtered);
                renderStatusChart(filtered);
                renderOutputMixChart(filtered);
                renderFundingChart(filtered);
                renderFteChart(filtered);
                renderAlerts(alerts);
                renderInsights(filtered, alerts);
                renderNarratives(filtered, alerts);
                renderResearchMatrix(filtered, alerts);
                renderTopProjects(filtered);
                renderBackOffice(filtered);
                renderOutputGaps(filtered, alerts);
                renderFinanceFte(filtered);
            };

            [elements.search, elements.year, elements.projectType, elements.fundingType, elements.projectStatus]
                .forEach((element) => {
                    element?.addEventListener('input', render);
                    element?.addEventListener('change', render);
                });

            elements.reset?.addEventListener('click', () => {
                elements.search.value = '';
                elements.year.value = '';
                elements.projectType.value = '';
                elements.fundingType.value = '';
                elements.projectStatus.value = '';
                render();
            });

            elements.modalOverlay?.addEventListener('click', closeModal);
            elements.modalClose?.addEventListener('click', closeModal);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.16 });

            document.querySelectorAll('.lux-reveal').forEach((node) => revealObserver.observe(node));

            render();
        })();
    </script>
@endsection
