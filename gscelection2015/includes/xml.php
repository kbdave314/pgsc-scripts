<?php
function errorLine(DOMDocument $xml, $str) {
  $xml_li = $xml->createElement("li", $str);
  $xml_li->setAttribute("class", "error");
  return $xml_li;
}
function userIdentity(DOMDocument $xml, VoteConfig $conf, ProgramState $state) {
  $xml_ident = $xml->createElement("ul",NULL);
  $idsubel = $xml->createElement("strong", "(" . $state->user . ")");
  $idfn = $xml->createElement("strong", $_SERVER['SSL_CLIENT_S_DN_CN']);
  $idstring = "Your certificate identifies you as ";
  $idid = $xml->createElement("li", $idstring);
  $idid->appendChild($idfn);
  $idid->appendChild(new DOMText(". "));
  $idid->appendChild($idsubel);
  $idid->setAttribute("class", "good");
  $xml_ident->appendChild($idid);
  if (!$state->ldaploaded or !$state->ldapinit or !$state->ldapufound) {
    $xml_ident->appendChild(errorLine($xml, $state->ldapmessage));
  } else {
  if ($state->isyear($conf->yearstring)) {
    $year_s = $conf->yearyes;
    $yclass = "good";
  }
  else {
    $year_s = $conf->yearno;
    $yclass = "bad";
  }
  $idy = $xml->createElement("li", $year_s);
  $idysub = $xml->createElement("strong", "(" . $state->year . ")");
  $idy->appendChild($idysub);
  $idy->setAttribute("class", $yclass);
  $xml_ident->appendChild($idy);

  if ($state->issubject($conf->subjectstring)) {
    $subject_s = $conf->subjectyes;
    $sclass = "good";
  } else {
    $subject_s = $conf->subjectno;
    $sclass = "bad";
  }
  $ids = $xml->createElement("li", $subject_s);
  $idssub = $xml->createElement("strong", "(" . $state->subject . ")");
  $ids->appendChild($idssub);
  $ids->setAttribute("class", $sclass);
  $xml_ident->appendChild($ids);

  if ($state->iseligible($conf->subjectstring, $conf->yearstring)) {
    $eligible_s = $conf->eligibleyes;
    $eclass = "great";
  } else {
    $eligible_s = $conf->eligibleno;
    $eclass = "terrible";
  }
  $ide = $xml->createElement("li", $eligible_s);
  $ide->setAttribute("class", $eclass);
  $xml_ident->appendChild($ide);
  }

  $xml_idfrag = $xml->createDocumentFragment();
  $xml_idfrag->appendChild($xml->createElement("h2", "Identification"));
  $xml_idfrag->appendChild($xml_ident);
  return $xml_idfrag;
}

function initDocument(DOMImplementation $imp) {
  $dtd = $imp->createDocumentType('html','','');
  $xml = $imp->createDocument("", "", $dtd);
  $xml->encoding = 'utf-8';
  $xml->preserveWhiteSpace = false;
  $xml->formatOutput = true;
  return $xml;
}
function initRoot(DOMDocument $xml) {
  $xml_html = $xml->createElement("html");
  $xml_html->setAttribute("xmlns", "http://www.w3.org/1999/xhtml");
  $xml_html->setAttribute("xml:lang", "en");
  return $xml_html;
}
function initHead(DOMDocument $xml, VoteConfig $conf) {
  $xml_head = $xml->createElement("head");
  $xml_title = $xml->createElement("title", $conf->title);
  $xml_stylelink = $xml->createElement("link");
  $xml_stylelink->setAttribute("rel", "stylesheet");
  $xml_stylelink->setAttribute("href", $conf->styleuri);
  $xml_head->appendChild($xml_title);
  $xml_head->appendChild($xml_stylelink);
  return $xml_head;
}

