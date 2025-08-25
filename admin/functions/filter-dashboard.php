<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	$filter = $_POST['filter'];

	if($filter == "" || $filter == "at") {
		$qTotalEarnings = mysqli_query($con, "
			SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket
		");
		$dTotalEarnings = mysqli_fetch_array($qTotalEarnings);
		$totalEarnings = $dTotalEarnings['total_accumulate'];
		
		$qTotalProfits = mysqli_query($con, "
			SELECT COALESCE(SUM(total_price), 0) AS total_profit FROM tbl_ticket WHERE win_ticket_id != 0
		");
		$dTotalProfits = mysqli_fetch_array($qTotalProfits);
		$totalProfits = $dTotalProfits['total_profit'];
		
		$qRafflesDone = mysqli_query($con, "
			SELECT COUNT(*) AS total_campaign FROM tbl_campaign WHERE status = 'closed'
		");
		$dRafflesDone = mysqli_fetch_array($qRafflesDone);
		$rafflesDone = $dRafflesDone['total_campaign'];
	}
	else if($filter == "lm") {
		$currentMonth = date("m");
		
		$qTotalEarnings = mysqli_query($con, "
			SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE DATE_FORMAT(purchased_date,'%m') = '$currentMonth'
		");
		$dTotalEarnings = mysqli_fetch_array($qTotalEarnings);
		$totalEarnings = $dTotalEarnings['total_accumulate'];
		
		$qTotalProfits = mysqli_query($con, "
			SELECT COALESCE(SUM(total_price), 0) AS total_profit FROM tbl_ticket WHERE win_ticket_id != 0 AND DATE_FORMAT(purchased_date,'%m') = '$currentMonth'
		");
		$dTotalProfits = mysqli_fetch_array($qTotalProfits);
		$totalProfits = $dTotalProfits['total_profit'];
		
		$qRafflesDone = mysqli_query($con, "
			SELECT COUNT(*) AS total_campaign FROM tbl_campaign WHERE status = 'closed' AND DATE_FORMAT(end_date,'%m') = '$currentMonth'
		");
		$dRafflesDone = mysqli_fetch_array($qRafflesDone);
		$rafflesDone = $dRafflesDone['total_campaign'];
	}
	else if($filter == "ltm") {
		$qTotalEarnings = mysqli_query($con, "
			SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE 
			purchased_date >= last_day(now()) + interval 1 day - interval 3 month
		");
		$dTotalEarnings = mysqli_fetch_array($qTotalEarnings);
		$totalEarnings = $dTotalEarnings['total_accumulate'];
		
		$qTotalProfits = mysqli_query($con, "
			SELECT COALESCE(SUM(total_price), 0) AS total_profit FROM tbl_ticket WHERE win_ticket_id != 0 AND 
			purchased_date >= last_day(now()) + interval 1 day - interval 3 month
		");
		$dTotalProfits = mysqli_fetch_array($qTotalProfits);
		$totalProfits = $dTotalProfits['total_profit'];
		
		$qRafflesDone = mysqli_query($con, "
			SELECT COUNT(*) AS total_campaign FROM tbl_campaign WHERE status = 'closed' AND 
			end_date >= last_day(now()) + interval 1 day - interval 3 month
		");
		$dRafflesDone = mysqli_fetch_array($qRafflesDone);
		$rafflesDone = $dRafflesDone['total_campaign'];
	}
	else if($filter == "loy") {
		$startQuery = date("Y-m-d",strtotime("-1 year"))." 00:00:00";
		$endQuery = date("Y-m-d")." 23:59:59";
		
		$qTotalEarnings = mysqli_query($con, "
			SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE 
			purchased_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
		");
		$dTotalEarnings = mysqli_fetch_array($qTotalEarnings);
		$totalEarnings = $dTotalEarnings['total_accumulate'];
		
		$qTotalProfits = mysqli_query($con, "
			SELECT COALESCE(SUM(total_price), 0) AS total_profit FROM tbl_ticket WHERE win_ticket_id != 0 AND 
			purchased_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
		");
		$dTotalProfits = mysqli_fetch_array($qTotalProfits);
		$totalProfits = $dTotalProfits['total_profit'];
		
		$qRafflesDone = mysqli_query($con, "
			SELECT COUNT(*) AS total_campaign FROM tbl_campaign WHERE status = 'closed' AND 
			end_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
		");
		$dRafflesDone = mysqli_fetch_array($qRafflesDone);
		$rafflesDone = $dRafflesDone['total_campaign'];
	}
	
	$data = array(
		"totalEarnings" => $totalEarnings,
		"totalProfits" => $totalProfits,
		"rafflesDone" => $rafflesDone
	);
	
	echo json_encode($data);
}
?>
