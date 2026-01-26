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

        .lux-page {
            font-family: 'IBM Plex Sans Thai', sans-serif;
            color: var(--lux-ink);
            background: none;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
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

        #aiChatPanel {
            position: fixed;
            inset: 0 0 0 auto;
            width: min(94vw, 560px);
            height: 100dvh;
            transform: translateX(100%);
            transition: transform 0.25s ease;
            border-left: 1px solid #e5e7eb;
            background: #ffffff;
        }

        #aiChatPanel.is-open {
            transform: translateX(0);
        }

        #aiChatOverlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.25);
            backdrop-filter: blur(2px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            z-index: 40;
        }

        #aiChatOverlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        #aiChatForm {
            position: sticky;
            bottom: 0;
            background: #ffffff;
        }

        #aiChatPanel .ai-suggest {
            white-space: nowrap;
        }

        #aiChatPrompt {
            position: sticky;
            top: 0;
            margin: 12px;
            padding: 8px 12px;
            border-radius: 12px;
            background: rgba(15, 118, 110, 0.08);
            color: #0f766e;
            font-size: 12px;
            font-weight: 600;
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            z-index: 1;
        }

        #aiChatPrompt.is-show {
            opacity: 1;
            transform: translateY(0);
        }

        #aiChatFabWrap {
            position: fixed;
            right: 1.5rem;
            bottom: 1.5rem;
            z-index: 50;
        }

        #aiChatBubble {
            position: absolute;
            right: 56px;
            bottom: 4px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 8px 12px;
            font-size: 12px;
            color: #0f766e;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
            display: none;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        #aiChatBubble.is-show {
            display: flex;
        }

        #aiChatBubble button {
            border: none;
            background: transparent;
            color: #9ca3af;
            font-size: 14px;
            line-height: 1;
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            #aiChatPanel {
                width: min(94vw, 480px);
            }
        }

        @media (max-width: 640px) {
            #aiChatPanel {
                width: 100vw;
                height: 100dvh;
                border-left: none;
                border-top: 1px solid #e5e7eb;
            }

            .lux-chart-scroll {
                display: flex;
                overflow-x: auto;
                gap: 1rem;
                scroll-snap-type: x mandatory;
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
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 6px 0 4px;
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
        }

        @media (min-width: 768px) {
            .lux-chart-scroll {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

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
    </style>

    <div class="lux-page">
        <div class="max-w-7xl mx-auto px-4 py-8 space-y-8">
            <div class="lux-card lux-hero p-6 md:p-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="lux-title text-3xl md:text-4xl text-slate-900">ภาพรวมโครงการวิจัย</h2>

                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="lux-chip text-xs font-semibold px-3 py-1 rounded-full">อัปเดตล่าสุด
                            {{ now()->format('d/m/Y') }}</span>
                        <span class="lux-chip text-xs font-semibold px-3 py-1 rounded-full">ทั้งหมด
                            {{ $projects->count() }} โครงการ</span>
                        <span class="lux-chip text-xs font-semibold px-3 py-1 rounded-full">PI
                            {{ $projects->pluck('pi_name')->unique()->count() }} คน</span>
                    </div>
                </div>
                <div class="lux-divider mt-6"></div>

                <div class="lux-quick-nav md:hidden">
                    <a href="#section-summary">สรุปภาพรวม</a>
                    <a href="#section-charts">กราฟ</a>
                    <a href="#section-irb">IRB</a>
                    <a href="#section-copi">Co-PI</a>
                    <a href="#section-projects">โครงการ</a>
                </div>

                <!-- 1) Summary Cards -->
                <div id="section-summary"></div>
                <div class="mt-4 mb-2 text-xs text-slate-600 md:hidden">
                    ปัดซ้ายขวาเพื่อดูสรุปทั้งหมด • แตะการ์ดเพื่ออ่านเร็วขึ้น
                </div>
                <div class="lux-summary-scroll mt-2 lux-grid-stagger">
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
                        <div class="lux-summary-card" data-tone="{{ $item['tone'] }}">
                            <div class="lux-summary-icon">{{ $item['icon'] }}</div>
                            <div>
                                <div class="lux-summary-value">{{ $item['count'] }}</div>
                                <div class="lux-summary-label">{{ $item['label'] }}</div>
                                <div class="lux-summary-sub">{{ $item['sub'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        <!-- 2) กรอง + Export -->
        <div class="lux-card p-4 md:p-5" id="section-filters">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">สร้างเอกสารสรุปข้อมูลทั้งหมด</h3>
                    <p class="text-xs text-slate-500">คัดกรองข้อมูลให้ตรงกับช่วงเวลาหรือธีมที่ต้องการ</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
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
                    <label class="text-xs font-semibold text-gray-700 block">ค้นหาโครงการ</label>
                    <input type="text" id="filterName" class="w-full border rounded px-2 py-1 text-sm"
                        placeholder="ชื่อโครงการ">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 block">ค้นหา PI</label>
                    <input type="text" id="filterPI" class="w-full border rounded px-2 py-1 text-sm"
                        placeholder="ชื่อ PI">
                </div>
                <div class="flex items-end gap-2">
                    <button id="exportCSV"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-sm rounded shadow">
                        ⬇ Export CSV
                    </button>
                    <button id="exportExcel"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white px-3 py-2 text-sm rounded shadow">
                        ⬇ Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- 🗨️ Floating Chat Agent (ADD-ON) -->
        <div id="aiChatFabWrap">
            <div id="aiChatBubble">
                <span>ถามได้เลย</span>
                <button type="button" id="aiChatBubbleClose" aria-label="close">✕</button>
            </div>
            <button id="aiChatFab"
                class="rounded-full shadow-lg w-12 h-12 bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center">
                💬
            </button>
        </div>

        <div id="aiChatOverlay"></div>

        <div id="aiChatPanel"
            class="fixed z-50 bg-white shadow-2xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <div class="font-semibold text-gray-800">AI Research Assistant</div>
                <button class="text-gray-400 hover:text-red-500" onclick="toggleAiChat(false)">✕</button>
            </div>

            <!-- suggested prompts -->
            <div class="px-3 pt-3 pb-0 text-xs text-gray-500">ตัวอย่างที่ถามได้</div>
            <div class="px-3 pb-2 flex flex-wrap gap-2">

                <!-- งบประมาณ -->
                <button
                    class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">งบประมาณรวมต่อปี
                    2568</button>
                <button class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">งบประมาณ 5 ปี
                    ล่าสุด</button>
                <button
                    class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">แหล่งทุนที่เด่นที่สุดปีนี้คืออะไร</button>

                <!-- FTE -->
                <button class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">สรุป FTE ต่อคน
                    ปี 2568</button>
                <button class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">FTE ปี 2567
                    เกิน 1</button>

                <!-- สถานะ / โครงการ -->
                <button
                    class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">สถานะโครงการในปี
                    2566</button>

                <!-- Outputs -->
                <button class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">จำนวน Outputs
                    ปี 2567 แยกประเภท</button>
                <button class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">Outputs ปีนี้
                    แยกประเภท</button>

                <!-- IRB -->
                <button class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">IRB
                    ใกล้หมดอายุ 30 วัน</button>

                <!-- แหล่งทุน/ประเภท -->
                <button
                    class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">แหล่งทุนที่เด่น ปี
                    2568</button>
                <button class="ai-suggest px-2 py-1 rounded-full border text-xs bg-gray-50 hover:bg-gray-100">งบภายใน-ภายนอก
                    ปี 2566 เทียบกัน</button>


            </div>


            <!-- messages -->
            <div id="aiChatBody" class="flex-1 overflow-y-auto p-3 space-y-3">
                <div class="text-xs text-gray-500">เริ่มพิมพ์คำถามเกี่ยวกับโครงการ/FTE/Outputs ได้เลยครับ</div>
            </div>

            <!-- input -->
            <form id="aiChatForm" class="border-t p-2 flex items-end gap-2">
                @csrf
                <textarea id="aiChatInput" rows="1"
                    class="flex-1 border rounded-lg px-3 py-2 text-sm resize-none focus:outline-none"
                    placeholder="พิมพ์คำถาม เช่น 'FTE ต่อคน ปี 2568' ..."></textarea>
                <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-3 py-2 rounded-lg">ส่ง</button>
            </form>
        </div>



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
            </div>
            <div id="chartSwipeTip" class="lux-swipe-tip mb-3">
                <span>เลื่อนซ้ายขวาเพื่อดูกราฟต่อไป • แตะกราฟเพื่อดูข้อมูลเพิ่ม</span>
                <button type="button" data-close="chartSwipeTip">ปิด</button>
            </div>
            <div class="lux-chart-scroll mb-6">
                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🔴 RED ZONE: โครงการใกล้สิ้นสุด / เกินกำหนด</h3>
                <canvas id="chartRedZoneTimeline" height="250"></canvas>
            </div>
            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🔥 โครงการที่เกินกำหนดแล้ว (Overdue)</h3> <canvas
                    id="chartOverdueProjects" height="250"></canvas>
            </div>

            <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4 md:col-span-2">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">⏱️ รายการโครงการที่เกินกำหนด (ลงรายละเอียด)</h3>
                    <span id="overdueCountBadge"
                        class="text-xs font-semibold px-3 py-1 rounded-full lux-chip">0 โครงการ</span>
                </div>
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
                </div>

                <div class="lux-chart-card bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 ทุนภายใน แยกตามแหล่งทุน (Top 8)</h3>
                    <canvas id="chartInternalBySource" height="250"></canvas>
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
        </div>

            <!-- IRB List -->
            <div class="bg-white shadow-sm border rounded-xl p-4 mb-6" id="section-irb">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">📑 รายการ IRB</h3>
                    <label class="text-xs text-gray-500">ตัวกรอง:
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

            <!-- 4) Co-Research (จาก COPIs) Summary -->
            <div class="bg-white shadow-sm border rounded-xl p-4 mb-6" id="section-copi">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🤝 รายชื่อผู้วิจัยร่วม CO-PI</h3>

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
                                class="ml-1 border rounded px-2 py-1 text-sm" placeholder="ชื่อโครงการ หรือ PI">
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
                <div class="flex items-center justify-between mt-4 text-xs text-gray-600">
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
            <div
                class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-xl w-[92vw] max-w-5xl max-h-[80vh] overflow-auto">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b px-4 py-3 gap-2">
                    <h3 id="chartModalTitle" class="font-semibold text-gray-800">รายละเอียด</h3>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500">Sort:
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
                        <button class="text-gray-500 hover:text-red-500 text-xl" onclick="closeModal()">&times;</button>
                    </div>
                </div>
                <div id="chartModalBody" class="p-4 text-sm"></div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
        <script>
            // ให้สคริปต์ด้านล่างใช้ window.outputs ได้เลย
            window.outputs = @json($outputsForJs);
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
            /* ---------- Data from backend ---------- */

            const projects = @json($projects);
            const outputs = @json($outputsForJs); // ใช้ตัวที่ map จาก Controller
            const ftes = @json($fteRoles);
            const irbs = @json($irbs);
            const progressReports = @json($progressReports);
            const copisSummary = @json($copiRows);


            @php
                // ดึง outputs ที่ติด authors (จากความสัมพันธ์ outputs->authors)
                $outputsForJs = $projects->flatMap->outputs
                    ->map(function ($o) {
                        return [
                            'id' => $o->id,
                            'type' => $o->type,
                            'title' => $o->title,
                            'year' => $o->year,
                            'authors' => $o->authors
                                ->map(
                                    fn($a) => [
                                        'name' => $a->name,
                                        'role' => $a->role, // 'first' | 'co' | 'corresponding'
                                    ],
                                )
                                ->values(),
                        ];
                    })
                    ->values();
            @endphp



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
            const projectById = new Map(projects.map(p => [p.id, p]));

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

                if (modalType === 'redzone' || modalType === 'overdue') {
                    const now = new Date();
                    const pageSize = state.pageSize || 10;
                    const yearOptions = Array.from(new Set(rows.map(endYearLabel))).sort();
                    const activeYear = state.yearFilter || '';
                    const diffHeader = modalType === 'overdue' ? 'เกินกำหนด (วัน)' : 'คงเหลือ/เกิน (วัน)';

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
                            const diffLabel = diffDays == null ? '-' : (diffDays < 0 ? `${Math.abs(diffDays)}` : `${diffDays}`);
                            html += `
                                <tr class="border-t">
                                    <td class="p-2 text-center" data-label="#">${start + idx + 1}</td>
                                    <td class="p-2 text-left text-blue-700 hover:underline" data-label="ชื่อโครงการ">
                                        <a href="/rm/projects/${p.id}" target="_blank" rel="noopener">${p.project_name_th || '-'}</a>
                                    </td>
                                    <td class="p-2 text-center" data-label="ปีสิ้นสุด">${formatDateTH(endDate, '-')}</td>
                                    <td class="p-2 text-center ${diffDays < 0 ? 'text-red-600 font-semibold' : 'text-amber-600 font-semibold'}" data-label="${diffHeader}">${diffLabel}</td>
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
                const name = norm(document.getElementById('filterName')?.value);
                const pi = norm(document.getElementById('filterPI')?.value);
                const search = norm(document.getElementById('tableSearch')?.value);

                return projects.filter(p => {
                    const year = norm(p.start_year);
                    const theme = norm(p.theme);
                    const funding = norm(p.funding_type);
                    const nameTh = norm(p.project_name_th);
                    const piName = norm(p.pi_name);

                    if (y && year !== y) return false;
                    if (t && theme !== t) return false;
                    if (f && funding !== f) return false;
                    if (name && !nameTh.includes(name)) return false;
                    if (pi && !piName.includes(pi)) return false;
                    if (search && !(`${nameTh} ${piName}`.includes(search))) return false;
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
                    document.getElementById('filterName')?.value,
                    document.getElementById('filterPI')?.value,
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
                    return {
                        title: `โครงการปี ${label}`,
                        projects: projects.filter(p => matchYearLabel(p, label))
                    };
                }

                if (id === 'chartYearlyNewVsTotal') {
                    const Y = toNumber(label);
                    const type = datasetLabel || '';
                    const isActiveInYear = (p, y) => {
                        const s = toNumber(p.start_year);
                        const e = toNumber(getEndYear(p)) || Infinity;
                        const yr = toNumber(y);
                        return s <= yr && yr <= e;
                    };
                    let list = [];
                    if (type === 'ทั้งหมด') {
                        list = projects.filter(p => isActiveInYear(p, Y));
                    } else if (type === 'ใหม่') {
                        list = projects.filter(p => matchYearLabel(p, label));
                    } else if (type === 'กำลังทำ') {
                        list = projects.filter(p => {
                            const s = toNumber(p.start_year);
                            const e = toNumber(getEndYear(p)) || Infinity;
                            return s < Y && Y <= e;
                        });
                    }
                    return {
                        title: `โครงการ${type} ปี ${label}`,
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
                    const year = String(label ?? '');
                    const type = String(datasetLabel ?? '');
                    return {
                        title: `Outputs ${type} ปี ${year}`,
                        projects: projectsFromOutputs(o => normalizeOutputYear(o.year ?? o.date ?? o.created_at) === year && outputType(o) === type)
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
                    const list = projects.filter(p => {
                        const endDate = parseProjectDate(getEndDateRaw(p));
                        if (!endDate) return false;
                        const diffDays = (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24);
                        if (labelText.includes('เกินกำหนด')) return diffDays < 0;
                        return diffDays >= 0;
                    });
                    return {
                        title: labelText,
                        projects: list,
                        modalType: 'overdue'
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
                const points = chart.getElementsAtEventForMode(evt, 'nearest', {
                    intersect: true
                }, true);
                if (!points || !points.length) return;
                const result = resolveProjectsForChart(chart, points[0]);
                if (result) showProjectList(result.title, result.projects, {
                    includeFunding: result.includeFunding,
                    modalType: result.modalType,
                    unit: result.unit,
                    windowMonths: result.windowMonths
                });
            }

            document.addEventListener('click', (evt) => {
                const canvas = evt.target;
                if (!canvas || canvas.tagName !== 'CANVAS') return;
                if (!canvas.id || !canvas.id.startsWith('chart')) return;
                const chart = Chart.getChart(canvas);
                if (!chart) return;
                handleChartClick(evt, chart);
            });

            /* ---------- 1) จำนวนโครงการรายปี (Bar + ตัวเลขบนแท่ง | กันเลขตกขอบ) ---------- */
            (function() {
                // รวมจำนวนโครงการต่อปี
                const counts = new Map();
                projects.forEach(p => {
                    const y = (getYear(p) ?? '').toString().trim() || 'ไม่ระบุ';
                    counts.set(y, (counts.get(y) || 0) + 1);
                });

                // ปีตัวเลขก่อน แล้วค่อยหมวดอื่น
                const isNumeric = (s) => /^\d+$/.test(s);
                const numericYears = [...counts.keys()].filter(isNumeric).map(Number).sort((a, b) => a - b).map(String);
                const otherYears = [...counts.keys()].filter(k => !isNumeric(k)).sort();
                const labels = numericYears.concat(otherYears);

                const data = labels.map(y => counts.get(y) ?? 0);
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
                                    text: 'ปี'
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
                function safeYear(val) {
                    if (!val) return 'ไม่ระบุ';
                    const str = String(val).trim();
                    if (/^\d{4}$/.test(str)) return str;
                    const match = str.match(/(25\d{2}|20\d{2}|19\d{2})/);
                    if (match) return match[1];
                    const d = new Date(str);
                    if (!isNaN(d)) return String(d.getFullYear());
                    return 'ไม่ระบุ';
                }

                const years = [...new Set(outputs.map(o => safeYear(o.year ?? o.date ?? o.created_at)))];
                const isNum = s => /^\d+$/.test(s);
                const sortedYears = years.filter(isNum).map(Number).sort((a, b) => a - b).map(String)
                    .concat(years.filter(y => !isNum(y)));

                const types = [...new Set(outputs.map(outputType))].sort();
                const grid = {};
                sortedYears.forEach(y => grid[y] = Object.fromEntries(types.map(t => [t, 0])));

                outputs.forEach(o => {
                    const y = safeYear(o.year ?? o.date ?? o.created_at);
                    const t = outputType(o);
                    if (!grid[y]) grid[y] = Object.fromEntries(types.map(tt => [tt, 0]));
                    grid[y][t] += 1;
                });

                const datasets = types.map((t, i) => ({
                    label: t,
                    data: sortedYears.map(y => grid[y][t]),
                    backgroundColor: `hsl(${(i*53)%360} 70% 60%)`,
                    stack: 'out'
                }));

                // ปลั๊กอินแสดงตัวเลขบนแท่ง + กันตกขอบบน/ขวา + เช็คซ่อนชุดข้อมูล
                const dataLabelPlugin = {
                    id: 'dataLabelPluginOutputs',
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

                        chart.data.datasets.forEach((ds, dsIndex) => {
                            // ถ้าชุดนี้ถูกซ่อนจาก legend -> ข้าม
                            if (!chart.isDatasetVisible(dsIndex)) return;

                            const meta = chart.getDatasetMeta(dsIndex);
                            meta.data.forEach((bar, i) => {
                                if (!bar || bar.hidden) return;

                                const v = Number(ds.data[i] || 0);
                                if (!isFinite(v) || v <= 0) return;

                                let drawX = bar.x;
                                let drawY = bar.y - 3; // วางเหนือแท่งเล็กน้อย

                                // clamp กันตกขอบบน
                                const minY = top + 8;
                                if (drawY < minY) drawY = minY;

                                // กันชิดขอบขวา (เผื่อข้อความยาว)
                                if (drawX > right - 2) drawX = right - 2;

                                ctx.fillText(String(v), drawX, drawY);
                            });
                        });

                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartOutputStacked');
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                new Chart(el, {
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
                        }, // เผื่อพื้นที่ด้านบน
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
                                    text: 'จำนวนผลงาน'
                                }
                            }
                        }
                    },
                    plugins: [dataLabelPlugin]
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
                const now = new Date();
                const group = {};
                irbs.forEach(irb => {
                    const exp = new Date(irb.exp_date);
                    const monthsAhead = (exp - now) / (1000 * 60 * 60 * 24 * 30);
                    if (monthsAhead >= 0 && monthsAhead <= 6) {
                        const key = `${exp.getMonth()+1}/${exp.getFullYear()}`;
                        group[key] = (group[key] || 0) + 1;
                    }
                });
                const labels = Object.keys(group).sort((a, b) => new Date(a) - new Date(b));
                new Chart(document.getElementById('chartIRBExpiry'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'IRB ใกล้หมดอายุ',
                            data: labels.map(l => group[l]),
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
                const TOP_N = 20;
                const sorted = Object.entries(countByPI).sort((a, b) => b[1] - a[1]);
                const top = sorted.slice(0, TOP_N);

                const labels = top.map(([pi]) => pi);
                const data = top.map(([, count]) => count);

                const el = document.getElementById('chartPIsPerUnit');
                if (!el) return;

                el.height = Math.max(250, labels.length * 28);

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

                const TOP_N_SOURCES = 8;
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
                            ctx
                        } = chart;
                        const meta = chart.getDatasetMeta(0);
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        meta.data.forEach((el, i) => {
                            const c = counts[i];
                            const m = budgets[i];
                            if (!c) return;
                            const {
                                x,
                                y
                            } = el.tooltipPosition();
                            ctx.fillText(`${c} (${formatThaiMoney(m)})`, x, y);
                        });
                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartExternalBySource');
                if (!el) return;
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                new Chart(el, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            label: 'จำนวนโครงการ',
                            data: counts,
                            backgroundColor: labels.map((_, i) => `hsl(${(i*37)%360} 70% 60%)`)
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
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

                const TOP_N_SOURCES = 8;
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
                            ctx
                        } = chart;
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        const meta = chart.getDatasetMeta(0);
                        meta.data.forEach((el, i) => {
                            const count = counts[i];
                            const money = budgets[i];
                            if (!count || !money) return;
                            const {
                                x,
                                y
                            } = el.tooltipPosition();
                            ctx.fillText(`${count} (${formatThaiMoney(money)})`, x, y);
                        });
                        ctx.restore();
                    }
                };

                const el = document.getElementById('chartInternalBySource');
                if (!el) return;
                const prev = Chart.getChart(el);
                if (prev) prev.destroy();

                new Chart(el, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            label: 'โครงการ',
                            data: counts,
                            backgroundColor: labels.map((_, i) => `hsl(${(i*37)%360} 70% 60%)`)
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
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
                            plugins: {
                                legend: {
                                    position: 'bottom'
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
                                    stacked: true
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

            /* ---------- 11) โครงการทั้งหมด vs ใหม่ vs ค้างจากปีก่อน (ต่อปี) + Data Label + กันเลขตกขอบ ---------- */
            (function() {
                const uniqYears = [...new Set(projects.map(getYear))]
                    .filter(y => y != null && String(y).trim() !== '');
                const years = uniqYears.map(Number).sort((a, b) => a - b).map(String);

                function isActiveInYear(p, y) {
                    const s = toNumber(p.start_year);
                    const e = toNumber(getEndYear(p)) || Infinity;
                    const Y = toNumber(y);
                    return s <= Y && Y <= e;
                }

                const totalPerYear = {};
                const newPerYear = {};
                const carryoverPerYear = {};

                years.forEach(y => {
                    totalPerYear[y] = projects.filter(p => isActiveInYear(p, y)).length;
                    newPerYear[y] = projects.filter(p => String(getYear(p)) === String(y)).length;
                    carryoverPerYear[y] = projects.filter(p => {
                        const s = toNumber(p.start_year);
                        const e = toNumber(getEndYear(p)) || Infinity;
                        const Y = toNumber(y);
                        return s < Y && Y <= e; // เริ่มก่อนปีนี้และยัง active
                    }).length;
                });

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

                        chart.data.datasets.forEach((dataset, dsIndex) => {
                            // ถ้าชุดข้อมูลถูกซ่อนจาก legend ให้ข้าม
                            if (!chart.isDatasetVisible(dsIndex)) return;

                            const meta = chart.getDatasetMeta(dsIndex);
                            meta.data.forEach((bar, index) => {
                                if (!bar || bar.hidden) return;
                                const val = dataset.data[index];
                                if (!isFinite(val) || val <= 0) return;

                                let drawX = bar.x;
                                let drawY = bar.y - 2;

                                // clamp กันตกขอบบน
                                const minY = top + 8;
                                if (drawY < minY) drawY = minY;

                                // กันชิดขอบขวา (เผื่อข้อความ)
                                if (drawX > right - 2) drawX = right - 2;

                                ctx.fillText(val, drawX, drawY);
                            });
                        });

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
                        labels: years,
                        datasets: [{
                                label: 'ทั้งหมด',
                                data: years.map(y => totalPerYear[y]),
                                backgroundColor: '#93c5fd'
                            },
                            {
                                label: 'ใหม่',
                                data: years.map(y => newPerYear[y]),
                                backgroundColor: '#34d399'
                            },
                            {
                                label: 'กำลังทำ',
                                data: years.map(y => carryoverPerYear[y]),
                                backgroundColor: '#fbbf24'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        layout: {
                            padding: {
                                top: 16
                            }
                        }, // ← เผื่อพื้นที่ด้านบนให้ label
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
                            y: {
                                beginAtZero: true,
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
                                title: {
                                    display: true,
                                    text: 'ปี'
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

                    new Chart(elOverdue, {
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
                        total += members.length;
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
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            chart.data.datasets.forEach((ds, dsIndex) => {
                                if (!chart.isDatasetVisible(dsIndex)) return;
                                const meta = chart.getDatasetMeta(dsIndex);
                                meta.data.forEach((bar, i) => {
                                    const v = Number(ds.data[i] || 0);
                                    if (!isFinite(v) || v <= 0) return;
                                    let x = bar.x;
                                    let y = bar.y - 3;
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
                    'id',
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
                const core = keys.filter(k => !k.startsWith('strategy_'));
                const ordered = [];

                preferred.forEach(k => {
                    if (core.includes(k)) ordered.push(k);
                });
                core.sort().forEach(k => {
                    if (!ordered.includes(k)) ordered.push(k);
                });
                return [...ordered, ...strategyKeys];
            }

            const detailColumns = {
                irb: ['irb_id', 'irb_code', 'irb_exp_date', 'irb_source'],
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
                    ...detailColumns.coresearch,
                    ...detailColumns.fte,
                    ...detailColumns.output
                ];
                const header = [...baseKeys, 'detail_type', ...detailHeaders];
                const rows = [header];

                list.forEach(p => {
                    const base = baseKeys.map(k => p[k] ?? '');
                    let baseWritten = false;
                    const irbs = p.irbs || [];
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
                            ...Array(detailColumns.coresearch.length + detailColumns.fte.length + detailColumns.output.length).fill('')
                        ]);
                    });

                    coResearches.forEach(cr => {
                        pushDetail('coresearch', [
                            ...Array(detailColumns.irb.length).fill(''),
                            cr.id ?? '', cr.name ?? '', cr.unit_status ?? '', cr.affiliation ?? '',
                            ...Array(detailColumns.fte.length + detailColumns.output.length).fill('')
                        ]);
                    });

                    fteRoles.forEach(f => {
                        pushDetail('fte', [
                            ...Array(detailColumns.irb.length + detailColumns.coresearch.length).fill(''),
                            f.id ?? '', f.name ?? '', f.role_group ?? '', f.job_detail ?? '', f.fte_value ?? '',
                            f.hours ?? '', f.task_detail ?? '', f.start_date ?? '', f.end_date ?? '',
                            ...Array(detailColumns.output.length).fill('')
                        ]);
                    });

                    outputsList.forEach(o => {
                        pushDetail('output', [
                            ...Array(detailColumns.irb.length + detailColumns.coresearch.length + detailColumns.fte.length).fill(''),
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

            document.getElementById('exportCSV')?.addEventListener('click', () => {
                const filtered = getFilteredProjects();
                const rows = buildCsvRows(filtered);
                downloadCsv(rows, `${exportBaseName()}_projects.csv`);
            });

            document.getElementById('exportExcel')?.addEventListener('click', () => {
                if (typeof XLSX === 'undefined') {
                    alert('ไม่พบโมดูลสำหรับสร้างไฟล์ Excel');
                    return;
                }

                const filtered = getFilteredProjects();
                const baseKeys = buildBaseKeyOrder(filtered);

                const projectSheetRows = filtered.map(p => {
                    const row = {};
                    baseKeys.forEach(k => {
                        row[k] = p[k] ?? '';
                    });
                    row.irb_count = (p.irbs || []).length;
                    row.coresearch_count = (p.co_researches || p.coResearches || []).length;
                    row.fte_count = (p.fte_roles || p.fteRoles || []).length;
                    row.output_count = (p.outputs || []).length;
                    return row;
                });

                const irbRows = filtered.flatMap(p => (p.irbs || []).map(irb => ({
                    project_id: p.id,
                    project_name_th: p.project_name_th,
                    pi_name: p.pi_name,
                    start_year: p.start_year,
                    irb_code: irb.code ?? '',
                    irb_exp_date: irb.exp_date ?? '',
                    irb_source: irb.source ?? ''
                })));

                const coResearchRows = filtered.flatMap(p => (p.co_researches || p.coResearches || []).map(cr => ({
                    project_id: p.id,
                    project_name_th: p.project_name_th,
                    pi_name: p.pi_name,
                    start_year: p.start_year,
                    name: cr.name ?? '',
                    unit_status: cr.unit_status ?? '',
                    affiliation: cr.affiliation ?? ''
                })));

                const fteRows = filtered.flatMap(p => (p.fte_roles || p.fteRoles || []).map(f => ({
                    project_id: p.id,
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
                    project_id: p.id,
                    project_name_th: p.project_name_th,
                    pi_name: p.pi_name,
                    start_year: p.start_year,
                    output_type: o.type ?? '',
                    output_title: o.title ?? '',
                    output_year: o.year ?? ''
                })));

                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(projectSheetRows), 'Projects');
                XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(irbRows), 'IRB');
                XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(coResearchRows), 'CoResearch');
                XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(fteRows), 'FTE');
                XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(outputRows), 'Outputs');

                XLSX.writeFile(wb, `${exportBaseName()}_projects.xlsx`);
            });

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
                const filterName = document.getElementById('filterName');
                const filterPI = document.getElementById('filterPI');
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

                [filterYear, filterTheme, filterFunding].forEach(el => el?.addEventListener('change', resetAndRender));
                [filterName, filterPI, tableSearch].forEach(el => el?.addEventListener('input', resetAndRender));

                renderTable();
            })();
        </script>

        <script>
            console.log('sample', (@json($projects))[0]);
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
                    el.height = Math.max(320, labels.length * 30);

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

            const aiFab = document.getElementById('aiChatFab');
            const aiPanel = document.getElementById('aiChatPanel');
            const aiOverlay = document.getElementById('aiChatOverlay');
            const aiForm = document.getElementById('aiChatForm');
            const aiInput = document.getElementById('aiChatInput');
            const aiBody = document.getElementById('aiChatBody');
            const aiBubble = document.getElementById('aiChatBubble');
            const aiBubbleClose = document.getElementById('aiChatBubbleClose');

            function toggleAiChat(show = null) {
                if (show === null) show = !aiPanel.classList.contains('is-open');
                aiPanel.classList.toggle('is-open', show);
                aiOverlay.classList.toggle('is-open', show);
                if (show) setTimeout(() => aiInput?.focus(), 50);
            }
            aiFab?.addEventListener('click', () => toggleAiChat());
            aiOverlay?.addEventListener('click', () => toggleAiChat(false));

            if (aiBubble) {
                aiBubble.classList.add('is-show');
            }
            aiBubbleClose?.addEventListener('click', () => {
                aiBubble?.classList.remove('is-show');
            });
            document.querySelectorAll('.ai-suggest').forEach(b => {
                b.addEventListener('click', () => {
                    aiInput.value = b.textContent.trim();
                    aiInput.dispatchEvent(new Event('input'));
                    aiForm.requestSubmit();
                });
            });

            // autosize
            aiInput?.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // 💡 NEW FEATURE: ส่งข้อความเมื่อกด Enter
            aiInput?.addEventListener('keydown', function(e) {
                // ตรวจสอบว่ากดปุ่ม Enter (keyCode 13)
                if (e.key === 'Enter') {
                    // ถ้าเป็น Shift+Enter หรือ Ctrl+Enter ให้ขึ้นบรรทัดใหม่ตามปกติ
                    if (e.shiftKey || e.ctrlKey) {
                        return;
                    }

                    // ถ้าเป็น Enter ธรรมดา ให้หยุดการทำงานปกติ (หยุดการขึ้นบรรทัดใหม่)
                    e.preventDefault();

                    // เรียกส่งฟอร์ม
                    aiForm.requestSubmit();
                }
            });

            function bubble(role, html) {
                const wrap = document.createElement('div');
                const base = role === 'user' ?
                    'bg-emerald-50 border-emerald-200 ml-12' :
                    'bg-white border-gray-200 mr-12';
                wrap.className = `border rounded-xl p-3 text-sm ${base}`;
                wrap.innerHTML = html;
                aiBody.appendChild(wrap);
                aiBody.scrollTop = aiBody.scrollHeight;
                return wrap;
            }

            // สร้าง canvas chart ภายในบับเบิลคำตอบ
            function renderInlineChart(container, spec) {
                const canvas = document.createElement('canvas');
                canvas.height = 220;
                container.appendChild(canvas);
                try {
                    const cfg = {
                        type: spec.type || 'bar',
                        data: {
                            labels: spec.labels || [],
                            datasets: spec.datasets || []
                        },
                        options: spec.options || {}
                    };
                    new Chart(canvas, cfg);
                } catch (e) {
                    const err = document.createElement('div');
                    err.className = 'text-xs text-red-500';
                    err.textContent = 'แสดงกราฟไม่สำเร็จ';
                    container.appendChild(err);
                }
            }

            aiForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const q = (aiInput.value || '').trim();
                if (!q) return;
                bubble('user', q);
                aiInput.value = '';
                aiInput.dispatchEvent(new Event('input'));

                const thinking = bubble('bot', '<div class="text-gray-500 text-sm">กำลังวิเคราะห์…</div>');

                try {
                    const resp = await fetch("{{ url('/rm/agent/ask') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name=_token]')?.value ||
                                '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            prompt: q
                        })
                    });
                    const data = await resp.json();

                    // แทนที่บับเบิลกำลังคิด
                    thinking.innerHTML = '';
                    if (data.reply) {
                        thinking.innerHTML = `<div class="prose prose-sm max-w-none">${data.reply}</div>`;
                    } else {
                        thinking.innerHTML = '<div class="text-gray-500">ไม่มีข้อความตอบกลับ</div>';
                    }

                    // ถ้ามีตาราง
                    // ถ้ามีตาราง

                    if (data.table && Array.isArray(data.table.rows)) {

                        // ✅ FIX 1.1: สร้าง wrapper เพื่อให้ตาราง Scroll ได้แนวนอน (ยังคงจำเป็นเผื่อตารางกว้าง)
                        const tableWrapper = document.createElement('div');
                        tableWrapper.className = 'overflow-x-auto mt-2';

                        const tbl = document.createElement('table');
                        // ✅ FIX: ลบ whitespace-nowrap ออก เพื่อให้ข้อความตัดบรรทัดได้
                        tbl.className = 'min-w-full text-xs border';

                        const thead = document.createElement('thead');
                        thead.className = 'bg-gray-50';
                        thead.innerHTML =
                            `<tr>${(data.table.headers||[]).map(h=>`<th class="p-2 text-left">${h}</th>`).join('')}</tr>`;
                        tbl.appendChild(thead);

                        const tbody = document.createElement('tbody');
                        (data.table.rows || []).forEach(r => {
                            const tr = document.createElement('tr');
                            tr.className = 'border-t';

                            tr.innerHTML = (data.table.fields || []).map(f => {
                                let content = r[f] ?? '-';

                                // ✅ FIX 2.1: ตัดข้อความสำหรับคอลัมน์ Project Name ที่ยาวเกินไป
                                if (f === 'project' || f === 'project_name') {
                                    const MAX_LENGTH = 50;
                                    if (content.length > MAX_LENGTH) {
                                        content = content.substring(0, MAX_LENGTH) + '...';
                                    }
                                }

                                // ✅ FIX 1.2: สร้าง Hyperlink ให้ชื่อโครงการ หากมี URL
                                if ((f === 'project' || f === 'project_name') && r.href && r
                                    .href !== '#') {
                                    content =
                                        `<a href="${r.href}" target="_blank" class="text-blue-600 hover:underline">${content}</a>`;
                                } else if (f === 'href') {
                                    // ถ้าเป็นคอลัมน์ href โดยตรง ให้แสดงเป็นไอคอน Link แทน
                                    content =
                                        `<a href="${r.href}" target="_blank" class="text-blue-600 hover:underline">🔗</a>`;
                                }

                                // ✅ FIX 2.2: กำหนดความกว้างขั้นต่ำ (min-width) ให้คอลัมน์ชื่อโครงการ
                                // เพื่อให้คอลัมน์ไม่หดตัวจนเล็กเกินไปเมื่อตัดบรรทัด
                                const style = (f === 'project' || f === 'project_name') ?
                                    'style="min-width: 150px;"' : '';

                                return `<td class="p-2" ${style}>${content}</td>`;

                            }).join('');
                            tbody.appendChild(tr);
                        });
                        tbl.appendChild(tbody);
                        tableWrapper.appendChild(tbl);
                        thinking.appendChild(tableWrapper); // เปลี่ยนมา append tableWrapper แทน
                    }
                    // ถ้ามีกราฟ
                    if (data.chart) {
                        const box = document.createElement('div');
                        box.className = 'mt-3';
                        thinking.appendChild(box);
                        renderInlineChart(box, data.chart);
                    }

                    // ถ้ามีกราฟที่สอง (เช่น donut แหล่งทุน)
                    if (data.chart2) {
                        const box2 = document.createElement('div');
                        box2.className = 'mt-3';
                        thinking.appendChild(box2);
                        renderInlineChart(box2, data.chart2);
                    }


                } catch (err) {
                    thinking.innerHTML = `<div class="text-red-600">เกิดข้อผิดพลาดในการเรียกผู้ช่วย</div>`;
                }
            });
        </script>
    </div>
@endsection
