<?php
if((isset($_SESSION['warenkorb']))&&(count($_SESSION['warenkorb'])>0))
{
    if($success) success('Ihre Kreditkarte ist gültig, Sie können nun den Bestellvorgang fortsetzen.');
?>
    <div class="zahlungsart">
        <h2>Bitte Zahlungsart auswählen:</h2>
        <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=zahlungsart'?>" method="post" onchange="submit()">
            <table class="einruecken">
                <tr>
                    <?php
                    for($i=0;$i<count($zahlungsarten);$i++)
                    {
                    ?>
                        <td>
                            <input type="radio" name="DATA_zahlungsart"
                                <?=($zahlungsart==$zahlungsarten[$i]?' checked="checked"':'')?>
                                   value="<?=$zahlungsarten[$i]?>">
                            <img src="Pictures/<?=strtolower($zahlungsarten[$i])?>_logo.png" alt="<?=$zahlungsarten[$i]
                            ?>" title="<?=$zahlungsarten[$i]?>">
                        </td>
                    <?php
                    }
                    ?>
                </tr>
            </table>
            <?php
            if($zahlungsart=='PayPal')
            {
                ?>
                <div class="btn_center">
                    <input class="weiter" type="submit" name="btnCheck" value="Weiter">
                </div>
                <?php
            }
            ?>
        </form>
        <?php
        if(($zahlungsart=='Visa')||($zahlungsart=='MasterCard'))
        {
        ?>
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=zahlungsart'?>" method="post">
                <table class="einruecken">
                    <tr>
                        <th class="vertikal">Karteninformationen</th>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <th class="vertikal">Kartennummer:</th>
                        <td><input type="text" name="DATA_kartennummer" <?=(isset($_POST['DATA_kartennummer'])?
                                ' value="'.$_POST['DATA_kartennummer'].'"':'')?>></td>
                    </tr>
                    <tr>
                        <th class="vertikal">Sicherheitscode</th>
                        <td><input type="text" name="DATA_sicherheitscode" <?=(isset($_POST['DATA_sicherheitscode'])?
                                ' value="'.$_POST['DATA_sicherheitscode'].'"':'')?>></td>
                    </tr>
                    <tr>
                        <th class="vertikal">Ablaufdatum</th>
                        <td>
                            <select name="DATA_month">
                                <?php
                                for($i=1;$i<=12;$i++)
                                {
                                    echo '<option value="'.$i.'"'.(((isset($_POST['DATA_month']))&&
                                        ($_POST['DATA_month']==$i))?' selected="selected"':'').'>'.($i<10?'0'.
                                        strval($i):strval($i)).'</option>'."\n";
                                }
                                ?>
                            </select>
                            <select name="DATA_year">
                                <?php
                                for($i=intval(date('Y'));$i<(intval(date('Y'))+10);$i++)
                                    echo '<option value="'.$i.'"'.(((isset($_POST['DATA_year']))&&
                                        ($_POST['DATA_year']==$i))?' selected="selected"':'').'>'.$i.'</option>'."\n";
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php
                if(!$success)
                {
                ?>
                    <div class="btn_center">
                        <input type="hidden" name="DATA_zahlungsart" value="<?=$zahlungsart?>">
                        <input class="button" type="submit" name="btnCheck" value="Daten überprüfen">
                    </div>
                <?php
                }
                ?>
            </form>
        <?php
        }

            $ack=(isset($_SESSION['nvpReqArray']['ACK'])?$_SESSION['nvpReqArray']['ACK']:'');
        if(($zahlungsart!='PayPal')&&(($success)||( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" )))
        {
        ?>
            <div class="btn_center">
                <a class="weiter" href="<?=$_SERVER['SCRIPT_NAME'].'?page=uebersicht'?>"><span>Weiter</span></a>
            </div>
        <?php
        }
        ?>
        <a class="zurueck" href="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferadresse'?>">Zurück zur Auswahl der
            Lieferadresse</a>
    </div>
<?php
}
else
{
    $url=$_SERVER['SCRIPT_NAME'];
    $pieces=explode('/',$url);
    if($pieces[count($pieces)-2]=='bezahlen')
    {
        $url='https://'.$_SERVER['SERVER_NAME'].str_replace('/'.$pieces[count($pieces)-2],'',
            dirname($_SERVER['SCRIPT_NAME']));
        header('Location:'.$url);
    }
}
?>