CREATE TABLE IF NOT EXISTS `plugin_cmf_customer_filter_definition` (
  `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `ownerId` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `definition` text NOT NULL,
  `allowedUserIds` text,
  `readOnly` BIT(1) DEFAULT 0,
  `shortcutAvailable` BIT(1) DEFAULT 0,
  `creationDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `modificationDate` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;