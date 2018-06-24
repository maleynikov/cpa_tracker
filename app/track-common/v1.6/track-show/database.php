<?php
    $arr_sql[]="SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_clicks` (
      `id` int(11) NOT NULL auto_increment,
      `date_add` datetime NOT NULL,
      `date_add_day` date NOT NULL,
      `date_add_hour` tinyint(4) NOT NULL,
      `user_ip` varchar(255) NOT NULL,
      `user_agent` text character set utf8 NOT NULL,
      `user_os` varchar(255) character set utf8 NOT NULL,
      `user_os_version` varchar(255) character set utf8 NOT NULL,
      `user_platform` varchar(255) character set utf8 NOT NULL,
      `user_platform_info` varchar(255) character set utf8 NOT NULL,
      `user_platform_info_extra` varchar(255) character set utf8 NOT NULL,
      `user_browser` varchar(255) character set utf8 NOT NULL,
      `user_browser_version` varchar(255) character set utf8 NOT NULL,
      `is_mobile_device` tinyint(1) NOT NULL,
      `is_phone` tinyint(1) NOT NULL,
      `is_tablet` tinyint(1) NOT NULL,
      `country` varchar(255) NOT NULL,
      `state` varchar(255) character set utf8 NOT NULL,
      `city` varchar(255) character set utf8 NOT NULL,
      `region` varchar(255) character set utf8 NOT NULL,
      `isp` varchar(255) character set utf8 NOT NULL,
      `rule_id` int(11) NOT NULL,
      `out_id` int(11) NOT NULL,
      `subid` varchar(255) character set utf8 NOT NULL,
      `subaccount` varchar(255) character set utf8 NOT NULL,
      `source_name` varchar(255) character set utf8 NOT NULL,
      `campaign_name` varchar(255) character set utf8 NOT NULL,
      `ads_name` varchar(255) character set utf8 NOT NULL,
      `referer` text character set utf8 NOT NULL,
      `search_string` text character set utf8 NOT NULL,
      `click_price` decimal(10,4) NOT NULL,
      `conversion_price_main` decimal(10,4) NOT NULL,
      `is_lead` tinyint(1) NOT NULL,
      `is_sale` tinyint(1) NOT NULL,
      `campaign_param1` varchar(255) character set utf8 NOT NULL,
      `campaign_param2` varchar(255) character set utf8 NOT NULL,
      `campaign_param3` varchar(255) character set utf8 NOT NULL,
      `campaign_param4` varchar(255) character set utf8 NOT NULL,
      `campaign_param5` varchar(255) character set utf8 NOT NULL,
      `click_param_name1` varchar(255) character set utf8 NOT NULL,
      `click_param_value1` text character set utf8 NOT NULL,
      `click_param_name2` varchar(255) character set utf8 NOT NULL,
      `click_param_value2` text character set utf8 NOT NULL,
      `click_param_name3` varchar(255) character set utf8 NOT NULL,
      `click_param_value3` text character set utf8 NOT NULL,
      `click_param_name4` varchar(255) character set utf8 NOT NULL,
      `click_param_value4` text character set utf8 NOT NULL,
      `click_param_name5` varchar(255) character set utf8 NOT NULL,
      `click_param_value5` text character set utf8 NOT NULL,
      `click_param_name6` varchar(255) character set utf8 NOT NULL,
      `click_param_value6` text character set utf8 NOT NULL,
      `click_param_name7` varchar(255) character set utf8 NOT NULL,
      `click_param_value7` text character set utf8 NOT NULL,
      `click_param_name8` varchar(255) character set utf8 NOT NULL,
      `click_param_value8` text character set utf8 NOT NULL,
      `click_param_name9` varchar(255) character set utf8 NOT NULL,
      `click_param_value9` text character set utf8 NOT NULL,
      `click_param_name10` varchar(255) character set utf8 NOT NULL,
      `click_param_value10` text character set utf8 NOT NULL,
      `click_param_name11` varchar(255) character set utf8 NOT NULL,
      `click_param_value11` text character set utf8 NOT NULL,
      `click_param_name12` varchar(255) character set utf8 NOT NULL,
      `click_param_value12` text character set utf8 NOT NULL,
      `click_param_name13` varchar(255) character set utf8 NOT NULL,
      `click_param_value13` text character set utf8 NOT NULL,
      `click_param_name14` varchar(255) character set utf8 NOT NULL,
      `click_param_value14` text character set utf8 NOT NULL,
      `click_param_name15` varchar(255) character set utf8 NOT NULL,
      `click_param_value15` text character set utf8 NOT NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `subid` (`subid`),
      KEY `date_add_day` (`date_add_day`),
      KEY `date_add_hour` (`date_add_hour`),
      KEY `user_os` (`user_os`),
      KEY `user_platform` (`user_platform`),
      KEY `user_browser` (`user_browser`),
      KEY `country` (`country`),
      KEY `state` (`state`),
      KEY `city` (`city`),
      KEY `region` (`region`),
      KEY `rule_id` (`rule_id`),
      KEY `out_id` (`out_id`),
      KEY `subaccount` (`subaccount`),
      KEY `source_name` (`source_name`),
      KEY `campaign_name` (`campaign_name`),
      KEY `ads_name` (`ads_name`),
      KEY `campaign_param1` (`campaign_param1`),
      KEY `campaign_param2` (`campaign_param2`),
      KEY `campaign_param3` (`campaign_param3`),
      KEY `campaign_param4` (`campaign_param4`),
      KEY `campaign_param5` (`campaign_param5`),
      KEY `click_param_name1` (`click_param_name1`),
      KEY `click_param_name2` (`click_param_name2`),
      KEY `click_param_name3` (`click_param_name3`),
      KEY `click_param_name4` (`click_param_name4`),
      KEY `click_param_name5` (`click_param_name5`),
      KEY `click_param_name6` (`click_param_name6`),
      KEY `click_param_name7` (`click_param_name7`),
      KEY `click_param_name8` (`click_param_name8`),
      KEY `click_param_name9` (`click_param_name9`),
      KEY `click_param_name10` (`click_param_name10`),
      KEY `click_param_name11` (`click_param_name11`),
      KEY `click_param_name12` (`click_param_name12`),
      KEY `click_param_name13` (`click_param_name13`),
      KEY `click_param_name14` (`click_param_name14`),
      KEY `click_param_name15` (`click_param_name15`),
      KEY `is_lead` (`is_lead`),
      KEY `is_sale` (`is_sale`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";


    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_conversions` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `type` varchar(255) CHARACTER SET utf8 NOT NULL,
		  `network` varchar(255) CHARACTER SET utf8 NOT NULL,
		  `subid` varchar(255) CHARACTER SET utf8 NOT NULL,
		  `profit` decimal(10,4) NOT NULL,
		  `date_add` datetime NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `txt_status` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
		  `t1` text CHARACTER SET utf8,
		  `t2` text CHARACTER SET utf8,
		  `t3` text CHARACTER SET utf8,
		  `t4` text CHARACTER SET utf8,
		  `t5` text CHARACTER SET utf8,
		  `t6` text CHARACTER SET utf8,
		  `t7` text CHARACTER SET utf8,
		  `t8` text CHARACTER SET utf8,
		  `t9` text CHARACTER SET utf8,
		  `t10` text CHARACTER SET utf8,
		  `t11` text CHARACTER SET utf8,
		  `t12` text CHARACTER SET utf8,
		  `t13` text CHARACTER SET utf8,
		  `t14` text CHARACTER SET utf8,
		  `t15` text CHARACTER SET utf8,
		  `t16` text CHARACTER SET utf8,
		  `t17` text CHARACTER SET utf8,
		  `t18` text CHARACTER SET utf8,
		  `t19` text CHARACTER SET utf8,
		  `t20` text CHARACTER SET utf8,
		  `t21` text CHARACTER SET utf8,
		  `t22` text CHARACTER SET utf8,
		  `t23` text CHARACTER SET utf8,
		  `t24` text CHARACTER SET utf8,
		  `t25` text CHARACTER SET utf8,
		  `t26` text CHARACTER SET utf8,
		  `t27` text CHARACTER SET utf8,
		  `t28` text CHARACTER SET utf8,
		  `t29` text CHARACTER SET utf8,
		  `t30` text CHARACTER SET utf8,
		  `f1` float(10,4) DEFAULT NULL,
		  `f2` float(10,4) DEFAULT NULL,
		  `f3` float(10,4) DEFAULT NULL,
		  `f4` float(10,4) DEFAULT NULL,
		  `f5` float(10,4) DEFAULT NULL,
		  `i1` int(11) DEFAULT NULL,
		  `i2` int(11) DEFAULT NULL,
		  `i3` int(11) DEFAULT NULL,
		  `i4` int(11) DEFAULT NULL,
		  `i5` int(11) DEFAULT NULL,
		  `i6` int(11) DEFAULT NULL,
		  `i7` int(11) DEFAULT NULL,
		  `i8` int(11) DEFAULT NULL,
		  `i9` int(11) DEFAULT NULL,
		  `i10` int(11) DEFAULT NULL,
		  `i11` int(11) DEFAULT NULL,
		  `i12` int(11) DEFAULT NULL,
		  `i13` int(11) DEFAULT NULL,
		  `i14` int(11) DEFAULT NULL,
		  `i15` int(11) DEFAULT NULL,
		  `i16` int(11) DEFAULT NULL,
		  `i17` int(11) DEFAULT NULL,
		  `i18` int(11) DEFAULT NULL,
		  `i19` int(11) DEFAULT NULL,
		  `i20` int(11) DEFAULT NULL,
		  `d1` int(11) DEFAULT NULL,
		  `d2` datetime DEFAULT NULL,
		  `d3` datetime DEFAULT NULL,
		  `d4` datetime DEFAULT NULL,
		  `d5` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `subid_2` (`subid`,`profit`),
		  KEY `type` (`type`),
		  KEY `network` (`network`),
		  KEY `subid` (`subid`),
		  KEY `status` (`status`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_cpa_networks` (
      `id` int(11) NOT NULL auto_increment,
      `network_name` varchar(255) character set utf8 NOT NULL,
      `network_category_name` varchar(255) character set utf8 NOT NULL,
      `network_platform` varchar(255) character set utf8 NOT NULL,
      `network_domain` text character set utf8 NOT NULL,
      `registration_url` text character set utf8 NOT NULL,
      `network_api_url` text character set utf8 NOT NULL,
      `offer_page_url` text character set utf8 NOT NULL,
      `api_key` varchar(255) character set utf8 NOT NULL,
      `status` tinyint(4) NOT NULL default '0',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_links_categories` (
      `id` int(11) NOT NULL auto_increment,
      `category_id` int(11) NOT NULL,
      `offer_id` int(11) NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `category_id` (`category_id`,`offer_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_links_categories_list` (
      `id` int(11) NOT NULL auto_increment,
      `category_caption` varchar(255) character set utf8 NOT NULL,
      `category_name` varchar(255) character set utf8 NOT NULL,
      `category_type` varchar(255) character set utf8 NOT NULL,
      `status` tinyint(4) NOT NULL default '0',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_offers` (
      `id` int(11) NOT NULL auto_increment,
      `network_id` int(11) NOT NULL,
      `offer_id` varchar(255) character set utf8 NOT NULL,
      `offer_name` text character set utf8 NOT NULL,
      `offer_description` text character set utf8 NOT NULL,
      `offer_payout_type` varchar(255) character set utf8 NOT NULL,
      `offer_payout` varchar(255) character set utf8 NOT NULL,
      `offer_payout_currency` varchar(255) character set utf8 NOT NULL,
      `offer_expiration_date` date NOT NULL,
      `offer_preview_url` text character set utf8 NOT NULL,
      `offer_tracking_url` text character set utf8 NOT NULL,
      `offer_comment` text character set utf8 NOT NULL,
      `is_active` tinyint(4) NOT NULL default '1',
      `date_add` datetime NOT NULL,
      `status` tinyint(4) NOT NULL default '0',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_rules` (
      `id` int(11) NOT NULL auto_increment,
      `link_name` varchar(255) character set utf8 NOT NULL,
      `date_add` datetime NOT NULL,
      `status` int(11) NOT NULL default '0',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_rules_items` (
      `id` int(11) NOT NULL auto_increment,
      `rule_id` int(11) NOT NULL,
      `parent_id` int(11) NOT NULL,
      `type` varchar(255) character set utf8 NOT NULL,
      `value` text character set utf8 NOT NULL,
      `status` int(11) NOT NULL default '0',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_users` (
      `id` int(11) NOT NULL auto_increment,
      `email` varchar(255) character set utf8 NOT NULL,
      `password` varchar(255) character set utf8 NOT NULL,
      `salt` varchar(255) NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

    $arr_sql[]="ALTER TABLE  `tbl_clicks` ADD  `is_parent` BOOL NOT NULL AFTER  `is_sale` ;";
    $arr_sql[]="ALTER TABLE  `tbl_clicks` ADD  `is_connected` BOOL NOT NULL AFTER  `is_parent` ;";
    $arr_sql[]="ALTER TABLE  `tbl_clicks` ADD  `parent_id` INT NOT NULL AFTER  `is_connected` ;";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_timezones` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `timezone_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `timezone_offset_h` INT NOT NULL ,
    `is_active` INT NOT NULL ,
    `status` INT NOT NULL
    );";
    
    $arr_sql[]="UPDATE `tbl_offers` SET `offer_tracking_url` = REPLACE(`offer_tracking_url`, '%SUBID%', '[SUBID]')";
    
    //$arr_sql[]="ALTER TABLE `tbl_conversions` ADD `txt_status` VARCHAR(255), ADD `t1` TEXT,  ADD `t2` TEXT,  ADD `t3` TEXT ,  ADD `t4` TEXT ,  ADD `t5` TEXT ,  ADD `t6` TEXT ,  ADD `t7` TEXT ,  ADD `t8` TEXT ,  ADD `t9` TEXT ,  ADD `t10` TEXT ,  ADD `t11` TEXT ,  ADD `t12` TEXT ,  ADD `t13` TEXT ,  ADD `t14` TEXT ,  ADD `t15` TEXT ,  ADD `t16` TEXT ,  ADD `t17` TEXT ,  ADD `t18` TEXT ,  ADD `t19` TEXT ,  ADD `t20` TEXT ,  ADD `t21` TEXT ,  ADD `t22` TEXT ,  ADD `t23` TEXT ,  ADD `t24` TEXT ,  ADD `t25` TEXT ,   ADD `t26` TEXT ,   ADD `t27` TEXT ,   ADD `t28` TEXT ,   ADD `t29` TEXT ,   ADD `t30` TEXT ,  ADD `f1` FLOAT(10,4) ,  ADD `f2` FLOAT(10,4) ,  ADD `f3` FLOAT(10,4) ,  ADD `f4` FLOAT(10,4) ,  ADD `f5` FLOAT(10,4) ,  ADD `i1` INT(11) ,  ADD `i2` INT(11) ,  ADD `i3` INT(11) ,  ADD `i4` INT(11) ,  ADD `i5` INT(11) ,  ADD `i6` INT(11) ,  ADD `i7` INT(11) ,  ADD `i8` INT(11) ,  ADD `i9` INT(11) ,  ADD `i10` INT(11) ,  ADD `i11` INT(11) ,  ADD `i12` INT(11) ,  ADD `i13` INT(11) ,  ADD `i14` INT(11) ,  ADD `i15` INT(11) ,  ADD `i16` INT(11) ,  ADD `i17` INT(11) ,  ADD `i18` INT(11) ,  ADD `i19` INT(11) ,  ADD `i20` INT(11) ,  ADD `d1` INT ,  ADD `d2` DATETIME ,  ADD `d3` DATETIME ,  ADD `d4` DATETIME ,  ADD `d5` DATETIME ";

    $arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_postback_params` ( `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , `conv_id` INT( 11 ) NOT NULL , `name` VARCHAR( 255 ) NOT NULL , `value` TEXT NOT NULL) ENGINE = MYISAM ;";
    
    $arr_sql[]="ALTER TABLE `tbl_clicks` ADD `is_unique` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `parent_id` ;";
	
	//$arr_sql[]="ALTER TABLE `tbl_conversions` ADD UNIQUE (`subid` , `profit`);";
	
	$arr_sql[]="CREATE TABLE IF NOT EXISTS `tbl_adnets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `url` varchar(500) CHARACTER SET utf8 NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
?>