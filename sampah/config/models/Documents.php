<?php
    namespace App\Models;

    use Config\Core\Database;
    use Config\Core\SystemInfo;
    use Exception;

    class Documents {
        public static array $ALL_DOCS = [
            "profile-perusahaan"          => "Profile Perusahaan Pialang Berjangka",
            "pernyataan-simulasi"         => "Pernyataan Telah Melakukan Simulasi Perdagangan",
            "pernyataan-pengalaman"       => "Pernyataan Telah Berpenglaman Melaksanakan Transaksi Perdagangan",
            "aplikasi-pembukaan-rekening" => "Aplikasi Pembukaan Rekening Transaksi Secara On-line",
            "pemberitahuan-adanya-risiko" => "Dokumen Pemberitahuan Adanya Resiko",
            "perjanjian-pemberian-amanat" => "Perjanjian Pemberian Amanat",
            "trading-rules"               => "Trading Rules",
            "personal-access-password"    => "Personal Access Password",
            "pernyataan-dana-nasabah"     => "Pernyataan Dana Nasabah",
            "surat-pernyataan"            => "Surat Pernyataan Nasabah",
            "kelengkapan-formulir"        => "Formulir Verifikasi Kelengkapan"
        ];
    }