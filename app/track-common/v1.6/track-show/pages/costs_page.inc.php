<?php if (!$include_flag){exit();} ?>
<link href="<?php echo _HTML_LIB_PATH;?>/select2/select2.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH;?>/select2/select2.js"></script>

<link href="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/moment.min.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker.js"></script>

<script>
	function add_costs()
	{		
		if ($('input[name=date_range]', '#add_costs').val()=='')
		{
			$('input[name=date_range]', '#add_costs').css('background-color', 'lightyellow');	
			return false;
		}

		if ($('input[name=costs_value]', '#add_costs').val()=='' || $('input[name=costs_value]', '#add_costs').val()==0)
		{
			$('input[name=costs_value]', '#add_costs').css('background-color', 'lightyellow');	
			return false;
		}
			
		$.ajax({
			  type: 'POST',
			  url: 'index.php',
			  data: $('#add_costs').serialize()
			}).done(function( msg ) 
			{
				$('input[name=costs_value]', '#add_costs').val('');
				$('#status_icon').removeClass('hidden');
				$('#status_icon').fadeIn(100).fadeOut(1200);

				return false;
			});
		return false;
	}

	function change_currency(currency)
	{
		var currency_name=''; var currency_code='';
		switch (currency)
		{		
			case 'rub': 
				currency_name='руб.';
				currency_code='rub';
			break;		
			case 'usd': 
				currency_name='долл.';
				currency_code='usd';
			break;
			case 'uah': 
				currency_name='грн.';
				currency_code='uah';
			break;				
		}
		$('#currency_selected').html(currency_name+'&nbsp;&nbsp;<span class="caret"></span>');
		$('#currency_code').val(currency_code);
		return false;
	}

	$(document).ready(function() 
	{
		$(".select2").select2();

		$('input[name="date_range"]').daterangepicker({format: 'DD.MM.YYYY', locale: {applyLabel: "Выбрать", cancelLabel: "<i class='fa fa-times' style='color:gray'></i>", fromLabel: "От", toLabel: "До", customRangeLabel:'Свой интервал', daysOfWeek:['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
        }});

		$('input[name="date_range"]', $('#add_costs')).focus (function (e) {
			$('input[name="date_range"]', $('#add_costs')).css('background-color', 'white'); 
		});        

		$('input[name="costs_value"]', $('#add_costs')).focus (function (e) {
			$('input[name="costs_value"]', $('#add_costs')).css('background-color', 'white'); 
		});

	});
</script>


	<form role="form" class="form-horizontal" method="post" id='add_costs' onsubmit="return add_costs();">
		<input type='hidden' name='ajax_act' value='add_costs'>
        <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
		<input type='hidden' id='currency_code' name='currency_code' value='rub'>
		<div class="form-group">
			 <label class="col-sm-2 control-label">Период</label>
			<div class="input-group col-sm-4">
			  <span class="input-group-addon"><i class='fa fa-calendar'></i></span>
			  <input type="text" name="date_range" class="form-control">
			</div>
		</div>

		<div class="form-group">
		    <label class="col-sm-2 control-label">Источник</label>
		    <div class='col-sm-4'>
				<select class='select2' style='width:100%' name='source_name'>
					<?php
						foreach ($arr_sources as $cur)
						{
							echo "<option value='"._e($cur['source_name'])."'>"._e($cur['name'])."</option>";
						}
					?>
				</select>
			</div>
		</div>


		<div class="form-group">
		    <label class="col-sm-2 control-label">Кампания</label>
		    <div class='col-sm-4'>
				<select class='select2' style='width:100%;' name='campaign_name'>
					<option value='' selected>Все</option>
					<?php
						foreach ($arr_campaigns as $cur)
						{
							echo "<option value='"._e($cur['campaign_name'])."'>"._e($cur['campaign_name'])."</option>";
						}
					?>
				</select>
			</div>
		</div>
		
		<div class="form-group">
		    <label class="col-sm-2 control-label">Объявление</label>
		    <div class='col-sm-4'>
				<select class='select2' style='width:100%;' name='ads_name'>
					<option value='' selected>Все</option>
					<?php
						foreach ($arr_ads as $cur)
						{
							echo "<option value='"._e($cur['ads_name'])."'>"._e($cur['ads_name'])."</option>";
						}
					?>			
				</select>
			</div>
		</div>		


		<div class="form-group">
		    <label class="col-sm-2 control-label">Сумма</label>
		    <div class='col-sm-4'>
				<div class="input-group">
					<input type="text" class="form-control" id="costs_value" name="costs_value" placeholder="0.00">
					<div class="input-group-btn">
						<button type="button" id="currency_selected" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">руб.&nbsp;&nbsp;<span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li><a href="#" onclick="return change_currency('usd');">долл., $</a></li>
							<li><a href="#" onclick="return change_currency('uah');">грн., ₴</a></li>
							<li><a href="#" onclick="return change_currency('rub');">руб.</a></li>
						</ul>
		        	</div>
		    	</div>
		    </div>
		</div>		
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-4">
				<button type="submit" class="btn btn-default" id="btn_send">Добавить</button>
				<span class='hidden' id='status_icon'><button class='btn btn-link'><i class='fa fa-check'></i></button></span>
			</div>
		</div>
	</form>