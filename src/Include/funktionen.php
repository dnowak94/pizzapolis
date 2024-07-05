<?php
// Daten aus der Datenbank anzeigen
function db_result($result,$i,$field)
{
    $result->data_seek($i);
    $row=$result->fetch_assoc();
    return $row[$field];
}

// Daten in die Datenbank schreiben
function db_update($feld)
{
    return $GLOBALS['db']->real_escape_string($feld);
}

function query($query) {
    return $GLOBALS['db']->query($query);
}

// leere Felder suchen
function leer($var)
{
    return ($var!='');
}

//Funktion zum genieren des Passworts
function generatepw($length)
{
    $result='';
    for($i=0;$i<$length;$i++)
    {
        //$lun : lowcase or upcase or number
        $lun=mt_rand(1,3);
        switch($lun)
        {
            case 1: $result .= chr(mt_rand(ord('a'),ord('z')));
                break;
            case 2: $result .= chr(mt_rand(ord('A'),ord('Z')));
                break;
            case 3: $result .= chr(mt_rand(ord('0'),ord('9')));
                break;
        }
    }
    return $result;
}

function write_ini_file($filename,$konstante,$wert)
{
    $found=false;
    $file=fopen($filename,'r+');
    while((!$found)&&(!feof($file)))
    {
        $line=fgets($file); // Zeile laden
        if(substr($line,0,strpos($line,'='))==$konstante) // Zeile = konstante
        {
            $found=true; // Zeile gefunden
            fseek($file,ftell($file)-strlen(substr($line,strpos($line,'=')+1))); // Schreibposition nach "=" setzen
            fwrite($file,"$wert"."\r"); // Wert schreiben
        }
    }
    fclose($file);
    return $found;
}

