<?
// ---- Test if the access to this page is allowed
	// To do

// ---- Load parameters
	$myview=$_REQUEST["v"];

	$mysearch=$_REQUEST["searchArray"];

	$myrfield=$_REQUEST["rf"];
	$myrid=$_REQUEST["rid"];
	$myoo=$_REQUEST["oo"];
	
	$myst=CheckVar("st","int",10);


// ---- Define colors
	$myColor[50]="E7E7E7";
	$myColor[55]="EFD1D1";
	$myColor[60]="F7F7F7";
	$myColor[65]="EFD1D1";
	

// ---- Load view information
	
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_views WHERE name='$myview'";
	$resv = $sql_ro->QueryRow($query);

	if (isset($_REQUEST["sort"]))
	{
		$mysort=CheckVar("sort","var",50);
	}
	else
	{
		$mysort=$resv["defaultsort"];
	}
	if (isset($_REQUEST["order"]))
	{
		$myorder=CheckVar("order","var",4);
	}
	else
	{
		$myorder=$resv["defaultway"];
	}

	if (($myorder!='') && ($myorder!='desc'))
	{
		$myorder="";
	}

// ---- Load template
	$tmpl_x=LoadTemplate("view.".$resv["type"]);

// ---- Load object info
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_objects WHERE id='".$resv["oid"]."'";
	$reso = $sql_ro->QueryRow($query);

	$query = "SELECT * FROM ".$MyOpt["tbl"]."_objects_fields AS fields WHERE oid='".$resv["oid"]."'";
	$sql_ro->Query($query);

	$tabField=array();
	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);
		// if ($sql_ro->data["name"]!="id")
		// {
			$tabField[$sql_ro->data["name"]]=$sql_ro->data;
		// }
	}

// ---- Set standard value

	$tmpl_x->assign("header_view", $resv["displayname"]);

	$tmpl_x->assign("url_static", $MyOpt["static"]."/".$module."/static");
	$tmpl_x->assign("aff_view", $myview);
	$tmpl_x->assign("form_checktime",$_SESSION['checkpost']);
	$tmpl_x->assign("tab_sort", $mysort);
	$tmpl_x->assign("tab_order", $myorder);
	$tmpl_x->assign("tab_rf", $myrfield);
	$tmpl_x->assign("tab_rid", $myrid);
	$tmpl_x->assign("tab_tmpl", $gl_tmpl);

// ---- Load fields
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_views_fields AS fields WHERE vid='".$resv["id"]."'  ORDER BY pos";
	$sql_ro->Query($query);

	$fields="";
	$tabTitle=array();
	
	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);
		
		$fields.=",fields.".$sql_ro->data["name"];
		$tabTitle[$sql_ro->data["name"]]=$i;
		
		$tmpl_x->assign("tab_name", $sql_ro->data["name"]);
		$tmpl_x->assign("tab_search", $mysearch[$sql_ro->data["name"]]);

		if ($sql_ro->data["name"]==$mysort)
		{
			$tmpl_x->assign("tab_selected", "class='tableSelected'");
			$tmpl_x->parse("main.lst_col_head.lst_col_sort");
		}
		else
		{
			$tmpl_x->assign("tab_selected", "");
		}
		$tmpl_x->assign("tab_header", htmlentities(UpperFirstLetter($tabField[$sql_ro->data["name"]]["displayname"],ENT_HTML5)));
		$tmpl_x->parse("main.lst_col_head");
		$tmpl_x->parse("main.lst_col_search");

	}

	if (!isset($tabTitle["id"]))
	{
		$fields="fields.id".$fields;
	}
	else
	{
		$fields="''".$fields;
	}
	
	$tmpl_x->assign("tab_object", $reso["name"]);
	$tmpl_x->assign("aff_object", $myoo);
	$tmpl_x->assign("aff_oid", $myrid);

// ---- Load lines
	$limit=20;

	$query = "SELECT ".$fields." FROM ".$MyOpt["tbl"]."_".$reso["tablename"]." AS fields ";
	$qw="WHERE fields.deleted=0 ";
	$qj="";
	if (is_array($mysearch))
	{
		foreach($mysearch as $f=>$d)
		{
			if ($d!="")
			{

				if ($tabField[$f]["type"]=="link")
				{
					$qj.="LEFT JOIN ".$MyOpt["tbl"]."_".$tabField[$f]["link"]." AS j_".$f." ON fields.".$f."=j_".$f.".id ";
					$qw.="AND j_".$f.".".$tabField[$f]["linkfield"]." LIKE '%".$d."%' ";
				}
				else
				{
					$qw.="AND ".$f." LIKE '%".$d."%' ";
				}
			}
		}
	}

	if ($myrid>0)
	{
		$qw.="AND ".$myrfield."='".$myrid."' ";
	}
	
	$query.=$qj;
	$query.=$qw;
	$query.= (($mysort!="") ? "ORDER BY $mysort $myorder" : "");
	$query.=" LIMIT $myst,$limit";
	$sql_ro->Query($query);
	$col=50;
	
	$tabList=array();
	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);
		$tabList[$i]=$sql_ro->data;
	}

	foreach($tabList as $i=>$field)
	{
		$tmpl_x->assign("tab_id", $field["id"]);
		$tmpl_x->assign("tab_color",$myColor[$col]);
		$tmpl_x->assign("tab_color2",$myColor[$col+5]);
		$col = abs($col-110);

		foreach($tabTitle as $f=>$dd)
		// foreach($field as $f=>$d)
		{
			// if (($f!="id") && (!is_numeric($f)))
			// if (!is_numeric($f))
			// {
//				$tmpl_x->assign("tab_line", htmlentities(DisplayObject($tabField["$f"],$d,"html"),ENT_HTML5));
				$tmpl_x->assign("tab_line", DisplayObject($tabField[$f],$field[$f],"html"));
				$tmpl_x->parse("main.lst_line.lst_col_line");
			// }
		}

		$tmpl_x->parse("main.lst_line");
	}

	$query = "SELECT COUNT(*) AS nb FROM ".$MyOpt["tbl"]."_".$reso["tablename"]." AS fields ";
	$query.=$qj;
	$query.=$qw;
	$res=$sql_ro->QueryRow($query);

	$nbtot=$res["nb"];
	if ($nbtot>$limit)
	{
		$lstpage="";
		$ii=1;
		$t=0;
		$nbp=10;

		for($i=0; $i<$nbtot; $i=$i+$limit)
		  {
			if (($i<=$myst) && ($i>$myst-$limit))
			  {
				$lstpage.="<a href=\"#\" OnClick=\"SubmitPage('".$i."');\">[$ii]</a> ";
				$t=0;
			  }
			else if ( (($i>$myst-$nbp*$limit/2) && ($i<$myst+$nbp*$limit/2)) || ($i>$nbtot-$limit) || ($i==0))
			  {
				$lstpage.="<a href=\"#\" OnClick=\"SubmitPage('".$i."');\">$ii</a> ";
				$t=0;
			  }
			else if ($t==0)
			  {
				$lstpage.=" ... ";
				$t=1;
			  }
			$ii=$ii+1;
		  }

		$tmpl_x->assign("aff_pages",$lstpage);
	}
	  
// ---- Print the page

	$tmpl_x->parse("header");
	$header=$tmpl_x->text("header");
	$tmpl_x->parse("main");
	$main=$tmpl_x->text("main");
?>