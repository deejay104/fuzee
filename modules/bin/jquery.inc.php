<?

	$result=array();

	
	$query = "SELECT ".$_REQUEST["f"]." FROM ".$MyOpt["tbl"]."_".$_REQUEST["t"]." AS fields ";
	$query.="WHERE fields.deleted=0 AND ".$_REQUEST["f"]." LIKE '%".$_REQUEST["term"]."%' ";
	$query.="ORDER BY ".$_REQUEST["f"]." ";
	$query.="LIMIT 20";
	$sql_ro->Query($query);

	for($i=0; $i<$sql_ro->rows; $i++)
	{ 
		$sql_ro->GetRow($i);
		array_push($result,$sql_ro->data[$_REQUEST["f"]]);
	}

	echo json_encode($result);
	exit;

		for($i=0; $i<$mssql->rows; $i++)
		  {
			$mssql->GetRow($i);
			array_push($result,$mssql->data["ticketid"]);
		  }
	
?>