<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FuelOrder;
use Illuminate\Support\Carbon;

class RecalculateFuelOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fuel-orders:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate computed quantities for all fuel orders based on new division logic for kilometer readings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = FuelOrder::with('utilizationEntries')->get();
        $count = 0;

        foreach ($orders as $order) {
            $totalQuantity = 0;

            foreach ($order->utilizationEntries as $entry) {
                $calcType = strtolower($entry->calculation_type ?? '');
                $qty = 0;

                if (str_contains($calcType, 'kilometer')) {
                    $diff = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                    $qty = $entry->fuel_factor_km > 0 ? $diff / $entry->fuel_factor_km : 0;
                } elseif (str_contains($calcType, 'actual')) {
                    if ($entry->end_time && $entry->start_time) {
                        $start = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                        $end = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                        $hours = max(0, $start->diffInMinutes($end) / 60);
                        $qty = $hours * $entry->fuel_factor_hr;
                    }
                } elseif (str_contains($calcType, 'hour')) {
                    $hours = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                    $qty = $hours * $entry->fuel_factor_hr;
                }

                $totalQuantity += $qty;
            }

            // Also check if say_quantity was exactly equal to calculated_quantity, in which case we should update it too.
            // If they manually overrode say_quantity, we might not want to overwrite it, but usually say_quantity defaults to calculated_quantity.
            $updateData = [
                'calculated_quantity' => $totalQuantity,
            ];
            
            // Optional: If they match previously, sync them. 
            if ($order->say_quantity == $order->calculated_quantity) {
                $updateData['say_quantity'] = $totalQuantity;
            }

            $order->update($updateData);
            $count++;
        }

        $this->info("Successfully recalculated {$count} fuel orders.");
    }
}
