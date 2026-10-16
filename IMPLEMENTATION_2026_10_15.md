# BeamCustom — tahap integrasi operasional 15 Oktober 2026

## Dilaksanakan
- Foto produk opsional ditambahkan pada modal submit Create Order; spesifikasi, perhitungan dan urutan input existing tidak diubah. Upload JPG/PNG/WebP maksimum 5 MB, kolom `booking_products.photo_path`, tautan foto di ringkasan detail.
- Sesi iradiasi manual dengan tanggal dan jam mulai/selesai, histori durasi, snapshot parameter mesin per sesi, validasi bentrokan mesin, tanpa timer otomatis. Mendukung penambahan sesi di hari berbeda. Mulai batch dicatat melalui sesi pertama, bukan `now()` dari tombol Start lama.
- Penyelesaian batch hanya jika minimal satu sesi manual telah dicatat. Waktu finish mengikuti waktu akhir sesi terakhir.
- Kolom catatan persetujuan dispatch, catatan dispatch, catatan review kualitas dan template produksi menjadi opsional; petugas/konfirmasi fisik tetap wajib.
- Label penerimaan, My Tasks, dan alur order sebagian diubah ke Indonesia. Tautan fitur tambahan di bawah workspace dihilangkan; backend terkait tidak dihapus karena masih direferensikan route/proses.
- Daftar slot pada assisted receiving diperketat dengan `filled_boxes` dan relasi contents.
- Form edit finansial existing tetap dan pembaruan finansial yang terkunci dibatasi pada superadmin/manager.

## Batasan penting
- **Form Create Order lama dipertahankan**; hanya slot foto opsional pada modal review; file `create.blade.php` tidak dirombak.
- **SELURUH template halaman Documents, controller report dan PDF tidak diubah**.
- Relokasi pallet **tidak** otomatis terjadi saat finish iradiasi; serah terima fisik tetap perlu alur dispatch yang diaudit.
- Tidak menghapus migrasi/tabel/data/fungsi historis hanya karena terlihat jarang digunakan.
- Ini bukan implementasi ulang total semua modul: dashboard global, seluruh navigasi, seluruh teks legacy, rancangan parameter mesin dinamis per-model, dan penataan penuh operasi lama **belum diselesaikan**. Jangan menyebutnya siap produksi tanpa uji end-to-end.
- Sebelum menjalankan, backup database + storage produk. Jalankan `php artisan migrate`, `php artisan optimize:clear` dan tes staging.
- Sesi manual baru hanya tersedia setelah migration. Data batch historis tanpa sesi baru memerlukan kebijakan migrasi/rekonsiliasi; jangan menulis sesi palsu.
- Durasi dihitung dari jam aktual yang diinput, bukan bukti jumlah unit yang telah teriradiasi. QA/dosimetri tetap menentukan verifikasi produk.

## Cek manual minimal
1. Buat order tanpa foto dan dengan foto; cek kolom `photo_path`, akses gambar admin, field spesifikasi tidak berubah.
2. Terima barang dan pastikan lokasi yang masih berisi quantity tidak ditawarkan.
3. Approval order, set parameter batch, buat sesi pada hari pertama dan kedua, pastikan overlap mesin ditolak.
4. Finish batch tanpa sesi ditolak; finish dengan sesi + QA valid berhasil.
5. Approve dispatch tanpa catatan, confirm dispatch tanpa catatan, pastikan referensi dan checkbox wajib.
6. Bandingkan byte/checksum template Documents/PDF dengan backup asli sebelum rilis.
