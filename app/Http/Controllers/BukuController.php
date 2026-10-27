<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDO;
use Throwable;

class BukuController extends Controller
{
    /**
     * Menampilkan data buku, pencarian, dan filter kategori.
     */
    public function index(Request $request)
    {
        $kataKunci = trim((string) $request->query('q', ''));
        $kategoriId = $request->query('kategori_buku_id', '');

        try {
            $pdo = DB::connection()->getPdo();

            $sql = 'SELECT b.id, b.kode_buku, b.judul, b.isbn,
                           b.tahun_terbit, b.kategori_buku_id,
                           k.nama_kategori
                    FROM buku AS b
                    JOIN kategori_buku AS k
                      ON k.id = b.kategori_buku_id
                    WHERE 1 = 1';

            $parameter = [];

            if ($kataKunci !== '') {
                $sql .= ' AND (
                    b.kode_buku LIKE :kode
                    OR b.judul LIKE :judul
                    OR b.isbn LIKE :isbn
                )';

                $nilai = '%' . $kataKunci . '%';

                $parameter['kode'] = $nilai;
                $parameter['judul'] = $nilai;
                $parameter['isbn'] = $nilai;
            }

            if ($kategoriId !== '') {
                $sql .= ' AND b.kategori_buku_id = :kategori';
                $parameter['kategori'] = (int) $kategoriId;
            }

            $sql .= ' ORDER BY b.kode_buku';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($parameter);

            $daftarBuku = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmtKategori = $pdo->prepare(
                'SELECT id, nama_kategori
                 FROM kategori_buku
                 ORDER BY nama_kategori'
            );

            $stmtKategori->execute();

            $daftarKategori = $stmtKategori->fetchAll(PDO::FETCH_ASSOC);

            return view('buku.index', compact(
                'daftarBuku',
                'daftarKategori',
                'kataKunci',
                'kategoriId'
            ));
        } catch (Throwable $error) {
            report($error);

            return response(
                'Data buku tidak dapat dibaca. Silakan periksa koneksi database.',
                500
            );
        }
    }

    /**
     * Menampilkan form tambah buku.
     */
    public function create()
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare(
                'SELECT id, nama_kategori
                 FROM kategori_buku
                 ORDER BY nama_kategori'
            );

            $stmt->execute();

            $daftarKategori = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return view('buku.create', compact('daftarKategori'));
        } catch (Throwable $error) {
            report($error);

            return response(
                'Form buku tidak dapat dibuka. Silakan periksa database.',
                500
            );
        }
    }

    /**
     * Memvalidasi dan menyimpan buku baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_buku' => [
                'required', 'string', 'size:10', 'regex:/^[A-Za-z0-9]+$/',
            ],
            'judul' => ['required', 'string', 'min:3', 'max:100'],
            'isbn' => ['required', 'string', 'size:13', 'regex:/^[0-9]{13}$/'],
            'tahun_terbit' => [
                'required', 'integer', 'between:2000,2026',
            ],
            'kategori_buku_id' => [
                'required', 'integer', 'exists:kategori_buku,id',
            ],
        ], [
            'kode_buku.size' => 'Kode buku harus tepat 10 karakter.',
            'kode_buku.regex' => 'Kode buku hanya boleh berisi huruf dan angka.',
            'judul.min' => 'Judul buku minimal 3 karakter.',
            'judul.max' => 'Judul buku maksimal 100 karakter.',
            'isbn.size' => 'ISBN harus tepat 13 digit.',
            'isbn.regex' => 'ISBN hanya boleh berisi 13 angka.',
            'tahun_terbit.between' => 'Tahun terbit harus antara 2000 dan 2026.',
            'kategori_buku_id.exists' => 'Kategori yang dipilih tidak tersedia.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        try {
            $pdo = DB::connection()->getPdo();

            $cek = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM buku
                 WHERE kode_buku = :kode OR isbn = :isbn'
            );

            $cek->execute([
                'kode' => $data['kode_buku'],
                'isbn' => $data['isbn'],
            ]);

            if ((int) $cek->fetchColumn() > 0) {
                return back()->withInput()->with(
                    'gagal',
                    'Kode buku atau ISBN sudah digunakan.'
                );
            }

            $simpan = $pdo->prepare(
                'INSERT INTO buku
                    (kode_buku, judul, isbn, tahun_terbit,
                     kategori_buku_id, created_at, updated_at)
                 VALUES
                    (:kode, :judul, :isbn, :tahun,
                     :kategori, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
            );

            $simpan->execute([
                'kode' => $data['kode_buku'],
                'judul' => $data['judul'],
                'isbn' => $data['isbn'],
                'tahun' => $data['tahun_terbit'],
                'kategori' => $data['kategori_buku_id'],
            ]);

            return redirect()->route('buku.index')->with(
                'berhasil',
                'Data buku berhasil ditambahkan.'
            );
        } catch (Throwable $error) {
            report($error);

            return back()->withInput()->with(
                'gagal',
                'Data buku gagal disimpan. Periksa data dan koneksi database.'
            );
        }
    }

    /**
     * Menampilkan form edit buku.
     */
    public function edit(string $buku)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare(
                'SELECT id, kode_buku, judul, isbn,
                        tahun_terbit, kategori_buku_id
                 FROM buku
                 WHERE id = :id'
            );

            $stmt->execute(['id' => $buku]);

            $dataBuku = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dataBuku) {
                return redirect()->route('buku.index')->with(
                    'gagal',
                    'Data buku tidak ditemukan.'
                );
            }

            $stmtKategori = $pdo->prepare(
                'SELECT id, nama_kategori
                 FROM kategori_buku
                 ORDER BY nama_kategori'
            );

            $stmtKategori->execute();

            $daftarKategori = $stmtKategori->fetchAll(PDO::FETCH_ASSOC);

            return view('buku.edit', compact(
                'dataBuku',
                'daftarKategori'
            ));
        } catch (Throwable $error) {
            report($error);

            return redirect()->route('buku.index')->with(
                'gagal',
                'Form edit buku tidak dapat dibuka.'
            );
        }
    }

    /**
     * Memvalidasi dan memperbarui buku.
     */
    public function update(Request $request, string $buku)
    {
        $validator = Validator::make($request->all(), [
            'kode_buku' => [
                'required', 'string', 'size:10', 'regex:/^[A-Za-z0-9]+$/',
            ],
            'judul' => ['required', 'string', 'min:3', 'max:100'],
            'isbn' => ['required', 'string', 'size:13', 'regex:/^[0-9]{13}$/'],
            'tahun_terbit' => [
                'required', 'integer', 'between:2000,2026',
            ],
            'kategori_buku_id' => [
                'required', 'integer', 'exists:kategori_buku,id',
            ],
        ], [
            'kode_buku.size' => 'Kode buku harus tepat 10 karakter.',
            'kode_buku.regex' => 'Kode buku hanya boleh berisi huruf dan angka.',
            'judul.min' => 'Judul buku minimal 3 karakter.',
            'judul.max' => 'Judul buku maksimal 100 karakter.',
            'isbn.size' => 'ISBN harus tepat 13 digit.',
            'isbn.regex' => 'ISBN hanya boleh berisi 13 angka.',
            'tahun_terbit.between' => 'Tahun terbit harus antara 2000 dan 2026.',
            'kategori_buku_id.exists' => 'Kategori yang dipilih tidak tersedia.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        try {
            $pdo = DB::connection()->getPdo();

            $cekBuku = $pdo->prepare(
                'SELECT COUNT(*) FROM buku WHERE id = :id'
            );

            $cekBuku->execute(['id' => $buku]);

            if ((int) $cekBuku->fetchColumn() === 0) {
                return redirect()->route('buku.index')->with(
                    'gagal',
                    'Data buku tidak ditemukan.'
                );
            }

            $cek = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM buku
                 WHERE (kode_buku = :kode OR isbn = :isbn)
                   AND id <> :id'
            );

            $cek->execute([
                'kode' => $data['kode_buku'],
                'isbn' => $data['isbn'],
                'id' => $buku,
            ]);

            if ((int) $cek->fetchColumn() > 0) {
                return back()->withInput()->with(
                    'gagal',
                    'Kode buku atau ISBN sudah digunakan buku lain.'
                );
            }

            $ubah = $pdo->prepare(
                'UPDATE buku
                 SET kode_buku = :kode,
                     judul = :judul,
                     isbn = :isbn,
                     tahun_terbit = :tahun,
                     kategori_buku_id = :kategori,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );

            $ubah->execute([
                'kode' => $data['kode_buku'],
                'judul' => $data['judul'],
                'isbn' => $data['isbn'],
                'tahun' => $data['tahun_terbit'],
                'kategori' => $data['kategori_buku_id'],
                'id' => $buku,
            ]);

            return redirect()->route('buku.index')->with(
                'berhasil',
                'Data buku berhasil diperbarui.'
            );
        } catch (Throwable $error) {
            report($error);

            return back()->withInput()->with(
                'gagal',
                'Data buku gagal diperbarui.'
            );
        }
    }

    /**
     * Menghapus buku.
     */
    public function destroy(string $buku)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $hapus = $pdo->prepare(
                'DELETE FROM buku WHERE id = :id'
            );

            $hapus->execute(['id' => $buku]);

            if ($hapus->rowCount() === 0) {
                return redirect()->route('buku.index')->with(
                    'gagal',
                    'Data buku tidak ditemukan.'
                );
            }

            return redirect()->route('buku.index')->with(
                'berhasil',
                'Data buku berhasil dihapus.'
            );
        } catch (Throwable $error) {
            report($error);

            return redirect()->route('buku.index')->with(
                'gagal',
                'Data buku gagal dihapus. Periksa koneksi database.'
            );
        }
    }
}
