<?php
//Eingabe
if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin']=0;
// eingeloggt
if(isset($_SESSION['id_user']))
{
    // Eingabe

    // GET, POST id und kategorie speichern
    $id=(isset($_GET['id'])?(valid_id('tblAdresse','id_adresse',$_GET['id'])?$db->real_escape_string($_GET['id']):''):
        (isset($_POST['id'])?(valid_id('tblAdresse','id_adresse',$_POST['id'])?$db->real_escape_string($_POST['id']):
        ''):''));

    // Formulare anzeigen

    //Gericht hinzufügen
    if((isset($_GET['action']))&&($_GET['action']=='add')) $showFormAdd=true;
    else $showFormAdd=false;

    // Gericht bearbeiten
    if((isset($_GET['action']))&&($_GET['action']=='edit')&&($id!=''))  $showFormEdit=true;
    else $showFormEdit=false;

    // Eingabe-Felder definieren
    $inputs=array(  array('label'=>'Vorname*','name'=>'DATA_vorname'),
                    array('label'=>'Nachname*','name'=>'DATA_nachname'),
                    array('label'=>'Adresse*','name'=>'DATA_adresse'),
                    array('label'=>'Postleitzahl*','name'=>'DATA_postleitzahl'),
                    array('label'=>'Ortschaft*','name'=>'DATA_ortschaft'));

    // Fehler überprüfen und Abspeichern
    if(isset($_POST['btnSave']))
    {
        // Fehler überprüfen
        $errors=error_check($inputs);
        if(count(array_filter($errors,"leer"))==0)
        {
            // Hinzufügen
            if($showFormAdd)
            {
                // in tblAdresse hinzufügen
                $query="INSERT INTO tblAdresse (dtVorname,dtNachname,dtAdresse,dtPostleitzahl,dtOrtschaft,fi_kunde)".
                        " VALUES (".
                        "'".db_update($_POST['DATA_vorname'])."',".
                        "'".db_update($_POST['DATA_nachname'])."',".
                        "'".db_update($_POST['DATA_adresse'])."',".
                        "'".db_update($_POST['DATA_postleitzahl'])."',".
                        "'".db_update($_POST['DATA_ortschaft'])."',".
                        $_SESSION['id_user'].")";
                mysqli_query($db,$query);
                // in Bestellungsadressen kopieren(hinzufügen)
                $query= "INSERT INTO tblBestellungsadresse (dtVorname,dtNachname,dtAdresse,dtPostleitzahl,dtOrtschaft,
                        fi_kunde,fi_adresse)".
                        "VALUES (".
                        "'".db_update($_POST['DATA_vorname'])."',".
                        "'".db_update($_POST['DATA_nachname'])."',".
                        "'".db_update($_POST['DATA_adresse'])."',".
                        "'".db_update($_POST['DATA_postleitzahl'])."',".
                        "'".db_update($_POST['DATA_ortschaft'])."',".
                        $_SESSION['id_user'].",".
                        mysql_insert_id().")";
                mysqli_query($db,$query);
                $showFormAdd=false;
            }

            // Bearbeiten
            if($showFormEdit)
            {
                // adresse in der Tabelle tblBestellungsadresse bleibt erhalten
                /* neue Adresse in der Tabelle tblBestellungsadresse hinzufügen, anderenfalls Adressen in
                   beiden Tabellen ändern */
				// überprüfen ob mit dieser Adresse Bestellungen gemacht wurden
                $query = "SELECT * FROM tblBestellung WHERE fi_kunde = ".$_SESSION['id_user'].
                        " AND fi_adresse = (SELECT id_bestellungsadresse FROM tblBestellungsadresse
                                                            WHERE fi_adresse = ".$id.")";
                $result = mysqli_query($db,$query);

                if(mysqli_num_rows($result)>0)
                {
					// Adresse in der Tabelle tblAdresse ändern
                    $query= "UPDATE tblAdresse SET ".
                            "dtVorname='".db_update($_POST['DATA_vorname'])."',".
                            "dtNachname='".db_update($_POST['DATA_nachname'])."',".
                            "dtAdresse='".db_update($_POST['DATA_adresse'])."',".
                            "dtPostleitzahl='".db_update($_POST['DATA_postleitzahl'])."',".
                            "dtOrtschaft='".db_update($_POST['DATA_ortschaft'])."'".
                            " WHERE id_adresse=".$id;
                    mysqli_query($query);

                    // adresse in der Tabelle tblBestellungsadresse bleibt erhalten, mit leerer fi_adresse
					// -> keine Verbindung zu der Adresse
                    $query = "UPDATE tblBestellungsadresse SET fi_adresse=NULL WHERE fi_adresse = ".$id;
                    mysqli_query($db,$query);

                    // geänderte Adresse in tblBestellungsadresse kopieren(hinzufügen)
					$query= "INSERT INTO tblBestellungsadresse (dtVorname,dtNachname,dtAdresse,dtPostleitzahl,dtOrtschaft,
					        fi_kunde,fi_adresse)".
                            "VALUES (".
                            "'".db_update($_POST['DATA_vorname'])."',".
                            "'".db_update($_POST['DATA_nachname'])."',".
                            "'".db_update($_POST['DATA_adresse'])."',".
                            "'".db_update($_POST['DATA_postleitzahl'])."',".
                            "'".db_update($_POST['DATA_ortschaft'])."',".
                            $_SESSION['id_user'].",".
                            mysql_insert_id().")";
					mysqli_query($db,$query);
                }
                else
                {
                    // Adresse in Tabelle tblAdresse ändern
                    $query= "UPDATE tblAdresse SET ".
                            "dtVorname='".db_update($_POST['DATA_vorname'])."',".
                            "dtNachname='".db_update($_POST['DATA_nachname'])."',".
                            "dtAdresse='".db_update($_POST['DATA_adresse'])."',".
                            "dtPostleitzahl='".db_update($_POST['DATA_postleitzahl'])."',".
                            "dtOrtschaft='".db_update($_POST['DATA_ortschaft'])."'".
                            " WHERE id_adresse=".$id;
                    mysqli_query($query);

                    // Adresse auch in Tabelle tblBestellungsadresse ändern
                    $query= "UPDATE tblBestellungsadresse SET ".
                            "dtVorname='".db_update($_POST['DATA_vorname'])."',".
                            "dtNachname='".db_update($_POST['DATA_nachname'])."',".
                            "dtAdresse='".db_update($_POST['DATA_adresse'])."',".
                            "dtPostleitzahl='".db_update($_POST['DATA_postleitzahl'])."',".
                            "dtOrtschaft='".db_update($_POST['DATA_ortschaft'])."'".
                            " WHERE fi_adresse = ".$id;
                    mysqli_query($db,$query);
                }
                $showFormEdit=false;
            }
        }
    }

    // Adresse Löschen
    if((isset($_POST['btnYes']))&&($id!=''))
    {
        // überprüfen ob diese Adresse nicht in tblBestellung
        $query= "SELECT * FROM tblBestellung
                WHERE fi_kunde = ".$_SESSION['id_user'].
                " AND fi_adresse = (SELECT id_bestellungsadresse FROM tblBestellungsadresse
                                                WHERE fi_adresse = ".$id.")";
        $result=mysqli_query($db,$query);

		if (mysqli_num_rows($result)>0)
        {
			// mit der Adresse gibt es noch offene Bestellungen
            // -> Adresse muss in tblBestellungsadresse erhalten bleiben
			// Adresse in tblBestellungsadresse überschreiben mit leerem Feld fi_adresse
			$query="UPDATE tblBestellungsadresse SET fi_adresse=NULL WHERE fi_adresse=".$id;
			mysqli_query($db,$query);

			// Adresse in tblAdresse löschen
			$query = "DELETE FROM tblAdresse WHERE id_adresse = ".$id;
            mysqli_query($db,$query);
        }
        else
        {
			// mit der Adresse keine offenen Bestellungen
            // Adresse auch aus tblBestellungsadresse löschen
            $query = "DELETE FROM tblBestellungsadresse WHERE fi_adresse = ".$id;
            mysqli_query($db,$query);
            // Adresse aus tblAdresse löschen
            $query = "DELETE FROM tblAdresse WHERE id_adresse = ".$id;
            mysqli_query($db,$query);
        }
    }
    //Ausgabe

    // Formular Adresse hinzufügen/bearbeiten anzeigen
    if($showFormAdd || $showFormEdit)
    {
        if($showFormEdit)
        {
            $query="SELECT * FROM tblAdresse WHERE id_adresse=".$id;
            $result=mysqli_query($db,$query);
        }
        ?>
        <div class="adresse">
            <h1>Adresse <?=($showFormAdd?'hinzufügen':'bearbeiten')?></h1>
            <a class="cancel" href="<?=$_SERVER['SCRIPT_NAME'].'?page=adressbuch'?>"><span>Abbrechen</span></a>
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=adressbuch&amp;action='.$_GET['action']?>" method="post">
                <table>
                    <?php
                    $i=0;
                    foreach($inputs as $feld=>$properties)
                    {
                        ?>
                        <tr>
                            <th class="vertikal"><?=$properties['label']?></th>
                            <td><input type="text" name="<?=$properties['name']?>"
                                <?=(isset($_POST[$properties['name']])?' value="'.$_POST[$properties['name']].'"':
                                ($showFormEdit?' value="'.db_result($result,0,'dt'.ucfirst(str_replace('DATA_','',
                                $properties['name']))).'"':''))?> class="<?=((isset($errors))?(($errors[$i]=='')?
                                'data':'redborder'):'data')?>">
                            </td>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                </table>
                <?=($id!=''?'<input type="hidden" name="id" value="'.$id.'">':'')?>
                <div class="btn_center">
                    <input type="submit" name="btnSave" value="Speichern">
                </div>
            </form>
            <p>
                <span class="required"> * : diese Felder müssen ausgefüllt werden</span><br>
                <?php
                if((isset($errors))&&(count(array_filter($errors,"leer"))>0))
                {
                    $errors=array_filter($errors,"leer");
                    // Fehler ausgeben
                    for($i=0;$i<count($errors);$i++)
                    {
                        echo '<span class="redtxt">'.$errors[$i].'</span><br>'."\n";
                    }
                }
                ?>
            </p>
        </div>
        <?php
    }
    ?>
    <div class="adressbuch">
        <span class="ueberschrift">Ihre Adressen</span>
        <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=adressbuch&amp;action=add'?>"><span>Adresse hinzufügen
            </span></a>
        <hr>
        <?php
        $query="SELECT id_adresse,dtVorname,dtNachname,dtAdresse,dtPostleitzahl,dtOrtschaft FROM tblAdresse ".
                "WHERE fi_kunde=".$_SESSION['id_user'];
        $result=mysqli_query($db,$query);
        if(mysqli_num_rows($result)>0)
        {
        ?>
            <table class="adressbuch">
                <?php
                for($i=0;$i<mysqli_num_rows($result);$i++)
                {
                    ?>
                    <tr>
                        <td class="numbering"><?=strval($i+1).'. '?></td>
                        <td class="displayadress">
                            <div class="displayadress">
                                <ul>
                                    <li><?=db_result($result,$i,'dtVorname').' '.db_result($result,$i,'dtNachname')?></li>
                                    <li><?=db_result($result,$i,'dtAdresse')?></li>
                                    <li><?=db_result($result,$i,'dtPostleitzahl').' '.db_result($result,$i,'dtOrtschaft')?></li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=adressbuch&amp;action=edit&amp;id='.
                                db_result($result,$i,'id_adresse')?>"><span>Bearbeiten</span></a>
                        </td>
                        <td>
                            <span class="abkuerzung">Abkürzung in der Auswahlliste:</span><br>
                            <span><?=db_result($result,$i,'dtVorname').' '.db_result($result,$i,'dtNachname').' - '.
                                substr(db_result($result,$i,'dtAdresse'),strpos(db_result($result,$i,'dtAdresse'),', ')+1).', '.
                                db_result($result,$i,'dtOrtschaft')?></span>
                        </td>
                        <td><a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=adressbuch&amp;action=del'.
                                '&amp;id='.db_result($result,$i,'id_adresse')?>"><span>Löschen</span></a></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        <?php
        }
        else echo '<div class="no-rows"><p>Sie haben keine Adresse definiert.</p></div>';
        ?>
    </div>

    <?php
    // Löschen Bestätigen
    if((isset($_GET['action']))&&($_GET['action']=='del')&&($id!=''))
    {
        $query="SELECT * FROM tblAdresse WHERE id_adresse=".$id;
        $result=mysqli_query($db,$query);
        ?>
        <div class="deleteconfirm">
            <p>Sind sie	sicher, dass Sie die Adresse <br><span>"<?=db_result($result,0,'dtVorname').' '.
                        db_result($result,0,'dtNachname').' - '.substr(db_result($result,0,'dtAdresse'),
                        strpos(db_result($result,0,'dtAdresse'),', ')+1).', '.db_result($result,0,'dtOrtschaft')?>"</span>
                <br>l&ouml;schen wollen?</p>
            <form action="<?=$_SERVER['SCRIPT_NAME']?>?page=adressbuch" method="POST">
                <input type="submit" name="btnYes" value="Ja" class="yes"/>
                <input type="submit" name="btnNo" value="Nein" class="no"/>
                <input type="hidden" name="id" value="<?=db_result($result,0,'id_adresse')?>">
            </form>
        </div>
<?php
    }
}
else
{
    $url=$_SERVER['SCRIPT_NAME'];
    $pieces=explode('/',$url);
    if($pieces[count($pieces)-3]=='Include')
    {
        $url=(isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].str_replace('/'.
            $pieces[count($pieces)-3].'/'.$pieces[count($pieces)-2],'',dirname($_SERVER['SCRIPT_NAME']));
        header('Location:'.$url);
    }
}
?>