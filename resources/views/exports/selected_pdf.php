<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; margin: 0 0 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f2f2f2; }
        .muted { color: #666; font-size: 11px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <h1>Selected Students</h1>
    <div class="muted">Generated at: {{ $generatedAt }}</div>

    <table>
        <thead>
            <tr>
                <th style="width: 70px;">Draw #</th>
                <th>Student Name</th>
                <th style="width: 160px;">Drawn At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($selected as $row)
                <tr>
                    <td>{{ $row['draw_no'] ?? '' }}</td>
                    <td>{{ $row['name'] ?? '' }}</td>
                    <td>{{ $row['drawn_at'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No selected students yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
