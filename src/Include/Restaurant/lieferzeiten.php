<?php
if(isset($_SESSION['restaurant']))
{
    // Eingabe

    // Zeitmöglichkeiten definieren
    $h=8;
    $m=0;
    while($h<24)
    {
        if($m==60)
        {
            $m=0;
            $h++;
        }
        $zeit[]=($h<10?'0'.$h:$h).":".($m<10?'0'.$m:$m);
        $m+=30;
    }

    $week=array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');

    // speichern welche Reihe geändert
    if(!isset($ShowErrors)) $showErrors=false;


    // Verarbeitung

    // Lieferzeit einer Reihe ändern/hinzufügen
    if(isset($_POST['btnSave']))
    {
        // Tag von > Tag bis -> vertauschen
        if($_POST['DATA_tag_von']>$_POST['DATA_tag_bis'])
        {
            $temp=$_POST['DATA_tag_von'];
            $_POST['DATA_tag_von']=$_POST['DATA_tag_bis'];
            $_POST['DATA_tag_bis']=$temp;
        }

        // Zeit von > Zeit bis -> vertauschen
        if($_POST['DATA_zeit_von']>$_POST['DATA_zeit_bis'])
        {
            $temp=$_POST['DATA_zeit_von'];
            $_POST['DATA_zeit_von']=$_POST['DATA_zeit_bis'];
            $_POST['DATA_zeit_bis']=$temp;
        }

        // POST id gibt es nicht -> hinzufügen
        if(!isset($_POST['id']))
        {
            // hinzufügen
            $query= "INSERT INTO tblLieferzeit (dtTag_von,dtTag_bis,dtZeit_von,dtZeit_bis,fi_verwalter)
                    VALUES(".
                    "'".$week[$_POST['DATA_tag_von']]."',".
                    "'".$week[$_POST['DATA_tag_bis']]."',".
                    "'".$zeit[$_POST['DATA_zeit_von']]."',".
                    "'".$zeit[$_POST['DATA_zeit_bis']]."',".
                    $_SESSION['restaurant'].")";
            mysqli_query($db,$query);
        }
        else
        {
            // ändern
            $query= "UPDATE tblLieferzeit SET ".
                    "dtTag_von='".$week[$_POST['DATA_tag_von']]."',".
                    "dtTag_bis='".$week[$_POST['DATA_tag_bis']]."',".
                    "dtZeit_von='".$zeit[$_POST['DATA_zeit_von']]."',".
                    "dtZeit_bis='".$zeit[$_POST['DATA_zeit_bis']]."',".
                    "fi_verwalter=".$_SESSION['restaurant'].
                    " WHERE id_lieferzeit=".$_POST['id'];
            mysqli_query($db,$query);
        }
    }

    // Lieferzeit Reihe löschen
    if((isset($_POST['btnYes']))&&(valid_id('tblLieferzeit','id_lieferzeit',$_POST['id'])))
    {
        $query="DELETE FROM tblLieferzeit WHERE id_lieferzeit=".$_POST['id'];
        mysqli_query($db,$query);
    }

    // Ausgabe

    // Löschen bestätigen
    if((isset($_POST['btnDelete']))&&(valid_id('tblLieferzeit','id_lieferzeit',$_POST['id'])))
    {
        $query="SELECT * FROM tblLieferzeit WHERE id_lieferzeit=".$_POST['id'];
        $result=mysqli_query($db,$query);
        ?>
        <div class="deleteconfirm">
            <p>Sind sie sicher, dass Sie den Eintrag<br><span>"<?=$wochentage[$_POST['DATA_tag_von']].' bis '.
                        $wochentage[$_POST['DATA_tag_bis']].' '.$zeit[$_POST['DATA_zeit_von']].' bis '.
                        $zeit[$_POST['DATA_zeit_bis']]?>"</span><br>löschen wollen?</p>
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferzeiten'?>" method="post">
                <div class="btn_center">
                    <input type="submit" name="btnYes" value="Ja">
                    <input type="submit" name="btnNo" value="Nein">
                </div>
                <input type="hidden" name="id" value="<?=$_POST['id']?>">
            </form>
            <span class="clearfix"></span>
        </div>
    <?php
    }

    // Lieferzeiten ausgeben
    $query="SELECT * FROM tblLieferzeit";
    $result=mysqli_query($db,$query);
    ?>
    <div class="lieferzeiten">
        <h1>Lieferzeiten</h1>
        <table class="tabelle">
            <tr>
                <th class="horizontal">
                    <span class="th-center">Wochentage</span>
                    <span class="th-center">Uhrzeiten</span>
                    <a class="add" href="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferzeiten&amp;action=add'?>">
                        <span>Hinzufügen</span></a>
                </th>
            </tr>
            <?php
            // Hinzufügen
            if((isset($_GET['action']))&&($_GET['action']=='add'))
            {
                ?>
                <tr class="extrema">
                    <td>
                        <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferzeiten'?>" method="post">
                            <select name="DATA_tag_von">
                                <?php
                                for($j=0;$j<count($wochentage);$j++)
                                {
                                    echo '<option '.($wochentage[$j]==(isset($_POST['DATA_tag_von'])?
                                        $wochentage[$_POST['DATA_tag_von']]:'Montag')?'selected="selected" ':'').
                                        'value="'.$j.'">'.$wochentage[$j].'</option>'."\n";
                                }
                                ?>
                            </select>
                            <span> bis </span>
                            <select name="DATA_tag_bis">
                                <?php
                                for($j=0;$j<count($wochentage);$j++)
                                {
                                    echo '<option '.($wochentage[$j]==(isset($_POST['DATA_tag_bis'])?
                                        $wochentage[$_POST['DATA_tag_bis']]:'Sonntag')?'selected="selected" ':'').
                                        'value="'.$j.'">'.$wochentage[$j].'</option>'."\n";
                                }
                                ?>
                            </select>

                            <select name="DATA_zeit_von">
                                <?php
                                for($j=0;$j<count($zeit);$j++)
                                {
                                    echo '<option '.($zeit[$j]==(isset($_POST['DATA_zeit_von'])?
                                        $zeit[$_POST['DATA_zeit_von']]:'08:00')?'selected="selected" ':'').
                                        'value="'.$j.'">'.$zeit[$j].'</option>'."\n";
                                }
                                ?>
                            </select>
                            <span> bis </span>
                            <select name="DATA_zeit_bis">
                                <?php
                                for($j=0;$j<count($zeit);$j++)
                                {
                                    echo '<option '.($zeit[$j]==(isset($_POST['DATA_zeit_bis'])?
                                        $zeit[$_POST['DATA_zeit_bis']]:'22:00')?'selected="selected" ':'').
                                        'value="'.$j.'">'.$zeit[$j].'</option>'."\n";
                                }
                                ?>
                            </select>
                            <input type="submit" name="btnSave" value="Hinzufügen">
                            <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferzeiten'?>">
                                <span>Abbrechen</span></a>
                        </form>
                    </td>
                </tr>
                <tr class="extrema">
                    <td><hr></td>
                </tr>
            <?php
            }

            // Einträge anzeigen
            for($i=0;$i<mysqli_num_rows($result);$i++)
            {
                ?>
                <tr<?=($i==mysqli_num_rows($result)-1?' class="extrema"':'')?>>
                    <td>
                        <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferzeiten'?>" method="post">
                            <select name="DATA_tag_von">
                                <?php
                                for($j=0;$j<count($wochentage);$j++)
                                {
                                    echo '<option '.(db_result($result,$i,'dtTag_von')==$week[$j]?
                                        'selected="selected" ':'').'value="'.$j.'">'.$wochentage[$j].'</option>'."\n";
                                }
                                ?>
                            </select>
                            <span> bis </span>
                            <select name="DATA_tag_bis">
                                <?php
                                for($j=0;$j<count($wochentage);$j++)
                                {
                                    echo '<option '.(db_result($result,$i,'dtTag_bis')==$week[$j]?
                                        'selected="selected" ':'').'value="'.$j.'">'.$wochentage[$j].'</option>'."\n";
                                }
                                ?>
                            </select>

                            <select name="DATA_zeit_von">
                                <?php
                                for($j=0;$j<count($zeit);$j++)
                                {
                                    echo '<option '.(db_result($result,$i,'dtZeit_von')==$zeit[$j]?
                                        'selected="selected" ':'').'value="'.$j.'">'.$zeit[$j].'</option>'."\n";
                                }
                                ?>
                            </select>
                            <span> bis </span>
                            <select name="DATA_zeit_bis">
                                <?php
                                for($j=0;$j<count($zeit);$j++)
                                {
                                    echo '<option '.(db_result($result,$i,'dtZeit_bis')==$zeit[$j]?
                                        'selected="selected" ':'').'value="'.$j.'">'.$zeit[$j].'</option>'."\n";
                                }
                                ?>
                            </select>
                            <input type="hidden" name="id" value="<?=db_result($result,$i,'id_lieferzeit')?>">
                            <input type="submit" name="btnSave" value="Speichern">
                            <?php
                            if(mysqli_num_rows($result)>1)
                                echo '<input type="submit" name="btnDelete" value="Entfernen">';
                            ?>
                        </form>
                    </td>
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
    if($pieces[count($pieces)-3]=='Include')
    {
        $url=(isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].str_replace('/'.
            $pieces[count($pieces)-3].'/'.$pieces[count($pieces)-2],'',dirname($_SERVER['SCRIPT_NAME']));
        header('Location:'.$url);
    }
}
?>