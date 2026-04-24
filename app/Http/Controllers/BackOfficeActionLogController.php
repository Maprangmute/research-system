<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeActionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackOfficeActionLogController extends Controller
{
    private function authorizeAdmin(): void
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole(User::ROLE_ADMIN)) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = BackOfficeActionLog::query()
            ->with(['user', 'task'])
            ->latest('created_at');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('subject_label', 'like', '%' . $search . '%')
                    ->orWhere('task_title', 'like', '%' . $search . '%')
                    ->orWhere('entry_person_name', 'like', '%' . $search . '%')
                    ->orWhere('output_title', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQ) use ($search) {
                        $userQ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('prefix', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($action = trim((string) $request->input('action'))) {
            $query->where('action', $action);
        }

        $logs = $query->paginate(50)->withQueryString();

        $actionOptions = BackOfficeActionLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('back_office_action_logs.index', [
            'logs' => $logs,
            'actionOptions' => $actionOptions,
        ]);
    }

    public function show(int $id)
    {
        $this->authorizeAdmin();

        $log = BackOfficeActionLog::query()
            ->with(['user', 'task', 'entry', 'output'])
            ->findOrFail($id);

        return view('back_office_action_logs.show', [
            'log' => $log,
        ]);
    }
}
