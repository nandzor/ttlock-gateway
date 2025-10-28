<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export HPS Emas - {{ date('d/m/Y H:i:s') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1f2937;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #6b7280;
        }

        .filters {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #374151;
        }

        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }

        .filter-label {
            font-weight: bold;
            color: #6b7280;
        }

        .filter-value {
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #1f2937;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status-active {
            color: #059669;
            font-weight: bold;
        }

        .status-inactive {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
        }

        .summary {
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #1e40af;
        }

        .summary-item {
            display: inline-block;
            margin-right: 30px;
            margin-bottom: 5px;
        }

        .summary-label {
            font-weight: bold;
            color: #1e40af;
        }

        .summary-value {
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data HPS Emas</h1>
        <p>Export tanggal: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Filter yang diterapkan:</h3>
        @if(!empty($filters['jenis_barang']))
            <div class="filter-item">
                <span class="filter-label">Jenis Barang:</span>
                <span class="filter-value">{{ $filters['jenis_barang'] }}</span>
            </div>
        @endif
        @if(!empty($filters['kadar_karat']))
            <div class="filter-item">
                <span class="filter-label">Kadar Karat:</span>
                <span class="filter-value">{{ $filters['kadar_karat'] }}K</span>
            </div>
        @endif
        @if(isset($filters['active']))
            <div class="filter-item">
                <span class="filter-label">Status:</span>
                <span class="filter-value">{{ $filters['active'] ? 'Active' : 'Inactive' }}</span>
            </div>
        @endif
    </div>
    @endif

    @if(!empty($statistics))
    <div class="summary">
        <h3>Ringkasan Data:</h3>
        <div class="summary-item">
            <span class="summary-label">Total Items:</span>
            <span class="summary-value">{{ number_format($statistics['total_items']) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Active Items:</span>
            <span class="summary-value">{{ number_format($statistics['active_items']) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Value:</span>
            <span class="summary-value">Rp {{ number_format($statistics['total_value'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Average Value:</span>
            <span class="summary-value">Rp {{ number_format($statistics['average_value'], 0, ',', '.') }}</span>
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Jenis Barang</th>
                <th style="width: 15%;">STLE (Rp)</th>
                <th style="width: 10%;">Kadar Karat</th>
                <th style="width: 10%;">Berat (Gram)</th>
                <th style="width: 15%;">Nilai Taksiran (Rp)</th>
                <th style="width: 8%;">LTV (%)</th>
                <th style="width: 15%;">Uang Pinjaman (Rp)</th>
                <th style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hpsEmas as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->jenis_barang }}</td>
                <td class="text-right">{{ number_format($item->stle_rp, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->kadar_karat }}K</td>
                <td class="text-right">{{ number_format($item->berat_gram, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->nilai_taksiran_rp, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->ltv, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->uang_pinjaman_rp, 0, ',', '.') }}</td>
                <td class="text-center {{ $item->active ? 'status-active' : 'status-inactive' }}">
                    {{ $item->active ? 'Active' : 'Inactive' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data HPS Emas ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis pada {{ date('d/m/Y H:i:s') }}</p>
        <p>Total data: {{ count($hpsEmas) }} item</p>
    </div>
</body>
</html>
