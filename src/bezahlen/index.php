<?php
header('Content-type: text/html; charset=utf-8');

// include der Konfigurationsdatei
include('../Config/config.php');

//include der Funktionen
include('../Include/funktionen.php');

if(isset($_GET['page'])) $page=$_GET['page'];
else $page='';

if($page=='zahlungsart') include('zahlungsart_auth.php');


// Ausgabe

// Debug
//echo '<pre>'.print_r($_SESSION,true).'</pre>';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>pizzapolis.lu - Bestellung abschliessen</title>
    <link rel="stylesheet" type="text/css" href="default.css">
	<script src="../Include/JavaScripts/javascript.js"></script>
</head>
<body>
    <?php
    switch($page)
    {
        case 'lieferadresse':
            include('lieferadresse.php');
            break;
        case 'zahlungsart':
            include('zahlungsart.php');
            break;
        case 'uebersicht':
            include('uebersicht.php');
            break;
        case 'bestaetigung':
            include('bestaetigung.php');
            break;
        default:
            include('lieferadresse.php');
            break;
    }
    ?>
</body>

</html>