<?php
if (!$include_flag){exit();}
// Таблица отчёта

global $group_types, 
	$toolbar, // HTML тулбара
	$table_n; // порядковый номер таблицы, если она на странице не одна

if(!isset($table_n)) {
	$table_n = 0;
} else {
	$table_n++;
}

?>
<div class="row">
	<div class="col-md-12">
		<table class="table table-striped table-bordered table-condensed dataTableT dataTableT<?php echo $table_n; ?> dataTable">
			<thead>
				<tr><th><?php echo col_name($var); ?></th><th>Переходы</th><th>Повторные</th><th>LP&nbsp;CTR</th><th>На&nbsp;оффер</th><th class="col_s">Продажи</th><th class="col_l">Лиды</th><th class="col_a">Действия</th><th class="col_s">Конверсия</th><th class="col_l">Конверсия</th><th class="col_a">Конверсия</th><th>Затраты</th><th class="col_s col_a">Прибыль</th><th class="col_s">EPC</th><th class="col_s">ROI</th><th class="col_s">CPS</th><th class="col_l">CPL</th><th class="col_a">CPA</th></tr>
			</thead>
			<tbody>
				<?php
					$column_total_data = array();
					foreach($var['arr_report_data'] as $r) {
						
						foreach($r as $k => $v) {
							$column_total_data[$k] += $v;
						}
						
						//$srch = round($r['income'] / $r['sale'], 2);
						//$cps = currencies_span(round2($r['price'] / $r['sale']));
						
						$group_link = $subtype == 'out_id' ? 'source_name' : 'out_id';
						
						//$k = param_key($r, $params['group_by']);
                		$name = param_val($r['id'], $var['group_by'], $var['filter'][0]['source_name']);
						
						/*
						
						if(trim($r['name']) == '' or $r['name'] == '{empty}') {
							$name = $group_types[$var['group_by']][1];
						} else {
							if($var['group_by'] == 'out_id') {
								$name = current(get_out_description($r['name']));
							} elseif($var['group_by'] == 'referer') {
								$name = str_replace('https://', '', $r['name']);
								$name = str_replace('http://', '', $name);
								if(substr($name, -1) == '/')
									$name = substr($name, 0, strlen($name)-1);
								
								if(substr($key, -1) == '/')
									$key = substr($key, 0, strlen($key)-1);
							} else {
								$name = $r['name'];
							}
						}
						*/
						//$name = (empty($r['name'] or $r['name'] == '{empty}') ? $group_types[$group_by][1] : $r['name']);
						
						// Ограничиваем глубину фильтров
						if(empty($var['report_params']['filter'][0]) or count($var['report_params']['filter'][0]) < 5) {
							$name = '<a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array($var['report_params']['group_by'] => _e($r['id']))))).'">' . _e($name) . '</a>';
						} else {
							$name = _e($name);
						}
						
						echo '<tr><td nowrap="">'.$name.'</td><td>'.
							$r['cnt'].'</td><td>'.
							t_repeated($r).'</td><td>'.
							t_lpctr($r).'</td><td>'.
							$r['out'].'</td><td class="col_s">'.
							$r['sale'].'</td><td class="col_l">'.
							$r['lead'].'</td><td class="col_a">'.
							$r['act'].'</td><td class="col_s">'.
							t_conversion($r).'</td><td class="col_l">'.
							t_conversion_l($r).'</td><td class="col_a">'.
							t_conversion_a($r).'</td><td>'.
							t_price($r).'</td><td class="col_s col_a">'.
							t_profit($r).'</td><td class="col_s">'.
							t_epc($r).'</td><td class="col_s">'.
							t_roi($r).'</td><td class="col_s">'.
							t_cps($r).'</td><td class="col_l">'.
							t_cpl($r).'</td><td class="col_a">'.
							t_cpa($r).'</td>'.
							'</tr>';
					}
				?>
			</tbody>
			<tfoot>
				<tr><th><strong>Итого</strong></th><?php 
					$r = $column_total_data;
					echo '<td>'.
							$r['cnt'].'</td><td>'.
							t_repeated($r).'</td><td>'.
							t_lpctr($r).'</td><td>'.
							$r['out'].'</td><td class="col_s">'.
							$r['sale'].'</td><td class="col_l">'.
							$r['lead'].'</td><td class="col_a">'.
							$r['act'].'</td><td class="col_s">'.
							t_conversion($r).'</td><td class="col_l">'.
							t_conversion_l($r).'</td><td class="col_a">'.
							t_conversion_a($r).'</td><td>'.
							t_price($r).'</td><td class="col_s col_a">'.
							t_profit($r).'</td><td class="col_s">'.
							t_epc($r).'</td><td class="col_s">'.
							t_roi($r).'</td><td class="col_s">'.
							t_cps($r).'</td><td class="col_l">'.
							t_cpl($r).'</td><td class="col_a">'.
							t_cpa($r).'</td>';
					
					?></tr>
			</tfoot>
		</table>
	</div>
</div>

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
	
    window.table<?php echo $table_n; ?> = $('.dataTableT<?php echo $table_n; ?>').dataTable
    ({    	
    	"aoColumns": [
            null, // Название
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Переходы
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Повторные
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // LP CTR
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Переходы на оффер
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Продажи
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Лиды
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Действия
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в продажи 
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в лиды
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в действия
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Затраты
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Прибыль
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // EPC
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // ROI
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // CPS
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // CPL
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }  // CPA
        ],
		"bPaginate": false,
	    "bLengthChange": false,
	    "bFilter": false,
	    "bSort": true,
	    "bInfo": false,
    	"bAutoWidth": false
	});
	
	///"bPaginate": <?php echo (count($data) > 10) ? 'true' : 'false'; ?>,
	window.table<?php echo $table_n; ?>.fnSortNeutral();
} );
</script>