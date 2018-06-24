<?php if (!$include_flag){exit();}
	$tracklink = tracklink();
?>
<div class="row">
    <div class="col-md-12">
        <h3>Целевые страницы</h3>
    </div>
</div>
	
<div class="row" id="master-form">
    <div class="col-md-12">
    	<p>Для учета посетителей на ваших целевых страницах установите, пожалуйста, код счетчика:
    	<pre>&lt;!--cpatracker.ru start--&gt;&lt;script type="text/javascript"&gt; ;(function(){if(window.cpa_inited)return;window.cpa_inited=true;var a=document.createElement("script");a.type="text/javascript";var b=""; if(typeof this.href!="undefined"){b=this.href.toString().toLowerCase()}else{b=document.location.toString().toLowerCase()}; a.async=true;a.src="<?php echo str_replace('http:', '', $tracklink); ?>/cookie.js?rnd="+Math.random(); var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(a,s)})();&lt;/script&gt;&lt;!--cpatracker.ru end--&gt;</pre>
    	Устанавливать код счётчика необходимо перед тегом &lt;/body&gt; в HTML-код страницы.</p><br />
    	<p>Если продажи или целевые действия (регистрации, скачивания, заполнения форм) происходят на вашем сайте &mdash; вызовите следующую функцию в момент выполнения действия пользователем.
    	<pre>&lt;script&gt;cpatracker_add_lead(profit);&lt;/script&gt;</pre> Вместо profit напишите сумму продажи в валюте RUB (российский рубль) или 0, для учета целевого действия.</p>
    </div>
</div>