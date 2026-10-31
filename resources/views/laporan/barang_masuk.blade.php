<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Barang Masuk</h2>
    </x-slot>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h4 class="mb-0">Filter Laporan</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.barangMasuk') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                    <input type="date" class="form-control @error('tanggal_awal') is-invalid @enderror" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
                    @error('tanggal_awal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control @error('tanggal_akhir') is-invalid @enderror" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                    @error('tanggal_akhir')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                    <a href="{{ route('laporan.barangMasuk.export', request()->query()) }}" class="btn btn-success flex-fill" target="_blank" rel="noopener">PDF</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0">Hasil Laporan</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Penerima</th>
                            <th>Nota</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pergerakan as $item)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                                <td>{{ $item->barang->nama_barang }}</td>
                                <td>{{ $item->jumlah }}</td>
                                <td>{{ filled($item->penerima) ? $item->penerima : 'Belum tersedia' }}</td>
                                <td>
                                    @if ($item->notaPenerimaanBarang)
                                        <a href="{{ route('barang.masuk.nota', $item->notaPenerimaanBarang) }}" target="_blank" rel="noopener">Lihat PDF</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $item->keterangan ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada barang masuk untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
