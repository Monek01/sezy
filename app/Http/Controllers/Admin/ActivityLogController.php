<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::with('user:id,first_name,last_name,email')->latest();

        if ($request->filled('action')) {
            $query->where('action', 'like', $request->string('action') . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->string('date'));
        }

        $logs = $query->paginate(30)->withQueryString();

        $actions = AuditLog::selectRaw("SUBSTRING_INDEX(action, '.', 1) as module")
            ->distinct()
            ->pluck('module')
            ->sort()
            ->values();

        return Inertia::render('Admin/ActivityLog/Index', [
            'logs'    => $logs,
            'actions' => $actions,
            'filters' => $request->only(['action', 'user_id', 'date']),
        ]);
    }
}
