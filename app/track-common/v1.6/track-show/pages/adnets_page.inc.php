<?php
if (!$include_flag) {
    exit();
}
?>
<?php
$arr_timezone_settings = get_timezone_settings();
?>
<script>
    function add_adnet() {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: $('#add_adnet').serialize()
        }).done(function( msg ) {
            location.reload(true); 
            return false;
        });        
        return false;
    }

    function delete_adnet() {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {csrfkey:"<?php echo CSRF_KEY ?>", ajax_act: 'delete_adnet', id: $('input[name=adnet_id]').val()}
        }).done(function( msg ) {
            location.reload(true); 
        });        
        return false;
    }

    function edit_adnet(obj, id) {
        $('#add_adnet')[0].reset();
        $('#adnets_list tr').css('background-color', 'inherit');
        $(obj).css('background-color', 'rgb(255, 255, 204)');
    	
        $('input[name=name]', '#add_adnet').val($('#ad_name_' + id).text()).focus();
        $('input[name=url]', '#add_adnet').val($('#ad_url_' + id).text());
        //$('input[name=ajax_act]', '#add_adnet').val('edit_adnet');
        $('input[name=adnet_id]', '#add_adnet').val(id);
        
        $('button', '#add_adnet').text('Изменить');
        $('#cancel_adnet_edit').show();
        $('#delete_adnet').show();
    }
    
    function cancel_adnet_edit()
    {
        $('#adnets_list tr').css('background-color', 'inherit');
        $('#cancel_adnet_edit').hide();
        $('#delete_adnet').hide();
        $('input[name=ajax_act]', '#add_adnet').val('add_timezone');
        $('button', '#add_adnet').text('Добавить');
        $('#add_adnet')[0].reset();
    }
</script>
<h3>Отправка конверсий в рекламные сети</h3>

<div class="row">
    <div class='col-md-10' style='margin-left:0px;'>
        <table class='table table-bordered table-hover' id="adnets_list">
            <thead>
            <th>Название сети</th>
            <th nowrap>URL</th>
            </thead>
            <tbody>
                <?php
                $adnets = get_adnets();


                foreach ($adnets as $cur) {
                    echo "<tr style='cursor:pointer;' onclick='edit_adnet(this, {$cur['id']})'>
                        <td id='ad_name_{$cur['id']}'>" . _e($cur['name']) . "</td>
                        <td id='ad_url_{$cur['id']}'>" . _e($cur['url']) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="row">
            <form class="form-inline" role="form" id="add_adnet" onSubmit="return add_adnet();">
                <div class="form-group col-xs-4">
                    <input type="text" class="form-control" name="name" placeholder="Название сети">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="url" placeholder="URL">
                </div>
                <button type="button" onclick="add_adnet()" class="btn btn-default">Добавить</button>
                <span class="btn btn-link" id="cancel_adnet_edit" onclick="cancel_adnet_edit()" style='display:none;'>отменить</span>
                <span class="btn btn-link" id="delete_adnet" onclick="delete_adnet()" style='display:none; float:right;'><i class="fa fa-trash-o" title='Удалить'></i></span>
                <input type="hidden" name="ajax_act" value="add_adnet">    
                <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY; ?>">
                <input type="hidden" name="adnet_id" value="">
            </form>
        </div>


    </div>
</div> <!-- ./row -->