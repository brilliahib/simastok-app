# Tasks: Nota Penerimaan Barang

**Input**: Design documents from `specs/006-nota-penerimaan/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/incoming-goods.md`

**Tests**: Included because this feature changes stock and handles uploaded files.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel with other tasks in that phase
- **[Story]**: User story mapping from `spec.md`
- Descriptions include exact paths

## Phase 1: User Story 1 - Record incoming goods with a receipt (Priority: P1)

**Goal**: Save one PDF and recipient for a multi-item stock intake, preserving stock and movements atomically.

**Independent Test**: Submit multiple valid item lines with a PDF and recipient; all movements refer to one receipt. Missing recipient or invalid PDF leaves stock and movements unchanged.

- [x] T001 [US1] Add feature tests for valid multi-item intake, required recipient, PDF validation, and unchanged stock on invalid requests in `tests/Feature/NotaPenerimaanBarangTest.php`.
- [x] T002 [US1] Run the focused feature suite using Laragon PHP with SQLite modules enabled; the default PHP on PATH has no SQLite driver.
- [x] T003 [US1] Create the `nota_penerimaan_barangs` table with recipient, date, and private file path in `database/migrations/2026_10_31_000000_create_nota_penerimaan_barangs_table.php`.
- [x] T004 [US1] Add a nullable receipt foreign key to stock movements in `database/migrations/2026_10_31_000001_add_nota_penerimaan_barang_id_to_pergerakan_barangs_table.php`.
- [x] T005 [US1] Add the `NotaPenerimaanBarang` model and its movement relation in `app/Models/NotaPenerimaanBarang.php` and `app/Models/PergerakanBarang.php`.
- [x] T006 [US1] Validate and privately store the PDF, create one receipt per intake, link each movement, and clean up the file if persistence fails in `app/Http/Controllers/PergerakanBarangController.php`.
- [x] T007 [US1] Add the required PDF picker and file upload encoding to `resources/views/barang/create_masuk.blade.php`.
- [x] T008 [US1] Run the focused intake tests and confirm valid intake, recipient validation, file validation, and rollback cleanup.

## Phase 2: User Story 2 - Open the receipt from the incoming goods report (Priority: P2)

**Goal**: Let authenticated users open the private receipt from incoming movement reports, while keeping older unlinked records readable.

**Independent Test**: A linked movement displays an authenticated receipt link that returns its PDF; an absent file returns 404 and legacy rows have no link.

- [x] T009 [US2] Add tests for authenticated receipt retrieval, missing receipt file handling, unauthenticated access, and a legacy movement without a receipt in `tests/Feature/NotaPenerimaanBarangTest.php`.
- [x] T010 [US2] Add the authenticated receipt download route in `routes/web.php` and controller action in `app/Http/Controllers/PergerakanBarangController.php`.
- [x] T011 [US2] Eager-load linked receipt data and show receipt links only for linked movements in `app/Http/Controllers/LaporanController.php` and `resources/views/laporan/barang_masuk.blade.php`.
- [x] T012 [US2] Run the focused feature suite and confirm report, download, missing-file, auth, and legacy-row cases pass.

## Phase 3: Polish and Cross-Cutting Concerns

**Purpose**: Check migration and requirements coverage.

- [x] T013 Review all eight functional requirements against implementation and tests; no gaps found.
- [x] T014 Run the full test suite and record its result below.
- [x] T015 Add an authenticated populated example-PDF download route, printable receipt view, form button, and feature coverage in `app/Http/Controllers/PergerakanBarangController.php`, `resources/views/barang/nota_penerimaan_contoh_pdf.blade.php`, `resources/views/barang/create_masuk.blade.php`, `routes/web.php`, and `tests/Feature/NotaPenerimaanBarangTest.php`.

## Dependencies & Execution Order

### Phase Dependencies

- **User Story 1**: Independent; establishes receipt records and stock intake linkage.
- **User Story 2**: Depends on User Story 1's receipt entity and link.
- **Polish**: Depends on both stories.

### User Story Dependencies

- **US1 (P1)**: Can start immediately.
- **US2 (P2)**: Starts after US1 creates the receipt model and relationship.

### Within Each User Story

- Tests were added before implementation. The initial pre-implementation run could not start because the default PHP runtime lacked SQLite; the focused suite passed after switching to Laragon PHP with SQLite enabled.
- Migration and model precede controller persistence; report linking follows the relationship.

## Implementation Strategy

1. Deliver US1 as the MVP: receipt upload, recipient, and atomic stock recording.
2. Deliver US2: authenticated PDF access and report links.
3. Run the focused feature tests after each story and the complete project suite at the end.

## Task Validation

- All tasks have a sequential ID, checkbox, story label where applicable, and exact repository path or command.
- Both user stories have independent test criteria and implementation coverage.
- No parallel markers are used because implementation steps share files and depend on each other.

## Verification Results

- Focused receipt tests: 9 passed (40 assertions) using Laragon PHP 8.3 with its SQLite modules enabled.
- Full suite: 32 passed and 2 failed. The failures are `tests/Feature/Auth/AuthenticationTest.php` (login leaves the user unauthenticated) and `tests/Feature/ExampleTest.php` (expects `/` to return 200, while the existing route redirects to login). Both failures reproduce when run independently.
- PHP syntax checks passed for all changed PHP files; `git diff --check` passed.
- Example receipt PDF test: confirms the form button is present and the download is a non-empty valid PDF (`%PDF-` signature).
