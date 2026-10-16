# BeamCustom production and deletion update

- Received pending orders may enter batch planning without manager order approval.
- Machine parameter creation supports received pending orders.
- Recording a first manual irradiation session transitions pending/approved booking to processing.
- Order Management now includes Process action for received orders.
- Delete is shown on draft orders with no receiving, batch, or inventory history; processed orders display Delete locked. Existing confirmation modal retained.
- Historical/received bookings are never force-deleted.
- Original Create Order and Documents/PDF files were not modified.
- Test using a database backup; no live database available in this environment.
