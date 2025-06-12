<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Robot IoT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Auto-refresh setiap 5 detik -->
    <meta http-equiv="refresh" content="5">
</head>
<body class="bg-light">

    <div class="container py-5">
        <h1 class="mb-4">📡 Robot IoT Dashboard</h1>

        <!-- Log Riwayat -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>🕓 Riwayat Perjalanan</h4>
            <a href="/export" class="btn btn-outline-dark btn-sm">📥 Export CSV</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Berat</th>
                        <th>Jalur</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                        <th>Durasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($log as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">Belum ada log perjalanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS (opsional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
