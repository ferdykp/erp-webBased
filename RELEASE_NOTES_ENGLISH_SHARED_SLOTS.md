# BeamCustom — Shared Slots and Production Visibility

Source: user-supplied BeamCustom_Penyempurnaan_Pallet_Bahasa_Hapus_2026-10-15(1).zip

- Shared slot implementation merges content-level receiving, relocation and dispatch handling.
- Production board now shows received orders without batches, and sends users to Production Setup or manager approval. Check-in does **not** itself create a production batch.
- Key workspace screens and navigation labels switched to short English terminology. This does not certify every legacy string in the application is translated.
- Create Order specification form and reports/PDF source remain unchanged from user ZIP (apart from pre-existing optional photo support).
- No schema migration included.

Test on a development database before deployment. Verify shared-slot receive A+B, dispatch A retaining B, relocation, manager approval, batch creation and manual sessions.
