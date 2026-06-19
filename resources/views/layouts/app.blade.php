<!DOCTYPE html>
<html lang="en">

@php
    $appTitle = 'SiHP Research Database Management system';
    $routeName = request()->route()?->getName();
    $pageTitle = match (true) {
        request()->routeIs('dashboard') => 'Dashboard',
        request()->routeIs('dashboard.command-center') => 'แดชบอร์ดภาพรวม',
        request()->routeIs('overview') => 'ภาพรวมระบบ',
        request()->routeIs('projects.index') => 'โครงการวิจัย',
        request()->routeIs('projects.create') => 'เพิ่มโครงการ',
        request()->routeIs('projects.edit') => 'แก้ไขโครงการ',
        request()->routeIs('projects.progress_reports.*') => 'รายงานความก้าวหน้า',
        request()->routeIs('projects.gantt*') => 'แผนงานโครงการ',
        request()->routeIs('back_office.index') => 'ภารกิจ',
        request()->routeIs('back_office.*') => 'ภารกิจ',
        request()->routeIs('outputs.index') => 'ภาพรวมผลงาน',
        request()->routeIs('outputs.people*') => 'ผลงานรายบุคคล',
        request()->routeIs('outputs.papers') => 'บทความวิจัย',
        request()->routeIs('outputs.presentations') => 'งานนำเสนอ',
        request()->routeIs('outputs.textbooks') => 'ตำราและบทหนังสือ',
        request()->routeIs('outputs.visualization*') => 'ข้อมูลผลงานสำหรับสัมมนา',
        request()->routeIs('outputs.project-data-quality*') => 'รายงานข้อมูลผิดพลาดของโปรเจค',
        request()->routeIs('outputs.external-academic*') => 'ผลงานวิชาการภายนอก',
        request()->routeIs('outputs.*') => 'ผลงานวิชาการ',
        request()->routeIs('fte.index') => 'FTE รายบุคคล',
        request()->routeIs('fte.detail') => 'รายละเอียด FTE รายบุคคล',
        request()->routeIs('fte.project.index') => 'FTE รายโปรเจค',
        request()->routeIs('fte.project.detail') => 'รายละเอียด FTE รายโปรเจค',
        request()->routeIs('fte.*') => 'FTE',
        request()->routeIs('wfa.*') => 'WFA',
        request()->routeIs('users.index') => 'จัดการผู้ใช้',
        request()->routeIs('users.manage') => 'จัดการสิทธิ์ผู้ใช้',
        request()->routeIs('users.create') => 'เพิ่มผู้ใช้',
        request()->routeIs('users.edit') => 'แก้ไขผู้ใช้',
        request()->routeIs('profile.show') => 'แก้ไขข้อมูลส่วนตัว',
        request()->routeIs('user_guide') => 'คู่มือการใช้งาน',
        request()->routeIs('project_logs.*') => 'ประวัติโครงการ',
        request()->routeIs('back_office_action_logs.*') => 'ประวัติ Back Office',
        request()->routeIs('user_activity_logs.*') => 'ประวัติการใช้งาน',
        request()->routeIs('rm.ai_dashboard.*') => 'แดชบอร์ด AI',
        default => 'ระบบจัดการฐานข้อมูลงานวิจัย',
    };
    $fullPageTitle = "{$pageTitle} - {$appTitle}";
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="facebook-domain-verification" content="tqgjbs7tegolvhlsr90o4exb5aq6hg">
    <title>{{ $fullPageTitle }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireStyles
    <script src="//unpkg.com/alpinejs" defer></script>



    <!-- ✅ jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ jQuery Gantt Plugin (Full) -->
    <link href="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/css/style.css" rel="stylesheet" />
    <script src="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/js/jquery.fn.gantt.js"></script>

    <!-- CSS + JS DHTMLX Gantt -->
    <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

    <style>
        .ts-wrapper {
            width: 100%;
            border: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .ts-wrapper.single .ts-control,
        .ts-wrapper.multi .ts-control {
            min-height: 42px !important;
            height: 42px !important;
            padding: 0 36px 0 12px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            background: #fff !important;
            box-shadow: none !important;
            display: flex !important;
            align-items: center !important;
            gap: 0 !important;
            font: inherit !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 24px !important;
            color: #111827 !important;
        }

        .ts-wrapper.single.input-active .ts-control,
        .ts-wrapper.multi.input-active .ts-control {
            box-shadow: none !important;
        }

        .ts-wrapper .ts-control input {
            min-width: 0 !important;
            width: 100% !important;
            height: 24px !important;
            min-height: 24px !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            font: inherit !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 24px !important;
            color: #111827 !important;
        }

        .ts-wrapper.single .ts-control > .item,
        .ts-wrapper.multi .ts-control > .item {
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: 0 !important;
            font: inherit !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 24px !important;
            color: #111827 !important;
        }

        .ts-wrapper.plugin-clear_button .clear-button,
        .ts-wrapper .clear-button {
            display: none !important;
        }

        .ts-dropdown,
        .ts-dropdown .option,
        .ts-dropdown .create {
            font: inherit !important;
            font-size: 16px !important;
            line-height: 24px !important;
        }

        .ts-wrapper.disabled .ts-control,
        .ts-wrapper.disabled .ts-control input {
            background: #f3f4f6 !important;
            color: #9ca3af !important;
            cursor: not-allowed !important;
        }

        .project-search-select {
            position: relative;
            width: 100%;
        }

        .project-search-select__native {
            position: absolute !important;
            left: -9999px !important;
            top: 0 !important;
            width: 1px !important;
            height: 1px !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .project-search-select__button {
            width: 100%;
            min-height: 42px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #ffffff;
            color: #111827;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            font: inherit;
            font-size: 16px;
            font-weight: 400;
            line-height: 24px;
            box-shadow: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .project-search-select__button:focus-visible {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .project-search-select__button[disabled] {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .project-search-select__label {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .project-search-select__icon {
            flex: 0 0 auto;
            color: #94a3b8;
            line-height: 1;
        }

        .project-search-select__menu {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 60;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.14);
        }

        .project-search-select__menu.hidden {
            display: none;
        }

        .project-search-select__search {
            width: 100%;
            min-height: 42px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #ffffff;
            font: inherit;
            font-size: 16px;
            font-weight: 400;
            line-height: 24px;
            color: #111827;
            box-shadow: none;
        }

        .project-search-select__search:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .project-search-select__options {
            max-height: 14rem;
            margin-top: 0.5rem;
            overflow-y: auto;
            scrollbar-gutter: stable;
        }

        .project-search-select__options::-webkit-scrollbar {
            width: 10px;
        }

        .project-search-select__options::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 999px;
        }

        .project-search-select__options::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 999px;
            border: 2px solid #f1f5f9;
        }

        .project-search-select__option {
            width: 100%;
            padding: 0.5rem 0.625rem;
            border-radius: 0.5rem;
            background: transparent;
            color: #111827;
            text-align: left;
            font: inherit;
            font-size: 15px;
            line-height: 1.4;
        }

        .project-search-select__option:hover,
        .project-search-select__option.is-selected {
            background: #f1f5f9;
        }

        .project-search-select__empty {
            padding: 0.5rem 0.625rem;
            color: #64748b;
            font-size: 0.8125rem;
        }
    </style>


</head>

@php
    $isModalView = request()->boolean('modal');
    $authUser = Auth::user();
    $personalOutputsRoute = null;
    $personalFteRoute = null;
    $shouldUsePersonalFteOnly = false;

    if ($authUser) {
        $personalOutputsRoute = $authUser->isAdminEquivalent()
            ? route('outputs.people')
            : route('outputs.people.show', ['person' => $authUser->full_name]);
        $personalFteRoute = route('fte.detail', ['person' => $authUser->full_name]);
        $shouldUsePersonalFteOnly = in_array($authUser->role, [
            \App\Models\User::ROLE_USER,
            \App\Models\User::ROLE_USER_ALL,
            \App\Models\User::ROLE_RESEARCH_EXECUTIVE,
            \App\Models\User::ROLE_HR,
        ], true);
    }
@endphp

<body class="{{ $isModalView ? 'bg-white text-gray-800' : 'bg-gray-100 text-gray-800' }}">
    @if (!$isModalView)
        <style>
            .mobile-nav {
                border-radius: 16px;
                padding: 8px 12px;
            }

            .mobile-nav a.brand {
                font-weight: 700;
                color: #0f172a;
                text-decoration: none;
            }

            .app-brand {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                min-width: 0;
                color: #0f172a;
                text-decoration: none;
            }

            .app-brand__logo {
                width: 34px;
                height: 34px;
                flex: none;
                border-radius: 10px;
                object-fit: contain;
                background: #ffffff;
                box-shadow: 0 0 0 1px #e2e8f0, 0 4px 10px rgba(15, 23, 42, 0.08);
            }

            .app-brand__text {
                display: grid;
                gap: 1px;
                min-width: 0;
                line-height: 1.08;
            }

            .app-brand__primary {
                font-size: 15px;
                font-weight: 800;
                letter-spacing: 0;
                white-space: nowrap;
            }

            .app-brand__secondary {
                font-size: 11px;
                font-weight: 700;
                color: #475569;
                letter-spacing: 0;
                white-space: nowrap;
            }

            .app-nav-shell {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .app-nav-menu {
                min-width: 0;
            }

            .app-nav-desktop-profile {
                display: none;
            }

            .app-nav-actions {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-left: auto;
            }

            @media (min-width: 1280px) {
                .app-nav-shell {
                    display: grid;
                    grid-template-columns: minmax(250px, 1fr) auto minmax(250px, 1fr);
                    gap: 16px;
                }

                .app-nav-menu {
                    display: flex;
                    justify-self: center;
                }

                .app-nav-desktop-profile {
                    display: block;
                }

                .app-nav-actions {
                    justify-self: end;
                    margin-left: 0;
                }

                .app-brand__logo {
                    width: 38px;
                    height: 38px;
                }

                .app-brand__primary {
                    font-size: 16px;
                }

                .app-brand__secondary {
                    font-size: 12px;
                }
            }

            @media (max-width: 420px) {
                .app-brand {
                    gap: 8px;
                }

                .app-brand__logo {
                    width: 32px;
                    height: 32px;
                }

                .app-brand__primary {
                    font-size: 14px;
                }

                .app-brand__secondary {
                    font-size: 10px;
                }
            }

            .mobile-nav-toggle {
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                background: #ffffff;
                width: 42px;
                height: 42px;
                align-items: center;
                justify-content: center;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }

            .mobile-nav-toggle:active {
                transform: scale(0.96);
            }

            .mobile-nav-toggle:hover {
                box-shadow: 0 3px 10px rgba(15, 23, 42, 0.08);
            }

            .mobile-nav-toggle__label {
                display: none;
                font-size: 0.82rem;
                font-weight: 800;
                color: #334155;
            }

            .mobile-hamburger {
                width: 18px;
                height: 14px;
                display: inline-flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .mobile-hamburger span {
                display: block;
                width: 100%;
                height: 2px;
                border-radius: 999px;
                background: #334155;
                transition: transform 0.2s ease, opacity 0.2s ease;
                transform-origin: center;
            }

            .mobile-nav-toggle.is-open .mobile-hamburger span:nth-child(1) {
                transform: translateY(6px) rotate(45deg);
            }

            .mobile-nav-toggle.is-open .mobile-hamburger span:nth-child(2) {
                opacity: 0;
            }

            .mobile-nav-toggle.is-open .mobile-hamburger span:nth-child(3) {
                transform: translateY(-6px) rotate(-45deg);
            }

            .mobile-nav-links a {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 10px;
                border-radius: 10px;
                color: #0f172a;
                border: 1px solid #e5e7eb;
            }

            .mobile-nav-links a:hover {
                background: #f1f5f9;
            }

            .mobile-nav-section {
                display: grid;
                gap: 0.55rem;
                padding: 0.75rem 0 0.85rem;
                border-bottom: 1px solid #e2e8f0;
            }

            .mobile-nav-section:last-child {
                border-bottom: 0;
                padding-bottom: 0;
            }

            .mobile-nav-section__title {
                padding: 0 2px;
                font-size: 0.72rem;
                font-weight: 900;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #64748b;
            }

            .mobile-logout-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                margin-top: 4px;
                padding: 10px 12px;
                border-radius: 12px;
                border: 1px solid #fca5a5;
                background: #fee2e2;
                color: #b91c1c;
                font-size: 14px;
                font-weight: 700;
                letter-spacing: 0.01em;
                transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
            }

            .mobile-logout-btn:hover {
                background: #fecaca;
                box-shadow: 0 6px 14px rgba(185, 28, 28, 0.14);
                transform: translateY(-1px);
            }

            .mobile-logout-btn:active {
                transform: translateY(0);
            }

            .mobile-drawer-overlay {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.32);
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
                z-index: 58;
            }

            .mobile-drawer-panel {
                position: fixed;
                top: 0;
                right: 0;
                height: 100dvh;
                width: min(90vw, 360px);
                background: #fff;
                border-left: 1px solid #e5e7eb;
                border-top-left-radius: 18px;
                border-bottom-left-radius: 18px;
                box-shadow: -10px 0 28px rgba(15, 23, 42, 0.14);
                padding: 14px 12px 16px;
                z-index: 59;
                overflow-y: auto;
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
                touch-action: pan-y;
            }

            html.app-nav-open,
            body.app-nav-open {
                overflow: hidden;
                overscroll-behavior: none;
            }

            @media (min-width: 768px) and (max-width: 1279px) {
                #main-nav .container {
                    max-width: none;
                    width: 100%;
                }

                .app-nav-shell {
                    width: 100%;
                    min-height: 54px;
                    justify-content: space-between;
                    border: 1px solid #e2e8f0;
                    background: rgba(255, 255, 255, 0.96);
                    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
                }

                .app-nav-menu,
                .app-nav-desktop-profile {
                    display: none !important;
                }

                .app-brand {
                    max-width: calc(100vw - 170px);
                }

                .app-brand__primary,
                .app-brand__secondary {
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .mobile-nav-toggle {
                    width: auto;
                    min-width: 94px;
                    padding: 0 14px;
                    gap: 10px;
                }

                .mobile-nav-toggle__label {
                    display: inline;
                }

                .mobile-drawer-panel {
                    width: min(58vw, 430px);
                    padding: 18px 16px 20px;
                }

                .mobile-nav-links {
                    gap: 0.6rem;
                }

                .mobile-nav-links a {
                    padding: 10px 12px;
                    font-size: 0.95rem;
                }
            }

            @media (min-width: 1024px) and (max-width: 1279px) and (orientation: landscape) {
                .app-nav-shell {
                    display: grid;
                    grid-template-columns: minmax(170px, 0.78fr) minmax(0, auto) auto;
                    gap: 8px;
                    min-height: 50px;
                    border: 0;
                    background: transparent;
                    box-shadow: none;
                    padding: 0;
                }

                .app-brand {
                    max-width: 190px;
                }

                .app-brand__logo {
                    width: 38px;
                    height: 38px;
                }

                .app-brand__primary {
                    font-size: 0.9rem;
                }

                .app-brand__secondary {
                    font-size: 0.7rem;
                }

                .app-nav-menu {
                    display: flex !important;
                    justify-self: center;
                    gap: 1px;
                }

                .app-nav-menu a,
                .app-nav-menu button {
                    padding-left: 7px !important;
                    padding-right: 7px !important;
                    font-size: 12px !important;
                    line-height: 1.2;
                }

                .app-nav-actions {
                    justify-self: end;
                }

                .app-nav-desktop-profile {
                    display: block !important;
                }

            }

            @media (min-width: 1024px) and (max-width: 1199px) and (orientation: landscape) {
                .app-nav-shell {
                    grid-template-columns: minmax(132px, 0.55fr) minmax(0, 1fr) auto;
                    gap: 4px;
                }

                .app-brand {
                    max-width: 150px;
                    gap: 8px;
                }

                .app-brand__logo {
                    width: 34px;
                    height: 34px;
                }

                .app-brand__primary {
                    font-size: 0.78rem;
                    line-height: 1.1;
                }

                .app-brand__secondary {
                    display: none;
                }

                .app-nav-menu {
                    gap: 0;
                }

                .app-nav-menu a,
                .app-nav-menu button {
                    padding-left: 5px !important;
                    padding-right: 5px !important;
                    font-size: 11px !important;
                    letter-spacing: 0;
                }

                .app-nav-desktop-profile button {
                    width: 34px;
                    height: 34px;
                }
            }

            #main-nav .container {
                max-width: none;
                width: 100%;
            }

            #main-nav .app-nav-shell {
                display: flex !important;
                width: 100%;
                min-height: 54px;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                border: 1px solid #e2e8f0 !important;
                border-radius: 16px !important;
                background: rgba(255, 255, 255, 0.96) !important;
                box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
                padding: 8px 12px !important;
            }

            #main-nav .app-nav-menu,
            #main-nav .app-nav-desktop-profile {
                display: none !important;
            }

            #main-nav .app-nav-actions {
                margin-left: auto;
                justify-self: auto;
            }

            #main-nav .app-brand {
                max-width: calc(100vw - 150px);
                gap: 10px;
            }

            #main-nav .app-brand__logo {
                width: 34px;
                height: 34px;
            }

            #main-nav .app-brand__primary {
                overflow: hidden;
                text-overflow: ellipsis;
                font-size: 15px;
                line-height: 1.1;
            }

            #main-nav .app-brand__secondary {
                display: block !important;
                overflow: hidden;
                text-overflow: ellipsis;
                font-size: 11px;
                line-height: 1.1;
            }

            #main-nav .mobile-nav-toggle {
                display: inline-flex !important;
                width: auto;
                min-width: 86px;
                height: 42px;
                gap: 10px;
                padding: 0 14px;
            }

            #main-nav .mobile-nav-toggle__label {
                display: inline !important;
            }

            #main-nav .mobile-drawer-panel {
                width: min(88vw, 430px);
                padding: 18px 16px 20px;
            }

            @media (min-width: 1280px) {
                #main-nav .container {
                    max-width: 100rem;
                    width: 100%;
                }

                #main-nav .app-nav-shell {
                    display: grid !important;
                    grid-template-columns: minmax(250px, 1fr) auto minmax(250px, 1fr);
                    min-height: 56px;
                    align-items: center;
                    gap: 16px;
                    border: 0 !important;
                    border-radius: 0 !important;
                    background: transparent !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                }

                #main-nav .app-nav-menu {
                    display: flex !important;
                    justify-self: center;
                    align-items: center;
                }

                #main-nav .app-nav-desktop-profile {
                    display: block !important;
                }

                #main-nav .app-nav-actions {
                    justify-self: end;
                    margin-left: 0;
                }

                #main-nav .mobile-nav-toggle {
                    display: none !important;
                }

                #main-nav .app-brand {
                    max-width: none;
                    gap: 10px;
                }

                #main-nav .app-brand__logo {
                    width: 38px;
                    height: 38px;
                }

                #main-nav .app-brand__primary {
                    font-size: 16px;
                    line-height: 1.08;
                }

                #main-nav .app-brand__secondary {
                    display: block !important;
                    font-size: 12px;
                    line-height: 1.08;
                }
            }

            @media (max-width: 420px) {
                #main-nav .app-brand {
                    max-width: calc(100vw - 132px);
                    gap: 8px;
                }

                #main-nav .app-brand__logo {
                    width: 32px;
                    height: 32px;
                }

                #main-nav .app-brand__primary {
                    font-size: 14px;
                }

                #main-nav .app-brand__secondary {
                    font-size: 10px;
                }

                #main-nav .mobile-nav-toggle {
                    min-width: 74px;
                    padding: 0 10px;
                }
            }

            /* Nav auto hide/show */
            #main-nav.nav-hidden {
                transform: translateY(-110%);
            }

            #save-loading-overlay {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(15, 23, 42, 0.4);
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
            }

            #save-loading-overlay.is-visible {
                display: flex;
            }

            .save-loading-overlay__card {
                min-width: 260px;
                max-width: min(92vw, 360px);
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                background: #ffffff;
                padding: 1.25rem 1.5rem;
                box-shadow: 0 24px 48px rgba(15, 23, 42, 0.22);
            }

            .save-loading-overlay__row {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .save-loading-overlay__spinner {
                width: 1.25rem;
                height: 1.25rem;
                border-radius: 999px;
                border: 2px solid #cbd5e1;
                border-top-color: #4f46e5;
                animation: save-loading-spin 0.75s linear infinite;
                flex: 0 0 auto;
            }

            .save-loading-overlay__title {
                margin: 0;
                font-size: 0.95rem;
                font-weight: 600;
                color: #1e293b;
                line-height: 1.35;
            }

            .save-loading-overlay__subtitle {
                margin: 0.15rem 0 0;
                font-size: 0.75rem;
                color: #64748b;
                line-height: 1.4;
            }

            @keyframes save-loading-spin {
                to {
                    transform: rotate(360deg);
                }
            }
        </style>
    @endif

    @if (session('clear_project_create_autosave') && auth()->check())
        <script>
            (() => {
                const createPath = @json(parse_url(route('projects.create'), PHP_URL_PATH));
                const userId = @json((string) auth()->id());
                if (!createPath || !userId) return;
                const formKey = `autosave:project:${userId}:${createPath}`;
                localStorage.removeItem(formKey);
            })();
        </script>
    @endif

    @if (!$isModalView)
        <!-- ✅ เปลี่ยนจาก max-w-7xl เป็น container -->
        <nav id="main-nav"
            class="bg-white border-b border-slate-200 shadow-sm fixed top-0 inset-x-0 z-50 transition-transform duration-200"
            x-data="{ openNav: false }"
            x-effect="window.dispatchEvent(new CustomEvent('app-mobile-nav', { detail: { open: openNav } }))">
            <div class="container mx-auto px-4 py-3">
                <div class="app-nav-shell mobile-nav">
                    <a href="/rm" class="app-brand brand" aria-label="{{ $fullPageTitle }}">
                        <img src="{{ asset('images/sihp-logo.png') }}" alt="" class="app-brand__logo">
                        <span class="app-brand__text">
                            <span class="app-brand__primary">SiHP Research</span>
                            <span class="app-brand__secondary">Database Management System</span>
                        </span>
                    </a>

                    <div class="app-nav-menu hidden xl:flex items-center gap-0.5 lg:gap-1 justify-center">
                        <a href="/dashboard"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-slate-700 hover:text-blue-700 hover:bg-slate-100">Dashboard</a>
                        <a href="/rm"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-slate-700 hover:text-blue-700 hover:bg-slate-100">Research Project</a>
                        <a href="/rm/create"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50">➕
                            เพิ่มโครงการ</a>

                        <a href="{{ route('back_office.index') }}"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-slate-700 hover:text-blue-700 hover:bg-slate-100">Missions</a>

                        <a href="{{ route('outputs.external-academic') }}"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-slate-700 hover:text-blue-700 hover:bg-slate-100">Extra</a>

                        <div class="relative" x-data="{ openOutputs: false }">
                            <button type="button" @click="openOutputs = !openOutputs" @click.away="openOutputs = false"
                                class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold inline-flex items-center gap-1 text-amber-700 hover:text-amber-800 hover:bg-amber-50 whitespace-nowrap">
                                Outputs
                                <svg :class="{ 'rotate-180': openOutputs }" class="w-4 h-4 text-amber-600 transition-transform"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div x-show="openOutputs" x-cloak
                                class="absolute left-0 mt-2 w-72 bg-white border border-slate-200 rounded-xl shadow-lg z-50 overflow-hidden">
                                <a href="{{ route('outputs.index') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    Outputs Overview
                                </a>
                                @if ($personalOutputsRoute)
                                    <a href="{{ $personalOutputsRoute }}"
                                        class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                        Outputs รายบุคคล
                                    </a>
                                @endif
                                <a href="{{ route('outputs.papers') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    Research Papers
                                </a>
                                <a href="{{ route('outputs.presentations') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    Presentations
                                </a>
                                <a href="{{ route('outputs.textbooks') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    Textbook / Book chapter
                                </a>
                                 <a href="{{ route('outputs.visualization') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    ข้อมูลผลงาน สำหรับสัมมมนา
                                </a>
                                <a href="{{ route('outputs.project-data-quality') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    รายงานข้อมูลผิดพลาดของโปรเจค
                                </a>
                            </div>
                        </div>

                        <div class="relative" x-data="{ openFte: false }">
                            <button type="button" @click="openFte = !openFte" @click.away="openFte = false"
                                class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold inline-flex items-center gap-1 text-slate-700 hover:text-blue-700 hover:bg-slate-100 whitespace-nowrap">
                                FTE
                                <svg :class="{ 'rotate-180': openFte }" class="w-4 h-4 text-gray-500 transition-transform"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div x-show="openFte" x-cloak
                                class="absolute left-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 overflow-hidden">
                                @if (! $shouldUsePersonalFteOnly)
                                    <a href="{{ route('fte.index') }}"
                                        class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                        FTE รายบุคคล
                                    </a>
                                @endif
                                @if ($personalFteRoute)
                                    <a href="{{ $personalFteRoute }}"
                                        class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                        FTE ของฉัน
                                    </a>
                                @endif
                                <a href="{{ route('fte.project.index') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    FTE รายโปรเจค
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('wfa.index') }}"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-slate-700 hover:text-blue-700 hover:bg-slate-100">WFA</a>
                    </div>

                    <div class="app-nav-actions">
                        <!-- Profile Dropdown (desktop only) -->
                        <div class="app-nav-desktop-profile relative hidden xl:block" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-10 h-10 bg-gray-200 rounded-full overflow-hidden focus:outline-none">
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}?v={{ time() }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                    alt="Profile" class="w-full h-full object-cover">
                            </button>

                            <div x-show="open" x-cloak @click.away="open = false"
                                class="absolute right-0 mt-2 w-72 bg-white border rounded shadow-md z-50">
                                <div class="px-4 py-2 text-sm text-gray-800 border-b leading-tight">
                                    <div class="font-medium">
                                        {{ Auth::user()->prefix }} {{ Auth::user()->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ตำแหน่ง: {{ Auth::user()->position }} ({{ Auth::user()->role }})
                                    </div>
                                </div>
                                <a href="{{ route('user_guide') }}"
                                    class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">คู่มือ</a>

                                @if ($authUser?->isAdminEquivalent())
                                    <a href="/user" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                        จัดการผู้ใช้
                                    </a>
                                @endif

                                @if ($authUser?->canViewLogs())
                                    <a href="/project-logs"
                                        class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                        Project log
                                    </a>
                                    @if ($authUser?->hasRole(\App\Models\User::ROLE_ADMIN))
                                        <a href="{{ route('back_office_action_logs.index') }}"
                                            class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                            Back Office Log
                                        </a>
                                    @endif
                                    <a href="{{ route('user_activity_logs.index') }}"
                                        class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                        User Activity Log
                                    </a>
                                @endif
                                <a href="{{ route('profile.show') }}"
                                    class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                    แก้ไขข้อมูลส่วนตัว
                                </a>

                                <form method="POST" action="{{ route('logout') }}" data-logout-form>
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        ออกจากระบบ
                                    </button>
                                </form>
                            </div>
                        </div>

                        <button type="button" class="inline-flex mobile-nav-toggle text-gray-600"
                            :class="{ 'is-open': openNav }"
                            @click="openNav = !openNav" aria-label="เปิดเมนู">
                            <span class="mobile-nav-toggle__label">เมนู</span>
                            <span class="mobile-hamburger" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    </div>
                </div>

                <div x-show="openNav" x-cloak>
                    <div class="mobile-drawer-overlay" x-show="openNav" @click="openNav = false"
                        x-transition.opacity.duration.200ms></div>

                    <aside class="mobile-drawer-panel" x-show="openNav" @click.stop
                        x-transition:enter="transform transition ease-out duration-250"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in duration-200"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full">
                        <div class="pt-1 pb-2 border-b flex items-center justify-between border-slate-200">
                            <div class="text-sm font-semibold text-slate-700">เมนู</div>
                            <button type="button" class="inline-flex mobile-nav-toggle text-gray-600"
                                @click="openNav = false" aria-label="ปิดเมนู">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                                </svg>
                            </button>
                        </div>
                        <div class="pt-3 pb-2 mobile-nav-links">
                            <div class="flex items-center gap-3 px-1 pb-3 border-b border-slate-200">
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}?v={{ time() }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                    alt="Profile" class="w-10 h-10 rounded-full object-cover">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 truncate">
                                        {{ Auth::user()->prefix }} {{ Auth::user()->name }}
                                    </div>
                                    <div class="text-xs text-slate-500 truncate">
                                        {{ Auth::user()->position }} ({{ Auth::user()->role }})
                                    </div>
                                </div>
                            </div>

                            <section class="mobile-nav-section">
                                <div class="mobile-nav-section__title">Main</div>
                                <a href="/dashboard">Dashboard</a>
                                <a href="/rm">Research Project</a>
                                <a href="/rm/create">➕ เพิ่มโครงการ</a>
                                <a href="{{ route('back_office.index') }}">Missions</a>
                                <a href="{{ route('outputs.external-academic') }}">Extra</a>
                            </section>

                            <section class="mobile-nav-section">
                                <div class="mobile-nav-section__title">Outputs</div>
                                <a href="{{ route('outputs.index') }}">Outputs Overview</a>
                                @if ($personalOutputsRoute)
                                    <a href="{{ $personalOutputsRoute }}">Outputs รายบุคคล</a>
                                @endif
                                <a href="{{ route('outputs.papers') }}">Research Papers</a>
                                <a href="{{ route('outputs.presentations') }}">Presentations</a>
                                <a href="{{ route('outputs.textbooks') }}">Textbook / Book chapter</a>
                                <a href="{{ route('outputs.visualization') }}">ข้อมูลผลงาน สำหรับสัมมมนา</a>
                                <a href="{{ route('outputs.project-data-quality') }}">รายงานข้อมูลผิดพลาดของโปรเจค</a>
                            </section>

                            <section class="mobile-nav-section">
                                <div class="mobile-nav-section__title">FTE</div>
                                @if (! $shouldUsePersonalFteOnly)
                                    <a href="{{ route('fte.index') }}">FTE รายบุคคล</a>
                                @endif
                                @if ($personalFteRoute)
                                    <a href="{{ $personalFteRoute }}">FTE ของฉัน</a>
                                @endif
                                <a href="{{ route('fte.project.index') }}">FTE รายโปรเจค</a>
                            </section>

                            <section class="mobile-nav-section">
                                <div class="mobile-nav-section__title">WFA</div>
                                <a href="{{ route('wfa.index') }}">WFA Reports</a>
                            </section>

                            <section class="mobile-nav-section">
                                <div class="mobile-nav-section__title">Account</div>
                                <a href="{{ route('user_guide') }}">คู่มือ</a>
                                <a href="{{ route('profile.show') }}">แก้ไขข้อมูลส่วนตัว</a>
                                @if ($authUser?->isAdminEquivalent())
                                    <a href="/user">จัดการผู้ใช้</a>
                                @endif
                            </section>

                            @if ($authUser?->canViewLogs())
                                <section class="mobile-nav-section">
                                    <div class="mobile-nav-section__title">Logs</div>
                                    <a href="/project-logs">Project log</a>
                                    @if ($authUser?->hasRole(\App\Models\User::ROLE_ADMIN))
                                        <a href="{{ route('back_office_action_logs.index') }}">Back Office Log</a>
                                    @endif
                                    <a href="{{ route('user_activity_logs.index') }}">User Activity Log</a>
                                </section>
                            @endif

                            <section class="mobile-nav-section">
                                <form method="POST" action="{{ route('logout') }}" data-logout-form>
                                    @csrf
                                    <button type="submit" class="mobile-logout-btn">
                                        ออกจากระบบ
                                    </button>
                                </form>
                            </section>
                        </div>
                    </aside>
                    </div>
                </div>
            </div>
        </nav>
    @endif

    <!-- ✅ main ก็ใช้ container เช่นกัน -->
    @php
        $isFullWidthPage = request()->routeIs('users.index') || request()->routeIs('outputs.visualization') || request()->routeIs('dashboard');
    @endphp
    <main class="{{ $isModalView ? 'pt-4 pb-4' : 'pt-24 pb-6' }} {{ $isFullWidthPage ? 'w-full px-0' : 'container mx-auto px-4' }}">
        @if (isset($header))
            <div class="mb-4">{{ $header }}</div>
        @endif
        @if (isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main>

    @php
        $hideAiChatOnPage = $isModalView
            || request()->routeIs('projects.create')
            || request()->routeIs('projects.edit')
            || request()->routeIs('back_office.*');
    @endphp
    @unless ($hideAiChatOnPage)
        @include('partials.global-ai-chat')
    @endunless

    @livewireScripts
</body>

</html>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<script>
    (() => {
        const logoutFlagKey = 'auth:logged_out';
        const loginUrl = @json(route('login'));

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form[data-logout-form]').forEach((form) => {
                if (form.dataset.logoutBound === '1') return;
                form.dataset.logoutBound = '1';
                form.addEventListener('submit', () => {
                    try {
                        window.__clearAppAiChatSession?.();
                        sessionStorage.setItem(logoutFlagKey, '1');
                    } catch (error) {}
                });
            });

            try {
                const navEntry = window.performance?.getEntriesByType?.('navigation')?.[0];
                const isBackForward = navEntry?.type === 'back_forward';
                if (!isBackForward) {
                    sessionStorage.removeItem(logoutFlagKey);
                }
            } catch (error) {}
        });

        window.addEventListener('pageshow', (event) => {
            try {
                const navEntry = window.performance?.getEntriesByType?.('navigation')?.[0];
                const isBackForward = event.persisted || navEntry?.type === 'back_forward';
                const hasLogoutFlag = sessionStorage.getItem(logoutFlagKey) === '1';

                if (isBackForward && hasLogoutFlag) {
                    window.location.replace(loginUrl);
                }
            } catch (error) {}
        });
    })();
