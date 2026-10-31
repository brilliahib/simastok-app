# Data Model: Nota Penerimaan Barang

## NotaPenerimaanBarang

One uploaded receipt for a single incoming-stock submission.

| Field | Meaning |
|---|---|
| id | Receipt identity |
| penerima | Required person receiving the goods |
| tanggal | Intake date |
| file_path | Private storage path for the PDF |
| created_at / updated_at | Record timestamps |

Relationships: has many `PergerakanBarang` rows created by this intake.

## PergerakanBarang

Existing item movement. Add nullable `nota_penerimaan_barang_id`; new incoming rows from this form reference the batch receipt. The existing `penerima`, `tanggal`, quantity, item, type, and description fields remain populated as before. Older records and movements from other flows keep a null receipt link.

## Storage lifecycle

Files use the private local disk. Deleting a movement does not delete the batch receipt because other lines may still reference it. This feature does not add receipt deletion or replacement flows.
