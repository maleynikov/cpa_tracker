<?php if (!$include_flag){exit();} ?>
<script src="<?php echo _HTML_TEMPLATE_PATH;?>/js/report_toolbar.js"></script>
<?php

// Create dates array for reports
$date1      = date('Y-m-d', strtotime('-6 days', strtotime(date('Y-m-d'))));
$date2      = date('Y-m-d');
$arr_dates  = getDatesBetween($date1, $date2);

$conv       = rq('conv');
$type       = rq('type', 0, 'daily_stats');
$subtype    = rq('subtype'); // XSS ОПАСНО!!!
//$mode       = rq('mode');
$limited_to = rq('limited_to');
$group_by   = rq('group_by', 0, $subtype);
$part       = rq('part', 0, 'all');

$from       = rq('from', 4, '');
$to         = rq('to', 4, '');


// Нижние кнопки 
$currency = rq('currency', 0, 'usd');
$col      = rq('col', 0, 'act');

if($params['conv'] == 'lead') {
	$col == 'leads';
};
	
$option_leads_type = array(
	'act'  => 'Все действия',
	'sale' => 'Продажи',
	'lead' => 'Лиды'
);

// Проверяем на соответствие существующим типам

if(empty($option_leads_type[$col])) 
	$col = 'act';

if(empty($option_currency[$currency])) 
	$currency = 'usd';

if($part == 'all') { ?><style><?php
	switch($col) {
		case 'act':
			echo '.col_s:not(.col_a) {display: none;} .col_l:not(.col_a) {display: none;} ';
			break;
		case 'sale':
			echo '.col_a:not(.col_s) {display: none;} .col_l:not(.col_s) {display: none;}';
			break;
		case 'lead':
			echo '.col_a:not(.col_l) {display: none;} .col_s:not(.col_l) {display: none;}';
			break;
	}
	?></style>
<?php } 

// ---------------------------------------

