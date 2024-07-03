<?php
if((isset($_SESSION['warenkorb']))&&(count($_SESSION['warenkorb'])>0))
{
    // Eingabe
    if(!isset($showForm)) $showForm=false;

    // Session auf Standard Adresse setzen
    if(!isset($_POST['DATA_id']))
    {
        $default=true;
        // Standard Adresse abragen
        $query="SELECT id_adresse FROM tblAdresse WHERE fi_kunde=".$_SESSION['id_user']." AND dtStandard=1";
        $result=mysqli_query($db,$query);
        if(mysqli_num_rows($result)>0)
        {
            $standard=db_result($result,0,'id_adresse');
            $_SESSION['lieferadresse']=$standard;
        }
    }
    elseif(valid_id('tblAdresse','id_adresse',$_POST['DATA_id']))
        $_SESSION['lieferadresse']=$_POST['DATA_id'];

    // Formular anzeigen
    if((isset($_GET['action']))&&($_GET['action']=='add')) $showForm=true;
        else $showForm=false;

    // Eingabe-Felder definieren
    $inputs=array(  array('label'=>'Vorname*','name'=>'DATA_vorname'),
                    array('label'=>'Nachname*','name'=>'DATA_nachname'),
                    array('label'=>'Adresse*','name'=>'DATA_adresse'),
                    array('label'=>'Postleitzahl*','name'=>'DATA_postleitzahl'),
                    array('label'=>'Ortschaft*','name'=>'DATA_ortschaft'));

    $query="SELECT * FROM tblAdresse WHERE fi_kunde=".$_SESSION['id_user'];
    $result=mysqli_query($db,$query);

    // Verarbeitung

    // Fehler überprüfen und Abspeichern
    if(isset($_POST['btnCheck']))
    {
        // Fehler überprüfen
        $errors=error_check($inputs);
        if(count(array_filter($errors,"leer"))==0)
        {
            // in tblAdresse hinzufügen
            $query="INSERT INTO tblAdresse (dtVorname,dtNachname,dtAdresse,dtPostleitzahl,dtOrtschaft,dtStandard,
                    fi_kunde)".
                    " VALUES (".
                    "'".db_update($_POST['DATA_vorname'])."',".
                    "'".db_update($_POST['DATA_nachname'])."',".
                    "'".db_update($_POST['DATA_adresse'])."',".
                    "'".db_update($_POST['DATA_postleitzahl'])."',".
                    "'".db_update($_POST['DATA_ortschaft'])."',".
                    (mysqli_num_rows($result)>0?'0':'1').",".
                    $_SESSION['id_user'].")";
            mysqli_query($db,$query);

            $_SESSION['lieferadresse']=mysql_insert_id($db);
            $_POST['DATA_id']=$_SESSION['lieferadresse'];
            $default=false;

            // in Bestellungsadressen kopieren(hinzufügen)
            $query= "INSERT INTO tblBestellungsadresse (dtVorname,dtNachname,dtAdresse,dtPostleitzahl,dtOrtschaft,
                    fi_kunde,fi_adresse) ".
                    "VALUES (".
                    "'".db_update($_POST['DATA_vorname'])."',".
                    "'".db_update($_POST['DATA_nachname'])."',".
                    "'".db_update($_POST['DATA_adresse'])."',".
                    "'".db_update($_POST['DATA_postleitzahl'])."',".
                    "'".db_update($_POST['DATA_ortschaft'])."',".
                    $_SESSION['id_user'].",".
                    $_SESSION['lieferadresse'].")";
            mysqli_query($db,$query);
            $showForm=false;
        }
        else $showForm=true;
    }

    // Ausgabe
        ?>
    <div class="adresse">
        <h1>Versandadresse</h1>
        <?php
        if(mysqli_num_rows($result)>0)
        {
        ?>
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferadresse'?>" method="post">
                <select name="DATA_id" onchange="submit()">
                <?php
                for($i=0;$i<mysqli_num_rows($result);$i++)
                {
                    ?>
                    <option value="<?=db_result($result,$i,'id_adresse')?>"
                        <?=(($default&&(db_result($result,$i,'id_adresse')==$standard))?' selected="selected"':
                        ((!$default&&($_POST['DATA_id']==db_result($result,$i,'id_adresse')))?' selected="selected"':
                            ''))?>><?=db_result($result,$i,'dtVorname').' '.db_result($result,$i,'dtNachname').' - '.
                            substr(db_result($result,$i,'dtAdresse'),strpos(db_result($result,$i,'dtAdresse'),', ')+1).', '.
                            db_result($result,$i,'dtOrtschaft')?></option>
                <?php
                }
                ?>
                </select>
            </form>
        <?php
        }
        if((isset($_POST['DATA_id']))&&(valid_id('tblAdresse','id_adresse',$_POST['DATA_id'])))
        {
            $query="SELECT * FROM tblAdresse WHERE id_adresse=".$_POST['DATA_id'];
            $result=mysqli_query($db,$query);
        }
        else
        {
            $query="SELECT * FROM tblAdresse WHERE fi_kunde=".$_SESSION['id_user'].
                " AND dtStandard=1";
            $result=mysqli_query($db,$query);
        }
        if(mysqli_num_rows($result)>0)
        {
        ?>
            <table>
                <tr>
                    <th class="vertikal">Empfänger</th>
                    <td><span class="adresse"><?=db_result($result,0,'dtVorname').' '.db_result($result,0,'dtNachname')?></span>
                    </td>
                </tr>
                <tr>
                    <th class="vertikal">Adresse</th>
                    <td><span class="adresse"><?=db_result($result,0,'dtAdresse')?></span></td>
                </tr>
                <tr>
                    <th class="vertikal">Postleitzahl/Ort</th>
                    <td><span class="adresse"><?=db_result($result,0,'dtPostleitzahl').' '.db_result($result,0,'dtOrtschaft')?>
                    </span></td>
                </tr>
            </table>
            <div class="btn_center">
                <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferadresse&amp;action=add'?>">
                    <span>andere Adresse</span></a>
            </div>
        <?php
        }
        else
        {
            ?>
            <div class="gerichttabelle"><p>Sie haben keine Adresse definiert.</p></div>
            <div class="btn_center">
                <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferadresse&amp;action=add'?>">
                    <span>Adresse hinzufügen</span></a>
            </div>
        <?php
        }
        if($showForm)
        {
            ?>
            <a class="cancel" href="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferadresse'?>">
                <img src="../Pictures/design/close.png"></a>
            <form class="inner_form" action="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferadresse'?>" method="post">
                <table>
                    <?php
                    $i=0;
                    foreach($inputs as $feld=>$properties)
                    {
                        ?>
                        <tr>
                            <th class="vertikal"><?=$properties['label']?></th>
                            <td><input type="text" name="<?=$properties['name']?>"
                                <?=(isset($_POST[$properties['name']])?' value="'.$_POST[$properties['name']].'"':'')?>
                                <?=(((isset($errors[$i]))&&($errors[$i]!=''))?' class="redborder"':'')?>></td>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                </table>
                <div class="btn_center">
                    <input class="button" type="submit" name="btnCheck" value="Eingabe bestätigen">
                </div>
            </form>
        <?php
        }
        ?>
        <div class="btn_center">
            <?php
            if(mysqli_num_rows($result)>0)
                echo '<a class="weiter" href="'.$_SERVER['SCRIPT_NAME'].'?page=zahlungsart"><span>Weiter</span></a>';
            ?>
        </div>
        <a class="zurueck" href="<?=str_replace('bezahlen','',$_SERVER['SCRIPT_NAME']).'?page=bestellen'?>">
            Zurück zur Bestellungsseite</a>
    </div>
<?php
}
else
{
    $url=$_SERVER['SCRIPT_NAME'];
    $pieces=explode('/',$url);
    if($pieces[count($pieces)-2]=='bezahlen')
    {
        $url=(isset($_SESSION['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].str_replace('/'.
            $pieces[count($pieces)-2],'',dirname($_SERVER['SCRIPT_NAME']));
        //echo $url;
        header('Location:'.$url);
    }
}