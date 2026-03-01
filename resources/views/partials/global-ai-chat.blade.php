@php
    $aiChatStorageKey = 'sihp-ai-chat:v2:user:' . (auth()->id() ?? 'guest');
@endphp

<style>
    #appAiChatRoot {
        position: relative;
        z-index: 80;
    }

    #appAiChatOverlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.14);
        backdrop-filter: blur(3px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.22s ease;
        z-index: 80;
    }

    #appAiChatOverlay.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    #appAiChatFabWrap {
        position: fixed;
        right: 1.25rem;
        bottom: calc(1.25rem + env(safe-area-inset-bottom));
        z-index: 81;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    #appAiChatFabWrap.is-panel-open {
        opacity: 0;
        pointer-events: none;
        transform: translateY(12px);
    }

    #appAiChatBubble {
        position: absolute;
        right: 4.5rem;
        bottom: 0.35rem;
        display: none;
        align-items: center;
        gap: 0.55rem;
        padding: 0.75rem 0.9rem;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.98);
        color: #0f766e;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.14);
    }

    #appAiChatBubble.is-show {
        display: inline-flex;
    }

    #appAiChatBubble button {
        border: none;
        background: transparent;
        color: #94a3b8;
        font-size: 0.9rem;
        line-height: 1;
        cursor: pointer;
    }

    #appAiChatFab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3.85rem;
        height: 3.85rem;
        border: none;
        border-radius: 999px;
        background: linear-gradient(135deg, #0f766e 0%, #059669 100%);
        color: #ffffff;
        box-shadow: 0 20px 44px rgba(5, 150, 105, 0.3);
        font-size: 1.35rem;
    }

    #appAiChatPanel {
        position: fixed;
        right: 1.25rem;
        bottom: 1.25rem;
        display: flex;
        flex-direction: column;
        width: min(430px, calc(100vw - 2rem));
        height: min(78dvh, 760px);
        border: 1px solid rgba(226, 232, 240, 0.96);
        border-radius: 1.75rem;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 26px 80px rgba(15, 23, 42, 0.24);
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
        transform: translateY(26px) scale(0.98);
        transform-origin: bottom right;
        transition: transform 0.24s ease, opacity 0.24s ease;
        z-index: 82;
    }

    #appAiChatPanel.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .app-ai-chat__header {
        padding: 1rem 1rem 0.9rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        background:
            radial-gradient(circle at top right, rgba(6, 182, 212, 0.16), transparent 34%),
            linear-gradient(135deg, rgba(15, 118, 110, 0.1), rgba(255, 255, 255, 0.98));
    }

    .app-ai-chat__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.7rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.12);
        color: #0f766e;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .app-ai-chat__heading {
        margin-top: 0.7rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .app-ai-chat__title {
        font-size: 1.02rem;
        font-weight: 800;
        line-height: 1.2;
        color: #0f172a;
    }

    .app-ai-chat__subtitle {
        margin-top: 0.32rem;
        font-size: 0.76rem;
        line-height: 1.45;
        color: #475569;
    }

    .app-ai-chat__header-actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .app-ai-chat__tool,
    .app-ai-chat__close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.4rem;
        border-radius: 0.9rem;
        border: 1px solid rgba(203, 213, 225, 0.9);
        background: rgba(255, 255, 255, 0.92);
        color: #334155;
        font-size: 0.74rem;
        font-weight: 700;
        padding: 0 0.8rem;
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .app-ai-chat__close {
        width: 2.4rem;
        padding: 0;
        font-size: 1rem;
    }

    .app-ai-chat__tool:hover,
    .app-ai-chat__close:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    #appAiChatSuggestPanel {
        margin: 0.75rem 1rem 0;
        padding: 0.8rem;
        border: 1px solid rgba(226, 232, 240, 0.96);
        border-radius: 1rem;
        background: #f8fafc;
    }

    #appAiChatSuggestPanel[hidden] {
        display: none;
    }

    .app-ai-chat__suggest-label {
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
    }

    .app-ai-chat__suggest-grid {
        margin-top: 0.65rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        max-height: 9.5rem;
        overflow-y: auto;
    }

    .app-ai-chat__suggest {
        border: 1px solid rgba(203, 213, 225, 0.92);
        background: #ffffff;
        color: #0f172a;
        border-radius: 999px;
        padding: 0.5rem 0.8rem;
        font-size: 0.75rem;
        line-height: 1.35;
        text-align: left;
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .app-ai-chat__suggest:hover {
        background: #ecfeff;
        border-color: #67e8f9;
        transform: translateY(-1px);
    }

    #appAiChatBody {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        background:
            radial-gradient(circle at top right, rgba(34, 197, 94, 0.08), transparent 28%),
            linear-gradient(180deg, #f8fafc 0%, #ffffff 24%);
        overscroll-behavior: contain;
    }

    .app-ai-chat__empty {
        border: 1px dashed #cbd5e1;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.72);
        padding: 1rem;
        font-size: 0.84rem;
        line-height: 1.55;
        color: #475569;
    }

    .app-ai-chat__message {
        border-radius: 1.1rem;
        border: 1px solid rgba(226, 232, 240, 0.95);
        padding: 0.85rem 0.95rem;
        font-size: 0.84rem;
        line-height: 1.55;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    }

    .app-ai-chat__message + .app-ai-chat__message {
        margin-top: 0.7rem;
    }

    .app-ai-chat__message--user {
        margin-left: 3rem;
        background: #ecfdf5;
        border-color: #a7f3d0;
    }

    .app-ai-chat__message--assistant {
        margin-right: 3rem;
        background: #ffffff;
    }

    .app-ai-chat__message-label {
        margin-bottom: 0.35rem;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #64748b;
    }

    .app-ai-chat__message-body {
        color: #0f172a;
        word-break: break-word;
        white-space: pre-line;
    }

    .app-ai-chat__thinking {
        color: #64748b;
    }

    .app-ai-chat__list {
        margin-top: 0.8rem;
        display: grid;
        gap: 0.55rem;
    }

    .app-ai-chat__list-item {
        display: block;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 0.95rem;
        background: #f8fafc;
        padding: 0.75rem 0.85rem;
        color: inherit;
        text-decoration: none;
    }

    .app-ai-chat__list-item:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .app-ai-chat__list-title {
        font-weight: 700;
        color: #0f172a;
    }

    .app-ai-chat__list-meta {
        margin-top: 0.3rem;
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.45;
    }

    .app-ai-chat__table-wrap {
        margin-top: 0.8rem;
        overflow-x: auto;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 1rem;
        background: #ffffff;
    }

    .app-ai-chat__table-title {
        margin-top: 0.8rem;
        font-size: 0.74rem;
        font-weight: 800;
        color: #334155;
    }

    .app-ai-chat__table {
        width: 100%;
        min-width: 100%;
        border-collapse: collapse;
        font-size: 0.74rem;
    }

    .app-ai-chat__table thead {
        background: #f8fafc;
    }

    .app-ai-chat__table th,
    .app-ai-chat__table td {
        padding: 0.55rem 0.65rem;
        text-align: left;
        vertical-align: top;
        border-top: 1px solid rgba(226, 232, 240, 0.9);
        color: #334155;
        white-space: normal;
    }

    .app-ai-chat__table th {
        border-top: none;
        font-weight: 800;
        color: #0f172a;
    }

    .app-ai-chat__table a {
        color: #2563eb;
        text-decoration: none;
    }

    .app-ai-chat__table a:hover {
        text-decoration: underline;
    }

    .app-ai-chat__chart {
        margin-top: 0.85rem;
        padding: 0.75rem;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 1rem;
        background: #ffffff;
    }

    .app-ai-chat__chart-note {
        margin-top: 0.45rem;
        font-size: 0.72rem;
        color: #64748b;
    }

    #appAiChatForm {
        position: sticky;
        bottom: 0;
        padding: 0.9rem 1rem 1rem;
        border-top: 1px solid rgba(226, 232, 240, 0.95);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), #ffffff 40%);
    }

    .app-ai-chat__composer {
        display: flex;
        align-items: flex-end;
        gap: 0.7rem;
        padding: 0.7rem;
        border: 1px solid rgba(203, 213, 225, 0.95);
        border-radius: 1.2rem;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .app-ai-chat__composer textarea {
        flex: 1;
        border: none;
        background: transparent;
        resize: none;
        min-height: 2.9rem;
        max-height: 7.5rem;
        padding: 0.1rem 0;
        font-size: 0.9rem;
        line-height: 1.45;
        color: #0f172a;
        outline: none;
    }

    .app-ai-chat__send {
        flex: 0 0 auto;
        min-width: 5.4rem;
        height: 2.9rem;
        border: none;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0f766e 0%, #059669 100%);
        color: #ffffff;
        font-size: 0.8rem;
        font-weight: 800;
        box-shadow: 0 14px 28px rgba(5, 150, 105, 0.24);
    }

    .app-ai-chat__note {
        margin-top: 0.45rem;
        font-size: 0.72rem;
        color: #64748b;
        text-align: right;
    }

    @media (max-width: 1024px) {
        #appAiChatPanel {
            left: 50%;
            top: 50%;
            right: auto;
            bottom: auto;
            width: min(92vw, 760px);
            height: min(78dvh, 820px);
            transform: translate(-50%, calc(-50% + 24px)) scale(0.98);
            transform-origin: center center;
        }

        #appAiChatPanel.is-open {
            transform: translate(-50%, -50%) scale(1);
        }
    }

    @media (max-width: 640px) {
        #appAiChatFabWrap {
            right: 1rem;
            bottom: calc(1rem + env(safe-area-inset-bottom));
        }

        #appAiChatBubble {
            display: none !important;
        }

        #appAiChatFab {
            width: 3.4rem;
            height: 3.4rem;
            font-size: 1.15rem;
        }

        #appAiChatPanel {
            left: 0;
            right: 0;
            top: auto;
            bottom: 0;
            width: 100vw;
            height: min(88dvh, 760px);
            border-bottom: none;
            border-radius: 1.5rem 1.5rem 0 0;
            transform: translateY(100%);
            transform-origin: center bottom;
        }

        #appAiChatPanel.is-open {
            transform: translateY(0);
        }

        .app-ai-chat__heading {
            align-items: center;
        }

        .app-ai-chat__header-actions {
            gap: 0.3rem;
        }

        .app-ai-chat__tool {
            font-size: 0.7rem;
            padding: 0 0.65rem;
        }

        .app-ai-chat__message--user {
            margin-left: 1.2rem;
        }

        .app-ai-chat__message--assistant {
            margin-right: 1.2rem;
        }

        .app-ai-chat__composer {
            align-items: stretch;
            gap: 0.55rem;
        }

        .app-ai-chat__send {
            min-width: 4.7rem;
        }

        #appAiChatForm {
            padding-bottom: calc(1rem + env(safe-area-inset-bottom));
        }
    }
