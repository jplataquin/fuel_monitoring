<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\FuelOrder;
use App\Models\UtilizationEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateFuelOrder extends Component
{
    public $assets = [];

    public $asset_id = '';

    public $date_from = '';

    public $date_to = '';

    public $calculated_quantity = 0;

    public $say_quantity = '';

    public $unprocessed_entries_count = 0;

    public $unprocessed_entries = [];

    public $calculated_hours = 0;

    public $calculated_kilometers = 0;

    public $grouped_totals = [];

    public $fuel_factor_km = 0;

    public $fuel_factor_hr = 0;

    public $actual_quantity = 0;

    public function mount()
    {
        $this->assets = Asset::all();
    }

    public function updatedAssetId()
    {
        // Reset dates when a new asset is selected
        $this->date_from = '';
        $this->date_to = '';

        if ($this->asset_id) {
            $entries = UtilizationEntry::where('asset_id', $this->asset_id)
                ->whereNull('fuel_order_id')
                ->orderBy('date', 'asc')
                ->get();

            if ($entries->count() > 0) {
                $this->date_from = $entries->first()->date->format('Y-m-d');
                $this->date_to = $entries->last()->date->format('Y-m-d');
            }
        }

        $this->calculateQuantity();
    }

    public function updatedDateFrom()
    {
        $this->calculateQuantity();
    }

    public function updatedDateTo()
    {
        $this->calculateQuantity();
    }

    public function calculateQuantity()
    {
        $this->calculated_quantity = 0;
        $this->calculated_hours = 0;
        $this->calculated_kilometers = 0;
        $this->grouped_totals = [];
        $this->fuel_factor_km = 0;
        $this->fuel_factor_hr = 0;
        $this->unprocessed_entries_count = 0;
        $this->unprocessed_entries = [];

        if (! $this->asset_id || ! $this->date_from || ! $this->date_to) {
            return;
        }

        $asset = Asset::find($this->asset_id);
        if (! $asset) {
            return;
        }

        $this->fuel_factor_km = $asset->fuel_factor_km ?? 0;
        $this->fuel_factor_hr = $asset->fuel_factor_hr ?? 0;

        $dateFrom = Carbon::parse($this->date_from)->startOfDay();
        $dateTo = Carbon::parse($this->date_to)->endOfDay();

        $entries = UtilizationEntry::with('chargeableAccount')
            ->where('asset_id', $this->asset_id)
            ->whereNull('fuel_order_id')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $this->unprocessed_entries_count = $entries->count();
        $this->unprocessed_entries = [];

        foreach ($entries as $entry) {
            $calcType = strtolower($entry->calculation_type ?? '');
            $entry_calculated_quantity = 0;
            $entry_calculated_kilometers = 0;
            $entry_calculated_hours = 0;
            $accountName = $entry->chargeableAccount->name ?? 'Unassigned';
            if ($entry->subAccount) {
                $accountName .= ' - ' . $entry->subAccount->name;
            }

            if (! isset($this->grouped_totals[$accountName])) {
                $this->grouped_totals[$accountName] = [
                    'kilometers' => 0,
                    'hours' => 0,
                    'quantity' => 0,
                ];
            }

            if (str_contains($calcType, 'kilometer')) {
                $diff = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                $this->calculated_kilometers += $diff;
                $entry_calculated_kilometers = $diff;
                $entry_calculated_quantity = $this->fuel_factor_km > 0 ? $diff / $this->fuel_factor_km : 0;
                $this->calculated_quantity += $entry_calculated_quantity;

                $this->grouped_totals[$accountName]['kilometers'] += $diff;
                $this->grouped_totals[$accountName]['quantity'] += $entry_calculated_quantity;
            } elseif (str_contains($calcType, 'actual')) {
                if ($entry->end_time && $entry->start_time) {
                    $start = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                    $end = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                    $diffInHours = max(0, $start->diffInMinutes($end) / 60);
                    $this->calculated_hours += $diffInHours;
                    $entry_calculated_hours = $diffInHours;
                    $entry_calculated_quantity = $diffInHours * $this->fuel_factor_hr;
                    $this->calculated_quantity += $entry_calculated_quantity;

                    $this->grouped_totals[$accountName]['hours'] += $diffInHours;
                    $this->grouped_totals[$accountName]['quantity'] += $entry_calculated_quantity;
                }
            } elseif (str_contains($calcType, 'hour')) {
                $diff = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                $this->calculated_hours += $diff;
                $entry_calculated_hours = $diff;
                $entry_calculated_quantity = $diff * $this->fuel_factor_hr;
                $this->calculated_quantity += $entry_calculated_quantity;

                $this->grouped_totals[$accountName]['hours'] += $diff;
                $this->grouped_totals[$accountName]['quantity'] += $entry_calculated_quantity;
            }

            $this->unprocessed_entries[] = [
                'id' => $entry->id,
                'date' => $entry->date->format('M d, Y'),
                'start_time' => $entry->start_time->format('H:i'),
                'end_time' => $entry->end_time ? $entry->end_time->format('H:i') : null,
                'unbudgeted' => $entry->unbudgeted,
                'particulars' => $entry->particulars,
                'start_kilometer_reading' => $entry->start_kilometer_reading,
                'end_kilometer_reading' => $entry->end_kilometer_reading,
                'start_hour_reading' => $entry->start_hour_reading,
                'end_hour_reading' => $entry->end_hour_reading,
                'calculation_type' => $entry->calculation_type,
                'charged_to' => $accountName,
                'calculated_kilometers' => $entry_calculated_kilometers,
                'calculated_hours' => $entry_calculated_hours,
                'calculated_quantity' => $entry_calculated_quantity,
            ];
        }
    }

    public function submit()
    {
        $this->validate([
            'asset_id' => 'required|exists:assets,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'say_quantity' => 'required|numeric|min:0',
        ]);

        // Recalculate to ensure accurate data on submission
        $this->calculateQuantity();

        if ($this->unprocessed_entries_count === 0) {
            $this->addError('date_from', 'No unprocessed utilization entries found for this asset within the selected date range.');
            return;
        }

        DB::transaction(function () {
            $fuelOrder = FuelOrder::create([
                'asset_id' => $this->asset_id,
                'calculated_quantity' => $this->calculated_quantity,
                'say_quantity' => $this->say_quantity,
                'calculated_hours' => $this->calculated_hours,
                'calculated_kilometers' => $this->calculated_kilometers,
                'fuel_factor_km' => $this->fuel_factor_km,
                'fuel_factor_hr' => $this->fuel_factor_hr,
                'date_from' => $this->date_from,
                'date_to' => $this->date_to,
                'status' => 'PEND',
                'actual_quantity' => $this->actual_quantity,
                'created_by' => Auth::id(),
            ]);

            $dateFrom = Carbon::parse($this->date_from)->startOfDay();
            $dateTo = Carbon::parse($this->date_to)->endOfDay();

            UtilizationEntry::where('asset_id', $this->asset_id)
                ->whereNull('fuel_order_id')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->update([
                    'fuel_order_id' => $fuelOrder->id,
                    'fuel_factor_km' => $this->fuel_factor_km,
                    'fuel_factor_hr' => $this->fuel_factor_hr,
                ]);
        });

        session()->flash('message', 'Fuel Order created successfully.');

        return redirect()->route('fuel-orders.index');
    }

    public function render()
    {
        return view('livewire.create-fuel-order');
    }
}
