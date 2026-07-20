<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $filePermission['desc'] ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Transaction</li>
            <li class="breadcrumb-item"><a href="/transaction/deposit/view">Deposit</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary">
            </div>
            <div class="card-body">
                <h5 class="card-title">Deal Type</h5>
                <p class="mb-0 fw-bold">Auto</p>
                <p>Deal Type <i>Auto</i> meaning the system will automatically make a deposit to MetaTrader and generate and save a Deal ID without requiring manual input from the user.</p>
    
                <p class="mb-0 fw-bold">Manual</p>
                <p>Deal Type <i>Manual</i> means the system does not make deposits to MetaTrader. Users are required to fill in the Deal ID manually, and the system will save the Deal ID according to the value entered in the Deal ID column.</p>
            </div>
        </div>
    </div>
    <div class="col-md-8 mb-3">
        <form action="" method="post" id="form-manual-deposit">
            <input type="hidden" name="key" value="<?= sprintf("%s_%s", "deposit", uniqid()) ?>">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-primary">Form Manual Deposit</h5>
                </div>        
                <div class="card-body">
                    <h5>Select Account</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email" class="form-label required">Email</label>
                                <select name="email" id="email" class="form-control select2" required>
                                    <option value="">Select</option>
                                    <?php $sqlGetUser = $db->query("SELECT MBR_EMAIL FROM tb_member WHERE MBR_STS = -1 AND MBR_ID != 1000000000"); ?>
                                    <?php if($sqlGetUser && $sqlGetUser->num_rows != 0) : ?>
                                        <?php foreach($sqlGetUser->fetch_all(MYSQLI_ASSOC) as $row) : ?>
                                            <option value="<?= $row['MBR_EMAIL'] ?>"><?= $row['MBR_EMAIL'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account" class="form-label required">Account</label>
                                <select name="account" id="account" class="form-control select2 required" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
    
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Rate</label>
                                <input type="text" id="rate" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
    
                    <hr>
                    <h5>Transaction Detail</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="bank-source" class="form-label required">Bank Source</label>
                                <select name="bank-source" id="bank-source" class="form-control select2" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="bank-receiver" class="form-label required">Bank Receiver</label>
                                <select name="bank-receiver" id="bank-receiver" class="form-control select2" required>
                                    <option value="">Select</option>
                                    <?php $sqlGetBank = $db->query("SELECT * FROM tb_bankadm"); ?>
                                    <?php if($sqlGetBank) : ?>
                                        <?php foreach($sqlGetBank->fetch_all(MYSQLI_ASSOC) as $row) : ?>
                                            <option value="<?= md5(md5($row['ID_BKADM'])) ?>"><?= implode(" / ", [$row['BKADM_CURR'], $row['BKADM_ACCOUNT'], $row['BKADM_HOLDER'], $row['BKADM_NAME']]) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="" class="form-label required">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="acount-currency">IDR</span>
                                    <input type="text" class="form-control amount-formatter" name="amount" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="" class="form-label">Note</label>
                                <input type="text" name="note" class="form-control" placeholder="Note">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="" class="form-label">Datetime</label>
                                <div class="input-group">
                                    <input type="date" name="date" class="form-control" value="<?= date("Y-m-d") ?>">
                                    <input type="time" class="input-group-text" name="time" value="<?= date("H:i:s") ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deal_type" class="form-label required">Deal Type</label>
                                <select name="deal_type" id="deal_type" class="form-control">
                                    <option value="manual">Manual</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deal_id" class="form-label">Deal Number</label>
                                <input type="number" name="deal_id" id="deal_id" class="form-control" placeholder="0" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="image" class="form-label">Transfer Proof</label>
                                <input type="file" name="image" id="image" class="dropify" accept="image/jpg, image/jpeg, iamge/png" data-allowed-file-extensions="png jpg jpeg">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#email').on('change', (e) => fetchInfo($('#email').val()));

        $('#account').on('change', (e) => {
            $('#acount-currency').text( $('#account option:selected').data('currency') || '' )
            $('#rate').val( $('#account option:selected').data('rate') || '' )
        })

        $('#deal_type').on('change', function() {
            if($(this).val() == "auto") {
                $('#deal_id').attr('readonly', 'true').val('')
                $('label[for="deal_id"]').removeClass('required')
            
            } else {
                $('#deal_id').removeAttr('readonly').val('')
                $('label[for="deal_id"]').addClass('required')
            }
        })

        $('#form-manual-deposit').on('submit', function(event) {
            event.preventDefault();
            let button = $(this).find('button[type="submit"]');
            // button.addClass('loading');

            Swal.fire({
                text: "Processing...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $.ajax({
                url: "/ajax/post/transaction/deposit/create",
                type: "post",
                dataType: "json",
                data: new FormData(this),
                contentType: false,
                processData: false,
                cache: false
            }).done((resp) => {
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.href = '/transaction/deposit/view';
                    }
                })

            }).catch((error) => {
                Swal.fire("Error", "Internal Server Error", "error");
            }).always(() => {
               button.removeClass('loading') 
            })
        })
    })

    function fetchInfo(email) {
        Swal.fire({
            text: "Fetch Account, please wait...",
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
            }
        })

        $.get(`/ajax/post/transaction/fetch_account?email=${email}`, (resp) => {
            if(!resp.success) {
                Swal.fire(resp.alert);
                return;
            }

            /** Accounts */
            $('#account').empty().append(new Option('Select', '', false, false));
            resp.data.accounts.forEach((val, key) => {
                let newOption = new Option(`${val.login} - $${val.free_margin}`, val.login, false, false);
                newOption.setAttribute('data-currency', val.currency);
                newOption.setAttribute('data-rate', val.rate);
                $('#account').append(newOption);
            })

            /** banks */
            $('#bank-source').empty().append(new Option('Select', '', false, false));
            resp.data.banks.forEach((val) => {
                let newOption = new Option(`${val.account} / ${val.holder} / ${val.provider}`, val.id, false, false);
                $('#bank-source').append(newOption)
            })

        }, 'json')
        .done((resp) => {
            $('#rate').val('')
            if(resp.success) {
                Swal.close();
            }
        })
        .fail((error) => {
            Swal.fire("Error", "Failed to fetch account", "error");
        })
    }
</script>