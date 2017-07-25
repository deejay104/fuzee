<?

	$result=array();

	
	$query ="SELECT ".$_REQUEST["f"]." FROM ".$MyOpt["tbl"]."_".$_REQUEST["t"]." AS fields ";
	$query.="WHERE fields.deleted=0 AND ".$_REQUEST["f"]." LIKE '%".utf8_decode($_REQUEST["term"])."%' ";
	$query.="ORDER BY ".$_REQUEST["f"]." ";
	$query.="LIMIT 20";
	$sql_ro->Query($query);

	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);
		array_push($result,utf8_encode($sql_ro->data[$_REQUEST["f"]]));
	}

	echo json_encode($result);
	exit;
	
?>