</style>

<div id="appAiChatRoot" data-endpoint="{{ route('rm.agent.ask') }}"
    data-storage-key="{{ $aiChatStorageKey }}"
    data-chart-src="https://cdn.jsdelivr.net/npm/chart.js">
    <div id="appAiChatFabWrap">
        <div id="appAiChatBubble">
            <span>ถาม AI ได้ทุกหน้า</span>
            <button type="button" id="appAiChatBubbleClose" aria-label="close">✕</button>
        </div>
        <button id="appAiChatFab" type="button" aria-label="เปิด AI chat">💬</button>
    </div>

    <div id="appAiChatOverlay"></div>

    <section id="appAiChatPanel" aria-hidden="true">
        <div class="app-ai-chat__header">
            <span class="app-ai-chat__eyebrow">AI Research Assistant</span>
            <div class="app-ai-chat__heading">
                <div>
                    <div class="app-ai-chat__title">ผู้ช่วยวิเคราะห์ข้อมูลวิจัยและ Back Office</div>
                    <div class="app-ai-chat__subtitle">ถามเรื่องโครงการ งบประมาณ FTE Outputs IRB หรือ งานนอกโครงการ ได้จากทุกหน้าของระบบ</div>
                </div>
                <div class="app-ai-chat__header-actions">
                    <button type="button" id="appAiChatSuggestToggle" class="app-ai-chat__tool">คำถามแนะนำ</button>
                    <button type="button" id="appAiChatClear" class="app-ai-chat__tool">ล้าง</button>
                    <button type="button" id="appAiChatClose" class="app-ai-chat__close" aria-label="ปิดหน้าต่างแชท">✕</button>
                </div>
            </div>
        </div>

        <div id="appAiChatSuggestPanel" hidden>
            <div class="app-ai-chat__suggest-label">เลือกคำถามตัวอย่างได้โดยไม่บังแชทหลัก</div>
            <div class="app-ai-chat__suggest-grid">
                <button type="button" class="app-ai-chat__suggest" data-suggestion="งบประมาณรวมต่อปี 2568">งบประมาณรวมต่อปี 2568</button>
                <button type="button" class="app-ai-chat__suggest" data-suggestion="FTE ปี 2568 เกิน 1">FTE ปี 2568 เกิน 1</button>
                <button type="button" class="app-ai-chat__suggest" data-suggestion="Outputs ปีนี้แยกประเภท">Outputs ปีนี้แยกประเภท</button>
                <button type="button" class="app-ai-chat__suggest" data-suggestion="IRB ใกล้หมดอายุ 30 วัน">IRB ใกล้หมดอายุ 30 วัน</button>
                <button type="button" class="app-ai-chat__suggest" data-suggestion="สรุป Back Office ปีนี้">สรุป Back Office ปีนี้</button>
                <button type="button" class="app-ai-chat__suggest" data-suggestion="งานนอกโครงการของทีมปีนี้">งานนอกโครงการของทีมปีนี้</button>
            </div>
        </div>

        <div id="appAiChatBody"></div>

        <form id="appAiChatForm">
            @csrf
            <div class="app-ai-chat__composer">
                <textarea id="appAiChatInput" rows="1"
                    placeholder="พิมพ์คำถาม เช่น สรุป Back Office ปีนี้ หรือ งบประมาณรวมปีล่าสุด"></textarea>
                <button type="submit" class="app-ai-chat__send">ส่ง</button>
            </div>
            <div class="app-ai-chat__note">Enter ส่งข้อความ, Shift+Enter ขึ้นบรรทัดใหม่</div>
        </form>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (() => {
        if (window.__appAiChatBound) return;
        window.__appAiChatBound = true;

        const root = document.getElementById('appAiChatRoot');
        if (!root) return;

        const endpoint = root.dataset.endpoint;
        const storageKey = root.dataset.storageKey;
        const chartSrc = root.dataset.chartSrc;
        const maxStoredMessages = 30;

        const overlay = document.getElementById('appAiChatOverlay');
        const panel = document.getElementById('appAiChatPanel');
        const body = document.getElementById('appAiChatBody');
        const form = document.getElementById('appAiChatForm');
        const input = document.getElementById('appAiChatInput');
        const fabWrap = document.getElementById('appAiChatFabWrap');
        const fab = document.getElementById('appAiChatFab');
        const bubble = document.getElementById('appAiChatBubble');
        const bubbleClose = document.getElementById('appAiChatBubbleClose');
        const closeBtn = document.getElementById('appAiChatClose');
        const clearBtn = document.getElementById('appAiChatClear');
        const suggestToggle = document.getElementById('appAiChatSuggestToggle');
        const suggestPanel = document.getElementById('appAiChatSuggestPanel');
        const suggestButtons = Array.from(root.querySelectorAll('[data-suggestion]'));
        const dashboardFabStack = document.getElementById('dashboardFabStack');

        const defaultState = {
            messages: [],
            draft: '',
            suggestionsOpen: false,
            bubbleDismissed: false,
        };

        let chartPromise = null;

        const cloneState = () => ({
            messages: [],
            draft: '',
            suggestionsOpen: false,
            bubbleDismissed: false,
        });

        const safeJsonParse = (value) => {
            try {
                return JSON.parse(value);
            } catch (error) {
                return null;
            }
        };

        const loadState = () => {
            try {
                const parsed = safeJsonParse(sessionStorage.getItem(storageKey));
                if (!parsed || typeof parsed !== 'object') return cloneState();
                return {
                    ...cloneState(),
                    ...parsed,
                    messages: Array.isArray(parsed.messages) ? parsed.messages : [],
                };
            } catch (error) {
                return cloneState();
            }
        };

        let state = loadState();

        const saveState = () => {
            try {
                const payload = {
                    ...state,
                    messages: Array.isArray(state.messages) ? state.messages.slice(-maxStoredMessages) : [],
                };
                sessionStorage.setItem(storageKey, JSON.stringify(payload));
            } catch (error) {}
        };

        const clearStoredState = () => {
            try {
                sessionStorage.removeItem(storageKey);
            } catch (error) {}
        };

        window.__clearAppAiChatSession = () => {
            clearStoredState();
        };

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const autoSizeInput = () => {
            if (!input) return;
            input.style.height = 'auto';
            input.style.height = `${Math.min(input.scrollHeight, 120)}px`;
        };

        const ensureChartLibrary = async () => {
            if (window.Chart) return window.Chart;
            if (chartPromise) return chartPromise;

            chartPromise = new Promise((resolve, reject) => {
                const existing = document.querySelector(`script[src="${chartSrc}"]`);
                if (existing) {
                    existing.addEventListener('load', () => resolve(window.Chart), {
                        once: true
                    });
                    existing.addEventListener('error', reject, {
                        once: true
                    });
                    if (window.Chart) resolve(window.Chart);
                    return;
                }

                const script = document.createElement('script');
                script.src = chartSrc;
                script.async = true;
                script.onload = () => resolve(window.Chart);
                script.onerror = reject;
                document.head.appendChild(script);
            });

            return chartPromise;
        };

        const scrollChatToBottom = () => {
            requestAnimationFrame(() => {
                if (!body) return;
                body.scrollTop = body.scrollHeight;
            });
        };

        const setSuggestPanelOpen = (open) => {
            state.suggestionsOpen = Boolean(open);
            suggestPanel.hidden = !state.suggestionsOpen;
            suggestToggle.textContent = state.suggestionsOpen ? 'ซ่อนคำถาม' : 'คำถามแนะนำ';
            saveState();
        };

        const setBubbleVisible = (show) => {
            if (!bubble) return;
            bubble.classList.toggle('is-show', show);
            state.bubbleDismissed = !show;
            saveState();
        };

        const setPanelOpen = (show) => {
            panel.classList.toggle('is-open', show);
            overlay.classList.toggle('is-open', show);
            fabWrap.classList.toggle('is-panel-open', show);
            panel.setAttribute('aria-hidden', show ? 'false' : 'true');
            dashboardFabStack?.classList.toggle('is-hidden-by-chat', show);

            if (show) {
                window.closeDashboardFloatingPanels?.();
                setBubbleVisible(false);
                setTimeout(() => {
                    input?.focus();
                    scrollChatToBottom();
                }, 40);
            }
        };

        const createMessage = (role) => {
            const wrap = document.createElement('section');
            wrap.className = `app-ai-chat__message app-ai-chat__message--${role === 'user' ? 'user' : 'assistant'}`;

            const label = document.createElement('div');
            label.className = 'app-ai-chat__message-label';
            label.textContent = role === 'user' ? 'คุณ' : 'AI';

            const content = document.createElement('div');
            content.className = 'app-ai-chat__message-body';

            wrap.appendChild(label);
            wrap.appendChild(content);
            body.appendChild(wrap);

            return {
                wrap,
                content
            };
        };

        const renderEmptyState = () => {
            body.innerHTML = '';
            const box = document.createElement('div');
            box.className = 'app-ai-chat__empty';
            box.innerHTML = 'ถามได้เกี่ยวกับโครงการ งบประมาณ FTE Outputs IRB และ Back Office<br>รีเฟรชหน้าแล้วบทสนทนายังอยู่ในแท็บเดิมจนกว่าจะ logout หรือปิดแท็บ';
            body.appendChild(box);
        };

        const renderTable = (container, table) => {
            if (!table || !Array.isArray(table.rows) || !table.rows.length) return;

            if (table.title) {
                const title = document.createElement('div');
                title.className = 'app-ai-chat__table-title';
                title.textContent = table.title;
                container.appendChild(title);
            }

            const wrap = document.createElement('div');
            wrap.className = 'app-ai-chat__table-wrap';

            const el = document.createElement('table');
            el.className = 'app-ai-chat__table';

            const headers = Array.isArray(table.headers) ? table.headers : [];
            const fields = Array.isArray(table.fields) ? table.fields : [];

            if (headers.length) {
                const thead = document.createElement('thead');
                thead.innerHTML = `<tr>${headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('')}</tr>`;
                el.appendChild(thead);
            }

            const tbody = document.createElement('tbody');
            table.rows.forEach((row) => {
                const tr = document.createElement('tr');
                tr.innerHTML = fields.map((field) => {
                    let value = row?.[field] ?? '-';
                    if ((field === 'project' || field === 'project_name' || field === 'task') && row?.href && row.href !== '#') {
                        value = `<a href="${escapeHtml(row.href)}" target="_blank" rel="noopener">${escapeHtml(value)}</a>`;
                    } else if (field === 'href' && row?.href && row.href !== '#') {
                        value = `<a href="${escapeHtml(row.href)}" target="_blank" rel="noopener">เปิด</a>`;
                    } else {
                        value = escapeHtml(value);
                    }
                    return `<td>${value}</td>`;
                }).join('');
                tbody.appendChild(tr);
            });

            el.appendChild(tbody);
            wrap.appendChild(el);
            container.appendChild(wrap);
        };

        const renderList = (container, list) => {
            if (!Array.isArray(list) || !list.length) return;

            const wrap = document.createElement('div');
            wrap.className = 'app-ai-chat__list';

            list.forEach((item) => {
                const link = document.createElement(item?.href ? 'a' : 'div');
                link.className = 'app-ai-chat__list-item';
                if (item?.href) {
                    link.href = item.href;
                    link.target = '_blank';
                    link.rel = 'noopener';
                }

                const title = item?.project || item?.task || item?.title || item?.id || 'ไม่ระบุ';
                const metaParts = [
                    item?.year_be ? `ปี ${item.year_be}` : null,
                    item?.pi ? `PI ${item.pi}` : null,
                    item?.funding || null,
                ].filter(Boolean);

                link.innerHTML = `
                    <div class="app-ai-chat__list-title">${escapeHtml(title)}</div>
                    <div class="app-ai-chat__list-meta">${escapeHtml(metaParts.join(' • ') || 'เปิดดูรายละเอียดเพิ่มเติม')}</div>
                `;
                wrap.appendChild(link);
            });

            container.appendChild(wrap);
        };

        const renderChart = async (container, chartSpec) => {
            if (!chartSpec) return;

            const chartWrap = document.createElement('div');
            chartWrap.className = 'app-ai-chat__chart';
            const canvas = document.createElement('canvas');
            canvas.height = 220;
            chartWrap.appendChild(canvas);
            container.appendChild(chartWrap);

            try {
                await ensureChartLibrary();
                new Chart(canvas, {
                    type: chartSpec.type || 'bar',
                    data: {
                        labels: chartSpec.labels || [],
                        datasets: chartSpec.datasets || [],
                    },
                    options: chartSpec.options || {},
                });
            } catch (error) {
                const note = document.createElement('div');
                note.className = 'app-ai-chat__chart-note';
                note.textContent = 'ไม่สามารถแสดงกราฟได้ในขณะนี้';
                chartWrap.appendChild(note);
            }
        };

        const renderAssistantPayload = async (target, payload) => {
            const reply = document.createElement('div');
            reply.innerHTML = payload?.reply || '<span class="app-ai-chat__thinking">ไม่มีข้อความตอบกลับ</span>';
            target.appendChild(reply);

            renderList(target, payload?.list);
            renderTable(target, payload?.table);
            if (payload?.chart) await renderChart(target, payload.chart);
            if (payload?.chart2) await renderChart(target, payload.chart2);
        };

        const appendUserMessage = (text, options = {}) => {
            const {
                persist = true
            } = options;
            const {
                content
            } = createMessage('user');
            content.textContent = text;
            if (persist) {
                state.messages.push({
                    role: 'user',
                    text
                });
                state.messages = state.messages.slice(-maxStoredMessages);
                saveState();
            }
            scrollChatToBottom();
        };

        const appendAssistantMessage = async (payload, options = {}) => {
            const {
                persist = true
            } = options;
            const {
                content
            } = createMessage('assistant');
            await renderAssistantPayload(content, payload);
            if (persist) {
                state.messages.push({
                    role: 'assistant',
                    payload
                });
                state.messages = state.messages.slice(-maxStoredMessages);
                saveState();
            }
            scrollChatToBottom();
        };

        const normalizePayload = (data) => ({
            reply: data?.reply || '',
            list: Array.isArray(data?.list) ? data.list : null,
            table: data?.table || null,
            chart: data?.chart || null,
            chart2: data?.chart2 || null,
        });

        const rerenderConversation = async () => {
            if (!Array.isArray(state.messages) || state.messages.length === 0) {
                renderEmptyState();
                return;
            }

            body.innerHTML = '';
            for (const message of state.messages) {
                if (message?.role === 'user') {
                    appendUserMessage(message.text || '', {
                        persist: false
                    });
                    continue;
                }
                await appendAssistantMessage(message?.payload || {
                    reply: ''
                }, {
                    persist: false
                });
            }
        };

        const clearConversation = async () => {
            state = cloneState();
            clearStoredState();
            input.value = '';
            autoSizeInput();
            setSuggestPanelOpen(false);
            if (!state.bubbleDismissed) {
                bubble?.classList.add('is-show');
            }
            await rerenderConversation();
        };

        fab?.addEventListener('click', () => setPanelOpen(!panel.classList.contains('is-open')));
        overlay?.addEventListener('click', () => setPanelOpen(false));
        closeBtn?.addEventListener('click', () => setPanelOpen(false));
        bubbleClose?.addEventListener('click', () => setBubbleVisible(false));
        clearBtn?.addEventListener('click', () => clearConversation());
        suggestToggle?.addEventListener('click', () => setSuggestPanelOpen(!state.suggestionsOpen));

        suggestButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const question = button.dataset.suggestion || button.textContent || '';
                input.value = question.trim();
                state.draft = input.value;
                saveState();
                autoSizeInput();
                setSuggestPanelOpen(false);
                form.requestSubmit();
            });
        });

        input.value = state.draft || '';
        autoSizeInput();
        input.addEventListener('input', () => {
            state.draft = input.value;
            saveState();
            autoSizeInput();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            if (event.shiftKey || event.ctrlKey || event.metaKey) return;
            event.preventDefault();
            form.requestSubmit();
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const question = (input.value || '').trim();
            if (!question) return;

            if (state.messages.length === 0) {
                body.innerHTML = '';
            }

            appendUserMessage(question);
            input.value = '';
            state.draft = '';
            saveState();
            autoSizeInput();
            setSuggestPanelOpen(false);

            const thinking = createMessage('assistant');
            thinking.content.innerHTML = '<div class="app-ai-chat__thinking">กำลังวิเคราะห์…</div>';
            scrollChatToBottom();

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        prompt: question
                    }),
                });

                const data = await response.json();
                const payload = normalizePayload(data);
                thinking.content.innerHTML = '';
                await renderAssistantPayload(thinking.content, payload);
                state.messages.push({
                    role: 'assistant',
                    payload
                });
                state.messages = state.messages.slice(-maxStoredMessages);
                saveState();
                scrollChatToBottom();
            } catch (error) {
                const payload = {
                    reply: '<span class="text-red-600">เกิดข้อผิดพลาดในการเรียกผู้ช่วย</span>'
                };
                thinking.content.innerHTML = '';
                await renderAssistantPayload(thinking.content, payload);
                state.messages.push({
                    role: 'assistant',
                    payload
                });
                state.messages = state.messages.slice(-maxStoredMessages);
                saveState();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setPanelOpen(false);
            }
        });

        if (state.bubbleDismissed) {
            bubble?.classList.remove('is-show');
        } else {
            bubble?.classList.add('is-show');
        }

        if (Array.isArray(state.messages) && state.messages.length > 0) {
            state.suggestionsOpen = false;
        }

        setSuggestPanelOpen(Boolean(state.suggestionsOpen));
        rerenderConversation();
    })();
</script>