function statusFragment(DOMDocument $xml, VoteConfig $conf, ProgramState $state) {
  $xml_status = $xml->createDocumentFragment();
  if ($state->sqlputsuccess) {
    $xml_status_good = $xml->createElement("p", $conf->submittedyes);
    $xml_status_good->setAttribute("class", "goodstatus");
    $xml_status->appendChild($xml_status_good);
    return $xml_status;
  } else if ($state->toomany) {
    $xml_status_bad = $xml->createElement("p", $conf->toomanyyes);
    $xml_status_bad->setAttribute("class", "badstatus");
    $xml_status->appendChild($xml_status_bad);
    return $xml_status;
  } else if (!is_null($state->sqlputmessage)) {
    $xml_status_error = $xml->createElement("p", $conf->puterror . $state->sqlputmessage);
    $xml_status_error->setAttribute("class", "error");
    $xml_status->appendChild($xml_status_error);
    return $xml_status;
  } else {
    $xml_status->appendChild($xml->createElement("br", NULL));
    return $xml_status;
  }
}
function voteDump(DOMDocument $xml, VoteConfig $conf, ProgramState $state) {
  if ($state->sqlgetconnect) {
  if ($state->sqlgetadminsuccess) {
    $voteheadlist = $state->raw_votehead;
    $xml_votes_head = $xml->createElement("thead", NULL);
    $xml_votes_head1 = $xml_votes_head->appendChild($xml->createElement("tr", NULL));
    foreach ($voteheadlist as $row) {
      $xml_votes_head1->appendChild($xml->createElement("th", $state->candidatenames[$row]));
    }
    $voteslist = $state->raw_votes;
    $xml_votes_body = $xml->createElement("tbody", NULL);
    if($voteslist) {
    foreach ($voteslist as $row) {
      $xml_vote_line = $xml->createElement("tr", NULL);
      foreach ($row as $col) {
        $xml_vote_line->appendChild($xml->createElement("td", $col));
      }
      $xml_votes_body->appendChild($xml_vote_line);
    }}
    $xml_votes = $xml->createElement("table", NULL);
    $xml_votes->appendChild($xml_votes_head);
    $xml_votes->appendChild($xml_votes_body);
  } else {
    $xml_votes = $xml->createElement("ul", NULL); 
    $xml_votes->appendChild(errorLine($xml, $conf->votefetcherror . $state->sqlgetadminmessage));
  }
  return $xml_votes;
  } else {
    $xml_votes = $xml->createElement("ul", NULL);
    $xml_votes->appendChild(errorLine($xml, $conf->votefetcherror . $state->sqlmessage));
    return $xml_votes;
  }
}

function sqlStatus(DOMDocument $xml, ProgramState $state) {
  if (!($state->sqlgetconnect)) {
    $connectstring = "Failed to connect to MySQL: (" 
      . $state->sqlerrno 
      . ") " 
      . $state->sqlmessage
      . ".";
    $class = "error";
  } else {
    $connectstring = "Connection to MySQL database successful.";
    $class = "good";
      }
  $xml_sqlstatus = $xml->createElement("p", $connectstring);
    $xml_sqlstatus->setAttribute("id","sqlstatus");
    $xml_sqlstatus->setAttribute("class", $class);
  return $xml_sqlstatus;
}
function ldapStatus(DOMDocument $xml, ProgramState $state) {
  if (!($state->ldapbind)) {
    $connectstring = "Failed to connect to LDAP server: " . $state->ldapmessage ;
    $class = "error";
  } else {
    $connectstring = "Connection to LDAP server successful.";
    $class = "good";
      }
  $xml_ldapstatus = $xml->createElement("p", $connectstring);
  $xml_ldapstatus->setAttribute("id","ldapstatus");
  $xml_ldapstatus->setAttribute("class", $class);
  return $xml_ldapstatus;
}
function initAdministration(DOMDocument $xml, VoteConfig $conf, DOMElement $xml_votes, ProgramState $state) {
  $xml_adminfragment = $xml->createDocumentFragment();
  $xml_adminfragment->appendChild($xml->createElement("h2", "Administration"));
  $xml_belongs = $xml->createElement("p", "You belong to the administrative list. ");
  $xml_belongs->appendChild($xml->createElement("strong", "(" . $conf->adminlist . ")"));
  $xml_belongs->setAttribute("class", "good");
  $xml_adminfragment->appendChild($xml_belongs);
  $xml_adminfragment->appendChild(sqlStatus($xml, $state));
  $xml_adminfragment->appendChild(ldapStatus($xml, $state));
/*  $xml_adminfragment->appendChild($xml->createElement("h3", "PHP Extensions"));
$xml_adminfragment->appendChild($xml->createElement("pre", implode("\n", get_loaded_extensions())));*/
  $xml_adminfragment->appendChild($xml->createElement("h3", "Voting Record"));
  $xml_adminfragment->appendChild($xml_votes);
  return $xml_adminfragment;
}

