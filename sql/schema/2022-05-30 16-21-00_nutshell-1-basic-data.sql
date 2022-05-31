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
/*Data for the table `pnut_roles` */

insert  into `pnut_roles`(`id`,`name`,`description`,`createdby`,`createdon`,`changedby`,`changedon`,`active`) values 
(25,'administrator','Full control on website','unknown','2022-05-13 06:57:06',NULL,NULL,1),
(26,'manager','Has limited administrative permissions','unknown','2022-05-13 06:57:06',NULL,NULL,1),
(27,'member','Has membership permissions','unknown','2022-05-13 06:57:06',NULL,NULL,1);

/*Data for the table `pnut_users` */

insert  into `pnut_users`(`id`,`username`,`password`,`active`,`createdby`,`createdon`,`changedby`,`changedon`) values 
(1,'admin','$2y$10$35hp4TKy/wnUTjdEsjU7ZeM48mfz3IWQSm/elydQvHIZ0IUeqe5wy',1,'system','2022-05-13 06:48:49','admin','2022-05-21 14:49:46'),
(122,'terry.sorelle','$2y$10$WvoPkSom7NnF.EuR/.tRJeuv2rBbWuHTaU04s3LIp0z8YFpAm8fCi',1,'system','2022-05-23 16:34:55','admin','2022-05-23 20:15:50'),

/*Data for the table `qnut_email_lists` */

insert  into `qnut_email_lists`(`id`,`code`,`name`,`description`,`mailBox`,`cansubscribe`,`adminonly`,`createdby`,`createdon`,`changedby`,`changedon`,`active`) values 
(1,'news','Email Newsletter',NULL,'news',1,0,'system','2021-11-30 17:38:01',NULL,NULL,1),
(17,'notify','Notifications',NULL,'notify',1,0,'system','2022-05-27 17:04:58',NULL,NULL,1);

/*Data for the table `qnut_listingtypes` */

insert  into `qnut_listingtypes`(`id`,`code`,`name`,`description`,`createdby`,`createdon`,`changedby`,`changedon`,`active`) values 
(0,'none','(none)','No listing','system','2017-10-28 07:59:51',NULL,NULL,1),
(1,'all','All','All listings','system','2017-10-28 08:00:44',NULL,NULL,1),
(2,'lookup','Lookup','Lookup only','system','2017-10-28 08:01:07',NULL,NULL,1),
(3,'printed','Printed','Printed directory only','system','2017-10-28 08:02:13',NULL,NULL,1);

/*Data for the table `tops_processes` */

insert  into `tops_processes`(`id`,`code`,`name`,`description`,`paused`,`enabled`) values 
(2,'email-queue-send','Send email','Process outgoing email in queue',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
