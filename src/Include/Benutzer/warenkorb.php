<?php
//Eingabe
if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin']=0;
// als User eingeloggt
if(($_SESSION['loggedin']==1)&&(isset($_SESSION['id_user'])))
{
    // Eingabe
    if(!isset($_SESSION['Payment_Amount'])) $_SESSION['Payment_Amount']=0;
    // Verarbeitung

    // in den Warenkorb
    if((isset($_GET['action']))&&($_GET['action']=='add')&&($id!=''))
    {
        if(!isset($_SESSION['warenkorb'])) $_SESSION['warenkorb']=array();
        if(array_key_exists($id,$_SESSION['warenkorb']))
            $_SESSION['warenkorb'][$id]++;
        else $_SESSION['warenkorb'][$id]=1;
    }

    // Warenkorb Anzahl -
    if(($id!='')&&(isset($_GET['action']))&&($_GET['action']=='sub')&&($_SESSION['warenkorb'][$id]>1))
         $_SESSION['warenkorb'][$id]--;

    //aus dem Warenkorb entfernen
    if((isset($_GET['action']))&&($_GET['action']=='rmv')&&($id!=''))
        unset($_SESSION['warenkorb'][$id]);

    $zeit=array();
    $datum=array();
    //array mit gültigen Daten(Datum) füllen
    $tag=intval(date('d'));
    $monat=intval(date('m'));
    $jahr=intval(date('Y'));

    // 10 gültige Tage im vorraus im array datum speichern
    while(count($datum)<10)
    {
        $akt_tag=($tag<10?'0'.$tag:$tag).'.'.($monat<10?'0'.$monat:$monat).'.'.$jahr;
        $found=opened_day($akt_tag);
        if(($found[0]!='')&&($found[1]!=''))
            $datum[]=$akt_tag;
        // sprung auf nächsten monat
        if(($monat!=2)&&($monat!=7)) //nicht Februar und nicht July
        {
            // Monat mit 31 oder Monat mit 30 Tage
            if((($monat%2==0)&&($tag==31))||(($monat%2==1)&&($tag==30)))
            {
                $tag=0;
                if($monat!=12)  //monat nicht Dezember
                    $monat++;   //monat+1
                else
                {
                    $monat=1;   //monat=Januar
                    $jahr++;    //jahr+1
                }
            }
        }
        else
        {
            //wenn Schaltjahr => Februar 29Tage
            if(($jahr%4==0)&&($jahr%100)&&($jahr&400))
            {
                if(($tag==29)&&($monat==2))     //29. Februar
                {
                    $tag=0;
                    $monat++;
                }
            }
            //wenn July (31 Tage)
            if(($monat==7)&&($tag==31))
            {
                $tag=0;
                $monat++;
            }
        }
        $tag++;
    }

    // Zeitmöglichkeiten des ausgewählen Tages definieren
    $found=opened_day($datum[0]);
    $zeit_von=$found[0];
    $zeit_bis=$found[1];

    // heute möglich test
    $today_ok=($datum[0]==date('d.m.Y'));

    // Lieferdatum & Lieferzeit auf 1. Position initialisieren
    if(!isset($_SESSION['lieferdatum'])) $_SESSION['lieferdatum']=$datum[0];

    // Lieferdatum ausgewählt
    if(isset($_POST['DATA_lieferdatum']))
    {
        $_SESSION['lieferdatum']=$datum[$_POST['DATA_lieferdatum']];
        $found=opened_day($datum[$_POST['DATA_lieferdatum']]);
        $zeit_von=$found[0];
        $zeit_bis=$found[1];
    }

    // heute möglich -> bei aufgerundener aktueller zeit anfangen
    if(($today_ok)&&(timetofloat(roundup_aktzeit())>=timetofloat($zeit_von)))
    {
        if((!isset($_POST['DATA_lieferdatum']))||((isset($_POST['DATA_lieferdatum']))&&
            ($_POST['DATA_lieferdatum']==0)))
        {
            //  aktuelle Zeit aufrunden + 30min setzen
            $temp=roundup_aktzeit();
        }
        else
        {
            // auf zeit_von setzen
            $temp=$zeit_von;
        }
    }
    else
    {
        // heute nicht möglich -> temp=zeit_von
        $temp=$zeit_von;
    }

    $i=0;
    $h=intval(substr($temp,0,strpos($temp,':')));
    $m=intval(substr($temp,strpos($temp,':')+1));
    while(timetofloat($temp)<timetofloat($zeit_bis))
    {
        $zeit[$i]=($h<10?'0'.$h:$h).':'.($m<10?'0'.$m:$m);
        $temp=$zeit[$i];

        $m+=30;
        $i++;
        if($m==60)
        {
            $m=0;
            $h++;
        }
    }

    $zeit[0]=$temp;

    // Lieferzeit ausgewählt
    if(isset($_POST['DATA_lieferzeit']))
        $_SESSION['lieferzeit']=$zeit[$_POST['DATA_lieferzeit']];
    else $_SESSION['lieferzeit']=$zeit[0];

    // Lieferart (Liefern/selbst Abholen) auswählen
    if(!isset($_SESSION['lieferart'])) $_SESSION['lieferart']=1;
    if((isset($_POST['DATA_lieferart']))&&(($_POST['DATA_lieferart']==0)||($_POST['DATA_lieferart']==1)))
        $_SESSION['lieferart']=$_POST['DATA_lieferart'];

    $init_zeit=roundup_aktzeit();
    if(timetofloat($init_zeit)>timetofloat($zeit_bis)) $init_zeit=$zeit_bis;
    // Bestellungsversuch nach Mitternacht -> zeit = zeit_von
    if(timetofloat($init_zeit)<timetofloat($zeit_von)) $init_zeit=$zeit_von;

    // INI-Datei laden
    $filename=dirname($_SERVER['SCRIPT_FILENAME']).'/Include/Restaurant/informationen.ini.php';
    $einstellungen=parse_ini_file($filename);

    // Ausgabe
    ?>
    <div class="shoppingCart">
        <h3 class="warenkorb">Warenkorb</h3>
        <?php
        if(isset($_SESSION['warenkorb']))
        {
            ?>
            <table>
                <?php
                $i=0;
                $_SESSION['Payment_Amount']=0;
                foreach($_SESSION['warenkorb'] as $fi_gericht=>$quantity)
                {
                    $query="SELECT * FROM tblGericht WHERE id_gericht=".$fi_gericht;
                    $result=mysqli_query($db,$query);
                    ?>
                    <tr>
                        <td class="quantity">
                            <?=($_SESSION['warenkorb'][$fi_gericht]>1?
                                '<a href="'.$_SERVER['SCRIPT_NAME'].'?page=bestellen&amp;action=sub&amp;id='.
                                    $fi_gericht.($kategorie!=''?'&amp;kategorie='.$kategorie:'')
                                    .($search!=''?'&amp;search='.$search:'').'">'.'<img class="plusminus" '.
                                    'src="Pictures/design/minus_button.png" alt="Verringern"></a>':'')?>
                            <span class="quantity"><?=$quantity?></span>
                            <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen&amp;action=add&amp;id='.
                                $fi_gericht.($kategorie!=''?'&amp;kategorie='.$kategorie:'')?>">
                                <img src="Pictures/design/plus_button.png" alt="Erhöhen"></a>
                        </td>

                        <td><?=db_result($result,0,'dtBezeichnung')?></td>
                        <td>
                            <?php $preis=$quantity*floatval(str_replace(',','.',db_result($result,0,'dtPreis'))); ?>
                            <span><?=number_format($preis,2,',','.').'€'?></span>
                        </td>
                        <td>
                            <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="post">
                                <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen&amp;action=rmv&amp;id='.
                                    $fi_gericht.($kategorie!=''?'&amp;kategorie='.$kategorie:'')?>">
                                    <img src="Pictures/design/delete_icon.png" alt="Entfernen"></a>
                            </form>
                        </td>
                    </tr>
                    <?php
                    $_SESSION['Payment_Amount']+=$preis;
                    $i++;
                }
                $_SESSION["Payment_Amount"] += $_SESSION['lieferart']*$einstellungen['lieferkosten'];
                ?>
            </table>
        <?php
        }
        ?>
        <span class="lieferkosten">Lieferkosten: <?=number_format($_SESSION['lieferart']*$einstellungen['lieferkosten'],
                2,',','.').'€'?></span>
        <span class="clearfix"></span>
        <hr>
        <div>
            <span class="total">Total : <?=number_format($_SESSION['Payment_Amount'],2,',','.').'€'?></span>
            <span class="clearfix"></span>
        </div>
        <div class="lieferung">
            <span class="ueberschrift">gewünschter Liefertermin:</span>
            <?php if(!$today_ok) echo '<p>für heute nicht möglich</p>'; ?>
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen'?>" method="post">
                <table class="dropdown">
                    <tr>
                        <td>
                            <select onchange="submit()" name="DATA_lieferdatum">
                                <?php
                                for($i=0;$i<count($datum);$i++)
                                {
                                    echo '<option '.(((isset($_POST['DATA_lieferdatum']))&&
                                        ($datum[$_POST['DATA_lieferdatum']]==$datum[$i]))?
                                        'selected="selected" ':'').' value="'.$i.'">'.
                                        $wochentage[intval(date('N',strtotime($datum[$i])))-1].
                                        ' - '.$datum[$i].'</option>'."\n";
                                }
                                ?>
                            </select>
                        </td>
                        <td>um</td>
                        <td>
                            <select name="DATA_lieferzeit" onchange="submit()">
                                <?php
                                if(isset($_POST['DATA_lieferzeit']))
                                    $selected=$zeit[$_POST['DATA_lieferzeit']];
                                else $selected=$init_zeit;
                                for($i=0;$i<count($zeit);$i++)
                                {
                                    echo '<option '.($selected==$zeit[$i]?'selected="selected"':'').
                                        ' value="'.$i.'">'.$zeit[$i].'</option>'."\n";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            $lieferart=array('selbst Abholung','Lieferung');
                            ?>

                            <select name="DATA_lieferart" onchange="submit()">
                                <?php
                                for($i=count($lieferart)-1;$i>=0;$i--)
                                    echo '<option value="'.$i.'"'.(((isset($_POST['DATA_lieferart']))&&
                                        ($_POST['DATA_lieferart']==$i))?' selected="selected"':'').'>'.$lieferart[$i].
                                        '</option>'."\n";
                                ?>
                            </select>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </form>
        </div>
            <div class="btn_center">
                <?php
                if((isset($_SESSION['warenkorb']))&&(count($_SESSION['warenkorb'])>0))
                {
                    ?>
                    <a href="bezahlen/index.php?page=<?=($_SESSION['lieferart']==1?'lieferadresse':'zahlungsart')?>">
                        <img src="Pictures/design/paypal_paynowCC_LG.gif" alt="Jetzt bezahlen mit PayPal"></a>
                <?php
                }
                else
                {
                    ?>
                    <img src="Pictures/design/paypal_paynowCC_LG.gif" alt="Jetzt bezahlen mit PayPal"><br>
                    <span class="required">Ihr Warenkorb ist leer.</span>
                <?php
                }
                ?>
            </div>
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