switch ($_REQUEST['type']) {
	case 'basic':
	
	// Параметры отчёта
	$params = report_options();
	
	//$params['where'] = "`is_connected` = '0'"; // только лэндинги
	//$params['mode'] = 'lp';
	
	if($params['mode'] == 'popular') {
		$params['mode'] = 'popular';
		$assign['report_name'] = 'Популярные параметры за ';
		$assign['report_params'] = $params;
		$assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');
		
		$report = get_clicks_report_grouped2($params);
		
		$assign['click_params'] = $report['click_params'];
		$assign['arr_report_data'] = $report['data'];
		$assign['arr_dates'] = $report['dates'];
		
		// Заголовок отчета
		echo tpx('report_name', $assign);

		// Фильтры конвертации
		//echo tpx('report_conv', $assign);
		
		// Фильтры
		echo tpx('report_groups', $assign);
		
		// Таблица отчета
		echo tpx('report_table', $assign);
		
	} elseif($params['mode'] == 'lp' or $params['mode'] == 'lp_offers') { 
		
		$group_types['out_id'][0] = 'Целевая страница';
		$params['mode'] = 'lp_offers';
		
		$assign = $params;
		$assign['report_params'] = $params;
		
		$assign['report_name'] = 'Целевые страницы за ';
		$report = get_clicks_report_grouped2($params);
		
		$assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');
		
		$assign['arr_report_data'] = $report['data'];
		$assign['click_params'] = $report['click_params'];
		$assign['arr_dates'] = $report['dates'];
		
		// Заголовок отчета
		echo tpx('report_name', $assign);
		
		// Фильтры
		//echo tpx('report_conv', $assign);
		
		if(!empty($report['data'])) {
			// Фильтры
			echo tpx('report_groups_lp', $assign);
			
			// Таблица отчета
			echo tpx('report_table', $assign);
		}
		/*
		if(!empty($report['data'])) {
		
			
			
			// Таблица отчета
			echo tpx('report_table', $assign);
			
			// Целевые страницы с подчинненными офферами
			if($part == 'all') {
				$params['mode'] = 'lp_offers';
				$assign['report_params'] = $params;
				$report = get_clicks_report_grouped2($params);
				$assign['arr_report_data'] = $report['data'];
				
				if(!empty($report['data'])) {
					echo '<div class="col-sm-9"><h3>Целевые страницы</h3></div>';
					// Таблица отчета
					echo tpx('report_table', $assign);
				}
			} else {
			
			}
			
		}
		*/
		
	} else {
		$report = get_clicks_report_grouped2($params);

		// Собираем переменные в шаблон
		$assign = $params;
		$assign['campaign_params'] = $report['campaign_params'];
		$assign['click_params'] = $report['click_params'];
		$assign['report_params'] = $params;
		$assign['report_name'] = col_name($params, true) . ' за ';
		$assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');
		$assign['arr_report_data'] = $report['data'];
		$assign['arr_dates'] = $report['dates'];
		
		//click_params

		// Заголовок отчета
		echo tpx('report_name', $assign);
		
		// Фильтры
		//echo tpx('report_conv', $assign);
		
		// Фильтры
		echo tpx('report_groups', $assign);

		// Таблица отчета
		echo tpx('report_table', $assign);
		
		// Если в Отчете по переходам выбран разрез Источник, то выводим таблицу Целевые страницы, добавляем к ней столбец Целевая страница и делаем источники кликабельными. 
		//if(in_array($params['group_by'], array('source_name', 'ads_name', 'campaign_name', 'referer', 'country'))) {
		
			$params['where'] = '';
			$params['mode'] = 'lp';
			$assign['report_params'] = $params;
			$report_lp = get_clicks_report_grouped2($params);
			$assign['arr_report_data'] = $report_lp['data'];
			
			if(!empty($report_lp['data'])) {
			
				echo '<div class="col-sm-9"><h3>Целевые страницы</h3></div>';
			
				// Таблица отчета
				echo tpx('report_table', $assign);
			}
			
			// Возвращаем режим на место, иначе кнопки внизу будут вести на этот тип отчёта
			$params['mode'] = $assign['report_params']['mode'] = '';
			
		//}
	}
	
	break;
    
    case 'all_stats':
        if ($from == '') {
            if ($to == '') {
                $from = get_current_day('-6 days');
                $to = get_current_day();
            } else {
                $from = date('d.m.Y', strtotime('-6 days', strtotime($to)));
            }
        } else {
            if ($to == '') {
                $to = date('d.m.Y', strtotime('+6 days', strtotime($from)));
            } else {
                // Will use existing values
            }
        }
    	
    	$fromF = date('d.m.Y', strtotime($from));
        $toF = date('d.m.Y', strtotime($to));
        $value_date_range = "$fromF - $toF";
        
        echo '<form method="post"  name="datachangeform" id="range_form">
                <div class="pull-left"><h3>' . $report_name . '</h3></div>
                <div id="per_day_range" class="pull-left" style="">
                    <span id="cur_day_range">'.date('d.m.Y', strtotime($from)).' - '. date('d.m.Y', strtotime($to)).'</span> <b class="caret"></b>
                    <input type="hidden" name="from" id="sStart" value="">
                    <input type="hidden" name="to" id="sEnd" value="">
                </div>
                <div class="pull-right" style="margin-top:18px;">' . type_subpanel() . '</div>
              </form>';
    	
    	include _TRACK_SHOW_COMMON_PATH."/pages/report_all.inc.php";
    break;
    
    case 'targetreport':
    	
    	$from = empty($_REQUEST['from']) ? date('Y-m-d', time() - 3600*24*6) : date('Y-m-d', strtotime($_REQUEST['from']));
    	$to =   empty($_REQUEST['to']) ? date('Y-m-d') :  date('Y-m-d', strtotime($_REQUEST['to']));
    	
    	include _TRACK_SHOW_COMMON_PATH."/pages/targetreport.php";
   	break;
}
?>

<link href="<?php echo _HTML_LIB_PATH;?>/datatables/css/jquery.dataTables.css" rel="stylesheet">
<link href="<?php echo _HTML_LIB_PATH;?>/datatables/css/dt_bootstrap.css" rel="stylesheet">
<script src="<?php echo _HTML_LIB_PATH;?>/datatables/js/jquery.dataTables.min.js" charset="utf-8" type="text/javascript"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/datatables/js/dt_bootstrap.js" charset="utf-8" type="text/javascript"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/sparkline/jquery.sparkline.min.js"></script>
<link href="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/moment.min.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker.js"></script>
<link href="<?php echo _HTML_LIB_PATH;?>/datepicker/css/datepicker.css" rel="stylesheet"/>
<script type="text/javascript" src="<?php echo _HTML_LIB_PATH;?>/datepicker/js/bootstrap-datepicker.js"></script>

