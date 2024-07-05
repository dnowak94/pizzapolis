<?php
//Registrierung
// Verarbeitung
$showForm=true;
// array mit Eingabefeldern für die Registrierung
$inputs=array(	array('label'=>'Vorname*','name'=>'DATA_vorname','type'=>'text'),
                    array('label'=>'Nachname*','name'=>'DATA_nachname','type'=>'text'),
                    array('label'=>'Username*','name'=>'DATA_username','type'=>'text'),
                    array('label'=>'Passwort*','name'=>'DATA_password1','type'=>'password'),
                    array('label'=>'Passwort bestätigen*','name'=>'DATA_password2','type'=>'password'),
                    array('label'=>'Adresse*','name'=>'DATA_adresse','type'=>'text'),
                    array('label'=>'Postleitzahl*','name'=>'DATA_postleitzahl','type'=>'text'),
                    array('label'=>'Ortschaft*','name'=>'DATA_ortschaft','type'=>'text'),
                    array('label'=>'Telefonnummer','name'=>'DATA_telefonnummer','type'=>'text'),
                    array('label'=>'E-Mail*','name'=>'DATA_email1','type'=>'email'),
                    array('label'=>'E-Mail bestätigen*','name'=>'DATA_email2','type'=>'email'),
                    array('label'=>'<img src="Include/captcha/captcha.php" alt="CAPTCHA">*',
                        'name'=>'DATA_captcha','type'=>'text'));

// Registrierungsformular abgesendet
if(isset($_POST['button_send']))
{
    $errors=error_check($inputs);

    if(count(array_filter($errors,"leer"))==0)
    {
        $activationcode=sha1($_POST['DATA_email1']);

        $query="INSERT INTO tblBenutzer (dtVorname,dtNachname,dtUsername,dtPasswort,dtTelefonnummer,`dtE-Mail`,
                dtAktivierungscode) ".
                "VALUES ('".
                db_update($_POST['DATA_vorname'])."','".
                db_update($_POST['DATA_nachname'])."','".
                db_update($_POST['DATA_username'])."',".
                "SHA1('".$_POST['DATA_password1']."'),'".
                db_update($_POST['DATA_telefonnummer'])."','".
                db_update($_POST['DATA_email1'])."','".
                db_update($activationcode)."')";
        mysqli_query($db,$query);

        //get last id
        $query="SELECT MAX(id_benutzer) AS 'id_benutzer' FROM tblBenutzer";
        $result=mysqli_query($db,$query);

        $id=db_result($result,0,'id_benutzer');

        // Adresse in Tabelle 'Adressen' speichern
        $query= "INSERT INTO tblAdresse (dtVorname,dtNachname,dtAdresse,dtPostleitzahl,dtOrtschaft,dtStandard,fi_kunde)
                VALUES('".
                db_update($_POST['DATA_vorname'])."','".
                db_update($_POST['DATA_nachname'])."','".
                db_update($_POST['DATA_adresse'])."','".
                db_update($_POST['DATA_postleitzahl'])."','".
                db_update($_POST['DATA_ortschaft'])."',".
                "1,".
                $id.")";
        mysqli_query($db,$query);

        $to=$db->real_escape_string($_POST['DATA_email1']);
        $fullname=  $db->real_escape_string($_POST['DATA_vorname']).' '.
                    $db->real_escape_string($_POST['DATA_nachname']);
        $subject='pizzapolis.lu - Aktivierung Ihres Kontos';
        $message='<p>Willkommen bei pizzapolis.lu!</p><p>Vielen Dank für Ihre Registrierung. Um Ihr Konto zu '.
            'aktivieren <a href="https://'.$_SERVER['SERVER_NAME'].
            $_SERVER['SCRIPT_NAME'].'?page=register&amp;id='.$id.'&amp;activationcode='.$activationcode.'">'.
            'Klicken Sie hier!</a></p>';
        send_mail($to,$fullname,$subject,$message);
        success('Eine E-Mail mit einem Aktivierungslink wurde an Ihre E-Mail Adresse "'.$to.'" gesendet.');
        $showForm=false;
    }
}

//Aktivierung
if((isset($_GET['id']))&&(isset($_GET['activationcode']))&&(valid_id('tblBenutzer','id_benutzer',$_GET['id'])))
{
    $showForm=false;
    $query= "SELECT dtAktivierungscode FROM tblBenutzer WHERE id_benutzer=".$db->real_escape_string($_GET['id']).
            " AND dtAktivierungscode='".$db->real_escape_string($_GET['activationcode'])."'";
    $result=mysqli_query($db,$query);
    if(mysqli_num_rows($result)==1)
    {
        $query="UPDATE tblBenutzer SET istAktiviert=1,dtAktivierungscode=NULL WHERE id_benutzer=".
            $db->real_escape_string($_GET['id']);
        mysqli_query($db,$query);
        success('Ihr Konto wurde erfolgreich aktiviert, sie können sich nun einloggen.');
    }
}

// Ausgabe
if($showForm)
{
?>
    <!--Registrierung-->
    <div class="register">
        <h1>Registrierung</h1>
        <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=register'?>" method="post">
            <table>
                <?php
                    $e=0;
                    foreach($inputs as $feld => $properties)
                    {
                        echo '<tr>'."\n";
                        echo '<th class="vertikal">'.$properties['label'].'</th>'."\n";
                        echo '<td><input type="'.$properties['type'].'" class="'.(((isset($errors[$e]))&&
                            ($errors[$e]!=''))?'redborder':'data').'" name="'.$properties['name'].'"'.
                            (((isset($_POST[$properties['name']]))&&($_POST[$properties['name']]!='')&&
                            ($properties['name']!='DATA_captcha'))?' value="'.
                            $_POST[$properties['name']].'"':'').'></td>'."\n";
                        if((isset($errors))&&(count(array_filter($errors,"leer"))>0))
                            echo '<td><span class="redtxt">'.($errors[$e]!='!'?$errors[$e]:'').'</span></td>'."\n";
                        echo '</tr>'."\n";
                        $e++;
                    }
                ?>
            </table>
            <div class="btn_center">
                <input type="submit" value="Registrieren" name="button_send">
            </div>
        </form>
    </div>
<?php
}
?>