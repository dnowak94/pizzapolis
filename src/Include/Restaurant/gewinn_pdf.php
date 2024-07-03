<?php
// Verbindung mit Datenbank
include('../../Config/config.php');
include('../funktionen.php');

// Eingabe
// Input-Felder mit Werten aus dem Text wieder in ein Array schreiben
$inputs=array();
$text=$_POST['inputs'];
$i=0;
while(strpos($text,'/')>0)
{
    $inputs[$i]=explode(';',substr($text,0,strpos($text,'/')));
    $text=substr_replace($text,'',0,strpos($text,'/')+1);
    $i++;
}

// letzte Gewinnauswertung auf heute setzen
$filename='informationen.ini.php';
$einstellungen=parse_ini_file($filename);
$letztes_datum=new DateTime($einstellungen['letzte_gewinnauswertung']);
write_ini_file($filename,'letzte_gewinnauswertung',"'".date('Y-m-d')."'");

// Ausgabe
//echo '<pre>'.print_r($inputs,true).'</pre>';
require('../FPDF/fpdf.php');
class myFPDF extends FPDF
{
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','',10);
        $this->Cell(0,10,'Luxembourg, den '.date('d.m.Y'),0,0,'L');
    }
}
$pdf = new myFPDF('L','mm');
$pdf->AddPage();
$pdf->SetMargins(20,20,20);
$pdf->SetAutoPageBreak(true,20);
$pdf->SetFont('Arial','B',18);
$pdf->SetX(20);
$pdf->Cell(0,10,'Gewinn vom '.date('d/m/Y'),1,1,'C');
$pdf->Ln(10);
$pdf->SetFont('','B',16);
$pdf->Cell(115,10,'Einnahmen',1,0,'L');
$pdf->SetX($pdf->GetX()+27); // 297-2*margin(20)-2*115=27
$pdf->Cell(115,10,'Ausgaben',1,1,'L');
$query="SELECT * FROM tblKategorie";
$result_all=mysqli_query($db,$query);
$gerichte=0;
$gesamtausgaben=0;
for($i=0;$i<mysqli_num_rows($result_all);$i++)
{
    $query="SELECT SUM(dtQuantitaet) AS `anzahl`, ROUND(SUM(dtQuantitaet*dtPreis),2) AS `dtPreis`
            FROM tblBestellung
            LEFT JOIN tblBestehen_aus ON id_bestellung=fi_bestellung
            LEFT JOIN tblGericht ON fi_gericht=id_gericht
            WHERE ((dtLieferdatum>='".$letztes_datum->format('Y-m-d')."' AND dtLieferdatum<'".
            date('Y-m-d')."'))
            AND fi_kategorie=".mysql_result($result_all,$i,'id_kategorie').
            " GROUP BY fi_kategorie
            ORDER BY dtPreis DESC";
    $result=mysqli_query($db,$query);
    if(mysqli_num_rows($result)>0)
    {
        $anzahl=mysql_result($result,0,'anzahl');
        $preis=mysql_result($result,0,'dtPreis');
    }
    else
    {
        $anzahl=0;
        $preis=0;
    }
    $preis=floatval($preis);
    if($i==0)
    {
        $pdf->SetFont('','B',14);
        $pdf->SetX($pdf->GetX()+5);
        $pdf->Cell(110,10,'verkaufte Produkte:',1,0);

        // Ausgaben
        $pdf->SetX($pdf->GetX()+32); // 297-2*margin(20)-2*[115 - 5(einrücken)]=32
        // Überschriften
        if(strpos($inputs[$i][0],':')>0)
        {
            $pdf->SetFont('','B',12);
            $pdf->Cell(0,10,$inputs[$i][0],1,1);
        }
        else
        {
            // Leerzeile
            if(($inputs[$i][0]=='')&&($inputs[$i][1]==''))
            {
                $pdf->Cell(70,10,'',1,0);
                $pdf->Cell(0,10,'',1,1);
            }
        }

        $pdf->SetX($pdf->GetX()+5);
        $pdf->SetFont('','B',12);
        $pdf->Cell(55,10,'Kategorie',1,0,'C');
        $pdf->Cell(20,10,'Anzahl',1,0,'C');
        $pdf->Cell(35,10,'Ertrag',1,0,'C');
        $pdf->SetFont('','',10);
    }

    // Einnahmen
    if($i>1)
    {
        $pdf->SetFont('','',10);
        $pdf->SetX($pdf->GetX()+5);
        $pdf->MultiCell(55,10,mysql_result($result_all,$i,'dtBezeichnung'),1,0);
        $pdf->Cell(20,10,$anzahl,1,0,'C');
        $pdf->Cell(35,10,number_format($preis,2,',','.').iconv('UTF-8','windows-1252','€'),1,0,'R');
        $gerichte+=$preis;
    }

    // Ausgaben
    if(($i>0)&&($i<count($inputs)))
    {
        $pdf->SetX($pdf->GetX()+32); // 297-2*margin(20)-2*[115 - 5(einrücken)]=32
        // Überschriften
        if(strpos($inputs[$i][0],':')>0)
        {
            $pdf->SetFont('','B',12);
            $pdf->Cell(0,10,$inputs[$i][0],1,1);
        }
        else
        {
            // Leerzeile
            if(($inputs[$i][0]=='')&&($inputs[$i][1]==''))
            {
                $pdf->Cell(70,10,'',1,0);
                $pdf->Cell(0,10,'',1,1);
            }
            else
            {
                // wenn _g enthalten -> Ganzahlen
                // _g wegschneiden
                $pdf->MultiCell(70,10,(strpos($inputs[$i][0],'_g')>0?substr($inputs[$i][0],0,strpos($inputs[$i][0],
                    '_g')):$inputs[$i][0]),1,0);
                // Ganzzahlen
                if(strpos($inputs[$i][0],'_g')>0)
                    $pdf->Cell(0,10,$inputs[$i][1],1,1,'R');
                else
                {
                    $pdf->Cell(0,10,($inputs[$i][1]>0?number_format($inputs[$i][1],2,',','.').
                        iconv('UTF-8','windows-1252','€'):''),1,1,'R');
                    $gesamtausgaben+=$inputs[$i][1];
                }
            }
        }
    }
}
// Gesamteinnahmen und -ausgaben
// Gesamt
//Einnahmen durch Gerichte
$pdf->SetX($pdf->GetX()+5);
$pdf->SetFont('','B',12);
$pdf->Cell(75,10,'Gesamt:',1,0,'C');
$pdf->Cell(35,10,number_format($gerichte,2,',','.').iconv('UTF-8','windows-1252','€'),1,0,'R');
// Ausgaben
// Leerzeile (zum Auffüllen)
$pdf->SetX($pdf->GetX()+32); // 297-2*margin(20)-2*115+5(Einrücken)=32
$pdf->Cell(70,10,'',1,0);
$pdf->Cell(0,10,'',1,1);
// Einnahmen durch Lieferungen
$pdf->SetX($pdf->GetX()+5);
$query= "SELECT SUM(dtLieferart*".$einstellungen['lieferkosten'].") AS 'liefereinnahmen'
                                    FROM tblBestellung
                                    WHERE ((dtLieferdatum>='".$letztes_datum->format('Y-m-d')."' AND dtLieferdatum<'".
    date('Y-m-d')."'))";
$result=mysqli_query($db,$query);
$lieferungen=floatval(mysql_result($result,0,'liefereinnahmen'));
$pdf->Cell(75,10,'Einnahmen durch Lieferungen:',1,0,'C');
$pdf->Cell(35,10,number_format($lieferungen,2,',','.').iconv('UTF-8','windows-1252','€'),1,0,'R');
// Leerzeile (zum Auffüllen)
$pdf->SetX($pdf->GetX()+32); // 297-2*margin(20)-2*115+5(Einrücken)=32
$pdf->Cell(70,10,'',1,0);
$pdf->Cell(0,10,'',1,1);
// Gesamteinnahmen
$pdf->SetX($pdf->GetX()+5);
$pdf->Cell(75,10,'Gesamteinnahmen:',1,0,'C');
$gesamteinnahmen=$gerichte+$lieferungen;
$pdf->Cell(35,10,number_format($gesamteinnahmen,2,',','.').iconv('UTF-8','windows-1252','€'),1,0,'R');
// Gesamtausgaben
$pdf->SetX($pdf->GetX()+32); // 297-2*margin(20)-2*115+5(Einrücken)=32
$pdf->Cell(70,10,'Gesamtausgaben:',1,0,'C');
$pdf->Cell(0,10,number_format($gesamtausgaben,2,',','.').iconv('UTF-8','windows-1252','€'),1,1,'R');
$pdf->Ln(6);
$pdf->SetX(80);
$pdf->Cell(70,10,'Gewinn:',1,0,'L');
$pdf->Cell(70,10,number_format($gesamteinnahmen-$gesamtausgaben,2,',','.').iconv('UTF-8','windows-1252','€'),1,1,'R');
// PDF als Download
$pdf->Output('gewinn_'.date('Y-m-d').'.pdf','D');
?>