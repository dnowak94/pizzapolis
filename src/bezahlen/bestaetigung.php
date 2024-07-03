<?php
if((isset($_SESSION['warenkorb']))&&(count($_SESSION['warenkorb'])>0))
{
    if(isset($_POST['btnConfirm']))
    {

        if(!isset($success)) $success=false;
        /*==================================================================
         PayPal Express Checkout Call
         ===================================================================
        */

        require_once ("paypalfunctions.php");

        if(isset($_SESSION['nvpReqArray']['CREDITCARDTYPE']))
            $PaymentOption=$_SESSION['nvpReqArray']['CREDITCARDTYPE'];
        else $PaymentOption='PayPal';

        if ( $PaymentOption == "PayPal" )
        {
            /*
            '------------------------------------
            ' The paymentAmount is the total value of
            ' the shopping cart, that was set
            ' earlier in a session variable
            ' by the shopping cart page
            '------------------------------------
            */

            $finalPaymentAmount =  $_SESSION["Payment_Amount"];

            /*
            '------------------------------------
            ' Calls the DoExpressCheckoutPayment API call
            '
            ' The ConfirmPayment function is defined in the file PayPalFunctions.jsp,
            ' that is included at the top of this file.
            '-------------------------------------------------
            */

            $resArray = ConfirmPayment ( $finalPaymentAmount );
            $ack = strtoupper($resArray["ACK"]);
            $_SESSION['nvpReqArray']['ACK']=$ack;
            if( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" )
            {
                /*
                '********************************************************************************************************************
                '
                ' THE PARTNER SHOULD SAVE THE KEY TRANSACTION RELATED INFORMATION LIKE
                '                    transactionId & orderTime
                '  IN THEIR OWN  DATABASE
                ' AND THE REST OF THE INFORMATION CAN BE USED TO UNDERSTAND THE STATUS OF THE PAYMENT
                '
                '********************************************************************************************************************
                */

                $transactionId		= $resArray["PAYMENTINFO_0_TRANSACTIONID"];
                /* ' Unique transaction ID of the payment. Note:  If the PaymentAction of the request was Authorization
                or Order, this value is your AuthorizationID for use with the Authorization & Capture APIs. */
                $transactionType 	= $resArray["PAYMENTINFO_0_TRANSACTIONTYPE"];
                //' The type of transaction Possible values: l  cart l  express-checkout
                $paymentType		= $resArray["PAYMENTINFO_0_PAYMENTTYPE"];
                //' Indicates whether the payment is instant or delayed. Possible values: l  none l  echeck l  instant
                $orderTime 			= $resArray["PAYMENTINFO_0_ORDERTIME"];  //' Time/date stamp of payment
                $amt				= $resArray["PAYMENTINFO_0_AMT"];
                //' The final amount charged, including any shipping and taxes from your Merchant Profile.
                $currencyCode		= $resArray["PAYMENTINFO_0_CURRENCYCODE"];
                /*' A three-character currency code for one of the currencies listed in PayPay-Supported Transactional
                Currencies. Default: USD. */
                $feeAmt				= $resArray["PAYMENTINFO_0_FEEAMT"];  /*' PayPal fee amount charged for the
                                                                            transaction */
                /* $settleAmt			= $resArray["PAYMENTINFO_0_SETTLEAMT"];
                //' Amount deposited in your PayPal account after a currency conversion. */
                $taxAmt				= $resArray["PAYMENTINFO_0_TAXAMT"];  //' Tax charged on the transaction.
                /* $exchangeRate		= $resArray["PAYMENTINFO_0_EXCHANGERATE"];
                //' Exchange rate if a currency conversion occurred. Relevant only if your are billing in their
                non-primary currency. If the customer chooses to pay with a currency other than the non-primary
                currency, the conversion occurs in the customer's account. */

                /*
                ' Status of the payment:
                        'Completed: The payment has been completed, and the funds have been added successfully to your account balance.
                        'Pending: The payment is pending. See the PendingReason element for more information.
                */

                $paymentStatus	= $resArray["PAYMENTINFO_0_PAYMENTSTATUS"];

                /*
                'The reason the payment is pending:
                '  none: No pending reason
                '  address: The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.
                '  echeck: The payment is pending because it was made by an eCheck that has not yet cleared.
                '  intl: The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.
                '  multi-currency: You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.
                '  verify: The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.
                '  other: The payment is pending for a reason other than those listed above. For more information, contact PayPal customer service.
                */

                $pendingReason	= $resArray["PAYMENTINFO_0_PENDINGREASON"];

                /*
                'The reason for a reversal if TransactionType is reversal:
                '  none: No reason code
                '  chargeback: A reversal has occurred on this transaction due to a chargeback by your customer.
                '  guarantee: A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.
                '  buyer-complaint: A reversal has occurred on this transaction due to a complaint about the transaction from your customer.
                '  refund: A reversal has occurred on this transaction because you have given the customer a refund.
                '  other: A reversal has occurred on this transaction due to a reason not listed above.
                */

                $reasonCode		= $resArray["PAYMENTINFO_0_REASONCODE"];

                $success=true;
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
            // Zahlung mit Kreditkarte abschließen
            $resArray=$_SESSION['nvpReqArray'];
            $resArray = DirectPayment("Sale",$_SESSION['Payment_Amount'], $resArray['CREDITCARDTYPE'],
                $resArray['ACCT'],$resArray['EXPDATE'],$resArray['CVV2'], $resArray['FIRSTNAME'],$resArray['LASTNAME'],
                $resArray['STREET'], $resArray['CITY'], '', $resArray['ZIP'], $resArray['COUNTRYCODE'],
                $resArray['CURRENCYCODE']);
            // überprüfen ob es Fehler gab
            $ack = strtoupper($resArray["ACK"]);
            $success=( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" );
            $_SESSION['nvpReqArray']['ACK']=$ack;
        }
        if($success)
        {
            // Bestellungsaufnahme

            // Lieferadresse in Bestellungsadressen kopieren
            $query="SELECT * FROM tblBestellungsadresse WHERE fi_adresse=".$_SESSION['lieferadresse'];
            $result=mysqli_query($db,$query);
            // Lieferadresse anders als Bestellungsadresse
            if(mysqli_num_rows($result)==0)
            {
                // Adresse abfragen
                $query="SELECT * FROM tblAdresse WHERE id_adresse=".$_SESSION['lieferadresse'];
                $result=mysqli_query($db,$query);

                if(mysqli_num_rows($result)==1)
                {
                    $adresse=mysql_fetch_assoc($result);
                    unset($adresse['id_adresse']);
                    unset($adresse['dtStandard']);
                    // Adresse in Bestellungsadressen kopieren
                    $query= "INSERT INTO tblBestellungsadresse (";
                            foreach($adresse as $feld=>$value) $query.=$feld.',';
                            $query.="fi_adresse) VALUES(";
                            foreach($adresse as $feld=>$value) $query.="'".$value."',";
                            $query.=$_SESSION['lieferadresse'].")";
                    mysqli_query($db,$query);
                    $_SESSION['lieferadresse']=mysql_insert_id($db);
                }
            }

            // neue Bestellung
            $query= "INSERT INTO tblBestellung (dtLieferdatum,dtLieferzeit,dtLieferart,fi_adresse,fi_kunde) ".
                "VALUES('".
                date('Y-m-d',strtotime($_SESSION['lieferdatum']))."','".
                db_update($_SESSION['lieferzeit'])."',".
                db_update($_SESSION['lieferart']).",".
                (isset($_SESSION['lieferadresse'])?db_update($_SESSION['lieferadresse']):'NULL').",".
                $_SESSION['id_user'].")";
            mysqli_query($db,$query);

            // Bestellungsnummer herausfinden
            $query="SELECT MAX(id_bestellung) AS 'id_bestellung' FROM tblBestellung";
            $result=mysqli_query($db,$query);
            $id_bestellung=db_result($result,0,'id_bestellung');

            // Gerichte zur Bestellung hinzufügen
            foreach($_SESSION['warenkorb'] as $fi_gericht=>$quantity)
            {
                $query="INSERT INTO tblBestehen_aus (fi_bestellung,fi_gericht,dtQuantitaet) ".
                    "VALUES(".$id_bestellung.','.$fi_gericht.','.$quantity.")";
                mysqli_query($db,$query);
            }

            // neue Rechnung
            $query="INSERT INTO tblRechnung (fi_bestellung) VALUES(".$id_bestellung.")";
            mysqli_query($db,$query);

            // Rechnung als PDF erstellen
            $path=str_replace('bezahlen','',dirname($_SERVER['SCRIPT_FILENAME']));
            include($path.'Include/Benutzer/rechnung.php');

            // E-Mail mit Rechnung senden
            // Kundendaten abfragen
            $query="SELECT * FROM tblBenutzer WHERE id_benutzer=".$_SESSION['id_user'];
            echo $query;
            $result=mysqli_query($db,$query);

            $to=db_result($result,0,'dtE-Mail');
            $subject='pizzapolis.lu - Bestellung für '.$_SESSION['lieferdatum'].' um '.
                $_SESSION['lieferzeit'];
            $fullname=db_result($result,0,'dtVorname').' '.db_result($result,0,'dtNachname');
            $message='<p>Vielen Dank für Ihre Bestellung, Ihre Bestellung wurde erfolgreich aufgenommen.</p>'.
                '<p>Die Rechnung liegt im Anhang als PDF vor.</p>';
            $attachment=str_replace('bezahlen','',dirname($_SERVER['SCRIPT_FILENAME'])).
                        'Rechnungen/rechnung_'.db_result($result,0,'dtUsername').'_'.date('Ymd@H-i').'.pdf';
            $success=send_mail($to,$fullname,$subject,$message,$attachment);
            unlink($attachment);

            // alle SESSION-Variablen löschen ausser 'loggedin' und 'id_user'
            foreach($_SESSION as $key=>$value)
            {
                if(($key!='loggedin')&&($key!='id_user'))
                    unset($_SESSION[$key]);
            }

            // redirect auf die Hauptseite
            $url=(isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].str_replace('bezahlen','',
                dirname($_SERVER['SCRIPT_NAME'])).'index.php?bestellt=true';
            header('Location: '.$url);

        }
        else
        {
            ?>
            <div class="adresse">
                <span class="redtxt">Bezahlung fehlgeschlagen!</span>
                <p>Wählen Sie bitte eine andere Zahlungsart aus.</p>
                <a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=zahlungsart'?>">
                    <span>Zahlungsart ändern'</span>
                </a>
            </div>
        <?php
        }
    }
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