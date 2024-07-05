<?php
//Eingabe
if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin']=0;
// eingeloggt
if($_SESSION['loggedin']==1)
{
    // Eingabe
    if(!isset($errormsg)) $errormsg='';
    if(!isset($generated_pw)) $generated_pw='';

    // Formulare anzeigen
    if(!isset($showFormUsername)) $showFormUsername=false;
    if(!isset($showFormPassword)) $showFormPassword=false;

    // Fehler
    if(!isset($showErrorOldPw)) $showErrorLänge=false;
    if(!isset($showErrorNewPw)) $showErrorNewPw=false;
    if(!isset($showErrorLänge)) $showErrorLänge=false;

    if(!isset($errors)) $errors=array('','','','');

    // Formulare anzeigen
    if((isset($_GET['edit']))&&($_GET['edit']=='username')) $showFormUsername=true;
    if((isset($_GET['edit']))&&($_GET['edit']=='pw')) $showFormPassword=true;

    // Verarbeitung

    // Benutzername ändern
    if(isset($_POST['btnchange_username']))
    {
        $errormsg=username_check($_POST['DATA_username']);
        // keine Fehler
        if($errormsg=='')
        {
            // Benutzername ändern
            // anders als vorheriger Username
            $query="SELECT dtUsername FROM tblBenutzer
                    WHERE id_benutzer=".(isset($_SESSION['id_user'])?$_SESSION['id_user']:$_SESSION['restaurant']);
            $result=mysqli_query($db,$query);
            if(db_result($result,0,'dtUsername')!=$_POST['DATA_username'])
            {
                $query="UPDATE tblBenutzer SET ".
                    "dtUsername='".db_update($_POST['DATA_username'])."'".
                    " WHERE id_benutzer=".(isset($_SESSION['id_user'])?$_SESSION['id_user']:$_SESSION['restaurant']);
                mysqli_query($db,$query);
                success('Ihr Benutzername wurde erfolgreich auf "'.$db->real_escape_string($_POST['DATA_username']).
                    '" geändert.');
            }
            else success('Ihr Benutzername wurde nicht geändert.');
            $showFormUsername=false;
        }
        else $showFormUsername=true;
    }

    // Passwort ändern

    //Passwort generieren
    if(isset($_POST['btngenerate']))
    {
        if(is_numeric($_POST['DATA_genpwlength']))
            $generated_pw=generatepw($_POST['DATA_genpwlength']);
        else $errors[count($errors)-1]='Länge nicht numerisch!';
        $showFormPassword=true;
    }
    if(isset($_POST['btnchange_password']))
    {
        if(($_POST['DATA_oldpassword']=='')&&($_POST['DATA_newpassword1']=='')&&($_POST['DATA_newpassword2']=='')&&
            ($_POST['DATA_newpassword2']=='')&&($_POST['DATA_generatedpw']==''))
        {
            $showFormPassword=false;
            success('Ihr Passwort wurde nicht geändert');
        }
        else
        {
            $query= "SELECT dtPasswort FROM tblBenutzer
                    WHERE id_benutzer=".(isset($_SESSION['id_user'])?$_SESSION['id_user']:$_SESSION['restaurant']).
                    " AND dtPasswort=SHA1('".$db->real_escape_string($_POST['DATA_oldpassword'])."')";
            $result=mysqli_query($db,$query);

            //altes Passwort korrekt?
            if(mysqli_num_rows($result)==1)
            {
                // Passwörter nicht leer und benutzerdefiniertes Passwort setzen
                if(($_POST['DATA_newpassword1']!='')&&($_POST['DATA_newpassword2']!=''))
                {
                    //Passwort übereinstimmunng
                    if($_POST['DATA_newpassword1']==$_POST['DATA_newpassword2'])
                    {
                        $query="UPDATE tblBenutzer
                                SET dtPasswort=SHA1('".$db->real_escape_string($_POST['DATA_newpassword1'])."')
                                WHERE id_benutzer=".(isset($_SESSION['id_user'])?$_SESSION['id_user']:
                            $_SESSION['restaurant']);
                        mysqli_query($db,$query);
                    }
                    else $errors[1]='Passwörter stimmen nicht überein!';
                }
                else
                {
                    if($_POST['DATA_newpassword1']=='') $errors[1]='Bitte neues Passwort eingeben!';
                    if($_POST['DATA_newpassword2']=='') $errors[2]='Bitte Passwort bestätigen!';
                }
            }
            else
            {
                $errors[0]='altes Passwort falsch!';
                $generated_pw=$_POST['DATA_generatedpw'];
            }
        }
        if(count(array_filter($errors,"leer"))>0) $showFormPassword=true;
    }

    //Ausgabe
    ?>
    <!--Benutzer Informationen-->
    <?php
    // Benutzername ändern
    if(!$showFormPassword)
    {
        // Username abfragen
        $query="SELECT dtUsername
                FROM tblBenutzer
                WHERE id_benutzer=".(isset($_SESSION['id_user'])?$_SESSION['id_user']:$_SESSION['restaurant']);
        $result=mysqli_query($db,$query);
        ?>
        <div class="infobox">
            <h1>Informationen</h1>
            <table class="info">
                <tr>
                    <th class="vertikal">Benutzername</th>
                    <?php
                    if($showFormUsername)
                    {
                        ?>
                        <td class="username">
                            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>" method="post">
                                <input type="text" name="DATA_username" value="<?=(isset($_POST['DATA_username'])?
                                    $_POST['DATA_username']:db_result($result,0,'dtUsername'))?>"<?=($errormsg!=''?
                                    ' class="redborder"':'')?>>
                                <input type="submit" name="btnchange_username" value="Ändern">
                            </form>
                        </td>
                    <?php
                        if($errormsg!='')
                            echo '<td><span class="redtxt">'.$errormsg.'</span></td>';
                        else echo '<td>&nbsp;</td>';
                    }
                    else
                    {
                        ?>
                        <td><?=db_result($result,0,'dtUsername')?></td>
                        <td><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=username'?>">
                            <img alt="edit" src="Pictures/design/pencil.png"></a>
                        </td>
                    <?php
                    }
                    ?>
                </tr>
                <tr>
                    <th class="vertikal">Passwort</th>
                    <td>*****</td>
                    <td><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=pw'?>">
                        <img alt="edit" src="Pictures/design/pencil.png"></a></td>
                </tr>
            </table>
            <?php
            if(isset($_SESSION['id_user']))
                include(dirname($_SERVER['SCRIPT_FILENAME']).'/Include/Benutzer/informationen.php');
            ?>
        </div>
    <?php
    }
    // Passwort ändern
    if($showFormPassword)
    {
        ?>
        <div class="passwort">
            <h1>Passwort ändern</h1>
            <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>"><span class="cancel">Zurück</span></a>
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>" method="post">
                <table class="info">
                    <tr>
                        <th class="vertikal">altes Passwort:</th>
                        <td><input type="password" name="DATA_oldpassword" class="<?=($errors[0]!=''?'redborder':
                                '')?>">
                        </td>
                        <?php
                        if($errors[0]!='') echo '<td><span class="redtxt">'.$errors[0].'</span></td>';
                        ?>
                    </tr>
                    <tr>
                        <th class="vertikal">neues Passwort:</th>
                        <td><input type="password" name="DATA_newpassword1"<?=($generated_pw!=''?' value="'.
                            $generated_pw.'"':'')?><?=($errors[1]!=''?' class="redborder"':'')?>"></td>
                        <?php
                        if($errors[1]!='') echo '<td><span class="redtxt">'.$errors[1].'</span></td>';
                        ?>
                    </tr>
                    <tr>
                        <th class="vertikal">Passwort bestätigen:</th>
                        <td><input type="password" name="DATA_newpassword2"
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
                        <td><input type="submit" name="btnchange_password" value="Passwort ändern"></td>
                        <td class="username"><input type="submit" value="Generieren" name="btngenerate">
                            <span class="abkuerzung">Länge:</span></td>
                        <td class="laenge"><input type="text" class="<?=($errors[count($errors)-1]?'redborder':
                                'klein')?>" value="<?=(isset($_POST['DATA_genpwlength'])?$_POST['DATA_genpwlength']:
                                '10')?>" name="DATA_genpwlength"></td>
                        <?php
                        if($errors[count($errors)-1]!='') echo '<td><span class="redtxt">'.
                            $errors[count($errors)-1].'</span></td>';
                        ?>
                    </tr>
                </table>
            </form>
            <span class="clearfix"></span>
        </div>
    <?php
    }
}
else
{
    $url=$_SERVER['SCRIPT_NAME'];
    $pieces=explode('/',$url);
    if($pieces[count($pieces)-2]=='Include')
    {
        $url=(isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].str_replace('/'.
            $pieces[count($pieces)-2],'',dirname($_SERVER['SCRIPT_NAME']));
        header('Location:'.$url);
    }
}