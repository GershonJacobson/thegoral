<?php
	session_start();
	require("../../config/session.php");

	$searchVal = $_POST['searchVal'];
	
	$list = [];
	
	if($searchVal != "") {
		$qSearch = mysqli_query($con, "
			SELECT DISTINCT first_name, last_name, email, phone FROM tbl_ticket WHERE purchased_by = 0 AND (first_name LIKE '%$searchVal%' OR last_name LIKE '%$searchVal%' OR email LIKE '%$searchVal%' OR phone LIKE '%$searchVal%') ORDER BY first_name ASC
		");
		while($dSearch = mysqli_fetch_array($qSearch)) {
			array_push($list, (object)[
				"firstName" => $dSearch['first_name'],
				"lastName" => $dSearch['last_name'],
				"emailAddress" => $dSearch['email'],
				"phone" => $dSearch['phone']
			]);
		}
		
		echo json_encode($list);
	}
	else {
		header("Location: 403");
	}
?>