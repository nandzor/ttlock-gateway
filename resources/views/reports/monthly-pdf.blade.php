<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Monthly Report - {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</title>
  <style>
    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 10px;
      color: #333;
    }

    .header {
      text-align: center;
      margin-bottom: 25px;
      border-bottom: 3px solid #F59E0B;
      padding-bottom: 15px;
    }

    .header h1 {
      margin: 0;
      color: #F59E0B;
      font-size: 26px;
      font-weight: bold;
    }

    .header p {
      margin: 5px 0 0 0;
      color: #666;
      font-size: 13px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-bottom: 25px;
    }

    .stat-card {
      background: #F9FAFB;
      border: 1px solid #E5E7EB;
      border-radius: 6px;
      padding: 12px;
      text-align: center;
    }

    .stat-card .title {
      font-size: 9px;
      color: #6B7280;
      text-transform: uppercase;
      margin-bottom: 6px;
    }

    .stat-card .value {
      font-size: 20px;
      font-weight: bold;
      color: #1F2937;
    }

    .stat-card.blue {
      border-left: 3px solid #3B82F6;
    }

    .stat-card.purple {
      border-left: 3px solid #8B5CF6;
    }

    .stat-card.orange {
      border-left: 3px solid #F59E0B;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      font-size: 9px;
    }

    th {
      background: #F59E0B;
      color: white;
      padding: 8px 5px;
      text-align: left;
      font-weight: bold;
      font-size: 9px;
      text-transform: uppercase;
    }

    td {
      padding: 7px 5px;
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

    .footer-row {
      background: #FEF3C7 !important;
      font-weight: bold;
      border-top: 2px solid #F59E0B;
    }

    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 1px solid #e5e7eb;
      text-align: center;
      color: #666;
      font-size: 9px;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #9ca3af;
      font-style: italic;
    }

    .filter-info {
      background: #FEF3C7;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
      border-left: 3px solid #F59E0B;
      font-size: 10px;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>Monthly Activity Report</h1>
    <p>{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</p>
    <p style="margin-top: 3px; font-size: 11px;">Generated on {{ now()->format('F d, Y - H:i:s') }}</p>
  </div>

  @if ($branchId)
    <div class="filter-info">
      <strong>📍 Branch Filter:</strong>
      {{ \App\Models\CompanyBranch::find($branchId)->branch_name ?? 'N/A' }}
    </div>
  @endif

  <!-- Summary Statistics -->
  <div class="stats-grid">
    <div class="stat-card blue">
      <div class="title">Total Detections</div>
      <div class="value">{{ number_format($monthlyStats['total_detections']) }}</div>
    </div>
    <div class="stat-card purple">
      <div class="title">Unique Persons</div>
      <div class="value">{{ number_format($monthlyStats['unique_persons']) }}</div>
    </div>
    <div class="stat-card orange">
      <div class="title">Total Events</div>
      <div class="value">{{ number_format($monthlyStats['total_events']) }}</div>
    </div>
  </div>

  @if ($reports->count() > 0)
    <table>
      <thead>
        <tr>
          <th style="width: 12%;">Date</th>
          <th style="width: 20%;">Branch</th>
          <th style="width: 13%;">City</th>
          <th class="text-center" style="width: 10%;">Devices</th>
          <th class="text-center" style="width: 13%;">Detections</th>
          <th class="text-center" style="width: 12%;">Events</th>
          <th class="text-center" style="width: 10%;">Persons</th>
          <th class="text-center" style="width: 10%;">Avg/Day</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($reports as $report)
          <tr>
            <td>{{ \Carbon\Carbon::parse($report->report_date)->format('M d') }}</td>
            <td><strong>{{ $report->branch->branch_name ?? 'Overall' }}</strong></td>
            <td>{{ $report->branch->city ?? '-' }}</td>
            <td class="text-center">{{ $report->total_devices }}</td>
            <td class="text-center"><strong>{{ number_format($report->total_detections) }}</strong></td>
            <td class="text-center">{{ number_format($report->total_events) }}</td>
            <td class="text-center">{{ $report->unique_person_count }}</td>
            <td class="text-center">{{ number_format($report->total_detections / max($report->total_devices, 1), 1) }}
            </td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="footer-row">
          <td colspan="3"><strong>Monthly Total</strong></td>
          <td class="text-center"><strong>{{ $totalDevices ?? 0 }}</strong></td>
          <td class="text-center"><strong>{{ number_format($monthlyStats['total_detections']) }}</strong></td>
          <td class="text-center"><strong>{{ number_format($monthlyStats['total_events']) }}</strong></td>
          <td class="text-center"><strong>{{ $monthlyStats['unique_persons'] }}</strong></td>
          <td class="text-center"><strong>{{ number_format($avgDetectionsPerDay ?? 0, 1) }}</strong></td>
        </tr>
      </tfoot>
    </table>
  @else
    <div class="no-data">
      No reports found for this month
    </div>
  @endif

  <div class="footer">
    CCTV Dashboard System | Monthly Report {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }} | Confidential
  </div>
</body>

</html>
