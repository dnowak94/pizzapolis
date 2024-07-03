<?php
$path=str_replace('bezahlen','',dirname($_SERVER['SCRIPT_FILENAME']));
require($path.'Include/FPDF/fpdf.php');
class myFPDF extends FPDF
{
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','',10);
        $this->Cell(0,10,'Luxembourg, den '.date('d.m.Y'),0,0,'L');
    }
}
$pdf = new myFPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetMargins(20,20,20);
$pdf->SetAutoPageBreak(true,5);

// Rechnung (Titel)
$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,0,'Rechnung');
$pdf->Ln(10);
// Adresse des Restaurants
$pdf->SetFont('','',14);
$info=parse_ini_file($path.'Include/Restaurant/informationen.ini.php');
$pdf->MultiCell(50,8,'Pizzapolis'."\n".$info['strasse']."\n".$info['postleitzahl'].' '.$info['ortschaft']."\n".
    $info['telefonnummer'],0,'L');
$pdf->Ln(10);
// Rechnungsnummer und Bestellungsnummer abrufen
$query="SELECT MAX(id_rechnung) AS 'id_rechnung',fi_bestellung FROM tblRechnung";
$result=mysqli_query($query,$GLOBALS['db']);
$pdf->SetFontSize(16);
$pdf->Cell(0,5,'Rechnung nr. '.db_result($result,0,'id_rechnung'),0,0,'C');
$pdf->Ln(10);
// Kunde
// Bestellungsadresse abrufen
if(isset($_SESSION['lieferadresse']))
{
    $query= "SELECT *
            FROM tblBestellungsadresse,tblBenutzer
            WHERE id_bestellungsadresse=".$_SESSION['lieferadresse'].
            " AND fi_kunde=id_benutzer";
}
else
{
    $query= "SELECT * FROM tblAdresse,tblBenutzer
            WHERE fi_kunde=".$_SESSION['id_user'].
            " AND dtStandard=1".
            " AND fi_kunde=id_benutzer";
}
$result=mysqli_query($query,$GLOBALS['db']);


if(mysqli_num_rows($result)>0)
{
    $pdf->SetFont('','B');
    $pdf->Cell(0,5,'Kunde:');
    $pdf->Ln();
    $pdf->SetFont('','',12); // Arial,regular,12pt
    $pdf->MultiCell(50,5,db_result($result,0,'dtVorname').' '.db_result($result,0,'dtNachname')."\n".
        db_result($result,0,'dtAdresse')."\n".db_result($result,0,'dtPostleitzahl').' '.db_result($result,0,
        'dtOrtschaft')."\n".db_result($result,0,'dtE-Mail'));
}
$pdf->SetY($pdf->GetY()+20);
// bestellungsnummer herausfinden
$query="SELECT MAX(id_bestellung) AS 'id_bestellung' FROM tblBestellung";
$result=mysqli_query($query,$GLOBALS['db']);

// Gerichte für die Rechnung abfragen
$query= "SELECT * FROM tblBestehen_aus,tblGericht
        WHERE fi_bestellung=".db_result($result,0,'id_bestellung').
        " AND fi_gericht=id_gericht";

$result=mysqli_query($query,$GLOBALS['db']);

// Tabelle mit den Gerichten erstellen
//table header
$pdf->SetFont('','B',12);
$pdf->Cell(15,10,'Anzahl',1,0,'C');
$pdf->Cell(100,10,'Gericht',1,0,'C');
$pdf->Cell(25,10,'Einzelpreis',1,0,'C');
$pdf->Cell(30,10,'Gesamtpreis',1,0,'C');
$pdf->Ln();

$pdf->SetFont('','',12);
for($i=0;$i<mysqli_num_rows($result);$i++)
{
    $quantity=db_result($result,$i,'dtQuantitaet');
    $pdf->Cell(15,8,$quantity,1,0,'C');
    $pdf->Cell(100,8,db_result($result,$i,'dtBezeichnung'),1,0,'L');
    $preis=floatval(str_replace(',','.',db_result($result,$i,'dtPreis')));
    $pdf->Cell(25,8,number_format($preis,2,',','.').iconv('UTF-8','windows-1252','€'),1,0,'R');
    $preis=intval($quantity)*$preis;
    $pdf->Cell(30,8,number_format($preis,2,',','.').iconv('UTF-8','windows-1252','€'),1,0,'R');
    $pdf->Ln();
}

$pdf->SetY($pdf->GetY()+10);
// Lieferkosten
$pdf->Cell(140,8,'Lieferkosten',1,0);
$pdf->Cell(30,8,number_format($_SESSION['lieferart']*$info['lieferkosten'],2,',','.').
    iconv('UTF-8','windows-1252','€'),1,1,'R');
// Total ausgeben
$pdf->Cell(140,8,'Total',1,0,'L');
$pdf->Cell(30,8,number_format($_SESSION['Payment_Amount'],2,',','.').iconv('UTF-8','windows-1252','€'),1,1,'R');

// Footer
$pdf->Footer();

$query="SELECT dtUsername FROM tblBenutzer WHERE id_benutzer=".$_SESSION['id_user'];
$result=mysqli_query($query,$GLOBALS['db']);
$pdf->Output($path.'Rechnungen/rechnung_'.db_result($result,0,'dtUsername').'_'.date('Ymd@H-i').'.pdf','F');
?>