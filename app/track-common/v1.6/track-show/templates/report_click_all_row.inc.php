<?php
if (!$include_flag){exit();}
// Таблица отчёта

global $group_types;
global $column_total_data, $source_config;

$r = $var['r'];

foreach($r as $k => $v) {
	if(is_array($v)) continue;
	$column_total_data[$k] += $v;
}

//dmp($var);

$group_by_fld = $var['sub'] ? 'subgroup_by' : 'group_by';
$group_by = $var[$group_by_fld];
//$name = 

$group_link = $subtype == 'out_id' ? 'source_name' : 'out_id';

/*
if(trim($r['name']) == '' or $r['name'] == '{empty}') {
	$name = $group_types[$group_by][1];
} else {
	if($group_by == 'out_id') {
		$name = current(get_out_description($r['name']));
	} elseif($group_by == 'referer') {
		$name = str_replace('https://', '', $r['name']);
		$name = str_replace('http://', '', $name);
		if(substr($name, -1) == '/')
			$name = substr($name, 0, strlen($name)-1);
		
		if(substr($key, -1) == '/')
			$key = substr($key, 0, strlen($key)-1);
	} elseif($group_by == 'source_name') {
		$name = empty($source_config[$r['name']]) ? $r['name'] : $source_config[$r['name']]['name'];
	} else {
		$name = $r['name'];
	}
}
*/
$name = $r['name'];

//$name = (empty($r['name'] or $r['name'] == '{empty}') ? $group_types[$group_by][1] : $r['name']);

// Ограничиваем глубину фильтров

//dmp($var);

if(empty($var['filter'][1]) or 1) {
	if($r['id'] == -1) { // Ссылка "Другие"
		$name = '<a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array($var['report_params']['group_by'] => _e($var['parent']['id']).'|0:1')))).'">' . _e($name) . '</a>';
	} else {
		$name = '<a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array($var['report_params'][$group_by_fld] => _e($r['id']).'|'.(empty($var['parent']) ? '0' : $var['parent']['id']).':1')))).'">' . _e($name) . '</a>';
	}
} else {
	$name = _e($name);
}

//$name = _e($name);

if($var['class'] != '') {
	$name = $var['pre_name'] . ' ' . $name;
}

//dmp($var);

echo '<tr class="'.$var['class'].'"><td nowrap="" class="name">'.$name.'</td><td>'.
	sortdata('cnt', $var) .'</td><td>'.
	sortdata('repeated', $var).'</td><td>'.
	sortdata('lpctr', $var).'</td><td class="col_s">'.
	sortdata('sale', $var).'</td><td class="col_l">'.
	sortdata('lead', $var).'</td><td class="col_a">'.
	sortdata('act', $var).'</td><td class="col_s">'.
	sortdata('conversion', $var).'</td><td class="col_l">'.
	sortdata('conversion_l', $var).'</td><td class="col_a">'.
	sortdata('conversion_a', $var).'</td><td>'.
	sortdata('price', $var).'</td><td class="col_s col_a">'.
	sortdata('profit', $var).'</td><td class="col_s">'.
	sortdata('epc', $var).'</td><td class="col_s">'.
	sortdata('roi', $var).'</td><td class="col_s">'.
	sortdata('cps', $var).'</td><td class="col_l">'.
	sortdata('cpl', $var).'</td><td class="col_a">'.
	sortdata('cpa', $var).'</td></tr>';