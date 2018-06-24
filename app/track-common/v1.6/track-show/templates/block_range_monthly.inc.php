<?php
if (!$include_flag) {exit(); }
// Календарик выбора месяцев

$fromF = date('m.Y', strtotime($var['report_params']['from']));
$toF   = date('m.Y', strtotime($var['report_params']['to']));

echo '<div style="width: 240px; float: left; margin-top: 18px; margin-left: 5px;">
    <div class="input-group">                          
          <div class="input-group-addon "><i class="fa fa-calendar"></i></div>
          <input style="display: inline; float:left; width: 80px;   border-right: 0;" id="dpMonthsF"   type="text"  data-date="102/2012" data-date-format="mm.yyyy" data-date-viewmode="years" data-date-minviewmode="months"  class="form-control"  name="from" value="' . $fromF . '">
          <input style="display: inline; float:left; width: 80px;  border-right: 0;  border-top-right-radius: 0; border-bottom-right-radius: 0;" id="dpMonthsT"   type="text"  data-date="102/2012" data-date-format="mm.yyyy" data-date-viewmode="years" data-date-minviewmode="months" type="text" class="form-control"   name="to" value="' . $toF . '">
          <button type="button" style="width:40px;" class="btn btn-default form-control" onclick="$(\'[name = datachangeform]\').submit();"><i class="glyphicon glyphicon-search"></i></button>  
    </div>
</div>';