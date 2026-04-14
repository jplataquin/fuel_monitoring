<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\ChargeableAccount;
use App\Models\UtilizationEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UtilizationEntryController extends Controller
{
    public function create(Request $request): View
    {
        $asset = Asset::findOrFail($request->asset_id);
        $chargeableAccounts = ChargeableAccount::where('status', 'Active')->orderBy('name', 'asc')->get();

        return view('utilization-entries.create', compact('asset', 'chargeableAccounts'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $asset = Asset::findOrFail($request->asset_id);

        $rules = [
            'asset_id' => 'required|exists:assets,id',
            'date' => 'required|date',
            'start_time' => [
                'required', 
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request, $asset) {
                    if ($asset->last_date !== null && $asset->last_time !== null && $request->date) {
                        try {
                            $assetDateTime = \Carbon\Carbon::parse($asset->last_date . ' ' . $asset->last_time);
                            $requestDateTime = \Carbon\Carbon::parse($request->date . ' ' . $value);
                            
                            if ($requestDateTime->lessThan($assetDateTime)) {
                                $fail('Date and Start Time cannot be earlier than the asset\'s last log (' . $assetDateTime->format('M d, Y H:i') . ').');
                            }
                        } catch (\Exception $e) {}
                    }
                }
            ],
            'end_time' => 'required|date_format:H:i|after:start_time',
            'driver_operator_name' => 'required|string|max:255',
            'chargeable_account_id' => 'required|exists:chargeable_accounts,id',
            'sub_account_id' => 'required|exists:sub_accounts,id',
            'reference' => 'nullable|string|max:255',
            'calculation_type' => 'required|string|in:Kilometer Reading,Hour Reading,Actual Operation Hours',
            'unbudgeted' => 'nullable',
            'particulars' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'start_kilometer_reading' => 'nullable|numeric|min:0',
            'end_kilometer_reading' => 'nullable|numeric|min:0',
            'start_hour_reading' => 'nullable|numeric|min:0',
            'end_hour_reading' => 'nullable|numeric|min:0',
        ];

        if ($request->calculation_type === 'Kilometer Reading') {
            $rules['start_kilometer_reading'] = ['required', 'numeric', 'min:0'];
            if ($asset->last_kilometer_reading !== null) {
                $rules['start_kilometer_reading'][] = 'gte:' . $asset->last_kilometer_reading;
            }
            $rules['end_kilometer_reading'] = ['required', 'numeric', 'min:0', 'gt:start_kilometer_reading'];
        } elseif ($request->calculation_type === 'Hour Reading') {
            $rules['start_hour_reading'] = ['required', 'numeric', 'min:0'];
            if ($asset->last_engine_hours !== null) {
                $rules['start_hour_reading'][] = 'gte:' . $asset->last_engine_hours;
            }
            $rules['end_hour_reading'] = ['required', 'numeric', 'min:0', 'gt:start_hour_reading'];
        }

        $validated = $request->validate($rules);

        $validated['unbudgeted'] = $request->has('unbudgeted');
        $validated['created_by'] = Auth::id();
        $validated['start_kilometer_reading'] = $validated['start_kilometer_reading'] ?? 0;
        $validated['end_kilometer_reading'] = $validated['end_kilometer_reading'] ?? 0;
        $validated['start_hour_reading'] = $validated['start_hour_reading'] ?? 0;
        $validated['end_hour_reading'] = $validated['end_hour_reading'] ?? 0;

        $validated['fuel_factor_km'] = $asset->fuel_factor_km;
        $validated['fuel_factor_hr'] = $asset->fuel_factor_hr;
        $validated['last_kilometer_reading'] = $asset->last_kilometer_reading;
        $validated['last_engine_hours'] = $asset->last_engine_hours;
        $validated['last_date'] = $asset->last_date;
        $validated['last_time'] = $asset->last_time;

        $entry = UtilizationEntry::create($validated);

        if ($request->calculation_type === 'Kilometer Reading') {
            $asset->last_kilometer_reading = $validated['end_kilometer_reading'];
            $asset->last_date = $validated['date'];
            $asset->last_time = $validated['end_time'];
            $asset->save();
        } elseif ($request->calculation_type === 'Hour Reading') {
            $asset->last_engine_hours = $validated['end_hour_reading'];
            $asset->last_date = $validated['date'];
            $asset->last_time = $validated['end_time'];
            $asset->save();
        } elseif ($request->calculation_type === 'Actual Operation Hours') {
            $asset->last_date = $validated['date'];
            $asset->last_time = $validated['end_time'];
            $asset->save();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Utilization entry created successfully.',
                'entry' => $entry,
            ]);
        }

        return redirect()->back()
            ->withInput($request->only([
                'date', 
                'start_time', 
                'end_time',
                'driver_operator_name', 
                'chargeable_account_id', 
                'reference', 
                'calculation_type'
            ]))
            ->with('status', 'Utilization entry created successfully.');
    }

    public function show(UtilizationEntry $utilizationEntry): View
    {
        $utilizationEntry->load(['asset', 'chargeableAccount', 'creator', 'updater']);
        return view('utilization-entries.show', compact('utilizationEntry'));
    }

    public function edit(UtilizationEntry $utilizationEntry): View
    {
        $user = Auth::user();
        $isAuthorized = in_array($user->role, ['administrator', 'moderator']) ||
            ($user->role === 'data_logger' && $utilizationEntry->created_at->diffInMinutes(now()) <= 5);

        if (! $isAuthorized) {
            abort(403, 'You are not authorized to edit this record or the 5-minute window has expired.');
        }

        $chargeableAccounts = ChargeableAccount::where('status', 'Active')->orderBy('name', 'asc')->get();

        return view('utilization-entries.edit', compact('utilizationEntry', 'chargeableAccounts'));
    }

    public function update(Request $request, UtilizationEntry $utilizationEntry): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $isAuthorized = in_array($user->role, ['administrator', 'moderator']) ||
            ($user->role === 'data_logger' && $utilizationEntry->created_at->diffInMinutes(now()) <= 5);

        if (! $isAuthorized) {
            abort(403, 'You are not authorized to edit this record or the 5-minute window has expired.');
        }
        
        $asset = $utilizationEntry->asset;

        // Find the next immediate utilization record for time validation
        $nextTimeEntry = \App\Models\UtilizationEntry::where('asset_id', $utilizationEntry->asset_id)
            ->where(function ($query) use ($utilizationEntry) {
                $query->where('date', '>', $utilizationEntry->getOriginal('date'))
                      ->orWhere(function ($q) use ($utilizationEntry) {
                          $q->where('date', '=', $utilizationEntry->getOriginal('date'))
                            ->where('start_time', '>', $utilizationEntry->getOriginal('start_time'));
                      });
            })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        // Find the next record with a non-zero Kilometer reading
        $nextKmEntry = \App\Models\UtilizationEntry::where('asset_id', $utilizationEntry->asset_id)
            ->where(function ($query) use ($utilizationEntry) {
                $query->where('date', '>', $utilizationEntry->getOriginal('date'))
                      ->orWhere(function ($q) use ($utilizationEntry) {
                          $q->where('date', '=', $utilizationEntry->getOriginal('date'))
                            ->where('start_time', '>', $utilizationEntry->getOriginal('start_time'));
                      });
            })
            ->where('start_kilometer_reading', '>', 0)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        // Find the next record with a non-zero Hour reading
        $nextHrEntry = \App\Models\UtilizationEntry::where('asset_id', $utilizationEntry->asset_id)
            ->where(function ($query) use ($utilizationEntry) {
                $query->where('date', '>', $utilizationEntry->getOriginal('date'))
                      ->orWhere(function ($q) use ($utilizationEntry) {
                          $q->where('date', '=', $utilizationEntry->getOriginal('date'))
                            ->where('start_time', '>', $utilizationEntry->getOriginal('start_time'));
                      });
            })
            ->where('start_hour_reading', '>', 0)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        $rules = [
            'date' => 'required|date',
            'start_time' => [
                'required', 
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request, $utilizationEntry) {
                    if ($utilizationEntry->last_date !== null && $utilizationEntry->last_time !== null && $request->date) {
                        try {
                            $lastDateString = $utilizationEntry->last_date instanceof \Carbon\Carbon ? $utilizationEntry->last_date->format('Y-m-d') : $utilizationEntry->last_date;
                            $lastDateTime = \Carbon\Carbon::parse($lastDateString . ' ' . $utilizationEntry->last_time);
                            $requestDateTime = \Carbon\Carbon::parse($request->date . ' ' . $value);
                            
                            if ($requestDateTime->lessThan($lastDateTime)) {
                                $fail('Date and Start Time cannot be earlier than the previous log (' . $lastDateTime->format('M d, Y H:i') . ').');
                            }
                        } catch (\Exception $e) {}
                    }
                }
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) use ($request, $nextTimeEntry) {
                    if ($nextTimeEntry && $request->date) {
                        try {
                            $endDateTime = \Carbon\Carbon::parse($request->date . ' ' . $value);
                            $nextDateString = $nextTimeEntry->date instanceof \Carbon\Carbon ? $nextTimeEntry->date->format('Y-m-d') : $nextTimeEntry->date;
                            $nextStartDateTime = \Carbon\Carbon::parse($nextDateString . ' ' . $nextTimeEntry->start_time);

                            if ($endDateTime->greaterThan($nextStartDateTime)) {
                                $fail('Date and End Time overlap with the next immediate record which starts at ' . $nextStartDateTime->format('M d, Y H:i') . '.');
                            }
                        } catch (\Exception $e) {}
                    }
                }
            ],
            'driver_operator_name' => 'required|string|max:255',
            'chargeable_account_id' => 'required|exists:chargeable_accounts,id',
            'sub_account_id' => 'required|exists:sub_accounts,id',
            'reference' => 'nullable|string|max:255',
            'calculation_type' => 'required|string|in:Kilometer Reading,Hour Reading,Actual Operation Hours',
            'unbudgeted' => 'nullable',
            'particulars' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'start_kilometer_reading' => 'nullable|numeric|min:0',
            'end_kilometer_reading' => 'nullable|numeric|min:0',
            'start_hour_reading' => 'nullable|numeric|min:0',
            'end_hour_reading' => 'nullable|numeric|min:0',
        ];

        if ($request->calculation_type === 'Kilometer Reading') {
            $rules['start_kilometer_reading'] = ['required', 'numeric', 'min:0'];
            $comparisonKm = $utilizationEntry->last_kilometer_reading;
            if ($comparisonKm !== null) {
                $rules['start_kilometer_reading'][] = 'gte:' . $comparisonKm;
            }
            $rules['end_kilometer_reading'] = [
                'required', 
                'numeric', 
                'min:0', 
                'gt:start_kilometer_reading',
                function ($attribute, $value, $fail) use ($nextKmEntry) {
                    if ($nextKmEntry && $nextKmEntry->start_kilometer_reading > 0) {
                        if ($value > $nextKmEntry->start_kilometer_reading) {
                            $fail('End Kilometer Reading cannot exceed the next available start reading (' . $nextKmEntry->start_kilometer_reading . ').');
                        }
                    }
                }
            ];
        } elseif ($request->calculation_type === 'Hour Reading') {
            $rules['start_hour_reading'] = ['required', 'numeric', 'min:0'];
            $comparisonHr = $utilizationEntry->last_engine_hours;
            if ($comparisonHr !== null) {
                $rules['start_hour_reading'][] = 'gte:' . $comparisonHr;
            }
            $rules['end_hour_reading'] = [
                'required', 
                'numeric', 
                'min:0', 
                'gt:start_hour_reading',
                function ($attribute, $value, $fail) use ($nextHrEntry) {
                    if ($nextHrEntry && $nextHrEntry->start_hour_reading > 0) {
                        if ($value > $nextHrEntry->start_hour_reading) {
                            $fail('End Engine Hours cannot exceed the next available start hours (' . $nextHrEntry->start_hour_reading . ').');
                        }
                    }
                }
            ];
        }

        $validated = $request->validate($rules);

        $validated['unbudgeted'] = $request->has('unbudgeted');
        $validated['updated_by'] = Auth::id();
        $validated['start_kilometer_reading'] = $validated['start_kilometer_reading'] ?? 0;
        $validated['end_kilometer_reading'] = $validated['end_kilometer_reading'] ?? 0;
        $validated['start_hour_reading'] = $validated['start_hour_reading'] ?? 0;
        $validated['end_hour_reading'] = $validated['end_hour_reading'] ?? 0;

        $utilizationEntry->update($validated);

        if ($request->calculation_type === 'Kilometer Reading') {
            $asset->last_kilometer_reading = $validated['end_kilometer_reading'];
            $asset->last_date = $validated['date'];
            $asset->last_time = $validated['end_time'];
            $asset->save();
        } elseif ($request->calculation_type === 'Hour Reading') {
            $asset->last_engine_hours = $validated['end_hour_reading'];
            $asset->last_date = $validated['date'];
            $asset->last_time = $validated['end_time'];
            $asset->save();
        } elseif ($request->calculation_type === 'Actual Operation Hours') {
            $asset->last_date = $validated['date'];
            $asset->last_time = $validated['end_time'];
            $asset->save();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Utilization entry updated successfully.',
                'entry' => $utilizationEntry,
            ]);
        }

        return redirect()->route('utilization-entries.show', $utilizationEntry->id)->with('status', 'Utilization entry updated successfully.');
    }

    public function destroy(UtilizationEntry $utilizationEntry): RedirectResponse
    {
        if (! in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }
        $utilizationEntry->update(['deleted_by' => Auth::id()]);
        $utilizationEntry->delete();

        return redirect()->route('assets.show', $utilizationEntry->asset_id)->with('status', 'Utilization entry deleted successfully.');
    }

    public function logs(Asset $asset, Request $request): JsonResponse
    {
        $query = $asset->utilizationEntries()
            ->with(['chargeableAccount', 'subAccount'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->filled('chargeable_account_id')) {
            $query->where('chargeable_account_id', $request->chargeable_account_id);
        }

        if ($request->filled('fuel_order_id')) {
            $query->where('fuel_order_id', $request->fuel_order_id);
        }

        $entries = $query->paginate(10);

        return response()->json($entries);
    }

    public function printLogs(Asset $asset, Request $request): View
    {
        $query = $asset->utilizationEntries()
            ->with(['chargeableAccount', 'subAccount'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->filled('chargeable_account_id')) {
            $query->where('chargeable_account_id', $request->chargeable_account_id);
        }

        if ($request->filled('fuel_order_id')) {
            $query->where('fuel_order_id', $request->fuel_order_id);
        }

        $entries = $query->get();

        return view('assets.print-logs', compact('asset', 'entries', 'request'));
    }
}
