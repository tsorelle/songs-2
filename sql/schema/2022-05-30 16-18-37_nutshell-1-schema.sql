/*
SQLyog Professional v13.1.9 (64 bit)
MySQL - 5.7.14 : Database - twoquake_nuts
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `pnut_authentications` */

DROP TABLE IF EXISTS `pnut_authentications`;

CREATE TABLE `pnut_authentications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) DEFAULT NULL,
  `last` datetime DEFAULT CURRENT_TIMESTAMP,
  `attempts` int(11) NOT NULL DEFAULT '1',
  `success` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=latin1;

/*Table structure for table `pnut_contacts` */

DROP TABLE IF EXISTS `pnut_contacts`;

CREATE TABLE `pnut_contacts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `listingtypeId` int(11) unsigned DEFAULT '1',
  `sortkey` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uid` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accountId` int(10) unsigned DEFAULT NULL,
  `createdby` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `createdon` datetime DEFAULT CURRENT_TIMESTAMP,
  `changedby` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changedon` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id_pnut_person` (`uid`),
  KEY `PersonNames` (`fullname`)
) ENGINE=MyISAM AUTO_INCREMENT=205 DEFAULT CHARSET=latin1;

/*Table structure for table `pnut_roles` */

DROP TABLE IF EXISTS `pnut_roles`;

CREATE TABLE `pnut_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `createdby` varchar(50) NOT NULL DEFAULT 'unknown',
  `createdon` datetime DEFAULT CURRENT_TIMESTAMP,
  `changedby` varchar(50) DEFAULT NULL,
  `changedon` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;

/*Table structure for table `pnut_userroles` */

DROP TABLE IF EXISTS `pnut_userroles`;

CREATE TABLE `pnut_userroles` (
  `userId` int(10) unsigned NOT NULL,
  `roleId` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `pnut_users` */

DROP TABLE IF EXISTS `pnut_users`;

CREATE TABLE `pnut_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(512) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `createdby` varchar(50) NOT NULL DEFAULT 'unknown',
  `createdon` datetime DEFAULT CURRENT_TIMESTAMP,
  `changedby` varchar(50) DEFAULT NULL,
  `changedon` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=126 DEFAULT CHARSET=latin1;

/*Table structure for table `pnut_usersessions` */

DROP TABLE IF EXISTS `pnut_usersessions`;

CREATE TABLE `pnut_usersessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sessionid` varchar(255) NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `signedin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session_id` (`sessionid`)
) ENGINE=MyISAM AUTO_INCREMENT=115 DEFAULT CHARSET=latin1;

/*Table structure for table `qnut_email_corrections` */

DROP TABLE IF EXISTS `qnut_email_corrections`;

CREATE TABLE `qnut_email_corrections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personId` int(11) unsigned DEFAULT NULL,
  `accountId` int(10) unsigned DEFAULT NULL,
  `reportedDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `errorLevel` int(11) NOT NULL,
  `errorMessage` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `retriesLeft` tinyint(4) DEFAULT '0',
  `createdby` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `createdon` datetime DEFAULT CURRENT_TIMESTAMP,
  `changedby` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changedon` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=971 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `qnut_email_lists` */

DROP TABLE IF EXISTS `qnut_email_lists`;

CREATE TABLE `qnut_email_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mailBox` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cansubscribe` tinyint(1) NOT NULL DEFAULT '0',
  `adminonly` tinyint(1) NOT NULL DEFAULT '0',
  `createdby` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `createdon` datetime DEFAULT CURRENT_TIMESTAMP,
  `changedby` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changedon` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `qnut_email_messages` */

DROP TABLE IF EXISTS `qnut_email_messages`;

