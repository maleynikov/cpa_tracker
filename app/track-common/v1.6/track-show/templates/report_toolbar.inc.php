<?php
global $option_leads_type, $col, $params, $option_currency, $currency, $report, $report_lp, $group_actions, $panels, $assign, $report_cols;

// Если есть данные, и их показ без ограничений
if(!empty($report['data']) or !empty($report_lp['data']) or $params['conv'] != 'all' or $params['col'] != 'act') {
	
	if($params['type'] != 'all_stats' and $params['part'] != 'all') {
	//if(1) {
		// Целевые страницы
		// Новая версия "дневного" тулбара, кнопки переключают данные, сгенерированные функцией get_clicks_report_element2
		//if($params['mode'] == 'lp_offers') {
		if(1) {
			// Офферы
			if($params['mode'] == '') {

				// Есть конверсии
				if($params['conv'] != 'none') {
					$group_actions = array(
						'act'   => array('cnt', 'act',  'conversion_a', 'price', 'profit', 'roi'),
						'sale'  => array('cnt', 'sale', 'conversion',   'price', 'profit', 'epc', 'roi'),
						'lead'  => array('cnt', 'lead', 'conversion_l', 'price', 'cpl')
					);
					
					$panels = array(
						'act'  => 'Все действия',
						'sale' => 'Продажи',
						'lead' => 'Лиды'
					);
				// Конверсий нет, некоторые кнопки не нужны
				} else {
					$group_actions = array(
						'act'  => array('cnt', 'repeated', 'lpctr', 'price'),
						'sale' => array('cnt', 'repeated', 'lpctr', 'price'),
						'lead' => array('cnt', 'repeated', 'lpctr', 'price')
					);
					$panels = array();
				}
			
			} else {
				// Есть конверсии
				if($params['conv'] != 'none') {
					$group_actions = array(
						'act'   => array('cnt', 'repeated', 'lpctr', 'act',  'conversion_a', 'price', 'profit', 'cpa'),
						'sale'  => array('cnt', 'repeated', 'lpctr', 'sale', 'conversion',   'price', 'profit', 'epc', 'roi'),
						'lead'  => array('cnt', 'repeated', 'lpctr', 'lead', 'conversion_l', 'price', 'cpl')
					);
					
					$panels = array(
						'act'  => 'Все действия',
						'sale' => 'Продажи',
						'lead' => 'Лиды'
					);
				// Конверсий нет, некоторые кнопки не нужны
				} else {
					$group_actions = array(
						'act'  => array('cnt', 'repeated', 'lpctr', 'price'),
						'sale' => array('cnt', 'repeated', 'lpctr', 'price'),
						'lead' => array('cnt', 'repeated', 'lpctr', 'price')
					);
					$panels = array();
				}
			}
		?>
<div class="row" id="report_toolbar">
    <div class="col-md-12">
        <div class="form-group">
    	<?php
    		$i = 0;
    		foreach($group_actions as $group => $actions) {
    			echo '<div class="btn-group rt_types rt_type_'.$group.'" data-toggle="buttons" style="'.($i > 0 ? 'display: none' : '').'">';
    			foreach($actions as $action) {
    				echo '<label class="btn btn-default '.($i == 0 ? 'active' : '').'" onclick="update_stats2(\''.$action.'\', '.($report_cols[$action]['money'] == 1 ? 'true' : 'false' ).');"><input type="radio" name="option_report_type">'.$report_cols[$action]['name'].'</label>';
    			$i++;
    			}
    			
    			echo '</div>';
    		}
    		
    		if(!empty($panels)) {
        		echo '<div class="btn-group margin5rb" id="rt_sale_section"  >'; // data-toggle="buttons"
        		/*
        		$i = 0;
        		foreach($panels as $value => $name) {
        			echo '<label class="btn btn-default '.($i == 0 ? 'active' : '').'" onclick="show_conv_mode(\''.$value.'\')"><input type="radio" name="option_leads_type">' . $name . '</label>';
        			$i++;
        		}
        		*/
        		
        		// Все действия, продажи, лиды !!!!!!!!!!!!!!!
        		
				foreach($option_leads_type as $k => $v) {
					$new_params = array('col' => $k);
					if(in_array($params['conv'], array('sale', 'lead', 'act'))) {
						$new_params['conv'] = $k;
					}
					//dmp($new_params);
  					echo '<a class="btn btn-default'.($col == $k ? ' active' : '').'" href="'.report_lnk($params, $new_params).'">' . $v . '</a>';
  				}
        		
        		
        		echo '</div>';
    		}
    		
    		echo tpx('report_conv', $assign);
    	?>
            <div class="btn-group pull-right margin5rb" id="rt_currency_section" data-toggle="buttons" style="display: none">
                <!--<label class="btn btn-default" onclick='show_currency("rub");'><input type="radio" name="option_currency"><i class="fa fa-rub"></i></label>
                <label class="btn btn-default active" onclick='show_currency("usd");'><input type="radio" name="option_currency">$</label>	-->
            <?php
					// Переключение валют
					foreach($option_currency as $k => $v) {
	  					echo '<label class="btn btn-default '.($currency == $k ? ' active' : '').'" onclick="show_currency(\''.$k.'\');">
					<input type="radio" name="option_currency">' . $v . '
				</label>';
	  				}
				?>
            </div>
        </div>
    </div> <!-- ./col-md-12 -->
</div> <!-- ./row -->
<script><?php 
		echo "show_conv_mode('" . $col . "', 0);";  // вкладка "Все действия"
		echo "update_stats2('cnt', false);";        // кнопка "Переходы"
		echo "show_currency('" . $currency . "');"; // валюта
		?>
	</script>		
<?php	} else { 
	// Старая версия "дневного" тулбара, кнопки переключают данные, сгенерированные функцией get_clicks_report_element
?>
<div class="row" id="report_toolbar">
    <div class="col-md-12">
        <div class="form-group">
            <div class="btn-group margin5rb" id='rt_type_section' data-toggle="buttons">
                <label id="rt_clicks_button" class="btn btn-default active" onclick='update_stats("clicks");'><input type="radio" name="option_report_type">Клики</label>
                <label id="rt_conversion_button" class="btn btn-default" onclick='update_stats("conversion");'><input type="radio" name="option_report_type">Конверсия</label>	
                <label id="rt_leadprice_button" class="btn btn-default" onclick='update_stats("lead_price");'><input type="radio" name="option_report_type">Стоимость лида</label>					
                <label id="rt_roi_button" class="btn btn-default" onclick='update_stats("roi");'><input type="radio" name="option_report_type">ROI</label>	
                <label id="rt_epc_button" class="btn btn-default" onclick='update_stats("epc");'><input type="radio" name="option_report_type">EPC</label>	
                <label id="rt_profit_button" class="btn btn-default" onclick='update_stats("profit");'><input type="radio" name="option_report_type">Прибыль</label>
            </div>

            <div class="btn-group margin5rb" id='rt_sale_section' data-toggle="buttons">*
                <label class="btn btn-default active" onclick='update_stats("sale");'><input type="radio" name="option_leads_type">Продажи</label>
                <label class="btn btn-default" onclick='update_stats("lead");'><input type="radio" name="option_leads_type">Лиды</label>	
            </div>
            	
            <?php
            	// В дневном тулбаре панель переключения конверсий следует в конце
				echo tpx('report_conv', $assign);
            ?>

            <div class="btn-group invisible pull-right margin5rb" id='rt_currency_section' data-toggle="buttons">
                <label class="btn btn-default" onclick='update_stats("currency_rub");'><input type="radio" name="option_currency"><i class="fa fa-rub"></i></label>
                <label class="btn btn-default active" onclick='update_stats("currency_usd");'><input type="radio" name="option_currency">$</label>	
            </div>
        </div>
    </div> <!-- ./col-md-12 -->
</div> <!-- ./row -->
<?php } 

	} elseif($params['part'] == 'all') { 
		
		// Тулбар 
		?>
		<div id="report_toolbar" class="row">
	<div class="col-md-12">
		<div class="form-group">
			<?php
            	// В общем тулбаре панель переключения конверсий следует в начале
				echo tpx('report_conv', $assign);
				
				// Этот селектор не нужен, если нет конверсий
				if($params['conv'] != 'none') {
            ?>
	  		<div id="rt_sale_section" class="btn-group margin5rb" <?php if($params['mode'] != 'popular' and 0) { ?>data-toggle="buttons"<?php } ?>>
	  			<?php
	  				// Изначально этот фильтр позволял переключать колонки без перезагрузки страницы, потому что они они все уже были на странице.
	  				// Но с появлением режима "Популярные" появляется необходимость перезагружать страницу, а с появлением фильтров конверсии (Все, Только продажи, и.т.д.) эта необходимость переходит на все отчёты
	  				
	  				// Все действия, продажи, лиды
					foreach($option_leads_type as $k => $v) {
						$new_params = array('col' => $k);
						if(in_array($params['conv'], array('sale', 'lead', 'act'))) {
							$new_params['conv'] = $k;
						}
	  					echo '<a class="btn btn-default'.($col == $k ? ' active' : '').'" href="'.report_lnk($params, $new_params).'">' . $v . '</a>';
	  				}
	  			?>
			</div>
			<?php } ?>
			<div id="rt_currency_section" class="btn-group pull-right margin5rb" data-toggle="buttons">
				<?php
					// Переключение валют
					foreach($option_currency as $k => $v) {
	  					echo '<label class="btn btn-default '.($currency == $k ? ' active' : '').'" onclick="update_cols(\'currency_'.$k.'\');">
					<input type="radio" name="option_leads_type">
					' . $v . '
				</label>';
	  				}
				?>
			</div>
		</div>
	</div>
</div>
<?php }
} // !empty($data)
?>	
