
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Buku</title>
</head>
<body>

    <h1>Daftar Buku</h1>

    <p>
        Masuk sebagai {{ auth()->user()->name }}
        ({{ auth()->user()->role }})
    </p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Keluar</button>
    </form>

    @can('kelola-buku')
        <p>
            <a href="{{ route('buku.create') }}">+ Tambah Buku</a>
        </p>
    @endcan

    @if (session('status'))
        <p style="color: green;">
            {{ session('status') }}
        </p>
    @endif

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Judul Buku</th>
                <th>Kategori</th>

                @can('kelola-buku')
                    <th>Aksi</th>
                @endcan
            </tr>
        </thead>

        <tbody>
            @forelse ($bukus as $buku)
                <tr>
                    <td>{{ $buku->kode }}</td>
                    <td>{{ $buku->judul }}</td>
                    <td>{{ $buku->kategori->nama }}</td>

                    @can('kelola-buku')
                        <td>
                            <a href="{{ route('buku.edit', $buku) }}">
                                Ubah
                            </a>

                            <form
                                action="{{ route('buku.destroy', $buku) }}"
                                method="POST"
                                style="display: inline;"
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
                    @endcan
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada data buku.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
