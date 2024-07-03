<?php
//Session starten
session_start();
session_name('mySession');

//Verbindung mit der Datenbank
$db=mysqli_connect('db','pizzapolis','P@ssw0rd');
if(!$db) {
    error_log('Connection error: ' . $db->connect_error);    
} 
mysqli_select_db($db,'pizzapolis');

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