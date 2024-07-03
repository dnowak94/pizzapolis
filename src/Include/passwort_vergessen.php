<?php
// Eingabe
if(!isset($generated_pw)) $generated_pw='';
if(!isset($errormsg)) $errormsg='';
if(!isset($code)) $code='';
if(!isset($showFormPassword)) $showFormPassword=true;
if(!isset($errors)) $errors=array('','','','');
if(!isset($valid)) $valid=false;
// Verarbeitung

// Passwort vergessen
if((isset($_GET['page']))&&($_GET['page']=='pwvergessen'))
{
    if(isset($_POST['button_send']))
    {
        $query="SELECT id_benutzer,`dtE-Mail` FROM tblBenutzer WHERE `dtE-Mail`='".
            mysql_real_escape_string($_POST['DATA_email'])."'";
        $result=mysqli_query($db,$query);
        if(mysqli_num_rows($result)==1)
        {
            $code=sha1(generatepw(10));
            $query="UPDATE tblBenutzer
                    SET dtAktivierungscode='".$code."'
                    WHERE id_benutzer=".db_result($result,0,'id_benutzer');
            mysqli_query($db,$query);
            $to=mysql_real_escape_string($_POST['DATA_email']);
            $subject='pizzapolis.lu - Passwort vergessen';
            $message='<p>Sie können ihr Passwort mit folgendem link zurücksetzen:</p>'.'<p><a href="'.
                (isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].
                '?page=resetpw&amp;id='.db_result($result,0,'id_benutzer').'&amp;1timecode='.$code.'">'.
                (isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].
                '?page=resetpw&id='.db_result($result,0,'id_benutzer').'&amp;1timecode='.$code.'</a></p>';
            send_mail($to,'',$subject,$message);
        }
        else $errormsg='E-Mail nicht registriert.';
    }
}

//Passwort zurücksetzen
if((isset($_GET['page']))&&($_GET['page']=='resetpw')&&(isset($_GET['1timecode']))&&(isset($_GET['id']))&&
valid_id('tblBenutzer','id_benutzer',$_GET['id']))
{
    $query="SELECT id_benutzer FROM tblBenutzer
            WHERE dtAktivierungscode='".$_GET['1timecode']."'
            AND id_benutzer=".mysql_real_escape_string($_GET['id']);
    $result=mysqli_query($db,$query);
    if(mysqli_num_rows($result)>0)
    {
        $valid=true;
        $query="UPDATE tblBenutzer
                SET dtAktivierungscode=NULL
                WHERE id_benutzer=".mysql_real_escape_string($_GET['id']);
        mysqli_query($db,$query);
        //Passwort generieren
        if(isset($_POST['btngenerate']))
        {
            if(is_numeric($_POST['DATA_genpwlength']))
                $generated_pw=generatepw($_POST['DATA_genpwlength']);
            else $errors[count($errors)-1]='Länge nicht numerisch!';
            $showFormPassword=true;
        }
        if(isset($_POST['button_send']))
        {
            // Passwörter nicht leer und neues Passwort setzen
            if(($_POST['DATA_password1']!='')&&($_POST['DATA_password2']!=''))
            {
                //Passwort übereinstimmunng
                if($_POST['DATA_password1']==$_POST['DATA_password2'])
                {
                    $query="UPDATE tblBenutzer
                            SET dtPasswort=SHA1('".mysql_real_escape_string($_POST['DATA_password1'])."')
                            WHERE id_benutzer=".$_SESSION['id_user'];
                    mysqli_query($db,$query);
                }
                else $errors[1]='Passwörter stimmen nicht überein!';
            }
            else
            {
                if($_POST['DATA_password1']=='') $errors[1]='Bitte neues Passwort eingeben!';
                if($_POST['DATA_password2']=='') $errors[2]='Bitte Passwort bestätigen!';
            }
        }
        if(count(array_filter($errors,"leer"))>0) $showFormPassword=true;
    }
}


// Ausgabe
// Passwort vergessen
if((isset($_GET['page']))&&($_GET['page']=='pwvergessen'))
{
    ?>
    <div class="infobox">
        <h1>Passwort vergessen</h1>
        <p><span class="in_form">Bitte geben Sie die E-Mail Adresse ein, die Sie bei der Registrierung oder der letzten
            Änderung verwendet haben.</span></p>
        <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=pwvergessen'?>" method="post">
            <table>
                <tr>
                    <th class="vertikal">E-Mail :</th>
                    <td><input type="email" name="DATA_email" class="<?=($errormsg!=''?'redborder':'data')?>"
                           value="<?=(isset($_POST['DATA_email'])?$_POST['DATA_email']:'')?>"></td>
                </tr>
                <tr>
                    <td><input type="submit" name="button_send" value="E-Mail senden"></td>
                </tr>
            </table>
        </form>
        <?php
        if($errormsg!='') echo '<span class="redtxt">'.$errormsg.'</span>';
        elseif(isset($_POST['DATA_email'])) success('Es wurde eine E-Mail an die '.'Adresse "'.$_POST['DATA_email'].
            '" gesendet.');
        ?>
    </div>
<?php
}

// Passwort zurücksetzen
if((isset($_GET['page']))&&($_GET['page']=='resetpw'))
{
   if($valid)
   {
    ?>
        <div class="infobox">
            <h1>Passwort zurücksetzen</h1>
            <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="post">
                <table class="info">
                    <tr>
                        <th class="vertikal">neues Passwort:</th>
                        <td><input type="password" name="DATA_password1"<?=($generated_pw!=''?' value="'.
                                $generated_pw.'"':'')?><?=($errors[1]!=''?' class="redborder"':'')?>"></td>
                        <?php
                        if($errors[1]!='') echo '<td><span class="redtxt">'.$errors[1].'</span></td>';
                        ?>
                    </tr>
                    <tr>
                        <th class="vertikal">Passwort bestätigen:</th>
                        <td><input type="password" name="DATA_password2"
                                <?=($generated_pw!=''?' value="'.$generated_pw.'"':'')?><?=($errors[2]!=''?
                                ' class="redborder"':'')?>></td>
                        <?php
                        if($errors[2]!='') echo '<td><span class="redtxt">'.$errors[2].'</span></td>';
                        ?>
                    </tr>
                    <tr>
                        <th class="vertikal">generiertes Passwort:</th>
                        <td><input type="text" name="DATA_generatedpw"
                                <?=($generated_pw!=''?' value="'.$generated_pw.'"':'')?>></td>
                    </tr>
                    <tr>
                        <td><input type="submit" name="button_send" value="Passwort ändern"></td>
                        <td class="username"><input type="submit" value="Generieren" name="btngenerate">
                            <span class="abkuerzung">Länge:</span></td>
                        <td class="laenge"><input type="text" class="<?=($errors[count($errors)-1]?'redborder':
                                'klein')?>" value="<?=(isset($_POST['DATA_genpwlength'])?$_POST['DATA_genpwlength']:
                                '10')?>" name="DATA_genpwlength"></td>
                        <?php
                        if($errors[count($errors)-1]!='') echo '<td><span class="redtxt">'.$errors[count($errors)-1].
                            '</span></td>';
                        ?>
                    </tr>
                </table>
            </form>
        </div>
        <?php
        if((isset($_POST['button_send']))&&($errormsg==''))
            success('Ihr Passwort wurde erfolgreich zurückgesetzt.');
    }
    else echo '<div class="no-rows"><p>Der Link zum Passwortzurücksetzen ist nicht gültig.</p>
            <span class="clearfix"></span></div>';
}
?>