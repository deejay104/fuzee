<?php
// ---------------------------------------------------------------------------------------------
//   Update version
// ---------------------------------------------------------------------------------------------
/*
    Fuzee
    Copyright (C) 2017 Matthieu Isorez

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>

<?php
	header('Content-type: text/html; charset=ISO-8859-1');
	error_reporting(E_ALL & ~E_NOTICE);
	set_time_limit (0);

// ---- Charge les prérequis
	$resume=true;
	require ("class/mysql.inc.php");

// ---- Charge les variables
	require ("version.txt");
	require ("config/config.inc.php");
	if (file_exists("config/variables.inc.php"))
	{
		require ("config/variables.inc.php");
	}

	require ("config/variables.tmpl.php");
	require ("modules/lib/functions.inc.php");

	echo "Code version : $myrev<br />";
	echo "Database : $db <br /><br />";

// ---- Vérification des variables
	echo "Update variables <br />";

	$nb=0;
	$MyOptTab=array();
	foreach ($MyOptTmpl as $nom=>$d)
	{
		if (is_array($d))
		{
			foreach($d as $var=>$dd)
		  {
				if(!isset($MyOpt[$nom][$var]))
			  {
			  	$MyOptTab[$nom][$var]=$dd;
			  	$nb=$nb+1;
			  	echo "Add : \$MyOpt[\"".$nom."\"][\"".$var."\"]='".$dd."'<br>";
			  }
			  else
			  {
			  	$MyOptTab[$nom][$var]=$MyOpt[$nom][$var];
			  }
			}
		}
		else
		{
			if(!isset($MyOpt[$nom]))
		  {
		  	$MyOptTab[$nom]["valeur"]=$d;
		  	$nb=$nb+1;
		  	echo "Add : \$MyOpt[\"".$nom."\"]='".$d."'<br>";
		  }
		  else
		  {
		  	$MyOptTab[$nom]["valeur"]=$MyOpt[$nom];
		  }
		}
	}

	if ($nb>0)
	{
		echo $nb." added variables<br>";

		$ret=GenerateVariables($MyOptTab);
		echo $ret."<br />";
	}
	echo "<br />";

// ---- Connexion à la base de données
	$sql_rw = new mysql_class($db_user_rw, $db_pwd_rw, $db_host_rw, $db, $db_port_rw);

	$query="CREATE TABLE IF NOT EXISTS `".$MyOpt["tbl"]."_config` (`param` VARCHAR( 20 ) NOT NULL ,`value` VARCHAR( 20 ) NOT NULL) ENGINE = MYISAM ";
	$res = $sql_rw->Update($query);

	$query="SELECT value FROM ".$MyOpt["tbl"]."_config WHERE param='dbversion'";
	$res=$sql_rw->QueryRow($query);
	$ver=$res["value"];

	if ($ver=="")
	{
		$ver="000";
		$query="INSERT INTO ".$MyOpt["tbl"]."_config (param,value) VALUES ('dbversion','$ver')";
		$sql_rw->Insert($query);
	}

	echo "Database version : $ver <br />";


// Initial creation
  if ($ver<100)
  {
		$sql=array();
		$sql[] = "CREATE TABLE `".$MyOpt["tbl"]."_objects` (
				  `id` int(10) UNSIGNED NOT NULL,
				  `name` varchar(50) NOT NULL DEFAULT '',
				  `tablename` varchar(20) NOT NULL,
				  `system` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
				  `deleted` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
				  `postcreate` varchar(20) NOT NULL,
				  `postupdate` varchar(20) NOT NULL,
				  `postdelete` varchar(20) NOT NULL,
				  `uidcreate` int(10) UNSIGNED NOT NULL,
				  `dtecreate` datetime NOT NULL,
				  `uidupdate` int(10) UNSIGNED NOT NULL,
				  `dteupdate` datetime NOT NULL,
				PRIMARY KEY  (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;"; 

		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_objects` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_objects`
			ADD KEY `deleted` (`deleted`),
			ADD KEY `system` (`system`)";
  
		$sql[] = "CREATE TABLE `".$MyOpt["tbl"]."_objects_fields` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `oid` int(10) UNSIGNED NOT NULL,
			  `name` varchar(50) NOT NULL DEFAULT '',
			  `displayname` varchar(50) NOT NULL,
			  `posx` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
			  `posy` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
			  `type` varchar(50) NOT NULL DEFAULT 'varchar',
			  `transform` varchar(10) NOT NULL,
			  `link` varchar(20) NOT NULL,
			  `linkfield` varchar(20) NOT NULL,
			  `system` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
			  `deleted` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
			  `hidden` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
			  `readonly` tinyint(3) UNSIGNED NOT NULL,
			  `locked` tinyint(3) UNSIGNED NOT NULL,
			  `uidcreate` int(10) UNSIGNED NOT NULL,
			  `dtecreate` datetime NOT NULL,
			  `uidupdate` int(10) UNSIGNED NOT NULL,
			  `dteupdate` datetime NOT NULL,
			 PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;"; 

		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_objects_fields` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_objects_fields`
			ADD KEY `deleted` (`deleted`),
			ADD KEY `hidden` (`hidden`),
			ADD KEY `system` (`system`),
			ADD KEY `oid` (`oid`);";


		$sql[] = "CREATE TABLE `".$MyOpt["tbl"]."_views` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `name` varchar(50) NOT NULL DEFAULT '',
			  `displayname` varchar(50) NOT NULL,
			  `oid` int(10) UNSIGNED NOT NULL,
			  `type` varchar(10) NOT NULL DEFAULT '',
			  `robject` int(10) UNSIGNED DEFAULT NULL,
			  `rfield` varchar(20) DEFAULT NULL,
			  `system` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
			  `deleted` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
			  `hidden` tinyint(4) NOT NULL DEFAULT '0',
			  `uidcreate` int(10) UNSIGNED NOT NULL,
			  `dtecreate` datetime NOT NULL,
			  `uidupdate` int(10) UNSIGNED NOT NULL,
			  `dteupdate` datetime NOT NULL,
			 PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;"; 

		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_views` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_views`
			ADD KEY `deleted` (`deleted`),
			ADD KEY `hidden` (`hidden`),
			ADD KEY `system` (`system`),
			ADD KEY `oid` (`oid`);";
			
		$sql[] = "CREATE TABLE IF NOT EXISTS `".$MyOpt["tbl"]."_views_fields` (
			`id` int unsigned NOT NULL auto_increment,
			`vid` int unsigned NOT NULL,
			`name` varchar(50) NOT NULL default '',
			`pos` tinyint(3) UNSIGNED NOT NULL,
			`deleted` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
			`uidcreate` int(10) UNSIGNED NOT NULL,
			`dtecreate` datetime NOT NULL,
			`uidupdate` int(10) UNSIGNED NOT NULL,
			`dteupdate` datetime NOT NULL,
			 PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;"; 

		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_views_fields` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_views_fields`
			ADD KEY `deleted` (`deleted`),
			ADD KEY `vid` (`vid`);";
			
		$sql[] = "CREATE TABLE IF NOT EXISTS `".$MyOpt["tbl"]."_users` (
			 `id` int unsigned NOT NULL auto_increment,
			 `login` varchar(50) NOT NULL default '',
			 `email` varchar(100) NOT NULL default '',
			 `password` varchar(40) NOT NULL default '',
			 `firstname` varchar(40) NOT NULL default '',
			 `lastname` varchar(40) NOT NULL default '',
			 `deleted` tinyint unsigned NOT NULL default '0',
			 `system` tinyint unsigned NOT NULL default '0',
			 `uidcreate` INT UNSIGNED NOT NULL, `dtecreate` DATETIME NOT NULL,
			 `uidupdate` INT UNSIGNED DEFAULT NULL, `dteupdate` DATETIME DEFAULT NULL,
			 PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;"; 

		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_users` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
		$sql[]="ALTER TABLE `".$MyOpt["tbl"]."_users`
			ADD KEY `deleted` (`deleted`),
			ADD KEY `email` (`email`),
			ADD KEY `login` (`login`);";


		
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_users` (`id`,`login`,`email`,`password`,`firstname`,`lastname`, `uidcreate`, `dtecreate`) VALUES ('1', 'admin', 'email@example.com', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'admin', 1, NOW());";

		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects` (`id`,`name`,`tablename`,`system`, `uidcreate`, `dtecreate`,`postcreate`) VALUES (1,'objects','objects', 1, 1, NOW(),'sysObject.create');";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects` (`id`,`name`,`tablename`,`system`, `uidcreate`, `dtecreate`,`postcreate`) VALUES (2,'objects_fields','objects_fields', 1, 1, NOW(),'sysField.create');";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects` (`id`,`name`,`tablename`,`system`, `uidcreate`, `dtecreate`) VALUES (3,'users','users', 1, 1, NOW());";

		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (1,'name', 'Name', 'varchar', 1, 1, NOW());";

		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`,`hidden`, `uidcreate`, `dtecreate`) VALUES (2,'oid', 'oid', 'int', 1, 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`,`readonly`, `uidcreate`, `dtecreate`) VALUES (2,'name', 'Name', 'mediumstring', 1, 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (2,'displayname', 'Displayname', 'mediumstring', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`transform`,`system`,`readonly`,`uidcreate`, `dtecreate`) VALUES (2,'type', 'Type', 'type', 'uppercase', 1, 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (2,'transform', 'transform', 'transform', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`link`,`linkfield`,`system`,`readonly`, `uidcreate`, `dtecreate`) VALUES (2,'link', 'link', 'syslink', 'objects', 'name', 1, 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`,`readonly`, `uidcreate`, `dtecreate`) VALUES (2,'linkfield', 'linkfield', 'sysfield', 1, 1, 1, NOW());";

		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (3,'login', 'Login', 'varchar', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (3,'email', 'Email', 'varchar', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`, `hidden`, `uidcreate`, `dtecreate`) VALUES (3,'password', 'Password', 'password', 1, 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (3,'firstname', 'Firstname','varchar', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` (`oid`,`name`,`displayname`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (3,'lastname', 'Lastname','varchar', 1, 1, NOW());";

		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` SET `oid`=3,`name`='uidcreate',`displayname`='Created by',`type`='link',`link`='users',`linkfield`='login',`system`=1, `locked`=1, `readonly`=1,`uidcreate`=1,dtecreate=NOW();";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` SET `oid`=3,`name`='dtecreate',`displayname`='Created',`type`='datetime',`system`=1, `locked`=1, `readonly`=1,`uidcreate`=1,dtecreate=NOW();";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` SET `oid`=3,`name`='uidupdate',`displayname`='Updated by',`type`='link',`link`='users',`linkfield`='login',`system`=1, `locked`=1, `readonly`=1,`uidcreate`=1,dtecreate=NOW();";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_objects_fields` SET `oid`=3,`name`='dteupdate',`displayname`='Updated',`type`='datetime',`system`=1, `locked`=1, `readonly`=1,`uidcreate`=1,dtecreate=NOW();";

		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views` (`id`,`name`,`displayname`,`oid`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (1,'objects', 'Objects', 1, 'list', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`, `uidcreate`, `dtecreate`) VALUES (1,'name', 1, NOW());";

		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views` (`id`,`name`,`displayname`,`oid`,`type`,`robject`,`rfield`,`system`, `uidcreate`, `dtecreate`) VALUES (2,'objects_fields', 'Fields', 2, 'list', 1, 'oid', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`,`pos`, `uidcreate`, `dtecreate`) VALUES (2,'name', 0, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`,`pos`, `uidcreate`, `dtecreate`) VALUES (2,'displayname', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`,`pos`, `uidcreate`, `dtecreate`) VALUES (2,'type', 2, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`,`pos`, `uidcreate`, `dtecreate`) VALUES (2,'transform', 3, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`,`pos`, `uidcreate`, `dtecreate`) VALUES (2,'link', 4, 1, NOW());";

		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views` (`id`,`name`,`displayname`,`oid`,`type`,`system`, `uidcreate`, `dtecreate`) VALUES (3,'users', 'Users', 3, 'list', 1, 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`, `uidcreate`, `dtecreate`) VALUES (3,'firstname', 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`, `uidcreate`, `dtecreate`) VALUES (3,'lastname', 1, NOW());";
		$sql[]="INSERT INTO `".$MyOpt["tbl"]."_views_fields` (`vid`,`name`, `uidcreate`, `dtecreate`) VALUES (3,'email', 1, NOW());";

		
		
		UpdateDB($sql,"100");
	}

// *********************************************************************************************************

	echo "<a href='index.php'>Login</a>";
	
// *********************************************************************************************************

function UpdateDB($sql,$setver)
  { global $sql_rw,$err,$MyOpt;
  	echo "Update v".$setver;
		$mysql_err=0;

  	foreach($sql as $i=>$query)
	  {
	  	$sql_rw->Update($query);
	  }

		if ($mysql_err==0)
	  {
			$query="UPDATE ".$MyOpt["tbl"]."_config SET value='$setver' WHERE param='dbversion'";
			$sql_rw->Update($query);
			echo " [done]<br />";
		  }
		else
	  {
			echo " [error]<br />";
	  }
  }

?>