<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivityLogController extends Controller
{
    private function authorizeAdmin(): void
    {
        $user = Auth::user();

        if (!$user || !$user->canViewLogs()) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = UserActivityLog::query()
            ->with('user')
            ->latest('created_at');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('subject_label', 'like', '%' . $search . '%')
                    ->orWhere('file_name', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%')
                    ->orWhere('device_type', 'like', '%' . $search . '%')
                    ->orWhere('url', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQ) use ($search) {
                        $userQ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('prefix', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($activityType = trim((string) $request->input('activity_type'))) {
            $query->where('activity_type', $activityType);
        }

        $logs = $query->paginate(50)->withQueryString();

        $activityOptions = UserActivityLog::query()
            ->select('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type');

        return view('user_activity_logs.index', [
            'logs' => $logs,
            'activityOptions' => $activityOptions,
        ]);
    }
}
