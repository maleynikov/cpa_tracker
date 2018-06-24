<?php if (!$include_flag){exit();} ?>
<h3>Системные сообщения</h3>
<?php
	if (count ($global_notifications)>0)
	{
		echo '<div class="panel-group" id="accordion">';
		$i=0;
		foreach ($global_notifications as $cur)
		{
			$i++;
			switch ($cur){
				case 'CRONTAB_CLICKS_NOT_INSTALLED': 
					$process_clicks_path = realpath(_TRACK_SHOW_PATH.'/process_clicks.php');
					$title = 'Статистика переходов не обновляется';
					$description='<p>Добавьте в cron запуск следующего файла:<br /><code>'.$process_clicks_path.'</code>,<br /> с интервалом в одну минуту.</p><p>Данный скрипт отвечает за импорт данных о переходах в базу данных.</p><p>Строка запуска может выглядеть примерно так:</p><p><code>*/1 * * * * /usr/bin/php5 /var/www/cpatracker.ru/track-show/process_clicks.php &gt;/dev/null</code></p><p>Для редактирования cron файла используйте панель управления сервером или команду "crontab -e" из консоли.</p><p>После первого успешного обновления статистики данное сообщение исчезнет. При возникновении проблем обратитесь в службу технической поддержки вашего хостинга.</p>';
				break;
				
				case 'CRONTAB_POSTBACK_NOT_INSTALLED': 
					$process_postback_path = realpath(_TRACK_SHOW_PATH.'/process_postback.php');
					$title = 'Статистика продаж не обновляется';
					$description='<p>Добавьте в cron запуск следующего файла:<br /><code>'.$process_postback_path.'</code>,<br /> с интервалом в одну минуту.</p><p>Данный скрипт отвечает за импорт данных о продажах в базу данных.</p><p>Строка запуска может выглядеть примерно так:</p><p><code>*/1 * * * * /usr/bin/php5 /var/www/cpatracker.ru/track-show/process_postback.php &gt;/dev/null</code></p><p>Для редактирования cron файла используйте панель управления сервером или команду "crontab -e" из консоли.</p><p>После первого успешного обновления данных о продажах данное сообщение исчезнет. При возникновении проблем обратитесь в службу технической поддержки вашего хостинга.</p>';
				break;

				default: 
					continue;
				break;
			}
		?>
		  <div class="panel panel-default">
		    <div class="panel-heading">
		      <h4 class="panel-title">
		        <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $i;?>">
		          <?php echo $title;?>
		        </a>
		      </h4>
		    </div>
		    <div id="collapse<?php echo $i;?>" class="panel-collapse collapse <?php if ($i==1){echo "in";}?>">
		      <div class="panel-body">
		      	<?php echo $description;?>
		      </div>
		    </div>
		  </div>
		<?php
		}
		echo '</div>';
	}
?>