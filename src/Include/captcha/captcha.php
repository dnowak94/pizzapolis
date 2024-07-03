<?php
// source : http://www.stoppt-den-spam.info/webmaster/captcha-tutorial/zahlen-cpatcha/captcha-php-script.html
session_start();
unset($_SESSION['captcha']);

// zufälligen Text für das Bild generieren
function gencaptchatxt($len) {
    function make_seed(){
        list($usec , $sec) = explode (' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
    }
    srand(make_seed());

    //Der String $possible enthält alle Zeichen, die verwendet werden sollen
    $possible="ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
    $str="";
    while(strlen($str)<$len) {
        $str.=substr($possible,(rand()%(strlen($possible))),1);
    }
    return($str);
}

$text = gencaptchatxt(5);  //Die Zahl bestimmt die Anzahl der Stellen
$_SESSION['captcha'] = strtolower($text);

header('Content-type: image/png');
$img = ImageCreateFromPNG('captcha.PNG'); // Hintergrundbild
$color = ImageColorAllocate($img, 0, 0, 0); //Farbe
$ttf = str_replace('captcha.php','',$_SERVER['SCRIPT_FILENAME']).'XFILES.TTF'; //Schriftart
$ttfsize = 25; //Schriftgrösse
$angle = rand(0,5); // Winkel der Buchstaben
$t_x = rand(5,30); // x Abstand
$t_y = 35;	//y Abstand
imagettftext($img, $ttfsize, $angle, $t_x, $t_y, $color, $ttf, $text); // TTF-Text im Bild erzeugen
imagepng($img); // neues PNG-Bild erzeugen
imagedestroy($img); // vom Bild eingenommenen Speicher wieder freigeben
?> 