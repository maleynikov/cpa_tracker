<?php
if (!$include_flag) {
    exit();
}

global $table_n;

if(!isset($table_n)) {
	$table_n = 0;
} else {
	$table_n++;
}

echo $table_n . '------------------------------';

$days = getDatesBetween($from, $to);

$group_by   = rq('group_by', 0, $subtype);
$limited_to = rq('limited_to');
$main_type  = $subtype;
$where      = '';


if(!empty($limited_to)) {
	$where = " and `"._str($subtype)."` = '"._str($limited_to)."'";
} else {
	//$subtype = $group_by;
}

/*
if(empty($params['limited_to'])) {
			$group_by = $params['subtype'];
		} else {
			$group_by = $params['group_by'];
			$where = " and `" . _str($params['subtype']) . "` = '" . _str($params['limited_to']) . "'";
		}
*/

if($subtype == 'out_id') {
	$id_fld = 'out_id';
} else {
	$id_fld = 'name';
}

/*
$r['is_lead'];
	cnt'];
	*/
	

// При некоторых группировках необходимо искать значения в других таблицах
$group_join = array(
	'out_id' => array('offer_name', 'tbl_offers', 'out_id', 'id') // например, название ссылки
);

$rows          = array(); // все клики за период
$data          = array(); // сгруппированные данные
$parent_clicks = array(); // массив для единичного зачёта дочерних кликов (иначе у нас LP CTR больше 100% может быть)

$q="SELECT " . (empty($group_join[$group_by]) ? mysql_real_escape_string($group_by) : 't2.' . $group_join[$group_by][0]) . " as `name`, 
	t1.id,
	t1.source_name,
	t1.out_id, 
	t1.parent_id,
	t1.click_price,
	t1.is_unique,
	t1.conversion_price_main,
	t1.is_sale,
	t1.is_lead
	FROM `tbl_clicks` t1
	" . (empty($group_join[$group_by]) ? '' : "LEFT JOIN `".$group_join[$group_by][1]."` t2 ON ".$group_join[$group_by][2]." = t2." . $group_join[$group_by][3]) . "
	WHERE t1.`date_add_day` BETWEEN '" . $from . "' AND '" . $to . "'" . $where;
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
		//$parent_clicks[$r['parent_id']] = 1;
		
		/*
		$r = $rows[$r['parent_id']];
		*/
		$k = $r[$group_by];
		$r['out'] = $out_calc;
		$r['cnt'] = 1;
	}
	$k = $r['name'];
	
	
	if($group_by == 'referer' and $r[$group_by] != '') {
		$url = parse_url($r[$group_by]);
		$k = $r['name'] = $url['host'];
	}
	
	if(!isset($data[$k])) {
		$data[$k] = array(
			'id' => $r[$id_fld],
			'name' => $r['name'],
			'price' => 0,
			'unique' => 0,
			'income' => 0,
			'sale' => 0,
			'lead' => 0,
			'out' => 0,
			'cnt' => 0,
		);
	}
	
	$data[$k]['lead']   += $r['is_lead'];
	$data[$k]['cnt']    += $r['cnt'];
	$data[$k]['price']  += $r['click_price'];
	$data[$k]['unique'] += $r['is_unique'];
	$data[$k]['income'] += $r['conversion_price_main'];
	$data[$k]['sale']   += $r['is_sale'];
	$data[$k]['out']    += $r['out'];
}

$fromF = date ('d.m.Y', strtotime($from));
$toF   = date ('d.m.Y', strtotime($to));
$value_date_range = "$fromF - $toF";

//dmp($data);
/*
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
*/
// Группировки aka разрезы

?>
<div class="row">&nbsp;</div>
<?php
	echo tpx('report_groups', $_REQUEST);
