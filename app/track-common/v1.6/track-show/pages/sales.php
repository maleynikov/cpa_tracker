<?php if (!$include_flag){exit();} ?>
<?php
	$from = $_REQUEST['sStart'];
	$to = $_REQUEST['sEnd'];
	$total = array();
	$month = false;
	
	$filter_by=trim($_REQUEST['filter_by']);
	$arr_sales=get_last_sales($filter_by);
	
	if ($_REQUEST['subtype']=='monthly') {
    if ($from == '') {
        if ($to == '') {
            $from = get_current_day('-6 days');
            $to = get_current_day();
        } else {
            $from = date('Y-m-d', strtotime('-6 month', strtotime($to)));
        }
    } else {
        if ($to == '') {
            $to = date('Y-m-d', strtotime('+6 month', strtotime($from)));
        }
    }
    $days = getMonthsBetween($from, $to);
    
    $month_active = 'active';
    $month = true;
} else {
    if ($from == '') {
        if ($to == '') {
            $from = get_current_day('-6 days');
            $to = get_current_day();
        } else {
            $from = date('Y-m-d', strtotime('-6 days', strtotime($to)));
        }
    } else {
        if ($to == '') {
            $to = date('Y-m-d', strtotime('+6 days', strtotime($from)));
        }
    }
    $days = getDatesBetween($from, $to);
    
    $days_active='active';
    
}

$sales = get_sales($from, $to, $days, $month);
krsort($sales);
?>
    <div class="row">
        <div class="col-md-4">
            <div class="btn-group">
                <a href="?act=reports&type=sales&subtype=daily" type="button" class="btn btn-default <?php echo $days_active?>">По дням</a>
                <a href="?act=reports&type=sales&subtype=monthly" type="button" class="btn btn-default <?php echo $month_active?>">По месяцам</a>
            </div>
        </div>
    </div>
    <div class='row'>
        <div class="col-md-4"><h3>Продажи по дням:</h3></div>
        <div id="per_day_range" class="pull-right">
            <span class="glyphicon glyphicon-calendar"></span>
            <span id="cur_day_range"><?php echo date(($month)?'m.Y':'d.m.Y', strtotime($from)); ?> - <?php echo date(($month)?'m.Y':'d.m.Y', strtotime($to)); ?></span> <b class="caret"></b>
            <form action="index.php?act=reports&type=sales&subtype=<?php echo ($month)?'monthly':'daily'?>" method="POST">
                <input type="hidden" name="sStart" id="sStart" value="">
                <input type="hidden" name="sEnd" id="sEnd" value="">
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php if (is_array($sales)) :?>
            <div id="chart_div"></div>
            <?php endif;?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class='table table-condensed table-striped table-bordered dataTableT'>
                <thead>
                    <tr>
                        <th>Источник</th>
                        <?php foreach ($days as $day) : ?>
                            <th><?php echo  (!$month)?date('d.m', strtotime($day)):$day; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sales == FALSE) :?>
                    <tr>
                        <td colspan="<?php echo count($days)+1;?>" style="text-align: center;">
                            За выбранный период продаж не было.
                        </td>
                    </tr>
                    <?php else : ?>
                        <?php foreach ($sales as $sale => $d) :?>
                            <tr>
                                <td><?php if($sale == '_') echo 'Не определен'; else { if(!empty($source_config[$sale])) { echo $source_config[$sale]['name']; } else echo $sale;} ?></td>
                                <?php foreach ($days as $day) : ?>
                                    <?php $dkey = (!$month)?date('d.m', strtotime($day)):$day;?>
                                    <td>
                                        <?php if (isset($d[$dkey])):?>
                                            <?php echo $d[$dkey];?>
                                            <?php $total[$dkey]+=$d[$dkey];?>
                                        <?php else : ?>
                                            0
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach;?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Итого:</th>
                        <?php foreach ($days as $day) : ?>
                            <th><?php echo  intval($total[(!$month)?date('d.m', strtotime($day)):$day]); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

