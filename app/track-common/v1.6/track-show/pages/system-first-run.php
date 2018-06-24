<?php if (!$include_flag){exit();} ?>
<p>Введите информацию о подключении к базе данных. Если вы в ней не уверены, свяжитесь со службой поддержки вашего хостинга.</p>

<div id="info_message" class="alert alert-warning" role="alert">
  <span class='btn_close' onclick='$("#info_message").hide();'>&times;</span>
  <span id="info_message_text"></span>
</div>

<div class="row">
  <div class="col-md-3">
    <form role="form" id="form_settings" onsubmit="return save_settings();">
	  <input type='hidden' name='ajax_act' value='create_database'>          
      <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
      <div class="form-group">
        <label>Имя базы данных</label>
        <input type="text" id="dbname" name='dbname' class="form-control">
      </div>
      <div class="form-group">
        <label>Имя пользователя</label>
        <input type="text" id="login" name='login' class="form-control">
      </div>
      <div class="form-group">
        <label>Пароль</label>
        <input type="text" id="password" name='password' class="form-control">
      </div>
      <div class="form-group">
        <label>Сервер базы данных</label>
        <input type="text" id="dbserver" name="dbserver" value="localhost" class="form-control">
      </div>   

      <div class="form-group">
        <label>Тип вашего сервера</label>
	        <select id="server_type" name="server_type" class="form-control">
			  <option value='apache' selected>Apache</option>
			  <option value='nginx'>Nginx</option>
			</select>
			<span class='help-block' onclick='$("#server_type_help").toggle();'><span style='cursor:pointer; border-bottom:1px lightgray dashed; font-family:"Tahoma";'>как&nbsp;узнать?</span></span>
			<div id='server_type_help' class='well'>
		   	<span class='btn_close' onclick='$("#server_type_help").hide();'>&times;</span>
		   	<p>Определите ваш текущий IP адрес на <a href='http://www.whatismyip.com' target='_blank'>www.whatismyip.com</a></p>
		   	<p>Найдите ваш IP адрес в таблице:</p>
		   	<table class='table'>
		   		<thead>
		   			<tr><th>IP адрес</th><th>Тип сервера</th></tr></thead>
		   		<tbody>
					<tr>
						<td><?php echo $_SERVER['REMOTE_ADDR'];?></td>
						<td>Apache</td>
					</tr>
					<tr>
						<td><?php echo $_SERVER['HTTP_X_FORWARDED_FOR'];?></td>
						<td>Nginx</td>
					</tr>
		   		</tbody>
		   	</table>
		   </div>			
      </div>   

      <button type="submit" class="btn btn-success">Сохранить</button>
    </form>
  </div>
</div>

<br />

<style>
	#info_message, #server_type_help{
		display: none;
		margin-top:10px;
	}

	.btn_close{
	   	float: right;
		font-size: 20px;
		font-weight: bold;
		line-height: 20px;
		color: #000000;
		text-shadow: 0 1px 0 #ffffff;
		opacity: 0.2;
		filter: alpha(opacity=20); cursor:pointer;
	}
</style>
<script>
	$(document).ready(function() 
	{
		$('#dbname').focus();
	});
	function save_settings()
	{
		$('#info_message').hide();
		
		if ($('#dbname').val()=='')
		{
			$('#dbname').focus();
			return false;
		}	

		if ($('#login').val()=='')
		{
			$('#login').focus();
			return false;
		}				

		if ($('#dbserver').val()=='')
		{
			$('#dbserver').focus();
			return false;
		}
		
		$.ajax({
		  type: 'POST',
		  data: $('#form_settings').serialize()
		}).done(function( msg ) 
		{
			var result=jQuery.parseJSON(msg);
			if (result[0])
			{
				window.location.replace(result[1]);
				return; 
			}
			else
			{
				switch (result[1])
				{
					case 'config_found': 
						$('#info_message_text').html('Файл с настройками уже найден:<br />'+result[2]+'<br />Перед сохранением новых параметров его необходимо удалить.');
						$('#info_message').show();
					break;
					
					case 'cache_not_writable': 
						$('#info_message_text').html('Установите права на запись (777) для папки <br />'+result[2]);
						$('#info_message').show();
					break;

					case 'db_error':
						$('#info_message_text').html('Ошибка базы данных<br />'+result[2]);
						$('#info_message').show();
					break;

					case 'db_not_found': 
						$('#info_message_text').html('База данных '+result[2]+' не найдена. Вам необходимо ее создать.');
						$('#info_message').show();
					break;
                    
					case 'htaccess_not_found':
						$('#info_message_text').html('Проверьте наличие файлов .htaccess в каталогах /track и /track-show.');
						$('#info_message').show();
					break;
					
					case 'wurfl_not_writable':
						$('#info_message_text').html('Файл ' + result[2] + ' не доступен для записи.');
						$('#info_message').show();
					break;
                    
					case 'table_not_create':
						$('#info_message_text').html('Не удается создать таблицу в базе данных. Проверьте права доступа для пользователя БД.');
						$('#info_message').show();
					break;
					
					case 'schema_not_found': 
						$('#info_message_text').html('Файл database.php со структурой базы данных не найден.<br />Установите последнюю версию скрипта с официального сайта.');
						$('#info_message').show();					
					break;

					default: 
						$('#info_message_text').html('Неизвестная ошибка. Напишите на support@cpatracker.ru');
						$('#info_message').show();					
					break;					
				}
			}
		});
		

		return false;
	}
</script>