<?php
header('Content-type: text/html; charset=utf-8');
//Name :  Nowak Dominik
//Datum:  09/04/2013
//Klasse: T3IFAN

//Eingabe

//include der Konfigurationsdatei
include('Config/config.php');

//include der Funktionen
include('Include/funktionen.php');

// SESSION variablen
// initialisieren
if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin']=0;

// variablen initialisieren
$showErrorLogin=0;

//Verarbeitung

//handle login
//check if Username or Password empty
if(isset($_POST['BUTTON_sendLogin']))
{
	if(($_POST['DATA_username']=='')||($_POST['DATA_password']==''))
	{
		$showErrorLogin=1;
	}
	
	if(($_POST['DATA_username']!='')&&($_POST['DATA_password']!=''))
	{
		$qlogin="SELECT * FROM tblBenutzer WHERE
				(dtUsername='".db_update($_POST['DATA_username'])."')
				AND 
				(dtPasswort=SHA1('".db_update($_POST['DATA_password'])."'))
				AND istAktiviert=1";
		$result=mysqli_query($db, $qlogin);	
		if(mysqli_num_rows($result)==1) 
		{
			if((db_result($result,0,'istRestaurant')==1)||(db_result($result,0,'istAdmin')==1))
                $_SESSION['restaurant']=db_result($result,0,'id_benutzer');
            else $_SESSION['id_user']=db_result($result,0,'id_benutzer');
			$_SESSION['loggedin']=1;
		}	
		else
		{	
			$showErrorLogin=1;
		}	
	}
}

// sofort nach dem login bei User Seite = Bestellen bei Restaurant Seite=aktuelle Bestellungen
if((isset($_POST['BUTTON_sendLogin']))&&($showErrorLogin==0)&&(isset($_SESSION['id_user'])))
    $_GET['page']='bestellen';
if((isset($_POST['BUTTON_sendLogin']))&&($showErrorLogin==0)&&(isset($_SESSION['restaurant'])))
    $_GET['page']='bestellungen';

// Wochentage auf Deutsch definieren
if((isset($_GET['page']))&&((($_GET['page']=='lieferzeiten')&&(isset($_SESSION['restaurant'])))||
    ($_GET['page']=='bestellen')))
    $wochentage=array('Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag');

//Logout
if((isset($_GET['page']))&&($_GET['page']=='logout'))
{
    unset($_SESSION);
    session_destroy();
}

//Ausgabe

