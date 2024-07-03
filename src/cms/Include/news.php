<?php
//Eingabe

if(!isset($_SESSION['cmsloggedin'])) $_SESSION['cmsloggedin']=0;

if($_SESSION['cmsloggedin']==1)
{
    // Eingabe
    if(!isset($showFormAdd)) $showFormAdd=false;
    if(!isset($showFormEdit)) $showFormEdit=false;

    $id=(isset($_GET['id'])?(valid_id('tblNews','id_news',$_GET['id'])?mysql_real_escape_string($_GET['id']):''):
        (isset($_POST['id'])?(valid_id('tblNews','id_news',$_POST['id'])?mysql_real_escape_string($_POST['id']):''):
            ''));

    // Eingabe-Felder
    $inputs=array(  array('label'=>'Titel*','name'=>'DATA_titel','type'=>'text'),
                    array('label'=>'Inhalt*','name'=>'DATA_inhalt','type'=>'textarea'),
                    array('label'=>'ist Sichtbar','name'=>'DATA_istSichtbar','type'=>'checkbox'));

    // Hinzufügen
    if((isset($_GET['action']))&&($_GET['action']=='add')) $showFormAdd=true;
    // Bearbeiten
    if((isset($_GET['action']))&&($_GET['action']=='edit')&&($id!='')) $showFormEdit=true;

    $timestamp=date('Y.m.d@H:i');

    //Verarbeitung

    if(isset($_POST['button_save']))
    {
        $counttxt=0; // Textfelder zählen
        // checkbox checked = 1; nicht checked = 0
        foreach($inputs as $feld => $properties)
        {
            if($properties['type']=='checkbox')
            {
                if(isset($_POST[$properties['name']])) $_POST[$properties['name']]=1;
                else $_POST[$properties['name']]=0;
            }
            else $counttxt++;
        }
        // array für Fehler
        $errors=array();
        for($i=0;$i<$counttxt;$i++) $errors[]='';

        $i=0;
        // Fehlerüberprüfung
        foreach($_POST as $inputname=>$value)
        {
            // Input-Felder leer
            if($i<$counttxt)
            {
                $label=$inputs[$i]['label'];
                if(($value=='')&&(strpos($label,'*')>0))
                {
                    $errors[$i]='Bitte '.substr($label,0,strlen($label)-1).' eingeben!';
                }
                else
                {
                    // Titel existiert
                    if($inputname=='DATA_titel')
                    {
                        $query="SELECT dtTitel FROM tblNews WHERE dtTitel='".$value."'".
                                (isset($_POST['id'])?' AND id_news<>'.$id:'');
                        $result=mysqli_query($db,$query);
                        if(mysqli_num_rows($result)==1)
                        {
                            $errors[$i]='Die News mit dem Titel "'.$value.'" existiert bereits, bitte wählen Sie '.
                                        'einen anderen Titel aus!';
                        }
                        else $errors[$i]='';
                    }
                }
                $i++;
            }
        }

        //keine Fehler
        if(count(array_filter($errors,"leer"))==0)
        {
            if(isset($_POST['showFormAdd'])) $showFormAdd=true;
            if(isset($_POST['showFormEdit'])) $showFormEdit=true;
            // Hinzufügen
            if($showFormAdd)
            {
                $timestamp=date('Y.m.d@H:i');

                //keine Fehler
                if(count(array_filter($errors,"leer"))==0)
                {
                    $qadd=  "INSERT INTO tblNews (dtTitel,dtInhalt,dtZeitstempel,istSichtbar,fi_ersteller)".
                            "VALUES(".
                            "'".db_update($_POST['DATA_titel'])."',".
                            "'".db_update($_POST['DATA_inhalt'])."',".
                            "'".db_update($timestamp)."',".
                            db_update($_POST['DATA_istSichtbar']).",".
                            $_SESSION['id_admin'].
                            ")";
                    mysqli_query($qadd,$db);
                    $showFormAdd=false;
                }
            }

            // Bearbeiten
            if($showFormEdit)
            {
                $qedit= "UPDATE tblNews SET ".
                        "dtTitel='".db_update($_POST['DATA_titel'])."',".
                        "dtInhalt='".db_update($_POST['DATA_inhalt'])."',".
                        "istSichtbar=".db_update($_POST['DATA_istSichtbar']).",".
                        "dtZeitstempel='".db_update($timestamp)."',".
                        "fi_ersteller=".$_SESSION['id_admin'].
                        " WHERE id_news=".$id;
                mysqli_query($qedit,$db);
                $showFormEdit=false;
            }
        }
        else
        {
            if(isset($_POST['showFormAdd'])) $showFormAdd=true;
            if(isset($_POST['showFormEdit'])) $showFormEdit=true;
        }
    }


    // Löschen
    if(isset($_POST['BUTTON_YES']))
    {
        $qdel="DELETE FROM tblNews WHERE id_news=".$id;
        //echo $qdel;
        mysqli_query($qdel,$db);
    }
    
    //Debug
	
    //Ausgabe
    ?>
    <div class="page">
        <div>
            <a class="right" href="<?=$_SERVER['SCRIPT_NAME'].'?page=news&amp;action=add'?>">
                <span>News hinzufügen</span></a>
            <span class="ueberschrift">News</span>
            <span class="clearfix"></span>
        </div>
        <hr>
        <?php
        // Hinzufügen-/Bearbeiten-Formular anzeigen
        if(($showFormAdd)||($showFormEdit))
        {
            if($showFormEdit)
            {
                // Formularinhalte aus der Datenbank laden
                $query="SELECT * FROM tblNews WHERE id_news=".$id;
                $result=mysqli_query($db,$query);
                $timestamp=db_result($result,0,'dtZeitstempel');
            }
            ?>
            <div class="formular">
                <a class="cancel" href="<?=$_SERVER['SCRIPT_NAME'].'?page=news'?>"><span>Abbrechen</span></a>
                <span class="clearfix"></span>
                <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=news'?>" method="post">
                    <table id="formular">
                        <?php
                        $e=0;
                        foreach($inputs as $feld => $properties)
                        {
                            ?>
                            <tr>
                                <td><span class="formular"><?=$properties['label']?></span></td>
                                <td>
                                    <?php
                                    // Normale Felder generieren
                                    if($properties['type']!='textarea')
                                    {
                                        ?>
                                        <input type="<?=$properties['type']?>" class="<?=(((isset($errors[$e]))&&
                                            ($errors[$e]!=''))?'redborder':'data')?>" name="<?=$properties['name']?>"
                                            value="<?=(isset($_POST[$properties['name']])?$_POST[$properties['name']]:
                                            ($showFormEdit?db_result($result,0,($properties['type']!='checkbox'?
                                                'dt'.ucfirst(str_replace('DATA_','',$properties['name'])):
                                                str_replace('DATA_','',$properties['name']))):''))?>"
                                                <?=((($properties['type']=='checkbox')&&
                                                    (((isset($_POST[$properties['name']]))&&
                                                    ($_POST[$properties['name']]==1))||(($showFormEdit)&&
                                                    (db_result($result,0,str_replace('DATA_','',
                                                    $properties['name']))==1))||($showFormAdd)))?' checked="checked"':
                                                    '')?>>
                                    <?php
                                    }
                                    else
                                    {
                                        // Textarea anzeigen
                                        ?>
                                        <textarea <?=(((isset($errors[$e]))&&($errors[$e]!=''))?'redborder':'')?>
                                            name="<?=$properties['name']?>"><?=(isset($_POST[$properties['name']])?
                                            $_POST[$properties['name']]:($showFormEdit?db_result($result,0,'dt'.
                                                    ucfirst(str_replace('DATA_','',$properties['name']))):''))?>
                                        </textarea>
                                    <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $e++;
                        }
                        ?>
                        <tr>
                            <td><span class="formular"><?=($showFormEdit?'letzter ':'').'Zeitstempel'?></span></td>
                            <td><span class="timestamp"><?=$timestamp?></span></td>
                        </tr>
                    </table>
                    <div class="border">
                        <?php
                        if($id!='') echo '<input type="hidden" name="id" value="'.$id.'">'."\n";
                        if($showFormAdd) echo '<input type="hidden" name="showFormAdd" value="'.$showFormAdd.
                            '">'."\n";
                        if($showFormEdit) echo '<input type="hidden" name="showFormEdit" value="'.$showFormEdit.
                            '">'."\n";
                        ?>
                        <input type="submit" class="button" value="Speichern" name="button_save" />
                    </div>
                </form>

                <?php
                //Fehlerausgabe
                if(isset($errors))
                {
                    for($i=0;$i<count($errors);$i++)
                    {
                        if($errors[$i]!='')
                            echo '<p class="redtxt">'.$errors[$i].'</p>'."\n";
                    }
                }
                ?>
            </div>
        <?php
        }
        // Löschen Bestätigen
        if((isset($_GET['action']))&&($_GET['action']=='delete')&&($id!=''))
        {
            $query="SELECT dtTitel FROM tblNews WHERE id_news=".$id;
            $result=mysqli_query($db,$query);
            ?>
            <div class="deleteconfirm">
                <div class="btn_center">
                    <p>Sind sie	sicher, dass Sie die News<br>
                        <span>"<?=db_result($result,0,'dtTitel')?>"</span><br>l&ouml;schen wollen?"</p>
                </div>
                <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=news'?>" method="POST">
                    <div class="btn_center">
                        <input type="submit" name="BUTTON_YES" value="Ja" class="button">
                        <input type="submit" name="BUTTON_NO" value="Nein" class="button">
                    </div>
                    <input type="hidden" name="id" value="<?=$id?>">
                </form>
            </div>
        <?php
        }
        $query="SELECT * FROM tblNews ORDER BY dtZeitstempel DESC";
        $result=mysqli_query($db,$query);
        ?>
        <table id="table_all">
            <tr>
                <th class="horizontal">Titel</th>
                <th class="horizontal">Inhalt</th>
                <th class="horizontal">Zeitstempel</th>
                <th colspan="3">Aktion</th>
            </tr>
            <?php
            for($i=0;$i<mysqli_num_rows($result);$i++)
            {
                ?>
                <tr>
                    <td><?=db_result($result,$i,'dtTitel')?></td>
                    <td class="inhalt"><?=db_result($result,$i,'dtInhalt')?></td>
                    <td><?=db_result($result,$i,'dtZeitstempel')?></td>
                    <td><a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=news&amp;'.
                    'action=edit&amp;id='.db_result($result,$i,'id_news')?>">Bearbeiten</a></td>
                    <td><a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=news&amp;'.
                        'action=delete&amp;id='.db_result($result,$i,'id_news')?>">Löschen</a></td>
                    <?php $url=str_replace('cms/','',$_SERVER['SCRIPT_NAME']); ?>
                    <td><a class="button" target="_blank" href="<?=$url.'?page=news&amp;'.
                    'action=preview&amp;id='.db_result($result,$i,'id_news')?>">Vorschau</a></td>
                </tr>
            <?php
            }
            ?>
        </table>
    </div>
<?php
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
?>