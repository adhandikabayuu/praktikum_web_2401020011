
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku Perpustakaan</title>
</head>
<body>
    <h1>Data Buku Perpustakaan</h1>

    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    <p>
        <a href="{{ route('buku.create') }}">Tambah Buku</a>
    </p>

    <form action="{{ route('buku.index') }}" method="GET">
        <label for="q">Cari kode, judul, atau ISBN:</label>
        <input
            type="text"
            id="q"
            name="q"
            value="{{ $kataKunci }}"
        >

        <label for="kategori_buku_id">Kategori:</label>
        <select id="kategori_buku_id" name="kategori_buku_id">
            <option value="">Semua Kategori</option>
            @foreach ($daftarKategori as $kategori)
                <option
                    value="{{ $kategori['id'] }}"
                    {{ (string) $kategoriId === (string) $kategori['id'] ? 'selected' : '' }}
                >
                    {{ $kategori['nama_kategori'] }}
                </option>
            @endforeach
        </select>

        <button type="submit">Cari / Filter</button>
        <a href="{{ route('buku.index') }}">Reset</a>
    </form>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Buku</th>
                <th>Judul</th>
                <th>ISBN</th>
                <th>Tahun Terbit</th>
                <th>Kategori</th>
                <th>Status Tahun</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarBuku as $index => $buku)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $buku['kode_buku'] }}</td>
                    <td>{{ $buku['judul'] }}</td>
                    <td>{{ $buku['isbn'] }}</td>
                    <td>{{ $buku['tahun_terbit'] }}</td>
                    <td>{{ $buku['nama_kategori'] }}</td>
                    <td>
                        {{ (int) $buku['tahun_terbit'] >= 2023 ? 'Baru' : 'Lama' }}
                    </td>
                    <td>
                        <a href="{{ route('buku.edit', $buku['id']) }}">Edit</a>

                        <form
                            action="{{ route('buku.destroy', $buku['id']) }}"
                            method="POST"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                onclick="return confirm('Yakin ingin menghapus buku ini?')"
                            >
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Data buku tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
