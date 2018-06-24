<?php
if (!$include_flag){exit();}

if($var['report_params']['mode'] == 'lp_offers' and $var['report_params']['part'] == 'all') {
	echo tpx('report_click_all_sub', $var);
} else {
	if($var['report_params']['part'] == 'all') {
		if($var['report_params']['mode'] == 'lp') {
			echo tpx('report_click_all_lp', $var);
		} elseif($var['report_params']['mode'] == 'popular') {
			echo tpx('report_click_all_pop', $var);
		} else {
			echo tpx('report_click_all', $var);
		}
	} else {
		if($var['report_params']['mode'] == 'lp_offers') {
			echo tpx('report_daily_lp', $var);
		} else {
			echo tpx('report_daily', $var);
		}
	}
}

?>