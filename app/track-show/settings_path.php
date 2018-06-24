<?php

	if (file_exists(dirname(__FILE__).'/local_settings.php'))
	{
		include dirname(__FILE__).'/local_settings.php';
	}
	else
	{
		include dirname(__FILE__).'/default_settings.php';
	}