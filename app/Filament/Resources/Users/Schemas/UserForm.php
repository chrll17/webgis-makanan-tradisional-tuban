<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    // 1. Wajib diisi HANYA saat membuat user baru ('create')
                    ->required(fn ($operation): bool => $operation === 'create')
                    // 2. Jika saat edit kolom ini dikosongkan, jangan kirim data kosong ke database
                    ->dehydrated(fn ($state): bool => filled($state))
                    // 3. (Opsional) Ubah label saat halaman edit agar admin paham
                    ->label(fn ($operation): string => $operation === 'edit' ? 'Password Baru (Kosongkan jika tidak diganti)' : 'Password'),
                Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ])
                    ->default('user')
                    ->required(),
            ]);
    }
}
