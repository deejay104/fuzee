<?
// ---- Test if the access to this page is allowed
	// To do

// ---- Get parameters
	$mynew=false;
	
	if ($myobject=="")
	{ $myobject=CheckVar("o","var",50); }

	if ($myid=="")
	{ $myid=CheckVar("id","int",20); }

	$myview=CheckVar("v","var",50);
	$myoid=CheckVar("oid","int",10);
	$myoo=CheckVar("oo","var",50);
	$myfonc=CheckVar("f","var",20);

	$checktime="";
	if (is_numeric($_REQUEST["chk"]))
	{
		$checktime=$_REQUEST["chk"];
	}

// ---- Load originate view data
	if ($myview!="")
	{
		$query = "SELECT views.id,views.name,views.robject,views.rfield,objects.tablename AS otabname FROM ".$MyOpt["tbl"]."_views AS views LEFT JOIN ".$MyOpt["tbl"]."_objects AS objects ON views.robject=objects.id WHERE views.name='".$myview."'";
		$res_v=$sql_ro->QueryRow($query);
	}

	
// ---- Create a new entry
	if (($myid==0) && ((!isset($_SESSION['tab_checkpost'][$checktime]))))
	{

		$myid=CreateObject($myobject);
		if ($myid>0)
		{
			$mynew=true;
		}
		$_SESSION['tab_checkpost'][$checktime]=$checktime;
	}
	else if ($myid==0)
	{
		$affrub="";
		if ($myview!="")
		{
			$affrub="view";
		}
		return;
	}
	
// ---- Save
	if ($myfonc==$tablang["lang_save"])
	{
		$tabForm=$_REQUEST["formArray"];

		if (($res_v["rfield"]!="") && ($myoid>0))
		{
			$tabForm[$res_v["rfield"]]=$myoid;
		}
		
		$conf["linkedobj"]=$res_v["otabname"];
		$conf["linkedoid"]=$myoid;
		$conf["new"]=$mynew;
		$r=SaveObject($myid,$myobject,$tabForm,$conf);
		
		if ($myoo!="")
		{
			$affrub="object";
			$myobject=$myoo;
			$myid=$myoid;
			$_REQUEST["f"]="";

			return;
		}		
		if ($myview!="")
		{
			$affrub="view";
			return;
		}
	}

// ---- Cancel
	if ($myfonc==$tablang["lang_cancel"])
	{
		if ($myview!="")
		{
			$affrub="view";
			return;
		}
	}

// ---- Display type
	$form="form";
	
// ---- Load template
	$tmpl_x=LoadTemplate("object");

	
// ---- Load object information
	$res_obj=loadObjectDef($myobject);
	$tabField=loadObjectFields($res_obj["id"]);

// ---- Load object data
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_".$res_obj["tablename"]." WHERE id='$myid'";
	$res_data = $sql_ro->QueryRow($query);

// ---- Set standard value
	$tmpl_x->assign("form_object", $myobject);
	$tmpl_x->assign("form_view", $myview);
	$tmpl_x->assign("form_id", $myid);
	$tmpl_x->assign("form_oid", $myoid);
	$tmpl_x->assign("form_oo", $myoo);
	$tmpl_x->assign("form_checktime",$_SESSION['checkpost']);

	$myuser=GetUser($res_data["uidupdate"]);

	$tmpl_x->assign("aff_lastupdate", $myuser["displayname"]." ".$tablang["lang_at"]." ".sql2date($res_data["dteupdate"]));

// ---- Show fields
	
	$tmpl_x->assign("aff_object", $myobject);
	foreach ($tabField as $f=>$d)
	{
		$tmpl_x->assign("aff_field", htmlentities($d["displayname"],ENT_HTML5));
		$tmpl_x->assign("aff_value", DisplayObject($d,$res_data[$f],$form,$mynew));

		$tmpl_x->parse("main.lst_line");
	}


// ---- Load related list
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_views AS views WHERE robject='".$res_obj["id"]."' AND hidden=0";
	$sql_ro->Query($query);

	$tmpl_x->assign("lst_rid", $myid);
	$tmpl_x->assign("lst_object", $myobject);

	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);

		$tmpl_x->assign("lst_displayname", htmlentities($sql_ro->data["displayname"],ENT_HTML5));
		$tmpl_x->assign("lst_related", $sql_ro->data["name"]);
		$tmpl_x->assign("lst_rfield", $sql_ro->data["rfield"]);
		
		$tmpl_x->parse("main.lst_related");
	}
	
// ---- Print the page
	$tmpl_x->assign("url_static", $MyOpt["static"]."/".$module."/static");

	$tmpl_x->parse("header");
	$header=$tmpl_x->text("header");
	$tmpl_x->parse("main");
	$main=$tmpl_x->text("main");

?>