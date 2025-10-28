<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Daily Report - {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</title>
  <style>
    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 12px;
      color: #333;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 2px solid #4F46E5;
      padding-bottom: 15px;
    }

    .header h1 {
      margin: 0;
      color: #4F46E5;
      font-size: 24px;
    }

    .header p {
      margin: 5px 0 0 0;
      color: #666;
      font-size: 14px;
    }

    .info-box {
      background: #f3f4f6;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th {
      background: #4F46E5;
      color: white;
      padding: 10px;
      text-align: left;
      font-weight: bold;
      font-size: 11px;
      text-transform: uppercase;
    }

    td {
      padding: 10px;
      border-bottom: 1px solid #e5e7eb;
    }

    tr:nth-child(even) {
      background: #f9fafb;
    }

    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
    }

    .font-semibold {
      font-weight: 600;
    }

    .text-blue {
      color: #2563eb;
    }

    .text-purple {
      color: #7c3aed;
    }

    .text-orange {
      color: #ea580c;
    }

    .text-green {
      color: #16a34a;
    }

    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 1px solid #e5e7eb;
      text-align: center;
      color: #666;
      font-size: 10px;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #9ca3af;
      font-style: italic;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>Daily Activity Report</h1>
    <p>{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</p>
  </div>

  @if ($branchId)
    <div class="info-box">
      <strong>Branch Filter:</strong>
      {{ \App\Models\CompanyBranch::find($branchId)->branch_name ?? 'N/A' }}
    </div>
  @endif

  @if ($reports->count() > 0)
    <table>
      <thead>
        <tr>
          <th>Branch</th>
          <th class="text-center">Devices</th>
          <th class="text-center">Detections</th>
          <th class="text-center">Events</th>
          <th class="text-center">Unique Persons</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($reports as $report)
          <tr>
            <td class="font-semibold">{{ $report->branch->branch_name ?? 'Overall' }}</td>
            <td class="text-center text-blue font-semibold">{{ $report->total_devices }}</td>
            <td class="text-center text-purple font-semibold">{{ number_format($report->total_detections) }}</td>
            <td class="text-center text-orange font-semibold">{{ number_format($report->total_events) }}</td>
            <td class="text-center text-green font-semibold">{{ $report->unique_person_count }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    @php
      $totalDevices = $reports->sum('total_devices');
      $totalDetections = $reports->sum('total_detections');
      $totalEvents = $reports->sum('total_events');
      $totalPersons = $reports->sum('unique_person_count');
    @endphp

    <table style="margin-top: 20px;">
      <thead>
        <tr>
          <th style="background: #059669;">Summary Totals</th>
          <th class="text-center" style="background: #059669;">{{ $totalDevices }}</th>
          <th class="text-center" style="background: #059669;">{{ number_format($totalDetections) }}</th>
          <th class="text-center" style="background: #059669;">{{ number_format($totalEvents) }}</th>
          <th class="text-center" style="background: #059669;">{{ $totalPersons }}</th>
        </tr>
      </thead>
    </table>
  @else
    <div class="no-data">
      No reports found for this date
    </div>
  @endif

  <div class="footer">
    Generated on {{ now()->format('Y-m-d H:i:s') }} | CCTV Dashboard System
  </div>
</body>

</html>
