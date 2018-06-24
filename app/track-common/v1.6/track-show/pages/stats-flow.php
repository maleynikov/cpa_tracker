<?php if (!$include_flag){exit();} ?>
<script src="<?php echo _HTML_TEMPLATE_PATH;?>/js/report_toolbar.js"></script>
<style>
.sortdata {
    display: none;
}
</style>
<?php
	$date = rq('date', 4, get_current_day());
	$hour = rq('hour', 2);
	$prev_date = date('Y-m-d', strtotime('-1 days', strtotime($date)));
	$next_date = date('Y-m-d', strtotime('+1 days', strtotime($date)));
	
	// Кнопки панели управления
	$group_actions = array(
		'act'   => array('cnt_act', 'conversion_a', 'roi', 'epc', 'profit'),
		'sale'  => array('cnt_sale', 'conversion',   'roi', 'epc', 'profit'),
		'lead'  => array('cnt_lead', 'conversion_l', 'cpl')
	);

	$main_type   = rq('report_type', 0, 'source_name');
	$limited_to  = '';
	
	$params = array(
		'type'     => 'basic',
		'part'     => 'hour',
		'filter'   => array(),
		'group_by' => $main_type,
		'subgroup_by' => $main_type,
		'conv'     => 'all',
		'mode'     => '',
		'col'      => 'sale_lead',
		'from'     => $date,
		'to'       => $date,
	);
		
	$arr_report_data = get_clicks_report_grouped2($params); 
	
	/********/
	
	
	$arr_hourly = array();

	foreach ($arr_report_data['data'] as $row_name => $row_data) {
		foreach ($row_data as $cur_hour => $data) {
			$arr_hourly[$row_name][$cur_hour] = get_clicks_report_element2 ($data, true, false, $group_actions);
		}
	}

	echo "<div class='row'>";
	echo "<div class='col-md-12'>";
	echo "<p align=center>";
	if ($date != get_current_day()) {
		echo "<a style='float:right;' href='?date={$next_date}&report_type={$main_type}'>".mysqldate2string($next_date)." &rarr;</a>";
	} else {
		echo "<a style='float:right; visibility:hidden;' href='?date={$next_date}&report_type={$main_type}'>".mysqldate2string($next_date)." &rarr;</a>";
	}
	echo "<b>".mysqldate2string($date)."</b>";
	echo "<a style='float:left;' href='?date={$prev_date}&report_type={$main_type}'>&larr; ".mysqldate2string($prev_date)."</a></p>";


	echo "<table class='table table-striped table-bordered table-condensed'>";
		echo "<tbody>";	
			echo "<tr>";
			echo "<td>";
				echo "<div class='btn-group'>";
				switch ($main_type)
				{
					case 'out_id': 
						echo "<button class='btn btn-link dropdown-toggle' data-toggle='dropdown' style='padding:0; color:black; font-weight: bold;'>Оффер <span class='caret'></span></button>
							  <ul class='dropdown-menu'>
							    <li><a href='?date={$date}&report_type=source_name'>Источник</a></li>
							  </ul>";
					break;
					
					default: 
						echo "<button class='btn btn-link dropdown-toggle' data-toggle='dropdown' style='padding:0; color:black; font-weight: bold;'>Источник <span class='caret'></span></button>
							  <ul class='dropdown-menu'>
							    <li><a href='?date={$date}&report_type=out_id'>Оффер</a></li>
							  </ul>";
					break;
				}
				 echo "</div>";			
			echo "</td>";			
			for ($i=0;$i<24; $i++)
			{
				echo "<td>".sprintf('%02d', $i)."</td>";
			}
			echo "</tr>";		
			echo "<tr>";
			
			foreach ($arr_hourly as $source_name=>$data)
			{
				echo "<td>"._e(param_val($source_name, $main_type))."</td>";
				
				if($main_type == 'source_name') {
					$source_name_lnk = param_key($source_name, 'source_name');
				} else {
					$source_name_lnk = '';
				}
				
				/*
				switch ($main_type)
				{
					case 'out_id': 
						$source_name=current(get_out_description($source_name));
						if ($source_name=='' || $source_name=='{empty}'){$source_name_show='Без оффера';}
						echo "<td>"._e($source_name)."</td>";	
					break;
					
					default: 
						if ($source_name=='' || $source_name=='{empty}') { 
							$source_name = 'Без&nbsp;источника'; $source_name_lnk = ''; 
						} else { 
							$source_name_lnk = $source_name;
							$source_name = empty($source_config[$source_name]['name']) ? $source_name : $source_config[$source_name]['name'];
						}
						echo "<td>"._e($source_name)."</td>";	
					break;
				}
				*/
				for ($i=0;$i<24; $i++)
				{
					$i2 = sprintf('%02d', $i);
					if ($data[$i2] != '')
					{
						echo "<td><a style='text-decoration:none; color:black;' href='?filter_by=hour&source_name="._e($source_name_lnk)."&date=$date&hour=$i'>{$data[$i2]}</a></td>";	
					}
					else
					{
						echo "<td></td>";
					}
				}
				echo "</tr>";
			}
		echo "</tbody>";
	echo "</table>";
echo "</div> <!-- ./col-md-12 -->";	
echo "</div> <!-- ./row -->";
// **********************************************

$panels = array(
	'act'  => 'Все действия',
	'sale' => 'Продажи',
	'lead' => 'Лиды'
);

