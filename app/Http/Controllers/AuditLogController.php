<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    protected AuditLogService $service;

    public function __construct(AuditLogService $service)
    {
        $this->service = $service;
    }

    /**
     * List audit logs (paginated JSON).
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 25);
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate($perPage);
        if ($request->wantsJson()) {
            return response()->json($logs);
        }

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
        ]);
    }

    /**
     * Show single audit log.
     */
    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        if (request()->wantsJson()) {
            return response()->json($log);
        }

        return Inertia::render('Admin/AuditLogs/Show', [
            'log' => $log,
        ]);
    }

    /**
     * Store audit log (can be used by internal callers or webhooks).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'action' => 'required|string|max:255',
            'module' => 'nullable|string|max:100',
            'old_data' => 'nullable',
            'new_data' => 'nullable',
            'ip_address' => 'nullable|string|max:50',
        ]);

        $entry = $this->service->log(
            $data['user_id'] ?? null,
            $data['action'],
            $data['module'] ?? null,
            $data['old_data'] ?? null,
            $data['new_data'] ?? null,
            $data['ip_address'] ?? $request->ip()
        );

        return response()->json($entry, 201);
    }
}
