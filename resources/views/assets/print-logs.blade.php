<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilization Logs - {{ $asset->fleet_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 5px;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 3px 0;
            color: #333;
        }
        .filters {
            margin-bottom: 15px;
            font-size: 11px;
            color: #555;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #eee;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap; }
        
        @media print {
            body { padding: 0; background: #fff; }
            .filters { border: none; padding: 0; background: transparent; }
            @page { margin: 1cm; size: landscape; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>Utilization Logs: {{ $asset->fleet_no }}</h2>
        <p><strong>Category:</strong> {{ $asset->assetType->name }} &nbsp;|&nbsp; <strong>Plate No:</strong> {{ $asset->plate_no ?? 'N/A' }} &nbsp;|&nbsp; <strong>Fuel:</strong> {{ $asset->fuel_type }}</p>
    </div>

    @if($request->filled('start_date') || $request->filled('end_date') || $request->filled('chargeable_account_id') || $request->filled('fuel_order_id'))
    <div class="filters">
        <strong>Filters Applied:</strong> 
        @if($request->filled('start_date')) Date From: <u>{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}</u> &nbsp; @endif
        @if($request->filled('end_date')) Date To: <u>{{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</u> &nbsp; @endif
        @if($request->filled('chargeable_account_id')) Account: <u>{{ App\Models\ChargeableAccount::find($request->chargeable_account_id)->name ?? 'Unknown' }}</u> &nbsp; @endif
        @if($request->filled('fuel_order_id')) Order ID: <u>#{{ $request->fuel_order_id }}</u> @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th class="nowrap">Date & Time</th>
                <th>Reference</th>
                <th>Particulars</th>
                <th>Personnel In-Charge</th>
                <th>Account / Sub Account</th>
                <th class="text-right nowrap">Start / End Odo</th>
                <th class="text-right nowrap">Start / End Hr</th>
                <th class="text-center nowrap">Fuel Order ID</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td class="nowrap">
                        {{ $entry->date->format('M d, Y') }}<br>
                        {{ $entry->start_time ? $entry->start_time->format('H:i') : '' }} - {{ $entry->end_time ? $entry->end_time->format('H:i') : '' }}
                        @if($entry->start_time && $entry->end_time)
                            @php
                                $start = \Carbon\Carbon::parse($entry->start_time);
                                $end = \Carbon\Carbon::parse($entry->end_time);
                                if ($end->lessThan($start)) {
                                    $end->addDay();
                                }
                                $hrs = $end->diffInMinutes($start) / 60;
                            @endphp
                            <br><small style="color: #555;">({{ number_format($hrs, 2) }} hrs)</small>
                        @endif
                    </td>
                    <td>{{ $entry->reference }}</td>
                    <td>{{ $entry->particulars }}</td>
                    <td>{{ $entry->driver_operator_name }}</td>
                    <td>
                        {{ $entry->chargeableAccount ? $entry->chargeableAccount->name : '—' }} - {{ $entry->subAccount ? $entry->subAccount->name : '—' }}
                    </td>
                    <td class="text-right">{{ number_format($entry->start_kilometer_reading, 2) }} - {{ number_format($entry->end_kilometer_reading, 2) }}</td>
                    <td class="text-right">{{ number_format($entry->start_hour_reading, 2) }} - {{ number_format($entry->end_hour_reading, 2) }}</td>
                    <td class="text-center">{{ $entry->fuel_order_id ? '#'.$entry->fuel_order_id : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #777;">No utilization logs found matching the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
