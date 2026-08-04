<?php
use Config\Core\SystemInfo;
header("Location: " . SystemInfo::app('ADMIN_URL') . "/investor/view");
exit;
?>
