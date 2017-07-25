<?

// Load object definition
function loadObjectDef($myobject)
{ global $MyOpt,$sql_ro;
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_objects WHERE name='$myobject'";
	$res_obj = $sql_ro->QueryRow($query);
	return $res_obj;
}


// Load object field
function loadObjectFields($id,$all=false)
{ global $MyOpt,$sql_ro;
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_objects_fields AS fields WHERE oid='".$id."' ".(($all) ? "" : "AND hidden=0")." ORDER BY posx, posy";
	$sql_ro->Query($query);

	$tabField=array();
	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);
		if (($sql_ro->data["name"]!="id") && (!is_numeric($sql_ro->data["name"])))
		{
			foreach($sql_ro->data as $f=>$d)
			{
				if (!is_numeric($f))
				{
					$tabField[$sql_ro->data["name"]][$f]=$d;
				}
			}
		}
	}
	return $tabField;
}

// Create a new object
function CreateObject($name)
{ global $MyOpt,$sql_rw,$gl_uid;

	$res_obj=loadObjectDef($name);

	$query="INSERT INTO ".$MyOpt["tbl"]."_".$res_obj["tablename"]." SET deleted=1, uidcreate='".$gl_uid."', dtecreate='".now()."', uidupdate='".$gl_uid."', dteupdate='".now()."'";
	$id=$sql_rw->Insert($query);
	
	return $id;
}

