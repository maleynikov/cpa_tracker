<?php if (!$include_flag){exit();} ?>
<script>
	function add_new_category()
	{
		var category_name=$('.links_category_add_form input[name=category_name]').val();
		if (category_name=='')
		{
			$('.links_category_add_form input[name=category_name]').focus();
			return false;
		}

		$.ajax({
		  type: 'POST',
		  url: 'index.php',
		  data: $('#category_add_form').serialize()
		}).done(function( category_id ) 
		{
			$('#categories_left_menu_list').append('<li><a href="?page=links&category_id='+category_id+'">'+htmlEncode(category_name)+'</a></li>');
			$('.links_category_add_form input[name=category_name]').focus();
			$('#category_add_form')[0].reset();
		});

		return false;
	}
	function toggle_add_category_form()
	{
		$('.links_category_add_form').toggle();
		switch ($('.links_category_add_form').css('display')){
			case 'none':
				$('#category_add_form')[0].reset();
			break;

			default: 
				$('.links_category_add_form input[name=category_name]').focus();
			break;
		}
	}

	function htmlEncode(value){
	    if (value) {
	        return jQuery('<div />').text(value).html();
	    } else {
	        return '';
	    }
	}	
</script>

<div class="col-md-3" id="categories_left_menu">
	<div class="bs-sidebar hidden-print affix-top">
		<ul class="nav bs-sidenav" id='categories_left_menu_list'>
			<?php
			    $result=get_links_categories_list();
			    $arr_categories=$result['categories'];
			    $arr_categories_count=$result['categories_count'];

			    if ($_REQUEST['category_id']=='' || $_REQUEST['category_id']==0){$class=" class='active'";}else{$class='';}
			    echo "<li {$class}>";
					    echo "<a href='?page=links'>Без категории";
						    if ($arr_categories_count[0]>0)
						    {
						    	echo "<span class='category_count'>"._e($arr_categories_count[0])."</span>";
						    }
					    echo "</a>";
			    echo "</li>";
			    
			    $cur_category_group='{empty}';

			    foreach ($arr_categories as $cur)
			    {
					if ($cur_category_group!=$cur['category_type'])
					{
						$cur_category_group=$cur['category_type'];
						switch ($cur_category_group)
						{
							case 'network':
								echo "<li class='nav-header'>CPA сети</li>"; 
							break;
							
							default:
								
							break;
						}
					}
					if ($_REQUEST['category_id']==$cur['id']){$class="class='active'";}else{$class="";}
					echo "<li {$class}>";
						echo "<a href='?page=links&category_id="._e($cur['id'])."'>";
							echo _e($cur['category_caption']);
							if ($arr_categories_count[$cur['id']]>0)
						    {
						    	echo "<span class='category_count'>"._e($arr_categories_count[$cur['id']])."</span>";
						    }	
						echo "</a>";
						
					echo "</li>";
			    }
		    ?>			
		</ul>
    <div class='links_category_add'>
    	<hr />
	    <span class='btn-link' onclick="toggle_add_category_form()">+ новая категория</span>
    </div>		


	<form class="form-inline links_category_add_form" role="form" method="post" onsubmit='return add_new_category()' id='category_add_form'>
		<input type='hidden' name='ajax_act' value='add_category'>
                
        <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
		  <div class="form-group">
			    <input type="text" class="form-control" name="category_name" placeholder="Название категории">
		  </div>
	  <button type="submit" class="btn btn-default">+</button>
	</form>
	
	</div>
	<?php 
		echo load_plugin('demo', 'demo_well');
	?>
</div>