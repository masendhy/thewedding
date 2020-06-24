<?php
/**
 * @author      Rootpixel
 * @copyright   (C) 2018 - Rootpixel. All rights reserved.
 * @link        https://rootpixel.net
 */
// load the core script
require __DIR__.'/core.php';
if (getenv('APP_ENV') == 'production') {
    error_reporting(0);
    @ini_set('display_errors', 0);
}else{
    error_reporting(1);
    @ini_set('display_errors', 1);
}
?>
<!--
Levidio Lorem Ipsum
http://rootpixel.co.id/levidio/
Template name: Wedding 1
Order at: http://rootpixel.co.id/levidio/
Author: Rootpixel
Website: http://www.rootpixel.net/
Contact: support@rootpixel.net
Social: http://facebook.com/rootpixel
Version : v1.0
-->
<!DOCTYPE html>
<!--
Levidio Lorem Ipsum
http://rootpixel.co.id/levidio/
Template name: Wedding 1
Order at: http://rootpixel.co.id/levidio/
Author: Rootpixel
Website: http://www.rootpixel.net/
Contact: support@rootpixel.net
Social: http://facebook.com/rootpixel
Version : v1.0
-->
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" CONTENT="noindex, nofollow">
    <title>Levidio Invitation - Wedding 3</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/dripicons.css">
    <link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css">
    <style type="text/css">
    header:after {
        display: none;
    }
    #subscribers{
        padding-top: 120px;
        height: 100vh;
    }
    .subscriber-list{
        padding: 20px;
        border-radius: 5px;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
    }

    /* Dark Style */
    .subscriber-list.dark{
        background-color: #1d1d1d;
        color: #999;
    }
    .subscriber-list.dark input, .subscriber-list.dark select{
        color: #ddd;
        padding: 6px 10px;
        background: #666;
        border: 0;
    }
    .subscriber-list.dark input:focus, .subscriber-list.dark select:focus{
        outline: none;
        box-shadow: none;
    }
    .subscriber-list.dark td{
        color: #aaa!important;
        font-weight: 400!important;
        border-color: #333!important;
    }
    .subscriber-list.dark th{
        font-weight: 600!important;
        color: #EEE!important;
        border-color: #333!important;
    }

    /* Light Style */
    .subscriber-list.light{
        background-color: #fff;
        color: #4d4d4d;
    }
    .subscriber-list.light input, .subscriber-list.light select{
        color: #4d4d4d;
        padding: 6px 10px;
        background: #eee;
        border: 0;
    }
    .subscriber-list.light input:focus, .subscriber-list.light select:focus{
        outline: none;
        box-shadow: none;
    }
    .subscriber-list.light td{
        color: #4d4d4d!important;
        font-weight: 400!important;
        border-color: #eee!important;
    }
    .subscriber-list.light th{
        font-weight: 600!important;
        color: #2d2d2d!important;
        border-color: #ccc!important;
    }
</style>
</head>

<body>

 <!-- Navbar -->
<div class="menu">
    <div class="container">
        <div class="row mb-0">
            <div class="col-md-5 col-sm-5 col-5 text-right my-auto">
                <p class="mb-0 logo bold italic float-left">SMITH & JANE <br> WEDDING</p>
            </div>
        </div>
    </div>
</div>
<!-- End: Navbar -->


    <!-- Subscribers -->
    <header id="subscribers">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="color--tosca text-center">Here's Your Subscriber List</h3>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-sm-8 offset-sm-2">
                    <div class="subscriber-list light">
                        <table id="subscribers-table" class="table table-responsive-md" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email address</th>
                                    <th>Guest</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                            // check the amount of your subscribers
                                $subscribers = $flatbase->read()->in(getenv('STORAGE_FILE_NAME'));
                            if ($subscribers->count() > 0){ #you have at least 1 subscriber, displaying the data in table
                                $num = 1;
                                foreach ($subscribers->get() as $subscriber) {
                                    ?>
                                    <tr>
                                        <td width="30px"><?= $num++;?></td>
                                        <td><?= $subscriber['name'];?></td>
                                        <td><?= $subscriber['email'];?></td>
                                        <td><?= $subscriber['guest'];?></td>
                                        <td><?= $subscriber['phone'];?></td>
                                        <td>
                                            <form method="POST" action="<?= getenv('FORM_URL') ?>">
                                                <input type="hidden" name="method_field" value="DELETE">
                                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                <input type="hidden" name="id" value="<?= $subscriber['id'] ?>">
                                                <button type="submit" class="btn btn--tosca" onclick="return confirm('This will permanently delete the data. Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                }else{ #you don't have at least 1 subscriber
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center">You don't have any subscriber yet.</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- End: Subscribers -->

<!-- Scripts -->
<script type="text/javascript" src="assets/js/jquery-3.1.0.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap.bundle.js"></script>
<script type="text/javascript" src="assets/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="assets/js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="assets/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="assets/js/jszip.min.js"></script> 
<script type="text/javascript" src="assets/js/buttons.html5.min.js"></script> 
<script type="text/javascript">
    $(document).ready(function() {
        $('#subscribers-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
            {
                extend: 'csvHtml5',
                text: '<i class="dripicons dripicons-print"></i> CSV',
                titleAttr: 'CSV',
                title: "<?=getenv('FILE_CSV_NAME')?>",
                exportOptions: {
                    columns: ':not(:last-child)',
                }
            }
            ]
        });
    } );
</script>
</body>
</html>