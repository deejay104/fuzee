<?php
/*
    Fuzee
    Copyright (C) 2017 Matthieu Isorez

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
// ---- Header de la page
	// Date du passé
	header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	// toujours modifié
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	
	// HTTP/1.0
	header("Pragma: no-cache");

	// Charset
	header('Content-type: text/html; charset=ISO-8859-1');

	//error_reporting( E_ALL ^ E_NOTICE ^ E_DEPRECATED );

// ---- Check if authenticated
	session_start();

	if ((isset($_SESSION['gl_uid'])) && ($_SESSION['gl_uid']>0))
	  { $gl_uid = $_SESSION['gl_uid']; }
	else
	  { include "login.php"; exit; }

// ---- Language
	$lang="fr";

// ---- Charge la config  
	if (!file_exists("config/config.inc.php"))
	  { FatalError("Configuration file is not found","Configuration file 'config/config.inc.php' does not exist."); }
	if (!file_exists("config/variables.inc.php"))
	  { FatalError("Variables file is not found","Configuration file 'config/variablesd.inc.php' does not exist."); }

	require ("config/config.inc.php");
	require ("config/variables.inc.php");
	require ("modules/lib/functions.inc.php");
	require ("modules/lib/object.inc.php");

	$time_total_start = microtime_float();

	require ("modules/lang/".$lang.".inc.php");

	if ($MyOpt["timezone"]!="")
	  { date_default_timezone_set($MyOpt["timezone"]); }

// ---- Gestion des thèmes

// Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_2 like Mac OS X; fr-fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8H7 Safari/6533.18.5
// Mozilla/5.0 (Linux; U; Android 2.2.1; fr-fr; HTC_Wildfire-orange-LS Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
// Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; XBLWP7; ZuneWP7)
// Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J3 Safari/6533.18.5

	$module="modules";

	$theme="";
	if ($_REQUEST["settheme"]!="")
	{	
		if ( (file_exists($module."/".$_REQUEST["settheme"])) && (preg_match("/[a-z]*/",$_REQUEST["settheme"])) )
		{
			$theme=substr($_REQUEST["settheme"],0,20);
			$_SESSION['mytheme']=$theme;
		}
	}
	else if (isset($_SESSION['mytheme']))
	{
		$theme=substr($_SESSION['mytheme'],0,20);
	}
	else if ($_SESSION['mytheme']="")
  {
		if ((preg_match("/CPU iPhone OS/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/Linux; U; Android/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/iPad; U; CPU OS/",$_SERVER["HTTP_USER_AGENT"]))
		   )
		{
			$theme="mobile";
			$_SESSION['mytheme']=$theme;
		}	
	}


// ---- Charge le numéro de version
	require ("version.txt");

// ---- Charge les templates
	require ("class/xtpl.inc.php");

// ---- Se connecte à la base MySQL
	require ("class/mysql.inc.php");
	$sql_ro = new mysql_class($db_user_ro, $db_pwd_ro, $db_host_ro, $db, $db_port_ro);
	$sql_rw = new mysql_class($db_user_rw, $db_pwd_rw, $db_host_rw, $db, $db_port_rw);

// ---- Fonction des informations de l'utilisateur

	if (($MyOpt["maintenance"]=="on") && (!GetDroit("ADM")))
	  {
	  	echo "This site is in maintenance.<br/>";
	  	echo "Try to reconnect later.<br/>";
	  	exit;
	  }	  	

// ---- Template par default
	$tmpl=$_REQUEST["tmpl"];

	if ((!preg_match("/[a-z]+/i",$tmpl)) || ($tmpl==""))
	  { $tmpl="main"; }
//	$tmpl="$tmpl.htm";

	$tmpl_prg=LoadTemplate($tmpl);
	


// ---- Maj du template
//	$tmpl_prg->assign("username", $myuser->aff("prenom")." ".$myuser->aff("nom"));

	$tmpl_prg->assign("site_logo", $MyOpt["site_logo"]);
	$tmpl_prg->assign("site_title", $MyOpt["site_title"]);


// ---- Flag pour ne pouvoir poster qu'une seule fois les mêmes infos
	if (!isset($_SESSION["checkpost"]))
	  {
		$checkpost=1;
		$_SESSION["checkpost"]=1;
	  }
	else
	  {
		$checkpost=$checkpost+1;
	  	$_SESSION["checkpost"]=$_SESSION["checkpost"]+1;
	  }

	if (!isset($_SESSION["tab_checkpost"]))
	  { 
		$tab_checkpost[""]="ok";
		$_SESSION["tab_checkpost"][""]="ok";
	  }


// ---- Définition des variables
	$gl_myprint_txt="";

// ---- Initialisation des variables
	$rub=$_REQUEST["p"];

	$tmpl_prg->assign("rub", ucwords($rub));
	$tmpl_prg->assign("module", ucwords($mod));

	$tmpl_prg->assign("date_expire", date("r"));
	//Mon, 22 Jul 2002 11:12:01 GMT

	$tmpl_prg->assign("url_static", $MyOpt["static"]."/".$module."/static");
	
// ---- Affichages du menu
/*
	foreach($MyOpt["menu"] as $menu=>$droit) {
		if ( ( ($droit=="x") || (($droit=="") && ($myuser->data["type"]!="invite")) || ((GetDroit($droit)) && ($droit!="")) ) && ($droit!="-") )
		  { 
		  	$tmpl_prg->parse("main.menu_".$menu); 
		  	$tmpl_prg->parse("main.menu_".$menu."_sm"); 
		  }
	}
*/
	$query="SELECT * FROM ".$MyOpt["tbl"]."_views WHERE robject IS NULL AND hidden=0 AND deleted=0";
	$sql_ro->Query($query);

	$tabField=array();
	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);
		$tmpl_prg->assign("lst_object", $sql_ro->data["name"]);
		$tmpl_prg->assign("lst_obj_display", $sql_ro->data["displayname"]);
		$tmpl_prg->parse("main.lst_menu"); 
	}

// ---- Charge la rubrique
	$affrub=$rub;
	while ($affrub!="")
	  {
			$oldrub=$affrub;
	
			// Initialise les variables
			$infos="";
			$icone="";
			$corps="";
			
			// Charge la rubrique
			if (MyRep("$affrub.inc.php")!="")
			  {
			  	$rub=$affrub;
			  	require(MyRep("$affrub.inc.php"));
			  }
			else
			  { FatalError("File not found","File : $affrub.inc.php"); }
			
			if ($affrub==$oldrub)
			  { $affrub=""; }
	  }
	
// ---- Affecte les blocs
	$time_total_end = microtime_float();
	$time = $time_total_end - $time_total_start;
	$time = preg_replace("/([0-9]*)\.([0-9]{3})[0-9]*/","$1.$2",$time);

	$tmpl_prg->assign("icon", $icon);
	$tmpl_prg->assign("header", $header);
	$tmpl_prg->assign("main", $main);
	$tmpl_prg->assign("myprint", $gl_myprint_txt);
	$tmpl_prg->assign("version", $version.(($MyOpt["maintenance"]=="on") ? " - MAINTENANCE ACTIVE" : "")." (".$time."s)");

// ---- Affiche la page
	$tmpl_prg->parse("main");
	echo $tmpl_prg->text("main");

// ---- Ferme la connexion à la base de données	  
 	$sql_ro->closedb();
 	$sql_rw->closedb();

?>
