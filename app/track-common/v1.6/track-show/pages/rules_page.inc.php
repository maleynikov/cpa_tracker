<link href="<?php echo _HTML_LIB_PATH;?>/select2/select2.css" rel="stylesheet"/>
<link href="<?php echo _HTML_LIB_PATH;?>/select2/select2-bootstrap.css" rel="stylesheet"/>

<script src="<?php echo _HTML_LIB_PATH;?>/mustache/mustache.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/select2/select2.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/clipboard/ZeroClipboard.min.js"></script>
<style>
	.rule-link-text {
		width: 500px; /* 345px; */
		border: none;
		margin-top: 10px;
	}
	.btn-rule-copy.direct {
		background: none !important;
		min-width: 40px !important;
	}
	.btn-rule-copy.direct.active {
		font-weight: bold;
		color: black !important;
	}
	
	.btn-rule-copy.for_text.active {
		color: black !important;
	}
	.btn-rule-copy.for_text {
		border: medium none;
	    border-radius: 0;
	    color: #888;
	    float: right;
	    margin: 0;
	    min-width: 50px;
	    padding: 10px 0;
	}
</style>
<script>
    var last_removed = 0;
	window.path_level = <?php echo count(explode('/', tracklink())); ?>;
    $(document).ready(function()
    {
        $('input[name=rule_name]').focus();
        $.ajax({
            type: "POST",
            url: "index.php",
            data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=get_rules_json'
        }).done(function(msg) {
            var template = $('#rulesTemplate').html();
            var template_data = $.parseJSON(msg);

            var html = Mustache.to_html(template, template_data);
            $('#rules_container').html(html);

            // Init ZeroClipboard
            $('button[id^="copy-button"]').each(function(i)
            {
                var cur_id = $(this).attr('id');
                var clip = new ZeroClipboard(document.getElementById(cur_id), {
                    moviePath: "<?php echo _HTML_LIB_PATH;?>/clipboard/ZeroClipboard.swf"
                });

                clip.on('mouseout', function(client, args) {
                    $('.btn-rule-copy').removeClass('zeroclipboard-is-hover');
                });
            });


            $('.delbut').on("click", function(e) {
                e.stopImmediatePropagation();
                var id = $(this).attr('id');
                delete_rule(id);            
            });
            $('.rname').on("click", function(e) {
                e.stopPropagation();
                var id = $(this).attr('id');
                var rule_name = $('#rule'+id).find('.rule-name-title');
                var rule_name_text = $(rule_name).text();
                var rule_old_name = rule_name_text;
                var stop_flag = false;
               // $(rule_name).attr('contenteditable','true');
               $(rule_name).html('<input id="rn'+id+'" class="form-control rulenamein" placeholder="Имя не может быть пустым"  value="'+rule_old_name+'" >')
               $("#rn"+id).focus(); 
               $("#rn"+id).select();
               $("#rn"+id).click(function(e){
                   e.stopPropagation();
               })
                 $("#rn"+id).keypress(function(e) {
                     e.stopPropagation();
                    if(e.which == 13) {                      
                     e.stopImmediatePropagation();
                     e.preventDefault();
                     rule_name_text =  $("#rn"+id).val().replace(/^\s+|\s+$/g, '');
                      if(rule_name_text.length){
                     $(rule_name).html($("#rn"+id).val());
                     stop_flag = true;
                    }else{
                        alert("Имя не может быть пустым.");
                         $("#rn"+id).val(rule_old_name);
                        $(rule_name).focus();                         
                    }
                }
                });
                $("#rn"+id).focusout(function(e) {
                    e.stopPropagation();
                 e.stopImmediatePropagation();
                 rule_name_text = $("#rn"+id).val().replace(/^\s+|\s+$/g, '');
                      if(rule_name_text.length){
                 save_name(id,$("#rn"+id).val().replace(/^\s+|\s+$/g, ''),rule_old_name);
                 if(!stop_flag){$(rule_name).html($("#rn"+id).val())}
                }else{
                        alert("Имя не может быть пустым.");
                        $("#rn"+id).val(rule_old_name);
                        $("#rn"+id).focus();                         
                }
            })
            });        
            function save_name(id,name,old_name){
                $.ajax({
                    type: 'POST',
                    url: 'index.php',
                    data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=update_rule_name&rule_id=' + id + '&rule_name=' + name + '&old_rule_name=' + old_name
                })
            }
            function users_label(obj) {
            	obj.parent().find('.users_label').text('Остальные посетители');
            }
            function prepareTextInput(tr,name,title){ 
            	users_label(tr);
                tr.find('.label-default').text(title);
                tr.find('input.select-item').attr('placeholder',title);
                tr.find('input.select-item').attr('itemtype',name);
                tr.find('input.select-link').select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});
                tr.find('input.select-sources').select2({data: {results: dictionary_sources}, width: 'copy', containerCssClass: 'form-control select2'});
            } 
           // buttons {// 
            
            $('.addcountry').on("click", function(e) {
                e.preventDefault();
                var template = $('#countryTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table = $('#rule' + rule_id + ' tbody');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('input.select-geo_country').select2({data: {results: dictionary_countries}, width: '250px', containerCssClass: 'form-control select2 noborder-select2'});
                rule_table.find('input.select-link').select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});               
                rule_table.find('input.select-sources').select2({data: {results: dictionary_sources}, width: 'copy', containerCssClass: 'form-control select2'});
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val());
rule_table.find('input.select-sources').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ; // ???
            });
            $('.addlang').on("click", function(e) {
                e.preventDefault();
                var template = $('#langTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table = $('#rule' + rule_id + ' tbody');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('input.select-lang').select2({data: {results: dictionary_langs}, width: '250px', containerCssClass: 'form-control select2 noborder-select2'});
                rule_table.find('input.select-link').select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});
                rule_table.find('input.select-sources').select2({data: {results: dictionary_sources}, width: 'copy', containerCssClass: 'form-control select2'});
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ; 
                rule_table.find('input.select-sources').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ; // ???
            });
            $('.addrefer').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table = $('#rule' + rule_id + ' tbody');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('input.select-link').select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});               
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;           
            });
             $('.addcity').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table =  $('#rule' + rule_id + ' tbody');
                rule_table.prepend(template);
                var tr = rule_table.find('tr').first();
                prepareTextInput(tr,'city','Город');               
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;    
            });
             $('.addregion').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table =  $('#rule' + rule_id + ' tbody');
                rule_table.prepend(template);
                var tr = rule_table.find('tr').first();
                prepareTextInput(tr,'region','Регион');               
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;    
            });
            $('.addprovider').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table =  $('#rule' + rule_id + ' tbody');
                rule_table.prepend(template);
                var tr = rule_table.find('tr').first();
                prepareTextInput(tr,'provider','Провайдер');               
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;    
            });
            $('.addip').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table =  $('#rule' + rule_id + ' tbody');
                rule_table.prepend(template);
                var tr = rule_table.find('tr').first();
                prepareTextInput(tr,'ip','IP адрес');               
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;    
            });
            $('.adddevice').on("click", function(e) {
                e.preventDefault();
                var template = $('#deviceTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table = $('#rule' + rule_id + ' tbody');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('input.select-device').select2({data: {results: dictionary_device}, width: '250px', containerCssClass: 'form-control select2 noborder-select2'});
                rule_table.find('input.select-link').select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val());
            }); 
            $('.addos').on("click", function(e) {
                e.preventDefault();
                var template = $('#osTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table = $('#rule' + rule_id + ' tbody');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('input.select-os').select2({data: {results: dictionary_os}, width: '250px', containerCssClass: 'form-control select2 noborder-select2'});
                rule_table.find('input.select-link').select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val());
            }); 
            $('.addplatform').on("click", function(e) {
                var template = $('#referTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table =  $('#rule' + rule_id + ' tbody');
				rule_table.prepend(template);
				var tr = rule_table.find('tr').first();
                prepareTextInput(tr,'platform','Платформа');               
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;    
            });
             $('.addbrowser').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table =  $('#rule' + rule_id + ' tbody');
                rule_table.prepend(template);
                var tr = rule_table.find('tr').first();
                prepareTextInput(tr,'browser','Браузер');               
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;    
            });
             $('.addagent').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table =  $('#rule' + rule_id + ' tbody');
                rule_table.prepend(template);
                var tr = rule_table.find('tr').first();
                prepareTextInput(tr,'agent','User-agent'); 
                
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;    
            });
             $('.addget').on("click", function(e) {
                e.preventDefault();
                var template = $('#getTemplate').html();
                var rule_id = $(this).parent().parent().attr('id');
                var rule_table =  $('#rule' + rule_id + ' tbody');
                users_label(rule_table);
                rule_table.prepend(template);       
                
                rule_table.find('input.select-link').select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});
                rule_table.find('input.select-link').first().select2('val',$('#rule'+rule_id).find('[name = default_out_id]').val()) ;    
            });
            
            // buttons }//  
            $('body').on("change",'.getpreinput',function() { 
                var text = $(this).parent().find('.in1').val()+'='+$(this).parent().find('.in2').val();
                $(this).parent().find('.select-item').val(text);
            });
            
            $('.btnsave').on("click", function(e) {
                e.preventDefault();
                var flag = true;
                var rule_id = $(this).attr('id');
                var rule_table = $('#rule' + rule_id + ' tbody');
                /*
                $(rule_table).find('input.in1').each(function() {                                      
                     if(!(/(^[a-z0-9_]+$)/.test($(this).val()))){
                          flag = false;  
                        }                     
                });
                 $(rule_table).find('input.in2').each(function() {                                      
                     if(!(/(^[a-z0-9_]+$)/.test($(this).val()))){
                          flag = false;  
                        }                     
                });*/
                if(!flag){ alert("В полях ввода для ссылки GET можно использовать только цифры и буквы латинского алфавита.");  return false;}
                $(rule_table).find('input.select-link').each(function() {                                      
                       $(this).addClass('toSave');                      
                });
                $(rule_table).find('input.select-item').each(function() {                                         
                       $(this).addClass('toSave');                     
                });
                if(update_rule(rule_id) && !$(rule_table).find('.fa-check').size()){
                    $(this).after('<i style="position: relative; right: 20px; top: 9px;"  class="fa fa-check pull-right"></i>');
                }
            });
            $('body').on("click", '.btnrmcountry', function(e) {
                e.preventDefault();
                var rule_id = $(this).closest("tr").parent().attr('id');
                $(this).closest("tr").remove();
                update_rule(rule_id);
            });          
            $(".table-rules th").on("click", function() {
                $(this).closest("table").children("tbody").toggle();
                $(this).closest("table").toggleClass("rule-table-selected");
            });

            
            // Fill values for destination links
            var dictionary_links = [];
            dictionary_links.push(<?php echo $js_offers_data; ?>);
            
            var dictionary_sources = <?php echo $js_sources_data; ?>;
  
            
            $('input.select-link').each(function()
            {
                $(this).select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});

                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            $('button.btn-rule-copy.direct').each(function() {
            	$(this).on('click', function(e) {change_source(e, false)});
            });
            
            $('input.select-sources').each(function()
            {
                $(this).select2({data: {results: dictionary_sources}, width: 'copy', containerCssClass: 'form-control select2'});
                $(this).select2("val", $(this).attr('data-selected-value'));
                $(this).on("select2-selecting", function(e) {change_source(e, true)});
            });
            
            var dictionary_countries = [];
           
            dictionary_countries.push(<?php echo  $js_countries_data; ?>); 
            
            $('input.select-geo_country').each(function()
            {
                $(this).select2({data: {results: dictionary_countries}, width: '250px', containerCssClass: 'form-control select2 noborder-select2'});
                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            dictionary_langs = [];
            dictionary_langs.push({text:"", children:[{id:"en", text:"Английский, en"},{id:"ru", text:"Русский, ru"},{id:"uk", text:"Украинский, uk"}]});
            dictionary_langs.push(<?php echo $js_langs_data; ?>);
            
            $('input.select-lang').each(function()
            {
                $(this).select2({data: {results: dictionary_langs}, width: '250px', containerCssClass: 'form-control select2 noborder-select2'});
                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            dictionary_os = [];
            dictionary_os.push({text:"", children:[
            	{id:"DEFINED_IOS",     text:"iOS"},
            	{id:"DEFINED_ANDROID", text:"Android"},
            	{id:"DEFINED_WINDOWS", text:"Windows"},
            	{id:"DEFINED_MACOS",   text:"Mac OS"},
            	{id:"DEFINED_LINUX",   text:"Linux"},
            	{id:"DEFINED_MOBILE",  text:"Все мобильные"},
            	{id:"DEFINED_DESKTOP", text:"Все десктопные"}
            ]});
            
            $('input.select-os').each(function() {
                $(this).select2({data: {results: dictionary_os}, width: '250px', containerCssClass: 'form-control select2 noborder-select2'});
                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            dictionary_device = [];
            dictionary_device.push({text:"", children:[
            	{id:"DEFINED_IPHONE",  text:"Apple iPhone"},
            	{id:"DEFINED_IPAD",    text:"Apple iPad"},
            ]});
            
            $('input.select-device').each(function() {
                $(this).select2({data: {results: dictionary_device}, width: '250px', containerCssClass: 'form-control select2 noborder-select2'});
                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            $('input.in1').each(function() {
                var text = $(this).parent().find('.select-item').val();
                var arr = text.split('=');
                $(this).val(arr[0]);
            });
            $('input.in2').each(function() {
                var text = $(this).parent().find('.select-item').val();
                var arr = text.split('=');
                $(this).val(arr[1]);
            });
        });
    });
	
	function change_source(obj, select2) {
		if(select2) {
			var source = obj.val;
			obj = obj.target;
			var table = $(obj).parent().parent().parent().parent().parent();
			var id = table.find('.btnsave').attr('id');
		} else {
			obj = obj.target;
			var table = $(obj).parent().parent().parent().parent();
			var id = table.find('.btnsave').attr('id');
			var source = $('#rule-link-select2-' + id).val();
			$('#rule-link-direct-' + id).toggleClass("active");
		}
		var direct = $('#rule-link-direct-' + id).hasClass("active") ? 1 : 0;
		table.find('.rule-link').each(function() {
			lnk = $(this).val();
			parts = lnk.split('/');
		});
		
		$.ajax({
            type: "POST",
            url: "index.php",
            data: 'ajax_act=get_source_link&source=' + source + '&name=' + parts[path_level] + '&id=' + id + '&direct=' + direct
        }).done(function(msg) {
        	table.find('.rule-link-text').val(msg);
	    });
	}
    
    function delete_rule(rule_id)
    {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=delete_rule&id=' + rule_id
        }).done(function(msg)
        {
            $('#rule' + rule_id).hide();
            var rule_name = $('#rule'+rule_id).find('.rule-name-title');
            var rule_name_text = $(rule_name).text();
            $('#rule_name').text(rule_name_text);
            $('#restore_alert').show();
            last_removed = rule_id;
        });

        return false;
    }
    
    
    function restore_rule() {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=restore_rule&id=' + last_removed
        }).done(function(msg)
        {
            $('#rule' + last_removed).show();
            $('#restore_alert').hide();
            last_removed = 0;
        });

    }
 
    function update_rule(rule_id)
    {

        var links = [];
        var sources = [];
        var rules_items = '';
        var values = '';
        var error = '';
        var rule_table = $('#rule' + rule_id + ' tbody');
        var name = $(rule_table).prev().find('.rule-name-title').text();
        var i = 0;
        $(rule_table).find('input.in1').each(function() {        
            if (!$(this).val()) {
                error = 'Выберите условие';
            }
        });
        $(rule_table).find('input.in2').each(function() {        
            if (!$(this).hasClass('canzero') && !$(this).val()) {
                error = 'Выберите условие';
            }
        });
        $(rule_table).find('input.select-item.toSave').each(function() {        
            if ($(this).val()) {
                rules_items = rules_items + '&rules_item['+i+"][val]=" + $(this).val();
                rules_items = rules_items + '&rules_item['+i+"][type]=" + $(this).attr('itemtype');
                i++;
            } else {
                error = 'Выберите условие';
            }
        });
        $(rule_table).find('input.select-link.toSave').each(function() {
            if ($(this).val()) {
                if (!in_array($(this).val(), links)) {
                    links.push($(this).val());
                }
                values = values + '&rule_value[]=' + $(this).val();
            } else {
                error = 'Выберите оффер';
            }
        });
        if (error) {
            alert(error);
            return false;
        } else {
            rules_items = rules_items + '&rules_item['+i+"][val]=default";
            rules_items = rules_items + '&rules_item['+i+"][type]=geo_country" ;
            $.ajax({
                type: 'POST',
                url: 'index.php',
                data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=update_rule&rule_id=' + rule_id + '&rule_name=' + name + rules_items + values
            }).done(function(msg)
            {
            	eval('answer = ' + msg);
            	//console.log(answer);
            	if(answer.status) {
	                if (links.length > 1) {
	                    var badge = '<span class="badge">' + (links.length) + ' ' + declination((links.length), 'оффер', 'оффера', 'офферов') + '</span>';
	                    $(rule_table).parent().find('.rule-destination-title').html(badge);
	                }
                } else {
                	alert(answer.error);
                }
            });
        }
        return true;
    }

    function declination(number, one, two, five) {
        number = Math.abs(number);
        number %= 100;
        if (number >= 5 && number <= 20) {
            return five;
        }
        number %= 10;
        if (number == 1) {
            return one;
        }
        if (number >= 2 && number <= 4) {
            return two;
        }
        return five;
    }
    
    function in_array(needle, haystack, strict) {
        var found = false, key, strict = !!strict;
        for (key in haystack) {
            if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
                found = true;
                break;
            }
        }
        return found;
    }
    function validate_add_rule() {
        var nameR = /^[a-z0-9\-\_]+$/i;
        if ($('#rule_name_id').val() == '') {
            return false;
        }
        if (!nameR.test($('input[name=rule_name]', $('#form_add_rule')).val())){
          $('#incorrect_name_alert').show();
          $('input[name=rule_name]', $('#form_add_rule')).focus();
          return false;
        }
        return true;
    }
