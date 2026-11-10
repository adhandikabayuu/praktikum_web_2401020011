<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Buku;
use App\Models\Kategori;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $pemrograman = Kategori::where('nama', 'Pemrograman Web')->first();
        $basisData = Kategori::where('nama', 'Basis Data')->first();

        Buku::updateOrCreate(
            ['kode' => 'BK001'],
            [
                'judul' => 'Dasar PHP',
                'kategori_id' => $pemrograman->id,
            ]
        );

        Buku::updateOrCreate(
            ['kode' => 'BK002'],
            [
                'judul' => 'Dasar Basis Data',
                'kategori_id' => $basisData->id,
            ]
        );
    }
}
