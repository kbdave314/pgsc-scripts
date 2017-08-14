<?php

function produceIdentity(VoteConfig $conf, LDAPConfig $ldapconf, ProgramState $state) {
  # Retrieves user id from certificate.
  $state->user = strstr($_SERVER['SSL_CLIENT_S_DN_Email'], '@', true);
  $state->ldaploaded = extension_loaded("ldap");
  if ($state->ldaploaded) {
    $ldap = ldap_connect($ldapconf->host);
  if ($ldap) {
    $state->ldapinit = true;
    $state->ldapbind = ldap_bind($ldap);
    if ($state->ldapbind) {
    # Searches for user in LDAP user database.
    $ldapuserquery = "uid=" . $state->user;
    $ldapusersearch = ldap_search($ldap, $ldapconf->userbase, $ldapuserquery);
    $state->ldapuquery = (bool)$ldapusersearch;
    if (!$state->ldapuquery) {
      $state->ldapmessage = "The LDAP user query failed: " . ldap_error($ldap) . ".";
    } else { 
    $ldapuserresult = ldap_get_entries($ldap, $ldapusersearch);
    if ($ldapuserresult['count']==0) {
      $state->ldapufound = false;
      $state->ldapmessage = "LDAP couldn't find a record of you.";
    } else {
      $state->ldapufound = true;
      $state->year = $ldapuserresult[0]["mitdirstudentyear"][0];
      $state->fullname = $ldapuserresult[0]["displayname"][0];
      $state->subject = $ldapuserresult[0]["ou"][0];
    }}

    $ldapgroupuserquery = "(&(cn=" . $conf->adminlist . ")(member=uid=" . $state->user . "," . $ldapconf->userbase . "))";
    $ldapgroupusersearch = ldap_search($ldap, $ldapconf->groupbase, $ldapgroupuserquery);
    $ldapgroupuserresult = ldap_get_entries($ldap, $ldapgroupusersearch);
    if ($ldapgroupuserresult['count']) {
      $state->onacl = true;
    } else {
      $state->onacl = false;
    }
    } else {
      $state->ldapbind = false;
      $state->ldapmessage = ldap_error($ldap) . ".";
  }} else {
    $state->ldapinit = false;
    $state->ldapmessage = ldap_error($ldap) . ".";
  }
    ldap_close($ldap);
  } else { 
    $state->ldaploaded = false; 
    $state->ldapmessage = "The LDAP extension for PHP (ldap.so) is not loaded.";
  }
  return;
}

function produceCandidateNames(VoteConfig $conf, LDAPConfig $ldapconf, ProgramState $state) {
  $voteheadlist = $state->raw_candidates;
  if (extension_loaded("ldap")) {
    $ldap = ldap_connect($ldapconf->host);
  if ($ldap) {
    ldap_bind($ldap);
    $candidatearray = array();
    foreach ($voteheadlist as list($position,$uid)) {
    $ldapuserquery = "uid=" . $uid;
    $ldapusersearch = ldap_search($ldap, $ldapconf->userbase, $ldapuserquery);
    $ldapuserresult = ldap_get_entries($ldap, $ldapusersearch);
    if ($ldapuserresult['count']==1) {
      $candidateline = array($position, $uid, $ldapuserresult[0]["displayname"][0]);
    } else {
      $candidateline = array($position, $uid);
    }
      array_push($candidatearray, $candidateline);
    }
    $state->candidates = $candidatearray;
  }
  return;
}}
