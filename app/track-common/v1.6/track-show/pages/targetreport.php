<?php
if (!$include_flag) {
    exit();
}
$days = getDatesBetween($from, $to);

$group_by   = rq('group_by', 0, 'out_id');
$limited_to = rq('limited_to', 2);

// При некоторых группировках необходимо искать значения в других таблицах
$group_join = array(
	'out_id' => array('offer_name', 'tbl_offers', 'out_id', 'id') // например, название ссылки
);

$rows          = array(); // все клики за период
$data          = array(); // сгруппированные данные
$parent_clicks = array(); // массив для единичного зачёта дочерних кликов (иначе у нас LP CTR больше 100% может быть)

$q="SELECT " . (empty($group_join[$group_by]) ? mysql_real_escape_string($group_by) : 't2.' . $group_join[$group_by][0]) . " as `name`, t1.*
	FROM `tbl_clicks` t1
	" . (empty($group_join[$group_by]) ? '' : "LEFT JOIN `".$group_join[$group_by][1]."` t2 ON ".$group_join[$group_by][2]." = t2." . $group_join[$group_by][3]) . "
	WHERE t1.`date_add_day` BETWEEN '" . $from . "' AND '" . $to . "'";
$rs = mysql_query($q) or die(mysql_error());
while($r = mysql_fetch_assoc($rs)) {
	$rows[$r['id']] = $r;
}



foreach($rows as $id => &$r) {
	// Если группировка по рефереру - обрезаем до домена
	if($r['parent_id'] == 0) {
		$k = $r[$group_by];
		$r['out'] = 0;
		$r['cnt'] = 1;
	} else { // подчинённая ссылка
		// не будем считать более одного исходящего с лэндинга
		$out_calc = isset($parent_clicks[$r['parent_id']]) ? 0 : 1;
		$parent_clicks[$r['parent_id']] = 1;
		
		$r = $rows[$r['parent_id']];
		$k = $r[$group_by];
		
		$r['out'] = $out_calc;
		$r['cnt'] = 0;
	}
	
	// Если запрошен отчет по конкретной ссылке - отсеиваем остальные
	if($limited_to and $r['out_id'] != $limited_to) continue;
	
	if($group_by == 'referer' and $r[$group_by] != '') {
		$url = parse_url($r[$group_by]);
		$k = $r['name'] = $url['host'];
	}
	
	if(!isset($data[$k])) {
		$data[$k] = array(
			'id' => $r['out_id'],
			'name' => $r['name'],
			'price' => 0,
			'unique' => 0,
			'income' => 0,
			'sale' => 0,
			'out' => 0,
			'cnt' => 0,
		);
	}
	
	$data[$k]['cnt'] += $r['cnt'];
	$data[$k]['price'] += $r['click_price'];
	$data[$k]['unique'] += $r['is_unique'];
	$data[$k]['income'] += $r['conversion_price_main'];
	$data[$k]['sale'] += $r['is_sale'];
	$data[$k]['out'] += $r['out'];
}


$fromF = date ('d.m.Y', strtotime($from));
$toF   = date ('d.m.Y', strtotime($to));
$value_date_range = "$fromF - $toF";

if($limited_to) {
	$report_name = 'Переходы на ' . current(get_out_description($limited_to)) . ' за';
	$report_name_tag = 'h5';
} else {
	$report_name = 'Переходы на целевые страницы за';
	$report_name_tag = 'h3';
}

// Выбор даты
echo '<form method="post"  name="datachangeform" id="range_form">
        <div id="per_day_range" class="pull-right" style="margin-top:0px; margin-bottom:10px;">
            <span class="glyphicon glyphicon-calendar"></span>
            <span id="cur_day_range">'.date('d.m.Y', strtotime($from)).' - '. date('d.m.Y', strtotime($to)).'</span> <b class="caret"></b>
            <input type="hidden" name="from" id="sStart" value="">
            <input type="hidden" name="to" id="sEnd" value="">
        </div>
        
        <div><'.$report_name_tag.'>'._e($report_name).'</'.$report_name_tag.'></div>
      </form>';