?>
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
		if(a.indexOf('usd') + 1) {
			a = $('.usd', $('<div>' + a + '</div>')).text().replace('$', '');
		}
		if(b.indexOf('usd') + 1) {
			b = $('.usd', $('<div>' + b + '</div>')).text().replace('$', '');
		}
		
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
    	if(a.indexOf('usd') + 1) {
			a = $('.usd', $('<div>' + a + '</div>')).text().replace('$', '');
		}
		if(b.indexOf('usd') + 1) {
			b = $('.usd', $('<div>' + b + '</div>')).text().replace('$', '');
		}
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
	
    window.table1 = $('.dataTableT').dataTable
    ({    	
    	"aoColumns": [
            null, // Название
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Переходы
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Повторные
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Продажи             
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в продажи 
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Затраты
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Прибыль
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // EPC	
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // ROI 
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Лиды
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в лиды 
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" }     // СPL
        ],
		"bPaginate": false,
	    "bLengthChange": false,
	    "bFilter": false,
	    "bSort": true,
	    "bInfo": false,
    	"bAutoWidth": false
	});
	
	///"bPaginate": <?php echo (count($data) > 10) ? 'true' : 'false'; ?>,
	window.table1.fnSortNeutral();
} );
</script>
<div class="row">
	<div class="col-md-12">
		<table class="table table-striped table-bordered table-condensed dataTableT dataTable">
			<thead>
				<tr><th><?php echo $group_types[$group_by][0]; ?></th><th>Переходы</th><th>Повторные</th><th class="col_s">Продажи</th><th class="col_s">Конверсия</th><th>Затраты</th><th class="col_s">Прибыль</th><th class="col_s">EPC</th><th class="col_s">ROI</th><th class="col_l">Лиды</th><th class="col_l">Конверсия</th><th class="col_l">CPL</th></tr>
			</thead>
			<tbody>
				<?php
					function currencies_span($v) {
						$rub_rate = 30;
						$style = '';
						if(empty($v)) {
							$style = 'style="color:lightgray;font-weight:normal;"';
						} elseif($v < 0) {
							$style = 'style="color:red;"';
						} 
						return '<b><span class="sdata usd" '.$style.'>'.($v < 0 ? '-' : '').'$'.abs($v).'</span><span class="sdata rub" '.$style.'>'.round($v*$rub_rate).'р.</span></b>';
					}
	
					foreach($data as $r) {
						
						// Округление
						$r['price'] = round($r['price'], 2);
						$r['income'] = round($r['income'], 2);
						
						$epc = currencies_span(round2($r['income'] / $r['cnt']));
						$profit = $r['income'] - $r['price'];
						$roi = round($profit / $r['price'] * 100, 1);
						$conversion = round2($r['sale'] / $r['cnt'] * 100);
						$conversion_l = round2($r['lead'] / $r['cnt'] * 100);
						$follow = round($r['out'] / $r['cnt'] * 100, 1);
						$srch = round($r['income'] / $r['sale'], 2);
						$cps = round($r['price'] / $r['sale'], 2);
						$cpl = round($r['price'] / $r['lead'], 2);
						
						$price = currencies_span($r['price']);
						$profit = currencies_span($profit);
						
						$repeated = $r['cnt'] - $r['unique'];
						if($repeated < 0) $repeated = 0;
						$repeated = round($repeated / $r['cnt']  * 100, 1);
						
						$group_link = $subtype == 'out_id' ? 'source_name' : 'out_id';
						
						$name = (empty($r['name']) ? $group_types[$group_by][1] : $r['name']);
						
						if(empty($limited_to)) {
							$name = '<a href="?act=reports&type='._e($type).'&subtype='._e($main_type).'&limited_to='.$r['id'].'&group_by='.$group_link.'">'.$name.'</a>';
						}
						
						echo '<tr><td nowrap="">'.$name.'</td><td>'.intval($r['cnt']).'</td><td>'.$repeated.'%</td><td class="col_s">'.$r['sale'].'</td><td class="col_s">'.$conversion.'%</td><td>'.$price.'</td><td class="col_s">'.$profit.'</td><td class="col_s">'.$epc.'</td><td class="col_s">'.$roi.'%</td><td class="col_l">'.$r['lead'].'</td><td class="col_l">'.$conversion_l.'%</td><td class="col_l">'.$cpl.'</td></tr>';
					}
				?>
			</tbody>
		</table>
	</div>
</div>
					
<div id="report_toolbar" class="row">
<div class="col-md-12">
<div class="form-group">
	  <div id="rt_sale_section" class="btn-group" data-toggle="buttons">
<label class="btn btn-default active" onclick="update_cols('sales');">
<input type="radio" name="option_leads_type">
Продажи
</label>
<label class="btn btn-default" onclick="update_cols('leads');">
<input type="radio" name="option_leads_type">
Лиды
</label>
</div>
	
	<div id="rt_currency_section" class="btn-group invisible" data-toggle="buttons">
<label class="btn btn-default" onclick="update_cols('currency_rub');">
<input type="radio" name="option_currency">
<i class="fa fa-rub"></i>
</label>
<label class="btn btn-default active" onclick="update_cols('currency_usd');">
<input type="radio" name="option_currency">
$
</label>
</div>
	<script>update_cols('sales'); update_cols('currency_usd');</script>
	</div>
</div>
</div>