// Display an object field
function DisplayObject($obj,$var,$form="html",$new=false)	
{ global $MyOpt,$tablang,$sql_ro;
	$txt=$var;
	$obj["type"]=strtolower($obj["type"]);
	$obj["transform"]=strtolower($obj["transform"]);

//	if (($obj["readonly"]==1) && ((!$new) || ($obj["system"]==1)))
// if ($obj["readonly"]==0) && 
	if ( (($obj["readonly"]==1) && (!$new)) || ($obj["locked"]==1) )
	{
		$form="html";
	}
	
	$type="";
	if ($obj["type"]=="date")
	{
		$txt=sql2date($var,"day");
		$type="date";
	}
	else if ($obj["type"]=="datetime")
	{
		$txt=sql2date($var);
	}
	else if ($obj["type"]=="email")
	{
		$type="email";
	}
	else if ($obj["type"]=="phone")
	{
		$type="tel";
		$txt=AffPhone($var);
	}
	else if ($obj["type"]=="link")
	{
		$query="SELECT obj.tablename, fields.* FROM ".$MyOpt["tbl"]."_objects AS obj LEFT JOIN ".$MyOpt["tbl"]."_objects_fields AS fields ON obj.id=fields.oid WHERE obj.name='".$obj["link"]."' AND fields.name='".$obj["linkfield"]."'";
		$reso= $sql_ro->QueryRow($query);

		$query="SELECT ".$obj["linkfield"]." AS field FROM ".$MyOpt["tbl"]."_".$reso["tablename"]." WHERE id='".$var."'";
		$resd= $sql_ro->QueryRow($query);
		$txt=$resd["field"];
	}
	
	if ($form=="form")
	{
		$txt=utf8_encode($txt);
		if ($obj["type"]=="text")
		{
			$txt="<textarea id=\"form_".$obj["name"]."\" name=\"formArray[".$obj["name"]."]\">".$txt."</textarea>";
		}
		else if ($obj["type"]=="html")
		{
			$t=$txt;
			$txt="<textarea id=\"form_".$obj["name"]."\" name=\"formArray[".$obj["name"]."]\">".$t."</textarea>";
			$txt.="<script>";
			// $txt.="$(function() {\n";
			$txt.="$('#form_".$obj["name"]."').trumbowyg({";
			$txt.="btnsAdd: ['table']";
			$txt.="});";
			$txt.="</script>";

		}
		else if ($obj["type"]=="type")
		{
			$txt ="<select id='".$obj["name"]."' name='formArray[".$obj["name"]."]'>";
			$txt.="<option value='smallstring'>Small String (VAR20)</option>";
			$txt.="<option value='mediumstring'>Medium String (VAR50)</option>";
			$txt.="<option value='largestring'>Large String (VAR100)</option>";
			$txt.="<option value='string'>String (VAR250)</option>";
			$txt.="<option value='text'>Text</option>";
			$txt.="<option value='html'>HTML</option>";
			$txt.="<option value='numeric'>Numeric</option>";
			$txt.="<option value='date'>Date</option>";
			$txt.="<option value='datetime'>Datetime</option>";
			$txt.="<option value='link'>Link</option>";
			$txt.="<option value='yesno'>Yes/No</option>";
			$txt.="<option value='password'>Password</option>";
			$txt.="</select>";
		}
		else if ($obj["type"]=="yesno")
		{
			$v=$txt;
			$txt ="<select id='".$obj["name"]."' name='formArray[".$obj["name"]."]'>";
			$txt.="<option value='Y' ".(($v=="Y") ? "selected" : "").">".$tablang["yes"]."</option>";
			$txt.="<option value='N' ".(($v=="N") ? "selected" : "").">".$tablang["no"]."</option>";
			$txt.="</select>";
		}
		else if ( ($obj["type"]=="link") || ($obj["type"]=="syslink") )
		{
			$query ="SELECT COUNT(*) AS nb FROM ".$MyOpt["tbl"]."_".$obj["link"]." AS fields ";
			$query.="WHERE fields.deleted=0  ";
			$res=$sql_ro->QueryRow($query);

			if ($res["nb"]>=20)
			{
				$txt ="<input name=\"formArray[".$obj["name"]."]\" id=\"form_".$obj["name"]."\" value=\"".$txt."\" type=\"".$type."\">\n";

				$txt.="<script type='text/javascript'>\n";
				$txt.="$(function() {\n";
				$txt.="$(\"#form_".$obj["name"]."\").autocomplete({\n";
				$txt.="source: \"index.php?p=jquery&t=".$obj["link"]."&f=".$obj["linkfield"]."\",\n";
				$txt.="delay: 0\n";
				$txt.="});\n";
				$txt.="});\n";
				$txt.="</script>\n";
			}
			else
			{
				$v=$txt;
				$query ="SELECT ".$obj["linkfield"]." AS txt FROM ".$MyOpt["tbl"]."_".$obj["link"]." AS fields ";
				$query.="WHERE fields.deleted=0 ";
				$query.="ORDER BY ".$obj["linkfield"]." ";
				$sql_ro->Query($query);

				$txt ="<select id='".$obj["name"]."' name='formArray[".$obj["name"]."]'>";
				$txt.="<option value=\"\" ".(($v=="") ? "selected" : "").">None</option>";
				for($i=0; $i<$sql_ro->rows; $i++)
				{ 
					$sql_ro->GetRow($i);
					$txt.="<option value=\"".utf8_encode($sql_ro->data["txt"])."\" ".(($v==utf8_encode($sql_ro->data["txt"])) ? "selected" : "").">".utf8_encode($sql_ro->data["txt"])."</option>";
				}
				$txt.="</select>";
			}
		}
		else if ($obj["type"]=="transform")
		{
			$v=$txt;
			$txt ="<select id='".$obj["name"]."' name='formArray[".$obj["name"]."]'>";
			$txt.="<option value='' ".(($v=="") ? "selected" : "").">None</option>";
			$txt.="<option value='uppercase' ".(($v=="uppercase") ? "selected" : "").">Upper case</option>";
			$txt.="<option value='lowercase' ".(($v=="lowercase") ? "selected" : "").">Lower case</option>";
			$txt.="<option value='ucword' ".(($v=="ucword") ? "selected" : "").">Upper word</option>";
			$txt.="</select>";
		}
		else if ($obj["type"]=="password")
		{
			$txt="<input name='formArray[".$obj["name"]."]' value='' type='password'>";
		}
		else
		{
		$txt="<input name=\"formArray[".$obj["name"]."]\" value=\"".$txt."\" type=\"".$type."\">";
		}
	}
	else
	{
		if ($obj["type"]=="link")
		{
			return DisplayObject($reso,$resd["field"],$form);
		}

		if ($obj["transform"]=="ucword")
		{
			$txt=UpperFirstLetter($var);
		}
		else if ($obj["transform"]=="lowercase")
		{
			$txt=strtolower($var);
		}
		if ($obj["transform"]=="uppercase")
		{
			$txt=strtoupper($var);
		}
		$txt="<span>".htmlentities($txt,ENT_HTML5)."</span>";
	}
	
	return $txt;
}


