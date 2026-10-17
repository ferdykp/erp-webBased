> Lanjutan setelah perubahan desain pengguna: [audit profil/master data dan hasil akhir 53 tes](MASTER_DATA_AUDIT_2026-10-16.md).

# Perbaikan UI dan reliabilitas — 16 Oktober 2026

Pekerjaan ini melanjutkan integrasi workflow sebelumnya dan mempertahankan keputusan dispatch tanpa approval. Tidak ada migrasi baru pada tahap ini; database operasional tidak dimodifikasi.

## Bug dan perilaku yang diperbaiki

- Modal dan panel kondisional disembunyikan oleh `x-cloak` sebelum Alpine siap. CSS penting berada langsung di head, sehingga tidak perlu menunggu bundle CSS untuk mencegah tampilan modal awal yang keliru.
- Sidebar mobile tersembunyi sejak render awal dan keluar dari navigasi keyboard saat tertutup; tetap terlihat pada desktop.
- Dialog utama memakai lapisan konsisten di atas sidebar, batas tinggi viewport, padding mobile, dan scrolling internal. Background halaman dikunci selama modal terbuka; keyboard Tab tetap di modal dan fokus kembali ke tombol pemicu setelah ditutup.
- Modal customer menggunakan klik pada backdrop, menggantikan listener outside di panel yang dapat bertabrakan dengan klik pembuka. Transisi keluar yang membuat panel hilang lebih cepat dari backdrop dihapus. Transisi masuk dipersingkat dan delay berlapis dihilangkan.
- Wizard customer create/edit melakukan validasi langkah aktif dan membawa pengguna ke field wajib yang belum lengkap saat submit dari keyboard. Field tersembunyi tidak lagi menyebabkan error browser `not focusable`. Langkah direset saat dibuka kembali; perpindahan langkah memfokuskan input yang relevan.
- Binding alamat edit customer sebelumnya memakai ekspresi kondisional sebagai target `x-model`, menyebabkan `Invalid left-hand side in assignment`. Kini memakai property alamat biasa dan memilih alamat kantor saat membuka edit.
- Indikator langkah aktif sebelumnya memiliki dua class background yang bertentangan sehingga nomor putih tidak terbaca. Class statis yang bertabrakan dihapus.
- Edit email customer memeriksa keunikan di users dan customers, mengecualikan akun sendiri; perubahan profil dilakukan dalam transaksi dengan lock customer. Error tidak menampilkan detail SQL ke pengguna.
- Tax ID/NPWP create dan edit kini disimpan, sesuai field yang sudah tersedia di form. Akun customer baru tidak lagi memakai password bersama `12345678`; memakai secret acak, lalu customer mengatur password lewat Forgot Password yang sudah tersedia. Tidak ada email sungguhan dikirim oleh pekerjaan ini.
- Form navigasi biasa melindungi dari submit berulang, menampilkan status busy, dan pulih saat kembali lewat browser history. Form AJAX, dialog, dan konfirmasi Alpine mempertahankan lifecycle masing-masing. Dialog delete mengonsumsi konfirmasi per submit agar tidak melewati konfirmasi baru setelah kembali lewat history.
- Tabel async melewati konfigurasi tanpa input yang sesuai, mencegah listener ganda, dan memakai pesan gagal berbahasa Inggris. Perlindungan stale response sebelumnya dipertahankan.
- Toast dapat ditutup, punya lebar responsif, tidak menghalangi klik di area kosong; success/info hilang setelah tujuh detik.
- Ukuran font, line-height, kontras label, focus ring, dan gutter utama dibuat lebih konsisten. Form Create Order/edit dan format laporan yang dilindungi dikecualikan dari perubahan tipografi/spacing utama.
- Preview kosong Order Management sebelumnya berada di tengah tinggi seluruh daftar, sehingga tampak seperti panel kosong pada viewport awal. Placeholder kini terlihat di atas; preview memiliki posisi sticky yang sesuai. Penutup span Receiving yang hilang juga diperbaiki.

## File utama tahap ini

