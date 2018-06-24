<?php
if (!$include_flag) {exit(); }

$params = $var['report_params'];

// Кнопки типов статистики
$type_buttons = array(
	'all' => 'Все',
	'day' => 'По дням',
	'month' => 'По месяцам',
);

echo '<div class="btn-group">';
foreach($type_buttons as $k => $v) {

	$add_params = array('part' => $k);
	
	// Дефолтные параметры для переключения на дни и месяцы
	if($k == 'month') {
		$add_params['from'] = date ('Y-m-01', strtotime(get_current_day('-6 months')));
		$add_params['to']   = date ('Y-m-t',  strtotime(get_current_day()));
	} elseif($k == 'day' and $params['part'] == 'month') {
		$add_params['from'] = get_current_day('-6 days');
   		$add_params['to']   = get_current_day();
	}
	
	echo '<a href="' . report_lnk($params, $add_params).'" type="button" class="btn btn-default ' . ($params['part'] == $k ? 'active' : '') . '">' . $v . '</a>';
}
echo '</div>';
