# Implementation Plan: Nota Penerimaan Barang

**Branch**: `006-nota-penerimaan` | **Date**: 2026-10-31 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/006-nota-penerimaan/spec.md`

## Summary

Extend the existing multi-item incoming-stock form to require a recipient and PDF receipt. Persist one private receipt record per intake batch, link its movements to it, and provide authenticated access to the PDF from the incoming-goods report. Reuse Laravel validation, transactions, storage, and existing report views.

## Technical Context

**Language/Version**: PHP 8.2+ (project runtime observed: PHP 8.3.2)
**Primary Dependencies**: Laravel 12; no new dependencies
**Storage**: Existing relational database and Laravel private local storage disk
**Testing**: Pest 3 / Laravel feature tests
**Target Platform**: Authenticated Laravel web application
**Project Type**: Server-rendered web application
**Performance Goals**: Existing report query behavior remains bounded by its current date filters; no new latency target specified
**Constraints**: PDF only, maximum 10 MB; receipt download requires authentication; stock and movement changes are atomic; legacy movements remain supported
**Scale/Scope**: One PDF per incoming-stock submission, linked to every movement created by that submission

## Constitution Check

The project constitution contains template placeholders and no ratified project principles. The plan follows existing Laravel patterns and uses installed capabilities only. **Gate result: PASS.**

## Project Structure

### Documentation

```text
specs/006-nota-penerimaan/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/incoming-goods.md
└── tasks.md
```

### Source Code

```text
app/Http/Controllers/PergerakanBarangController.php
app/Http/Controllers/LaporanController.php
app/Models/PergerakanBarang.php
app/Models/NotaPenerimaanBarang.php
database/migrations/<timestamp>_create_nota_penerimaan_barangs_table.php
database/migrations/<timestamp>_add_nota_penerimaan_barang_id_to_pergerakan_barangs_table.php
resources/views/barang/create_masuk.blade.php
resources/views/laporan/barang_masuk.blade.php
routes/web.php
tests/Feature/NotaPenerimaanBarangTest.php
```

**Structure Decision**: Keep the feature inside the current stock movement controller and incoming report. A dedicated receipt entity represents one uploaded PDF per batch and prevents duplicating file metadata for every item line. The nullable movement link keeps older records valid.

## Implementation Design

- Validate a required recipient and `nota` PDF upload up to 10 MB in the current `storeMasuk` request.
- Store the PDF on the default private disk. Create one `NotaPenerimaanBarang` row and attach its ID to all movements in that transaction; continue saving the recipient on each movement for existing reports.
- If the database transaction fails after storing the file, delete the uploaded file before rethrowing the failure.
- Add an authenticated route that streams/downloads the linked file only after confirming it exists.
- Eager-load receipt data in the incoming report and show one link per linked movement; do not render a link for legacy rows.
- Add feature tests for successful multi-item linkage, invalid uploads/recipient with unchanged stock, authenticated file access, missing files, and legacy rows.

## Constitution Re-check

The plan adds no dependencies, keeps the established controller/view structure, and protects files behind authentication. All stock changes remain transactional. **Gate result: PASS.**

## Complexity Tracking

No constitution violations or new architectural abstractions.
