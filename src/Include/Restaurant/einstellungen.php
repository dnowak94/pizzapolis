<?php
if(isset($_SESSION['restaurant']))
{
    // Eingabe
    if(!isset($edit)) $edit='';
    if(!isset($error)) $error='';

    // Werte ändern

    if(isset($_GET['edit'])) $edit=$_GET['edit'];


    $filename='Include/Restaurant/informationen.ini.php';

    // Verarbeitung
    if(isset($_POST['btnSave']))
    {
        if(isset($_POST['DATA_lieferkosten'])) $edit='lieferkosten';
        if(isset($_POST['DATA_lieferwagen'])) $edit='lieferwagen';
        if(isset($_POST['DATA_kraftstoffpreis'])) $edit='kraftstoffpreis';
        if($edit!='lieferwagen')
        {
            $temp=check_preis($_POST['DATA_'.$edit]);
            if($temp[0]=='')
            {
                if(write_ini_file($filename,$edit,$temp[1])) $edit=''; // in .ini Datei schreiben
            }
            else $error=$temp[0];
        }
        else
        {
            if((is_numeric($_POST['DATA_lieferwagen']))&&(strpos($_POST['DATA_lieferwagen'],'.')==0)&&
                ($_POST['DATA_lieferwagen']>0))
            {
                if(write_ini_file($filename,'lieferwagen',$_POST['DATA_lieferwagen'])) $edit='';
            }
            else $error='!';
        }
    }
    $einstellungen=parse_ini_file($filename);
    $lieferkosten=number_format($einstellungen['lieferkosten'],2,',','.');
    $kraftstoffpreis=number_format($einstellungen['kraftstoffpreis'],3,',','.');

    // Ausgabe
?>
    <div class="einstellungen">
		<h1>Einstellungen</h1>
        <table class="info">
            <tr>
                <th class="vertikal">Lieferpreis für Kunden</th>
                <?php
                if(($edit=='lieferkosten')||(($edit=='lieferkosten')&&($error!='')))
                {
                ?>
                    <td>
                        <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=einstellungen'?>" method="post">
                            <input class="klein<?=($error!=''?' redborder':'')?>" type="text"
                                   value="<?=(isset($_POST['DATA_lieferkosten'])?$_POST['DATA_lieferkosten']:
                                       $lieferkosten)?>" name="DATA_lieferkosten">
                            <span class="euro">€</span>
                            <input type="submit" name="btnSave" value="Ändern">
                        </form>
                    </td>
                <?php
                }
                else
                {
                ?>
                    <td><span><?=$lieferkosten?>€</span></td>
                    <td><a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=einstellungen&amp;edit=lieferkosten'?>">
                            <span>Ändern</span></a></td>
                <?php
                }
                ?>
            </tr>
            <tr>
                <th class="vertikal">aktueller Kraftstoffpreis</th>
                <?php
                if(($edit=='kraftstoffpreis')||(($edit=='kraftstoffpreis')&&($error!='')))
                {
                    ?>
                    <td>
                        <form action="<?=$_SERVER['SCRIPT_NAME'].'?page=einstellungen'?>" method="post">
                            <input class="klein<?=($error!=''?' redborder':'')?>" type="text"
                               value="<?=(isset($_POST['DATA_kraftstoffpreis'])?$_POST['DATA_kraftstoffpreis']:
                                   $kraftstoffpreis)?>" name="DATA_kraftstoffpreis">
                            <span class="euro">€</span>
                            <input type="submit" name="btnSave" value="Ändern">
                        </form>
                    </td>
                <?php
                }
                else
                {
                    ?>
                    <td><span><?=$kraftstoffpreis?>€</span></td>
                    <td><a class="button" href="<?=$_SERVER['SCRIPT_NAME'].'?page=einstellungen&amp;'.
                            'edit=kraftstoffpreis'?>"><span>Ändern</span></a></td>
                <?php
                }
                ?>
            </tr>
        </table>
    </div>
<?php
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