<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Dashboard Report</title>
  <style>
    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 11px;
      color: #333;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 3px solid #4F46E5;
      padding-bottom: 20px;
    }

    .header h1 {
      margin: 0;
      color: #4F46E5;
      font-size: 28px;
      font-weight: bold;
    }

    .header p {
      margin: 8px 0 0 0;
      color: #666;
      font-size: 13px;
    }

    .filter-info {
      background: #EEF2FF;
      padding: 12px;
      margin-bottom: 25px;
      border-radius: 5px;
      border-left: 4px solid #4F46E5;
      font-size: 11px;
    }

    .filter-info strong {
      color: #312E81;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: #F9FAFB;
      border: 1px solid #E5E7EB;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
    }

    .stat-card .title {
      font-size: 10px;
      color: #6B7280;
      text-transform: uppercase;
      margin-bottom: 8px;
    }

    .stat-card .value {
      font-size: 24px;
      font-weight: bold;
      color: #1F2937;
    }

    .stat-card.blue {
      border-left: 4px solid #3B82F6;
    }

    .stat-card.green {
      border-left: 4px solid #10B981;
    }

    .stat-card.purple {
      border-left: 4px solid #8B5CF6;
    }

    .stat-card.orange {
      border-left: 4px solid #F59E0B;
    }

    .section-title {
      font-size: 16px;
      font-weight: bold;
      color: #1F2937;
      margin: 25px 0 15px 0;
      padding-bottom: 8px;
      border-bottom: 2px solid #E5E7EB;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th {
      background: #4F46E5;
      color: white;
      padding: 10px 8px;
      text-align: left;
      font-weight: bold;
      font-size: 10px;
      text-transform: uppercase;
    }

    td {
      padding: 10px 8px;
      border-bottom: 1px solid #E5E7EB;
      font-size: 11px;
    }

    tr:nth-child(even) {
      background: #F9FAFB;
    }

    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
    }

    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: bold;
    }

    .badge-blue {
      background: #DBEAFE;
      color: #1E40AF;
    }

    .chart-container {
      margin: 20px 0;
      padding: 15px;
      background: #F9FAFB;
      border: 1px solid #E5E7EB;
      border-radius: 8px;
    }

    .chart-bar {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
    }

    .chart-bar .label {
      width: 100px;
      font-size: 10px;
      color: #4B5563;
    }

    .chart-bar .bar-container {
      flex: 1;
      background: #E5E7EB;
      height: 20px;
      border-radius: 4px;
      overflow: hidden;
      margin: 0 10px;
    }

    .chart-bar .bar {
      height: 100%;
      background: linear-gradient(90deg, #4F46E5, #6366F1);
    }

    .chart-bar .value {
      width: 60px;
      text-align: right;
      font-size: 10px;
      font-weight: bold;
      color: #1F2937;
    }

    .footer {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 2px solid #E5E7EB;
      text-align: center;
      color: #6B7280;
      font-size: 10px;
    }

    .page-break {
      page-break-after: always;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>Analytics Dashboard Report</h1>
    <p>Period: {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} -
      {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
    <p style="margin-top: 5px; font-size: 11px;">Generated on {{ now()->format('F d, Y - H:i:s') }}</p>
  </div>

  @if ($branchId)
    <div class="filter-info">
      <strong>📍 Branch Filter:</strong>
      {{ \App\Models\CompanyBranch::find($branchId)->branch_name ?? 'N/A' }}
    </div>
  @endif

  <!-- Statistics Grid -->
  <div class="stats-grid">
    <div class="stat-card blue">
      <div class="title">Total Detections</div>
      <div class="value">{{ number_format($totalDetections) }}</div>
    </div>
    <div class="stat-card green">
      <div class="title">Unique Persons</div>
      <div class="value">{{ number_format($uniquePersons) }}</div>
    </div>
    <div class="stat-card purple">
      <div class="title">Active Branches</div>
      <div class="value">{{ number_format($uniqueBranches) }}</div>
    </div>
    <div class="stat-card orange">
      <div class="title">Active Devices</div>
      <div class="value">{{ number_format($uniqueDevices) }}</div>
    </div>
  </div>

  <!-- Daily Trend Chart -->
  <div class="section-title">📈 Detection Trend (Daily)</div>
  <div class="chart-container">
    @php
      $maxCount = $dailyTrend->max('count') ?: 1;
    @endphp
    @foreach ($dailyTrend as $item)
      <div class="chart-bar">
        <div class="label">{{ \Carbon\Carbon::parse($item->date)->format('M d') }}</div>
        <div class="bar-container">
          <div class="bar" style="width: {{ ($item->count / $maxCount) * 100 }}%;"></div>
        </div>
        <div class="value">{{ number_format($item->count) }}</div>
      </div>
    @endforeach
  </div>

  <!-- Top Branches Table -->
  <div class="section-title">🏆 Top Branches by Detections</div>
  <table>
    <thead>
      <tr>
        <th style="width: 10%;">Rank</th>
        <th style="width: 40%;">Branch Name</th>
        <th style="width: 30%;">City</th>
        <th class="text-center" style="width: 20%;">Detections</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($topBranches as $index => $item)
        <tr>
          <td class="text-center">
            <span class="badge badge-blue">{{ $index + 1 }}</span>
          </td>
          <td><strong>{{ $item->branch->branch_name ?? 'N/A' }}</strong></td>
          <td>{{ $item->branch->city_name ?? '-' }}</td>
          <td class="text-center">
            <strong>{{ number_format($item->detection_count) }}</strong>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Summary Box -->
  <div style="margin-top: 25px; padding: 15px; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px;">
    <strong style="color: #166534;">📊 Report Summary:</strong>
    Total of <strong>{{ number_format($totalDetections) }}</strong> detections
    from <strong>{{ number_format($uniquePersons) }}</strong> unique persons
    across <strong>{{ number_format($uniqueBranches) }}</strong> branches
    and <strong>{{ number_format($uniqueDevices) }}</strong> devices
    during the selected period.
  </div>

  <div class="footer">
    CCTV Dashboard System | Analytics Report | Confidential
  </div>
</body>

</html>