</script>

<script>
    (function() {
        let locked = false;
        let scrollY = 0;

        function setMobileNavLock(open) {
            const shouldLock = Boolean(open);
            if (shouldLock === locked) return;

            locked = shouldLock;
            document.documentElement.classList.toggle('app-nav-open', shouldLock);
            document.body.classList.toggle('app-nav-open', shouldLock);

            if (shouldLock) {
                scrollY = window.scrollY || 0;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
                document.body.style.width = '100%';
            } else {
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.body.style.right = '';
                document.body.style.width = '';
                window.scrollTo(0, scrollY);
            }
        }

        window.addEventListener('app-mobile-nav', (event) => {
            setMobileNavLock(event.detail?.open);
        });
    })();
</script>

<script>
    // ===== Auto hide nav on scroll down, show on scroll up =====
    (function() {
        const nav = document.getElementById('main-nav');
        if (!nav) return;

        let lastY = window.scrollY || 0;
        let ticking = false;

        function onScroll() {
            const currentY = window.scrollY || 0;
            const delta = currentY - lastY;

            // Always show near top
            if (currentY < 40) {
                nav.classList.remove('nav-hidden');
                lastY = currentY;
                ticking = false;
                return;
            }

            if (Math.abs(delta) > 6) {
                if (delta > 0) {
                    nav.classList.add('nav-hidden'); // scrolling down -> hide
                } else {
                    nav.classList.remove('nav-hidden'); // scrolling up -> show
                }
                lastY = currentY;
            }
            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(onScroll);
                ticking = true;
            }
        }, {
            passive: true
        });
    })();

    /* =========================================================
       DATE-BE + REPEATABLE (FINAL / PRODUCTION SAFE)
       ========================================================= */

    function getClientTodayLocal() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return today;
    }

    function normalizeDateOnly(date) {
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function parseDateInputValue(raw) {
        const normalizeThaiDigits = (input) => String(input || '').replace(/[๐-๙]/g, (ch) => String('๐๑๒๓๔๕๖๗๘๙'.indexOf(ch)));
        const value = normalizeThaiDigits(String(raw || '').trim()).replace(/\s+/g, ' ');
        if (!value) return null;

        let match = value.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
        if (match) {
            const [, dd, mm, yyyy] = match;
            let year = Number(yyyy);
            if (year > 2400) year -= 543;
            return new Date(year, Number(mm) - 1, Number(dd));
        }

        match = value.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
        if (match) {
            const [, yyyy, mm, dd] = match;
            let year = Number(yyyy);
            if (year > 2400) year -= 543;
            return new Date(year, Number(mm) - 1, Number(dd));
        }

        match = value.match(/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/);
        if (match) {
            const [, dd, monthRaw, yyyyRaw] = match;
            const monthMap = {
                'มกราคม': 0, 'ม.ค.': 0, 'ม.ค': 0,
                'กุมภาพันธ์': 1, 'ก.พ.': 1, 'ก.พ': 1,
                'มีนาคม': 2, 'มี.ค.': 2, 'มี.ค': 2,
                'เมษายน': 3, 'เม.ย.': 3, 'เม.ย': 3,
                'พฤษภาคม': 4, 'พ.ค.': 4, 'พ.ค': 4,
                'มิถุนายน': 5, 'มิ.ย.': 5, 'มิ.ย': 5,
                'กรกฎาคม': 6, 'ก.ค.': 6, 'ก.ค': 6,
                'สิงหาคม': 7, 'ส.ค.': 7, 'ส.ค': 7,
                'กันยายน': 8, 'ก.ย.': 8, 'ก.ย': 8,
                'ตุลาคม': 9, 'ต.ค.': 9, 'ต.ค': 9,
                'พฤศจิกายน': 10, 'พ.ย.': 10, 'พ.ย': 10,
                'ธันวาคม': 11, 'ธ.ค.': 11, 'ธ.ค': 11
            };
            const monthIdx = monthMap[monthRaw];
            if (monthIdx !== undefined) {
                let yyyy = Number(yyyyRaw);
                if (yyyy > 2400) yyyy -= 543;
                return new Date(yyyy, monthIdx, Number(dd));
            }
        }

        const fallback = new Date(value);
        if (!Number.isNaN(fallback.getTime())) return fallback;
        return null;
    }

    function isProjectMobileWheelDateMode() {
        return window.PROJECT_MOBILE_WHEEL_DATE === true;
    }

    function toYmdCE(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const yyyy = String(date.getFullYear());
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function toDmyCE(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const yyyy = String(date.getFullYear());
        return `${dd}-${mm}-${yyyy}`;
    }

    function formatDateBELabel(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const day = date.getDate();
        const month = date.toLocaleDateString('th-TH', {
            month: 'long'
        });
        const year = date.getFullYear() + 543;
        return `${day} ${month} ${year}`;
    }

    function ensureThaiWheelPickerModal() {
        if (window.__thaiWheelPickerModal) return window.__thaiWheelPickerModal;

        if (!document.getElementById('thaiWheelPickerStyle')) {
            const style = document.createElement('style');
            style.id = 'thaiWheelPickerStyle';
            style.textContent = `
                .thai-wheel-overlay{position:fixed;inset:0;background:rgba(15,23,42,.14);display:none;align-items:flex-end;justify-content:center;z-index:120;backdrop-filter:blur(1.5px)}
                .thai-wheel-overlay.is-open{display:flex}
                .thai-wheel-sheet{width:min(680px,96vw);background:rgba(255,255,255,.86);border-radius:18px 18px 0 0;box-shadow:0 -10px 32px rgba(0,0,0,.13);padding:12px;border:1px solid rgba(203,213,225,.65)}
                .thai-wheel-title{font-size:14px;font-weight:700;color:#0f172a;margin:2px 4px 10px}
                .thai-wheel-body{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
                .thai-wheel-col{display:flex;flex-direction:column}
                .thai-wheel-col label{font-size:11px;color:#64748b;margin:0 0 4px 2px}
                .thai-wheel-select{height:168px;border:1px solid #cbd5e1;border-radius:12px;padding:2px 0;background:rgba(255,255,255,.9);font-size:18px;font-weight:600;color:#0f172a}
                .thai-wheel-select option{padding:8px 4px;text-align:center}
                .thai-wheel-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}
                .thai-wheel-btn{border:none;border-radius:12px;padding:11px 10px;font-size:16px;font-weight:700}
                .thai-wheel-btn--ok{background:#2563eb;color:#fff}
                .thai-wheel-btn--cancel{background:#e2e8f0;color:#0f172a}
            `;
            document.head.appendChild(style);
        }

        const monthNames = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const now = new Date();
        const nowBE = now.getFullYear() + 543;
        const yearMin = nowBE - 10;
        const yearMax = nowBE + 10;

        const overlay = document.createElement('div');
        overlay.className = 'thai-wheel-overlay';
        overlay.innerHTML = `
            <div class="thai-wheel-sheet" role="dialog" aria-modal="true" aria-label="เลือกวันที่">
                <div class="thai-wheel-title">เลือกวันที่ (พ.ศ.)</div>
                <div class="thai-wheel-body">
                    <div class="thai-wheel-col">
                        <label>วัน</label>
                        <select class="thai-wheel-select" id="thaiWheelDay" size="5"></select>
                    </div>
                    <div class="thai-wheel-col">
                        <label>เดือน</label>
                        <select class="thai-wheel-select" id="thaiWheelMonth" size="5"></select>
                    </div>
                    <div class="thai-wheel-col">
                        <label>ปี พ.ศ.</label>
                        <select class="thai-wheel-select" id="thaiWheelYear" size="5"></select>
                    </div>
                </div>
                <div class="thai-wheel-actions">
                    <button type="button" class="thai-wheel-btn thai-wheel-btn--ok" id="thaiWheelOk">เลือกวัน</button>
                    <button type="button" class="thai-wheel-btn thai-wheel-btn--cancel" id="thaiWheelCancel">ยกเลิก</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const daySel = overlay.querySelector('#thaiWheelDay');
        const monthSel = overlay.querySelector('#thaiWheelMonth');
        const yearSel = overlay.querySelector('#thaiWheelYear');
        const okBtn = overlay.querySelector('#thaiWheelOk');
        const cancelBtn = overlay.querySelector('#thaiWheelCancel');
        let onConfirm = null;

        for (let m = 0; m < 12; m++) {
            const opt = document.createElement('option');
            opt.value = String(m + 1);
            opt.textContent = monthNames[m];
            monthSel.appendChild(opt);
        }
        for (let y = yearMin; y <= yearMax; y++) {
            const opt = document.createElement('option');
            opt.value = String(y);
            opt.textContent = String(y);
            yearSel.appendChild(opt);
        }

        const daysInMonth = (yearCE, month1) => new Date(yearCE, month1, 0).getDate();
        const refillDays = () => {
            const yearBE = Number(yearSel.value || nowBE);
            const month = Number(monthSel.value || 1);
            const maxDay = daysInMonth(yearBE - 543, month);
            const prev = Number(daySel.value || 1);
            daySel.innerHTML = '';
            for (let d = 1; d <= maxDay; d++) {
                const opt = document.createElement('option');
                opt.value = String(d);
                opt.textContent = String(d);
                daySel.appendChild(opt);
            }
            daySel.value = String(Math.min(prev, maxDay));
        };

        const setDate = (date) => {
            const d = date instanceof Date && !Number.isNaN(date.getTime()) ? date : new Date();
            monthSel.value = String(d.getMonth() + 1);
            yearSel.value = String(d.getFullYear() + 543);
            refillDays();
            daySel.value = String(d.getDate());
        };

        const open = (date, callback) => {
            onConfirm = callback;
            setDate(date);
            overlay.classList.add('is-open');
        };
        const close = () => overlay.classList.remove('is-open');

        monthSel.addEventListener('change', refillDays);
        yearSel.addEventListener('change', refillDays);
        cancelBtn.addEventListener('click', close);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) close();
        });
        okBtn.addEventListener('click', () => {
            const day = Number(daySel.value || 1);
            const month = Number(monthSel.value || 1);
            const yearBE = Number(yearSel.value || nowBE);
            const picked = new Date(yearBE - 543, month - 1, day);
            if (typeof onConfirm === 'function') onConfirm(picked);
            close();
        });

        window.__thaiWheelPickerModal = { open };
        return window.__thaiWheelPickerModal;
    }

    function initNativeMobileDateInput(input) {
        if (!(input instanceof HTMLInputElement)) return;
        if (input.dataset.nativeDateReady === '1') return;

        const parsed = parseDateInputValue(input.value);
        const holder = input.closest('.relative') || input.parentElement;
        if (!holder) return;

        let displayInput = holder.querySelector(`input[data-native-date-display-for="${CSS.escape(input.name || '')}"]`);
        if (!displayInput) {
            displayInput = document.createElement('input');
            displayInput.type = 'text';
            displayInput.readOnly = true;
            displayInput.className = input.className;
            displayInput.classList.remove('js-date-be', 'thai-datepicker', 'native-date-raw', 'flatpickr-hidden');
            displayInput.dataset.nativeDateDisplayFor = input.name || '';
            holder.appendChild(displayInput);
        }

        const placeholder = input.getAttribute('placeholder') || input.dataset.placeholder || '';
        if (placeholder) {
            displayInput.placeholder = placeholder;
            displayInput.dataset.placeholder = placeholder;
        }

        input.type = 'hidden';
        input.classList.add('native-date-raw');
        input.style.display = 'none';
        input.value = parsed ? toYmdCE(parsed) : '';

        const syncDisplay = () => {
            const rawDate = parseDateInputValue(input.value);
            displayInput.value = rawDate ? formatDateBELabel(rawDate) : '';
        };
        syncDisplay();
        input.addEventListener('input', syncDisplay);
        input.addEventListener('change', syncDisplay);

        const openWheel = () => {
            const picker = ensureThaiWheelPickerModal();
            const current = parseDateInputValue(input.value) || getClientTodayLocal();
            picker.open(current, (picked) => {
                if (input.dataset.noPast === '1' && normalizeDateOnly(picked) < getClientTodayLocal()) {
                    return;
                }
                input.value = toYmdCE(picked);
                syncDisplay();
                input.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
                input.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            });
        };

        displayInput.addEventListener('click', openWheel);

        if (input.id) {
            const btn = document.querySelector(`[data-open-picker][data-target="${input.id}"]`);
            if (btn && btn.dataset.nativePickerBound !== '1') {
                btn.dataset.nativePickerBound = '1';
                btn.addEventListener('click', openWheel);
            }
        }
        input.dataset.nativeDateReady = '1';
    }

    window.initNativeMobileDateInput = initNativeMobileDateInput;

    function appendFlatpickrHook(instance, hook, callback) {
        if (!instance || typeof callback !== 'function') return;
        const current = instance.config?.[hook];
        if (Array.isArray(current)) {
            if (!current.includes(callback)) current.push(callback);
            return;
        }
        if (typeof current === 'function') {
            instance.config[hook] = [current, callback];
            return;
        }
        instance.config[hook] = [callback];
    }

    function bindEndDateMinFromStart(startInput, endInput, key) {
        if (!(startInput instanceof HTMLElement) || !(endInput instanceof HTMLElement)) {
            return;
        }

        const retryKey = `__dateRangeRetry_${key}`;
        const startReady = !!startInput._flatpickr;
        const endReady = !!endInput._flatpickr;
        if (!startReady || !endReady) {
            const retries = Number(endInput[retryKey] || 0);
            if (retries >= 60) return;
            endInput[retryKey] = String(retries + 1);
            setTimeout(() => bindEndDateMinFromStart(startInput, endInput, key), 80);
            return;
        }
        delete endInput[retryKey];

        const updaterKey = `__dateRangeUpdater_${key}`;
        if (!endInput[updaterKey]) {
            endInput[updaterKey] = () => {
                const fpEnd = endInput._flatpickr;
                if (!fpEnd) return;

                const startDate = startInput._flatpickr?.selectedDates?.[0] ||
                    parseDateInputValue(startInput.value) ||
                    parseDateInputValue(startInput._flatpickr?.altInput?.value);
                const minDate = startDate
                    ? normalizeDateOnly(startDate)
                    : (endInput.dataset.noPast === '1' ? getClientTodayLocal() : null);

                fpEnd.set('minDate', minDate || null);

                const endDate = fpEnd.selectedDates?.[0] ||
                    parseDateInputValue(endInput.value) ||
                    parseDateInputValue(fpEnd.altInput?.value);
                if (endDate && minDate && normalizeDateOnly(endDate) < minDate) {
                    fpEnd.clear();
                    endInput.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            };

            startInput.addEventListener('change', endInput[updaterKey]);
            startInput.addEventListener('input', endInput[updaterKey]);
        }

        const hookKey = `${updaterKey}_hooked`;
        if (!endInput[hookKey]) {
            const fpStart = startInput._flatpickr;
            if (fpStart) {
                appendFlatpickrHook(fpStart, 'onChange', endInput[updaterKey]);
                appendFlatpickrHook(fpStart, 'onValueUpdate', endInput[updaterKey]);
            }

            const fpEnd = endInput._flatpickr;
            if (fpEnd) {
                appendFlatpickrHook(fpEnd, 'onOpen', endInput[updaterKey]);
                fpEnd.altInput?.addEventListener('focus', endInput[updaterKey]);
                fpEnd.altInput?.addEventListener('click', endInput[updaterKey]);
            }

            endInput[hookKey] = '1';
        }

        endInput[updaterKey]();
    }

    function bindProjectDatePairs() {
        if (isProjectMobileWheelDateMode()) {
            const bindNativeRange = (startInput, endInput) => {
                if (!(startInput instanceof HTMLInputElement) || !(endInput instanceof HTMLInputElement)) return;
                const update = () => {
                    const startDate = parseDateInputValue(startInput.value);
                    if (startDate) {
                        endInput.min = toYmdCE(normalizeDateOnly(startDate));
                    } else if (endInput.dataset.noPast === '1') {
                        endInput.min = toYmdCE(getClientTodayLocal());
                    } else {
                        endInput.removeAttribute('min');
                    }
                    const endDate = parseDateInputValue(endInput.value);
                    if (startDate && endDate && normalizeDateOnly(endDate) < normalizeDateOnly(startDate)) {
                        endInput.value = '';
                        endInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                };
                startInput.addEventListener('change', update);
                startInput.addEventListener('input', update);
                endInput.addEventListener('change', update);
                endInput.addEventListener('input', update);
                update();
            };

            bindNativeRange(
                document.querySelector('input[name="team_start_date"]'),
                document.querySelector('input[name="team_end_date"]')
            );
            bindNativeRange(
                document.querySelector('input[name="start_date"]'),
                document.querySelector('input[name="end_date"]')
            );
            document.querySelectorAll('input[name^="fte_roles["][name$="[start_date]"]').forEach((startInput) => {
                const endName = startInput.name.replace('[start_date]', '[end_date]');
                const endInput = document.querySelector(`input[name="${endName}"]`);
                bindNativeRange(startInput, endInput);
            });
            return;
        }

        bindEndDateMinFromStart(
            document.querySelector('input[name="team_start_date"]'),
            document.querySelector('input[name="team_end_date"]'),
            'team'
        );

        bindEndDateMinFromStart(
            document.querySelector('input[name="start_date"]'),
            document.querySelector('input[name="end_date"]'),
            'contract'
        );

        document.querySelectorAll('input[name^="fte_roles["][name$="[start_date]"]').forEach((startInput) => {
            const endName = startInput.name.replace('[start_date]', '[end_date]');
            const endInput = document.querySelector(`input[name="${endName}"]`);
            bindEndDateMinFromStart(startInput, endInput, `fte_${startInput.name}`);
        });
    }

    /* ---------- DATE BE INIT ---------- */
    function initDateBE(scope = document) {
        const toDmyCE = (date) => {
            if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
            const dd = String(date.getDate()).padStart(2, '0');
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const yyyy = String(date.getFullYear());
            return `${dd}-${mm}-${yyyy}`;
        };

        scope.querySelectorAll('.js-date-be, .thai-datepicker').forEach(input => {
            if (input.hasAttribute('data-native-date-display-for')) return;
            if (isProjectMobileWheelDateMode()) {
                initNativeMobileDateInput(input);
                return;
            }

            // ❗ กัน init ซ้ำ
            if (input._flatpickr) return;

            // รองรับค่าที่อาจเป็น พ.ศ. หรือรูปแบบเก่าจาก old()/autosave
            // ให้ normalize เป็น d-m-Y (ค.ศ.) ก่อน init picker
            const normalized = toDmyCE(parseDateInputValue(input.value));
            if (normalized) {
                input.value = normalized;
            }

            const noPast = input.dataset.noPast === '1';

            const fp = flatpickr(input, {
                locale: 'th',
                dateFormat: 'd-m-Y', // ✅ ค่าจริง (ค.ศ.)
                altInput: true,
                altFormat: 'j F Y', // ใช้ตอน init เท่านั้น
                disableMobile: true,
                ...(noPast ? {
                    minDate: getClientTodayLocal()
                } : {}),

                onReady(selectedDates, __, inst) {
                    if (noPast) {
                        inst.set('minDate', getClientTodayLocal());
                    }
                    syncAltStyle(input, inst);
                    forceHeaderBE(inst);
                    if (inst.altInput && selectedDates && selectedDates[0]) {
                        const d = selectedDates[0];
                        const day = d.getDate();
                        const month = d.toLocaleDateString('th-TH', {
                            month: 'long'
                        });
                        const year = d.getFullYear() + 543;
                        inst.altInput.value = `${day} ${month} ${year}`;
                    }
                },

                onOpen(_, __, inst) {
                    if (noPast) {
                        inst.set('minDate', getClientTodayLocal());
                    }
                    forceHeaderBE(inst);
                    setTimeout(() => forceHeaderBE(inst), 0);
                    setTimeout(() => forceHeaderBE(inst), 50);
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

                    const day = d.getDate();
                    const month = d.toLocaleDateString('th-TH', {
                        month: 'long'
                    });
                    const year = d.getFullYear() + 543;

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

        bindProjectDatePairs();
    }

    function syncFlatpickrRawValue(input) {
        if (!(input instanceof HTMLInputElement)) return;

        const fp = input._flatpickr;
        if (!fp) return;

        // Always prefer the visible text field near this input first.
        const scope = input.closest('label, .relative, .w-full, .grid, .space-y-2') || input.parentElement;
        const visibleInput = scope?.querySelector?.('input:not([name]):not([type="hidden"])');
        const visibleRaw = (visibleInput?.value || '').trim();

        let picked = parseDateInputValue(visibleRaw);
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
            const altRaw = (fp.altInput?.value || '').trim();
            picked = parseDateInputValue(altRaw);
        }
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
            picked = fp.selectedDates?.[0];
        }
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
            picked = parseDateInputValue((input.value || '').trim());
        }
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) return;

        const day = String(picked.getDate()).padStart(2, '0');
        const month = String(picked.getMonth() + 1).padStart(2, '0');
        const year = String(picked.getFullYear());
        input.value = `${day}-${month}-${year}`;
    }

    function syncNativeWheelRawValue(input) {
        if (!(input instanceof HTMLInputElement)) return;
        if (!input.classList.contains('native-date-raw')) return;

        const holder = input.closest('.relative') || input.parentElement;
        const displayInput = holder?.querySelector?.(`input[data-native-date-display-for="${CSS.escape(input.name || '')}"]`);

        let picked = parseDateInputValue((displayInput?.value || '').trim());
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
            picked = parseDateInputValue((input.value || '').trim());
        }

        input.value = (picked instanceof Date && !Number.isNaN(picked.getTime()))
            ? toYmdCE(picked)
            : '';
    }

    function syncDateInputsBeforeSubmit(form) {
        if (!(form instanceof HTMLFormElement)) return;
        form.querySelectorAll('input.thai-datepicker, input.js-date-be').forEach((input) => {
            syncFlatpickrRawValue(input);
            syncNativeWheelRawValue(input);
        });
    }

    function getProjectValidationDisplayTarget(field) {
        if (!(field instanceof HTMLElement)) return field;

        if (field instanceof HTMLSelectElement) {
            const tomSelectControl = field.tomselect?.control;
            if (
                tomSelectControl instanceof HTMLElement &&
                (
                    field.tomselect?.wrapper?.offsetParent !== null ||
                    tomSelectControl.offsetParent !== null ||
                    tomSelectControl.getClientRects?.().length
                )
            ) {
                return tomSelectControl;
            }
        }

        if (field instanceof HTMLInputElement) {
            const flatpickrAltInput = field._flatpickr?.altInput;
            if (flatpickrAltInput instanceof HTMLInputElement && flatpickrAltInput.offsetParent !== null) {
                return flatpickrAltInput;
            }

            const name = field.getAttribute('name') || '';
            if (name) {
                const nativeDisplayInput = document.querySelector(
                    `input[data-native-date-display-for="${CSS.escape(name)}"]`
                );
                if (nativeDisplayInput instanceof HTMLInputElement && nativeDisplayInput.offsetParent !== null) {
                    return nativeDisplayInput;
                }
            }
        }

        return field;
    }

    function closeProjectSearchDropdowns(except = null) {
        document.querySelectorAll('[data-project-search-select-menu]').forEach((menu) => {
            if (except && menu === except) return;
            menu.classList.add('hidden');
        });
    }

    function ensureProjectSearchDropdown(select, config = {}) {
        if (!(select instanceof HTMLSelectElement)) return null;

        const staleWrapper = select.nextElementSibling;
        if (staleWrapper instanceof HTMLElement && staleWrapper.matches('.project-search-select') && !select.tomselect) {
            staleWrapper.remove();
            delete select.dataset.projectSearchDropdown;
            delete select.dataset.projectSearchDropdownBound;
        }

        if (select.tomselect?.destroy && select.dataset.projectSearchDropdown !== '1') {
            select.tomselect.destroy();
        }

        if (select.dataset.projectSearchDropdown === '1' && select.tomselect) {
            select.tomselect.refreshOptions();
            return select.tomselect;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'project-search-select';
        wrapper.dataset.projectSearchDropdown = '1';
        wrapper.setAttribute('data-project-search-dropdown', '1');
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

        const events = {};
        const emit = (eventName, ...args) => {
            (events[eventName] || []).forEach((handler) => {
                if (typeof handler === 'function') {
                    handler(...args);
                }
            });
        };

        const placeholder = String(
            config.placeholder ||
            select.dataset.searchPlaceholder ||
            select.options[0]?.textContent?.trim() ||
            'เลือกข้อมูล'
        ).trim();
        searchInput.placeholder = String(config.searchPlaceholder || 'พิมพ์เพื่อค้นหา...').trim();

        const optionItems = () => Array.from(select.options)
            .filter((option) => option.value !== '')
            .map((option) => ({
                value: String(option.value),
                label: String(option.textContent || '').trim(),
            }));
        const normalizeSearchText = (value) => {
            const normalized = String(value || '').toLowerCase().trim();

            return config.ignoreSearchWhitespace
                ? normalized.replace(/[\s\u200B\u200C\u200D\uFEFF]+/g, '')
                : normalized;
        };

        const syncLabel = () => {
            const selectedOption = Array.from(select.options).find((option) => option.value === select.value);
            label.textContent = selectedOption && selectedOption.value
                ? String(selectedOption.textContent || '').trim()
                : placeholder;
            toggle.title = label.textContent;
        };

        const renderOptions = () => {
            const term = normalizeSearchText(searchInput.value);
            const items = optionItems().filter((item) => term === '' || normalizeSearchText(item.label).includes(term));
            optionsWrap.innerHTML = '';

            if (!items.length) {
                emptyEl.classList.remove('hidden');
                return;
            }

            emptyEl.classList.add('hidden');
            items.forEach((item) => {
                const optionButton = document.createElement('button');
                optionButton.type = 'button';
                optionButton.className = 'project-search-select__option';
                optionButton.textContent = item.label;
                optionButton.dataset.value = item.value;
                if (item.value === String(select.value || '')) {
                    optionButton.classList.add('is-selected');
                }
                optionButton.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                });
                optionButton.addEventListener('click', () => {
                    const previousValue = String(select.value || '');
                    select.value = item.value;
                    if (String(select.value || '').trim()) {
                        select.setCustomValidity('');
                    }
                    syncLabel();
                    renderOptions();
                    menu.classList.add('hidden');
                    emit('item_add', item.value, previousValue);
                    emit('dropdown_close');
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });
                optionsWrap.appendChild(optionButton);
            });
        };

        const syncState = () => {
            toggle.disabled = !!select.disabled;
            wrapper.classList.toggle('opacity-60', !!select.disabled);
            wrapper.classList.toggle('pointer-events-none', !!select.disabled);
            syncLabel();
            if (select.disabled) {
                menu.classList.add('hidden');
            }
        };

        toggle.addEventListener('click', () => {
            if (select.disabled) return;
            const willOpen = menu.classList.contains('hidden');
            closeProjectSearchDropdowns(willOpen ? menu : null);
            menu.classList.toggle('hidden', !willOpen);
            if (willOpen) {
                searchInput.value = '';
                renderOptions();
                searchInput.focus();
                emit('dropdown_open');
            } else {
                emit('dropdown_close');
            }
        });

        wrapper.addEventListener('mousedown', (event) => {
            event.stopPropagation();
        });

        wrapper.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        searchInput.addEventListener('input', renderOptions);
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                menu.classList.add('hidden');
                toggle.focus();
                emit('dropdown_close');
            }
        });

        if (select.dataset.projectSearchDropdownBound !== '1') {
            select.dataset.projectSearchDropdownBound = '1';
            select.addEventListener('change', () => {
                syncState();
                renderOptions();
            });
        }

        const api = {
            wrapper,
            control: toggle,
            control_input: searchInput,
            dropdown: menu,
            on(eventName, handler) {
                events[eventName] = events[eventName] || [];
                events[eventName].push(handler);
            },
            clear(silent = false) {
                this.setValue('', silent);
            },
            setValue(value, silent = false) {
                select.value = value == null ? '' : String(value);
                if (String(select.value || '').trim()) {
                    select.setCustomValidity('');
                }
                syncState();
                renderOptions();
                if (!silent) {
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            },
            getValue() {
                return String(select.value || '');
            },
            enable() {
                select.disabled = false;
                syncState();
            },
            disable() {
                select.disabled = true;
                syncState();
            },
            refreshOptions() {
                syncState();
                renderOptions();
            },
            destroy() {
                wrapper.remove();
                select.classList.remove('project-search-select__native');
                delete select.tomselect;
                delete select.dataset.projectSearchDropdown;
            },
        };

        select.dataset.projectSearchDropdown = '1';
        select.tomselect = api;

        syncState();
        renderOptions();
        return api;
    }

    function syncProjectSearchDropdown(select) {
        if (!(select instanceof HTMLSelectElement)) return;
        select.tomselect?.refreshOptions?.();
    }

    function syncProjectValidationClasses(field, invalid) {
        if (!(field instanceof HTMLElement)) return;

        const toggleClasses = (el) => {
            if (!(el instanceof HTMLElement)) return;
            el.classList.toggle('border-red-500', invalid);
            el.classList.toggle('bg-red-50', invalid);
        };

        const displayTarget = getProjectValidationDisplayTarget(field);
        toggleClasses(displayTarget);

        if (field !== displayTarget) {
            toggleClasses(field);
        }

        if (field instanceof HTMLSelectElement && field.tomselect) {
            toggleClasses(field.tomselect.control);
            const controlInput = field.tomselect.control_input;
            if (controlInput instanceof HTMLElement) {
                toggleClasses(controlInput);
            }
            if (field.tomselect.wrapper instanceof HTMLElement) {
                field.tomselect.wrapper.classList.remove('border-red-500', 'bg-red-50');
            }
            if (invalid) {
                field.tomselect.wrapper?.setAttribute?.('aria-invalid', 'true');
                field.tomselect.control?.setAttribute?.('aria-invalid', 'true');
            } else {
                field.tomselect.wrapper?.removeAttribute?.('aria-invalid');
                field.tomselect.control?.removeAttribute?.('aria-invalid');
            }
        }
    }

    function parseProjectComparableDateInput(input) {
        if (!(input instanceof HTMLInputElement)) return null;

        const values = [
            input.value,
            input._flatpickr?.altInput?.value,
        ];

        const name = input.getAttribute('name') || '';
        if (name) {
            values.push(
                document.querySelector(`input[data-native-date-display-for="${CSS.escape(name)}"]`)?.value || ''
            );
        }

        for (const raw of values) {
            const parsed = parseDateInputValue((raw || '').trim());
            if (parsed instanceof Date && !Number.isNaN(parsed.getTime())) {
                return normalizeDateOnly(parsed);
            }
        }

        return null;
    }

    function getFteDateRangePair(field) {
        if (!(field instanceof HTMLInputElement)) return null;

        const name = field.getAttribute('name') || '';
        const match = name.match(/^(fte_roles\[\d+\])\[(start_date|end_date)\]$/);
        if (!match) return null;

        const [, baseName] = match;
        const form = field.form;
        if (!(form instanceof HTMLFormElement)) return null;

        const startInput = form.querySelector(`input[name="${CSS.escape(`${baseName}[start_date]`)}"]`);
        const endInput = form.querySelector(`input[name="${CSS.escape(`${baseName}[end_date]`)}"]`);

        if (!(startInput instanceof HTMLInputElement) || !(endInput instanceof HTMLInputElement)) {
            return null;
        }

        return {
            startInput,
            endInput,
        };
    }

    function applyFteDateRangeValidity(startInput, endInput) {
        if (!(startInput instanceof HTMLInputElement) || !(endInput instanceof HTMLInputElement)) {
            return true;
        }

        const startDate = parseProjectComparableDateInput(startInput);
        const endDate = parseProjectComparableDateInput(endInput);
        const isValid = !(startDate && endDate && endDate < startDate);

        endInput.setCustomValidity(isValid ? '' : 'วันสิ้นสุดภาระงาน ต้องไม่น้อยกว่าวันเริ่มต้นภาระงาน');

        return isValid;
    }

    function validateProjectFteDateRanges(form) {
        if (!(form instanceof HTMLFormElement)) {
            return {
                ok: true,
                firstInvalidField: null,
            };
        }

        let firstInvalidField = null;

        form.querySelectorAll('input[name^="fte_roles["][name$="[start_date]"]').forEach((startInput) => {
            const endName = startInput.name.replace('[start_date]', '[end_date]');
            const endInput = form.querySelector(`input[name="${CSS.escape(endName)}"]`);
            const isValid = applyFteDateRangeValidity(startInput, endInput);

            if (!isValid && !firstInvalidField) {
                firstInvalidField = getProjectValidationDisplayTarget(endInput);
            }
        });

        return {
            ok: !firstInvalidField,
            firstInvalidField,
        };
    }

    window.getProjectValidationDisplayTarget = getProjectValidationDisplayTarget;
    window.syncProjectValidationClasses = syncProjectValidationClasses;
    window.validateProjectFteDateRanges = validateProjectFteDateRanges;
    window.ensureProjectSearchDropdown = ensureProjectSearchDropdown;
    window.syncProjectSearchDropdown = syncProjectSearchDropdown;
    window.closeProjectSearchDropdowns = closeProjectSearchDropdowns;

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-project-search-dropdown]')) {
            closeProjectSearchDropdowns();
        }
    });

    document.addEventListener('submit', (event) => {
        syncDateInputsBeforeSubmit(event.target);
    }, true);

    const syncProjectFteDateRangeField = (field) => {
        const pair = getFteDateRangePair(field);
        if (!pair) return false;
        applyFteDateRangeValidity(pair.startInput, pair.endInput);
        return true;
    };

    document.addEventListener('input', (event) => {
        if (!(event.target instanceof HTMLInputElement)) return;
        const handled = syncProjectFteDateRangeField(event.target);
        if (handled && window.isFteTableMode?.()) {
            window.syncAllFteProxyInvalidStates?.();
        }
    }, true);

    document.addEventListener('change', (event) => {
        if (!(event.target instanceof HTMLInputElement)) return;
        const handled = syncProjectFteDateRangeField(event.target);
        if (handled && window.isFteTableMode?.()) {
            window.syncAllFteProxyInvalidStates?.();
        }
    }, true);

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form#projectForm').forEach((form) => {
            validateProjectFteDateRanges(form);
        });
    });


    /* ---------- ทำให้ altInput หน้าตาเหมือน input เดิม ---------- */
    function syncAltStyle(originalInput, inst) {
        if (!inst.altInput) return;

        // copy class
        inst.altInput.className = originalInput.className;
        inst.altInput.classList.remove('js-date-be', 'thai-datepicker', 'native-date-raw', 'flatpickr-hidden');

        // placeholder
        const ph = originalInput.getAttribute('placeholder') || originalInput.dataset.placeholder;
        if (ph) inst.altInput.placeholder = ph;

        inst.altInput.readOnly = true;

        // hide the original input to avoid duplicate fields (mobile bug)
        originalInput.classList.add('flatpickr-hidden');
        originalInput.setAttribute('aria-hidden', 'true');
        originalInput.tabIndex = -1;
        originalInput.style.display = 'none';
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
        ['.flatpickr-prev-month', '.flatpickr-next-month'].forEach(sel => {
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
        0% {
            background-color: #dbeafe;
        }

        100% {
            background-color: transparent;
        }
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
        color: #334155;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 0.15rem 0.6rem;
        border-radius: 9999px;
    }

    .row-collapse-toggle-with-remove {
        right: 0.5rem;
    }

    .row-collapsed> :not(.row-collapse-toggle) {
        display: none !important;
    }

    .row-collapsed>.row-collapse-summary {
        display: flex !important;
    }

    /* ซ่อนปุ่มลูกศร < > บน Flatpickr */
    .flatpickr-calendar .flatpickr-prev-month,
    .flatpickr-calendar .flatpickr-next-month {
        display: none !important;
    }

    .row-collapse-summary {
        width: 100%;
        display: none;
        white-space: normal;
        overflow: hidden;
        text-overflow: clip;
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

    .row-collapse-summary .summary-primary {
        font-weight: 600;
        color: #0f172a;
    }

    .row-collapse-summary .summary-muted {
        color: #64748b;
    }

    .row-collapse-summary-text {
        flex: 1 1 auto;
        min-width: 0;
    }

    .row-collapse-summary-text .summary-output-card {
        display: grid;
        gap: 0.3rem;
        border-left: 3px solid #cbd5e1;
        background: transparent;
        border-radius: 0.6rem;
        padding: 0.45rem 0.55rem 0.45rem 0.65rem;
    }

    .row-collapse-summary-text .summary-output-top {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .row-collapse-summary-text .summary-output-type {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        border: 1px solid #cbd5e1;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.08rem 0.5rem;
    }

    .row-collapse-summary-text .summary-output-type--ea101 {
        background: #7aa2f71a;
        border-color: #7aa2f766;
        color: #7aa2f7;
    }

    .row-collapse-summary-text .summary-output-type--ea103 {
        background: #f7b2671a;
        border-color: #f7b26766;
        color: #f7b267;
    }

    .row-collapse-summary-text .summary-output-type--ea119 {
        background: #f4b1831a;
        border-color: #f4b18366;
        color: #f4b183;
    }

    .row-collapse-summary-text .summary-output-type--ea120 {
        background: #e8a5a51a;
        border-color: #e8a5a566;
        color: #e8a5a5;
    }

    .row-collapse-summary-text .summary-output-line {
        display: flex;
        gap: 0.35rem;
        align-items: flex-start;
        line-height: 1.35;
        color: #334155;
    }

    .row-collapse-summary-text .summary-output-key {
        flex: 0 0 auto;
        color: #0f172a;
        font-weight: 700;
    }

    .row-collapse-summary-text .summary-output-value {
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
        color: #475569;
    }

    .row-collapse-summary-text .summary-output-line--title .summary-output-value {
        font-size: 1.02rem;
        font-weight: 700;
        color: #0f172a;
    }

    .output-row:nth-of-type(even).row-collapsed {
        background: #f6f8fb;
        border-color: #e5eaf2;
    }

    .output-row:nth-of-type(odd).row-collapsed {
        background: #ffffff;
        border-color: #e5eaf2;
    }

    .row-collapse-remove {
        flex: 0 0 auto;
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fff1f2;
        border-radius: 9999px;
        padding: 0.1rem 0.55rem;
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .row-collapsible.has-inline-remove {
        padding-bottom: 2rem;
    }

    .row-collapsible.has-inline-remove [data-repeatable-remove].repeatable-remove-text {
        position: absolute;
        right: 0.5rem;
        bottom: 0.5rem;
        z-index: 6;
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fff1f2;
        border-radius: 9999px;
        padding: 0.1rem 0.55rem;
    }

    .row-collapsed .row-collapse-remove {
        position: absolute;
        bottom: 0.5rem;
        right: 0.5rem;
        z-index: 6;
    }

    .row-collapsed .row-collapse-toggle,
    .row-collapsed .row-collapse-toggle-with-remove {
        top: 0.5rem;
        right: 0.5rem;
    }

    .row-collapsed {
        min-height: 2.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #f8fafc;
        box-sizing: border-box;
        padding-bottom: 2rem;
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

    .project-form .repeatable-remove-text {
        font-size: 0.75rem;
        line-height: 1rem;
        font-weight: 600;
        color: #b91c1c;
    }

    .row-collapse-summary {
        padding: 0.4rem 0.8rem;
    }

    .no-row-collapse-toggle .row-collapse-toggle,
    .no-row-collapse-toggle .row-collapse-summary {
        display: none !important;
    }

    @media (max-width: 640px) {
        .output-row.row-collapsed {
            padding-bottom: 0.8rem;
        }

        .output-row.row-collapsed .row-collapse-summary {
            padding-top: 2.05rem;
            padding-right: 0.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            align-items: stretch;
        }

        .output-row.row-collapsed .row-collapse-summary .row-collapse-summary-text {
            width: 100%;
        }

        .output-row.row-collapsed .row-collapse-summary .summary-output-card {
            padding: 0.42rem 0.5rem 0.45rem 0.6rem;
        }

        .output-row.row-collapsed .row-collapse-remove {
            position: static;
            margin-left: auto;
            margin-top: 0.1rem;
        }

        .row-collapse-summary {
            padding-right: 3.5rem;
        }

        .row-collapse-toggle {
            top: 0.35rem;
            right: 0.35rem;
        }

        .row-collapse-toggle-with-remove {
            right: 0.35rem;
        }

        .row-collapsed .row-collapse-remove {
            bottom: 0.35rem;
            right: 0.35rem;
        }

        .row-collapsible.has-inline-remove [data-repeatable-remove].repeatable-remove-text {
            right: 0.35rem;
            bottom: 0.35rem;
        }

        .row-collapsed .row-collapse-toggle,
        .row-collapsed .row-collapse-toggle-with-remove {
            top: 0.35rem;
            right: 0.35rem;
        }
    }

    .fte-tools {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        width: 100%;
    }

    .fte-tools__search {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        padding: 0.55rem 0.75rem;
        font-size: 0.9rem;
    }

    .fte-tools__table-btn {
        border: 1px solid #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 0.5rem;
        padding: 0.5rem 0.85rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
    }

    .fte-tools__table-btn.is-active {
        background: #dbeafe;
    }

    .fte-tools__actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 0 0 auto;
    }

    .fte-tools__clear-btn {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
    }

    @media (min-width: 768px) {
        .fte-tools {
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
        }

        .fte-tools__search {
            width: auto;
            flex: 1 1 auto;
            min-width: 0;
        }

        #fte-container.fte-cards-hidden {
            position: absolute !important;
            left: -99999px !important;
            top: auto !important;
            width: 1px !important;
            height: 1px !important;
            overflow: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        #fte-table-wrap {
            display: none;
            margin-bottom: 1rem;
            border: 1px solid #dbeafe;
            border-radius: 0.75rem;
            overflow: visible;
            background: #ffffff;
        }

        #fte-table-wrap.is-active {
            display: block;
        }

        .fte-table-log {
            display: none;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
            margin-top: 0.55rem;
            color: #111827;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .fte-table-log.is-active {
            display: flex;
        }

        .fte-table-log__text {
            min-width: 0;
            line-height: 1.35;
        }

        .fte-table-log__color-green {
            color: #15803d;
            font-weight: 700;
        }

        .fte-table-log__color-yellow {
            color: #ca8a04;
            font-weight: 700;
        }

        .fte-table-log__color-red {
            color: #be123c;
            font-weight: 700;
        }

        .fte-table-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .fte-table-grid thead th {
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.5rem;
            border-bottom: 1px solid #bfdbfe;
            text-align: left;
            white-space: normal;
            position: sticky;
            top: 0;
            z-index: 1;
            line-height: 1.2;
        }

        .fte-table-grid tbody td {
            border-top: 1px solid #e2e8f0;
            padding: 0.4rem;
            vertical-align: middle;
        }

        .fte-table-grid tbody tr {
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .fte-table-grid tbody tr.fte-table-row-remote-new {
            background: #ecfdf5;
            box-shadow: inset 4px 0 0 #22c55e;
        }

        .fte-table-grid tbody tr.fte-table-row-remote-new td {
            background: #ecfdf5;
        }

        .fte-table-grid tbody tr.fte-table-row-remote-updated {
            background: #fef9c3;
            box-shadow: inset 4px 0 0 #eab308;
        }

        .fte-table-grid tbody tr.fte-table-row-remote-updated td {
            background: #fefce8;
        }

        .fte-table-grid tbody tr.fte-table-row-deleted {
            background: #fff1f2;
            box-shadow: inset 4px 0 0 #f43f5e;
        }

        .fte-table-grid tbody tr.fte-table-row-deleted td {
            position: relative;
        }

        .fte-table-grid input,
        .fte-table-grid select {
            width: 100%;
            min-width: 0;
            border: 1px solid #cbd5e1;
            border-radius: 0.4rem;
            padding: 0.38rem 0.5rem;
            font-size: 0.9rem;
            background: #fff;
            box-sizing: border-box;
        }

        .fte-table-grid tbody tr.fte-table-row-remote-new input,
        .fte-table-grid tbody tr.fte-table-row-remote-new select {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .fte-table-grid tbody tr.fte-table-row-remote-updated input,
        .fte-table-grid tbody tr.fte-table-row-remote-updated select {
            background: #fefce8;
            border-color: #fde047;
        }

        .fte-table-grid .fte-table-index {
            text-align: center;
            color: #1d4ed8;
            font-weight: 700;
            width: 48px;
        }

        .fte-table-grid .fte-table-remove-btn {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fff5f5;
            border-radius: 0.4rem;
            padding: 0.35rem 0.5rem;
            font-size: 0.75rem;
            width: 100%;
            white-space: nowrap;
        }

        .fte-table-grid tbody tr.fte-table-row-deleted input,
        .fte-table-grid tbody tr.fte-table-row-deleted select {
            color: #9f1239;
            background: #fff1f2;
            border-color: #fda4af;
            text-decoration: line-through;
            text-decoration-color: rgba(190, 24, 93, 0.55);
            text-decoration-thickness: 2px;
        }

        .fte-table-grid td:nth-child(9) input {
            font-size: 0.84rem;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.01em;
        }

        .fte-table-grid td:nth-child(10) input {
            font-size: 0.82rem;
            padding-left: 0.35rem;
            padding-right: 0.35rem;
            font-variant-numeric: tabular-nums;
        }

        .fte-table-grid td:nth-child(3) select,
        .fte-table-grid td:nth-child(4) select,
        .fte-table-grid td:nth-child(5) select {
            font-size: 0.84rem;
        }

        .fte-table-grid td:nth-child(6) input,
        .fte-table-grid td:nth-child(7) input {
            font-size: 0.86rem;
            padding-left: 0.35rem;
            padding-right: 0.35rem;
            font-variant-numeric: tabular-nums;
        }

        .fte-table-empty {
            text-align: center;
            color: #64748b;
            padding: 1rem;
            font-size: 0.85rem;
        }

        .fte-table-grid input[readonly] {
            background: #f8fafc;
            color: #475569;
        }

        .project-form {
            max-width: 96rem !important;
        }

        .project-form,
        .project-form input,
        .project-form select,
        .project-form textarea,
        .project-form button {
            font-size: 1rem;
        }

        .project-form h2 {
            font-size: 1.75rem;
        }

        .project-form h3 {
            font-size: 1.35rem;
        }
    }
</style>

<script>
    (function() {
        const COLLAPSIBLE_SELECTOR =
            '[data-repeatable-row], .irb-row, .copi-row, .coresearch-row, .fte-row, .output-row';
        const isProjectCreatePage = () => /^\/rm\/create\/?$/.test(window.location?.pathname || '');
        const isMobileViewport = () => window.matchMedia('(max-width: 640px)').matches;
        const disableRowCollapse = () => window.disableRowCollapse === true;
        const disableByRoute = () => {
            const path = window.location?.pathname || '';
            return /\/fte\/.+\/detail$/.test(path) || /\/fte\/project\/.+/.test(path);
        };
        const isCollapseDisabled = () =>
            disableRowCollapse() ||
            disableByRoute() ||
            document.body.classList.contains('no-row-collapse-toggle');
        const isRowCollapseLocked = () => false;

        function clearRowCollapseUi(row) {
            if (!row) return;
            row.querySelectorAll('.row-collapse-toggle, .row-collapse-summary').forEach((el) => el.remove());
            row.classList.remove('row-collapsible', 'row-collapsed');
            row.dataset.collapsed = '0';
        }

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
            if (isRowCollapseLocked(row)) {
                clearRowCollapseUi(row);
                updateSummary(row);
                return;
            }
            if (collapsed && row?.dataset?.forceOpen === '1') {
                collapsed = false;
            }
            updateSummary(row);
            row.dataset.collapsed = collapsed ? '1' : '0';
            row.classList.toggle('row-collapsed', collapsed);
            updateToggleLabel(row);
        }

        function ensureToggle(row) {
            if (isCollapseDisabled()) return;
            if (isRowCollapseLocked(row)) {
                clearRowCollapseUi(row);
                return;
            }
            if (row.querySelector('.row-collapse-toggle')) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'row-collapse-toggle';
            if (row.querySelector('[data-repeatable-remove]')) {
                btn.classList.add('row-collapse-toggle-with-remove');
            }
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (row.dataset.forceOpen === '1') {
                    setCollapsed(row, false);
                    return;
                }
                setCollapsed(row, row.dataset.collapsed !== '1');
            });
            row.classList.add('row-collapsible');
            row.appendChild(btn);
            ensureSummary(row);
            updateToggleLabel(row);
        }

        function ensureSummary(row) {
            if (isCollapseDisabled()) return;
            if (document.body.classList.contains('no-row-collapse-summary')) return;
            if (isMobileViewport() && !window.enableRowCollapseSummaryOnMobile && !row.classList.contains("output-row")) return;
            if (row.querySelector('.row-collapse-summary')) return;
            const summary = document.createElement('div');
            summary.className = 'row-collapse-summary text-sm text-slate-700 mt-2 hidden';
            const text = document.createElement('div');
            text.className = 'row-collapse-summary-text';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'row-collapse-remove';
            removeBtn.textContent = 'ลบ';
            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                requestRepeatableRowRemoval(row);
            });
            summary.appendChild(text);
            summary.appendChild(removeBtn);
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
                    const selectedValue = (el.value ?? '').toString().trim();
                    if (!selectedValue) continue;
                    const text = el.options[el.selectedIndex]?.textContent?.trim();
                    if (text) return text;
                }
                const value = el.value?.trim();
                if (value) return maybeFormatDateForSummary(el, value);
            }
            return '';
        }

        function toBuddhistNumericDate(value) {
            const raw = (value ?? '').toString().trim();
            const dmy = raw.match(/^(\d{1,2})([/-])(\d{1,2})\2(\d{4})$/);
            const ymd = raw.match(/^(\d{4})([/-])(\d{1,2})\2(\d{1,2})$/);
            const thaiText = raw.match(/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/);
            if (!dmy && !ymd && !thaiText) return raw;

            let day;
            let month;
            let year;

            if (dmy) {
                day = parseInt(dmy[1], 10);
                month = parseInt(dmy[3], 10);
                year = parseInt(dmy[4], 10);
            } else if (ymd) {
                year = parseInt(ymd[1], 10);
                month = parseInt(ymd[3], 10);
                day = parseInt(ymd[4], 10);
            } else {
                const monthMap = {
                    'มกราคม': 1,
                    'ม.ค.': 1,
                    'ม.ค': 1,
                    'กุมภาพันธ์': 2,
                    'ก.พ.': 2,
                    'ก.พ': 2,
                    'มีนาคม': 3,
                    'มี.ค.': 3,
                    'มี.ค': 3,
                    'เมษายน': 4,
                    'เม.ย.': 4,
                    'เม.ย': 4,
                    'พฤษภาคม': 5,
                    'พ.ค.': 5,
                    'พ.ค': 5,
                    'มิถุนายน': 6,
                    'มิ.ย.': 6,
                    'มิ.ย': 6,
                    'กรกฎาคม': 7,
                    'ก.ค.': 7,
                    'ก.ค': 7,
                    'สิงหาคม': 8,
                    'ส.ค.': 8,
                    'ส.ค': 8,
                    'กันยายน': 9,
                    'ก.ย.': 9,
                    'ก.ย': 9,
                    'ตุลาคม': 10,
                    'ต.ค.': 10,
                    'ต.ค': 10,
                    'พฤศจิกายน': 11,
                    'พ.ย.': 11,
                    'พ.ย': 11,
                    'ธันวาคม': 12,
                    'ธ.ค.': 12,
                    'ธ.ค': 12,
                };

                day = parseInt(thaiText[1], 10);
                month = monthMap[(thaiText[2] || '').trim()] ?? NaN;
                year = parseInt(thaiText[3], 10);
            }

            if (
                Number.isNaN(day) || Number.isNaN(month) || Number.isNaN(year) ||
                day < 1 || day > 31 || month < 1 || month > 12
            ) {
                return raw;
            }

            if (year < 2400) year += 543;
            const dayText = String(day).padStart(2, '0');
            const monthText = String(month).padStart(2, '0');
            return `${dayText}-${monthText}-${year}`;
        }

        function toBuddhistFromDateLike(value) {
            const raw = (value ?? '').toString().trim();
            if (!raw) return '';
            const parsed = new Date(raw);
            if (Number.isNaN(parsed.getTime())) return '';
            const day = String(parsed.getDate()).padStart(2, '0');
            const month = String(parsed.getMonth() + 1).padStart(2, '0');
            const year = String(parsed.getFullYear() + 543);
            return `${day}-${month}-${year}`;
        }

        function maybeFormatDateForSummary(el, value) {
            const isDateInput = el.classList?.contains('thai-datepicker') || el.classList?.contains('js-date-be');
            if (!isDateInput) return value;
            return toBuddhistNumericDate(value);
        }

        function formatSummaryDateText(value) {
            const raw = (value ?? '').toString().trim();
            if (!raw) return '';
            const normalized = toBuddhistNumericDate(raw);
            if (normalized !== raw) return normalized;
            return toBuddhistFromDateLike(raw) || raw;
        }

        function normalizeSummaryDateForRowInput(inputEl) {
            if (!inputEl) return '';
            const altRaw = inputEl._flatpickr?.altInput?.value?.trim();
            if (altRaw) return altRaw;

            const visibleAltInLabel = inputEl.closest('label')?.querySelector?.(
                'input:not([name]):not([type="hidden"])'
            );
            if (
                visibleAltInLabel &&
                visibleAltInLabel !== inputEl &&
                (visibleAltInLabel.value || '').trim() !== ''
            ) {
                return visibleAltInLabel.value.trim();
            }

            const raw = (inputEl.value ?? '').toString().trim();
            if (!raw) return '';

            const dmy = raw.match(/^(\d{1,2})([/-])(\d{1,2})\2(\d{4})$/);
            if (dmy) {
                const day = String(parseInt(dmy[1], 10)).padStart(2, '0');
                const month = String(parseInt(dmy[3], 10)).padStart(2, '0');
                let year = parseInt(dmy[4], 10);
                if (year < 2400) year += 543;
                return `${day}-${month}-${year}`;
            }

            const ymd = raw.match(/^(\d{4})([/-])(\d{1,2})\2(\d{1,2})$/);
            if (ymd) {
                let year = parseInt(ymd[1], 10);
                const month = String(parseInt(ymd[3], 10)).padStart(2, '0');
                const day = String(parseInt(ymd[4], 10)).padStart(2, '0');
                if (year < 2400) year += 543;
                return `${day}-${month}-${year}`;
            }

            return raw;
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
                const selectedValue = (el.value ?? '').toString().trim();
                if (!selectedValue) return '';
                const text = el.options[el.selectedIndex]?.textContent?.trim();
                if (text) return text;
            }
            return maybeFormatDateForSummary(el, (el.value ?? '').toString().trim());
        }

        function matchKey(name) {
            const m = name.match(/^([a-zA-Z_]+)\[(\d+)\]\[([^\]]+)\]/);
            if (!m) return null;
            return {
                root: m[1],
                index: m[2],
                field: m[3]
            };
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

        function getFieldElementByName(row, matcher) {
            const els = row.querySelectorAll('[name]');
            for (const el of els) {
                if (matcher(el.name, el)) return el;
            }
            return null;
        }

        function getDateSummaryFromElement(el) {
            if (!el) return '';
            const altRaw = el._flatpickr?.altInput?.value?.trim();
            if (altRaw) {
                return altRaw;
            }
            const picked = el._flatpickr?.selectedDates?.[0];
            if (picked instanceof Date && !Number.isNaN(picked.getTime())) {
                const day = String(picked.getDate()).padStart(2, '0');
                const month = String(picked.getMonth() + 1).padStart(2, '0');
                const year = picked.getFullYear() + 543;
                return `${day}-${month}-${year}`;
            }
            // Do not fallback to raw input value here; raw hidden value can be stale and
            // differ from the visible date text shown to users.
            return '';
        }

        function pickBestDateInput(inputs) {
            if (!inputs || inputs.length === 0) return null;

            const scored = inputs.map((el) => {
                let score = 0;
                if ((el.type || '').toLowerCase() !== 'hidden') score += 4;
                if (el.classList?.contains('thai-datepicker') || el.classList?.contains('js-date-be')) score += 3;
                if (el._flatpickr) score += 2;
                if ((el.value || '').trim()) score += 1;
                return {
                    el,
                    score
                };
            });

            scored.sort((a, b) => b.score - a.score);
            return scored[0]?.el || inputs[0];
        }

        function getVisibleDateNearInput(input, row) {
            if (!input) return '';

            const nearby = input.closest('label, .relative, .w-full, .grid, .space-y-2') || input.parentElement || row;
            const visibleCandidates = nearby?.querySelectorAll?.(
                'input:not([name]):not([type="hidden"])'
            );
            if (!visibleCandidates || visibleCandidates.length === 0) return '';

            for (const candidate of visibleCandidates) {
                if (candidate === input) continue;
                const raw = (candidate.value || '').trim();
                if (raw) return raw;
            }
            return '';
        }

        function getProjectCardDateValue(row, root, field) {
            const inputs = Array.from(row.querySelectorAll(`input[name^="${root}["][name$="[${field}]"]`));
            if (!inputs.length) return '';

            const primary = pickBestDateInput(inputs);
            const orderedInputs = [primary, ...inputs.filter((el) => el !== primary)];

            for (const input of orderedInputs) {
                const fromPicker = getDateSummaryFromElement(input);
                if (fromPicker) return formatSummaryDateText(fromPicker);

                const nearVisible = getVisibleDateNearInput(input, row);
                if (nearVisible) return formatSummaryDateText(nearVisible);

                const normalized = normalizeSummaryDateForRowInput(input);
                if (normalized) return formatSummaryDateText(normalized);
            }

            return '';
        }

        function findLabelByText(row, text) {
            const labels = Array.from(row.querySelectorAll('label'));
            return labels.find((label) => (label.textContent || '').includes(text)) || null;
        }

        function getDateTextFromLabel(label, fallbackInput) {
            if (!label) return '';

            const visibleFlatpickr = label.querySelector(
                'input:not([name]):not([type="hidden"])'
            );
            if (visibleFlatpickr && (visibleFlatpickr.value || '').trim()) {
                return formatSummaryDateText(visibleFlatpickr.value.trim());
            }

            if (fallbackInput) {
                const fromPicker = fallbackInput._flatpickr?.altInput?.value?.trim();
                if (fromPicker) return formatSummaryDateText(fromPicker);
                const normalized = normalizeSummaryDateForRowInput(fallbackInput);
                if (normalized) return formatSummaryDateText(normalized);
            }

            const anyVisibleInput = label.querySelector(
                'input:not([name]):not([type="hidden"])'
            );
            if (anyVisibleInput && (anyVisibleInput.value || '').trim()) {
                return formatSummaryDateText(anyVisibleInput.value.trim());
            }

            return '';
        }

        function getVisibleFlatpickrDateInputs(row) {
            if (!row) return [];
            return Array.from(
                row.querySelectorAll('input:not([name]):not([type="hidden"])')
            ).filter((el) => {
                const val = (el.value || '').trim();
                if (!val) return false;
                if (el.offsetParent !== null) return true;
                const style = window.getComputedStyle(el);
                return style.display !== 'none' && style.visibility !== 'hidden';
            });
        }

        function extractDisplayedDateText(inputEl) {
            if (!inputEl) return '';
            const fromPicker = inputEl._flatpickr?.altInput?.value?.trim();
            if (fromPicker) return formatSummaryDateText(fromPicker);

            const nearVisible = inputEl.closest('label, .relative, .w-full, .grid, .space-y-2')?.querySelector?.(
                'input:not([name]):not([type="hidden"])'
            );
            if (nearVisible && (nearVisible.value || '').trim()) {
                return formatSummaryDateText(nearVisible.value.trim());
            }

            const raw = (inputEl.value || '').trim();
            return raw ? formatSummaryDateText(raw) : '';
        }

        function cacheProjectRowDateSummary(row) {
            if (!row) return;

            const fteStartInput = row.querySelector('input[name^="fte_roles["][name$="[start_date]"]');
            const fteEndInput = row.querySelector('input[name^="fte_roles["][name$="[end_date]"]');
            if (fteStartInput || fteEndInput) {
                row.dataset.fteStartDisplay = extractDisplayedDateText(fteStartInput);
                row.dataset.fteEndDisplay = extractDisplayedDateText(fteEndInput);
            }

            const irbExpInput = row.querySelector('input[name^="irbs["][name$="[exp_date]"]');
            if (irbExpInput) {
                row.dataset.irbExpDisplay = extractDisplayedDateText(irbExpInput);
            }
        }

        function getStrictDisplayedDateForNamedInput(row, selector) {
            const input = row.querySelector(selector);
            if (!input) return '';

            const altRaw = input._flatpickr?.altInput?.value?.trim();
            if (altRaw) return formatSummaryDateText(altRaw);

            const scope = input.closest('label, .relative, .w-full, .grid, .space-y-2') || input.parentElement || row;
            const visible = scope.querySelector('input:not([name]):not([type="hidden"])');
            if (visible && (visible.value || '').trim()) {
                return formatSummaryDateText(visible.value.trim());
            }

            return '';
        }

        function getDisplayDateFromInput(inputEl) {
            if (!inputEl) return '';

            const altRaw = inputEl._flatpickr?.altInput?.value?.trim();
            if (altRaw) return altRaw;

            const visibleInLabel = inputEl.closest('label')?.querySelector?.(
                'input:not([name]):not([type="hidden"])'
            );
            if (visibleInLabel && (visibleInLabel.value || '').trim() !== '') {
                return visibleInLabel.value.trim();
            }

            return normalizeSummaryDateForRowInput(inputEl);
        }

        function withHexAlpha(hex, alphaHex) {
            const raw = String(hex || '').trim().replace('#', '');
            if (!/^[0-9a-fA-F]{6}$/.test(raw)) return '#cbd5e1';
            return `#${raw}${alphaHex}`;
        }

        const OUTPUT_TYPE_BASE_COLORS = {
            EA101: '#7aa2f7',
            EA102: '#7dcfb6',
            EA103: '#f7b267',
            EA104: '#f79d9d',
            EA105: '#b39ddb',
            EA106: '#7ec8e3',
            EA107: '#9ad29a',
            EA108: '#9fa8da',
            EA109: '#f6b26b',
            EA110: '#f4a6c1',
            EA111: '#9bb8ff',
            EA112: '#8fd3c8',
            EA113: '#c5d86d',
            EA114: '#c3a6e8',
            EA115: '#d9b08c',
            EA116: '#8ecae6',
            EA117: '#9ccc9c',
            EA118: '#a5b4fc',
            EA119: '#f4b183',
            EA120: '#e8a5a5',
            EA999: '#82c7b2',
            OTHER: '#aab2bd',
        };

        function getOutputTypeBadgeStyle(typeCode) {
            const code = String(typeCode || '').toUpperCase();
            const base = OUTPUT_TYPE_BASE_COLORS[code] || OUTPUT_TYPE_BASE_COLORS.OTHER;
            return `background:${withHexAlpha(base, '1A')};border-color:${withHexAlpha(base, '66')};color:${base};`;
        }

        function updateSummary(row) {
            const summary = row.querySelector('.row-collapse-summary');
            if (!summary) return;
            const summaryText = summary.querySelector('.row-collapse-summary-text');
            if (!summaryText) return;
            summary.classList.remove('row-collapse-summary--output');
            cacheProjectRowDateSummary(row);

            const siblings = getSiblings(row);
            const index = Math.max(1, siblings.indexOf(row) + 1);
            const prefix = `#${index}`;
            const filledOr = (value, fallback) => {
                const text = (value ?? '').toString().trim();
                return text && text !== '-' ? text : fallback;
            };

            if (row.classList.contains('irb-row') || hasName(row, (n) => n.startsWith('irbs['))) {
                let idx = getIndexFromId(row, 'irb');
                if (idx === null) {
                    row.querySelectorAll('[name]').forEach(el => {
                        const meta = matchKey(el.name);
                        if (meta && meta.root === 'irbs') idx = meta.index;
                    });
                }
                const values = idx !== null ? getGroupValues(row, 'irbs', idx) : {};
                const code = filledOr(values.code, 'ยังไม่กรอกรหัส');
                const source = filledOr(values.source, 'ยังไม่กรอกหน่วยงาน');
                const exp = filledOr(
                    getStrictDisplayedDateForNamedInput(row, 'input[name^="irbs["][name$="[exp_date]"]') ||
                    (row.dataset.irbExpDisplay || '').trim(),
                    'ยังไม่เลือกวันหมดอายุ'
                );
                summaryText.innerHTML = `
        <span class="summary-chip summary-primary">IRB ${prefix}</span>
        <span class="summary-chip summary-muted">รหัส ${code}</span>
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
                const name = filledOr(values.name, 'ยังไม่กรอกชื่อ');
                const aff = filledOr(values.affiliation, 'ยังไม่กรอกสังกัด');
                summaryText.innerHTML = `
        <span class="summary-chip summary-primary">COPI ${prefix}</span>
        <span class="summary-chip summary-muted">ชื่อ ${name}</span>
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
                const name = filledOr(values.name, 'ยังไม่กรอกชื่อ');
                const aff = filledOr(values.affiliation, 'ยังไม่กรอกสังกัด');
                const status = filledOr(values.unit_status, 'ยังไม่เลือกสถานะ');
                summaryText.innerHTML = `
        <span class="summary-chip summary-primary">Co-Researcher ${prefix}</span>
        <span class="summary-chip summary-muted">ชื่อ ${name}</span>
        <span class="summary-chip summary-muted">สังกัด ${aff}</span>
        <span class="summary-chip summary-muted">สถานะ ${status}</span>
      `;
                return;
            }

            if (row.classList.contains('fte-row') || hasName(row, (n) => n.startsWith('fte_roles['))) {
                const name = filledOr(
                    getFieldValueByName(row, (n) => n.startsWith('fte_roles[') && n.endsWith('[name]')),
                    'ยังไม่กรอกชื่อ'
                );
                const task = getFieldValueByName(row, (n) =>
                    n.startsWith('fte_roles[') && (n.endsWith('[job_detail]') || n.endsWith('[task_detail]'))
                );
                const fte = getFieldValueByName(row, (n) =>
                    n.startsWith('fte_roles[') && (n.endsWith('[fte_value]') || n.endsWith('[fte]'))
                );
                const startText = filledOr(
                    getStrictDisplayedDateForNamedInput(row, 'input[name^="fte_roles["][name$="[start_date]"]') ||
                    (row.dataset.fteStartDisplay || '').trim(),
                    ''
                );
                const endText = filledOr(
                    getStrictDisplayedDateForNamedInput(row, 'input[name^="fte_roles["][name$="[end_date]"]') ||
                    (row.dataset.fteEndDisplay || '').trim(),
                    ''
                );
                const hasDate = Boolean(startText || endText);
                const dateText = hasDate ? `${startText || '-'} ถึง ${endText || '-'}` : 'ยังไม่เลือกช่วงเวลา';
                const taskText = filledOr(task, 'ยังไม่เลือกภาระงาน');
                const fteText = fte ? `FTE ${fte}` : 'FTE ยังไม่คำนวณ';
                summaryText.innerHTML = `
        <span class="summary-chip summary-primary">ผู้มีส่วนร่วม ${prefix}</span>
        <span class="summary-chip summary-muted">ชื่อ ${name}</span>
        <span class="summary-chip summary-muted">ภาระงาน ${taskText}</span>
        <span class="summary-chip summary-muted">ช่วงเวลา ${dateText}</span>
        <span class="summary-chip summary-muted">${fteText}</span>
      `;
                return;
            }

            if (row.classList.contains('output-row') || hasName(row, (n) => n.startsWith('outputs['))) {
                summary.classList.add('row-collapse-summary--output');
                const title = filledOr(
                    getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[title]')),
                    'ยังไม่กรอกชื่อผลงาน'
                );
                const type = filledOr(
                    getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[type]')),
                    'ยังไม่เลือกประเภท'
                );
                const publish = filledOr(
                    getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[year]')),
                    'ยังไม่เลือกวันที่ publish'
                );
                const projectOutput = filledOr(
                    getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[project_output]')),
                    ''
                );
                const typeCode = (type.match(/EA\d{3}/i)?.[0] || type || 'EA---').toUpperCase();
                const typeBadgeStyle = getOutputTypeBadgeStyle(typeCode);
                const typeBadgeText = type !== 'ยังไม่เลือกประเภท' ? type : typeCode;
                summaryText.innerHTML = `
        <div class="summary-output-card">
          <div class="summary-output-top">
            <span class="summary-chip summary-primary">Output ${prefix}</span>
            <span class="summary-output-type" style="${typeBadgeStyle}">${typeBadgeText}</span>
          </div>
          <div class="summary-output-line summary-output-line--title">
            <span class="summary-output-key">ชื่อ:</span>
            <span class="summary-output-value">${title}</span>
          </div>
          ${projectOutput ? `
          <div class="summary-output-line">
            <span class="summary-output-key">ลิงก์/หมายเหตุ:</span>
            <span class="summary-output-value">${projectOutput}</span>
          </div>` : ''}
          <div class="summary-output-line">
            <span class="summary-output-key">วันที่ publish:</span>
            <span class="summary-output-value">${publish}</span>
          </div>
        </div>
      `;
                return;
            }

            const name = filledOr(
                getFieldValueByName(row, (n) => n.endsWith('[name]') || n.endsWith('[person_name]')),
                'ยังไม่กรอกชื่อ'
            );
            const task = filledOr(
                getFieldValueByName(row, (n) => n.endsWith('[job_detail]') || n.endsWith('[task_detail]')),
                'ยังไม่เลือกภาระงาน'
            );
            const start = getFieldValueByName(row, (n) => n.endsWith('[start_date]') || n.endsWith('[start]'));
            const end = getFieldValueByName(row, (n) => n.endsWith('[end_date]') || n.endsWith('[end]'));
            const startEl = getFieldElementByName(row, (n) => n.endsWith('[start_date]') || n.endsWith('[start]'));
            const endEl = getFieldElementByName(row, (n) => n.endsWith('[end_date]') || n.endsWith('[end]'));
            const fte = getFieldValueByName(row, (n) => n.endsWith('[fte_value]') || n.endsWith('[fte]'));
            const startText = normalizeSummaryDateForRowInput(startEl) || formatSummaryDateText(start) || '-';
            const endText = normalizeSummaryDateForRowInput(endEl) || formatSummaryDateText(end) || '-';
            const dateText = (start || end) ? `${startText} ถึง ${endText}` : 'ยังไม่เลือกช่วงเวลา';
            const fteText = fte ? `FTE ${fte}` : 'FTE ยังไม่คำนวณ';

            summaryText.innerHTML = `
      <span class="summary-chip summary-primary">รายการ ${prefix}</span>
      <span class="summary-chip summary-muted">ชื่อ ${name}</span>
      <span class="summary-chip summary-muted">ภาระงาน ${task}</span>
      <span class="summary-chip summary-muted">ช่วงเวลา ${dateText}</span>
      <span class="summary-chip summary-muted">${fteText}</span>
    `;
        }

        function prepareRow(row) {
            if (!row || row.dataset.collapseReady === '1') return;
            row.dataset.collapseReady = '1';
            if (isCollapseDisabled()) return;
            if (isRowCollapseLocked(row)) {
                clearRowCollapseUi(row);
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
                return;
            }
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
            if (isCollapseDisabled()) return;
            const target = e.target;
            if (
                target?.closest?.('.swal2-container') ||
                target?.closest?.('.swal2-popup') ||
                document.body.classList.contains('swal2-shown') ||
                target?.closest?.('.thai-wheel-overlay') ||
                target?.closest?.('.thai-wheel-sheet') ||
                target?.closest?.('.thai-wheel-select') ||
                target?.closest?.('.thai-wheel-btn') ||
                target?.closest?.('.flatpickr-calendar') ||
                target?.classList?.contains?.('flatpickr-calendar') ||
                target?.classList?.contains?.('flatpickr-day') ||
                target?.classList?.contains?.('flatpickr-monthDropdown-month')
            ) {
                return;
            }
            if (
                target?.closest?.('.swal2-container') ||
                target?.closest?.('.swal2-popup') ||
                document.body.classList.contains('swal2-shown') ||
                target?.closest?.('[data-repeatable-remove]') ||
                target?.closest?.('[data-row-remove]') ||
                target?.closest?.('button[onclick*="removeRow("]') ||
                target?.closest?.('button[onclick*="removeAuthorRow("]') ||
                target?.closest?.('button[onclick*="removeAwardRecipientRow("]') ||
                target?.closest?.('button[onclick*="removeBookAuthorRow("]') ||
                target?.closest?.('button[onclick*="removeConferencePresenterRow("]') ||
                target?.closest?.('.row-collapse-remove') ||
                target?.closest?.('.fte-summary-delete')
            ) {
                return;
            }
            const row = target?.closest?.(COLLAPSIBLE_SELECTOR);
            if (!row) {
                document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(el => setCollapsed(el, true));
                return;
            }
            if (target.closest('.row-collapse-toggle')) return;
            if (row.dataset.collapsed === '1') {
                setCollapsed(row, false);
                const siblings = getSiblings(row);
                siblings.forEach(el => {
                    if (el !== row) setCollapsed(el, true);
                });
            }
        });

        document.addEventListener('focusin', (e) => {
            if (isCollapseDisabled()) return;
            const target = e.target;
            if (
                target?.closest?.('[data-repeatable-remove]') ||
                target?.closest?.('[data-row-remove]') ||
                target?.closest?.('button[onclick*="removeRow("]') ||
                target?.closest?.('button[onclick*="removeAuthorRow("]') ||
                target?.closest?.('button[onclick*="removeAwardRecipientRow("]') ||
                target?.closest?.('button[onclick*="removeBookAuthorRow("]') ||
                target?.closest?.('button[onclick*="removeConferencePresenterRow("]') ||
                target?.closest?.('.row-collapse-remove') ||
                target?.closest?.('.fte-summary-delete')
            ) {
                return;
            }
            const row = e.target?.closest?.(COLLAPSIBLE_SELECTOR);
            if (!row) return;
            if (row.dataset.collapsed === '1') setCollapsed(row, false);
            const siblings = getSiblings(row);
            siblings.forEach(el => {
                if (el !== row) setCollapsed(el, true);
            });
        });

        function collapseSiblings(row) {
            if (isCollapseDisabled()) return;
            const siblings = getSiblings(row);
            siblings.forEach(el => setCollapsed(el, el !== row));
        }

        function updateIndex(container) {
            container.querySelectorAll('[data-repeatable-index]')
                .forEach((el, i) => el.textContent = i + 1);
            container.querySelectorAll(COLLAPSIBLE_SELECTOR)
                .forEach(row => updateToggleLabel(row));
            container.querySelectorAll('.output-row')
                .forEach((row, i) => row.setAttribute('data-index', i));
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

        function getRowRemovalLabel(row) {
            if (!row) return 'รายการนี้';

            const siblings = getSiblings(row);
            const index = Math.max(1, siblings.indexOf(row) + 1);
            const prefix = `#${index}`;
            const filledOr = (value, fallback = '') => {
                const text = (value ?? '').toString().trim();
                return text && text !== '-' ? text : fallback;
            };

            if (row.classList.contains('irb-row') || hasName(row, (n) => n.startsWith('irbs['))) {
                let idx = getIndexFromId(row, 'irb');
                if (idx === null) {
                    row.querySelectorAll('[name]').forEach((el) => {
                        const meta = matchKey(el.name);
                        if (meta && meta.root === 'irbs') idx = meta.index;
                    });
                }
                const values = idx !== null ? getGroupValues(row, 'irbs', idx) : {};
                const code = filledOr(values.code, 'ยังไม่กรอกรหัส');
                return `IRB ${prefix} รหัส ${code}`;
            }

            if (row.classList.contains('copi-row') || hasName(row, (n) => n.startsWith('copis['))) {
                let idx = getIndexFromId(row, 'copi');
                if (idx === null) {
                    row.querySelectorAll('[name]').forEach((el) => {
                        const meta = matchKey(el.name);
                        if (meta && meta.root === 'copis') idx = meta.index;
                    });
                }
                const values = idx !== null ? getGroupValues(row, 'copis', idx) : {};
                const name = filledOr(values.name, 'ยังไม่กรอกชื่อ');
                return `COPI ${prefix} ชื่อ ${name}`;
            }

            if (row.classList.contains('coresearch-row') || hasName(row, (n) => n.startsWith('coresearch['))) {
                let idx = getIndexFromId(row, 'coresearch');
                if (idx === null) {
                    row.querySelectorAll('[name]').forEach((el) => {
                        const meta = matchKey(el.name);
                        if (meta && meta.root === 'coresearch') idx = meta.index;
                    });
                }
                const values = idx !== null ? getGroupValues(row, 'coresearch', idx) : {};
                const name = filledOr(values.name, 'ยังไม่กรอกชื่อ');
                return `Co-Researcher ${prefix} ชื่อ ${name}`;
            }

            if (row.classList.contains('fte-row') || hasName(row, (n) => n.startsWith('fte_roles['))) {
                const name = filledOr(
                    getFieldValueByName(row, (n) => n.startsWith('fte_roles[') && n.endsWith('[name]')),
                    'ยังไม่กรอกชื่อ'
                );
                return `ผู้มีส่วนร่วม ${prefix} ชื่อ ${name}`;
            }

            if (row.classList.contains('output-row') || hasName(row, (n) => n.startsWith('outputs['))) {
                const title = filledOr(
                    getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[title]')),
                    'ยังไม่กรอกชื่อผลงาน'
                );
                return `Output ${prefix} ชื่อ ${title}`;
            }

            const name = filledOr(
                getFieldValueByName(row, (n) => n.endsWith('[name]') || n.endsWith('[person_name]')),
                ''
            );
            if (name) return `รายการ ${prefix} ชื่อ ${name}`;
            return `รายการ ${prefix}`;
        }

        function confirmRemove(callback, options = {}) {
            const detail = (options.detail || '').toString().trim();
            const text = detail ? `ต้องการลบ ${detail} หรือไม่?` : 'ต้องการลบรายการนี้ใช่หรือไม่?';
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยืนยันการลบ',
                    text,
                    showCancelButton: true,
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc2626'
                }).then(r => r.isConfirmed && callback());
            } else {
                if (confirm(text)) callback();
            }
        }

        window.confirmProjectRowRemoval = confirmRemove;

        function getRowRemovalAnchor(row) {
            if (!row) return null;

            const nextSibling = row.nextElementSibling;
            if (nextSibling instanceof HTMLElement) return nextSibling;

            const prevSibling = row.previousElementSibling;
            if (prevSibling instanceof HTMLElement) return prevSibling;

            const repeatable = row.closest?.('[data-repeatable]');
            if (repeatable instanceof HTMLElement) {
                const addBtn = repeatable.querySelector(
                    `[data-repeatable-add="${repeatable.dataset.id || ''}"]`
                );
                if (addBtn instanceof HTMLElement) return addBtn;
            }

            const parent = row.parentElement;
            if (!parent) return null;

            const nextAfterParent = parent.nextElementSibling;
            if (nextAfterParent instanceof HTMLElement) return nextAfterParent;

            return parent instanceof HTMLElement ? parent : null;
        }

        function findPreviousSectionHeading(startNode) {
            let node = startNode;

            while (node && node !== document.body) {
                let sibling = node.previousElementSibling;

                while (sibling) {
                    if (sibling instanceof HTMLElement) {
                        if (sibling.matches('h2, h3, h4')) return sibling;

                        const nestedHeading = sibling.querySelector('h2, h3, h4');
                        if (nestedHeading instanceof HTMLElement) return nestedHeading;
                    }

                    sibling = sibling.previousElementSibling;
                }

                node = node.parentElement;
            }

            return null;
        }

        function getRowSectionHeading(row) {
            if (!row || row.matches?.('.author-row, .conference-row')) return null;

            const sectionRoot = row.closest?.(
                '[data-repeatable], #irb-code-container, #copi-container, #coresearch-container, #fte-container, #output-container'
            );

            return findPreviousSectionHeading(sectionRoot || row);
        }

        function getMobileScrollOffset() {
            const mainNav = document.getElementById('main-nav');
            const navHeight = mainNav instanceof HTMLElement ? mainNav.getBoundingClientRect().height : 0;
            return Math.max(navHeight + 28, Math.round(window.innerHeight * 0.25));
        }

        function removeRowWithMobileScroll(rowOrId, commitRemoval) {
            const row = typeof rowOrId === 'string' ? document.getElementById(rowOrId) : rowOrId;
            if (!row || typeof commitRemoval !== 'function') return;

            if (!isMobileViewport()) {
                commitRemoval();
                return;
            }

            const rowRect = row.getBoundingClientRect();
            const targetTop = Math.max(16, Math.min(rowRect.top, window.innerHeight - 120));
            const anchor = getRowRemovalAnchor(row);
            const sectionHeading = getRowSectionHeading(row);

            commitRemoval();

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    if (sectionHeading instanceof HTMLElement && sectionHeading.isConnected) {
                        const offset = getMobileScrollOffset();
                        const nextTop = Math.max(
                            0,
                            window.scrollY + sectionHeading.getBoundingClientRect().top - offset
                        );

                        window.scrollTo({
                            top: nextTop,
                            left: 0,
                            behavior: 'auto'
                        });
                        return;
                    }

                    const liveAnchor = anchor instanceof HTMLElement && anchor.isConnected ? anchor : null;
                    if (!liveAnchor) return;

                    const delta = liveAnchor.getBoundingClientRect().top - targetTop;
                    if (Math.abs(delta) < 2) return;

                    window.scrollBy({
                        top: delta,
                        left: 0,
                        behavior: 'auto'
                    });
                });
            });
        }

        window.removeProjectRowWithScroll = removeRowWithMobileScroll;

        function requestRepeatableRowRemoval(row) {
            if (!row) return;
            const group = row.closest('[data-repeatable]');
            const container = group ? document.getElementById(group.dataset.container) : row.parentElement;
            const detail = getRowRemovalLabel(row);
            const groupId = group?.dataset?.id || '';

            confirmRemove(() => {
                removeRowWithMobileScroll(row, () => {
                    row.remove();
                    updateIndex(container);
                    if (groupId === 'output' && typeof window.reindexOutput === 'function') {
                        window.reindexOutput();
                    }
                });
            }, {
                detail
            });
        }

        function bindRemove(row) {
            row.querySelectorAll('[data-repeatable-remove]').forEach(btn => {
                const currentText = (btn.textContent || '').trim();
                if (currentText === '✕' || currentText.toLowerCase() === 'x') {
                    btn.textContent = 'ลบ';
                    btn.classList.add('repeatable-remove-text');
                }
                row.classList.add('has-inline-remove');

                btn.onclick = (event) => {
                    event?.preventDefault?.();
                    event?.stopPropagation?.();
                    requestRepeatableRowRemoval(row);
                };
            });
        }

        function addRow(group) {
            const container = document.getElementById(group.dataset.container);
            const tpl = document.getElementById(group.dataset.template);
            let index = parseInt(group.dataset.index || '1', 10);
            const groupId = group.dataset.id || '';

            if (!container || !tpl) return;

            const fragment = tpl.content.cloneNode(true);
            const row = fragment.querySelector('[data-repeatable-row]');
            if (!row) return;

            replaceIndex(row, index);
            if (groupId === 'output') {
                row.setAttribute('data-index', index);
            }
            bindRemove(row);
            prepareRow(row);

            container.appendChild(row);
            updateIndex(container);
            highlightRow(row);
            if (groupId === 'output' && typeof window.reindexOutput === 'function') {
                window.reindexOutput();
            }
            if (groupId === 'fte' && typeof window.handleFteRowAdded === 'function') {
                window.handleFteRowAdded(row);
            } else {
                setCollapsed(row, false);
            }

            group.dataset.index = index + 1;

            if (window.initDateBE) initDateBE(row);
        }

        document.addEventListener('click', e => {
            const btn = e.target.closest('[data-repeatable-add]');
            if (!btn) return;

            const id = btn.getAttribute('data-repeatable-add');
            const group = document.querySelector(`[data-repeatable][data-id="${id}"]`);
            if (!group) return;

            if (
                id === 'fte' &&
                e.isTrusted &&
                typeof window.validateFteBeforeAdd === 'function' &&
                !window.validateFteBeforeAdd({
                    showAlert: true
                })
            ) {
                return;
            }

            addRow(group);
            if (id === 'fte') {
                window.refreshFteUx?.();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (isCollapseDisabled()) {
                document.querySelectorAll('.row-collapse-toggle, .row-collapse-summary').forEach(el => el
                    .remove());
                document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(row => {
                    row.classList.remove('row-collapsed');
                    row.dataset.collapsed = '0';
                });
                return;
            }
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

            // /rm/create: ไม่ให้มีแถวตั้งต้นติดมา ต้องกด + เพิ่ม ก่อนเสมอ
            if (isProjectCreatePage()) {
                document.querySelectorAll('[data-repeatable]').forEach(group => {
                    const container = document.getElementById(group.dataset.container);
                    if (!container) return;
                    container.innerHTML = '';
                    group.dataset.index = '0';
                });
            }

            // collapse pre-filled rows on edit screens only
            const isEdit = document.querySelector(
                'input[name="_method"][value="PUT"], input[name="_method"][value="PATCH"]');
            if (isEdit) {
                document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(row => {
                    if (isRowCollapseLocked(row)) {
                        clearRowCollapseUi(row);
                        return;
                    }
                    if (rowHasData(row)) setCollapsed(row, true);
                });
            }

            window.initFteUx?.();
        });

        let fteUxRefreshQueued = false;
        let suppressNextFteUxRefresh = false;
        const shouldRefreshFteUx = () => {
            if (window.isFteTableMode?.()) return true;
            return Boolean((document.getElementById('fte-search-input')?.value || '').trim());
        };
        const scheduleFteUxRefresh = () => {
            if (!shouldRefreshFteUx()) return;
            if (suppressNextFteUxRefresh) {
                suppressNextFteUxRefresh = false;
                return;
            }
            if (fteUxRefreshQueued) return;
            fteUxRefreshQueued = true;
            requestAnimationFrame(() => {
                fteUxRefreshQueued = false;
                window.refreshFteUx?.();
            });
        };

        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (!(node instanceof HTMLElement)) return;
                    if (node.matches && node.matches(COLLAPSIBLE_SELECTOR)) {
                        prepareRow(node);
                        collapseSiblings(node);
                        if (node.matches('.fte-row') || node.id === 'fte-container') {
                            scheduleFteUxRefresh();
                        }
                        return;
                    }
                    node.querySelectorAll?.(COLLAPSIBLE_SELECTOR).forEach(row => {
                        prepareRow(row);
                        collapseSiblings(row);
                        if (row.matches('.fte-row')) {
                            scheduleFteUxRefresh();
                        }
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });

    })();
