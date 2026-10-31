# Feature Specification: Nota Penerimaan Barang

**Feature Branch**: `006-nota-penerimaan`

**Created**: 2026-10-31

**Status**: Draft

**Input**: User description: "input nota penerimaan barang dan ada nama penerimanya jg"

## User Scenarios & Testing

### User Story 1 - Record incoming goods with a receipt (Priority: P1)

As a staff member receiving stock, I want to record the items, quantities, date, recipient name, and the receipt PDF together so the stock record has supporting proof.

**Why this priority**: Recording the receipt and stock change is the feature's main purpose.

**Independent Test**: Submit one or more incoming item lines with a valid PDF and recipient; confirm stock and movement records are updated and linked to the receipt.

**Acceptance Scenarios**:

1. **Given** an authenticated user and valid item lines, **When** they submit a PDF receipt and recipient name, **Then** stock is increased and each resulting movement refers to the same saved receipt.
2. **Given** an intake submission with no recipient or no PDF, **When** it is submitted, **Then** validation errors are shown and no stock or movement data changes.

---

### User Story 2 - Open the receipt from the incoming goods report (Priority: P2)

As a staff member reviewing incoming stock, I want to open the uploaded receipt from its report rows so I can verify the recorded intake.

**Why this priority**: An uploaded document must remain accessible as evidence after intake is saved.

**Independent Test**: Open the incoming goods report for a movement with a linked receipt and follow its receipt link; confirm the PDF is returned. Confirm an unlinked legacy movement has no broken link.

**Acceptance Scenarios**:

1. **Given** a movement linked to an uploaded receipt, **When** an authenticated user follows its receipt link, **Then** the stored PDF is served inline or downloaded.
2. **Given** a receipt file path that cannot be found, **When** its link is opened, **Then** the user receives a not-found response rather than a filesystem error.

### User Story 3 - Download a populated example receipt (Priority: P2)

As a staff member recording incoming stock, I want to download a filled example receipt so I can follow the expected receipt fields and format.

**Independent Test**: Open the incoming goods form, download the example, and confirm it is a valid PDF containing item rows, quantities, unit prices, date, recipient, and notes.

**Acceptance Scenarios**:

1. **Given** an authenticated user on the incoming goods form, **When** they choose the example download, **Then** a populated PDF is downloaded and clearly marked as an example.

### Edge Cases

- Reject non-PDF files and PDFs larger than 10 MB without changing stock.
- If saving the receipt record or any stock movement fails, roll back all stock changes and remove the newly uploaded file.
- Existing incoming movements without a receipt remain visible and usable in reports.
- One receipt uploaded for a multi-item intake is linked to every movement created by that intake.

## Requirements

### Functional Requirements

- **FR-001**: The incoming goods form MUST require one PDF receipt file of at most 10 MB.
- **FR-002**: The incoming goods form MUST require and record the recipient name for the intake.
- **FR-003**: The system MUST associate one receipt document with all stock movements created by one multi-item intake.
- **FR-004**: The system MUST increase each selected item's stock and create its incoming movement only when the whole intake is valid and can be saved.
- **FR-005**: The incoming goods report MUST show an accessible receipt link for movements with an associated document.
- **FR-006**: Receipt access MUST require authentication and MUST NOT expose the stored file through a public URL.
- **FR-007**: Existing incoming movements without receipt documents MUST remain available in reports without a receipt link.
- **FR-008**: The incoming goods form MUST provide an authenticated download for a populated sample receipt whose fields match the incoming movement data and clearly identify it as an example.

### Key Entities

- **Goods Receipt**: One incoming delivery record with its recipient, receipt date, stored PDF, and the movements created from the intake.
- **Stock Movement**: An item-specific incoming quantity and date, linked to the goods receipt when created through the receipt flow.

## Success Criteria

### Measurable Outcomes

- **SC-001**: Staff can submit a valid intake with its PDF and recipient in one form submission.
- **SC-002**: Every movement from a submitted multi-item intake displays a working link to the same receipt PDF.
- **SC-003**: Invalid file or missing recipient submissions leave stock quantities and movement history unchanged in all tested cases.
- **SC-004**: Existing movements remain visible in incoming goods reports whether or not they have a receipt.
- **SC-005**: Staff can download a valid PDF example that includes populated receipt and item data.

## Assumptions

- The feature extends the existing incoming-stock flow; it does not introduce a separate supplier selection because a supplier is not needed to identify the receipt in the corrected request.
- One PDF represents the full intake batch, which may contain multiple item lines.
- PDF files are limited to 10 MB and kept on the application's private storage disk.
- Existing authentication and the current required recipient field are reused.
