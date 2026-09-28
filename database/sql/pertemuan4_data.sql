USE praktikum_web_2401020011;

INSERT INTO program_studi (nama_prodi) VALUES
    ('Teknik Informatika'),
    ('Sistem Informasi');

INSERT INTO mahasiswa
    (nim, nama, email, usia, program_studi_id)
VALUES
    ('2401020011', 'Bayu Adhandika',
     'bayu@example.com', 21, 1),

    ('2401020012', 'Ahmad Fauzan',
     'ahmad@example.com', 20, 1),

    ('2401020013', 'Rizky Pratama',
     'rizky@example.com', 21, 2),

    ('2401020099', 'Data Sementara',
     'sementara@example.com', 19, 2);

UPDATE mahasiswa
SET email = 'bayu.adhandika@example.com'
WHERE nim = '2401020011';

DELETE FROM mahasiswa
WHERE nim = '2401020099';

SELECT
    m.nim,
    m.nama,
    m.email,
    m.usia,
    p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
    ON p.id = m.program_studi_id
ORDER BY m.nim;