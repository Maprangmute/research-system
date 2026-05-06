<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
   {{-- คนที่ดาวน์โหลด (user ปัจจุบัน) --}}
<meta name="author" content="{{ $user ? trim(($user->prefix ?? '') . ' ' . $user->name) : '' }}">
{{-- ผู้สร้างโครงการ --}}
<meta name="subject" content="{{ $project->createdBy ? trim(($project->createdBy->prefix ?? '') . ' ' . $project->createdBy->name) : '' }}">

    <title>{{ $project->project_name_th }}</title>
    <style>
        @page {
            header: html_HeaderDefault;
            footer: html_Footer;
            margin-top: 92px;
            margin-bottom: 72px;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 12.8pt;
            line-height: 1.45;
            color: #1f2937;
        }
        .report-shell {
            padding-top: 4px;
            width: 100%;
            margin: 0;
        }
        .report-hero {
            border: 1px solid #dbe7f5;
            border-radius: 18px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 52%, #eef6ff 100%);
            padding: 16px 18px;
            margin-bottom: 18px;
        }
        .report-kicker {
            font-size: 11.2pt;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
        }
        .report-title {
            margin-top: 5px;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.12;
        }
        .hero-subtitle {
            margin-top: 6px;
            color: #475569;
            font-size: 13.2pt;
        }
        .hero-meta {
            margin-top: 10px;
            color: #64748b;
            font-size: 12.2pt;
        }
        .hero-inline-details {
            margin-top: 10px;
            font-size: 12pt;
            color: #475569;
            line-height: 1.5;
        }
        .hero-inline-details strong {
            color: #0f172a;
        }
        .hero-chip-row {
            margin-top: 10px;
        }
        .hero-chip {
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.4pt;
            font-weight: bold;
            border: 1px solid #dbe7f5;
            color: #334155;
            background: rgba(255, 255, 255, 0.82);
        }
        .hero-chip--type {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }
        .hero-chip--theme {
            background: #f5f3ff;
            color: #7c3aed;
            border-color: #ddd6fe;
        }
        .hero-chip--status {
            background: #ecfeff;
            color: #0f766e;
            border-color: #99f6e4;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
            margin-bottom: 16px;
        }
        .summary-grid td {
            width: 25%;
            border: 1px solid #dbe7f5;
            padding: 10px 12px;
            vertical-align: top;
            background-clip: padding-box;
        }
        .summary-grid .summary-label {
            color: #64748b;
            font-size: 11.4pt;
            font-weight: bold;
            line-height: 1.15;
        }
        .summary-grid .summary-value {
            margin-top: 10px;
            font-size: 16pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.22;
        }
        .summary-grid .summary-note {
            margin-top: 10px;
            color: #64748b;
            font-size: 11.4pt;
            line-height: 1.25;
        }
        .section-title {
            background: linear-gradient(90deg, #eff6ff 0%, #f8fafc 100%);
            color: #0F172A;
            padding: 8px 14px 8px 12px;
            margin: 18px 0 8px;
            font-weight: bold;
            font-size: 15.8pt;
            line-height: 1.2;
            border-radius: 10px;
            border: 1px solid #dbe7f5;
            border-left: 5px solid #2563eb;
        }
        .section-title--info { background: linear-gradient(90deg, #eff6ff 0%, #f8fbff 100%); border-left-color: #2563eb; }
        .section-title--timeline { background: linear-gradient(90deg, #eefcf7 0%, #f8fffc 100%); border-left-color: #059669; }
        .section-title--funding { background: linear-gradient(90deg, #fff7ed 0%, #fffbf7 100%); border-left-color: #ea580c; }
        .section-title--team { background: linear-gradient(90deg, #f5f3ff 0%, #faf8ff 100%); border-left-color: #7c3aed; }
        .section-title--progress { background: linear-gradient(90deg, #ecfeff 0%, #f7feff 100%); border-left-color: #0891b2; }
        .section-title--fte { background: linear-gradient(90deg, #fefce8 0%, #fffef8 100%); border-left-color: #ca8a04; }
        .section-title--irb { background: linear-gradient(90deg, #fff1f2 0%, #fff9fa 100%); border-left-color: #e11d48; }
        .section-title--output { background: linear-gradient(90deg, #eef2ff 0%, #f8faff 100%); border-left-color: #4f46e5; }
        .section-title--strategy { background: linear-gradient(90deg, #f0fdf4 0%, #f8fff9 100%); border-left-color: #16a34a; }
        .section-subtitle {
            margin: -2px 0 10px;
            color: #64748b;
            font-size: 12.4pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #dde6f6;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 1em;
        }
        thead {
            background: #F1F5F9;
            color: #0F172A;
        }
        tbody tr:nth-child(even) td {
            background: #fbfdff;
        }
        th {
            font-size: 1.02em;
            font-weight: bold;
        }
        .info-table th {
            width: 18%;
            background: #f8fbff;
            color: #334155;
            text-align: left;
            white-space: nowrap;
        }
        .info-table td {
            width: 32%;
        }
        .nowrap { white-space: nowrap; }
        .check {
            text-align: center;
            font-size: 1.2em;
            color: #0f766e;
        }
        .fte-bar-wrap {
            width: 100%;
            height: 10px;
            background: #E6F3F2;
            border-radius: 6px;
            overflow: hidden;
        }
        .fte-bar {
            height: 10px;
            background: #0F766E;
        }
        .outputs-table {
            table-layout: fixed;
        }
        .outputs-table th,
        .outputs-table td {
            word-break: break-word;
            white-space: normal;
        }
        .progress-overview {
            border: 1px solid #d7eaf2;
            background: linear-gradient(180deg, #f8feff 0%, #ffffff 100%);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .progress-overview-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }
        .progress-overview-grid td {
            border: 0;
            padding: 0 0 12px;
            vertical-align: top;
            font-size: 11.3pt;
            line-height: 1.65;
        }
        .progress-kicker {
            margin-bottom: 4px;
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0f766e;
        }
        .progress-headline {
            font-size: 14.2pt;
            font-weight: bold;
            line-height: 1.4;
            color: #0f172a;
        }
        .progress-meta-chip {
            display: inline-block;
            margin-right: 8px;
            margin-top: 8px;
            padding: 5px 10px;
            border-radius: 999px;
            background: #ecfeff;
            border: 1px solid #b7e8f2;
            color: #155e75;
            font-size: 10pt;
            font-weight: bold;
        }
        .progress-file {
            margin-top: 10px;
            color: #475569;
            font-size: 10.8pt;
            line-height: 1.6;
        }
        .progress-group-title {
            margin: 16px 0 8px;
            padding: 8px 12px;
            border-left: 4px solid #0891b2;
            background: #f0fbff;
            color: #0f172a;
            font-size: 12.6pt;
            font-weight: bold;
            border-radius: 8px;
        }
        .progress-card {
            border: 1px solid #dde6f6;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 10px;
            background: #ffffff;
        }
        .progress-card:nth-child(even) {
            background: #fbfdff;
        }
        .progress-card-title {
            margin: 0 0 12px;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
        }
        .progress-card-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }
        .progress-card-grid td {
            border: 0;
            padding: 0 14px 0 0;
            vertical-align: top;
        }
        .progress-card-grid td:last-child {
            padding-right: 0;
        }
        .progress-card-label {
            margin-bottom: 6px;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }
        .progress-card-body {
            font-size: 12.2pt;
            line-height: 1.8;
            color: #1e293b;
            word-break: break-word;
            white-space: normal;
        }
        .progress-card-grid--activity td:nth-child(1) { width: 34%; }
        .progress-card-grid--activity td:nth-child(2) { width: 46%; }
        .progress-card-grid--activity td:nth-child(3) { width: 20%; }
        .progress-card-grid--issues td:nth-child(1) { width: 34%; }
        .progress-card-grid--issues td:nth-child(2) { width: 48%; }
        .progress-card-grid--issues td:nth-child(3) { width: 18%; }
        .progress-empty {
            border: 1px dashed #bfd7ea;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            color: #0f766e;
            font-size: 11pt;
            background: #f8fcff;
        }
        .outputs-col-index {
            width: 5%;
            text-align: center;
        }
        .outputs-col-title {
            width: 35%;
        }
        .outputs-col-type {
            width: 14%;
        }
        .outputs-col-detail {
            width: 31%;
        }
        .outputs-col-date {
            width: 15%;
        }
        .output-title {
            font-weight: bold;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 4px;
        }
        .output-title-inline-index {
            color: #475569;
            margin-right: 12px;
        }
        .output-subline {
            margin-top: 4px;
            color: #334155;
            line-height: 1.35;
        }
        .output-subline strong {
            color: #0f172a;
        }
        .output-list {
            margin-top: 6px;
        }
        .output-card {
            border: 1px solid #dbe4f2;
            border-radius: 12px;
            padding: 11px 13px;
            margin-bottom: 12px;
            background: #fff;
            page-break-inside: avoid;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.03);
        }
        .output-card-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .output-card-grid td {
            border: none !important;
            padding: 0;
            background: transparent !important;
            vertical-align: top;
        }
        .output-card-main {
            padding-right: 12px !important;
        }
        .output-card-date {
            color: #475569;
            font-size: 11.5pt;
            margin-bottom: 8px;
            white-space: nowrap;
        }
        .output-meta {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #dbe4f2;
        }
        .output-meta-row {
            margin-top: 5px;
            line-height: 1.42;
            font-size: 12.2pt;
        }
        .output-type-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 1em;
            font-weight: bold;
            border: 1px solid transparent;
            margin-top: 2px;
        }
        .output-qr-cell {
            width: 34px;
            text-align: right;
        }
        .output-qr-wrap {
            display: inline-block;
            text-align: center;
            padding: 1px;
            border: 0;
            border-radius: 0;
            background: #ffffff;
        }
        .output-qr-wrap img {
            display: block;
            width: 24px;
            height: 24px;
        }

        .pdf-project-header {
            color: #0f172a;
            line-height: 1.2;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            hyphens: auto;
        }
        .pdf-header-line {
            font-size: 11.5pt;
            color: #64748b;
            border-bottom: 1px solid #dbe7f5;
            padding: 0 0 6px;
        }
        .pdf-footer {
            width: 100%;
            font-size: 11.5pt;
            color: #64748b;
            border-top: 1px solid #dbe7f5;
            padding-top: 5px;
        }

        .project-name-adaptive {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            hyphens: auto;
        }
        .funding-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin-top: 4px;
            margin-bottom: 14px;
        }
        .funding-grid td {
            border: none !important;
            padding: 0;
            vertical-align: top;
            background: transparent !important;
        }
        .funding-card {
            border: none;
            border-radius: 14px;
            padding: 12px 14px;
            background: linear-gradient(180deg, #fffaf5 0%, #ffffff 100%);
            min-height: 104px;
        }
        .funding-card--budget {
            background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
            border-color: #fdba74;
        }
        .funding-label {
            font-size: 11.2pt;
            color: #9a3412;
            font-weight: bold;
            line-height: 1.2;
        }
        .funding-value {
            margin-top: 6px;
            font-size: 14.8pt;
            line-height: 1.28;
            color: #0f172a;
            font-weight: bold;
        }
        .funding-note {
            margin-top: 4px;
            font-size: 11.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>
@php
    function th_date_or_dash($value) {
        if (!$value) return '-';
        try {
            \Carbon\Carbon::setLocale('th');
            $d = \Carbon\Carbon::parse($value);
            return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
        } catch (\Exception $e) {
            return $value ?: '-';
        }
    }
    function text_or_dash($value) {
        $txt = trim((string)($value ?? ''));
        return $txt !== '' ? $txt : '-';
    }
    function output_badge_style($type) {
        $text = trim((string) ($type ?? ''));
        preg_match('/^(EA\d{3})/i', $text, $match);
        $code = strtoupper($match[1] ?? 'EA999');
        $palette = [
            'EA101' => ['bg' => '#eaf2ff', 'border' => '#7aa2f7', 'text' => '#315ca8'],
            'EA102' => ['bg' => '#e9f8f1', 'border' => '#7dcfb6', 'text' => '#2f7d67'],
            'EA103' => ['bg' => '#fff1e3', 'border' => '#f7b267', 'text' => '#9a5814'],
            'EA104' => ['bg' => '#ffeaea', 'border' => '#f79d9d', 'text' => '#a64545'],
            'EA105' => ['bg' => '#f2ecfb', 'border' => '#b39ddb', 'text' => '#6e54a3'],
            'EA106' => ['bg' => '#ebf7fc', 'border' => '#7ec8e3', 'text' => '#2c7893'],
            'EA107' => ['bg' => '#eef8ee', 'border' => '#9ad29a', 'text' => '#467846'],
            'EA108' => ['bg' => '#eef0fb', 'border' => '#9fa8da', 'text' => '#404bb3'],
            'EA109' => ['bg' => '#fff3e6', 'border' => '#f6b26b', 'text' => '#9a5b1b'],
            'EA110' => ['bg' => '#fff0f6', 'border' => '#f4a6c1', 'text' => '#9a4b69'],
            'EA111' => ['bg' => '#eef3ff', 'border' => '#9bb8ff', 'text' => '#4365b1'],
            'EA112' => ['bg' => '#eaf8f5', 'border' => '#8fd3c8', 'text' => '#31796e'],
            'EA113' => ['bg' => '#f4f9e6', 'border' => '#c5d86d', 'text' => '#6b7e19'],
            'EA114' => ['bg' => '#f5eefc', 'border' => '#c3a6e8', 'text' => '#72519f'],
            'EA115' => ['bg' => '#f8f0ea', 'border' => '#d9b08c', 'text' => '#866141'],
            'EA116' => ['bg' => '#ecf7fc', 'border' => '#8ecae6', 'text' => '#2e6f8d'],
            'EA117' => ['bg' => '#eef8ee', 'border' => '#9ccc9c', 'text' => '#4d7c4d'],
            'EA118' => ['bg' => '#eef0ff', 'border' => '#a5b4fc', 'text' => '#4c59b7'],
            'EA119' => ['bg' => '#fff1e7', 'border' => '#f4b183', 'text' => '#9b6035'],
            'EA120' => ['bg' => '#fdf0f0', 'border' => '#e8a5a5', 'text' => '#9b5a5a'],
            'EA999' => ['bg' => '#ecf8f4', 'border' => '#82c7b2', 'text' => '#317562'],
        ];
        $style = $palette[$code] ?? $palette['EA999'];

        return "background: {$style['bg']}; border-color: {$style['border']}; color: {$style['text']};";
    }
    function first_url_or_null(...$values) {
        foreach ($values as $value) {
            $text = trim((string) ($value ?? ''));
            if ($text === '') continue;
            if (preg_match('/https?:\/\/[^\s<>"\']+/iu', $text, $match) === 1) {
                return $match[0];
            }
            if (preg_match('/(?:^|[\s(])((?:www\.)[^\s<>"\')]+)/iu', $text, $match) === 1) {
                return 'https://' . ltrim($match[1], '/');
            }
            if (filter_var($text, FILTER_VALIDATE_URL)) {
                return $text;
            }
            if (str_starts_with(mb_strtolower($text), 'www.')) {
                return 'https://' . ltrim($text, '/');
            }
        }
        return null;
    }
    function qr_ascii_url($text) {
        $value = trim((string) ($text ?? ''));
        if ($value === '') {
            return null;
        }

        $parts = @parse_url($value);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return $value;
        }

        $host = $parts['host'];
        if (function_exists('idn_to_ascii')) {
            $asciiHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if (is_string($asciiHost) && $asciiHost !== '') {
                $host = $asciiHost;
            }
        }

        $encodeUtf8 = static function ($part) {
            return preg_replace_callback('/[^\x21-\x7E]/u', static function ($match) {
                return rawurlencode($match[0]);
            }, (string) $part);
        };

        $path = $parts['path'] ?? '';
        if ($path !== '') {
            $leadingSlash = str_starts_with($path, '/');
            $segments = array_map(static function ($segment) {
                return rawurlencode(rawurldecode($segment));
            }, explode('/', ltrim($path, '/')));
            $path = ($leadingSlash ? '/' : '') . implode('/', $segments);
        }

        $query = isset($parts['query']) ? $encodeUtf8($parts['query']) : null;
        $fragment = isset($parts['fragment']) ? $encodeUtf8($parts['fragment']) : null;

        $authority = '';
        if (isset($parts['user'])) {
            $authority .= $parts['user'];
            if (isset($parts['pass'])) {
                $authority .= ':' . $parts['pass'];
            }
            $authority .= '@';
        }
        $authority .= $host;
        if (isset($parts['port'])) {
            $authority .= ':' . $parts['port'];
        }

        return $parts['scheme'] . '://' . $authority . $path
            . ($query !== null && $query !== '' ? '?' . $query : '')
            . ($fragment !== null && $fragment !== '' ? '#' . $fragment : '');
    }
    function qr_data_uri($text) {
        $value = qr_ascii_url($text);
        if ($value === '' || !class_exists(\BaconQrCode\Writer::class)) {
            return null;
        }
        try {
            $renderer = new \BaconQrCode\Renderer\GDLibRenderer(120);
            $writer = new \BaconQrCode\Writer($renderer);
            $png = $writer->writeString($value);
            return 'data:image/png;base64,' . base64_encode($png);
        } catch (\Throwable $e) {
            return null;
        }
    }
    if (!function_exists('adaptive_project_font_pt')) {
        function adaptive_project_font_pt($value) {
            $len = mb_strlen(trim((string) ($value ?? '')));
            if ($len >= 170) return 11.5;
            if ($len >= 140) return 12;
            if ($len >= 110) return 13;
            if ($len >= 85) return 14;
            return 15;
        }
    }

    $projectTitle = text_or_dash($project->project_name_th);
    $projectTitleFontPt = adaptive_project_font_pt($projectTitle);
    $ownerName = trim((string) (($project->createdBy->prefix ?? '') . ' ' . ($project->createdBy->name ?? '')));
    $projectStatus = text_or_dash($project->planning_status);
    $headerLogoPath = public_path('images/sihp-logo.png');
    $headerLogo = file_exists($headerLogoPath) ? 'file://' . $headerLogoPath : null;
@endphp
{{-- HEADER --}}
<htmlpageheader name="HeaderFirst">
    <div class="pdf-header-line">
        <table style="width:100%; border-collapse:collapse; margin:0; border:none;">
            <tr>
                <td style="width:58%; border:none; padding:0; text-align:left; vertical-align:middle;">
                    <table style="border-collapse:collapse; width:auto; margin:0; border:none;">
                        <tr>
                            @if ($headerLogo)
                                <td style="border:none; padding:0 7px 0 0; vertical-align:middle;">
                                    <img src="{{ $headerLogo }}" alt="SiHP Logo" style="height:18px; width:auto; vertical-align:middle;">
                                </td>
                            @endif
                            <td style="border:none; padding:0; vertical-align:middle; font-size:13.2pt; color:#0f172a; font-weight:bold;">
                                SiHP Research Database Management System
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:42%; border:none; padding:0; text-align:right; vertical-align:middle; font-size:13.6pt; font-weight:bold; color:#0f172a;">
                    Project Report
                </td>
            </tr>
        </table>
    </div>
</htmlpageheader>

<htmlpageheader name="HeaderDefault">
    <div class="pdf-header-line">
        <table style="width:100%; border-collapse:collapse; margin:0; border:none;">
            <tr>
                <td style="width:58%; border:none; padding:0; text-align:left; vertical-align:middle;">
                    <table style="border-collapse:collapse; width:auto; margin:0; border:none;">
                        <tr>
                            @if ($headerLogo)
                                <td style="border:none; padding:0 7px 0 0; vertical-align:middle;">
                                    <img src="{{ $headerLogo }}" alt="SiHP Logo" style="height:18px; width:auto; vertical-align:middle;">
                                </td>
                            @endif
                            <td style="border:none; padding:0; vertical-align:middle; font-size:13.2pt; color:#0f172a; font-weight:bold;">
                                SiHP Research Database Management System
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:42%; border:none; padding:0; text-align:right; vertical-align:middle; font-size:13.6pt; font-weight:bold; color:#0f172a;">
                    Project Report
                </td>
            </tr>
        </table>
    </div>
    <div class="pdf-project-header" style="font-size: {{ $projectTitleFontPt }}pt; padding: 6px 0 0;">
        {{ $projectTitle }}
    </div>
</htmlpageheader>

{{-- FOOTER --}}
<htmlpagefooter name="Footer">
    @php
        use Carbon\Carbon;
        $carbon = Carbon::now()->setTimezone('Asia/Bangkok')->locale('th');
        $carbon->settings(['formatFunction' => 'translatedFormat']);
        $thaiDateTime = $carbon->format('j F Y เวลา H:i');
    @endphp

    <div class="pdf-footer">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none; padding: 0; font-size: 11pt; color: #64748b;">
                    ผู้สร้างรายงาน {{ $user->prefix }} {{ $user->name ?? 'ผู้ใช้ระบบ' }} · {{ $thaiDateTime }}
                </td>
                <td style="border: none; padding: 0; text-align: right; font-size: 11pt; color: #64748b;">
                    หน้า {PAGENO} / {nbpg}
                </td>
            </tr>
        </table>
    </div>
</htmlpagefooter>

<sethtmlpageheader name="HeaderFirst" value="on" show-this-page="1" />
<div class="report-shell">
    <div class="report-hero">
        <div class="report-kicker">Project Report</div>
        <div class="report-title" style="font-size: {{ $projectTitleFontPt + 1 }}pt;">
            {{ $projectTitle }}
        </div>
        <div class="hero-subtitle">
            {{ text_or_dash($project->project_name_en) }}
        </div>
        <div class="hero-chip-row">
            <span class="hero-chip hero-chip--type">{{ text_or_dash($project->project_type) }}</span>
            <span class="hero-chip hero-chip--theme">{{ text_or_dash($project->theme) }}</span>
            <span class="hero-chip hero-chip--status">{{ $projectStatus }}</span>
        </div>
        <div class="hero-meta">
            ผู้สร้างโครงการ: {{ text_or_dash($ownerName) }} |
            สถานะโครงการ: {{ text_or_dash($project->planning_status) }} |
            ช่วงเวลาโครงการ: {{ th_date_or_dash($project->start_date) }} ถึง {{ th_date_or_dash($project->end_date) }}
        </div>
        <div class="hero-inline-details">
            <strong>ลักษณะโครงการ:</strong> {{ text_or_dash($project->project_list) }}
            |
            <strong>ขนาดโครงการ / ปีโครงการ:</strong> {{ text_or_dash($project->size) }} / {{ text_or_dash($project->start_year) }}
            |
            <strong>รหัสโครงการ R:</strong> {{ text_or_dash($project->r_code) }}
            |
            <strong>เอกสารโครงการ (Proposal / IRB):</strong> {{ text_or_dash($project->shortcut_link) }}
        </div>
    </div>

    <table class="summary-grid">
        <tr>
            <td style="background:#eff6ff;">
                <div class="summary-label">กลุ่มเนื้อหาหลักของงาน (Content/Theme)</div>
                <div class="summary-value" style="font-size:15pt;">{{ text_or_dash($project->project_type) }}</div>
                <div class="summary-note">Content/Theme ของโครงการ</div>
            </td>
            <td style="background:#f5f3ff;">
                <div class="summary-label">ประเภทของโครงการ/กิจกรรม (TYPE)</div>
                <div class="summary-value" style="font-size:15pt;">{{ text_or_dash($project->theme) }}</div>
                <div class="summary-note">กลุ่มเนื้อหาของงาน</div>
            </td>
            <td style="background:#f8fafc;">
                <div class="summary-label">Data source / Method</div>
                <div class="summary-value" style="font-size:13pt;">{{ text_or_dash($project->data_source) }}</div>
                <div class="summary-note">แหล่งที่มาของข้อมูลและวิธีการใช้ข้อมูล</div>
            </td>
            <td style="background:#ecfeff;">
                <div class="summary-label">งบประมาณ</div>
                <div class="summary-value">{{ $project->budget !== null ? number_format($project->budget, 2) : '-' }}</div>
                <div class="summary-note">บาท</div>
            </td>
        </tr>
        <tr>
            <td style="background:#fff7ed;">
                <div class="summary-label">จำนวน Outputs</div>
                <div class="summary-value">{{ number_format($project->outputs->count()) }}</div>
            </td>
        </tr>
    </table>

    {{-- SECTION B --}}
    <div class="section-title section-title--timeline">B: ระยะเวลาดำเนินโครงการ</div>
    <div class="section-subtitle">วันสำคัญของโครงการตั้งแต่เริ่มเตรียม proposal จนถึงช่วงดำเนินงานตามสัญญา</div>
    <table>
        <thead>
            <tr>
                <th>วันที่โครงการเริ่มทำงานของทีมวิจัย</th>
                <th>วันที่สิ้นสุดการทำงานของทีมวิจัย</th>
                <th>BA001 - วันที่เริ่มเขียน Proposal</th>
                <th>BC101 - วันที่สัญญาอนุมัติ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ th_date_or_dash($project->team_start_date) }}</td>
                <td>{{ th_date_or_dash($project->team_end_date) }}</td>
                <td>{{ th_date_or_dash($project->proposal_date) }}</td>
                <td>{{ th_date_or_dash($project->contract_date) }}</td>
            </tr>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th>BC102 - วันที่โครงการเริ่ม (ตามสัญญา)</th>
                <th>BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ th_date_or_dash($project->start_date) }}</td>
                <td>{{ th_date_or_dash($project->end_date) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- SECTION C --}}
    <div class="section-title section-title--funding">C: ข้อมูลแหล่งทุน</div>
    <div class="section-subtitle">สรุปประเภทแหล่งทุน หน่วยงานผู้สนับสนุน และงบประมาณของโครงการ</div>
    <table class="funding-grid">
        <tr>
            <td style="width:18%;">
                <div class="funding-card">
                    <div class="funding-label">DA102 - ประเภทแหล่งทุน</div>
                    <div class="funding-value">{{ text_or_dash($project->funding_type) }}</div>
                </div>
            </td>
            <td style="width:28%;">
                <div class="funding-card">
                    <div class="funding-label">DA103 - ชื่อแหล่งทุน</div>
                    <div class="funding-value">{{ text_or_dash($project->funding_source) }}</div>
                </div>
            </td>
            <td style="width:16%;">
                <div class="funding-card">
                    <div class="funding-label">DA104 - รัฐบาล / เอกชน / มูลนิธิ</div>
                    <div class="funding-value">{{ text_or_dash($project->funding_org_type) }}</div>
                </div>
            </td>
            <td style="width:23%;">
                <div class="funding-card">
                    <div class="funding-label">DA105 - ชื่อทุน</div>
                    <div class="funding-value">{{ text_or_dash($project->funding_name) }}</div>
                </div>
            </td>
            <td style="width:15%;">
                <div class="funding-card funding-card--budget">
                    <div class="funding-label">DA106 - งบประมาณ (บาท)</div>
                    <div class="funding-value">{{ $project->budget !== null ? number_format($project->budget, 2) : '-' }}</div>
                    <div class="funding-note">บาท</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- SECTION D: Roles and COPI --}}
    <div class="section-title section-title--team">D: รายชื่อผู้ร่วมวิจัย</div>
    <div class="section-subtitle">รายชื่อ PI และ Co-PI ที่มีบทบาทหลักในการดำเนินงานวิจัย</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">ลำดับ</th>
                <th>ชื่อ</th>
                <th>สังกัด</th>
                <th>บทบาท</th>
            </tr>
        </thead>
        <tbody>
            {{-- PI --}}
            <tr>
                <td class="nowrap">1</td>
                <td>{{ text_or_dash($project->pi_name) }}</td>
                <td>{{ text_or_dash($project->pi_affiliation) }}</td>
                <td>PI</td>
            </tr>
            {{-- COPI --}}
            @forelse ($project->copis as $i => $copi)
                <tr>
                    <td class="nowrap">{{ $i+2 }}</td>
                    <td>{{ text_or_dash($copi->name) }}</td>
                    <td>{{ text_or_dash($copi->affiliation) }}</td>
                    <td>Co-PI</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มี Co-PI</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION D2: Co-Researcher --}}
    <div class="section-title section-title--team">D2: Co-Researcher</div>
    <div class="section-subtitle">ผู้ร่วมวิจัยและผู้มีส่วนร่วมจากหน่วยงานภายในหรือภายนอก</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">ลำดับ</th>
                <th>ชื่อ</th>
                <th>สังกัด</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @forelse ($project->coResearches as $co)
                <tr>
                    <td class="nowrap">{{ $i++ }}</td>
                    <td>{{ text_or_dash($co->name) }}</td>
                    <td>{{ text_or_dash($co->affiliation) }}</td>
                    <td>{{ text_or_dash($co->unit_status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if (false)
    @php
        $latestProgressReport = ($project->progressReports ?? collect())->first();
    @endphp
    <div class="section-title">E: ความก้าวหน้าของการดำเนินงาน</div>
    @if ($latestProgressReport)
        @php
            $progressTitle = text_or_dash($latestProgressReport->progress_title ?: 'ความก้าวหน้าของการดำเนินงาน');
            $progressSubtitle = text_or_dash($latestProgressReport->progress_subtitle);
            $progressRows = collect($latestProgressReport->progress_rows ?? [])
                ->filter(function ($row) {
                    return !empty(trim((string) ($row['plan'] ?? ''))) ||
                        !empty(trim((string) ($row['actual'] ?? ''))) ||
                        !empty(trim((string) ($row['alignment'] ?? '')));
                })
                ->values();
            $issueRows = collect($latestProgressReport->issue_rows ?? [])
                ->filter(function ($row) {
                    return !empty(trim((string) ($row['problem'] ?? ''))) ||
                        !empty(trim((string) ($row['solution'] ?? ''))) ||
                        !empty(trim((string) ($row['consultation'] ?? '')));
                })
                ->values();
        @endphp

        <table>
            <tbody>
                <tr>
                    <td style="width: 50%;">
                        <strong>ผู้รายงาน:</strong> {{ text_or_dash($latestProgressReport->submitter_name) }}
                    </td>
                    <td style="width: 50%;">
                        <strong>วันที่รายงาน:</strong> {{ th_date_or_dash($latestProgressReport->submit_date) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>หัวข้อ:</strong> {{ $progressTitle }}
                        @if ($progressSubtitle !== '-')
                            <br><strong>คำอธิบาย:</strong> {{ $progressSubtitle }}
                        @endif
                        @if (!empty($latestProgressReport->file_info))
                            <br><strong>ไฟล์/ลิงก์:</strong> {{ text_or_dash($latestProgressReport->file_info) }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>แผนการดำเนินงาน</th>
                    <th>การดำเนินงานจริง</th>
                    <th>ความสอดคล้องของการดำเนินงานจริงกับแผนการดำเนินงาน</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($progressRows as $row)
                    <tr>
                        <td>{{ text_or_dash($row['plan'] ?? '') }}</td>
                        <td>{{ text_or_dash($row['actual'] ?? '') }}</td>
                        <td>{{ text_or_dash($row['alignment'] ?? '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="check">ไม่มีข้อมูลกิจกรรม</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>ปัญหาอุปสรรค</th>
                    <th>แนวทางการแก้ปัญหา</th>
                    <th>ประเด็นขอคำปรึกษาจากที่ประชุม</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($issueRows as $row)
                    <tr>
                        <td>{{ text_or_dash($row['problem'] ?? '') }}</td>
                        <td>{{ text_or_dash($row['solution'] ?? '') }}</td>
                        <td>{{ text_or_dash($row['consultation'] ?? '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="check">ไม่มีข้อมูลปัญหาอุปสรรค</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <tbody>
                <tr>
                    <td class="check">ไม่มีข้อมูลความก้าวหน้าของการดำเนินงาน</td>
                </tr>
            </tbody>
        </table>
    @endif
    @endif

    {{-- SECTION F: FTE --}}
    <div class="section-title section-title--fte">F: รายชื่อผู้มีส่วนร่วม (FTE)</div>
    <div class="section-subtitle">บุคลากรที่เกี่ยวข้อง กลุ่มงาน ภาระงาน และค่า FTE ของแต่ละรายการ</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">#</th>
                <th>ชื่อ</th>
                <th>กลุ่ม</th>
                <th>ภาระงาน</th>
                <th>รายละเอียดภารกิจ</th>
                <th>วันเริ่ม</th>
                <th>วันสิ้นสุด</th>
                <th class="nowrap">FTE</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->fteRoles as $i => $fte)
                <tr>
                    <td class="nowrap">{{ $i+1 }}</td>
                    <td>{{ text_or_dash($fte->name) }}</td>
                    <td>{{ text_or_dash($fte->role_group) }}</td>
                    <td>{{ text_or_dash($fte->job_detail) }}</td>
                    <td>{{ text_or_dash($fte->task_detail) }}</td>
                    <td>
                        {{ th_date_or_dash($fte->start_date) }}
                    </td>
                    <td>
                        {{ th_date_or_dash($fte->end_date) }}
                    </td>
                    <td class="nowrap">{{ text_or_dash($fte->fte_value) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title section-title--fte">F.1: สรุป FTE รายเดือน</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">เดือน</th>
                <th class="nowrap">FTE รวม</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($fteMonthlySummary ?? [] as $row)
                @php
                    $monthLabel = $row['month'] ?? '-';
                    if (preg_match('/^\d{4}-\d{2}$/', $monthLabel)) {
                        $d = \Carbon\Carbon::createFromFormat('Y-m', $monthLabel)->locale('th');
                        $monthLabel = $d->translatedFormat('F') . ' ' . ($d->year + 543);
                    }
                    $total = (float) ($row['total'] ?? 0);
                @endphp
                <tr>
                    <td class="nowrap">{{ $monthLabel }}</td>
                    <td class="nowrap">{{ number_format($total, 6) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title section-title--fte">F.2: FTE รวม 3 ปีล่าสุด</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">ปี (พ.ศ.)</th>
                <th class="nowrap">FTE รวม</th>
            </tr>
        </thead>
        <tbody>
            @php
                $yearLabels = $fteYearLabels ?? [];
                $yearData = $fteYearData ?? [];
            @endphp
            @forelse ($yearLabels as $i => $label)
                <tr>
                    <td class="nowrap">{{ $label }}</td>
                    <td class="nowrap">{{ number_format($yearData[$i] ?? 0, 6) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION G: IRB --}}
    <div class="section-title section-title--irb">G: รหัส IRB</div>
    <div class="section-subtitle">รายการรหัสรับรองจริยธรรม หน่วยงาน และวันหมดอายุที่เกี่ยวข้องกับโครงการ</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">#</th>
                <th>รหัส</th>
                <th>หน่วยงาน</th>
                <th>วันหมดอายุ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->irbs as $i => $irb)
                <tr>
                    <td class="nowrap">{{ $i+1 }}</td>
                    <td>{{ text_or_dash($irb->code) }}</td>
                    <td>{{ text_or_dash($irb->source) }}</td>
                    <td>
                        {{ th_date_or_dash($irb->exp_date) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION H: Outputs --}}
    <div class="section-title section-title--output">H: ผลงานวิจัย (Outputs)</div>
    <div class="section-subtitle">สรุปผลงานที่เกิดจากโครงการ พร้อมประเภท รายละเอียด และข้อมูลอ้างอิงสำคัญ</div>
    <div class="output-list">
        @forelse ($project->outputs as $i => $output)
            @php
                $outputLink = first_url_or_null($output->project_output ?? null, $output->output ?? null);
                $outputQr = $outputLink ? qr_data_uri($outputLink) : null;
            @endphp
            <div class="output-card">
                <table class="output-card-grid">
                    <tr>
                        <td class="output-card-main">
                            <div class="output-card-date">{{ th_date_or_dash($output->year) }}</div>
                            <div class="output-title"><span class="output-title-inline-index">{{ $i + 1 }}.</span>{{ text_or_dash($output->title) }}</div>
                            <div class="output-type-badge" style="{{ output_badge_style($output->type) }}">{{ text_or_dash($output->type) }}</div>

                            <div class="output-meta">
                                <div class="output-meta-row">
                                    <strong>รายละเอียด/ลิงก์:</strong>
                                    {{ text_or_dash($output->project_output ?? $output->output ?? '') }}
                                </div>

                                @if (!empty($output->journal) || !empty($output->quartile) || $output->impact_factor !== null)
                                    <div class="output-meta-row">
                                        <strong>Journal:</strong> {{ text_or_dash($output->journal) }}
                                    </div>
                                    <div class="output-meta-row">
                                        <strong>Ranking:</strong> {{ text_or_dash($output->quartile) }}
                                        |
                                        <strong>Impact Factor:</strong> {{ text_or_dash($output->impact_factor) }}
                                    </div>
                                @endif

                                @if ((str_starts_with((string) ($output->type ?? ''), 'EA118') && !empty($output->awardRecipients) && $output->awardRecipients->count()) || (!str_starts_with((string) ($output->type ?? ''), 'EA118') && !empty($output->authors) && $output->authors->count()))
                                    <div class="output-meta-row">
                                        <strong>{{ str_starts_with((string) ($output->type ?? ''), 'EA118') ? 'Award Recipients' : 'Authors' }}:</strong>
                                        {{ str_starts_with((string) ($output->type ?? ''), 'EA118') ? $output->awardRecipients->pluck('name')->filter()->implode(', ') : $output->authors->pluck('name')->filter()->implode(', ') }}
                                    </div>
                                @endif

                                @if (!empty($output->conferences) && $output->conferences->count())
                                    <div class="output-meta-row">
                                        <strong>Conferences:</strong>
                                        {{ $output->conferences->map(function ($conference) {
                                            $parts = array_filter([
                                                $conference->name,
                                                $conference->presentation_type,
                                                $conference->location,
                                                $conference->presenterDisplay(),
                                            ]);
                                            return implode(' • ', $parts);
                                        })->filter()->implode(' | ') }}
                                    </div>
                                @endif

                                @if (!empty($output->book_title) || !empty($output->publisher_name) || !empty($output->chapter_pages) || !empty($output->isbn))
                                    <div class="output-meta-row">
                                        <strong>ชื่อหนังสือ:</strong> {{ text_or_dash($output->book_title) }}
                                    </div>
                                    <div class="output-meta-row">
                                        <strong>ชื่อสำนักพิมพ์:</strong> {{ text_or_dash($output->publisher_name) }}
                                        |
                                        <strong>ชื่อบท / เลขหน้า:</strong> {{ text_or_dash($output->chapter_pages) }}
                                        |
                                        <strong>เลข ISBN:</strong> {{ text_or_dash($output->isbn) }}
                                    </div>
                                @endif

                                @if (!empty($output->bookAuthors) && $output->bookAuthors->count())
                                    <div class="output-meta-row">
                                        <strong>Book Authors:</strong>
                                        {{ $output->bookAuthors->pluck('name')->filter()->implode(', ') }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        @if ($outputQr)
                        <td class="output-qr-cell">
                            <div class="output-qr-wrap">
                                <img src="{{ $outputQr }}" alt="QR">
                            </div>
                        </td>
                        @endif
                    </tr>
                </table>
            </div>
        @empty
            <table>
                <tbody>
                    <tr>
                        <td class="check">ไม่มีข้อมูล</td>
                    </tr>
                </tbody>
            </table>
        @endforelse
    </div>

    @include('projects.partials.progress_section_pdf', ['project' => $project])

    {{-- SECTION J: ยุทธศาสตร์ --}}
    <div class="section-title section-title--strategy">J: ยุทธศาสตร์ Siriraj Health Policy</div>
    <div class="section-subtitle">การเชื่อมโยงโครงการกับยุทธศาสตร์และเป้าหมายการดำเนินงานของหน่วย</div>
    <table>
        <thead>
            <tr>
                <th>หมวด</th>
                <th>รายการ</th>
                <th>มี</th>
            </tr>
        </thead>
        <tbody>
            @php
                $strategies = [
                    '1' => [
                        'label' => 'AA501: Capacity Building',
                        'items' => [
                            '1' => '1.1 การสรรหานักวิจัยด้านนโยบายสุขภาพที่มีความสามารถ',
                            '2' => '1.2 การพัฒนาศักยภาพฯ ผ่านการเรียนรู้จากการปฏิบัติงานจริง',
                            '3' => '1.3 การพัฒนาศักยภาพฯ ผ่านการศึกษาต่อเนื่อง',
                            '4' => '1.4 การบูรณาการกับการเรียนการสอนนักศึกษาแพทย์',
                            '5' => '1.5 การพัฒนาศักยภาพฯ ให้แก่บุคลากรศิริราช',
                        ],
                    ],
                    '2' => [
                        'label' => 'AA502: Excellence Research',
                        'items' => [
                            '1' => '2.1 การเพิ่มบทบาทของหน่วยวิจัยฯ',
                            '2' => '2.2 การสร้างความสัมพันธ์กับผู้กำหนดนโยบาย',
                            '3' => '2.3 การสรรหาและกำหนดหัวข้อวิจัย',
                        ],
                    ],
                    '3' => [
                        'label' => 'AA503: Health Policy Linkage',
                        'items' => [
                            '1' => '3.1 การสังเคราะห์องค์ความรู้ฯ จากงานวิจัยต้นแบบ',
                            '2' => '3.2 การดำเนินงานวิจัยที่มีคุณภาพ',
                            '3' => '3.3 ผลักดันให้งานวิจัยใช้ตัดสินใจนโยบาย',
                            '4' => '3.4 เชื่อมโยงนวัตกรรมกับการพัฒนานโยบาย',
                            '5' => '3.5 การสื่อสารงานวิจัยต่อสาธารณะ',
                        ],
                    ],
                    '4' => [
                        'label' => 'AA504: Partnership Engagement',
                        'items' => [
                            '1' => '4.1 การสร้างเครือข่ายงานวิจัย',
                            '2' => '4.2 การสร้างความร่วมมือฯ',
                        ],
                    ],
                    '5' => [
                        'label' => 'AA505: System Support',
                        'items' => [
                            '1' => '5.1 การพัฒนาระบบสนับสนุนการดำเนินงานวิจัย',
                            '2' => '5.2 การพัฒนาระบบสนับสนุนเส้นทางอาชีพในหน่วยวิจัย',
                        ],
                    ],
                ];
            @endphp
            @foreach ($strategies as $sKey => $strategy)
                @foreach ($strategy['items'] as $iKey => $desc)
                <tr>
                    @if ($loop->first)
                        <td rowspan="{{ count($strategy['items']) }}" style="font-weight:bold;">{{ $strategy['label'] }}</td>
                    @endif
                    <td>{{ $desc }}</td>
              <td class="check font-weight-bold" style="color: red;">
    {{-- Check if the project has this strategy item --}}
    {!! !empty($project->{'strategy_'.$sKey.'_'.$iKey}) ? '<strong style="color:red;">X</strong>' : '' !!}
</td>

                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- PAGE NUMBER (for wkhtmltopdf auto replace) --}}
    <script type="text/javascript">
        (function() {
            function subst() {
                var vars = {};
                var x = window.location.search.substring(1).split('&');
                for (var i in x) {
                    var z = x[i].split('=', 2);
                    vars[z[0]] = unescape(z[1]);
                }
                var y = ['page', 'topage', 'frompage', 'topage'];
                for (var i in y) {
                    var els = document.getElementsByClassName(y[i]);
                    for (var j = 0; j < els.length; ++j) {
                        if (y[i] === 'page') {
                            els[j].textContent = vars['page'];
                        }
                        if (y[i] === 'topage') {
                            els[j].textContent = vars['topage'];
                        }
                    }
                }
            }
            window.onload = subst;
        })();
    </script>
</div>
</body>
</html>