// Save an object from a table
function SaveObject($id,$name,$tab,$conf)
{ global $MyOpt,$sql_ro,$sql_rw,$gl_uid;

	$res_obj=loadObjectDef($name);
	if (!is_numeric($res_obj["id"]))
	{
		FatalError("Object definition not found","Object name:".$name);
	}
	$tabField=loadObjectFields($res_obj["id"],true);
	if (!is_array($tabField))
	{
		FatalError("No definition field found","");
	}

	if (!is_array($tab))
	{
		FatalError("Empty array","");
	}

	$query="UPDATE ".$MyOpt["tbl"]."_".$res_obj["tablename"]." SET ";
	foreach($tab as $f=>$d)
	{
		if (is_array($tabField[$f]))
		{
			if ($tabField[$f]["type"]=="link")
			{
				if (is_numeric($d))
				{
					$query.=$f."='".$d."', ";
				}	
				else
				{
					$q="SELECT id FROM ".$MyOpt["tbl"]."_".$tabField[$f]["link"]." WHERE ".$tabField[$f]["linkfield"]."='".utf8_decode(addslashes($d))."'";
					$resd=$sql_ro->QueryRow($q);
					$query.=$f."='".$resd["id"]."', ";					
				}
			}
			else if (($tabField[$f]["type"]=="password") && ($d!=''))
			{
				$query.=$f."='".md5($d)."', ";
			}
			else
			{
				// $query.=$f."='".utf8_encode(substr($d,0,50))."', ";
				$query.=$f."='".utf8_decode(addslashes($d))."', ";
			}
		}
	}
	$query.="deleted='0', ";
	$query.="uidupdate='".$gl_uid."', ";
	$query.="dteupdate=NOW() ";
	$query.="WHERE id='".$id."'";
	$sql_rw->Update($query);

	$query="SELECT system FROM ".$MyOpt["tbl"]."_".$res_obj["tablename"]." WHERE id='".$id."'";
	$res=$sql_ro->QueryRow($query);
	$mysys=$res["system"];
	
	if (($res_obj["postcreate"]=="sysObject.create") && ($tab["name"]!="") && ($mysys==0))
	{
		$query="CREATE TABLE `".$MyOpt["tbl"]."_".$tab["name"]."` (
				`id` int(10) UNSIGNED NOT NULL,
				`system` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
				`deleted` tinyint(3) UNSIGNED NOT NULL,
				`uidcreate` int(10) UNSIGNED NOT NULL,
				`dtecreate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`uidupdate` int(10) UNSIGNED NOT NULL,
				`dteupdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
		$sql_rw->Update($query);

		$query="UPDATE ".$MyOpt["tbl"]."_objects SET tablename='".$tab["name"]."' WHERE id='".$id."'";
		$sql_rw->Update($query);

		$query="ALTER TABLE `".$MyOpt["tbl"]."_".$tab["name"]."` ADD PRIMARY KEY (`id`);";
		$sql_rw->Update($query);
		$query="ALTER TABLE `".$MyOpt["tbl"]."_".$tab["name"]."` ADD INDEX (`system`);"; 
		$sql_rw->Update($query);
		$query="ALTER TABLE `".$MyOpt["tbl"]."_".$tab["name"]."` ADD INDEX (`deleted`);"; 
		$sql_rw->Update($query);
		$query="ALTER TABLE `".$MyOpt["tbl"]."_".$tab["name"]."` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;";
		$sql_rw->Update($query);

		$query="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` SET oid='".$id."', name='uidcreate', displayname='Created by', type='link', link='users', linkfield='login', system='1', hidden='1',locked=1,uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
		$sql_rw->Insert($query);
		$query="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` SET oid='".$id."',name='dtecreate', displayname='Created', type='datetime', link='', linkfield='', system='1', hidden='1', locked=1, uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
		$sql_rw->Insert($query);
		$query="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` SET oid='".$id."',name='uidupdate', displayname='Updated by', type='link', link='users', linkfield='login', system='1', hidden='1', locked=1, uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
		$sql_rw->Insert($query);
		$query="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` SET oid='".$id."',name='dteupdate', displayname='Updated', type='datetime', link='', linkfield='', system='1', hidden='1', locked=1, uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
		$sql_rw->Insert($query);

		$query="INSERT INTO `".$MyOpt["tbl"]."_views` SET name='".$tab["name"]."', displayname='".ucwords($tab["name"])."', oid='".$id."', type='list', uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
		$vid=$sql_rw->Insert($query);

		$query="INSERT INTO `".$MyOpt["tbl"]."_views_fields` SET vid='".$vid."', name='uidupdate', uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
		$sql_rw->Insert($query);
		$query="INSERT INTO `".$MyOpt["tbl"]."_views_fields` SET vid='".$vid."', name='dteupdate', uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
		$sql_rw->Insert($query);

		$query="UPDATE ".$MyOpt["tbl"]."_".$res_obj["tablename"]." SET system=2 WHERE id='".$id."'";
		$sql_rw->Update($query);
	}
	else if (($res_obj["postcreate"]=="sysField.create") && ($tab["name"]!="") && ($mysys==0))
	{
		$query="SELECT tablename FROM ".$MyOpt["tbl"]."_objects AS objects WHERE id='".$conf["linkedoid"]."'";
		$res=$sql_ro->QueryRow($query);

		$tab["type"]=strtolower($tab["type"]);
		
		$type="VARCHAR(20)";
		if ($tab["type"]=="smallstring")
		{
			$type="VARCHAR(20)";
		}
		else if ($tab["type"]=="mediumstring")
		{
			$type="VARCHAR(50)";
		}
		else if ($tab["type"]=="largestring")
		{
			$type="VARCHAR(100)";
		}
		else if ($tab["type"]=="string")
		{
			$type="VARCHAR(250)";
		}
		else if ($tab["type"]=="text")
		{
			$type="TEXT";
		}
		else if ($tab["type"]=="html")
		{
			$type="TEXT";
		}
		else if ($tab["type"]=="numeric")
		{
			$type="INT(11)";
		}
		else if ($tab["type"]=="yesno")
		{
			$type="VARCHAR(1)";
		}
		else if ($tab["type"]=="link")
		{
			$type="INT(11)";

			$query="SELECT id FROM ".$MyOpt["tbl"]."_objects AS objects WHERE name='".$tab["link"]."'";
			$reslo=$sql_ro->QueryRow($query);

			// Create related list
			$query="INSERT INTO `".$MyOpt["tbl"]."_views` SET name='".$res["tablename"]."-".$tab["name"]."', displayname='Related ".ucwords($res["tablename"])."', oid='".$conf["linkedoid"]."', type='list', robject='".$reslo["id"]."', rfield='".$tab["name"]."', uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
			$sql_rw->Insert($query);
		}

		$query="ALTER TABLE `".$MyOpt["tbl"]."_".$res["tablename"]."` ADD `".$tab["name"]."` ".$type." NOT NULL;";
		$sql_rw->Update($query);

		$query="UPDATE ".$MyOpt["tbl"]."_".$res_obj["tablename"]." SET system=2 WHERE id='".$id."'";
		$sql_rw->Update($query);
	}

	return "";
}

?>