<?php
use App\Models\Helper;

$data = Helper::getSafeInput($_POST);
$theme = $data['theme'];

$_SESSION['MBR_THEME'] = $theme;

JsonResponse([
    'success' => true,
    'message' => "Theme updated successfully",
    'data' => []
]);
