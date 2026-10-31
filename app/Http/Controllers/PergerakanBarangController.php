<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\NotaPenerimaanBarang;
use App\Models\PergerakanBarang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PergerakanBarangController extends Controller
{
    public function createMasuk()
    {
        $barangs = Barang::orderBy('nama_barang')->get();
        return view('barang.create_masuk', compact('barangs'));
    }

    public function storeMasuk(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|integer|exists:barangs,id|distinct',
            'items.*.jumlah_masuk' => 'required|integer|min:1',
            'items.*.harga_masuk' => 'required|integer|min:1|max:99999999',
            'tanggal' => 'required|date',
            'penerima' => 'required|string|max:255',
            'nota' => 'required|file|mimes:pdf|max:10240',
            'keterangan' => 'nullable|string',
        ], [
            'items.required' => 'Tambahkan setidaknya satu barang.',
            'items.min' => 'Tambahkan setidaknya satu barang.',
            'items.*.barang_id.required' => 'Pilih barang untuk baris ini.',
            'items.*.barang_id.exists' => 'Barang yang dipilih tidak ditemukan.',
            'items.*.barang_id.distinct' => 'Barang ini sudah dipilih pada baris lain.',
            'items.*.jumlah_masuk.required' => 'Masukkan jumlah barang untuk baris ini.',
            'items.*.jumlah_masuk.integer' => 'Jumlah barang harus berupa bilangan bulat.',
            'items.*.jumlah_masuk.min' => 'Jumlah barang minimal 1.',
            'items.*.harga_masuk.required' => 'Masukkan harga masuk untuk baris ini.',
            'items.*.harga_masuk.integer' => 'Harga masuk harus berupa bilangan bulat Rupiah.',
            'items.*.harga_masuk.min' => 'Harga masuk minimal Rp 1.',
            'items.*.harga_masuk.max' => 'Harga masuk maksimal Rp 99.999.999.',
            'penerima.required' => 'Masukkan nama penerima barang.',
            'penerima.max' => 'Nama penerima maksimal 255 karakter.',
        ]);

        $filePath = $request->file('nota')->store('nota-penerimaan', 'local');

        if (! $filePath) {
            throw ValidationException::withMessages([
                'nota' => 'Gagal menyimpan file nota. Silakan coba lagi.',
            ]);
        }

        try {
            DB::transaction(function () use ($validated, $filePath) {
                $nota = NotaPenerimaanBarang::create([
                    'penerima' => $validated['penerima'],
                    'tanggal' => $validated['tanggal'],
                    'file_path' => $filePath,
                ]);

                foreach ($validated['items'] as $item) {
                    $barang = Barang::whereKey($item['barang_id'])->lockForUpdate()->firstOrFail();
                    $barang->jumlah += $item['jumlah_masuk'];
                    $barang->save();

                    PergerakanBarang::create([
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah_masuk'],
                        'harga_satuan' => $item['harga_masuk'],
                        'tipe' => 'masuk',
                        'tanggal' => $validated['tanggal'],
                        'penerima' => $validated['penerima'],
                        'nota_penerimaan_barang_id' => $nota->id,
                        'keterangan' => $validated['keterangan'] ?? null,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($filePath);
            throw $exception;
        }

        return redirect()->route('dashboard')->with('success', 'Berhasil mencatat barang masuk.');
    }

    public function downloadNota(NotaPenerimaanBarang $notaPenerimaanBarang)
    {
        if (! Storage::disk('local')->exists($notaPenerimaanBarang->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($notaPenerimaanBarang->file_path, 'nota-penerimaan.pdf');
    }

    public function createKeluar()
    {
        $barangs = Barang::orderBy('nama_barang')->get();
        return view('barang.create_keluar', compact('barangs'));
    }

    public function storeKeluar(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'jumlah_keluar' => 'required|integer|min:1',
            'harga_keluar' => 'required|integer|min:1|max:99999999',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $barang = Barang::whereKey($validated['barang_id'])->lockForUpdate()->firstOrFail();

            if ($validated['jumlah_keluar'] > $barang->jumlah) {
                throw ValidationException::withMessages([
                    'jumlah_keluar' => "Jumlah keluar melebihi stok yang ada ({$barang->jumlah}).",
                ]);
            }

            $barang->jumlah -= $validated['jumlah_keluar'];
            $barang->save();

            PergerakanBarang::create([
                'barang_id' => $barang->id,
                'jumlah' => $validated['jumlah_keluar'],
                'harga_satuan' => $validated['harga_keluar'],
                'tipe' => 'keluar',
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        });

        return redirect()->route('dashboard')->with('success', 'Berhasil mencatat barang keluar.');
    }
}
