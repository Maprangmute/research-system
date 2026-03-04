@php
    $aiChatStorageKey = 'sihp-ai-chat:v2:user:' . (auth()->id() ?? 'guest');
    $aiChatProjectSuggestions = cache()->remember('sihp-ai-chat:project-suggestions:v1', now()->addMinutes(30), function () {
        if (!\Illuminate\Support\Facades\Schema::hasTable('projects')) return [];

        return \Illuminate\Support\Facades\DB::table('projects')
            ->select(['id', 'project_name_th', 'project_name_en', 'project_code', 'pi_name', 'start_year'])
            ->where(function ($query) {
                $query->whereNotNull('project_name_th')->where('project_name_th', '!=', '')
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNotNull('project_name_en')->where('project_name_en', '!=', '');
                    });
            })
            ->orderByDesc('start_year')
            ->orderByDesc('id')
            ->limit(320)
            ->get()
            ->map(function ($project, $index) {
                $title = trim((string)($project->project_name_th ?: $project->project_name_en ?: ''));
                if ($title === '') return null;

                $englishTitle = trim((string)($project->project_name_en ?? ''));
                $projectCode = trim((string)($project->project_code ?? ''));
                $piName = trim((string)($project->pi_name ?? ''));
                $year = trim((string)($project->start_year ?? ''));
                $aliases = array_values(array_filter(array_unique([
                    $title,
                    preg_replace('/\s+/u', '', $title),
                    $englishTitle,
                    preg_replace('/\s+/u', '', $englishTitle),
                    $projectCode,
                ])));

                $metaParts = array_filter([
                    $year !== '' ? "ปี {$year}" : null,
                    $piName !== '' ? "PI {$piName}" : null,
                ]);

                return [
                    'text' => $title,
                    'label' => \Illuminate\Support\Str::limit($title, 120, '...'),
                    'category' => 'โครงการ',
                    'kind' => 'project',
                    'priority' => max(96, 160 - (int) floor($index / 8)),
                    'aliases' => array_slice($aliases, 0, 6),
                    'featured' => false,
                    'meta' => implode(' / ', $metaParts),
                ];
            })
            ->filter()
            ->values()
            ->all();
    });
    $aiChatPersonSuggestions = cache()->remember('sihp-ai-chat:person-suggestions:v1', now()->addMinutes(30), function () {
        $normalizeKey = static function ($value): string {
            $value = trim((string) $value);
            if ($value === '') return '';
            $value = preg_replace('/\s+/u', '', $value);
            return mb_strtolower($value, 'UTF-8');
        };

        $stripPrefix = static function ($value): string {
            $value = trim((string) $value);
            if ($value === '') return '';

            $pattern = '/^(?:นายแพทย์หญิง|นายแพทย์|แพทย์หญิง|แพทย์|ทันตแพทย์หญิง|ทันตแพทย์|เภสัชกรหญิง|เภสัชกร|'
                . 'ศ\.คลินิก|ศ\.นพ\.|ศ\.พญ\.|รศ\.นพ\.|รศ\.พญ\.|ผศ\.นพ\.|ผศ\.พญ\.|'
                . 'ศ\.|รศ\.|ผศ\.|อ\.|ดร\.?|ภกญ\.|ภญ\.|ภก\.|พญ\.|นพ\.|ทพ\.ญ\.|ทพ\.|นาย|นางสาว|นาง)\s*/u';

            do {
                $next = preg_replace($pattern, '', $value);
                if ($next === null || $next === $value) break;
                $value = trim($next);
            } while ($value !== '');

            return trim(preg_replace('/\s+/u', ' ', $value));
        };

        $invalidKeys = array_filter(array_map($normalizeKey, [
            '',
            '-',
            'ไม่ระบุ',
            'n/a',
            'na',
            'unknown',
        ]));

        $peopleMap = [];

        $pushPerson = static function ($rawName, string $source, int $count = 1) use (&$peopleMap, $normalizeKey, $stripPrefix, $invalidKeys) {
            $rawName = trim((string) $rawName);
            if ($rawName === '') return;

            $queryName = $stripPrefix($rawName);
            if ($queryName === '') $queryName = $rawName;

            $queryKey = $normalizeKey($queryName);
            if ($queryKey === '' || in_array($queryKey, $invalidKeys, true)) return;
            if (mb_strlen($queryName, 'UTF-8') < 2) return;

            $parts = preg_split('/\s+/u', $queryName) ?: [];
            $aliases = array_values(array_filter(array_unique([
                $rawName,
                $queryName,
                preg_replace('/\s+/u', '', $rawName),
                preg_replace('/\s+/u', '', $queryName),
                $parts[0] ?? null,
                count($parts) > 1 ? end($parts) : null,
            ])));

            if (!isset($peopleMap[$queryKey])) {
                $peopleMap[$queryKey] = [
                    'text' => $queryName,
                    'label' => $rawName,
                    'aliases' => $aliases,
                    'sources' => [$source => true],
                    'hits' => max(1, $count),
                ];
                return;
            }

            $peopleMap[$queryKey]['hits'] += max(1, $count);
            $peopleMap[$queryKey]['sources'][$source] = true;
            $peopleMap[$queryKey]['aliases'] = array_values(array_filter(array_unique(array_merge(
                $peopleMap[$queryKey]['aliases'],
                $aliases
            ))));

            if (mb_strlen($rawName, 'UTF-8') > mb_strlen($peopleMap[$queryKey]['label'], 'UTF-8')) {
                $peopleMap[$queryKey]['label'] = $rawName;
            }
        };

        $loadGroupedNames = static function (string $table, string $column, string $source, int $limit = 180) use ($pushPerson) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) return;

            $rows = \Illuminate\Support\Facades\DB::table($table)
                ->selectRaw("{$column} as value, COUNT(*) as aggregate_count")
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->groupBy($column)
                ->orderByRaw('COUNT(*) DESC')
                ->limit($limit)
                ->get();

            foreach ($rows as $row) {
                $pushPerson($row->value ?? '', $source, (int) ($row->aggregate_count ?? 1));
            }
        };

        $loadGroupedNames('projects', 'pi_name', 'PI', 220);
        $loadGroupedNames('fte_roles', 'name', 'FTE', 220);
        $loadGroupedNames('copis', 'name', 'Co-PI', 180);
        $loadGroupedNames('co_research', 'name', 'Co-Research', 180);
        $loadGroupedNames('back_office_entries', 'person_name', 'Back Office', 260);

        return collect($peopleMap)
            ->sortByDesc(fn ($person) => (($person['hits'] ?? 0) * 10) + (count($person['sources'] ?? []) * 12))
            ->take(320)
            ->map(function ($person) {
                $sources = array_keys($person['sources'] ?? []);
                $priority = 128 + min(42, (int) ($person['hits'] ?? 0)) + (count($sources) * 8);

                return [
                    'text' => $person['text'] ?? '',
                    'label' => $person['label'] ?? ($person['text'] ?? ''),
                    'category' => 'บุคลากร',
                    'kind' => 'person',
                    'priority' => $priority,
                    'aliases' => array_values(array_slice($person['aliases'] ?? [], 0, 8)),
                    'featured' => false,
                    'meta' => implode(' / ', array_slice($sources, 0, 3)),
                ];
            })
            ->filter(fn ($person) => !empty($person['text']))
            ->values()
            ->all();
    });
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
        margin: 0.65rem 1rem 0;
        overflow: hidden;
    }

    .app-ai-chat__suggest-label {
        display: none;
    }

    .app-ai-chat__suggest-grid {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.55rem;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 0 0 0.25rem;
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.7) transparent;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
    }

    .app-ai-chat__suggest-grid::-webkit-scrollbar {
        height: 0.35rem;
    }

    .app-ai-chat__suggest-grid::-webkit-scrollbar-track {
        background: transparent;
    }

    .app-ai-chat__suggest-grid::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.65);
        border-radius: 999px;
    }

    .app-ai-chat__suggest-grid::after {
        content: '';
        flex: 0 0 0.2rem;
    }

    .app-ai-chat__suggest {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        border: 1px solid rgba(203, 213, 225, 0.92);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #0f172a;
        border-radius: 999px;
        padding: 0.52rem 0.9rem;
        font-size: 0.75rem;
        line-height: 1.2;
        text-align: left;
        white-space: nowrap;
        scroll-snap-align: start;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .app-ai-chat__suggest:hover {
        background: #ecfeff;
        border-color: #67e8f9;
        transform: translateY(-1px);
    }

    .app-ai-chat__suggest-text {
        display: inline-block;
    }

    .app-ai-chat__suggest-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 0.5rem;
        padding: 0.16rem 0.45rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.08);
        color: #0f766e;
        font-size: 0.67rem;
        font-weight: 800;
        line-height: 1;
    }

    .app-ai-chat__predict {
        margin-top: 0.6rem;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 1rem;
        background: rgba(248, 250, 252, 0.96);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .app-ai-chat__predict[hidden] {
        display: none;
    }

    .app-ai-chat__predict-head {
        padding: 0.65rem 0.8rem 0.4rem;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
    }

    .app-ai-chat__predict-list {
        display: grid;
        gap: 0.28rem;
        max-height: 13.5rem;
        overflow-y: auto;
        padding: 0 0.45rem 0.5rem;
    }

    .app-ai-chat__predict-item {
        display: grid;
        gap: 0.18rem;
        width: 100%;
        border: 1px solid transparent;
        border-radius: 0.85rem;
        background: transparent;
        padding: 0.58rem 0.72rem;
        text-align: left;
        color: #0f172a;
        transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
    }

    .app-ai-chat__predict-item:hover,
    .app-ai-chat__predict-item.is-active {
        background: #ffffff;
        border-color: #bae6fd;
        transform: translateY(-1px);
    }

    .app-ai-chat__predict-item-title {
        font-size: 0.79rem;
        font-weight: 700;
        line-height: 1.35;
        color: #0f172a;
    }

    .app-ai-chat__predict-item-meta {
        font-size: 0.7rem;
        line-height: 1.35;
        color: #64748b;
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
        width: 100%;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 0.95rem;
        background: #f8fafc;
        padding: 0.75rem 0.85rem;
        color: inherit;
        text-decoration: none;
        text-align: left;
        font: inherit;
        cursor: pointer;
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
        overflow: visible;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 1rem;
        background: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    .app-ai-chat__table-title {
        margin-top: 0.8rem;
        font-size: 0.74rem;
        font-weight: 800;
        color: #334155;
    }

    .app-ai-chat__table {
        width: max-content;
        min-width: max(100%, 42rem);
        border-collapse: collapse;
        font-size: 0.74rem;
        table-layout: auto;
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
        word-break: normal;
        overflow-wrap: anywhere;
        line-height: 1.45;
        min-width: 6rem;
    }

    .app-ai-chat__table th {
        border-top: none;
        font-weight: 800;
        color: #0f172a;
        white-space: nowrap;
        background: #f8fafc;
    }

    .app-ai-chat__table tbody tr:nth-child(even) {
        background: rgba(248, 250, 252, 0.72);
    }

    .app-ai-chat__table .app-ai-chat__col--category,
    .app-ai-chat__table .app-ai-chat__col--type,
    .app-ai-chat__table .app-ai-chat__col--status {
        min-width: 5.2rem;
    }

    .app-ai-chat__table .app-ai-chat__col--project,
    .app-ai-chat__table .app-ai-chat__col--project_name,
    .app-ai-chat__table .app-ai-chat__col--task,
    .app-ai-chat__table .app-ai-chat__col--title {
        min-width: 14rem;
    }

    .app-ai-chat__table .app-ai-chat__col--pi,
    .app-ai-chat__table .app-ai-chat__col--owner,
    .app-ai-chat__table .app-ai-chat__col--name {
        min-width: 7.5rem;
    }

    .app-ai-chat__table .app-ai-chat__col--budget,
    .app-ai-chat__table .app-ai-chat__col--fte,
    .app-ai-chat__table .app-ai-chat__col--hours,
    .app-ai-chat__table .app-ai-chat__col--year,
    .app-ai-chat__table .app-ai-chat__col--year_be,
    .app-ai-chat__table .app-ai-chat__col--count {
        min-width: 6.25rem;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .app-ai-chat__table .app-ai-chat__col--href,
    .app-ai-chat__table .app-ai-chat__col--link {
        min-width: 4.5rem;
        text-align: center;
        white-space: nowrap;
    }

    .app-ai-chat__table a {
        color: #2563eb;
        text-decoration: none;
    }

    .app-ai-chat__table a:hover {
        text-decoration: underline;
    }

    .app-ai-chat__table-wrap--stacked {
        overflow: visible;
        border: none;
        background: transparent;
        box-shadow: none;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table {
        width: 100%;
        min-width: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table thead {
        display: none;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table tbody {
        display: grid;
        gap: 0.8rem;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table tbody tr {
        display: block;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        padding: 0.15rem 0.85rem;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table tbody tr:nth-child(even) {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table td {
        display: grid;
        grid-template-columns: minmax(5.1rem, 6.8rem) minmax(0, 1fr);
        gap: 0.6rem;
        min-width: 0;
        width: 100%;
        border-top: 1px solid rgba(226, 232, 240, 0.78);
        padding: 0.58rem 0;
        text-align: left;
        align-items: start;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table td:first-child {
        border-top: none;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table td::before {
        content: attr(data-label);
        font-size: 0.69rem;
        font-weight: 800;
        line-height: 1.35;
        color: #64748b;
        text-transform: none;
        letter-spacing: 0.01em;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--project,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--project_name,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--task,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--title {
        font-weight: 700;
        color: #0f172a;
    }

    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--budget,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--fte,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--hours,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--year,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--year_be,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--count,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--href,
    .app-ai-chat__table-wrap--stacked .app-ai-chat__table .app-ai-chat__col--link {
        text-align: left;
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

        #appAiChatSuggestPanel {
            margin-top: 0.55rem;
        }

        .app-ai-chat__suggest-grid {
            gap: 0.45rem;
            padding-bottom: 0.2rem;
        }

        .app-ai-chat__suggest {
            padding: 0.48rem 0.78rem;
            font-size: 0.72rem;
        }

        .app-ai-chat__suggest-badge {
            margin-left: 0.42rem;
            font-size: 0.63rem;
        }

        .app-ai-chat__predict {
            margin-top: 0.52rem;
        }

        .app-ai-chat__predict-head {
            padding: 0.58rem 0.72rem 0.34rem;
            font-size: 0.64rem;
        }

        .app-ai-chat__predict-list {
            max-height: 11.5rem;
            padding: 0 0.38rem 0.42rem;
        }

        .app-ai-chat__predict-item {
            padding: 0.52rem 0.64rem;
        }

        .app-ai-chat__predict-item-title {
            font-size: 0.75rem;
        }

        .app-ai-chat__table {
            min-width: max(100%, 36rem);
        }

        .app-ai-chat__table .app-ai-chat__col--project,
        .app-ai-chat__table .app-ai-chat__col--project_name,
        .app-ai-chat__table .app-ai-chat__col--task,
        .app-ai-chat__table .app-ai-chat__col--title {
            min-width: 12rem;
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
                    <button type="button" id="appAiChatClear" class="app-ai-chat__tool">ล้าง</button>
                    <button type="button" id="appAiChatClose" class="app-ai-chat__close" aria-label="ปิดหน้าต่างแชท">✕</button>
                </div>
            </div>
        </div>

        <div id="appAiChatSuggestPanel" hidden>
            <div class="app-ai-chat__suggest-label">เลือกคำถามตัวอย่างได้โดยไม่บังแชทหลัก</div>
            <div id="appAiChatSuggestGrid" class="app-ai-chat__suggest-grid"></div>
        </div>

        <div id="appAiChatBody"></div>

        <form id="appAiChatForm">
            @csrf
            <div class="app-ai-chat__composer">
                <textarea id="appAiChatInput" rows="1"
                    placeholder="พิมพ์คำถามมได้เลย :D"></textarea>
                <button type="submit" class="app-ai-chat__send">ส่ง</button>
            </div>
            <div id="appAiChatPredict" class="app-ai-chat__predict" hidden></div>
            <div class="app-ai-chat__note">Enter ส่งข้อความ, Shift+Enter ขึ้นบรรทัดใหม่, Tab รับคำแนะนำ</div>
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
        const projectSuggestionSeed = @json($aiChatProjectSuggestions, JSON_UNESCAPED_UNICODE);
        const personSuggestionSeed = @json($aiChatPersonSuggestions, JSON_UNESCAPED_UNICODE);

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
        const suggestPanel = document.getElementById('appAiChatSuggestPanel');
        const suggestGrid = document.getElementById('appAiChatSuggestGrid');
        const predictBox = document.getElementById('appAiChatPredict');
        const dashboardFabStack = document.getElementById('dashboardFabStack');

        const defaultState = {
            messages: [],
            draft: '',
            bubbleDismissed: false,
        };

        let chartPromise = null;

        const cloneState = () => ({
            messages: [],
            draft: '',
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

        const normalizeSuggestText = (value) => String(value ?? '')
            .toLowerCase()
            .normalize('NFKC')
            .replace(/\s+/gu, ' ')
            .trim();

        const squashSuggestText = (value) => normalizeSuggestText(value).replace(/\s+/gu, '');

        const bangkokYearCE = (() => {
            try {
                const yearPart = new Intl.DateTimeFormat('en-US', {
                    timeZone: 'Asia/Bangkok',
                    year: 'numeric'
                }).formatToParts(new Date()).find((part) => part.type === 'year');
                return Number(yearPart?.value) || new Date().getFullYear();
            } catch (error) {
                return new Date().getFullYear();
            }
        })();

        const currentYearBE = bangkokYearCE + 543;
        const previousYearBE = currentYearBE - 1;
        const twoYearsAgoBE = currentYearBE - 2;

        const createSuggestion = (text, options = {}) => ({
            text: String(text ?? '').trim(),
            label: String(options.label ?? text ?? '').trim(),
            category: String(options.category ?? 'ทั่วไป').trim(),
            kind: String(options.kind ?? '').trim(),
            priority: Number(options.priority ?? 0),
            aliases: Array.isArray(options.aliases) ? options.aliases.filter(Boolean) : [],
            featured: Boolean(options.featured),
            meta: String(options.meta ?? '').trim(),
            yearBe: Number(options.yearBe ?? 0) || null,
        });

        const dedupeSuggestions = (items) => {
            const map = new Map();
            items.forEach((item) => {
                if (!item?.text) return;
                const key = squashSuggestText(item.text);
                const existing = map.get(key);
                if (!existing || (item.priority || 0) > (existing.priority || 0)) {
                    map.set(key, item);
                }
            });
            return Array.from(map.values());
        };

        const levenshteinDistanceLimited = (left, right, maxDistance = 2) => {
            const leftChars = Array.from(String(left ?? ''));
            const rightChars = Array.from(String(right ?? ''));
            const leftLength = leftChars.length;
            const rightLength = rightChars.length;

            if (!leftLength) return rightLength;
            if (!rightLength) return leftLength;
            if (Math.abs(leftLength - rightLength) > maxDistance) return maxDistance + 1;

            let previousRow = Array.from({
                length: rightLength + 1
            }, (_, index) => index);

            for (let leftIndex = 1; leftIndex <= leftLength; leftIndex += 1) {
                const currentRow = [leftIndex];
                let rowMin = currentRow[0];

                for (let rightIndex = 1; rightIndex <= rightLength; rightIndex += 1) {
                    const substitutionCost = leftChars[leftIndex - 1] === rightChars[rightIndex - 1] ? 0 : 1;
                    const nextValue = Math.min(
                        previousRow[rightIndex] + 1,
                        currentRow[rightIndex - 1] + 1,
                        previousRow[rightIndex - 1] + substitutionCost
                    );
                    currentRow[rightIndex] = nextValue;
                    rowMin = Math.min(rowMin, nextValue);
                }

                if (rowMin > maxDistance) return maxDistance + 1;
                previousRow = currentRow;
            }

            return previousRow[rightLength];
        };

        const extractSuggestionYearClause = (value) => {
            const raw = String(value ?? '').trim();
            const explicitWithYear = raw.match(/ปี\s*(25\d{2}|20\d{2})/u);
            if (explicitWithYear) return `ปี ${explicitWithYear[1]}`;

            const explicitBare = raw.match(/(?:^|\s)(25\d{2}|20\d{2})(?:\s|$)/u);
            if (explicitBare) return `ปี ${explicitBare[1]}`;

            const normalized = normalizeSuggestText(raw);
            if (normalized.includes('ปีที่แล้ว') || normalized.includes('ปีก่อน')) return 'ปีที่แล้ว';
            if (normalized.includes('ปีนี้') || normalized.includes('ปีล่าสุด')) return 'ปีล่าสุด';
            return 'ปีล่าสุด';
        };

        const extractSuggestionScopePrefix = (value) => {
            const normalized = normalizeSuggestText(value);
            if (normalized.includes('back office') || normalized.includes('งานนอกโครงการ')) {
                return 'สรุป Back Office ของ';
            }
            if (normalized.includes('โครงการ') || normalized.includes('research')) {
                return 'สรุปโครงการของ';
            }
            return 'สรุปงานของ';
        };

        const buildPersonSuggestionText = (item, draftText = '') => {
            const personName = String(item?.text || item?.label || '').trim();
            if (!personName) return '';

            return `${extractSuggestionScopePrefix(draftText)} ${personName} ${extractSuggestionYearClause(draftText)}`
                .replace(/\s+/gu, ' ')
                .trim();
        };

        const buildProjectSuggestionText = (item) => {
            const projectName = String(item?.text || item?.label || '').trim();
            if (!projectName) return '';
            return `ค้นหา: ${projectName}`;
        };

        const yearContexts = [{
                query: 'ปีล่าสุด',
                yearBe: currentYearBE,
                badge: String(currentYearBE),
                aliases: [`ปี ${currentYearBE}`, 'ปีนี้', 'latest year'],
                featured: true,
                weight: 38,
            },
            {
                query: 'ปีที่แล้ว',
                yearBe: previousYearBE,
                badge: String(previousYearBE),
                aliases: [`ปี ${previousYearBE}`, 'ปีก่อน', 'last year'],
                featured: true,
                weight: 34,
            },
            {
                query: `ปี ${currentYearBE}`,
                yearBe: currentYearBE,
                badge: '',
                aliases: ['ปีล่าสุด', 'ปีนี้'],
                featured: false,
                weight: 28,
            },
            {
                query: `ปี ${previousYearBE}`,
                yearBe: previousYearBE,
                badge: '',
                aliases: ['ปีที่แล้ว', 'ปีก่อน'],
                featured: false,
                weight: 24,
            },
            {
                query: `ปี ${twoYearsAgoBE}`,
                yearBe: twoYearsAgoBE,
                badge: '',
                aliases: ['ย้อนหลัง 2 ปี'],
                featured: false,
                weight: 18,
            }
        ];

        const compactYearQuestion = (prefix, yearContext) =>
            yearContext.query.startsWith('ปี ') ? `${prefix} ${yearContext.query}` : `${prefix}${yearContext.query}`;

        const spacedYearQuestion = (prefix, yearContext) =>
            `${prefix} ${yearContext.query}`.replace(/\s+/gu, ' ').trim();

        const betweenYearQuestion = (prefix, yearContext, suffix = '') =>
            `${prefix} ${yearContext.query}${suffix ? ` ${suffix}` : ''}`.replace(/\s+/gu, ' ').trim();

        const buildSuggestionCatalog = () => {
            const items = [];
            const add = (...suggestions) => suggestions.flat().forEach((suggestion) => {
                if (suggestion?.text) items.push(suggestion);
            });

            const addYearSeries = (builder, options = {}) => {
                yearContexts.forEach((yearContext, index) => {
                    add(createSuggestion(builder(yearContext), {
                        category: options.category,
                        priority: Number(options.priority ?? 0) + yearContext.weight - index,
                        aliases: [...(options.aliases || []), ...yearContext.aliases],
                        featured: Boolean(options.featured && yearContext.featured),
                        meta: options.showYearBadge === false ? '' : yearContext.badge,
                        yearBe: yearContext.yearBe,
                    }));
                });
            };

            addYearSeries((year) => compactYearQuestion('งบประมาณรวม', year), {
                category: 'งบประมาณ',
                priority: 220,
                aliases: ['budget', 'งบรวม'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('งบประมาณรวมต่อปี', year), {
                category: 'งบประมาณ',
                priority: 214,
                aliases: ['งบประมาณรายปี', 'budget per year'],
                featured: true,
            });
            addYearSeries((year) => compactYearQuestion('งบภายนอก', year), {
                category: 'งบประมาณ',
                priority: 208,
                aliases: ['ทุนภายนอก'],
                featured: true,
            });
            addYearSeries((year) => compactYearQuestion('งบภายใน', year), {
                category: 'งบประมาณ',
                priority: 204,
                aliases: ['ทุนภายใน'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('Top แหล่งทุน', year), {
                category: 'งบประมาณ',
                priority: 198,
                aliases: ['แหล่งทุนสูงสุด', 'top funding source'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('ทุนภายนอกตามแหล่งทุน', year), {
                category: 'งบประมาณ',
                priority: 194,
                aliases: ['funding source external'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('ทุนภายในตามแหล่งทุน', year), {
                category: 'งบประมาณ',
                priority: 190,
                aliases: ['funding source internal'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('เปรียบเทียบงบภายในกับงบภายนอก', year), {
                category: 'งบประมาณ',
                priority: 186,
                aliases: ['เทียบงบ', 'compare budget'],
                featured: false,
            });

            addYearSeries((year) => spacedYearQuestion('จำนวนโครงการ', year), {
                category: 'โครงการ',
                priority: 180,
                aliases: ['projects count'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('โครงการใหม่', year), {
                category: 'โครงการ',
                priority: 176,
                aliases: ['new projects'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('สถานะโครงการ', year), {
                category: 'โครงการ',
                priority: 172,
                aliases: ['project status'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('โครงการแยกตามสถานะ', year), {
                category: 'โครงการ',
                priority: 168,
                aliases: ['project by status'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('โครงการที่กำลังดำเนินการ', year), {
                category: 'โครงการ',
                priority: 164,
                aliases: ['in progress projects'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('โครงการที่ปิดโปรเจค', year), {
                category: 'โครงการ',
                priority: 160,
                aliases: ['completed projects'],
                featured: false,
            });

            addYearSeries((year) => betweenYearQuestion('Outputs', year, 'แยกประเภท'), {
                category: 'Outputs',
                priority: 212,
                aliases: ['outputs by type', 'output type'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('จำนวน Outputs', year), {
                category: 'Outputs',
                priority: 208,
                aliases: ['outputs count'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('Publication', year), {
                category: 'Outputs',
                priority: 204,
                aliases: ['publication per year'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('บทบาทผู้เขียน', year), {
                category: 'Outputs',
                priority: 200,
                aliases: ['author role'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('First author', year), {
                category: 'Outputs',
                priority: 196,
                aliases: ['first-author'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('Corresponding author', year), {
                category: 'Outputs',
                priority: 192,
                aliases: ['corresponding'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('Co-author', year), {
                category: 'Outputs',
                priority: 188,
                aliases: ['co author'],
                featured: false,
            });

            addYearSeries((year) => spacedYearQuestion('FTE ต่อคน', year), {
                category: 'FTE',
                priority: 210,
                aliases: ['fte per person'],
                featured: true,
            });
            addYearSeries((year) => `FTE ${year.query} เกิน 1`, {
                category: 'FTE',
                priority: 206,
                aliases: ['fte > 1', 'fte high'],
                featured: true,
                showYearBadge: false,
            });
            addYearSeries((year) => `FTE ${year.query} เกิน 0.5`, {
                category: 'FTE',
                priority: 202,
                aliases: ['fte > 0.5'],
                featured: false,
                showYearBadge: false,
            });
            addYearSeries((year) => spacedYearQuestion('คนที่มี FTE สูงสุด', year), {
                category: 'FTE',
                priority: 198,
                aliases: ['top fte'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('FTE รวม', year), {
                category: 'FTE',
                priority: 194,
                aliases: ['fte total'],
                featured: false,
            });

            addYearSeries((year) => spacedYearQuestion('สรุป Back Office', year), {
                category: 'Back Office',
                priority: 208,
                aliases: ['back office summary'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('งานนอกโครงการของทีม', year), {
                category: 'Back Office',
                priority: 204,
                aliases: ['ทีม back office', 'งานนอกโครงการ'],
                featured: true,
            });
            addYearSeries((year) => spacedYearQuestion('Back Office ชั่วโมงรวม', year), {
                category: 'Back Office',
                priority: 200,
                aliases: ['back office hours'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('Back Office FTE รวม', year), {
                category: 'Back Office',
                priority: 196,
                aliases: ['back office fte'],
                featured: false,
            });
            addYearSeries((year) => spacedYearQuestion('outputs ของ Back Office', year), {
                category: 'Back Office',
                priority: 192,
                aliases: ['back office outputs'],
                featured: false,
            });

            add(
                createSuggestion('IRB ใกล้หมดอายุ 30 วัน', {
                    category: 'IRB',
                    priority: 230,
                    aliases: ['irb 30 วัน', 'irb expiring 30 days'],
                    featured: true,
                }),
                createSuggestion('IRB ใกล้หมดอายุ 60 วัน', {
                    category: 'IRB',
                    priority: 226,
                    aliases: ['irb 60 วัน'],
                    featured: true,
                }),
                createSuggestion('IRB ใกล้หมดอายุ 90 วัน', {
                    category: 'IRB',
                    priority: 222,
                    aliases: ['irb 90 วัน'],
                    featured: true,
                }),
                createSuggestion('IRB ใกล้หมดอายุ 180 วัน', {
                    category: 'IRB',
                    priority: 218,
                    aliases: ['irb 180 วัน', 'irb 6 เดือน'],
                    featured: true,
                }),
                createSuggestion('IRB ที่หมดอายุแล้ว', {
                    category: 'IRB',
                    priority: 214,
                    aliases: ['irb expired'],
                    featured: false,
                }),
                createSuggestion('IRB ของโครงการไหนใกล้หมดอายุ', {
                    category: 'IRB',
                    priority: 210,
                    aliases: ['irb project expiring'],
                    featured: false,
                }),
                createSuggestion('โครงการใกล้สิ้นสุด 3 เดือน', {
                    category: 'โครงการ',
                    priority: 206,
                    aliases: ['due soon 3 months', 'red zone 3 เดือน'],
                    featured: true,
                }),
                createSuggestion('โครงการใกล้สิ้นสุด 6 เดือน', {
                    category: 'โครงการ',
                    priority: 202,
                    aliases: ['due soon 6 months'],
                    featured: true,
                }),
                createSuggestion('โครงการใกล้สิ้นสุด 9 เดือน', {
                    category: 'โครงการ',
                    priority: 198,
                    aliases: ['due soon 9 months'],
                    featured: false,
                }),
                createSuggestion('โครงการเกินกำหนด', {
                    category: 'โครงการ',
                    priority: 194,
                    aliases: ['overdue project'],
                    featured: true,
                }),
                createSuggestion('RED ZONE ทั้งหมด', {
                    category: 'โครงการ',
                    priority: 190,
                    aliases: ['red zone'],
                    featured: false,
                }),
                createSuggestion('PI ตามหน่วยงาน', {
                    category: 'บุคลากร',
                    priority: 186,
                    aliases: ['pi by unit'],
                    featured: false,
                }),
                createSuggestion('PI ทั้งหมด', {
                    category: 'บุคลากร',
                    priority: 184,
                    aliases: ['all pi', 'principal investigator ทั้งหมด'],
                    featured: true,
                }),
                createSuggestion('Co-PI ทั้งหมด', {
                    category: 'บุคลากร',
                    priority: 182,
                    aliases: ['copi', 'co-pi'],
                    featured: false,
                }),
                createSuggestion('Co-Research ตามหน่วยงาน', {
                    category: 'บุคลากร',
                    priority: 178,
                    aliases: ['coresearch', 'co research'],
                    featured: false,
                }),
                createSuggestion('Co-Research ทั้งหมด', {
                    category: 'บุคลากร',
                    priority: 176,
                    aliases: ['all co-research', 'coresearch ทั้งหมด'],
                    featured: false,
                }),
                createSuggestion('โครงการทั้งหมด', {
                    category: 'โครงการ',
                    priority: 174,
                    aliases: ['all projects', 'โปรเจคทั้งหมด'],
                    featured: false,
                }),
                createSuggestion('โหลดโครงการทั้งหมดเป็น Excel', {
                    category: 'ส่งออกไฟล์',
                    priority: 173,
                    aliases: ['export all projects excel', 'โครงการทั้งหมด excel'],
                    featured: true,
                }),
                createSuggestion('โหลดโครงการทั้งหมดเป็น PDF', {
                    category: 'ส่งออกไฟล์',
                    priority: 172,
                    aliases: ['export all projects pdf', 'โครงการทั้งหมด pdf'],
                    featured: true,
                }),
                createSuggestion('ทุนทั้งหมด', {
                    category: 'งบประมาณ',
                    priority: 170,
                    aliases: ['แหล่งทุนทั้งหมด', 'all funding'],
                    featured: true,
                }),
                createSuggestion('ประเภททุนทั้งหมด', {
                    category: 'งบประมาณ',
                    priority: 168,
                    aliases: ['all funding types'],
                    featured: false,
                }),
                createSuggestion('งบประมาณย้อนหลัง 5 ปี', {
                    category: 'งบประมาณ',
                    priority: 166,
                    aliases: ['budget 5 years'],
                    featured: false,
                }),
                createSuggestion('Outputs ทั้งหมด', {
                    category: 'Outputs',
                    priority: 164,
                    aliases: ['ผลงานทั้งหมด', 'all outputs'],
                    featured: true,
                }),
                createSuggestion('FTE ทั้งหมด', {
                    category: 'FTE',
                    priority: 162,
                    aliases: ['all fte'],
                    featured: false,
                }),
                createSuggestion('Back Office ทั้งหมด', {
                    category: 'Back Office',
                    priority: 162,
                    aliases: ['งานนอกโครงการทั้งหมด', 'all back office'],
                    featured: false,
                }),
                createSuggestion('สรุปภาพรวม dashboard', {
                    category: 'ภาพรวม',
                    priority: 160,
                    aliases: ['overview dashboard'],
                    featured: false,
                }),
                createSuggestion('ทั้งหมด', {
                    category: 'ช่วยเลือกคำสั่ง',
                    priority: 158,
                    aliases: ['อะไรทั้งหมด', 'มีอะไรทั้งหมด'],
                    featured: false,
                })
            );

            [
                'เบาหวาน',
                'ความดันโลหิตสูง',
                'ผู้สูงอายุ',
                'เด็กและเยาวชน',
                'วัคซีน',
                'สุขภาพจิต',
                'มะเร็ง',
                'PM2.5',
                'AI',
                'telemedicine',
                'อุบัติเหตุทางถนน',
                'ระบบบริการสุขภาพ',
                'โรคอ้วน',
                'บุหรี่ไฟฟ้า',
                'โรคหัวใจ',
                'โภชนาการ',
                'Long COVID',
                'โรคติดเชื้อ',
                'การเข้าถึงบริการ',
                'คุณภาพชีวิต'
            ].forEach((topic, index) => {
                add(createSuggestion(`ค้นหา: ${topic}`, {
                    category: 'ค้นหา',
                    priority: 162 - index,
                    aliases: [topic],
                    featured: index < 6,
                }));
            });

            (Array.isArray(projectSuggestionSeed) ? projectSuggestionSeed : []).forEach((project, index) => {
                add(createSuggestion(project.text, {
                    label: project.label || project.text,
                    category: project.category || 'โครงการ',
                    kind: project.kind || 'project',
                    priority: Math.max(94, Number(project.priority ?? 0) - Math.floor(index / 16)),
                    aliases: Array.isArray(project.aliases) ? project.aliases : [],
                    featured: false,
                    meta: project.meta || '',
                }));
            });

            (Array.isArray(personSuggestionSeed) ? personSuggestionSeed : []).forEach((person, index) => {
                add(createSuggestion(person.text, {
                    label: person.label || person.text,
                    category: person.category || 'บุคลากร',
                    kind: person.kind || 'person',
                    priority: Math.max(100, Number(person.priority ?? 0) - Math.floor(index / 12)),
                    aliases: Array.isArray(person.aliases) ? person.aliases : [],
                    featured: false,
                    meta: person.meta || '',
                }));
            });

            return dedupeSuggestions(items)
                .filter((item) => item.text)
                .sort((left, right) => (right.priority || 0) - (left.priority || 0) || left.text.localeCompare(right.text, 'th'));
        };

        const suggestionCatalog = buildSuggestionCatalog();
        const featuredSuggestions = suggestionCatalog.filter((item) => item.featured).slice(0, 40);
        let predictResults = [];
        let activePredictIndex = -1;

        const renderSuggestionLabel = (item) => {
            const text = `<span class="app-ai-chat__suggest-text">${escapeHtml(item.label || item.text)}</span>`;
            const badge = item.meta ? `<span class="app-ai-chat__suggest-badge">${escapeHtml(item.meta)}</span>` : '';
            return `${text}${badge}`;
        };

        const renderSuggestChips = () => {
            if (!suggestGrid) return;
            suggestGrid.innerHTML = featuredSuggestions.map((item) => `
                <button type="button" class="app-ai-chat__suggest" data-suggestion="${escapeHtml(item.text)}" data-submit-mode="send">
                    ${renderSuggestionLabel(item)}
                </button>
            `).join('');
        };

        const findSuggestionItem = (suggestion) => {
            if (suggestion && typeof suggestion === 'object') return suggestion;

            const key = squashSuggestText(suggestion);
            if (!key) return null;

            return suggestionCatalog.find((item) =>
                squashSuggestText(item?.text) === key || squashSuggestText(item?.label) === key
            ) || null;
        };

        const getProjectSourceVariants = (sourceSquashed, queryLength) => {
            const variants = new Set();
            const normalizedSource = String(sourceSquashed || '');
            if (!normalizedSource) return [];

            variants.add(normalizedSource);
            if (queryLength > 0) {
                [queryLength - 1, queryLength, queryLength + 1, queryLength + 2].forEach((length) => {
                    if (length > 0) {
                        variants.add(normalizedSource.slice(0, Math.min(normalizedSource.length, length)));
                    }
                });
            }

            return Array.from(variants).filter(Boolean);
        };

        const getApproximateSuggestionBoost = (query, source, item) => {
            const querySquashed = squashSuggestText(query);
            const sourceSquashed = squashSuggestText(source);
            if (!querySquashed || !sourceSquashed) return 0;
            if (querySquashed.length < 3 || sourceSquashed.length < 3) return 0;
            if (querySquashed === sourceSquashed) return 0;

            if ((item?.kind || '') === 'person') {
                const maxDistance = querySquashed.length <= 5 ? 1 : 2;
                const distance = levenshteinDistanceLimited(querySquashed, sourceSquashed, maxDistance);
                if (distance === 1) return 248;
                if (distance === 2) return 176;
                return 0;
            }

            if ((item?.kind || '') === 'project') {
                if (querySquashed.length < 4) return 0;
                const maxDistance = querySquashed.length <= 6 ? 1 : 2;
                const bestDistance = getProjectSourceVariants(sourceSquashed, querySquashed.length)
                    .reduce((best, variant) => Math.min(best, levenshteinDistanceLimited(querySquashed, variant, maxDistance)), Number.POSITIVE_INFINITY);

                if (bestDistance === 1) return 214;
                if (bestDistance === 2) return 154;
            }

            return 0;
        };

        const getSuggestionScore = (query, item) => {
            const normalizedQuery = normalizeSuggestText(query);
            if (!normalizedQuery) return item.priority || 0;

            const squashedQuery = squashSuggestText(normalizedQuery);
            const tokens = normalizedQuery.split(' ').filter(Boolean);
            let score = item.priority || 0;

            [item.text, item.label, item.category, ...(item.aliases || [])]
                .map((value) => normalizeSuggestText(value))
                .filter(Boolean)
                .forEach((source) => {
                    const sourceSquashed = squashSuggestText(source);
                    if (source === normalizedQuery || sourceSquashed === squashedQuery) {
                        score = Math.max(score, 520 + (item.priority || 0));
                    } else if (source.startsWith(normalizedQuery) || sourceSquashed.startsWith(squashedQuery)) {
                        score = Math.max(score, 380 + (item.priority || 0));
                    } else if (source.includes(normalizedQuery) || sourceSquashed.includes(squashedQuery)) {
                        score = Math.max(score, 300 + (item.priority || 0));
                    }

                    let tokenScore = 0;
                    tokens.forEach((token) => {
                        const tokenSquashed = squashSuggestText(token);
                        if (source.startsWith(token) || sourceSquashed.startsWith(tokenSquashed)) {
                            tokenScore += 56;
                        } else if (source.includes(token) || sourceSquashed.includes(tokenSquashed)) {
                            tokenScore += 32;
                        }
                    });
                    score = Math.max(score, tokenScore + (item.priority || 0));

                    const fuzzyBoost = getApproximateSuggestionBoost(normalizedQuery, source, item);
                    if (fuzzyBoost > 0) {
                        score = Math.max(score, fuzzyBoost + (item.priority || 0));
                    }
                });

            if (item.yearBe && normalizedQuery.includes(String(item.yearBe))) {
                score += 68;
            }

            return score;
        };

        const hidePredictSuggestions = () => {
            if (!predictBox) return;
            predictResults = [];
            activePredictIndex = -1;
            predictBox.hidden = true;
            predictBox.innerHTML = '';
        };

        const renderPredictSuggestions = (query, options = {}) => {
            if (!predictBox) return;

            const normalizedQuery = normalizeSuggestText(query);
            if (!normalizedQuery) {
                hidePredictSuggestions();
                return;
            }

            const nextResults = suggestionCatalog
                .map((item) => ({
                    ...item,
                    score: getSuggestionScore(normalizedQuery, item),
                }))
                .filter((item) => item.score >= (item.priority || 0) + 26)
                .sort((left, right) => right.score - left.score || (right.priority || 0) - (left.priority || 0))
                .slice(0, 8);

            if (!nextResults.length) {
                hidePredictSuggestions();
                return;
            }

            predictResults = nextResults;
            if (options.preserveActive !== true || activePredictIndex < 0 || activePredictIndex >= predictResults.length) {
                activePredictIndex = 0;
            }

            predictBox.hidden = false;
            predictBox.innerHTML = `
                <div class="app-ai-chat__predict-head">คำถาม รายชื่อ หรือโครงการใกล้เคียงจากที่พิมพ์</div>
                <div class="app-ai-chat__predict-list">
                    ${predictResults.map((item, index) => `
                        <button type="button"
                            class="app-ai-chat__predict-item ${index === activePredictIndex ? 'is-active' : ''}"
                            data-suggestion="${escapeHtml(item.text)}"
                            data-submit-mode="fill">
                            <span class="app-ai-chat__predict-item-title">${escapeHtml(item.label || item.text)}</span>
                            <span class="app-ai-chat__predict-item-meta">${escapeHtml([item.category, item.meta].filter(Boolean).join(' • '))}</span>
                        </button>
                    `).join('')}
                </div>
            `;
        };

        const movePredictSelection = (step) => {
            if (!predictResults.length) return;
            activePredictIndex = (activePredictIndex + step + predictResults.length) % predictResults.length;
            renderPredictSuggestions(input.value, {
                preserveActive: true
            });
        };

        const applySuggestion = (suggestion, {
            submit = false
        } = {}) => {
            const item = findSuggestionItem(suggestion);
            const nextQuestion = item?.kind === 'person'
                ? buildPersonSuggestionText(item, input.value)
                : item?.kind === 'project'
                    ? buildProjectSuggestionText(item)
                    : String(item?.text ?? suggestion ?? '').trim();
            if (!nextQuestion) return;
            input.value = nextQuestion;
            state.draft = nextQuestion;
            saveState();
            autoSizeInput();
            input.focus();
            if (submit) {
                hidePredictSuggestions();
                form.requestSubmit();
                return;
            }
            renderPredictSuggestions(nextQuestion);
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
            const resolvedFields = fields.length ? fields : Object.keys(table.rows[0] || {}).filter((field) => field !== 'href');
            const linkableFields = ['project', 'project_name', 'task', 'title'];
            const rowsHaveHref = table.rows.some((row) => row?.href && row.href !== '#');
            const toFieldClass = (value, index) => {
                const token = String(value ?? '')
                    .toLowerCase()
                    .replace(/[^a-z0-9_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                return token || `col-${index}`;
            };
            const inlineLinkField = resolvedFields.find((field) => linkableFields.includes(field));
            const visibleColumns = resolvedFields
                .map((field, index) => ({
                    field,
                    label: headers[index] || field || '-',
                    index,
                }))
                .filter((column) => !(column.field === 'href' && rowsHaveHref && inlineLinkField));
            const shouldStackTable = true;

            wrap.classList.toggle('app-ai-chat__table-wrap--stacked', shouldStackTable);

            if (headers.length) {
                const thead = document.createElement('thead');
                thead.innerHTML = `<tr>${visibleColumns.map((column) => {
                    const fieldClass = toFieldClass(column.field || column.label, column.index);
                    return `<th class="app-ai-chat__col app-ai-chat__col--${fieldClass}">${escapeHtml(column.label)}</th>`;
                }).join('')}</tr>`;
                el.appendChild(thead);
            }

            const tbody = document.createElement('tbody');
            table.rows.forEach((row) => {
                const tr = document.createElement('tr');
                tr.innerHTML = visibleColumns.map((column) => {
                    const field = column.field;
                    const fieldClass = toFieldClass(field, column.index);
                    const labelText = column.label || field || '-';
                    let value = row?.[field] ?? '-';
                    if (linkableFields.includes(field) && row?.href && row.href !== '#') {
                        value = `<a href="${escapeHtml(row.href)}" target="_blank" rel="noopener">${escapeHtml(value)}</a>`;
                    } else if (field === 'href' && row?.href && row.href !== '#') {
                        value = `<a href="${escapeHtml(row.href)}" target="_blank" rel="noopener">เปิด</a>`;
                    } else {
                        value = escapeHtml(value);
                    }
                    return `<td class="app-ai-chat__col app-ai-chat__col--${fieldClass}" data-label="${escapeHtml(labelText)}">${value}</td>`;
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
                const actionPrompt = String(item?.prompt ?? '').trim();
                const link = document.createElement(item?.href ? 'a' : (actionPrompt ? 'button' : 'div'));
                link.className = 'app-ai-chat__list-item';
                if (item?.href) {
                    link.href = item.href;
                    link.target = '_blank';
                    link.rel = 'noopener';
                } else if (actionPrompt) {
                    link.type = 'button';
                    link.dataset.chatPrompt = actionPrompt;
                    link.dataset.submitMode = String(item?.submit_mode || 'send');
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
            hidePredictSuggestions();
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
        suggestGrid?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-suggestion]');
            if (!button) return;
            applySuggestion(button.dataset.suggestion || button.textContent || '', {
                submit: (button.dataset.submitMode || 'send') === 'send'
            });
        });
        body?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-chat-prompt]');
            if (!button) return;
            applySuggestion(button.dataset.chatPrompt || '', {
                submit: (button.dataset.submitMode || 'send') === 'send'
            });
        });
        predictBox?.addEventListener('mousedown', (event) => {
            if (event.target.closest('[data-suggestion]')) {
                event.preventDefault();
            }
        });
        predictBox?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-suggestion]');
            if (!button) return;
            applySuggestion(button.dataset.suggestion || button.textContent || '', {
                submit: false
            });
        });

        input.value = state.draft || '';
        autoSizeInput();
        input.addEventListener('input', () => {
            state.draft = input.value;
            saveState();
            autoSizeInput();
            renderPredictSuggestions(input.value);
        });

        input.addEventListener('focus', () => {
            renderPredictSuggestions(input.value);
        });

        input.addEventListener('blur', () => {
            window.setTimeout(() => {
                if (predictBox?.contains(document.activeElement)) return;
                hidePredictSuggestions();
            }, 140);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' && predictResults.length) {
                event.preventDefault();
                movePredictSelection(1);
                return;
            }
            if (event.key === 'ArrowUp' && predictResults.length) {
                event.preventDefault();
                movePredictSelection(-1);
                return;
            }
            if (event.key === 'Tab' && predictResults.length && !event.shiftKey) {
                event.preventDefault();
                const selected = predictResults[activePredictIndex] || predictResults[0];
                if (selected) applySuggestion(selected.text, {
                    submit: false
                });
                return;
            }
            if (event.key !== 'Enter') return;
            if (event.shiftKey || event.ctrlKey || event.metaKey) return;
            event.preventDefault();
            hidePredictSuggestions();
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
            hidePredictSuggestions();

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
        renderSuggestChips();
        suggestPanel.hidden = featuredSuggestions.length === 0;
        renderPredictSuggestions(state.draft || '');
        rerenderConversation();
    })();
</script>
