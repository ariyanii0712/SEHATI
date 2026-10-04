-- ============================================================
-- DUMMY DATA: 5 Anggota dengan Peminjaman Buku Fisik Terlambat
-- Tanggal insert: 2026-06-20
-- Jalankan di database: library
-- ============================================================

-- ------------------------------------------------------------
-- STEP 1: Insert 5 Member baru
-- (Gunakan student_id yang unik agar tidak bentrok)
-- ------------------------------------------------------------

INSERT INTO members (student_id, nama, phone, email) VALUES
('2021001001', 'Andi Firmansyah',    '081234567801', 'andi.firmansyah@student.ac.id'),
('2021002002', 'Dewi Rahmawati',     '081234567802', 'dewi.rahmawati@student.ac.id'),
('2022003003', 'Rizky Pratama',      '081234567803', 'rizky.pratama@student.ac.id'),
('2022004004', 'Siti Nuraini',       '081234567804', 'siti.nuraini@student.ac.id'),
('2023005005', 'Bagas Wicaksono',    '081234567805', 'bagas.wicaksono@student.ac.id');

-- ------------------------------------------------------------
-- STEP 2: Insert peminjaman buku fisik dengan due_date lampau
-- Status 'borrowed' + due_date < CURDATE() = OVERDUE
--
-- Catatan: book_id harus merujuk ke buku fisik (media_type='physical')
--          yang ada di database Anda. Sesuaikan jika perlu.
-- ------------------------------------------------------------

INSERT INTO borrowings (member_id, book_id, borrow_date, due_date, status)
SELECT
    m.id          AS member_id,
    b.book_id     AS book_id,
    b.borrow_date AS borrow_date,
    b.due_date    AS due_date,
    'borrowed'    AS status
FROM
    -- Ambil ID member yang baru diinsert
    (
        SELECT id, student_id FROM members
        WHERE student_id IN (
            '2021001001','2021002002','2022003003','2022004004','2023005005'
        )
    ) AS m

    -- Pasangkan dengan data pinjaman per member
    JOIN (
        SELECT '2021001001' AS sid, book_id, '2026-05-01' AS borrow_date, '2026-05-14' AS due_date
        FROM books WHERE media_type = 'physical' LIMIT 1

        UNION ALL

        SELECT '2021002002', book_id, '2026-05-10', '2026-05-24'
        FROM books WHERE media_type = 'physical' LIMIT 1 OFFSET 1

        UNION ALL

        SELECT '2022003003', book_id, '2026-05-15', '2026-05-29'
        FROM books WHERE media_type = 'physical' LIMIT 1 OFFSET 2

        UNION ALL

        SELECT '2022004004', book_id, '2026-05-20', '2026-06-03'
        FROM books WHERE media_type = 'physical' LIMIT 1 OFFSET 3

        UNION ALL

        SELECT '2023005005', book_id, '2026-06-01', '2026-06-14'
        FROM books WHERE media_type = 'physical' LIMIT 1 OFFSET 4

    ) AS b ON m.student_id = b.sid;

-- ------------------------------------------------------------
-- STEP 3: Kurangi stock_available buku yang dipinjam
-- (Agar konsisten dengan logika aplikasi)
-- ------------------------------------------------------------

UPDATE books
SET stock_available = GREATEST(0, stock_available - 1)
WHERE id IN (
    SELECT DISTINCT book_id
    FROM borrowings
    WHERE member_id IN (
        SELECT id FROM members
        WHERE student_id IN (
            '2021001001','2021002002','2022003003','2022004004','2023005005'
        )
    )
    AND status = 'borrowed'
);

-- ------------------------------------------------------------
-- Verifikasi: Lihat hasil overdue
-- ------------------------------------------------------------
SELECT
    members.nama,
    members.student_id,
    books.judul,
    books.media_type,
    borrowings.borrow_date,
    borrowings.due_date,
    DATEDIFF(CURDATE(), borrowings.due_date) AS overdue_days,
    DATEDIFF(CURDATE(), borrowings.due_date) * 2000 AS denda_rp
FROM borrowings
INNER JOIN members ON members.id = borrowings.member_id
INNER JOIN books   ON books.id   = borrowings.book_id
WHERE
    borrowings.status   = 'borrowed'
AND borrowings.due_date < CURDATE()
AND members.student_id IN (
    '2021001001','2021002002','2022003003','2022004004','2023005005'
)
ORDER BY overdue_days DESC;
