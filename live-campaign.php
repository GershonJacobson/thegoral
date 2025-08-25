<?php
session_start();

require("config/session.php");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
	<meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Live campaign - The Goral</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.svg" />

    <link
      rel="stylesheet"
      href="assets/css/bootstrap/css/bootstrap.min.css"
    />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/font/fontawesome/css/all.min.css" />

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
  
    <script src="assets/font/fontawesome/js/all.min.js"></script>
	<script src="assets/js/index.js"></script>
	<script>
	$(document).ready(function () {
		$('.input-radio .radio').change(function() {
			var selectedFilter = $(this).val();
			
			window.location = "?filter=" + selectedFilter;
		});
		
		$('.load-more').click(function(){
			var row = Number($('#row').val());
			var allcount = Number($('#all').val());
			var currentNo = Number($('#currentNo').val());
			var filter = $("#filter").val();
			var rowperpage = 5;
			row = row + rowperpage;

			if(row <= allcount){
				$("#row").val(row);

				$.ajax({
					url: 'functions/get-live-campaign-data',
					type: 'post',
					data: {row:row, currentNo: currentNo, filter: filter},
					beforeSend:function(){
						$(".load-more").text("Loading...");
					},
					success: function(response){
						
						// Setting little delay while displaying new content
						setTimeout(function() {
							// appending posts after last post with class="post"
							$(".post:last").after(response).show().fadeIn("slow");

							var rowno = row + rowperpage;
							$('#currentNo').val(rowno);
							// checking row value is greater than allcount or not
							if(rowno > allcount){

								// Change the text and background
								$('.load-more').hide();
							}else{
								$(".load-more").html('<i class="fa-solid fa-chevron-down"></i>');
							}
						}, 2000);

					}
				});
			}else{
				$('.load-more').text("Loading...");

				// Setting little delay while removing contents
				setTimeout(function() {

					// When row is greater than allcount then remove all class='post' element after 3 element
					$('.post:nth-child(3)').nextAll('.post').remove();

					// Reset the value of row
					$("#row").val(0);

					// Change the text and background
					$('.load-more').html('<i class="fa-solid fa-chevron-down"></i>');
					$('.load-more').css("background","#15a9ce");
					
				}, 2000);
			}
		});
	});
	</script>
  </head>
  <body>
    <div class="header-ac-bg">
      <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
          <div class="container-fluid">
            <a class="navbar-brand" href="/">
              <img class="logo" src="assets/images/logo.svg" alt="logo" />
            </a>
            <button
              class="navbar-toggler collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbarNav"
              aria-controls="navbarNav"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class="icon-bar top-bar"></span>
              <span class="icon-bar middle-bar"></span>
              <span class="icon-bar bottom-bar"></span>
            </button>
            
			<?php require("header.php"); ?>
          </div>
        </nav>
        <div class="row">
          <div class="title-c">Live Campaign</div>
        </div>
        <div class="row">
          <div class="search-box">
            <img src="assets/images/icon-search.png" alt="" />
            <input type="text" placeholder="Search..." />
          </div>
        </div>
        <div class="row">
          <div class="menu-c">
            <a
              class="active"
              href="/live-campaign"
              style="border-bottom: 3px solid #fec862"
              >Live Campaigns</a
            >
            <a href="/all-campaign">All Campaigns</a>
          </div>
        </div>
      </div>
    </div>
    <div class="content-c">
		<div class="container">
		<div class="row">
			<div class="filter-by-container" style="margin-top:10px">
				<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
					Sort by
				</button>
				
				<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
					<li>
						<div class="label-radio"><label for="pmmtl"> Pot: Most Money - Least</label></div>
						<div class="input-radio"><input type="radio" class="radio" name="filter" id="pmmtl" value="pmmtl" <?php if($_GET['filter'] == "" || $_GET['filter'] == "pmmtl") { echo "checked"; } ?>/></div>
					</li>
					
					<li>
						<div class="label-radio"><label for="clltm"> Countdown: least time left - most</label></div>
						<div class="input-radio"><input type="radio" class="radio" name="filter" id="clltm" value="clltm" <?php if($_GET['filter'] == "clltm") { echo "checked"; } ?>/></div>
					</li>
				</ul>
			</div>
		
			<script>
			var countdowns = [];
			</script>
			<?php
			$rowperpage = 5;

            $allcount_query = "SELECT count(*) as allcount FROM tbl_campaign WHERE public = 1 AND (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1) AND category = ''";
            $allcount_result = mysqli_query($con,$allcount_query);
            $allcount_fetch = mysqli_fetch_array($allcount_result);
            $allcount = $allcount_fetch['allcount'];
			
			if($allcount > 0) {
				if($_GET['filter'] == "" || $_GET['filter'] == "pmmtl") {
					$query = mysqli_query($con, "
						SELECT
						tbl_campaign.campaign_id,
						tbl_campaign.first_name,
						tbl_campaign.last_name,
						tbl_campaign.email_address,
						tbl_campaign.phone,
						tbl_campaign.page_url,
						tbl_campaign.campaign_name,
						tbl_campaign.public,
						tbl_campaign.status,
						DATE_FORMAT(tbl_campaign.start_date,'%d') AS start_date,
						DATE_FORMAT(tbl_campaign.start_date,'%Y-%m-%d') AS start_date_f,
						tbl_campaign.end_date,
						DATE_FORMAT(tbl_campaign.end_date,'%Y-%m-%d') AS end_date_f,
						DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
						DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
						COALESCE(SUM(total_price),0) as rating
						FROM tbl_campaign
						LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
						WHERE
						tbl_campaign.public = 1 AND (tbl_campaign.status = 'open' AND tbl_campaign.keep_show = 0) OR (tbl_campaign.status = 'closed' AND tbl_campaign.keep_show = 1) AND tbl_campaign.category = ''
						GROUP BY tbl_campaign.campaign_id 
						ORDER BY rating DESC limit 0,$rowperpage");
				}
				else if($_GET['filter'] == "plmtm") {
					$query = mysqli_query($con, "
						SELECT
						tbl_campaign.campaign_id,
						tbl_campaign.first_name,
						tbl_campaign.last_name,
						tbl_campaign.email_address,
						tbl_campaign.phone,
						tbl_campaign.page_url,
						tbl_campaign.campaign_name,
						tbl_campaign.public,
						tbl_campaign.status,
						DATE_FORMAT(tbl_campaign.start_date,'%d') AS start_date,
						DATE_FORMAT(tbl_campaign.start_date,'%Y-%m-%d') AS start_date_f,
						tbl_campaign.end_date,
						DATE_FORMAT(tbl_campaign.end_date,'%Y-%m-%d') AS end_date_f,
						DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
						DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
						COALESCE(SUM(total_price),0) as rating
						FROM tbl_campaign
						LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
						WHERE
						tbl_campaign.public = 1 AND (tbl_campaign.status = 'open' AND tbl_campaign.keep_show = 0) OR (tbl_campaign.status = 'closed' AND tbl_campaign.keep_show = 1) AND tbl_campaign.category = ''
						GROUP BY tbl_campaign.campaign_id 
						ORDER BY rating ASC limit 0,$rowperpage");
				}
				else if($_GET['filter'] == "clltm") {
					$query = mysqli_query($con, "
						SELECT * FROM tbl_campaign WHERE public = 1 AND (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1) AND tbl_campaign.category = '' ORDER BY status DESC, end_date ASC limit 0,$rowperpage");
				}
				else if($_GET['filter'] == "cmltl") {
					$query = mysqli_query($con, "
						SELECT * FROM tbl_campaign WHERE public = 1 AND (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1) AND tbl_campaign.category = '' ORDER BY end_date DESC limit 0,$rowperpage");
				}
				
				if(mysqli_num_rows($query) > 0) {
					while($data = mysqli_fetch_array($query)) {
					?>
						<script>
						countdowns.push({
							campaignID: "<?php echo $data['campaign_id']; ?>",
							countdownDate: new Date("<?php echo $data['end_date']; ?>".replace(" ", "T")).getTime()
						  });
						</script>
						
						<div class="col-md-4 post" style="margin-top:10px" id="post_<?php echo $data['campaign_id']; ?>">
							<div class="card">
								<div class="card-body">
									<a href="/<?php echo $data['page_url']; ?>">
									<h5 class="card-title"><img alt="" src="assets/images/user-icon.png"> <?php echo $data['campaign_name']; ?></h5>
									<h6 class="card-subtitle mb-2">
										$<?php
										$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
										$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
										
										echo $dAccumulateTicket['total_accumulate'];
										?>
									</h6>
									<p class="card-text">
										<?php
										$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
										
										echo mysqli_num_rows($qAccumulateParticipant);
										?>
										Participant
									</p>
									<div class="card-info">
										<span class="live-c"><img alt="" src="assets/images/time-icon.png"></span> 
										<!-- <span class="live-c">Time<br>remaining</span>  -->
										<!-- <span class="live-c" id="days"></span> 
										<span class="live-c" id="hours"></span> 
										<span class="live-c" id="minutes"></span>
										<span class="live-c" id="seconds"></span>-->
										<span style="display:flex" class="countdown-<?php echo $data['campaign_id']; ?>">0d 0h 0m 0s</span>
									</div></a>
								</div>
							</div>
						</div>
					<?php
					}
				}
				?>
				
				<script>
				$(document).ready(function () {
					var timer = setInterval(function() {
						// Get todays date and time
						
						var now = Date.now();
						var index = countdowns.length - 1;
						
						// we have to loop backwards since we will be removing
						// countdowns when they are finished
						while (index >= 0) {
							var countdown = countdowns[index];
							// Find the distance between now and the count down date
							
							var distance = countdown.countdownDate - now;
							
							// Time calculations for days, hours, minutes and seconds
							var days = Math.floor(distance / (1000 * 60 * 60 * 24));
							var hours = Math.floor(
								(distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
							var minutes = Math.floor(
								(distance % (1000 * 60 * 60)) / (1000 * 60));
							var seconds = Math.floor((distance % (1000 * 60)) / 1000);
							//var timerElement = document.getElementById("race" + countdown.id);
							var abc = "<span>"+days+"<br>Days</span>" + "<span>"+hours+"<br>Hours</span>" + "<span>"+minutes+"<br>Minutes</span>" + "<span>"+seconds+"<br>Seconds</span>";
							$(".countdown-" + countdown.campaignID).html(abc);
							// If the count down is over, write some text
							if (distance < 0) {
								//timerElement.innerHTML = "EXPIRED";
								// this timer is done, remove it
								$(".countdown-" + countdown.campaignID).text("COMPLETED");
								countdowns.splice(index, 1);
							} else {
								//timerElement.innerHTML =  hours + "h " + minutes + "m " + seconds + "s ";
							}
							index -= 1;
						}
						
						// if all countdowns have finished, stop timer
						if (countdowns.length < 1) {
							clearInterval(timer);
						}
					}, 1000);
				});
				</script>
				
			<?php
			}
			?>
      		</div>
			
			<?php
			if($allcount > 0) {
				if($allcount > 5) {
				?>
					<h2 class="load-more" style="margin-top: 20px;"><i class="fa-solid fa-chevron-down" style="font-size: 20px; cursor: pointer;"></i></h2>
					<input type="hidden" id="filter" value="<?php echo $_GET['filter']; ?>">
					<input type="hidden" id="row" value="0">
					<input type="hidden" id="all" value="<?php echo $allcount; ?>">
					<input type="hidden" id="currentNo" value="<?php echo $rowperpage; ?>">
				<?php
				}
			}
			?>
		</div>
    </div>
	<div class="content-c" style="background-color : white !important">
		<div class="container">
		<div class="row">
				<div class="text-center" style="margin-bottom: 40px;">
					<div class="title">Make your own campaign</div>
					
					<?php
					if($getUserID != "") {
					?>
						<button data-toggle="modal" data-target="#createCampaignModal" id="btnGetStarted4" class="btn-get-started">Get Started</button>
					<?php
					}
					else {
					?>
						<a href="sign-in" class="btn-get-started" style="text-decoration:none">Get Started</a>
					<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="social-media text-center">
            <span><img src="assets/images/logo.svg" alt="" /></span>
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-twitter"></i>
            <i class="fa-brands fa-linkedin-in"></i>
            <i class="fa-brands fa-instagram"></i>
          </div>
        </div>
        <div class="row">
          <div class="menu-footer">
            <a href="/">Home</a>
            <a href="/live-campaign" class="active"
              >Live Campaigns</a
            >
            <a href="/all-campaign">All Campaigns</a>
          </div>
        </div>
        <div class="row">
          <div class="text-desc">
            Lörem ipsum od ohet dilogi. Bell trabel, samuligt, ohöbel utom
            diska. Jinesade bel när feras redorade i belogi. FAR paratyp <br />
            i muvåning, och pesask vyfisat. Viktiga poddradio har un mad och
            inde.
          </div>
        </div>
        <div class="row">
          <div class="copyright">© 2022 The Goral</div>
        </div>
      </div>
    </div>
  </body>
</html>
