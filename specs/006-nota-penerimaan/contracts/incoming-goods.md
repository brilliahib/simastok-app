# Incoming Goods Receipt Contract

## Submit incoming goods

- **Route**: `POST /barang/masuk` (existing authenticated stock-in route)
- **Request fields**: existing `items[]`, `tanggal`, `penerima`, and optional `keterangan`; add required `nota` PDF upload (maximum 10 MB).
- **Success**: redirect to dashboard; stock increases and all created movements share one receipt record.
- **Invalid input**: redirect back with field validation errors; no stock or movement changes.

## Retrieve receipt PDF

- **Route**: `GET /barang/masuk/nota/{notaPenerimaanBarang}`
- **Access**: authenticated users only
- **Success**: return the stored PDF as a download.
- **Missing record or file**: return 404.