function candidateFragment(DOMDocument $xml, $nameof, ProgramState $state) {
  $xml_candidateline = $xml->createElement("li", NULL);
  $xml_candidatebox = $xml->createElement("input", NULL);
  $xml_candidatebox->setAttribute("name", $nameof);
  $xml_candidatebox->setAttribute("value", $nameof);
  $xml_candidatebox->setAttribute("type", "checkbox");
  if ($state->sqlgetusersuccess2) {
  if ($state->hasvoted) {
    if ($state->ballot["$nameof"]) { 
      $xml_candidatebox->setAttribute("checked", "checked");
      $xml_candidateline->setAttribute("class", "chosen");
    }
  }}
  if (isset($_POST[$nameof])) {
    if ($_POST["$nameof"]) $xml_candidatebox->setAttribute("checked", "checked");
  }
  $xml_candidatelabel = $xml->createElement("label", $state->candidatenames[$nameof]);
  $xml_candidatelabel->setAttribute("for", $nameof);
  $xml_candidateline->appendChild($xml_candidatebox);
  $xml_candidateline->appendChild($xml_candidatelabel);
  return $xml_candidateline;
}

function candidateList(DOMDocument $xml, VoteConfig $conf, ProgramState $state) {
  if ($state->sqlgetusersuccess) {
      $candidatelist = $state->raw_candidates;
      $xmlcandidate = $xml->createElement("ul", NULL);
      $xmlcandidate->setAttribute("id", "votelist");
      foreach ($candidatelist as $row) {
      $xml_candidatefragment = candidateFragment($xml, $row, $state);
      $xmlcandidate->appendChild($xml_candidatefragment);
      }
  } else {
    $xmlcandidate = $xml->createElement("p", $conf->candidatefetcherror . $state->sqlgetusermessage);
    $xmlcandidate->setattribute("class", "error");
  }
  return $xmlcandidate;
}

function initForm(DOMDocument $xml, VoteConfig $conf, DOMElement $xmlcandidate, ProgramState $state) {
  $xml_formfragment = $xml->createDocumentFragment();
  $xml_formfragment->appendChild($xml->createElement("h2", "Vote"));
  if ($state->hasvoted) {
    $votestring = "You have already voted. You may change your vote using this form. You may choose at most " . $conf->limit . " candidates.";
  } else {
    $votestring = "You have not yet voted. Please submit your preferences. You may choose at most " . $conf->limit . " candidates.";
  }
  $xml_formfragment->appendChild($xml->createElement("p", $votestring));
  $xml_formfragment->appendChild($xml->createElement("h3", "Ballot"));
  $xml_form = $xml->createElement("form");
  $xml_form->setAttribute("method", "post");
  $xml_form->setAttribute("action", "./");
  $xml_formfragment->appendChild($xml_form);
  $xml_form->appendChild($xmlcandidate);

  $xml_uid = $xml->createElement("input");
  $xml_uid->setAttribute("type", "hidden");
  $xml_uid->setAttribute("name", "uid");
  $xml_uid->setAttribute("value", $state->user);
  $xml_form->appendChild($xml_uid);

  $xml_submit = $xml->createElement("input");
  $xml_submit->setAttribute("id", "submit");
  $xml_submit->setAttribute("type", "submit");
  $xml_submit->setAttribute("name", "submit");
  $xml_submit->setAttribute("value", "Submit");
  $xml_form->appendChild($xml_submit);
  return $xml_formfragment;
}

function initBody(DOMDocument $xml, VoteConfig $conf, ProgramState $state) {
  $xml_body = $xml->createElement("body");
  $xml_heading = $xml->createElement("h1", NULL);
  $xml_logo = $xml->createElement("a", " ");
  $xml_logo->setAttribute("href", "http://www.mit.edu");
  $xml_logo->setAttribute("class", "mit-logo");
  $xml_heading->appendChild($xml_logo);
  $xml_heading->appendChild(new DOMText($conf->title));
  $xml_body->appendChild($xml_heading);
  $xml_body->appendChild(statusFragment($xml, $conf, $state));
  if ($conf->electionactive) $xml_body->appendChild(userIdentity($xml, $conf, $state));
  $admin = $state->onacl;
  if ($admin) {
    $xml_votes = voteDump($xml, $conf, $state);
    $xml_body->appendChild(initAdministration($xml, $conf, $xml_votes, $state));
  }
  $eligibility = $state->iseligible($conf->subjectstring, $conf->yearstring);
  if ($eligibility and $conf->electionactive) {
    $xml_candidate = candidateList($xml, $conf, $state);
    $xml_body->appendChild(initForm($xml, $conf, $xml_candidate, $state));
  }
  return $xml_body;
}

function produceDocument(DOMImplementation $imp, VoteConfig $conf, ProgramState $state) {
  $xml = initDocument($imp);
  $xml_html = initRoot($xml);
  $xml_head = initHead($xml, $conf);
  $xml_html->appendChild($xml_head); 
  $xml_body = initBody($xml, $conf, $state);
  $xml_html->appendChild($xml_body);
  $xml->appendChild($xml_html);
  return $xml->saveXML();
}

