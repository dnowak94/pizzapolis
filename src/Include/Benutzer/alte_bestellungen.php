<?php
//Eingabe
if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin']=0;
// als User eingeloggt
if(($_SESSION['loggedin']==1)&&(isset($_SESSION['id_user'])))
{
    if((isset($_GET['page']))&&($_GET['page']=='allebestellungen')) $allebestellungen=true;
    else $allebestellungen=false;
    $query= "SELECT * FROM tblBestellung,tblBestellungsadresse
            WHERE tblBestellung.fi_kunde=".$_SESSION['id_user'].
            " AND tblBestellung.fi_adresse=id_bestellungsadresse
            AND tblBestellungsadresse.fi_kunde=".$_SESSION['id_user'].
            " ORDER BY dtLieferdatum DESC,dtLieferzeit DESC".(!$allebestellungen?' LIMIT 0,5':'');
    $result_all=mysqli_query($db,$query);
    ?>
    <div class="alte_bestellungen">
        <h1>Sie haben <?=($allebestellungen?'bereits':'letztens')?> folgendes bestellt:</h1>
    <?php
    if(mysqli_num_rows($result_all)>0)
    {
        $index=0;
        for($i=0;$i<mysqli_num_rows($result_all);$i++)
        {

            // query pro bestellung
            $query=	"SELECT id_gericht,dtBezeichnung,dtQuantitaet,dtPreis FROM tblBestehen_aus,tblGericht ".
                "WHERE fi_bestellung=".db_result($result_all,$i,'id_bestellung')." AND ".
                "id_gericht=fi_gericht";
            $result=mysqli_query($db,$query);
            ?>
            <!-- <?=(!$allebestellungen?'letzte':'alle')?> Bestellungen-->
            <div class="alte_bestellung">
                <span class="ueberschrift">bestellt für den <?=date('d.m.Y',strtotime(db_result($result_all,$i,
                        'dtLieferdatum'))).' um '.db_result($result_all,$i,'dtLieferzeit')?></span>
                <table class="tabelle">
                    <tr>
                        <th class="horizontal">Anzahl</th>
                        <th class="horizontal">Gericht</th>
                        <th class="horizontal">Preis</th>
                        <th class="horizontal">Einzelpreis</th>
                        <th class="horizontal">Adresse</th>
                        <td>&nbsp;</td>
                    </tr>
                    <?php
                    for($j=0;$j<mysqli_num_rows($result);$j++)
                    {
                        ?>
                        <tr<?=($j==mysqli_num_rows($result)-1?' class="extrema"':'')?>>
                            <td><span class="bestellen"><?=db_result($result,$j,'dtQuantitaet')?></span>
                                <input type="hidden" name="quantity" value="<?=db_result($result,$j,'dtQuantitaet')?>">
                            </td>
                            <td><?=db_result($result,$j,'dtBezeichnung')?><input type="hidden" name="fi_gericht" value="
                            <?=db_result($result,$j,'fi_gericht')?>">
                            </td>
                            <td><span><?=number_format(db_result($result,$j,'dtQuantitaet')*db_result($result,$j,
                                            'dtPreis'),2,',','.').'€'?></span>
                            </td>
                            <td><span><?=number_format(db_result($result,$j,'dtPreis'),2,',','.')?>€</span></td>
                            <td>
                               <span class="adresse"><?=db_result($result_all,$i,'dtVorname').' '.
                                       db_result($result_all,$i,'dtNachname')?></span><br>
                                <span class="adresse"><?=db_result($result_all,$i,'dtAdresse')?></span><br>
                                <span class="adresse"><?=db_result($result_all,$i,'dtPostleitzahl').
                                        ' '.db_result($result_all,$i,'dtOrtschaft')?></span>
                            </td>
                            <td class="button">
                                <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen&amp;action='.
                                'add&amp;id='.db_result($result,$j,'id_gericht')?>"><span>nochmal bestellen</span></a>
                            </td>
                        </tr>
                        <?php
                        $index++;
                    }
                    ?>
                </table>
            </div>
        <?php
        }
    }
    else
        echo '<h3>Sie haben noch nichts bestellt, jetzt <a class="link" href="'.$_SERVER['SCRIPT_NAME'].
            '?page=bestellen"><span>bestellen?</span></a></h3>';
    ?>
    </div>
<?php
}
else
{
    $url=$_SERVER['SCRIPT_NAME'];
    $pieces=explode('/',$url);
    if($pieces[count($pieces)-2]=='Include')
    {
        $url='https://'.$_SERVER['SERVER_NAME'].str_replace('/'.$pieces[count($pieces)-2],'',
            dirname($_SERVER['SCRIPT_NAME']));
        header('Location:'.$url);
    }
}
?>