<script>
    $('#dpMonthsF').datepicker();
    $('#dpMonthsT').datepicker();
    
    <?php
    	$from = empty($_POST['from']) ? date('d.m.Y', time() - 3600*24*6) : date('d.m.Y', strtotime($_POST['from']));
    	$to = empty($_POST['to']) ? date('d.m.Y') :  date('d.m.Y', strtotime($_POST['to']));
    ?>
    
    $('#per_day_range').daterangepicker(
        {
            startDate: '<?php echo _e($from)?>',
            endDate: '<?php echo _e($to)?>',
            format: 'DD.MM.YYYY',
            locale: {
                applyLabel: "Выбрать",
                cancelLabel: "<i class='fa fa-times' style='color:gray'></i>",
                fromLabel: "От",
                toLabel: "До",
                customRangeLabel: 'Свой интервал',
                daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
            },
            ranges: {
                'Сегодня': [moment(), moment()],
                'Вчера': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Последние 7 дней': [moment().subtract('days', 6), moment()],
                'Последние 30 дней': [moment().subtract('days', 29), moment()],
                'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                'Прошлый месяц': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                
            }
        },
	    function(start, end) {
	        $('#cur_day_range').text(start.format('DD.MM.YYYY') + ' - ' + end.format('DD.MM.YYYY'));
	        $('#sStart').val(start.format('YYYY-MM-DD'));
	        $('#sEnd').val(end.format('YYYY-MM-DD'));
	        
	        hashes = window.location.href.split('&');
	        for(var i = 0; i < hashes.length; i++) {
			    hash = hashes[i].split('=');
			    if(hash[0] == 'from') {
			    	hashes[i] = 'from=' + start.format('YYYY-MM-DD');
			    }
			    if(hash[0] == 'to') {
			    	hashes[i] = 'to=' + end.format('YYYY-MM-DD');
			    }
			}
			history.pushState(null, null, hashes.join('&'));
			
	        //console.log($('#range_form').serialize());
	        $('#range_form').submit();
	    }
    );
    
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
    
    function cnv2(m) {
    	n = $('.cnt', $('<div>' + m + '</div>')).text();
        if(n != '') {
        	n = n.split(':')
	        if(n.length == 2) {
	        	n0 = n[0]; n1 = n[1];
	        } else if(n.length == 1) {
	        	n0 = 0; n1 = n[0];
	        } else {
	        	n0 = 0; n1 = 0;
	        }
        } else {
        	n0 = 0; n1 = 0;
        }
        return [parseFloat(n0), parseFloat(n1)];
    }
    
    jQuery.fn.dataTableExt.oSort['click-data-asc'] = function(a, b) {
		if(a.indexOf('sortdata') + 1) {
			a = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + a + '</div>')).text().split('|');
			b = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + b + '</div>')).text().split('|');
			return srt_data(a, b, 0, 1);
		} else {
			x = cnv2(a);
			y = cnv2(b);
			return ((x[0] < y[0]) ? -1 : ((x[0] > y[0]) ? 1 : ((x[1] < y[1]) ? -1 : ((x[1] > y[1]) ? 1 : 0))   ));
		}
    };

    jQuery.fn.dataTableExt.oSort['click-data-desc'] = function(a, b) {
    	if(a.indexOf('sortdata') + 1) {
    		a = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + a + '</div>')).text().split('|');
			b = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + b + '</div>')).text().split('|');
			return srt_data(a, b, 0, -1);
    	} else { 
    		x = cnv2(a);
			y = cnv2(b);
			return ((x[0] < y[0]) ? 1 : ((x[0] > y[0]) ? -1 : ((x[1] < y[1]) ? 1 : ((x[1] > y[1]) ? -1 : 0))  ));
		}
    };
</script>
<?php 
echo tpx('report_toolbar');

echo '<script>';

echo "update_cols('" . $col . "', 0);";

if($part == 'all') { 
	echo "update_cols('currency_" . $currency . "', 1);";
}
echo '</script>';
?>
<input type='hidden' id='usd_selected' value='1'>
<input type='hidden' id='type_selected' value='cnt'>
<input type='hidden' id='sales_selected' value='1'>