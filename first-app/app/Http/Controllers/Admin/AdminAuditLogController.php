<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $moderatorId = (int) $request->integer('moderator_id');
        $action = trim((string) $request->string('action'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to')); 

        $auditLogsQuery = AuditLog::query()
            ->with([
                'admin:id,username,email',
                'report:id,title',
            ]);

        if ($search !== '') {
            $auditLogsQuery->where(function ($query) use ($search) {
                $query->where('action_type', 'like', "%{$search}%")
                    ->orWhere('ip_hash', 'like', "%{$search}%")
                    ->orWhereHas('admin', function ($adminQuery) use ($search) {
                        $adminQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('report', function ($reportQuery) use ($search) {
                        $reportQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($moderatorId > 0) {
            $auditLogsQuery->where('admin_id', $moderatorId);
        }

        if ($action !== '') {
            $auditLogsQuery->where('action_type', $action);
        }

        if ($dateFrom !== '') {
            $auditLogsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $auditLogsQuery->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $auditLogsQuery
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $moderators = User::query()
            ->where('role', 'admin')
            ->orderBy('username')
            ->get(['id', 'username']);

        $actions = AuditLog::query()
            ->select('action_type')
            ->distinct()
            ->orderBy('action_type')
            ->pluck('action_type');

        return view('admin.audit-logs.index', compact(
            'auditLogs',
            'moderators',
            'actions',
            'search',
            'moderatorId',
            'action',
            'dateFrom',
            'dateTo'
        ));
    }
}