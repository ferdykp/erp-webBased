> Pembaruan UI dan verifikasi browser desktop/mobile/tablet berikutnya tersedia di [laporan UI terbaru](UI_RELIABILITY_2026-10-16.md). Kendala browser historis di dokumen ini sudah teratasi untuk cakupan yang dicatat di laporan tersebut.

# BeamCustom — hasil integrasi dan pengujian 16 Oktober 2026

Status: perbaikan kode dan pengujian otomatis selesai untuk cakupan di bawah. Verifikasi UX seluruh fitur di browser belum selesai; bukan jaminan aplikasi bebas bug.

## Keputusan terbaru pengguna

Dispatch tanpa approval apa pun. Perbedaan dosis, catatan quality `hold`/`needs_correction`, dan hasil multi-produk yang belum dipetakan ke batch hanya menghasilkan peringatan. Peringatan ditampilkan sebelum pengiriman dan disimpan sebagai snapshot pada dispatch beserta audit. Dosis aktual di luar batas juga tidak menghalangi penyelesaian batch; operator mendapat pesan peringatan.

Kelengkapan hasil ukur, produksi selesai, bukti run, pemeriksaan visual/indikator/kerusakan, otorisasi peran, rekonsiliasi stok, dan pencegahan dispatch ganda tetap diperiksa. Parameter target mesin tetap mengikuti spesifikasi produk. Tidak ada approval manager pengganti.

## Perubahan yang diselesaikan

- Receiving langsung mengarah ke production setup. Satu slot menerima beberapa booking/produk; alokasi menyimpan produk dan satuannya. Receiving berulang ditolak secara atomik.
- Batch planning menyediakan satu batch atau pembagian beberapa batch tanpa mengulang Porter. Multi-produk dialokasikan secara terpisah. Loading Mode konsisten `single_side`/`double_side`.
- Start Production tidak mengarang timestamp mesin. Run aktual dapat dicatat beberapa kali/lintas hari. Durasi dijumlahkan dari run valid. Benturan mesin menunjukkan booking/batch/waktu; batas waktu berurutan diperbolehkan. Koreksi run melalui void dengan alasan dan audit.
- Finish Batch menyimpan QA, memerlukan run aktual dan pemeriksaan berhasil. Produksi selesai tidak otomatis mengeluarkan stok.
- Dispatch langsung mencatat petugas/waktu/referensi/satuan/lokasi, tanpa approval palsu. Riwayat legacy approved tetap dapat diproses. Transaksi dan row locking menjaga stok booking lain dalam slot bersama.
- Dosimetri melindungi hasil tersimpan saat jumlah tablet berubah, memvalidasi kepemilikan record/batch, mendukung beberapa set hasil, serta mengunci perubahan setelah dispatch. Pemilihan batch opsional; pengiriman batch milik order lain ditolak. Klien yang tidak mengirim kolom batch mempertahankan pemetaan yang sudah ada.
- Delete memakai dialog khusus dan validasi backend. Riwayat receiving/produksi/QA/dosimetri/dispatch tidak dihapus lewat CRUD biasa. Master yang digunakan dan slot berisi dilindungi.
- Navigasi/status/tombol Next Step diperbaiki; pencarian/paginasi async melindungi dari respons lama dan menampilkan error tanpa mengosongkan tabel.
- Password recovery, route yang menunjuk method tidak tersedia, dimensi customer `10` menjadi `1`, dan beberapa null fallback diperbaiki.
- Form Create Order, ekspor, dan template laporan dilindungi tes checksum terhadap baseline sesi. Revisi laporan/print yang dibuat pengguna pada 16 Oktober diterima sebagai baseline terpisah. Route PDF lama dipulihkan berdampingan dengan print preview pengguna. Product Testing tetap terpisah.

## File utama dan tujuan

