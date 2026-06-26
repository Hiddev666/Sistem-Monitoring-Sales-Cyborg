# Struktur Database Aplikasi Sistem Monitoring Sales (Vionna)

## absensi
| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | user_id | bigint |
| 3 | tanggal | date |
| 4 | waktu_masuk | time |
| 5 | lat_masuk | decimal |
| 6 | lng_masuk | decimal |
| 7 | accuracy_masuk | decimal |
| 8 | waktu_keluar | time |
| 9 | lat_keluar | decimal |
| 10 | lng_keluar | decimal |
| 11 | accuracy_keluar | decimal |
| 12 | total_jam | int |
| 13 | status | varchar |
| 14 | created_at | timestamp |
| 15 | updated_at | timestamp |
| 16 | deleted_at | timestamp |

## configurations
| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | key | varchar |
| 3 | value | text |
| 4 | type | varchar |
| 5 | description | text |
| 6 | created_at | timestamp |
| 7 | updated_at | timestamp |

## jadwal_klien
| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | jadwal_kunjungan_id | bigint |
| 3 | klien_id | bigint |
| 4 | urutan | int |
| 5 | status | varchar |
| 6 | waktu_checkin | time |
| 7 | waktu_checkout | time |
| 8 | lat_checkin | decimal |
| 9 | foto_checkin | varchar |
| 10 | lng_checkin | decimal |
| 11 | accuracy_checkin | decimal |
| 12 | foto_checkout | varchar |
| 13 | catatan_kunjungan | text |
| 14 | tanda_tangan | varchar |
| 15 | hasil_tipe | enum |
| 16 | nominal_transaksi | decimal |
| 17 | lat_checkout | decimal |
| 18 | lng_checkout | decimal |
| 19 | accuracy_checkout | decimal |
| 20 | waktu_form_selesai | timestamp |
| 21 | durasi_kunjungan | int |
| 22 | hasil_kunjungan | text |
| 23 | keterangan | text |
| 24 | created_at | timestamp |
| 25 | updated_at | timestamp |
| 26 | deleted_at | timestamp |

## jadwal_kunjungan
| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | user_id | bigint |
| 3 | tanggal | date |
| 4 | keterangan | varchar |
| 5 | status | varchar |
| 6 | created_by | bigint |
| 7 | waktu_mulai | time |
| 8 | waktu_selesai | time |
| 9 | created_at | timestamp |
| 10 | updated_at | timestamp |
| 11 | deleted_at | timestamp |

## klien
| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | nama_klien | varchar |
| 3 | kategori | enum |
| 4 | alamat | text |
| 5 | wilayah_id | bigint |
| 6 | latitude | decimal |
| 7 | longitude | decimal |
| 8 | contact_person | varchar |
| 9 | phone | varchar |
| 10 | is_active | tinyint |
| 11 | created_at | timestamp |
| 12 | updated_at | timestamp |
| 13 | deleted_at | timestamp |

## lokasi_realtime
| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | user_id | bigint |
| 3 | latitude | decimal |
| 4 | longitude | decimal |
| 5 | akurasi_meter | decimal |
| 6 | recorded_at | timestamp |
| 7 | created_at | timestamp |
| 8 | updated_at | timestamp |

## model_has_permissions
| No | Column | Type |
|---:|---|---|
| 1 | permission_id | bigint |
| 2 | model_type | varchar |
| 3 | model_id | bigint |

## model_has_roles

| No | Column | Type |
|---:|---|---|
| 1 | role_id | bigint |
| 2 | model_type | varchar |
| 3 | model_id | bigint |

## permissions

| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | name | varchar |
| 3 | guard_name | varchar |
| 4 | description | text |
| 5 | created_at | timestamp |
| 6 | updated_at | timestamp |

## role_has_permissions

| No | Column | Type |
|---:|---|---|
| 1 | permission_id | bigint |
| 2 | role_id | bigint |

## roles

| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | name | varchar |
| 3 | guard_name | varchar |
| 4 | description | text |
| 5 | created_at | timestamp |
| 6 | updated_at | timestamp |

## users

| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | name | varchar |
| 3 | email | varchar |
| 4 | email_verified_at | timestamp |
| 5 | password | varchar |
| 6 | phone | varchar |
| 7 | photo | varchar |
| 8 | remember_token | varchar |
| 9 | created_at | timestamp |
| 10 | updated_at | timestamp |
| 11 | deleted_at | timestamp |
| 12 | wilayah_id | bigint |
| 13 | is_active | tinyint |

## wilayah
| No | Column | Type |
|---:|---|---|
| 1 | id | bigint |
| 2 | nama_wilayah | varchar |
| 3 | keterangan | text |
| 4 | created_at | timestamp |
| 5 | updated_at | timestamp |

