<?php if(count(App\Models\Account::myAccount($user['MBR_ID'])) > 0 || !empty(App\Models\Account::getDemoAccount($userid))) : ?>
    
    <?php require_once __DIR__ . "/v2/index.php"; ?>

<?php else : ?>
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">
            <div class="panel">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <a href="/account/create" class="text-center btn btn-md btn-primary mt-3 mb-3">Create Real Account</a>
                            <div class="alert alert-warning mt-3" role="alert">
                                You don't have any real account yet. Please create a real account to access the web trade platform.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
