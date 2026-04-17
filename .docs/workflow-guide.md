# Workflow Guide (User-facing)

How loans move through the 12-stage workflow. Written for users (BDH, BM, advisors, bank/office employees). For dev internals see `workflow-developer.md`.

## Stages at a glance

| # | Stage | English label | Gujarati label | Who |
|---|---|---|---|---|
| 1 | inquiry | Inquiry | પૂછપરછ | advisor |
| 2 | document_selection | Document Selection | દસ્તાવેજ પસંદગી | advisor |
| 3 | document_collection | Document Collection | દસ્તાવેજ સંગ્રહ | advisor |
| 4 | parallel_processing | Parallel Processing | સમાંતર પ્રક્રિયા | (parent) |
| 4a | app_number | Application Number | અરજી નંબર | bank employee |
| 4b | bsm_osv | BSM / OSV | BSM / OSV | bank employee |
| 4c | legal_verification | Legal Verification | કાયદેસર ચકાસણી | advisor ↔ bank ↔ office |
| 4d | technical_valuation | Technical Valuation | ટેકનિકલ મૂલ્યાંકન | advisor ↔ office |
| 4e | sanction_decision | Sanction Decision | મંજૂરી નિર્ણય | BM / BDH |
| 5 | rate_pf | Rate & PF | વ્યાજ દર & PF | owner ↔ bank ↔ owner |
| 6 | sanction | Sanction Letter | મંજૂરી પત્ર | office → bank → owner |
| 7 | docket | Docket Login | ડોકેટ લોગિન | owner → bank → office |
| 8 | kfs | KFS | KFS | bank |
| 9 | esign | E-Sign & eNACH | ઈ-સાઈન & eNACH | bank → customer → office → owner |
| 10 | disbursement | Disbursement | વિતરણ | owner |
| 11 | otc_clearance | OTC Clearance | OTC ક્લિયરન્સ | office (cheque only) |

## Typical path

1. **Loan created** — either from a quotation (conversion) or directly. Inquiry + Document Selection auto-complete for quotation-converted loans.
2. **Document Collection** — advisor ticks off each required document. The system tracks received / pending / rejected / waived per document. Stage auto-completes when all required documents are resolved (received or waived).
3. **Parallel Processing** — the parent stage kicks in. Sub-stages run in two sequential waves:
   - **Wave A (sequential)**: Application Number → BSM/OSV
   - **Wave B (parallel)**: once BSM/OSV completes, Legal Verification, Technical Valuation, and Sanction Decision all open up simultaneously.
4. **Sanction Decision** — BM / BDH picks one:
   - **Approve** → loan is sanctioned, parallel processing completes, flow moves to Rate & PF.
   - **Escalate to BM / BDH** → decision bubbles up to the next level.
   - **Reject** → loan is rejected from this stage; all pending stages close.
5. **Rate & PF, Sanction Letter, Docket Login** — each is a three-phase stage. The owning advisor kicks off, the bank reviews / generates the document, then it bounces back for final details and completion.
6. **KFS** — bank issues the Key Facts Statement. Single-phase.
7. **E-Sign & eNACH** — four phases coordinating bank → customer → office → owner.
8. **Disbursement** — funds released.
   - If **fund transfer (NEFT/RTGS)**: loan is marked completed immediately; OTC is skipped.
   - If **cheque**: OTC Clearance opens for tracking cheque handover.
9. **OTC Clearance** — office staff logs the cheque handover; loan completes.

## What you see on the loan page

- **Stage pipeline** at the top — visual dots for each main stage: completed (green), current (orange), pending (gray), rejected (red), skipped (muted).
- **Current stage card** — who's assigned, what's pending, form fields if applicable.
- **Sub-stage cards** (only when in Parallel Processing).
- **Action buttons** — context-specific: "Complete", "Transfer to…", "Raise Query", "Reject", etc.
- **Notes** — per-stage form data (sanctioned amount, EMI, application number, etc.).
- **Queries panel** — anyone working a stage can raise a query to another stakeholder. Active queries **block stage completion** until resolved.

## Who owns what

Assignments follow this pattern:

- **Advisor roles** (loan_advisor, branch_manager, BDH) — own advisor-facing stages: document collection, documents, remarks.
- **Bank employee** — owns bank-side work: application number, BSM/OSV, KFS, bank-side phases of multi-phase stages.
- **Office employee** — owns internal review: technical valuation capture, docket review, post-sanction paperwork, OTC.
- **BM / BDH** — gatekeeper for Sanction Decision.

Default assignees are configured per product and per bank, with fallback logic based on city and branch. See `user-assignment.md`.

## Transfers

If the wrong person has a stage, or work needs to move (e.g., absence, workload), click "Transfer to…" on the stage card:

- Pick the new user
- Optionally add a reason
- The stage's active queries automatically move with it
- A transfer ledger record is written so the history is preserved (`/loans/{id}/transfers`)

Transferring the `sanction` multi-phase stage during ping-pong between phases is what the phase actions (Send to Bank, Return to Owner, etc.) do under the hood.

## Queries (blocking)

When a stage owner needs something from another stakeholder, they raise a query instead of completing the stage:

1. Click **Raise Query** on the stage
2. Write the question (max 5000 chars)
3. Recipient gets a notification and sees the query on the stage
4. Recipient responds — status becomes "Responded"
5. Original raiser reviews and **Resolves** the query
6. Stage can now be completed

Unresolved queries block completion.

## Status changes

Loan owners (BM/BDH level for some) can:

- **Put On Hold** — pause the loan. Stages remain in place but become read-only. Reason is required.
- **Cancel** — terminal. Reason required. Permission: super_admin, admin, branch_manager, BDH.
- **Reactivate** — from on_hold, cancelled, or rejected back to active. If rejecting from `rejected`, the rejected stages are restored to `in_progress`.

All status changes are logged and require a reason (except reactivation).

## Completion outcomes

- **Completed** — loan reached final stage (OTC Clearance for cheque; Disbursement for fund transfer).
- **Rejected** — rejected from any stage with a reason. Rejected at Sanction Decision is most common.
- **Cancelled** — admin-cancelled for non-rejection reasons (customer withdrew, etc.).
- **On Hold** — temporarily paused; can resume.

## Notifications

The bell icon in the navbar shows unread count (polled every 60s). Events that notify:

- Stage assigned to you
- Stage you own got a query
- Query you raised got a response or was resolved
- Loan you created / advised was completed
- Loan status changed

## Tips

- If a stage seems stuck, check the **Queries panel** first — an unresolved query may be blocking.
- Multi-phase stages have a specific action button per phase (e.g., "Send for Sanction" for Phase 1, "Sanction Generated" for Phase 2). Don't try to use "Complete" until the final phase.
- Documents can be edited back to pending even after the document_collection stage completed — the system will soft-revert the stage.
- When a loan is on_hold or cancelled, valuation and disbursement forms become read-only; reactivate first.

## See also

- `loans.md` — loan CRUD, visibility, documents, valuation, disbursement, remarks
- `workflow-developer.md` — the engine under the hood
- `dashboard.md` — where these stages show up in the dashboard tabs
