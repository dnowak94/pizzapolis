<?php
//Session starten
session_start();
session_name('mySession');

//Verbindung mit der Datenbank
# mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db=new mysqli('db:3306','pizzapolis','P@ssw0rd', 'pizzapolis');
if ($db->connect_errno) {
    throw new RuntimeException('mysqli connection error: ' . $db->connect_error);
}
$db->set_charset('utf8mb4');
if ($db->errno) {
    throw new RuntimeException('mysqli error: ' . $db->error);
}
printf("Success... %s\n", mysqli_get_host_info($db));

//Funktion zum kürzen des Inhalts
function cutcontent($content,$limit)
{

    $content = trim(strip_tags($content));
    // Inhalt auf den Wert limit verkürzen
    //nur ein wort -> alles rausschreiben
    if(strpos($content,' ')>0)
    {
        //Content kürzen
        $output='';
        $i=0;
        while(($i<$limit)&&(strlen($content)>0))
        {
            $output.= substr($content,0,strpos($content,' ')+1);
            $content=str_replace(substr($content,0,strpos($content,' ')+1),'',$content);
            $i++;
        }
        $output.='...';
    }
    else
        // nur ein Wort
        $output=$content;
    return $output;
}
?>