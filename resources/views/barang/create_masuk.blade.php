<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Catat Barang Masuk
        </h2>
    </x-slot>

    @php
        $oldItems = old('items');
        if (!is_array($oldItems) || $oldItems === []) {
            $oldItems = [['barang_id' => '', 'jumlah_masuk' => '', 'harga_masuk' => '']];
        }
        $oldItems = array_values($oldItems);
    @endphp

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Form Barang Masuk</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('barang.storeMasuk') }}" method="POST" enctype="multipart/form-data"
                        x-data="{
                            rows: @js($oldItems).map((row, index) => ({
                                key: index,
                                barang_id: row?.barang_id ?? '',
                                jumlah_masuk: row?.jumlah_masuk ?? '',
                                harga_masuk: row?.harga_masuk ?? '',
                            })),
                            fieldErrors: @js($errors->getMessages()),
                            nextKey: @js(count($oldItems)),
                            addRow() {
                                this.rows.push({ key: this.nextKey++, barang_id: '', jumlah_masuk: '', harga_masuk: '' });
                            },
                            removeRow(index) {
                                if (this.rows.length > 1) this.rows.splice(index, 1);
                            },
                        }">
                        @csrf

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">Barang yang Masuk</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm" @click="addRow()">
                                    Tambah Barang
                                </button>
                            </div>

                            @error('items')
                                <div class="alert alert-danger py-2" role="alert">{{ $message }}</div>
                            @enderror

                            <template x-for="(item, index) in rows" :key="item.key">
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0" x-text="'Barang ' + (index + 1)"></h6>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            :disabled="rows.length === 1" @click="removeRow(index)"
                                            aria-label="Hapus baris barang">
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" :for="'items-' + index + '-barang-id'">Pilih Barang</label>
                                        <select class="form-select"
                                            :class="fieldErrors['items.' + index + '.barang_id'] ? 'is-invalid' : ''"
                                            :id="'items-' + index + '-barang-id'"
                                            :name="'items[' + index + '][barang_id]'" x-model="item.barang_id" required>
                                            <option value="">-- Pilih Barang untuk Ditambah Stoknya --</option>
                                            @foreach ($barangs as $barang)
                                                <option value="{{ $barang->id }}">
                                                    {{ $barang->nama_barang }} (Stok Saat Ini: {{ $barang->jumlah }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <template x-if="fieldErrors['items.' + index + '.barang_id']">
                                            <div class="invalid-feedback d-block"
                                                x-text="fieldErrors['items.' + index + '.barang_id'][0]"></div>
                                        </template>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label" :for="'items-' + index + '-jumlah-masuk'">Jumlah Masuk</label>
                                        <input type="number" min="1" step="1" class="form-control"
                                            :class="fieldErrors['items.' + index + '.jumlah_masuk'] ? 'is-invalid' : ''"
                                            :id="'items-' + index + '-jumlah-masuk'"
                                            :name="'items[' + index + '][jumlah_masuk]'" x-model="item.jumlah_masuk" required>
                                        <template x-if="fieldErrors['items.' + index + '.jumlah_masuk']">
                                            <div class="invalid-feedback d-block"
                                                x-text="fieldErrors['items.' + index + '.jumlah_masuk'][0]"></div>
                                        </template>
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label" :for="'items-' + index + '-harga-masuk'">Harga Masuk per Unit (Rp)</label>
                                        <input type="number" min="1" max="99999999" step="1" class="form-control"
                                            :class="fieldErrors['items.' + index + '.harga_masuk'] ? 'is-invalid' : ''"
                                            :id="'items-' + index + '-harga-masuk'"
                                            :name="'items[' + index + '][harga_masuk]'" x-model="item.harga_masuk" required>
                                        <template x-if="fieldErrors['items.' + index + '.harga_masuk']">
                                            <div class="invalid-feedback d-block"
                                                x-text="fieldErrors['items.' + index + '.harga_masuk'][0]"></div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control @error('tanggal') is-invalid @enderror" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            <div class="form-text">Secara default diisi tanggal hari ini.</div>
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="penerima" class="form-label">Penerima</label>
                            <input type="text" class="form-control @error('penerima') is-invalid @enderror" id="penerima" name="penerima" value="{{ old('penerima') }}" maxlength="255" required>
                            @error('penerima')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="nota" class="form-label mb-0">Nota Penerimaan (PDF, maksimal 10 MB)</label>
                                <a href="{{ route('barang.masuk.nota.contoh') }}" class="btn btn-outline-secondary btn-sm">
                                    Unduh Contoh Nota PDF
                                </a>
                            </div>
                            <input type="file" class="form-control @error('nota') is-invalid @enderror" id="nota" name="nota" accept="application/pdf,.pdf" required>
                            @error('nota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Pembelian dari Supplier A">{{ old('keterangan') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
