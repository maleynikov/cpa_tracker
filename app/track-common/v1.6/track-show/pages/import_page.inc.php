<?php if (!$include_flag){exit();} 
	$mode = (isset($_POST['leadsType']) and $_POST['leadsType'] == 'lead') ? 'lead' : 'sale';	
?>
<script type="text/javascript">
	function check_import()
	{
		if ($('#leadsType').val()=='sale' && ($('#amount_value').val()==0 || $('#amount_value').val()==''))
		{
			return false;
		}

		if ($('#subids').val()=='')
		{
			return false;
		}    
	    
		return true;
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
</script>

<form role="form" method='post' onSubmit='return check_import();'>
	<div class="form-group">
		<div class="btn-group" data-toggle="buttons">
			<label class="btn btn-default <?php if($mode == 'sale') { ?>active<?php } ?>" onclick="$('#leadsType').val('sale'); $('#sale_amount').show(); $('#amount_value').attr('required', true);">
				<input type="radio" name="options" id="option1"> Продажа
			</label>

			<label class="btn btn-default <?php if($mode == 'lead') { ?>active<?php } ?>" onclick="$('#leadsType').val('lead'); $('#sale_amount').hide(); $('#amount_value').removeAttr('required');">
				<input type="radio" name="options" id="option2"> Лид
			</label>
		</div>
	</div>

	<div class="form-group" id='sale_amount'>
		<label>Оплата за продажу</label>
		<div class="row">
			<div class="col-xs-6">
				<div class="input-group">
					<input type="text" class="form-control" id='amount_value' name='amount_value' placeholder="0.00" required>
					<div class="input-group-btn">
						<button type="button" id="currency_selected" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">руб.&nbsp;&nbsp;<span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li><a href="#" onclick="return change_currency('usd');">долл., $</a></li>
							<li><a href="#" onclick="return change_currency('uah');">грн., ₴</a></li>
							<li><a href="#" onclick="return change_currency('rub');">руб.</a></li>
						</ul>
		        	</div>
		    	</div><!-- /input-group -->
		  	</div><!-- /.col-lg-6 -->
		</div><!-- /.row -->
	</div>

	<div class="row">
		<div class="form-group col-xs-6">
			<label for="exampleInputFile">Список SubID</label>
                        <textarea class="form-control" rows='5' id='subids' name='subids' required></textarea>
		    <p class="help-block">По одному на строке или через запятую.</p>
		</div>
	</div>

	<button type="submit" class="btn btn-default">Добавить</button>
  	<input type='hidden' name='ajax_act' value='import_sales'>
        <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
  	<input type='hidden' id='currency_code' name='currency_code' value='rub'>
	<input type='hidden' id='leadsType' name='leadsType' value='sale'>
		
	<?php if($mode == 'lead') { ?>
		<script>
			$('#leadsType').val('lead'); $('#sale_amount').hide(); $('#amount_value').removeAttr('required');
		</script>
	<?php } ?>
	<?php if(!empty($_REQUEST['currency_code'])) { ?>
		<script>
			change_currency('<?php echo $_REQUEST['currency_code'];?>');
		</script>
	<?php } ?>
</form>