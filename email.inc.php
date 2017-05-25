<?php
require_once 'class.phpmailer.php';
require_once 'class.smtp.php';
define ('EHOST', 'dedrelay.secureserver.net:25');
define ('EFROM', 'noreply@myurbansky.com');
define ('ENAME', 'myUrbanSky');
define ('EUSER', 'noreply@myurbansky.com');
define ('EPASS', 'yV7n4J6JD3Jr');

function emailNotify($data) {
    $loc_id = array_key_exists('loc_id', $data) ? $data['loc_id'] : 0;
    $query = "
    SELECT *
    FROM notify
    WHERE loc_id = 0 or loc_id = $loc_id
    ORDER BY loc_id DESC
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_assoc($r)) {
        if ($row['loc_id'] == $loc_id or $row['loc_id'] == 0) 
            break;
    }
    // Change time format
    $data['timestamp'] = date('m/d/Y g:i a', strtotime($data['timestamp']));
    $data['date'] = date('m/d/Y', strtotime($data['timestamp']));
    $data['time'] = date('g:i a', strtotime($data['timestamp']));

    // Merge fields
    $subject = mergeMessage($row['subject'], $data);
    $body = mergeMessage($row['body'], $data);
    $to = $data['email'];

    // dd($body);

    emailSend($to, $subject, $body);

}

function smsNotify($data) {
    $loc_id = array_key_exists('loc_id', $data) ? $data['loc_id'] : 0;
    $query = "
    SELECT *
    FROM notify
    WHERE loc_id = 0 or loc_id = $loc_id
    ORDER BY loc_id DESC
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_assoc($r)) {
        if ($row['loc_id'] == $loc_id or $row['loc_id'] == 0) 
            break;
    }
    // Change time format
    $data['timestamp'] = date('m/d/Y g:i a', strtotime($data['timestamp']));
    $data['date'] = date('m/d/Y', strtotime($data['timestamp']));
    $data['time'] = date('g:i a', strtotime($data['timestamp']));

    // Merge fields
    $body = mergeMessage($row['sms'], $data);
    $to = $data['sms'].'@'.getSMS($data['carrier']);

    // dd($body);
    emailSend($to, '', $body);

}

function emailSend($to, $subject, $body) {
    $mail = new PHPMailer;

    $mail->isSMTP();
    $mail->Host = EHOST;
    $mail->From = EFROM;
    $mail->FromName = ENAME;
    // $mail->SMTPAuth = true;
    // $mail->Username = EUSER;
    // $mail->Password = EPASS;
    // $mail->SMTPSecure = 'ssl';   

    $mail->Subject = $subject;
    $mail->addAddress($to);
    $mail->Body = $body;
    if (!$mail->send()) {
        dd("Mail could not be sent to $to.\nMailer Error: ".print_r($mail->ErrorInfo,true));
    }
    dd("Email sent to $to");

}

/*
 * Returns the message with the merged field
 * The delimiters are { }
 */
function mergeMessage($text, $key, $value = '') {
    if (is_array($key)) {
        foreach ($key as $k => $value) {
            $d = explode('{'.$k.'}', $text);
            for ($i = 1; $i < count($d); $i++) {
                $d[$i] = $d[$i-1].$value.$d[$i];
            }
            $i--;
            $text = $d[$i];
        }
    }
    else {
        $d = explode('{'.$key.'}', $text);
        for ($i = 1; $i < count($d); $i++) {
            $d[$i] = $d[$i-1].$value.$d[$i];
        }
        $i--;
        $text = $d[$i];
    }

    return $text;
}

?>
