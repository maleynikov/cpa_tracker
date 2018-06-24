<?php if (!$include_flag){exit();} ?>
<script>
  $(document).ready(function() 
  {
    $('input[name="email"]').focus(); 
  });

  function check_form()
  {
    $('#email').css('background-color', 'white');
    $('#password').css('background-color', 'white');

    if (($('#email').val()!='') && ($('#password').val()!=''))
    {
      return true;
    }

    if ($('#password').val()=='')
    {
      $('#password').css('background-color', 'lightyellow');
      $('#password').focus();
    }

    if ($('#email').val()=='')
    {
      $('#email').css('background-color', 'lightyellow');
      $('#email').focus();
    }

    return false;
  }
</script>

    <div id="legend">
      <legend class="">Заполните данные администратора</legend>
    </div>
<div class="row">

  <div class="col-md-4">
    <form role="form" method="POST" id="register_admin" onSubmit="return check_form();">
      <input type=hidden name='page' value='register'>
      <input type=hidden name='act' value='register_admin'>

      <div class="form-group">
        <label for="exampleInputEmail1">E-mail</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Введите e-mail">
      </div>
      <div class="form-group">
        <label for="exampleInputPassword1">Пароль для входа</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Введите пароль">
      </div>
      <div class="form-group">
        <input type="checkbox" id="subscribe" name="subscribe" checked="checked">
        <label for="subscribe" style="display: inline">Получать информацию об обновлениях трекера на e-mail</label>
      </div>
      <button type="submit" class="btn btn-success">Сохранить</button>
    </form>
  </div>
</div>