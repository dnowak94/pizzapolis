<?php
header('Content-type: text/html; charset=utf-8');
//Name :  Nowak Dominik
//Datum:  09/04/2013
//Klasse: T3IFAN

//Eingabe
include('../Config/config.php');
include('../Include/funktionen.php');

$showErrorUsername=0;
$showErrorPassword=0;
$showErrorLogin=0;

//Logout
if((isset($_GET['page']))&&($_GET['page']=='logout'))
{
    unset($_SESSION);
    session_destroy();
}

if(!isset($_SESSION['cmsloggedin'])) $_SESSION['cmsloggedin']=0;

//Verarbeitung

//handle login
//check if Username or Password empty
if(isset($_POST['BUTTON_sendLogin']))
{
	if($_POST['DATA_username']=='')
	{
		$showErrorUsername=1;
	}

	if($_POST['DATA_password']=='')
	{
		$showErrorPassword=1;
	}

	if(($_POST['DATA_username']!='')&&($_POST['DATA_password']!=''))
	{
        $qlogin="SELECT * FROM tblBenutzer WHERE
        dtUsername='".db_update($_POST['DATA_username'])."'
        AND
        dtPasswort=SHA1('".db_update($_POST['DATA_password'])."')
        AND istAdmin=1";
        $result=mysqli_query($db,$qlogin);
        if(mysqli_num_rows($result)==1)
        {
            $_SESSION['cmsloggedin']=1;
            $_SESSION['id_admin']=db_result($result,0,'id_benutzer');
        }
        else $showErrorLogin=1;
	}
}

//Ausgabe

//Debug
//echo '<pre>'.print_r($_SESSION,true).'</pre>';

?>    
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="default.css">
        <script src="Include/tinymce/tinymce.min.js"></script>
        <script>tinymce.init({selector:'textarea'});</script>
        <title>CMS</title>
	</head>
	<body>
    <?php
        // show Login Form
        if($_SESSION['cmsloggedin']==0)
        {
        ?>
            <div class="login">
                <span class="ueberschrift">Backend Login</span>
                <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="POST">
                    <table id="login">
                        <tr>
                            <td><label for="DATA_username" class="login">Username:</label></td>
                            <td><input type="text" name="DATA_username" class="<?=((($showErrorLogin==1)||
                                    ($showErrorUsername==1))?'redborder':'login')?>"></td>
                        </tr>
                        <tr>
                            <td><label for="DATA_password" class="login">Password:</label></td>
                            <td><input type="password" name="DATA_password" class="<?=((($showErrorLogin==1)||
                                    ($showErrorUsername==1))?'redborder':'login')?>"></td>
                        </tr>
                    </table>
                    <div class="btn_center">
                        <input type="submit" class="button" value="Login" name="BUTTON_sendLogin">
                    </div>
                    <?php
                    if($showErrorLogin==1)
                        echo '<span class="redtxt">Login Daten nicht korrekt</span>';
                    ?>
                </form>
            </div>
        <?php
        }

        //show Form
        if($_SESSION['cmsloggedin']==1)
        {
        ?>
            <header>
                <div id='cssmenu'>
                    <ul>
                        <li class='active'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=news'?>">
                                <span>News</span></a></li>
                        <li class='active'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=seiten'?>">
                                <span>Seiten</span></a></li>
                        <li class='active'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=gewinn'?>">
                                <span>Summe des Gewinns</span></a></li>
                        <li class='active'><a href="<?=$_SERVER['SCRIPT_NAME'].'?page=logout'?>">
                                <span>Logout</span></a></li>
                    </ul>
                </div>
            </header>
            <?php
            //News- oder Seitenverwaltung laden
            if(isset($_GET['page']))
            {
                switch($_GET['page'])
                {
                    case 'news':
                        include('Include/news.php');
                        break;
                    case 'seiten':
                        include('Include/seiten.php');
                        break;
                    case 'gewinn':
                        include('../Include/Restaurant/gewinn.php');
                        break;
                    default:
                        include('Include/news.php');
                        break;
                }
            }
            else include('Include/news.php');
        }
        ?>
	</body>
	
</html>