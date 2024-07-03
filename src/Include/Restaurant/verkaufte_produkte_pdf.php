<?php
// Verbindung mit Datenbank
include('../funktionen.php');
include('../../Config/config.php');

// letzte Auswertung der verkauften Produkte auf heute setzen
$filename='informationen.ini.php';
$einstellungen=parse_ini_file($filename);
$letztes_datum=new DateTime($einstellungen['letzte_auswertungprodukte']);
write_ini_file($filename,'letzte_auswertungprodukte',"'".date('Y-m-d')."'");

require('../FPDF/fpdf.php');
$pdf = new FPDF('L','mm');
$pdf->AddPage();
$pdf->SetMargins(20,20,20);
$pdf->SetAutoPageBreak(true,20);
$pdf->SetFont('Arial','B',18);
// Überschrift
$pdf->SetX(20);
$pdf->Cell(0,10,'verkauften Produkte vom '.date('d.m.Y',strtotime($letztes_datum->format('d.m.Y'))).' bis '.
    date('d.m.Y'),1,1,'C');
$pdf->Ln(10);
// Tabelle
// Header
$pdf->SetFont('','B',12);
$pdf->Cell(85,10,'Produkt',1,0,'C');
$pdf->Cell(85,10,'Zutaten',1,0,'C');
$pdf->Cell(35,10,'Anzahl',1,0,'C');
$pdf->Cell(52,10,'Einnahmen',1,1,'C');
$pdf->SetFont('','',12);

// gibt es Bestellungen seit der letzten Auswertung
$query=" SELECT *
         FROM tblBestellung
         WHERE dtLieferdatum>='".$letztes_datum->format('Y-m-d')."' AND dtLieferdatum<'".date('Y-m-d')."'";
$result=mysqli_query($db,$query);

$gesamt=0;
if(mysqli_num_rows($result)>0)
{
    // es gibt welche
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
        $pdf->MultiCell(85,10,mysql_result($result,$i,'dtBezeichnung'),1,0);
        $pdf->SetFont('','',10);
        $pdf->MultiCell(85,10,mysql_result($result,$i,'dtZutaten'),1,0);
        $pdf->SetFont('','',12);
        $pdf->MultiCell(35,10,(mysql_result($result,$i,'anzahl')!=NULL?mysql_result($result,$i,'anzahl'):0),1,0,'C');
        $pdf->MultiCell(52,10,number_format(floatval(mysql_result($result,$i,'dtPreis')),2,',','.').
            iconv('UTF-8','windows-1252','€'),1,1,'R');
        $gesamt+=floatval(mysql_result($result,$i,'dtPreis'));
    }
}
else
{
    $query="SELECT * FROM tblGericht";
    $result=mysqli_query($db,$query);
    for($i=0;$i<mysqli_num_rows($result);$i++)
    {
        $pdf->MultiCell(85,10,mysql_result($result,$i,'dtBezeichnung'),1,0);
        $pdf->SetFont('','',10);
        $pdf->MultiCell(85,10,mysql_result($result,$i,'dtZutaten'),1,0);
        $pdf->SetFont('','',12);
        $pdf->MultiCell(35,10,0,1,0,'C');
        $pdf->MultiCell(52,10,number_format(0,2,',','.').iconv('UTF-8','windows-1252','€'),1,1,'R');
    }
}
$pdf->Ln(5);
$pdf->SetX(190);
$pdf->SetFont('','B',14);
$pdf->Cell(35,10,'Gesamt:',0,0,'R');
$pdf->Cell(52,10,number_format($gesamt,2,',','.').iconv('UTF-8','windows-1252','€'),0,0,'R');
$pdf->Output('verkaufte_produkte_'.$letztes_datum->format('Y-m-d').'_'.date('Y-m-d').'.pdf','D');
?>