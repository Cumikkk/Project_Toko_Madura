<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Create Product</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Tools</li>
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view">Product</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card custom-card">
            <div class="card-body">
                <form action="<?= $filePermission['link'] ?>" method="post" id="form-create-product">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">Product Info</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="suffix" class="form-label required">Suffix</label>
                                        <input type="text" name="suffix" id="suffix" class="form-control" placeholder="Suffix" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="prefix" class="form-label required">Prefix</label>
                                        <input type="number" name="prefix" id="prefix" class="form-control" placeholder="Prefix" value="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label required">Name</label>
                                        <input type="text" name="name" id="name" class="form-control" placeholder="Nama Produk" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type" class="form-label required">Type</label>
                                        <input type="text" name="type" id="type" class="form-control" placeholder="Type" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="rate" class="form-label required">Rate</label>
                                        <select name="rate" id="rate" class="form-control select2">
                                            <?php foreach(App\Models\Product::$rates as $rate) : ?>
                                                <option value="<?= $rate ?>"><?= $rate ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="currency" class="form-label required">Currency</label>
                                        <select name="currency" id="currency" class="form-control select2">
                                            <?php //foreach(App\Models\Product::$currency as $curr) : ?>
                                                <option value="<?//= $curr ?>"><?//= $curr ?></option>
                                            <?php //endforeach; ?>
                                        </select>
                                    </div>
                                </div> -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type_as" class="form-label required">SPA/MULTI</label>
                                        <select name="type_as" id="type_as" class="form-control select2">
                                            <?php foreach(App\Models\Product::$typeAs as $tp) : ?>
                                                <option value="<?= $tp ?>"><?= $tp ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_account" class="form-label required">Max Account</label>
                                        <input type="number" name="max_account" id="max_account" class="form-control" placeholder="Maksimal akun yang bisa dibuat oleh user" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="commission" class="form-label required">Komisi</label>
                                        <input type="number" name="commission" id="commission" class="form-control" placeholder="Komisi" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="leverage" class="form-label required">Leverage</label>
                                        <input type="number" name="leverage" id="leverage" class="form-control" min="1" step="1" placeholder="Leverage" value="400" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="swap" class="form-label required">Swap</label>
                                        <select name="swap" id="swap" class="form-control select2">
                                            <option value="">Please select</option>
                                            <option value="Tidak">Tidak</option>
                                            <option value="Pengajuan">Pengajuan</option>
                                            <option value="Free 7 Hari">Free 7 Hari</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="group" class="form-label required">Group</label>
                                        <input type="text" name="group" id="group" class="form-control" placeholder="Group" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="form-label required">Status</label>
                                        <select name="status" id="status" class="form-control select2">
                                            <option value="">Please select</option>
                                            <option value="-1" >Enable</option>
                                            <option value="1" >Disable</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sales_model" class="form-label required">Sales Model</label>
                                        <select name="sales_model" id="sales_model" class="form-control select2">
                                            <option value="">Please select</option>
                                            <?php
                                                $query = "SELECT * FROM tb_racctype_category";
                                                if($query) {
                                                    $result = $db->query($query);
                                                    while($row = $result->fetch_assoc()) {
                                                        echo '<option value="'.$row['ID_RCAT'].'">'.ucwords($row['RCAT_TYPE']).'</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="trading_rule" class="form-label required">Trading Rules</label>
                                        <input type="file" name="trading_rule" id="trading_rule" class="dropify" data-allowed-file-extensions="jpg png jpeg pdf" data-max-size="2M" data-show-remove="false" required>
                                    </div>
                                </div>
                            </div>
                        </div>    
                        <div class="col-md-6">
                            <h5 class="card-title">Deposit New Account</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="minimum_deposit" class="form-label required">Minimum Margin</label>
                                        <div class="input-group">
                                            <span class="input-group-text transaction-currency"></span>
                                            <input type="text" name="minimum_deposit" id="mininum_dminimum_depositeposit" class="form-control amount-formatter" placeholder="Minimum Margin" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="maximum_deposit" class="form-label required">Maximum Margin</label>
                                        <div class="input-group">
                                            <span class="input-group-text transaction-currency"></span>
                                            <input type="text" name="maximum_deposit" id="maximum_deposit" class="form-control amount-formatter" placeholder="Maximum Margin" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h5 class="card-title">Deposit</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="minimum_topup" class="form-label required">Minimum Deposit</label>
                                        <div class="input-group">
                                            <span class="input-group-text transaction-currency"></span>
                                            <input type="text" name="minimum_topup" id="minimum_topup" class="form-control amount-formatter" placeholder="Minimum Deposit" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="maximum_topup" class="form-label required">Maximum Deposit</label>
                                        <div class="input-group">
                                            <span class="input-group-text transaction-currency"></span>
                                            <input type="text" name="maximum_topup" id="maximum_topup" class="form-control amount-formatter" placeholder="Maximum Deposit" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h5 class="card-title">Withdrawal</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="minimum_withdrawal" class="form-label required">Minimum Withdrawal</label>
                                        <div class="input-group">
                                            <span class="input-group-text transaction-currency"></span>
                                            <input type="text" name="minimum_withdrawal" id="minimum_withdrawal" class="form-control amount-formatter" placeholder="Minimum Withdrawal" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="maximum_withdrawal" class="form-label required">Maximum Withdrawal</label>
                                        <div class="input-group">
                                            <span class="input-group-text transaction-currency"></span>
                                            <input type="text" name="maximum_withdrawal" id="maximum_withdrawal" class="form-control amount-formatter" placeholder="Maximum Withdrawal" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3 mb-0 text-end">
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#rate').on('change', function() {
            let span = $('.transaction-currency');
            if(this.value.toLowerCase() == "floating") span.text('USD')
            else span.text('IDR') 
        }).change();

        $('#form-create-product').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize(), 
                button = $(this).find('button[type="submit"]'),
                url = "/ajax/post".concat($(this).attr('action'));

            button.addClass('loading');

            $.ajax({
                url: url,
                type: 'post',
                dataType: "json",
                data: new FormData(this),
                contentType: false,
                processData: false,
                cache: false,
            }).done((resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.href = resp.data.redirect;
                    }
                })
            })
        })  
    })
</script>