function error_check($inputs)
{
    $errors=array();
    for($i=0;$i<count($inputs);$i++) $errors[]='';

    //check for errors
    $i=0;
    foreach($_POST as $feld=>$value)
    {
        if(($i<count($inputs))&&($feld!='id')&&($feld!='kategorie'))
        {
            // check if inputs are empty
            $label=$inputs[$i]['label'];
            if(($value=='')&&(strpos($label,'*')>0)&&($feld!='DATA_captcha'))
            {
                if($feld=='DATA_email2')
                    $errors[$i]='Bitte bestätigen Sie Ihre E-Mail Adresse!';
                else $errors[$i]='Bitte '.substr($label,0,strlen($label)-1).' eingeben!';
            }

            // Username exists
            if(($feld=='DATA_username')&&(username_check($_POST['DATA_username'])!=''))
                $errors[$i]=username_check($_POST['DATA_username']);

            // E-Mail check
            if($feld=='DATA_email1')
            {
                // Gültigkeitscheck source : http://stackoverflow.com/questions/13404441/preg-match-for-email-checking
                if(preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',$value))
                {
                    // E-Mail exists check
                    $query="SELECT id_benutzer,`dtE-Mail` FROM tblBenutzer WHERE `dtE-Mail`='".$value."'".
                        (isset($_SESSION['id_user'])?' AND id_benutzer<>'.$_SESSION['id_user']:'');
                    $result=query($query,$GLOBALS['db']);
                    if(mysqli_num_rows($result)==1)
                        $errors[$i]='Die eingebene E-Mail existiert bereits.';
                }
                else
                    $errors[$i]='E-Mail ist ungültig!';
            }

            //E-Mail1 <> E-Mail2
            if(($feld=='DATA_email1')&&($_POST['DATA_email1']!=$_POST['DATA_email2']))
            {
                $errors[$i]='E-Mails stimmen nicht überein!';
                $errors[$i+1]='!';
            }


            //Bei Registrierungsformular
            if((isset($_GET['page']))&&($_GET['page']=='register'))
            {
                // Password confirm check
                if($feld=='DATA_password1')
                {
                    //check if Passwords match
                    if($_POST['DATA_password1']!=$_POST['DATA_password2'])
                    {
                        $errors[$i]='Passwörter stimmen nicht überein!';
                        $errors[$i+1]='!';
                    }
                    if($_POST['DATA_password2']=='')
                        $errors[$i]='Bitte bestätigen Sie ihr Passwort.';
                }

                // CAPTCHA überprüfen
                if(($feld=='DATA_captcha')&&(strtolower($_POST['DATA_captcha'])!=$_SESSION['captcha']))
                    $errors[$i]='CAPTCHA ist nicht korrekt!';
            }
        }
        $i++;
    }
    return $errors;
}

function username_check($username)
{
    $errormsg='';
    // dtUsername nicht leer
    $username=trim($username);
    if($username!='')
    {
        //check if username exists
        $username=$db->real_escape_string($username);
        $query= "SELECT dtUsername FROM tblBenutzer
                WHERE dtUsername='".$username."'";
        if((isset($_SESSION['id_user']))||(isset($_SESSION['restaurant'])))
            $query.=" AND id_benutzer<>".(isset($_SESSION['id_user'])?$_SESSION['id_user']:$_SESSION['restaurant']);
        $result=query($query,$GLOBALS['db']);
        if(mysqli_num_rows($result)>0)
        {
            $errormsg='Der Benutzername "'.$_POST['DATA_username'].'" existiert bereits!';
        }
    }
    else $errormsg='Bitte Username eingeben!';
    return $errormsg;
}

function check_preis($preis)
{
    $result=array('','');
    // gültiges Preis-Format
    if($preis!='')
    {
        // 0 hinter komma hinzufügen
        if(strpos($preis,',')>0)
        {
            $dec=strlen(substr($preis,strpos($preis,',')+1));
            if($dec<2) $preis.=str_repeat('0',2-$dec);
        }
        else $preis.=',00';

        if(!preg_match('/^[0-9]+,[0-9]{2,}$/',$preis))
            $result[0]='ungültiges Preis-Format; gültig: 0,00';
        else $result[1]=str_replace(',','.',$preis);
    }
    else $result[0]='Bitte Preis eingeben.';
    return $result;
}

function valid_id($tabelle,$id_feld,$id)
{
    $valid=false;
    $id=$GLOBALS['db']->real_escape_string($id);
    if(is_numeric($id))
    {
        $query= "SELECT ".$id_feld." FROM ".$tabelle.
                " WHERE ".$id_feld."=".$id;
        $result=$GLOBALS['db']->query($query);
        if(mysqli_num_rows($result)==1) $valid=true;
    }
    return $valid;
}

function success($message)
{
    ?>
    <div class="success" id="dialog">
        <span class="useroutput"><?=$message?></span>
    </div>
   <script>success()</script>
<?php
}

function timetofloat($zeit)
{
    $stunde=intval(substr($zeit,0,2));
    $minute=intval(substr($zeit,strpos($zeit,':')+1,2));
    // minute in stunden
    if($minute>0) $minute=($minute / 60);
    $result=$stunde+$minute;
    return $result;
}

function opened_day($tag)
{
    $query="SELECT * FROM tblLieferzeit";
    $result=query($query,$GLOBALS['db']);
    $wochentage=array(
        'Mon'=>1,
        'Tue'=>2,
        'Wed'=>3,
        'Thu'=>4,
        'Fri'=>5,
        'Sat'=>6,
        'Sun'=>7);

    $temp=date('N',strtotime($tag));
    $found=array('','');
    for($i=0;$i<mysqli_num_rows($result);$i++)
    {
        $tag_von=$wochentage[db_result($result,$i,'dtTag_von')];
        $tag_bis=$wochentage[db_result($result,$i,'dtTag_bis')];
        $zeit_von=db_result($result,$i,'dtZeit_von');
        $zeit_bis=db_result($result,$i,'dtZeit_bis');
        if(($temp>=$tag_von)&&($temp<=$tag_bis))
        {
            if($tag===date('d.m.Y'))
            {
                if((timetofloat(roundup_aktzeit())>=timetofloat($zeit_von))&&
                    (timetofloat(roundup_aktzeit())<=timetofloat($zeit_bis)))
                {
                    $found[0]=$zeit_von;
                    $found[1]=$zeit_bis;
                }

            }
            else
            {
                $found[0]=$zeit_von;
                $found[1]=$zeit_bis;
            }
        }
    }
    return $found;
}

function roundup_aktzeit()
{
    $h=intval(date('H'));
    $m=intval(date('i'));

    // minute < 30 -> minute + 30 - minute + 30
    if($m<30)
    {
        $m = $m + (30 - $m) + 30;
    }
    else
    {
        // minute > 50 -> minute=30
        $m=30;
        $h++;
    }
    if($m==60)
    {
        $m=0;
        $h++;
    }
    $result=($h<10?'0'.$h:$h).':'.($m<10?'0'.$m:$m);
    return $result;
}

function resize_image($imagename)
{
    $uploaddir = dirname($_SERVER['SCRIPT_FILENAME']).'/Pictures/gerichte/';
    $uploadfile = $uploaddir . basename($imagename);
    $outfile = $uploadfile;
    $success=false;

    if((move_uploaded_file($_FILES['DATA_foto']['tmp_name'],$uploadfile))
        &&((exif_imagetype($uploadfile)==IMAGETYPE_JPEG)
            ||(exif_imagetype($uploadfile)==IMAGETYPE_PNG)
            ||(exif_imagetype($uploadfile)==IMAGETYPE_GIF)))
    {
        // get original image
        switch(exif_imagetype($uploadfile))
        {
            case IMAGETYPE_JPEG: $image=imagecreatefromjpeg($uploadfile);
                break;
            case IMAGETYPE_PNG: $image=imagecreatefromjpeg($uploadfile);
                break;
            case IMAGETYPE_GIF: $image=imagecreatefromgif($uploadfile);
                break;
        }

        // Get image width and height
        list($width, $height) = getimagesize($uploadfile);
        $side=125;

        // create new image
        $newimage = imagecreatetruecolor($side, $side);
        imagecopyresampled($newimage, $image, 0, 0, 0, 0, $side,
            $side, $width, $height);

        switch(exif_imagetype($uploadfile))
        {
            case IMAGETYPE_JPEG: imagejpeg($newimage,$outfile,100);
                break;
            case IMAGETYPE_PNG: imagepng($newimage,$outfile,100);
                break;
            case IMAGETYPE_GIF: imagegif($newimage,$outfile,100);
                break;
        }
        $success=true;
    }

    return $success;
}

function send_mail($to,$fullname,$subject,$message,$attachment='')
{
    // source: https://github.com/PHPMailer/PHPMailer
    require('PHPMailer/PHPMailerAutoload.php');

    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';

    // SMTP Server
    $mail->isSMTP();
    $mail->Host='127.0.0.1';

    // From
    $mail->SetFrom('nowdo257@school.lu','pizzapolis.lu');
    // To
    $mail->addAddress($to,$fullname);

    // Attachment
    if($attachment!='') $mail->addAttachment($attachment);
    $mail->isHTML(true);

    // Subject, Message
    $mail->Subject=$subject;
    $mail->Body=$message;
    $message=str_replace('<p>','',$message);
    $message=str_replace('</p>',"\n\r",$message);
    $mail->AltBody=$message;
    $mail->setLanguage('de','PHPMailer/language/phpmailer.lang-de.php');

    $result=array($mail->send(),'Mailer Error: '.$mail->ErrorInfo);
    return $result;
}
?>