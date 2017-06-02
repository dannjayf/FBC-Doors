<?php

include 'config.php';
include 'config_hid.php';

// Check ACL
$query = "
SELECT id, loc
FROM token 
WHERE loc is not null
";

$r = sql_query($query);

while ($row = mysql_fetch_array($r)) {
    updTokenAsset($row['id'], $row['loc']);
}

$query = "
SELECT
    id,
    admin_user,
    admin_pass,
    ip_address
FROM asset
WHERE
    active = 1
    and loc = 11
    and ip_address = '192.168.100.214'
ORDER BY ip_address
";

$r = sql_query($query);

while ($row = mysql_fetch_array($r)) {


    $hid = $row['id'];
    $host = $row['ip_address'];
    $username = $row['admin_user'];
    $password = $row['admin_pass'];
    echo "\n\n$host\n";

    $query = "
    SELECT 
        a.token
    FROM token a, acl b
    WHERE (a.id = b.token_id and b.asset_id = $hid)
    ";

    $r2 = sql_query($query);

    while ($row2 = mysql_fetch_array($r2)) {
        echo $hid,' ',$host,' ',$row2['token'],"\n";

        $vars = hidAddCard($row2['token']);

        echo $vars[0]['tag']; echo "\n";
    }
}

function updTokenAsset($token_id, $loc) {
    $query = "
    SELECT id
    FROM asset
    WHERE loc = $loc
    ";

    $r = sql_query($query);
    $asset = array();
    while ($row = mysql_fetch_array($r)) {
        $asset[$row['id']] = 1;
    }
    updAssetACL($loc, $token_id, $asset);
}
// Update the ACL
function updAssetACL($loc, $tid, $asset) {

    $query = "
    SELECT 
        a.id,
        b.token_id
    FROM asset a
    LEFT OUTER JOIN acl b ON (b.asset_id = a.id and b.token_id = $tid)
    WHERE loc = $loc
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        $id = $row['id'];
        // Delete if no longer assigned to this asset
        if ($row['token_id'] == $tid and empty($asset[$id]))
            delACL($id, $tid);
        elseif (!empty($asset[$id]) and $row['token_id'] != $tid)
            addACL($id, $tid);
    }
}

