<?
// ---- Test if the access to this page is allowed
	// To do

// ---- Load parameters
	$myview=CheckVar("v",'var',50);
	
// ---- Load actual list
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_views WHERE name='$myview'";
	$resv = $sql_ro->QueryRow($query);

	$query = "DELETE FROM ".$MyOpt["tbl"]."_views_fields WHERE vid='".$resv["id"]."'";
	$sql_rw->Query($query);


// ---- Update field
	foreach($_POST["id"] as $i=>$d)
	{
		$query = "INSERT INTO ".$MyOpt["tbl"]."_views_fields SET vid='".$resv["id"]."',name='".$d."',pos='".$i."',uidcreate='".$gl_uid."',dtecreate='".now()."',uidupdate='".$gl_uid."',dteupdate='".now()."'";
		$sql_rw->Update($query);
	}
?>