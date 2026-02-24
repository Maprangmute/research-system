<?php

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use PDF;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with([
            // ดึงเฉพาะฟิลด์ที่ต้องใช้จาก coResearches
            'coResearches:id,project_id,name,unit_status,affiliation',

            // Eager load อื่น ๆ
            'copis',
            'fteRoles',
            'irbs',
            'progressReports',

            // สำคัญ: outputs + authors + project สำหรับฝั่ง JS
            'outputs.authors:id,output_id,name,role',
            'outputs.project:id,project_name_th', // ถ้าจะใช้ชื่อโครงการตอน drill-down
        ])
        ->get();

        // collections เดิม (ยังเก็บไว้ใช้กับ Blade ส่วนอื่น ๆ ได้)
        $copis           = $projects->flatMap->copis;
        $coResearches    = $projects->flatMap->coResearches;
        $fteRoles        = $projects->flatMap->fteRoles;
        $outputs         = $projects->flatMap->outputs;
        $irbs            = $projects->flatMap->irbs;
        $progressReports = $projects->flatMap->progressReports;

        // จัดรูป outputs สำหรับฝั่ง JS (แนวทาง A)
        $outputsForJs = $outputs->map(function ($o) {
            return [
                'id'           => $o->id,
                'project_id'   => $o->project_id,
                'project_name' => optional($o->project)->project_name_th,
                'type'         => $o->type,
                'title'        => $o->title,
                'year'         => $o->year,
                'authors'      => $o->authors->map(fn($a) => [
                    'name' => $a->name,
                    'role' => $a->role, // 'first' | 'co' | 'corresponding'
                ])->values(),
            ];
        })->values();

        $themes = Project::select('theme')->distinct()->pluck('theme');

        $view = 'dashboard';
        $forceView = $request->query('view');
        if ($forceView === 'mobile') {
            $view = 'dashboard_mobile';
        } elseif ($forceView !== 'desktop') {
            $ua = (string) $request->header('User-Agent', '');
            $isMobile = (bool) preg_match(
                '/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i',
                $ua
            );
            if ($isMobile) {
                $view = 'dashboard_mobile';
            }
        }

        return view($view, compact(
            'projects',
            'copis',
            'coResearches',
            'fteRoles',
            'outputs',          // ยังส่งตัวเดิมไปได้ (เช่นไว้ใช้ count())
            'outputsForJs',     // << เพิ่มตัวนี้สำหรับฝั่ง JS
            'irbs',
            'progressReports',
            'themes'
        ));
    }

    public function exportPdf(Request $request)
    {
        @set_time_limit(120);
        @ini_set('memory_limit', '512M');

        $user = $request->user();
        $generatedBy = $user?->name ?? 'ไม่ระบุ';

        $data = $request->validate([
            'charts' => 'array',
            'charts.*.id' => 'required|string',
            'charts.*.title' => 'required|string',
            'charts.*.image' => 'required|string',
            'overdueRows' => 'array',
            'irbRows' => 'array',
            'copiRows' => 'array',
            'projectRows' => 'array',
            'meta.generatedText' => 'required|string',
            'meta.generatedAt' => 'nullable|string',
            'meta.logo' => 'nullable|string',
        ]);

        $charts = $data['charts'] ?? [];
        $tables = [
            'overdue' => $data['overdueRows'] ?? [],
            'irb' => $data['irbRows'] ?? [],
            'copi' => $data['copiRows'] ?? [],
            'projects' => $data['projectRows'] ?? [],
        ];

        $meta = [
            'generatedBy' => $generatedBy,
            'generatedText' => $data['meta']['generatedText'] ?? '',
            'generatedAt' => $data['meta']['generatedAt'] ?? '',
            'logo' => $data['meta']['logo'] ?? null,
            'title' => 'Siriraj Health Policy Database Research',
            'subtitle' => 'Dashboard Export: กราฟและตารางสถานะโครงการ',
        ];

        $html = view('dashboard_pdf', compact('charts', 'tables', 'meta'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'THSarabunNew');

        // ✅ ปิดการรัน <script type="text/php"> (ตัด footer ที่ถูก inject แบบ dompdf)
        $options->set('isPhpEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $stamp = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        $userSlug = preg_replace('/\\s+/', '', $generatedBy);
        $filename = "SiHealthpolicy_overview_{$userSlug}_{$stamp}.pdf";

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\""
        ]);
    }
}
