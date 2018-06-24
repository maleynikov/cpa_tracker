<?php
require_once _TRACK_LIB_PATH.'/class/common.php';
require_once _TRACK_LIB_PATH.'/class/custom.php';
$available_nets = array();
$networks = dir(_TRACK_LIB_PATH.'/postback');

while ($file = $networks->read())
{
    if ($file != '.' && $file != '..')
    {
        $file = str_replace('.php', '', $file);
        $name = $file;
        if ($file == 'GdeSlon')
        {
            $name = 'Где Слон?';
        }
        $name=str_replace ('_','',$file );
        $available_nets[$file] = $name;
    }
}

asort($available_nets);
$custom = new custom();
?>
<link href="<?php echo _HTML_LIB_PATH;?>/select2/select2.css" rel="stylesheet"/>

<script src="<?php echo _HTML_LIB_PATH;?>/mustache/mustache.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/select2/select2.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/clipboard/ZeroClipboard.min.js"></script>

<script type="text/javascript">
    var links;
    var base_custom = "<?php echo  $custom->get_links(); ?>";
    $(document).ready(function()
    {
        // init ZeroClipboard
        var clip = new ZeroClipboard(document.getElementById("copy-button"), {
            moviePath: "<?php echo _HTML_LIB_PATH;?>/clipboard/ZeroClipboard.swf"
        });

        $('.net-btn').click(function() {
            var btn = this;
            $('#search-row').hide();
            $('#master-row').show();
            $('#master-form').hide();
            $.post(
                    'index.php?ajax_act=postback_info',
                    {
                        net: $(this).attr('net'),
                        csrfkey: '<?php echo  CSRF_KEY ?>'
                    },
            function(data) {
                if (data.status == 'OK') {

                    links = data.links;

                    var fullName=$(btn).attr('net').replace("_", "");

                    $('.net-name').text(fullName);
                    $('#netlink_name').text(fullName);
                    $('#netlink_text').html(data.net_text);
                    $('#netlink_href').attr('href', data.reg_url);
                    var template = $('#linkTemplate').html();
                    var template_data = data;

                    var html = Mustache.to_html(template, template_data);

                    $('#links').html(html);

                    $('button[id^="copy-button"]').each(function(i)
                    {
                        var cur_id = $(this).attr('id');
                        var clip = new ZeroClipboard(this, {
                            moviePath: "<?php echo _HTML_LIB_PATH;?>/clipboard/ZeroClipboard.swf"
                        });

                        clip.on('mouseout', function(client, args) {
                            $('.btn-rule-copy').removeClass('zeroclipboard-is-hover');
                        });
                    });

                    $('#result-row').show();
                }
            },
                    'json'
                    );
        });


        $('#is_lead').change(function() {
            show_urls($('#is_lead').is(':checked'), $('#is_sale').is(':checked'));
        });
        $('#is_sale').change(function() {
            show_urls($('#is_lead').is(':checked'), $('#is_sale').is(':checked'));
        });

        $('#custom-master-start').click(function() {
            $('#search-row').hide();
            $('#net-row2').hide();
            $('#result-row').hide();
            $('#master-form').show();
        });
        
        var checkbox_change = function() {
            var cur_url = base_custom;

            $('#master-form input[type=checkbox]').each(function(i) {
                if ($(this).is(':checked')) {
                    cur_url = cur_url + '&' + $(this).attr('id') + '=' + $('#' + $(this).attr('id') + '_val').val();
                }
                $('#custom-link-val').val(cur_url);
            });

        };

        $('#master-form input[type=checkbox]').change(checkbox_change);

        $('#master-form input[type=text]').change(function() {
            var cur_url = base_custom;
            $('#master-form input[type=checkbox]').each(function(i) {
                if ($(this).is(':checked')) {
                    cur_url = cur_url + '&' + $(this).attr('id') + '=' + $('#' + $(this).attr('id') + '_val').val();
                }
                $('#custom-link-val').val(cur_url);
            });

        });
		
		
		checkbox_change();
    });


    function show_urls(is_lead, is_sale) {
        $.each(links, function(i, item) {
            var url = item.url;
            if (is_lead) {
                url = url + '&is_lead=1';
            }

            if (is_sale) {
                url = url + '&is_sale=1';
            }
            $('#net-link-' + item.id).val(url);
        })
    }
</script>

