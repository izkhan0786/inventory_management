<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index()
    {
        return Inertia::render('AuditLog/Index', [
            'logs' => AuditLog::with('user')->latest()->paginate(50)
        ]);
    }
}
