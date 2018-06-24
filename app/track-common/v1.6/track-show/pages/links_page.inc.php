<?php if (!$include_flag){exit();} ?>
<script>
$( document ).ready(function() {
	var uploader = new ss.SimpleUpload({
	      button: 'btn_mass_import_offers', // HTML element used as upload button
	      url: '<?php echo _HTML_ROOT_PATH;?>/index.php', // URL of server-side upload handler
	      name: 'ajax_upload_offers', // Parameter name of the uploaded file
		  customHeaders: {'Authorization': '<?php echo CSRF_KEY;?>'},
		  onSubmit: function(filename, extension) 
		  {
			$('#btn_mass_import_offers i').removeClass('fa-upload'); 
		  	$('#btn_mass_import_offers i').addClass('fa-spinner fa-spin'); 
		  },
		  onComplete: function(filename, response) {
			location.reload(true);
          }		  
	});
});
</script>
<?php
$category_id=$_REQUEST['category_id'];
$category_name='{empty}';

$arr_offers=array();
if ($category_id>0)
{
	$offers_stats_array=array();
	$sql="select id, category_caption, category_name, category_type from tbl_links_categories_list where id='".mysql_real_escape_string($category_id)."'";
	$result=mysql_query($sql);
	$row=mysql_fetch_assoc($result);
	
	if ($row['id']>0)
	{
		$category_name=$row['category_caption'];		
	}
	else
	{
		// Category id not found
		header ("Location: ".full_url().'?page=links');
		exit();
	}

	switch ($row['category_type'])
	{
		case 'network':
			$page_type='network';
			
			// Get network ID
			$sql="select id from tbl_cpa_networks where network_category_name='".mysql_real_escape_string($row['category_name'])."'";
			$result=mysql_query($sql);
			$row=mysql_fetch_assoc($result);
			$network_id=$row['id'];
			
			// Get list of offers from network
			$sql="select * from tbl_offers where network_id='".mysql_real_escape_string($network_id)."' and status=0 order by date_add desc, id asc";
			$result=mysql_query($sql);
			while ($row=mysql_fetch_assoc($result))
			{
				$arr_offers[]=$row;	
			}
		break;
		
		default:
			// Get list of offers in category
			$sql="select tbl_offers.* from tbl_offers left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id where tbl_links_categories.category_id='".mysql_real_escape_string($category_id)."' and tbl_offers.network_id='0' and tbl_offers.status=0 order by tbl_offers.date_add desc, tbl_offers.id asc";
			$result=mysql_query($sql);
			$arr_offers=array();
			$offers_id=array();
			while ($row=mysql_fetch_assoc($result))
			{
				$row['offer_id']=$row['id'];
				$offers_id[]="'".mysql_real_escape_string($row['id'])."'";
				$arr_offers[]=$row;	
			}
			$offers_id_str=implode(',', $offers_id);
			
			$sql="select out_id, count(id) as cnt from tbl_clicks where out_id in ({$offers_id_str}) group by out_id";
			$result=mysql_query($sql);
			$offers_stats_array=array();
			while ($row=mysql_fetch_assoc($result))
			{
				$offers_stats_array[$row['out_id']]=$row['cnt'];
			}
		break;
	}
}
else
{
	// Get list of offers without category
	$sql="select tbl_offers.* from tbl_offers left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id where tbl_links_categories.id IS NULL and tbl_offers.network_id='0' and tbl_offers.status=0 order by tbl_offers.date_add desc, tbl_offers.id asc";
	$result=mysql_query($sql);
	$arr_offers=array();
	$offers_id=array();
	while ($row=mysql_fetch_assoc($result))
	{
		$row['offer_id']=$row['id'];
		$offers_id[]="'".mysql_real_escape_string($row['id'])."'";
		$arr_offers[]=$row;	
	}
	$offers_id_str=implode(',', $offers_id);
	
	$sql="select out_id, count(id) as cnt from tbl_clicks where out_id in ({$offers_id_str}) group by out_id";
	$result=mysql_query($sql);
	$offers_stats_array=array();
	while ($row=mysql_fetch_assoc($result))
	{
		$offers_stats_array[$row['out_id']]=$row['cnt'];
	}
}
?>
<script>
    
        var last_removed = 0;
	function import_offers_from_network(id)
	{
		$('#networks_import_ajax').show();
		$.ajax({
		  type: 'POST',
		  url: 'index.php',
		  data: { csrfkey: '<?php echo CSRF_KEY;?>',ajax_act: 'import_hasoffers_offers', id: id}
		}).done(function( msg ) 
		{
			$('#networks_import_ajax').hide();
			$('#networks_import_status_text').html(msg);
			$('#networks_import_status').show();
		});
	}
	
	function check_add_offer()
	{	
		var offer_url=$('input[name="link_url"]', $('#form_add_offer'));
		$(offer_url).css('background-color', 'white');		
		if ($(offer_url).val()=='')
		{
			$(offer_url).css('background-color','lightyellow');
			$(offer_url).focus();
			return false;	
		}
		return true;
	}	
	function show_category_edit()
	{
		if ($('.category_edit').css('display')=='none')
		{
			$('.category_edit').show();
			$('.category_edit input[name=category_name]').focus();			
		}
		else{
			$('.category_edit').hide();
		}
	}
	
	function check_category_edit()
	{
		if ($('.category_edit input[name=is_delete]').val()=='1')
		{
			return true;
		}

		if ($('.category_edit input[name=category_name]').val()=='')
		{
			return false;
		}

		return true;
	}
	
	function delete_category()
	{
		$('#form_category_edit input[name=is_delete]').val('1');
		$('#form_category_edit').submit();
	}

	function delete_link(obj, id)
	{
            last_removed = id;
		$.ajax({
		  type: 'POST',
		  url: 'index.php',
		  data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=delete_link&id='+id
		}).done(function( msg ) 
		{
                        $('#linkrow-' + id).hide();
                        $('#link_name_alert').text($('#link-name-'+id).text());
                        $('#remove_alert').show();
		});

		return false;
	}
	function move_link_to_category (offer_id, category_id)
	{
		$.ajax({
		  type: 'POST',
		  url: 'index.php',
		  data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=move_link_to_category&offer_id='+offer_id+'&category_id='+category_id
		}).done(function( msg ) 
		{

		});
		return false;
	}
        
        
        function restore_link() {
                var id = last_removed;
		$.ajax({
		  type: 'POST',
		  url: 'index.php',
		  data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=restore_link&id='+id
		}).done(function( msg ) 
		{
//			$(obj).parent().parent().parent().parent().parent().remove();
                        $('#linkrow-' + id).show();
                        $('#remove_alert').hide();
		});
                last_removed = 0;
		return false;
        }

	$(document).ready(function() 
	{
            $('#new_link_name').focus();
            $('#link_add_tooltip').tooltip({delay: { show: 300, hide: 100 }});	
	});	
