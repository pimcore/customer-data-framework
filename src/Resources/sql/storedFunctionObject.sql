DROP FUNCTION IF EXISTS PLUGIN_CMF_COLLECT_OBJECT_SEGMENT_ASSIGNMENTS;

CREATE FUNCTION PLUGIN_CMF_COLLECT_OBJECT_SEGMENT_ASSIGNMENTS(elementIdent INT) RETURNS TEXT
  BEGIN
    DECLARE segmentIds TEXT;
    DECLARE breaks TINYINT;

    SELECT `segments` FROM `plugin_cmf_segment_assignment` WHERE `elementId` = elementIdent INTO segmentIds;
    SELECT `breaksInheritance` FROM `plugin_cmf_segment_assignment` WHERE `elementId` = elementIdent INTO breaks;

    WHILE breaks <> 1 AND elementIdent <> 1 DO
      SELECT `o_parentId`  FROM `objects` WHERE `o_id` = elementIdent INTO elementIdent;
      SELECT CONCAT_WS(',', segmentIds, (SELECT `segments` FROM `plugin_cmf_segment_assignment` WHERE `elementId` = elementIdent)) INTO segmentIds;
      SELECT `breaksInheritance` INTO breaks FROM `plugin_cmf_segment_assignment` WHERE `elementId` = elementIdent;
    END WHILE;

    RETURN segmentIds;
  END