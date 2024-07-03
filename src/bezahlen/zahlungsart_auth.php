<?php
if((isset($_GET['page']))&&($_GET['page']=='zahlungsart')&&(isset($_SESSION['warenkorb']))&&
    (count($_SESSION['warenkorb'])>0))
{
    // Eingabe
    if(!isset($success)) $success=false;
    if(isset($_POST['DATA_zahlungsart'])) $zahlungsart=$_POST['DATA_zahlungsart'];
    else $zahlungsart='Visa';
    $zahlungsarten=array('Visa','MasterCard','PayPal');

    if(isset($_SESSION['lieferadresse']))
    {
        $query= "SELECT * FROM tblAdresse,tblBenutzer
                WHERE id_adresse=".$_SESSION['lieferadresse'].
                " AND fi_kunde=id_benutzer";
        $result=mysqli_query($db,$query);
    }
    else
    {
        $query= "SELECT * FROM tblAdresse,tblBenutzer
                WHERE fi_kunde=".$_SESSION['id_user'].
                " AND dtStandard=1
                AND fi_kunde=id_benutzer";
        $result=mysqli_query($db,$query);
    }

    // Verarbeitung
    if(isset($_POST['btnCheck']))
    {
        $PaymentOption=$_POST['DATA_zahlungsart'];

        require_once ("paypalfunctions.php");

        if ( $PaymentOption == "PayPal")
        {
            // ==================================
            // PayPal Express Checkout Module
            // ==================================

            //'------------------------------------
            //' The paymentAmount is the total value of
            //' the shopping cart, that was set
            //' earlier in a session variable
            //' by the shopping cart page
            //'------------------------------------

            $paymentAmount = $_SESSION["Payment_Amount"];

            //'------------------------------------
            //' When you integrate this code
            //' set the variables below with
            //' shipping address details
            //' entered by the user on the
            //' Shipping page.
            //'------------------------------------

            $shipToName = db_result($result,0,'dtVorname').' '.db_result($result,0,'dtNachname');
            $shipToStreet = db_result($result,0,'dtAdresse');
            $shipToStreet2 = ""; //Leave it blank if there is no value
            $shipToCity = db_result($result,0,'dtOrtschaft');
            $shipToState = "";
            $shipToCountryCode = "LU"; // Please refer to the PayPal country codes in the API documentation
            $shipToZip = db_result($result,0,'dtPostleitzahl');
            $phoneNum='';
            /*
            $shipToName = "<<ShiptoName>>";
            $shipToStreet = "<<ShipToStreet>>";
            $shipToStreet2 = "<<ShipToStreet2>>"; //Leave it blank if there is no value
            $shipToCity = "<<ShipToCity>>";
            $shipToState = "<<ShipToState>>";
            $shipToCountryCode = "<<ShipToCountryCode>>"; // Please refer to the PayPal country codes in the API documentation
            $shipToZip = "<<ShipToZip>>";
            $phoneNum = "<<PhoneNumber>>";*/


            //'------------------------------------
            //' The currencyCodeType and paymentType
            //' are set to the selections made on the Integration Assistant
            //'------------------------------------
            $currencyCodeType = "EUR";
            $paymentType = "Sale";

            //'------------------------------------
            //' The returnURL is the location where buyers return to when a
            //' payment has been succesfully authorized.
            //'
            //' This is set to the value entered on the Integration Assistant
            //'------------------------------------
            $returnURL = (isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].
                $_SERVER['SCRIPT_NAME'].'?page=uebersicht';

            //'------------------------------------
            //' The cancelURL is the location buyers are sent to when they hit the
            //' cancel button during authorization of payment during the PayPal flow
            //'
            //' This is set to the value entered on the Integration Assistant
            //'------------------------------------
            $cancelURL = (isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].str_replace('bezahlen','',
                dirname($_SERVER['SCRIPT_NAME'])).'index.php?page=bestellen';

            //'------------------------------------
            //' Calls the SetExpressCheckout API call
            //'
            //' The CallMarkExpressCheckout function is defined in the file PayPalFunctions.php,
            //' it is included at the top of this file.
            //'-------------------------------------------------
            $resArray = CallMarkExpressCheckout ($paymentAmount, $currencyCodeType, $paymentType, $returnURL,
                $cancelURL, $shipToName, $shipToStreet, $shipToCity, $shipToState,
                $shipToCountryCode, $shipToZip, $shipToStreet2, $phoneNum
            );

            if(isset($resArray['ACK']))
            {
                $ack = strtoupper($resArray["ACK"]);
                $_SESSION['nvpReqArray']['ACK']=$ack;
            }
            else
            {
                $ack='';
                $_SESSION['nvpReqArray']['ACK']='';
            }
            if($ack=="SUCCESS" || $ack=="SUCCESSWITHWARNING")
            {
                $token = urldecode($resArray["TOKEN"]);
                $_SESSION['reshash']=$token;
                RedirectToPayPal ( $token );
                $success=true;
            }
            else
            {
                //Display a user friendly Error on the page using any of the following error information returned by PayPal
                $ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
                $ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
                $ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
                $ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);

                echo "SetExpressCheckout API call failed. ";
                echo "Detailed Error Message: " . $ErrorLongMsg;
                echo "Short Error Message: " . $ErrorShortMsg;
                echo "Error Code: " . $ErrorCode;
                echo "Error Severity Code: " . $ErrorSeverityCode;

                $success=false;
            }
        }
        else
        {
            $PaymentProcessorSelected = "PayPal Direct Payment";
            if ((( $PaymentOption == "Visa") || ( $PaymentOption == "MasterCard") || ($PaymentOption == "Amex") ||
                ($PaymentOption == "Discover"))&& ( $PaymentProcessorSelected == "PayPal Direct Payment"))
            {

                //'------------------------------------
                //' The paymentAmount is the total value of
                //' the shopping cart, that was set
                //' earlier in a session variable
                //' by the shopping cart page
                //'------------------------------------
                $paymentAmount = 0;

                //'------------------------------------
                //' The currencyCodeType and paymentType
                //' are set to the selections made on the Integration Assistant
                //'------------------------------------
                $currencyCodeType = "EUR";
                $paymentType = "Authorization";

                //' Set these values based on what was selected by the user on the Billing page Html form

                /*
                $creditCardType              = "<<Visa/MasterCard/Amex/Discover>>"; //' Set this to one of the acceptable values (Visa/MasterCard/Amex/Discover) match it to what was selected on your Billing page
                $creditCardNumber            = "<<CC number>>"; //' Set this to the string entered as the credit card number on the Billing page
                $expDate                     = "<<Expiry Date>>"; //' Set this to the credit card expiry date entered on the Billing page
                $cvv2                        = "<<cvv2>>"; //' Set this to the CVV2 string entered on the Billing page
                $firstName                   = "<<firstName>>"; //' Set this to the customer's first name that was entered on the Billing page
                $lastName                    = "<<lastName>>"; //' Set this to the customer's last name that was entered on the Billing page
                $street                      = "<<street>>"; //' Set this to the customer's street address that was entered on the Billing page
                $city                        = "<<city>>"; //' Set this to the customer's city that was entered on the Billing page
                $state                       = "<<state>>"; //' Set this to the customer's state that was entered on the Billing page
                $zip                         = "<<zip>>"; //' Set this to the zip code of the customer's address that was entered on the Billing page
                $countryCode                 = "<<PayPal Country Code>>"; //' Set this to the PayPal code for the Country of the customer's address that was entered on the Billing page
                $currencyCode                = "<<PayPal Currency Code>>"; //' Set this to the PayPal code for the Currency used by the customer
                */
                $creditCardType=$zahlungsart;
                $creditCardNumber=$_POST['DATA_kartennummer'];
                $expDate=$_POST['DATA_month'].$_POST['DATA_year'];
                $cvv2=$_POST['DATA_sicherheitscode'];

                $firstName=db_result($result,0,'tblBenutzer.dtVorname');
                $lastName=db_result($result,0,'tblBenutzer.dtNachname');
                $street=db_result($result,0,'dtAdresse');
                $city=db_result($result,0,'dtOrtschaft');
                $state='';
                $zip=db_result($result,0,'dtPostleitzahl');
                $countryCode="LU";
                $currencyCode="EUR";


                /*
                '------------------------------------------------
                ' Calls the DoDirectPayment API call
                '
                ' The DirectPayment function is defined in PayPalFunctions.php included at the top of this file.
                '-------------------------------------------------
                */

                $resArray = DirectPayment ($paymentType, $paymentAmount, $creditCardType, $creditCardNumber, $expDate,
                    $cvv2, $firstName, $lastName, $street, $city, $state, $zip, $countryCode, $currencyCode);
                $ack = strtoupper($resArray["ACK"]);
                $_SESSION['nvpReqArray']['ACK']=$ack;
                if($ack=="SUCCESS" || $ack=="SUCCESSWITHWARNING")
                {
                    //Getting transaction ID from API responce.
                    $TransactionID = urldecode($resArray["TRANSACTIONID"]);

                    //echo "Your payment has been successfully processed";
                    $success=true;
                }
                else
                {
                    //Display a user friendly Error on the page using any of the following error information returned by PayPal
                    $ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
                    $ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
                    $ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
                    $ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);

                    echo "Direct credit card payment API call failed. ";
                    echo "Detailed Error Message: " . $ErrorLongMsg;
                    echo "Short Error Message: " . $ErrorShortMsg;
                    echo "Error Code: " . $ErrorCode;
                    echo "Error Severity Code: " . $ErrorSeverityCode;

                    $success=false;
                }
            }
        }
    }
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