<?php
require "/var/web/requestor.ualadys.com/xmpphp/XMPPHP	/XMPP.php";
$conn = new XMPPHP_XMPP('talk.google.com', 5222, 'railroad.man2k', 'ggg', 'xmpphp', 'gmail.com', $printlog=True);
$conn->connect();
<h1>ffwef</h1>
while(1==1) {
    $payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start'));
    foreach($payloads as $event) {
        $pl = $event[1];
        switch($event[0]) {
            case 'message':
                echo "---------------------------------------------------------------------------------<br>";
                echo "Message from: {$pl['from']}\n";
                if($pl['subject']) print "Subject: {$pl['subject']}\n";
                print $pl['body'] . "\n";
                print "---------------------------------------------------------------------------------<br>";
                $conn->message($pl['from'], $body="Thanks for sending me \"{$pl['body']}\".", $type=$pl['type']);
                if($pl['body'] == 'quit') $conn->disconnect();
                if($pl['body'] == 'break') $conn->send("</end>");
            break;
            case 'presence':
                print "Presence: {$pl['from']} [{$pl['show']}] {$pl['status']}\n";
            break;
            case 'session_start':
                $conn->presence($status="Cheese!");
            break;
        }
    }
}
?>