CREATE TABLE `qnut_email_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listId` int(11) NOT NULL,
  `sender` varchar(128) NOT NULL,
  `replyAddress` varchar(128) DEFAULT NULL,
  `subject` varchar(128) DEFAULT NULL,
  `messageText` text NOT NULL,
  `contentType` char(4) DEFAULT NULL,
  `template` varchar(128) DEFAULT NULL,
  `tags` varchar(128) DEFAULT NULL,
  `recipientCount` int(11) DEFAULT '1',
  `postedDate` datetime NOT NULL,
  `postedBy` varchar(128) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Table structure for table `qnut_email_queue` */

DROP TABLE IF EXISTS `qnut_email_queue`;

CREATE TABLE `qnut_email_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mailMessageId` int(11) NOT NULL,
  `personId` varchar(36) DEFAULT NULL,
  `toAddress` varchar(128) DEFAULT NULL,
  `toName` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2257 DEFAULT CHARSET=latin1;

/*Table structure for table `qnut_email_subscriptions` */

DROP TABLE IF EXISTS `qnut_email_subscriptions`;

CREATE TABLE `qnut_email_subscriptions` (
  `personId` int(11) NOT NULL DEFAULT '0',
  `listId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`personId`,`listId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `qnut_listingtypes` */

DROP TABLE IF EXISTS `qnut_listingtypes`;

CREATE TABLE `qnut_listingtypes` (
  `id` int(10) unsigned NOT NULL,
  `code` varchar(32) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `createdby` varchar(64) NOT NULL DEFAULT 'system',
  `createdon` datetime DEFAULT CURRENT_TIMESTAMP,
  `changedby` varchar(64) DEFAULT NULL,
  `changedon` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_listingtypes` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `tops_mailboxes` */

DROP TABLE IF EXISTS `tops_mailboxes`;

CREATE TABLE `tops_mailboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailboxcode` varchar(30) NOT NULL DEFAULT '',
  `address` varchar(100) DEFAULT NULL,
  `displaytext` varchar(100) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `createdby` varchar(50) NOT NULL DEFAULT 'unknown',
  `createdon` datetime DEFAULT CURRENT_TIMESTAMP,
  `changedby` varchar(50) DEFAULT NULL,
  `changedon` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `boxIndex` (`mailboxcode`)
) ENGINE=MyISAM AUTO_INCREMENT=179 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_permissions` */

DROP TABLE IF EXISTS `tops_permissions`;

CREATE TABLE `tops_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permissionName` varchar(128) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permissions_name` (`permissionName`)
) ENGINE=MyISAM AUTO_INCREMENT=166 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_process_log` */

DROP TABLE IF EXISTS `tops_process_log`;

CREATE TABLE `tops_process_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `processCode` varchar(128) DEFAULT NULL,
  `posted` datetime DEFAULT NULL,
  `event` varchar(128) DEFAULT NULL,
  `messageType` int(11) DEFAULT NULL,
  `message` varchar(1024) DEFAULT NULL,
  `detail` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=150 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_processes` */

DROP TABLE IF EXISTS `tops_processes`;

CREATE TABLE `tops_processes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(128) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `description` varchar(128) DEFAULT NULL,
  `paused` datetime DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_rolepermissions` */

DROP TABLE IF EXISTS `tops_rolepermissions`;

CREATE TABLE `tops_rolepermissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permissionId` int(11) DEFAULT NULL,
  `roleName` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissionRoleIdx` (`permissionId`,`roleName`)
) ENGINE=MyISAM AUTO_INCREMENT=206 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_tasklog` */

DROP TABLE IF EXISTS `tops_tasklog`;

CREATE TABLE `tops_tasklog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime DEFAULT NULL,
  `type` int(10) unsigned DEFAULT NULL,
  `message` varchar(256) DEFAULT NULL,
  `taskname` varchar(128) DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=507 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_taskqueue` */

DROP TABLE IF EXISTS `tops_taskqueue`;

CREATE TABLE `tops_taskqueue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `frequency` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '24 Hours',
  `intervalType` tinyint(4) DEFAULT '1',
  `taskname` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `namespace` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `inputs` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comments` mediumtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
