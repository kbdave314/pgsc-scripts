#!/usr/bin/php-cgi
<?php
date_default_timezone_set('America/New_York');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html version="-//W3C//DTD XHTML 1.1//EN"
      xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<link rel="stylesheet" href="../style/basestyle" />
<!--
<?php
$lprefix = "system:";
$acl = system("fs la ./ | grep 'system' | head -n1 | cut -d' ' -f3 | cut -d':' -f2");
$accesslist = system("grep '^[Require]' .htaccess | cut -d':' -f2");
$hascert = @$_SERVER['SSL_CLIENT_S_DN_CN'];
if ($hascert) {
$fullname = $_SERVER['SSL_CLIENT_S_DN_CN'];
$email = $_SERVER['SSL_CLIENT_S_DN_Email'];
$user = strstr($email, '@', true);
$hasaccess = @system("blanche $accesslist -u -r -noauth | grep '$user'");
$onacl = @system("blanche $acl -u -r -noauth | grep '$user'");

};
$mysqli = new mysqli("sql.mit.edu", "physics-gsc",, "physics-gsc+openhouse2015nominations");
if ($mysqli->connect_errno) {
      $connectstring = "<p>Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "</p>";
} else $connectstring ="<p>Connection to MySQL server successful.</p>";

$doremove = "no";
$doadd = "no";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $doremove = $_POST["removeme"];
  $doadd = $_POST["addme"];
}
if ($doadd == "yes") {
  $candidatequery = $mysqli->query("SELECT uid FROM Signups WHERE uid = '$user'");
  $onlist = $candidatequery->num_rows;
  $wasadded = false;
  $duplicate = false;
  if ($onlist == 0) {
    $wasadded = $mysqli->query("INSERT INTO Signups (uid, timestamp) VALUES ('$user', " . time() . ")");
  } else {
    $duplicate = true;
  };
};
if ($doremove == "yes") {
  $wasremoved = $mysqli->query("DELETE FROM Signups WHERE uid = '$user'");
};
?>
-->
<title>PGSC: 2015 Poster Session Signup</title>
</head>
<body>
<h1>PGSC: 2015 Poster Session Signup</h1>
<?php
if ($wasadded) echo '<p><strong>You have signed up to present at the poster session.</strong></p>';
if ($wasremoved) echo '<p><strong>You have withdrawn from the poster session.</strong></p>';
if ($duplicate) echo '<p><strong>You had already signed up to present at the poster session.</strong></p>';
?>
<h2>Identification</h2>
<?php
if ($hascert) {?>
<p>You have been identified as <strong><?php echo "$fullname";?> (<?php echo "$email";?>)</strong>.</p>
<?php } else { ?>
<p>You have not been identified. Nothing will be displayed.</p>
<?php }; ?>
<?php
  if ($onacl) {?>
<h2>Administration</h2>
<p>You have administrative privileges.</p>
<?php 
  echo $connectstring;
  $res = $mysqli->query("SELECT * FROM Signups ORDER BY uid ASC");
?>
<h3>List of presenters</h3>
<table>
<thead>
<tr>
<td>MIT User ID</td>
<td>Timestamp</td>
</tr>
</thead>
<tbody>
<?php
$res->data_seek(0);
while ($row = $res->fetch_assoc()) {
  $thetimestamp = date('r', $row['timestamp']);
  echo "<tr><td>" . $row['uid'] . "</td><td>" . $thetimestamp . "</td></tr>\n";
}
# SELECT uid FROM physics-gsc+gsc2014nominations WHERE uid = $user
# INSERT INTO `physics-gsc+gsc2014nominations`.`Candidates` (`uid`) VALUES ('kdave');
?>
</tbody>
</table>
<?php  } ; ?>
<h2>Signup</h2>
<?php
$res = $mysqli->query("SELECT uid FROM Signups WHERE uid = '$user'");
$onlist = $res->num_rows;
?>
<p><?php if ($onlist == 0) {echo 'You have not signed up for the poster session. Submit this form to sign up.'; }
  else {echo 'You have signed up for the poster session. Submit this form to withdraw.';} ; ?>
</p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<?php
if ($onlist == 0) echo '<input type="hidden" name="addme" value="yes" /><input type="hidden" name="removeme" value="no" />';
if ($onlist > 0) echo '<input type="hidden" name="addme" value="no" /><input type="hidden" name="removeme" value="yes" />';
echo "\n";
?>
<input id="submit" type="submit" name="Toggle" value="Submit" />
</form>
</body>
</html>
