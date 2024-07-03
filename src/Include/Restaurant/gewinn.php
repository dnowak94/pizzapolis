<?php
if((isset($_SESSION['restaurant']))||((isset($_SESSION['cmsloggedin']))&&($_SESSION['cmsloggedin']==1)))
{
    // Eingabe
    $filename=str_replace('/cms','',dirname($_SERVER['SCRIPT_FILENAME'])).'/Include/Restaurant/informationen.ini.php';
    $einstellungen=parse_ini_file($filename);
    $letztes_datum= new DateTime($einstellungen['letzte_gewinnauswertung']);

    if(!isset($errors)) $errors=array();

    // Kraftstoffkosten berechnen
    $query= "SELECT SUM(dtKosten) AS 'kraftstoffkosten'
             FROM tblKraftstoffkosten
             WHERE dtDatum>='".$letztes_datum->format('Y-m-d')."' AND dtDatum<'".date('Y-m-d')."'
             HAVING SUM(dtKosten) IS NOT NULL";
    $result=mysqli_query($db,$query);

    if(mysqli_num_rows($result)>0)
        $kraftstoffkosten=db_result($result,0,'kraftstoffkosten');
    else $kraftstoffkosten=0;

    if(!isset($ausgaben)) $ausgaben=$kraftstoffkosten;

    // Eingabe-Felder
    $inputs=array(  array('Betriebskosten:',0),
                    array('Lieferwagen',''),
                    array('Kraftstoffkosten',$kraftstoffkosten),
                    array('',''),
                    array('',''),
                    array('soziale Kosten und Steuern:',0),
                    array('Sozialversicherung',''),
                    array('Personal',''),
                    array('Steuern',''),
                    array('restliche Kosten',''));
    // Verarbeitung
    // Ausgaben berechnen
    if(isset($_POST['btnAusgaben']))
    {
        $ausgaben=0;
        foreach($_POST as $feld=>$value)
        {
            if($feld!='btnAusgaben')
            {
                if($_POST[$feld]=='') $value=0;
                $value=str_replace(',','.',$value);
                // Fehlerüberprüfen
                if(is_numeric($value))
                {
                    $errors[$feld]='';
                }
                else $errors[$feld]='!';
                // Berechnen
                if($errors[$feld]=='')
                {
                    $ausgaben+=$value;

                    // nach Feld suchen zum Wert speichern
                    $found=false;
                    $i=0;
                    while((!$found)&&($i<count($inputs)))
                    {
                        // Felder im Array in die gleiche Namen wie die Input-Felder umwandeln
                        if(strpos($inputs[$i][0],':')>0)
                            $feldname=substr($inputs[$i][0],0,strpos($inputs[$i][0],':'));
                        else $feldname=$inputs[$i][0];
                        // Leerzeichen durch '_' ersetzten
                        $feldname='DATA_'.str_replace(' ','_',strtolower($feldname));
                        // Feldname von POST = Feldname in Array -> Wert in Array speichern
                        if($feld==$feldname)
                        {
                            $found=true;
                            $inputs[$i][1]=$value;
                        }
                        $i++;
                    }
                }
            }
        }
    }

    // Ausgabe
?>
    <div class="gewinn">
        <?php
        $path=str_replace('/cms','',dirname($_SERVER['SCRIPT_NAME']));
        if(count(array_filter($errors,"leer"))==0)
        {
            ?>
            <div class="right">
                <div class="btn_center">
                    <form action="<?=$path?>/Include/Restaurant/gewinn_pdf.php" method="post">
                        <input type="image" src="<?=$path?>/Pictures/design/pdf_button_up.gif" alt="Als PDF speichern">
                        <?php
                        // Input-Array in Text-Form ("implode")
                        $text='';
                        for($i=0;$i<count($inputs);$i++) $text.=implode(';',$inputs[$i]).'/';
                        ?>
                        <input type="hidden" name="inputs" value="<?=$text?>"><br>
                        <span>Als PDF speichern</span>
                    </form>
                </div>
            </div>
        <?php
        }
        ?>
        <h1>Gewinn vom <?=$letztes_datum->format('d.m.Y').' bis '.date('d.m.Y')?></h1>
        <table class="gewinn">
            <tr>
                <td>
                    <table class="gewinn">
                        <tr>
                            <th class="level2" colspan="3">Einnahmen</th>
                        </tr>
                        <tr>
                            <th class="ueberschrift" colspan="3"><a href="<?=$_SERVER['SCRIPT_NAME'].
                                    '?page=verkauf'?>">verkaufte Produkte:</a></th>
                        </tr>
                        <tr>
                            <th class="theader">Kategorie</th>
                            <th class="theader">Anzahl Gerichte</th>
                            <th class="theader">Ertrag</th>
                        </tr>

                        <?php
                        // Tabelle mit der gruppierten Anzahl der verkauften Produkte
                        $query="SELECT * FROM tblKategorie";
                        $result_all=mysqli_query($db,$query);
                        $gerichte=0;
                        for($i=0;$i<mysqli_num_rows($result_all);$i++)
                        {
                            $query="SELECT SUM(dtQuantitaet) AS `dtQuantitaet`,
                                    ROUND(SUM(dtQuantitaet*dtPreis),2) AS `dtPreis`
                                    FROM tblBestellung
                                    LEFT JOIN tblBestehen_aus ON id_bestellung=fi_bestellung
                                    LEFT JOIN tblGericht ON fi_gericht=id_gericht
                                    WHERE ((dtLieferdatum>='".$letztes_datum->format('Y-m-d')."' AND dtLieferdatum<'".
                                    date('Y-m-d')."'))
                                    AND fi_kategorie=".db_result($result_all,$i,'id_kategorie').
                                    " GROUP BY fi_kategorie
                                    ORDER BY dtPreis DESC";
                            $result=mysqli_query($db,$query);
                            if(mysqli_num_rows($result)>0)
                            {
                                $anzahl=db_result($result,0,'dtQuantitaet');
                                $preis=db_result($result,0,'dtPreis');
                            }
                            else
                            {
                                $anzahl=0;
                                $preis=0;
                            }
                            $preis=floatval($preis);
                            ?>
                            <tr>
                                <td><span class="klein"><?=db_result($result_all,$i,'dtBezeichnung')?></span></td>
                                <td class="center"><span class="klein"><?=$anzahl?></span></td>
                                <td class="right"><span class="klein"><?=number_format($preis,2,',','.')?>€</span>
                                </td>
                            </tr>
                            <?php
                            $gerichte+=$preis;
                        }
                        ?>
                        <tr>
                            <th class="ueberschrift" colspan="2">Gesamt:</th>
                            <td class="right"><?=number_format($gerichte,2,',','.')?>€</td>
                        </tr>
                        <tr>
                            <th class="ueberschrift" colspan="2">Einnahmen durch Lieferungen:</th>
                            <?php

                            $query= "SELECT SUM(dtLieferart*".$einstellungen['lieferkosten'].") AS 'liefereinnahmen'
                                    FROM tblBestellung
                                    WHERE ((dtLieferdatum>='".$letztes_datum->format('Y-m-d')."' AND dtLieferdatum<'".
                                    date('Y-m-d')."'))";
                            $result=mysqli_query($db,$query);
                            $lieferungen=floatval(db_result($result,0,'liefereinnahmen'));
                            $einnahmen=$gerichte+$lieferungen;
                            ?>
                            <td class="right"><?=number_format($lieferungen,2,',','.')?>€</td>
                        </tr>
                        <tr>
                            <th class="ueberschrift" colspan="2">Gesamteinahmen:</th>
                            <td class="right"><?=number_format($einnahmen,2,',','.')?>€</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=gewinn'?>" method="post">
                        <table class="gewinn">
                            <tr><th class="level2" colspan="3">Ausgaben</th></tr>
                            <?php
                            for($i=0;$i<count($inputs);$i++)
                            {
                                ?>
                                <tr>
                                    <?php
                                    // Überschriften
                                    if(strpos($inputs[$i][0],':')>0)
                                    {
                                        echo '<th class="ueberschrift">'.$inputs[$i][0].'</th>'."\n";
                                    }
                                    else
                                    {
                                        // Leerzeile
                                        if(($inputs[$i][0]=='')&&($inputs[$i][1]==''))
                                        {
                                            ?>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        <?php
                                        }
                                        else
                                        {
                                            echo '<td>'.$inputs[$i][0].'</td>'."\n";

                                            // Feldname erzeugen
                                            $feldname='DATA_'.str_replace(' ','_',strtolower($inputs[$i][0]));

                                            // wenn > 0 -> number_format andernfalls leerzeichen
                                            $value=($inputs[$i][1]>0?number_format($inputs[$i][1],2,',','.'):'');

                                            echo '<td><input type="text" class="feld'.(((isset($errors[$feldname]))
                                                &&($errors[$feldname]!=''))?' redborder':'').'" value="'.
                                                (isset($_POST[$feldname])?$_POST[$feldname]:$value).
                                                '" name="'.$feldname.'"></td>'."\n";
                                        }
                                    }
                                    echo "\n";
                                    ?>
                                    <td>&nbsp;</td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <th class="ueberschrift">Gesamtausgaben:</th>
                                <td class="right"><?=(isset($ausgaben)?number_format($ausgaben,2,',','.').'€':'')?></td>
                            </tr>
                        </table>
                        <div class="btn_center">
                            <input class="button" type="submit" name="btnAusgaben" value="Ausgaben berechnen">
                        </div>
                    </form>
                </td>
            </tr>
        </table>
        <table class="gewinn mitte">
            <tr>
                <th class="level2">Gewinn:</th>
                <td class="right"><?=number_format($einnahmen-$ausgaben,2,',','.').'€'?></td>
            </tr>
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