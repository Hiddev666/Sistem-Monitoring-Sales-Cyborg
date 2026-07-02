<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        .period {
            margin-bottom: 16px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        th {
            background: #1f2937;
            color: #ffffff;
            text-align: left;
        }

        td.number {
            text-align: right;
        }

        .empty {
            text-align: center;
            color: #6b7280;
            padding: 18px;
        }

        .signature-section {
            width: 100%;
            margin-top: 28px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 260px;
            float: right;
            text-align: center;
        }

        .signature-role {
            margin-bottom: 5px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            text-underline-offset: 2px;
            min-height: 25px;
        }

        .signature-clear {
            clear: both;
        }
    </style>
</head>

<body>
    <h1>{{ $title }}</h1>
    <div class="period">Periode: {{ $startDate }} hingga {{ $endDate }}</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                @foreach(array_keys($rows[0] ?? []) as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                @foreach($row as $value)
                <td class="{{ is_numeric($value) ? 'number' : '' }}">
                    @if(is_numeric($value) && $value > 999)
                    {{ number_format($value, 0, ',', '.') }}
                    @else
                    {{ $value }}
                    @endif
                </td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td class="empty" colspan="{{ $columnCount ?? (count(array_keys($rows[0] ?? [])) + 1) }}">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-role">Mengetahui,</div>
            <div class="signature-role">Manager</div>
            <div class="signature-role">&nbsp;&nbsp;</div>
            <br><br>
            <div class="signature-name">{{ $managerName ?? 'Manager' }}</div>
        </div>
        <div class="signature-clear"></div>
    </div>
</body>

</html>
