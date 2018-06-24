<?php
if (!$include_flag) {exit(); }

// Календарик выбора дней
echo '<div id="per_day_range" class="pull-left" style="">
        <span id="cur_day_range">'.date('d.m.Y', strtotime($var['report_params']['from'])).' - '. date('d.m.Y', strtotime($var['report_params']['to'])).'</span> <b class="caret"></b>
        <input type="hidden" name="from" id="sStart" value="">
        <input type="hidden" name="to" id="sEnd" value="">
    </div>';