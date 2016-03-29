<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    
    <title>Demo Live Chat</title>

    <script src="<?php echo base_url() ?>/public/js/jquery-1.11.0.min.js"></script>
    <script src="<?php echo base_url() ?>/public/js/demo_chat.js"></script>
    <script src="<?php echo base_url() ?>/public/js/config.js"></script>
  
    <link type="text/css" rel="stylesheet" media="all" href="<?php echo base_url() ?>/public/css/demo_chat.css" />
    
</head>
<body>
    <?php if ( isset($_SESSION['username']) && isset($_SESSION['password']) ) {
        echo "<a href='http://localhost/demo_livechat/index.php/user/logout'>Logout</a>";
    } ?>
    <div id="container">

        <h2>Online Users</h2>
        <table width="45%" cellspacing="1" cellpadding="2" class="tableContent" style="margin-left:0px !important; text-align:center;">
            <tbody>
                <tr style="background-color:#9EB0E9;  font-size:13px; font-weight:bold; color:#fff;">
                    <th>Status</th>
                    <th>User Id</th>
                    <th>User Name</th>
                </tr>
                                  
            <?php
            if( isset($listOfUsers) ) {
                foreach($listOfUsers as $key => $value) {
            ?>
                <tr style="background-color:#efefef;">
                    <td>
                        <?php 
                        if( $value['status'] == 1 ) {
                            echo 'Online';
                        }else {
                            echo 'Offline';
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo $value['id']; ?>
                    </td>
                    <td>
                        <?php 
                        if( $_SESSION['username'] == $value['username'] ) { 
                        ?>
                            <a href="#" style="text-decoration:none">
                        <?php 
                        } else { 
                        ?>  
                            <a href="javascript:void(0)" onClick="javascript:chatWith('<?php echo $value['username'] ?>');">
                        <?php 
                        } 
                        ?>      
                        <?php echo $value['username'] ?>
                            </a>
                    </td>
                </tr>
            <?php   
                } // end foreach loop
            } // end if condition
            ?>          
                
            </tbody>
        </table>
        
    </div>

</body>
</html>