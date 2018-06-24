<?php
$r = $var['r'];
global $row_total_data, $column_total_data, $arr_sparkline, $sparkline;

$group_by_fld = $var['sub'] ? 'subgroup_by' : 'group_by';
$group_by = $var[$group_by_fld];

// Первая колонка, название

/*
if ($r['name'] == '{empty}' or trim($r['name']) == '') {
	$name = $group_types[$var[$group_by_fld]][1];
} else {
	if($var[$group_by_fld] == 'out_id') {
		//$source_name_full = $source_name;
		$name = current(get_out_description($r['name']));
	} elseif($var[$group_by_fld] == 'referer') {
		$name = str_replace('https://', '', $r['name']);
		$name = str_replace('http://', '', $name);
		if(substr($name, -1) == '/')
			$name = substr($name, 0, strlen($name)-1);
		
		if(substr($key, -1) == '/')
			$key = substr($key, 0, strlen($key)-1);
	} else {
		
	}
}*/

$name = $r['name'];

// Ограничиваем глубину фильтров
if(empty($var['filter'][1]) or 1) {
	$name = '<a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array($var['report_params'][$group_by_fld] => _e($r['id']).'|'.(empty($var['parent']) ? '0' : $var['parent']['id']).':1')))).'">' . _e($name) . '</a>';
} else {
	$name = _e($name);
}

echo '<tr class="'.$var['class'].'"><td class="name"><table class="sparktable"><tr><td> ' . $name . "</td><td><span style='float:right; margin-left:10px;'><div id='sparkline_".$sparkline."'></div></span></td></tr></table></td>";
//echo '<tr class="'.$var['class'].'"><td class="name"> ' . $name . "<span style='float:right; margin-left:10px; position: absolute'><div id='sparkline_".$sparkline."'></div></span></td>";

// Следующие колонки, данные

//dmp($r);

foreach ($var['arr_dates'] as $cur_date) {
	$row = $r[$cur_date];
	
	// Преобразования для дневной статистики
	$var_sub = $var; 
	if($var_sub['parent'] != '') $var_sub['parent'] = $var['parent'][$cur_date];
	
	$row['order'] = $r['order'];
	$row['ln']    = $r['ln'];
	$var_sub['r'] = $row;

	stat_inc_total($cur_date, $row);
	
	$arr_sparkline[$sparkline][] = $row['cnt'] + 0;
	
	echo '<td>' . get_clicks_report_element2 ($var_sub) . '</td>';
}

// Колонка Итого

$var_sub = $var;
$var_sub['r'] = $row_total_data;

echo '<td>'.get_clicks_report_element2($var_sub, false).'</td></tr>';
?>		