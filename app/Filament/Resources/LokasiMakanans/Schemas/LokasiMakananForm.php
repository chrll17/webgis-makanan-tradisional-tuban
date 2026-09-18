<?php

namespace App\Filament\Resources\LokasiMakanans\Schemas;

use App\Models\Lokasi;
use App\Models\LokasiMakanan;
use App\Models\Makanan;
use Closure;
use Filament\Actions\Action as Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;

class LokasiMakananForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | DATA MAKANAN
                |--------------------------------------------------------------------------
                */

                Section::make('Informasi Makanan')
                    ->schema([
                        // Membuat dropdown untuk memilih makanan yang sudah ada di database
                        Select::make('makanan_id')
                            ->label('Pilih Makanan')
                            // Mengambil pilihan dari relasi 'makanan', yang ditampilkan ke user adalah kolom 'nama_makanan'
                            ->relationship('makanan', 'nama_makanan')
                            ->searchable() // Admin bisa mengetik untuk mencari nama makanan
                            ->preload() // Memuat daftar makanan sejak awal agar pencarian terasa instan
                            ->required()
                            
                            ->editOptionForm([
                                TextInput::make('nama_makanan')
                                    ->label('Nama Makanan')
                                    ->required()
                                    // 🔥 VALIDASI ANTI-DUPLIKASI SAAT EDIT 🔥
                                    ->rule(fn ($record) => function (string $attribute, $value, Closure $fail) use ($record) {
                                        // Membersihkan spasi di awal/akhir dan mengubah ketikan admin menjadi huruf kecil semua
                                        $inputBersih = strtolower(trim($value));
                                        
                                        // Cek langsung ke database dengan mengubah kolom menjadi huruf kecil semua (LOWER)
                                        $query = Makanan::whereRaw('LOWER(nama_makanan) = ?', [$inputBersih]);
                                        
                                        // Jika sedang mengedit, abaikan ID makanan itu sendiri agar tidak dianggap bentrok dengan dirinya sendiri
                                        if ($record) {
                                            $query->where('id', '!=', $record->id);
                                        }
                                        
                                        // Jika datanya ditemukan, gagalkan proses simpan dan munculkan error merah
                                        if ($query->exists()) {
                                            $fail('Makanan ini sudah ada di database.');
                                        }
                                    }),
                            ])

                            // HANYA TAMPILKAN IKON PENSIL DI HALAMAN EDIT (Disembunyikan di Halaman Tambah Data)
                            ->editOptionAction(function (Actions $action, string $operation) {
                                return $action->visible($operation === 'edit');
                            })

                            // 🔥 FITUR TAMBAH MAKANAN BARU VIA DROPDOWN (+) 🔥
                            ->createOptionForm([
                                TextInput::make('nama_makanan')
                                    ->required()
                                    ->maxLength(255)
                                    // Validasi anti-duplikasi saat membuat makanan baru
                                    ->rule(fn () => function (string $attribute, $value, Closure $fail) {
                                        $inputBersih = strtolower(trim($value));
                                        
                                        // Cek apakah makanan sudah ada di database
                                        if (Makanan::whereRaw('LOWER(nama_makanan) = ?', [$inputBersih])->exists()) {
                                            $fail('Makanan ini sudah ada di database! Silakan pilih dari dropdown.');
                                        }
                                    }),
                            ])
                            ->helperText('Pilih makanan atau klik tombol + untuk menambah kategori makanan baru secara langsung.')

                            // 🔥 VALIDASI KUSTOM ANTI-DUPLIKAT MENU DI WARUNG YANG SAMA 🔥
                            ->rules([
                                fn (Get $get, $livewire) => function (string $attribute, $value, Closure $fail) use ($get, $livewire) {
                                    $lokasiId = $get('lokasi_id'); // Ambil ID warung yang sedang dipilih
                                    
                                    // Deteksi ID record yang sedang diedit secara aman di Filament (Mendukung Page, Modal, & Relation Manager)
                                    $currentId = null;
                                    if (property_exists($livewire, 'record') && $livewire->record) {
                                        $currentId = $livewire->record->id;
                                    } elseif (method_exists($livewire, 'getRecord') && $livewire->getRecord()) {
                                        $currentId = $livewire->getRecord()->id;
                                    } elseif (method_exists($livewire, 'getMountedActionRecord') && $livewire->getMountedActionRecord()) {
                                        $currentId = $livewire->getMountedActionRecord()->id;
                                    } else {
                                        $currentId = request()->route('record');
                                    }

                                    // Jika makanan dan warung sudah dipilih, kita cek ke tabel pivot lokasi_makanans
                                    if ($value && $lokasiId) {
                                        $exists = LokasiMakanan::where('makanan_id', $value)
                                            ->where('lokasi_id', $lokasiId)
                                            // Pengecualian ID baris saat ini agar tidak bentrok dengan dirinya sendiri saat diedit
                                            ->when($currentId, fn ($query) => $query->where('id', '!=', $currentId))
                                            ->exists();

                                        // Jika kombinasi warung dan makanan ini sudah ada di database, batalkan
                                        if ($exists) {
                                            $fail('Makanan ini sudah terdaftar di tempat tersebut.');
                                        }
                                    }
                                },
                            ]),
                    ]),

                // ==========================================
                // BAGIAN 2: DETAIL MENU & MEDIA
                // ==========================================
                Section::make('Detail Menu & Media')
                    ->schema([
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3),

                        FileUpload::make('foto')
                            ->label('Foto')
                            ->image()
                            ->disk('public') // Disimpan di disk public (folder storage/app/public)
                            ->visibility('public') // Bisa diakses umum melalui internet/browser
                            ->directory('galeri') // Disimpan di subfolder 'galeri'
                            ->multiple()         // Mengizinkan upload lebih dari 1 foto
                            ->reorderable()      // Admin bisa menggeser/mengurutkan foto
                            ->maxFiles(5)        // (Opsional) Membatasi maksimal 5 foto agar server tidak berat
                            ->live() // Membuat komponen menjadi reaktif
                            ->afterStateUpdated(function ($state, Set $set) {
                                // Jika upload kosong atau bukan array, kosongkan juga state pilihan GPS
                                if (! is_array($state) || empty($state)) {
                                    $set('daftar_pilihan_gps', []); 
                                    $set('pilih_koordinat_gps', null); 
                                    return;
                                }

                                $gpsOptions = []; // Array sementara untuk menampung koordinat dari foto-foto
                                $index = 1; // Penanda nomor urut foto

                                // Loop semua foto untuk mencari yang memiliki EXIF GPS
                                foreach ($state as $file) {
                                    // Memastikan objek adalah file sementara yang sah di Livewire
                                    if (! $file instanceof TemporaryUploadedFile) {
                                        $index++; 
                                        continue; // Lanjut ke foto berikutnya jika file tidak valid
                                    }

                                    // Mengambil jalur fisik file sementara di server
                                    $path = $file->getRealPath();
                                    // Membaca metadata EXIF menggunakan fungsi bawaan PHP. Tanda @ mencegah error fatal jika foto tidak punya EXIF
                                    $exif = @exif_read_data($path, 0, true);
                                    // Mengambil bagian metadata GPS (jika ada)
                                    $gps = $exif['GPS'] ?? $exif ?? null;

                                    // Jika foto ini punya metadata GPS, langsung kita proses!
                                    if ($gps && isset($gps['GPSLatitude'], $gps['GPSLongitude'])) {
                                        try {
                                            // Menentukan arah mata angin (N=North/Utara, S=South/Selatan, E=East/Timur, W=West/Barat)
                                            $latRef = $gps['GPSLatitudeRef'] ?? 'N';
                                            $longRef = $gps['GPSLongitudeRef'] ?? 'E';

                                            // Mengonversi data pecahan EXIF menjadi angka desimal koordinat GPS (dibulatkan 8 desimal)
                                            $lat = round(self::getGpsCoordinate($gps['GPSLatitude'], $latRef), 8);
                                            $long = round(self::getGpsCoordinate($gps['GPSLongitude'], $longRef), 8);

                                            // Simpan ke array dengan format Key: "lat,long" & Value: "Label yang mudah dibaca"
                                            $key = "{$lat},{$long}"; 
                                            $gpsOptions[$key] = "Foto ke-{$index} (Lat: {$lat}, Long: {$long})"; 
                                        } catch (\Exception $e) {
                                            // Jika gagal memproses 1 foto, catat error di log server tanpa menghentikan sistem
                                            Log::error('Gagal memproses GPS dari salah satu foto: ' . $e->getMessage());
                                        }
                                    }
                                    $index++; 
                                }
                                // Simpan daftar koordinat yang ditemukan ke dalam state tersembunyi 'daftar_pilihan_gps'
                                $set('daftar_pilihan_gps', $gpsOptions);

                                // Jika ada foto ber-GPS yang ditemukan, otomatis pilih foto pertama sebagai default
                                if (! empty($gpsOptions)) {
                                    $firstKey = array_key_first($gpsOptions);
                                    $set('pilih_koordinat_gps', $firstKey);

                                    // Pecah teks "lat,long" menjadi 2 variabel terpisah
                                    [$lat, $long] = explode(',', $firstKey);
                                    // Panggil helper applyGpsLocation untuk mengisi otomatis form lokasi di bawah
                                    self::applyGpsLocation((float) $lat, (float) $long, $set);
                                }
                            })
                            ->helperText('Jika foto diambil langsung menggunakan kamera HP (GPS aktif), koordinat lokasi di bawah akan terisi otomatis.'),

                        // Input tersembunyi untuk menyimpan array pilihan koordinat hasil pembacaan foto di atas
                        Hidden::make('daftar_pilihan_gps') // 🔥 TAMBAHAN
                            ->default([]), // 🔥 TAMBAHAN

                        // Dropdown ini HANYA MUNCUL jika admin mengunggah LEBIH DARI 1 foto dan foto-foto tersebut punya koordinat yang berbeda!
                        Select::make('pilih_koordinat_gps') 
                            ->label('Pilih Sumber Koordinat GPS') 
                            ->helperText('Terdeteksi lebih dari satu foto dengan koordinat GPS berbeda. Silakan pilih koordinat foto mana yang ingin digunakan sebagai titik lokasi warung.') 
                            ->options(fn (Get $get) => $get('daftar_pilihan_gps') ?? []) // Mengambil opsi dari input Hidden di atas
                            // Kondisi visibilitas: Hitung jumlah item di 'daftar_pilihan_gps', munculkan jika lebih dari 1
                            ->visible(fn (Get $get) => count($get('daftar_pilihan_gps') ?? []) > 1) 
                            ->live() // 🔥 TAMBAHAN
                            ->afterStateUpdated(function ($state, Set $set) { // 🔥 TAMBAHAN
                                // Ketika admin mengganti pilihan foto di dropdown, langsung hitung ulang lokasinya!
                                if ($state && str_contains($state, ',')) { // 🔥 TAMBAHAN
                                    [$lat, $long] = explode(',', $state); // 🔥 TAMBAHAN
                                    self::applyGpsLocation((float) $lat, (float) $long, $set); // 🔥 TAMBAHAN
                                }
                            }),
                    ]),

                // ==========================================
                // BAGIAN 3: INFORMASI TEMPAT (LOKASI)
                // ==========================================
                Section::make('Informasi Tempat / Rumah Makan')
                    ->schema([
                        
                        // Dropdown untuk memilih tempat yang sudah ada
                        Select::make('lokasi_id')
                            ->label('Pilih Tempat')
                            ->relationship('lokasi', 'nama_tempat')
                            ->searchable()
                            ->preload()
                            // Wajib diisi JIKA admin TIDAK sedang mendaftarkan tempat baru
                            ->required(fn (Get $get) => ! $get('is_new_tempat'))
                            // Matikan/kunci dropdown ini JIKA admin mengaktifkan toggle "Daftarkan Tempat Baru"
                            ->disabled(fn (Get $get) => $get('is_new_tempat'))
                            ->placeholder('Cari tempat makan yang sudah terdaftar...')
                            // 🔥 KODE INI UNTUK EDIT LANGSUNG DARI DROPDOWN 🔥
                            ->editOptionForm([
                                TextInput::make('nama_tempat')
                                    ->label('Nama Tempat')
                                    ->required(),
                                Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->required(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('latitude')
                                            ->label('Latitude')
                                            ->numeric()
                                            ->required()
                                            ->minValue(-7.20) // Batas selatan pemetaan Kabupaten Tuban
                                            ->maxValue(-6.70),// Batas utara pemetaan Kabupaten Tuban
                                        TextInput::make('longitude')
                                            ->label('Longitude')
                                            ->numeric()
                                            ->required()
                                            ->minValue(111.50) // Batas barat pemetaan Kabupaten Tuban
                                            ->maxValue(112.30),// Batas timur pemetaan Kabupaten Tuban
                                    ]),
                            ])
                            // HANYA TAMPILKAN IKON PENSIL DI HALAMAN EDIT (Disembunyikan di Halaman Tambah Data)
                            ->editOptionAction(function (Actions $action, string $operation) {
                                return $action->visible($operation === 'edit');
                            }),

                        // Toggle penanda jika ingin mendaftarkan tempat baru
                        Toggle::make('is_new_tempat')
                            ->label('Daftarkan Tempat Baru')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                // Jika toggle disala-nyalakan (ON / true), kosongkan pilihan di dropdown lokasi_id agar tidak rancu
                                if ($state === true) {
                                    $set('lokasi_id', null);
                                }
                            }),

                        // Form input tambahan yang hanya muncul jika "Daftarkan Tempat Baru" bernilai TRUE
                        Group::make([
                            TextInput::make('new_nama_tempat')
                                ->label('Nama Tempat Baru')
                                ->required(fn (Get $get) => $get('is_new_tempat')), // Wajib diisi hanya saat toggle Tempat Baru aktif

                            Textarea::make('new_alamat')
                                ->label('Alamat')
                                ->required(fn (Get $get) => $get('is_new_tempat')),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('new_lat')
                                        ->label('Latitude')
                                        ->numeric()
                                        ->minValue(-7.20)
                                        ->maxValue(-6.70)
                                        ->required(fn (Get $get) => $get('is_new_tempat')),

                                    TextInput::make('new_long')
                                        ->label('Longitude')
                                        ->numeric()
                                        ->minValue(111.50)
                                        ->maxValue(112.30)
                                        ->required(fn (Get $get) => $get('is_new_tempat')),
                                ]),
                                
                            // BOKS PERINGATAN (Hanya muncul otomatis jika koordinat (new_lat & new_long) yang diketik/ditarik dari foto ternyata sudah ada di database)
                            Placeholder::make('peringatan_duplikat_new')
                                ->label('⚠️ Peringatan Koordinat Ganda')
                                ->content(function (Get $get) {
                                    $lat = $get('new_lat');
                                    $long = $get('new_long');

                                    if (! $lat || ! $long) return null;

                                    // Cari apakah ada lokasi lain dengan koordinat yang persis sama
                                    $existing = Lokasi::where('latitude', $lat)
                                        ->where('longitude', $long)
                                        ->get();

                                    // Cek apakah ada data yang ditemukan (isNotEmpty)
                                    if ($existing->isNotEmpty()) {
                                        $jumlah = $existing->count();

                                        // Membuat daftar bullet HTML berisikan nama tempat yang bentrok
                                        $listHtml = '<ul class="mt-2 ml-2 list-disc list-inside space-y-1 text-sm font-normal text-gray-700 dark:text-gray-300">';
                                        foreach ($existing as $loc) {
                                            $listHtml .= "<li><span class='font-bold text-amber-700 dark:text-amber-400'>\"{$loc->nama_tempat}\"</span> &mdash; <span class='italic text-xs text-gray-500'>{$loc->alamat}</span></li>";
                                        }
                                        $listHtml .= '</ul>';

                                        // Tampilkan alert box dengan jumlah total tempat yang bentrok
                                        return new HtmlString(
                                            "<div class='p-4 bg-amber-500/10 border border-amber-500 rounded-lg text-amber-600 dark:text-amber-400 font-medium'>
                                                Titik koordinat ini sudah dipakai oleh <span class='font-bold underline'>{$jumlah} tempat</span> berikut di database:
                                                {$listHtml}
                                            </div>"
                                        );
                                    }
                                    return null;
                                })
                                ->visible(function (Get $get) {
                                    $lat = $get('new_lat');
                                    $long = $get('new_long');
                                    return $lat && $long && Lokasi::where('latitude', $lat)
                                        ->where('longitude', $long)
                                        ->exists();
                                }),

                            // TOGGLE KONFIRMASI (Wajib diaktifkan jika peringatan di atas muncul)
                            Toggle::make('konfirmasi_duplikat_new')
                                ->label('Saya yakin ini adalah tempat baru yang berbeda (misal: satu pasar / pujasera dengan tempat di atas)')
                                ->accepted() // Memaksa tombol "Simpan" terkunci jika toggle ini belum dinyalakan (saat boks peringatan muncul)
                                ->validationMessages([
                                    'accepted' => 'Anda harus mengonfirmasi bahwa tempat ini memang berada di lokasi yang sama dengan warung yang sudah terdaftar.',
                                ])
                                ->visible(function (Get $get) {
                                    $lat = $get('new_lat');
                                    $long = $get('new_long');
                                    return $lat && $long && Lokasi::where('latitude', $lat)
                                        ->where('longitude', $long)
                                        ->exists();
                                })
                                ->dehydrated(false), // Mencegah nilai toggle ini dikirim ke database (karena kolom konfirmasi_duplikat_new tidak ada di tabel)
                        ])->visible(fn (Get $get) => $get('is_new_tempat')), // Seluruh grup ini hanya muncul jika toggle Tempat Baru ON

                    ]),
            ]);
    }

    /**
     * Helper Function internal untuk mengonversi data GPS EXIF yang rumit menjadi angka desimal biasa (Decimal Degrees).
     */
    private static function getGpsCoordinate($coordinate, $ref): float
    {
        // Jika format yang dibaca EXIF sudah berbentuk desimal biasa, langsung konversi tipe ke float
        if (!is_array($coordinate)) {
            $decimal = (float) $coordinate;
        } else {
            // Jika formatnya array DMS (Degrees/Minutes/Seconds) seperti ["7/1", "54/1", "30/1"], kita hitung pembagian pecahannya
            $parts = array_map(function ($part) {
                if (is_string($part) && str_contains($part, '/')) {
                    [$num, $den] = explode('/', $part);
                    return $den == 0 ? 0 : ((float)$num / (float)$den);
                }
                return (float) $part;
            }, $coordinate);

            // Memisahkan Derajat (Degrees), Menit (Minutes), dan Detik (Seconds)
            $degrees = $parts[0] ?? 0;
            $minutes = $parts[1] ?? 0;
            $seconds = $parts[2] ?? 0;

            // Rumus konversi DMS ke Decimal Degree: Derajat + (Menit / 60) + (Detik / 3600)
            $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
        }

        // Jika Jika belahan bumi Selatan (S) atau Barat (W), maka koordinat bernilai negatif (-). Tuban berada di Selatan (S), jadi otomatis minus
        return (strtoupper(trim($ref)) === 'S' || strtoupper(trim($ref)) === 'W') ? -$decimal : $decimal;
    }

    /**
     * Helper Function pintar untuk mencocokkan koordinat foto dengan database warung secara otomatis (Auto-Match GIS).
     */
    private static function applyGpsLocation(float $lat, float $long, Set $set): void
    {
        // Menentukan jarak toleransi pergeseran GPS sebesar 0.0001 derajat (setara dengan sekitar 11 meter di permukaan bumi)
        $tolerance = 0.0001; 

        // Mencari apakah di database ADA warung yang letaknya di dalam kotak radius toleransi 11 meter dari titik foto yang diupload
        $lokasiExisting = Lokasi::whereBetween('latitude', [$lat - $tolerance, $lat + $tolerance])
                                            ->whereBetween('longitude', [$long - $tolerance, $long + $tolerance])
                                            ->first();

        // KASUS 1: FOTO DIPOTRET DI WARUNG YANG SUDAH TERDAFTAR
        if ($lokasiExisting) {
            // Matikan toggle Tempat Baru karena tempatnya sudah ada
            $set('is_new_tempat', false);
            // Otomatis pilih nama warung yang ditemukan pada dropdown Pilih Tempat
            $set('lokasi_id', $lokasiExisting->id);

            // Munculkan pop-up hijau di pojok kanan atas layar admin untuk memberi tahu bahwa sistem bekerja otomatis
            Notification::make()
                ->title('Lokasi Sudah Terdaftar!')
                ->body("Koordinat yang dipilih berada di area warung '{$lokasiExisting->nama_tempat}'. Sistem otomatis memilih warung tersebut.")
                ->success()
                ->send();
        // KASUS 2: FOTO DIPOTRET DI WARUNG YANG BELUM ADA DI DATABASE
        } else {
            // Nyalakan toggle "Daftarkan Tempat Baru"
            $set('is_new_tempat', true);
            // Otomatis isi kotak Latitude dan Longitude di form pendaftaran tempat baru dengan koordinat dari foto
            $set('new_lat', $lat);
            $set('new_long', $long);
        }
    }
}
