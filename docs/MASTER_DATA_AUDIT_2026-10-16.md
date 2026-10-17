# Audit lanjutan profil, master data dan dosimetri — 16 Oktober 2026

Pekerjaan dilanjutkan dari commit pengguna `0ed0c28` (new design), tanpa mengganti desain atau form Create Order terbaru pengguna. Aturan dispatch tanpa approval dan perbedaan dosis sebagai peringatan tetap berlaku.

## Perbaikan nyata

1. **Edit profil sendiri untuk semua peran.** Route sebelumnya hanya mengizinkan superadmin meskipun tombol Edit Profile muncul bagi manager/production/cargo admin. Kini empat peran dapat membuka dan menyimpan profil sendiri; hanya superadmin dapat mengedit akun orang lain. Pemeriksaan kepemilikan berada di controller, bukan sekadar UI. Parameter ID nonangka menghasilkan 404.
2. **Form password tepat sasaran.** Ketika superadmin mengedit staf lain, form password yang sebenarnya mengubah password superadmin tidak ditampilkan. Pengelolaan password tetap milik pemilik akun. JavaScript validasi password hanya dimuat ketika field-nya tersedia. Setelah mengedit staf lain, pengguna kembali ke daftar staf.
3. **Penghapusan staf menjaga riwayat dan akun tersisa.** Pemeriksaan mencakup pembuat/pemeriksa template, pembuat dokumen, approver legacy, petugas relokasi dan pembatal run, selain audit/QA/dispatch. Transaksi mengunci akun dalam urutan ID dan memeriksa kembali actor agar dua request yang saling menghapus tidak menghilangkan kedua akun. Akun sendiri ditandai Current Account di UI.
4. **Dialog JavaScript biasa konsisten dengan dialog Alpine.** Tombol penutup diberi penanda eksplisit; Escape dan klik backdrop menjalankan fungsi penutup yang sudah ada, menjaga reset state, scroll dan focus restoration. Tombol penutup memakai type=button agar tidak mengirim form tanpa sengaja.
5. **Nama unit produksi aman untuk handler JavaScript.** `addslashes` diganti encoding `@js` pada tombol edit master dan parameter. Nama berisi kutip dan newline diuji melalui parser handler render. Label, judul dan nama aksi dibuat jelas dalam English.
6. **Draft dokumen menyertakan seluruh set dosimetri.** Sebelumnya snapshot mengambil set pertama saja. Snapshot baru menyimpan seluruh detail dan array `dosimeter_sets`, tetap mempertahankan key `dosimeters` untuk kompatibilitas. Versi lama tidak diubah; versi baru tetap dibuat dalam lock booking.
7. **Hapus baris dosimeter tidak lagi selalu ditolak middleware.** UI baru pengguna mengirim `confirm_delete`, tetapi middleware global juga membutuhkan `confirmed`. Request kini mengirim keduanya. Implementasi koreksi pengguna dipertahankan, dengan tes penomoran ulang, snapshot audit, perlindungan baris terakhir dan penguncian setelah dispatch. Tidak ada penghapusan evidence file.
8. **Checkbox inactive tidak kembali aktif saat validasi gagal.** Hidden fallback `is_active=0` ditambahkan pada form Porter dan Warehouse PIC, sehingga pilihan unchecked ikut dikirim dan dapat dipulihkan dengan old input.
9. **Daftar staf dan judul halaman diperjelas.** Nama peran tanpa underscore, label aksi untuk pembaca layar, judul halaman, keterangan akun yang sedang diedit dan footer yang tidak mengklaim SSL secara statis.

## File berubah pada tahap ini

| File/kelompok | Tujuan |
|---|---|
| `app/Http/Controllers/UserAdminController.php`, `routes/web.php` | Kepemilikan profil, redirect dan delete staf atomik |
| `app/Http/Controllers/OperationsEnhancementController.php` | Snapshot seluruh set dosimetri |
| `resources/js/modal-lifecycle.js` | Escape/backdrop untuk modal dengan fungsi JavaScript biasa |
| `resources/views/admin/profile/{index,edit,profileList}.blade.php` | Judul, password sesuai pemilik, label dan Current Account |
| `resources/views/admin/production-lines/index.blade.php`, `admin/production/parameter.blade.php` | Encoding nama unit dan UI master |
| Modal terkait pada bookings, production, dosimeter dan customer | Penanda tombol dismiss dan type=button |
| `resources/views/admin/dosimeter/show.blade.php` | Payload konfirmasi delete sesuai middleware |
| `resources/views/admin/{porter,warehouse-pic}/{create,edit}.blade.php` | Nilai unchecked yang eksplisit |
| `tests/Feature/StaffManagementTest.php` | Empat peran, cross-account denial, form password, history dan delete aman |
| `tests/Feature/OperationalWorkflowTest.php` | Snapshot multi-set, koreksi dosimeter, handler nama unit khusus |
| `tests/Support/ChecksAlpineExpressions.php` | Pemeriksaan syntax handler HTML selain directive Alpine |
| `scripts/verify-concurrency.php` | Skenario saling hapus staf bersamaan |
| `tests/Fixtures/protected-user-revisions-2026-10-16.json`, `tests/Feature/ProtectedArtifactsTest.php` | Menerima hash form Create Order yang sudah diubah pengguna pada commit `0ed0c28`, tanpa mengubah source form tersebut |

Tidak ada migrasi database baru pada tahap ini. Tidak menjalankan reset database, seed operasional atau deployment.

## Verifikasi akhir

- **SQLite: 53 tes Laravel lulus, 796 assertion.**
- **MySQL: 53 tes Laravel lulus, 796 assertion**, pada schema `beamcustom_codex_ux_20261016_regression` dengan rollback transaksi.
- **14 tes JavaScript lulus.**
- **96 Blade view** dikompilasi dan diperiksa tanpa error. PHP syntax, route/controller, handler/directive dari halaman render, diff whitespace dan Vite build lulus.
- **5 uji concurrency MySQL lulus:** duplicate receiving satu pemenang; dua order menerima ke shared slot dengan stok tepat 900; duplicate dispatch satu pemenang dan stok order lain 420; overlapping machine run satu pemenang; dua staf mencoba saling menghapus dengan satu akun tetap ada dan request actor yang sudah dihapus ditolak.
- Concurrency memakai schema khusus `beamcustom_codex_ux_20261016`, fixture berlabel `UX-RACE-269ff904`. Satu akun dummy dihapus oleh skenario pengujian sesuai tujuan tes; fixture lain tetap tersedia. Tidak menyentuh akun/data operasional.
- Browser fixture: login staf, menu Production Units, Add Unit → Escape → fokus kembali ke tombol dan scroll unlocked; daftar staf → edit manager → form password tidak tampil; tidak ada console warning/error dalam pemeriksaan ini. Tidak menyimpan profil atau mengganti password lewat browser.
- Bukti terbaru: `ux-evidence/staff-edit-desktop-20261016.png`. Bukti mobile/tablet dan dispatch dari tahap sebelumnya tetap ada pada laporan UI.
- Metadata Browserslist masih memberi warning usia data; build berhasil. Tidak ada pembaruan dependency yang dipaksakan.

## Batas cakupan

Tes otomatis membuktikan skenario di atas, bukan seluruh kombinasi data/urutan/perangkat. Seluruh peran belum diuji melalui semua klik browser; scanner fisik, pengiriman email sungguhan, session expiry, dan beban data produksi masih memerlukan verifikasi tersendiri. Detail tahap sebelumnya: `UI_RELIABILITY_2026-10-16.md` dan `WORKFLOW_INTEGRATION_2026-10-16.md`.
