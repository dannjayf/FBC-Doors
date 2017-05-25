<?php

include 'config.php';
$pgm = 'waps.php';

head('Camera Models');

if (!empty($_REQUEST['Save'])) {
    $id = $_REQUEST['id'];
    $model = mysql_real_escape_string($_REQUEST['model']);
    $make = mysql_real_escape_string($_REQUEST['make']);

    if ($id == 'add') {
	$query = "
	INSERT INTO waps
	(model, 
        make
        )
	VALUES (
        '$model',
        '$make'
        ) ";
    }
    else {
	$query = "
	UPDATE waps
	SET
            model = '$model',
            make = '$make'
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
	FROM waps
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
    ?>
    </table>
    <input type="submit" name="Save" value="Save" />
    </form>
    <?php
}
else {
    $query = "
    SELECT *
    FROM waps
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