</script>
<?php
if ($category_name!='{empty}')
{?>



	<?php
	echo "<div class='category_title' onclick='show_category_edit()'><span class='category_name'>"._e($category_name)."</span> <i class='fa fa-edit'></i></div>";
	echo "<div class='category_edit'>";
	?>
<div class="row">
	<form class="form-inline" role="form" method='post' id='form_category_edit' onsubmit='return check_category_edit();'>
			<input type='hidden' name='ajax_act' value='category_edit'>
                        <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
			<input type='hidden' name='category_id' value='<?php echo _e($_REQUEST['category_id']);?>'>
			<input type='hidden' name='is_delete' value='0'>

		  <div class="form-group col-xs-3">
			    <input type="text" class="form-control" name='category_name' placeholder='Название категории' value='<?php echo _e($category_name);?>'>
		  </div>

	  <button type="submit" class="btn btn-default">Изменить</button>
	  <button type="button" class="btn btn-link" onclick='delete_category()'>Удалить</button>
	</form>
</div>
	<?php

	echo "</div>";
}
?>
<?php
if ($page_type=='network')
{
	echo "<p align=right><img style='margin-right:15px; display: none;' id='networks_import_ajax' src='img/icons/ajax.gif'><span class='btn' onclick='import_offers_from_network(\""._e($category_id)."\")'>Импорт офферов</span></p>";
	echo "<div class='alert' id='networks_import_status' style='display:none;'>
			<button type='button' class='close' data-dismiss='alert'>&times;</button>
			<strong id='networks_import_status_text'></strong>
		  </div>";
}
else
{
?>

<p><strong>Новый оффер</strong> <a href="#" data-toggle="tooltip" data-placement="bottom" title="" onclick="return false;" style="cursor:default; color:gray;" id="link_add_tooltip" data-original-title="Для использования SubID добавьте [SUBID] в URL" class='no-hover'><i class="fa fa-question-circle"></i></a></p>

<div class="row">
	<form class="form-inline" role="form" method="post" onSubmit='return check_add_offer();' id='form_add_offer'>
		<input type=hidden name='ajax_act' value='add_offer'>
                <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
		<input type=hidden name='category_id' value='<?php echo _e($category_id);?>'>

		  <div class="form-group col-xs-3">
			    <input type="text" class="form-control" name='link_name' id="new_link_name" placeholder="Название оффера">
		  </div>

		  <div class="form-group  col-xs-5">
			    <input type="text" class="form-control" name='link_url' placeholder="URL">
		  </div>

	  <button type="submit" class="btn btn-default">Добавить</button>
	  	<button id='btn_mass_import_offers' title="Загрузить из файла" class='btn btn-link' style='margin-left:10px;'><i class="fa fa-upload"></i> Excel</button>
	</form>

</div>

<?php
}

