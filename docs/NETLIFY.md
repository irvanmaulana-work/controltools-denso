# Menjalankan CT-Insulator di Netlify

Aplikasi Netlify memakai backend Node.js di `netlify/server/index.mjs`. Kode Laravel tetap disimpan sebagai referensi, tetapi tidak dijalankan atau dipublikasikan oleh Netlify. Server merender halaman login, dashboard, pengguna, partlist, tiket, dan riwayat secara langsung; halaman ini tidak memerlukan JavaScript browser.

## Deploy dan akun pertama

Netlify menggunakan Node.js 24, menjalankan perintah build yang tercantum di `netlify.toml`, dan mempublikasikan aset dari `public/build`. Jangan menambahkan redirect `/* /index.html 200`: route aplikasi ditangani oleh server. Folder publish tidak memuat `index.php` atau kode PHP lain.

Netlify Database menyediakan Postgres. Skema ada di `db/schema.ts`, dan migrasi di `netlify/database/migrations` diterapkan otomatis saat deploy. Jangan menjalankan migrasi secara manual. Database Netlify yang baru tidak otomatis berisi akun atau data dari database Laravel sebelumnya.

Variabel rahasia `INITIAL_ADMIN_PASSWORD` diperlukan untuk penyiapan admin pertama. Gunakan 12–128 karakter; nilainya dimasukkan melalui konfigurasi lingkungan, bukan kode atau berkas yang di-commit. Pada situs setelah deploy, buka `/setup`, isi nama, NPK, username, dan password awal tersebut. Penyiapan terkunci setelah akun pertama dibuat. Password disimpan dalam bentuk hash scrypt. Setelah itu, masuk melalui `/login`.

Admin menambahkan akun approver dan member dari menu Pengguna serta menambahkan stok dari menu Partlist. Member membuat tiket dengan memilih partlist dan approver. Approver hanya dapat memproses tiket yang ditugaskan kepadanya. Stok berkurang saat approval, dengan transaksi dan penguncian baris untuk mencegah pemotongan ganda. Penolakan wajib menyertakan alasan dan tidak mengurangi stok. Approver juga dapat menambahkan partlist baru dengan jumlah awal 1, sesuai aturan aplikasi Laravel.

Perubahan peran dibaca dari database pada setiap permintaan. Perubahan password atau peran mencabut semua sesi akun tersebut sehingga pengguna perlu masuk kembali. Sesi disimpan di database, berlaku delapan jam, dan memakai cookie Secure/HttpOnly. Semua perubahan data memerlukan token CSRF serta origin yang sesuai. Percobaan login dibatasi dalam database, sehingga batas tetap berlaku saat server berganti instance.

## Pengembangan dan pemeriksaan

Gunakan `npm test` untuk pengujian integrasi melalui HTTP dengan Postgres sementara (PGlite), tanpa menyentuh data Netlify. Pengujian mencakup penyiapan pertama, login/logout, CSRF, akses per peran, CRUD, validasi, approval, penolakan, pengeluaran stok, pemrosesan bersamaan, serta pencabutan sesi.

Untuk pengembangan dengan fitur Netlify, gunakan `netlify dev --port 8889`. Netlify tetap melakukan validasi build serta penerapan migrasi saat deployment. Pengujian lokal bukan bukti bahwa deploy atau migrasi produksi sudah berhasil.
