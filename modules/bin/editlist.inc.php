<?
// ---- Test if the access to this page is allowed
	// To do

// ---- Load parameters
	$myview=CheckVar("v",'var',50);
	$myfonc=CheckVar("f","var",20);
	$mysearch=CheckVar("s","var",20);

// ---- Load template
	$tmpl_x=LoadTemplate("editlist");

// ---- Save
	if ($myfonc==$tablang["lang_save"])
	{
		$mydn=CheckVar("dn",'var',50);
		$query="UPDATE ".$MyOpt["tbl"]."_views SET displayname='".$mydn."' WHERE name='$myview'";
		$sql_rw->Update($query);

		$affrub="view";
		$_REQUEST["f"]="";

		return;
	}
	
// ---- Set standard value

	$tmpl_x->assign("header_view", $resv["displayname"]);
	$tmpl_x->assign("url_static", $MyOpt["static"]."/".$module."/static");
	$tmpl_x->assign("form_view", $myview);
	
	if ($mysearch!="")
	{
		$tmpl_x->assign("lang_textsearch", $mysearch);
	}
		
// ---- Load view information

	$query = "SELECT * FROM ".$MyOpt["tbl"]."_views WHERE name='$myview'";
	$resv = $sql_ro->QueryRow($query);

	$tmpl_x->assign("form_displayname", $resv["displayname"]);
	
// ---- Load object information

	$query = "SELECT * FROM ".$MyOpt["tbl"]."_objects WHERE id='".$resv["oid"]."'";
	$reso = $sql_ro->QueryRow($query);

	$query = "SELECT * FROM ".$MyOpt["tbl"]."_objects_fields AS fields WHERE oid='".$resv["oid"]."' ORDER BY displayname";
	$sql_ro->Query($query);

	$tabField=array();
	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);
		// if ($sql_ro->data["name"]!="id")
		// {
			$tabField[$sql_ro->data["name"]]['name']=$sql_ro->data["displayname"];
			$tabField[$sql_ro->data["name"]]['show']="yes";
		// }
	}


// ---- Display active field
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_views_fields AS fields WHERE vid='".$resv["id"]."'  ORDER BY pos";
	$sql_ro->Query($query);

	$fields="id";
	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);

		$tmpl_x->assign("aff_name", $sql_ro->data["name"]);
		$tmpl_x->assign("aff_displayname", htmlentities($tabField[$sql_ro->data["name"]]["name"],ENT_HTML5));
		$tabField[$sql_ro->data["name"]]["show"]="no";
		
		$tmpl_x->parse("main.lst_enabled");

	}

// ---- Display all other fields
	foreach($tabField as $n=>$d)
	{
		if ($d["show"]=="yes")
		{
			$tmpl_x->assign("aff_name", $n);
			$tmpl_x->assign("aff_displayname", htmlentities($d["name"],ENT_HTML5));
			$tabField[$sql_ro->data["name"]]["show"]="no";
			$tmpl_x->parse("main.lst_disabled");
		}
	}

// ---- Print the page

	$tmpl_x->parse("header");
	$header=$tmpl_x->text("header");
	$tmpl_x->parse("main");
	$main=$tmpl_x->text("main");
	
?>
	