if (count($arr_offers)>0)
{
	echo "<div class='row'>&nbsp;</div>";
	echo "<div class='row'>";
	echo "<div class='col-md-12'>";
        echo '<div class="alert alert-info" style="display:none;" id="remove_alert"><button type="button" class="close" data-dismiss="alert">&times;</button>'
                . '<strong>Внимание!</strong> Оффер <strong id="link_name_alert"></strong> был удален, Вы можете его <b><u><a href="javascript:void(0);" onClick="restore_link();">восстановить</a></u></b>.'
                . '</div>';
	echo "<table class='table table-striped table-condensed table-bordered'>";
	echo "<tr>";
		echo "<th>Название</th>";
		echo "<th>URL</th>";
		echo "<th>Переходов</th>";
	echo "</tr>";
	foreach ($arr_offers as $cur)
	{
		$tracking_url=$cur['offer_tracking_url'];
		if (strpos ($tracking_url, 'https://')===0)
		{
			$tracking_url=substr($tracking_url, strlen ('https://'));
		}

		if (strpos ($tracking_url, 'http://')===0)
		{
			$tracking_url=substr($tracking_url, strlen ('http://'));
		}
		$total_visits=$offers_stats_array[$cur['offer_id']]+0;
		echo "<tr class='links_row' id=linkrow-".$cur['offer_id'].">";
			echo "<td>";
			?>
			<div class="btn-group links_menu">
                <button class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                <ul class="dropdown-menu">
					<?php
					if (count($arr_categories)>0)
					{
						if (intval($_REQUEST['category_id'])!=0)
						{
							echo "<li><a href='#' onclick='return move_link_to_category(\""._e($cur['offer_id'])."\", 0);'>Без категории</a></li>";
						}
						foreach ($arr_categories as $cur_category)
						{
							if ($cur_category['id']!=$_REQUEST['category_id'])
							{
								echo "<li><a href='#' onclick='return move_link_to_category(\""._e($cur['offer_id'])."\", \""._e($cur_category['id'])."\");'>"._e($cur_category['category_caption'])."</a></li>";								
							}
						}
		                echo '<li class="divider"></li>';
					}
					echo "<li><a href='#' style='color:red;' onclick='return delete_link(this, \""._e($cur['offer_id'])."\")'>Удалить оффер</a></li>";
					?>
                </ul>
              </div>
			<?php
			echo '<span id="link-name-'.$cur['offer_id'].'">'._e($cur['offer_name'])."</span></td>";
			echo "<td><input type=text style='width:350px; border:none; background:none; -webkit-box-shadow:none; box-shadow:none;' value='"._e($tracking_url)."'></td>";
			echo "<td>"._e($total_visits)."</td>";
		echo "</tr>";	
	}
	echo "</table>";
	echo '</div><!-- ./col-md-12 -->';
	echo '</div><!-- ./row -->';
}
?>