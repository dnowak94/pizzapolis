<?php
// als Restaurant eingeloggt
if(isset($_SESSION['restaurant']))
{
    // Eingabe


    $id=(((isset($_GET['id']))&&(valid_id('tblBestellung','id_bestellung',$_GET['id'])))?$_GET['id']:'');
    $showDetaits=((mysqli_num_rows($result)>0)&&($id!='')&&(isset($_GET['action']))&&($_GET['action']=='details'));

    // Verarbeitung
    if((isset($_GET['action']))&&($_GET['action']=='liefern')&&($id!=''))
    {
        $query="UPDATE tblBestellung SET dtStatus=1 WHERE id_bestellung=".$id;
        mysqli_query($db,$query);
    }

    // Ausgabe
    // abfragen ob mehrere Bestellungen vorhanden
    $query= "SELECT id_bestellung FROM tblBestellung
             WHERE dtStatus=0";
    $result=mysqli_query($db,$query);
    ?>
    <div class="bestellungen">
    <h1>aktuelle Bestellungen</h1>
    <?php
    // mehrere Bestellungen vorhanden
    if(mysqli_num_rows($result)>0)
    {
        // Bestellungsliste anzeigen
        if(!$showDetaits)
        {
            $query= "SELECT id_bestellung,dtLieferdatum,dtLieferzeit,dtLieferart,dtTelefonnummer,`dtE-Mail`,
                    tblBestellungsadresse.dtVorname,tblBestellungsadresse.dtNachname,dtAdresse,dtPostleitzahl,
                    dtOrtschaft
                    FROM tblBestellung,tblBenutzer,tblBestellungsadresse
                    WHERE dtLieferart=1
                    AND tblBestellung.fi_adresse=id_bestellungsadresse
                    AND tblBestellung.fi_kunde=id_benutzer
                    AND `dtStatus`=0
                    AND istRestaurant=0";
            $result_all=mysqli_query($db,$query);
            if(mysqli_num_rows($result_all)>0)
            {
            ?>
                <h2>zum Ausliefern</h2>

                <table class="tabelle">
                <tr>
                    <th class="horizontal">Liefertermin</th>
                    <th class="horizontal">Gerichte</th>
                    <th class="horizontal">Lieferadresse</th>
                    <th class="horizontal">Bestellart</th>
                    <th  class="horizontal" colspan="2">Aktion</th>
                </tr>
                <?php
                // Bestellungen anzeigen
                for($i=0;$i<mysqli_num_rows($result_all);$i++)
                {
                    ?>
                    <tr<?=($i==mysqli_num_rows($result_all)-1?' class="extrema"':'')?>>
                        <td><span class="liefertermin"><?=date('d.m.Y',strtotime(db_result($result_all,$i,
                                    'dtLieferdatum'))).' '.db_result($result_all,$i,'dtLieferzeit')?></span></td>
                        <td>
                            <table>
                                <?php
                                $query="SELECT * FROM tblBestehen_aus,tblGericht WHERE fi_bestellung=".
                                    db_result($result_all,$i,'id_bestellung')." AND fi_gericht=id_gericht";
                                $result=mysqli_query($db,$query);
                                for($j=0;$j<mysqli_num_rows($result);$j++)
                                {
                                    ?>
                                    <tr<?=($j==mysqli_num_rows($result)-1?' class="extrema"':'')?>>
                                        <td><?=db_result($result,$j,'dtQuantitaet').'x '?>
                                            <?=db_result($result,$j,'dtBezeichnung')?>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </table>
                        </td>
                        <td>
                           <span class="adresse"><?=db_result($result_all,$i,'dtVorname').' '.
                                   db_result($result_all,$i,'dtNachname')?></span><br>
                            <span class="adresse"><?=db_result($result_all,$i,'dtAdresse')?></span><br>
                            <span class="adresse"><?=db_result($result_all,$i,'dtPostleitzahl').
                                ' '.db_result($result_all,$i,'dtOrtschaft')?></span>
                        </td>
                        <td><span>Lieferung</span></td>
                        <td><a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellungen&amp;'.
                        'action=liefern&amp;id='.db_result($result_all,$i,'id_bestellung')?>">
                                <span>Liefern</span></a></td>
                        <td><a  class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellungen&amp;action='.
                        'details&amp;id='.db_result($result_all,$i,'id_bestellung')?>"><span>Details</span></a></td>
                    </tr>
                <?php
                }
                ?>
            </table>
            <?php
            }
            // selbst Abholung
            $query="SELECT id_bestellung,dtLieferdatum,dtLieferzeit,dtLieferart,dtTelefonnummer,`dtE-Mail`,dtVorname,
                    dtNachname
                    FROM tblBestellung,tblBenutzer
                    WHERE dtLieferart=0
                    AND tblBestellung.fi_adresse IS NULL
                    AND tblBestellung.fi_kunde=id_benutzer
                    AND `dtStatus`=0
                    AND istRestaurant=0";
            $result_all=mysqli_query($db,$query);
            if(mysqli_num_rows($result_all)>0)
            {
                ?>

                <h3>werden abgeholt</h3>
                <table class="tabelle">
                    <tr>
                        <th class="horizontal">Liefertermin</th>
                        <th class="horizontal">Gerichte</th>
                        <th class="horizontal">Kunde</th>
                        <th class="horizontal">Bestellart</th>
                        <th  class="horizontal" colspan="2">Aktion</th>
                    </tr>
                    <?php
                    // Bestellungen anzeigen
                    for($i=0;$i<mysqli_num_rows($result_all);$i++)
                    {
                        ?>
                        <tr<?=($i==mysqli_num_rows($result_all)-1?' class="extrema"':'')?>>
                            <td><span class="liefertermin"><?=date('d.m.Y',strtotime(db_result($result_all,$i,
                                'dtLieferdatum'))).' '.db_result($result_all,$i,'dtLieferzeit')?></span></td>
                            <td>
                                <table>
                                    <?php
                                    $query="SELECT * FROM tblBestehen_aus,tblGericht WHERE fi_bestellung=".
                                        db_result($result_all,$i,'id_bestellung')." AND fi_gericht=id_gericht";
                                    $result=mysqli_query($db,$query);
                                    for($j=0;$j<mysqli_num_rows($result);$j++)
                                    {
                                        ?>
                                        <tr<?=($j==mysqli_num_rows($result)-1?' class="extrema"':'')?>>
                                            <td><?=db_result($result,$j,'dtQuantitaet').'x '?>
                                                <?=db_result($result,$j,'dtBezeichnung')?>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </table>
                            </td>
                            <td><?=db_result($result_all,$i,'dtVorname').' '.db_result($result_all,$i,
                                'dtNachname')?></td>
                            <td><span>selbst Abholung</span></td>
                            <td><a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellungen&amp;'.
                                'action=liefern&amp;id='.db_result($result_all,$i,'id_bestellung')?>">
                                    <span>Fertig</span></a>
                            </td>
                            <td>
                                <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellungen&amp;action='.
                                'details&amp;id='.db_result($result_all,$i,'id_bestellung')?>"><span>Details</span>
                                </a></td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
                <?php
            }
            ?>
            </div>
            <?php
        }


        // Details anzeigen
        if($showDetaits)
        {
            $query="SELECT id_bestellung,tblBestellungsadresse.dtVorname,tblBestellungsadresse.dtNachname,`dtE-Mail`,
                    dtTelefonnummer,dtAdresse,dtPostleitzahl,dtOrtschaft
                    FROM tblBestellung,tblBestellungsadresse,tblBenutzer
                    WHERE id_bestellung=".$id.
                    " AND tblBestellung.fi_adresse=id_bestellungsadresse".
                    " AND tblBestellung.fi_kunde=id_benutzer";
            $result_all=mysqli_query($db,$query);
            if(!mysqli_num_rows($result_all)>0)
            {
                $query="SELECT id_bestellung,dtVorname,dtNachname,`dtE-Mail`,dtTelefonnummer
                        FROM tblBestellung,tblBenutzer
                        WHERE id_bestellung=".$id.
                        " AND fi_kunde=id_benutzer";
                $result=mysqli_query($db,$query);
            }
            ?>
            <div class="details">
                <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellungen'?>"><span class="cancel">Zurück</span></a>
                <h2>Kunde:</h2>
                <table>
                    <tr>
                        <th class="vertikal">Name</th>
                        <td><?=db_result((mysqli_num_rows($result_all)>0?$result_all:$result),0,'dtVorname').' '.
                                db_result((mysqli_num_rows($result_all)>0?$result_all:$result),0,'dtNachname')?></td>
                    </tr>
                    <tr>
                        <th class="vertikal">E-Mail</th>
                        <td><?=db_result((mysqli_num_rows($result_all)>0?$result_all:$result),0,'dtE-Mail')?></td>
                    </tr>
                    <tr>
                        <th class="vertikal">Telefonnummer</th>
                        <td><?=db_result((mysqli_num_rows($result_all)>0?$result_all:$result),0,'dtTelefonnummer')?></td>
                    </tr>
                    <?php
                    if(mysqli_num_rows($result_all)>0)
                    {
                    ?>
                        <tr>
                            <th class="vertikal">Adresse</th>
                            <td><?=db_result($result_all,0,'dtAdresse')?></td>
                        </tr>
                        <tr>
                            <th class="vertikal">Postleitzahl</th>
                            <td><?=db_result($result_all,0,'dtPostleitzahl')?></td>
                        </tr>
                        <tr>
                            <th class="vertikal">Ortschaft</th>
                            <td><?=db_result($result_all,0,'dtOrtschaft')?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>

                <h2>Gerichte:</h2>
                <table>
                    <tr>
                        <th class="horizontal">Name</th>
                        <th class="horizontal">Preis</th>
                        <th class="horizontal">Anzahl</th>
                    </tr>
                    <?php
                    $query="SELECT * FROM tblBestehen_aus,tblGericht WHERE fi_bestellung=".
                        $id." AND fi_gericht=id_gericht";
                    $result=mysqli_query($db,$query);
                    $total=0;
                    for($i=0;$i<mysqli_num_rows($result);$i++)
                    {
                        ?>
                        <tr>
                            <td><?=db_result($result,$i,'dtBezeichnung')?></td>
                            <td class="preis"><?=db_result($result,$i,'dtPreis').'€'?></td>
                            <td class="anzahl"><?=db_result($result,$i,'dtQuantitaet')?></td>
                        </tr>
                        <?php
                        $total+=floatval(db_result($result,$i,'dtPreis'))*intval(db_result($result,$i,'dtQuantitaet'));
                    }
                    ?>
                </table>
                <br>
                <table>
                    <tr>
                        <td class="no-border">&nbsp;</td>
                        <td><span class="total">Total:</span></td>
                        <td class="preis"><?=number_format($total,2,',','.').'€'?></td>
                    </tr>
                </table>
                <span class="clearfix"></span>
            </div>
<?php
        }
    }
    else
    {
        ?>
        <div class="no-rows">
            <p>Es gibt im Moment keine aktiven Bestellungen.</p>
            <span class="clearfix"></span>
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
?>