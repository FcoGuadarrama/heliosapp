<?php

namespace App\Http\Controllers;

use App\Application\DashboardOverview;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardOverview $overview): Response
    {
        return Inertia::render('Dashboard', [
            'overview' => $overview->forUser($request->user()),
        ]);
    }
}
