<?php
class VoteConfig {
  public $title = "Physics Graduate Student Council 2016 Election";
  //public $styleuri = "/style/basestyle.css"; # stylesheet uri
  public $styleuri = "basestyle.css"; # stylesheet uri

  public $positionstable = "Positions";
  public $votetable = "Votes";
  public $candidatetable = "Candidates";

  public $subjectstring = "PHYSICS";
  public $yearstring = "G";
  public $adminlist = "physics-gsc-gsc-election";

  public $electionactive = true;


  /* Status messages */
  public $yearyes = "You are a graduate student, ";
  public $yearno = " You are not a graduate student, ";
  public $subjectyes = "and you are in the Department of Physics. ";
  public $subjectno = "and you are not in the Department of Physics. ";
  public $eligibleyes = "Therefore, you are eligible to vote.";
  public $eligibleno = "Therefore, you are not eligible to vote.";
  public $toomanyyes = "You have voted for too many candidates. Please select fewer candidates.";
  public $submittedyes = "Your vote has successfully been submitted. Any time before the deadline, you may return to this form and revise your vote.";

  /* Error messages */
  public $votefetcherror = "Unable to fetch vote record: ";
  public $candidatefetcherror = "Unable to produce ballot: ";
  public $puterror = "Unable to submit vote: ";
}

/* MySQL Configuration */
class MySQLConfig {
  public $host = "sql.mit.edu";
  public $user = "physics-gsc";
  public function password() {
    return exec("./getsqlpassword.sh");
  }
  public $database = "physics-gsc+election2016";
}
/* LDAP Configuration */
class LDAPConfig {
  public $host = "ldap://ldap-too.mit.edu/";
  public $userbase = "ou=users,ou=moira,dc=mit,dc=edu";
  public $groupbase = "ou=lists,ou=moira,dc=mit,dc=edu"; }
