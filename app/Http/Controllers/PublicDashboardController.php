<?php

namespace App\Http\Controllers;

use App\Models\PublicDashboardLink;
use App\Traits\DashboardDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicDashboardController extends Controller
{
    use DashboardDataTrait;

    public function show(string $slug)
    {
        $link = PublicDashboardLink::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Default to current month for public dashboard
        $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = Carbon::now()->format('Y-m-d');

        $chartData = $this->getBudgetDashboardData($dateFrom, $dateTo);

        return view('public-dashboard', compact('chartData', 'dateFrom', 'dateTo', 'link'));
    }
}
