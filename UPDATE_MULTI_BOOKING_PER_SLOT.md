# Pembaruan Gudang: Banyak Produk/Booking per Petak

## Perilaku baru
- Receiving menawarkan seluruh petak terdaftar, baik kosong maupun terisi.
- Alokasi beberapa pallet fisik boleh memilih petak yang sama.
- Tidak ada batas maksimum buatan aplikasi untuk jumlah barang dalam suatu petak. Operator wajib memverifikasi kelayakan kapasitas fisik dan SOP.
- `pallet_contents` merupakan sumber kepemilikan barang per booking; `filled_boxes` total kuantitas aktif sebagai nilai agregat historis, dan `current_booking_id` tidak digunakan untuk kepemilikan tunggal.
- Dispatch menghapus isi booking yang dikirim saja dan mempertahankan isi booking lain dalam petak yang sama.
- Relokasi gudang memindahkan seluruh isi petak asal dan menggabungkannya dengan isi petak tujuan sambil memperbarui catatan placement tiap booking. Relokasi sebagian isi belum tersedia di menu resmi.
- Endpoint relokasi lama pada AdminBookingController dinonaktifkan agar tidak mem-bypass histori penempatan.

## Penting sebelum menjalankan
1. Backup database dan storage.
2. Periksa konsistensi stok historis; perubahan ini tidak mengubah/merekonsiliasi data lama otomatis.
3. Hentikan proses receiving/dispatch yang bersamaan selama proses deployment.
4. Jalankan `composer install`, `npm ci`, `php artisan optimize:clear`, `npm run build`.
5. Uji skenario: Booking A terima 100 di petak X; Booking B terima 75 di petak X; dispatch A menyisakan 75 B; pindahkan isi X ke petak Y yang sudah berisi barang tanpa kehilangan catatan A/B; periksa status slot setelah semua isi keluar.

## Batasan
- `filled_boxes` adalah agregat numerik warisan dan tidak membedakan satuan (dus, drum, karung). Lihat rincian produk untuk satuan sesungguhnya. Perlu refactor skema untuk agregasi lintas satuan yang bermakna.
- Belum diuji menggunakan database asli atau pengujian Laravel end-to-end. Hanya pemeriksaan sintaks PHP/JS dan konsistensi source.
- File Create Order dan file laporan/PDF tidak diubah.
- Tidak ada perubahan migration yang otomatis membenahi data lama.
