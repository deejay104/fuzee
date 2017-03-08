<?
// ---------------------------------------------------------------------------------------------
//   Fonctions
// ---------------------------------------------------------------------------------------------

function GetModule($mod)
  { global $MyOpt;
	if ($MyOpt["module"][$mod]=="on")
	  { return true; }
	else
	  { return false; }
  }

function MyRep($file,$dir='')
  { global $module,$theme;
  	$myfile=substr($file,0,strrpos($file,"."));
	$myext=substr($file,strrpos($file,".")+1,strlen($file)-strrpos($file,".")-1);

	if (file_exists("$module/$dir/$myfile.$theme.$myext"))
  	  { return "$module/$dir/$myfile.$theme.$myext"; }
  	else if (file_exists("$module/$dir/$file"))
  	  { return "$module/$dir/$file"; }
  	else if (file_exists("$module/bin/$file"))
  	  { return "$module/bin/$file"; }
  	else if (file_exists("$module/$file"))
  	  { return "$module/$file"; }
  	else if (file_exists("config/$file"))
  	  { return "config/$file"; }
  	else
  	  { return ""; }
  }

// Check parameters
function CheckVar($v,$type,$l)
{
	if (!isset($_REQUEST[$v]))
	{ 
		if ($type=="int")
		{ return 0; }
		return "";
	}
	$vv=substr($_REQUEST[$v],0,$l);
	$vv=preg_replace("/[;]*/","",$vv);

	if ($type=="int")
	{
		if ((!is_numeric($vv)) && ($vv!=""))
		{
			FatalError("Incorrect parameter","The variable ".$v." is not numeric.");
		}
		else if ($vv=="")
		{
			$vv=0;
		}
	}
	return $vv;
}

  
 // Load Template and apply translation
function LoadTemplate($tmpl)
{ global $tablang;
	$tmpl = new XTemplate (MyRep($tmpl.".htm"	,'tmpl'));

	foreach ($tablang as $key=>$val)
	  {
		$tmpl->assign($key, $val);
	  }

	return $tmpl;
}
  
// Show full displayname
function AffDisplayname($firstname,$lastname)
{ global $MyOpt;
	$displayname="";
	$lastname=strtoupper($lastname);

	$firstname=preg_replace("/-/"," ",$firstname);
	$firstname=ucwords($firstname);
	$firstname=preg_replace("/ /","-",$firstname);

	if ($MyOpt["firstDisplay"]=="name")
	  {
		$displayname=$lastname;
		$displayname.=(($firstname!="") && ($lastname!=""))?" ":"";
		$displayname.=$firstname;
		$displayname.=(($firstname=="")&&($lastname==""))?"N/A":"";
	  }		
	else
	  {
		$displayname=$firstname;
		$displayname.=(($firstname!="") && ($lastname!=""))?" ":"";
		$displayname.=$lastname;
		$displayname.=(($firstname=="")&&($lastname==""))?"N/A":"";
	  }		
	return $displayname;
}

// Calculate user rights
function GetRights($group)
  {
		if (trim($group)=="")
		  { return true; }
		else if ($myuser->role[$group])
		  { return true; }
		else if ($myuser->groups["ADM"])
		  { return true; }
		elseif ($myuser->groups[$group])
		  { return true; }
		else
		  { return false; }
  }

function myPrint($txt)
{ global $gl_mode,$gl_myprint_txt;
	if ($gl_mode=="batch")
	{
		echo $txt."\n";
	}
	else
	{
		$gl_myprint_txt.=$txt."<br />";
	}
}

// Clean up and compress HTML
function Purge($txt)
{
	$p[]="/  /";	  $r[]=" ";
	$p[]="/   /";	  $r[]=" ";
	$p[]="/    /";	$r[]=" ";
	$p[]="/     /";	$r[]=" ";
	$p[]="/\t/";	  $r[]=" ";
	$p[]="/\r/";	  $r[]="";
	$p[]="/\n/";	  $r[]="";

	$txt=preg_replace($p,$r,$txt);
	return $txt;
}