<link href="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/moment.min.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script>
	<?php		
		if($_REQUEST['subtype']=='monthly') {
			$from = empty($_POST['sStart']) ? date('m.Y') : date('m.Y', strtotime($_POST['sStart']));
			$to = empty($_POST['sEnd']) ? date('m.Y') : date('m.Y', strtotime($_POST['sEnd']));
		} else {
    		$from = empty($_POST['sStart']) ? date('d.m.Y', time() - 3600*24*6) : date('d.m.Y', strtotime($_POST['sStart']));
    		$to = empty($_POST['sEnd']) ? date('d.m.Y') : date('d.m.Y', strtotime($_POST['sEnd']));
    	}
    ?>
	
    $('#per_day_range').daterangepicker(
            {
                <?php if ($_REQUEST['subtype']=='monthly') :?>
                format: 'MM.YYYY',
                <?php else : ?>    
                format: 'DD.MM.YYYY',
                <?php endif; ?>
                startDate: '<?php echo _e($from)?>',
                endDate: '<?php echo _e($to)?>',
                locale: {
                    applyLabel: "Выбрать",
                    cancelLabel: "<i class='fa fa-times' style='color:gray'></i>",
                    fromLabel: "От",
                    toLabel: "До",
                    customRangeLabel: 'Свой интервал',
                    daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
                },
                ranges: {
                    <?php if ($_REQUEST['subtype']=='monthly') :?>
                    'Последние 3 мес': [moment().subtract('month', 3).startOf('month').startOf('month')],
                    'Последние 6 мес': [moment().subtract('month', 6).startOf('month'), moment()],
                    'Последний год': [moment().subtract('month', 12).startOf('month'), moment()],
                    'Последние 2 года': [moment().subtract('month', 24).startOf('month'), moment()],
                    <?php else : ?>
                    'Сегодня': [moment(), moment()],
                    'Вчера': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Последние 7 дней': [moment().subtract('days', 6), moment()],
                    'Последние 30 дней': [moment().subtract('days', 29), moment()],
                    'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                    'Прошлый месяц': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                    <?php endif; ?>
                }
            },
    function(start, end) {
        $('#cur_day_range').text(start.format(<?php echo ($month)?"'MM.YYYY'":"'DD.MM.YYYY'";?>) + ' - ' + end.format(<?php echo ($month)?"'MM.YYYY'":"'DD.MM.YYYY'";?>));
        $('#sStart').val(start.format('YYYY-MM-DD'));
        $('#sEnd').val(end.format('YYYY-MM-DD'));
        $('#per_day_range form').submit();
    }
    );


    function delete_sale(obj, type, click_id, conversion_id)
    {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=delete_sale&type=' + type + '&click_id=' + click_id + '&conversion_id=' + conversion_id
        }).done(function(msg)
        {
            $(obj).parent().parent().parent().parent().parent().remove();
        });

        return false;
    }
   <?php
   	   // Тонкая подстройка количества осей графика, чтобы там не было дробных значений
   	   $axes_tune = array(
   	   		1 => 2,
   	   		2 => 3,
   	   );
   	   
   	   if (is_array($sales)) { ?> 
    google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          [<?php if ($_REQUEST['subtype']=='monthly') echo "'Месяц'"; else echo "'День'";?>, 'Продажи'],
          <?php $i = 0; $max = 0;?>
          <?php foreach ($days as $day) { ?>
              <?php $i++;
              $dkey = (!$month)?date('d.m', strtotime($day)):$day;
              if($total[$dkey] > $max) $max = $total[$dkey];
              echo  '[\''.$dkey.'\', '. intval($total[$dkey]).']'; ?><?php if ($i < count($days)) echo ',';?>
                
          <?php } ?>
        ]);

        var options = {
          title: 'Отчет продаж',
          width: '100%',
          height: 250,
          chartArea: {left: '20',width:'800'},
          legend: {position: 'in'},
          hAxis: {title: 'Количество', format:'string'},
          vAxis: {textPosition: 'out', format:'#', <?php if(!empty($axes_tune[$max])) { ?>gridlines: {count: <?php echo $axes_tune[$max];?>}<?php } ?>}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
      <?php } ?>
</script>

