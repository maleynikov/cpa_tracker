<?php
if (!$include_flag) {exit(); }
// Заголовок отчёта с названием, временными рамками и кнопками разбиения по времени

echo '<form method="post" name="datachangeform" id="range_form">
    <div class="pull-left"><h3>' . $var['report_name'] . '</h3></div>
    '.tpx('block_range_' . $var['timestep'], $var).'
    <div class="pull-right" style="margin-top:18px;">' . tpx('report_timestep', $var). '</div>
  </form>
<div class="row"></div>';

echo tpx('report_breadcrumbs');