// Calc time with microseconds
function microtime_float()
{
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}

// Get user data
function GetUser($uid)
{ global $MyOpt,$sql_ro;
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_users WHERE id='$uid'";
	$res = $sql_ro->QueryRow($query);

	if (!is_array($res))
	{
		return array();
	}
	
	$tabUser=array();
	foreach ($res as $i=>$d)
	{
		if (!is_numeric($i))
		{
			$tabUser[$i]=$d;
		}
	}
	$tabUser["displayname"]=AffDisplayname($res["firstname"],$res["lastname"]);
	return $tabUser;
}


// Show a phone number
function AffPhone($txt)
{
  	$rtxt=$txt;
	$rtxt=preg_replace("/^0([1-9])([0-9]*)$/","+33\\1\\2",$txt);
	return $rtxt;
}


// Affiche un temps en minute en heures/minutes
function AffTime($tps,$short="yes") {
	$th=floor($tps/60);
	$tm=$tps-$th*60;
	$tm=substr("00",0,2-strlen($tm)).$tm;

	if (($th>0) || ($short=="no"))
	  { return $th."h ".$tm; }
	else
	  { return $tm."min"; }
}

// Transforme une date en format SQL
function date2sql($date) {
	if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/",$date))
	  { return $date; }

  $d = preg_replace('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2,4})$/','\\3-\\2-\\1', $date);
  if ($d == $date) { $d = preg_replace('/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{2,4})$/','\\3-\\2-\\1', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})$/','\\3-\\2-\\1', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{2,4})\/([0-9]{1,2})\/([0-9]{1,2})$/','\\1-\\2-\\3', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{2,4}).([0-9]{1,2}).([0-9]{1,2})$/','\\1-\\2-\\3', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ?([0-9:]*)?$/','\\1-\\2-\\3', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2,4}) ?([0-9:]*)?$/','\\3-\\2-\\1', $date); }
  if (($d == $date) && ($date != '')) { $d = "nok"; }
  return $d;
}

// Transforme une date SQL en date jj/mm/aaaa
function sql2date($date,$aff="")
{
	if ($aff=="day")
	{
		return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) [^$]*$/','\\3/\\2/\\1', $date);
	}
	else if ($aff=="nosec")
	{
		return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]*):([0-9]*):([0-9 ]*)$/','\\3/\\2/\\1 \\4:\\5', $date);
	}
	else if ($aff=="hour")
	{
		$h=preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):?([0-9]{1,2})?:?([0-9]{1,2})?$/','\\4', $date);
		$m=preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):?([0-9]{1,2})?:?([0-9]{1,2})?$/','\\5', $date);
	  	return $h.(($m!="") ? ":$m" : ":00");
	}
	else
	{
		return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})([0-9: ]*)$/','\\3/\\2/\\1\\4', $date);
	}
}

// Transforme une date SQL en heure hh:mm
function sql2time($date,$aff="") {
	if ($aff=="nosec")
	  { return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]*):([0-9]*):([0-9]*)$/','\\4:\\5', $date); }
	else
	  { return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]*):([0-9]*):([0-9]*)$/','\\4:\\5:\\6', $date); }
}


// Calcul le nombre de secondes entre deux dates
function date_diff_txt($date1, $date2) {
  $s = strtotime($date2)-strtotime($date1);
  return $s;
}



// Ajoute un nombre de jour à une date
function CalcDate($dte, $n)
  {
		return date("Y-m-d",mktime(0, 0, 0, date("n",strtotime($dte)), date("j",strtotime($dte))+$n, date("Y",strtotime($dte))));
  }	


function AffInitiales($res)
  {
  	if ($res["initiales"]!="")
  	  { return strtoupper($res["initiales"]); }
  	else
  	  { return strtoupper(substr($res["prenom"],0,1).substr($res["nom"],0,1)); }
  }


