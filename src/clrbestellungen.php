<?php
// TODO delete clrbestellungen.php
session_start();
if((isset($_SESSION['id_user']))&&($_SESSION['id_user']==1))
{
    if($_SERVER['SERVER_NAME']=='foxi.ltam.lu')
    {
        $db=mysql_connect('127.0.0.1','nowdo257','mnidn257zp94');
        mysql_select_db('nowdo257',$db);
    }
    else
    {
        $db=mysql_connect('127.0.0.1','dominik','mnidn257zp94');
        mysql_select_db('dominik',$db);
    }

$query="SELECT id_bestellung FROM bestellungen";
$result=mysqli_query($db,$query);

$i=0;
while($i<mysqli_num_rows($result))
{
    $qdel="DELETE FROM tblBestehen_aus WHERE fi_bestellung=".mysql_result($result,$i,'id_bestellung');
    mysqli_query($db,$qdel);
    $qdel="DELETE FROM bestellungen WHERE id_bestellung=".mysql_result($result,$i,'id_bestellung');
    mysqli_query($db,$qdel);
    $i++;
}

$query="SELECT id_rechnung FROM rechnungen";
$result=mysqli_query($db,$query);
$i=0;
while($i<mysqli_num_rows($result))
{
    $qdel="DELETE FROM rechnungen WHERE id_rechnung=".mysql_result($result,$i,'id_rechnung');
    mysqli_query($qdel,$db);
    $i++;
}

// AUto increment
$query="ALTER TABLE bestellungen AUTO_INCREMENT=1";
mysqli_query($db,$query);
$query="ALTER TABLE rechnungen AUTO_INCREMENT=1";
mysqli_query($db,$query);
}

foreach(glob('Rechnungen/*') as $file) unlink($file);
?>