# Penyempurnaan penerimaan, bahasa, dan CRUD — 15 Oktober 2026

## Perubahan yang ada dalam paket
- Penerimaan Barang/Smart Receiving: label dan bantuan operator diterjemahkan; input jumlah barang per pallet dan jumlah pallet fisik tidak memiliki atribut max angka. Sistem tetap membatasi lokasi berdasarkan stok kosong yang ada dan mencegah alokasi pallet yang berisi nol barang.
- Manajemen Order: tombol **Hapus** diperlihatkan untuk superadmin pada order pending yang belum mempunyai riwayat operasional; menggunakan modal konfirmasi yang sudah ada. Backend mengecek ulang status dan histori dalam transaksi, menolak penghapusan order yang sudah diterima/diproduksi/diserahkan.
- Penghapusan lokasi pallet: menolak penghapusan jika pallet punya status terisi, jumlah barang, current booking, isi aktif, atau placement aktif.
- Sejumlah halaman operasional/dispatch/quality/warehouse/master data dan teks konfirmasi hapus diterjemahkan ke Bahasa Indonesia. Terjemahan menyeluruh seluruh UI legacy *belum terverifikasi*.
- Konfirmasi hapus untuk modul lain yang sebelumnya memilikinya dipertahankan. Tombol hapus baru **tidak** ditambahkan ke semua entitas historis: aktivitas iradiasi, dosimetri, audit, dan dispatch tidak boleh dibuang dengan CRUD biasa.
- Form Create Order tetap identik dengan ZIP sebelumnya (foto produk opsional yang sudah tersedia tidak berubah). Template laporan, output PDF serta halaman terkait dijaga byte-by-byte dengan ZIP sebelumnya.

## Catatan keselamatan
"Tanpa maksimum" berlaku untuk angka input, bukan kapasitas fisik gudang. Setiap pallet yang dipakai memerlukan slot kosong yang terdaftar; mode jumlah pallet tidak boleh mengalokasikan nol unit. Untuk jumlah pallet fisik yang lebih besar daripada slot gudang, buat/otorisasi lokasi fisik tambahan terlebih dahulu. Jangan menghilangkan kontrol konflik stock.

## Pengujian
Lulus pemeriksaan sintaks PHP pada controller yang disentuh serta pemeriksaan kecocokan file terlindungi dan integritas ZIP. Belum ada pengujian Laravel end-to-end dengan database asli, permission aktual, maupun browser. Jalankan pengujian sebelum production.

## Instalasi
Backup database dan storage. Ekstrak lalu jalankan `composer install`, `npm ci`, `php artisan optimize:clear`, `php artisan migrate:status`, `php artisan migrate` sesuai migration yang belum terpasang, dan `npm run build`. Jangan `db:seed` atau `migrate:fresh` pada database yang punya data penting.
