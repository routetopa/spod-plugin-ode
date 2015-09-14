<?php

$sql = 'DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'ode_datalet`;
CREATE TABLE `' . OW_DB_PREFIX . 'ode_datalet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownerId` int(11) NOT NULL,
  `post` text,
  `dataset` text,
  `component` text,
  `data` text,
  `query` text,
  `forder` text,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum("approval","approved","blocked") NOT NULL DEFAULT "approved",
  `privacy` varchar(50) NOT NULL DEFAULT "everybody",
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `privacy` (`privacy`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'ode_datalet_post`;
CREATE TABLE `' . OW_DB_PREFIX . 'ode_datalet_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postId` int(11) NOT NULL,
  `dataletId` int(11) NOT NULL,
  `plugin` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `postId` (`postId`),
  KEY `dataletId` (`dataletId`),
  KEY `plugin` (`plugin`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;';

OW::getDbo()->query($sql);
