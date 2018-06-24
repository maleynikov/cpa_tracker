<?php
if (!$include_flag){exit();}
// Таблица отчёта

$paginate = 'false'; // Постраничность в таблица. Это текстовое, а не булево значение!

if(isset($var['filter'][1]['out_id'])) {
	$out_ids = array_keys($var['arr_report_data']);
	if(isset($var['arr_report_data'][$out_ids[0]]['sub']) and count($var['arr_report_data'][$out_ids[0]]['sub']) > 50) {
		$paginate = 'true';
	}
}

//dmp($var['arr_report_data'] );;

global $group_types;
global $column_total_data;
global $table_n;

if(!isset($table_n)) {
	$table_n = 0;
} else {
	$table_n++;
}
?>
<style>
.sortdata {
	display: none;
}
tr.sub td.name {
	padding-left: 25px !important;
}
tr.sub td.name:before {
	content: '├';
	position: absolute;
	left: 8px;
}
tr.sub.last td.name:before {
	content: '└';
	position: absolute;
	left: 8px;
}
</style>
<div class="row">
	<div class="col-md-12 hidecont">
		<table class="table table-striped table-bordered table-condensed dataTableT dataTableT<?php echo $table_n; ?> dataTable">
			<thead>
				<tr><th><?php echo col_name($var); ?></th><th>Переходы</th><th>Повторные</th><th>LP&nbsp;CTR</th><th class="col_s">Продажи</th><th class="col_l">Лиды</th><th class="col_a">Действия</th><th class="col_s">Конверсия</th><th class="col_l">Конверсия</th><th class="col_a">Конверсия</th><th>Затраты</th><th class="col_s col_a">Прибыль</th><th class="col_s">EPC</th><th class="col_s">ROI</th><th class="col_s">CPS</th><th class="col_l">CPL</th><th class="col_a">CPA</th></tr>
			</thead>
			<tbody>
				<?php
					$column_total_data = array();
					foreach($var['arr_report_data'] as $r) {
						$var['r'] = $r;
						$var['class'] = '';
						$var['sub'] = 0;
						$var['parent'] = '';
						$var['pre_name'] = '';
						
						echo tpx('report_click_all_row', $var);
						
						if(!empty($r['sub'])) {
							$i = 1;
							$cnt = count($r['sub']);
							foreach($r['sub'] as $r0) {
								$var['r'] = $r0;
								$var['sub'] = 1;
								$var['class'] = 'sub';
								$var['parent'] = $r;
								
								if($cnt == $i) {
									$var['class'] .= ' last';
								}
								
								//$var['pre_name'] = ($cnt == $i) ? '└' : '├';
								
								echo tpx('report_click_all_row', $var);
								$i++;
							}
						}
					}
				?>
			</tbody>
		</table>
	</div>
</div>

<script>

$(document).ready(function() {
	$('.hidecont').show();
	
	// Конвертируем сложные для сортировки данные (валюта, проценты) в нормальные числа
	cnv2 = function(a) {
		if(a.indexOf('usd') + 1) {
			a = $('.usd', $('<div>' + a + '</div>')).text().replace('$', '');
		}
		a = a.split('%', 1);
		if (a == '') {
            a = 0;
        }
        a = parseFloat(a);
        return a;
	}
	
	// Многомерная сортировка
	srt_data = function(a, b, i, asc) {
		asc_work = (i == 3 || i == 0) ? 1 : asc; // порядок лэндинга и оффера одинаковый всегда
		maxlen = a.length;
		x = parseFloat(a[i]);
		y = parseFloat(b[i]);
		if(x < y) {
			return asc_work * -1;
		} else if(x > y) {
			return asc_work * 1;
		} else {
			if(i < maxlen - 1) {
				i++;
				return srt_data(a, b, i, asc);
			} else {
				return 0;
			}
		}
	}
	
	jQuery.fn.dataTableExt.oSort['click-data-asc'] = function(a, b) {
		if(a.indexOf('sortdata') + 1) {
			a = $('.sortdata', $('<div>' + a + '</div>')).text().split('|');
			b = $('.sortdata', $('<div>' + b + '</div>')).text().split('|');
			return srt_data(a, b, 0, 1);
		} else {
			x = cnv2(a);
			y = cnv2(b);
			return ((x < y) ? -1 : ((x > y) ? 1 : 0));
		}
    };

    jQuery.fn.dataTableExt.oSort['click-data-desc'] = function(a, b) {
    	if(a.indexOf('sortdata') + 1) {
    		a = $('.sortdata', $('<div>' + a + '</div>')).text().split('|');
			b = $('.sortdata', $('<div>' + b + '</div>')).text().split('|');
			return srt_data(a, b, 0, -1);
    	} else { 
    		x = cnv2(a);
			y = cnv2(b);
			return ((x < y) ? 1 : ((x > y) ? -1 : 0));
		}
    };
	
	window.lp_aftersort = function() { 
		$('tr.sub').removeClass('last'); 
		rows = $('#DataTables_Table_<?php echo $table_n; ?> tbody').children(); 
		for(i = 0; i < rows.length; i++) {
			// Если это конец таблицы или следующая колонка уже не подчинённая
	    	if($(rows[i]).hasClass('sub') && ((i == rows.length - 1) || !$(rows[i + 1]).hasClass('sub'))) {
	    		$(rows[i]).addClass('last');
	    	}
	    } 
	    
	    
	}
	
    window.table<?php echo $table_n; ?> = $('.dataTableT<?php echo $table_n; ?>').dataTable ({
    	"aoColumns": [
            { "bSortable": false }, // Название
            { "asSorting": [ "asc", "desc" ], "sType": "click-data" }, // Переходы
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Повторные
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // LP CTR
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Продажи
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Лиды
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Действия
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в продажи 
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в лиды 
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Конверсия в действия 
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Затраты
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // Прибыль
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // EPC
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // ROI
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // СPS
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }, // СPL
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" }  // СPA
        ],
		"bPaginate": <?php echo $paginate;?>,
	    "bLengthChange": <?php echo $paginate;?>,
	    "bFilter": false,
	    "bSort": true,
	    "bInfo": false,
    	"bAutoWidth": false,
     	"fnDrawCallback": lp_aftersort
     	 
	}).fnSort([ [1,'desc'] ]);
} );

</script>