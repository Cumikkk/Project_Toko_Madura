<?php 
$accountExclude = App\Models\Account::accountExclude($userData['MBR_ID']);
$mbrSuffix = App\Models\Account::accountInclude($userData['MBR_ID']);
?>
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title align-items-center d-flex gap-2">
                    <i class="fas fa-user-circle"></i> 
                    MetaTrader Account
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped" id="table-account" data-code="<?= $userCode ?>">
                        <thead>
                            <tr class="text-center">
                                <td>Date Reg.</td>
                                <td>Type</td>
                                <td>Login</td>
                                <td>Leverage</td>
                                <td>Type</td>
                                <td>Balance</td>
                                <td>Margin Free</td>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <form action="/ajax/post/member/update/account" method="post" id="form-account-setting">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title align-items-center d-flex gap-2">
                        <i class="fas fa-gear"></i> 
                        Configuration Account Setting
                    </h5>
                </div>
                <div class="card-body">
                    <input type="hidden" name="code" value="<?= $userCode; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_account" class="form-control-label required">Max Number of Real Accounts</label>
                                <input type="number" class="form-control" name="max_account" id="max_account" placeholder="0" value="<?= $userData['MBR_ACCMAX'] ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="allowallreferral" class="form-control-label required">Display All Referral Links</label>
                                <select name="allowallreferral" id="allowallreferral" class="form-control select2">
                                    <option value="-1" <?= $userData['MBR_REFFERALALL'] == -1? "selected" : ""; ?>>Enable</option>
                                    <option value="1" <?= $userData['MBR_REFFERALALL'] == 1? "selected" : ""; ?>>Disable</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="account_include" class="form-control-label required"><span class="text-danger">(Override)</span> Allowed Account Types List</label>
                                <select name="account_include[]" id="account_include" class="form-control select2" multiple>
                                    <?php foreach(App\Models\AccountType::all() as $accountType) : ?>
                                        <?php if($accountType['RTYPE_SUFFIX'] != "000" && $accountType['RTYPE_STS'] == -1) : ?>
                                            <?php $selected = in_array($accountType['RTYPE_SUFFIX'], $mbrSuffix, true); ?>
                                            <option value="<?= $accountType['RTYPE_SUFFIX'] ?>" <?= $selected? "selected" : ""; ?>>
                                                (<?= $accountType['RTYPE_SUFFIX'] ?>) <?= implode("/", [$accountType['RTYPE_TYPE'], ($accountType['RTYPE_ISFLOATING']? "Floating" : ($accountType['RTYPE_RATE'] / 1000)), $accountType['RTYPE_KOMISI']]) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- <div class="col-md-12">
                            <div class="form-group">
                                <label for="account_type" class="form-control-label">List Tipe Akun yang tidak bisa dibuat</label>
                                <select name="account_type[]" id="account_type" class="form-control select2" multiple>
                                    <?php foreach(App\Models\AccountType::all() as $accountType) : ?>
                                        <?php if($accountType['RTYPE_SUFFIX'] != "000" && $accountType['RTYPE_STS'] == -1) : ?>
                                            <?php $selected = in_array($accountType['RTYPE_SUFFIX'], $accountExclude, true); ?>
                                            <option value="<?= $accountType['RTYPE_SUFFIX'] ?>" <?= $selected? "selected" : ""; ?>>
                                                (<?= $accountType['RTYPE_SUFFIX'] ?>) <?= implode("/", [$accountType['RTYPE_TYPE'], ($accountType['RTYPE_ISFLOATING']? "Floating" : ($accountType['RTYPE_RATE'] / 1000)), $accountType['RTYPE_KOMISI']]) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div> -->
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary px-4">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        table = $('#table-account').DataTable( {
            dom: 'Blfrtip',
            processing: true,
            serverSide: true,
            deferRender: true,
			buttons: [
				{
					extend: 'excel',
					text: 'Excel',
				},
				{
					extend: 'copy',
					text: 'Copy'
				},
                {
                    text: 'Refresh',
                    name: 'refresh',
                    action: function (e, dt, node, config) {
                        const btn = dt.button('refresh:name');
                        const $node = $(btn.node());

                        if (!$node.data('original-text')) {
                            $node.data('original-text', $node.html());
                        }

                        btn.enable(false);
                        btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');

                        dt.ajax.reload(null, false);
                    }
                }
			],
            order: [[0, 'desc']],
            lengthMenu: [[10, 50, 100], [10, 50, 100]],
            ajax: {
                url: "/ajax/datatable/member/update/account",
                data: {
                    code: $('#table-account').data('code')
                }
            }
        })

        $('#form-account-setting').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post($(this).attr('action'), $(this).serialize(), (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        })
        try {
            const btnRef = table.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            table.on('processing.dt', function (e, settings, processing) {
                const btn = table.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                if (processing) {
                    btn.enable(false);
                    btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
                } else {
                    btn.enable(true);
                    const original = $node.data('original-text') || originalRefText;
                    btn.text(original);
                }
            });

            table.on('xhr.dt', function () {
                const btn = table.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }
    })
</script>