// Send an email
function MyMail($from,$to,$tabcc,$subject,$message,$headers="",$files="")
{ global $MyOpt;

	if (is_array($from))
  {
	 	$me=$from["name"];
	  $fromadd=$from["mail"];
	}
	else
	{
		if ($from=="") { $from = ini_get("sendmail_from"); }

		preg_match("/^([^@]*)@([^$]*)$/",$from,$t);
		$me=$t[0];
		$fromadd=$from;
	}

	if ($MyOpt["sendmail"]==1) { MyPrint("From:$from - To:$to - Cc:$cc - Subject:$subject"); return -1; }

	require_once 'external/PHPMailer/PHPMailerAutoload.php';
	
	//Create a new PHPMailer instance
	$mail = new PHPMailer;


	if ($MyOpt["mail"]["smtp"]==1)
	{
		// Set PHPMailer to use SMTP transport
		$mail->isSMTP();
		//Set the hostname of the mail server
		$mail->Host = $MyOpt["mail"]["host"];
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = $MyOpt["mail"]["port"];
		// Do not close connection to SMTP
		$mail->SMTPKeepAlive = true;
		//Whether to use SMTP authentication
		if ($MyOpt["mail"]["username"]!="")
		{
			$mail->SMTPAuth = true;
			//Username to use for SMTP authentication
			$mail->Username = $MyOpt["mail"]["username"];
			//Password to use for SMTP authentication
			$mail->Password = $MyOpt["mail"]["password"];
		}
	}
	else
	{
		$mail->isSendmail();
	}

	//Set who the message is to be sent from
	$mail->setFrom($fromadd, $me);
	//Set an alternative reply-to address
	$mail->addReplyTo($fromadd, "");
	//Set who the message is to be sent to

	$mail->addAddress($to);
//$mail->addAddress("matthieu@les-mnms.net");

	if ((is_array($tabcc)) && (count($tabcc)>0))
	{
		foreach($tabcc as $i=>$m)
		{
			$mail->addCC($m);
		}
	}
	
	//Set the subject line
	$mail->Subject = $subject;

	$mail->msgHTML($message);
	$mail->AltBody = strip_tags($message);

	if (is_array($files))
	{
		foreach($files as $i=>$d)
		{
			if ($d["type"]=="text")
			{
				$mail->AddStringAttachment($d["data"],$d["nom"]);
			}
			else if ($d["type"]=="file")
			{
				$mail->AddAttachment($d["data"],$d["nom"]);
			}
		}
	}
	
	//send the message, check for errors
	return $mail->send();
}


function SendMail($From,$To,$Cc,$Subject,$Text,$Html,$AttmFiles)
{ global $MyOpt;
	/*
	function SendMail($From, $FromName, $To, $ToName, $Subject, $Text, $Html, $AttmFiles)
	$From      ... sender mail address like "my@address.com"
	$FromName  ... sender name like "My Name"
	$To        ... recipient mail address like "your@address.com"
	$ToName    ... recipients name like "Your Name"
	$Subject   ... subject of the mail like "This is my first testmail"
	$Text      ... text version of the mail
	$Html      ... html version of the mail
	$AttmFiles ... array containing the filenames to attach like array("file1","file2")
	*/

	// attachments
	$attach=array();
	if($AttmFiles)
	{
		$i=0;
		foreach($AttmFiles as $AttmFile)
		{
			$patharray = explode ("/", $AttmFile); 
			$FileName=$patharray[count($patharray)-1];
			$attach[$i]["nom"]=$FileName;
			$attach[$i]["type"]="file";
			$attach[$i]["data"]=$AttmFile;

			$i=$i+1;
		}
  }
	
	//message ends
	$Msg.="\n--".$OB."--\n";

	if ($MyOpt["sendmail"]==1) { echo "From:$From - To:$To - Cc:$Cc - Subject:$Subject<BR>"; return -1; }

	return MyMail($From,$To,$Cc,$Subject,$Msg,$headers);
}



