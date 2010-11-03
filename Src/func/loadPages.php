<?php
	/**
	 * USE ONLY IF THE SERVER DOESNT SUPPORTING MOD_REWRITE
	 */
    /*	
		$data = explode("/",@$HTTP_SERVER_VARS['PATH_INFO']);
		$page = isset($data[1]) ? $data[1] : "hem";
		
		if (isset($data[2])) {
			
			if (substr($data[2], 0, 2) == "id") {
			$id = substr($data[2], 3);
			}
			else {
				$action = $data[2];
				$id = isset($data[1]) ? substr($data[3], 3) : null;
			}
			
		}
	*/	
	
	$page   = isset($_GET['p']) ? $_GET['p'] : 'hem';
	$id     = isset($_GET['id']) ? substr($_GET['id'], 3) : null;
	$action = isset($_GET['action']) ? $_GET['action'] : 'none';
	
	//Sätter sessionen för senaste sidan man kollat på
	$_SESSION['currentPage'] = $page;
	$currentPage = "?p=$page";
	
	switch ($page) {
		
		case "hem":
			$selectedPage = "hem.php";
		break;
		
		//Install pages
		
		case "install":
			$selectedPage = "install/install.php";
		break;
		
		//Inlog utlogg sidor
		
		case "login":
			$selectedPage = "login/login.php";
		break;
		
		case "loginprocess":
			$selectedPage = "login/loginProcess.php";
		break;
		
		case "logout":
			$selectedPage = "login/logoutProcess.php";
		break;
		
		//Hanterar sidstil
		case "andrastil":
			$selectedPage = "sitestyle.php";
		break;
		
		//Visar alla filer
		case "visafiler": 
			$selectedPage = "viewfiles/showfiles.php";
		break;
		
		//Hanterar användare
		
		case "visaAnvandare":
			$selectedPage = "users/showUser.php";
		break;
		
		default:
			$selectedPage = "nofind.php";
		break;
		
		
	}
	
	require_once(PATH_PAGES . $selectedPage);