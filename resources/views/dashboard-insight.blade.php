@extends('layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap');

        :root {
            --dash-ink: #0f172a;
            --dash-muted: #64748b;
            --dash-line: #dbe5f1;
            --dash-panel: rgba(255, 255, 255, 0.88);
            --dash-blue: #2563eb;
            --dash-teal: #14b8a6;
            --dash-amber: #d99b2b;
            --dash-red: #ef4444;
            --dash-green: #22c55e;
            --dash-violet: #7c3aed;
        }

        body {
            background: #f3f6fa;
            overflow-x: hidden;
        }

        .di-page,
        .di-page * {
            box-sizing: border-box;
            font-family: 'IBM Plex Sans Thai', sans-serif;
        }

        .di-page {
            min-height: 100vh;
            color: var(--dash-ink);
            background:
                radial-gradient(circle at 94% 8%, rgba(20, 184, 166, 0.18), transparent 28rem),
                radial-gradient(circle at 6% 18%, rgba(37, 99, 235, 0.12), transparent 24rem),
                linear-gradient(180deg, #f8fafc 0%, #eef3f8 52%, #f8fafc 100%);
        }

        .di-shell {
            width: min(1380px, calc(100vw - 40px));
            margin: 0 auto;
            padding: 24px 0 44px;
        }

        .di-panel {
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 22px;
            background: var(--dash-panel);
            box-shadow: 0 18px 54px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(14px);
        }

        .di-hero {
            padding: clamp(22px, 2.4vw, 34px);
            background:
                linear-gradient(135deg, rgba(15, 118, 110, 0.12), transparent 40%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.92));
        }

        .di-kicker {
            color: #0f766e;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .di-title {
            margin-top: 10px;
            font-size: clamp(26px, 2.7vw, 40px);
            line-height: 1.08;
            font-weight: 800;
            letter-spacing: 0;
        }

        .di-subtitle {
            max-width: 860px;
            margin-top: 14px;
            color: #475569;
            font-size: clamp(13px, 1.05vw, 16px);
            line-height: 1.7;
        }

        .di-filter-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
        }

        .di-field {
            display: grid;
            gap: 6px;
        }

        .di-field span {
            color: #475569;
            font-size: 12px;
            font-weight: 800;
        }

        .di-field input,
        .di-field select {
            min-height: 44px;
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: #fff;
            padding: 0 12px;
            color: #0f172a;
            font-size: 14px;
            outline: none;
        }

        .di-field input:focus,
        .di-field select:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.14);
        }

        .di-reset {
            min-height: 44px;
            align-self: end;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: #fff;
            color: #334155;
            font-weight: 800;
            cursor: pointer;
        }

        .di-floating-actions {
            position: fixed;
            left: max(18px, env(safe-area-inset-left));
            bottom: calc(18px + env(safe-area-inset-bottom));
            z-index: 45;
            display: grid;
            gap: 12px;
        }

        .di-fab {
            width: 58px;
            height: 58px;
            min-height: 58px;
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
            color: #0f172a;
            cursor: pointer;
            font-size: 0;
            font-weight: 900;
            line-height: 1.2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.16s ease, box-shadow 0.16s ease;
        }

        .di-fab:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.2);
        }

        .di-fab span {
            display: block;
            margin-bottom: 0;
            font-size: 24px;
            line-height: 1;
        }

        .di-overlay {
            position: fixed;
            inset: 0;
            z-index: 48;
            display: none;
            background: rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(3px);
        }

        .di-overlay.is-open {
            display: block;
        }

        .di-filter-popup {
            position: fixed;
            left: max(112px, calc(env(safe-area-inset-left) + 92px));
            top: 50%;
            z-index: 49;
            width: min(920px, calc(100vw - 136px));
            max-height: min(82vh, 720px);
            overflow: auto;
            transform: translate(-12px, -50%);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .di-filter-popup.is-open {
            opacity: 1;
            pointer-events: auto;
            transform: translate(0, -50%);
        }

        .di-popup-head {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .di-filter-popup.is-open .di-popup-head {
            display: flex;
        }

        .di-close {
            width: 40px;
            height: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #fff;
            color: #475569;
            cursor: pointer;
            font-size: 22px;
            line-height: 1;
        }

        .di-download-popup {
            max-height: calc(100dvh - 40px);
            overflow-y: auto;
            position: fixed;
            left: max(112px, calc(env(safe-area-inset-left) + 92px));
            top: 50%;
            z-index: 49;
            width: min(360px, calc(100vw - 136px));
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.2);
            padding: 18px;
            transform: translate(-12px, -50%);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .di-download-popup.is-open {
            opacity: 1;
            pointer-events: auto;
            transform: translate(0, -50%);
        }

        .di-download-action {
            width: 100%;
            min-height: 48px;
            margin-top: 12px;
            border: 0;
            border-radius: 16px;
            background: #0f766e;
            color: #fff;
            cursor: pointer;
            font-weight: 900;
        }

        .di-section {
            margin-top: 18px;
            padding: clamp(16px, 2vw, 24px);
        }

        .di-section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .di-tag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(37, 99, 235, 0.18);
            background: rgba(37, 99, 235, 0.08);
            padding: 5px 10px;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .di-section h2 {
            margin-top: 8px;
            font-size: clamp(18px, 1.65vw, 24px);
            line-height: 1.25;
            font-weight: 850;
        }

        .di-insight {
            max-width: 540px;
            border-left: 4px solid var(--dash-teal);
            border-radius: 18px;
            background: rgba(236, 253, 245, 0.68);
            padding: 10px 12px;
            color: #335164;
            font-size: 13px;
            line-height: 1.6;
        }

        .di-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .di-kpi {
            border: 1px solid rgba(203, 213, 225, 0.86);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.78);
            padding: 14px;
            min-width: 0;
        }

        .di-kpi span {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
        }

        .di-kpi strong {
            display: block;
            margin-top: 7px;
            color: #0f172a;
            font-size: clamp(21px, 2vw, 30px);
            line-height: 1.05;
            font-weight: 900;
        }

        .di-kpi small {
            display: block;
            margin-top: 6px;
            color: #64748b;
            font-size: 11px;
            line-height: 1.45;
        }

        .di-summary-panel .di-section-head {
            margin-bottom: 12px;
        }

        .di-summary-panel .di-section h2,
        .di-summary-panel h2 {
            font-size: clamp(17px, 1.4vw, 22px);
        }

        .di-summary-panel .di-insight {
            max-width: 270px;
            padding: 8px 10px;
            font-size: 12px;
            line-height: 1.45;
        }

        .di-summary-grid {
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .di-summary-budget-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .di-summary-panel .di-kpi {
            position: relative;
            overflow: hidden;
            border-radius: 14px;
            padding: 10px 11px;
            background: rgba(255, 255, 255, 0.84);
        }

        .di-summary-panel .di-kpi::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            background: var(--summary-color, #cbd5e1);
        }

        .di-summary-panel .di-kpi span {
            font-size: 10.5px;
        }

        .di-summary-panel .di-kpi strong {
            margin-top: 5px;
            font-size: clamp(18px, 1.45vw, 24px);
        }

        .di-summary-panel .di-kpi small {
            margin-top: 4px;
            font-size: 10.5px;
        }

        .di-summary-panel .di-table {
            font-size: 12px;
        }

        .di-summary-panel .di-table th {
            font-size: 11px;
        }

        .di-summary-table-title {
            margin-top: 16px;
            color: #0f172a;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.35;
        }

        .di-theme-summary-table {
            table-layout: fixed;
        }

        .di-theme-summary-table th:first-child,
        .di-theme-summary-table td:first-child {
            width: 56%;
        }

        .di-theme-summary-table th:nth-child(2),
        .di-theme-summary-table td:nth-child(2) {
            width: 14%;
        }

        .di-theme-summary-table th:nth-child(3),
        .di-theme-summary-table td:nth-child(3) {
            width: 14%;
        }

        .di-theme-summary-table th:nth-child(4),
        .di-theme-summary-table td:nth-child(4) {
            width: 16%;
        }

        .di-summary-content-theme-table thead th {
            background: #e0f2fe;
            color: #075985;
            border-bottom-color: #bae6fd;
        }

        .di-summary-type-table thead th {
            background: #ecfdf5;
            color: #047857;
            border-bottom-color: #bbf7d0;
        }

        .di-theme-summary-table tbody tr:nth-child(odd) td {
            background: #ffffff;
        }

        .di-theme-summary-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .di-summary-budget-grid .di-kpi strong {
            font-size: clamp(18px, 1.5vw, 25px);
        }

        .di-summary-grid .di-kpi:nth-child(1) { --summary-color: #2563eb; }
        .di-summary-grid .di-kpi:nth-child(2) { --summary-color: #14b8a6; }
        .di-summary-grid .di-kpi:nth-child(3) { --summary-color: #7c3aed; }
        .di-summary-grid .di-kpi:nth-child(4) { --summary-color: #d99b2b; }
        .di-summary-grid .di-kpi:nth-child(5) { --summary-color: #22c55e; }
        .di-summary-grid .di-kpi:nth-child(6) { --summary-color: #ef4444; }
        .di-summary-budget-grid .di-kpi:nth-child(1) { --summary-color: #0f766e; }
        .di-summary-budget-grid .di-kpi:nth-child(2) { --summary-color: #2563eb; }
        .di-summary-budget-grid .di-kpi:nth-child(3) { --summary-color: #d99b2b; }

        .di-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .di-card {
            min-width: 0;
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.86);
            padding: 14px;
        }

        .di-card--wide {
            grid-column: 1 / -1;
        }

        .di-card h3 {
            margin: 0 0 10px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 850;
        }

        .di-chart-note {
            margin-top: 10px;
            border-radius: 14px;
            background: rgba(248, 250, 252, 0.92);
            padding: 9px 11px;
            color: #64748b;
            font-size: 11px;
            font-weight: 750;
            line-height: 1.55;
        }

        .di-analysis-card {
            border-left: 4px solid var(--dash-teal);
            border-radius: 16px;
            background: rgba(236, 253, 245, 0.68);
            padding: 10px 12px;
            color: #335164;
            font-size: 12px;
            line-height: 1.55;
        }

        .di-analysis-card strong {
            display: block;
            margin-bottom: 4px;
            color: #0f172a;
            font-size: 12px;
            font-weight: 900;
        }

        .di-ai-insight-note {
            margin-top: 14px;
            color: #64748b;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.6;
        }

        .di-analysis-card:nth-child(2) {
            border-left-color: var(--dash-blue);
            background: rgba(239, 246, 255, 0.78);
        }

        .di-portfolio-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(420px, 0.46fr);
            gap: 16px;
            align-items: start;
        }

        .di-funding-layout,
        .di-output-layout,
        .di-ops-layout,
        .di-workload-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(340px, 0.34fr);
            gap: 16px;
            align-items: start;
        }

        .di-workload-layout {
            grid-template-columns: minmax(0, 0.94fr) minmax(430px, 0.52fr);
            gap: 20px;
        }

        .di-portfolio-charts,
        .di-funding-charts,
        .di-output-charts,
        .di-ops-charts,
        .di-workload-charts {
            display: grid;
            gap: 14px;
        }

        .di-portfolio-charts .di-card,
        .di-funding-charts .di-card,
        .di-output-charts .di-card,
        .di-ops-charts .di-card,
        .di-workload-charts .di-card {
            display: flex;
            min-height: 420px;
            flex-direction: column;
        }

        .di-funding-charts .di-card,
        .di-output-charts .di-card,
        .di-ops-charts .di-card,
        .di-workload-charts .di-card {
            min-height: 390px;
        }

        .di-portfolio-charts .di-chart,
        .di-funding-charts .di-chart,
        .di-output-charts .di-chart,
        .di-ops-charts .di-chart,
        .di-workload-charts .di-chart {
            flex: 1;
            min-height: 350px;
            height: auto;
        }

        .di-funding-charts .di-chart,
        .di-output-charts .di-chart,
        .di-ops-charts .di-chart,
        .di-workload-charts .di-chart {
            min-height: 320px;
        }

        .di-portfolio-aside,
        .di-funding-aside,
        .di-output-aside,
        .di-ops-aside,
        .di-workload-aside {
            position: sticky;
            top: 76px;
            display: grid;
            max-height: calc(100vh - 104px);
            gap: 10px;
            overflow: auto;
            border: 1px solid rgba(199, 210, 254, 0.88);
            border-radius: 22px;
            background:
                linear-gradient(135deg, rgba(67, 56, 202, 0.1), transparent 44%),
                rgba(255, 255, 255, 0.92);
            padding: 14px;
            box-shadow: 0 18px 54px rgba(15, 23, 42, 0.09);
            overscroll-behavior: contain;
        }

        .di-funding-aside {
            border-color: rgba(253, 186, 116, 0.88);
            background:
                linear-gradient(135deg, rgba(217, 119, 6, 0.12), transparent 44%),
                rgba(255, 255, 255, 0.92);
        }

        .di-output-aside {
            border-color: rgba(196, 181, 253, 0.88);
            background:
                linear-gradient(135deg, rgba(124, 58, 237, 0.11), transparent 44%),
                rgba(255, 255, 255, 0.92);
        }

        .di-ops-aside {
            border-color: rgba(125, 211, 252, 0.88);
            background:
                linear-gradient(135deg, rgba(14, 165, 233, 0.12), transparent 44%),
                rgba(255, 255, 255, 0.92);
        }

        .di-workload-aside {
            border-color: rgba(167, 139, 250, 0.88);
            background:
                linear-gradient(135deg, rgba(109, 40, 217, 0.11), transparent 44%),
                rgba(255, 255, 255, 0.92);
            max-height: none;
            position: sticky;
            top: 76px;
            align-self: start;
        }

        .di-portfolio-aside::before,
        .di-funding-aside::before,
        .di-output-aside::before,
        .di-ops-aside::before,
        .di-workload-aside::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg, #4338ca, #14b8a6);
        }

        .di-funding-aside::before {
            background: linear-gradient(90deg, #d97706, #14b8a6);
        }

        .di-output-aside::before {
            background: linear-gradient(90deg, #7c3aed, #14b8a6);
        }

        .di-ops-aside::before {
            background: linear-gradient(90deg, #0ea5e9, #ef4444);
        }

        .di-workload-aside::before {
            background: linear-gradient(90deg, #7c3aed, #2563eb);
        }

        .di-portfolio-aside-title,
        .di-funding-aside-title,
        .di-output-aside-title,
        .di-ops-aside-title,
        .di-workload-aside-title {
            color: #1e1b4b;
            font-size: 18px;
            font-weight: 900;
            line-height: 1.25;
        }

        .di-funding-aside-title {
            color: #78350f;
        }

        .di-output-aside-title {
            color: #4c1d95;
        }

        .di-ops-aside-title {
            color: #075985;
        }

        .di-workload-aside-title {
            color: #4c1d95;
        }

        .di-portfolio-aside-subtitle,
        .di-funding-aside-subtitle,
        .di-output-aside-subtitle,
        .di-ops-aside-subtitle,
        .di-workload-aside-subtitle {
            margin-top: -4px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.5;
        }

        .di-portfolio-aside .di-analysis-card,
        .di-funding-aside .di-analysis-card,
        .di-output-aside .di-analysis-card,
        .di-ops-aside .di-analysis-card,
        .di-workload-aside .di-analysis-card {
            border-left-width: 0;
            border-radius: 16px;
            background: #f8fafc;
            padding: 12px;
            color: #334155;
            font-size: 12px;
            line-height: 1.62;
        }

        .di-portfolio-aside {
            max-height: none;
        }

        .di-portfolio-aside .di-analysis-card {
            min-height: 190px;
            padding: 16px;
            font-size: 13px;
            line-height: 1.76;
        }

        .di-portfolio-aside .di-analysis-card span {
            display: block;
        }

        .di-funding-aside .di-analysis-card {
            background: #fffbeb;
        }

        .di-output-aside .di-analysis-card {
            background: #faf5ff;
        }

        .di-ops-aside .di-analysis-card {
            background: #f0f9ff;
        }

        .di-workload-aside .di-analysis-card {
            background: #f5f3ff;
        }

        .di-workload-aside .di-analysis-card:last-child {
            min-height: 420px;
            padding: 16px;
            font-size: 13px;
            line-height: 1.78;
        }

        .di-workload-aside .di-analysis-card:last-child span {
            display: block;
            white-space: pre-line;
        }

        .di-portfolio-aside .di-analysis-card strong,
        .di-funding-aside .di-analysis-card strong,
        .di-output-aside .di-analysis-card strong,
        .di-ops-aside .di-analysis-card strong,
        .di-workload-aside .di-analysis-card strong {
            margin-bottom: 5px;
            color: #0f172a;
            font-size: 13px;
        }

        .di-portfolio-summary-list,
        .di-funding-summary-list,
        .di-output-summary-list,
        .di-ops-summary-list,
        .di-workload-summary-list {
            display: grid;
            gap: 7px;
            margin-top: 2px;
        }

        .di-portfolio-summary-list span,
        .di-funding-summary-list span,
        .di-output-summary-list span,
        .di-ops-summary-list span,
        .di-workload-summary-list span {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border-radius: 12px;
            background: rgba(239, 246, 255, 0.86);
            padding: 7px 9px;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .di-funding-summary-list span {
            background: rgba(255, 247, 237, 0.92);
        }

        .di-output-summary-list span {
            background: rgba(250, 245, 255, 0.95);
        }

        .di-ops-summary-list span {
            background: rgba(240, 249, 255, 0.95);
        }

        .di-workload-summary-list span {
            background: rgba(245, 243, 255, 0.95);
        }

        .di-portfolio-summary-list b,
        .di-funding-summary-list b,
        .di-output-summary-list b,
        .di-ops-summary-list b,
        .di-workload-summary-list b {
            color: #1d4ed8;
            font-weight: 900;
        }

        .di-funding-summary-list b {
            color: #b45309;
        }

        .di-output-summary-list b {
            color: #6d28d9;
        }

        .di-ops-summary-list b {
            color: #0369a1;
        }

        .di-workload-summary-list b {
            color: #6d28d9;
        }

        .di-chart {
            position: relative;
            width: 100%;
            height: 320px;
            min-width: 0;
            overflow: hidden;
        }

        .di-chart canvas {
            max-width: 100%;
        }

        .di-chart--short {
            height: 260px;
        }

        .di-chart--tall {
            height: 400px;
        }

        .di-chart--people-budget {
            height: 760px !important;
            min-height: 760px !important;
            flex: 0 0 auto !important;
        }

        .di-card--people-budget {
            min-height: 830px !important;
        }

        .fte-avatar-chart-shell {
            position: relative;
            flex: 1;
            min-height: 350px;
        }

        .fte-avatar-chart-shell canvas {
            position: relative;
            z-index: 2;
        }

        .fte-avatar-layer {
            position: absolute;
            inset: 0;
            z-index: 1;
            overflow: visible;
            pointer-events: none;
        }

        .di-skill-capacity-card {
            min-height: 430px;
            border-color: #c7d7eb;
            border-radius: 20px;
            background: #ffffff;
        }

        .di-skill-capacity-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
            min-width: 0;
        }

        .di-skill-capacity-kicker {
            color: #64748b;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .di-skill-capacity-title {
            margin-top: 4px;
            max-width: 100%;
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .di-skill-capacity-table-wrap {
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #d5e1ef;
            border-radius: 14px;
            background: #fff;
        }

        .di-skill-capacity-table {
            width: 100%;
            min-width: 0;
            border-collapse: collapse;
            color: #233047;
            font-size: 13px;
            table-layout: fixed;
        }

        .di-skill-capacity-table th,
        .di-skill-capacity-table td {
            padding: 13px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #dce4ee;
            border-left: 1px solid #dce4ee;
        }

        .di-skill-capacity-table th:first-child,
        .di-skill-capacity-table td:first-child {
            border-left: 0;
        }

        .di-skill-capacity-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0;
            line-height: 1.2;
            text-align: left;
            text-transform: none;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .di-skill-capacity-table tbody td {
            background: #ffffff;
        }

        .di-skill-capacity-table tfoot td {
            background: #eef3f8;
            color: #0f172a;
            font-weight: 900;
            border-bottom: 0;
        }

        .di-skill-capacity-table__group {
            background: #e8f2ff !important;
            color: #1e3a8a !important;
            font-size: 9.5px !important;
            line-height: 1.22 !important;
            text-align: center !important;
        }

        .di-skill-capacity-table__group--current {
            background: #e8f8ef !important;
            color: #047857 !important;
        }

        .di-skill-capacity-table--skill thead tr:first-child th:nth-child(3),
        .di-skill-capacity-table--skill tbody td:nth-child(3),
        .di-skill-capacity-table--skill tfoot td:nth-child(2) {
            border-left: 1px solid #dce4ee;
        }

        .di-skill-capacity-table--skill thead tr:first-child th:nth-child(2),
        .di-skill-capacity-table--skill tbody td:nth-child(2) {
            border-left: 0;
        }

        .di-skill-capacity-table--skill thead tr:first-child th:nth-child(2) {
            font-size: 14px;
            line-height: 1.25;
        }

        .di-skill-capacity-table--skill thead tr:first-child th:nth-child(4),
        .di-skill-capacity-table--skill thead tr:nth-child(2) th:nth-child(4),
        .di-skill-capacity-table--skill tbody td:nth-child(6),
        .di-skill-capacity-table--skill tfoot td:nth-child(5) {
            border-left: 1px solid #dce4ee !important;
            box-shadow: none;
        }

        .di-skill-capacity-table--skill thead tr:nth-child(2) th:nth-child(1) {
            background: #f5f3ff !important;
            color: #0f172a !important;
        }

        .di-skill-capacity-table--skill thead tr:nth-child(2) th:nth-child(2) {
            background: #eff6ff !important;
            color: #0f172a !important;
        }

        .di-skill-capacity-table--skill thead tr:nth-child(2) th:nth-child(3) {
            background: #fff7ed !important;
            color: #0f172a !important;
        }

        .di-skill-capacity-table--skill thead tr:nth-child(2) th {
            text-align: center;
            vertical-align: middle;
        }

        .di-skill-capacity-table__num {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .di-skill-capacity-table tbody .di-skill-capacity-table__num,
        .di-skill-capacity-table tfoot .di-skill-capacity-table__num {
            white-space: nowrap;
        }

        .di-skill-capacity-rank {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            min-height: 28px;
            border-radius: 999px;
            background: #dff3ff;
            color: #036987;
            font-weight: 900;
        }

        .di-skill-capacity-count {
            color: #067a3f;
            font-weight: 900;
        }

        .di-skill-capacity-name {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            color: #0f172a;
            font-size: 12px;
            font-weight: 900;
            line-height: 1.35;
        }

        .di-skill-capacity-borrowed {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            min-height: 24px;
            border-radius: 999px;
            background: #fef3c7;
            color: #b45309;
            font-weight: 900;
        }

        .di-skill-capacity-empty {
            color: #94a3b8;
        }

        .di-skill-capacity-note {
            margin-top: 10px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }

        .fte-avatar-marker {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translate(-50%, -50%);
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, 0.96);
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--fte-accent, #0f766e), #0f172a);
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.18), 0 0 0 2px var(--fte-accent, #0f766e);
        }

        .fte-avatar-marker img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #f8fafc;
        }

        .fte-avatar-marker__fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 30% 25%, rgba(255, 255, 255, 0.26), transparent 38%),
                linear-gradient(135deg, var(--fte-accent, #0f766e), #0f172a);
            color: #fff;
            font-weight: 900;
        }

        .fte-avatar-marker.has-photo .fte-avatar-marker__fallback,
        .fte-avatar-marker.is-photo-missing img {
            display: none;
        }

        .fte-avatar-marker.is-photo-missing .fte-avatar-marker__fallback {
            display: flex;
        }

        .di-author-board {
            display: grid;
            gap: 10px;
        }

        .di-author-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 14px;
            align-items: center;
            color: #64748b;
            font-size: 11px;
            font-weight: 850;
        }

        .di-author-legend span {
            display: inline-flex;
            gap: 6px;
            align-items: center;
        }

        .di-author-legend i {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            display: inline-block;
        }

        .di-author-legend .first { background: #ef4444; }
        .di-author-legend .co { background: #e3c449; }
        .di-author-legend .corresponding { background: #83d84e; }

        .di-author-head {
            display: grid;
            grid-template-columns: minmax(170px, 0.34fr) minmax(0, 1fr) 52px;
            gap: 12px;
            align-items: center;
            color: #64748b;
            font-size: 11px;
            font-weight: 900;
            padding: 0 12px;
        }

        .di-author-row {
            display: grid;
            grid-template-columns: minmax(170px, 0.34fr) minmax(0, 1fr) 52px;
            gap: 12px;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
            padding: 10px 12px;
            cursor: pointer;
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        .di-author-row:hover,
        .di-author-row:focus {
            border-color: #c4b5fd;
            box-shadow: 0 12px 30px rgba(109, 40, 217, 0.12);
            outline: none;
            transform: translateY(-1px);
        }

        .di-author-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 850;
        }

        .di-author-stack {
            display: flex;
            height: 30px;
            overflow: hidden;
            border-radius: 999px;
            background: #e2e8f0;
        }

        .di-author-seg {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            font-size: 12px;
            font-weight: 900;
        }

        .di-author-seg--first {
            background: #ef4444;
            color: #fff;
        }

        .di-author-seg--co {
            background: #e3c449;
            color: #172033;
        }

        .di-author-seg--corresponding {
            background: #83d84e;
            color: #15351e;
        }

        .di-author-total {
            justify-self: end;
            border-radius: 999px;
            background: #0f172a;
            color: #fff;
            padding: 5px 10px;
            font-weight: 900;
        }

        .di-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .di-table th,
        .di-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 8px;
            text-align: left;
            vertical-align: top;
        }

        .di-table th {
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
        }

        .di-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            padding: 22px;
            color: #64748b;
            text-align: center;
            font-weight: 700;
        }

        .di-pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .di-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #eff6ff;
            padding: 5px 10px;
            color: #1e40af;
            font-size: 12px;
            font-weight: 800;
        }

        .di-modal {
            position: fixed;
            inset: 0;
            z-index: 70;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, 0.28);
            backdrop-filter: blur(4px);
        }

        .di-modal.is-open {
            display: flex;
        }

        .di-modal-panel {
            display: flex;
            flex-direction: column;
            width: min(1280px, 96vw);
            max-height: min(92dvh, 920px);
            overflow: hidden;
            border: 1px solid rgba(203, 213, 225, 0.95);
            border-radius: 26px;
            background: #fff;
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.28);
        }

        .di-modal-head {
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, 0.96);
            padding: 16px 18px;
            backdrop-filter: blur(12px);
        }

        .di-modal-title {
            color: #0f172a;
            font-size: 18px;
            font-weight: 900;
            line-height: 1.3;
        }

        .di-modal-subtitle {
            margin-top: 4px;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        .di-modal-body {
            flex: 1;
            min-height: 0;
            overflow: auto;
            padding: 14px 18px 18px;
        }

        .di-modal-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .di-modal-content {
            --modal-accent-bg: #eef6ff;
            --modal-accent-text: #1e3a8a;
            --modal-accent-border: #bfdbfe;
            --modal-kpi-bg: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        .di-modal-content--funding {
            --modal-accent-bg: #fff7ed;
            --modal-accent-text: #9a3412;
            --modal-accent-border: #fed7aa;
            --modal-kpi-bg: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
        }

        .di-modal-content--output {
            --modal-accent-bg: #faf5ff;
            --modal-accent-text: #6d28d9;
            --modal-accent-border: #e9d5ff;
            --modal-kpi-bg: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%);
        }

        .di-modal-content--operation {
            --modal-accent-bg: #ecfeff;
            --modal-accent-text: #0e7490;
            --modal-accent-border: #a5f3fc;
            --modal-kpi-bg: linear-gradient(180deg, #ecfeff 0%, #ffffff 100%);
        }

        .di-modal-content--risk {
            --modal-accent-bg: #fef2f2;
            --modal-accent-text: #b91c1c;
            --modal-accent-border: #fecaca;
            --modal-kpi-bg: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%);
        }

        .di-modal-content--workload {
            --modal-accent-bg: #f5f3ff;
            --modal-accent-text: #5b21b6;
            --modal-accent-border: #ddd6fe;
            --modal-kpi-bg: linear-gradient(180deg, #f5f3ff 0%, #ffffff 100%);
        }

        .di-modal-kpi {
            border: 1px solid var(--modal-accent-border);
            border-radius: 16px;
            background: var(--modal-kpi-bg);
            padding: 11px 12px;
        }

        .di-modal-kpi span {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 850;
            line-height: 1.35;
        }

        .di-modal-kpi strong {
            display: block;
            margin-top: 4px;
            color: #0f172a;
            font-size: 18px;
            font-weight: 950;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .di-modal-table-wrap {
            overflow: auto;
            border: 1px solid #dbe5f1;
            border-radius: 18px;
            background: #fff;
        }

        .di-modal-table {
            min-width: 920px;
            table-layout: fixed;
        }

        .di-modal-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: var(--modal-accent-bg);
            color: var(--modal-accent-text);
            font-size: 11px;
        }

        .di-modal-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .di-modal-table th:first-child,
        .di-modal-table td:first-child {
            width: 36%;
        }

        .di-modal-table th:nth-child(2),
        .di-modal-table td:nth-child(2) {
            width: 16%;
        }

        .di-modal-table th:nth-child(3),
        .di-modal-table td:nth-child(3) {
            width: 9%;
        }

        .di-modal-table th:nth-child(4),
        .di-modal-table td:nth-child(4) {
            width: 13%;
        }

        .di-modal-table th:nth-child(5),
        .di-modal-table td:nth-child(5) {
            width: 13%;
        }

        .di-modal-table th:nth-child(6),
        .di-modal-table td:nth-child(6) {
            width: 13%;
        }

        @media (max-width: 1366px) {
            .di-shell {
                width: min(1180px, calc(100vw - 36px));
            }

            .di-portfolio-layout,
            .di-funding-layout,
            .di-output-layout,
            .di-ops-layout,
            .di-workload-layout {
                grid-template-columns: 1fr;
            }

            .di-portfolio-aside,
            .di-funding-aside,
            .di-output-aside,
            .di-ops-aside,
            .di-workload-aside {
                position: static;
                order: -1;
                max-height: none;
                overflow: visible;
                overscroll-behavior: auto;
            }

            .di-portfolio-charts .di-card,
            .di-funding-charts .di-card,
            .di-output-charts .di-card,
            .di-ops-charts .di-card,
            .di-workload-charts .di-card {
                min-height: 380px;
            }

            .di-portfolio-charts .di-chart,
            .di-funding-charts .di-chart,
            .di-output-charts .di-chart,
            .di-ops-charts .di-chart,
            .di-workload-charts .di-chart {
                min-height: 310px;
            }
        }

        @media (max-width: 1180px) {
            .di-filter-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .di-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .di-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .di-grid {
                grid-template-columns: 1fr;
            }

            .di-portfolio-layout {
                grid-template-columns: 1fr;
            }

            .di-funding-layout,
            .di-output-layout,
            .di-ops-layout,
            .di-workload-layout {
                grid-template-columns: 1fr;
            }

            .di-portfolio-aside,
            .di-funding-aside,
            .di-output-aside,
            .di-ops-aside,
            .di-workload-aside {
                position: static;
                order: -1;
                max-height: none;
                overflow: visible;
                overscroll-behavior: auto;
            }
        }

        @media (min-width: 721px) and (max-width: 1024px) {
            .di-shell {
                width: min(100vw - 28px, 980px);
                padding-top: 18px;
            }

            .di-hero,
            .di-section {
                padding: 18px;
            }

            .di-title {
                font-size: clamp(24px, 4vw, 34px);
            }

            .di-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .di-summary-budget-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .di-portfolio-aside .di-analysis-card,
            .di-funding-aside .di-analysis-card,
            .di-output-aside .di-analysis-card,
            .di-ops-aside .di-analysis-card,
            .di-workload-aside .di-analysis-card {
                min-height: auto;
            }

            .di-chart,
            .di-chart--short,
            .di-chart--tall {
                height: 340px;
            }

            .di-portfolio-charts .di-card,
            .di-funding-charts .di-card,
            .di-output-charts .di-card,
            .di-ops-charts .di-card,
            .di-workload-charts .di-card {
                min-height: 390px;
            }

            .di-filter-popup,
            .di-download-popup {
                left: 72px;
                width: min(760px, calc(100vw - 96px));
            }

            .di-skill-capacity-title {
                font-size: 12px;
                line-height: 1.35;
            }

            .di-skill-capacity-table th,
            .di-skill-capacity-table td {
                padding: 7px 4px;
            }

            .di-skill-capacity-table th {
                font-size: 7.5px;
                letter-spacing: 0;
                line-height: 1.12;
                white-space: normal;
                overflow-wrap: anywhere;
            }

            .di-skill-capacity-table__group {
                font-size: 8px !important;
                letter-spacing: .03em !important;
            }

            .di-skill-capacity-name {
                font-size: 10.5px;
                line-height: 1.25;
            }

            .di-ops-charts .di-watch-card {
                grid-column: 1 / -1;
                width: 100%;
                min-height: auto;
            }

            .di-watch-card .overflow-x-auto {
                overflow-x: visible;
            }

            .di-watch-table {
                width: 100%;
                min-width: 0;
                table-layout: auto;
            }

            .di-modal {
                padding: 14px;
            }

            .di-modal-panel {
                width: calc(100vw - 28px);
                max-height: calc(100dvh - 28px);
                border-radius: 22px;
            }

            .di-modal-body {
                padding: 14px;
            }

            .di-modal-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .di-modal-table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .di-modal-table {
                min-width: 760px;
            }

        }

        @media (min-width: 721px) and (max-width: 900px) and (orientation: portrait) {
            .di-modal-table-wrap {
                overflow-x: visible;
            }

            .di-modal-table {
                min-width: 0;
                width: 100%;
                table-layout: fixed;
                font-size: 11px;
            }

            .di-modal-table th,
            .di-modal-table td {
                padding: 9px 7px;
                white-space: normal;
                overflow-wrap: anywhere;
            }

            .di-modal-table th:first-child,
            .di-modal-table td:first-child {
                width: 32%;
            }

            .di-modal-table th:nth-child(2),
            .di-modal-table td:nth-child(2) {
                width: 16%;
            }

            .di-modal-table th:nth-child(3),
            .di-modal-table td:nth-child(3),
            .di-modal-table th:nth-child(4),
            .di-modal-table td:nth-child(4),
            .di-modal-table th:nth-child(5),
            .di-modal-table td:nth-child(5),
            .di-modal-table th:nth-child(6),
            .di-modal-table td:nth-child(6) {
                width: 13%;
            }
        }

        @media (max-width: 720px) {
            .di-floating-actions {
                left: 10px;
                bottom: calc(12px + env(safe-area-inset-bottom));
                gap: 10px;
            }

            .di-fab {
                width: 54px;
                height: 54px;
                min-height: 54px;
            }

            .di-filter-popup,
            .di-download-popup {
                left: 10px;
                right: 10px;
                top: 50%;
                width: auto;
                max-height: 84vh;
            }

            .di-shell {
                width: min(100vw - 20px, 1380px);
                padding-top: 14px;
            }

            .di-hero,
            .di-section {
                padding: 14px;
                border-radius: 18px;
            }

            .di-title {
                font-size: clamp(22px, 7vw, 28px);
                line-height: 1.18;
            }

            .di-card {
                padding: 12px;
                border-radius: 16px;
            }

            .di-card h3 {
                font-size: 13px;
                line-height: 1.35;
            }

            .di-filter-grid,
            .di-kpi-grid {
                grid-template-columns: 1fr;
            }

            .di-summary-grid,
            .di-summary-budget-grid {
                grid-template-columns: 1fr;
            }

            .di-section-head {
                display: block;
            }

            .di-insight {
                margin-top: 12px;
            }

            .di-chart,
            .di-chart--short,
            .di-chart--tall {
                height: 300px;
                min-height: 300px;
            }

            .di-portfolio-charts .di-card,
            .di-funding-charts .di-card,
            .di-output-charts .di-card,
            .di-ops-charts .di-card,
            .di-workload-charts .di-card {
                min-height: 360px;
            }

            .di-portfolio-charts .di-chart,
            .di-funding-charts .di-chart,
            .di-output-charts .di-chart,
            .di-ops-charts .di-chart,
            .di-workload-charts .di-chart {
                min-height: 290px;
            }

            .di-card .di-filter-grid {
                grid-template-columns: 1fr !important;
            }

            .di-ops-charts .di-watch-card {
                width: calc(100vw - 24px);
                max-width: none;
                margin-left: calc(50% - 50vw + 12px);
                margin-right: calc(50% - 50vw + 12px);
                min-height: auto;
            }

            .di-watch-card .overflow-x-auto {
                overflow-x: visible;
            }

            .di-watch-table {
                min-width: 0;
                width: 100%;
            }

            .di-watch-table thead {
                display: none;
            }

            .di-watch-table,
            .di-watch-table tbody,
            .di-watch-table tr,
            .di-watch-table td {
                display: block;
                width: 100%;
            }

            .di-watch-table tr {
                margin-bottom: 10px;
                border: 1px solid #dbe5f1;
                border-radius: 14px;
                background: #ffffff;
                padding: 10px 11px;
                box-sizing: border-box;
                overflow: hidden;
            }

            .di-watch-table tr:last-child {
                margin-bottom: 0;
            }

            .di-watch-table td {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                border-bottom: 1px solid rgba(148, 163, 184, .22);
                padding: 8px 0;
                text-align: right;
                white-space: normal;
                overflow-wrap: anywhere;
            }

            .di-watch-table td:last-child {
                border-bottom: 0;
                padding-bottom: 0;
            }

            .di-watch-table td::before {
                content: attr(data-label);
                flex: 0 0 82px;
                color: #64748b;
                font-size: 11px;
                font-weight: 900;
                text-align: left;
            }

            .di-watch-table td:first-child {
                display: block;
                margin: -10px -11px 6px;
                padding: 10px 11px;
                background: #eff6ff;
                color: #1d4ed8;
                text-align: left;
                font-weight: 850;
            }

            .di-watch-table td:first-child::before {
                display: block;
                margin-bottom: 4px;
                color: currentColor;
                opacity: .72;
            }

            .di-portfolio-summary-list span,
            .di-funding-summary-list span,
            .di-output-summary-list span,
            .di-ops-summary-list span,
            .di-workload-summary-list span {
                display: grid;
                gap: 3px;
                justify-content: stretch;
            }

            .di-table {
                min-width: 620px;
                font-size: 12px;
            }

            .di-table.di-watch-table {
                min-width: 0;
                width: 100%;
            }

            .di-theme-summary-table {
                min-width: 0;
                table-layout: auto;
            }

            .di-theme-summary-table colgroup,
            .di-theme-summary-table thead {
                display: none;
            }

            .di-theme-summary-table,
            .di-theme-summary-table tbody,
            .di-theme-summary-table tr,
            .di-theme-summary-table td {
                display: block;
                width: 100%;
            }

            .di-theme-summary-table th:first-child,
            .di-theme-summary-table td:first-child,
            .di-theme-summary-table th:nth-child(2),
            .di-theme-summary-table td:nth-child(2),
            .di-theme-summary-table th:nth-child(3),
            .di-theme-summary-table td:nth-child(3),
            .di-theme-summary-table th:nth-child(4),
            .di-theme-summary-table td:nth-child(4) {
                width: 100%;
            }

            .di-theme-summary-table tr {
                margin-bottom: 10px;
                border: 1px solid #dbe5f1;
                border-radius: 14px;
                padding: 10px 11px;
                box-sizing: border-box;
                overflow: hidden;
            }

            .di-theme-summary-table tr:last-child {
                margin-bottom: 0;
            }

            .di-summary-content-theme-table tr {
                border-color: #bae6fd;
                background: #f0f9ff;
            }

            .di-summary-type-table tr {
                border-color: #bbf7d0;
                background: #f0fdf4;
            }

            .di-theme-summary-table tbody tr:nth-child(odd) td,
            .di-theme-summary-table tbody tr:nth-child(even) td {
                background: transparent;
            }

            .di-theme-summary-table td {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                border-bottom: 1px solid rgba(148, 163, 184, .22);
                padding: 8px 0;
                text-align: right;
                line-height: 1.45;
                white-space: normal;
                overflow-wrap: anywhere;
                box-sizing: border-box;
            }

            .di-theme-summary-table td:last-child {
                border-bottom: 0;
                padding-bottom: 0;
            }

            .di-theme-summary-table td::before {
                content: attr(data-label);
                flex: 0 0 104px;
                color: #64748b;
                font-size: 11px;
                font-weight: 900;
                text-align: left;
            }

            .di-theme-summary-table td:first-child {
                display: block;
                margin: -10px -11px 6px;
                padding: 10px 11px;
                text-align: left;
                font-weight: 850;
            }

            .di-summary-content-theme-table td:first-child {
                background: #e0f2fe !important;
                color: #075985;
            }

            .di-summary-type-table td:first-child {
                background: #dcfce7 !important;
                color: #047857;
            }

            .di-theme-summary-table td:first-child::before {
                display: block;
                margin-bottom: 4px;
                color: currentColor;
                opacity: .72;
            }

            .di-skill-capacity-head {
                display: block;
            }

            .di-skill-capacity-title {
                font-size: 13px;
                line-height: 1.5;
            }

            .di-skill-capacity-table-wrap {
                overflow-x: visible;
            }

            .di-skill-capacity-table {
                min-width: 0;
                font-size: 12px;
                table-layout: auto;
            }

            .di-skill-capacity-table colgroup,
            .di-skill-capacity-table thead {
                display: none;
            }

            .di-skill-capacity-table tbody,
            .di-skill-capacity-table tfoot,
            .di-skill-capacity-table tr,
            .di-skill-capacity-table td {
                display: block;
                width: 100%;
            }

            .di-skill-capacity-table tr {
                border-bottom: 1px solid #dbe5f1;
                padding: 9px 8px;
            }

            .di-skill-capacity-table tr:last-child {
                border-bottom: 0;
            }

            .di-skill-capacity-table th,
            .di-skill-capacity-table td {
                border-bottom: 0;
                padding: 5px 0;
            }

            .di-skill-capacity-table td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                text-align: right;
                white-space: normal;
            }

            .di-skill-capacity-table td::before {
                content: attr(data-label);
                flex: 0 0 118px;
                color: #64748b;
                font-size: 11px;
                font-weight: 900;
                text-align: left;
                text-transform: uppercase;
            }

            .di-skill-capacity-table td:nth-child(2) {
                display: block;
                text-align: left;
            }

            .di-skill-capacity-table td:nth-child(2)::before {
                display: block;
                margin-bottom: 4px;
            }

            .di-skill-capacity-table tfoot td {
                background: transparent;
            }

            .di-modal {
                align-items: stretch;
                padding: 8px;
            }

            .di-modal-panel {
                width: 100%;
                max-height: calc(100dvh - 16px);
                border-radius: 18px;
            }

            .di-modal-head,
            .di-modal-body {
                padding: 12px;
            }

            .di-modal-head {
                align-items: flex-start;
            }

            .di-modal-title {
                font-size: 16px;
            }

            .di-modal-subtitle {
                font-size: 12px;
            }

            .di-modal-summary {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .di-modal-kpi {
                padding: 9px 10px;
            }

            .di-modal-kpi strong {
                font-size: 16px;
            }

            .di-modal-table-wrap {
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }

            .di-modal-table {
                min-width: 0;
                width: 100%;
                table-layout: auto;
            }

            .di-modal-table thead {
                display: none;
            }

            .di-modal-table,
            .di-modal-table tbody,
            .di-modal-table tr,
            .di-modal-table td {
                display: block;
                width: 100% !important;
                max-width: 100%;
            }

            .di-modal-table tr {
                margin-bottom: 10px;
                border: 1px solid #dbe5f1;
                border-radius: 16px;
                background: #ffffff;
                padding: 10px 11px;
                box-sizing: border-box;
                overflow: hidden;
            }

            .di-modal-table tr:last-child {
                margin-bottom: 0;
            }

            .di-modal-table td {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                border-bottom: 1px solid rgba(148, 163, 184, .22);
                padding: 8px 0;
                text-align: right;
                white-space: normal;
                overflow-wrap: anywhere;
                box-sizing: border-box;
            }

            .di-modal-table td:last-child {
                border-bottom: 0;
            }

            .di-modal-table td::before {
                content: attr(data-label);
                flex: 0 0 92px;
                max-width: 42%;
                color: #64748b;
                font-size: 11px;
                font-weight: 900;
                text-align: left;
            }

            .di-modal-table td > *:not(:first-child),
            .di-modal-table td > a,
            .di-modal-table td {
                min-width: 0;
            }

            .di-modal-table td:first-child {
                display: block;
                width: auto !important;
                max-width: none;
                margin: -10px -11px 7px;
                padding: 11px;
                background: var(--modal-accent-bg) !important;
                color: var(--modal-accent-text);
                text-align: left;
                font-weight: 850;
            }

            .di-modal-table td:first-child::before {
                display: block;
                margin-bottom: 4px;
                color: currentColor;
                opacity: .72;
            }

            .di-author-row {
                grid-template-columns: 1fr;
                gap: 7px;
            }

            .di-author-head {
                display: none;
            }

            .di-author-name {
                white-space: normal;
            }

            .di-author-total {
                justify-self: start;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @if ($canDownloadDashboardData ?? false)
        <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    @endif

    <div class="di-page">
        <div class="di-overlay" id="diOverlay"></div>
        <div class="di-floating-actions" aria-label="Dashboard tools">
            <button id="openFilterPopup" type="button" class="di-fab"><span>≡</span>ฟิลเตอร์</button>
            @if ($canDownloadDashboardData ?? false)
                <button id="openDownloadPopup" type="button" class="di-fab"><span>↓</span>โหลด</button>
            @endif
        </div>
        @if ($canDownloadDashboardData ?? false)
        <aside id="downloadPopup" class="di-download-popup" aria-hidden="true">
            <div class="di-popup-head" style="display:flex;">
                <div>
                    <div class="di-tag">Download</div>
                    <h2 class="mt-2 text-lg font-extrabold">ดาวน์โหลดชุดข้อมูล</h2>
                </div>
                <button id="closeDownloadPopup" type="button" class="di-close" aria-label="ปิด">×</button>
            </div>
            <p id="downloadSummary" class="mt-2 text-sm leading-relaxed text-slate-600">ดาวน์โหลดข้อมูลตามตัวกรองปัจจุบันเป็น Excel หรือ CSV</p>
            <button id="downloadExcel" type="button" class="di-download-action">ดาวน์โหลด Excel</button>
            <button id="downloadCsv" type="button" class="di-download-action mt-3">ดาวน์โหลด CSV</button>
            <form id="yearlyProjectExport" class="mt-4 border-t border-slate-200 pt-4">
                <h3 class="font-extrabold text-slate-800">โหลดเพิ่มเติม · โครงการรายปี</h3>
                <p class="mt-2 text-xs leading-relaxed text-slate-600">รายชื่อในแท่งใหม่และ ongoing พร้อมทีมเฉพาะ SiHP ตามตัวกรองปัจจุบัน เลือกช่วงปี พ.ศ. ได้เอง</p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="text-xs font-bold">ปีเริ่มต้น (พ.ศ.)
                        <input id="yearlyExportFrom" type="number" value="2567" min="2400" step="1" required class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                    </label>
                    <label class="text-xs font-bold">ปีสิ้นสุด (พ.ศ.)
                        <input id="yearlyExportTo" type="number" value="2569" min="2400" step="1" required class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                    </label>
                </div>
                <p class="mt-2 text-xs leading-relaxed text-slate-500">หนึ่งแถวต่อโครงการ รวมทีมในช่องเดียว • โครงการที่เริ่มในปีนั้นแสดงเป็น “ใหม่” • รายชื่อทีมเป็นข้อมูลปัจจุบัน</p>
                <button id="downloadYearlyExcel" type="submit" class="di-download-action mt-3">Excel โครงการรายปี + ทีม SiHP</button>
                <p id="yearlyExportMessage" role="status" aria-live="polite" class="mt-2 text-xs leading-relaxed text-slate-600"></p>
            </form>
            <a href="{{ url('/dashboard?view=legacy') }}" class="mt-3 inline-flex w-full min-h-[44px] items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-700">เปิดหน้าเดิม</a>
        </aside>
        @endif
        <div id="chartDetailModal" class="di-modal" aria-hidden="true">
            <div class="di-modal-panel">
                <div class="di-modal-head">
                    <div>
                        <div id="chartDetailTitle" class="di-modal-title">รายละเอียดข้อมูล</div>
                        <div id="chartDetailSubtitle" class="di-modal-subtitle"></div>
                    </div>
                    <button id="chartDetailClose" type="button" class="di-close" aria-label="ปิด">×</button>
                </div>
                <div id="chartDetailBody" class="di-modal-body"></div>
            </div>
        </div>
        <main class="di-shell">
            <section class="di-panel di-hero">
                <div class="di-kicker">Research Command Dashboard</div>
                <h1 class="di-title">ภาพรวมโครงการที่เกิดขึ้น (Dashboard)</h1>
                <div id="activeFilterPills" class="di-pill-row mt-4"></div>
            </section>

            <section id="dashboardFilterPanel" class="di-panel di-section di-filter-popup">
                <div class="di-popup-head">
                    <div>
                        <div class="di-tag">Filters</div>
                        <h2>ตัวกรองข้อมูล</h2>
                    </div>
                    <button id="closeFilterPopup" type="button" class="di-close" aria-label="ปิด">×</button>
                </div>
                <div class="di-section-head">
                    <div>
                        <div class="di-tag">Filters</div>
                        <h2>เลือกชุดข้อมูลที่ต้องการวิเคราะห์</h2>
                    </div>
                    <div id="filterSummary" class="di-insight">กำลังแสดงข้อมูลทั้งหมดในระบบ</div>
                </div>
                <div class="di-filter-grid">
                    <label class="di-field">
                        <span>ปีโครงการ</span>
                        <select id="filterYear">
                            <option value="">ทั้งหมด</option>
                        </select>
                    </label>
                    <label class="di-field">
                        <span>ลักษณะโครงการ (TYPE)</span>
                        <select id="filterTheme">
                            <option value="">ทั้งหมด</option>
                            @foreach ($themes as $theme)
                                <option value="{{ $theme }}">{{ $theme }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="di-field">
                        <span>กลุ่มเนื้อหาหลักของงาน (Content/Theme)</span>
                        <select id="filterType">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeOptions as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="di-field">
                        <span>ทุน</span>
                        <select id="filterFunding">
                            <option value="">ทั้งหมด</option>
                            <option value="ภายใน">ภายใน</option>
                            <option value="ภายนอก">ภายนอก</option>
                        </select>
                    </label>
                    <label class="di-field">
                        <span>สถานะ</span>
                        <select id="filterStatus">
                            <option value="">ทั้งหมด</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="di-field">
                        <span>ค้นหา</span>
                        <input id="filterSearch" type="search" placeholder="ชื่อโครงการ / PI / แหล่งทุน">
                    </label>
                    <label class="di-field">
                        <span>แหล่งทุน</span>
                        <select id="filterSource">
                            <option value="">ทั้งหมด</option>
                            @foreach ($fundingSources as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button id="resetFilters" type="button" class="di-reset">ล้างตัวกรอง</button>
                </div>
            </section>

            <section class="di-panel di-section di-summary-panel">
                <div class="di-section-head">
                    <div>
                        <div class="di-tag">Realtime Summary</div>
                        <h2>ภาพรวมโครงการวิจัย</h2>
                        <p class="mt-2 text-sm font-semibold text-slate-600">เอกสารสรุปข้อมูลทั้งหมดตามตัวกรองปัจจุบัน</p>
                    </div>
                </div>
                <div class="di-kpi-grid di-summary-grid">
                    <div class="di-kpi"><span>โครงการ</span><strong id="heroSummaryProjects">0</strong><small>ทั้งหมดใน scope</small></div>
                    <div class="di-kpi"><span>หัวหน้าโครงการ</span><strong id="heroSummaryPi">0 ท่าน</strong><small>จำนวน PI</small></div>
                    <div class="di-kpi"><span>ผู้วิจัยร่วม</span><strong id="heroSummaryCopi">0 ท่าน</strong><small>Co-PI ไม่ซ้ำ</small></div>
                    <div class="di-kpi"><span>ผู้ร่วมวิจัย</span><strong id="heroSummaryCoResearch">0 ท่าน</strong><small>Co-Research ไม่ซ้ำ</small></div>
                            <div class="di-kpi"><span>รวมจำนวนโครงการ</span><strong id="heroSummaryOutputs">0</strong><small>Outputs</small></div>
                </div>
                <div class="di-kpi-grid di-summary-budget-grid">
                    <div class="di-kpi"><span>งบประมาณรวม</span><strong id="rtSummaryBudget">0 บ.</strong><small>รวมเฉพาะข้อมูลใน scope</small></div>
                    <div class="di-kpi"><span>งบเฉลี่ย/โครงการ</span><strong id="rtSummaryAvgBudget">0 บ.</strong><small>ไม่นับโครงการไม่มีงบ</small></div>
                            <div class="di-kpi"><span>โครงการที่มีคนในหน่วย SIHP เป็น PI</span><strong id="rtSummaryPolicyPiBudget">0 บ.</strong><small id="rtSummaryPolicyPiCount">0 โครงการที่มีงบ</small></div>
                </div>
                <h3 class="di-summary-table-title">ตารางสรุปตามกลุ่มเนื้อหาหลักของงาน (Content/Theme)</h3>
                <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                    <table class="di-table di-theme-summary-table di-summary-content-theme-table">
                        <colgroup>
                            <col style="width: 56%;">
                            <col style="width: 14%;">
                            <col style="width: 14%;">
                            <col style="width: 16%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>กลุ่มเนื้อหาหลักของงาน (Content/Theme)</th>
                                <th class="text-right">จำนวนโครงการ</th>
                                <th class="text-right">สัดส่วน</th>
                                <th class="text-right">งบรวม</th>
                            </tr>
                        </thead>
                        <tbody id="rtContentThemeSummaryBody"></tbody>
                    </table>
                </div>
                <h3 class="di-summary-table-title">ตารางสรุปตามลักษณะโครงการ (TYPE)</h3>
                <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                    <table class="di-table di-theme-summary-table di-summary-type-table">
                        <colgroup>
                            <col style="width: 56%;">
                            <col style="width: 14%;">
                            <col style="width: 14%;">
                            <col style="width: 16%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>ลักษณะโครงการ (TYPE)</th>
                                <th class="text-right">จำนวนโครงการ</th>
                                <th class="text-right">สัดส่วน</th>
                                <th class="text-right">งบรวม</th>
                            </tr>
                        </thead>
                        <tbody id="rtTypeSummaryBody"></tbody>
                    </table>
                </div>
            </section>

            <section class="di-panel di-section">
                <div class="di-section-head">
                    <div>
                        <div class="di-tag">Portfolio</div>
                        <h2>สถานะโครงการและจังหวะการเติบโต</h2>
                    </div>
                </div>
                <div class="di-portfolio-layout">
                    <div class="di-portfolio-charts">
                        <article class="di-card" data-portfolio-key="yearly">
                            <h3>📈 จำนวนโครงการรายปี (รวม)</h3>
                            <div class="di-chart"><canvas id="chartProjectsPerYear"></canvas></div>
                            <div id="chartProjectsPerYearNote" class="di-chart-note"></div>
                        </article>
                        <article class="di-card" data-portfolio-key="lifecycle">
                            <h3>📈 โครงการรายปี: ทั้งหมด ใหม่ กำลังดำเนินการ ปิด/ยกเลิก</h3>
                            <div class="di-chart"><canvas id="chartLifecycle"></canvas></div>
                            <div id="chartLifecycleNote" class="di-chart-note"></div>
                        </article>
                        <article class="di-card" data-portfolio-key="status">
                            <h3>🧩 สถานะโครงการ</h3>
                            <div class="di-chart"><canvas id="chartStatus"></canvas></div>
                        </article>
                        <article class="di-card" data-portfolio-key="contract">
                            <h3>🔥 ภาพรวมสถานะโครงการตามสัญญา</h3>
                            <div class="di-chart"><canvas id="chartContractOverview"></canvas></div>
                        </article>
                        <article class="di-card" data-portfolio-key="red">
                            <h3>🔴 RED ZONE: ย้อนหลัง 3 ปี</h3>
                            <div class="di-chart"><canvas id="chartRedZoneTimeline"></canvas></div>
                        </article>
                        <article class="di-card" data-portfolio-key="collab">
                            <h3>🤝 สัดส่วนความร่วมมือ</h3>
                            <div class="di-chart"><canvas id="chartCollab"></canvas></div>
                        </article>
                        <article class="di-card" data-portfolio-key="pi-unit">
                            <h3>👨‍🔬 จำนวนงานของ PI ตามหน่วยงาน</h3>
                            <div class="di-chart"><canvas id="chartPiUnit"></canvas></div>
                        </article>
                    </div>
                    <aside class="di-portfolio-aside">
                        <div class="di-portfolio-aside-title">Analysis Insight</div>
                        <div class="di-portfolio-aside-subtitle">สรุปภาพรวมจากกราฟใน section สถานะโครงการและจังหวะการเติบโต</div>
                        <div id="portfolioSummaryList" class="di-portfolio-summary-list"></div>
                        <div class="di-analysis-card">
                            <strong>เกิดอะไรขึ้น</strong>
                            <span id="portfolioWhat">กำลังประมวลผลภาพรวมโครงการ</span>
                        </div>
                        <div class="di-analysis-card">
                            <strong>ภาพสะท้อนของหน่วยงาน</strong>
                            <span id="portfolioReflection">กำลังประมวลผลภาพสะท้อนของหน่วยงาน</span>
                        </div>
                    <div class="di-ai-insight-note">เป็นการใช้ AI ช่วยวิเคราะห์เบื้องต้น อันเกิดจาก data ของหน่วย และยังต้องอาศัยการร่วมกันคิดของทีมเพื่อให้ได้ข้อสรุปที่ชัดเจน</div>
                    </aside>
                </div>
            </section>

            <section class="di-panel di-section">
                <div class="di-section-head">
                    <div>
                        <div class="di-tag">Funding</div>
                        <h2>งบประมาณและแหล่งทุน</h2>
                    </div>
                </div>
                <div class="di-funding-layout">
                    <div class="di-funding-charts">
                        <article class="di-card">
                            <h3>💰 งบประมาณที่ได้รับ (รวมรายปี)</h3>
                            <div class="di-chart"><canvas id="chartBudgetYear"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>💰 งบประมาณที่ได้รับ (รวมรายปี) เฉพาะโครงการที่หน่วยวิจัยฯ เป็น PI หลัก</h3>
                            <div class="di-chart"><canvas id="chartBudgetPolicyPiYear"></canvas></div>
                        </article>
                        <article class="di-card di-card--people-budget">
                            <h3>💰 งบประมาณที่ได้รับแบ่งจากรายชื่อผู้วิจัยหลัก (PI / Co-PI)</h3>
                            <div class="di-chart di-chart--people-budget"><canvas id="chartBudgetPeople"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>🏦 งบประมาณ ภายใน / ภายนอกที่ได้รับ</h3>
                            <div class="di-chart"><canvas id="chartFundingSplit"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>🏦 แนวโน้มงบภายนอก / ภายใน รายปี</h3>
                            <div class="di-chart"><canvas id="chartFundingTypeYear"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>🏛️ Top แหล่งทุน</h3>
                            <div class="di-chart"><canvas id="chartFundingSource"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>🏦 ทุนภายนอกตามแหล่งทุน</h3>
                            <div class="di-chart"><canvas id="chartExternalBySource"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>🏛️ ทุนภายในตามแหล่งทุน</h3>
                            <div class="di-chart"><canvas id="chartInternalBySource"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>🏦 ทุนภายนอกตามแหล่งทุน: แนวโน้มรายปี</h3>
                            <div class="di-chart"><canvas id="chartExternalBySourceYear"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>🏛️ ทุนภายในตามแหล่งทุน: แนวโน้มรายปี</h3>
                            <div class="di-chart"><canvas id="chartInternalBySourceYear"></canvas></div>
                        </article>
                    </div>
                    <aside class="di-funding-aside">
                        <div class="di-funding-aside-title">Funding Insight</div>
                        <div class="di-funding-aside-subtitle">สรุปภาพรวมจากกราฟใน section งบประมาณและแหล่งทุน</div>
                        <div id="fundingSummaryList" class="di-funding-summary-list"></div>
                        <div class="di-analysis-card">
                            <strong>เกิดอะไรขึ้น</strong>
                            <span id="fundingWhat">กำลังประมวลผลงบประมาณ</span>
                        </div>
                        <div class="di-analysis-card">
                            <strong>ภาพสะท้อนของหน่วยงาน</strong>
                            <span id="fundingReflection">กำลังประมวลผลภาพสะท้อนด้านแหล่งทุน</span>
                        </div>
                    <div class="di-ai-insight-note">เป็นการใช้ AI ช่วยวิเคราะห์เบื้องต้น อันเกิดจาก data ของหน่วย และยังต้องอาศัยการร่วมกันคิดของทีมเพื่อให้ได้ข้อสรุปที่ชัดเจน</div>
                    </aside>
                </div>
            </section>

            <section class="di-panel di-section">
                <div class="di-section-head">
                    <div>
                        <div class="di-tag">Outputs</div>
                        <h2>ผลงานและบทบาทผู้เขียน</h2>
                    </div>
                </div>
                <div class="di-output-layout">
                    <div class="di-output-charts">
                        <article class="di-card">
                            <h3>📝 Output ตามประเภท</h3>
                            <div class="di-chart"><canvas id="chartOutputType"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>📝 Output แยกประเภท (รายปี)</h3>
                            <div class="di-chart"><canvas id="chartOutputStacked"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>📚 Publication รายปี</h3>
                            <div class="di-chart"><canvas id="chartPublicationYear"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>✍️ บทบาทผู้เขียนผลงานวิชาการรายปี (First Author / Co-author / Corresponding Author)</h3>
                            <div class="mb-3 di-filter-grid" style="grid-template-columns: 1fr 1fr;">
                                <label class="di-field"><span>ปีผลงาน</span><select id="authorYear"></select></label>
                                <label class="di-field"><span>ค้นหาชื่อ</span><input id="authorSearch" type="search" placeholder="พิมพ์บางส่วนของชื่อ"></label>
                                <input id="authorTop" type="hidden" value="5">
                            </div>
                            <div class="di-author-legend mb-2">
                                <span><i class="first"></i>First Author</span>
                                <span><i class="co"></i>Co-author</span>
                                <span><i class="corresponding"></i>Corresponding Author</span>
                            </div>
                            <div id="authorBoard" class="di-author-board"></div>
                        </article>
                    </div>
                    <aside class="di-output-aside">
                        <div class="di-output-aside-title">Output Insight</div>
                        <div class="di-output-aside-subtitle">สรุปภาพรวมจากกราฟใน section ผลงานและบทบาทผู้เขียน</div>
                        <div id="outputSummaryList" class="di-output-summary-list"></div>
                        <div class="di-analysis-card">
                            <strong>เกิดอะไรขึ้น</strong>
                            <span id="outputWhat">กำลังประมวลผลผลงาน</span>
                        </div>
                        <div class="di-analysis-card">
                            <strong>ภาพสะท้อนของหน่วยงาน</strong>
                            <span id="outputReflection">กำลังประมวลผลบทบาทผู้เขียน</span>
                        </div>
                    <div class="di-ai-insight-note">เป็นการใช้ AI ช่วยวิเคราะห์เบื้องต้น อันเกิดจาก data ของหน่วย และยังต้องอาศัยการร่วมกันคิดของทีมเพื่อให้ได้ข้อสรุปที่ชัดเจน</div>
                    </aside>
                </div>
            </section>

            <section class="di-panel di-section">
                <div class="di-section-head">
                    <div>
                        <div class="di-tag">Operations</div>
                        <h2>ความร่วมมือ การติดตาม และ IRB</h2>
                    </div>
                </div>
                <div class="di-ops-layout">
                    <div class="di-ops-charts">
                        <article class="di-card">
                            <h3>📍 Co-Research ตามหน่วยงาน (3/6/12 เดือน)</h3>
                            <div class="di-chart"><canvas id="chartCoResearch"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>⚠️ IRB ใกล้หมดอายุ 6 เดือน</h3>
                            <div class="di-chart"><canvas id="chartIrb"></canvas></div>
                        </article>
                        <article class="di-card">
                            <h3>🔴 โครงการเกินกำหนดตามสัญญา/ทีมวิจัย</h3>
                            <div class="di-chart"><canvas id="chartOverdue"></canvas></div>
                        </article>
                        <article class="di-card di-watch-card">
                            <h3>รายการโครงการที่ควรติดตาม</h3>
                            <div class="overflow-x-auto">
                                <table class="di-table di-watch-table">
                                    <thead>
                                        <tr>
                                            <th>โครงการ</th>
                                            <th>PI</th>
                                            <th>ปี</th>
                                            <th>สถานะ</th>
                                            <th>หมายเหตุ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="watchTable"></tbody>
                                </table>
                            </div>
                        </article>
                    </div>
                    <aside class="di-ops-aside">
                        <div class="di-ops-aside-title">Operation Insight</div>
                        <div class="di-ops-aside-subtitle">สรุปภาพรวมจากกราฟใน section ความร่วมมือ การติดตาม และ IRB</div>
                        <div id="opsSummaryList" class="di-ops-summary-list"></div>
                        <div class="di-analysis-card">
                            <strong>เกิดอะไรขึ้น</strong>
                            <span id="opsWhat">กำลังประมวลผลการติดตาม</span>
                        </div>
                        <div class="di-analysis-card">
                            <strong>ภาพสะท้อนของหน่วยงาน</strong>
                            <span id="opsReflection">กำลังประมวลผลภาระประสานงาน</span>
                        </div>
                    <div class="di-ai-insight-note">เป็นการใช้ AI ช่วยวิเคราะห์เบื้องต้น อันเกิดจาก data ของหน่วย และยังต้องอาศัยการร่วมกันคิดของทีมเพื่อให้ได้ข้อสรุปที่ชัดเจน</div>
                    </aside>
                </div>
            </section>

            <section class="di-panel di-section">
                <div class="di-section-head">
                    <div>
                        <div class="di-tag">Workload</div>
                        <h2>ภาระงานที่เกิดขึ้น</h2>
                    </div>
                </div>
                <div class="di-workload-layout">
                    <div class="di-workload-charts">
                        <article class="di-card">
                            <h3>🫧 FTE รวมต่อคน</h3>
                            <div class="fte-avatar-chart-shell">
                                <canvas id="chartFtePeople"></canvas>
                                <div class="fte-avatar-layer" id="chartFtePeopleAvatarLayer" aria-hidden="true"></div>
                            </div>
                        </article>
                        <article class="di-card di-skill-capacity-card">
                            <div class="di-skill-capacity-head">
                                <div>
                                    <div class="di-skill-capacity-kicker">Skill set capacity</div>
                                    <div class="di-skill-capacity-title">ภาพรวมของการกระจายคน (Workforce), เวลาการทำงาน (Time Spending) เเละภาระงาน (Tasks) ตามกลุ่มทักษะ</div>
                                </div>
                            </div>
                            <div class="di-skill-capacity-table-wrap">
                                <table class="di-skill-capacity-table di-skill-capacity-table--skill">
                                    <colgroup>
                                        <col style="width:44px;">
                                        <col style="width:28%;">
                                        <col style="width:10%;">
                                        <col style="width:12%;">
                                        <col style="width:10%;">
                                        <col style="width:11%;">
                                        <col style="width:12%;">
                                        <col style="width:13%;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="di-skill-capacity-table__num"></th>
                                            <th rowspan="2">Skill set</th>
                                            <th colspan="3" class="di-skill-capacity-table__num di-skill-capacity-table__group di-skill-capacity-table__group--current">Current Situation (สถานการณ์ทำงานจริง)</th>
                                            <th colspan="3" class="di-skill-capacity-table__num di-skill-capacity-table__group">Position (กรอบอัตรากำลังตามแผน)</th>
                                        </tr>
                                        <tr>
                                            <th class="di-skill-capacity-table__num">Workforce</th>
                                            <th class="di-skill-capacity-table__num">Time Spent (%)</th>
                                            <th class="di-skill-capacity-table__num">Tasks (%)</th>
                                            <th class="di-skill-capacity-table__num">Position</th>
                                            <th class="di-skill-capacity-table__num">Position (%)</th>
                                            <th class="di-skill-capacity-table__num">Position ที่ยืมตัวมา</th>
                                        </tr>
                                    </thead>
                                    <tbody id="skillCapacityBody">
                                        <tr>
                                            <td colspan="8" class="di-skill-capacity-table__num">กำลังประมวลผลข้อมูล</td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="skillCapacityFoot"></tfoot>
                                </table>
                            </div>
                            <div class="di-skill-capacity-note">
                                หมายเหตุ: ตัวเลขนี้เป็นการคำนวณในภาพรวมแบบค่าเฉลี่ย จึงอาจมีข้อจำกัดในการสะท้อนภาระงานจริงรายบุคคล (เช่น กรณีที่มีบุคลากรทำงานหนักเกินเกณฑ์แต่ถูกค่าเฉลี่ยหารลดลง) แนะนำให้ตรวจสอบรายละเอียดเพิ่มเติมจากดัชนี FTE (Full-Time Equivalent) รายบุคคล และอีกสองตารางด้านล่าง
                            </div>
                        </article>
                    </div>
                    <aside class="di-workload-aside">
                        <div class="di-workload-aside-title">Workload Insight</div>
                        <div class="di-workload-aside-subtitle">สรุปภาพรวมจากกราฟใน section ภาระงานที่เกิดขึ้น</div>
                        <div id="workloadSummaryList" class="di-workload-summary-list"></div>
                        <div class="di-analysis-card">
                            <strong>เกิดอะไรขึ้น</strong>
                            <span id="workloadWhat">กำลังประมวลผล FTE</span>
                        </div>
                        <div class="di-analysis-card">
                            <strong>ภาพสะท้อนของหน่วยงาน</strong>
                            <span id="workloadReflection">กำลังประมวลผลการกระจายภาระงาน</span>
                        </div>
                    <div class="di-ai-insight-note">เป็นการใช้ AI ช่วยวิเคราะห์เบื้องต้น อันเกิดจาก data ของหน่วย และยังต้องอาศัยการร่วมกันคิดของทีมเพื่อให้ได้ข้อสรุปที่ชัดเจน</div>
                    </aside>
                </div>
            </section>
        </main>
    </div>

    <script>
        (() => {
            const projects = @json($dashboardProjectsJs);
            const dashboardFteSkillAnalysis = @json($dashboardFteSkillAnalysis ?? []);
            const fteProfileUrls = @json($fteProfileUrls ?? []);
            const externalOutputs = @json($externalOutputsData ?? []);
            const backOfficeRows = @json($backOfficeData ?? []);
            const canDownloadDashboardData = @json($canDownloadDashboardData ?? false);
            const chartMap = new Map();
            const COLORS = ['#2563eb', '#14b8a6', '#d99b2b', '#ef4444', '#22c55e', '#7c3aed', '#06b6d4', '#94a3b8'];
            const FTE_AVATAR_COLORS = ['#0f766e', '#2563eb', '#7c3aed', '#ea580c', '#0891b2', '#65a30d', '#db2777', '#dc2626'];
            const fteAvatarCache = new Map();
            const FUNDING_COLORS = {
                total: '#2563eb',
                policyPi: '#0f766e',
                internal: '#14b8a6',
                external: '#f59e0b',
                unknown: '#94a3b8',
                pi: '#2563eb',
                copi: '#8b5cf6',
            };
            const FUNDING_SOURCE_COLORS = ['#2563eb', '#14b8a6', '#f59e0b', '#ef4444', '#22c55e', '#8b5cf6', '#06b6d4', '#64748b', '#d946ef', '#84cc16'];
            const fmt = new Intl.NumberFormat('th-TH');
            const money = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 2 });
            const oneDecimal = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
            const CURRENT_BE_YEAR = new Date().getFullYear() + 543;
            const PREVIOUS_BE_YEAR = CURRENT_BE_YEAR - 1;
            const PREVIOUS_PREVIOUS_BE_YEAR = CURRENT_BE_YEAR - 2;
            const CURRENT_BE_YEAR_LABEL = String(CURRENT_BE_YEAR);
            const PREVIOUS_BE_YEAR_LABEL = String(PREVIOUS_BE_YEAR);
            const PREVIOUS_PREVIOUS_BE_YEAR_LABEL = String(PREVIOUS_PREVIOUS_BE_YEAR);
            const CURRENT_YEAR_RANGE_LABEL = `${PREVIOUS_PREVIOUS_BE_YEAR_LABEL}-${CURRENT_BE_YEAR_LABEL}`;
            const els = {
                year: document.getElementById('filterYear'),
                theme: document.getElementById('filterTheme'),
                type: document.getElementById('filterType'),
                funding: document.getElementById('filterFunding'),
                status: document.getElementById('filterStatus'),
                source: document.getElementById('filterSource'),
                search: document.getElementById('filterSearch'),
                reset: document.getElementById('resetFilters'),
                authorYear: document.getElementById('authorYear'),
                authorSearch: document.getElementById('authorSearch'),
                authorTop: document.getElementById('authorTop'),
                overlay: document.getElementById('diOverlay'),
                filterPanel: document.getElementById('dashboardFilterPanel'),
                openFilter: document.getElementById('openFilterPopup'),
                closeFilter: document.getElementById('closeFilterPopup'),
                downloadPopup: document.getElementById('downloadPopup'),
                openDownload: document.getElementById('openDownloadPopup'),
                closeDownload: document.getElementById('closeDownloadPopup'),
                downloadCsv: document.getElementById('downloadCsv'),
                downloadExcel: document.getElementById('downloadExcel'),
                detailModal: document.getElementById('chartDetailModal'),
                detailClose: document.getElementById('chartDetailClose'),
                detailTitle: document.getElementById('chartDetailTitle'),
                detailSubtitle: document.getElementById('chartDetailSubtitle'),
                detailBody: document.getElementById('chartDetailBody'),
                portfolioWhat: document.getElementById('portfolioWhat'),
                portfolioReflection: document.getElementById('portfolioReflection'),
                portfolioSummaryList: document.getElementById('portfolioSummaryList'),
                fundingWhat: document.getElementById('fundingWhat'),
                fundingReflection: document.getElementById('fundingReflection'),
                fundingSummaryList: document.getElementById('fundingSummaryList'),
                outputWhat: document.getElementById('outputWhat'),
                outputReflection: document.getElementById('outputReflection'),
                outputSummaryList: document.getElementById('outputSummaryList'),
            };

            Chart.defaults.font.family = "'IBM Plex Sans Thai', sans-serif";
            Chart.defaults.responsive = true;
            Chart.defaults.maintainAspectRatio = false;
            Chart.defaults.plugins.legend.labels.boxWidth = 14;

            const valueLabelPlugin = {
                id: 'diValueLabel',
                afterDatasetsDraw(chart, args, options) {
                    if (options?.display === false) return;
                    const ctx = chart.ctx;
                    const formatter = typeof options?.formatter === 'function'
                        ? options.formatter
                        : (value) => fmt.format(Number(value) || 0);
                    ctx.save();
                    ctx.font = options?.font || '800 11px "IBM Plex Sans Thai", sans-serif';
                    ctx.fillStyle = options?.color || '#0f172a';
                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        if (!chart.isDatasetVisible(datasetIndex)) return;
                        const meta = chart.getDatasetMeta(datasetIndex);
                        meta.data.forEach((element, index) => {
                            const raw = Number(dataset.data?.[index] || 0);
                            if (!(raw > 0)) return;
                            const textValue = formatter(raw, { chart, dataset, datasetIndex, index });
                            if (!textValue) return;
                            const props = element.tooltipPosition();
                            const isHorizontal = chart.options?.indexAxis === 'y';
                            const isArc = element.constructor?.id === 'arc';
                            ctx.textAlign = isArc ? 'center' : (isHorizontal ? 'left' : 'center');
                            ctx.textBaseline = 'middle';
                            let x = props.x;
                            let y = props.y;
                            if (isHorizontal) x += 8;
                            else if (!isArc) y -= 12;
                            if (isArc) {
                                const angle = (element.startAngle + element.endAngle) / 2;
                                x = element.x + Math.cos(angle) * ((element.innerRadius + element.outerRadius) / 2);
                                y = element.y + Math.sin(angle) * ((element.innerRadius + element.outerRadius) / 2);
                            }
                            ctx.fillText(textValue, x, y);
                        });
                    });
                    ctx.restore();
                }
            };
            const stackTotalLabelPlugin = {
                id: 'diStackTotalLabel',
                afterDatasetsDraw(chart, args, options) {
                    if (options?.display !== true) return;
                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.font = options.font || '900 11px "IBM Plex Sans Thai", sans-serif';
                    ctx.fillStyle = options.color || '#0f172a';
                    const isHorizontal = chart.options?.indexAxis === 'y';
                    ctx.textAlign = isHorizontal ? 'left' : 'center';
                    ctx.textBaseline = isHorizontal ? 'middle' : 'bottom';
                    const labels = chart.data.labels || [];
                    labels.forEach((_, index) => {
                        let total = 0;
                        let anchorElement = null;
                        chart.data.datasets.forEach((dataset, datasetIndex) => {
                            if (!chart.isDatasetVisible(datasetIndex)) return;
                            const value = Number(dataset.data?.[index] || 0);
                            if (!(value > 0)) return;
                            total += value;
                            const element = chart.getDatasetMeta(datasetIndex)?.data?.[index];
                            if (!element) return;
                            if (isHorizontal) {
                                if (!anchorElement || element.x > anchorElement.x) anchorElement = element;
                            } else if (!anchorElement || element.y < anchorElement.y) {
                                anchorElement = element;
                            }
                        });
                        if (!anchorElement || !(total > 0)) return;
                        const label = options.formatter ? options.formatter(total, { chart, index, total }) : fmt.format(total);
                        if (!label) return;
                        if (isHorizontal) {
                            const x = Math.min(anchorElement.x + 10, chart.chartArea.right - ctx.measureText(label).width - 2);
                            ctx.fillText(label, x, anchorElement.y);
                        } else {
                            ctx.fillText(label, anchorElement.x, Math.max(anchorElement.y - 4, chart.chartArea.top + 12));
                        }
                    });
                    ctx.restore();
                }
            };
            const doughnutCalloutPlugin = {
                id: 'diDoughnutCallout',
                afterDatasetsDraw(chart, args, options) {
                    if (options?.display !== true) return;
                    const meta = chart.getDatasetMeta(0);
                    const dataset = chart.data.datasets?.[0];
                    if (!meta?.data?.length || !dataset) return;
                    const formatter = typeof options.formatter === 'function'
                        ? options.formatter
                        : (value) => fmt.format(value);
                    const { ctx, chartArea: { left, right, top, bottom } } = chart;
                    ctx.save();
                    ctx.font = options.font || '900 11px "IBM Plex Sans Thai", sans-serif';
                    ctx.fillStyle = options.color || '#0f172a';
                    ctx.strokeStyle = options.lineColor || 'rgba(100,116,139,0.52)';
                    ctx.lineWidth = 1;
                    ctx.textBaseline = 'middle';

                    const callouts = meta.data.map((arc, index) => {
                        const value = Number(dataset.data?.[index] || 0);
                        if (!(value > 0)) return null;
                        const label = formatter(value, { chart, index });
                        if (!label) return null;
                        const angle = (arc.startAngle + arc.endAngle) / 2;
                        const side = Math.cos(angle) >= 0 ? 'right' : 'left';
                        const sx = arc.x + Math.cos(angle) * (arc.outerRadius - 1);
                        const sy = arc.y + Math.sin(angle) * (arc.outerRadius - 1);
                        const mx = arc.x + Math.cos(angle) * (arc.outerRadius + 14);
                        const targetY = arc.y + Math.sin(angle) * (arc.outerRadius + 14);
                        return { label, side, sx, sy, mx, targetY, y: targetY, width: ctx.measureText(label).width };
                    }).filter(Boolean);

                    ['left', 'right'].forEach((side) => {
                        const items = callouts.filter((item) => item.side === side).sort((a, b) => a.targetY - b.targetY);
                        const gap = Number(options.gap || 18);
                        let cursor = top + 12;
                        items.forEach((item) => {
                            item.y = Math.max(Math.min(item.targetY, bottom - 12), cursor);
                            cursor = item.y + gap;
                        });
                        const overflow = (items[items.length - 1]?.y || 0) - (bottom - 12);
                        if (overflow > 0) {
                            cursor = bottom - 12;
                            items.slice().reverse().forEach((item) => {
                                item.y = Math.min(item.y - overflow, cursor);
                                item.y = Math.max(item.y, top + 12);
                                cursor = item.y - gap;
                            });
                        }
                    });

                    callouts.forEach((item) => {
                        const isRight = item.side === 'right';
                        let tx = item.mx + (isRight ? 16 : -16);
                        if (isRight) {
                            tx = Math.min(Math.max(tx, left + 4), right - item.width - 4);
                            ctx.textAlign = 'left';
                        } else {
                            tx = Math.max(Math.min(tx, right - 4), left + item.width + 4);
                            ctx.textAlign = 'right';
                        }
                        ctx.beginPath();
                        ctx.moveTo(item.sx, item.sy);
                        ctx.lineTo(item.mx, item.y);
                        ctx.lineTo(tx + (isRight ? -5 : 5), item.y);
                        ctx.stroke();
                        ctx.fillText(item.label, tx, item.y);
                    });
                    ctx.restore();
                }
            };
            const fteAvatarBubblePlugin = {
                id: 'fteAvatarBubblePlugin',
                afterDatasetsDraw(chart) {
                    if (chart?.canvas?.id !== 'chartFtePeople') return;
                    scheduleFteAvatarLayerSync(chart);
                },
                resize(chart) {
                    if (chart?.canvas?.id !== 'chartFtePeople') return;
                    scheduleFteAvatarLayerSync(chart);
                },
                afterDestroy(chart) {
                    if (chart?._fteAvatarSyncFrame) cancelAnimationFrame(chart._fteAvatarSyncFrame);
                    clearFteAvatarLayer(chart);
                }
            };
            Chart.register(valueLabelPlugin, stackTotalLabelPlugin, doughnutCalloutPlugin, fteAvatarBubblePlugin);

            const text = (v, fallback = '') => String(v ?? '').trim() || fallback;
            const norm = (v) => text(v).toLowerCase();
            const num = (v) => {
                if (v === null || v === undefined || v === '') return 0;
                if (typeof v === 'number') return Number.isFinite(v) ? v : 0;
                const cleaned = String(v).replace(/[^0-9.\-]/g, '');
                const parsed = Number(cleaned);
                return Number.isFinite(parsed) ? parsed : 0;
            };
            function stripPersonHonorifics(value) {
                let output = text(value);
                const pattern = /^(?:นายแพทย์หญิง|นายแพทย์|แพทย์หญิง|แพทย์|ทันตแพทย์หญิง|ทันตแพทย์|เภสัชกรหญิง|เภสัชกร|ศ\.คลินิก|ศ\.นพ\.|ศ\.พญ\.|รศ\.นพ\.|รศ\.พญ\.|ผศ\.นพ\.|ผศ\.พญ\.|ศ\.|รศ\.|ผศ\.|อ\.|ดร\.?|ภกญ\.|ภญ\.|ภก\.|พญ\.|นพ\.|ทพ\.ญ\.|ทพ\.|นาย|นางสาว|นาง)\s*/u;
                while (output && pattern.test(output)) output = output.replace(pattern, '').trim();
                return output.replace(/\s+/gu, ' ').trim();
            }
            function normalizePersonKey(value) {
                return stripPersonHonorifics(value).replace(/\s+/gu, ' ').trim().toLowerCase();
            }
            function buildPersonLookupKeys(value) {
                const raw = text(value);
                const stripped = stripPersonHonorifics(raw);
                const tokens = normalizePersonKey(stripped).split(/\s+/u).filter(Boolean);
                const variants = [
                    raw,
                    stripped,
                    raw.replace(/\s+/gu, ''),
                    stripped.replace(/\s+/gu, ''),
                    tokens[0] || '',
                    tokens.length > 1 ? `${tokens[0]} ${tokens[tokens.length - 1]}` : '',
                    tokens.length > 1 ? `${tokens[0]}${tokens[tokens.length - 1]}` : '',
                ];
                return Array.from(new Set(
                    variants
                        .flatMap((candidate) => [
                            text(candidate).replace(/\s+/gu, ' ').trim().toLowerCase(),
                            text(candidate).replace(/\s+/gu, '').trim().toLowerCase(),
                        ])
                        .filter(Boolean)
                ));
            }
            function personInitials(name) {
                const tokens = text(name).split(/\s+/u).filter(Boolean);
                if (!tokens.length) return '?';
                if (tokens.length === 1) return Array.from(tokens[0]).slice(0, 2).join('');
                return `${Array.from(tokens[0])[0] || ''}${Array.from(tokens[tokens.length - 1])[0] || ''}`;
            }
            function fteAccentFor(name) {
                return colorFromPalette(normalizePersonKey(name), FTE_AVATAR_COLORS);
            }
            function resolveFteAvatar(name, accent) {
                const key = `${normalizePersonKey(name)}|${accent}`;
                if (fteAvatarCache.has(key)) return fteAvatarCache.get(key);
                const entry = {
                    initials: personInitials(name),
                    photoUrl: buildPersonLookupKeys(name).map((candidate) => fteProfileUrls[candidate] || null).find(Boolean) || null,
                };
                fteAvatarCache.set(key, entry);
                return entry;
            }
            function getFteAvatarLayer(target) {
                const canvas = target?.canvas || target;
                if (!canvas || canvas.id !== 'chartFtePeople') return null;
                return document.getElementById('chartFtePeopleAvatarLayer');
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
                if (!chart?.canvas || chart.canvas.id !== 'chartFtePeople') return;
                if (chart._fteAvatarSyncFrame) cancelAnimationFrame(chart._fteAvatarSyncFrame);
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
                    if (!Number.isFinite(radius) || !Number.isFinite(x) || !Number.isFinite(y)) return null;
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
                const signature = records.map((record) => [record.label, record.x.toFixed(1), record.y.toFixed(1), record.radius.toFixed(1), record.photoUrl || ''].join('|')).join('||');
                if (layer.dataset.signature === signature) return;
                layer.dataset.signature = signature;
                layer.innerHTML = '';
                records.forEach((record) => layer.appendChild(createFteAvatarMarker(record)));
            }
            function computeFteBubbleRadius(totalFte) {
                return Math.max(14, Math.min(34, 12 + Math.sqrt(Math.max(0, Number(totalFte) || 0)) * 8));
            }
            function buildFteSummaryDatasets(items) {
                const grouped = new Map();
                items.forEach((item) => {
                    const name = text(item.name, 'ไม่ระบุชื่อ');
                    if (!grouped.has(name)) grouped.set(name, { fte: 0, projectSet: new Set() });
                    const group = grouped.get(name);
                    group.fte += num(item.fte_value || item.fte || item.hours);
                    const projectId = item.project?.id || item.project_id;
                    if (projectId !== null && projectId !== undefined) group.projectSet.add(projectId);
                });
                return Array.from(grouped.entries())
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
                            data: [{ x: data.projectSet.size, y: data.fte, r: computeFteBubbleRadius(data.fte) }],
                        };
                    });
            }
            const budget = (p) => num(p.total_budget) || num(p.budget);
            const normalizeYearLabel = (value, fallback = 'ไม่ระบุปี') => {
                const raw = text(value);
                if (!raw) return fallback;
                const direct = raw.match(/^(25\d{2}|20\d{2}|19\d{2})$/);
                if (direct) {
                    const year = Number(direct[1]);
                    return String(year < 2400 ? year + 543 : year);
                }
                const dateYear = raw.match(/(25\d{2}|20\d{2}|19\d{2})/);
                if (dateYear) {
                    const year = Number(dateYear[1]);
                    return String(year < 2400 ? year + 543 : year);
                }
                return raw;
            };
            const yearLabel = (p) => normalizeYearLabel(p.start_year);
            const outputYear = (o) => normalizeYearLabel(o.year ?? o.publish_year ?? o.export_year ?? o.publish_date ?? o.date ?? o.created_at);
            const isPublication = (type) => /paper|journal|publication|article|EA101/i.test(text(type));
            const outputTypeLabel = (type) => {
                const raw = text(type, 'ไม่ระบุประเภท');
                if (/^EA101\b/i.test(raw) || raw === 'EA101') return 'EA101 Research Paper or Journal Article';
                if (/^EA103\b/i.test(raw) || raw === 'EA103') return 'EA103 Presentations in Academic Conferences';
                return raw;
            };
            const beYear = (value) => normalizeYearLabel(value, '');
            const parseDate = (value) => {
                if (!value) return null;
                const d = new Date(value);
                return Number.isNaN(d.getTime()) ? null : d;
            };
            const dateLabel = (value) => {
                const d = value instanceof Date ? value : parseDate(value);
                if (!d) return '-';
                return d.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
            };
            const status = (p) => text(p.planning_status, 'ไม่ระบุสถานะ');
            const projectStatusOrder = [
                'Project Planing (เริ่มต้น - Submit IRB)',
                'เก็บข้อมูล',
                'วิเคราะห์ข้อมูล',
                'เขียนรายงานเเละ publication',
                'ปิดโปรเจค',
            ];
            const projectStatusDisplayLabel = (value) => {
                const raw = text(value, 'ไม่ระบุสถานะ');
                const normalized = norm(raw).replace(/\s+/g, '');

                if (normalized.includes('projectplaning') && normalized.includes('submitirb')) return projectStatusOrder[0];
                if (normalized.includes('เก็บข้อมูล')) return projectStatusOrder[1];
                if (normalized.includes('วิเคราะห์ข้อมูล')) return projectStatusOrder[2];
                if (normalized.includes('เขียนรายงาน')) return projectStatusOrder[3];
                if (normalized.includes('ปิดโปรเจค')) return projectStatusOrder[4];

                return raw;
            };
            const projectStatusChartRows = (rows) => {
                const counts = groupSum(rows, (p) => projectStatusDisplayLabel(p.planning_status));

                return projectStatusOrder
                    .map((label) => ({ label, value: Number(counts.find((row) => row.label === label)?.value || 0) }))
                    .filter((row) => row.value > 0);
            };
            const fundingType = (p) => text(p.funding_type, 'ไม่ระบุ');
            const fundingSource = (p) => text(p.funding_source, 'ไม่ระบุ');
            const isClosed = (p) => /ปิด|complete|completed|close/i.test(status(p));
            const isCancelled = (p) => /ยกเลิก|cancel/i.test(status(p));
            const isActive = (p) => !isClosed(p) && !isCancelled(p);
            const projectUrl = (id) => `/rm/projects/${id}`;
            const projectEndDate = (p) => parseDate(p.team_end_date || p.end_date || p.finish_date);
            const fteSkillLabel = (r) => text(r.skill_role || r.role_group || r.job_detail || r.task_detail, 'ไม่ระบุสายงาน');
            const fteValue = (r) => num(r?.fte_value || r?.fte || r?.hours);
            const isEligibleWorkloadFte = (r) => {
                if (!r || !(fteValue(r) > 0)) return false;
                if (!r.user_id && !r.user) return false;
                if (r.is_resigned || r.is_consultant || r.is_borrowed) return false;
                return r.is_unit_user === true;
            };
            const skillSetDisplayLabel = (value) => {
                const raw = text(value, 'ไม่ระบุ');
                const normalized = norm(raw);
                if (normalized.includes('scientific') || normalized.includes('research')) return 'Scientific';
                if (normalized.includes('management') || normalized.includes('coordination') || normalized.includes('project')) return 'Management';
                if (normalized.includes('communication') || normalized.includes('public relation') || normalized.includes('it') || normalized.includes('data')) return 'IT + Communication';
                if (normalized.includes('operation') || normalized.includes('administrative') || normalized.includes('admin') || normalized.includes('logistics')) return 'Logistics + Admin';
                return raw;
            };
            const skillSetCanonicalLabel = (value) => {
                const raw = text(value, 'ไม่ระบุ');
                const normalized = norm(raw);
                if (normalized.includes('scientific') || normalized === 'research') return 'Scientific Research';
                if (normalized.includes('it') || normalized.includes('data')) return 'IT & Data Management Systems';
                if (normalized.includes('communication') || normalized.includes('public relation')) return 'Communication & Public Relations';
                if (normalized.includes('management') || normalized.includes('coordination') || normalized === 'project') return 'Project Management & Coordination';
                if (normalized.includes('operation') || normalized.includes('administrative') || normalized.includes('admin') || normalized.includes('logistics')) return 'Operations & Administrative Support';
                return raw;
            };
            const skillSetCanonicalKey = (value) => norm(skillSetCanonicalLabel(value));
            const dashboardSkillSummaryByKey = new Map(
                (Array.isArray(dashboardFteSkillAnalysis?.skill_rows) ? dashboardFteSkillAnalysis.skill_rows : [])
                    .map((row) => [skillSetCanonicalKey(row.label || row.thai || row.key), row])
            );
            const skillSetSortRank = (value) => {
                const label = skillSetDisplayLabel(value);
                return ['Scientific', 'Management', 'IT + Communication', 'Logistics + Admin'].indexOf(label);
            };
            const isPolicyUnitText = (value) => {
                const v = norm(value).replace(/[()]/g, ' ');
                return (v.includes('ภายในหน่วยวิจัย') && v.includes('สุขภา'))
                    || v.includes('หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ')
                    || v.includes('siriraj health policy')
                    || v.includes('policy-driven health');
            };
            const isPolicyPi = (p) => [
                p.project_list,
                p.pi_affiliation,
                p.unit_name,
                p.department,
            ].some(isPolicyUnitText);
            const isPolicyCopi = (copi) => [
                copi?.affiliation,
                copi?.unit_status,
                copi?.person_type,
            ].some(isPolicyUnitText);
            const collabRawLabel = (p) => text(p.collab_type, text(p.project_list, 'ไม่ระบุ'));
            const collabDisplayLabel = (value) => {
                const raw = text(value, 'ไม่ระบุ');
                const normalized = norm(raw);

                if (isPolicyUnitText(raw)) return 'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
                if (
                    normalized === 'cross_dept_in_med' ||
                    normalized.includes('ข้ามหน่วย') ||
                    (normalized.includes('นอกหน่วย') && normalized.includes('คณะ')) ||
                    normalized.includes('ในคณะแพทย์') ||
                    normalized.includes('ภายในคณะแพทย')
                ) {
                    return 'โครงการร่วมมือกับบุคลากรภายในคณะแพทยศาสตร์ศิริราชพยาบาล';
                }
                if (
                    normalized === 'outside_faculty' ||
                    normalized === 'outside_university' ||
                    normalized.includes('นอกคณะ') ||
                    normalized.includes('วิจัยนอกคณะ') ||
                    normalized.includes('ภายนอกคณะแพทย')
                ) {
                    return 'โครงการร่วมมือกับบุคลากรภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล';
                }

                return raw;
            };

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function fillYearOptions() {
                const years = Array.from(new Set(projects.map(yearLabel))).sort((a, b) => {
                    const ay = Number(a.match(/\d{4}/)?.[0] || 99999);
                    const by = Number(b.match(/\d{4}/)?.[0] || 99999);
                    return ay - by;
                }).filter((year) => !isFutureYearLabel(year));
                years.forEach((year) => {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    els.year.appendChild(option);
                });
            }

            function filteredRows() {
                const values = {
                    year: text(els.year?.value),
                    theme: norm(els.theme?.value),
                    type: norm(els.type?.value),
                    funding: norm(els.funding?.value),
                    status: norm(els.status?.value),
                    source: norm(els.source?.value),
                    search: norm(els.search?.value),
                };
                return projects.filter((p) => {
                    if (values.year && yearLabel(p) !== values.year) return false;
                    if (values.theme && norm(p.theme) !== values.theme) return false;
                    if (values.type && norm(p.project_type) !== values.type) return false;
                    if (values.funding && norm(p.funding_type) !== values.funding) return false;
                    if (values.status && norm(p.planning_status) !== values.status) return false;
                    if (values.source && norm(p.funding_source) !== values.source) return false;
                    if (values.search) {
                        const haystack = [
                            p.project_name_th, p.project_code, p.pi_name, p.funding_source,
                            p.project_type, p.theme, p.planning_status
                        ].map(norm).join(' ');
                        if (!haystack.includes(values.search)) return false;
                    }
                    return true;
                });
            }

            function relatedOutputs(rows) {
                return rows.flatMap((p) => (Array.isArray(p.outputs) ? p.outputs : []).map((o) => ({
                    ...o,
                    source_kind: 'project',
                    source_label: 'Project output',
                    project: p,
                })));
            }

            function externalPublicationOutputs() {
                return filteredExternalOutputs().map((o) => ({
                    ...o,
                    id: `external-${o.id ?? o.code ?? o.output_code}`,
                    year: o.publish_year ?? externalOutputYear(o),
                    source_kind: 'extra',
                    source_label: 'Extra output',
                    project_name: 'Extra output',
                    authors: Array.isArray(o.authors) ? o.authors : [],
                }));
            }

            function backOfficePublicationOutputs() {
                return filteredBackOfficeRows().flatMap((task) => (Array.isArray(task.outputs) ? task.outputs : []).map((output) => ({
                    ...output,
                    id: `backoffice-${output.id ?? output.code}`,
                    year: output.year ?? backOfficeYear(task),
                    source_kind: 'backoffice',
                    source_label: 'Back office',
                    project_name: text(task.title, 'Back office'),
                    url: output.url || task.url,
                    journal: output.journal || task.creator_name || '-',
                    authors: Array.isArray(output.authors) ? output.authors : [],
                })));
            }

            function combinedOutputRecords(rows = filteredRows()) {
                return [
                    ...relatedOutputs(rows),
                    ...externalPublicationOutputs(),
                    ...backOfficePublicationOutputs(),
                ].filter((o) => !isFutureYearLabel(outputYear(o)));
            }

            function authorPublicationOutputs(rows = filteredRows()) {
                return combinedOutputRecords(rows).filter((o) => isPublication(o.type));
            }

            function groupSum(items, keyFn, valueFn = () => 1) {
                const map = new Map();
                items.forEach((item) => {
                    const key = text(keyFn(item), 'ไม่ระบุ');
                    map.set(key, (map.get(key) || 0) + valueFn(item));
                });
                return Array.from(map, ([label, value]) => ({ label, value }));
            }

            function top(entries, n = 8) {
                return entries
                    .filter((item) => Number(item.value) > 0)
                    .sort((a, b) => b.value - a.value || a.label.localeCompare(b.label, 'th'))
                    .slice(0, n);
            }

            function yearsFromRows(rows) {
                return Array.from(new Set(rows.map(yearLabel)))
                    .filter((year) => year !== 'ไม่ระบุปี')
                    .filter((year) => !isFutureYearLabel(year))
                    .sort((a, b) => Number(a.match(/\d{4}/)?.[0] || 99999) - Number(b.match(/\d{4}/)?.[0] || 99999));
            }

            function sortYearLabels(labels) {
                return Array.from(labels)
                    .filter((year) => year !== 'ไม่ระบุปี')
                    .sort((a, b) => Number(String(a).match(/\d{4}/)?.[0] || 99999) - Number(String(b).match(/\d{4}/)?.[0] || 99999));
            }

            function activeYearsFor(rows, valueFn) {
                return yearsFromRows(rows).filter((year) => rows.some((item) => yearLabel(item.project || item) === year && Number(valueFn(item) || 0) > 0));
            }

            function visibleYearWindow(years, limit = 10) {
                const list = Array.from(years || []);
                return {
                    visible: list.slice(Math.max(0, list.length - limit)),
                    hidden: list.slice(0, Math.max(0, list.length - limit)),
                };
            }

            function formatHiddenYearsSummary(hiddenYears, rows) {
                if (!hiddenYears.length) return 'แสดงครบทุกปีในชุดข้อมูลที่เลือก';
                const total = hiddenYears.reduce((sum, year) => sum + rows.filter((p) => yearLabel(p) === year).length, 0);
                const first = hiddenYears[0];
                const last = hiddenYears[hiddenYears.length - 1];
                const range = first === last ? first : `${first}-${last}`;
                return `ซ่อนปีเก่ากว่า ${hiddenYears.length} ปี (${range}) รวม ${fmt.format(total)} โครงการ เพื่อให้กราฟอ่านง่ายขึ้น`;
            }

            function yearNumber(label) {
                return Number(String(label ?? '').match(/\d{4}/)?.[0] || 0);
            }

            function isFutureYearLabel(label) {
                const year = yearNumber(label);
                return year > CURRENT_BE_YEAR;
            }

            function isCurrentOrPastYearLabel(label) {
                const year = yearNumber(label);
                return year > 0 && year <= CURRENT_BE_YEAR;
            }

            function isPlanningProject(p) {
                const value = norm([
                    p?.planning_status,
                    p?.execution_status,
                    p?.project_status,
                    p?.status,
                ].filter(Boolean).join(' '));
                return value.includes('plan')
                    || value.includes('proposal')
                    || value.includes('วางแผน')
                    || value.includes('รอ')
                    || value.includes('เตรียม');
            }

            function currentPastRows(rows, rowYearFn = yearLabel) {
                return rows.filter((item) => isCurrentOrPastYearLabel(rowYearFn(item)));
            }

            function futureYearPlanSummary(rows, rowYearFn = yearLabel, unit = 'โครงการ') {
                const futureRows = rows.filter((item) => isFutureYearLabel(rowYearFn(item)));
                if (!futureRows.length) return '';
                const futureYears = sortYearLabels(new Set(futureRows.map(rowYearFn))).filter(isFutureYearLabel);
                return futureYears.map((year) => `มี plan การพัฒนาในปี ${year} จำนวน ${fmt.format(futureRows.filter((item) => rowYearFn(item) === year).length)} ${unit}`).join(' · ');
            }

            function colorFromPalette(label, palette = COLORS) {
                const key = text(label);
                let hash = 0;
                for (let i = 0; i < key.length; i++) hash = ((hash << 5) - hash) + key.charCodeAt(i);
                return palette[Math.abs(hash) % palette.length];
            }

            function fundingTypeColor(type) {
                const label = text(type);
                if (label === 'ภายใน') return FUNDING_COLORS.internal;
                if (label === 'ภายนอก') return FUNDING_COLORS.external;
                return FUNDING_COLORS.unknown;
            }

            function fundingSourceColor(source) {
                return colorFromPalette(source, FUNDING_SOURCE_COLORS);
            }

            function stackDatasets(rows, years, keyFn, valueFn, maxKeys = 7, colorFn = null) {
                const keys = top(groupSum(rows, keyFn, valueFn), maxKeys).map((x) => x.label);
                return keys.map((key, index) => barDataset(
                    key,
                    years.map((year) => rows
                        .filter((item) => yearLabel(item.project || item) === year && text(keyFn(item)) === key)
                        .reduce((sum, item) => sum + valueFn(item), 0)),
                    colorFn ? colorFn(key, index) : COLORS[index % COLORS.length]
                ));
            }

            function renderChart(id, config) {
                const canvas = document.getElementById(id);
                if (!canvas) return;
                const old = chartMap.get(id);
                if (old) old.destroy();
                config.options = config.options || {};
                config.options.layout = config.options.layout || {};
                config.options.layout.padding = {
                    top: 28,
                    right: 12,
                    bottom: 8,
                    left: 8,
                    ...(config.options.layout.padding || {})
                };
                config.options.plugins = config.options.plugins || {};
                if (!Object.prototype.hasOwnProperty.call(config.options.plugins, 'diValueLabel')) {
                    config.options.plugins.diValueLabel = {};
                }
                if (!Object.prototype.hasOwnProperty.call(config.options.plugins, 'diStackTotalLabel')) {
                    config.options.plugins.diStackTotalLabel = { display: false };
                }
                if (!Object.prototype.hasOwnProperty.call(config.options.plugins, 'diDoughnutCallout')) {
                    config.options.plugins.diDoughnutCallout = { display: false };
                }
                const scales = config.options.scales || {};
                Object.keys(scales).forEach((axis) => {
                    const scale = scales[axis] || {};
                    if (axis === 'x' || axis === 'y') {
                        scale.grace = scale.grace || '22%';
                        scale.beginAtZero = scale.beginAtZero ?? true;
                    }
                    scales[axis] = scale;
                });
                config.options.scales = scales;
                const chart = new Chart(canvas, config);
                chartMap.set(id, chart);
                canvas.onclick = (event) => {
                    const points = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true);
                    const fallback = points.length ? points : chart.getElementsAtEventForMode(event, 'index', { intersect: false }, true);
                    if (!fallback.length) return;
                    openChartDetail(id, chart, fallback[0]);
                };
            }

            function barDataset(label, data, color) {
                return { label, data, backgroundColor: color, borderColor: color, borderWidth: 1, borderRadius: 8, maxBarThickness: 36 };
            }

            function updateText(id, value) {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            }

            function setPopup(openTarget = null) {
                const isFilterOpen = openTarget === 'filter';
                const isDownloadOpen = openTarget === 'download';
                els.overlay?.classList.toggle('is-open', Boolean(openTarget));
                els.filterPanel?.classList.toggle('is-open', isFilterOpen);
                els.downloadPopup?.classList.toggle('is-open', isDownloadOpen);
                els.downloadPopup?.setAttribute('aria-hidden', isDownloadOpen ? 'false' : 'true');
            }

            function csvEscape(value) {
                const textValue = String(value ?? '');
                return /[",\n]/.test(textValue) ? `"${textValue.replace(/"/g, '""')}"` : textValue;
            }

            function exportStamp() {
                return new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
            }

            function outputAuthorText(output) {
                return (Array.isArray(output?.authors) ? output.authors : [])
                    .map((author) => [author.name, author.role].filter(Boolean).join(' / '))
                    .filter(Boolean)
                    .join('; ');
            }

            function selectedProjectIds(rows) {
                return new Set(rows.map((p) => String(p.id ?? '')).filter(Boolean));
            }

            function externalOutputYear(row) {
                return normalizeYearLabel(row.publish_year ?? row.publish_date ?? row.created_at);
            }

            function backOfficeYear(row) {
                return normalizeYearLabel(row.year ?? row.start_date ?? row.created_at);
            }

            function filteredExternalOutputs() {
                const year = text(els.year?.value);
                const search = norm(els.search?.value);
                return (Array.isArray(externalOutputs) ? externalOutputs : []).filter((row) => {
                    if (year && externalOutputYear(row) !== year) return false;
                    if (search) {
                        const haystack = [row.code, row.output_code, row.title, row.type, row.job_category, row.journal, row.note]
                            .map(norm).join(' ');
                        if (!haystack.includes(search)) return false;
                    }
                    return true;
                });
            }

            function filteredBackOfficeRows() {
                const year = text(els.year?.value);
                const search = norm(els.search?.value);
                return (Array.isArray(backOfficeRows) ? backOfficeRows : []).filter((row) => {
                    if (year && backOfficeYear(row) !== year) return false;
                    if (search) {
                        const haystack = [row.code, row.backoffice_id, row.title, row.description, row.purpose, row.creator_name]
                            .map(norm).join(' ');
                        if (!haystack.includes(search)) return false;
                    }
                    return true;
                });
            }

            function downloadBlob(content, filename, type = 'text/csv;charset=utf-8;') {
                const blob = new Blob([content], { type });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            }

            function projectExportRows(rows) {
                return rows.map((p) => ({
                    id: p.id ?? '',
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th ?? '',
                    pi_name: p.pi_name ?? '',
                    start_year: yearLabel(p),
                    project_type: p.project_type ?? '',
                    theme: p.theme ?? '',
                    funding_type: p.funding_type ?? '',
                    funding_source: p.funding_source ?? '',
                    planning_status: p.planning_status ?? '',
                    budget: budget(p),
                    irb_count: (p.irbs || []).length,
                    copi_count: (p.copis || []).length,
                    coresearch_count: (p.co_researches || []).length,
                    fte_count: (p.fte_roles || []).length,
                    output_count: (p.outputs || []).length,
                }));
            }

            function irbExportRows(rows) {
                return rows.flatMap((p) => (p.irbs || []).map((irb) => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th ?? '',
                    pi_name: p.pi_name ?? '',
                    start_year: yearLabel(p),
                    irb_code: irb.code ?? '',
                    irb_exp_date: irb.exp_date ?? '',
                    irb_source: irb.source ?? irb.irb_source ?? '',
                })));
            }

            function copiExportRows(rows) {
                return rows.flatMap((p) => (p.copis || []).map((copi) => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th ?? '',
                    pi_name: p.pi_name ?? '',
                    start_year: yearLabel(p),
                    copi_name: copi.name ?? '',
                    copi_affiliation: copi.affiliation ?? '',
                })));
            }

            function coResearchExportRows(rows) {
                return rows.flatMap((p) => (p.co_researches || []).map((coResearch) => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th ?? '',
                    pi_name: p.pi_name ?? '',
                    start_year: yearLabel(p),
                    name: coResearch.name ?? '',
                    unit_status: coResearch.unit_status ?? '',
                    affiliation: coResearch.affiliation ?? '',
                })));
            }

            function fteExportRows(rows) {
                return rows.flatMap((p) => (p.fte_roles || []).map((fte) => ({
                    project_code: p.project_code ?? '',
                    project_name_th: p.project_name_th ?? '',
                    pi_name: p.pi_name ?? '',
                    start_year: yearLabel(p),
                    name: fte.name ?? '',
                    role_group: fte.role_group ?? '',
                    skill_role: fte.skill_role ?? '',
                    position: fte.position ?? '',
                    job_detail: fte.job_detail ?? '',
                    fte_value: fte.fte_value ?? fte.fte ?? '',
                    hours: fte.hours ?? '',
                    task_detail: fte.task_detail ?? '',
                    start_date: fte.start_date ?? '',
                    end_date: fte.end_date ?? '',
                    is_borrowed: fte.is_borrowed ? 'yes' : '',
                })));
            }

            function outputExportRows(rows) {
                return relatedOutputs(rows).map((output) => ({
                    project_code: output.project?.project_code ?? '',
                    project_name_th: output.project?.project_name_th ?? output.project_name ?? '',
                    pi_name: output.project?.pi_name ?? '',
                    start_year: output.project ? yearLabel(output.project) : '',
                    output_code: output.output_code ?? output.code ?? '',
                    output_type: output.type ?? '',
                    output_title: output.title ?? '',
                    output_year: outputYear(output),
                    journal: output.journal ?? '',
                    quartile: output.quartile ?? '',
                    impact_factor: output.impact_factor ?? '',
                    authors: outputAuthorText(output),
                    created_at: output.created_at ?? '',
                    updated_at: output.updated_at ?? '',
                }));
            }

            function externalOutputExportRows() {
                return filteredExternalOutputs().map((output) => ({
                    output_code: output.output_code ?? output.code ?? '',
                    output_type: output.type ?? '',
                    job_category: output.job_category ?? '',
                    output_title: output.title ?? '',
                    publish_date: output.publish_date ?? '',
                    publish_year: externalOutputYear(output),
                    journal: output.journal ?? '',
                    quartile: output.quartile ?? '',
                    impact_factor: output.impact_factor ?? '',
                    note: output.note ?? '',
                    url: output.url ?? '',
                    created_at: output.created_at ?? '',
                    updated_at: output.updated_at ?? '',
                }));
            }

            function backOfficeExportRows() {
                return filteredBackOfficeRows().map((row) => ({
                    backoffice_id: row.backoffice_id ?? row.code ?? '',
                    title: row.title ?? '',
                    purpose: row.purpose ?? '',
                    description: row.description ?? '',
                    start_date: row.start_date ?? '',
                    end_date: row.end_date ?? '',
                    year: backOfficeYear(row),
                    entries_count: row.entries_count ?? '',
                    outputs_count: row.outputs_count ?? '',
                    hours: row.hours ?? '',
                    fte: row.fte ?? '',
                    creator_name: row.creator_name ?? '',
                    url: row.url ?? '',
                    created_at: row.created_at ?? '',
                    updated_at: row.updated_at ?? '',
                }));
            }

            function csvRowsFromObjects(rows, recordType) {
                return rows.map((row) => ({
                    record_type: recordType,
                    ...row,
                }));
            }

            function downloadFilteredCsv() {
                if (!canDownloadDashboardData) return;
                const rows = filteredRows();
                const combined = [
                    ...csvRowsFromObjects(projectExportRows(rows), 'project'),
                    ...csvRowsFromObjects(outputExportRows(rows), 'project_output'),
                    ...csvRowsFromObjects(externalOutputExportRows(), 'external_output'),
                    ...csvRowsFromObjects(backOfficeExportRows(), 'back_office'),
                    ...csvRowsFromObjects(fteExportRows(rows), 'fte'),
                ];
                const headers = Array.from(combined.reduce((set, row) => {
                    Object.keys(row).forEach((key) => set.add(key));
                    return set;
                }, new Set(['record_type'])));
                const csv = [
                    headers.join(','),
                    ...combined.map((row) => headers.map((key) => csvEscape(row[key])).join(','))
                ].join('\n');
                downloadBlob('\uFEFF' + csv, `dashboard-insight-${exportStamp()}.csv`);
            }

            function appendSheet(workbook, rows, name) {
                const ws = XLSX.utils.json_to_sheet(rows.length ? rows : [{ note: 'ไม่มีข้อมูลตามเงื่อนไขที่เลือก' }]);
                XLSX.utils.book_append_sheet(workbook, ws, name);
            }

            function downloadFilteredExcel() {
                if (!canDownloadDashboardData) return;
                if (typeof XLSX === 'undefined') {
                    alert('ไม่พบโมดูลสำหรับสร้างไฟล์ Excel');
                    return;
                }
                const rows = filteredRows();
                const workbook = XLSX.utils.book_new();
                appendSheet(workbook, projectExportRows(rows), 'Projects');
                appendSheet(workbook, irbExportRows(rows), 'IRB');
                appendSheet(workbook, copiExportRows(rows), 'Co-PI');
                appendSheet(workbook, coResearchExportRows(rows), 'Co-Research');
                appendSheet(workbook, fteExportRows(rows), 'FTE');
                appendSheet(workbook, outputExportRows(rows), 'Project Outputs');
                appendSheet(workbook, externalOutputExportRows(), 'External Outputs');
                appendSheet(workbook, backOfficeExportRows(), 'Back Office');
                XLSX.writeFile(workbook, `dashboard-insight-${exportStamp()}.xlsx`);
            }

            // Shared selection keeps the two exported groups identical to the chart bars.
            function yearlyProjectGroups(rows, year) {
                const analysisRows = currentPastRows(rows);
                return {
                    year: Number(year),
                    newProjects: analysisRows.filter((p) => yearNumber(yearLabel(p)) === Number(year)),
                    ongoingProjects: analysisRows.filter((p) => {
                        const start = yearNumber(yearLabel(p));
                        return start && start <= Number(year) && isActive(p);
                    }),
                };
            }

            document.getElementById('yearlyProjectExport')?.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (!canDownloadDashboardData) return;
                const button = document.getElementById('downloadYearlyExcel');
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                updateText('yearlyExportMessage', 'กำลังเตรียม Excel…');
                try {
                    const exporter = await import(@json(asset('js/dashboard-yearly-export.js') . '?v=' . filemtime(public_path('js/dashboard-yearly-export.js'))));
                    const years = exporter.validateYears(document.getElementById('yearlyExportFrom').value,
                        document.getElementById('yearlyExportTo').value, CURRENT_BE_YEAR);
                    const rows = filteredRows();
                    await exporter.downloadYearlyWorkbook(years.map((year) => yearlyProjectGroups(rows, year)), {
                        scope: activeFilterLabels().map(([label, value]) => `${label}: ${value}`).join(' • '),
                    });
                    updateText('yearlyExportMessage', 'สร้างไฟล์เรียบร้อยแล้ว');
                } catch (error) {
                    console.error('Yearly project export failed', error);
                    updateText('yearlyExportMessage', error instanceof Error && error.message.startsWith('กรุณา')
                        ? error.message : 'สร้างไฟล์ไม่สำเร็จ กรุณารีเฟรชหน้าและลองอีกครั้ง');
                } finally {
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                }
            });

            function modalKindForChart(id) {
                if (/Irb|Overdue|RedZone/.test(id)) return 'risk';
                if (/Budget|Funding|External|Internal/.test(id)) return 'funding';
                if (/Output|Publication/.test(id)) return 'output';
                if (/CoResearch|Collab|PiUnit/.test(id)) return 'operation';
                if (/Fte/.test(id)) return 'workload';
                return 'portfolio';
            }

            function modalKpis(rows, context = {}) {
                const kind = context.kind || 'portfolio';
                const chartId = context.chartId || '';
                const totalBudget = rows.reduce((sum, p) => sum + budget(p), 0);
                const piCount = new Set(rows.map((p) => text(p.pi_name)).filter(Boolean)).size;
                const activeCount = rows.filter((p) => isActive(p)).length;
                const uniqueCoResearchCount = (items) => new Set(items.flatMap((p) => (Array.isArray(p.co_researches) ? p.co_researches : [])
                    .map((c) => text(c.name))
                    .filter(Boolean))).size;
                if (chartId === 'chartProjectsPerYear') {
                    return [
                        ['โครงการปีนี้', fmt.format(rows.length)],
                        ['ยัง Active', fmt.format(activeCount)],
                        ['งบรวมในปี', `${money.format(totalBudget)} บ.`],
                        ['จำนวน PI', `${fmt.format(piCount)} ท่าน`],
                    ];
                }
                if (chartId === 'chartLifecycle') {
                    return [
                        ['ชุดที่เลือก', context.dataset || '-'],
                        ['จำนวนโครงการ', fmt.format(rows.length)],
                        ['Active', fmt.format(activeCount)],
                        ['ปิด/ยกเลิก', `${fmt.format(rows.filter(isClosed).length)} / ${fmt.format(rows.filter(isCancelled).length)}`],
                    ];
                }
                if (chartId === 'chartStatus') {
                    return [
                        ['สถานะที่เลือก', context.focus || '-'],
                        ['จำนวนโครงการ', fmt.format(rows.length)],
                        ['งบรวม', `${money.format(totalBudget)} บ.`],
                        ['จำนวน PI', `${fmt.format(piCount)} ท่าน`],
                    ];
                }
                if (chartId === 'chartContractOverview' || chartId === 'chartRedZoneTimeline' || chartId === 'chartOverdue') {
                    const overdueCount = overdueProjects(rows).length;
                    const nearestEnd = rows.map(projectEndDate).filter(Boolean).sort((a, b) => a - b)[0];
                    return [
                        ['กลุ่มสัญญา', context.focus || '-'],
                        ['จำนวนโครงการ', fmt.format(rows.length)],
                        ['เกินกำหนด', fmt.format(overdueCount)],
                        ['วันสิ้นสุดใกล้สุด', dateLabel(nearestEnd)],
                    ];
                }
                if (chartId === 'chartBudgetPeople') {
                    return [
                        ['บุคคล/บทบาท', context.focus || '-'],
                        ['บทบาทที่กด', context.dataset || '-'],
                        ['โครงการมีงบ', fmt.format(rows.length)],
                        ['วงเงินรวม', `${money.format(totalBudget)} บ.`],
                    ];
                }
                if (chartId === 'chartBudgetYear' || chartId === 'chartBudgetPolicyPiYear') {
                    return [
                        ['ปีงบประมาณ', context.focus || '-'],
                        [chartId === 'chartBudgetPolicyPiYear' ? 'โครงการ PI หน่วยฯ' : 'โครงการมีงบ', fmt.format(rows.length)],
                        ['งบรวม', `${money.format(totalBudget)} บ.`],
                        ['งบเฉลี่ย/โครงการ', `${money.format(rows.length ? totalBudget / rows.length : 0)} บ.`],
                    ];
                }
                if (chartId === 'chartFundingTypeYear' || chartId === 'chartFundingSplit') {
                    const sourceCount = new Set(rows.map(fundingSource).filter(Boolean)).size;
                    return [
                        ['ประเภททุน', context.dataset || context.focus || '-'],
                        ['โครงการมีงบ', fmt.format(rows.length)],
                        ['งบรวม', `${money.format(totalBudget)} บ.`],
                        ['จำนวนแหล่งทุน', fmt.format(sourceCount)],
                    ];
                }
                if (chartId === 'chartFundingSource' || chartId === 'chartExternalBySource' || chartId === 'chartInternalBySource' || chartId === 'chartExternalBySourceYear' || chartId === 'chartInternalBySourceYear') {
                    const typeCount = new Set(rows.map(fundingType).filter(Boolean)).size;
                    return [
                        ['แหล่งทุน', context.dataset || context.focus || '-'],
                        ['โครงการมีงบ', fmt.format(rows.length)],
                        ['งบรวม', `${money.format(totalBudget)} บ.`],
                        ['ประเภททุน', fmt.format(typeCount)],
                    ];
                }
                if (chartId === 'chartOutputType' || chartId === 'chartOutputStacked' || chartId === 'chartPublicationYear') {
                    const outputs = context.outputs || [];
                    const publicationCount = outputs.filter((o) => isPublication(o.type)).length;
                    return [
                        ['กราฟที่เลือก', context.focus || '-'],
                        ['Output records', fmt.format(outputs.length)],
                        ['Publication', fmt.format(publicationCount)],
                        ['โครงการต้นทาง', fmt.format(rows.length)],
                    ];
                }
                if (chartId === 'chartCollab') {
                    return [
                        ['รูปแบบความร่วมมือ', context.focus || '-'],
                        ['จำนวนโครงการ', fmt.format(rows.length)],
                        ['จำนวนผู้ร่วมวิจัย', `${fmt.format(uniqueCoResearchCount(rows))} ท่าน`],
                        ['ยัง Active', fmt.format(activeCount)],
                    ];
                }
                if (chartId === 'chartPiUnit') {
                    return [
                        ['หน่วยงาน PI', context.focus || '-'],
                        ['จำนวนโครงการ', fmt.format(rows.length)],
                        ['จำนวน PI', `${fmt.format(piCount)} ท่าน`],
                        ['งบรวม', `${money.format(totalBudget)} บ.`],
                    ];
                }
                if (chartId === 'chartCoResearch') {
                    return [
                        ['กลุ่ม Co-Research', context.focus || '-'],
                        ['จำนวนโครงการ', fmt.format(rows.length)],
                        ['จำนวนผู้ร่วมวิจัย', `${fmt.format(uniqueCoResearchCount(rows))} ท่าน`],
                        ['ยัง Active', fmt.format(activeCount)],
                    ];
                }
                if (chartId === 'chartIrb') {
                    const selectedIrbs = context.irbs || [];
                    const earliest = selectedIrbs.map((irb) => parseDate(irb.exp_date)).filter(Boolean).sort((a, b) => a - b)[0];
                    return [
                        ['เดือนหมดอายุ', context.focus || '-'],
                        ['IRB records', fmt.format(selectedIrbs.length)],
                        ['โครงการที่เกี่ยวข้อง', fmt.format(rows.length)],
                        ['วันหมดอายุใกล้สุด', dateLabel(earliest)],
                    ];
                }
                if (chartId === 'chartFtePeople') {
                    const person = context.dataset || context.focus || '';
                    const personFteRows = rows.flatMap((p) => (Array.isArray(p.fte_roles) ? p.fte_roles : [])
                        .filter((r) => isEligibleWorkloadFte(r) && text(r.name, 'ไม่ระบุชื่อ') === person));
                    const personFte = personFteRows.reduce((sum, r) => sum + fteValue(r), 0);
                    const skillCount = new Set(personFteRows.map((r) => fteSkillLabel(r)).filter(Boolean)).size;
                    return [
                        ['บุคลากร', person || '-'],
                        ['FTE รวม', money.format(personFte)],
                        ['โครงการ', fmt.format(rows.length)],
                        ['Skill set', fmt.format(skillCount)],
                    ];
                }
                if (kind === 'funding') {
                    return [
                        ['โครงการมีงบ', fmt.format(rows.length)],
                        ['งบรวม', `${money.format(totalBudget)} บ.`],
                        ['งบเฉลี่ย/โครงการ', `${money.format(rows.length ? totalBudget / rows.length : 0)} บ.`],
                        ['แหล่งทุน', context.focus || '-'],
                    ];
                }
                if (kind === 'output') {
                    const outputs = context.outputs || relatedOutputs(rows).filter((o) => !isFutureYearLabel(outputYear(o)));
                    const outputTypes = new Set(outputs.map((o) => outputTypeLabel(o.type))).size;
                    return [
                        ['Output ที่เกี่ยวข้อง', fmt.format(outputs.length)],
                        ['โครงการต้นทาง', fmt.format(rows.length)],
                        ['ประเภท output', fmt.format(outputTypes)],
                        ['ปี/หมวดที่เลือก', context.focus || '-'],
                    ];
                }
                if (kind === 'operation') {
                    const irbCount = rows.reduce((sum, p) => sum + (Array.isArray(p.irbs) ? p.irbs.length : 0), 0);
                    const overdueCount = overdueProjects(rows).length;
                    return [
                        ['โครงการ', fmt.format(rows.length)],
                        ['ยัง Active', fmt.format(activeCount)],
                        ['IRB ในรายการ', fmt.format(irbCount)],
                        ['เกินกำหนด', fmt.format(overdueCount)],
                    ];
                }
                if (kind === 'workload') {
                    const fteRows = rows.flatMap((p) => (Array.isArray(p.fte_roles) ? p.fte_roles : []).filter(isEligibleWorkloadFte));
                    const totalFte = fteRows.reduce((sum, r) => sum + fteValue(r), 0);
                    const peopleCount = new Set(fteRows.map((r) => text(r.name)).filter(Boolean)).size;
                    return [
                        ['โครงการ', fmt.format(rows.length)],
                        ['FTE รวม', money.format(totalFte)],
                        ['คนที่เกี่ยวข้อง', fmt.format(peopleCount)],
                        ['เฉลี่ย/โครงการ', money.format(rows.length ? totalFte / rows.length : 0)],
                    ];
                }
                const yearCount = new Set(rows.map((p) => yearLabel(p)).filter((value) => value && value !== '-')).size;
                return [
                    ['จำนวนโครงการ', fmt.format(rows.length)],
                    ['จำนวน PI', `${fmt.format(piCount)} ท่าน`],
                    ['โครงการ Active', fmt.format(activeCount)],
                    ['ช่วงปีในกลุ่ม', `${fmt.format(yearCount)} ปี`],
                ];
            }

            function modalColumns(context = {}) {
                const kind = context.kind || 'portfolio';
                const chartId = context.chartId || '';
                if (chartId === 'chartProjectsPerYear') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['ลักษณะโครงการ', (p) => escapeHtml(text(p.project_type, '-'))],
                        ['Content/Theme', (p) => escapeHtml(text(p.theme, '-'))],
                        ['งบประมาณ', (p) => `${money.format(budget(p))} บ.`],
                        ['สถานะ', (p) => escapeHtml(status(p))],
                    ];
                }
                if (chartId === 'chartLifecycle') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['ปีเริ่ม', (p) => escapeHtml(yearLabel(p))],
                        ['สถานะสัญญา', (p) => escapeHtml(contractBucket(p).label)],
                        ['วันสิ้นสุด', (p) => escapeHtml(dateLabel(projectEndDate(p)))],
                        ['งบประมาณ', (p) => `${money.format(budget(p))} บ.`],
                        ['สถานะงาน', (p) => escapeHtml(status(p))],
                    ];
                }
                if (chartId === 'chartStatus') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['ปี', (p) => escapeHtml(yearLabel(p))],
                        ['สถานะงาน', (p) => escapeHtml(projectStatusDisplayLabel(p.planning_status))],
                        ['สถานะสัญญา', (p) => escapeHtml(contractBucket(p).label)],
                        ['วันสิ้นสุด', (p) => escapeHtml(dateLabel(projectEndDate(p)))],
                    ];
                }
                if (chartId === 'chartContractOverview' || chartId === 'chartRedZoneTimeline' || chartId === 'chartOverdue') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['สถานะสัญญา', (p) => escapeHtml(contractBucket(p).label)],
                        ['วันสิ้นสุด', (p) => escapeHtml(dateLabel(projectEndDate(p)))],
                        ['สถานะงาน', (p) => escapeHtml(status(p))],
                        ['งบประมาณ', (p) => `${money.format(budget(p))} บ.`],
                    ];
                }
                if (chartId === 'chartBudgetPeople') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['บทบาทที่เลือก', () => escapeHtml(context.dataset || '-')],
                        ['PI โครงการ', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['งบประมาณ', (p) => `${money.format(budget(p))} บ.`],
                        ['แหล่งทุน', (p) => escapeHtml(fundingSource(p))],
                        ['ปี', (p) => escapeHtml(yearLabel(p))],
                    ];
                }
                if (chartId === 'chartCollab') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['รูปแบบความร่วมมือ', (p) => escapeHtml(collabDisplayLabel(collabRawLabel(p)))],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['Co-Research', (p) => fmt.format(Array.isArray(p.co_researches) ? p.co_researches.length : 0)],
                        ['ปี', (p) => escapeHtml(yearLabel(p))],
                        ['สถานะ', (p) => escapeHtml(status(p))],
                    ];
                }
                if (chartId === 'chartPiUnit') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['หน่วยงาน PI', (p) => escapeHtml(text(p.pi_affiliation || p.unit_name || p.department, '-'))],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['งบประมาณ', (p) => `${money.format(budget(p))} บ.`],
                        ['ปี', (p) => escapeHtml(yearLabel(p))],
                        ['สถานะ', (p) => escapeHtml(status(p))],
                    ];
                }
                if (chartId === 'chartCoResearch') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['จำนวนผู้ร่วมวิจัย', (p) => `${fmt.format(new Set((Array.isArray(p.co_researches) ? p.co_researches : []).map((c) => text(c.name)).filter(Boolean)).size)} ท่าน`],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['ปี', (p) => escapeHtml(yearLabel(p))],
                        ['สถานะสัญญา', (p) => escapeHtml(contractBucket(p).label)],
                        ['สถานะงาน', (p) => escapeHtml(status(p))],
                    ];
                }
                if (chartId === 'chartIrb') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['IRB ใกล้หมดอายุ', (p) => escapeHtml(dateLabel((context.irbs || []).filter((irb) => irb.project?.id === p.id).map((irb) => parseDate(irb.exp_date)).filter(Boolean).sort((a, b) => a - b)[0]))],
                        ['IRB ทั้งหมด', (p) => fmt.format(Array.isArray(p.irbs) ? p.irbs.length : 0)],
                        ['สถานะงาน', (p) => escapeHtml(status(p))],
                        ['วันสิ้นสุดโครงการ', (p) => escapeHtml(dateLabel(projectEndDate(p)))],
                    ];
                }
                if (chartId === 'chartFtePeople') {
                    const person = context.dataset || context.focus || '';
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['FTE คนนี้', (p) => money.format((p.fte_roles || []).filter((r) => isEligibleWorkloadFte(r) && text(r.name, 'ไม่ระบุชื่อ') === person).reduce((sum, r) => sum + fteValue(r), 0))],
                        ['Skill set', (p) => escapeHtml(Array.from(new Set((p.fte_roles || []).filter((r) => isEligibleWorkloadFte(r) && text(r.name, 'ไม่ระบุชื่อ') === person).map(fteSkillLabel))).join(', ') || '-')],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['ปี', (p) => escapeHtml(yearLabel(p))],
                        ['สถานะ', (p) => escapeHtml(status(p))],
                    ];
                }
                if (kind === 'funding') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['ปี', (p) => escapeHtml(yearLabel(p))],
                        ['ทุน', (p) => escapeHtml(fundingType(p))],
                        ['แหล่งทุน', (p) => escapeHtml(fundingSource(p))],
                        ['งบประมาณ', (p) => `${money.format(budget(p))} บ.`],
                        ['บทบาท', (p) => isPolicyPi(p) ? 'PI หน่วยวิจัยฯ' : 'ร่วม/อื่นๆ'],
                    ];
                }
                if (kind === 'output') {
                    const outputCountByProject = new Map((context.outputs || []).map((o) => [o.project?.id, 0]));
                    (context.outputs || []).forEach((o) => outputCountByProject.set(o.project?.id, (outputCountByProject.get(o.project?.id) || 0) + 1));
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['ปีโครงการ', (p) => escapeHtml(yearLabel(p))],
                        ['Output ในกลุ่มนี้', (p) => fmt.format(outputCountByProject.get(p.id) || (Array.isArray(p.outputs) ? p.outputs.length : 0))],
                        ['ประเภทหลัก', (p) => escapeHtml(outputTypeLabel((p.outputs || [])[0]?.type))],
                        ['สถานะ', (p) => escapeHtml(status(p))],
                    ];
                }
                if (kind === 'operation') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['สถานะ', (p) => escapeHtml(status(p))],
                        ['วันสิ้นสุด', (p) => escapeHtml(dateLabel(projectEndDate(p)))],
                        ['IRB', (p) => fmt.format(Array.isArray(p.irbs) ? p.irbs.length : 0)],
                        ['Co-Research', (p) => fmt.format(Array.isArray(p.co_researches) ? p.co_researches.length : 0)],
                    ];
                }
                if (kind === 'workload') {
                    return [
                        ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                        ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                        ['FTE รวม', (p) => money.format((p.fte_roles || []).filter(isEligibleWorkloadFte).reduce((sum, r) => sum + fteValue(r), 0))],
                        ['จำนวนคน', (p) => fmt.format(new Set((p.fte_roles || []).filter(isEligibleWorkloadFte).map((r) => text(r.name)).filter(Boolean)).size)],
                        ['ปี', (p) => escapeHtml(yearLabel(p))],
                        ['สถานะ', (p) => escapeHtml(status(p))],
                    ];
                }
                return [
                    ['โครงการ', (p) => `<a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a>`],
                    ['PI', (p) => escapeHtml(text(p.pi_name, '-'))],
                    ['ปี', (p) => escapeHtml(yearLabel(p))],
                    ['สถานะสัญญา', (p) => escapeHtml(contractBucket(p).label)],
                    ['วันสิ้นสุด', (p) => escapeHtml(dateLabel(projectEndDate(p)))],
                    ['สถานะ', (p) => escapeHtml(status(p))],
                ];
            }

            function outputRecordLink(output, fallback = '-') {
                const title = text(output.title || output.output_title || output.output_code || output.code, fallback);
                const url = output.url || output.project_output || output.link || '';
                return url
                    ? `<a href="${escapeHtml(url)}" target="_blank" rel="noopener" class="text-blue-700 font-semibold hover:underline">${escapeHtml(title)}</a>`
                    : escapeHtml(title);
            }

            function outputProjectCell(output) {
                const project = output.project;
                const projectName = text(project?.project_name_th || output.project_name || output.source_label, '-');
                if (project?.id) {
                    return `<a href="${projectUrl(project.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(projectName)}</a>`;
                }
                const url = output.url || output.project_output || output.link || '';
                return url
                    ? `<a href="${escapeHtml(url)}" target="_blank" rel="noopener" class="text-blue-700 font-semibold hover:underline">${escapeHtml(projectName)}</a>`
                    : escapeHtml(projectName);
            }

            function outputRecordsTable(outputs, context = {}) {
                const uniqueOutputs = Array.from(new Map(outputs.map((output, index) => {
                    const key = output.id ?? output.output_code ?? output.code ?? `${output.source_kind || 'output'}-${outputYear(output)}-${output.title || output.output_title || index}`;
                    return [String(key), output];
                })).values());
                if (!uniqueOutputs.length) return '<div class="di-empty">ไม่พบรายการ Publication ในกลุ่มนี้</div>';

                const projectRows = Array.from(new Map(uniqueOutputs
                    .map((output) => output.project)
                    .filter(Boolean)
                    .map((project) => [project.id ?? project.project_id ?? project.project_code ?? project.project_name_th, project])
                ).values());
                const kpis = modalKpis(projectRows, { ...context, outputs: uniqueOutputs });

                return `
                    <div class="di-modal-content di-modal-content--output">
                    <div class="di-modal-summary">
                        ${kpis.map(([label, value]) => `<div class="di-modal-kpi"><span>${escapeHtml(label)}</span><strong>${escapeHtml(value)}</strong></div>`).join('')}
                    </div>
                    <div class="di-modal-table-wrap">
                        <table class="di-table di-modal-table">
                            <thead>
                                <tr>
                                    <th>Publication</th>
                                    <th>ประเภท</th>
                                    <th>ปี</th>
                                    <th>โครงการ/แหล่งที่มา</th>
                                    <th>ผู้เขียน</th>
                                    <th>Journal/แหล่งเผยแพร่</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${uniqueOutputs.map((output) => `
                                    <tr>
                                        <td data-label="Publication">${outputRecordLink(output)}</td>
                                        <td data-label="ประเภท">${escapeHtml(outputTypeLabel(output.type))}</td>
                                        <td data-label="ปี">${escapeHtml(outputYear(output))}</td>
                                        <td data-label="โครงการ/แหล่งที่มา">${outputProjectCell(output)}</td>
                                        <td data-label="ผู้เขียน">${escapeHtml(outputAuthorText(output) || '-')}</td>
                                        <td data-label="Journal/แหล่งเผยแพร่">${escapeHtml(text(output.journal || output.source || output.publisher || output.source_label, '-'))}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    </div>
                `;
            }

            function projectsTable(rows, context = {}) {
                if (context.chartId === 'chartPublicationYear') {
                    return outputRecordsTable(Array.isArray(context.outputs) ? context.outputs : [], context);
                }
                if (!rows.length) return '<div class="di-empty">ไม่พบรายการโครงการในกลุ่มนี้</div>';
                const kind = context.kind || 'portfolio';
                const kpis = modalKpis(rows, context);
                const columns = modalColumns(context);
                return `
                    <div class="di-modal-content di-modal-content--${escapeHtml(kind)}">
                    <div class="di-modal-summary">
                        ${kpis.map(([label, value]) => `<div class="di-modal-kpi"><span>${escapeHtml(label)}</span><strong>${escapeHtml(value)}</strong></div>`).join('')}
                    </div>
                    <div class="di-modal-table-wrap">
                        <table class="di-table di-modal-table">
                            <thead>
                                <tr>
                                    ${columns.map(([label]) => `<th>${escapeHtml(label)}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.map((p) => `
                                    <tr>
                                        ${columns.map(([label, render]) => `<td data-label="${escapeHtml(label)}">${render(p)}</td>`).join('')}
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    </div>
                `;
            }

            function resetDetailStage() {
                if (els.detailBody) {
                    els.detailBody.scrollTop = 0;
                    els.detailBody.scrollLeft = 0;
                    els.detailBody.querySelectorAll('*').forEach((node) => {
                        if (node instanceof HTMLElement) {
                            node.scrollTop = 0;
                            node.scrollLeft = 0;
                        }
                    });
                }
                const panel = els.detailModal?.querySelector('.di-modal-panel');
                if (panel instanceof HTMLElement) {
                    panel.scrollTop = 0;
                    panel.scrollLeft = 0;
                }
            }

            function openDetail(title, rows, subtitle = '', context = {}) {
                resetDetailStage();
                els.detailTitle.textContent = title;
                els.detailSubtitle.textContent = subtitle || `${fmt.format(rows.length)} โครงการ`;
                els.detailBody.innerHTML = projectsTable(rows, context);
                resetDetailStage();
                els.detailModal?.classList.add('is-open');
                els.detailModal?.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(resetDetailStage);
            }

            function closeDetail() {
                els.detailModal?.classList.remove('is-open');
                els.detailModal?.setAttribute('aria-hidden', 'true');
                resetDetailStage();
                if (els.detailBody) els.detailBody.innerHTML = '';
                els.detailTitle.textContent = 'รายละเอียดข้อมูล';
                els.detailSubtitle.textContent = '';
                document.body.style.overflow = '';
            }

            function openChartDetail(id, chart, point) {
                const rows = filteredRows();
                const label = text(chart.data.labels?.[point.index], 'ไม่ระบุ');
                const dataset = text(chart.data.datasets?.[point.datasetIndex]?.label);
                let matched = [];
                let title = label;
                const context = { chartId: id, kind: modalKindForChart(id), focus: label, dataset };

                if (id === 'chartRedZoneTimeline') {
                    title = `RED ZONE ปี ${label}`;
                    matched = overdueProjects(rows).filter((p) => normalizeYearLabel(projectEndDate(p)?.getFullYear()) === label);
                } else if (id === 'chartContractOverview') {
                    title = `ภาพรวมสัญญา: ${label}`;
                    matched = rows.filter((p) => contractBucket(p).label === label);
                } else if (id === 'chartProjectsPerYear') {
                    title = `จำนวนโครงการปี ${label}`;
                    matched = rows.filter((p) => yearLabel(p) === label);
                } else if (id === 'chartLifecycle') {
                    title = `${dataset}: ${label}`;
                    matched = dataset === 'ใหม่'
                        ? rows.filter((p) => yearLabel(p) === label)
                        : rows.filter((p) => {
                            const start = yearNumber(yearLabel(p));
                            return start && start <= yearNumber(label);
                        });
                    if (dataset === 'กำลังดำเนินการ') matched = matched.filter(isActive);
                    if (dataset === 'ปิดโปรเจค') matched = matched.filter(isClosed);
                    if (dataset === 'ยกเลิก') matched = matched.filter(isCancelled);
                } else if (id === 'chartBudgetYear') {
                    title = `งบประมาณรวมปี ${label}`;
                    matched = rows.filter((p) => yearLabel(p) === label && budget(p) > 0);
                } else if (id === 'chartBudgetPolicyPiYear') {
                    title = `งบ PI หน่วยวิจัยฯ ปี ${label}`;
                    matched = rows.filter((p) => yearLabel(p) === label && isPolicyPi(p) && budget(p) > 0);
                } else if (id === 'chartFundingTypeYear') {
                    title = `${dataset} ปี ${label}`;
                    matched = rows.filter((p) => yearLabel(p) === label && fundingType(p) === dataset && budget(p) > 0);
                } else if (id === 'chartStatus' || id === 'chartOverdue') {
                    title = `สถานะ: ${label}`;
                    matched = (id === 'chartOverdue' ? overdueProjects(currentPastRows(rows)) : rows)
                        .filter((p) => projectStatusDisplayLabel(p.planning_status) === label || status(p) === label);
                } else if (id === 'chartCollab') {
                    title = `ความร่วมมือ: ${label}`;
                    matched = rows.filter((p) => collabDisplayLabel(collabRawLabel(p)) === label);
                } else if (id === 'chartFundingSplit') {
                    title = `ทุน: ${label}`;
                    matched = currentPastRows(rows).filter((p) => fundingType(p) === label && budget(p) > 0);
                } else if (id === 'chartFundingSource' || id === 'chartExternalBySource' || id === 'chartInternalBySource') {
                    title = `แหล่งทุน: ${label}`;
                    matched = currentPastRows(rows).filter((p) => fundingSource(p) === label && budget(p) > 0);
                    if (id === 'chartExternalBySource') matched = matched.filter((p) => fundingType(p) === 'ภายนอก');
                    if (id === 'chartInternalBySource') matched = matched.filter((p) => fundingType(p) === 'ภายใน');
                } else if (id === 'chartExternalBySourceYear' || id === 'chartInternalBySourceYear') {
                    title = `${dataset} ปี ${label}`;
                    const type = id === 'chartExternalBySourceYear' ? 'ภายนอก' : 'ภายใน';
                    matched = rows.filter((p) => yearLabel(p) === label && fundingType(p) === type && fundingSource(p) === dataset && budget(p) > 0);
                } else if (id === 'chartBudgetPeople') {
                    const personName = text(chart.$budgetPeopleLabels?.[point.index], label);
                    context.focus = personName;
                    const idGroups = chart.$budgetPeopleProjectIds?.[personName] || null;
                    const roleIds = idGroups
                        ? (dataset === 'PI' ? idGroups.pi : (dataset === 'Co-PI' ? idGroups.copi : idGroups.all))
                        : null;
                    const ids = Array.isArray(roleIds) && roleIds.length ? roleIds : idGroups?.all;
                    const titleRole = Array.isArray(roleIds) && roleIds.length ? dataset : 'PI / Co-PI';
                    title = `${titleRole}: ${personName}`;
                    if (Array.isArray(ids)) {
                        const idSet = new Set(ids.map((value) => String(value)));
                        matched = currentPastRows(rows).filter((p) => {
                            const projectKey = p.id ?? p.project_id ?? p.project_code ?? p.project_name_th;
                            return idSet.has(String(projectKey)) && budget(p) > 0;
                        });
                    } else {
                        matched = currentPastRows(rows).filter((p) => {
                            if (!(budget(p) > 0)) return false;
                            if (dataset === 'PI') return isPolicyPi(p) && text(p.pi_name) === personName;
                            return (Array.isArray(p.copis) ? p.copis : []).some((copi) => isPolicyCopi(copi) && text(copi.name) === personName);
                        });
                    }
                } else if (id === 'chartOutputType' || id === 'chartPublicationYear' || id === 'chartOutputStacked') {
                    const outputs = combinedOutputRecords(rows);
                    const selected = id === 'chartOutputType'
                        ? outputs.filter((o) => outputTypeLabel(o.type) === label)
                        : (id === 'chartOutputStacked'
                            ? outputs.filter((o) => outputYear(o) === label && outputTypeLabel(o.type) === dataset)
                            : outputs.filter((o) => outputYear(o) === label && isPublication(o.type)));
                    title = id === 'chartOutputType' ? `Output: ${label}` : (id === 'chartOutputStacked' ? `${dataset} ปี ${label}` : `Publication ปี ${label}`);
                    context.focus = id === 'chartOutputStacked' ? `${dataset} / ${label}` : label;
                    context.outputs = selected;
                    matched = selected.map((o) => o.project).filter(Boolean);
                } else if (id === 'chartPiUnit') {
                    title = `PI หน่วยงาน: ${label}`;
                    matched = currentPastRows(rows).filter((p) => text(p.pi_affiliation || p.unit_name || p.department, 'ไม่ระบุหน่วยงาน') === label);
                } else if (id === 'chartCoResearch') {
                    title = `Co-Research: ${label}`;
                    matched = rows.filter((p) => (Array.isArray(p.co_researches) ? p.co_researches : []).some((c) => text(c.unit_status || c.affiliation, 'ไม่ระบุหน่วยงาน') === label));
                } else if (id === 'chartIrb') {
                    title = `IRB ใกล้หมดอายุ: ${label}`;
                    const selectedIrbs = irbExpiring(rows).filter((irb) => {
                        const d = parseDate(irb.exp_date);
                        return d && `${d.getFullYear() + 543}-${String(d.getMonth() + 1).padStart(2, '0')}` === label;
                    });
                    context.irbs = selectedIrbs;
                    matched = selectedIrbs.map((irb) => irb.project).filter(Boolean);
                } else if (id === 'chartFtePeople') {
                    title = `FTE: ${dataset}`;
                    matched = rows.filter((p) => (Array.isArray(p.fte_roles) ? p.fte_roles : [])
                        .some((r) => isEligibleWorkloadFte(r) && text(r.name, 'ไม่ระบุชื่อ') === dataset));
                }

                const unique = Array.from(new Map(matched.map((p, index) => [p.id ?? p.project_id ?? p.project_code ?? `${p.project_name_th || 'project'}-${index}`, p])).values());
                openDetail(title, unique, '', context);
            }

            function activeFilterLabels() {
                return [
                    ['ปี', els.year?.value],
                    ['ลักษณะโครงการ (TYPE)', els.theme?.value],
                    ['กลุ่มเนื้อหาหลักของงาน (Content/Theme)', els.type?.value],
                    ['ทุน', els.funding?.value],
                    ['สถานะ', els.status?.value],
                    ['แหล่งทุน', els.source?.value],
                    ['ค้นหา', els.search?.value],
                ].filter(([, value]) => text(value) !== '');
            }

            function renderFilterState(rows) {
                const active = activeFilterLabels();
                const summary = active.length
                    ? `กำลังวิเคราะห์ ${fmt.format(rows.length)} โครงการ ตาม ${active.length} เงื่อนไขที่เลือก`
                    : `กำลังแสดงข้อมูลทั้งหมด ${fmt.format(rows.length)} โครงการในระบบ`;
                updateText('filterSummary', summary);
                if (canDownloadDashboardData) {
                    const outputCount = outputExportRows(rows).length;
                    const externalOutputCount = externalOutputExportRows().length;
                    const backOfficeCount = backOfficeExportRows().length;
                    updateText('downloadSummary', `ดาวน์โหลด ${fmt.format(rows.length)} โครงการ, ${fmt.format(outputCount + externalOutputCount)} outputs และ ${fmt.format(backOfficeCount)} back office ตามตัวกรองปัจจุบันเป็น Excel หรือ CSV`);
                }
                document.getElementById('activeFilterPills').innerHTML = active.length
                    ? active.map(([label, value]) => `<span class="di-pill">${escapeHtml(label)}: ${escapeHtml(value)}</span>`).join('')
                    : '<span class="di-pill">ทั้งหมด</span>';
            }

            function renderProjectOverview(rows) {
                const outputs = relatedOutputs(rows);
                const copis = rows.flatMap((p) => Array.isArray(p.copis) ? p.copis : []);
                const coResearches = rows.flatMap((p) => Array.isArray(p.co_researches) ? p.co_researches : []);
                const uniquePersonCount = (items, nameFn) => new Set(items.map((item) => normalizePersonKey(nameFn(item))).filter(Boolean)).size;
                const totalBudget = rows.reduce((sum, p) => sum + budget(p), 0);
                const funded = rows.filter((p) => budget(p) > 0);
                const policyPiFunded = rows.filter((p) => isPolicyPi(p) && budget(p) > 0);
                const policyPiBudget = policyPiFunded.reduce((sum, p) => sum + budget(p), 0);

                updateText('heroSummaryProjects', fmt.format(rows.length));
                updateText('heroSummaryPi', `${fmt.format(uniquePersonCount(rows, (p) => p.pi_name))} ท่าน`);
                updateText('heroSummaryCopi', `${fmt.format(uniquePersonCount(copis, (copi) => copi.name))} ท่าน`);
                updateText('heroSummaryCoResearch', `${fmt.format(uniquePersonCount(coResearches, (coResearch) => coResearch.name))} ท่าน`);
                updateText('heroSummaryOutputs', fmt.format(outputs.length));
                updateText('rtSummaryBudget', `${money.format(totalBudget)} บ.`);
                updateText('rtSummaryAvgBudget', funded.length ? `${money.format(totalBudget / funded.length)} บ.` : '0 บ.');
                updateText('rtSummaryPolicyPiBudget', `${money.format(policyPiBudget)} บ.`);
                updateText('rtSummaryPolicyPiCount', `${fmt.format(policyPiFunded.length)} โครงการที่มีงบ`);

                const summarySortKey = (label) => {
                    const value = String(label ?? '').trim();
                    const prefix = value.match(/^([A-Za-z]*)(\d+)/);

                    return {
                        text: value,
                        alpha: prefix ? prefix[1].toUpperCase() : '',
                        number: prefix ? Number(prefix[2]) : Number.POSITIVE_INFINITY,
                    };
                };
                const sortSummaryByLabelCode = (items) => items.slice().sort((a, b) => {
                    const ak = summarySortKey(a.label);
                    const bk = summarySortKey(b.label);

                    if (ak.number !== bk.number) return ak.number - bk.number;
                    if (ak.alpha !== bk.alpha) return ak.alpha.localeCompare(bk.alpha);
                    return ak.text.localeCompare(bk.text, 'th');
                });
                const buildSummaryRows = (labelFn) => sortSummaryByLabelCode(groupSum(rows, labelFn, () => 1)
                    .filter((item) => Number(item.value) > 0))
                    .slice(0, 12)
                    .map((item) => {
                        const scoped = rows.filter((p) => labelFn(p) === item.label);
                        const scopedBudget = scoped.reduce((sum, p) => sum + budget(p), 0);
                        return {
                            ...item,
                            percent: rows.length ? (item.value / rows.length) * 100 : 0,
                            budget: scopedBudget
                        };
                    });
                const renderSummaryTable = (elementId, summaryRows, emptyText, labelText) => {
                    const target = document.getElementById(elementId);
                    if (!target) return;

                    target.innerHTML = summaryRows.length ? summaryRows.map((item) => `
                        <tr>
                            <td data-label="${escapeHtml(labelText)}">${escapeHtml(item.label)}</td>
                            <td data-label="จำนวนโครงการ" class="text-right">${fmt.format(item.value)}</td>
                            <td data-label="สัดส่วน" class="text-right">${money.format(item.percent)}%</td>
                            <td data-label="งบรวม" class="text-right">${money.format(item.budget)} บ.</td>
                        </tr>
                    `).join('') : `<tr><td colspan="4" class="text-center text-slate-500">${escapeHtml(emptyText)}</td></tr>`;
                };
                const contentThemeRows = buildSummaryRows((p) => text(p.project_type, 'ไม่ระบุ'));
                const typeRows = buildSummaryRows((p) => text(p.theme, 'ไม่ระบุ'));

                renderSummaryTable(
                    'rtContentThemeSummaryBody',
                    contentThemeRows,
                    'ยังไม่มีข้อมูลกลุ่มเนื้อหาหลักของงาน',
                    'Content/Theme'
                );
                renderSummaryTable(
                    'rtTypeSummaryBody',
                    typeRows,
                    'ยังไม่มีข้อมูลลักษณะโครงการ',
                    'TYPE'
                );
            }

            function overdueProjects(rows) {
                const today = new Date();
                return rows.filter((p) => {
                    if (!isActive(p)) return false;
                    const end = parseDate(p.team_end_date || p.end_date || p.finish_date);
                    return end && end < today;
                });
            }

            function contractBucket(p) {
                const end = projectEndDate(p);
                const now = new Date();
                if (isCancelled(p)) return { label: 'ยกเลิกโปรเจค', color: '#f97316' };
                if (isClosed(p)) return { label: 'ปิดโปรเจคแล้ว', color: '#22c55e' };
                if (!end) return { label: 'ข้อมูลไม่ครบ', color: '#f59e0b' };
                const days = Math.ceil((end - now) / 86400000);
                if (days < 0) return { label: 'เกินกำหนดและยังไม่ปิดโปรเจค', color: '#ef4444' };
                if (days <= 90) return { label: 'ใกล้ครบกำหนดใน 3 เดือน', color: '#8b5cf6' };
                return { label: 'ยังไม่ครบกำหนดสัญญา', color: '#0ea5e9' };
            }

            function irbExpiring(rows) {
                const now = new Date();
                const limit = new Date(now);
                limit.setMonth(limit.getMonth() + 6);
                return rows.flatMap((p) => (Array.isArray(p.irbs) ? p.irbs : []).map((irb) => ({ ...irb, project: p })))
                    .filter((irb) => {
                        const d = parseDate(irb.exp_date);
                        return d && d >= now && d <= limit;
                    });
            }

            function renderPortfolio(rows) {
                const analysisRows = currentPastRows(rows);
                const recentYears = Array.from({ length: 3 }, (_, index) => String(CURRENT_BE_YEAR - (2 - index)));
                const overdue = overdueProjects(analysisRows);
                renderChart('chartRedZoneTimeline', {
                    type: 'bar',
                    data: { labels: recentYears, datasets: [barDataset('เกินกำหนด', recentYears.map((year) => overdue.filter((p) => normalizeYearLabel(projectEndDate(p)?.getFullYear()) === year).length), '#ef4444')] },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const contractRows = top(groupSum(rows, (p) => contractBucket(p).label), 8);
                renderChart('chartContractOverview', {
                    type: 'bar',
                    data: { labels: contractRows.map((x) => x.label), datasets: [barDataset('จำนวนโครงการ', contractRows.map((x) => x.value), '#f59e0b')] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const years = yearsFromRows(analysisRows);
                const { visible: chartYears, hidden: hiddenYears } = visibleYearWindow(years, 10);
                renderChart('chartProjectsPerYear', {
                    type: 'bar',
                    data: { labels: chartYears, datasets: [barDataset('จำนวนโครงการ', chartYears.map((year) => analysisRows.filter((p) => yearLabel(p) === year).length), '#0ea5e9')] },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const projectPlanText = futureYearPlanSummary(rows);
                const projectYearNote = formatHiddenYearsSummary(hiddenYears, analysisRows);
                updateText('chartProjectsPerYearNote', projectPlanText ? `${projectYearNote}. ${projectPlanText}` : projectYearNote);
                const yearlyGroups = chartYears.map((year) => yearlyProjectGroups(rows, yearNumber(year)));
                const lifecycleCumulativeRows = analysisRows.filter((p) => {
                    const start = yearNumber(yearLabel(p));
                    return start && start <= CURRENT_BE_YEAR;
                });
                const lifecycleFutureRows = rows.filter((p) => isFutureYearLabel(yearLabel(p)));
                renderChart('chartLifecycle', {
                    type: 'bar',
                    data: {
                        labels: chartYears,
                        datasets: [
                            {
                                type: 'line',
                                label: 'ทั้งหมดสะสม',
                                data: chartYears.map((year) => analysisRows.filter((p) => {
                                    const start = yearNumber(yearLabel(p));
                                    return start && start <= yearNumber(year);
                                }).length),
                                borderColor: '#2563eb',
                                backgroundColor: '#2563eb',
                                borderWidth: 3,
                                tension: 0.32,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                order: 0,
                            },
                            barDataset('ใหม่', yearlyGroups.map((group) => group.newProjects.length), '#34d399'),
                            barDataset('กำลังดำเนินการ', yearlyGroups.map((group) => group.ongoingProjects.length), '#fbbf24'),
                            barDataset('ปิดโปรเจค', chartYears.map((year) => analysisRows.filter((p) => {
                                const start = yearNumber(yearLabel(p));
                                return start && start <= yearNumber(year) && isClosed(p);
                            }).length), '#ef4444'),
                            barDataset('ยกเลิก', chartYears.map((year) => analysisRows.filter((p) => {
                                const start = yearNumber(yearLabel(p));
                                return start && start <= yearNumber(year) && isCancelled(p);
                            }).length), '#f97316'),
                        ]
                    },
                    options: { plugins: { legend: { position: 'bottom' }, diStackTotalLabel: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                updateText('chartLifecycleNote', [
                    hiddenYears.length
                        ? formatHiddenYearsSummary(hiddenYears, analysisRows)
                        : 'แสดงครบทุกปีที่มีข้อมูลถึงปีปัจจุบัน',
                    `เส้นสะสมถึงปี ${CURRENT_BE_YEAR_LABEL} รวม ${fmt.format(lifecycleCumulativeRows.length)} โครงการ`,
                    lifecycleFutureRows.length ? `มีโครงการ planning ในอนาคต ${fmt.format(lifecycleFutureRows.length)} โครงการที่ยังไม่ถูกรวมในเส้นสะสม` : '',
                ].filter(Boolean).join('. '));
                const statusRows = projectStatusChartRows(rows);
                renderChart('chartStatus', {
                    type: 'bar',
                    data: { labels: statusRows.map((x) => x.label), datasets: [barDataset('จำนวนโครงการ', statusRows.map((x) => x.value), '#7c3aed')] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const collabRows = top(groupSum(rows, (p) => collabDisplayLabel(collabRawLabel(p))), 6);
                renderChart('chartCollab', {
                    type: 'bar',
                    data: { labels: collabRows.map((x) => x.label), datasets: [barDataset('จำนวนโครงการ', collabRows.map((x) => x.value), '#14b8a6')] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const piUnitRows = top(groupSum(analysisRows, (p) => text(p.pi_affiliation || p.unit_name || p.department, 'ไม่ระบุหน่วยงาน')), 12);
                renderChart('chartPiUnit', {
                    type: 'bar',
                    data: { labels: piUnitRows.map((x) => x.label), datasets: [barDataset('จำนวนโครงการ', piUnitRows.map((x) => x.value), '#2563eb')] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const mainStatus = top(groupSum(rows, (p) => projectStatusDisplayLabel(p.planning_status)), 1)[0];
                const activeCount = rows.filter(isActive).length;
                const closedCount = rows.filter(isClosed).length;
                const cancelledCount = rows.filter(isCancelled).length;
                const currentYear = CURRENT_BE_YEAR_LABEL;
                const previousYear = PREVIOUS_BE_YEAR_LABEL;
                const currentYearCount = analysisRows.filter((p) => yearLabel(p) === currentYear).length;
                const previousYearCount = analysisRows.filter((p) => yearLabel(p) === previousYear).length;
                const twoYearCount = currentYearCount + previousYearCount;
                const twoYearShare = analysisRows.length ? Math.round((twoYearCount / analysisRows.length) * 100) : 0;
                const redCount = overdue.length;
                const collabTop = collabRows[0];
                const topPiUnit = piUnitRows[0];
                const peakYear = top(groupSum(analysisRows, yearLabel), 1)[0];
                const futurePlanText = futureYearPlanSummary(rows);
                const redTrend = recentYears.map((year) => overdue.filter((p) => normalizeYearLabel(projectEndDate(p)?.getFullYear()) === year).length);
                const redDelta = redTrend.length > 1 ? redTrend[redTrend.length - 1] - redTrend[redTrend.length - 2] : 0;
                const redTrendText = redTrend.length > 1
                    ? `${redDelta === 0 ? 'ทรงตัวจาก' : (redDelta > 0 ? `เพิ่มขึ้น ${fmt.format(redDelta)} โครงการจาก` : `ลดลง ${fmt.format(Math.abs(redDelta))} โครงการจาก`)}ปีก่อน`
                    : 'ยังไม่มีข้อมูลย้อนหลังให้เทียบ';
                const statusShare = rows.length && mainStatus ? Math.round((mainStatus.value / rows.length) * 100) : 0;
                const activeShare = rows.length ? Math.round((activeCount / rows.length) * 100) : 0;
                const incompleteContract = contractRows.find((item) => item.label === 'ข้อมูลไม่ครบ');
                const incompleteText = incompleteContract && incompleteContract.value > 0
                    ? `อย่างไรก็ตาม กราฟสถานะตามสัญญาชี้ว่ามีรายการ “ข้อมูลไม่ครบ” ${fmt.format(incompleteContract.value)} โครงการ จึงควรอ่าน trend ควบคู่กับคุณภาพข้อมูลวันเริ่มต้น/วันสิ้นสุดโครงการ`
                    : 'ข้อมูลตามสัญญามีความพร้อมพอสำหรับอ่านจังหวะติดตามในภาพรวม';
                if (els.portfolioSummaryList) {
                    els.portfolioSummaryList.innerHTML = [
                        ['โครงการใน scope', `${fmt.format(rows.length)} โครงการ`],
                        ['Active', `${fmt.format(activeCount)} โครงการ`],
                        ['ควรติดตาม', `${fmt.format(redCount)} โครงการ`],
                        ['ปีนี้ + ปีก่อน', `${fmt.format(twoYearCount)} โครงการ`],
                    ].map(([label, value]) => `<span>${escapeHtml(label)} <b>${escapeHtml(value)}</b></span>`).join('');
                }
                updateText('portfolioWhat', mainStatus
                    ? `ตอนนี้มีโครงการวิจัยใน scope ${fmt.format(rows.length)} โครงการ สถานะที่เห็นมากที่สุดคือ “${mainStatus.label}” ${fmt.format(mainStatus.value)} โครงการ (${fmt.format(statusShare)}%). ถ้าดูเฉพาะช่วงปีนี้กับปีก่อน (${previousYear}-${currentYear}) จะมีโครงการรวม ${fmt.format(twoYearCount)} โครงการ หรือประมาณ ${fmt.format(twoYearShare)}% ของพอร์ต เพราะปีนี้ยังไม่จบจึงไม่ควรอ่านเป็นแนวโน้มทั้งปีแบบเด็ดขาด. ปีที่งานเข้ามามากที่สุดโดยนับถึงปีปัจจุบันคือ ${peakYear?.label || '-'}${peakYear ? ` จำนวน ${fmt.format(peakYear.value)} โครงการ` : ''}.${futurePlanText ? ` ${futurePlanText}.` : ''}`
                    : 'ยังไม่มีข้อมูลสถานะโครงการในชุดนี้');
                updateText('portfolioReflection', `ภาระงานของหน่วยไม่ได้อยู่ที่จำนวนโครงการใหม่อย่างเดียว แต่ยังมีโครงการ active ที่ต้องดูแลต่อ ${fmt.format(activeCount)} โครงการ (${fmt.format(activeShare)}%) ควบคู่กับโครงการที่ปิดแล้ว ${fmt.format(closedCount)} โครงการ${cancelledCount ? ` และยกเลิก ${fmt.format(cancelledCount)} โครงการ` : ''}. กลุ่ม RED ZONE มี ${fmt.format(redCount)} โครงการ โดยอ่านช่วงปีก่อนถึงปีนี้ (${previousYear}-${currentYear}) แล้ว ${redTrendText}; ควรใช้เป็นรายการตั้งต้นสำหรับติดตามสัญญา IRB และวันสิ้นสุดการทำงานของทีมวิจัย. ${incompleteText}.${collabTop ? ` กราฟสัดส่วนความร่วมมือชี้ว่ารูปแบบหลักคือ “${collabTop.label}” ${fmt.format(collabTop.value)} โครงการ จึงต้องอ่านคู่กับกราฟจำนวนงานของ PI ตามหน่วยงาน` : ''}${topPiUnit ? ` ซึ่งตอนนี้งานกระจุกมากที่สุดที่ “${topPiUnit.label}” ${fmt.format(topPiUnit.value)} โครงการ เพื่อแยกว่าภาระประสานงานอยู่กับเครือข่าย/หน่วยงานใดเป็นหลัก.` : '.'}`);
            }

            function renderFunding(rows) {
                const chartFundingRows = currentPastRows(rows);
                const yearRows = top(groupSum(chartFundingRows, yearLabel, budget), 99).sort((a, b) => Number(a.label.match(/\d{4}/)?.[0] || 99999) - Number(b.label.match(/\d{4}/)?.[0] || 99999));
                const cleanYearRows = yearRows.filter((item) => item.label !== 'ไม่ระบุปี');
                renderChart('chartBudgetYear', {
                    type: 'bar',
                    data: { labels: cleanYearRows.map((x) => x.label), datasets: [barDataset('งบประมาณรวม', cleanYearRows.map((x) => x.value / 1_000_000), FUNDING_COLORS.total)] },
                    options: { plugins: { legend: { display: false }, diValueLabel: { formatter: (value) => `${money.format(value)} ลบ.` } }, scales: { y: { beginAtZero: true, title: { display: true, text: 'ล้านบาท' } } } }
                });
                const policyPiYearRows = top(groupSum(chartFundingRows.filter(isPolicyPi), yearLabel, budget), 99)
                    .filter((item) => item.label !== 'ไม่ระบุปี')
                    .sort((a, b) => Number(a.label.match(/\d{4}/)?.[0] || 99999) - Number(b.label.match(/\d{4}/)?.[0] || 99999));
                renderChart('chartBudgetPolicyPiYear', {
                    type: 'bar',
                    data: { labels: policyPiYearRows.map((x) => x.label), datasets: [barDataset('PI หน่วยวิจัยฯ', policyPiYearRows.map((x) => x.value / 1_000_000), FUNDING_COLORS.policyPi)] },
                    options: { plugins: { legend: { display: false }, diValueLabel: { formatter: (value) => `${money.format(value)} ลบ.` } }, scales: { y: { beginAtZero: true, title: { display: true, text: 'ล้านบาท' } } } }
                });
                const split = top(groupSum(chartFundingRows, fundingType, budget), 6);
                renderChart('chartFundingSplit', {
                    type: 'bar',
                    data: { labels: split.map((x) => x.label), datasets: [barDataset('ล้านบาท', split.map((x) => x.value / 1_000_000), split.map((x) => fundingTypeColor(x.label)))] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false }, diValueLabel: { formatter: (value) => `${money.format(value)} ลบ.` } }, scales: { x: { beginAtZero: true, title: { display: true, text: 'ล้านบาท' } } } }
                });
                const sourceRows = top(groupSum(chartFundingRows, fundingSource, budget), 8);
                renderChart('chartFundingSource', {
                    type: 'bar',
                    data: { labels: sourceRows.map((x) => x.label), datasets: [barDataset('ล้านบาท', sourceRows.map((x) => x.value / 1_000_000), sourceRows.map((x) => fundingSourceColor(x.label)))] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false }, diValueLabel: { formatter: (value) => `${money.format(value)} ลบ.` } }, scales: { x: { beginAtZero: true } } }
                });
                ['ภายนอก', 'ภายใน'].forEach((type, idx) => {
                    const canvasId = idx === 0 ? 'chartExternalBySource' : 'chartInternalBySource';
                    const sourceSplitRows = top(groupSum(chartFundingRows.filter((p) => fundingType(p) === type), fundingSource, budget), 8);
                    renderChart(canvasId, {
                        type: 'bar',
                        data: { labels: sourceSplitRows.map((x) => x.label), datasets: [barDataset('ล้านบาท', sourceSplitRows.map((x) => x.value / 1_000_000), sourceSplitRows.map((x) => fundingSourceColor(x.label)))] },
                        options: { indexAxis: 'y', plugins: { legend: { display: false }, diValueLabel: { formatter: (value) => value ? `${money.format(value)} ลบ.` : '' } }, scales: { x: { beginAtZero: true, title: { display: true, text: 'ล้านบาท' } } } }
                    });
                });
                const fundingTrendRows = chartFundingRows.filter((p) => ['ภายใน', 'ภายนอก'].includes(fundingType(p)) && budget(p) > 0);
                const fundingYears = activeYearsFor(fundingTrendRows, budget);
                renderChart('chartFundingTypeYear', {
                    type: 'bar',
                    data: {
                        labels: fundingYears,
                        datasets: [
                            barDataset('ภายใน', fundingYears.map((year) => fundingTrendRows.filter((p) => yearLabel(p) === year && fundingType(p) === 'ภายใน').reduce((sum, p) => sum + budget(p), 0) / 1_000_000), FUNDING_COLORS.internal),
                            barDataset('ภายนอก', fundingYears.map((year) => fundingTrendRows.filter((p) => yearLabel(p) === year && fundingType(p) === 'ภายนอก').reduce((sum, p) => sum + budget(p), 0) / 1_000_000), FUNDING_COLORS.external),
                        ]
                    },
                    options: { plugins: { legend: { position: 'bottom' }, diValueLabel: { formatter: (value) => value > 0 ? `${money.format(value)} ลบ.` : '' }, diStackTotalLabel: { display: false } }, scales: { y: { beginAtZero: true, title: { display: true, text: 'ล้านบาท' } } } }
                });
                ['ภายนอก', 'ภายใน'].forEach((type, idx) => {
                    const canvasId = idx === 0 ? 'chartExternalBySourceYear' : 'chartInternalBySourceYear';
                    const typedRows = chartFundingRows.filter((p) => fundingType(p) === type && budget(p) > 0);
                    renderChart(canvasId, {
                        type: 'bar',
                        data: {
                            labels: fundingYears,
                            datasets: stackDatasets(typedRows, fundingYears, fundingSource, (p) => budget(p) / 1_000_000, 6, (source) => fundingSourceColor(source))
                        },
                        options: { plugins: { legend: { position: 'bottom' }, diValueLabel: { display: false }, diStackTotalLabel: { display: true, formatter: (value) => `${money.format(value)} ลบ.` } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, title: { display: true, text: 'ล้านบาท' } } } }
                    });
                });
                const people = new Map();
                const addPerson = (name, role, amount, projectKey) => {
                    const key = text(name, 'ไม่ระบุชื่อ');
                    if (!people.has(key)) {
                        people.set(key, {
                            name: key,
                            pi: 0,
                            copi: 0,
                            projectIds: new Set(),
                            piProjectIds: new Set(),
                            copiProjectIds: new Set(),
                        });
                    }
                    people.get(key)[role] += amount;
                    if (projectKey !== undefined && projectKey !== null && String(projectKey).trim() !== '') {
                        people.get(key).projectIds.add(String(projectKey));
                        if (role === 'pi') people.get(key).piProjectIds.add(String(projectKey));
                        if (role === 'copi') people.get(key).copiProjectIds.add(String(projectKey));
                    }
                };
                chartFundingRows.forEach((p) => {
                    const amount = budget(p);
                    if (!(amount > 0)) return;
                    const projectKey = p.id ?? p.project_id ?? p.project_code ?? p.project_name_th;
                    if (isPolicyPi(p) && text(p.pi_name)) addPerson(p.pi_name, 'pi', amount, projectKey);
                    const seenCopis = new Set();
                    (Array.isArray(p.copis) ? p.copis : []).forEach((copi) => {
                        const name = text(copi.name);
                        if (!isPolicyCopi(copi) || !name || seenCopis.has(name)) return;
                        seenCopis.add(name);
                        addPerson(name, 'copi', amount, projectKey);
                    });
                });
                const peopleRows = Array.from(people.values())
                    .map((x) => ({
                        ...x,
                        total: x.pi + x.copi,
                        projectCount: x.projectIds.size,
                        piProjectCount: x.piProjectIds.size,
                        copiProjectCount: x.copiProjectIds.size,
                    }))
                    .sort((a, b) => b.total - a.total)
                    .slice(0, 15);
                renderChart('chartBudgetPeople', {
                    type: 'bar',
                    data: {
                        labels: peopleRows.map((x) => [
                            x.name,
                            `PI: ${fmt.format(x.piProjectCount || 0)} โครงการ | Co-PI: ${fmt.format(x.copiProjectCount || 0)} โครงการ`,
                        ]),
                        datasets: [
                            barDataset('PI', peopleRows.map((x) => x.pi / 1_000_000), FUNDING_COLORS.pi),
                            barDataset('Co-PI', peopleRows.map((x) => x.copi / 1_000_000), FUNDING_COLORS.copi),
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        plugins: {
                            legend: { position: 'bottom' },
                            diValueLabel: { display: false },
                            diStackTotalLabel: { display: true, formatter: (value) => `${money.format(value)} ลบ.` }
                        },
                        layout: { padding: { right: 72 } },
                        scales: {
                            x: { stacked: true, beginAtZero: true, grace: '14%' },
                            y: { stacked: true, offset: true, ticks: { autoSkip: false, padding: 14, font: { size: 10 }, lineHeight: 1.45 } }
                        }
                    }
                });
                const budgetPeopleChart = chartMap.get('chartBudgetPeople');
                if (budgetPeopleChart) {
                    budgetPeopleChart.$budgetPeopleLabels = peopleRows.map((x) => x.name);
                    budgetPeopleChart.$budgetPeopleProjectIds = Object.fromEntries(peopleRows.map((x) => [
                        x.name,
                        {
                            pi: Array.from(x.piProjectIds),
                            copi: Array.from(x.copiProjectIds),
                            all: Array.from(x.projectIds),
                        }
                    ]));
                }
                const totalBudget = chartFundingRows.reduce((sum, p) => sum + budget(p), 0);
                const topSource = sourceRows[0];
                const fundedRows = chartFundingRows.filter((p) => budget(p) > 0);
                const externalBudget = chartFundingRows.filter((p) => fundingType(p) === 'ภายนอก').reduce((sum, p) => sum + budget(p), 0);
                const internalBudget = chartFundingRows.filter((p) => fundingType(p) === 'ภายใน').reduce((sum, p) => sum + budget(p), 0);
                const policyPiBudget = chartFundingRows.filter(isPolicyPi).reduce((sum, p) => sum + budget(p), 0);
                const externalShare = totalBudget ? Math.round((externalBudget / totalBudget) * 100) : 0;
                const internalShare = totalBudget ? Math.round((internalBudget / totalBudget) * 100) : 0;
                const policyPiShare = totalBudget ? Math.round((policyPiBudget / totalBudget) * 100) : 0;
                const peakBudgetYear = cleanYearRows.reduce((best, item) => (!best || item.value > best.value ? item : best), null);
                const latestBudgetYear = cleanYearRows[cleanYearRows.length - 1];
                const topPerson = peopleRows[0];
                if (els.fundingSummaryList) {
                    els.fundingSummaryList.innerHTML = [
                        ['งบรวม', `${money.format(totalBudget / 1_000_000)} ลบ.`],
                        ['โครงการมีงบ', `${fmt.format(fundedRows.length)} โครงการ`],
                        ['งบภายนอก', `${money.format(externalBudget / 1_000_000)} ลบ.`],
                        ['งบภายใน', `${money.format(internalBudget / 1_000_000)} ลบ.`],
                        ['งบที่มาจาก PI หน่วยวิจัยฯ', `${money.format(policyPiBudget / 1_000_000)} ลบ.`],
                    ].map(([label, value]) => `<span>${escapeHtml(label)} <b>${escapeHtml(value)}</b></span>`).join('');
                }
                updateText('fundingWhat', totalBudget > 0
                    ? `ในชุดโครงการที่เลือก มีโครงการที่ระบุงบประมาณถึงปีปัจจุบัน ${fmt.format(fundedRows.length)} โครงการ รวม ${money.format(totalBudget / 1_000_000)} ล้านบาท ปีที่งบเข้ามาสูงสุดคือ ${peakBudgetYear?.label || '-'}${peakBudgetYear ? ` (${money.format(peakBudgetYear.value / 1_000_000)} ล้านบาท)` : ''}. ปีล่าสุดที่มีข้อมูลงบโดยไม่นับปีอนาคตคือ ${latestBudgetYear?.label || '-'}${latestBudgetYear ? ` อยู่ที่ ${money.format(latestBudgetYear.value / 1_000_000)} ล้านบาท` : ''} ซึ่งควรอ่านร่วมกับจำนวนโครงการและช่วงเวลารับทุน ไม่ใช่ดูยอดเงินอย่างเดียว.`
                    : 'ยังไม่มีข้อมูลงบประมาณในชุดข้อมูลนี้');
                updateText('fundingReflection', topSource
                    ? `โครงสร้างทุนของพอร์ตนี้มาจากงบภายนอก ${money.format(externalBudget / 1_000_000)} ล้านบาท (${fmt.format(externalShare)}%) และงบภายใน ${money.format(internalBudget / 1_000_000)} ล้านบาท (${fmt.format(internalShare)}%). งบที่หน่วยวิจัยฯ เป็น PI หลักคิดเป็น ${money.format(policyPiBudget / 1_000_000)} ล้านบาท หรือประมาณ ${fmt.format(policyPiShare)}% ของงบรวม แปลว่าหน่วยไม่ได้เป็นเพียงผู้ร่วมในโครงการ แต่ถือบทบาทนำในงบจำนวนมาก. แหล่งทุนที่มีน้ำหนักสูงสุดคือ “${topSource.label}” ${money.format(topSource.value / 1_000_000)} ล้านบาท${topPerson ? ` ส่วน PI/Co-PI ที่ถือวงเงินสูงสุดในกราฟคือ ${topPerson.name} ${money.format(topPerson.total / 1_000_000)} ล้านบาท` : ''}.`
                    : 'ยังไม่มีข้อมูลแหล่งทุนเพียงพอสำหรับวิเคราะห์โครงสร้างทุน');
            }

            function authorRoleLabel(role) {
                const key = norm(role);
                if (key === 'first') return 'First author';
                if (key === 'co') return 'Co-author';
                if (key === 'corresponding') return 'Corresponding author';
                return text(role, 'ไม่ระบุบทบาท');
            }

            function authorYearsForSelection(selection) {
                const years = Array.from(new Set(String(selection || '').match(/\d{4}/g) || []));
                if (years.length === 2 && String(selection || '').includes('-')) {
                    const [start, end] = years.map(Number).sort((a, b) => a - b);
                    return Array.from({ length: end - start + 1 }, (_, index) => String(start + index));
                }
                return years;
            }

            function isUnitAuthor(author) {
                return author?.is_unit_user === true;
            }

            function authorRowsForYear(publications, year) {
                const selectedYears = authorYearsForSelection(year);
                const map = new Map();
                publications.filter((o) => selectedYears.includes(outputYear(o))).forEach((o) => {
                    (Array.isArray(o.authors) ? o.authors : []).filter(isUnitAuthor).forEach((a) => {
                        const name = text(a.name);
                        const role = norm(a.role);
                        if (!name) return;
                        if (!map.has(name)) map.set(name, { name, first: 0, co: 0, corresponding: 0 });
                        const item = map.get(name);
                        if (role === 'first') item.first++;
                        if (role === 'co') item.co++;
                        if (role === 'corresponding') item.corresponding++;
                    });
                });
                return Array.from(map.values())
                    .map((item) => ({ ...item, total: item.first + item.co + item.corresponding }))
                    .sort((a, b) => b.total - a.total);
            }

            function authorOutputsForSelection(publications, selectedYear, authorName) {
                const selectedYears = authorYearsForSelection(selectedYear);
                const target = norm(authorName);
                return publications
                    .filter((o) => selectedYears.includes(outputYear(o)))
                    .map((o) => {
                        const roles = (Array.isArray(o.authors) ? o.authors : [])
                            .filter((a) => isUnitAuthor(a) && norm(a.name) === target)
                            .map((a) => authorRoleLabel(a.role));
                        return { output: o, roles };
                    })
                    .filter((item) => item.roles.length > 0);
            }

            function openAuthorDetail(authorName, row, publications) {
                const selected = els.authorYear.value;
                const items = authorOutputsForSelection(publications, selected, authorName);
                const projectsInvolved = new Set(items.map((item) => item.output.project?.id).filter(Boolean)).size;
                const title = `บทบาทผู้เขียน: ${authorName}`;
                const subtitle = `${selected} · ${fmt.format(items.length)} ผลงาน`;
                resetDetailStage();
                els.detailTitle.textContent = title;
                els.detailSubtitle.textContent = subtitle;
                els.detailBody.innerHTML = items.length ? `
                    <div class="di-modal-content di-modal-content--output">
                        <div class="di-modal-summary">
                            <div class="di-modal-kpi"><span>ผลงานรวม</span><strong>${fmt.format(row.total)} ครั้ง</strong></div>
                            <div class="di-modal-kpi"><span>First Author</span><strong>${fmt.format(row.first)} ครั้ง</strong></div>
                            <div class="di-modal-kpi"><span>Co-author</span><strong>${fmt.format(row.co)} ครั้ง</strong></div>
                            <div class="di-modal-kpi"><span>Corresponding</span><strong>${fmt.format(row.corresponding)} ครั้ง</strong></div>
                        </div>
                        <div class="di-modal-summary">
                            <div class="di-modal-kpi"><span>ผลงานที่พบ</span><strong>${fmt.format(items.length)} รายการ</strong></div>
                            <div class="di-modal-kpi"><span>โครงการต้นทาง</span><strong>${fmt.format(projectsInvolved)} โครงการ</strong></div>
                            <div class="di-modal-kpi"><span>ช่วงปี</span><strong>${escapeHtml(selected)}</strong></div>
                            <div class="di-modal-kpi"><span>บทบาทเด่น</span><strong>${escapeHtml(authorDominantRole(row))}</strong></div>
                        </div>
                        <div class="di-modal-table-wrap">
                            <table class="di-table di-modal-table">
                                <thead>
                                    <tr>
                                        <th>ผลงาน</th>
                                        <th>บทบาท</th>
                                        <th>ปี</th>
                                        <th>โครงการ</th>
                                        <th>Journal/แหล่งเผยแพร่</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.map(({ output, roles }) => {
                                        const project = output.project;
                                        const projectName = text(project?.project_name_th || output.project_name, '-');
                                        const projectCell = project?.id
                                            ? `<a href="${projectUrl(project.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(projectName)}</a>`
                                            : (output.url
                                                ? `<a href="${escapeHtml(output.url)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(projectName)}</a>`
                                                : escapeHtml(projectName));
                                        return `
                                            <tr>
                                                <td data-label="ผลงาน">${escapeHtml(text(output.title || output.output_title || output.output_code, '-'))}</td>
                                                <td data-label="บทบาท">${escapeHtml(Array.from(new Set(roles)).join(', '))}</td>
                                                <td data-label="ปี">${escapeHtml(outputYear(output))}</td>
                                                <td data-label="โครงการ">${projectCell}</td>
                                                <td data-label="Journal/แหล่งเผยแพร่">${escapeHtml(text(output.journal || output.source || output.publisher || output.source_label, '-'))}</td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : '<div class="di-empty">ไม่พบผลงานของผู้เขียนคนนี้ในเงื่อนไขที่เลือก</div>';
                els.detailModal?.classList.add('is-open');
                els.detailModal?.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                resetDetailStage();
                requestAnimationFrame(resetDetailStage);
            }

            function authorDominantRole(row) {
                if (!row) return '-';
                const roles = [
                    ['first', row.first],
                    ['co', row.co],
                    ['corresponding', row.corresponding],
                ].sort((a, b) => b[1] - a[1]);
                return authorRoleLabel(roles[0]?.[0]);
            }

            function authorYearNarrative(publications, selectedYear) {
                if (!selectedYear) return 'กราฟบทบาทผู้เขียนยังไม่มีปีผลงานให้เปรียบเทียบ';
                const selectedYears = authorYearsForSelection(selectedYear).sort();
                const selectedRows = authorRowsForYear(publications, selectedYear);
                const selectedTop = selectedRows[0];
                if (!selectedTop) return `ในกราฟบทบาทผู้เขียน ${selectedYear} ยังไม่มีข้อมูลผู้เขียนให้สรุป`;
                if (selectedYears.length > 1) {
                    const firstYear = selectedYears[0];
                    const lastYear = selectedYears[selectedYears.length - 1];
                    const firstTop = authorRowsForYear(publications, firstYear)[0];
                    const lastTop = authorRowsForYear(publications, lastYear)[0];
                    const compareText = firstTop && lastTop
                        ? `ถ้าแยกดูรายปี ปี ${firstYear} ผู้ที่เด่นคือ ${firstTop.name} (${authorDominantRole(firstTop)}, ${fmt.format(firstTop.total)} ครั้ง) ส่วนปี ${lastYear} คือ ${lastTop.name} (${authorDominantRole(lastTop)}, ${fmt.format(lastTop.total)} ครั้ง).`
                        : 'ข้อมูลรายปีในช่วงนี้ยังไม่พอสำหรับเปรียบเทียบผู้เขียนเด่นแยกปี.';
                    return `ในกราฟบทบาทผู้เขียนช่วง ${selectedYear} ผู้ที่มีบทบาทรวมสูงสุดคือ ${selectedTop.name} รวม ${fmt.format(selectedTop.total)} ครั้ง โดยบทบาทหลักคือ ${authorDominantRole(selectedTop)}. ${compareText}`;
                }
                const previousYear = String(Number(selectedYears[0] || 0) - 1);
                const previousRows = authorRowsForYear(publications, previousYear);
                const previousTop = previousRows[0];
                const diff = previousTop ? selectedTop.total - previousTop.total : selectedTop.total;
                const compareText = previousTop
                    ? `เทียบกับปี ${previousYear} ที่ผู้เขียนเด่นคือ ${previousTop.name} (${authorDominantRole(previousTop)}, ${fmt.format(previousTop.total)} ครั้ง) ปี ${selectedYear} ${diff === 0 ? 'อยู่ในระดับใกล้เคียงกัน' : (diff > 0 ? `สูงขึ้น ${fmt.format(diff)} ครั้ง` : `ลดลง ${fmt.format(Math.abs(diff))} ครั้ง`)}`
                    : `ยังไม่มีข้อมูลปี ${previousYear} ให้เทียบโดยตรง`;
                return `ในกราฟบทบาทผู้เขียน ปี ${selectedYear} ผู้ที่มีบทบาทเด่นที่สุดคือ ${selectedTop.name} รวม ${fmt.format(selectedTop.total)} ครั้ง โดยบทบาทหลักคือ ${authorDominantRole(selectedTop)}. ${compareText}.`;
            }

            function outputAuthorReflection(publications) {
                const pubRows = top(groupSum(publications, outputYear), 99)
                    .filter((item) => item.label !== 'ไม่ระบุปี')
                    .filter((item) => !isFutureYearLabel(item.label))
                    .sort((a, b) => Number(a.label.match(/\d{4}/)?.[0] || 99999) - Number(b.label.match(/\d{4}/)?.[0] || 99999));
                const peakPubYear = pubRows.reduce((best, item) => (!best || item.value > best.value ? item : best), null);
                const authorRoleRows = Array.from(publications.reduce((map, o) => {
                    (Array.isArray(o.authors) ? o.authors : []).filter(isUnitAuthor).forEach((a) => {
                        const role = authorRoleLabel(a.role);
                        map.set(role, (map.get(role) || 0) + 1);
                    });
                    return map;
                }, new Map()).entries()).map(([label, value]) => ({ label, value })).sort((a, b) => b.value - a.value);
                const topRole = authorRoleRows[0];
                return `ในกลุ่มผลงานวิชาการ มี publication ${fmt.format(publications.length)} รายการ${peakPubYear ? ` โดยปีที่ publication เด่นที่สุดคือ ${peakPubYear.label} (${fmt.format(peakPubYear.value)} รายการ)` : ''}. ${topRole ? `เมื่อรวมทุกปี บทบาทผู้เขียนของคนในหน่วยวิจัยฯ ที่ปรากฏมากที่สุดคือ ${topRole.label} ${fmt.format(topRole.value)} ครั้ง. ` : ''}${authorYearNarrative(publications, els.authorYear.value)} กราฟนี้แสดงเฉพาะคนในหน่วยวิจัยฯ และมีตัวเลือกปีปัจจุบัน ปีก่อน และช่วงปีก่อนหน้าหน้า-ปีปัจจุบัน โดยจำกัดที่ Top 5 เพื่อให้อ่านชื่อและสัดส่วนบทบาทได้ชัดขึ้น.`;
            }

            function renderOutputs(rows) {
                const outputs = combinedOutputRecords(rows);
                const chartOutputs = outputs.filter((o) => !isFutureYearLabel(outputYear(o)));
                const typeRows = top(groupSum(chartOutputs, (o) => outputTypeLabel(o.type)), 8);
                renderChart('chartOutputType', {
                    type: 'bar',
                    data: { labels: typeRows.map((x) => x.label), datasets: [barDataset('จำนวน', typeRows.map((x) => x.value), '#7c3aed')] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const outputYears = sortYearLabels(new Set(chartOutputs.map(outputYear)))
                    .filter((year) => !isFutureYearLabel(year))
                    .filter((year) => chartOutputs.some((o) => outputYear(o) === year));
                const outputTypes = top(groupSum(chartOutputs, (o) => outputTypeLabel(o.type)), 7).map((x) => x.label);
                renderChart('chartOutputStacked', {
                    type: 'bar',
                    data: {
                        labels: outputYears,
                        datasets: outputTypes.map((type, index) => barDataset(
                            type,
                            outputYears.map((year) => chartOutputs.filter((o) => outputYear(o) === year && outputTypeLabel(o.type) === type).length),
                            COLORS[index % COLORS.length]
                        ))
                    },
                    options: { plugins: { legend: { position: 'bottom' }, diValueLabel: { display: false }, diStackTotalLabel: { display: true } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const pubs = authorPublicationOutputs(rows);
                const pubRows = top(groupSum(pubs, outputYear), 99)
                    .filter((item) => item.label !== 'ไม่ระบุปี')
                    .filter((item) => !isFutureYearLabel(item.label))
                    .filter((item) => yearNumber(item.label) >= 2561)
                    .sort((a, b) => Number(a.label.match(/\d{4}/)?.[0] || 99999) - Number(b.label.match(/\d{4}/)?.[0] || 99999));
                renderChart('chartPublicationYear', {
                    type: 'bar',
                    data: { labels: pubRows.map((x) => x.label), datasets: [barDataset('Publication', pubRows.map((x) => x.value), '#14b8a6')] },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                renderAuthors(pubs);
                const topType = typeRows[0];
                const peakOutputYear = top(groupSum(chartOutputs, outputYear), 1)[0];
                const latestOutputYear = outputYears[outputYears.length - 1];
                const latestOutputCount = latestOutputYear ? chartOutputs.filter((o) => outputYear(o) === latestOutputYear).length : 0;
                if (els.outputSummaryList) {
                    els.outputSummaryList.innerHTML = [
                        ['Output รวม', `${fmt.format(chartOutputs.length)} รายการ`],
                        ['Publication', `${fmt.format(pubs.length)} รายการ`],
                        ['ประเภทเด่น', topType ? `${topType.label}` : '-'],
                        ['ปีล่าสุด', latestOutputYear ? `${latestOutputYear}: ${fmt.format(latestOutputCount)}` : '-'],
                    ].map(([label, value]) => `<span>${escapeHtml(label)} <b>${escapeHtml(value)}</b></span>`).join('');
                }
                updateText('outputWhat', topType
                    ? `ในชุดข้อมูลที่เลือก มี output ถึงปีปัจจุบันรวม ${fmt.format(chartOutputs.length)} รายการ กลุ่มที่ออกมากที่สุดคือ “${topType.label}” ${fmt.format(topType.value)} รายการ แปลว่างานเผยแพร่/สื่อสารผลลัพธ์เป็นสัดส่วนสำคัญของพอร์ตผลงาน. ปีที่ output หนาแน่นที่สุดโดยนับถึงปีปัจจุบันคือ ${peakOutputYear?.label || '-'}${peakOutputYear ? ` (${fmt.format(peakOutputYear.value)} รายการ)` : ''}; ปีล่าสุดที่มี output คือ ${latestOutputYear || '-'}${latestOutputYear ? ` จำนวน ${fmt.format(latestOutputCount)} รายการ` : ''}.`
                    : 'ยังไม่มี output ในชุดข้อมูลนี้');
                updateText('outputReflection', outputAuthorReflection(pubs));
            }

            function renderAuthors(publications) {
                const currentBeYear = CURRENT_BE_YEAR_LABEL;
                const previousBeYear = PREVIOUS_BE_YEAR_LABEL;
                const previousPreviousBeYear = PREVIOUS_PREVIOUS_BE_YEAR_LABEL;
                const rangeYear = CURRENT_YEAR_RANGE_LABEL;
                const dataYears = sortYearLabels(new Set(publications.map(outputYear))).filter((year) => !isFutureYearLabel(year)).reverse();
                const years = [rangeYear, currentBeYear, previousBeYear, previousPreviousBeYear, ...dataYears.filter((year) => ![currentBeYear, previousBeYear, previousPreviousBeYear].includes(year))];
                const current = els.authorYear.value;
                els.authorYear.innerHTML = years.map((y) => `<option value="${escapeHtml(y)}">${escapeHtml(y)}</option>`).join('');
                if (years.includes(current)) els.authorYear.value = current;
                else els.authorYear.value = rangeYear;
                const selected = els.authorYear.value;
                const search = norm(els.authorSearch.value);
                const topN = Number(els.authorTop.value || 5);
                const map = new Map();
                const selectedYears = authorYearsForSelection(selected);
                publications.filter((o) => selectedYears.includes(outputYear(o))).forEach((o) => {
                    (Array.isArray(o.authors) ? o.authors : []).filter(isUnitAuthor).forEach((a) => {
                        const name = text(a.name);
                        const role = norm(a.role);
                        if (!name) return;
                        if (!map.has(name)) map.set(name, { name, first: 0, co: 0, corresponding: 0 });
                        const item = map.get(name);
                        if (role === 'first') item.first++;
                        if (role === 'co') item.co++;
                        if (role === 'corresponding') item.corresponding++;
                    });
                });
                let rows = Array.from(map.values()).map((x) => ({ ...x, total: x.first + x.co + x.corresponding }));
                if (search) rows = rows.filter((x) => norm(x.name).includes(search));
                rows = rows.sort((a, b) => b.total - a.total).slice(0, topN);
                const maxTotal = Math.max(...rows.map((x) => x.total), 1);
                const roleTitle = (cls, count) => {
                    const labels = { first: 'First Author', co: 'Co-author', corresponding: 'Corresponding Author' };
                    return `${labels[cls] || cls}: ${fmt.format(count)} ครั้ง`;
                };
                const seg = (count, cls) => count > 0 ? `<span class="di-author-seg di-author-seg--${cls}" title="${escapeHtml(roleTitle(cls, count))}" style="width:${Math.max(4, count / maxTotal * 100)}%">${count > 1 ? fmt.format(count) : ''}</span>` : '';
                document.getElementById('authorBoard').innerHTML = rows.length ? `
                    <div class="di-author-head">
                        <span>ผู้เขียน</span>
                        <span>บทบาทผู้เขียน</span>
                        <span>รวม</span>
                    </div>
                    ${rows.map((x) => `
                        <div class="di-author-row" role="button" tabindex="0" data-author-name="${escapeHtml(x.name)}">
                            <div class="di-author-name" title="${escapeHtml(x.name)}">${escapeHtml(x.name)}</div>
                            <div class="di-author-stack" title="${escapeHtml(`First Author ${fmt.format(x.first)} ครั้ง / Co-author ${fmt.format(x.co)} ครั้ง / Corresponding Author ${fmt.format(x.corresponding)} ครั้ง`)}">
                            ${seg(x.first, 'first')}
                            ${seg(x.co, 'co')}
                            ${seg(x.corresponding, 'corresponding')}
                            </div>
                            <div class="di-author-total" title="${escapeHtml(`รวม ${fmt.format(x.total)} ครั้ง`)}">${fmt.format(x.total)}</div>
                        </div>
                    `).join('')}
                ` : '<div class="di-empty">ไม่พบข้อมูลบทบาทผู้เขียนในเงื่อนไขนี้</div>';
                document.querySelectorAll('#authorBoard .di-author-row').forEach((node) => {
                    const name = node.getAttribute('data-author-name');
                    const row = rows.find((item) => item.name === name);
                    if (!row) return;
                    const open = () => openAuthorDetail(row.name, row, publications);
                    node.addEventListener('click', open);
                    node.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            open();
                        }
                    });
                });
            }

            function renderOperations(rows) {
                const coRows = rows.flatMap((p) => (Array.isArray(p.co_researches) ? p.co_researches : []).map((c) => ({ ...c, project: p })));
                const coUnits = top(groupSum(coRows, (c) => text(c.unit_status || c.affiliation, 'ไม่ระบุหน่วยงาน')), 8).map((x) => x.label);
                const withinMonths = (p, months) => {
                    const start = parseDate(p.team_start_date || p.start_date || p.created_at);
                    if (!start) return months === 12;
                    const diff = (new Date() - start) / 86400000 / 30.44;
                    return diff <= months;
                };
                renderChart('chartCoResearch', {
                    type: 'bar',
                    data: {
                        labels: coUnits,
                        datasets: [
                            barDataset('ภายใน 3 เดือน', coUnits.map((unit) => coRows.filter((c) => text(c.unit_status || c.affiliation, 'ไม่ระบุหน่วยงาน') === unit && withinMonths(c.project, 3)).length), '#0f766e'),
                            barDataset('ภายใน 6 เดือน', coUnits.map((unit) => coRows.filter((c) => text(c.unit_status || c.affiliation, 'ไม่ระบุหน่วยงาน') === unit && withinMonths(c.project, 6)).length), '#c0841a'),
                            barDataset('ภายใน 1 ปี', coUnits.map((unit) => coRows.filter((c) => text(c.unit_status || c.affiliation, 'ไม่ระบุหน่วยงาน') === unit && withinMonths(c.project, 12)).length), '#475569'),
                        ]
                    },
                    options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const irbRows = irbExpiring(rows);
                const irbByMonth = top(groupSum(irbRows, (irb) => {
                    const d = parseDate(irb.exp_date);
                    return d ? `${d.getFullYear() + 543}-${String(d.getMonth() + 1).padStart(2, '0')}` : 'ไม่ระบุ';
                }), 12);
                renderChart('chartIrb', {
                    type: 'bar',
                    data: { labels: irbByMonth.map((x) => x.label), datasets: [barDataset('IRB ใกล้หมดอายุ', irbByMonth.map((x) => x.value), '#ef4444')] },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const overdueRows = top(groupSum(overdueProjects(rows), (p) => projectStatusDisplayLabel(p.planning_status)), 8);
                renderChart('chartOverdue', {
                    type: 'bar',
                    data: { labels: overdueRows.map((x) => x.label), datasets: [barDataset('เกินกำหนด', overdueRows.map((x) => x.value), '#ef4444')] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
                const overdue = overdueProjects(rows).slice(0, 10);
                const watchRows = [...overdue, ...rows.filter((p) => !budget(p) && isActive(p)).slice(0, Math.max(0, 10 - overdue.length))];
                document.getElementById('watchTable').innerHTML = watchRows.length ? watchRows.map((p) => `
                    <tr>
                        <td data-label="โครงการ"><a href="${projectUrl(p.id)}" class="text-blue-700 font-semibold hover:underline">${escapeHtml(text(p.project_name_th, '-'))}</a></td>
                        <td data-label="PI">${escapeHtml(text(p.pi_name, '-'))}</td>
                        <td data-label="ปี">${escapeHtml(yearLabel(p))}</td>
                        <td data-label="สถานะ">${escapeHtml(status(p))}</td>
                        <td data-label="หมายเหตุ">${overdue.includes(p) ? 'เกินกำหนด/ควรติดตาม' : 'ยังไม่มีข้อมูลงบประมาณ'}</td>
                    </tr>
                `).join('') : '<tr><td colspan="5" data-label="รายการ" class="text-center text-slate-500">ยังไม่มีรายการที่ต้องติดตามเด่นชัด</td></tr>';
                const topCoUnit = top(groupSum(coRows, (c) => text(c.unit_status || c.affiliation, 'ไม่ระบุหน่วยงาน')), 1)[0];
                const coPeopleCount = new Set(coRows.map((c) => norm(c.name)).filter(Boolean)).size;
                const coProjectCount = new Set(coRows.map((c) => c.project?.id).filter(Boolean)).size;
                const overdueCount = overdueProjects(rows).length;
                const activeCount = rows.filter(isActive).length;
                const watchCount = watchRows.length;
                const irbPeak = irbByMonth[0];
                const opsSummary = document.getElementById('opsSummaryList');
                if (opsSummary) {
                    opsSummary.innerHTML = [
                        ['คนร่วมวิจัยไม่ซ้ำ', `${fmt.format(coPeopleCount)} คน`],
                        ['รายการ Co-Research', `${fmt.format(coRows.length)} รายการ`],
                        ['IRB 6 เดือน', `${fmt.format(irbRows.length)} รายการ`],
                        ['เกินกำหนด', `${fmt.format(overdueCount)} โครงการ`],
                    ].map(([label, value]) => `<span>${escapeHtml(label)} <b>${escapeHtml(value)}</b></span>`).join('');
                }
                updateText('opsWhat', `ใน scope นี้มีโครงการ active ${fmt.format(activeCount)} โครงการ และมีโครงการที่ควรติดตาม ${fmt.format(overdueCount)} โครงการ. Co-Research มี ${fmt.format(coPeopleCount)} คนไม่ซ้ำ จาก ${fmt.format(coRows.length)} รายการใน ${fmt.format(coProjectCount)} โครงการ. IRB ที่จะหมดอายุใน 6 เดือนมี ${fmt.format(irbRows.length)} รายการ${irbPeak ? ` โดยเดือนที่มากสุดคือ ${irbPeak.label} (${fmt.format(irbPeak.value)} รายการ)` : ''}.`);
                updateText('opsReflection', topCoUnit
                    ? `กลุ่มหน่วยงานที่มีรายการ Co-Research มากสุดคือ “${topCoUnit.label}” ${fmt.format(topCoUnit.value)} รายการ. ตัวเลขนี้เป็นจำนวนรายการบทบาทในโครงการ ไม่ใช่จำนวนคนไม่ซ้ำ จึงควรอ่านคู่กับจำนวนคนไม่ซ้ำด้านบนและรายการควรติดตาม เพื่อแยกภาระประสานงานออกจากงานตามสัญญา/IRB.`
                    : 'ยังไม่มีข้อมูล Co-Research เพียงพอสำหรับอ่านภาระประสานงานในชุดข้อมูลนี้');
            }

            function renderSkillCapacityTable(rows, workloadItems, totalFte) {
                const body = document.getElementById('skillCapacityBody');
                const foot = document.getElementById('skillCapacityFoot');
                if (!body || !foot) return [];

                const capacityRows = Array.isArray(dashboardFteSkillAnalysis?.capacity_skill_rows)
                    ? dashboardFteSkillAnalysis.capacity_skill_rows
                    : [];
                if (capacityRows.length) {
                    const workforceTotal = Number(dashboardFteSkillAnalysis.capacity_workforce_total || 0);
                    const workforceFteTotal = Number(dashboardFteSkillAnalysis.capacity_workforce_fte_total || 0);
                    const positionTotal = Number(dashboardFteSkillAnalysis.capacity_total || 0);
                    const borrowedTotal = Number(dashboardFteSkillAnalysis.capacity_skill_borrowed_total || 0);

                    body.innerHTML = capacityRows.map((row, index) => `
                        <tr>
                            <td data-label="ลำดับ"><span class="di-skill-capacity-rank">${fmt.format(index + 1)}</span></td>
                            <td data-label="Skill set"><div class="di-skill-capacity-name"><span>${escapeHtml(text(row.skill_role, '-'))}</span></div></td>
                            <td class="di-skill-capacity-table__num" data-label="Workforce"><span class="di-skill-capacity-count">${fmt.format(Number(row.workforce_people || 0))}</span></td>
                            <td class="di-skill-capacity-table__num" data-label="Time Spending (%)">${oneDecimal.format(Number(row.time_spending_pct || 0))}</td>
                            <td class="di-skill-capacity-table__num" data-label="Tasks (%)">${oneDecimal.format(Number(row.task_pct || 0))}</td>
                            <td class="di-skill-capacity-table__num" data-label="Position"><span class="di-skill-capacity-count">${fmt.format(Number(row.people || 0))}</span></td>
                            <td class="di-skill-capacity-table__num" data-label="Position (%)">${oneDecimal.format(Number(row.pct || 0))}</td>
                            <td class="di-skill-capacity-table__num" data-label="Position ที่ยืมตัวมา">${
                                Number(row.borrowed_people || 0) > 0
                                    ? `<span class="di-skill-capacity-borrowed">${fmt.format(Number(row.borrowed_people || 0))}</span>`
                                    : '<span class="di-skill-capacity-empty">-</span>'
                            }</td>
                        </tr>
                    `).join('');
                    foot.innerHTML = `
                        <tr>
                            <td colspan="2" data-label="รวม">รวม</td>
                            <td class="di-skill-capacity-table__num" data-label="Workforce">${fmt.format(workforceTotal)}</td>
                            <td class="di-skill-capacity-table__num" data-label="Time Spending (%)">${workforceFteTotal > 0 ? oneDecimal.format(100) : oneDecimal.format(0)}</td>
                            <td class="di-skill-capacity-table__num" data-label="Tasks (%)">${capacityRows.reduce((sum, row) => sum + Number(row.task_count || 0), 0) > 0 ? oneDecimal.format(100) : oneDecimal.format(0)}</td>
                            <td class="di-skill-capacity-table__num" data-label="Position">${fmt.format(positionTotal)}</td>
                            <td class="di-skill-capacity-table__num" data-label="Position (%)">${positionTotal > 0 ? oneDecimal.format(100) : oneDecimal.format(0)}</td>
                            <td class="di-skill-capacity-table__num" data-label="Position ที่ยืมตัวมา">${borrowedTotal > 0 ? fmt.format(borrowedTotal) : '-'}</td>
                        </tr>
                    `;

                    return capacityRows.map((row) => ({
                        skill: text(row.skill_role, '-'),
                        fte: Number(row.time_spending_pct || 0),
                        workforcePeople: Number(row.workforce_people || 0),
                        timeSpendingPct: Number(row.time_spending_pct || 0),
                        taskPct: Number(row.task_pct || 0),
                        people: Number(row.people || 0),
                        borrowedPeople: Number(row.borrowed_people || 0),
                    }));
                }

                const allFteItems = rows.flatMap((p) => (Array.isArray(p.fte_roles) ? p.fte_roles : []).map((r) => ({ ...r, project: p })));
                const skillMap = new Map();
                const ensureSkill = (skill) => {
                    const key = skillSetDisplayLabel(skill);
                    if (!skillMap.has(key)) {
                        skillMap.set(key, {
                        skill: key,
                        fte: 0,
                        taskCount: 0,
                        workforce: new Set(),
                        positionPeople: new Set(),
                        borrowedPeople: new Set(),
                        });
                    }
                    return skillMap.get(key);
                };

                workloadItems.forEach((item) => {
                    const value = fteValue(item);
                    if (!(value > 0)) return;
                    const row = ensureSkill(fteSkillLabel(item));
                    row.fte += value;
                    row.taskCount += 1;
                    const personKey = normalizePersonKey(item.name);
                    if (personKey) row.workforce.add(personKey);
                });

                allFteItems.forEach((item) => {
                    const personKey = normalizePersonKey(item.name);
                    if (!personKey || item.is_resigned || item.is_consultant) return;
                    if (!item.is_unit_user && !item.user_id && !item.user) return;
                    const row = ensureSkill(fteSkillLabel(item));
                    row.positionPeople.add(personKey);
                    if (item.is_borrowed) row.borrowedPeople.add(personKey);
                });

                const skillRows = Array.from(skillMap.values())
                    .map((row) => ({
                        ...row,
                        workforcePeople: row.workforce.size,
                        timeSpendingPct: totalFte ? (row.fte / totalFte) * 100 : 0,
                        people: row.positionPeople.size,
                        borrowedPeople: row.borrowedPeople.size,
                    }))
                    .filter((row) => row.fte > 0 || row.people > 0)
                    .sort((a, b) => {
                        const rankA = skillSetSortRank(a.skill);
                        const rankB = skillSetSortRank(b.skill);
                        if (rankA !== -1 || rankB !== -1) {
                            if (rankA === -1) return 1;
                            if (rankB === -1) return -1;
                            return rankA - rankB;
                        }
                        return (b.fte - a.fte) || (b.people - a.people) || a.skill.localeCompare(b.skill, 'th');
                    })
                    .slice(0, 4);

                if (!skillRows.length) {
                    body.innerHTML = '<tr><td colspan="8" class="di-skill-capacity-table__num">ไม่มีข้อมูล Skill set capacity ใน scope นี้</td></tr>';
                    foot.innerHTML = '';
                    return [];
                }

                const positionTotal = new Set(Array.from(skillMap.values()).flatMap((row) => Array.from(row.positionPeople))).size;
                const borrowedTotal = skillRows.reduce((sum, row) => sum + row.borrowedPeople, 0);
                const workforceTotal = new Set(workloadItems.map((item) => normalizePersonKey(item.name)).filter(Boolean)).size;
                const displayedFteTotal = skillRows.reduce((sum, row) => sum + row.fte, 0);
                const displayedTaskTotal = skillRows.reduce((sum, row) => sum + row.taskCount, 0);
                const displayedPeopleTotal = skillRows.reduce((sum, row) => sum + row.people, 0);

                body.innerHTML = skillRows.map((row, index) => {
                    const positionPct = positionTotal ? (row.people / positionTotal) * 100 : 0;
                    const taskPct = displayedTaskTotal ? (row.taskCount / displayedTaskTotal) * 100 : 0;
                        return `
                            <tr>
                            <td data-label="ลำดับ"><span class="di-skill-capacity-rank">${fmt.format(index + 1)}</span></td>
                            <td data-label="Skill set"><div class="di-skill-capacity-name"><span>${escapeHtml(row.skill)}</span></div></td>
                            <td class="di-skill-capacity-table__num" data-label="Workforce"><span class="di-skill-capacity-count">${fmt.format(row.workforcePeople)}</span></td>
                            <td class="di-skill-capacity-table__num" data-label="Time Spending (%)">${money.format(row.timeSpendingPct)}</td>
                            <td class="di-skill-capacity-table__num" data-label="Tasks (%)">${money.format(taskPct)}</td>
                            <td class="di-skill-capacity-table__num" data-label="Position"><span class="di-skill-capacity-count">${fmt.format(row.people)}</span></td>
                            <td class="di-skill-capacity-table__num" data-label="Position (%)">${money.format(positionPct)}</td>
                            <td class="di-skill-capacity-table__num" data-label="Position ที่ยืมตัวมา">${
                                row.borrowedPeople > 0
                                    ? `<span class="di-skill-capacity-borrowed">${fmt.format(row.borrowedPeople)}</span>`
                                    : '<span class="di-skill-capacity-empty">-</span>'
                            }</td>
                        </tr>
                    `;
                }).join('');
                foot.innerHTML = `
                    <tr>
                        <td colspan="2" data-label="รวม">รวม</td>
                        <td class="di-skill-capacity-table__num" data-label="Workforce">${fmt.format(workforceTotal)}</td>
                        <td class="di-skill-capacity-table__num" data-label="Time Spending (%)">${displayedFteTotal > 0 ? money.format(100) : money.format(0)}</td>
                        <td class="di-skill-capacity-table__num" data-label="Tasks (%)">${displayedTaskTotal > 0 ? money.format(100) : money.format(0)}</td>
                        <td class="di-skill-capacity-table__num" data-label="Position">${fmt.format(displayedPeopleTotal)}</td>
                        <td class="di-skill-capacity-table__num" data-label="Position (%)">${displayedPeopleTotal > 0 ? money.format(100) : money.format(0)}</td>
                        <td class="di-skill-capacity-table__num" data-label="Position ที่ยืมตัวมา">${borrowedTotal > 0 ? fmt.format(borrowedTotal) : '-'}</td>
                    </tr>
                `;
                return skillRows;
            }

            function renderWorkload(rows) {
                const fteItems = rows
                    .flatMap((p) => (Array.isArray(p.fte_roles) ? p.fte_roles : []).map((r) => ({ ...r, project: p })))
                    .filter(isEligibleWorkloadFte);
                const fteRows = top(groupSum(fteItems, (r) => text(r.name, 'ไม่ระบุชื่อ'), fteValue), 12);
                clearFteAvatarLayer(document.getElementById('chartFtePeople'));
                renderChart('chartFtePeople', {
                    type: 'bubble',
                    data: { datasets: buildFteSummaryDatasets(fteItems) },
                    options: {
                        layout: { padding: { top: 18, right: 18, bottom: 12, left: 12 } },
                        plugins: {
                            legend: { display: false },
                            diValueLabel: { display: false },
                            tooltip: {
                                displayColors: false,
                                callbacks: {
                                    label: (ctx) => `${ctx.dataset.label}: โครงการ ${fmt.format(ctx.raw.x)} / FTE ${money.format(ctx.raw.y)}`,
                                },
                            },
                        },
                        scales: {
                            x: { beginAtZero: true, title: { display: true, text: 'จำนวนโครงการ' }, ticks: { precision: 0 } },
                            y: { beginAtZero: true, title: { display: true, text: 'FTE รวม' } },
                        },
                    }
                });
                const totalFte = fteItems.reduce((sum, item) => sum + fteValue(item), 0);
                const skillCapacityRows = renderSkillCapacityTable(rows, fteItems, totalFte);
                const projectCount = new Set(fteItems.filter((item) => fteValue(item) > 0).map((item) => item.project?.id).filter(Boolean)).size;
                const topPerson = fteRows[0];
                const topRole = skillCapacityRows[0] ? { label: skillCapacityRows[0].skill, value: skillCapacityRows[0].fte } : null;
                const topFivePeopleTotal = fteRows.slice(0, 5).reduce((sum, item) => sum + item.value, 0);
                const topPersonShare = totalFte ? (topPerson?.value || 0) / totalFte * 100 : 0;
                const topFiveShare = totalFte ? topFivePeopleTotal / totalFte * 100 : 0;
                const allFteItemsForSkillSummary = rows
                    .flatMap((p) => (Array.isArray(p.fte_roles) ? p.fte_roles : []).map((r) => ({ ...r, project: p })))
                    .filter((item) => fteValue(item) > 0);
                const skillRowsDetailed = skillCapacityRows.map((item) => {
                    const skillKey = skillSetCanonicalKey(item.skill);
                    const aggregateSkillRow = dashboardSkillSummaryByKey.get(skillKey);
                    const scoped = allFteItemsForSkillSummary.filter((row) => skillSetCanonicalKey(fteSkillLabel(row)) === skillKey);
                    const peopleRows = top(groupSum(scoped, (row) => text(row.name, 'ไม่ระบุชื่อ'), fteValue), 3);
                    const peopleCount = Number(aggregateSkillRow?.people_count ?? item.workforcePeople ?? 0);
                    const projectCountForSkill = Number(aggregateSkillRow?.project_count ?? 0)
                        || new Set(scoped.map((row) => row.project?.id ?? row.project_id).filter(Boolean)).size;
                    const fteTotalForSkill = Number(aggregateSkillRow?.fte ?? item.fte ?? 0);
                    return {
                        label: item.skill,
                        value: fteTotalForSkill,
                        share: item.timeSpendingPct,
                        people: peopleCount,
                        projects: projectCountForSkill,
                        avgPerPerson: peopleCount ? fteTotalForSkill / peopleCount : 0,
                        avgPerProject: projectCountForSkill ? fteTotalForSkill / projectCountForSkill : 0,
                        topPeople: peopleRows,
                    };
                });
                const skillBreakdownText = skillRowsDetailed.slice(0, 4).map((item) =>
                    `“${item.label}” ${money.format(item.value)} FTE (${money.format(item.share)}%) จาก ${fmt.format(item.people)} คน / ${fmt.format(item.projects)} โครงการ`
                ).join('; ');
                const skillCapacityText = skillRowsDetailed.slice(0, 5).map((item, index) => {
                    const leadPeople = item.topPeople.length
                        ? item.topPeople.map((person) => `${person.label} ${money.format(person.value)} FTE`).join(', ')
                        : 'ยังไม่เห็นคนเด่นในสายงานนี้';
                    const pressure = item.people <= 2 && item.value >= 5
                        ? 'จุดนี้เสี่ยงกระจุกตัวที่คนจำนวนน้อย'
                        : (item.avgPerPerson >= 3
                            ? 'ค่าเฉลี่ยต่อคนค่อนข้างสูง ควรดู workload รายบุคคลประกอบ'
                            : 'ภาระกระจายตัวพออ่านต่อในระดับโครงการได้');

                    return `${index + 1}. ${item.label}: ${money.format(item.value)} FTE หรือ ${money.format(item.share)}% ของ FTE ทั้งหมดใน scope นี้ จาก ${fmt.format(item.projects)} โครงการ เฉลี่ย ${money.format(item.avgPerProject)} FTE/โครงการ และ ${money.format(item.avgPerPerson)} FTE/คน; คนที่ถือภาระสูงสุดคือ ${leadPeople}. ${pressure}.`;
                }).join(' ');
                const workloadSummary = document.getElementById('workloadSummaryList');
                if (workloadSummary) {
                    workloadSummary.innerHTML = [
                        ['FTE รวม', money.format(totalFte)],
                        ['โครงการที่มี FTE', `${fmt.format(projectCount)} โครงการ`],
                        ['สายงาน FTE สูงสุด', topRole ? topRole.label : '-'],
                    ].map(([label, value]) => `<span>${escapeHtml(label)} <b>${escapeHtml(value)}</b></span>`).join('');
                }
                updateText('workloadWhat', topPerson
                    ? `ภาระงาน FTE เฉพาะคนในหน่วยที่ยัง active และไม่ใช่ที่ปรึกษา รวม ${money.format(totalFte)} จาก ${fmt.format(projectCount)} โครงการ. กราฟรูปโปรไฟล์อ่านสองมิติพร้อมกัน: แกน X คือจำนวนโครงการที่คนคนนั้นเกี่ยวข้อง, แกน Y คือ FTE รวม, และขนาดวงคือระดับภาระงาน. คนที่มี FTE สูงสุดคือ ${topPerson.label} ${money.format(topPerson.value)} FTE คิดเป็น ${money.format(topPersonShare)}% ของ FTE ทั้งหมดใน scope นี้; ส่วน Top 5 คนแรกรวมกัน ${money.format(topFivePeopleTotal)} FTE หรือ ${money.format(topFiveShare)}% ของ FTE ทั้งหมด.`
                    : 'ยังไม่มีข้อมูล FTE ของคนในหน่วยที่ยัง active และไม่ใช่ที่ปรึกษาในชุดข้อมูลนี้');
                updateText('workloadReflection', topRole
                    ? `เมื่อจัดกลุ่มตามสายงาน/skill ภาพรวมคือ ${skillBreakdownText}. อ่านแยกตามสายงานได้ดังนี้: ${skillCapacityText} สรุปคือ capacity ไม่ได้ดูแค่ยอดรวม แต่ต้องดูพร้อมกัน 4 ชั้น ได้แก่ สายงานที่กิน FTE สูง, จำนวนโครงการที่สายงานนั้นรับอยู่, จำนวนคนที่รองรับ และคนที่ถือ FTE สูงสุดในแต่ละสายงาน.`
                    : 'ยังไม่มีข้อมูลสายงาน/skill ของ FTE เพียงพอสำหรับวิเคราะห์การกระจายภาระงาน');
            }

            function refresh() {
                const rows = filteredRows();
                renderFilterState(rows);
                renderProjectOverview(rows);
                renderPortfolio(rows);
                renderFunding(rows);
                renderOutputs(rows);
                renderOperations(rows);
                renderWorkload(rows);
            }

            function refreshAuthorsOnly() {
                const publications = authorPublicationOutputs(filteredRows());
                renderAuthors(publications);
                updateText('outputReflection', outputAuthorReflection(publications));
            }

            fillYearOptions();
            [els.year, els.theme, els.type, els.funding, els.status, els.source].forEach((el) => el?.addEventListener('change', refresh));
            [els.authorYear, els.authorTop].forEach((el) => el?.addEventListener('change', refreshAuthorsOnly));
            els.search?.addEventListener('input', refresh);
            els.authorSearch?.addEventListener('input', refreshAuthorsOnly);
            els.openFilter?.addEventListener('click', () => setPopup('filter'));
            els.closeFilter?.addEventListener('click', () => setPopup(null));
            els.openDownload?.addEventListener('click', () => setPopup('download'));
            els.closeDownload?.addEventListener('click', () => setPopup(null));
            els.overlay?.addEventListener('click', () => setPopup(null));
            els.detailClose?.addEventListener('click', closeDetail);
            els.detailModal?.addEventListener('click', (event) => {
                if (event.target === els.detailModal) closeDetail();
            });
            els.downloadCsv?.addEventListener('click', downloadFilteredCsv);
            els.downloadExcel?.addEventListener('click', downloadFilteredExcel);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setPopup(null);
                    closeDetail();
                }
            });
            els.reset?.addEventListener('click', () => {
                [els.year, els.theme, els.type, els.funding, els.status, els.source].forEach((el) => { if (el) el.value = ''; });
                if (els.search) els.search.value = '';
                refresh();
            });
            let dashboardResizeFrame = null;
            const resizeDashboardCharts = () => {
                if (dashboardResizeFrame) cancelAnimationFrame(dashboardResizeFrame);
                dashboardResizeFrame = requestAnimationFrame(() => {
                    chartMap.forEach((chart) => chart.resize());
                });
            };
            window.addEventListener('resize', resizeDashboardCharts, { passive: true });
            window.addEventListener('orientationchange', () => setTimeout(resizeDashboardCharts, 220), { passive: true });
            refresh();
        })();
    </script>
@endsection
