<?php

namespace App\Http\Controllers;

use App\Imports\UtilizationEntryImport;
use App\Models\Asset;
use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\UtilizationEntry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UtilizationEntryController extends Controller
{
    public function index(Request $request): View
    {
        $query = UtilizationEntry::with(['asset', 'chargeableAccount', 'subAccount', 'fuelOrder', 'creator'])
            ->whereNotNull('fuel_order_id');

        if ($request->filled('chargeable_account_id')) {
            $query->where('chargeable_account_id', $request->chargeable_account_id);
        }

        if ($request->filled('sub_account_id')) {
            if ($request->sub_account_id === 'null') {
                $query->whereNull('sub_account_id');
            } else {
                $query->where('sub_account_id', $request->sub_account_id);
            }
        }

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('fuel_order_id')) {
            $query->where('fuel_order_id', $request->fuel_order_id);
        }

        if ($request->filled('fuel_order_status')) {
            $query->whereHas('fuelOrder', function ($q) use ($request) {
                $q->where('status', $request->fuel_order_status);
            });
        }

        if ($request->filled('unbudgeted')) {
            $query->where('unbudgeted', $request->boolean('unbudgeted'));
        }

        if ($request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        // Get total calculated fuel across all matched entries (excluding soft-deleted ones)
        $totalCalculatedFuel = $query->get()->filter(fn ($entry) => ! $entry->trashed())->sum('calculated_quantity');

        $utilizationEntries = $query->latest('date')->latest('start_time')->paginate(10)->withQueryString();

        $chargeableAccounts = ChargeableAccount::where('status', 'Active')->orderBy('name')->get();
        $subAccounts = SubAccount::orderBy('name')->get();
        $assets = Asset::orderBy('fleet_no')->get();

        return view('utilization-entries.index', compact('utilizationEntries', 'chargeableAccounts', 'subAccounts', 'assets', 'totalCalculatedFuel'));
    }

    public function print(Request $request): View
    {
        $query = UtilizationEntry::with(['asset', 'chargeableAccount', 'subAccount', 'fuelOrder', 'creator'])
            ->whereNotNull('fuel_order_id');

        if ($request->filled('chargeable_account_id')) {
            $query->where('chargeable_account_id', $request->chargeable_account_id);
        }

        if ($request->filled('sub_account_id')) {
            if ($request->sub_account_id === 'null') {
                $query->whereNull('sub_account_id');
            } else {
                $query->where('sub_account_id', $request->sub_account_id);
            }
        }

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('fuel_order_id')) {
            $query->where('fuel_order_id', $request->fuel_order_id);
        }

        if ($request->filled('fuel_order_status')) {
            $query->whereHas('fuelOrder', function ($q) use ($request) {
                $q->where('status', $request->fuel_order_status);
            });
        }

        if ($request->filled('unbudgeted')) {
            $query->where('unbudgeted', $request->boolean('unbudgeted'));
        }

        if ($request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        $utilizationEntries = $query->latest('date')->latest('start_time')->get();

        $totalCalculatedFuel = $utilizationEntries->filter(fn ($entry) => ! $entry->trashed())->sum('calculated_quantity');

        $chargeableAccount = $request->filled('chargeable_account_id') ? ChargeableAccount::find($request->chargeable_account_id) : null;
        $subAccount = $request->filled('sub_account_id') ? SubAccount::find($request->sub_account_id) : null;
        $asset = $request->filled('asset_id') ? Asset::find($request->asset_id) : null;

        return view('utilization-entries.print', compact('utilizationEntries', 'totalCalculatedFuel', 'chargeableAccount', 'subAccount', 'asset'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $asset = Asset::findOrFail($request->asset_id);

        $rules = [
            'asset_id' => 'required|exists:assets,id',
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->chargeable_account_id) {
                        $account = ChargeableAccount::find($request->chargeable_account_id);
                        if ($account && $account->classification === 'Scoped') {
                            $entryDate = Carbon::parse($value)->startOfDay();
                            $startDate = $account->start_date ? $account->start_date->startOfDay() : null;
                            $endDate = $account->end_date ? $account->end_date->startOfDay() : null;

                            if ($startDate && $entryDate->lt($startDate)) {
                                $fail('The utilization date must be within the scoped period of the selected chargeable account ('.$startDate->format('M d, Y').' to '.($endDate ? $endDate->format('M d, Y') : 'N/A').').');
                            }
                            if ($endDate && $entryDate->gt($endDate)) {
                                $fail('The utilization date must be within the scoped period of the selected chargeable account ('.($startDate ? $startDate->format('M d, Y') : 'N/A').' to '.$endDate->format('M d, Y').').');
                            }
                        }
                    }
                },
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('utilization_entries')->where(function ($query) use ($request) {
                    return $query->where('asset_id', $request->asset_id)
                        ->where('date', $request->date)
                        ->whereNull('deleted_at');
                }),
                function ($attribute, $value, $fail) use ($request, $asset) {
                    if ($asset->last_date !== null && $asset->last_time !== null && $request->date) {
                        try {
                            $assetDateTime = Carbon::parse($asset->last_date.' '.$asset->last_time);
                            $requestDateTime = Carbon::parse($request->date.' '.$value);

                            if ($requestDateTime->lessThan($assetDateTime)) {
                                $fail('Date and Start Time cannot be earlier than the asset\'s last log ('.$assetDateTime->format('M d, Y H:i').').');
                            }
                        } catch (\Exception $e) {
                        }
                    }
                },
            ],
            'end_time' => 'required|date_format:H:i',
            'driver_operator_name' => 'required|string|max:255',
            'chargeable_account_id' => 'required|exists:chargeable_accounts,id',
            'sub_account_id' => 'required_unless:unbudgeted,1|nullable|exists:sub_accounts,id',
            'reference' => 'nullable|string|max:255',
            'calculation_type' => 'required|string|in:Kilometer Reading,Hour Reading,Timeframe,Actual Hours',
            'unbudgeted' => 'nullable',
            'particulars' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'start_kilometer_reading' => 'nullable|numeric|min:0',
            'end_kilometer_reading' => 'nullable|numeric|min:0',
            'start_hour_reading' => 'nullable|numeric|min:0',
            'end_hour_reading' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
        ];

        if ($request->calculation_type !== 'Actual Hours') {
            $rules['end_time'] = 'required|date_format:H:i|after:start_time';
        }

        if ($request->calculation_type === 'Kilometer Reading') {
            $rules['start_kilometer_reading'] = ['required', 'numeric', 'min:0'];
            if ($asset->last_kilometer_reading !== null) {
                $rules['start_kilometer_reading'][] = 'gte:'.$asset->last_kilometer_reading;
            }
            $rules['end_kilometer_reading'] = ['required', 'numeric', 'min:0', 'gt:start_kilometer_reading'];
        } elseif ($request->calculation_type === 'Hour Reading') {
            $rules['start_hour_reading'] = ['required', 'numeric', 'min:0'];
            if ($asset->last_engine_hours !== null) {
                $rules['start_hour_reading'][] = 'gte:'.$asset->last_engine_hours;
            }
            $rules['end_hour_reading'] = ['required', 'numeric', 'min:0', 'gt:start_hour_reading'];
        } elseif ($request->calculation_type === 'Actual Hours') {
            $rules['actual_hours'] = ['required', 'numeric', 'gt:0'];
        }

        $validated = $request->validate($rules);

        $validated['unbudgeted'] = $request->unbudgeted == '1';
        if ($validated['unbudgeted']) {
            $validated['sub_account_id'] = null;
        }
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
        } elseif ($request->calculation_type === 'Timeframe' || $request->calculation_type === 'Actual Hours') {
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
                'calculation_type',
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

        if ($utilizationEntry->fuelOrder && $utilizationEntry->fuelOrder->status === 'DONE') {
            abort(403, 'Cannot edit utilization entry because its assigned fuel order has been completed.');
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

        if ($utilizationEntry->fuelOrder && $utilizationEntry->fuelOrder->status === 'DONE') {
            abort(403, 'Cannot edit utilization entry because its assigned fuel order has been completed.');
        }

        $asset = $utilizationEntry->asset;

        // Find the next immediate utilization record for time validation
        $nextTimeEntry = UtilizationEntry::where('asset_id', $utilizationEntry->asset_id)
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
        $nextKmEntry = UtilizationEntry::where('asset_id', $utilizationEntry->asset_id)
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
        $nextHrEntry = UtilizationEntry::where('asset_id', $utilizationEntry->asset_id)
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
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->chargeable_account_id) {
                        $account = ChargeableAccount::find($request->chargeable_account_id);
                        if ($account && $account->classification === 'Scoped') {
                            $entryDate = Carbon::parse($value)->startOfDay();
                            $startDate = $account->start_date ? $account->start_date->startOfDay() : null;
                            $endDate = $account->end_date ? $account->end_date->startOfDay() : null;

                            if ($startDate && $entryDate->lt($startDate)) {
                                $fail('The utilization date must be within the scoped period of the selected chargeable account ('.$startDate->format('M d, Y').' to '.($endDate ? $endDate->format('M d, Y') : 'N/A').').');
                            }
                            if ($endDate && $entryDate->gt($endDate)) {
                                $fail('The utilization date must be within the scoped period of the selected chargeable account ('.($startDate ? $startDate->format('M d, Y') : 'N/A').' to '.$endDate->format('M d, Y').').');
                            }
                        }
                    }
                },
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('utilization_entries')->where(function ($query) use ($request, $utilizationEntry) {
                    return $query->where('asset_id', $utilizationEntry->asset_id)
                        ->where('date', $request->date)
                        ->whereNull('deleted_at');
                })->ignore($utilizationEntry->id),
                function ($attribute, $value, $fail) use ($request, $utilizationEntry) {
                    if ($utilizationEntry->last_date !== null && $utilizationEntry->last_time !== null && $request->date) {
                        try {
                            $lastDateString = $utilizationEntry->last_date instanceof Carbon ? $utilizationEntry->last_date->format('Y-m-d') : $utilizationEntry->last_date;
                            $lastDateTime = Carbon::parse($lastDateString.' '.$utilizationEntry->last_time);
                            $requestDateTime = Carbon::parse($request->date.' '.$value);

                            if ($requestDateTime->lessThan($lastDateTime)) {
                                $fail('Date and Start Time cannot be earlier than the previous log ('.$lastDateTime->format('M d, Y H:i').').');
                            }
                        } catch (\Exception $e) {
                        }
                    }
                },
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request, $nextTimeEntry) {
                    if ($nextTimeEntry && $request->date) {
                        try {
                            $endDateTime = Carbon::parse($request->date.' '.$value);
                            $nextDateString = $nextTimeEntry->date instanceof Carbon ? $nextTimeEntry->date->format('Y-m-d') : $nextTimeEntry->date;
                            $nextStartDateTime = Carbon::parse($nextDateString.' '.$nextTimeEntry->start_time);

                            if ($endDateTime->greaterThan($nextStartDateTime)) {
                                $fail('Date and End Time overlap with the next immediate record which starts at '.$nextStartDateTime->format('M d, Y H:i').'.');
                            }
                        } catch (\Exception $e) {
                        }
                    }
                },
            ],
            'driver_operator_name' => 'required|string|max:255',
            'chargeable_account_id' => 'required|exists:chargeable_accounts,id',
            'sub_account_id' => 'required_unless:unbudgeted,1|nullable|exists:sub_accounts,id',
            'reference' => 'nullable|string|max:255',
            'calculation_type' => 'required|string|in:Kilometer Reading,Hour Reading,Timeframe,Actual Hours',
            'unbudgeted' => 'nullable',
            'particulars' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'start_kilometer_reading' => 'nullable|numeric|min:0',
            'end_kilometer_reading' => 'nullable|numeric|min:0',
            'start_hour_reading' => 'nullable|numeric|min:0',
            'end_hour_reading' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
        ];

        if ($request->calculation_type !== 'Actual Hours') {
            $rules['end_time'][] = 'after:start_time';
        }

        if ($request->calculation_type === 'Kilometer Reading') {
            $rules['start_kilometer_reading'] = ['required', 'numeric', 'min:0'];
            $comparisonKm = $utilizationEntry->last_kilometer_reading;
            if ($comparisonKm !== null) {
                $rules['start_kilometer_reading'][] = 'gte:'.$comparisonKm;
            }
            $rules['end_kilometer_reading'] = [
                'required',
                'numeric',
                'min:0',
                'gt:start_kilometer_reading',
                function ($attribute, $value, $fail) use ($nextKmEntry) {
                    if ($nextKmEntry && $nextKmEntry->start_kilometer_reading > 0) {
                        if ($value > $nextKmEntry->start_kilometer_reading) {
                            $fail('End Kilometer Reading cannot exceed the next available start reading ('.$nextKmEntry->start_kilometer_reading.').');
                        }
                    }
                },
            ];
        } elseif ($request->calculation_type === 'Hour Reading') {
            $rules['start_hour_reading'] = ['required', 'numeric', 'min:0'];
            $comparisonHr = $utilizationEntry->last_engine_hours;
            if ($comparisonHr !== null) {
                $rules['start_hour_reading'][] = 'gte:'.$comparisonHr;
            }
            $rules['end_hour_reading'] = [
                'required',
                'numeric',
                'min:0',
                'gt:start_hour_reading',
                function ($attribute, $value, $fail) use ($nextHrEntry) {
                    if ($nextHrEntry && $nextHrEntry->start_hour_reading > 0) {
                        if ($value > $nextHrEntry->start_hour_reading) {
                            $fail('End Engine Hours cannot exceed the next available start hours ('.$nextHrEntry->start_hour_reading.').');
                        }
                    }
                },
            ];
        } elseif ($request->calculation_type === 'Actual Hours') {
            $rules['actual_hours'] = ['required', 'numeric', 'gt:0'];
        }

        $validated = $request->validate($rules);

        $validated['unbudgeted'] = $request->unbudgeted == '1';
        if ($validated['unbudgeted']) {
            $validated['sub_account_id'] = null;
        }
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
        } elseif ($request->calculation_type === 'Timeframe' || $request->calculation_type === 'Actual Hours') {
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

        if ($utilizationEntry->fuel_order_id !== null) {
            return redirect()->back()->with('error', 'Cannot delete utilization entry because it is already assigned to an active or completed fuel order.');
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

    public function bulkUpload(Asset $asset): View
    {
        return view('utilization-entries.bulk-upload', compact('asset'));
    }

    public function bulkPreview(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $array = Excel::toArray(new UtilizationEntryImport, $request->file('file'));
            $sheet = $array[0] ?? [];

            if (empty($sheet)) {
                return response()->json(['error' => 'The uploaded file is empty or invalid.'], 422);
            }

            if (count($sheet) > 50) {
                return response()->json(['error' => 'Maximum allowable entries is 50 rows per bulk upload. The uploaded file has '.count($sheet).' rows.'], 422);
            }

            $mappedRows = [];
            foreach ($sheet as $rawRow) {
                // Check if the row is entirely empty
                $nonEmpty = array_filter($rawRow, fn ($val) => $val !== null && $val !== '');
                if (empty($nonEmpty)) {
                    continue;
                }
                $mappedRows[] = $this->mapExcelRow($rawRow);
            }

            if (empty($mappedRows)) {
                return response()->json(['error' => 'The uploaded file has no data rows.'], 422);
            }

            $validationResult = $this->validateBatchRows($asset, $mappedRows);

            return response()->json([
                'rows' => $validationResult['rows'],
                'has_errors' => $validationResult['has_errors'],
                'total_rows' => count($validationResult['rows']),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to parse Excel file: '.$e->getMessage()], 500);
        }
    }

    public function bulkStore(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'rows' => 'required|array',
        ]);

        $rows = $request->input('rows');

        if (count($rows) > 50) {
            return response()->json(['error' => 'Maximum allowable entries is 50 rows per bulk upload.'], 422);
        }

        // Run server-side validation again to guarantee security
        $validationResult = $this->validateBatchRows($asset, $rows);

        if ($validationResult['has_errors']) {
            return response()->json([
                'message' => 'Validation failed on some rows.',
                'errors' => $validationResult['rows'],
            ], 422);
        }

        try {
            DB::transaction(function () use ($asset, $validationResult) {
                foreach ($validationResult['rows'] as $row) {
                    $entryData = [
                        'asset_id' => $asset->id,
                        'date' => $row['date'],
                        'start_time' => $row['start_time'],
                        'end_time' => $row['end_time'],
                        'driver_operator_name' => $row['driver_operator_name'],
                        'chargeable_account_id' => $row['chargeable_account_id'],
                        'sub_account_id' => $row['sub_account_id'],
                        'reference' => $row['reference'],
                        'calculation_type' => $row['calculation_type'],
                        'unbudgeted' => $row['unbudgeted'],
                        'particulars' => $row['particulars'],
                        'start_kilometer_reading' => $row['start_kilometer_reading'],
                        'end_kilometer_reading' => $row['end_kilometer_reading'],
                        'start_hour_reading' => $row['start_hour_reading'],
                        'end_hour_reading' => $row['end_hour_reading'],
                        'actual_hours' => $row['actual_hours'],
                        'remarks' => $row['remarks'],
                        'created_by' => Auth::id(),
                        'fuel_factor_km' => $asset->fuel_factor_km,
                        'fuel_factor_hr' => $asset->fuel_factor_hr,
                        'last_kilometer_reading' => $asset->last_kilometer_reading,
                        'last_engine_hours' => $asset->last_engine_hours,
                        'last_date' => $asset->last_date,
                        'last_time' => $asset->last_time,
                    ];

                    UtilizationEntry::create($entryData);

                    // Progressively update the asset state
                    if ($row['calculation_type'] === 'Kilometer Reading') {
                        $asset->last_kilometer_reading = $row['end_kilometer_reading'];
                        $asset->last_date = $row['date'];
                        $asset->last_time = $row['end_time'];
                    } elseif ($row['calculation_type'] === 'Hour Reading') {
                        $asset->last_engine_hours = $row['end_hour_reading'];
                        $asset->last_date = $row['date'];
                        $asset->last_time = $row['end_time'];
                    } else {
                        $asset->last_date = $row['date'];
                        $asset->last_time = $row['end_time'];
                    }
                    $asset->save();
                }
            });

            return response()->json([
                'success' => true,
                'message' => count($rows).' utilization entries created successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Database transaction failed: '.$e->getMessage()], 500);
        }
    }

    private function mapExcelRow(array $rawRow): array
    {
        $mapped = [];
        $keys = array_keys($rawRow);

        $findValue = function ($aliases) use ($rawRow, $keys) {
            foreach ($aliases as $alias) {
                if (in_array($alias, $keys)) {
                    return $rawRow[$alias];
                }
                $normalizedAlias = str_replace(['_', ' '], '', strtolower($alias));
                foreach ($keys as $key) {
                    $normalizedKey = str_replace(['_', ' '], '', strtolower($key));
                    if ($normalizedKey === $normalizedAlias) {
                        return $rawRow[$key];
                    }
                }
            }

            return null;
        };

        // Parse combined account and sub-account
        $combinedValue = trim($findValue(['account_sub_account', 'account_::_sub_account', 'account__sub_account', 'charged_to', 'account']) ?? '');

        $accountName = '';
        $subAccountName = '';
        $unbudgeted = false;

        if (! empty($combinedValue) && str_contains($combinedValue, ' :: ')) {
            $parts = explode(' :: ', $combinedValue);
            if (count($parts) >= 2) {
                $subName = trim(array_pop($parts));
                $accName = trim(implode(' :: ', $parts));

                $accountName = $accName;
                if (strtolower($subName) === 'unbudgeted') {
                    $unbudgeted = true;
                    $subAccountName = '';
                } else {
                    $unbudgeted = false;
                    $subAccountName = $subName;
                }
            } else {
                $accountName = $combinedValue;
                $unbudgeted = false;
                $subAccountName = '';
            }
        } else {
            // Fallbacks if columns are separate (helps with our feature test suites!)
            $accountName = trim($findValue(['chargeable_account', 'charged_to', 'account']) ?? '');
            $subAccountName = trim($findValue(['sub_account', 'sub_account_name']) ?? '');
            $unbudgetedVal = trim($findValue(['unbudgeted', 'unbudgeted_log']) ?? '');
            $unbudgeted = in_array(strtolower($unbudgetedVal), ['yes', '1', 'true', 'y']);
        }

        $mapped['date'] = $this->parseExcelDate($findValue(['date']));
        $mapped['start_time'] = $this->parseExcelTime($findValue(['start_time']));
        $mapped['end_time'] = $this->parseExcelTime($findValue(['end_time']));
        $mapped['driver_operator_name'] = $findValue(['driver_operator_name', 'driver_operator', 'personnel_in_charge', 'driver', 'operator']);
        $mapped['chargeable_account'] = $accountName;
        $mapped['sub_account'] = $subAccountName;
        $mapped['reference'] = $findValue(['reference', 'ref']);
        $mapped['calculation_type'] = $findValue(['calculation_type', 'calc_type', 'type']);
        $mapped['unbudgeted'] = $unbudgeted;
        $mapped['particulars'] = $findValue(['particulars', 'particulars_mission', 'particulars_or_mission', 'mission']);
        $mapped['start_reading'] = $findValue(['start_reading', 'start_odo', 'start_engine']);
        $mapped['end_reading'] = $findValue(['end_reading', 'end_odo', 'end_engine']);
        $mapped['actual_hours'] = $findValue(['actual_hours', 'hours']);
        $mapped['remarks'] = $findValue(['remarks', 'notes', 'remark']);

        return $mapped;
    }

    private function parseExcelDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Exception $e) {
            }
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return $value;
        }
    }

    private function parseExcelTime($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject($value))->format('H:i');
            } catch (\Exception $e) {
            }
        }
        try {
            // If it's a string of HH:MM:SS or HH:MM
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return $value;
        }
    }

    private function validateBatchRows(Asset $asset, array $mappedRows): array
    {
        $simulatedOdometer = $asset->last_kilometer_reading;
        $simulatedEngineHours = $asset->last_engine_hours;
        $simulatedDate = $asset->last_date;
        $simulatedTime = $asset->last_time;

        $validatedRows = [];
        $hasErrorsTotal = false;

        foreach ($mappedRows as $index => $row) {
            $rowErrors = [];

            // 1. Resolve Account
            $accountName = trim($row['chargeable_account'] ?? '');
            $account = null;
            if (empty($accountName)) {
                $rowErrors[] = 'Chargeable account is required.';
            } else {
                $account = ChargeableAccount::where('name', $accountName)->first();
                if (! $account) {
                    $rowErrors[] = "Chargeable account '{$accountName}' not found.";
                } elseif ($account->status !== 'Active') {
                    $rowErrors[] = "Chargeable account '{$accountName}' is inactive.";
                }
            }

            // 2. Resolve Unbudgeted
            $unbudgetedVal = trim($row['unbudgeted'] ?? '');
            $unbudgeted = in_array(strtolower($unbudgetedVal), ['yes', '1', 'true', 'y']);

            // 3. Resolve Sub Account
            $subAccountName = trim($row['sub_account'] ?? '');
            $subAccount = null;
            if (! $unbudgeted && $account) {
                if (empty($subAccountName)) {
                    $rowErrors[] = 'Sub-account is required when not unbudgeted.';
                } else {
                    $subAccount = SubAccount::where('chargeable_account_id', $account->id)
                        ->where('name', $subAccountName)
                        ->first();
                    if (! $subAccount) {
                        $rowErrors[] = "Sub-account '{$subAccountName}' not found under account '{$accountName}'.";
                    }
                }
            }

            // 4. Validate Date Scope
            $entryDateStr = $row['date'];
            if (empty($entryDateStr)) {
                $rowErrors[] = 'Date is required.';
            } else {
                try {
                    $entryDate = Carbon::parse($entryDateStr);
                    if ($account && $account->classification === 'Scoped') {
                        $startDate = $account->start_date ? $account->start_date->startOfDay() : null;
                        $endDate = $account->end_date ? $account->end_date->startOfDay() : null;
                        $compDate = $entryDate->startOfDay();

                        if ($startDate && $compDate->lt($startDate)) {
                            $rowErrors[] = 'Date ('.Carbon::parse($entryDateStr)->format('M d, Y').") must be after or on Chargeable Account's start date (".$startDate->format('M d, Y').').';
                        }
                        if ($endDate && $compDate->gt($endDate)) {
                            $rowErrors[] = 'Date ('.Carbon::parse($entryDateStr)->format('M d, Y').") must be before or on Chargeable Account's end date (".$endDate->format('M d, Y').').';
                        }
                    }
                } catch (\Exception $e) {
                    $rowErrors[] = 'Invalid date format.';
                }
            }

            // 5. Check calculation type
            $calcType = trim($row['calculation_type'] ?? '');
            $allowedTypes = ['Kilometer Reading', 'Hour Reading', 'Timeframe', 'Actual Hours'];
            if (empty($calcType)) {
                $rowErrors[] = 'Calculation type is required.';
            } elseif (! in_array($calcType, $allowedTypes)) {
                $rowErrors[] = "Invalid calculation type '{$calcType}'. Must be one of: ".implode(', ', $allowedTypes);
            }

            // 6. Validate basic fields
            if (empty($row['driver_operator_name'])) {
                $rowErrors[] = 'Personnel In-Charge is required.';
            }
            if (empty($row['particulars'])) {
                $rowErrors[] = 'Particulars / Mission is required.';
            }

            // 7. Validate Times and readings sequentiality
            $startTimeStr = $row['start_time'];
            $endTimeStr = $row['end_time'];

            if (empty($startTimeStr)) {
                $rowErrors[] = 'Start Time is required.';
            }
            if (empty($endTimeStr)) {
                $rowErrors[] = 'End Time is required.';
            }

            if (! empty($entryDateStr) && ! empty($startTimeStr)) {
                try {
                    $reqDateTime = Carbon::parse($entryDateStr.' '.$startTimeStr);

                    // Compare with running asset last date/time
                    if ($simulatedDate !== null && $simulatedTime !== null) {
                        $assetDateTime = Carbon::parse($simulatedDate.' '.$simulatedTime);
                        if ($reqDateTime->lessThan($assetDateTime)) {
                            $rowErrors[] = "Date and Start Time cannot be earlier than previous log's end time (".$assetDateTime->format('M d, Y H:i').').';
                        }
                    }

                    // End time check
                    if (! empty($endTimeStr)) {
                        $reqEndDateTime = Carbon::parse($entryDateStr.' '.$endTimeStr);
                        if ($reqEndDateTime->lessThanOrEqualTo($reqDateTime)) {
                            $rowErrors[] = 'End Time must be after Start Time.';
                        }

                        if (empty($rowErrors)) {
                            $simulatedDate = $entryDateStr;
                            $simulatedTime = $endTimeStr;
                        }
                    }
                } catch (\Exception $e) {
                    $rowErrors[] = 'Invalid date/time values.';
                }
            }

            // Readings and calculation validation
            $startReading = floatval($row['start_reading'] ?? $row['start_kilometer_reading'] ?? $row['start_hour_reading'] ?? 0);
            $endReading = floatval($row['end_reading'] ?? $row['end_kilometer_reading'] ?? $row['end_hour_reading'] ?? 0);
            $actualHours = floatval($row['actual_hours'] ?? 0);

            if ($calcType === 'Kilometer Reading') {
                $startVal = $row['start_reading'] ?? $row['start_kilometer_reading'] ?? null;
                if ($startVal === null || $startVal === '') {
                    $rowErrors[] = 'Start Reading (Odometer) is required.';
                } else {
                    if ($simulatedOdometer !== null && $startReading < $simulatedOdometer) {
                        $rowErrors[] = "Start Odometer ({$startReading}) cannot be less than previous log's End Odometer ({$simulatedOdometer}).";
                    }
                }
                $endVal = $row['end_reading'] ?? $row['end_kilometer_reading'] ?? null;
                if ($endVal === null || $endVal === '') {
                    $rowErrors[] = 'End Reading (Odometer) is required.';
                } elseif ($endReading <= $startReading) {
                    $rowErrors[] = "End Odometer ({$endReading}) must be greater than Start Odometer ({$startReading}).";
                }
                if (empty($rowErrors)) {
                    $simulatedOdometer = $endReading;
                }
            } elseif ($calcType === 'Hour Reading') {
                $startVal = $row['start_reading'] ?? $row['start_hour_reading'] ?? null;
                if ($startVal === null || $startVal === '') {
                    $rowErrors[] = 'Start Reading (Engine Hours) is required.';
                } else {
                    if ($simulatedEngineHours !== null && $startReading < $simulatedEngineHours) {
                        $rowErrors[] = "Start Engine Hours ({$startReading}) cannot be less than previous log's End Engine Hours ({$simulatedEngineHours}).";
                    }
                }
                $endVal = $row['end_reading'] ?? $row['end_hour_reading'] ?? null;
                if ($endVal === null || $endVal === '') {
                    $rowErrors[] = 'End Reading (Engine Hours) is required.';
                } elseif ($endReading <= $startReading) {
                    $rowErrors[] = "End Engine Hours ({$endReading}) must be greater than Start Engine Hours ({$startReading}).";
                }
                if (empty($rowErrors)) {
                    $simulatedEngineHours = $endReading;
                }
            } elseif ($calcType === 'Actual Hours') {
                if ($row['actual_hours'] === null || $row['actual_hours'] === '') {
                    $rowErrors[] = 'Actual Hours is required.';
                } elseif ($actualHours <= 0) {
                    $rowErrors[] = "Actual Hours ({$actualHours}) must be greater than 0.";
                }
            }

            $validatedRows[] = [
                'index' => $index + 1,
                'date' => $row['date'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'driver_operator_name' => $row['driver_operator_name'],
                'chargeable_account_id' => $account ? $account->id : null,
                'chargeable_account' => $accountName,
                'sub_account_id' => $subAccount ? $subAccount->id : null,
                'sub_account' => $subAccountName,
                'reference' => $row['reference'] ?? '',
                'calculation_type' => $calcType,
                'unbudgeted' => $unbudgeted,
                'particulars' => $row['particulars'] ?? '',
                'start_kilometer_reading' => $calcType === 'Kilometer Reading' ? $startReading : 0,
                'end_kilometer_reading' => $calcType === 'Kilometer Reading' ? $endReading : 0,
                'start_hour_reading' => $calcType === 'Hour Reading' ? $startReading : 0,
                'end_hour_reading' => $calcType === 'Hour Reading' ? $endReading : 0,
                'actual_hours' => $calcType === 'Actual Hours' ? $actualHours : 0,
                'remarks' => $row['remarks'] ?? '',
                'has_errors' => ! empty($rowErrors),
                'errors' => $rowErrors,
            ];

            if (! empty($rowErrors)) {
                $hasErrorsTotal = true;
            }
        }

        return [
            'rows' => $validatedRows,
            'has_errors' => $hasErrorsTotal,
        ];
    }

    public function bulkTemplate(Asset $asset)
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($asset->fleet_no, 0, 31));

        $headers = [
            'Date (YYYY-MM-DD)',
            'Start Time (HH:MM)',
            'End Time (HH:MM)',
            'Personnel In-Charge',
            'Account :: Sub Account', // Column E
            'Calculation Type',      // Column F
            'Start Reading',         // Column G
            'End Reading',           // Column H
            'Actual Hours',          // Column I
            'Particulars / Mission', // Column J
            'Reference',             // Column K
            'Remarks',                // Column L
        ];

        // Format Header
        foreach ($headers as $colIndex => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter.'1', $header);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Fetch options
        $accounts = ChargeableAccount::where('status', 'Active')
            ->with(['subAccounts' => function ($q) {
                $q->orderBy('name', 'asc');
            }])
            ->orderBy('name', 'asc')
            ->get();

        $dropdownOptions = [];
        foreach ($accounts as $account) {
            $dropdownOptions[] = "{$account->name} :: Unbudgeted";
            foreach ($account->subAccounts as $sub) {
                $dropdownOptions[] = "{$account->name} :: {$sub->name}";
            }
        }
        sort($dropdownOptions);

        // Add Options sheet
        $optionsSheet = $spreadsheet->createSheet();
        $optionsSheet->setTitle('Options');
        $optionsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        // Write options
        foreach ($dropdownOptions as $idx => $option) {
            $optionsSheet->setCellValue('A'.($idx + 1), $option);
        }

        // Reset active sheet to Template
        $spreadsheet->setActiveSheetIndex(0);

        // Set up 50 blank rows with data validations
        for ($row = 2; $row <= 51; $row++) {
            // Dropdown validation for Account - Sub Account (Column E)
            if (count($dropdownOptions) > 0) {
                $validationAccount = $sheet->getCell('E'.$row)->getDataValidation();
                $validationAccount->setType(DataValidation::TYPE_LIST);
                $validationAccount->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validationAccount->setAllowBlank(false);
                $validationAccount->setShowInputMessage(true);
                $validationAccount->setShowErrorMessage(true);
                $validationAccount->setShowDropDown(true);
                $validationAccount->setErrorTitle('Input error');
                $validationAccount->setError('Value is not in list');
                $validationAccount->setPromptTitle('Pick from list');
                $validationAccount->setPrompt('Please choose an Account :: Sub Account combination');
                $validationAccount->setFormula1('Options!$A$1:$A$'.count($dropdownOptions));
            }

            // Dropdown validation for Calculation Type (Column F)
            $validationCalc = $sheet->getCell('F'.$row)->getDataValidation();
            $validationCalc->setType(DataValidation::TYPE_LIST);
            $validationCalc->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validationCalc->setAllowBlank(false);
            $validationCalc->setShowInputMessage(true);
            $validationCalc->setShowErrorMessage(true);
            $validationCalc->setShowDropDown(true);
            $validationCalc->setErrorTitle('Input error');
            $validationCalc->setError('Value is not in list');
            $validationCalc->setPromptTitle('Pick from list');
            $validationCalc->setPrompt('Please choose a calculation type');
            $validationCalc->setFormula1('"Kilometer Reading,Hour Reading,Timeframe,Actual Hours"');
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = $asset->fleet_no.' - Bulk Utilization Upload.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function bulkUploadChunk(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'file_chunk' => 'required|file',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
            'file_name' => 'required|string',
            'file_id' => 'required|string',
        ]);

        $chunk = $request->file('file_chunk');
        $chunkIndex = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $fileId = $request->input('file_id');
        $originalFileName = $request->input('file_name');

        $chunkDir = "chunks/{$fileId}";
        $chunkPath = "{$chunkDir}/{$chunkIndex}";

        // Save chunk
        Storage::put($chunkPath, fopen($chunk->getRealPath(), 'r'));

        // If it's the final chunk, assemble and process it
        if ($chunkIndex === $totalChunks - 1) {
            $assembledDir = 'temp_uploads';
            $assembledFileName = "{$fileId}_{$originalFileName}";
            $assembledPath = "{$assembledDir}/{$assembledFileName}";

            // Ensure directory exists
            if (! Storage::exists($assembledDir)) {
                Storage::makeDirectory($assembledDir);
            }

            // Create write stream to assembled file
            $assembledFullPath = Storage::path($assembledPath);
            $out = fopen($assembledFullPath, 'wb');

            // Merge chunks sequentially
            for ($i = 0; $i < $totalChunks; $i++) {
                $segmentPath = Storage::path("{$chunkDir}/{$i}");
                if (! Storage::exists("{$chunkDir}/{$i}")) {
                    fclose($out);
                    Storage::deleteDirectory($chunkDir);

                    return response()->json(['error' => "Chunk fragment {$i} is missing. Please retry upload."], 422);
                }
                $in = fopen($segmentPath, 'rb');
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
            fclose($out);

            // Delete raw chunks
            Storage::deleteDirectory($chunkDir);

            // Now parse the assembled file!
            try {
                $array = Excel::toArray(new UtilizationEntryImport, $assembledFullPath);
                $sheet = $array[0] ?? [];

                // Clean up the assembled file
                Storage::delete($assembledPath);

                if (empty($sheet)) {
                    return response()->json(['error' => 'The uploaded file is empty or invalid.'], 422);
                }

                if (count($sheet) > 50) {
                    return response()->json(['error' => 'Maximum allowable entries is 50 rows per bulk upload. The uploaded file has '.count($sheet).' rows.'], 422);
                }

                $mappedRows = [];
                foreach ($sheet as $rawRow) {
                    $nonEmpty = array_filter($rawRow, fn ($val) => $val !== null && $val !== '');
                    if (empty($nonEmpty)) {
                        continue;
                    }
                    $mappedRows[] = $this->mapExcelRow($rawRow);
                }

                if (empty($mappedRows)) {
                    return response()->json(['error' => 'The uploaded file has no data rows.'], 422);
                }

                $validationResult = $this->validateBatchRows($asset, $mappedRows);

                return response()->json([
                    'rows' => $validationResult['rows'],
                    'has_errors' => $validationResult['has_errors'],
                    'total_rows' => count($validationResult['rows']),
                    'success' => true,
                ]);

            } catch (\Exception $e) {
                if (Storage::exists($assembledPath)) {
                    Storage::delete($assembledPath);
                }

                return response()->json(['error' => 'Failed to parse Excel file chunks: '.$e->getMessage()], 500);
            }
        }

        return response()->json([
            'success' => true,
            'progress' => round((($chunkIndex + 1) / $totalChunks) * 100, 2),
            'chunk_index' => $chunkIndex,
        ]);
    }
}
