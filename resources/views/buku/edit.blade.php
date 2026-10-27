
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku</title>
</head>
<body>
    <h1>Edit Data Buku</h1>

    @if ($errors->any())
        <div>
            <p>Data belum valid. Periksa kembali:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    <form action="{{ route('buku.update', $dataBuku['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label for="kode_buku">Kode Buku (10 karakter):</label><br>
            <input
                type="text"
                id="kode_buku"
                name="kode_buku"
                value="{{ old('kode_buku', $dataBuku['kode_buku']) }}"
                maxlength="10"
                required
            >
        </p>

        <p>
            <label for="judul">Judul Buku (3–100 karakter):</label><br>
            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul', $dataBuku['judul']) }}"
                minlength="3"
                maxlength="100"
                required
            >
        </p>

        <p>
            <label for="isbn">ISBN (13 digit):</label><br>
            <input
                type="text"
                id="isbn"
                name="isbn"
                value="{{ old('isbn', $dataBuku['isbn']) }}"
                minlength="13"
                maxlength="13"
                required
            >
        </p>

        <p>
            <label for="tahun_terbit">Tahun Terbit (2000–2026):</label><br>
            <input
                type="number"
                id="tahun_terbit"
                name="tahun_terbit"
                value="{{ old('tahun_terbit', $dataBuku['tahun_terbit']) }}"
                min="2000"
                max="2026"
                required
            >
        </p>

        <p>
            <label for="kategori_buku_id">Kategori Buku:</label><br>
            <select id="kategori_buku_id" name="kategori_buku_id" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach ($daftarKategori as $kategori)
                    <option
                        value="{{ $kategori['id'] }}"
                        {{ (string) old('kategori_buku_id', $dataBuku['kategori_buku_id']) === (string) $kategori['id'] ? 'selected' : '' }}
                    >
                        {{ $kategori['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('buku.index') }}">Batal</a>
    </form>
</body>
</html>