</script>

<script>
    (() => {
        const FTE_CONTAINER_ID = 'fte-container';
        const FTE_TOOLS_ID = 'fte-tools';
        const FTE_SEARCH_ID = 'fte-search-input';
        const FTE_TABLE_MODE_BTN_ID = 'fte-table-mode-btn';
        const FTE_CLEAR_SEARCH_BTN_ID = 'fte-search-clear-btn';
        const FTE_TABLE_LOG_ID = 'fte-table-log';
        const FTE_TABLE_LOG_TEXT_ID = 'fte-table-log-text';
        const FTE_TABLE_WRAP_ID = 'fte-table-wrap';
        const FTE_TABLE_MODE_STORAGE_KEY = `fte:table-mode:${window.AUTH_USER_ID || 'guest'}`;
        const isTableModeDisabledOnPage = window.DISABLE_FTE_TABLE_MODE === true;
        const isDesktopLike = () => window.matchMedia('(min-width: 768px)').matches;

        const getContainer = () => document.getElementById(FTE_CONTAINER_ID);
        const getRows = () => Array.from(document.querySelectorAll(`#${FTE_CONTAINER_ID} .fte-row`))
            .filter((row) => !row.closest('template'));
        const FTE_PROXY_LINK_ATTR = 'data-fte-proxy-id';
        let fteProxyIdSeq = 0;
        let fteSubmitAttempted = false;

        const getRequiredFields = (row) => Array.from(row.querySelectorAll('input[required], select[required], textarea[required]'))
            .filter((field) => !field.disabled && field.type !== 'hidden');

        const getFieldLabel = (field) => {
            const text = field.closest('label')?.querySelector('span')?.textContent?.trim();
            return text || 'ช่องข้อมูลที่จำเป็น';
        };

        const highlightInvalidField = (field) => {
            field.classList.add('border-red-500', 'bg-red-50');
            setTimeout(() => field.classList.remove('bg-red-50'), 1200);
        };

        const focusField = (field) => {
            const row = field.closest('.fte-row');
            if (row) {
                row.classList.remove('row-collapsed');
                row.dataset.collapsed = '0';
                row.dataset.forceOpen = '1';
            }
            field.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            setTimeout(() => field.focus({
                preventScroll: true
            }), 200);
        };

        window.validateFteBeforeAdd = ({
            showAlert = true
        } = {}) => {
            const projectForm = document.getElementById('projectForm');
            fteSubmitAttempted = true;
            window.validateProjectFteDateRanges?.(projectForm);

            if (window.isFteTableMode?.()) {
                const result = validateFteTableFields({
                    showAlert,
                    title: 'กรอก FTE ให้ครบก่อนเพิ่มแถวใหม่',
                    markStyles: true
                });
                if (!result.ok) return false;
            }

            const rows = getRows();
            let firstInvalid = null;

            for (const row of rows) {
                for (const field of getRequiredFields(row)) {
                    const val = String(field.value ?? '').trim();
                    const isEmpty = val === '';
                    const invalid = isEmpty || !field.checkValidity();
                    if (!invalid) {
                        field.classList.remove('border-red-500', 'bg-red-50');
                        continue;
                    }
                    firstInvalid = field;
                    break;
                }
                if (firstInvalid) break;
            }

            if (!firstInvalid) return true;

            highlightInvalidField(firstInvalid);
            focusField(firstInvalid);
            firstInvalid.reportValidity?.();

            if (!showAlert) return false;

            const label = getFieldLabel(firstInvalid);
            const fieldMessage = String(firstInvalid.validationMessage || '').trim();
            const alertText = fieldMessage || `กรุณากรอกข้อมูลที่ยังไม่ครบ: ${label}`;
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรอก FTE ให้ครบก่อนเพิ่มแถวใหม่',
                    text: alertText,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#2563eb',
                    didClose: () => focusField(firstInvalid)
                });
            } else {
                alert(alertText);
            }

            return false;
        };

        const getFteTableRequiredFields = () => Array.from(document.querySelectorAll(
            `#${FTE_TABLE_WRAP_ID} [data-fte-required="1"]`
        ));

        const clearProxyInvalidStyles = (el) => {
            if (!el) return;
            el.classList.remove('border-red-500', 'bg-red-50');
            el.style.borderColor = '';
            el.style.backgroundColor = '';
            if (el._sourceField) {
                el._sourceField.classList.remove('border-red-500', 'bg-red-50');
            }
        };

        const markProxyInvalidStyles = (el) => {
            if (!el) return;
            el.classList.add('border-red-500', 'bg-red-50');
            el.style.borderColor = '#ef4444';
            el.style.backgroundColor = '#fef2f2';
            if (el._sourceField) {
                el._sourceField.classList.add('border-red-500', 'bg-red-50');
            }
        };

        const syncProxyInvalidFromSource = (source, proxy) => {
            if (!source || !proxy) return;
            if (typeof proxy.setCustomValidity === 'function') {
                proxy.setCustomValidity(source.validity?.customError ? String(source.validationMessage || '') : '');
            }
            const sourceIsValid = source.validity ? source.validity.valid : true;
            if (sourceIsValid) {
                source.classList.remove('border-red-500', 'bg-red-50');
            }
            const invalid = source.classList.contains('border-red-500') || source.classList.contains('bg-red-50');
            if (invalid) {
                proxy.classList.add('border-red-500', 'bg-red-50');
                proxy.style.borderColor = '#ef4444';
                proxy.style.backgroundColor = '#fef2f2';
            } else {
                proxy.classList.remove('border-red-500', 'bg-red-50');
                proxy.style.borderColor = '';
                proxy.style.backgroundColor = '';
            }
        };

        const syncAllFteProxyInvalidStates = () => {
            document.querySelectorAll(`#${FTE_TABLE_WRAP_ID} [${FTE_PROXY_LINK_ATTR}]`).forEach((proxy) => {
                const proxyId = proxy.getAttribute(FTE_PROXY_LINK_ATTR);
                if (!proxyId) return;
                const source = document.querySelector(`#${FTE_CONTAINER_ID} [${FTE_PROXY_LINK_ATTR}="${proxyId}"]`);
                syncProxyInvalidFromSource(source, proxy);
            });
        };
        window.syncAllFteProxyInvalidStates = syncAllFteProxyInvalidStates;

        const bindTableProxyEndDateMin = (startSource, endSource, endProxy) => {
            if (
                !(startSource instanceof HTMLInputElement) ||
                !(endSource instanceof HTMLInputElement) ||
                !(endProxy instanceof HTMLInputElement)
            ) {
                return;
            }

            const updateMinDate = () => {
                const minDate = parseProjectComparableDateInput(startSource);
                const endDate = parseProjectComparableDateInput(endSource);

                if (endProxy._flatpickr) {
                    endProxy._flatpickr.set('minDate', minDate || null);
                    endProxy._flatpickr.set('disable', minDate ? [date => normalizeDateOnly(date) < minDate] : []);
                    requestAnimationFrame(() => syncProxyFlatpickrBeUi(endProxy._flatpickr));
                }

                if (minDate && endDate && endDate < minDate) {
                    if (endProxy._flatpickr) {
                        endProxy._flatpickr.clear();
                    } else {
                        endProxy.value = '';
                    }

                    setFieldValue(endSource, '', {
                        isDate: true
                    });
                }

                applyFteDateRangeValidity(startSource, endSource);
                syncProxyInvalidFromSource(endSource, endProxy);
            };

            startSource.addEventListener('input', updateMinDate);
            startSource.addEventListener('change', updateMinDate);

            if (endProxy._flatpickr) {
                appendFlatpickrHook(endProxy._flatpickr, 'onOpen', updateMinDate);
            }

            updateMinDate();
            requestAnimationFrame(updateMinDate);
        };

        const getProxyFieldPositionText = (field) => {
            const td = field.closest('td');
            const tr = field.closest('tr');
            const rowNo = tr?.querySelector('.fte-table-index')?.textContent?.trim() || '?';
            const colIndex = td?.cellIndex ?? -1;
            const th = colIndex >= 0 ?
                document.querySelector(`#${FTE_TABLE_WRAP_ID} thead th:nth-child(${colIndex + 1})`) :
                null;
            const colName = th?.textContent?.trim() || 'ช่องข้อมูล';
            return `แถว ${rowNo} : ${colName}`;
        };

        const validateFteTableFields = ({
            showAlert = true,
            title = 'กรุณากรอกข้อมูลให้ครบถ้วน',
            markStyles = true
        } = {}) => {
            syncAllFteProxyInvalidStates();
            const requiredFields = getFteTableRequiredFields();
            let firstInvalid = null;

            requiredFields.forEach((field) => {
                const empty = String(field.value ?? '').trim() === '';
                const invalid = empty || !field.checkValidity();
                if (invalid) {
                    if (markStyles) markProxyInvalidStyles(field);
                    if (!firstInvalid) firstInvalid = field;
                } else {
                    if (markStyles) clearProxyInvalidStyles(field);
                }
            });

            if (!firstInvalid) return {
                ok: true
            };

            firstInvalid.reportValidity?.();
            firstInvalid.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            setTimeout(() => firstInvalid.focus({
                preventScroll: true
            }), 200);

            if (showAlert) {
                const pos = getProxyFieldPositionText(firstInvalid);
                const fieldMessage = String(firstInvalid.validationMessage || '').trim();
                const alertText = fieldMessage ? `${fieldMessage} (${pos})` : `กรุณากรอก: ${pos}`;
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title,
                        text: alertText,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    alert(alertText);
                }
            }

            return {
                ok: false,
                firstInvalid
            };
        };
        window.validateFteTableFields = validateFteTableFields;

        const rowSearchText = (row) => {
            const pick = (selector) => {
                const el = row.querySelector(selector);
                if (!el) return '';
                if (el.tagName === 'SELECT') {
                    return (el.options[el.selectedIndex]?.textContent || '').trim();
                }
                return String(el.value || '').trim();
            };

            return [
                pick('input[name^="fte_roles["][name$="[name]"]'),
                pick('select[name^="fte_roles["][name$="[role_group]"]'),
                pick('select[name^="fte_roles["][name$="[job_detail]"]'),
                pick('select[name^="fte_roles["][name$="[task_detail]"]')
            ].join(' ').toLowerCase();
        };

        window.applyFteRowSearchFilter = () => {
            const query = (document.getElementById(FTE_SEARCH_ID)?.value || '').trim().toLowerCase();
            if (window.isFteTableMode?.()) {
                renderFteTable();
                return;
            }
            getRows().forEach((row) => row.style.display = !query || rowSearchText(row).includes(query) ? '' : 'none');
        };

        const isTableModeEnabled = () => getContainer()?.classList.contains('fte-table-mode');
        window.isFteTableMode = isTableModeEnabled;

        window.collapseFteRowsExcept = (activeRow) => {
            const active = typeof activeRow === 'string' ? document.getElementById(activeRow) : activeRow;
            getRows().forEach((row) => {
                if (!row) return;
                if (active && row === active) {
                    row.classList.remove('row-collapsed');
                    row.dataset.collapsed = '0';
                    row.dataset.forceOpen = '1';
                    return;
                }
                row.dataset.collapsed = '1';
                row.classList.add('row-collapsed');
                delete row.dataset.forceOpen;
            });
        };

        const expandAllFteRows = () => {
            getRows().forEach((row) => {
                row.classList.remove('row-collapsed');
                row.dataset.collapsed = '0';
                delete row.dataset.forceOpen;
            });
        };

        window.handleFteRowAdded = (rowOrId) => {
            const row = typeof rowOrId === 'string' ? document.getElementById(rowOrId) : rowOrId;
            if (!row) return;
            if (isTableModeEnabled()) {
                expandAllFteRows();
                const hasQuery = Boolean((document.getElementById(FTE_SEARCH_ID)?.value || '').trim());
                if (!hasQuery && appendFteTableRow(row)) {
                    suppressNextFteUxRefresh = true;
                    return;
                }
                window.refreshFteUx?.();
            } else {
                window.collapseFteRowsExcept(row);
                const hasQuery = Boolean((document.getElementById(FTE_SEARCH_ID)?.value || '').trim());
                if (hasQuery) {
                    window.applyFteRowSearchFilter?.();
                }
            }
        };

        const getField = (row, selector) => row.querySelector(selector);
        const getSourceProxyId = (row) => {
            const field = row?.querySelector(`[${FTE_PROXY_LINK_ATTR}]`) ||
                row?.querySelector('input[name^="fte_roles["][name$="[name]"]') ||
                row?.querySelector('select[name^="fte_roles["][name$="[role_group]"]');
            return field?.getAttribute(FTE_PROXY_LINK_ATTR) || '';
        };
        const getFieldValue = (el) => {
            if (!el) return '';
            if (el.tagName === 'SELECT') return el.value || '';
            return String(el.value ?? '');
        };

        const parseDateAny = (raw) => {
            const normalizeThaiDigits = (input) => String(input || '').replace(/[๐-๙]/g, (ch) => String('๐๑๒๓๔๕๖๗๘๙'.indexOf(ch)));
            const value = normalizeThaiDigits(String(raw || '').trim()).replace(/\s+/g, ' ');
            if (!value) return null;

            let m = value.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
            if (m) {
                let yyyy = Number(m[3]);
                if (yyyy > 2400) yyyy -= 543;
                return {
                    dd: String(Number(m[1])).padStart(2, '0'),
                    mm: String(Number(m[2])).padStart(2, '0'),
                    yyyy
                };
            }

            m = value.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
            if (m) {
                let yyyy = Number(m[1]);
                if (yyyy > 2400) yyyy -= 543;
                return {
                    dd: String(Number(m[3])).padStart(2, '0'),
                    mm: String(Number(m[2])).padStart(2, '0'),
                    yyyy
                };
            }

            m = value.match(/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/u);
            if (m) {
                const monthRaw = m[2];
                const monthMap = {
                    'มกราคม': 1, 'ม.ค.': 1, 'ม.ค': 1,
                    'กุมภาพันธ์': 2, 'ก.พ.': 2, 'ก.พ': 2,
                    'มีนาคม': 3, 'มี.ค.': 3, 'มี.ค': 3,
                    'เมษายน': 4, 'เม.ย.': 4, 'เม.ย': 4,
                    'พฤษภาคม': 5, 'พ.ค.': 5, 'พ.ค': 5,
                    'มิถุนายน': 6, 'มิ.ย.': 6, 'มิ.ย': 6,
                    'กรกฎาคม': 7, 'ก.ค.': 7, 'ก.ค': 7,
                    'สิงหาคม': 8, 'ส.ค.': 8, 'ส.ค': 8,
                    'กันยายน': 9, 'ก.ย.': 9, 'ก.ย': 9,
                    'ตุลาคม': 10, 'ต.ค.': 10, 'ต.ค': 10,
                    'พฤศจิกายน': 11, 'พ.ย.': 11, 'พ.ย': 11,
                    'ธันวาคม': 12, 'ธ.ค.': 12, 'ธ.ค': 12
                };
                const month = monthMap[monthRaw];
                if (month) {
                    let yyyy = Number(m[3]);
                    if (yyyy > 2400) yyyy -= 543;
                    return {
                        dd: String(Number(m[1])).padStart(2, '0'),
                        mm: String(month).padStart(2, '0'),
                        yyyy
                    };
                }
            }

            return null;
        };

        const toBuddhistDateText = (raw) => {
            const parsed = parseDateAny(raw);
            if (!parsed) return String(raw || '').trim();
            const yyyyBE = parsed.yyyy < 2400 ? parsed.yyyy + 543 : parsed.yyyy;
            return `${parsed.dd}-${parsed.mm}-${yyyyBE}`;
        };

        const toGregorianDateText = (raw) => {
            const parsed = parseDateAny(raw);
            if (!parsed) return String(raw || '').trim();
            let yyyy = parsed.yyyy;
            if (yyyy > 2400) yyyy -= 543;
            return `${parsed.dd}-${parsed.mm}-${yyyy}`;
        };

        const setFieldValue = (el, value, opts = {}) => {
            if (!el) return;
            const nextValue = opts.isDate ? toGregorianDateText(value) : value;
            if (el.tagName === 'SELECT') {
                el.value = nextValue;
            } else {
                el.value = nextValue;
            }
            if (el._flatpickr) {
                try {
                    el._flatpickr.setDate(nextValue, false);
                } catch (_) {}
            }
            el.dispatchEvent(new Event('input', {
                bubbles: true
            }));
            el.dispatchEvent(new Event('change', {
                bubbles: true
            }));
        };

        const syncProxyFlatpickrBeUi = (inst) => {
            if (!inst) return;

            const selectedDate = inst.selectedDates?.[0];
            if (selectedDate && inst.input instanceof HTMLInputElement) {
                const dd = String(selectedDate.getDate()).padStart(2, '0');
                const mm = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const yyyy = String(selectedDate.getFullYear() + 543);
                inst.input.value = `${dd}-${mm}-${yyyy}`;
            } else if (inst.input instanceof HTMLInputElement && inst.input.value) {
                inst.input.value = toBuddhistDateText(inst.input.value);
            }

            const yearInput = inst.calendarContainer?.querySelector('.flatpickr-current-month input.cur-year');
            if (!yearInput) return;

            const year = parseInt(yearInput.value, 10);
            if (!Number.isNaN(year) && year > 0 && year < 2400) {
                yearInput.value = String(year + 543);
            }

            if (yearInput.dataset.beBound === '1') return;
            yearInput.dataset.beBound = '1';
            yearInput.addEventListener('blur', () => {
                const rawYear = parseInt(yearInput.value, 10);
                if (Number.isNaN(rawYear)) return;

                const ceYear = rawYear > 2400 ? rawYear - 543 : rawYear;
                const picked = inst.selectedDates?.[0];
                if (picked) {
                    const nextDate = new Date(picked);
                    nextDate.setFullYear(ceYear);
                    inst.setDate(nextDate, true);
                } else {
                    inst.changeYear(ceYear);
                }

                requestAnimationFrame(() => syncProxyFlatpickrBeUi(inst));
            });
        };

        const createProxyInput = (source, opts = {}) => {
            if (!source) {
                const fallback = document.createElement('input');
                fallback.type = 'text';
                fallback.disabled = true;
                return fallback;
            }

            let proxy;
            const isDateField = opts.isDate === true;

            if (source.tagName === 'SELECT') {
                proxy = source.cloneNode(true);
                proxy.value = source.value;
            } else {
                if (isDateField) {
                    proxy = document.createElement('input');
                    proxy.type = 'text';
                    const fromPicker = source._flatpickr?.altInput?.value || source.value || '';
                    proxy.value = toBuddhistDateText(fromPicker);
                    proxy.placeholder = 'วว-ดด-ปปปป (พ.ศ.)';
                } else {
                    proxy = source.cloneNode(false);
                    proxy.value = getFieldValue(source);
                    if (!proxy.type) proxy.type = opts.type || 'text';
                }
            }

            proxy.className = '';
            proxy.removeAttribute('name');
            proxy.removeAttribute('id');
            proxy.required = false;
            proxy.removeAttribute('required');
            proxy.removeAttribute('aria-invalid');
            if (source.required) {
                proxy.dataset.fteRequired = '1';
                proxy.setAttribute('aria-required', 'true');
            }
            if (source.readOnly || opts.readOnly) proxy.readOnly = true;
            if (opts.readOnly) proxy.setAttribute('readonly', 'readonly');
            proxy._sourceField = source;
            if (!source.getAttribute(FTE_PROXY_LINK_ATTR)) {
                source.setAttribute(FTE_PROXY_LINK_ATTR, `ftep-${++fteProxyIdSeq}`);
            }
            proxy.setAttribute(FTE_PROXY_LINK_ATTR, source.getAttribute(FTE_PROXY_LINK_ATTR));

            const syncFromSource = () => {
                if (isDateField) {
                    const fromPicker = source._flatpickr?.altInput?.value || source.value || '';
                    proxy.value = toBuddhistDateText(fromPicker);
                } else {
                    proxy.value = getFieldValue(source);
                }
            };
            source.addEventListener('input', syncFromSource);
            source.addEventListener('change', syncFromSource);
            source.addEventListener('input', () => syncProxyInvalidFromSource(source, proxy));
            source.addEventListener('change', () => syncProxyInvalidFromSource(source, proxy));

            proxy.addEventListener('input', () => setFieldValue(source, proxy.value, opts));
            proxy.addEventListener('change', () => setFieldValue(source, proxy.value, opts));
            proxy.addEventListener('input', () => clearProxyInvalidStyles(proxy));
            proxy.addEventListener('change', () => clearProxyInvalidStyles(proxy));

            if (isDateField && window.flatpickr) {
                const locale = window.flatpickr?.l10ns?.th ? 'th' : undefined;
                const formatBE = (date) => {
                    const dd = String(date.getDate()).padStart(2, '0');
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const yyyy = date.getFullYear() + 543;
                    return `${dd}-${mm}-${yyyy}`;
                };
                window.flatpickr(proxy, {
                    dateFormat: 'd-m-Y',
                    allowInput: false,
                    locale,
                    disableMobile: true,
                    prevArrow: '',
                    nextArrow: '',
                    defaultDate: toGregorianDateText(proxy.value || ''),
                    formatDate: (date) => formatBE(date),
                    parseDate: (dateStr) => {
                        const g = toGregorianDateText(dateStr);
                        return window.flatpickr.parseDate(g, 'd-m-Y');
                    },
                    onReady: (_sd, _ds, inst) => {
                        syncProxyFlatpickrBeUi(inst);
                        requestAnimationFrame(() => syncProxyFlatpickrBeUi(inst));
                    },
                    onOpen: (_sd, _ds, inst) => {
                        syncProxyFlatpickrBeUi(inst);
                        requestAnimationFrame(() => syncProxyFlatpickrBeUi(inst));
                    },
                    onMonthChange: (_sd, _ds, inst) => {
                        requestAnimationFrame(() => syncProxyFlatpickrBeUi(inst));
                    },
                    onYearChange: (_sd, _ds, inst) => {
                        requestAnimationFrame(() => syncProxyFlatpickrBeUi(inst));
                    },
                    onValueUpdate: (_sd, _ds, inst) => {
                        syncProxyFlatpickrBeUi(inst);
                    },
                    onChange: (_dates, dateStr, inst) => {
                        proxy.value = toBuddhistDateText(dateStr);
                        syncProxyFlatpickrBeUi(inst);
                        setFieldValue(source, proxy.value, opts);
                    },
                });
            }

            syncProxyInvalidFromSource(source, proxy);

            return proxy;
        };

        const ensureTableWrap = () => {
            const container = getContainer();
            if (!container) return null;
            let wrap = document.getElementById(FTE_TABLE_WRAP_ID);
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.id = FTE_TABLE_WRAP_ID;
                wrap.className = 'fte-table-wrap';
                container.parentElement?.insertBefore(wrap, container);
            }
            return wrap;
        };

        const ensureTableLog = () => {
            const wrap = ensureTableWrap();
            if (!wrap) return null;

            let log = document.getElementById(FTE_TABLE_LOG_ID);
            if (!log) {
                log = document.createElement('div');
                log.id = FTE_TABLE_LOG_ID;
                log.className = 'fte-table-log';
                log.innerHTML = `<div id="${FTE_TABLE_LOG_TEXT_ID}" class="fte-table-log__text"></div>`;
                wrap.insertAdjacentElement('afterend', log);
            }

            return log;
        };

        const createFteTableShell = () => {
            const table = document.createElement('table');
            table.className = 'fte-table-grid';
            table.innerHTML = `
                <colgroup>
                    <col style="width:3%">
                    <col style="width:15%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:6%">
                    <col style="width:7%">
                    <col style="width:8%">
                    <col style="width:5%">
                </colgroup>
                <thead>
                    <tr>
                        <th>ลำดับ</th><th>ชื่อ</th><th>กลุ่ม</th><th>ภาระงาน</th><th>รายละเอียดงาน</th><th>เริ่ม</th><th>สิ้นสุด</th>
                        <th>ชั่วโมง</th><th>FTE Auto</th><th>FTE หัวหน้า</th><th>ลบ</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            return table;
        };

        const buildFteTableRow = (row, idx) => {
            const tr = document.createElement('tr');
            const sourceProxyId = getSourceProxyId(row);
            if (sourceProxyId) {
                tr.dataset.sourceProxyId = sourceProxyId;
            }
            const tdIndex = document.createElement('td');
            tdIndex.className = 'fte-table-index';
            tdIndex.textContent = String(idx + 1);
            tr.appendChild(tdIndex);

            const cellDefs = [
                ['input[name^="fte_roles["][name$="[name]"]'],
                ['select[name^="fte_roles["][name$="[role_group]"]'],
                ['select[name^="fte_roles["][name$="[job_detail]"]'],
                ['select[name^="fte_roles["][name$="[task_detail]"]'],
                ['input[name^="fte_roles["][name$="[start_date]"]', {
                    isDate: true
                }],
                ['input[name^="fte_roles["][name$="[end_date]"]', {
                    isDate: true
                }],
                ['input[name^="fte_roles["][name$="[hours]"]', {
                    type: 'number'
                }],
                ['input[name^="fte_roles["][name$="[fte_value]"]', {
                    readOnly: true
                }],
                ['input[name^="fte_roles["][name$="[fte_value_research]"]', {
                    type: 'number'
                }],
            ];

            cellDefs.forEach(([selector, options = {}]) => {
                const td = document.createElement('td');
                const proxy = createProxyInput(getField(row, selector), options);
                td.appendChild(proxy);
                tr.appendChild(td);
            });

            bindTableProxyEndDateMin(
                getField(row, 'input[name^="fte_roles["][name$="[start_date]"]'),
                getField(row, 'input[name^="fte_roles["][name$="[end_date]"]'),
                tr.querySelector('td:nth-child(7) input')
            );

            const removeTd = document.createElement('td');
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'fte-table-remove-btn';
            removeBtn.textContent = 'ลบ';
            removeBtn.addEventListener('click', () => {
                const detail = getFteTableRemovalLabel(row, tr);
                const doRemove = () => {
                    const proxyInRow = tr.querySelector(`[${FTE_PROXY_LINK_ATTR}]`);
                    const proxyId = proxyInRow?.getAttribute(FTE_PROXY_LINK_ATTR) || '';
                    const sourceField = proxyId ?
                        document.querySelector(`#${FTE_CONTAINER_ID} [${FTE_PROXY_LINK_ATTR}="${proxyId}"]`) :
                        null;
                    const sourceRow = sourceField?.closest('.fte-row') || row;
                    if (sourceRow) {
                        if (typeof window.removeFteRow === 'function') {
                            window.removeFteRow(sourceRow.id);
                        } else {
                            const idInput = sourceRow.querySelector('input[name^="fte_roles["][name$="[id]"]');
                            const existingId = Number(idInput?.value || 0);
                            if (existingId > 0) {
                                const deletedContainer = document.getElementById('fte-deleted-ids');
                                if (deletedContainer && !deletedContainer.querySelector(`input[value="${existingId}"]`)) {
                                    deletedContainer.insertAdjacentHTML(
                                        'beforeend',
                                        `<input type="hidden" name="fte_deleted_ids[]" value="${existingId}">`
                                    );
                                }
                            }
                            sourceRow.remove();
                        }
                    }

                    applySourceRowStateToTableTr(sourceRow, tr);
                    syncSingleFteTableRowState(sourceRow);
                };

                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ยืนยันการลบ',
                        text: `ต้องการลบ ${detail} หรือไม่?`,
                        showCancelButton: true,
                        confirmButtonText: 'ลบ',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: '#dc2626'
                    }).then((result) => {
                        if (result.isConfirmed) doRemove();
                    });
                    return;
                }

                if (confirm(`ต้องการลบ ${detail} หรือไม่?`)) {
                    doRemove();
                }
            });
            removeTd.appendChild(removeBtn);
            tr.appendChild(removeTd);
            applySourceRowStateToTableTr(row, tr);
            return tr;
        };

        const applySourceRowStateToTableTr = (sourceRow, tr) => {
            if (!(sourceRow instanceof HTMLElement) || !(tr instanceof HTMLElement)) return;
            const isNew = sourceRow.classList.contains('fte-row-remote-new');
            const isUpdated = sourceRow.classList.contains('fte-row-remote-updated');
            const isDeleted = sourceRow.dataset.deleted === '1' || sourceRow.dataset.remoteDeleted === '1';
            tr.classList.toggle('fte-table-row-remote-new', isNew);
            tr.classList.toggle('fte-table-row-remote-updated', !isNew && isUpdated);
            tr.classList.toggle('fte-table-row-deleted', isDeleted);

            tr.querySelectorAll('input, select, button').forEach((field) => {
                if (!(field instanceof HTMLElement)) return;
                if (field.classList.contains('fte-table-remove-btn')) {
                    field.textContent = isDeleted ? 'ถูกลบ' : 'ลบ';
                    field.toggleAttribute('disabled', isDeleted);
                    return;
                }
                field.toggleAttribute('disabled', isDeleted);
            });
        };

        const getFteTableRemovalLabel = (row, tr) => {
            const safe = (value, fallback) => {
                const text = String(value || '').trim();
                return text || fallback;
            };

            const indexText = safe(
                tr?.querySelector('.fte-table-index')?.textContent,
                '?'
            );

            const sourceName = getField(row, 'input[name^="fte_roles["][name$="[name]"]')?.value;
            const proxyName = tr?.querySelector('td:nth-child(2) input')?.value;
            const name = safe(sourceName || proxyName, 'ยังไม่กรอกชื่อ');

            return `ผู้มีส่วนร่วม #${indexText} ชื่อ ${name}`;
        };

        const syncFteTableIndexes = (tbody) => {
            tbody?.querySelectorAll('tr').forEach((tr, idx) => {
                const indexCell = tr.querySelector('.fte-table-index');
                if (indexCell) indexCell.textContent = String(idx + 1);
            });
        };

        const syncSingleFteTableRowState = (sourceRow) => {
            if (!window.isFteTableMode?.()) return;
            const proxyId = getSourceProxyId(sourceRow);
            if (!proxyId) return;
            const tr = document.querySelector(`#${FTE_TABLE_WRAP_ID} tr[data-source-proxy-id="${proxyId}"]`);
            if (!tr) return;
            applySourceRowStateToTableTr(sourceRow, tr);
        };

        const syncFteTableOrderFromSource = () => {
            if (!window.isFteTableMode?.()) return;
            const tbody = document.querySelector(`#${FTE_TABLE_WRAP_ID} table.fte-table-grid tbody`);
            if (!tbody) return;

            const sourceRows = getRows();
            const currentRows = Array.from(tbody.querySelectorAll('tr[data-source-proxy-id]'));
            const byProxyId = new Map(currentRows.map((tr) => [tr.dataset.sourceProxyId, tr]));
            const activeQueue = sourceRows
                .filter((row) => row.dataset.deleted !== '1' && row.dataset.remoteDeleted !== '1')
                .map((row) => byProxyId.get(getSourceProxyId(row)))
                .filter(Boolean);

            const nextRows = [];
            currentRows.forEach((tr) => {
                const proxyId = tr.dataset.sourceProxyId || '';
                const sourceRow = proxyId ? sourceRows.find((row) => getSourceProxyId(row) === proxyId) : null;
                if (sourceRow && (sourceRow.dataset.deleted === '1' || sourceRow.dataset.remoteDeleted === '1')) {
                    nextRows.push(tr);
                    return;
                }

                const replacement = activeQueue.shift();
                if (replacement) {
                    nextRows.push(replacement);
                }
            });

            activeQueue.forEach((tr) => nextRows.push(tr));
            nextRows.forEach((tr) => tbody.appendChild(tr));

            syncFteTableIndexes(tbody);
        };
        window.syncSingleFteTableRowState = syncSingleFteTableRowState;
        window.syncFteTableOrderFromSource = syncFteTableOrderFromSource;

        const appendFteTableRow = (row) => {
            const wrap = ensureTableWrap();
            if (!wrap || !row) return false;

            let table = wrap.querySelector('table.fte-table-grid');
            if (!table) {
                table = createFteTableShell();
                wrap.innerHTML = '';
                wrap.appendChild(table);
            }

            const tbody = table.querySelector('tbody');
            if (!tbody) return false;

            const nextIndex = tbody.querySelectorAll('tr').length;
            tbody.appendChild(buildFteTableRow(row, nextIndex));
            syncFteTableIndexes(tbody);
            syncAllFteProxyInvalidStates();
            return true;
        };

        const renderFteTable = () => {
            const wrap = ensureTableWrap();
            if (!wrap) return;
            const query = (document.getElementById(FTE_SEARCH_ID)?.value || '').trim().toLowerCase();
            const rows = getRows().filter((row) => !query || rowSearchText(row).includes(query));

            if (!rows.length) {
                wrap.innerHTML = '<div class="fte-table-empty">ไม่พบรายการ FTE</div>';
                return;
            }

            const table = createFteTableShell();
            const tbody = table.querySelector('tbody');
            const fragment = document.createDocumentFragment();

            rows.forEach((row, idx) => {
                fragment.appendChild(buildFteTableRow(row, idx));
            });

            tbody.appendChild(fragment);
            wrap.innerHTML = '';
            wrap.appendChild(table);
            wrap.scrollLeft = 0;
            syncAllFteProxyInvalidStates();
        };

        const forceMarkFteProxyInvalidFromSourceValue = () => {
            if (!fteSubmitAttempted) return;
            document.querySelectorAll(`#${FTE_TABLE_WRAP_ID} [${FTE_PROXY_LINK_ATTR}]`).forEach((proxy) => {
                const source = proxy._sourceField;
                if (!source) return;
                const invalid = !(source.validity?.valid ?? true) || (source.required && String(source.value ?? '').trim() === '');
                if (invalid) {
                    markProxyInvalidStyles(proxy);
                } else {
                    clearProxyInvalidStyles(proxy);
                }
            });
        };

        const applyTableModeState = () => {
            const container = getContainer();
            const wrap = ensureTableWrap();
            if (!container || !wrap) return;
            if (isTableModeDisabledOnPage) {
                container.classList.remove('fte-table-mode');
            }
            const active = !isTableModeDisabledOnPage && isTableModeEnabled() && isDesktopLike();
            container.classList.toggle('fte-cards-hidden', active);
            wrap.classList.toggle('is-active', active);
            if (active) {
                expandAllFteRows();
                renderFteTable();
            }
        };

        const ensureTools = () => {
            const container = getContainer();
            if (!container || document.getElementById(FTE_TOOLS_ID)) return;
            const wrap = ensureTableWrap();

            const tools = document.createElement('div');
            tools.id = FTE_TOOLS_ID;
            tools.className = 'fte-tools';
            tools.innerHTML = `
                <input
                    id="${FTE_SEARCH_ID}"
                    type="search"
                    class="fte-tools__search"
                    placeholder="ค้นหา FTE: ชื่อ / กลุ่ม / ภาระงาน"
                >
                <div class="fte-tools__actions">
                    <button
                        id="${FTE_CLEAR_SEARCH_BTN_ID}"
                        type="button"
                        class="fte-tools__clear-btn"
                    >
                        ล้างคำค้นหา
                    </button>
                    ${isTableModeDisabledOnPage ? '' : `
                    <button
                        id="${FTE_TABLE_MODE_BTN_ID}"
                        type="button"
                        class="fte-tools__table-btn hidden md:inline-flex"
                    >
                        ดูแบบตาราง
                    </button>`}
                </div>
            `;

            if (wrap) {
                container.parentElement?.insertBefore(tools, wrap);
            } else {
                container.parentElement?.insertBefore(tools, container);
            }

            const searchInput = document.getElementById(FTE_SEARCH_ID);
            searchInput?.addEventListener('input', window.applyFteRowSearchFilter);

            const clearBtn = document.getElementById(FTE_CLEAR_SEARCH_BTN_ID);
            clearBtn?.addEventListener('click', () => {
                if (!searchInput) return;
                searchInput.value = '';
                window.applyFteRowSearchFilter();
                searchInput.focus();
            });

            ensureTableLog();

            const tableBtn = document.getElementById(FTE_TABLE_MODE_BTN_ID);
            const setTableBtnState = () => {
                const active = container.classList.contains('fte-table-mode');
                tableBtn?.classList.toggle('is-active', active);
                if (tableBtn) tableBtn.textContent = active ? 'ดูแบบ Card' : 'ดูแบบตาราง';
                applyTableModeState();
            };

            const storedTableMode = localStorage.getItem(FTE_TABLE_MODE_STORAGE_KEY);
            const shouldEnableTableMode = !isTableModeDisabledOnPage && storedTableMode !== '0';
            if (shouldEnableTableMode) {
                container.classList.add('fte-table-mode');
            } else if (isTableModeDisabledOnPage) {
                container.classList.remove('fte-table-mode');
            }
            setTableBtnState();

            tableBtn?.addEventListener('click', () => {
                container.classList.toggle('fte-table-mode');
                const active = container.classList.contains('fte-table-mode');
                localStorage.setItem(FTE_TABLE_MODE_STORAGE_KEY, active ? '1' : '0');
                setTableBtnState();
            });

            window.addEventListener('resize', () => {
                applyTableModeState();
            });
        };

        window.initFteUx = () => {
            ensureTools();
            window.applyFteRowSearchFilter();
        };

        window.refreshFteUx = () => {
            ensureTools();
            applyTableModeState();
            window.applyFteRowSearchFilter();
        };

        const clearFteTableLog = () => {
            const log = ensureTableLog();
            const text = document.getElementById(FTE_TABLE_LOG_TEXT_ID);
            if (text) text.textContent = '';
            log?.classList.remove('is-active');
        };

        const updateFteTableLog = (html) => {
            const log = ensureTableLog();
            const text = document.getElementById(FTE_TABLE_LOG_TEXT_ID);
            if (!log || !text) return;
            const nextHtml = String(html || '').trim();
            text.innerHTML = nextHtml;
            log.classList.toggle('is-active', nextHtml !== '');
        };

        const formatFteLogDateTime = (isoString) => {
            const fallback = new Date();
            const date = isoString ? new Date(isoString) : fallback;
            if (Number.isNaN(date.getTime())) return '';
            const dateText = date.toLocaleDateString('th-TH', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });
            const timeText = date.toLocaleTimeString('th-TH', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });
            return {
                dateText,
                timeText,
            };
        };

        document.addEventListener('fte:remote-change', (event) => {
            const detail = event.detail || {};
            const actorName = String(detail.actorName || 'ผู้ใช้อื่น');
            const occurred = formatFteLogDateTime(detail.occurredAt);
            if (!occurred) {
                return;
            }
            updateFteTableLog(
                `มีการอัปเดทจาก ${actorName} วันที่ ${occurred.dateText} เวลา ${occurred.timeText} โดย ` +
                `<span class="fte-table-log__color-green">สีเขียว</span> = เพิ่มใหม่, ` +
                `<span class="fte-table-log__color-yellow">สีเหลือง</span> = มีการแก้ไข, ` +
                `<span class="fte-table-log__color-red">สีแดง</span> = ถูกลบ`
            );
            if (!window.isFteTableMode?.()) return;
            const rowId = String(detail.rowId || '');
            if (!rowId) return;
            const sourceRow = document.getElementById(rowId);
            if (!sourceRow) return;
            syncSingleFteTableRowState(sourceRow);
            syncFteTableOrderFromSource();
        });

        window.syncFteTableValidationUi = ({
            ensureRender = false
        } = {}) => {
            if (!window.isFteTableMode?.()) return;
            fteSubmitAttempted = true;
            if (ensureRender && !document.querySelector(`#${FTE_TABLE_WRAP_ID} table.fte-table-grid`)) {
                renderFteTable();
            }
            validateFteTableFields({
                showAlert: false,
                markStyles: true
            });
            forceMarkFteProxyInvalidFromSourceValue();
            syncAllFteProxyInvalidStates();
        };

        document.addEventListener('DOMContentLoaded', () => {
            window.initFteUx();
        });

        const onFteFormSubmit = (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.querySelector(`#${FTE_CONTAINER_ID}`)) return;
            fteSubmitAttempted = true;
            if (window.isFteTableMode?.()) {
                window.syncFteTableValidationUi?.({
                    ensureRender: false
                });
            }
            setTimeout(() => {
                window.syncFteTableValidationUi?.({
                    ensureRender: false
                });
            }, 0);
        };
        // capture phase so this still runs even when page-level validator calls stopPropagation()
        document.addEventListener('submit', onFteFormSubmit, true);

        document.addEventListener('invalid', (event) => {
            if (!fteSubmitAttempted) return;
            if (!window.isFteTableMode?.()) return;
            const source = event.target;
            if (!(source instanceof HTMLElement)) return;
            const name = source.getAttribute('name') || '';
            if (!/^fte_roles\[\d+\]\[/.test(name)) return;

            const proxyId = source.getAttribute(FTE_PROXY_LINK_ATTR);
            if (!proxyId) return;
            const proxy = document.querySelector(`#${FTE_TABLE_WRAP_ID} [${FTE_PROXY_LINK_ATTR}="${proxyId}"]`);
            if (!proxy) return;
            markProxyInvalidStyles(proxy);
        }, true);

        // Ensure submit-attempt state is set as soon as browser emits any invalid event in this form.
        document.addEventListener('invalid', (event) => {
            const source = event.target;
            if (!(source instanceof HTMLElement)) return;
            const form = source.closest('form');
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.querySelector(`#${FTE_CONTAINER_ID}`)) return;
            fteSubmitAttempted = true;
        }, true);
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
    document.addEventListener('input', function(e) {
        if (!e.target.classList.contains('fte-hours')) return;

        const row = e.target.closest('.fte-row');
        const fteInput = row?.querySelector('.fte-value');
        if (!fteInput) return;

        const hr = parseFloat(e.target.value) || 0;
        // const fte = hr * 0.00745;
        const fte = hr * 0.000621;

        fteInput.value = fte.toFixed(8);
        fteInput.dispatchEvent(new Event('input', {
            bubbles: true
        }));
        fteInput.dispatchEvent(new Event('change', {
            bubbles: true
        }));
    });
