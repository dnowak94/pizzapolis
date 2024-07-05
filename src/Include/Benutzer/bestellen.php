<?php
//Eingabe
if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin']=0;
// als Kunde eingeloggt
if(($_SESSION['loggedin']==1)&&(isset($_SESSION['id_user'])))
{
    // Eingabe

    // GET id und kategorie speichern
    $id=(isset($_GET['id'])?(valid_id('tblGericht','id_gericht',$_GET['id'])?$db->real_escape_string($_GET['id']):''):
        '');

    $kategorie= (isset($_GET['kategorie'])?(valid_id('tblKategorie','id_kategorie',$_GET['kategorie'])?
        $db->real_escape_string($_GET['kategorie']):''):'');

    // String des gesuchten Gerichtes speichern
    $search=(isset($_GET['search'])?$db->real_escape_string($_GET['search']):'');

    // Ausgabe

    //Kategorien in der Sidebar
    ?>
    <div id="kategorie">
        <ul>
            <li>Kategorie</li>
            <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen&amp;kategorie=Alle'
                ?>"><span>Alle</span></a></li>
            <?php
            $query="SELECT * FROM tblKategorie";
            $result=mysqli_query($db,$query);

            for($i=0;$i<mysqli_num_rows($result);$i++)
            {
                ?>
                <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen&amp;kategorie='.
                        db_result($result,$i,'id_kategorie')?>"><span><?=db_result($result,$i,'dtBezeichnung')?></span>
                    </a></li>
            <?php
            }
            ?>
        </ul>
    </div>

    <?php
    // nach Gericht suchen
    ?>
    <div class="suchen">
        <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="get">
        <table>
            <tr>
                <td><span>Suche:</span></td>
                <td>
                    <input type="hidden" name="page" value="bestellen">
                    <input type="text" name="search" placeholder="Suchen">
                </td>
            </tr>
        </table>
        </form>
    </div>
    <br>
    <br>
    <?php
    //bestellbare oder gefundene Gerichte anzeigen
    $query="SELECT * FROM tblGericht".
        ($kategorie!=''?' WHERE fi_kategorie='.$kategorie:'').
        ' ORDER by dtBezeichnung';
    if(isset($_GET['search']))
        $query="SELECT * FROM tblGericht WHERE dtBezeichnung LIKE '%".$db->real_escape_string($_GET['search'])
            ."%' ORDER by dtBezeichnung";

    $result_all=mysqli_query($db,$query);
    ?>
    <div class="bestellen">
        <?php
        if(mysqli_num_rows($result_all)>0)
        {
            ?>
            <table class="tabelle">
                <tr>
                    <th class="horizontal" colspan="2">Gericht</th>
                    <th class="horizontal">Preis</th>
                    <th>&nbsp;</th>
                </tr>
                <?php
                for($i=0;$i<mysqli_num_rows($result_all);$i++)
                {
                    ?>
                    <tr <?=($i==mysqli_num_rows($result_all)-1?'class="extrema"':'')?>>
                        <td>
                            <img src="Pictures/gerichte/<?=(db_result($result_all,$i,'dtFoto')!=''?
                                db_result($result_all,$i,'dtFoto'):'kein_bild.jpg')?>" alt="bild">
                        </td>
                        <td>
                            <span class="gericht"><?=db_result($result_all,$i,'dtBezeichnung')?></span><br>
                            <span class="zutaten"><?=db_result($result_all,$i,'dtZutaten')?></span>
                        </td>
                        <td>
                            <span><?=number_format(db_result($result_all,$i,'dtPreis'),2,',','.').'€'?></span>
                        </td>
                        <td>
                            <form action="<?=$_SERVER['SCRIPT_NAME']?>?page=bestellen" method="POST">
                                <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen&amp;action=add&amp;id='.
                                    db_result($result_all,$i,'id_gericht').($kategorie!=''?'&amp;kategorie='.
                                    $kategorie:'').($search!=''?'&amp;search='.$search:'')?>">
                                    <img src="Pictures/design/shopping-cart-insert.png"
                                         alt="in den Warenkorb">
                                </a>
                            </form>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </table>
        <?php
        }
        else echo '<span>Es wurden leider keine entsprechenden Gerichte gefunden.</span>';
        ?>
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