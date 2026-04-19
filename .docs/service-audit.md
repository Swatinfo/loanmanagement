# Service Cohesion Audit (2026-04-17)

14 services in `app/Services/`. Most are cohesive. One is a god service. One small merge candidate.

## Inventory

| Service | Public methods | Verdict |
|---|---:|---|
| `ConfigService` | 6 | Keep as-is |
| `DisbursementService` | 1 | Keep |
| `FileUploadService` | 3 (static) | Keep |
| `LoanConversionService` | 2 | Keep |
| `LoanDocumentService` | 9 | Keep |
| **`LoanStageService`** | **27** | **Split (see below)** |
| `LoanTimelineService` | 1 | Merge candidate |
| `NotificationService` | 7 | Keep |
| `NumberToWordsService` | static-only | Keep |
| `PdfGenerationService` | 2 | Keep |
| `PermissionService` | 8 | Keep |
| `QuotationService` | 1 | Keep |
| `RemarkService` | 2 | Keep |
| `StageQueryService` | 4 | Keep |

## Problem: `LoanStageService` has 27 public methods

It owns six distinct responsibilities. Every controller that touches a stage depends on all of them.

### Proposed split

| Proposed service | Methods moved in | What it owns |
|---|---|---|
| `StageRoleResolver` | `resolveStageRole`, `resolvePhaseRole`, `buildWorkflowSnapshot`, `getLoanStageRole`, `getLoanPhaseRole`, `findUserForRole` | Bank-wise role resolution + snapshot |
| `StageRepository` | `getOrderedStages`, `getStageByKey`, `getSubStages`, `isParallelStage`, `getMainStageKeys`, `getLoanStageStatus` | Read-only stage queries |
| `StageLifecycleService` | `initializeStages`, `autoCompleteStages`, `updateStageStatus`, `revertStageIfIncomplete`, `canStartStage`, `getNextStage` | Stage progression |
| `StageAssignmentService` | `assignStage`, `skipStage`, `autoAssignStage`, `autoAssignParallelSubStages`, `findBestAssignee`, `transferStage` | Who does the stage |
| `StageProgressService` | `checkParallelCompletion`, `getParallelSubStages`, `recalculateProgress` | Progress math |
| (moved) `LoanRejectionService::rejectLoan` | `rejectLoan` | Rejection — could go in a new `LoanStatusService` alongside the status-change flow from `LoanController` |

### Why not do this right now

- Every call site uses `LoanStageService`. Blast radius is large.
- No tests currently protect the workflow behavior (Phase 5.2 addresses this).
- Splitting without tests risks silent regressions in bank-wise role resolution or phase transitions — both are audit-sensitive.

### Recommended sequence

1. **Phase 5.2 first** — write the workflow test suite.
2. **Then split** — refactor one sub-service at a time, keep `LoanStageService` as a thin facade that delegates to the new services (so controllers don't change in the same PR).
3. **Then inline** — after tests pass green across the split, delete the facade and update controller constructors.

## Smaller finding: `LoanTimelineService::getTimeline` is one method

Candidates to absorb it:
- `LoanStageService` (adds to god service — no)
- A new `StageRepository` (it is mostly a read query) — natural home after the split above

Until the split happens, leaving it standalone is fine. Don't move it in isolation.

## Keep as-is

All other services have one clear responsibility and small surface area:

- `ConfigService` — config read/write
- `DisbursementService` — disbursement processing
- `FileUploadService` — upload rules + filename hashing
- `LoanConversionService` — quotation→loan conversion + direct creation
- `LoanDocumentService` — loan document lifecycle
- `NotificationService` — notifications + unread count
- `PermissionService` — permission resolution + cache (recently polished in Phase 1.1)
- `PdfGenerationService` — PDF rendering
- `QuotationService` — quotation generation
- `RemarkService` — remarks CRUD
- `StageQueryService` — query raise/respond/resolve

## Decision

**Defer the split to after Phase 5.2 (workflow tests).** Writing tests first is non-negotiable — without them, splitting the service is dangerous.

No code changes in this audit. This file is the record of findings.
