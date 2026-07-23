<?php
try {
    if (empty($_POST['settings']) || !is_array($_POST['settings'])) {
        JsonResponse([
            'success' => false,
            'alert' => ['title' => 'Gagal', 'text' => 'Data tidak valid', 'icon' => 'error']
        ]);
    }

    foreach ($_POST['settings'] as $key => $value) {
        $keyEscaped = $db->real_escape_string($key);
        $valEscaped = floatval($value);
        $db->query("UPDATE pengaturan_sistem SET nilai = {$valEscaped} WHERE nama_pengaturan = '{$keyEscaped}'");
    }

    JsonResponse([
        'success' => true,
        'message' => 'Pengaturan berhasil diperbarui',
        'alert' => ['title' => 'Sukses', 'text' => 'Pengaturan berhasil diperbarui', 'icon' => 'success']
    ]);
} catch (Exception $e) {
    JsonResponse([
        'success' => false,
        'message' => $e->getMessage(),
        'alert' => ['title' => 'Error', 'text' => $e->getMessage(), 'icon' => 'error']
    ]);
}
