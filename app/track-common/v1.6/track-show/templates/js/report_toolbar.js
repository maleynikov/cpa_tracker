var timer_cols = false;

// Вставляем параметры во все ссылки контейнера
function modify_links(name, val) {
	var node = document.getElementsByClassName("container")[1];
	var els = node.getElementsByTagName("a");

	for(var i=0, j=els.length; i<j; i++) {
		// Не трогаем контейнер с соответствующим селектором
		//console.log($(els[i]).parent());
		if(name == 'col' && $(els[i]).parent().attr('id') == 'rt_sale_section') continue;
		
		href = els[i].href;
		offset = href.indexOf(name);
		if(offset == -1) {
	    	divider = href.indexOf('?') == -1 ? '?' : '&';
	    	end = href.indexOf('#');
	    	if (end == -1) {
	    		end = href.length;
	    	}
	    	els[i].href = els[i].href.substring(0, end) + divider + name + '=' + val + els[i].href.substring(end, href.length);
		} else {
			end = href.indexOf('&', offset);
			if (end == -1) {
				end = href.length;
			}
			now = unescape(href.substring(offset, end));
			els[i].href = els[i].href.substring(0, offset + name.length + 1) + val + els[i].href.substring(offset + now.length, href.length);
		}	
	}
}

function update_cols(selected_option, mod_links) {
	switch (selected_option) {
		case 'act':
			$('.col_s').hide();
			$('.col_l').hide();
			$('.col_a').show();
			if(mod_links) {
				modify_links('col', 'act');
			}
		break;
		case 'sale':
			$('.col_l').hide();
			$('.col_a').hide();
			$('.col_s').show();
			if(mod_links) {
				modify_links('col', 'sale');
			}
		break;
		case 'lead':
			$('.col_a').hide();
			$('.col_s').hide();
			$('.col_l').show();
			if(mod_links) {
				modify_links('col', 'lead');
			}
		break;
		case 'currency_rub': 
			$('.sdata.usd').hide();
			$('.sdata.rub').show();
			modify_links('currency', 'rub');
		break;
		case 'currency_usd': 
			$('.sdata.usd').show();
			$('.sdata.rub').hide();
			modify_links('currency', 'usd');
		break;
	}
}

function update_stats2(selected_option, currency_show) {
	$('.timetab.sdata').hide();
	$('.timetab.sdata').removeClass('current');
	$('.timetab.sdata.' + selected_option).show();
	$('.timetab.sdata.' + selected_option).addClass('current');
	
	$('#type_selected').val(selected_option);
	
	$('#rt_currency_section').toggle(currency_show);
}

function show_currency(value) {
	switch (value) {
		case 'rub': 
			$('.sdata.usd').hide();
			$('.sdata.rub').show();
			modify_links('currency', 'rub');
		break;
		case 'usd': 
			$('.sdata.usd').show();
			$('.sdata.rub').hide();
			modify_links('currency', 'usd');
		break;
	}
}

function show_conv_mode(value, mod_links) {	
	mod_links = true;
	
	// прячем все панели
	$('.rt_types').hide(); 
	
	// показываем нужную
	$('.rt_types.rt_type_' + value).show();
	
	// жмём первую кнопку на показанной панели
	$('.rt_types:visible').children()[0].click();
	
	switch (value) {
		case 'act':
			$('.col_s').hide();
			$('.col_l').hide();
			$('.col_a').show();
			if(mod_links) {
				modify_links('col', 'act');
			}
		break;
		case 'sale':
			$('.col_l').hide();
			$('.col_a').hide();
			$('.col_s').show();
			if(mod_links) {
				modify_links('col', 'sale');
			}
		break;
		case 'lead':
			$('.col_a').hide();
			$('.col_s').hide();
			$('.col_l').show();
			if(mod_links) {
				modify_links('col', 'lead');
			}
		break;
	}
}

function update_stats(selected_option)
{
	switch (selected_option)
	{
		case 'clicks':
			$('#type_selected').val('clicks'); $('#rt_currency_section').addClass('invisible'); 
		break;

		case 'conversion':
			$('#type_selected').val('conversion'); $('#rt_currency_section').addClass('invisible');
		break;

		case 'lead_price':
			$('#type_selected').val('lead_price'); $('#rt_currency_section').removeClass('invisible');
		break;

		case 'roi':
			$('#type_selected').val('roi'); $('#rt_currency_section').addClass('invisible');
		break;

		case 'epc':
			$('#type_selected').val('epc'); $('#rt_currency_section').removeClass('invisible');
		break;

		case 'profit':
			$('#type_selected').val('sales'); $('#rt_currency_section').removeClass('invisible');
		break;

		case 'sale': 
			$('#sales_selected').val('1');
		break;

		case 'lead': 
			$('#sales_selected').val('0');
		break;

		case 'currency_rub': 
			$('#usd_selected').val('0');
		break;

		case 'currency_usd': 
			$('#usd_selected').val('1');
		break;

		default: break;
	}

	$('.sdata').hide();

	// Lead price was selected and we switched from leads to sales
	if ($('#sales_selected').val()==1)
	{
		if ($('#rt_leadprice_button').hasClass('active'))
		{
			$('#rt_leadprice_button').removeClass('active');
			$("#type_selected").val("clicks"); 
			$('#rt_clicks_button').addClass('active');			
			
			$("#rt_currency_section").hide();			
		}

		$('#rt_roi_button').show();
		$('#rt_epc_button').show();		
		$('#rt_profit_button').show();

		$('#rt_leadprice_button').hide();					
		if ($('#usd_selected').val()==1)
		{
			switch ($('#type_selected').val())
			{
				case 'clicks':
					$('.clicks').show();
					$('#rt_sale_section').show();
				break;
				case 'conversion':
					$('.conversion').show();
					$('#rt_sale_section').show();
				break;			
				case 'roi':
					$('.roi').show();
					$('#rt_sale_section').hide();
				break;
				default: 
					$('.'+$('#type_selected').val()).show();
					$('#rt_sale_section').hide();
					$('.rub').hide();
				break;
			}
		}
		else
		{
			switch ($('#type_selected').val())
			{
				case 'clicks':
					$('.clicks').show();
					$('#rt_sale_section').show();
				break;
				case 'conversion':
					$('.conversion').show();
					$('#rt_sale_section').show();
				break;		
				case 'roi':
					$('.roi').show();
					$('#rt_sale_section').hide();
				break;				
				default: 
					$('.'+$('#type_selected').val()).show();
					$('.usd').hide();
					$('#rt_sale_section').hide();
				break;
			}
		}	
	}
	else
	{
		$('#rt_roi_button').hide();
		$('#rt_epc_button').hide();		
		$('#rt_profit_button').hide();		
		$('#rt_leadprice_button').show();				
				
			switch ($('#type_selected').val())
			{
				case 'clicks':
					$('.leads_clicks').show();
				break;
				case 'conversion':
					$('.leads_conversion').show();
				break;
				case 'lead_price':
					if ($('#usd_selected').val()==1)
					{
						$('.leads_price.usd').show();
					}
					else
					{
						$('.leads_price.rub').show();
					}									
				break;				
				default: 
					$('.'+$('#type_selected').val()).show();
					$('.usd').hide();
				break;
			}
	}
}

function toggle_report_toolbar()
{
	if ($('#rt_type_section').hasClass('invisible'))
	{
		$('#rt_type_section').removeClass('invisible');
		$('#rt_sale_section').removeClass('invisible');
	}
	else
	{
		$('#rt_type_section').addClass('invisible');
		$('#rt_sale_section').addClass('invisible');
		$('#rt_currency_section').addClass('invisible');
	}
}