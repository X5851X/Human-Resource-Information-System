<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .date {
            font-size: 11px;
            margin-bottom: 20px;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .summary-grid {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .summary-item {
            width: 20%;
            padding: 10px;
            border: 1px solid #ddd;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #4361EE;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-present {
            background-color: rgba(67, 97, 238, 0.15);
            color: #4361EE;
        }
        .status-late {
            background-color: rgba(255, 193, 7, 0.15);
            color: #FFC107;
        }
        .status-absent {
            background-color: rgba(220, 53, 69, 0.15);
            color: #DC3545;
        }
        .status-permit {
            background-color: rgba(108, 117, 125, 0.15);
            color: #6C757D;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN KEHADIRAN</div>
        <div class="subtitle">{{ $title }}</div>
        <div class="date">Dicetak pada: {{ $date }}</div>
    </div>
    
    <div class="summary">
        <div class="summary-title">RINGKASAN</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Hadir</div>
                <div class="summary-value">{{ $summary['present'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Terlambat</div>
                <div class="summary-value">{{ $summary['late'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Tidak Hadir</div>
                <div class="summary-value">{{ $summary['absent'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Izin</div>
                <div class="summary-value">{{ $summary['permit'] }}</div>
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $record['nama'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($record['tanggal'])->format('d/m/Y') }}</td>
                    <td>{{ $record['jamMasuk'] ?? '-' }}</td>
                    <td>{{ $record['jamKeluar'] ?? '-' }}</td>
                    <td>
                        <span class="status-badge 
                            @if($record['status'] == 'Hadir') status-present 
                            @elseif($record['status'] == 'Terlambat') status-late 
                            @elseif($record['status'] == 'Tidak Hadir') status-absent 
                            @elseif($record['status'] == 'Izin') status-permit 
                            @endif">
                            {{ $record['status'] }}
                        </span>
                    </td>
                    <td>{{ $record['keterangan'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data untuk ditampilkan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Human Resource Information System &copy; 2025</p>
    </div>
</body>
</html>