| File/kelompok | Tujuan |
|---|---|
| `app/Services/WarehouseInventory.php` | Receiving, stok bersama, locking, rekonsiliasi, audit |
| `app/Services/DosimetryRelease.php` | Kelengkapan hasil ukur dan peringatan nonblocking |
| `app/Http/Controllers/OrderDispatchController.php` | Direct dispatch atomik dan snapshot peringatan |
| `app/Http/Controllers/AdminBookingController.php` | Receiving multi-produk, filter, delete aman |
| `app/Http/Controllers/AdminProductionController.php` | Batch, setting, start, finish dan QA |
| `app/Http/Controllers/IrradiationSessionController.php` | Run aktual, overlap, void |
| `app/Http/Controllers/AssistedOperationsController.php` | Receiving/planning kontekstual |
| `app/Http/Controllers/OperationsEnhancementController.php` | Relokasi dan kompatibilitas template parameter |
| `app/Http/Controllers/DosimeterController.php` | Pencatatan hasil dan kepemilikan data |
| `app/Http/Controllers/{Porter,WarehousePic,AdminProductionLine,CustomerProfile,UserAdmin,ProductTesting}Controller.php` | Perlindungan penghapusan/riwayat terkait |
| `app/Http/Controllers/{PasswordRecovery,AdminAuth,CustomerAuth,CustomerBooking}Controller.php` | Recovery, login, dan regresi customer |
| `app/Models/{Admin,BookingBatch,DosimeterDetail,PalletContent,PlacementDetail}.php` | Relasi/fillable pendukung |
| `app/Http/Middleware/ConfirmDeletion.php`, `bootstrap/app.php`, `routes/web.php` | Konfirmasi backend, role dan kompatibilitas route |
| `resources/views/admin/workspace/*.blade.php` | Receiving, batch plan, board, workflow, dispatch |
| `resources/views/admin/{bookings,dosimeter,pallets,production}/…` | Status, form terhubung, dosimetri, stok |
| `resources/js/{async-table,batch-assistant,receiving-assistant,delete-confirmation}.js`, `resources/js/app.js` | Interaksi, error/race handling, modal |
| `resources/views/auth/…`, layout/sidebar/status/customer related views | Recovery dan navigasi/status |
| `database/migrations/2026_10_18_00000{1,2,3,4}_*.php` | Nullable approval, kepemilikan produk, batch dosimeter, snapshot peringatan |
| `database/migrations/2026_09_22_000002_normalize_quantity_columns.php` | CASE portabel untuk SQLite/MySQL |
| `tests/Feature/{MenuSmoke,OperationalWorkflow,ProtectedArtifacts}Test.php` | Regresi fitur/menu/data/artefak |
| `tests/Support/NonDestructiveDatabase.php`, `tests/Fixtures/…`, `tests/js/…` | Database uji terisolasi, baseline, tes interaksi |
| `scripts/{verify-source.sh,check-blade.php,verify-concurrency.php}` | Pemeriksaan sumber/build dan concurrency MySQL |

Working tree sudah berisi banyak perubahan pengguna sebelum pekerjaan ini. Daftar di atas menjelaskan area integrasi, bukan klaim bahwa seluruh diff repository dibuat oleh agen. File laporan pengguna tidak didesain ulang.

## Transisi yang berlaku

| Tahap | Sumber kebenaran dan syarat transisi |
|---|---|
| Awaiting Goods → Received | Receipt/arrival dan detail placement; stock per booking/produk/satuan tercatat |
| Received → Planned/Ready | Alokasi batch dalam sisa quantity; parameter wajib valid; tanpa approval order |
| Ready → In Progress | Start memindahkan status; belum menyatakan timestamp run aktual |
| In Progress → Completed Batch | Minimal satu run nonvoid dan QA berhasil; selisih dosis hanya warning |
| Completed Batch → Completed Order | Semua produk sudah dialokasikan seluruhnya dan tidak ada batch belum selesai |
| Completed Order → Dosimetry/Documents | Hasil ukur tersimpan pada order yang benar; laporan lama tetap tersedia |
| Completed Order → Dispatched | Bukti wajib lengkap, stok cocok, verifikasi fisik, referensi, transaksi sekali; tanpa approval dan tanpa blokir perbedaan dosis/pemetaan |
| Run correction | Void dengan alasan, simpan jejak; durasi dihitung ulang; batch selesai tidak diedit melalui jalur ini |

