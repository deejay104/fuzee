<?
// ---------------------------------------------------------------------------------------------
//   Variables
// ---------------------------------------------------------------------------------------------

$MyOptTmpl=array();
$MyOptHelp=array();

$MyOptHelp[""]="";

// Tables prefix
$MyOptTmpl["tbl"]="fz";
$MyOptHelp["tbl"]="Tables prefix";

// Site is in maintenance
$MyOptTmpl["maintenance"]="off";
$MyOptHelp["maintenance"]="Activate maintenance mode (on=site on maintenance, off=site accessible)";

// path
$MyOptTmpl["mydir"]=htmlentities(preg_replace("/updatedb\.php/","",$_SERVER["SCRIPT_FILENAME"]));
$MyOptHelp["mydir"]="Site path. Used by scripts";

// Timezone
$MyOptTmpl["timezone"]=date_default_timezone_get();
$MyOptHelp["timezone"]="Local timezone (Europe/Paris)";

// URL
$MyOptTmpl["host"]=htmlentities($_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].preg_replace("/\/index\.php/","",$_SERVER["SCRIPT_NAME"]));
$MyOptHelp["host"]="Full URL. Used to generate static URL.";

// URL for static files
$MyOptTmpl["static"]="";
$MyOptHelp["static"]="URL used for static files. (Could be the same as the full URL)";

// Site title
$MyOptTmpl["site_title"]="Fuzee";
$MyOptHelp["site_title"]="Web site title";

// Logo du site dans le dossier images
$MyOptTmpl["site_logo"]="logo.png";
$MyOptHelp["site_logo"]="Logo filename (as to put uploaded to images folder)";

// Active l'envoi de mail (0=ok, 1=nok)
$MyOptTmpl["sendmail"]="off";
$MyOptHelp["sendmail"]="Activate email (on=send email, off=disable email)";

$MyOptTmpl["mail"]["smtp"]="1";
$MyOptHelp["mail"]["smtp"]="Send email with SMTP (0=Sendmail, 1=SMTP)";

$MyOptTmpl["mail"]["host"]="localhost";
$MyOptHelp["mail"]["host"]="SMTP host";

$MyOptTmpl["mail"]["port"]="25";
$MyOptHelp["mail"]["port"]="SMTP port";

$MyOptTmpl["mail"]["username"]="";
$MyOptHelp["mail"]["username"]="SMTP username";

$MyOptTmpl["mail"]["password"]="";
$MyOptHelp["mail"]["password"]="SMTP user password";



?>
