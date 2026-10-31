# Quickstart: Nota Penerimaan Barang

1. Apply the new migrations with `php artisan migrate`.
2. Sign in and open the existing **Catat Barang Masuk** form.
3. Add one or more item lines, enter the intake date and recipient, select a PDF no larger than 10 MB, and submit.
4. Confirm item stock is increased and a movement is created for each item.
5. Open **Laporan Barang Masuk** and open the receipt link from each movement created by the intake.
6. Confirm a non-PDF or missing recipient is rejected without changing stock.

Run feature coverage with `php artisan test --filter=NotaPenerimaanBarangTest`.
