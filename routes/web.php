<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::get('/form-mahasiswa', function () {
    return view('form-mahasiswa');
});

Route::post('/form-mahasiswa', function (Request $request) {

    // Sanitasi data sebelum validasi
    $dataBersih = [
        'nama' => strip_tags(trim((string) $request->input('nama'))),

        'email' => filter_var(
            (string) $request->input('email'),
            FILTER_SANITIZE_EMAIL
        ),

        'usia' => trim((string) $request->input('usia')),

        // Spasi awal dan akhir dibersihkan sebelum validasi
        'nim' => trim((string) $request->input('nim')),
    ];

    // Validasi server-side
    $validator = Validator::make(
        $dataBersih,
        [
            'nama' => ['required', 'min:3', 'max:50'],
            'email' => ['required', 'email'],
            'usia' => ['required', 'integer', 'min:17', 'max:60'],
            'nim' => ['required', 'digits_between:8,12'],
        ],
        [
            'nama.required' => 'Nama wajib diisi.',
            'nama.min' => 'Nama minimal 3 karakter.',
            'nama.max' => 'Nama maksimal 50 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',

            'usia.required' => 'Usia wajib diisi.',
            'usia.integer' => 'Usia harus berupa angka.',
            'usia.min' => 'Usia minimal 17 tahun.',
            'usia.max' => 'Usia maksimal 60 tahun.',

            'nim.required' => 'NIM wajib diisi.',
            'nim.digits_between' => 'NIM harus terdiri dari 8–12 digit.',
        ]
    );

    // Jika validasi gagal
    if ($validator->fails()) {
        return redirect('/form-mahasiswa')
            ->withErrors($validator)
            ->withInput();
    }

    // Mengambil hanya data yang sudah lolos validasi
    $data = $validator->validated();

    // Mengubah usia menjadi integer
    $data['usia'] = (int) $data['usia'];

    return view('hasil-form', [
        'data' => $data
    ]);
});