# panduan
## 1. rubah migrasi users
hapus kode bawaan migrasi users yang tidak digunakan

## 2. model & migrasi Makanan
Jalankan dari folder project:
```powershell
php artisan make:model Makanan -m
```

## 3. Buat migrasi aktivasi ekstensi postgis
Jalankan dari folder project:
```powershell
php artisan make:migration enable_postgis_extension
```
migrasi ini harus dibuat sebelum migrasi lokasi karena tabel lokasi perlu menggunakan ekstensi postgis

## 4. model & migrasi Lokasi
Jalankan dari folder project:
```powershell
php artisan make:model Lokasi -m
```

## 5. model & migrasi LokasiMakanan
Jalankan dari folder project:
```powershell
php artisan make:model LokasiMakanan -m
```

## 6. model & migrasi PostingMakanan
Jalankan dari folder project:
```powershell
php artisan make:model PostingMakanan -m
```