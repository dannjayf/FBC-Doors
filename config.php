<?php
global $dows, $verbose;

$verbose = false;

$dows = array('Su','Mo','Tu','We','Th','Fr','Sa');

include 'config-local.php';

$_SESSION['loc'] = $building_id;

date_default_timezone_set('America/Chicago');

include 'functions.php';


$db_db = mysql_connect($db_host, $db_user, $db_pass) or die("Unable to connect to DB");

if ($db_db) {
    mysql_select_db($db_name, $db_db) or die("Unable to select $db_name");
    mysql_query("SET NAMES utf8", $db_db);
}

function sql_query($query) {
    global $db_db;

    $r = mysql_query($query, $db_db) or die(mysql_error($db_db)."\n$query");
    return $r;
}

$prefixes = [
'' => '',
'Mr.' => 'Mr.',
'Mrs.' => 'Mrs.',
'Ms.' => 'Ms.',
];

?>