</script>

<script>
    (() => {
        const PROJECT_FORM_ID = 'projectForm';
        const FTE_PAYLOAD_INPUT_ID = 'fteRolesPayload';
        const FTE_FIELD_SELECTOR = 'input[name^="fte_roles["], select[name^="fte_roles["], textarea[name^="fte_roles["]';
        const FTE_NAME_PATTERN = /^fte_roles\[(\d+)\]\[([^\]]+)\]$/;

        const isProjectForm = (form) => form instanceof HTMLFormElement && form.id === PROJECT_FORM_ID;

        const shouldSkipField = (field) => {
            if (!(field instanceof HTMLElement)) return true;
            if (field.closest('template')) return true;
            if (field.disabled) return true;
            return false;
        };

        const readFieldValue = (field) => {
            if (field instanceof HTMLInputElement) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    return field.checked ? field.value : null;
                }
                return field.value;
            }

            if (field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
                return field.value;
            }

            return null;
        };

        const collectFteRows = (form) => {
            const rows = new Map();

            form.querySelectorAll(FTE_FIELD_SELECTOR).forEach((field) => {
                if (shouldSkipField(field)) return;

                const name = field.getAttribute('name') || '';
                const match = name.match(FTE_NAME_PATTERN);
                if (!match) return;

                const [, index, key] = match;
                if (!rows.has(index)) {
                    rows.set(index, {});
                }

                const value = readFieldValue(field);
                if (value === null) return;
                rows.get(index)[key] = value;
            });

            return Array.from(rows.entries())
                .sort((a, b) => Number(a[0]) - Number(b[0]))
                .map(([, row]) => row);
        };

        const disablePackedFields = (form) => {
            form.querySelectorAll(FTE_FIELD_SELECTOR).forEach((field) => {
                if (shouldSkipField(field)) return;
                field.dataset.ftePackedDisabled = '1';
                field.disabled = true;
            });
        };

        const restorePackedFields = (form) => {
            form.querySelectorAll('[data-fte-packed-disabled="1"]').forEach((field) => {
                field.disabled = false;
                delete field.dataset.ftePackedDisabled;
            });
            delete form.dataset.ftePackedSubmitted;
        };

        const updatePackedMeta = (form, packedRows) => {
            const expectedRows = form.querySelector('#fteExpectedRows');
            const expectedVars = form.querySelector('#formExpectedVars');

            if (expectedRows) {
                expectedRows.value = String(packedRows.length);
            }

            if (expectedVars) {
                const namedFields = Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'))
                    .filter((field) => !field.disabled && !field.closest('template'))
                    .length;
                expectedVars.value = String(namedFields);
            }
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!isProjectForm(form)) return;
            if (form.dataset.ftePackedSubmitted === '1') return;

            const payloadInput = form.querySelector(`#${FTE_PAYLOAD_INPUT_ID}`);
            if (!(payloadInput instanceof HTMLInputElement)) return;

            const packedRows = collectFteRows(form);
            if (packedRows.length === 0) {
                payloadInput.value = '';
                return;
            }

            payloadInput.value = JSON.stringify(packedRows);
            disablePackedFields(form);
            updatePackedMeta(form, packedRows);
            form.dataset.ftePackedSubmitted = '1';
        });

        window.addEventListener('pageshow', () => {
            const form = document.getElementById(PROJECT_FORM_ID);
            if (!isProjectForm(form)) return;
            restorePackedFields(form);
        });
    })();
