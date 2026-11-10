<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        Kategori::updateOrCreate(
            ['nama' => 'Pemrograman Web'],
            ['nama' => 'Pemrograman Web']
        );

        Kategori::updateOrCreate(
            ['nama' => 'Basis Data'],
            ['nama' => 'Basis Data']
        );
    }
}
