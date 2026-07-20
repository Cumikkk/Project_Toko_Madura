<?php

use App\Models\User;
use App\Models\Ib;

$isAllowToBecomeIb = Ib::isAllowToBecomeIb($user['userid']);
$ibData = User::get_ib_data($user['MBR_ID']);
?>

<div class="row">
    <div class="col-md-12 mb-25">
        <div class="dashboard-breadcrumb mb-25">
            <h2>Become IB</h2>
            <div class="input-group-a dashboard-filter">
            </div>
        </div>
    </div>

    <?php if(is_array($ibData) && $ibData['BECOME_STS'] == -1) : ?>
        <div class="col-md-6 m-auto mb-25">
            <div class="panel">
                <div class="panel-header">
                    <h5>Already Become IB</h5>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td width="40%">Date Request</td>
                                <td width="60%"><?= date("Y-m-d H:i:s", strtotime($ibData['BECOME_DATETIME'])); ?></td>
                            </tr>
                            <tr>
                                <td width="40%">Date Processed</td>
                                <td width="60%"><?= date("Y-m-d H:i:s", strtotime($ibData['BECOME_TIMESTAMP'])); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif(!empty($isAllowToBecomeIb['requirements'])) : ?>
        <?php $havePendingIb = (is_array($ibData)); ?>
        <div class="col-md-6 m-auto mb-25">
            <div class="panel">
                <div class="panel-header">
                    <h5>Terms & Conditions</h5>
                </div>
                <div class="panel-body">
                    <form action="" method="post" id="form-request-ib">
                        <div class="mb-25">
                            <ul>
                                <?php foreach($isAllowToBecomeIb['requirements'] as $require) : ?>
                                    <li>
                                        <?php if($require['status'] || $havePendingIb) : ?>
                                            <i class="fas fa-check text-success me-1"></i>
                                        <?php else : ?> 
                                            <i class="fas fa-close text-danger me-1"></i>
                                        <?php endif; ?>
                                        <?= $require['text'] ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
    
                        <?php if(!empty($ibData) && $ibData['BECOME_STS'] == 1) : ?>
                            <div class="alert alert-danger mb-25">
                                <small>Permintaan anda sebelumnya telah ditolak, keterangan: </small>
                                <br>
                                <small><?= $ibData['BECOME_NOTE'] ?></small>
                            </div>
                        <?php endif; ?>
    
                        
                        <?php if(!$ibData || $ibData['BECOME_STS'] == 1) : ?>
                            <div class="d-flex justify-content-between mb-25">
                                <div class="form-check">
                                    <input class="form-check-input" name="terms" type="checkbox" required>
                                    <label class="form-check-label" for="loginCheckbox">
                                        I agree <a href="#" class="text-decoration-underline">Terms & Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" <?= ($isAllowToBecomeIb['success'])? "" : "disabled" ?>>Request Become IB</button>
                            </div>
    
                        <?php elseif($ibData['BECOME_STS'] == 0) : ?>
                            <div class="text-end">
                                <button type="button" class="btn btn-info" disabled>In review</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#form-request-ib').on('submit', function(event) {
                    event.preventDefault();
        
                    Swal.fire({
                        title: "Are you sure to continue?",
                        icon: 'question',
                        showCancelButton: true,
                        reverseButtons: true
                    }).then((result) => {
                        if(result.value) {
                            Swal.fire({
                                text: "Please wait...",
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            })
        
                            $.post("/ajax/post/ib/become", Object.fromEntries(new FormData(this).entries()) , function(resp) {
                                if(!resp.success) {
                                    Swal.fire("Failed", resp.error, "error");
                                    return false;
                                }
                
                                location.reload();
                            }, 'json')
                        }
                    })
                })
            })
        </script>
    <?php endif; ?>
</div>