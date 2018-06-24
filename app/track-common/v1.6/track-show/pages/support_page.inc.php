<?php if (!$include_flag){exit();} ?>
<script>
function send_support_message()
{
	if ($('#support_message_text').val()=='')
	{
		$('#support_message_text').focus();
		return false;
	}

	$('#alert-message').hide();
	$.ajax({
          type: "POST",
          url: "index.php",
          data: $('#support_form').serialize()
        })
          .done(function( msg ) 
          {
			var result=msg.toString().split('|');
			if (result[0]=='1')
			{
				switch (result[1]){
					case '[message_recieved]': 
						$('#alert-message').html ('Сообщение успешно отправлено');
					break;

					default: 
						$('#alert-message').html (result[1]);
					break;
				}
				
				$('#support_form')[0].reset();
				$('#alert-message').show();
			}
			else
			{
				$('#alert-message').html(result[1]);
				$('#alert-message').show();
			}
			return false;
          });        
        return false;
}

$(function() 
{
	$("#support_message_text").focus();
});

</script>
<address>
  <strong>Официальный сайт проекта</strong><br>
  <a href="http://www.cpatracker.ru/" target="_blank">http://www.cpatracker.ru</a>
</address>
 
<address style="margin-bottom:0px;">
  <strong>Контактный e-mail</strong><br>
  <a href="mailto:support@cpatracker.ru">support@cpatracker.ru</a>
</address>

<div class="row">
	<div class="col-md-8">
		<div class="page-header page-header-with-icon">
		<strong>Быстрая связь с технической поддержкой</strong><br>
		</div>
		<form class="form form-validation form-contact" method="post" novalidate="novalidate" id='support_form' onSubmit='return send_support_message();'>
			<input type=hidden name='ajax_act' value='send_support_message'>
			<input type="hidden" name="user_email" id='user_email' value="<?php echo $auth_info[1];?>">
        	<input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group control-group">
						<textarea class="form-control" data-rule-required="true" rows="5" name="message" id='support_message_text' placeholder="Текст сообщения"></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<button class="btn btn-primary btn-block form-contact-submit" type="submit">Отправить</button>
					</div>
				</div>
		</form>
	</div>
</div>

<div class="row">
	<div class="col-md-8" style='margin-top:20px;'>
		<div class="alert alert-success" id='alert-message' style='display:none;'></div>
	</div>
</div>
<br />
<!--
	<div class="row">
		<a href="http://www.cpatracker.ru/support/<?php echo _e($installation_guid);?>" target='_blank' class='btn btn-link' style='color:gray;'><i class="icon-comment"></i> Посмотреть историю переписки</a>
	</div>
-->