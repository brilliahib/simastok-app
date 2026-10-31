<?php

use App\Models\Barang;
use App\Models\NotaPenerimaanBarang;
use App\Models\PergerakanBarang;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->create());
});

function barangUntukNota(string $nama): Barang
{
    return Barang::create([
        'nama_barang' => $nama,
        'jumlah' => 5,
        'harga' => 1000,
        'tanggal_masuk' => '2026-10-01',
    ]);
}

function dataNotaMasuk(array $items, ?UploadedFile $nota = null): array
{
    return [
        'items' => $items,
        'tanggal' => '2026-10-31',
        'penerima' => 'Siti Aminah',
        'keterangan' => 'Penerimaan stok',
        'nota' => $nota ?? UploadedFile::fake()->createWithContent('nota.pdf', "%PDF-1.4\nNota penerimaan\n%%EOF"),
    ];
}

it('stores one receipt linked to every movement in a multi-item intake', function () {
    $pertama = barangUntukNota('Kertas');
    $kedua = barangUntukNota('Tinta');

    $response = $this->post(route('barang.storeMasuk'), dataNotaMasuk([
        ['barang_id' => $pertama->id, 'jumlah_masuk' => 3, 'harga_masuk' => 1000],
        ['barang_id' => $kedua->id, 'jumlah_masuk' => 2, 'harga_masuk' => 1500],
    ]));

    $response->assertRedirect(route('dashboard'));
    expect($pertama->fresh()->jumlah)->toBe(8)
        ->and($kedua->fresh()->jumlah)->toBe(7);

    $movements = PergerakanBarang::orderBy('id')->get();
    expect($movements)->toHaveCount(2)
        ->and($movements->pluck('penerima')->unique()->all())->toBe(['Siti Aminah'])
        ->and($movements->pluck('nota_penerimaan_barang_id')->unique())->toHaveCount(1);

    $nota = NotaPenerimaanBarang::firstOrFail();
    expect($nota->penerima)->toBe('Siti Aminah')
        ->and($nota->pergerakanBarangs)->toHaveCount(2);
    Storage::disk('local')->assertExists($nota->file_path);
});

it('rejects a missing recipient or PDF without changing stock', function () {
    $barang = barangUntukNota('Kertas');
    $data = dataNotaMasuk([
        ['barang_id' => $barang->id, 'jumlah_masuk' => 3, 'harga_masuk' => 1000],
    ]);
    unset($data['penerima'], $data['nota']);

    $this->post(route('barang.storeMasuk'), $data)
        ->assertSessionHasErrors(['penerima', 'nota']);

    expect($barang->fresh()->jumlah)->toBe(5)
        ->and(PergerakanBarang::count())->toBe(0)
        ->and(NotaPenerimaanBarang::count())->toBe(0);
});

it('rejects non-PDF uploads without changing stock', function () {
    $barang = barangUntukNota('Kertas');

    $this->post(route('barang.storeMasuk'), dataNotaMasuk([
        ['barang_id' => $barang->id, 'jumlah_masuk' => 3, 'harga_masuk' => 1000],
    ], UploadedFile::fake()->create('nota.txt', 1, 'text/plain')))
        ->assertSessionHasErrors('nota');

    expect($barang->fresh()->jumlah)->toBe(5)
        ->and(PergerakanBarang::count())->toBe(0);
});

it('rejects PDFs larger than 10 MB without changing stock', function () {
    $barang = barangUntukNota('Kertas');

    $this->post(route('barang.storeMasuk'), dataNotaMasuk([
        ['barang_id' => $barang->id, 'jumlah_masuk' => 3, 'harga_masuk' => 1000],
    ], UploadedFile::fake()->create('nota.pdf', 10241, 'application/pdf')))
        ->assertSessionHasErrors('nota');

    expect($barang->fresh()->jumlah)->toBe(5)
        ->and(PergerakanBarang::count())->toBe(0);
});

it('removes the uploaded file if saving a movement fails', function () {
    $barang = barangUntukNota('Kertas');
    PergerakanBarang::creating(function () {
        throw new \RuntimeException('Movement persistence failed.');
    });

    $this->post(route('barang.storeMasuk'), dataNotaMasuk([
        ['barang_id' => $barang->id, 'jumlah_masuk' => 3, 'harga_masuk' => 1000],
    ]))->assertStatus(500);

    expect($barang->fresh()->jumlah)->toBe(5)
        ->and(NotaPenerimaanBarang::count())->toBe(0)
        ->and(PergerakanBarang::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('nota-penerimaan'))->toBe([]);
});

it('serves an uploaded receipt to authenticated users and shows its report link', function () {
    $barang = barangUntukNota('Kertas');
    $this->post(route('barang.storeMasuk'), dataNotaMasuk([
        ['barang_id' => $barang->id, 'jumlah_masuk' => 3, 'harga_masuk' => 1000],
    ]));
    $nota = NotaPenerimaanBarang::firstOrFail();

    $this->get(route('barang.masuk.nota', $nota))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
    $this->get(route('laporan.barangMasuk'))
        ->assertOk()
        ->assertSee(route('barang.masuk.nota', $nota));
});

it('returns not found when the stored receipt file is missing', function () {
    $nota = NotaPenerimaanBarang::create([
        'penerima' => 'Siti Aminah',
        'tanggal' => '2026-10-31',
        'file_path' => 'nota-penerimaan/missing.pdf',
    ]);

    $this->get(route('barang.masuk.nota', $nota))->assertNotFound();
});

it('requires authentication to retrieve a receipt PDF', function () {
    $nota = NotaPenerimaanBarang::create([
        'penerima' => 'Siti Aminah',
        'tanggal' => '2026-10-31',
        'file_path' => 'nota-penerimaan/private.pdf',
    ]);
    auth()->logout();

    $this->get(route('barang.masuk.nota', $nota))->assertRedirect(route('login'));
});

it('keeps legacy incoming movements reportable without a receipt link', function () {
    $barang = barangUntukNota('Kertas');
    PergerakanBarang::create([
        'barang_id' => $barang->id,
        'jumlah' => 2,
        'harga_satuan' => 1000,
        'tipe' => 'masuk',
        'tanggal' => '2026-10-01',
        'penerima' => 'Siti Aminah',
    ]);

    $this->get(route('laporan.barangMasuk'))
        ->assertOk()
        ->assertSee('Siti Aminah')
        ->assertDontSee('/barang/masuk/nota/');
});
