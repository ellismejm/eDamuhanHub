<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')->toString()))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')->toString()))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $modules = AuditLog::query()->distinct()->orderBy('module')->pluck('module');

        return view('admin.audit-logs.index', compact('logs', 'modules'));
    }
}
