<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class UnifiedWorkspaceController extends Controller
{
    public function settings(Request $request): View
    {
        return view('admin.workspace.settings', ['role' => $request->user('admin')?->role]);
    }
}
