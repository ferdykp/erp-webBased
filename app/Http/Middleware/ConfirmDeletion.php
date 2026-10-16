<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConfirmDeletion
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('DELETE')) {
            $request->validate(['confirmed' => 'accepted'], ['confirmed.accepted' => 'Confirm deletion in the dialog before deleting this record.']);
        }
        return $next($request);
    }
}