<style>
    .sales_row:hover .sales_menu, .sales_row.hover .sales_menu { visibility: visible; }
    .sales_menu{
        visibility: hidden; float:right; margin-left:5px;
    }
</style>

<div class="row">
	<form class="form-inline" role="form" method="post">
		<input type='hidden' name='act' value='reports'>
		<input type='hidden' name='type' value='sales'>
		<input type='hidden' name='sales' value='sales'>

		<div class="form-group col-xs-4">
			<input type="text" class="form-control" name="filter_by" placeholder="Поиск по SubID" value="<?php echo _e($_REQUEST['filter_by']);?>">
		</div>

		<button type="submit" class="btn btn-default">Найти</button>
	</form>
</div>
<div class='row'>&nbsp;</div>
<?php
	echo "<div class='row'>";
	echo "<div class='col-md-12'>";
	echo "<table class='table table-striped table-bordered table-condensed' style='width:600px;'>";
		echo "<thead>";
		echo '<tr><th>Дата</th><th>Оффер</th><th>Сумма, $</th><th>Сеть</th><th>Страна</th><th>Источник</th><th>Кампания</th><th>Реферер</th><th>SubID</th></tr>';
		echo "</thead>";
		echo "<tbody>";	
		//dmp($source_config);
			foreach ($arr_sales as $cur)
			{

				$cur_referrer=$cur['referer'];
				if (strpos($cur_referrer, 'http://')===0){$cur_referrer=substr($cur_referrer, strlen('http://'));}
				if (strpos($cur_referrer, 'https://')===0){$cur_referrer=substr($cur_referrer, strlen('https://'));}
				if (strpos($cur_referrer, '/')===(strlen($cur_referrer)-1)){$cur_referrer=substr($cur_referrer, 0, -1);}
				
				if (strlen($cur_referrer)>40)
				{
					$cur_referrer=substr($cur_referrer,0,38).'…';
				}

				echo "<tr class='sales_row'  style='cursor:pointer;' onclick='$(this).next().toggle();'>";			
					echo "<td nowrap>".mysqldate2short($cur['date_add'])."</td>
							<td nowrap>"._e($cur['offer_name']);
							echo "<div class='btn-group sales_menu'>
								<button class='btn btn-default btn-xs dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button>
								<ul class='dropdown-menu'>
									<li><a href='#' style='color:red;' onclick=\"return delete_sale(this, '"._e($cur['type'])."', '"._e($cur['click_id'])."', '"._e($cur['conversion_id'])."')\">Удалить продажу</a></li>
								</ul>
							</div>";
							
							$source_name = $cur['source_name']; 
							$source_name = empty($source_config[$source_name]['name']) ? $source_name : $source_config[$source_name]['name'];
							
							echo "</td>
							<td>"._e(round($cur['profit'], 3))."</td>
							<td>"._e($cur['network'])."</td>    
							<td>"._e($cur['country'])."</td>
							<td>"._e($source_name)."</td>
							<td>"._e($cur['campaign_name'])." "._e($cur['ads_name'])."</td>
							<td><a target='_blank' href='"._e($cur['referer'])."'>"._e($cur_referrer)."</a></td>
					<td>"._e($cur['subid'])."</a>";
					echo "</td>";
				echo "</tr>";
                                
                                echo '<tr style="display:none;"><td colspan="9">';
                                if ($cur['t1'] != '') {
                                    echo "<span class='badge' style='float:right; font-weight:normal; margin-right:25px;'>"._e($cur['t1'])."</span>";
                                }
                                
                                if ($cur['date_add'] != '') {
                                    echo 'Дата конверсии: '. date('d.m.Y H:i:s', strtotime($cur['date_add'])).'<br>';
                                }
                                
                                echo 'Сеть: '.$cur['network'].'<br>';
                                
                                echo 'Статус: ';
                                if ($cur['txt_status'] != '') {
                                    echo $cur['txt_status'];
                                }
                                else {
                                    switch ($cur['status']) {
                                        case '1':
                                            echo 'Approved';
                                            break;
                                        case '2':
                                            echo 'Declined';
                                            break;
                                        case '3':
                                            echo 'Waiting';
                                            break;
                                        default:
                                            echo 'Unknown';
                                            break;
                                    }
                                }
                                echo '<br>';
                                
                                echo 'SubID: '.$cur['subid'];
                                
                                if ($cur['t16'] != '' || $cur['t17'] != '' || $cur['t18'] != '' || $cur['t19'] != '') {
                                    echo '('.$cur['t16'].' '.$cur['t17'].' '.$cur['t18'].' '.$cur['t19'].' '.')';
                                }
                                echo '<br>';
                                
                                if ($cur['t20'] != '') {
                                    echo 'Валюта: '.$cur['t20'].'<br>';
                                }
                                
                                if ($cur['i3'] != 0) {
                                    echo 'ID транзакции: '.$cur['i3'].'<br>';
                                }
                                
                                if ($cur['i9'] != 0) {
                                    echo 'ID выплаты: '.$cur['i9'].'<br>';
                                }
                                
                                if ($cur['t2'] != '') {
                                    echo 'UserAgent: '.$cur['t2'].'<br>';
                                }
                                
                                if ($cur['t3'] != '') {
                                    echo 'Цель: '.$cur['t3'].'<br>';
                                }
                                if ($cur['i1'] != 0){
                                    echo 'ID Цели: '.$cur['i1'].'<br>';
                                }                    
                                if ($cur['i2'] != 0) {
                                    echo 'Оффер: '.$cur['i2'];
                                    if ($cur['t4'] != '') {
                                        echo ' - '.$cur['t4'];
                                    }
                                    echo '<br>';                                    
                                }
                                
                                if ($cur['t5'] != '') {
                                    echo 'Unique ID: '.$cur['t5'].'<br>';
                                }
                                
                                if ($cur['i7'] != 0) {
                                    echo 'Поток: '.$cur['i7'];
                                    if ($cur['t6'] != '') {
                                        echo ' - '.$cur['t6'];
                                    }
                                    
                                    echo '<br>';
                                }
                                
                                if ($cur['i8'] != 0 || $cur['t7'] != '') {
                                    echo 'Источник: '.$cur['i8'].' '.$cur['t7'].'<br>';
                                }
                                
                                if ($cur['t8'] != '') {
                                    echo 'CPL/CPA: '.$cur['t8'].'<br>';
                                }
                                
                                if ($cur['t9'] != '') {
                                    echo 'Страна: '.$cur['t9'].'<br>';
                                }
                                
                                if ($cur['t10'] != '') {
                                    echo 'Город: '.$cur['t10'].'<br>';
                                }
                                
                                if ($cur['t11'] != '') {
                                    echo 'Браузер: '.$cur['t11'].'<br>';
                                }
                                
                                if ($cur['t12'] != '') {
                                    echo 'ОС: '.$cur['t12'].'<br>';
                                }
                                
                                if ($cur['t13'] != '') {
                                    echo 'Устройство: '.$cur['i13'].' '.$cur['t13'].'<br>';
                                }
                                
                                
                                if ($cur['t15'] != '') {
                                    echo 'Баннер: '.$cur['i10'].' '.$cur['t15'].'<br>';
                                }
                                
                                if (count($cur['add']) > 0) {
                                    echo 'Дополнительно:<br>';
                                    foreach ($cur['add'] as $add) {
                                        echo $add['name'].':'.$add['value'].'<br>';
                                    }
                                }
                                
                                echo '</td></tr>';
			}
		echo "</tbody>";
	echo "</table>";
	echo "</div> <!-- ./col-md-12 -->";
	echo "</div> <!-- ./row -->";
?>
<script>
function delete_sale(obj, type, click_id, conversion_id)
{
	$.ajax({
	  type: 'POST',
	  url: 'index.php',
	  data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=delete_sale&type='+type+'&click_id='+click_id+'&conversion_id='+conversion_id
	}).done(function( msg ) 
	{
		$(obj).parent().parent().parent().parent().parent().remove();
	});

	return false;
}
</script>

<style>
	.sales_row:hover .sales_menu, .sales_row.hover .sales_menu { visibility: visible; }
	.sales_menu{
		visibility: hidden; float:right; margin-left:5px;
	}
</style>