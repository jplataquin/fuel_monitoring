<?php

namespace App\Http\Controllers;

use App\Models\PublicDashboardLink;
use App\Traits\DashboardDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicDashboardController extends Controller
{
    use DashboardDataTrait;

    public function show(string $slug, Request $request)
    {
        $link = PublicDashboardLink::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Default to current month for public dashboard
        $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = Carbon::now()->format('Y-m-d');

        $chartData = $this->getBudgetDashboardData($dateFrom, $dateTo);
        $assetVarianceData = $this->getAssetVarianceData($dateFrom, $dateTo);

        if ($request->ajax()) {
            return response()->json([
                'budget_html' => view('partials.dashboard-grid', compact('chartData'))->render(),
                'asset_html' => view('partials.asset-grid', compact('assetVarianceData'))->render(),
                'chart_data' => $chartData
            ]);
        }

        return view('public-dashboard', compact('chartData', 'assetVarianceData', 'dateFrom', 'dateTo', 'link'));
    }
}
