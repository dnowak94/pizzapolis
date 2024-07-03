<?php
// alle News anzeigen
if((isset($_GET['page']))&&($_GET['page']=='news'))
{
    ?>
    <div class="news_all">
        <h1>News</h1>
        <div class="einrucken">
            <?php
            $id=((isset($_GET['id'])&&(valid_id('tblNews','id_news',$_GET['id'])))?$_GET['id']:'');
            $query="SELECT * FROM tblNews".
                    ($id!=''?" WHERE id_news=".$_GET['id']:'').
                    (!((isset($_GET['action']))&&($_GET['action']=='preview')&&(isset($_SESSION['id_admin'])))?
                    ($id!=''?" AND ":" WHERE ")."istSichtbar=1":'').
                    " ORDER BY dtZeitstempel DESC";
            $result=mysqli_query($db,$query);

            if(mysqli_num_rows($result)>0)
            {
                for($i=0;$i<mysqli_num_rows($result);$i++)
                {
                ?>
                    <h3><a class="link" href="<?=$_SERVER['SCRIPT_NAME'].'?page=news&amp;id='.db_result($result,$i,
                            'id_news')?>"><span><?=db_result($result,$i,'dtTitel')?></span></a></h3>
                    <div class="content"><?=db_result($result,$i,'dtInhalt')?></div>
                    <p class="updated"><?=db_result($result,$i,'dtZeitstempel')?></p>
            <?php
                    if($i<mysqli_num_rows($result)-1) echo '<hr>';
                }
            }
            else echo '<p>News nicht verfügbar.</p>';
            ?>
        </div>
    </div>
    <?php
}
else
{
    // News im kleinen anzeigen
    $query="SELECT * FROM tblNews
            WHERE istSichtbar=1
            ORDER BY dtZeitstempel DESC,dtTitel";
    $result=mysqli_query($db,$query);
    if(mysqli_num_rows($result)>0)
    {
        ?>
        <div class="news">
            <?php
            for($i=0;$i<mysqli_num_rows($result);$i++)
            {
                ?>
                <h3><?=db_result($result,$i,'dtTitel')?></h3>
                <p class="news"><?=cutcontent(db_result($result,$i,'dtInhalt'),10)?>
                    <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=news&amp;id='.db_result($result,$i,'id_news')?>">
                        <span class="mehr">weiter lesen</span></a>
                </p>
                <p class="update">letzte Änderung :<?=db_result($result,$i,'dtZeitstempel')?></p>
                <hr>
                <?php
            }
            ?>
        </div>
    <?php
    }
    ?>

    <div class="gerichte">
        <h3>neueste Gerichte</h3>
        <?php
        $query="SELECT * FROM tblGericht
                WHERE dtFoto<>''
                ORDER BY dtZeitstempel DESC,dtBezeichnung
                LIMIT 0,3";
        $result=mysqli_query($db,$query);
        if(mysqli_num_rows($result)>0)
        {
            $path=dirname($_SERVER['SCRIPT_NAME']);
            ?>
            <table>
                <?php
                for($i=0;$i<mysqli_num_rows($result);$i++)
                {
                ?>
                    <tr>
                        <td>
                            <img src="<?=$path.'/Pictures/gerichte/'.db_result($result,$i,'dtFoto')?>"
                                alt="<?=db_result($result,$i,'dtBezeichnung')?>">
                        </td>
                        <td>
                            <p class="gericht"><?=db_result($result,$i,'dtBezeichnung')?></p>
                            <p class="zutaten"><?=db_result($result,$i,'dtZutaten')?></p>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </table>
            <?php
        }
        ?>
    </div>
<?php
}
?>