<?php
if(isset($_SESSION['restaurant']))
{
    // Eingabe
    $filename=dirname($_SERVER['SCRIPT_FILENAME']).'/Include/Restaurant/informationen.ini.php';
    $einstellungen=parse_ini_file($filename);
    $letztes_datum= new DateTime($einstellungen['letzte_gewinnauswertung']);
    // Verarbeitung
    // Ausgabe
    ?>
    <div class="verkauf">
        <div class="right">
            <div class="btn_center">
                <a class="link" href="Include/Restaurant/verkaufte_produkte_pdf.php">
                    <img src="Pictures/design/pdf_button_up.gif" alt="als PDF speichern"><br>
                    <span class="link">Als PDF speichern</span>
                </a>
                <span class="clearfix"></span>
            </div>
        </div>
        <h1>
            verkaufte Produkte<br>
            vom <?=date('d.m.Y',strtotime($einstellungen['letzte_gewinnauswertung']))?>
            bis <?=date('d.m.Y')?>
        </h1>
        <table class="gewinn">
            <tr>
                <th class="ueberschrift">Produkt</th>
                <th class="ueberschrift">Zutaten</th>
                <th class="ueberschrift">Anzahl</th>
                <th class="ueberschrift">Einnahmen</th>
            </tr>
            <?php
            $query=" SELECT *
                     FROM tblBestellung
                     WHERE dtLieferdatum>='".$letztes_datum->format('Y-m-d')."' AND dtLieferdatum<'".date('Y-m-d')."'";
            $result=mysqli_query($db,$query);

            $gesamt=0;
            if(mysqli_num_rows($result)>0)
            {
                $query="SELECT tblGericht.dtBezeichnung,dtZutaten,SUM(tblBestehen_aus.dtQuantitaet) AS 'anzahl',
                        SUM(dtQuantitaet*dtPreis) AS 'dtPreis'
                        FROM tblGericht
                        LEFT OUTER JOIN tblBestehen_aus ON id_gericht=fi_gericht
                        LEFT OUTER JOIN tblBestellung ON fi_bestellung=id_bestellung
                        AND dtLieferdatum>='".$letztes_datum->format('Y-m-d')."' AND dtLieferdatum<'".date('Y-m-d').
                        "' GROUP BY id_gericht
                        ORDER BY anzahl DESC,dtPreis DESC";
                $result=mysqli_query($db,$query);
                for($i=0;$i<mysqli_num_rows($result);$i++)
                {
                    ?>
                    <tr>
                        <td><span class="klein"><?=db_result($result,$i,'dtBezeichnung')?></span></td>
                        <td><span class="klein"><?=db_result($result,$i,'dtZutaten')?></span></td>
                        <td class="center"><span class="klein"><?=(db_result($result,$i,'anzahl')!=NULL?
                                    db_result($result,$i,'anzahl'):0)?></span></td>
                        <td class="right"><span class="klein"><?=(db_result($result,$i,'dtPreis')!=NULL?
                                    number_format(db_result($result,$i,'dtPreis'),2,',','.'):0)?>€</span></td>
                    </tr>
                    <?php
                    $gesamt+=floatval(db_result($result,$i,'dtPreis'));
                }
            }
            else
            {
                $query="SELECT * FROM tblGericht";
                $result=mysqli_query($db,$query);
                for($i=0;$i<mysqli_num_rows($result);$i++)
                {
                    ?>
                    <tr>
                        <td><span class="klein"><?=db_result($result,$i,'dtBezeichnung')?></span></td>
                        <td><span class="klein"><?=db_result($result,$i,'dtZutaten')?></span></td>
                        <td class="center"><span class="klein">0</span></td>
                        <td class="right"><span class="klein">0€</span></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
        <span class="total">Gesamt: <?=number_format($gesamt,2,',','.')?>€</span>
        <span class="clearfix"></span>
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