</script>

<script>
    (() => {
        const overlayId = 'save-loading-overlay';
        const submitControlsSelector = 'button[type="submit"], input[type="submit"]';
        const saveKeywords = ['บันทึก', 'save'];

        const normalize = (value) => String(value || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();

        const getControlLabel = (control) => {
            if (!control) return '';
            if (control.tagName === 'INPUT') {
                return normalize(control.value);
            }
            return normalize(control.textContent);
        };

        const shouldHandleForm = (form, submitter) => {
            if (!(form instanceof HTMLFormElement)) return false;

            const controls = Array.from(form.querySelectorAll(submitControlsSelector));
            if (submitter && submitter.matches?.(submitControlsSelector) && !controls.includes(submitter)) {
                controls.push(submitter);
            }

            return controls.some((control) => {
                const label = getControlLabel(control);
                return saveKeywords.some((keyword) => label.includes(keyword));
            });
        };

        const ensureOverlay = () => {
            let overlay = document.getElementById(overlayId);
            if (overlay) return overlay;

            overlay = document.createElement('div');
            overlay.id = overlayId;
            overlay.className = 'save-loading-overlay';
            overlay.setAttribute('aria-live', 'assertive');
            overlay.innerHTML = `
                <div class="save-loading-overlay__card">
                    <div class="save-loading-overlay__row">
                        <span class="save-loading-overlay__spinner" aria-hidden="true"></span>
                        <div>
                            <p class="save-loading-overlay__title">กำลังบันทึกข้อมูล...</p>
                            <p class="save-loading-overlay__subtitle">กรุณารอสักครู่</p>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
            return overlay;
        };

        const showOverlay = () => {
            const overlay = ensureOverlay();
            overlay.classList.add('is-visible');
        };

        const hideOverlay = () => {
            const overlay = document.getElementById(overlayId);
            if (!overlay) return;
            overlay.classList.remove('is-visible');
        };

        const markControlLoading = (control, isPrimary) => {
            if (!(control instanceof HTMLElement)) return;

            control.dataset.saveGuardDisabled = '1';
            control.classList.add('opacity-70', 'cursor-not-allowed');

            if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                control.disabled = true;
            }

            if (!isPrimary) return;

            if (control instanceof HTMLInputElement) {
                if (!control.dataset.originalValue) {
                    control.dataset.originalValue = control.value;
                }
                control.value = 'กำลังบันทึก...';
                return;
            }

            if (!control.dataset.originalHtml) {
                control.dataset.originalHtml = control.innerHTML;
            }
            control.innerHTML =
                '<span class="inline-flex items-center gap-2"><span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/70 border-t-white"></span><span>กำลังบันทึก...</span></span>';
        };

        const restoreForm = (form) => {
            if (!(form instanceof HTMLFormElement)) return;
            form.removeAttribute('data-save-submitting');

            form.querySelectorAll('[data-save-guard-disabled="1"]').forEach((control) => {
                if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                    control.disabled = false;
                }

                if (control instanceof HTMLElement) {
                    control.classList.remove('opacity-70', 'cursor-not-allowed');
                }

                if (control instanceof HTMLInputElement && control.dataset.originalValue) {
                    control.value = control.dataset.originalValue;
                    delete control.dataset.originalValue;
                }

                if (control instanceof HTMLElement && control.dataset.originalHtml) {
                    control.innerHTML = control.dataset.originalHtml;
                    delete control.dataset.originalHtml;
                }

                if (control instanceof HTMLElement) {
                    delete control.dataset.saveGuardDisabled;
                }
            });
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (form.dataset.saveSubmitting === '1') {
                event.preventDefault();
                return;
            }

            if (event.defaultPrevented) return;

            const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
            if (!shouldHandleForm(form, submitter)) return;

            form.dataset.saveSubmitting = '1';

            const submitControls = Array.from(form.querySelectorAll(submitControlsSelector));
            const primaryControl =
                submitter && submitter.matches?.(submitControlsSelector) ? submitter : submitControls[0] || null;

            submitControls.forEach((control) => {
                markControlLoading(control, control === primaryControl);
            });

            showOverlay();
        });

        window.addEventListener('pageshow', () => {
            hideOverlay();
            document.querySelectorAll('form[data-save-submitting="1"]').forEach((form) => {
                restoreForm(form);
            });
        });
    })();
</script>