</script>

<style>

    .btn-rule-copy{
        border: none; 
        border-radius:0px; 
        padding:10px 0px; 
        margin:0px; 
        min-width:50px; 
        color:#999;
        background: none;
        float:left;
    }

    .btn-rule-settings{
        border: none; 
        border-radius:0px; 
        margin:0px; 
        text-align: left;
        display: inline-block;
        float:left;
    }
    
    .btn-rule-copy.zeroclipboard-is-hover {background-color:#428bca !important; color:white !important;}
    .btn-rule-copy.zeroclipboard-is-active { background-color:#2e618d !important;}

    .rule-name-title{
        padding:10px 10px 10px 5px;
        border-radius:0px; 
        display: inline-block;
        float:left;
        border:none;
        min-width: 120px;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;    
    }

    .rule-destination-title{
        padding:10px; 
        margin:0px;
        border-radius:0px; 
        display: inline-block;
        float:left;
        border:none;
        font-weight:normal;
        min-width: 200px;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;        
    }    

    .btn-rule-settings:hover i{
        color: gray;
    }

    table.table-rules{
        margin-bottom: 0px;
        border-bottom: none;
    }

    table.table-rules:last-child{
       /* border-bottom: 1px solid #ddd; */
    }
    table.table-rules th:hover{
        background: linen;
    }
    .table-rules tbody{
        margin-bottom:10px;
        /* [!] */
        display: none;
        border:1px solid lightgray;
    }
    
    .rule-table-selected{
        margin-bottom: 10px !important;
    }

    .rule-table-selected thead{
            border:1px solid lightgray;
            border-bottom: none;
    }

    .rule-table-selected th, .rule-table-selected .btn-rule-copy{
        background-color:linen; 
    }

    .table-rules thead th {
        padding:0px inherit !important; 
        cursor:pointer; 
        border:none !important;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;        
    }
    
    .table-rules thead th div.btn-group{
        display:inline-block; 
        float:left;
    }

    .btn-default {
        background-color:inherit;
    }
    
    .trash-button .btn {
        border:none;
        border-radius:0px; 
    }    
</style>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Удаление ссылки</h4>
      </div>
        <input type="hidden" id="hideid">
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button"  class="btn btn-danger yeapdel">Удалить</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script id="getTemplate" type="text/template">
     {{#conditions}}
                    <tr>
                        <td>
                            <div class="form-inline" role="form">                            
                                <div class="btn-group trash-button">
                                    <button class='btn btn-default btnrmcountry'><i class="fa fa-trash-o text-muted"></i></button>
                                </div>
                                <div class="form-group">
                                    <span class="label label-default">GET</span>
                                </div>
                                <div class="form-group">
                                <input type="text" class="form-control getpreinput in1" style="width: 134px;" placeholder="Поле" > 
                                <input type="text" class="form-control getpreinput in2 canzero"  style="width: 134px;" placeholder="Значение" > 
                                <input type="hidden" class="select-item" itemtype='get' >
                                </div>
                                <div class='pull-right' style='width:200px;'><input placeholder="Ссылка" require="" type="hidden" name='out_id[]' class='select-link' data-selected-value=''></div>
                            </div>
                        </td>
                    </tr>
       {{/conditions}}          
</script>
<script id="referTemplate" type="text/template">
     {{#conditions}}
                    <tr>
                        <td>
                            <div class="form-inline" role="form">                            
                                <div class="btn-group trash-button">
                                    <button class='btn btn-default btnrmcountry'><i class="fa fa-trash-o text-muted"></i></button>
                                </div>
                                <div class="form-group">
                                    <span class="label label-default">Реферер</span>
                                </div>
                                <div class="form-group">
                                <input type="text" class="form-control select-item" placeholder="Реферер" itemtype='referer'  > 
                                </div>
                                <div class='pull-right' style='width:200px;'><input placeholder="Ссылка" require="" type="hidden" name='out_id[]' class='select-link' data-selected-value=''></div>
                            </div>
                        </td>
                    </tr>
       {{/conditions}}          
</script>
<script id="countryTemplate" type="text/template">
     {{#conditions}}
                    <tr>
                        <td>
                            <div class="form-inline" role="form">                            
                                <div class="btn-group trash-button">
                                    <button class='btn btn-default btnrmcountry'><i class="fa fa-trash-o text-muted"></i></button>
                                </div>
                                <div class="form-group">
                                    <span class="label label-default">Страна</span>
                                </div>
                                <div class="form-group">
                                <input type="hidden" placeholder="Страна" itemtype='geo_country' class='select-geo_country select-item' data-selected-value=''>
                                <!-- <button class='btn btn-default' style='border:none;'>  <i class="fa fa-caret-down text-muted"></i></button> -->
                                </div>
                                <div class='pull-right' style='width:200px;'><input placeholder="Ссылка" require="" type="hidden" name='out_id[]' class='select-link' data-selected-value=''></div>
                            </div>
                        </td>
                    </tr>
       {{/conditions}}          
</script>
<script id="deviceTemplate" type="text/template">
     {{#conditions}}
                    <tr>
                        <td>
                            <div class="form-inline" role="form">                            
                                <div class="btn-group trash-button">
                                    <button class='btn btn-default btnrmcountry'><i class="fa fa-trash-o text-muted"></i></button>
                                </div>
                                <div class="form-group">
                                    <span class="label label-default">Устройство</span>
                                </div>
                                <div class="form-group">
                                <input type="hidden" placeholder="Устройство" itemtype='device' class='select-device select-item' data-selected-value=''>
                                </div>
                                <div class='pull-right' style='width:200px;'><input placeholder="Ссылка" require="" type="hidden" name='out_id[]' class='select-link' data-selected-value=""></div>
                            </div>
                        </td>
                    </tr>
       {{/conditions}}          
</script>
<script id="osTemplate" type="text/template">
     {{#conditions}}
                    <tr>
                        <td>
                            <div class="form-inline" role="form">                            
                                <div class="btn-group trash-button">
                                    <button class='btn btn-default btnrmcountry'><i class="fa fa-trash-o text-muted"></i></button>
                                </div>
                                <div class="form-group">
                                    <span class="label label-default">ОС</span>
                                </div>
                                <div class="form-group">
                                <input type="hidden" placeholder="ОС" itemtype='os' class='select-os select-item' data-selected-value=''>
                        		<!-- <button class='btn btn-default' style='border:none;'>  <i class="fa fa-caret-down text-muted"></i></button> -->
                                </div>
                                <div class='pull-right' style='width:200px;'><input placeholder="Ссылка" require="" type="hidden" name='out_id[]' class='select-link' data-selected-value=""></div>
                            </div>
                        </td>
                    </tr>
       {{/conditions}}          
</script>

<script id="langTemplate" type="text/template">
     {{#conditions}}
                    <tr>
                        <td>
                            <div class="form-inline" role="form">                            
                                <div class="btn-group trash-button">
                                    <button class='btn btn-default btnrmcountry'><i class="fa fa-trash-o text-muted"></i></button>
                                </div>
                                <div class="form-group">
                                    <span class="label label-default">Язык</span>
                                </div>
                                <div class="form-group">
                                <input type="hidden" placeholder="Язык" itemtype='lang' class='select-lang select-item' data-selected-value=''>
                                <!-- <button class='btn btn-default' style='border:none;'>  <i class="fa fa-caret-down text-muted"></i></button> -->
                                </div>
                                <div class='pull-right' style='width:200px;'><input placeholder="Ссылка" require="" type="hidden" name='out_id[]' class='select-link' data-selected-value=''></div>
                            </div>
                        </td>
                    </tr>
       {{/conditions}}          
</script>
<script id="rulesTemplate" type="text/template">
    {{#rules}}
        <table id="rule{{id}}" class='table table-rules'>
            <thead>
                <tr>
                    <th>
                        <button type='button' id='copy-button-{{id}}' class='btn-rule-copy' role="button" data-clipboard-target='rule-link-{{id}}'><i class="fa fa-copy" title="Скопировать ссылку в буфер"></i></button>
                        <span class='rule-name-title'   >{{name}}</span>
                        <span class='rule-destination-title'>
                            {{destination}}
                            {{#destination_multi}}
                                <span class='badge'>{{destination_multi}}</span>
                            {{/destination_multi}}
                        </span>
                        <input type="hidden" id='rule-link-{{id}}' class="rule-link" value='{{url}}'>
                    </th>
                </tr>
            </thead>

            <tbody id="{{id}}">
                {{#conditions}}
                    <tr>
                        <td>
                            <div class="form-inline" role="form">                            
                                <div class="btn-group trash-button">
                                    <button class='btn btn-default btnrmcountry'><i class="fa fa-trash-o text-muted"></i></button>
                                </div>
                                <div class="form-group">
                                    <span class="label label-default">{{type}}</span>
                                </div>                        
                        {{#textinput}}
                        {{#getinput}}
                                <input type="text" class="form-control getpreinput in1" style="width: 134px;" placeholder="Поле" > 
                                <input type="text" class="form-control getpreinput in2 canzero"  style="width: 134px;" placeholder="Значение" > 
                                <input type="hidden" class="select-item" itemtype='get' value="{{value}}">                        
                        {{/getinput}}
                        {{^getinput}}
                                <div class="form-group">
                                <input type="text" class="form-control select-item toSave" placeholder="{{type}}" itemtype='{{select_type}}' value='{{value}}' > 
                                </div>
                        {{/getinput}}
                        {{/textinput}}
                        {{^textinput}}
                                <div class="form-group">
                                <input type="hidden" placeholder="{{type}}" itemtype='{{select_type}}' class='select-{{select_type}} select-item toSave' data-selected-value='{{value}}'>
                                <!-- <button class='btn btn-default' style='border:none;'>{{value}} <i class="fa fa-caret-down text-muted"></i></button> -->
                                </div>
                        {{/textinput}}                        
                                <div class='pull-right' style='width:200px;'><input type=hidden name='out_id[]' class='select-link toSave' data-selected-value='{{destination_id}}'></div>
                            </div>
                        </td>
                    </tr>
                {{/conditions}}
           
                <tr><td>
                    <form class="form-inline" role="form">
                        <div class="btn-group trash-button" style='visibility:hidden'>
                            <button class='btn btn-default'><i class="fa fa-trash-o text-muted"></i></button>
                        </div>    
                        <div class="form-group">
                            <span class="label label-primary users_label">{{other_users}}</span>
                        </div>
                        <div class="form-group">
                            <button class='btn btn-default' style='border:none; visibility:hidden'><i class="fa fa-caret-down text-muted"></i></button>
                        </div>
                        <div class='pull-right' style='width:200px;'><input type='hidden' name='default_out_id' class='select-link toSave' data-selected-value='{{default_destination_id}}'></div>
                    </form>
                </td></tr>

                <tr><td>
                    <form class="form-inline" role="form">
                        <div class="form-group">
                            <div class="btn-group">
                                <button class='btn btn-default dropdown-toggle btn-rule-settings' data-toggle="dropdown"><i class="fa fa-bars text-muted"></i></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a class="rname" id="{{id}}"  href="#">Переименовать ссылку</a></li>
                                    <li class="divider"></li>
                                    <li><a class="delbut" id="{{id}}" href="#">Удалить ссылку</a></li>
                                </ul>
                            </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
                                  Добавить условие
                                  <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" id="{{id}}">
                                    <li><a class="addcountry" href="#">Страна</a></li>
                                    <li><a class="addlang" href="#">Язык браузера</a></li>
                                    <li><a class="addrefer" href="#">Реферер</a></li>
                                    <li><a class="addcity" href="#">Город</a></li>
                                    <li><a class="addregion" href="#">Регион</a></li>
                                    <li><a class="addprovider" href="#">Провайдер</a></li>
                                    <li><a class="addip" href="#">IP адрес</a></li>
                                    <li><a class="adddevice" href="#">Устройство</a></li>
                                    <li><a class="addos" href="#">ОC</a></li>
                                    <li><a class="addplatform" href="#">Платформа</a></li>
                                    <li><a class="addbrowser" href="#">Браузер</a></li> 
                                    <li><a class="addagent" href="#">User-agent</a></li>
                                    <li class="divider"></li>
                                    <li><a class="addget" href="#">Параметр в GET-запросе</a></li>
                                </ul>                            
                              </div>
                        </div>
                        <button id="{{id}}" class='btn btn-default pull-right btnsave'>Сохранить</button>
                    </form>
                    
                </td></tr>
                <tr>
                	<td>
                		<div style='width:200px; margin: 5px 5px 5px 0;' class="pull-left"><input type=hidden name='source_id[]' class='select-sources toSave' data-selected-value='source' id="rule-link-select2-{{id}}">
                		
                	</div>
                    <button type='button' id='copy-button-text-{{id}}' class='btn-rule-copy for_text' role="button" data-clipboard-target='rule-link-text-{{id}}'><i class="fa fa-copy" title="Скопировать ссылку в буфер"></i></button>
                    <button type='button' class='btn-rule-copy for_text' onclick="$('#rule-link-row-{{id}}').toggle()" style="margin-right: 5px;"><i class="fa fa-link"></i></button>
                    <button type="button" id="rule-link-direct-{{id}}" class="btn-rule-copy direct" role="button" style="margin-right: 5px;">LP</button>
                    </td>
                </tr>
               	<tr id="rule-link-row-{{id}}" style="display: none">
                    <td>
                    	
                    	
                    	<button type='button' class='btn-rule-copy for_text' onclick="window.open($('#rule-link-text-{{id}}').val());"><i class="fa fa-external-link"></i></button>
                    		
                    	<input type="text" class="rule-link-text" id="rule-link-text-{{id}}" value="{{url}}" />
                    	
                    </td>
                </tr>
            </tbody>
        </table>
    {{/rules}}
</script>

<div class="row">
    <div class="col-sm-9">
        <div class="alert alert-danger" style="display:none;" id="incorrect_name_alert">
            Неверное название ссылки, используйте только латинские буквы, цифры и знаки _ и -.
        </div>
        <form class="form-inline" method="post" onsubmit="return validate_add_rule();" id="form_add_rule" role="form" style="margin-bottom:30px">
        <div class="form-group">
            <label class="sr-only">Название ссылки</label>
            <input type="text" class="form-control" placeholder="Название ссылки" id="rule_name_id" name="rule_name">
        </div>
        &nbsp;→&nbsp;
        <div class="form-group">
            <label class="sr-only">Ссылка</label>
            <input type="hidden" placeholder="Ссылка" name='out_id' class='select-link toSave' data-selected-value='<?php echo $js_last_offer_id;?>'>
        </div>
        <button type="submit" class="btn btn-default" onclick="">Добавить</button>
        <input type="hidden" name="ajax_act" value="add_rule">
        <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
        </form>         
    </div>
</div>

<div class="row">
    <div class="col-md-9">
        <div class="alert alert-info" style="display: none;" id="restore_alert">
            <strong>Внимание!</strong> Ссылка <span id="rule_name"></span> была удалена, Вы можете её <strong><u><a href="javascript:void(0);" onClick="restore_rule();">восстановить</a></u></strong>
        </div>
    </div>
</div>

<div class='row'>
    <div class="col-md-9" id='rules_container'></div>
</div>

<div class="row">&nbsp;</div>