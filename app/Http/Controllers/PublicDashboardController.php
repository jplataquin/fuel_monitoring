<?php

namespace App\Http\Controllers;

use App\Models\ChargeableAccount;
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
                'budget_html' => view('partials.dashboard-grid', compact('chartData', 'link'))->render(),
                'asset_html' => view('partials.asset-grid', compact('assetVarianceData'))->render(),
                'chart_data' => $chartData,
            ]);
        }

        return view('public-dashboard', compact('chartData', 'assetVarianceData', 'dateFrom', 'dateTo', 'link'));
    }

    public function subAccountDashboard(string $slug, ChargeableAccount $chargeableAccount)
    {
        $link = PublicDashboardLink::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if ($chargeableAccount->status !== 'Active') {
            abort(404);
        }

        $data = $this->getSubAccountDashboardData($chargeableAccount);

        $chartLabels = $data['chartLabels'];
        $remainingBalances = $data['remainingBalances'];
        $subAccountData = $data['subAccountData'];

        return view('public-sub-account-dashboard', compact('link', 'chargeableAccount', 'chartLabels', 'remainingBalances', 'subAccountData'));
    }

    public function manifest(string $slug)
    {
        $link = PublicDashboardLink::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $manifest = [
            'name' => 'Fuel Budget - '.($link->name ?? 'Shared Overview'),
            'short_name' => 'Fuel Budget',
            'description' => 'Live fuel budget and asset performance monitoring dashboard.',
            'start_url' => route('public.dashboard', $link->slug),
            'display' => 'standalone',
            'background_color' => '#1c1b1f',
            'theme_color' => '#D0BCFF',
            'orientation' => 'any',
            'icons' => [
                [
                    'src' => asset('images/logo.svg'),
                    'sizes' => '192x192',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => asset('images/logo.svg'),
                    'sizes' => '512x512',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        return response()->json($manifest);
    }
}
