<?php
if (!$include_flag){exit();}
// Таблица отчёта

//dmp($var);

global $group_types, 
	$toolbar, // HTML тулбара
	$table_n; // порядковый номер таблицы, если она на странице не одна

if(!isset($table_n)) {
	$table_n = 0;
} else {
	$table_n++;
}

if($var['report_params']['group_by'] != '')  {
	$var['arr_report_data'] = current($var['arr_report_data']);
}
?>
<div class="row">
	&nbsp;
</div>
<div class="row">
	<div class="col-md-12">
		<table class="table table-striped table-bordered table-condensed dataTableT dataTableT<?php echo $table_n; ?> dataTable">
			<thead>
				<tr><th><?php if($var['report_params']['group_by'] == '') { ?>Название</th><th>Значение<?php } else { echo $group_types[$var['report_params']['group_by']][0]; } ?></th><th>Переходы</th><th>От&nbsp;общего</th><?php if($var['report_params']['conv'] != 'none') { ?><th class="col_s">Продажи</th><th class="col_l">Лиды</th><th class="col_a">Действия</th><th class="col_s">Конверсия</th><th class="col_l">Конверсия</th><th class="col_a">Конверсия</th><?php } ?><th>Затраты</th><?php if($var['report_params']['conv'] != 'none') { ?><th class="col_s col_a">Прибыль</th><th>EPC</th><th class="col_s col_a">ROI</th><th class="col_s">CPS</th><th class="col_l">CPL</th><th class="col_a">CPA</th><?php } ?></tr>
			</thead>
			<tbody>
				<?php
					
					$column_total_data = array();
					foreach($var['arr_report_data'] as $k => $r) {
						
						if($var['report_params']['group_by'] == '')  {
							
							// Если популярное значение "Не определено"
							//if($r['popular'] == $group_types[$r['name']][1]) continue;
							
							// Если популярное значение отфильтровано
							//if(!empty($var['report_params']['filter'][$r['name']])) continue;
							
							//echo $var['report_params']['conv']. '<br >';
							//dmp($r);
							
							// Если конверсия по действию, а действий нет
							if(in_array($var['report_params']['conv'], array('act', 'sale', 'lead')) and $r[$var['report_params']['conv']] == 0) continue;
							
							$name = param_name($r['name'], $var['report_params']['filter'][0]['source_name']);
							
							$popular_str = substr(param_val($r['popular'], $k), 0, 50);
							
							// Ограничиваем глубину фильтров
							if(empty($var['report_params']['filter'][0]) or count($var['report_params']['filter'][0]) < 5) {
								$r['popular'] = '<a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array($r['name'] => _e($r['popular']))))).'" title="' . _e($r['popular']) . '">' . _e($popular_str) . '</a>';
							} else {
								$r['popular'] = _e($popular_str);
							}
							
							echo '<tr><td nowrap=""><b><a href="' . report_lnk($var['report_params'], array('mode' => '', 'group_by' => $r['name'])) . '">' . $name . '</a></b></td><td>' . $r['popular'] . '</td>';
						} else {
							echo '<tr><td nowrap=""><a href="' . report_lnk($var['report_params'], array('group_by' => '', 'filter_str' => array_merge($var['report_params']['filter_str'], array($var['report_params']['group_by'] => _e($k))))) . '">' . $k . '</a></td>';
						}
						
						echo '<td>' . $r['cnt'] . '</td><td>' . round3($r['cnt'] / $r['total'] * 100) . '%</td>';
						
						if($var['report_params']['conv'] != 'none') {
							echo '<td class="col_s">'.
							$r['sale'].'</td><td class="col_l">'.
							$r['lead'].'</td><td class="col_a">'.
							$r['act'].'</td><td class="col_s">'.
							t_conversion($r).'</td><td class="col_l">'.
							t_conversion_l($r).'</td><td class="col_a">'.
							t_conversion_a($r).'</td>';
						}
						
						echo '<td>' . t_price($r).'</td>';

						if($var['report_params']['conv'] != 'none') {
							echo '<td class="col_s col_a">'.
								t_profit($r).'</td><td>'.
								t_epc($r).'</td><td class="col_s col_a">'.
								t_roi($r).'</td><td class="col_s">'.
								t_cps($r).'</td><td class="col_l">'.
								t_cpl($r).'</td><td class="col_a">'.
								t_cpa($r).'</td>';
						}
						echo '</tr>';
					}
				?>
			</tbody>
		</table>
	</div>
</div>

<script>
$(document).ready(function() {
	jQuery.fn.dataTableExt.oApi.fnSortNeutral = function ( oSettings ){
		oSettings.aaSorting = [[<?php if($var['report_params']['group_by'] == '') {?>2<?php } else { ?>1<?php } ?>, "desc", 0]];
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
            <?php if($var['report_params']['group_by'] == '') {?>null, <?php } ?>
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Переходы
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // От общего
            <?php if($var['report_params']['conv'] != 'none') { ?>
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Продажи
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Лиды
            { "asSorting": [ "desc", "asc" ], "sType": "numeric" },    // Действия
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в продажи 
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в лиды 
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в действия 
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Затраты
            <?php } ?>
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" } // Прибыль
            <?php if($var['report_params']['conv'] != 'none') { ?>
            ,{ "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // EPC	
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // ROI
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // CPS
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // CPL
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }  // CPA
            <?php } ?>
        ],
		"bPaginate": false,
	    "bLengthChange": false,
	    "bFilter": false,
	    "bSort": true,
	    "bInfo": false,
    	"bAutoWidth": false
	});
	window.table<?php echo $table_n; ?>.fnSortNeutral();
} );
</script>