// Calcul un dégradé de couleur
function CalcColor($color,$pour,$fcolor="FFFFFF")
  {
	$color2=str_replace('#','',$color);

	$rr=hexdec(substr($color2, 0, 2))*((100-$pour)/100)+hexdec(substr($fcolor, 0, 2))*($pour/100);
	if ($rr>254) { $rr=255; }
	$rr=strtoupper(dechex($rr));
	$rr=substr("00",0,2-strlen($rr)).$rr;

	$vv=hexdec(substr($color2, 2, 2))*((100-$pour)/100)+hexdec(substr($fcolor, 2, 2))*($pour/100);
	if ($vv>254) { $vv=255; }
	$vv=strtoupper(dechex($vv));
	$vv=substr("00",0,2-strlen($vv)).$vv;

	$bb=hexdec(substr($color2, 4, 2))*((100-$pour)/100)+hexdec(substr($fcolor, 4, 2))*($pour/100);
	if ($bb>254) { $bb=255; }
	$bb=strtoupper(dechex($bb));
	$bb=substr("00",0,2-strlen($bb)).$bb;

	$color2=$rr.$vv.$bb;
	return $color2;
  }


/* **** Complète une chaine de caractères ****

	$txt	Chaine à compléter
	$nb	Nb de caractères que doit comporter la chaine
	$car	Caractère de remplissage  
*/
function CompleteTxt($txt,$nb,$car)
  {
	$n=$nb-strlen($txt);
	if ($n<0) { $n=0; }
	$ret="";
	for ($i=0;$i<$nb;$i++) { $ret.=$car; }
	return substr($ret,0,$n).$txt;
  }

function InvCompleteTxt($txt,$car)
  {
	$n=-1;
	for ($i=0;$i<strlen($txt);$i++) { if ((substr($txt,$i,1)!=$car) && ($n==-1)) { $n=$i; } }
	return substr($txt,$n,strlen($txt)-$n);
  }


/* **** Return a chain with the first letter in uppercase ****
	$txt	Chain
*/

function UpperFirstLetter($txt)
  {
  	$t=strtoupper(substr($txt,0,1)).substr($txt,1,strlen($txt)-1);
  	return $t;
  }

function FatalError($txt,$msg="")
  { global $tmpl_prg;
  	if (isset($tmpl_prg))
  	{
		$tmpl_prg->assign("header","$txt");
		$tmpl_prg->assign("main","$msg");
		$tmpl_prg->parse("main");
		echo $tmpl_prg->text("main");
	}
	else
	{
		echo $txt."\"n";
		echo $msg."\"n";
	}
	exit;			 
  }

// Affiche une valeur au format xxx,yy
function AffMontant($val)
  {
  	global $MyOpt;
  	preg_match("/([\-0-9]*)\.?([0-9]*)/i",$val,$m);
	$ret=$m[1].",".$m[2].substr("00",0,2-strlen($m[2]));
	
	$ret=$ret." ".$MyOpt["devise"];
	
	return $ret;
  }

// Duplique une chaine de caractères
function Duplique($txt,$nb)
  {
	$ret="";
	for($i=0;$i<$nb;$i++)
	  { $ret.=$txt; }
	return $ret;
  }

// Affiche la taille d'un fichier en human reading
function CalcSize($s)
{
	if ($s<1024)
	{
		return $s." octets";
	}
	else if ($s<1024*1024)
	{
		return floor($s/1024)." ko";
	}
	else if ($s<1024*1024*1024)
	{
		return floor($s/1024/1024)." Mo";
	}
	else if ($s<1024*1024*1024*1024)
	{
		return floor($s/1024/1024/1024)." Go";
	}
}

// Affiche les 4 premières lignes d'un texte

/*
Truc mavchin<BR>chose et companie<BR>1<BR>
2<BR>3<BR>4<BR>
5&gt;<BR>6<BR>
7<BR>8<BR>
9<BR>

**

Truc mavchin<BR>chose et companie<BR>1<BR>
2<BR>3<BR>4<BR>
5&gt;<BR>6<BR>
7<BR>8<BR>
9<BR>


*/

