<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Utilization Logs - {{ $asset->fleet_no }}</title>
    <!-- Use a simple Bootstrap-like print stylesheet -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #212529;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 15px;
        }
        .header h2 {
            margin: 0 0 10px;
            font-size: 22px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #000;
        }
        .header p {
            margin: 5px 0;
            color: #495057;
            font-weight: 500;
        }
        .filters {
            margin-bottom: 20px;
            font-size: 10px;
            color: #6c757d;
            background-color: #f8f9fa;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .filters strong {
            color: #212529;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f1f3f5;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.05em;
            color: #495057;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .fw-bold { font-weight: 700; }
        .text-primary { color: #0d6efd; }
        
        @media print {
            body { padding: 0; }
            .filters { border: 1px solid #dee2e6; background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
            th { background-color: #f1f3f5 !important; -webkit-print-color-adjust: exact; }
            @page { margin: 1.5cm; size: landscape; }
            .d-print-none { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>Utilization Logs: {{ $asset->fleet_no }}</h2>
        <p>
            <span class="fw-bold">Category:</span> {{ $asset->assetType->name }} &nbsp;&nbsp;|&nbsp;&nbsp; 
            <span class="fw-bold">Plate No:</span> {{ $asset->plate_no ?? 'N/A' }} &nbsp;&nbsp;|&nbsp;&nbsp; 
            <span class="fw-bold">Fuel:</span> {{ $asset->fuel_type }}
        </p>
    </div>

    @if($request->filled('start_date') || $request->filled('end_date') || $request->filled('chargeable_account_id') || $request->filled('fuel_order_id'))
    <div class="filters">
        <strong>Filters Applied:</strong> &nbsp;&nbsp;
        @if($request->filled('start_date')) <span class="nowrap">Date From: <u class="fw-bold">{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}</u></span> &nbsp;&nbsp; @endif
        @if($request->filled('end_date')) <span class="nowrap">Date To: <u class="fw-bold">{{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</u></span> &nbsp;&nbsp; @endif
        @if($request->filled('chargeable_account_id')) <span class="nowrap">Account: <u class="fw-bold">{{ App\Models\ChargeableAccount::find($request->chargeable_account_id)->name ?? 'Unknown' }}</u></span> &nbsp;&nbsp; @endif
        @if($request->filled('fuel_order_id')) <span class="nowrap">Order ID: <u class="fw-bold">#{{ $request->fuel_order_id }}</u></span> @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th class="nowrap">Date & Time</th>
                <th>Reference</th>
                <th>Particulars</th>
                <th>Personnel</th>
                <th>Account / Sub Account</th>
                <th class="text-end nowrap">KM (Start-End)</th>
                <th class="text-end nowrap">HRS (Start-End)</th>
                <th class="text-center nowrap">Fuel Order</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td class="nowrap">
                        <div class="fw-bold">{{ $entry->date->format('M d, Y') }}</div>
                        <div style="font-size: 9px; color: #6c757d;">
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
                                <br>({{ number_format($hrs, 2) }} hrs)
                            @endif
                        </div>
                    </td>
                    <td class="nowrap fw-bold">{{ $entry->reference }}</td>
                    <td>{{ $entry->particulars }}</td>
                    <td>{{ $entry->driver_operator_name }}</td>
                    <td>
                        <div class="fw-bold">{{ $entry->chargeableAccount ? $entry->chargeableAccount->name : '—' }}</div>
                        <div style="font-size: 9px; color: #6c757d;">{{ $entry->subAccount ? $entry->subAccount->name : '—' }}</div>
                    </td>
                    <td class="text-end nowrap font-monospace">
                        {{ number_format($entry->start_kilometer_reading, 2) }}<br>
                        {{ number_format($entry->end_kilometer_reading, 2) }}
                    </td>
                    <td class="text-end nowrap font-monospace">
                        {{ number_format($entry->start_hour_reading, 2) }}<br>
                        {{ number_format($entry->end_hour_reading, 2) }}
                    </td>
                    <td class="text-center">
                        @if($entry->fuel_order_id)
                            <span class="fw-bold">#{{ $entry->fuel_order_id }}</span>
                        @else
                            <span style="color: #dee2e6;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 40px; color: #adb5bd; font-style: italic;">
                        No utilization logs found matching the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
