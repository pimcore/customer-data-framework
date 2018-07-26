CREATE TABLE IF NOT EXISTS `plugin_cmf_activities_metadata` (
  `activityId` int(20) NOT NULL,
  `key` varchar(150) COLLATE utf8_bin NOT NULL,
  `data` longtext COLLATE utf8_bin,
  PRIMARY KEY (`activityId`,`key`),
  KEY `activityId` (`activityId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