?>

<div class="row" id='report_toolbar'>
	<div class="col-md-12">
		<div class="form-group" >
			<div class="invisible pull-left" id="rt_type_section">
				<?php
	
			$i = 0;
    		foreach($group_actions as $group => $actions) {
    			echo '<div class="btn-group rt_types rt_type_'.$group.'" data-toggle="buttons" style="'.($i > 0 ? 'display: none' : '').'">';
    			foreach($actions as $action) {
    				echo '<label class="btn btn-default '.($i == 0 ? 'active' : '').'" onclick="update_stats2(\''.$action.'\', '.($report_cols[$action]['money'] == 1 ? 'true' : 'false' ).');"><input type="radio" name="option_report_type">'.$report_cols[$action]['name'].'</label>';
    			$i++;
    			}
    			
    			echo '</div>';
    		}
    		
    		if(!empty($panels)) {
        		echo '<div class="btn-group margin5rb" id="rt_sale_section" data-toggle="buttons">'; // data-toggle="buttons"
        		
        		$i = 0;
        		foreach($panels as $value => $name) {
        			echo '<label class="btn btn-default '.($i == 0 ? 'active' : '').'" onclick="show_conv_mode(\''.$value.'\')"><input type="radio" name="option_leads_type">' . $name . '</label>';
        			$i++;
        		}
        		
        		
        		// Все действия, продажи, лиды !!!!!!!!!!!!!!!
        		/*
				foreach($option_leads_type as $k => $v) {
					$new_params = array('col' => $k);
					if(in_array($params['conv'], array('sale', 'lead', 'act'))) {
						$new_params['conv'] = $k;
					}
					//dmp($new_params);
  					echo '<a class="btn btn-default'.($col == $k ? ' active' : '').'" href="'.report_lnk($params, $new_params).'">' . $v . '</a>';
  				}
        		*/
        		
        		echo '</div>';
    		}
				
	?>
				<div class="btn-group pull-right margin5rb" id="rt_currency_section" data-toggle="buttons" style="display: none">
	            <?php
						// Переключение валют
						foreach($option_currency as $k => $v) {
		  					echo '<label class="btn btn-default '.($currency == $k ? ' active' : '').'" onclick="show_currency(\''.$k.'\');">
						<input type="radio" name="option_currency">' . $v . '
					</label>';
		  				}
					?>
	            </div>
			</div>
			<div class="btn-group pull-right">
				<button type="button" class="btn btn-default" title="Параметры отчета" onclick='toggle_report_toolbar()'><i class='fa fa-cog'></i></button>
			</div>		
		</div>
	</div> <!-- ./col-md-12 -->
</div> <!-- ./row -->

<input type='hidden' id='usd_selected' value='1'>
<input type='hidden' id='type_selected' value='clicks'>
<input type='hidden' id='sales_selected' value='0'>


<?php
// ********************************************************

if(!empty($arr_data)) {

	echo "<h4>Лента переходов за ".sdate($date).'<span style="float:right;">'."<a title='Экспорт в Excel' href='?csrfkey="._e(CSRF_KEY)."&ajax_act=excel_export&date="._e($date)."'><img src='"._HTML_TEMPLATE_PATH."/img/icons/table-excel.png'></a></span><span style='float:right; margin-right:16px;'><a title='Экспорт в TSV' href='?csrfkey="._e(CSRF_KEY)."&ajax_act=tsv_export&date="._e($date)."'><img src='"._HTML_TEMPLATE_PATH."/img/icons/table-tsv.png'></a></span>".'<div class="col-xs-4" style="float: right; margin-bottom: 7px;"><form action="" method="get"><input type="hidden" name="filter_by" value="search"/><input type="hidden" name="date" value="'.$date.'"/><input name="search" class="form-control" " type="text" value="'._e($search).'" placeholder="поиск" /></form></div>'."</h4>";

	echo "<table class='table table-striped' id='stats-flow'><thead>
			<tr><th></th><th></th><th>Ссылка</th><th>Источник</th><th>Кампания</th><th colspan=\"6\">Реферер</th><th></th></tr>
		</thead>";
	echo "<tbody>";
	foreach ($arr_data as $row) {
		require _TRACK_SHOW_COMMON_PATH . '/pages/stats-flow-row.php';
	}
	echo "</tbody></table>";
	if($total > 20) {
		echo '<a href="#" onclick="return load_flow(this)" class="center-block text-center">Показать больше</a>';
		
		?>
<script type="text/javascript">
	function load_flow(obj) {
		$.post(
            'index.php?ajax_act=a_load_flow', {
                offset: $('#stats-flow tbody').children().length / 2 ,
                date: '<?php echo _str($date) ?>',
                hour: '<?php echo _str($hour) ?>',
                filter_by: '<?php echo _str($_REQUEST['filter_by']) ?>',
                value: '<?php echo _str($_REQUEST['value']) ?>',
                source_name: '<?php echo _str($_REQUEST['source_name']) ?>'
            }
        ).done(
        	function(data) {
        		if(data == '') {
        			$(obj).hide();
        		} else {
            		$('#stats-flow tbody').children().last().after(data);
            	}
            }
        ); 
		return false;
	}	
</script><?php 
	}
}
?>
<script>
show_conv_mode('act', 0);
update_stats2('cnt_act', false); 
show_currency('usd');
</script>