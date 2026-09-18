<?php

namespace App\Filament\Resources\Lokasis\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use App\Models\Lokasi;

class LokasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_tempat')
                    ->required(),
                Textarea::make('alamat')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->required()
                    ->minValue(-7.20)
                    ->maxValue(-6.70)
                    ->live(onBlur: true), // Reaktif saat admin selesai mengetik/paste

                TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->required()
                    ->minValue(111.50)
                    ->maxValue(112.30)
                    ->live(onBlur: true),

                // BOKS PERINGATAN (Hanya muncul otomatis jika lat & long sudah ada di database)
                Placeholder::make('peringatan_duplikat')
                    ->label('⚠️ Peringatan Koordinat Ganda')
                    // Isi teks/HTML di dalam placeholder ini dibuat dinamis menggunakan function yang menangkap fungsi Get dan data lama ($record)
                    ->content(function (Get $get, ?Lokasi $record) {
                        // Mengambil nilai latitude dan longitude yang sedang diketik oleh admin di form saat ini
                        $lat = $get('latitude');
                        $long = $get('longitude');

                        // Jika salah satu kolom koordinat masih kosong, hentikan fungsi dan jangan tampilkan apa-apa (null)
                        if (! $lat || ! $long) return null;

                        // Cari apakah ada lokasi lain dengan koordinat yang persis sama
                        $existing = Lokasi::where('latitude', $lat)
                            ->where('longitude', $long)
                            // ->when($record, ...): Jika form dalam mode EDIT ($record ada isinya), maka abaikan ID tempat yang sedang diedit ini.
                            // Tujuannya agar saat mengedit tempat lama tanpa mengubah koordinat, sistem tidak menganggap tempat tersebut bentrok dengan dirinya sendiri.
                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                            ->get(); // Ambil semua data yang bentrok (jika ada)

                        // Cek apakah ada data yang ditemukan (isNotEmpty)
                        if ($existing->isNotEmpty()) {
                            // Menghitung jumlah tempat yang menggunakan titik koordinat tersebut
                            $jumlah = $existing->count();
                            
                            // Rangkai daftar nama tempat ke dalam format list HTML
                            $listHtml = '<ul class="mt-2 ml-2 list-disc list-inside space-y-1 text-sm font-normal text-gray-700 dark:text-gray-300">';
                            foreach ($existing as $loc) {
                                // Menambahkan poin list berisi nama tempat (cetak tebal/kuning) dan alamatnya (cetak miring)
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

                        // Jika tidak ada yang bentrok, kembalikan null (boks tidak akan berisi apa-apa)
                        return null;
                    })
                    // Mengatur kapan boks placeholder ini boleh dimunculkan ke layar admin
                    ->visible(function (Get $get, ?Lokasi $record) {
                        $lat = $get('latitude');
                        $long = $get('longitude');
                        // Jika koordinat belum lengkap diisi, sembunyikan boks peringatan
                        if (! $lat || ! $long) return false;

                        // Boks hanya akan MUNCUL (true) jika query exists() menghasilkan nilai true (benar-benar ada duplikat di database)
                        return Lokasi::where('latitude', $lat)
                            ->where('longitude', $long)
                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                            ->exists();
                    }),

                // TOGGLE KONFIRMASI (Wajib diaktifkan jika peringatan di atas muncul)
                Toggle::make('konfirmasi_duplikat')
                    ->label('Saya yakin ini adalah tempat baru yang berbeda (misal: satu pasar / pujasera dengan tempat di atas)')
                    // Memaksa admin mengaktifkan toggle ini agar form bisa disimpan!
                    // Jika admin mencoba menekan tombol "Simpan" tapi toggle ini masih OFF, proses simpan akan diblokir oleh Filament
                    ->accepted() 
                    // Pesan error merah kustom yang akan muncul jika admin lupa mengaktifkan toggle sebelum menyimpan
                    ->validationMessages([
                        'accepted' => 'Anda harus mengonfirmasi bahwa tempat ini memang berada di lokasi yang sama dengan warung yang sudah terdaftar.',
                    ])
                    // Aturan kemunculan toggle ini dibuat sama persis dengan aturan kemunculan boks peringatan di atas.
                    // Toggle hanya akan muncul jika memang terdeteksi ada koordinat ganda di database.
                    ->visible(function (Get $get, ?Lokasi $record) {
                        $lat = $get('latitude');
                        $long = $get('longitude');
                        if (! $lat || ! $long) return false;

                        return Lokasi::where('latitude', $lat)
                            ->where('longitude', $long)
                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                            ->exists();
                    })
                    ->dehydrated(false), // Agar field konfirmasi ini tidak ikut disimpan ke tabel lokasis di database
            ]);
    }
}
