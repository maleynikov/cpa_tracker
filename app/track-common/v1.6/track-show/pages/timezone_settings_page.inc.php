<?php if (!$include_flag){exit();} ?>
<?php
	$arr_timezone_settings=get_timezone_settings();
?>
<script>
    function add_timezone()
    {
        $.ajax({
          type: "POST",
          url: "index.php",
          data: $('#add_timezone').serialize()
        })
          .done(function( msg ) 
          {
            location.reload(true); 
            return false;
          });        
        return false;
    }

    function delete_timezone()
    {
        $.ajax({
          type: "POST",
          url: "index.php",
          data: {csrfkey:"<?php echo CSRF_KEY?>",ajax_act: 'delete_timezone', id: $('input[name=timezone_id]').val()}
        })
        .done(function( msg ) 
        {
        	location.reload(true); 
        });        
        return false;
    }

    function edit_timezone(obj,id)
    {
    	$('#add_timezone')[0].reset();
    	$('#timezones_list tr').css('background-color', 'inherit');
    	$(obj).css('background-color', 'rgb(255, 255, 204)');
    	
    	var offset_h=parseInt($('#tz_offset_'+id).text().split(':')[0]);
    	$('select[name=timezone_offset_h] option[value="'+offset_h+'"]', '#add_timezone').prop('selected', true);

    	$('input[name=timezone_name]', '#add_timezone').val($('#tz_name_'+id).text()).focus();
		$('input[name=ajax_act]', '#add_timezone').val('edit_timezone');
		$('input[name=timezone_id]', '#add_timezone').val(id);
    	$('button', '#add_timezone').text('Изменить');
    	$('#cancel_timezone_edit').show();
    	$('#delete_timezone').show();
    }
    
    function cancel_timezone_edit()
    {
    	$('#timezones_list tr').css('background-color', 'inherit');
    	$('#cancel_timezone_edit').hide();
    	$('#delete_timezone').hide();
		$('input[name=ajax_act]', '#add_timezone').val('add_timezone');
    	$('button', '#add_timezone').text('Добавить');
    	$('#add_timezone')[0].reset();
    }
</script>
<h3>Настройка часовых поясов</h3>

<p><b>Сейчас на сервере:</b> <span id="servertime"><?php echo date("d.m.Y H:i:s"); ?></span><br /></p>
<br />
<div class="row">
<div class='col-md-10' style='margin-left:0px;'>
	<table class='table table-bordered table-hover' id='timezones_list'>
		<thead>
			<th>Название</th>
			<th nowrap>Смещение от времени сервера</th>
		</thead>
		<tbody>
			<?php
				foreach ($arr_timezone_settings as $cur)
				{
					echo "<tr style='cursor:pointer;' onclick='edit_timezone(this, {$cur['id']})'>
							<td id='tz_name_{$cur['id']}'>"._e($cur['timezone_name'])."</td>
							<td id='tz_offset_{$cur['id']}'>";
							if ($cur['timezone_offset_h']>=0)
							{
								echo _e(sprintf("+%02d:00", $cur['timezone_offset_h']));
							}
							else
							{
								echo _e(sprintf("%03d:00", $cur['timezone_offset_h']));
							}
					echo "</td>
						</tr>";
				}
			?>
		</tbody>
	</table>



<div class="row">
	<form class="form-inline" role="form" id="add_timezone" onSubmit='return add_timezone();'>
		  <div class="form-group col-xs-6">
			    <input type="text" class="form-control" name='timezone_name' placeholder="Название часового пояса">
		  </div>
	  <div class="form-group">
	    <select name='timezone_offset_h' class='form-control'>
					<option value="" selected></option>
					<option value="-12">-12 часов</option>
					<option value="-11">-11 часов</option>
					<option value="-10">-10 часов</option>
					<option value="-9">-9 часов</option>
					<option value="-8">-8 часов</option>
					<option value="-7">-7 часов</option>
					<option value="-6">-6 часов</option>
					<option value="-5">-5 часов</option>
					<option value="-4">-4 часа</option>
					<option value="-3">-3 часа</option>
					<option value="-2">-2 часа</option>
					<option value="-1">-1 час</option>
					<option value="0">0</option>
					<option value="1">+1 час</option>
					<option value="2">+2 часа</option>
					<option value="3">+3 часа</option>
					<option value="4">+4 часа</option>
					<option value="5">+5 часов</option>
					<option value="6">+6 часов</option>
					<option value="7">+7 часов</option>
					<option value="8">+8 часов</option>
					<option value="9">+9 часов</option>
					<option value="10">+10 часов</option>
					<option value="11">+11 часов</option>
					<option value="12">+12 часов</option>
			</select>
	  </div>
	  <button type="button" onclick="add_timezone()" class="btn btn-default">Добавить</button>
	  <span class="btn btn-link" id='cancel_timezone_edit' onclick="cancel_timezone_edit()" style='display:none;'>отменить</span>
	  <span class="btn btn-link" id='delete_timezone' onclick="delete_timezone()" style='display:none; float:right;'><i class='icon-trash' title='Удалить часовой пояс'></i></span>
	  <input type="hidden" name="ajax_act" value="add_timezone">    
          <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
	  <input type="hidden" name="timezone_id" value="">
	</form>
</div>


</div>
</div> <!-- ./row -->
<script type="text/javascript">
	var currenttime = '<?php print date("F d, Y H:i:s", time())?>';

	var montharray=new Array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12")
	var serverdate=new Date(currenttime);

	function padlength(what)
	{
		var output=(what.toString().length==1)? "0"+what : what;
		return output;
	}

	function displaytime()
	{
		serverdate.setSeconds(serverdate.getSeconds()+1)

		var datestring=padlength(serverdate.getDate())+"."+montharray[serverdate.getMonth()]+"."+serverdate.getFullYear()
		var timestring=padlength(serverdate.getHours())+":"+padlength(serverdate.getMinutes())+":"+padlength(serverdate.getSeconds())
		document.getElementById("servertime").innerHTML=datestring+" "+timestring
	}

	window.onload=function()
	{
		setInterval("displaytime()", 1000)
	}
</script>