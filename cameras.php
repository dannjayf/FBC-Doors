<?php

include 'config.php';
$pgm = 'cameras.php';

head('Camera Models');

if (!empty($_REQUEST['Save'])) {
    $id = $_REQUEST['id'];
    $model = mysql_real_escape_string($_REQUEST['model']);
    $make = mysql_real_escape_string($_REQUEST['make']);
    $snapshot = mysql_real_escape_string($_REQUEST['snapshot']);
    $mjpeg = mysql_real_escape_string($_REQUEST['mjpeg']);
    $width = $_REQUEST['width'] == '' ? 'null' : $_REQUEST['width'];
    $height = $_REQUEST['height'] == '' ? 'null' : $_REQUEST['height'];
    $active = $_REQUEST['active'] == 1 ? 1 : 0;

    if ($id == 'add') {
	$query = "
	INSERT INTO cameras
	(model, 
        make,
        snapshot,
        mjpeg,
        width,
        height
        )
	VALUES (
        '$model',
        '$make',
        '$snapshot',
        '$mjpeg',
        $width,
        $height
        ) ";
    }
    else {
	$query = "
	UPDATE cameras
	SET
            model = '$model',
            make = '$make',
            snapshot = '$snapshot',
            mjpeg = '$mjpeg',
            width = $width,
            height = $height
	WHERE
	    id = $id
	";
    }
    $r = sql_query($query);

    echo "<h2>Updated</h2>";
    echo "<a href='$pgm'>Continue</a>\n";
    echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
    
}
elseif(!empty($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    if ($id != 'add') {
	$query = "
	SELECT *
	FROM cameras
	WHERE id = $id
	";

	$r = sql_query($query);
	$row = mysql_fetch_array($r);
    }
    // Setup defaults

    ?>
    <form action="" method="POST">
    <input type="hidden" name="id" value = "<?php echo $id;?>" />
    <table>
    <?php
    editrow('Model', 'model', $row['model'], 20);
    editrow('Make', 'make', $row['make'], 20);
    editrow('Snapshot', 'snapshot', $row['snapshot'], 60);
    editrow('MJpeg', 'mjpeg', $row['mjpeg'], 60);
    editrow('Width', 'width', $row['width'], 4);
    editrow('Height', 'height', $row['height'], 4);
    ?>
    </table>
    <input type="submit" name="Save" value="Save" />
    </form>
    <?php
}
else {
    $query = "
    SELECT *
    FROM cameras
    ORDER BY model
    ";

    $r = sql_query($query);
    // echo "<pre>",print_r($r),"</pre>";

    ?>
    <table>
    <thead>
    <tr>
    <th>Model</th>
    <th>Make</th>
    </th>
    </thead>
    <tbody>
    <?php
    while ($row = mysql_fetch_array($r)) {
	echo "<tr>",
	    "<td><a href='$pgm?id={$row['id']}'>{$row['model']}</a></td>",
	    "<td>{$row['make']}</td>",
	    "</tr>\n";
    }
    echo "<tr><td><a href='$pgm?id=add'>Add</a></td></tr>\n";
    ?>
    </table>
    <?php
    
}
?>