//Debug
//echo '<pre>'.print_r($errors,true).'</pre>';
//echo '<pre>'.print_r($data,true).'</pre>';
//echo '<pre>'.print_r($_SESSION,true).'</pre>';
?>    
<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<link rel="stylesheet" type="text/css" href="default.css">
        <script src="Include/JavaScripts/javascript.js"></script>
    <title>pizzapolis.lu</title>
	</head>
	<body>
		<header>
			<div id='cssmenu'>
				<ul>
				   <li class='active'><a href="<?=$_SERVER['SCRIPT_NAME']?>">
                           <span>Hauptseite</span></a></li>
				   <?php
				    if(isset($_SESSION['id_user']))
				    {
					?>
					    <li class='has-sub'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen'?>">
                                <span>Bestellen</span></a>
                            <ul>
                                <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellen'?>">
                                        <span>neue Bestellung</span></a></li>
                                <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=letztebestellungen'?>">
                                        <span>letzte Bestellungen</span></a></li>
                                <li class='last'><a href='<?=$_SERVER['SCRIPT_NAME'].'?page=allebestellungen'?>'>
                                        <span>alle Bestellungen</span></a></li>
                            </ul>
					    </li>
                        <li class='has-sub'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>">
                            <span>Mein Konto</span></a>
                            <ul>
                                <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>">
                                    <span>Informationen</span></a></li>
                                <li class='last'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=adressbuch'?>">
                                    <span>Adressbuch</span></a></li>
                                <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=username'?>">
                                    <span>Benutzername ändern</span></a></li>
                                <li class="last"><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=pw'?>">
                                    <span>Passwort ändern</span></a></li>
                            </ul>
                        </li>
				   <?php
				    }

                    if(isset($_SESSION['restaurant']))
                    {
                    ?>
                        <li class='active'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=bestellungen'?>">
                                <span>Bestellungen</span></a></li>
						<li class='has-sub'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=verwaltung'?>">
                                <span>Verwaltung</span></a>
						<ul>
                            <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=angebot'?>"><span>angebotene Gerichte</span>
                                </a></li>
                            <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=kraftstoffkosten'?>"><span>Kraftstoffkosten</span>
                                </a></li>
                            <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=gewinn'?>"><span>Summe des Gewinns</span>
                                </a></li>
                            <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=verkauf'?>"><span>verkaufte Produkte</span>
                                </a></li>
                            <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=einstellungen'?>"><span>Einstellungen</span>
                                </a></li>
							<li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=lieferzeiten'?>">
                                    <span>Lieferzeiten ändern</span></a></li>
						</ul>
                        <li class="has-sub"><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>"><span>Mein Konto</span>
                            </a>
                            <ul>
                                <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=username'?>">
                                    <span>Benutzername ändern</span></a></li>
                                <li class="last"><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=pw'?>">
                                    <span>Passwort ändern</span></a></li>
                            </ul>
                        </li>
                    <?php
                    }

					//query für das menu
					$query="SELECT * FROM tblSeite WHERE istMenu=1";
					
					/*
					$qlinks="SELECT * FROM pages WHERE (isMenu=1 AND isloggedin=".$_SESSION['loggedin'].")".
					(isset($_SESSION['id_user'])?'AND forUser=1':(isset($_SESSION['restaurant'])?'AND forRestaurant=1':''));
					*/
					$result=mysqli_query($db,$query);
					if(mysqli_num_rows($result)>0)
					{
						for($i=0;$i<mysqli_num_rows($result);$i++)
						{
							echo '<li class="active"><a href="'.$_SERVER['SCRIPT_NAME'].'?page='.
							db_result($result,$i,'id_seite').'">'.
							db_result($result,$i,'dtTitel').'</a>';
						}
					}
				    ?>
				</ul>
			</div>
			<?php
                if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin']=0;
                //Login
                if($_SESSION['loggedin']==0)
                {
                ?>
                    <div class="login">
                        <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="post">
                            <table class="login">
                                <tr class="login">
                                    <td class="login">Username</td>
                                    <td class="login">Password</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr class="login">
                                    <td class="login"><input type="text" name="DATA_username"
                                        <?=($showErrorLogin==1?' class="errorlogin"':' class="login"')?> /></td>
                                    <td class="login"><input type="password" name="DATA_password"
                                        <?=($showErrorLogin==1?' class="errorlogin"':' class="login"')?> /></td>
                                    <td class="login"><input class="btnlogin" type="submit" name="BUTTON_sendLogin"
                                         value="Login" /></td>
                                </tr>
                                <tr class="login">
                                    <td class="login"><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=register'?>">
                                        <span class="register">Noch nicht registriert?</span></a></td>
                                    <td class="login"><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=pwvergessen'?>">
                                        <span class="register">Passwort vergessen?</span></a></td>
                                    <td>&nbsp;</td>
                                </tr>
                            </table>
                            <?php
                            if($showErrorLogin==1) echo '<span class="errorlogin">Incorrect username or password!</span>';
                            ?>
                        </form>
                    </div>
                <?php
                }
                else
                {
                    //Logout
                    echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?page=logout"><span class="logout">Logout</span></a>';
                }
            ?>
	</header>

    <!--Seite laden-->
    <div class="page">
    <?php
    // Array mit Seiten bei denen Sidebar nicht angezeigt wird
    $showNotOnPages=array('gewinn','bestellen','angebot','verwaltung');
    if((isset($_GET['page']))&&(!in_array($_GET['page'],$showNotOnPages))||(!isset($_GET['page'])))
    {
        ?>
        <!--Sidebar-->
        <div class="sidebar">
            <?php
            // letzte Bestellungen
            if(isset($_SESSION['id_user']))
            {
                $query="SELECT * FROM tblBestellung,tblBestehen_aus,tblGericht
                        WHERE fi_kunde=".$_SESSION['id_user'].
                    " AND id_bestellung=fi_bestellung
                    AND fi_gericht=id_gericht
                    AND dtFoto<>''
                    GROUP BY id_gericht
                    ORDER BY dtLieferdatum DESC,dtLieferzeit DESC
                    LIMIT 0,3";
                $result=mysqli_query($db,$query);
                ?>
                <div class="box">
                    <div class="letzte">
                        <h4>letzte Bestellungen</h4>
                        <?php
                        if(mysqli_num_rows($result)>0)
                        {
                            $path=dirname($_SERVER['SCRIPT_NAME']).'/Pictures/gerichte/';
                            ?>
                            <table>
                                <?php
                                for($i=0;$i<mysqli_num_rows($result);$i++)
                                {
                                    ?>
                                    <tr>
                                        <td><img class="klein" src="<?=$path.db_result($result,$i,'dtFoto')?>"
                                                 alt="<?=db_result($result,$i,'dtBezeichnung')?>"></td>
                                        <td>
                                            <p class="side_gericht"><?=db_result($result,$i,'dtBezeichnung')?></p>
                                            <p class="side_zutaten"><?=db_result($result,$i,'dtZutaten')?></p>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </table>
                            <a href="<?=$_SERVER['SCRIPT_NAME'].'?page=allebestellungen'?>"><span class="mehr">mehr</span>
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            <?php
            }
            ?>
            <!--News-->
            <div class="box">
                <h3><a class="link" href="<?=$_SERVER['SCRIPT_NAME'].'?page=news'?>">News</a></h3>
                <div class="side_news">
                    <?php
                    $query="SELECT * FROM tblNews
                            WHERE istSichtbar=1
                            ORDER BY dtZeitstempel DESC,dtTitel
                            LIMIT 0,3";
                    $result=mysqli_query($db,$query);
                    if(mysqli_num_rows($result)>0)
                    {
                        for($i=0;$i<mysqli_num_rows($result);$i++)
                        {
                            ?>
                            <h4><?=db_result($result,$i,'dtTitel')?></h4>
                            <p class="text"><?=cutcontent(db_result($result,$i,'dtInhalt'),5)?><a href="
                            <?=$_SERVER['SCRIPT_NAME'].'?page=news&amp;id='.db_result($result,$i,'id_news')?>"><br>
                                    <span class="mehr">weiter lesen</span></a></p>
                            <p class="update">letzte Änderung: <?=db_result($result,$i,'dtZeitstempel')?></p>
                            <?php
                            if($i<mysqli_num_rows($result)-1) echo '<hr>';
                        }
                    }
                    else echo '<p class="text">keine News vorhanden</p>'."\n";
                    ?>
                </div>
            </div>
            <?php
            // als Benutzer
            if(isset($_SESSION['id_user']))
            {
                ?>
                <div class="box">
                    <h3>Benutzerkonto</h3>
                    <ul>
                        <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=username'?>">
                                <span class="useroplink">Benutzername ändern</span></a></li>
                        <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=pw'?>">
                                <span class="useroplink">Passwort ändern</span></a></li>
                        <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto'?>">
                                <span class="useroplink">Informationen bearbeiten</span></a></li>
                        <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=adressbuch'?>">
                                <span class="useroplink">Adressbuch verwalten</span></a></li>
                    </ul>
                </div>
            <?php
            }
            // als Restaurantverwalter
            if(isset($_SESSION['restaurant']))
            {
                ?>
                <div class="box">
                    <span>Benutzerkontenoperationen:</span>
                    <ul>
                        <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=username'?>">
                                <span class="useroplink">Benutzername ändern</span></a></li>
                        <li><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=konto&amp;edit=pw'?>">
                                <span class="useroplink">Passwort ändern</span></a></li>
                    </ul>
                </div>
            <?php
            }
            ?>
            <!--Anzahl Registrierungen-->
            <ul>
                <?php
                $query="SELECT COUNT(*) AS 'anzahl' FROM tblBenutzer";
                $result=mysqli_query($db,$query);
                if(mysqli_num_rows($result)>0)
                    $anzahl=db_result($result,0,'anzahl');
                else $anzahl=0;
                ?>
                <li><span class="nbbenutzer">registrierte Benutzer : <?=$anzahl?></span></li>
                <li><span class="nbbenutzer">registrierte Restaurants : 1</span></li>
            </ul>

            <span class="clearfix"></span>
        </div>
    <?php
    }

    // Bestellung erfolgreich
    if((isset($_GET['bestellt']))&&($_GET['bestellt']=='true')&&(isset($_SESSION['id_user'])))
    {
        $query="SELECT `dtE-Mail`
        FROM tblBenutzer
        WHERE id_benutzer=".$_SESSION['id_user'];
        $result=mysqli_query($db,$query);
        success('Ihre Bestellung wurde erfolgreich aufgenommen und eine E-Mail wurde mit der '.
            'Rechnung an ihre E-Mail Adresse "'.db_result($result,0,'dtE-Mail').'" gesendet.');
    }

    // einzelne Seiten laden
    if(isset($_GET['page']))
    {
        ?>
            <?php
            //Seite anzeigen oder Fehler ausgeben
            if(valid_id('tblSeite','id_seite',$_GET['page']))
            {
                $id=((isset($_GET['page'])&&(valid_id('tblSeite','id_seite',$_GET['page'])))?$_GET['page']:'');
                $query="SELECT * FROM tblSeite".
                    ($id!=''?" WHERE id_seite=".$_GET['page']:'').
                    (!((isset($_GET['action']))&&($_GET['action']=='preview')&&(isset($_SESSION['id_admin'])))?
                        ($id!=''?" AND ":" WHERE ")."istSichtbar=1":'');
                $result=mysqli_query($db,$query);
                if(mysqli_num_rows($result)>0)
                {
                    ?>
                    <!--Inhalt aus der Datenbank laden-->
                    <div class="content">
                        <?=db_result($result,0,'dtInhalt')?>
                    </div>
                <?php
                }
            }
            /*else
            {
                // TODO falsche Seite Fehlerausgabe
                ?>
                <div class="falsche_seite">
                    <p>Sie dürfen diese Seite nicht sehen.</p>
                </div>
            <?php
            }*/

            if(!is_numeric($_GET['page']))
            {
                if((!isset($_SESSION['id_user']))&&(!isset($_SESSION['restaurant'])))
                {
                    switch($_GET['page'])
                    {
                        // Registrierung
                        case 'register':
                            include('Include/registrierung.php');
                            break;
                        // Passwort vergessen und Passwort zurücksetzen
                        case 'pwvergessen':
                            include('Include/passwort_vergessen.php');
                            break;
                        case 'resetpw':
                            include('Include/passwort_vergessen.php');
                            break;
                        default:
                            include('Include/home.php');
                    }
                }
                else
                {
                    // Benutzerfunktionen
                    if(isset($_SESSION['id_user']))
                    {
                        switch($_GET['page'])
                        {
                            // Bestellen
                            case 'bestellen':
                                include('Include/Benutzer/bestellen.php');
                                include('Include/Benutzer/warenkorb.php');
                                break;

                            //letzte Bestellungen
                            case 'letztebestellungen':
                                include('Include/Benutzer/alte_bestellungen.php');
                                break;
                            // alle Bestellungen
                            case 'allebestellungen':
                                include('Include/Benutzer/alte_bestellungen.php');
                                break;

                            // Informationen bearbeiten
                            case 'konto':
                                include('Include/benutzerkonto.php');
                                break;

                            // Adressbuch
                            case 'adressbuch':
                                include('Include/Benutzer/adressbuch.php');
                                break;
                            // Home laden
                            default:
                                include('Include/home.php');
                                break;
                        }
                    }
                    else
                    {
                        // Restaurantfunktionen
						switch($_GET['page'])
                        {
                            // Konto
                            case 'konto':
                                include('Include/benutzerkonto.php');
                                break;

                            // Verwaltung
                            case 'verwaltung':
                                // Summe des Gewinns
                                include('Include/Restaurant/gewinn.php');
                                break;

                            // angebotenene Gerichte verwalten
                            case 'angebot':
                                include('Include/Restaurant/angebotene_gerichte.php');
                                break;
                            // aktuelle Bestellungen
                            case 'bestellungen':
                                include('Include/Restaurant/aktuelle_bestellungen.php');
                                break;
                            // Lieferzeiten ändern
                            case 'lieferzeiten':
                                include('Include/Restaurant/lieferzeiten.php');
                                break;
                            // Summe des Gewinns
                            case 'gewinn':
                                include('Include/Restaurant/gewinn.php');
                                break;
                            // Einstellungen zu Lieferungen
                            case 'einstellungen':
                                include('Include/Restaurant/einstellungen.php');
                                break;
                            // verkaufte Gerichte anzeigen
                            case 'verkauf':
                                include('Include/Restaurant/verkaufte_produkte.php');
                                break;
                            case 'kraftstoffkosten':
                                include('Include/Restaurant/kraftstoffkosten.php');
                                break;
                            // Home laden
                            default:
                                include('Include/home.php');
                                break;
                        }
                    }
                }
            }
        }
        else include('Include/home.php');
        ?>
        <span class="clearfix"></span>
        </div>
	</body>
</html>
