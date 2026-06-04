<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan {{ ucfirst(str_replace('_', ' ', $jenis)) }} {{ $tahun }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #1e3a8a; }
        p { text-align: center; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1e3a8a; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f5f5f5; }
        .footer { margin-top: 30px; text-align: right; color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Sistem Informasi Perpustakaan</h1>
    <p>Laporan {{ ucfirst(str_replace('_', ' ', $jenis)) }} Tahun {{ $tahun }}</p>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        @if($jenis === 'peminjaman')
        <thead>
            <tr>
                <th>No</th><th>Anggota</th><th>Buku</th>
                <th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p['anggota']['user']['name'] ?? '-' }}</td>
                <td>{{ collect($p['detail'])->map(fn($d) => $d['buku']['judul'])->join(', ') }}</td>
                <td>{{ $p['tanggal_pinjam'] }}</td>
                <td>{{ $p['tanggal_jatuh_tempo'] }}</td>
                <td>{{ $p['status'] }}</td>
            </tr>
            @endforeach
        </tbody>

        @elseif($jenis === 'buku_terpopuler')
        <thead>
            <tr><th>No</th><th>Judul Buku</th><th>Kategori</th><th>Total Dipinjam</th></tr>
        </thead>
        <tbody>
            @foreach($data as $i => $b)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $b['judul'] }}</td>
                <td>{{ $b['kategori']['nama'] ?? '-' }}</td>
                <td>{{ $b['total_dipinjam'] }}</td>
            </tr>
            @endforeach
        </tbody>

        @elseif($jenis === 'denda')
        <thead>
            <tr><th>No</th><th>Anggota</th><th>Hari Terlambat</th><th>Total Denda</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($data as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d['peminjaman']['anggota']['user']['name'] ?? '-' }}</td>
                <td>{{ $d['hari_terlambat'] }} hari</td>
                <td>Rp {{ number_format($d['total_denda'], 0, ',', '.') }}</td>
                <td>{{ $d['status'] === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar' }}</td>
            </tr>
            @endforeach
        </tbody>
        @endif
    </table>

    <div class="footer">
        <p>Total Data: {{ count($data) }} | Sistem Informasi Perpustakaan</p>
    </div>
</body>
</html>