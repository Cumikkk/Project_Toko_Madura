<?php

    use App\Models\Apuppt;
    use App\Models\Helper;
    $tbody = Apuppt::dttotTabel();
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">DTTOT Table</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">APUPPT</a></li>
            <li class="breadcrumb-item active" aria-current="page">DTTOT Table</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Tabel DTTOT</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_search" class="table table-striped table-hover table-bordered" width="100%">
                        <thead>
                            <tr>
                                <?php 
                                    $num = 1; 
                                    foreach($tbody as $tbd => $vtbd){ 
                                        if($num == 1){
                                ?>
                                        <th class="text-center">No</th>
                                        <?php 
                                            foreach($vtbd->getCellIterator() as $ths => $vths){       
                                        ?>
                                            <th class="text-center"><?php print_r($vths->getValue("")); ?></th>
                                        <?php 
                                            }
                                        ?>
                                <?php 
                                            break;
                                        }
                                        $num++; 
                                    }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $num = 1; 
                                foreach($tbody as $tbd => $vtbd){ 
                                    if($num != 1){
                            ?>
                                <tr>
                                    <td><?php echo ($num-1); ?></td>
                                    <?php 
                                        foreach($vtbd->getCellIterator() as $ths => $vths){       
                                    ?>
                                        <td><?php  print_r($vths->getValue("")) ?></td>
                                    <?php 
                                        }
                                    ?>
                                </tr>
                            <?php 
                                    }
                                    $num++; 
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#table_search').DataTable({
            dom: 'Blfrtip',
            "processing": true,
            "deferRender": true,
            "lengthMenu": [[10, 25, 50, -1].reverse(), [10, 25, 50, "Semua"].reverse()],
            "scrollX": true,
            "order": [[ 0, "asc" ]]
        });
        var nm = Number("<?php echo base64_decode(Helper::form_input($_GET["nms"]));  ?>");
        var nk = Number("<?php echo base64_decode(Helper::form_input($_GET["nks"]));  ?>");
        let tby = document.querySelectorAll('tbody');
        console.log(tby);
        if(!isNaN(nk)){ 
            tby[0].children[nk].children[2].style.background = 'orange'; 
            tby[0].children[nk].children[2].scrollIntoView();
            setTimeout(function(){
                tby[0].children[nk].children[2].scrollIntoView();
            }, 1000);
        }
        if(!isNaN(nm)){ 
            tby[0].children[nm].children[1].style.background = 'yellow'; 
            tby[0].children[nm].children[2].scrollIntoView();
            setTimeout(function(){
                tby[0].children[nm].children[2].scrollIntoView();
            }, 1500);
        }
    });
</script>