<script id="linkTemplate" type="text/template">
    {{#links}}
    <div>
    <em id="instruction">{{{description}}}</em>
    <div class="input-group">
    <span class="input-group-btn">
    <button id="copy-button" class="btn btn-default clpbrd-copy" id="{{id}}" data-clipboard-target='net-link-{{id}}' title="Скопировать в буфер" type="button"><i class='fa fa-copy' id='clipboard_copy_icon'></i></button>
    </span>
    <input type="text" style="width:100%;" class="form-control" id="net-link-{{id}}" value="{{url}}" readonly><br>
    </div>
    </div>
    {{/links}}
</script>


<div class="row">
    <div class="col-md-12">
        <h3>Настройка Postback</h3>
    </div>
</div>

<div class="row" id="net-row2">
    <div class="col-md-12">
        В данном разделе вы можете настроить автоматический импорт информации о продажах из поддерживаемых CPA сетей.
    </div>
</div>
<br>
<div class="row" id="net-row">
    <div class="col-md-12">
        <div class="btn-group">
            <?php $i = 0; ?>
            <?php foreach ($available_nets as $net => $name) : ?>
                <button class="btn btn-default net-btn" net="<?php echo $net ?>"><?php echo  $name; ?></button>
                <?php $i++; ?>
                <?php if ($i % 7 == 0): ?>
                </div>
                <div class="btn-group">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-default net-btn" id="custom-master-start">Универсальная ссылка</button>
    </div>
</div>
<br>

<div class="row" id="result-row" style="display:none;">
    <div class="col-md-12">
        Postback ссылка для сети <b><span class="net-name"></span></b>:<br><br>
        <div id="links">

        </div>
        <div class="panel panel-primary" style="margin-top: 30px;">
            <div class="panel-heading">
                <h3 class="panel-title">Партнерская сеть <span class="net-name"></span></h3>
            </div>
            <div class="panel-body">
                <span id="netlink_text"></span>
                <div>
                    <a class="btn btn-primary pull-right" id="netlink_href" href="" target="_blank" style="padding: 5px 10px; margin-top: 15px;">Зарегистрироваться в <span id="netlink_name"></span> →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="master-form" style="display:none;">
    <div class="col-md-12">
        <div class="input-group">
            <span class="input-group-btn">
                <button id="copy-button" class="btn btn-default clpbrd-copy" id="custom-link" data-clipboard-target='custom-link-val' title="Скопировать в буфер" type="button"><i class='fa fa-copy' id='clipboard_copy_icon'></i></button>
            </span>
            <input type="text" style="width:100%;" class="form-control" id="custom-link-val" value="<?php echo  $custom->get_links(); ?>" ><br>
        </div><br>
        Выберите какие параметры отслеживать (помимо параметров из таблицы трекер хранит все параметры начинающиеся с префикса pbsave_):<br>

        <table class="table table-hover table-striped">
            <tr>
                <td><input type="checkbox" id="profit" checked="checked"></td>
                <td>Сумма конверсии:</td>
                <td><input type="text" id="profit_val" value="{profit}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param20"></td>
                <td>Валюта:</td>
                <td><input type="text" id="txt_param20_val" value="{currency}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="subid" checked="checked"></td>
                <td>SubID:</td>
                <td><input type="text" id="subid_val" value="{subid}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="status"></td>
                <td>Статус:</td>
                <td><input type="text" id="status_val" value="{status}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="date_add"></td>
                <td>Дата:</td>
                <td><input type="text" id="date_add_val" value="{date}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param1"></td>
                <td>IP:</td>
                <td><input type="text" id="txt_param1_val" value="{ip}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param2"></td>
                <td>User Agent:</td>
                <td><input type="text" id="txt_param2_val" value="{uagent}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param4"></td>
                <td>Название оффера:</td>
                <td><input type="text" id="txt_param4_val" value="{offer_name}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param7"></td>
                <td>Источник:</td>
                <td><input type="text" id="txt_param7_val" value="{source}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="int_param1"></td>
                <td>ID цели:</td>
                <td><input type="text" id="int_param1_val" value="{goal_id}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="int_param2"></td>
                <td>ID оффера:</td>
                <td><input type="text" id="int_param2_val" value="{offer_id}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="int_param3"></td>
                <td>ID заказа:</td>
                <td><input type="text" id="int_param3_val" value="{order_id}"></td>
            </tr>
        </table>
    </div>    
</div>