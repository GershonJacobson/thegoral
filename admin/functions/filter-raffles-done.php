<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	$filter = $_POST['filter'];
	
	if($filter == "" || $filter == "at") {
		$qClosedCampaign = mysqli_query($con, "SELECT COUNT(*) AS total_campaign FROM tbl_campaign");
		$dClosedCampaign = mysqli_fetch_array($qClosedCampaign);
		$rafflesDone = $dClosedCampaign['total_campaign'];
		
		$qWeeklyRaffles = mysqli_query($con, "SELECT COUNT(*) AS total_weekly_raffles FROM tbl_campaign WHERE category = 'weekly'");
		$dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles);
		$totalWeeklyRaffles = $dWeeklyRaffles['total_weekly_raffles'];
		
		$qUserCampaigns = mysqli_query($con, "SELECT COUNT(*) AS total_user_campaign FROM tbl_campaign WHERE category = ''");
		$dUserCampaigns = mysqli_fetch_array($qUserCampaigns);
		$totalUserCampaign = $dUserCampaigns['total_user_campaign'];
	}
	else if($filter == "lm") {
		$currentMonth = date("m");
		
		$qClosedCampaign = mysqli_query($con, "SELECT COUNT(*) AS total_campaign FROM tbl_campaign WHERE 
		DATE_FORMAT(end_date,'%m') = '$currentMonth'
		");
		$dClosedCampaign = mysqli_fetch_array($qClosedCampaign);
		$rafflesDone = $dClosedCampaign['total_campaign'];
		
		$qWeeklyRaffles = mysqli_query($con, "SELECT COUNT(*) AS total_weekly_raffles FROM tbl_campaign WHERE category = 'weekly' AND 
		DATE_FORMAT(end_date,'%m') = '$currentMonth'
		");
		$dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles);
		$totalWeeklyRaffles = $dWeeklyRaffles['total_weekly_raffles'];
		
		$qUserCampaigns = mysqli_query($con, "SELECT COUNT(*) AS total_user_campaign FROM tbl_campaign WHERE category = '' AND 
		DATE_FORMAT(end_date,'%m') = '$currentMonth'
		");
		$dUserCampaigns = mysqli_fetch_array($qUserCampaigns);
		$totalUserCampaign = $dUserCampaigns['total_user_campaign'];
	}
	else if($filter == "ltm") {
		$qClosedCampaign = mysqli_query($con, "SELECT COUNT(*) AS total_campaign FROM tbl_campaign WHERE 
		end_date >= last_day(now()) + interval 1 day - interval 3 month
		");
		$dClosedCampaign = mysqli_fetch_array($qClosedCampaign);
		$rafflesDone = $dClosedCampaign['total_campaign'];
		
		$qWeeklyRaffles = mysqli_query($con, "SELECT COUNT(*) AS total_weekly_raffles FROM tbl_campaign WHERE category = 'weekly' AND 
		end_date >= last_day(now()) + interval 1 day - interval 3 month
		");
		$dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles);
		$totalWeeklyRaffles = $dWeeklyRaffles['total_weekly_raffles'];
		
		$qUserCampaigns = mysqli_query($con, "SELECT COUNT(*) AS total_user_campaign FROM tbl_campaign WHERE category = '' AND 
		end_date >= last_day(now()) + interval 1 day - interval 3 month
		");
		$dUserCampaigns = mysqli_fetch_array($qUserCampaigns);
		$totalUserCampaign = $dUserCampaigns['total_user_campaign'];
	}
	else if($filter == "loy") {
		$qClosedCampaign = mysqli_query($con, "SELECT COUNT(*) AS total_campaign FROM tbl_campaign WHERE 
		end_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
		");
		$dClosedCampaign = mysqli_fetch_array($qClosedCampaign);
		$rafflesDone = $dClosedCampaign['total_campaign'];
		
		$qWeeklyRaffles = mysqli_query($con, "SELECT COUNT(*) AS total_weekly_raffles FROM tbl_campaign WHERE category = 'weekly' AND 
		end_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
		");
		$dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles);
		$totalWeeklyRaffles = $dWeeklyRaffles['total_weekly_raffles'];
		
		$qUserCampaigns = mysqli_query($con, "SELECT COUNT(*) AS total_user_campaign FROM tbl_campaign WHERE category = '' AND 
		end_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
		");
		$dUserCampaigns = mysqli_fetch_array($qUserCampaigns);
		$totalUserCampaign = $dUserCampaigns['total_user_campaign'];
	}
	
	$data = array(
		"rafflesDone" => $rafflesDone,
		"totalWeeklyRaffles" => $totalWeeklyRaffles,
		"totalUserCampaign" => $totalUserCampaign
	);
	
	echo json_encode($data);
}
?>
