#!/usr/bin/php-cgi

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html version="-//W3C//DTD XHTML 1.1//EN"
      xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<link rel="stylesheet" href="../style/basestyle" />
<!--
<?php
### Text ###
$pagetitle = "PGSC: 2014 GSC Representative Nomination Form";
$pageheading = $pagetitle;

$wasaddedstring = "You have added yourself to the candidate list.";
$wasremovedstring = "You have removed yourself from the candidate list.";
$duplicatestring = "You were already on the candidate list to begin with.";

$identificationstringprefix = "You have been identified as \n";
$noidentificationstring = 
  "You have not been identified. 
  Nothing will be displayed.";

$hasprivilegesstring = "You have administrative privileges.";

$onliststring = 
  "You are not on the list of candidates. 
  Submit this form to nominate yourself.";
$offliststring =
  "You are on the list of candidates. 
  Submit this form to rescind your nomination.";

### SQL settings ###
$mysqli = new mysqli( # sql interface
    "sql.mit.edu", # sql server
    "physics-gsc", # sql username
    , # sql password
    "physics-gsc+gsc2014nominations" # sql database
  );

### Business logic ###
## Access control
$lprefix = "system:"; # Moira group prefix
$acl = system(
    "fs la ./ | \
    grep 'system' | \
    head -n1 | \
    cut -d' ' -f3 | \
    cut -d':' -f2"); # Access control list of this locker
$accesslist = system(
  "grep '^[Require]' .htaccess | \
  cut -d':' -f2"); # Access list for this directory
$hascert = @$_SERVER['SSL_CLIENT_S_DN_CN']; # Authentication check
if ($hascert) {
$fullname = $_SERVER['SSL_CLIENT_S_DN_CN']; # Full name
$email = $_SERVER['SSL_CLIENT_S_DN_Email']; # Email
$user = strstr($email, '@', true); # Athena username
$hasaccess = @system("blanche $accesslist -u -r -noauth | \ 
  grep '$user'"); # Authorization check -- nomination
$onacl = @system("blanche $acl -u -r -noauth | \
  grep '$user'"); # Authorization check -- administration
};

## SQL connection
if ($mysqli->connect_errno) {
  $connectstring = 
    "<p>Failed to connect to MySQL: (" . 
    $mysqli->connect_errno . 
    ") " . 
    $mysqli->connect_error . 
    "</p>";
} else $connectstring ="<p>Connection to MySQL server successful.</p>";

## Insertion, selection, and deletion
$doremove = "no";
$doadd = "no";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $doremove = $_POST["removeme"];
  $doadd = $_POST["addme"];
}
if ($doadd == "yes") {
  $candidatequery = $mysqli->query("SELECT uid FROM Candidates WHERE uid = '$user'");
  $onlist = $candidatequery->num_rows;
  $wasadded = false;
  $duplicate = false;
  if ($onlist == 0) {
    $wasadded = $mysqli->query("INSERT INTO Candidates (uid) VALUES ('$user')");
  } else {
    $duplicate = true;
  };
};
if ($doremove == "yes") {
  $wasremoved = $mysqli->query("DELETE FROM Candidates WHERE uid = '$user'");
};
?>
-->
<title>
  <?php echo $pagetitle; ?>
</title>
</head>

<body>
<h1>
  <?php echo $pageheading; ?>
</h1>

<?php # State change notification
if ($wasadded || $wasremoved || $duplicate) { ?>
  <p><strong> <?php
  if ($wasadded) echo $wasaddedstring;
  if ($wasremoved) echo $wasremovedstring;
  if ($duplicate) echo $duplicatestring; 
  ?>
  </strong></p>
<?php } ?>

<h2>Identification</h2>
<p>
<?php
  if ($hascert) {
    echo $identificationstringprefix; ?>
    <strong>
      <?php echo "$fullname";?> 
      (<?php echo "$email";?>)
    </strong>.
<?php } else {
  echo $noidentificationstring;
}; ?>
</p>


<?php if ($onacl) { ?>
<h2>Administration</h2>
  <p><?php echo $hasprivilegesstring; ?></p>
  <?php 
    echo $connectstring;
    $res = $mysqli->query("SELECT uid FROM Candidates ORDER BY uid ASC");
?>

  <h3>List of candidates</h3>
  <table>
    <thead><tr><td>MIT User ID</td></tr></thead>
    <tbody>
      <?php
        $res->data_seek(0);
        while ($row = $res->fetch_assoc()) {
          echo "<tr><td>" . $row['uid'] . "</td></tr>\n";
      }?>
    </tbody>
  </table>
<?php  } ; ?>

<h2>Nomination</h2>
<?php
  $res = $mysqli->query("SELECT uid FROM Candidates WHERE uid = '$user'");
  $onlist = $res->num_rows;
?>
<p><?php 
  if ($onlist == 0) { 
    echo $onliststring; 
  } else {
    echo $offliststring;
  } 
?></p>

<form 
  method="post" 
  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
  <?php
    if ($onlist == 0) {
      $addme = 'yes';
      $removeme = 'no';
    } else {
      $addme = 'no';
      $removeme = 'yes';
    } ?>
  <input 
    type="hidden" 
    name="addme" 
    value="<?php echo $addme; ?>" />
  <input 
    type="hidden" 
    name="removeme" 
    value="<?php echo $removeme; ?>" />
  <input 
    id="submit" 
    type="submit" 
    name="Toggle" 
    value="Submit" />
</form>
</body>
</html>
