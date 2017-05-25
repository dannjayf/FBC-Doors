<?php

include 'config.php';
$pgm = 'powers.php';

head('IP Power Models');

if (!empty($_REQUEST['Save'])) {
    $id = $_REQUEST['id'];
    $make = mysql_real_escape_string($_REQUEST['make']);
    $model = mysql_real_escape_string($_REQUEST['model']);
    $ports = $_REQUEST['ports'] ? $_REQUEST['ports'] : 'null';

    if ($id == 'add') {
	$query = "
	INSERT INTO powers
	(make,
        model, 
        ports
        )
	VALUES (
        '$make',
        '$model',
        $ports
        ) ";
    }
    else {
	$query = "
	UPDATE powers
	SET
            make = '$make',
            model = '$model',
            ports = $ports
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
	FROM powers
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
    editrow('Make', 'make', $row['make'], 20);
    editrow('Model', 'model', $row['model'], 20);
    editrow('Circuits', 'ports', $row['ports'], 4);
    ?>
    </table>
    <input type="submit" name="Save" value="Save" />
    </form>
    <?php
}
else {
    $query = "
    SELECT *
    FROM powers
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
    <th>Ports</th>
    </th>
    </thead>
    <tbody>
    <?php
    while ($row = mysql_fetch_array($r)) {
	echo "<tr>",
	    "<td><a href='$pgm?id={$row['id']}'>{$row['model']}</a></td>",
	    "<td>{$row['make']}</td>",
	    "<td>{$row['ports']}</td>",
	    "</tr>\n";
    }
    echo "<tr><td><a href='$pgm?id=add'>Add</a></td></tr>\n";
    ?>
    </table>
    <?php
    
}
?>

