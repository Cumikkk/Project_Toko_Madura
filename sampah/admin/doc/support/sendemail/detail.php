<?php
/**
 * Email Detail View
 */

use App\Models\Helper;

$id = Helper::form_input($_GET['id'] ?? 0);

if(empty($id)) {
    echo "<script>window.close();</script>";
    exit;
}

$sql = $db->query("
    SELECT 
        eh.*,
        a.ADM_NAME as sender_name
    FROM tb_log_sendemail eh
    LEFT JOIN tb_admin a ON (a.ADM_ID = eh.REQUEST_BY)
    WHERE MD5(MD5(eh.ID_SENDEMAIL)) = '$id'
    LIMIT 1
");

if($sql->num_rows == 0) {
    echo "<script>alert('Email not found'); window.close();</script>";
    exit;
}

$email = $sql->fetch_assoc();
$attachments = !empty($email['ATTACHMENT']) ? json_decode($email['ATTACHMENT'], true) : [];
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Email Detail</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/support/sendemail/view">Send Email</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </div>
    <!-- <div class="d-flex">
        <button class="btn btn-secondary" onclick="window.print()">
            <i class="fe fe-printer"></i> Print
        </button>
        <button class="btn btn-light ms-2" onclick="window.close()">
            <i class="fe fe-x"></i> Close
        </button>
    </div> -->
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title mb-0">Email Information</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Recipient Email</th>
                                <td><?= htmlspecialchars($email['RECIPIENT']) ?></td>
                            </tr>
                            <tr>
                                <th>Subject</th>
                                <td><?= htmlspecialchars($email['SUBJECT']) ?></td>
                            </tr>
                            <tr>
                                <th>Sent By</th>
                                <td><?= htmlspecialchars($email['sender_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Sent Date</th>
                                <td><?= date('d/m/Y H:i:s', strtotime($email['DATETIME'])) ?></td>
                            </tr>
                            <?php if(!empty($attachments)): ?>
                            <tr>
                                <th>Attachments</th>
                                <td>
                                    <ul class="mb-0">
                                        <?php foreach($attachments as $attachment): ?>
                                        <li>
                                            <?php if($attachment['link']) : ?>
                                                <a href="<?= $attachment['link'] ?>" target="_blank">
                                                    <i class="fe fe-paperclip"></i> <?= htmlspecialchars($attachment['original_name']) ?>
                                                </a>
                                            <?php else : ?>
                                                <i class="fe fe-paperclip"></i> <?= htmlspecialchars($attachment['original_name']) ?>
                                            <?php endif; ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title">Email Content</h6>
            </div>
            <div class="card-body">
                <div class="email-content-preview" style="padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; min-height: 300px;">
                    <?= base64_decode($email['CONTENT']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .page-header .d-flex,
        .main-header,
        .main-sidebar,
        .breadcrumb {
            display: none !important;
        }
        
        .email-content-preview {
            border: none !important;
            background: white !important;
        }
    }
    
    .email-content-preview {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
    }
    
    .badge {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
</style>
