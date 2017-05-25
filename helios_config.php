<?php
include 'config.php';

$pgm = 'helios_config.php';
if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $tmenu = "<a href=$pgm?loc=new>Location</a>";



    if (!empty($_REQUEST['Create']) or !empty($_REQUEST['Save'])) {
        $id = $_REQUEST['id'];
        $info = get_helios_info($id);

        head(getLocation()."|Helios PBook for ".$info['name'], $tmenu);

        echo "<h2>Updated</h2>";

        echo "<a href='$pgm'>Continue</a>\n";
        echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
    }
    elseif (!empty($_REQUEST['id']) and empty($_REQUEST['Cancel'])) {
        $id = $_REQUEST['id'];

        // First get the info about the ATA
        $info = get_helios_info($id);
        $tmenu .= " | <a href='$pgm'>Select</a>";

        head(getLocation()."|Helios PBook for ".$info['name'], $tmenu);
        ob_flush(); flush();
        ?>
        <form action="<?php echo $pgm;?>" method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <table>
        <thead>
        <tr>
        <th>Entry</th>
        <th>Enabled</th>
        <th>Name</th>
        <th>Email</th>
        <th>Act</th>
        <th>De-Act</th>
        <th>Sw 1</th>
        <th>Sw 2</th>
        <th>RIFD</th>
        <th>Num 1</th>
        <th>Time 1</th>
        <th>Station 1</th>
        <th>Num 2</th>
        <th>Time 2</th>
        <th>Station 2</th>
        <th>Num 3</th>
        <th>Time 3</th>
        <th>Station 3</th>
        <th>Subst</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Retreive info from database for the number of ports
        $pbooks = get_helios_pbook($id);
        $i = 0;
        if (is_array($pbooks)) {
            foreach($pbooks as $pbook) {
                $name = empty($pbook['name']) ? '' : fix_quote($pbook['name']);
                $email = empty($pbook['email']) ? '' : fix_quote($pbook['email']);

                echo "<tr>\n",
                    "<td>$i<input type=\"hidden\" name=\"a[$i]\" value=\"{$pbook['entry_id']}\" /></td>\n",
                    "<td>".sel_list("en[$i]", $pbook['en'], array('No', 'Yes'))."</td>\n",
                    "<td><input type=\"text\" size=\"15\" name=\"name[$i]\" value=\"$name\" /></td>\n",
                    "<td><input type=\"text\" size=\"15\" name=\"email[$i]\" value=\"$email\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"ap[$i]\" value=\"{$pbook['ap']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"dp[$i]\" value=\"{$pbook['dp']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"l1p[$i]\" value=\"{$pbook['l1p']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"l2p[$i]\" value=\"{$pbook['l2p']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"cid[$i]\" value=\"{$pbook['cid']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"n1[$i]\" value=\"{$pbook['n1']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"c1[$i]\" value=\"{$pbook['c1']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"10\" name=\"s1[$i]\" value=\"{$pbook['s1']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"n2[$i]\" value=\"{$pbook['n2']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"c2[$i]\" value=\"{$pbook['c2']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"10\" name=\"s2[$i]\" value=\"{$pbook['s2']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"n3[$i]\" value=\"{$pbook['n3']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"c3[$i]\" value=\"{$pbook['c3']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"10\" name=\"s3[$i]\" value=\"{$pbook['s3']}\" /></td>\n",
                    "<td><input type=\"text\" size=\"5\" name=\"subst[$i]\" value=\"{$pbook['subst']}\" /></td>\n",
                    "</tr>\n";
                    $i++;
            }
        }
        for ($j = 0; $j < 5; $j++) {
            echo "<tr>\n",
                "<td><input type=\"text\" size=\"3\" name=\"a[$i]\" value=\"\" /></td>\n",
                "<td>".sel_list("en[$i]", '', array('No', 'Yes'))."</td>\n",
                "<td><input type=\"text\" size=\"15\" name=\"name[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"15\" name=\"email[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"ap[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"dp[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"l1p[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"l2p[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"cid[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"n1[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"c1[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"10\" name=\"s1[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"n2[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"c2[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"10\" name=\"s2[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"n3[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"c3[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"10\" name=\"s3[$i]\" value=\"\" /></td>\n",
                "<td><input type=\"text\" size=\"5\" name=\"subst[$i]\" value=\"\" /></td>\n",
                "</tr>\n";
            $i++;
        }
        ?>
        </table>
        <input type="submit" name="Save" value="Save" />
        <input type="submit" name="Cancel" value="Cancel" />
        <?php
        if (trim($info['model']) == 'Patton')
            echo "<input type='submit' name='Create' value='Create' />\n";
        ?>
        </form>
        <?php
    }
    else {
        head(getLocation()."|Helios PBook", $tmenu);
        $query = "
        SELECT
            a.id,
            a.name,
            a.model,
            a.mac_address,
            a.ip_address
        FROM helios a
        WHERE loc = $loc
        ORDER BY a.name
        ";

        $r = mysql_query($query) or die($query);

        ?>
        <table>
        <thead>
        <tr>
        <th>Name</th>
        <th>Model</th>
        <th>Mac Address</th>
        <th>IP Address</th>
        </th>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($r)) {

            echo "<tr>",
                "<td><a href='$pgm?id={$row['id']}'>{$row['name']}</a></td>",
                "<td align=center>{$row['model']}</td>",
                "<td align=center>{$row['mac_address']}</td>",
                "<td align=center>{$row['ip_address']}</td>",
                "</tr>\n";
        }
        echo "<tr><td><a href='$pgm?id=add'>Add</a></td></tr>\n";
        ?>
        </table>
        <?php
        
    }
}
?>
</body>
</html>
