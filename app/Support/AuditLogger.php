<?php

namespace App\Support;

use App\Models\BackOfficeActionLog;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    public static function logBackOfficeAction(string $action, array $context = [], ?Request $request = null): void
    {
        if (!Schema::hasTable('back_office_action_logs')) {
            return;
        }

        $request ??= request();
        $payload = self::baseRequestPayload($request);

        BackOfficeActionLog::create(array_merge($payload, [
            'user_id' => $context['user_id'] ?? Auth::id(),
            'task_id' => $context['task_id'] ?? null,
            'entry_id' => $context['entry_id'] ?? null,
            'output_id' => $context['output_id'] ?? null,
            'action' => $action,
            'subject_type' => $context['subject_type'] ?? null,
            'subject_label' => $context['subject_label'] ?? null,
            'task_title' => $context['task_title'] ?? null,
            'entry_person_name' => $context['entry_person_name'] ?? null,
            'output_title' => $context['output_title'] ?? null,
            'changes' => $context['changes'] ?? null,
        ]));
    }

    public static function logUserActivity(string $activityType, array $context = [], ?Request $request = null): void
    {
        if (!Schema::hasTable('user_activity_logs')) {
            return;
        }

        $request ??= request();
        $payload = self::baseRequestPayload($request);

        UserActivityLog::create(array_merge($payload, [
            'user_id' => $context['user_id'] ?? Auth::id(),
            'activity_type' => $activityType,
            'subject_type' => $context['subject_type'] ?? null,
            'subject_label' => $context['subject_label'] ?? null,
            'file_name' => $context['file_name'] ?? null,
            'session_id' => $context['session_id'] ?? self::sessionId($request),
            'meta' => $context['meta'] ?? null,
        ]));
    }

    public static function logFileDownload(string $fileName, array $context = [], ?Request $request = null): void
    {
        self::logUserActivity('file_download', array_merge($context, [
            'file_name' => $fileName,
            'subject_label' => $context['subject_label'] ?? $fileName,
        ]), $request);
    }

    private static function baseRequestPayload(?Request $request): array
    {
        return [
            'route_name' => $request?->route()?->getName(),
            'http_method' => $request?->getMethod(),
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => (string) ($request?->userAgent() ?? ''),
            'device_type' => self::detectDeviceType((string) ($request?->userAgent() ?? '')),
        ];
    }

    private static function detectDeviceType(string $userAgent): string
    {
        $ua = mb_strtolower($userAgent);

        if ($ua === '') {
            return 'unknown';
        }

        if (preg_match('/bot|crawler|spider|curl|wget/i', $userAgent)) {
            return 'bot';
        }

        if (preg_match('/ipad|tablet|sm-t|tab/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/iphone|android.+mobile|mobile|opera mini|iemobile/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function sessionId(?Request $request): ?string
    {
        try {
            return $request?->hasSession() ? $request->session()->getId() : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