## Hasil pengujian

- Laravel SQLite dan MySQL: **44 tes lulus / 663 assertion pada masing-masing database** (run akhir setelah perubahan warning dan pemetaan opsional).
- JavaScript: **8 tes lulus**, meliputi alokasi, konfirmasi ulang setelah perubahan, shared slot, kegagalan request dan respons lama.
- **96 Blade view** berhasil dikompilasi dan diperiksa sintaks PHP; route/controller dan build Vite lulus.
- **4 skenario concurrency MySQL lulus**: receipt order sama satu pemenang; receipt dua order stok tepat 900; duplicate dispatch satu pemenang dan sisa order lain 420; run overlap satu tersimpan.
- Tes concurrency sempat menemukan masalah snapshot stok MySQL; diperbaiki dengan locking read saat menjumlahkan stok, kemudian seluruh skenario lulus.
- Regresi mencakup 24 kategori pengujian minimum pada permintaan, termasuk Create Order beserta foto/perhitungan, empty/shared receiving, batch tanpa Porter/approval, multi-run, conflict, QA, direct dispatch, nullable approval, unsafe/safe delete, PDF/Excel, Product Testing, dan role.
- Laporan proses dari fixture di-render menjadi PDF A4 satu halaman dan diinspeksi visual: tabel/header muat tanpa clipping pada contoh tersebut. Ini bukan verifikasi seluruh variasi dokumen.
- Tidak ada migrasi/seed/reset ke database operasional. Tes menggunakan SQLite memory dan schema MySQL `beamcustom_codex_ux_20261016_regression`; transaksi di-rollback. Concurrency memakai schema `beamcustom_codex_ux_20261016`, fixture sintetis sengaja dipertahankan untuk audit.
- Build memberi peringatan metadata Browserslist lama; build tetap berhasil.

## Bukti UX browser dan batas verifikasi

Uji desktop langsung sebelumnya mencakup login, navigasi order, receiving, single batch, start tanpa waktu, dua run aktual, pesan konflik, finish QA, serta simpan dosimetri. Screenshot `ux-evidence/dosimetry-desktop.jpg` berasal dari tahap itu, sebelum perubahan peringatan terbaru.

Reload browser berikutnya ditolak peninjauan persetujuan otomatis karena batas penggunaan akun. Tidak diputar lewat browser/HTTP alternatif. Pengujian kode/database tetap dilanjutkan.

Belum terverifikasi: seluruh klik semua menu, modal dispatch final dan interaksi terbaru di browser, ukuran mobile/tablet, scanner/perangkat fisik, delivery email sungguhan, session expiry lintas browser, serta performa pada volume produksi. Menu render dan parsing JavaScript tidak membuktikan semua interaksi browser benar. Lokalisasi English seluruh pesan legacy juga belum dijamin; form/laporan yang dilindungi tidak diterjemahkan ulang.

Legacy record multi-produk tanpa pemilik produk yang dapat ditentukan tidak diisi secara spekulatif. Ketidaksesuaian inventory perlu rekonsiliasi eksplisit; mapping dosimeter yang kosong hanya warning sesuai arahan pengguna.

## Penerapan

Empat migrasi integrasi 18 Oktober bersifat incremental; sudah diuji pada database terisolasi dan **belum diterapkan ke database operasional**. Setelah backup, gunakan prosedur deployment proyek untuk meninjau `php artisan migrate:status` lalu `php artisan migrate --force` di staging sebelum produksi. Deploy kode dan schema bersama agar kolom baru tersedia. Jangan gunakan migrate:fresh/db:wipe. Tidak ada ZIP/commit/PR dibuat karena tidak diminta.

Reproduksi lokal aman: `bash scripts/verify-source.sh` memakai SQLite memory. MySQL membutuhkan schema uji yang diizinkan oleh `NonDestructiveDatabase`, bukan database operasional. `scripts/verify-concurrency.php` hanya menerima nama schema uji yang telah ditetapkan dan meninggalkan fixture sintetis.
