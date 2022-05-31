ALTER TABLE table_name_here
ADD `createdby` VARCHAR(50) NOT NULL DEFAULT 'unknown',
ADD `createdon` DATETIME DEFAULT CURRENT_TIMESTAMP,    
ADD `changedby` VARCHAR(50) DEFAULT NULL,              
ADD `changedon` DATETIME DEFAULT NULL                 
-- ADD `active` tinyint(1) NOT NULL DEFAULT '1'
