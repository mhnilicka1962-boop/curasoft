<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderByDesc('created_at');

        if ($request->filled('benutzer')) {
            $query->where(function ($q) use ($request) {
                $q->whereRaw('benutzer_email ilike ?', ['%' . $request->benutzer . '%'])
                  ->orWhereRaw('benutzer_name ilike ?', ['%' . $request->benutzer . '%']);
            });
        }
        if ($request->filled('aktion')) {
            $query->where('aktion', $request->aktion);
        }
        if ($request->filled('modell')) {
            $query->where('modell_typ', $request->modell);
        }
        if ($request->filled('von')) {
            $query->whereDate('created_at', '>=', $request->von);
        }
        if ($request->filled('bis')) {
            $query->whereDate('created_at', '<=', $request->bis);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('audit.index', compact('logs'));
    }
}
