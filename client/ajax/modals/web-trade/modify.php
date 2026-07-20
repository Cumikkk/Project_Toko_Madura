<?php

use App\Models\Helper;

$ticket = Helper::stringTonumber($_GET['ticket']) ?? null;
$sl = Helper::stringTonumber($_GET['sl']) ?? 0;
$tp = Helper::stringTonumber($_GET['tp']) ?? 0;
?>

<form action="" method="post" id="modify-order-form">
    <div class="row">
        <input type="hidden" name="ticket" value="<?= $ticket ?>">
        <div class="col-12">
            <label for="volume" class="form-label">Volume</label>
            <input type="number" name="volume" class="form-control" value="0" step="0.01" disabled>
        </div>
        <div class="col-12">
            <label for="price" class="form-label">Price</label>
            <input type="number" name="price" class="form-control" value="0" step="0.01" disabled>
        </div>
        <div class="col-6">
            <label for="sl" class="form-label">SL</label>
            <input type="number" name="sl" class="form-control" value="<?= $sl ?>" min="0" step="0.01" required>
        </div>
        <div class="col-6">
            <label for="tp" class="form-label">TP</label>
            <input type="number" name="tp" class="form-control" value="<?= $tp ?>" min="0" step="0.01" required>
        </div>
        <div class="col-6">
            <a href="javascript:void(0)" id="modify-order-action" class="btn btn-block btn-info w-100 mb-3"><i class="fas fa-edit"></i> Modify</a>
        </div>
        <div class="col-6">
            <a href="javascript:void(0)" id="close-order" class="btn btn-block btn-danger w-100 mb-3"><i class="fas fa-times"></i> Close</a>
        </div>
    </div>
</form>