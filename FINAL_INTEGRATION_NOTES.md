# BeamCustom — Paket Integrasi Operasional (15 Oktober 2026)

## Fokus perubahan dari source terakhir
- Form Create Order asli dipertahankan, dengan tambahan foto produk opsional dari versi sebelumnya.
- Template dan controller laporan, cetak serta unduh PDF dipertahankan persis dari ZIP sebelumnya (diperiksa byte-by-byte untuk file terkait).
- Halaman Alur Order memiliki tab penerimaan, persiapan, sesi iradiasi, dosimetri, dokumen dan serah terima, dengan tautan ke form operasional masing-masing.
- Menu utama dan panel produksi mendapat istilah Bahasa Indonesia yang lebih jelas.
- Pencatatan sesi iradiasi tetap manual sesuai versi sebelumnya; batch dapat memiliki beberapa sesi.
- Modal Start Irradiation lama di Batch Queue yang sudah tidak dipakai dihapus.
- Catatan biasa opsional pada workflow yang sudah diperbarui; alasan koreksi relokasi tetap diwajibkan untuk audit.
- `DatabaseSeeder` tidak lagi memanggil `CustomListSeeder` destruktif. CustomListSeeder sekarang menggunakan firstOrCreate dan tidak melakukan TRUNCATE.
- Folder `bootstrap/cache` dan `storage/framework` disertakan dengan `.gitignore` agar Laravel bisa membuat cache setelah ekstraksi.

## Batasan dan pengujian
- Source dicek sintaks PHP dan integritas ZIP, namun tidak tersedia database lokal yang setara dengan pengguna dan tidak dijalankan end-to-end.
- Jangan pakai pada produksi sebelum backup DB/storage, menjalankan migration di staging, memeriksa izin role, dan uji lengkap customer → order → penerimaan → batch → sesi → dosimetri → serah terima.
- Akses serah terima masih mensyaratkan quality review yang sesuai SOP karena pengecekan keselamatan dan bukti pengeluaran tidak boleh dilepas begitu saja. Tab Quality Review terpisah bukan langkah navigasi utama.
- Form Create Order dan tampilan Documents/report tidak direstrukturisasi.
- Belum ada penghapusan besar terhadap file route/controller legacy: hal itu memerlukan coverage pengujian fitur dan referensi agar tidak merusak proyek.
- Parameter mesin mengikuti skema kolom existing, belum merupakan dynamic schema berbeda untuk setiap model mesin.
- My Tasks berfungsi sebagai antrean pekerjaan berdasarkan role, bukan tugas personal yang ditugaskan.

## Instalasi (development/staging)
1. Backup database dan direktori storage dari aplikasi sebelumnya.
2. Pastikan `bootstrap/cache`, `storage/framework/{cache/data,sessions,views}` writable oleh PHP.
3. `composer install && npm ci && php artisan optimize:clear`
4. Tinjau migration: `php artisan migrate:status` lalu `php artisan migrate` pada staging.
5. `npm run build`
6. JANGAN jalankan `php artisan migrate:fresh`/`db:wipe`; `php artisan db:seed` hanya setelah memeriksa seeder dan backup.

## Pengujian wajib
- Buat order lama dengan/tanpa foto dan bandingkan seluruh field spesifikasi.
- Penerimaan jumlah aktual, penentuan pallet dan deteksi lokasi terisi/duplikat.
- Dua sesi tanggal/jam pada hari berbeda, overlap mesin ditolak.
- Menyelesaikan batch dan mengisi dosimetri, termasuk kondisi gagal pemeriksaan.
- Otorisasi dan konfirmasi serah terima hanya saat barang fisik keluar.
- Bandingkan laporan existing dan hasil PDF dengan backup.
