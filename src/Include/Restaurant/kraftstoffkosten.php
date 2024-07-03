<?php
if(isset($_SESSION['restaurant']))
{
    // Eingabe
    if((isset($_GET['action']))&&($_GET['action']=='add')) $showFormAdd=true;
    else $showFormAdd=false;
    if(!isset($errormsg)) $errormsg='kein numerischer Wert!';
    if(!isset($errorLiter)) $errorLiter=false;
    if(!isset($errorWert)) $errorWert=false;
    $filename=dirname($_SERVER['SCRIPT_FILENAME']).'/Include/Restaurant/informationen.ini.php';
    $einstellungen=parse_ini_file($filename);
    // Verarbeitung
    if(isset($_POST['btnEintragen']))
    {
        $errorLiter=(($_POST['DATA_liter']=='')&&(!is_numeric($_POST['DATA_liter'])));
        $wert=check_preis($_POST['DATA_wert']);
        if($wert[0]!='') $errorWert=true;
        else $_POST['DATA_wert']=$wert[1];
        $wert=check_preis($_POST['DATA_liter']);
        if($wert[0]!='') $errorLiter=true;
        else $_POST['DATA_liter']=$wert[1];
        if((!$errorLiter)&&(!$errorWert))
        {
            // keine Fehler
            $query= "INSERT INTO tblKraftstoffkosten (dtDatum,dtLiter,dtKosten,dtAktuellerPreis,fi_verwalter)
                    VALUES(".
                    "'".date("Y-m-d")."',".
                    $_POST['DATA_liter'].",".
                    $_POST['DATA_wert'].",".
                    $einstellungen['kraftstoffpreis'].",".
                    $_SESSION['restaurant'].")";
            echo $query;
            //mysqli_query($db,$query);
        }
		else $showFormAdd=true;
    }
    // Ausgabe
    if($showFormAdd)
    {
        ?>
        <div class="kraftstoffkosten">
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=kraftstoffkosten'?>" method="post">
                <table>
                    <tr>
                        <th class="vertikal">Liter</th>
                        <td><input id="liter" class="feld <?=($errorLiter?' redborder':'')?>" type="text"
                                   name="DATA_liter" <?=(isset($_POST['DATA_liter'])?' value="'.$_POST['DATA_liter'].
                                '"':'')?> onchange="calcKraftstoffkosten(<?=$einstellungen['kraftstoffpreis']?>)"></td>
                    </tr>
                    <tr>
                        <th class="vertikal">Wert der Kraftstofftickets</th>
                        <td><input id="kosten" class="feld <?=($errorWert?' redborder':'')?>" type="text"
                           name="DATA_wert"<?=(isset($_POST['DATA_wert'])?' value="'.number_format($_POST['DATA_wert'],
                                2,',','').'"':'')?>></td>
                    </tr>
                </table>
                <div class="btn_center">
                    <input type="submit" name="btnEintragen" value="Eintragen">
                </div>
            </form>
            <span class="clearfix"></span>
        </div>
        <?php
    }
    ?>
    <div class="kraftstoffkosten">
        <h1>Kraftstoffkosten</h1>
        <a class="add" href="<?=$_SERVER['SCRIPT_NAME'].'?page=kraftstoffkosten&amp;action=add'?>">
            <span>Eintragen</span></a>
        <table class="tabelle">
            <tr>
				<th class="horizontal">Datum</th>
				<th class="horizontal">Liter</th>
				<th class="horizontal">Kosten</th>
				<th class="horizontal">Preis/Liter</th>
			</tr>
            <?php
            $filename=dirname($_SERVER['SCRIPT_FILENAME']).'/Include/Restaurant/informationen.ini.php';

            $einstellungen=parse_ini_file($filename);
            $letztes_datum= new DateTime($einstellungen['letzte_gewinnauswertung']);

            $query= "SELECT *
                    FROM tblKraftstoffkosten
                    WHERE dtDatum>='".$letztes_datum->format('Y-m-d')."'
                    ORDER BY dtDatum DESC";
            $result=mysqli_query($db,$query);
            for($i=0;$i<mysqli_num_rows($result);$i++)
            {
                ?>
                <tr>
                    <td><?=date('d.m.Y',strtotime(db_result($result,$i,'dtDatum')))?></td>
                    <td><?=number_format(db_result($result,$i,'dtLiter'),2,',','.')?>l</td>
                    <td><?=number_format(db_result($result,$i,'dtKosten'),2,',','.')?>€</td>
                    <td><?=number_format(db_result($result,$i,'dtAktuellerPreis'),3,',','.')?>€</td>
                </tr>
                <?php
            }
            ?>
            </table>
            <?php
            if(mysqli_num_rows($result)==0)
                echo '<span class="required">Für diesem Monat gibt es noch keine Einträge</span>';
            ?>
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