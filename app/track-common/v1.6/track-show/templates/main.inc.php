<?php if (!$include_flag){exit();} ?>
<!-- CPA Tracker, http://www.cpatracker.ru -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include (_TRACK_SHOW_COMMON_PATH.'/templates/head.inc.php'); ?>
    </head>

    <body>
        <div id="wrap">
        
            <?php include _TRACK_SHOW_COMMON_PATH."/templates/menu-top.inc.php"; ?>
            <div class="container">
                <div class="row">
                    <?php 
                        if (in_array($page_sidebar, $page_sidebar_allowed))
                        {
                            include (_TRACK_SHOW_COMMON_PATH.'/templates/'.$page_sidebar);
                        }
                        else
                        {
                            include (_TRACK_SHOW_COMMON_PATH.'/templates/sidebar-left.inc.php');
                        }

                        if ($bHideLeftSidebar!==true){$main_container_class='col-sm-9';} else {$main_container_class='col-sm-12';}
                    ?>
                    <div class="<?php echo $main_container_class?>">
                        <?php
                        	if($_REQUEST['page']!='login') {
	                        	echo load_plugin('payreminder');
	                    		echo load_plugin('expiry');
                    		}
                            if (in_array($page_content, $page_content_allowed)) {
                                include (_TRACK_SHOW_COMMON_PATH.'/pages/'.$page_content);
                            }
                        ?>
                    </div>
                </div> <!-- /row -->
            </div> <!-- /container -->            
        </div> <!-- /wrap -->

        <div id="footer">
            <?php echo tpx('footer'); ?>
        </div>
        <?php
        	if(!empty($_GET['debug'])) {
        		foreach($sql_log as $q)	{
        			echo $q.'<br>';
        		}
        		echo 'Total: <b>' .$sql_time. '</b>';
        	}
        ?>
    </body>
</html>