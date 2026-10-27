
<?php

use App\Http\Controllers\BukuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('mahasiswa.index');
});

/*
|--------------------------------------------------------------------------
| CRUD BUKU PERPUSTAKAAN
|--------------------------------------------------------------------------
*/

Route::get('/buku', [BukuController::class, 'index'])
    ->name('buku.index');

Route::get('/buku/tambah', [BukuController::class, 'create'])
    ->name('buku.create');

Route::post('/buku', [BukuController::class, 'store'])
    ->name('buku.store');

Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])
    ->name('buku.edit');

Route::put('/buku/{buku}', [BukuController::class, 'update'])
    ->name('buku.update');

Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])
    ->name('buku.destroy');

/*
|--------------------------------------------------------------------------
| READ, PENCARIAN, DAN FILTER MAHASISWA
|--------------------------------------------------------------------------
*/

Route::get('/mahasiswa', function (Request $request) {
    $kataKunci = trim((string) $request->query('q', ''));
    $programStudiId = $request->query('program_studi_id', '');

    try {
        $pdo = DB::connection()->getPdo();

        $sql = 'SELECT m.id, m.nim, m.nama, m.email, m.usia,
                       m.program_studi_id, p.nama_prodi
                FROM mahasiswa AS m
                JOIN program_studi AS p
                  ON p.id = m.program_studi_id
                WHERE 1 = 1';

        $parameter = [];

        if ($kataKunci !== '') {
            $sql .= ' AND (
                m.nim LIKE :kata_kunci_nim
                OR m.nama LIKE :kata_kunci_nama
                OR m.email LIKE :kata_kunci_email
            )';

            $nilai = '%' . $kataKunci . '%';

            $parameter['kata_kunci_nim'] = $nilai;
            $parameter['kata_kunci_nama'] = $nilai;
            $parameter['kata_kunci_email'] = $nilai;
        }

        if ($programStudiId !== '') {
            $sql .= ' AND m.program_studi_id = :program_studi_id';
            $parameter['program_studi_id'] = (int) $programStudiId;
        }

        $sql .= ' ORDER BY m.nim';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameter);
        $daftarMahasiswa = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmtProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );

        $stmtProdi->execute();
        $daftarProgramStudi = $stmtProdi->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'daftarProgramStudi',
            'kataKunci',
            'programStudiId'
        ));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data mahasiswa tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('mahasiswa.index');

/*
|--------------------------------------------------------------------------
| CREATE MAHASISWA
|--------------------------------------------------------------------------
*/

Route::post('/mahasiswa', function (Request $request) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:20'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => [
            'required',
            'integer',
            'exists:program_studi,id',
        ],
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE nim = :nim OR email = :email'
        );

        $cek->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan.'
            );
        }

        $simpan = $pdo->prepare(
            'INSERT INTO mahasiswa
             (nim, nama, email, usia, program_studi_id)
             VALUES
             (:nim, :nama, :email, :usia, :program_studi_id)'
        );

        $simpan->execute($data);

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil ditambahkan.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal disimpan. Periksa koneksi dan data.'
        );
    }
})->name('mahasiswa.store');

/*
|--------------------------------------------------------------------------
| FORM EDIT MAHASISWA
|--------------------------------------------------------------------------
*/

Route::get('/mahasiswa/{id}/edit', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare(
            'SELECT id, nim, nama, email, usia, program_studi_id
             FROM mahasiswa
             WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
        $mahasiswa = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        $stmtProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );

        $stmtProdi->execute();
        $daftarProgramStudi = $stmtProdi->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa-edit', compact(
            'mahasiswa',
            'daftarProgramStudi'
        ));
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')->with(
            'gagal',
            'Data gagal dibuka untuk diubah.'
        );
    }
})->name('mahasiswa.edit');

/*
|--------------------------------------------------------------------------
| UPDATE MAHASISWA
|--------------------------------------------------------------------------
*/

Route::put('/mahasiswa/{id}', function (
    Request $request,
    int $id
) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:20'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => [
            'required',
            'integer',
            'exists:program_studi,id',
        ],
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE (nim = :nim OR email = :email)
               AND id <> :id'
        );

        $cek->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
            'id' => $id,
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan mahasiswa lain.'
            );
        }

        $ubah = $pdo->prepare(
            'UPDATE mahasiswa
             SET nim = :nim,
                 nama = :nama,
                 email = :email,
                 usia = :usia,
                 program_studi_id = :program_studi_id
             WHERE id = :id'
        );

        $ubah->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
            'id' => $id,
        ]);

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil diperbarui.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi dan data.'
        );
    }
})->name('mahasiswa.update');

/*
|--------------------------------------------------------------------------
| DELETE MAHASISWA
|--------------------------------------------------------------------------
*/

Route::delete('/mahasiswa/{id}', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare(
            'DELETE FROM mahasiswa WHERE id = :id'
        );

        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil dihapus.'
        );
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')->with(
            'gagal',
            'Data gagal dihapus. Periksa relasi atau koneksi.'
        );
    }
})->name('mahasiswa.destroy');
