<?php

// Updates the data table with info about registration status of phones

include 'config.php';

$nums  = $_POST['num'];
$ip   = $_POST['ip'];
$stat = $_POST['stat'];

error_log(date('Y-m-d H:i:s ').count($nums)." records received.\n", 3, '/tmp/trixbox.upd');


foreach ($nums as $i => $num) {
    $query = "
    SELECT
        data.id,
        ata.location
    FROM data
    LEFT OUTER JOIN ata ON (data.ata_id = ata.id)
    WHERE data.ext = '$num'
    ";

    $r = sql_query($query);

    $row = mysql_fetch_row($r);
    $ts = strftime('%Y-%m-%d %H:%M:%S');
    if ($row[0]) {
        $id = $row[0];
        $query = "
        UPDATE data
        SET ip = '$ip[$i]', stat='$stat[$i]', updts = '$ts'
        WHERE id = $id
        ";

        sql_query($query);
        if (strpos($stat[$i], 'Ok'))
            $loc[$row[1]]++;
    }
    $query = "
    INSERT INTO ata_log
    (datetime, ext, stat, ip)
    VALUES ('$ts', '$num', '$stat[$i]', '$ip[$i]')
    ";

    sql_query($query);

}
error_log(date('Y-m-d i:h:s ').print_r($loc,true)."\n", 3, '/tmp/loc.log');

echo count($nums).": Ok\n";

?>