function GetFirstLine($txt,$nb=4)
  {
  	$p=0;
  	$i=0;

	$txt=preg_replace("/<br ?\/?>/i","<br/>",$txt);
	$txt=preg_replace("/<br\/><br\/>/","<br/>",$txt);
	$txt=preg_replace("/<br\/><br\/>/","<br/>",$txt);
	$txt=preg_replace("/\r|\n/i","",$txt);

	while($i<$nb)
	  {
		$p0=strpos($txt,"<br/>",$p);
		if ($p0>0)
		  {
			$p=$p0+1;
		  }
		else
		  {
			$p=strlen($txt);
		  	$i=$nb+1;
		  }
		$i=$i+1;
	  }
	if ($p==strlen($txt))
	  { return $txt; }
	else
	  { return substr($txt,0,$p-1)."<br/>..."; }
  }

// Convertie une couleur en RGB
function ConvertColor2RGB($col,$add=0)
  {
  	$r=hexdec(substr($col,0,2));
  	$r=($r+$add>255) ? 255 : $r+$add;
  	$g=hexdec(substr($col,2,2));
  	$g=($g+$add>255) ? 255 : $g+$add;
  	$b=hexdec(substr($col,4,2));
  	$b=($b+$add>255) ? 255 : $b+$add;
  	return "rgb($r, $g, $b)";
  }


// Affiche une date
function DisplayDate($dte)
  {
	$d=time()-strtotime($dte);
	$mid=time()-strtotime(date("Y-m-d 23:59:59",time()-3600*24));

	$h=floor($d/3600);
	$m=floor(($d-$h*3600)/60);
	$s=$d-$h*3600-$m*60;

	if (($s<60) && ($m==0) && ($h==0))
	  {
			return "il y a ".$s." secondes";
	  }
	else if (($m<2) && ($h==0))
	  {
			return "il y a 1 minute";
	  }
	else if (($m<60) && ($h==0))
	  {
			return "il y a ".$m." minutes";
	  }
	else if (($h<2) && ($m==0))
	  {
			return "il y a  1 heure";
	  }
	else if (($h<2) && ($m<2))
	  {
			return "il y a  1 heure"." et 1 minute";
	  }
	else if ($h<2)
	  {
			return "il y a  1 heure"." et ".$m." minutes";
	  }
	else if (($d<$mid) && ($h<2))
	  {
			return "il y a  1 heure et ".$m." minutes";
	  }
	else if ($d<$mid)
	  {
			return "il y a ".$h." heures et ".$m." minutes";
	  }
	else if (($d<$mid+3600*34) && ($d>$mid))
	  {
			return "hier à ".sql2time($dte,"no");
	  }
	else
	  {
			return "le ".sql2date($dte,"jour")." à ".sql2time($dte,"no");
	  }	


  }

// Affiche un temps en minute en heure:minute
function AffHeures($min){
	$t=$min;
	$h=floor($t/60);
	$m=$t-$h*60;
	$m=substr("00",0,2-strlen($m)).$m;

	$ret=$h."h $m";
	return $ret;
}

// Generate variables file
function GenerateVariables($tab)
{
	$ret="";
	$conffile="config/variables.inc.php";
	if (!file_exists($conffile))
	{
		$ret.="Create file. ";
		$fd=fopen($conffile,"w");
		fwrite($fd,"\n");
		fclose($fd);
	}

	if(is_writable($conffile))
	{
		$fd=fopen($conffile,"w");
		fwrite($fd,"<?\n");
		foreach($tab as $nom=>$d)
		{
			if (is_array($d))
			{
				foreach($d as $var=>$dd)
				{
					if ($var=="valeur")
					{
						fwrite($fd,"\$MyOpt[\"".$nom."\"]=\"".$dd."\";\n");
					}
					else
					{
						fwrite($fd,"\$MyOpt[\"".$nom."\"][\"".$var."\"]=\"".$dd."\";\n");
					}
				}
			}
		}
		
		fwrite($fd,"?>\n");
		fclose($fd);
		$ret.="Saved";
	}
	else
	{
		$ret.="Acces denied. File : ".$conffile;
	}
	return $ret;
}


function now()
{
	return date("Y-m-d H:i:s");
}

?>
