<?php
// als User eingeloggt
if(isset($_SESSION['id_user']))
{
    // Eingabe

    if(!isset($showFormInfo)) $showFormInfo=false;
    if(!isset($showFormUsername)) $showFormUsername=false;
    if(!isset($showFormPassword)) $showFormPassword=false;
    if(!isset($showInfo)) $showInfo=true;

    // Formulare anzeigen

    // Informationen ändern
    if((isset($_GET['edit']))&&($_GET['edit']=='info')) $showFormInfo=true;


    $inputs=array(  array('label'=>'Vorname*','name'=>'DATA_vorname','type'=>'text'),
                    array('label'=>'Nachname*','name'=>'DATA_nachname','type'=>'text'),
                    array('label'=>'Telefonnummer','name'=>'DATA_telefonnummer','type'=>'text'),
                    array('label'=>'E-Mail*','name'=>'DATA_email1','type'=>'email'),
                    array('label'=>'E-Mail bestätigen*','name'=>'DATA_email2','type'=>'email'));

    // Verarbeitung

    // Informationen ändern
    if(isset($_POST['btnSave']))
    {
        //Fehlerüberprüfung
        $errors=error_check($inputs);

        //keine Fehler => update query
        if(count(array_filter($errors,"leer"))==0)
        {
            $query= "UPDATE tblBenutzer SET ".
                    "dtVorname='".db_update($_POST['DATA_vorname'])."',".
                    "dtNachname='".db_update($_POST['DATA_nachname'])."',".
                    "dtTelefonnummer='".db_update($_POST['DATA_telefonnummer'])."',".
                    "`dtE-Mail`='".db_update($_POST['DATA_email1'])."'".
                    " WHERE id_benutzer=".$_SESSION['id_user'];
            mysqli_query($db,$query);
            $showFormInfo=false;
            unset($errors);
        }
        else $showFormInfo=true;
    }
    // Ausgabe
    ?>
    <!--Informationen-->
    <?php
    // Informationen abrufen
    $query="SELECT *
            FROM tblBenutzer
            WHERE id_benutzer=".$_SESSION['id_user'];
    $result_info=mysqli_query($db,$query);
    if($showFormInfo)
    {
        // Als Formular
        ?>
        <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>" method="post">
            <table class="info">
                <?php
                $i=0;
                foreach($inputs as $feld=>$properties)
                {
                    ?>
                    <tr>
                        <th class="vertikal"><?=$properties['label']?></th>
                        <td><input type="<?=$properties['type']?>" value="<?=(isset($_POST[$properties['name']])?
                                $_POST[$properties['name']]:db_result($result_info,0,
                                ((($properties['name']=='DATA_email1')||($properties['name']=='DATA_email2'))?
                                    'dtE-Mail':'dt'.ucfirst(str_replace('DATA_','',$properties['name'])))))?>"
                                   name="<?=$properties['name']?>" class="<?=((isset($errors[$i]))&&
                                    ($errors[$i]!='')?'redborder':'data')?>">
                        </td>
                        <?php
                        if($i==0)
                        {
                        ?>
                            <td>
                                <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>">
                                    <span class="cancel">Zurück</span>
                                </a>
                            </td>
                        <?php
                        }
                        else echo '<td>&nbsp;</td>'."\n";
                        ?>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </table>
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
    <?php
    }
    else
    {
        // Daten nur anzeigen
        ?>
        <table class="info">
            <?php
            $i=0;
            foreach($inputs as $feld=>$properties)
            {
                if(($properties['name']!='DATA_email1')&&($properties['name']!='DATA_email2'))
                {
                    ?>
                    <tr>
                        <th class="vertikal"><?=$properties['label']?></th>
                        <td><?=db_result($result_info,0,'dt'.ucfirst(str_replace('DATA_','',$properties['name'])))
                            ?></td>
                        <?php
                        if($i==0)
                        {
                        ?>
                            <td><a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;'.
                                'edit=info'?>"><span>Bearbeiten</span></a></td>
                        <?php
                        }
                        else echo '<td>&nbsp;</td>';
                        ?>
                    </tr>
                <?php
                }
                else
                {
                    if($properties['name']=='DATA_email1')
                    {
                    ?>
                        <tr>
                            <th class="vertikal">E-Mail</th>
                            <td><?=db_result($result_info,0,'dtE-Mail')?></td>
                            <td>&nbsp;</td>
                        </tr>
                    <?php
                    }
                }
                $i++;
            }
            ?>
        </table>
        <hr>
        <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=adressbuch'?>"><span>Adressbuch verwalten</span></a>
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