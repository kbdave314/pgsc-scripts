<?php
require_once 'config.php'; # configuration file
require_once 'includes/sql.php'; # MySQL functions
require_once 'includes/ldap.php'; # LDAP functions
require_once 'includes/xml.php'; # XML/DOM functions
class ProgramState {

  public $sqlconnect; # Was the connection to MySQL successful?
  public $sqlmessage;
  public $sqlerrno; # Error code from last connect call.

  public $sqlsuccess;
  public $sqlputsuccess = false;
  public $sqlputconnect;
  public $sqlputvoteheadsuccess = false;
  public $sqlputmessage;

  public $sqlgetconnect;
  public $sqlgetadminsuccess = false;
  public $sqlgetadminmessage;
  public $sqlgetpossuccess = false;
  public $sqlgetposmessage;
  public $sqlgetusersuccess = false;
  public $sqlgetusermessage;
  public $sqlgetusersuccess2 = false;
  public $sqlgetusermessage2;

  public $ldaploaded; # Is the LDAP extension loaded?
  public $ldapinit; # Did LDAP initialize correctly?
  public $ldapbind; # Was the LDAP bind successful?
  public $ldapuquery; # Did the LDAP user query execute successfully?
  public $ldapufound; # Was the user found in the LDAP database?
  public $ldapmessage; # Where the error string goes.

  public $onacl = false;
  public $fullname = "";
  public $user = "";
  public $subject = "";
  public $year = "";
  function issubject($subjectstring) {return ( $this->subject == $subjectstring);
  }
  function isyear($yearstring) {return ($this->year == $yearstring);} 
  function iseligible($subjectstring, $yearstring) {
    return ($this->isyear($yearstring) && $this->issubject($subjectstring));
  }

  public $hasvoted;
  public $toomany;

  public $raw_votes;
  public $raw_votehead;
  public $raw_positions;
  public $raw_candidates;

  public $candidates;

  public $ballot;
}


function main() {
  $conf = new VoteConfig;
  $imp = new DOMImplementation;
  $mysqlconf = new MySQLConfig;
  $ldapconf = new LDAPConfig;

  $state = new ProgramState;

  produceIdentity($conf, $ldapconf, $state);
  putSQL($conf, $mysqlconf, $state);
  getSQL($conf, $mysqlconf, $state);
  produceCandidateNames($conf, $ldapconf, $state);
  $document = produceDocument($imp, $conf, $state);
  echo $document;
  exit;
}

main();
