<?php
// ---------------------------------------------------------------------------------------------
//   Page de Login
//   
// ---------------------------------------------------------------------------------------------
//   Variables  : 
// ---------------------------------------------------------------------------------------------

	session_start();
	if (isset($_SESSION['gl_uid']))
	  { $gl_uid = $_SESSION['gl_uid']; }

// ---- Récupère les variables transmises
	$username="";
	$password="";
	if (isset($_REQUEST['p']))
	  { $rub=$_REQUEST["p"]; }
	$username=$_REQUEST["username"];
	$password=$_REQUEST["password"];
	$myid=$_REQUEST["myid"];
	$fonc=$_REQUEST["f"];
	
	if ($_REQUEST["varlogin"]!="")
	  {
	  	//eval("if (is_array(\$HTTP_".$_SERVER["REQUEST_METHOD"]."_VARS)) { foreach( \$HTTP_".$_SERVER["REQUEST_METHOD"]."_VARS as \$key=>\$value) { \$var .= \"&\$key=\$value\"; } }");
	  	//$var.="&rub=$rub";
		$var=$_REQUEST["varlogin"];
	  }
	else
	  {
	  	$var=$_SERVER["REQUEST_URI"];
	  }

	$var=preg_replace("/\/login.php/","",$var);

// ---- Charge le numéro de version
	require ("version.txt");

// ---- Charge les prérequis
	require ("class/xtpl.inc.php");
	require ("class/mysql.inc.php");

// ---- Charge les variables
	require ("config/config.inc.php");
	require ("config/variables.inc.php");
	require ("modules/lib/functions.inc.php");

	$lang="fr";
	require ("modules/lang/".$lang.".inc.php");

// ---- Gestion des thèmes
	$theme="";
	if ( (isset($_REQUEST["settheme"])) && ($_REQUEST["settheme"]!="") )
	  {	
	  	$theme=$themes[$_REQUEST["settheme"]];
		$_SESSION['mytheme']=$theme;
	  }
	else if ((isset($_SESSION['mytheme'])) && ($_SESSION['mytheme']!=""))
	  {	$theme=$_SESSION['mytheme']; }
	else if ($_SESSION['mytheme']=="")
	  {
		if ((preg_match("/CPU iPhone OS/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/Linux; U; Android/",$_SERVER["HTTP_USER_AGENT"])) ||
			(preg_match("/iPad; U; CPU OS/",$_SERVER["HTTP_USER_AGENT"])) || 
			(preg_match("/Linux; Android/",$_SERVER["HTTP_USER_AGENT"])) 
			
		   )
		  {
			$theme="mobile";
			$_SESSION['mytheme']=$theme;
		  }
		
	  }


// ---- Test si l'on a validé la page
	$ok=0;
	$errmsg="";
	
	if ($fonc == "Login")
//	if ($username!="")
	{
		if ($password=="") { $password="nok"; }
		$username=strtolower($username);
		$username=preg_replace("/[\"'<>\\\;]/i","",$username);

		//preg_match("/^([^ ]*) (.*?)$/",$username,$t);

		$sql = new mysql_class($db_user_rw, $db_pwd_rw, $db_host_rw, $db, $db_port_rw);
		$query = "SELECT id,login,email,password FROM ".$MyOpt["tbl"]."_users WHERE ((email='$username' AND email<>'') OR (login='$username' AND login<>'')) AND deleted=0";
		$res   = $sql->QueryRow($query);

	
		if (($res["id"]>0) && (md5($res["password"].md5(session_id()))==$password))
		{
	
			$query="INSERT INTO ".$MyOpt["tbl"]."_login (username,dte_maj,header) VALUES ('".addslashes($res["prenom"])." ".addslashes($res["nom"])."','".now()."','".substr(addslashes($_SERVER["HTTP_USER_AGENT"]),0,200)."')";
//			$sql->Insert($query);
			$_SESSION['gl_uid']=$res["id"];

//			$query="UPDATE p67_utilisateurs SET dte_login='".now()."' WHERE id='".$res["id"]."'";
//			$sql->Update($query);

	
			echo "<HTML><HEAD><SCRIPT language=\"JavaScript\">function go() { document.location=\"$var\"; }</SCRIPT></HEAD><BODY onload=\"go();\"></BODY></HTML>";
			exit;

		}
		else
		{
			$errmsg="Votre mot de passe est incorrect.";
		}
	}
	else if ($fonc == "logout")
	{
		$_SESSION['gl_uid']="";
		echo "<HTML><HEAD><SCRIPT language=\"JavaScript\">function go() { document.location=\"index.php\"; }</SCRIPT></HEAD><BODY onload=\"go();\"></BODY></HTML>";
		exit;
	}

// ---- Charge les templates
	$module="modules";
	$tmpl_prg=LoadTemplate("login");

	
	if ($tmpl_prg->text("main.unsecure")=="")
	  { $tmpl_prg->parse("main.secure"); }


// ---- Calcul de l'id
	$myid=md5(session_id());

// ---- Affiche la page
	$tmpl_prg->assign("url_static", $MyOpt["static"]."/".$module."/static");

	$tmpl_prg->assign("myid", $myid);
	$tmpl_prg->assign("var", $var);
	$tmpl_prg->assign("errmsg", $errmsg);
	$tmpl_prg->assign("version", $version);
	$tmpl_prg->assign("site_title", $MyOpt["site_title"]);
    $tmpl_prg->assign("site_logo", $MyOpt["site_logo"]);

	$tmpl_prg->parse("main");
	echo $tmpl_prg->text("main");


?>
