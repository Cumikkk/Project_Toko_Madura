<?php
require_once __DIR__ . "/../config/setting.php";

$secret = $_ENV['GITHUB_WEBHOOK_SECRET'] ?? 'your_default_secret';
$repositoryName = $_ENV['GITHUB_REPO'] ?? '';
$user = $_ENV['GITHUB_USER_PULL'] ?? '';
$branchName = "staging";
$directory = __DIR__ . "/../";

$script = __DIR__  . "/../config/deployment/pull.sh";

// Baca payload & verify signature (dipendekin)
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
// if (!hash_equals($hash, $signature)) {
//     http_response_code(401);
//     exit('Invalid signature');
// }

$data = json_decode($payload, true);
$fullRepo = $data['repository']['full_name'] ?? '';
$ref = $data['ref'] ?? '';
$branch = basename($ref);

/** Validasi nama Repository */
if ($fullRepo !== $repositoryName) {
    http_response_code(200);
    echo "Invalid Repository: {$fullRepo}";
    exit;
}

/** Validasi Nama Branch */
if ($branch !== $branchName) {
    http_response_code(200);
    echo "Branch {$branch} ignored";
    exit;
}

/** validasi nama user */
if(empty($user)) {
    http_response_code(400);
    echo "User for auto-pull not configured.";
    exit;
}

// Jalankan script deploy sebagai user yang tepat
$cmd = implode(" ", [
    "sudo -u",
    escapeshellarg($user),
    escapeshellarg($script),
    escapeshellarg($user),
    escapeshellarg($repositoryName),
    escapeshellarg($branchName),
    escapeshellarg($directory),
]);

$outputLines = [];
$exitCode = 0;
exec($cmd . ' 2>&1', $outputLines, $exitCode);

if ($exitCode !== 0) {
    http_response_code(400);
    echo "Deployment failed:\n";
    echo implode("\n", $outputLines);
    exit;
}

echo "OK\n";