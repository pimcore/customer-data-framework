DROP FUNCTION IF EXISTS PLUGIN_CMF_COLLECT_DOCUMENT_SEGMENT_ASSIGNMENTS;

CREATE FUNCTION PLUGIN_CMF_COLLECT_DOCUMENT_SEGMENT_ASSIGNMENTS(elementIdent INT) RETURNS VARCHAR(1023)
  BEGIN
    DECLARE segmentIds VARCHAR(1023);
    DECLARE breaks TINYINT;

    SELECT `segments` FROM `plugin_cmf_segment_assignment` WHERE `elementId` = elementIdent INTO segmentIds;
    SELECT `breaksInheritance` FROM `plugin_cmf_segment_assignment` WHERE `elementId` = elementIdent INTO breaks;

    WHILE breaks <> 1 AND elementIdent <> 1 DO
      SELECT `parentId`  FROM `assets` WHERE `id` = elementIdent INTO elementIdent;
      SELECT CONCAT_WS(',', segmentIds, (SELECT `segments` FROM `plugin_cmf_segment_assignment` WHERE `elementId` = elementIdent)) INTO segmentIds;
      SELECT `breaksInheritance` INTO breaks FROM `plugin_cmf_segment_assignment` WHERE `elementId` = elementIdent;
    END WHILE;

    RETURN segmentIds;
  END