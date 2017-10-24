CREATE TABLE IF NOT EXISTS `plugin_cmf_segment_assignment_index` (
  `elementId` INT(11),
  `elementType` ENUM('document', 'asset', 'object'),
  `segmentId` INT,
  PRIMARY KEY (`elementId`, `elementType`, `segmentId`)
);

CREATE TABLE IF NOT EXISTS `plugin_cmf_segment_assignment_queue` (
  `elementId` INT(11),
  `elementType` ENUM('document', 'asset', 'object'),
  PRIMARY KEY (`elementId`, `elementType`)
);

CREATE TABLE IF NOT EXISTS `plugin_cmf_segment_assignment` (
  `elementId` INT(11),
  `elementType` ENUM('document', 'asset', 'object'),
  `segments` TEXT,
  `breaksInheritance` TINYINT,
  `inPreparation` TINYINT,
  PRIMARY KEY (`elementId`, `elementType`)
);