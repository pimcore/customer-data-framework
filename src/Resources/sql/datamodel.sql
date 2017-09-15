CREATE TABLE `plugin_cmf_segment_assignment_index` (
  `elementId` INT(11),
  `elementType` ENUM('document', 'asset', 'object'),
  `segment` INT,
  PRIMARY KEY (`elementId`, `elementType`, `segment`)
);

CREATE TABLE `plugin_cmf_segment_assignment_queue` (
  `elementId` INT(11),
  `elementType` ENUM('document', 'asset', 'object'),
  PRIMARY KEY (`elementId`, `elementType`)
);

CREATE TABLE `plugin_cmf_segment_assignment` (
  `elementId` INT(11),
  `elementType` ENUM('document', 'asset', 'object'),
  `segments` VARCHAR(1023),
  `breaksInheritance` TINYINT,
  PRIMARY KEY (`elementId`, `elementType`)
);