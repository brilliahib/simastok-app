<?php

namespace App\Http\Controllers;

use App\Models\PergerakanBarang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Import facade PDF

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $pergerakan = PergerakanBarang::with('barang')
            ->when($tanggalAwal, function ($query) use ($tanggalAwal) {
                return $query->where('tanggal', '>=', $tanggalAwal);
            })
            ->when($tanggalAkhir, function ($query) use ($tanggalAkhir) {
                return $query->where('tanggal', '<=', $tanggalAkhir);
            })
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalMasuk = $pergerakan->where('tipe', 'masuk')->sum('jumlah');
        $totalKeluar = $pergerakan->where('tipe', 'keluar')->sum('jumlah');
        $perubahanBersih = $totalMasuk - $totalKeluar;

        return view('laporan.index', compact(
            'pergerakan',
            'tanggalAwal',
            'tanggalAkhir',
            'totalMasuk',
            'totalKeluar',
            'perubahanBersih'
        ));
    }

    public function exportPDF(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $pergerakan = PergerakanBarang::with('barang')
            ->when($tanggalAwal, function ($query) use ($tanggalAwal) {
                return $query->where('tanggal', '>=', $tanggalAwal);
            })
            ->when($tanggalAkhir, function ($query) use ($tanggalAkhir) {
                return $query->where('tanggal', '<=', $tanggalAkhir);
            })
            ->orderBy('tanggal', 'desc')
            ->get();

        $pdf = PDF::loadView('laporan.pdf', compact('pergerakan', 'tanggalAwal', 'tanggalAkhir'));

        return $pdf->download('laporan-pergerakan-barang-' . date('Y-m-d') . '.pdf');
    }

    public function barangMasuk(Request $request)
    {
        [$pergerakan, $tanggalAwal, $tanggalAkhir] = $this->barangMasukData($request);

        return view('laporan.barang_masuk', compact('pergerakan', 'tanggalAwal', 'tanggalAkhir'));
    }

    public function exportBarangMasukPDF(Request $request)
    {
        [$pergerakan, $tanggalAwal, $tanggalAkhir] = $this->barangMasukData($request);

        $pdf = Pdf::loadView('laporan.barang_masuk_pdf', compact('pergerakan', 'tanggalAwal', 'tanggalAkhir'));

        return $pdf->download('laporan-barang-masuk-' . date('Y-m-d') . '.pdf');
    }

    private function barangMasukData(Request $request): array
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
        ], [
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
        ]);

        $tanggalAwal = $validated['tanggal_awal'] ?? null;
        $tanggalAkhir = $validated['tanggal_akhir'] ?? null;

        $pergerakan = PergerakanBarang::with(['barang', 'notaPenerimaanBarang'])
            ->where('tipe', 'masuk')
            ->when($tanggalAwal, fn ($query) => $query->whereDate('tanggal', '>=', $tanggalAwal))
            ->when($tanggalAkhir, fn ($query) => $query->whereDate('tanggal', '<=', $tanggalAkhir))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return [$pergerakan, $tanggalAwal, $tanggalAkhir];
    }
}
