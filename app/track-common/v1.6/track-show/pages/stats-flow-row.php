<?php if (!$include_flag){exit();} ?>
<?php
	//dmp($row);
	$date_url = (isset($_REQUEST['date']) and preg_match('/^\d{4}-\d{2}-\d{2}$/', $_REQUEST['date'])) ? '&date=' . $_REQUEST['date'] : '';
	echo "<tr style='cursor:pointer;' onclick='$(this).next().toggle();'>";
			if ($row['country']==''){
				$country_title='';
				$country_icon='question.png';
			} else {
				$country_title="{$row['country']}"; 
				$country_icon=strtolower($row['country']).'.png';
			}
			
			$source_name = empty($source_config[$row['source_name']]['name']) ? $row['source_name'] : $source_config[$row['source_name']]['name'];
			if($row['source_name'] == 'source') $source_name = 'Не определен';
			
			$rule_decs = get_rule_description($row['rule_id']);
			
			echo "
			<td nowrap><img title='"._e($country_title)."' src='"._HTML_TEMPLATE_PATH."/img/countries/"._e($country_icon)."'> <i title='"._e($row['user_os'])." "._e($row['user_os_version'])."' class='b-favicon-os "._e(get_class_by_os($row['user_os']))."'></i> 
			<i title='"._e($row['user_platform'].' '.$row['user_platform_info'].' '.$row['user_platform_info_extra'])."' class='b-favicon-os "._e(get_class_by_platform($row['user_platform']))."'></i></td>
			<td nowrap title='"._e($row['dt'])."'>"._e($row['td'])."</td>
			<td>";
			
			if(!empty($rule_decs)) {
				echo "<a href='?filter_by=rule_id&value={$row['rule_id']}{$date_url}'>".$rule_decs."</a>&nbsp;&nbsp;&rarr;&nbsp;&nbsp;";
			}
			
			
				
			if($row['out_id'] > 0) {
				echo "<a href='?filter_by=out_id&value={$row['out_id']}{$date_url}'>"._e(current(get_out_description($row['out_id'])))."</a>";
			} else {
				echo "Не определён";
			}
			
			echo "</td>
			<td><a href='?filter_by=source_name&value="._e($row['source_name']).$date_url."'>"._e($source_name)."</td>
			<td>"._e($row['campaign_name'].' '.$row['ads_name'])."</td>";
			
			
			if($row['source_name'] == 'yadirect' and !empty($row['click_param_value8'])) {
				$cur_referrer = $row['click_param_value8'];
				if (mb_strlen($cur_referrer, 'UTF-8') > 40) {
					$wrapped_referrer = mb_substr($cur_referrer, 0, 38, 'UTF-8').'…';
				} else {
					$wrapped_referrer = $cur_referrer;
				}
				$wrapped_referrer = '<span style="color: royalblue">'._e($wrapped_referrer). '</span>';
				
			} else {
				$cur_referrer=str_replace (array('http://www.', 'www.'),'',$row['referer']);
				if (strpos($cur_referrer, 'http://')===0){$cur_referrer=substr($cur_referrer, strlen('http://'));}
				if (mb_strlen($cur_referrer, 'UTF-8') > 35) {
					$wrapped_referrer = mb_substr($cur_referrer, 0, 29, 'UTF-8').'…';
				} else {
					$wrapped_referrer = $cur_referrer;
				}
				$wrapped_referrer = _e($wrapped_referrer);
			}
			
			
			

			// Merge cells if we don't have additional params
			if ($row['campaign_param1'].$row['campaign_param2'].$row['campaign_param3'].$row['campaign_param4'].$row['campaign_param5']=='')
			{
				echo "<td colspan=6  title='"._e($cur_referrer)."'>".$wrapped_referrer."</td>";			
			}
			else
			{
				echo "<td title='"._e($cur_referrer)."'>".$wrapped_referrer."</td>";			
				echo "<td>"._e($row['campaign_param1'])."</td>
				<td>"._e($row['campaign_param2'])."</td>
				<td>"._e($row['campaign_param3'])."</td>
				<td>"._e($row['campaign_param4'])."</td>
				<td>"._e($row['campaign_param5'])."</td>";
			}

			echo "<td>";
				if ($row['conversion_price_main']>0)
				{
					echo "<span class='label label-success' style='font-weight:normal'>"._e($row['conversion_price_main'])."</span>";
				}
			echo "</td>";
		echo "</tr>";	
		echo "<tr style='display:none;'><td colspan=12 style='background:#f9f9f9; padding:0;'>
		<div style='padding:10px 15px 15px 15px; width:100%; line-height:26px;'>
		";

		$arr_locations=array();
		if ($row['country']!=''){$arr_locations[]=$row['country'];}
		if ($row['state']!=''){$arr_locations[]=$row['state'];}
		if ($row['city']!=''){$arr_locations[]=$row['city'];}				
		$str_location=implode (', ', $arr_locations);
		if ($str_location!='')
		{
			echo "<i class='icon-ip'></i> "._e($str_location);
		}
		echo "<span class='badge' style='float:right; font-weight:normal; margin-right:25px;'>"._e($row['user_ip'])."</span><br />";
		if ($row['referer']!='')
		{
			echo _e($row['referer'])."<br />";
		}
		echo _e("{$row['user_os']} {$row['user_os_version']}")."<br />
			"._e("{$row['user_platform']} {$row['user_platform_info']} {$row['user_platform_info_extra']}")."<br />
			"._e("{$row['user_browser']} {$row['user_browser_version']}");

		echo '<p>'._e($row['user_agent']).'</p>';
		echo '<p>'._e($row['subid']).'</p>';
		
		$campaign_params = params_list($row, 'campaign_param');
		if(!empty($campaign_params)) {
			echo '<p>Параметры перехода: '._e(join('; ', $campaign_params)).'</p>';
		}
		
		$click_params = params_list($row, 'click_param_value', $row['source_name']);
		if(!empty($click_params)) {
			echo '<p>Параметры ссылки: '._e(join('; ', $click_params)).'</p>';
		}
		
		//echo '<p>'.print_r($row, true).'</p>';
		echo "</div>"; 		
		echo "<div style='width:100%; background:white; height:10px; margin:0; padding:0;'></div>";

		echo "</td></tr>";
?>