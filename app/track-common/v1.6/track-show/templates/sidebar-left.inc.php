<?php if (!$include_flag){exit();} ?>
<?php
	if ($bHideLeftSidebar!==true){
?>

<div class="col-md-3">
<?php
if (is_array($arr_left_menu) && count($arr_left_menu)>0)
{
?>
	<div class="bs-sidebar hidden-print affix-top">
		<ul class="nav bs-sidenav">
			<?php
				foreach ($arr_left_menu as $cur)
				{
					$class=($cur['is_active']==1)?'active':'';
			?>
				<li class="<?php echo $class;?>"><a href="<?php echo _e($cur['link']);?>"><?php echo _e($cur['caption']);?></a></li>
			<?php
				}
			?>
		</ul>
	</div>
<?php
}
?>
<?php
	echo load_plugin('demo', 'demo_well');
}
?>
</div>
