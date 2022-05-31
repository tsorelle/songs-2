/*
SQLyog Professional v13.1.1 (64 bit)
MySQL - 5.7.14 : Database - twoquake_qnut
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


/*-- OPTIONAL: Table structure for table `tops_entity_properties`

CREATE TABLE `tops_entity_properties` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entityCode` VARCHAR(128) NOT NULL,
  `key` VARCHAR(128) NOT NULL,
  `order` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `valueCount` INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `lookup` VARCHAR(128) DEFAULT NULL,
  `required` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `defaultValue` VARCHAR(128) DEFAULT NULL,
  `datatype` VARCHAR(2) NOT NULL DEFAULT 's',
  `label` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;


CREATE TABLE `tops_entity_property_values` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `instanceId` INT(10) UNSIGNED NOT NULL,
  `entityPropertyId` INT(10) UNSIGNED NOT NULL,
  `value` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM AUTO_INCREMENT=131 DEFAULT CHARSET=latin1;
*/

/*Table structure for table `tops_mailboxes` */

CREATE TABLE `tops_mailboxes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mailboxcode` VARCHAR(30) NOT NULL DEFAULT '',
  `address` VARCHAR(100) DEFAULT NULL,
  `displaytext` VARCHAR(100) DEFAULT NULL,
  `description` VARCHAR(100) DEFAULT NULL,
  `createdby` VARCHAR(50) NOT NULL DEFAULT 'unknown',
  `createdon` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `changedby` VARCHAR(50) DEFAULT NULL,
  `changedon` DATETIME DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  `public` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `boxIndex` (`mailboxcode`)
) ENGINE=MYISAM AUTO_INCREMENT=172 DEFAULT CHARSET=latin1;

CREATE TABLE `tops_permissions` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `permissionName` VARCHAR(128) NOT NULL,
  `description` VARCHAR(512) DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permissions_name` (`permissionName`)
) ENGINE=MYISAM AUTO_INCREMENT=166 DEFAULT CHARSET=latin1;

/*Data for the table `tops_permissions` */

INSERT  INTO `tops_permissions`(`id`,`permissionName`,`description`,`active`) VALUES 
(1,'administer-mailboxes','Administer mailboxes',1),
(2,'administer-peanut-features','Administer peanut features',1),
(3,'administer-directory','Administer directory',1),
(4,'view-directory','View directory',1),
(5,'update-directory','Update directory',1),
(6,'send-mailings','Send mailings',1),
(7,'edit-content','Edit content',1),
(8,'update-calendar','Update events calendar',1);


CREATE TABLE `tops_process_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processCode` VARCHAR(128) DEFAULT NULL,
  `posted` DATETIME DEFAULT NULL,
  `event` VARCHAR(128) DEFAULT NULL,
  `messageType` INT(11) DEFAULT NULL,
  `message` VARCHAR(1024) DEFAULT NULL,
  `detail` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_processes` */

CREATE TABLE `tops_processes` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(128) DEFAULT NULL,
  `name` VARCHAR(128) DEFAULT NULL,
  `description` VARCHAR(128) DEFAULT NULL,
  `paused` DATETIME DEFAULT NULL,
  `enabled` TINYINT(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MYISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_rolepermissions` */

CREATE TABLE `tops_rolepermissions` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `permissionId` INT(11) DEFAULT NULL,
  `roleName` VARCHAR(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissionRoleIdx` (`permissionId`,`roleName`)
) ENGINE=MYISAM AUTO_INCREMENT=206 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_tasklog` 

*/

CREATE TABLE `tops_tasklog` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `time` DATETIME DEFAULT NULL,
  `type` INT(10) UNSIGNED DEFAULT NULL,
  `message` VARCHAR(256) DEFAULT NULL,
  `taskname` VARCHAR(128) DEFAULT NULL,
  `active` TINYINT(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MYISAM AUTO_INCREMENT=491 DEFAULT CHARSET=latin1;

/*Table structure for table `tops_taskqueue` */

CREATE TABLE `tops_taskqueue` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `frequency` VARCHAR(32) NOT NULL DEFAULT '24 Hours',
  `taskname` VARCHAR(128) DEFAULT NULL,
  `namespace` VARCHAR(128) DEFAULT NULL,
  `startdate` DATE DEFAULT NULL,
  `enddate` DATE DEFAULT NULL,
  `inputs` VARCHAR(512) DEFAULT NULL,
  `comments` TEXT,
  `active` TINYINT(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MYISAM AUTO_INCREMENT=99 DEFAULT CHARSET=latin1;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