| File | Tujuan |
|---|---|
| `resources/css/app.css` | Tipografi, gutter, modal responsif, focus ring, reduced motion |
| `resources/js/modal-lifecycle.js` | Focus trap/restoration, judul aksesibel, scroll lock |
| `resources/js/customer-wizard.js` | Validasi dan perpindahan langkah customer |
| `resources/js/form-submission.js` | Submit berulang dan pemulihan history |
| `resources/js/{app,async-table,delete-confirmation}.js` | Registrasi modul dan integrasi lifecycle |
| `resources/views/admin/customerList/{index,table}.blade.php` | Modal, binding edit, label aksi, wizard |
| `app/Http/Controllers/CustomerProfileController.php` | Keunikan email, NPWP, transaksi, secret akun baru |
| `resources/views/admin/layout/{app,aside,notif}.blade.php` | CSS awal, sidebar, scrolling, toast |
| `resources/views/layouts/{head,master,aside,navbar,notif}.blade.php` | Cloak dan shell customer |
| `resources/views/admin/bookings/index.blade.php` | Preview, markup dan modal delete |
| View lain dengan `x-show`/modal di business, production, dosimeter, testing, warehouse, customer | Cloak awal dan atribut dialog; artefak yang dilindungi tidak diubah |
| `resources/views/admin/workspace/dispatch.blade.php` | Dialog/backdrop dan Escape |
| `tests/Feature/CustomerManagementTest.php` | Create/edit, NPWP, duplicate email atomik |
| `tests/Support/ChecksAlpineExpressions.php`, `tests/Feature/OperationalWorkflowTest.php` | Parsing directive Alpine termasuk assignment x-model pada halaman render |
| `tests/js/{customer-wizard,form-submission,async-table}.test.mjs` | Regresi wizard, submit/history, async feedback |

Working tree sudah mengandung perubahan pengguna dan pekerjaan sebelumnya; daftar ini tidak mengatribusi seluruh diff proyek ke tahap ini.

## Hasil verifikasi

- **47 tes Laravel / 700 assertion lulus pada SQLite dan MySQL**, serta **14 tes JavaScript lulus**.
- PHP lint, kompilasi 96 Blade view, route/controller, dan build Vite diperiksa. Peringatan metadata Browserslist lama tidak menggagalkan build.
- Tes checksum artefak Create Order, ekspor dan laporan tetap lulus terhadap baseline sesi dan revisi laporan pengguna sebelumnya.
- Browser lokal memakai SQLite fixture `/tmp/beam-ux-20261015.sqlite`, bukan database operasional.
- Desktop 1280 px: Production, Product Testing, Documents, Settings, Order Management tidak memiliki overflow halaman horizontal dan tidak menampilkan modal saat awal. Tidak ada console error/warning baru pada rangkaian navigasi ini.
- Mobile 390×844: customer modal muat di viewport (pageWidth 390), focus input, Enter berpindah ke langkah field wajib yang kosong, Escape, Cancel, reopen/reset, dan buka-tutup cepat diperiksa.
- Tablet 768×1024: navigasi sidebar, warehouse layout (pageWidth 768), dialog delete lalu Cancel, dan focus restoration diperiksa. Tidak ada penghapusan data melalui browser.
- Dispatch desktop: referensi dan checkbox fixture diisi untuk membuka modal; Cancel mengembalikan fokus ke Confirm Dispatch. Tombol final pengiriman tidak ditekan dan stok fixture tetap ada.
- Bukti: `ux-evidence/customer-modal-mobile-20261016.png`, `warehouse-tablet-20261016.png`, `orders-desktop-20261016.png`, `dispatch-modal-desktop-20261016.png`.

## Batas verifikasi

Kendala batas penggunaan browser pada pekerjaan sebelumnya sudah tidak menghalangi sesi uji ini. Cakupan di atas tetap merupakan sampel; seluruh kombinasi interaksi di semua menu, semua peran, semua ukuran layar, perangkat scanner, session expiry, email sungguhan dan volume data produksi belum diuji menyeluruh. Tidak ada klaim seluruh aplikasi bebas bug. Riwayat batasan integrasi dan pengujian concurrency ada pada `WORKFLOW_INTEGRATION_2026-10-16.md`.
