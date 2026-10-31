# Research: Nota Penerimaan Barang

## Decisions

1. **Represent one uploaded receipt per intake batch.** The current form accepts multiple stock lines in one submission, so one receipt entity is linked to all resulting movements. This avoids duplicate file metadata and matches the physical document.
2. **Keep the receipt PDF private.** The existing `local` disk root is `storage/app/private`; use the authenticated application route to serve files instead of exposing a public storage URL.
3. **Retain movement recipient values.** Existing reports read `pergerakan_barangs.penerima`; continue populating it for compatibility while also storing the recipient on the receipt record.
4. **Keep the movement link nullable.** Historical and initial-stock movements may not have receipt files and must remain readable.
5. **Clean up a stored file on persistence failure.** Store the uploaded file before database writes, then delete it if the database transaction throws, avoiding orphaned PDFs.

## Alternatives Considered

- Storing the same file path on every movement was rejected because one intake can contain many item lines and would duplicate batch data.
- A public storage link was rejected because receipt documents are business records and should pass through authenticated access.
- Supplier selection was excluded because the clarified request is for an incoming goods receipt and does not require supplier metadata.
