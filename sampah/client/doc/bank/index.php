<?php $_SESSION['modal'] = ['update-bank', 'otp-bank']; 

use App\Models\MemberBank;
use App\Models\FileUpload;
use App\Factory\FileUploadFactory;?>
<div class="dashboard-breadcrumb mb-25">
    <div class="d-flex align-items-center">
        <h2 class="mb-0">Daftar Bank</h2>
    </div>
</div>

<div class="row">
    <?php require_once __DIR__ . "/create.php"; ?>

    <div class="col-md-8 mb-3">
        <div class="panel">
            <div class="panel-body">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table-bank">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-center">Tanggal Dibuat</th>
                                        <th class="text-center">Rekening</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach(App\Models\User::myBank($user['MBR_ID']) as $bank) : ?>
                                        <tr>
                                            <td><?= date('Y-m-d H:i:s', strtotime($bank['MBANK_DATETIME'])); ?></td>
                                            <td class="text-start">
                                                <p class="mb-0"><?= $bank['MBANK_NAME'] ?></p>
                                                <p class="mb-0"><?= $bank['MBANK_HOLDER'] ?> /
                                                    <span class="rekening" data-real="<?= htmlspecialchars($bank['MBANK_ACCOUNT'], ENT_QUOTES) ?>"><?= str_repeat('*', strlen($bank['MBANK_ACCOUNT'])) ?></span>
                                                    <button type="button" class="toggle-mask btn btn-sm <?= ($user['MBR_THEME'] == '1') ? 'btn-outline-light' : ''; ?>" style="border: 0px;"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                                                </p>
                                            </td>
                                            <td>
                                                <?= MemberBank::status($bank['MBANK_STS'])['html']; ?>
                                                <?php if($bank['MBANK_STS'] == MemberBank::$statusRejected) : ?>
                                                    <p class="mb-0">Note: </p>
                                                    <p class="mb-0"><?= htmlspecialchars($bank['MBANK_REJECT_NOTE'], ENT_QUOTES) ?></p>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0)" class="btn btn-success btn-sm text-white" data-bs-toggle="modal" data-bs-target="#modal-edit-bank" data-nama="<?= $bank['MBANK_NAME'] ?>" data-rekening="<?= $bank['MBANK_ACCOUNT'] ?>" data-image="<?= FileUploadFactory::aws()->awsFile($bank['MBANK_IMG']); ?>" data-id="<?= md5(md5($bank['ID_MBANK'])) ?>"><i class="fas fa-edit"></i></a>
                                                <a href="javascript:void(0)" class="btn btn-sm btn-info btnSetDefault" data-code="<?= md5(md5($bank['ID_MBANK'])) ?>"><i class="fas fa-lock text-white"></i></a>
                                                <?php if($bank['MBANK_STS'] != MemberBank::$statusAccepted) : ?>
                                                    <a href="javascript:void(0)" class="btn btn-danger btn-sm text-white btndlt" data-nama="<?= $bank['MBANK_NAME'] ?>" data-rekening="<?= $bank['MBANK_ACCOUNT'] ?>" data-id="<?= md5(md5($bank['ID_MBANK'])) ?>"><i class="fas fa-trash"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#table-bank').DataTable({
            scrollX: true,
            processing: true,
            order: [[0, 'desc']],
        });

        $('#table-bank tbody').on('click', '.toggle-mask', function(e){
            e.preventDefault();
            var $btn = $(this);
            var $span = $btn.siblings('.rekening');
            var real = $span.data('real') ? String($span.data('real')) : '';
            var $icon = $btn.find('i');
            // If currently eye (meaning masked, user wants to show)
            if($icon.hasClass('fa-eye')){
                $span.text(real);
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
                $btn.attr('title','Hide');
            } else {
                $span.text('*'.repeat(real.length));
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
                $btn.attr('title','Show');
            }
        });

        $('.btnSetDefault').on('click', function(e){
            let data = $(e.currentTarget).data();
            Swal.fire({
                title: "Konfirmasi",
                text: "Apakah anda yakin ingin mengatur bank ini sebagai bank utama?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, yakin",
                cancelButtonText: "Batal",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with setting default bank
                    $.post("/ajax/post/profile/set-default-bank", data, function(resp) {
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                location.reload();
                            }
                        })
                    }, 'json');
                }
            });
        });

        $('.btndlt').on('click', function(e){
            Swal.fire({
                title: "Konfirmasi",
                text: `Apakah anda yakin menghapus bank ini dengan nama bank/akun sebgai berikut : (${$(this).data('nama')}/${$(this).data('rekening')}) ?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, yakin",
                cancelButtonText: "Batal",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let DataSent = new FormData();
                    DataSent.append('x', $(this).data('id'));
                    Swal.fire({
                        text: "Please wait...",
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                    // Proceed with setting default bank
                    $.ajax({
                        url: "/ajax/post/profile/delete-bank",
                        type: 'post',
                        dataType: "json",
                        data: DataSent,
                        contentType: false,
                        processData: false,
                        cache: false,
                    }).done((resp) => {
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                if(resp?.data?.reloc?.length){
                                    location.href = resp?.data?.reloc;
                                }else{ location.reload(); }
                            }
                        });

                    });
                }
            });
        });

    })
</script>