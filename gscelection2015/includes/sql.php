<?php
#require_once 'config.php';

function getColumnZero($anarray) {
  return $anarray[0];
}

function getColumnsZero($somearrays) {
  return array_map("getColumnZero", $somearrays);
}

function getValueOf($key) {
  if (isset($_POST[$key])) {
    return 1;
  } else {
    return 0;
  }
}

function toLiteral($key) {
  return (':' . $key);
}

function getSQLi(MySQLConfig $mysqlconf, ProgramState $state) {
  $mysqli = new mysqli($mysqlconf->host, $mysqlconf->user, $mysqlconf->password(), $mysqlconf->database);
  $sqlerror = $mysqli->connect_error;
  $state->sqlconnect = is_null($sqlerror);
  if (!$state->sqlconnect) {
    $state->sqlmessage = $sqlerror;
    $state->sqlerrno = $mysqli->connect_errno;
  }
  return $mysqli;
}

function putSQL(VoteConfig $conf, MySQLConfig $mysqlconf, ProgramState $state) {
  $mysqli = getSQLi($mysqlconf, $state);
  $state->sqlputconnect = $state->sqlconnect;
  if (isset($_POST["submit"])) {
    if ($state->sqlputconnect) {
      if (!(isset($state->raw_votehead))) {
        $votehead = $mysqli->prepare("SHOW COLUMNS FROM " . $conf->votetable);
        $state->sqlputvoteheadsuccess = $votehead->execute();
        if ($state->sqlputvoteheadsuccess) {
        $x = $votehead->get_result();
        $voteheadlist = $x->fetch_all();
        $state->raw_votehead = getColumnsZero($voteheadlist);
        } else {
          $state->sqlputmessage = $votehead->error;
        }
      }
      if ($state->sqlputvoteheadsuccess) {
      $justcandidates = $state->raw_votehead;
      array_shift($justcandidates);
      $justvotes = array_map("getValueOf", $justcandidates);
      if (array_sum($justvotes) > $conf->limit) {
        $state->toomany = true;
        return;
      } else {
      $keystring = "(" . implode(", ", $state->raw_votehead) . ")";
      $literalarray = array_map("toLiteral", $state->raw_votehead);
      $userstring = '"' . $state->user . '"';
      $valuearray = array_merge((array)$userstring, $justvotes);
      $valuestring = "(" . implode(", ", $valuearray) . ")";
      $substitutionrule = array_combine($literalarray, $valuearray);
      $sqlstatement = 
        "REPLACE INTO " 
        . $conf->votetable . " " 
        . $keystring 
        . " VALUES "
        . $valuestring;
      $state->sqlputsuccess =  $mysqli->query($sqlstatement);
      if (!$state->sqlputsuccess) {
        $state->sqlputmessage = $mysqli->error;
      }
      }
      }} else {
        $state->sqlputmessage = $state->sqlmessage;
      }
    }
  return;
}

function getSQL(VoteConfig $conf, MySQLConfig $mysqlconf, ProgramState $state) {
  $mysqli = getSQLi($mysqlconf, $state);
  $state->sqlgetconnect = is_null($mysqli->connect_error);
  if ($state->sqlgetconnect) {
    $eligibility = $state->iseligible($conf->subjectstring, $conf->yearstring);
    $admin = $state->onacl;
      if ($admin) {
        $votehead = $mysqli->prepare("SHOW COLUMNS FROM " . $conf->votetable);
        $state->sqlgetadminsuccess = $votehead->execute();
        if ($state->sqlgetadminsuccess) {
        $x = $votehead->get_result();
        $voteheadlist = $x->fetch_all(MYSQLI_NUM);
        $state->raw_votehead = getColumnsZero($voteheadlist);
        $votes = $mysqli->prepare("SELECT * FROM " . $conf->votetable);
        $state->sqlgetadminsuccess = $votes->execute();
        if ($state->sqlgetadminsuccess) {
        $x = $votes->get_result();
        $voteslist = $x->fetch_all(MYSQLI_ASSOC);
        $state->raw_votes = $voteslist;
        } else {
          $state->sqlgetadminmessage = $votes->error;
        }
        } else {
          $state->sqlgetadminmessage = $votehead->error;
        }
      }
      if ($eligibility) {
        if (!(isset($state->raw_candidates))) {
          $candidates = $mysqli->prepare("SELECT uid FROM " . $conf->candidatetable);
          if (!$candidates) {
            $state->sqlgetusersuccess = false;
          } else {
            $state->sqlgetusersuccess = $candidates->execute();
          }
          if ($state->sqlgetusersuccess) {
          $x = $candidates->get_result();
          $candidatelist = $x->fetch_all();
          $state->raw_candidates = getColumnsZero($candidatelist);
          } else {
            $state->sqlgetusermessage = $mysqli->error;
          }
        }
        $hasvotedquery = (string)('SELECT COUNT(*) FROM '
          . $conf->votetable
          . ' WHERE uid="'
          . $state->user
          . '"');
        $hasvoted = $mysqli->prepare($hasvotedquery);
        if (!$hasvoted) {
          $state->sqlgetusersuccess2 = false;
        }
        else {$state->sqlgetusersuccess2 = $hasvoted->execute();
        }
        if ($state->sqlgetusersuccess2) {
        $state->hasvoted = getColumnZero($hasvoted->get_result()->fetch_all())[0];
        if ($state->hasvoted) {
          $retrieveballot = $mysqli->prepare(
            'SELECT * FROM ' 
            . $conf->votetable
            . ' WHERE uid="'
            . $state->user
            . '"');
          $state->sqlgetusersuccess2 = $retrieveballot->execute();
          if ($state->sqlgetusersuccess2) {
            $state->ballot = $retrieveballot->get_result()->fetch_assoc();
          } else {
            $state->sqlgetusermessage2 = $retrieveballot->error();
          }
        }
        } else {
          $state->sqlgetusermessage2 = $hasvoted->error;
        }
      }
  } else {
    $state->sqlgetmessage = $mysqli->connect_error;
  }
  return;
}
