<?php
if(isset($_SESSION['restaurant']))
{
    // Eingabe
    if(!isset($value)) $value='';

    // GET, POST id und kategorie speichern
    $id=(isset($_GET['id'])?(valid_id('tblGericht','id_gericht',$_GET['id'])?$db->real_escape_string($_GET['id']):''):
        (isset($_POST['id'])?(valid_id('tblGericht','id_gericht',$_POST['id'])?$db->real_escape_string($_POST['id']):''):
            ''));


    $kategorie= (isset($_GET['kategorie'])?(valid_id('tblKategorie','id_kategorie',$_GET['kategorie'])?
                $db->real_escape_string($_GET['kategorie']):''):(isset($_POST['kategorie'])?(valid_id('tblKategorie',
                'id_kategorie',$_POST['kategorie'])?$db->real_escape_string($_POST['kategorie']):''):''));

    // Formulare anzeigen
    //Gericht hinzufügen
    $showFormAdd=((((isset($_GET['action']))&&($_GET['action']=='add'))||
        ((isset($_POST['kategorie']))&&(!isset($_POST['id']))))&&($kategorie!=''));
    // Gericht bearbeiten
    $showFormEdit=((((isset($_GET['action']))&&($_GET['action']=='edit'))||(isset($_POST['id'])))&&($id!='')&&
        ($kategorie!=''));

    // Tabelle anzeigen
    if(!isset($showTable)) $showTable=true;

    $inputs=array(	  array('label'=>'Bezeichnung*','name'=>'DATA_bezeichnung','type'=>'text','class'=>'data'),
                      array('label'=>'Zutaten*','name'=>'DATA_zutaten','type'=>'text','class'=>'zutaten'),
                      array('label'=>'Preis*','name'=>'DATA_preis','type'=>'text','class'=>'preis'),
                      array('label'=>'Foto','name'=>'DATA_foto','type'=>'file','accept'=>'foto/jpeg, foto/png,
                      foto/gif','class'=>'bild'));


    if(!isset($errors)) $errors=array();
    for($i=0;$i<count($inputs);$i++) $errors[$i]='';

    // Verarbeitung

    // Speichern gedrückt
    if(isset($_POST['btnSave']))
    {
        // Fehlerüberprüfung
        $errors=error_check($inputs);

        // existiert Gericht
        if($_POST['DATA_bezeichnung']!='')
        {
            $query="SELECT dtBezeichnung FROM tblGericht
                    WHERE dtBezeichnung LIKE '".$_POST['DATA_bezeichnung']."'
                    AND fi_kategorie=".$_POST['kategorie'];
            if(($showFormEdit)&&(valid_id('tblGericht','id_gericht',$_POST['id'])))
                $query.=" AND id_gericht<>".$db->real_escape_string($_POST['id']);
            $result=mysqli_query($db,$query);
            if(mysqli_num_rows($result)==1)
            {
                $errors[0]='Gericht existiert bereits!';
            }
        }
        // Preis überprüfen
        $temp=check_preis($_POST['DATA_preis']);
        $errors[2]=$temp[0];
        $_POST['DATA_preis']=$temp[1];

        // güötige Bild-Datei
        if((isset($_FILES['DATA_foto']))&&($_FILES['DATA_foto']['name']!=''))
        {
            $filename=$_FILES['DATA_foto']['name'];
            $file_ext=substr($filename,strpos($filename,'.')+1,strlen($filename)-strpos($filename,'.')-1);
            $validextensions=array('jpg','png','gif');
            // bei DATA_foto und Fileextension nicht in $validextensions enthalten, also ungültige Extension
            var_dump(array_search($file_ext,$validextensions));
            if(array_search($file_ext,$validextensions)===false)
                $errors[count($inputs)-1]='ungültige Bilddatei<br>gültig: *.jpg, *.png, *.gif';
        }

        //keine Fehler
        if(count(array_filter($errors,"leer"))==0)
        {
            if((isset($_FILES['DATA_foto']['name']))&&($_FILES['DATA_foto']!=''))
            {
                $filename = dirname($_SERVER['SCRIPT_FILENAME']).'/Pictures/gerichte/'.$_FILES['DATA_foto']['name'];
                if(!file_exists($filename))
                    resize_image($_FILES['DATA_foto']['name']);
            }
            // Gericht hinzufügen
            if($showFormAdd)
            {
                $query="INSERT INTO tblGericht (dtBezeichnung,dtZutaten,dtPreis,dtFoto,dtZeitstempel,fi_kategorie) ".
                        "VALUES ('".
                        db_update($_POST['DATA_bezeichnung'])."','".
                        db_update($_POST['DATA_zutaten'])."',".
                        db_update(str_replace(',','.',$_POST['DATA_preis'])).",'".
                        db_update($_FILES['DATA_foto']['name'])."','".
                        db_update(date('Y.m.d@H:i'))."',".
                        $kategorie.")";
                mysqli_query($db,$query);
                $showFormAdd=false;
            }
            // Gericht bearbeiten
            if($showFormEdit)
            {
                $query="UPDATE tblGericht SET ".
                    "dtBezeichnung='".db_update($_POST['DATA_bezeichnung'])."',".
                    "dtZutaten='".db_update($_POST['DATA_zutaten'])."',".
                    "dtPreis=".db_update(str_replace(',','.',$_POST['DATA_preis'])).",".
                    ($_FILES['DATA_foto']['name']!=''?"dtFoto='".db_update($_FILES['DATA_foto']['name'])."',":'').
                    "fi_kategorie=".db_update($kategorie).
                    " WHERE id_gericht=".$id;
                mysqli_query($db,$query);
                $showFormEdit=false;
            }
        }
        else
        {
            if(isset($_POST['id'])) $showFormEdit=true;
            else $showFormAdd=true;
        }
    }

    // Gericht löschen
    if((isset($_POST['btnYes']))&&(valid_id('tblGericht','id_gericht',$_POST['id'])))
    {
        $query="SELECT dtFoto FROM tblGericht WHERE id_gericht=".$_POST['id'];
        $result=mysqli_query($db,$query);
        // Datei löschen
        if(db_result($result,0,'dtFoto')!='')
        {
            $path=dirname($_SERVER['SCRIPT_FILENAME']).'/Pictures/gerichte/';
            unlink($path.db_result($result,0,'dtFoto'));
        }
        // aus der Datenbank löschen
        $query="DELETE FROM tblGericht WHERE id_gericht=".$_POST['id'];
        mysqli_query($db,$query);
    }

    // Ausgabe

    //Kategorien in der Sidebar
    ?>
    <div id="kategorie">
        <ul>
            <li>Kategorie</li>
            <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot&amp;kategorie=Alle'
                ?>"><span>Alle</span></a></li>
            <?php
            $query="SELECT * FROM tblKategorie";
            $result=mysqli_query($db,$query);

            for($i=0;$i<mysqli_num_rows($result);$i++)
            {
                ?>
                <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot&amp;kategorie='.
                    db_result($result,$i,'id_kategorie')?>"><span><?=db_result($result,$i,'dtBezeichnung')?></span></a></li>
            <?php
            }
            ?>
        </ul>
    </div>
    <?php
    // Gericht löschen
    if((isset($_GET['action']))&&($_GET['action']=='del')&&(isset($_GET['id']))&&(valid_id('tblGericht','id_gericht',
        $_GET['id'])))
    {
        $showTable=false;
        $query="SELECT * FROM tblGericht WHERE id_gericht=".$_GET['id'];
        $result=mysqli_query($db,$query);
        ?>
        <div class="deleteconfirm">
            <p>Sind sie sicher, dass Sie das Gericht<br><span>"<?=db_result($result,0,'dtBezeichnung')?>"</span><br>
                löschen wollen?</p>
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot'?>" method="post">
                <div class="btn_center">
                    <input type="submit" name="btnYes" value="Ja" class="yes">
                    <input type="submit" name="btnNo" value="Nein" class="no">
                </div>
                <input type="hidden" name="id" value="<?=$_GET['id']?>">
            </form>
            <span class="clearfix"></span>
        </div>
        <?php
    }
    if($showTable)
    {
    ?>
        <div class="angebot">
            <?php
            $query="SELECT * FROM tblKategorie".($kategorie!=''?' WHERE id_kategorie='.$kategorie:'');
            $result_all=mysqli_query($db,$query);
            for($i=0;$i<mysqli_num_rows($result_all);$i++)
            {
                ?>
                <div>
                <a class="link" href="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot&amp;kategorie='.
                    db_result($result_all,$i,'id_kategorie')?>">
                    <span class="ueberschrift"><?=db_result($result_all,$i,'dtBezeichnung')?></span>
                </a>
                <a class="add" href="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot&amp;action=add&amp;kategorie='.
                        db_result($result_all,$i,'id_kategorie')?>"><span>Gericht hinzufügen</span></a>

                </div>
                <hr>

                    <?php
                    $query="SELECT * FROM tblGericht WHERE fi_kategorie=".db_result($result_all,$i,'id_kategorie');
                    $result=mysqli_query($db,$query);
                    if(mysqli_num_rows($result)>0)
                    {
                        if((!$showFormAdd)&&(!$showFormEdit))
                        {
                        ?>
                            <div class="gerichttabelle">
                                <table class="tabelle">
                                    <tr>
                                        <th class="horizontal">Bezeichnung</th>
                                        <th class="horizontal">Zutaten</th>
                                        <th class="horizontal">Preis</th>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <?php
                                    for($j=0;$j<mysqli_num_rows($result);$j++)
                                    {
                                        ?>
                                        <tr<?=($j==mysqli_num_rows($result)-1?' class="extrema"':'')?>>
                                            <td><?=db_result($result,$j,'dtBezeichnung')?></td>
                                            <td><?=db_result($result,$j,'dtZutaten')?></td>
                                            <td><?=number_format(db_result($result,$j,'dtPreis'),2,',','.').'€'?></td>
                                            <td>
                                                <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot&amp;'.
                                                'action=edit&amp;kategorie='.db_result($result_all,$i,'id_kategorie').
                                                    '&amp;id='.db_result($result,$j,'id_gericht')?>">
                                                    <span>Bearbeiten</span>
                                                </a>
                                            </td>
                                            <td>
                                                <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot&amp;'.
                                                    'action=del&amp;id='.db_result($result,$j,'id_gericht')?>">
                                                    <span>Löschen</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </table>
                            </div>
                    <?php
                        }
                    }
                    else echo '<div class="gerichttabelle"><p>Es wurden leider keine Gerichte in dieser Kategorie '.
                                'gefunden.</p></div>';
                    ?>
            <?php
            }

            // Gericht hinzufügen/bearbeiten
            if($showFormAdd || $showFormEdit)
            {
                ?>
                <div class="gerichteverwalten">
                    <?php
                    if($showFormEdit)
                    {
                        $query="SELECT * FROM tblGericht WHERE id_gericht=".$id;
                        $result=mysqli_query($db,$query);
                        echo '<img src="Pictures/gerichte/'.db_result($result,0,'dtFoto').
                            '" alt="Foto nicht verfügbar">';
                    }
                    ?>
                    <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot'?>"><span class="cancel">Zurück</span></a>
                    <form enctype="multipart/form-data" action="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot'?>"
                          method="post">
                        <table>
                            <?php
                            $i=0;
                            foreach($inputs as $feld=>$properties)
                            {
                                ?>
                                <tr>
                                    <th class="vertikal"><?=$properties['label']?>:</th>
                                    <td>
                                        <?php
                                        if(isset($_POST[$properties['name']]))
                                        {
                                            $value=$_POST[$properties['name']];
                                            if(($properties['name']=='DATA_preis')&&($value!=''))
                                                $value=number_format($value,2,',','.');
                                        }
                                        else
                                        {
                                            if($showFormEdit)
                                            {
                                                if($properties['name']!='DATA_foto')
                                                {
                                                    $value=db_result($result,0,'dt'.ucfirst(substr($properties['name'],
                                                    strpos($properties['name'],'_')+1)));
                                                    if($properties['name']=='DATA_preis')
                                                        $value=number_format(db_result($result,0,'dtPreis'),2,',','.');
                                                }
                                                else $value='';
                                            }
                                        }

                                        echo '<input type="'.$properties['type'].'" class="'.($errors[$i]!=''?
                                            'redborder':$properties['class']).'" name="'.$properties['name'].
                                            '" value="'.$value.'"'.($properties['name']=='DATA_foto'?'accept="'.
                                            $properties['accept'].'"':'').'>';
                                        if($properties['name']=='DATA_preis') echo '<span class="euro">€</span>';
                                        ?>
                                    </td>
                                    <?php
                                    if((isset($errors))&&(count(array_filter($errors))>0))
                                    echo '<td class="error"><span class="redtxt">'.$errors[$i].'</span></td>';
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
                        <?php
                        if($id!='')
                            echo '<input type="hidden" name="id" value="'.$id.'">';
                        if($kategorie!='')
                            echo '<input type="hidden" name="kategorie" value="'.$kategorie.'">';
                        ?>
                    </form>
                </div>
                <span class="required">Felder mit * müssen ausgefüllt sein.</span>
            <?php
            }
            ?>
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