// Группировки aka разрезы
if($limited_to) {
?>
<div class="row">&nbsp;</div>
<div class='row report_grouped_menu'>
<div class='col-md-12'>

	<div class="btn-group">
		<?php if ($group_by=='campaign_name'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=campaign_name&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>" >Кампания</a>

		<?php if ($group_by=='ads_name'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=ads_name&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Объявление</a>

		<?php if ($group_by=='referer'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=referer&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Площадка</a>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Гео
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=country&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Страна</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=city&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Город</a></li>
                        <li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=ip&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">IP-адрес</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=region&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Регион</a></li>			
			<li class="divider"></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=isp&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Провайдер</a></li>			
			</ul>
		</div>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Устройство
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=user_os&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">ОС</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=user_platform&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Платформа</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=user_browser&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Браузер</a></li>
			</ul>
		</div>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Другие параметры
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=campaign_param1&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #1</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=campaign_param2&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #2</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=campaign_param3&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #3</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=campaign_param4&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #4</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=campaign_param5&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #5</a></li>
			<li class="divider"></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value1&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #1</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value2&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #2</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value3&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #3</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value4&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #4</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value5&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #5</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value6&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #6</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value7&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #7</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value8&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #8</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value9&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #9</a></li>
			<li><a href="?act=reports&type=targetreport&subtype=<?php echo _e($main_type);?>&group_by=click_param_value10&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #10</a></li>
			</ul>
		</div>
	</div>

</div> <!-- ./col-md-12 -->
</div> <!-- ./row -->
<div class="row">&nbsp;</div>
<?php } ?>
<script>
$(document).ready(function() {
	jQuery.fn.dataTableExt.oApi.fnSortNeutral = function ( oSettings ){
		oSettings.aaSorting = [[1, "desc", 0]];
		oSettings.aiDisplay.sort( function (x,y) {
		    return x-y;
		} );
		oSettings.aiDisplayMaster.sort( function (x,y) {
		    return x-y;
		} );
		oSettings.oApi._fnReDraw( oSettings );
	};
	
	jQuery.fn.dataTableExt.oSort['click-data-asc'] = function(a, b) {
        x = a.split('%', 1);
        y = b.split('%', 1);

        if (x == '') {
            x = 0;
        }
        if (y == '') {
            y = 0;
        }
        x = parseFloat(x);
        y = parseFloat(y);

        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    };

    jQuery.fn.dataTableExt.oSort['click-data-desc'] = function(a, b)
    {
		x = a.split('%', 1);
        y = b.split('%', 1);
        if (x == '') {
            x = 0;
        }
        if (y == '') {
            y = 0;
        }
        x = parseFloat(x);
        y = parseFloat(y);
        return ((x < y) ? 1 : ((x > y) ? -1 : 0));
    };
	
    var table = $('.dataTableT').dataTable
    ({    	
    	"aoColumns": [
            null,
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },
            <?php if($limited_to > 0) { ?>{ "asSorting": [ "desc", "asc" ], "sType": "click-data" },<?php } ?>
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" },
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" },
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }
        ],
		"bPaginate": <?php echo (count($data) > 10) ? 'true' : 'false'; ?>,
	    "bLengthChange": false,
	    "bFilter": false,
	    "bSort": true,
	    "bInfo": false,
    	"bAutoWidth": false
	});
		
	table.fnSortNeutral();
} );
</script>	
<div class="row">
	<div class="col-md-12">
		<table class="table table-striped table-bordered table-condensed dataTableT" style="width:600px;">
			<thead>
				<tr><th>Страница</th><th>Переходы</th><?php if($limited_to > 0) {?><th>Повторные</th><?php } ?><th>LP&nbsp;CTR</th><th>Ушло на офферы</th><th>Продаж</th><th>Конверсия</th><th>Доход</th><th>Затраты</th><th>Прибыль</th><th>ROI</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($data as $r) {
						if(!$limited_to and !$r['out']) continue;
						
						$profit = $r['income'] - $r['price'];
						$roi = round($profit / $r['price'] * 100, 1);
						$conversion = round2($r['sale'] / $r['cnt'] * 100);
						$follow = round($r['out'] / $r['cnt'] * 100, 1);
						$repeated = $r['cnt'] - $r['unique'];
						if($repeated < 0) $repeated = 0;
						$repeated = round($repeated / $r['cnt']  * 100, 1);
						
						echo '<tr><td nowrap=""><a href="?act=reports&type=targetreport&subtype=daily&limited_to='.$r['id'].'&group_by=campaign_name">'.$r['name'].'</a></td><td>'.$r['cnt'].'</td>
'.($limited_to > 0 ? '<td>'.$repeated.'%</td>' : '').'<td>'.$follow.'%</td><td>'.$r['out'].'</td><td>'.$r['sale'].'</td><td>'.$conversion.'%</td><td>'.$r['income'].'</td><td>'.$r['price'].'</td><td>'.$profit.'</td><td>'.$roi.'%</td></tr>';
					}
				?>
			</tbody>
		</table>
	</div>
</div>