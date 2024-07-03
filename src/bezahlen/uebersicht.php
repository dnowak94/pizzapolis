<?php
if((isset($_SESSION['warenkorb']))&&(count($_SESSION['warenkorb'])>0)&&(isset($_SESSION['nvpReqArray'])))
{
    /*==================================================================
     PayPal Express Checkout Call
     ===================================================================
    */
    // Check to see if the Request object contains a variable named 'token'
    $token = "";
    if (isset($_REQUEST['token']))
    {
        $token = $_REQUEST['token'];
    }
    elseif(isset($_SESSION['TOKEN']))
    {
        $token=$_SESSION['TOKEN'];
    }

    // If the Request object contains the variable 'token' then it means that the user is coming from PayPal site.
    if (( $token != "" )&&(!isset($_SESSION['nvpReqArray']['CREDITCARDTYPE'])))
    {

        require_once ("paypalfunctions.php");

        /*
        '------------------------------------
        ' Calls the GetExpressCheckoutDetails API call
        '
        ' The GetShippingDetails function is defined in PayPalFunctions.jsp
        ' included at the top of this file.
        '-------------------------------------------------
        */


        $resArray = GetShippingDetails( $token );
        $ack = strtoupper($resArray["ACK"]);
        $_SESSION['nvpReqArray']['ACK']=$ack;
        if( $ack == "SUCCESS" || $ack == "SUCESSWITHWARNING")
        {
            /*
            ' The information that is returned by the GetExpressCheckoutDetails call should be integrated by the partner into his Order Review
            ' page
            */
            $cntryCode			= $resArray["COUNTRYCODE"]; // ' Payer's country of residence in the form of ISO standard 3166 two-character country codes.
            $shipToName			= $resArray["PAYMENTREQUEST_0_SHIPTONAME"]; // ' Person's name associated with this address.
            $shipToStreet		= $resArray["PAYMENTREQUEST_0_SHIPTOSTREET"]; // ' First street address.
            /*$shipToStreet2		= $resArray["PAYMENTREQUEST_0_SHIPTOSTREET2"]; // ' Second street address.*/
            $shipToCity			= $resArray["PAYMENTREQUEST_0_SHIPTOCITY"]; // ' Name of city.
            /* $shipToState		= $resArray["PAYMENTREQUEST_0_SHIPTOSTATE"]; // ' State or province */
            $shipToCntryCode	= $resArray["PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE"]; // ' Country code.
            $shipToZip			= $resArray["PAYMENTREQUEST_0_SHIPTOZIP"]; // ' U.S. Zip code or other country-specific postal code.
            $addressStatus 		= $resArray["ADDRESSSTATUS"]; // ' Status of street address on file with PayPal
            /*$invoiceNumber		= $resArray["INVNUM"]; // ' Your own invoice or tracking number, as set by you in the element of the same name in SetExpressCheckout request . */
            /* $phonNumber			= $resArray["PHONENUM"]; // ' Payer's contact telephone number. Note:  PayPal returns a contact telephone number only if your Merchant account profile settings require that the buyer enter one.*/
        }
        else
        {
            //Display a user friendly Error on the page using any of the following error information returned by PayPal
            $ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
            $ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
            $ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
            $ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);

            echo "GetExpressCheckoutDetails API call failed. ";
            echo "Detailed Error Message: " . $ErrorLongMsg;
            echo "Short Error Message: " . $ErrorShortMsg;
            echo "Error Code: " . $ErrorCode;
            echo "Error Severity Code: " . $ErrorSeverityCode;
        }
    }
    else
    {
        $shipToName = $_SESSION['nvpReqArray']['FIRSTNAME'].' '.$_SESSION['nvpReqArray']['LASTNAME'];
        $shipToStreet=$_SESSION['nvpReqArray']['STREET'];
        $shipToCity=$_SESSION['nvpReqArray']['CITY'];
        $shipToZip=$_SESSION['nvpReqArray']['ZIP'];
    }

    // INI-Datei laden
    $filename=str_replace('bezahlen','',dirname($_SERVER['SCRIPT_FILENAME'])).'Include/Restaurant/informationen.ini.php';
    $einstellungen=parse_ini_file($filename);

    $query="SELECT dtVorname,dtNachname,dtTelefonnummer FROM tblBenutzer WHERE id_benutzer=".$_SESSION['id_user'];
    $result=mysqli_query($db,$query);
    // Ausgabe
    ?>
    <div class="review">
        <h1>Bestellungsübersicht</h1>
        <table class="tabelle einruecken">
            <tr>
                <th class="vertikal">Auftraggeber</th>
                <td><?=db_result($result,0,'dtVorname').' '.db_result($result,0,'dtNachname')?></td>
            </tr>
            <tr>
               <th class="vertikal">Lieferart</th>
                <td><?=($_SESSION['lieferart']==1?'Lieferung':'selbst Abholen')?></td>
            </tr>
            <tr>
                <th class="vertikal">Empfänger</th>
                <td><?=$shipToName?></td>
            </tr>
            <tr>
                <th class="vertikal"><?=(isset($_SESSION['lieferadresse'])?'Liefer':'Rechnungs')?>adresse</th>
                <td><?=$shipToStreet?></td>
            </tr>
            <tr>
                <th class="vertikal">Postleitzahl</th>
                <td><?=$shipToZip?></td>
            </tr>
            <tr>
                <th class="vertikal">Ortschaft</th>
                <td><?=$shipToCity?></td>
            </tr>
            <tr class="extrema">
                <th class="vertikal">Telefonnummer</th>
                <td><?=db_result($result,0,'dtTelefonnummer')?></td>
            </tr>
        </table>
        <h2>Artikel:</h2>
        <table class="tabelle einruecken">
            <tr>
                <th class="horizontal">Anzahl</th>
                <th class="horizontal">Gericht</th>
                <th class="horizontal">Einzelpreis</th>
                <th class="horizontal">Querpreis</th>
            </tr>
            <?php
            $i=0;
            foreach($_SESSION['warenkorb'] as $fi_gericht=>$quantity)
            {
                $query="SELECT * FROM tblGericht WHERE id_gericht=".$fi_gericht;
                $result=mysqli_query($db,$query);
                ?>
                <tr<?=($i==count($_SESSION['warenkorb'])-1?' class="extrema"':'')?>>
                    <td class="quantity">
                        <span class="quantity"><?=$quantity?></span>
                    </td>

                    <td><?=db_result($result,0,'dtBezeichnung')?></td>
                    <?php
                    $preis=floatval(str_replace(',','.',db_result($result,0,'dtPreis')));
                    ?>
                    <td><span><?=number_format($preis,2,',','.').'€'?></span></td>
                    <td><span><?=number_format($quantity*$preis,2,',','.').'€'?></span></td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <span class="lieferkosten">Lieferkosten :
            <?=number_format($_SESSION['lieferart']*$einstellungen['lieferkosten'],2,',','.')?>€</span>
        <span class="clearfix"></span>
        <hr>
        <span class="total">zu zahlender Betrag : <?=number_format($_SESSION['Payment_Amount'],2,',','.').'€'?></span>
        <div class="btn_center">
            <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=bestaetigung'?>" method="post">
                <input type="submit" class="button" name="btnConfirm" value="Bestellung bestätigen">
            </form>
        </div>
        <a class="zurueck" href="<?=$_SERVER['SCRIPT_NAME'].'?page=zahlungsart'?>">Zurück zur Auswahl der
            Zahlungsart</a><br>
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
        $url=(isset($_SESSION['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].str_replace('/'.
            $pieces[count($pieces)-2],'',dirname($_SERVER['SCRIPT_NAME']));
        //echo $url;
        header('Location:'.$url);
    }
}
?>