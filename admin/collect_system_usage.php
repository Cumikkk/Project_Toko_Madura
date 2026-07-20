<?php

date_default_timezone_set("Asia/Jakarta");
// Path ke file log
$log_file = '/home/ragondav/public_html/admin-ragondav.techcrm.net/system_usage_log.txt';

// Mendapatkan timestamp saat ini
$timestamp = date('Y-m-d H:i:s');

// Mendapatkan penggunaan CPU dalam persentase
$cpu_usage_output = shell_exec("top -bn1 | grep 'Cpu(s)'");
preg_match('/(\d+\.\d+)\s*id/', $cpu_usage_output, $matches);
$cpu_usage = round(100 - floatval($matches[1]), 2);

// Mendapatkan total dan penggunaan RAM dalam MB
$free_output = shell_exec('free -m');
preg_match_all('/\d+/', $free_output, $matches);
$total_ram = $matches[0][0];
$used_ram = $matches[0][2];
$ram_usage = round(($used_ram / $total_ram) * 100, 2);

// Mendapatkan total dan penggunaan storage dalam MB
$df_output = shell_exec('df -BM --total | grep total');
preg_match_all('/\d+/', $df_output, $matches);
$total_storage = $matches[0][0];
$used_storage = $matches[0][1];
$storage_usage = round(($used_storage / $total_storage) * 100, 2);

// Menambahkan data ke file log
$log_entry = "$timestamp,$cpu_usage,$ram_usage,$storage_usage\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);

?>
