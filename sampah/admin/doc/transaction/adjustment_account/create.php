<?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
    <a href="javascript:void(0)" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus"></i> Create New</a>

    <div class="modal fade" id="createModal" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Create Adjustment Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form action="/ajax/post<?= $permisCreate['link'] ?>" id="createForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="account" class="form-label">Account Login</label>
                            <select class="form-select select2-modal" id="account" name="account" required>
                                <?php 
                                $metarrfx = Config\Core\SystemInfo::app('DB_METALIVE');
                                $sqlGetAccounts = $db->query("
                                    SELECT 
                                        ACC_LOGIN, 
                                        MBR_NAME, 
                                        MBR_EMAIL 
                                    FROM tb_member 
                                    JOIN (
                                        SELECT 
                                            ACC_MBR,    
                                            GROUP_CONCAT(`login` SEPARATOR ', ') as ACC_LOGIN
                                        FROM tb_racc
                                        JOIN $metarrfx.mt5_users ON `login` = ACC_LOGIN
                                        WHERE ACC_DERE = 1
                                        GROUP BY ACC_MBR 
                                    ) as acc ON MBR_ID = acc.ACC_MBR 
                                "); 
                                ?>
                                <?php if($sqlGetAccounts) : ?>
                                    <?php foreach($sqlGetAccounts->fetch_all(MYSQLI_ASSOC) as $member) : ?>
                                        <optgroup label="<?= $member['MBR_EMAIL'] ?>">
                                            <?php foreach(explode(', ', $member['ACC_LOGIN']) as $account) : ?>
                                                <option value="<?= $account ?>"><?= $account ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php  ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select select2-modal" id="type" name="type" required>
                                <option value="<?= App\Models\AdjustmentAccount::$typeDeposit ?>">deposit</option>
                                <option value="<?= App\Models\AdjustmentAccount::$typeWithdrawal ?>">withdrawal</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control amount-formatter" id="amount" name="amount" placeholder="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment</label>
                            <select name="comment" id="comment" class="form-control">
                                <?php foreach(App\Models\AdjustmentAccount::comment() as $comment) : ?>
                                    <option value="<?= $comment ?>"><?= $comment ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            let modal = $('#createModal');

            modal.on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    dropdownParent: $('#createModal')
                });
            });

            $('#createForm').on('submit', function(e) {
                e.preventDefault();
                let button = $(this).find('button[type="submit"]');
                button.addClass('loading')

                $.post($(this).attr('action'), $(this).serialize(), function(response) {
                    button.removeClass('loading')
                    modal.modal('hide');
                    Swal.fire(response.alert).then(() => {
                        if(response.success) {
                            table_history.ajax.reload();
                        }
                    })
                }, 'json')
            });
        })
    </script>
<?php endif; ?>