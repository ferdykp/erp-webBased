# Production run workflow fix

- Ready batch: **Start Production** changes the workflow state, without generating times or irradiation session records.
- Processing batch: once an actual run has ended, record its start and end. For additional runs, use **Add Another Run**.
- Finish Batch retains existing result/QA checks and requires at least one real recorded session.
- Machine overlap checks intentionally remain active. If an overlap warning appears, inspect existing session records and correct the actual times; do not bypass the validation.
- Does not add a live equipment control or automatic irradiation timer.
- No report or Create Order changes.
- Requires end-to-end testing against your local database, including machine scheduling, multi-run sessions, and authorization.
