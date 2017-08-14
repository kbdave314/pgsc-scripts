#!/usr/bin/php-cgi

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html version="-//W3C//DTD XHTML 1.1//EN"
      xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<link rel="stylesheet" href="../style/basestyle" />
<?php
$lprefix = "system:";
$acl = exec("fs la ./ | grep 'system' | head -n1 | cut -d' ' -f3 | cut -d':' -f2");
$accesslist = exec("grep '^[Require]' .htaccess | cut -d':' -f2");
$hascert = $_SERVER['SSL_CLIENT_S_DN_CN'];
if ($hascert) {
$fullname = $_SERVER['SSL_CLIENT_S_DN_CN'];
$email = $_SERVER['SSL_CLIENT_S_DN_Email'];
$user = strstr($email, '@', true);
$hasaccess = exec("blanche $accesslist -u -r -noauth | grep '$user'");
$onacl = @exec("blanche $acl -u -r -noauth | grep '$user'");
};
?>
<title>PGSC: Passwords and Keys</title>
</head>
<body>
<h1><a href="http://www.mit.edu/" title="MIT" class="mit-logo"> </a>PGSC: Passwords and Keys</h1>
<p style="clear:left">This page lists passwords and keys that need to be shared across multiple people in the Physics Graduate Student Council. (Really, they should never be shared, but that can't be helped.)</p>
<h2>Authentication</h2>
<?php
if ($hascert) {?>
<p class="good">You have been identified as <strong><?php echo "$fullname";?> (<?php echo "$email";?>)</strong>.</p>
<?php } else { ?>
<p class="bad">You have not been identified. No keys will be displayed.</p>
<?php }; ?>
<?php
if ($hascert) {?>
<h2>Key Tables</h2>
<?php
  if ($onacl) {?>
<h3>Locker Access Control</h3>
<table>
<thead>
<tr>
<td>Attribute</td>
<td>Value</td>
</tr>
</thead>
<tbody>
<?php
$row = 1;
if (($handle = fopen("./keylist.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        echo '<tr>';
        for ($c=0; $c < $num; $c++) {
            //echo $data[$c] . "<br />\n";
            if(empty($data[$c])) {
               $value = "&nbsp;";
            }else{
               $value = $data[$c];
            }
            echo '<td>'.$value.'</td>';
        }
        echo '</tr>';
        $row++;
    }
    fclose($handle);
}
?>
<tr>
<td>PGSC SQL user (physics-gsc) password</td>
<td><?php
echo passthru("./getsqlpassword.sh");
?></td>
</tr>
</tbody>
</table>
<?php } ; 
if ($hasaccess) { ?>
<h3>Tax-Exempt Payment Card Information</h3>
<table>
<thead>
<tr>
<td>Attribute</td>
<td>Value</td>
</tr>
</thead>
<tbody>
<?php
$row = 1;
if (($handle = fopen("./cclist.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        echo '<tr>';
        for ($c=0; $c < $num; $c++) {
            //echo $data[$c] . "<br />\n";
            if(empty($data[$c])) {
               $value = "&nbsp;";
            }else{
               $value = $data[$c];
            }
            echo '<td>'.$value.'</td>';
        }
        echo '</tr>';
        $row++;
    }
    fclose($handle);
}
?>
</tbody>
</table>
<?php };
 } ?>
<h2>About this page</h2>
<p>This is an XHTML 1.1 + PHP document. It was dynamically generated at <?php echo date(DATE_RFC2822);?>.
</p>
<pre class="in" title="What is this document?"><code>
<?php
$xdgmime = 'xdg-mime query filetype keys*';
echo "$xdgmime";
?>
</code></pre>
<pre class="out" title="It's PHP!"><code>
<?php
echo passthru($xdgmime);
?>
</code></pre>
<p>It is served by the scripts daemon for the <code>physics-gsc</code> locker.</p>
<pre class="in" title="Who am I?"><code>
<?php
$whoami = 'whoami';
echo "$whoami";
?>
</code></pre>
<pre class="out" title="I'm the PGSC scripts daemon!"><code>
<?php
echo passthru($whoami);
?>
</code></pre>
<p>This is a <strong>cleartext</strong> document stored in a directory for which only the group <code><?php echo $lprefix . $acl ;?></code> and administrative daemons have read and write permissions.</p>
<pre class="in" title="Checking permissions..."><code>
<?php
$la = 'fs la ./';
echo "$la";
?>
</code></pre>

<pre class="out" title="...and the resulting output."><code>
<?php
echo passthru($la);
?>
</code></pre>
<p>This document will only be served to clients with an MIT certificate demonstrating that they belong to the group <code><?php echo $lprefix . $accesslist ;?></code>.</p>
<pre class="in" title="Checking .htaccess..."><code>
<?php
$grep = 'grep "^[AuthType|Require]" .htaccess';
echo $grep;
?>
</code></pre>
<pre class="out" title="...and the resulting output."><code>
<?php
echo passthru($grep);
?>
</code></pre>
<p>Presently, the following users belong to <code><?php echo $lprefix . $accesslist ;?></code>.</p>
<pre class="in" title = "Who's allowed?"><code>
<?php
$blanche = "blanche -u -r -noauth $accesslist";
echo $blanche;
?>
</code></pre>
<pre class="out" title="We are."><code>
<?php
echo passthru($blanche);
?>
</code></pre>
<p>Since you are viewing this page, you must be on this list.</p>
</body>
</html>
