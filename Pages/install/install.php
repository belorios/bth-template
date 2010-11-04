<?php
    
	$body   = null;
	$layout = "1col_std";
	
	if (isset($_POST['send'])) {
		
		//Funktion för att hantera fel vid databasanrop
		function ctlPrint($table, $type) {
			$GLOBALS['fail'][$table] = $GLOBALS['pdo']->getFault($GLOBALS['dbc']);
			$body = "
				<tr>
					<td>$type tabellen $table... &nbsp; &nbsp; &nbsp; </td>";
			if ($GLOBALS['fail'][$table] == "0") {
				$body .= "<td>Lyckades</td>";
			}
			else {
				$body .= "<td>Misslyckades</td>"; 
			}
			return "$body </tr>";
		}
		
		//Default värden
		$fail  = array();
		$fault = false;	
		$database = DB_SCHEMA;
		$clearOld = TRUE;
		
		//Tabellnamn
		$tableGroupUsers = DB_PREFIX . "GroupUsers";
		$tableUsers 	 = DB_PREFIX . "Users";
		$tableGroups	 = DB_PREFIX . "Groups";
	
		//Klasser
		$pdo   = new pdoConnection();
		$dbc   = $pdo->getConnection(false);
		$Users = new Users();
		
		//Rensar ut eventuella äldre tabeller
		if ($clearOld == true) {
			$stmt = $dbc->query("DROP TABLE IF EXISTS $tableGroupUsers");	$body .= ctlPrint($tableGroupUsers, "Tar bort");
			$stmt = $dbc->query("DROP TABLE IF EXISTS $tableUsers");		$body .= ctlPrint($tableUsers, "Tar bort");
			$stmt = $dbc->query("DROP TABLE IF EXISTS $tableGroups");		$body .= ctlPrint($tableGroups, "Tar bort");
			$body .= "<tr><td>&nbsp;</td></tr>";
		}
		
		//Skapar användartabellen
		$stmt = $dbc->query("
			CREATE TABLE $tableUsers (
			
			  -- Primary key(s)
			  idUsers BIGINT AUTO_INCREMENT NULL PRIMARY KEY,
			
			  -- Attributes
			  username VARCHAR(40)  NOT NULL UNIQUE,
			  realname VARCHAR(60)  NOT NULL,
			  email    VARCHAR(100) NOT NULL,
			  passwd   VARCHAR(64)  NOT NULL
			) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_swedish_ci
		");
		$body .= ctlPrint($tableUsers, "Skapar");
		
		//Skapar grupptabellen
		$stmt = $dbc->query("
			CREATE TABLE $tableGroups (
			
			-- Primary key(s)
				idGroups CHAR(3) NOT NULL PRIMARY KEY,
			
			-- Attributes
				shortdesc	VARCHAR(30)  NOT NULL,
				groupdesc 	VARCHAR(255) NOT NULL
			) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_swedish_ci
		");
		$body .= ctlPrint($tableGroups, "Skapar");
		
		//Skapar tabellen som länkar ihop användare med grupper
		$stmt = $dbc->query("
			CREATE TABLE $tableGroupUsers (
			
			-- Foreign keys
				idUsers BIGINT NOT NULL,
				idGroups CHAR(3) NOT NULL,
			
				FOREIGN KEY (idUsers)
					REFERENCES $tableUsers(idUsers)
					ON UPDATE CASCADE ON DELETE CASCADE,
				FOREIGN KEY (idGroups)
					REFERENCES $tableGroups(idGroups)
					ON UPDATE CASCADE ON DELETE CASCADE,
				PRIMARY KEY (idUsers, idGroups)
				
			
			-- Attributes
			-- None
			) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_swedish_ci
		");
		$body .= ctlPrint($tableGroupUsers, "Skapar");
		
		
		//Kontrollerar om något gått fel vid skapandet av tabeller
		foreach($fail as $fel) {
			if ($fel != "0") {
				$fault = true;
			}
		}
		
		if ($fault == false) {
			
			$body .= "<tr><td>&nbsp;</td></tr>";
			
			$dbc->beginTransaction();
		  
		  //Skapar användare
			$stmt = $dbc->query("
				INSERT INTO $tableUsers (username, realname, email, passwd) VALUES 
				('kalle', 'Kalle Kubik', 'kalle@example.com', '".$Users->passwdHash("kalle")."'),
				('erik', 'Erik Estrada', 'erik@example.com', '".$Users->passwdHash("erik")."'),
				('jenna', 'Jenna Jeans', 'jenna@example.com', '".$Users->passwdHash("jenna")."')
			");
			$body .= ctlPrint($tableUsers, "Skapar data för");
			
			//Skapar grupper
			$stmt = $dbc->query("
				INSERT INTO $tableGroups (idGroups, shortdesc, groupdesc) VALUES 
				('adm', 'Administratör', 'Administratörerna för sajten'),
				('mod', 'Modes skribent', 'Skriver om mode'),
				('skr', 'Skribent', 'Helt vanlig skribent')
			;
			");
			$body .= ctlPrint($tableGroups, "Skapar data för");
			
			//Mappar användare mot grupper
			$stmt = $dbc->query("
				INSERT INTO $tableGroupUsers (idGroups, idUsers) VALUES 
				('adm', 1), 
				('mod', 2),
				('skr', 3)
			;
			");
			$body .= ctlPrint($tableGroupUsers, "Skapar data för");
			
			//Skapar dummy data om detta är valt
			if ($_POST['dummyData'] == '1') {
			}
				
			$dbc->commit();
		}
		
		foreach($fail as $fel) {
			if ($fel != "0") {
				$_SESSION['errorMessage'][] = $fel;
				$fault = true;
			}
		}
		
		if ($fault == true) {
		  
			$success = "
				<p>
					<b>Installationen misslyckades!</b> <br /> 
					Var god försök rätta till felen och prova sedan igen. <br />
					<a href='" . PATH_SITE . "/install'>Klicka här för att försöka igen</a>
				</p>
			";
		}
		else {
			
			if ($_POST['dummyData'] == '1') {
	      	//Initiera rss flödet
	      }	
			
			$success = "<p><b>Installationen lyckades!</b></p>";
		}
		
		$body = "
			<h1>Installerar bloggen</h1>
			<p>
				<table>
					$body
				</table>
				$success
			</p>
		";
	}
	else {
		$prefixText = (DB_PREFIX != "") ? ", alla tabeller kommer installeras med prefixet ".DB_PREFIX : null;
		$body .= "
			<h1>Installera bloggen</h1>
			<p>
				Detta kommer att installera databasen för applikation{$prefixText}. <br />
				<span style='color: #e62011; font-weight: bold;'>Varning</span> detta kommer att radera eventuella gamla tabeller. 
			</p>
				
				<form action='' method='post'> 
				Vill du fylla databasen med lite dummy data? &nbsp;
					 <input type='radio' checked='checked' name='dummyData' value='1' /> Ja
					 <input type='radio' name='dummyData' value='0' /> Nej 
					 <p>
					 	<input type='submit' name='send' value='Installera' />
					 </p>
				</form>
		";
	}
