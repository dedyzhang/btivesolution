<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email penerima saran & masukan (vendor / BTIVE)
    |--------------------------------------------------------------------------
    |
    | Semua instance sekolah sebaiknya memakai alamat yang sama agar masukan
    | terkumpul di satu inbox. Pisahkan beberapa penerima dengan koma.
    |
    | Ops per sekolah:
    | - BTIVE_FEEDBACK_EMAIL = email pusat (sama di semua deploy)
    | - APP_URL = domain instance sekolah ini (link "Buka Detail" di email)
    | - Pengaturan → nama_sekolah terisi (muncul di subject/body email)
    |
    */
    'development_email' => env('BTIVE_FEEDBACK_EMAIL', 'btivesolution@gmail.com'),

];
