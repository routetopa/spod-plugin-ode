<?php

$path = OW::getPluginManager()->getPlugin('ode')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'ode');

$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'ode_datalet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownerId` int(11) NOT NULL,
  `post` text,
  `component` text,
  `data` mediumtext,
  `fields` text,
  `params` text,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum("approval","approved","blocked") NOT NULL DEFAULT "approved",
  `privacy` varchar(50) NOT NULL DEFAULT "everybody",
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `privacy` (`privacy`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'ode_datalet_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postId` int(11) NOT NULL,
  `dataletId` int(11) NOT NULL,
  `plugin` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `postId` (`postId`),
  KEY `dataletId` (`dataletId`),
  KEY `plugin` (`plugin`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'ode_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` TEXT,
  `value` MEDIUMTEXT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;';

OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('ode', 'ode-settings');
