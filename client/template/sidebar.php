<?php
use App\Library\Sales\SalesMain;
use App\Models\User;

$isIB = false;
$salesData = SalesMain::getUserType($user['MBR_TYPE']);
?>

<!-- main sidebar start -->
<div class="main-sidebar">
    <div class="main-menu">
        <ul class="sidebar-menu scrollable">
            <ul class="sidebar-item">
                <li class="sidebar-dropdown-item">
                    <a href="/dashboard" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-home"></i></span> <span class="sidebar-txt">Dashboard</span></a>
                </li>
            </ul>

            <?php if($user['MBR_STS'] == 2) : ?>
                <?php $step = [1]; ?>
                <li class="sidebar-item">
                    <a role="button" class="sidebar-link-group-title has-sub">Verification of personal data</a>
                    <ul class="sidebar-link-group">
                        <?php foreach($step as $s) : ?>
                            <?php 
                            switch(true) {
                                case ($s == $user['MBR_VERIF']) :
                                    $url    = "/verif/step-$s";
                                    $icon   = "lock-open";
                                    $text   = "";
                                    break;

                                case ($s > $user['MBR_VERIF']) :
                                    $url    = "javascript:void(0)";
                                    $icon   = "lock";
                                    $text   = "";
                                    break;

                                default:
                                    $url    = "/verif/step-$s";
                                    $icon   = "check";
                                    $text   = "text-success";
                                    break;
                            }
                            ?>

                            <li class="sidebar-dropdown-item">
                                <a href="<?php echo $url; ?>" class="sidebar-link">
                                    <span class="nav-icon"><i class="fa-light fa-<?php echo $icon ?>"></i></span> 
                                    <span class="sidebar-txt <?php echo $text ?>">Step <?php echo $s ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if($user['MBR_STS'] == -1) : ?>
                <style>
                    .blink-text {
                        animation: blinker 1s linear infinite; /* Terapkan animasi */
                    }

                    @keyframes blinker {
                        50% {
                            opacity: 0; /* Sembunyikan teks pada 50% durasi */
                        }
                    }
                </style>
                <li class="sidebar-item">
                    <a role="button" class="sidebar-link-group-title has-sub">Trade</a>
                    <ul class="sidebar-link-group">
                        <!-- <li class="sidebar-dropdown-item">
                            <a href="/web-trade" class="sidebar-link">
                                <span class="nav-icon">
                                    <i class="fa-light fa-chart-column"></i>
                                </span>
                                <span class="blink-text sidebar-txt">Web Trade </span>
                            </a>
                        </li> -->
                        <li class="sidebar-dropdown-item">
                            <a href="/account" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-user-tie"></i></span> <span class="sidebar-txt">Account</span></a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a role="button" class="sidebar-link-group-title has-sub">Profile</a>
                    <ul class="sidebar-link-group">
                        <li class="sidebar-dropdown-item">
                            <a href="/personal-information" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-user"></i></span> <span class="sidebar-txt">Personal Information</span></a>
                        </li>
                        <li class="sidebar-dropdown-item">
                            <a href="/security" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-shield"></i></span> <span class="sidebar-txt">Security</span></a>
                        </li>
                        <li class="sidebar-dropdown-item">
                            <a href="/bank" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-bank"></i></span> <span class="sidebar-txt">Bank</span></a>
                        </li>
                    </ul>
                </li>
                
                <li class="sidebar-item">
                    <a role="button" class="sidebar-link-group-title has-sub">Finance</a>
                    <ul class="sidebar-link-group">
                        <?php if($user['MBR_WALLET'] == 1) : ?>
                            <li class="sidebar-dropdown-item">
                                <a href="/wallet" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-wallet"></i></span> <span class="sidebar-txt">Wallet</span></a>
                            </li>
                        <?php endif; ?>
                        <li class="sidebar-dropdown-item">
                            <a href="/deposit" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-arrow-right-to-bracket"></i></span> <span class="sidebar-txt">Deposit</span></a>
                        </li>
                        <li class="sidebar-dropdown-item">
                            <a href="/withdrawal" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-arrow-right-from-bracket"></i></span> <span class="sidebar-txt">Withdrawal</span></a>
                        </li>
                        <li class="sidebar-dropdown-item">
                            <a href="/internal-transfer" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-arrow-right-arrow-left"></i></span> <span class="sidebar-txt">Internal Transfer</span></a>
                        </li>
                    </ul>
                </li>
                
                <li class="sidebar-item">
                    <a role="button" class="sidebar-link-group-title has-sub">IB</a>
                    <ul class="sidebar-link-group">
                        <li class="sidebar-dropdown-item">
                            <a href="/ib/become" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-users"></i></span> <span class="sidebar-txt">Become IB</span></a>
                            <?php
                                $isIB = User::get_ib_data($user['MBR_ID'], [-1]); 
                                if($isIB) :
                            ?>
                            <a href="/ib/tree" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-users"></i></span> <span class="sidebar-txt">Treeview</span></a>
                                <?php if($salesData && $salesData->isAllowToRequestAccountCondition()) : ?>
                                    <a href="/ib/my-teams" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-users"></i></span> <span class="sidebar-txt">My Teams</span></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </li>
                    </ul>
                </li>

                <?php //if($salesData) : ?>
                <?php if(true == false) : ?>
                    <li class="sidebar-item">
                        <a role="button" class="sidebar-link-group-title has-sub">Sales</a>
                        <ul class="sidebar-link-group">
                            <?php if($salesData->isAllowToRequestAccountCondition()) : ?>
                                <li class="sidebar-dropdown-item">
                                    <a href="/sales/commission/setting" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-gear"></i></span> <span class="sidebar-txt">Commission Setting</span></a>
                                </li>
                            <?php endif; ?>
                            <?php
                                if($salesData) {
                                    if($salesData->division()['ID_SLSDIVISION'] == 1 && $salesData->level() == 2 && $salesData->isCanShareRefferal() == 0){
                                        echo '<li class="sidebar-dropdown-item">
                                        <a href="/sales/dorman" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-user-lock"></i></span> <span class="sidebar-txt">Dorman</span></a>
                                        </li>
                                        <li class="sidebar-dropdown-item">
                                        <a href="/sales/keep" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-user-circle"></i></span> <span class="sidebar-txt">Your Retention</span></a>
                                        </li>';
                                    }
                                }
                            ?>
                            <li class="sidebar-dropdown-item">
                                <a href="/sales/wallet" class="sidebar-link"><span class="nav-icon"><i class="fa-light fa-wallet"></i></span> <span class="sidebar-txt">Wallet</span></a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                
                <li class="help-center">
                    <h3>Help Center</h3>
                    <!-- <p>We're an award-winning, forward thinking</p> -->
                    <a href="/help-center" class="btn btn-sm btn-light">Go to Help Center</a>
                </li>
                <li class="help-center" style="margin-top: 20px;">
                    <p>Your Download is Just One Click Away!</p>
                    <a href="/downloads" class="btn btn-sm btn-light">Download</a>
                </li>
            <?php endif; ?>
            
        </ul>
    </div>
</div>