<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pratinjau Nota Penerimaan Barang</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h1 { margin-bottom: 4px; text-align: center; font-size: 20px; }
        .sample { margin: 0 0 24px; text-align: center; color: #666; font-weight: bold; }
        .details { width: 100%; margin-bottom: 20px; }
        .details td { padding: 4px 0; vertical-align: top; }
        .details td:first-child { width: 125px; }
        table.items { width: 100%; border-collapse: collapse; }
        .items th, .items td { border: 1px solid #555; padding: 8px; }
        .items th { background: #eee; text-align: left; }
        .number { text-align: right; }
        .total { margin-top: 12px; text-align: right; font-weight: bold; }
        .signatures { width: 100%; margin-top: 56px; text-align: center; }
        .signatures td { width: 50%; }
        .signature-space { height: 52px; }
        .notice { margin-top: 24px; color: #666; font-size: 10px; text-align: center; }
    </style>
</head>
<body>
    <h1>Nota Penerimaan Barang</h1>
    <p class="sample">PRATINJAU FORMAT NOTA PENERIMAAN</p>

    <table class="details">
        <tr><td>No. Nota</td><td>: PRATINJAU/NPB/001</td></tr>
        <tr><td>Tanggal Masuk</td><td>: {{ $tanggal->format('d/m/Y') }}</td></tr>
        <tr><td>Penerima</td><td>: {{ $penerima }}</td></tr>
        <tr><td>Keterangan</td><td>: {{ $keterangan }}</td></tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th class="number">Jumlah Masuk</th>
                <th class="number">Harga Satuan (Rp)</th>
                <th class="number">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['nama_barang'] }}</td>
                    <td class="number">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($item['jumlah'] * $item['harga_satuan'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Total: Rp {{ number_format(collect($items)->sum(fn ($item) => $item['jumlah'] * $item['harga_satuan']), 0, ',', '.') }}</p>

    <table class="signatures">
        <tr><td>Penerima</td><td>Petugas</td></tr>
        <tr><td class="signature-space"></td><td class="signature-space"></td></tr>
        <tr><td>{{ $penerima }}</td><td>(____________________)</td></tr>
    </table>

    <p class="notice">Pratinjau format dengan data ilustrasi; bukan bukti transaksi penerimaan barang.</p>
</body>
</html>
