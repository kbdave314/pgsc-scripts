/* This script takes the entries in the uid column from 
    physics-gsc+gsc2014nominations.Candidates
  and creates a table 
    physics-gsc+gsc2014nominations.Votes
  that has uid and said entries as column headings, with uid as a unique identifier. */
USE physics-gsc+election2016;
SET @sql = NULL;
SELECT
  GROUP_CONCAT(DISTINCT
    CONCAT(
      'MAX(IF(uid = ''',
          uid,
          ''', TRUE, FALSE)) AS ',
          uid))
        INTO @sql
        FROM Candidates;
SET @sql = CONCAT('SELECT uid, ', @sql, ' FROM Candidates GROUP BY uid');
SET @sql = CONCAT('CREATE TABLE Votes SELECT * FROM (', @sql,') AS CandidateMatrix WHERE FALSE');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
ALTER TABLE Votes ADD UNIQUE (uid);
