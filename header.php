<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link rel="stylesheet" href="assets/css/sweetalert.css" />
<link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
<script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
<script src="assets/js/sweetalert.min.js"></script>
<?php
// Inisialisasi variabel untuk menghindari warning "Undefined variable"
$addActiveHM = '';
$addActiveLC = '';
$addActiveAC = '';
$addActiveDr = '';
$addActiveCt = '';
$color = '';

// Inisialisasi variabel sesi jika belum terdefinisi
$getUserID = isset($getUserID) ? $getUserID : '';
$getUserRole = isset($getUserRole) ? $getUserRole : '';
$getFirstName = isset($getFirstName) ? $getFirstName : '';
$getLastName = isset($getLastName) ? $getLastName : '';
$getEmailAddress = isset($getEmailAddress) ? $getEmailAddress : '';
$getPhone = isset($getPhone) ? $getPhone : '';

$link = $_SERVER['PHP_SELF'];
$link_array = explode('/',$link);
$page = basename($link,".php");

if($page == "user-dashboard") {
	$collapse = "collapsed";
	$activeD = "active";
}
else if($page == "wallet") {
	$collapse = "collapsed";
	$activeW = "active";
}
else {
	$collapse = "collapse";
	$activeD = "";
	$activeW = "";
}
?>

<script>
	$(document).ready(function () {
	$('#startDate').change(function() {
    var currentDate = "<?php echo date('Y-m-d'); ?>";
    var startDate = $("#startDate").val();
	$("#endDate").val("");
    if(startDate < currentDate) {
      $("#startDate").val("");
    }
  	});

  

  $('#endDate').change(function() {
	 var currentDate = "<?php echo date('Y-m-d'); ?>";
      var startDate = $("#startDate").val();
      var endDate = $("#endDate").val();

      if(endDate < startDate) {
        $("#endDate").val("");
      }

	  if(endDate < currentDate){
		$("#endDate").val("");
	  }
 	 });
	})
	
</script>


<div class="collapse navbar-collapse justify-content-center" id="navbarNav" style="width: 100%;">
	<ul class="navbar-nav">
		<li class="nav-item">
			<?php
			if($page == "index") {
				$addActiveHM = "active";
			}
			?>
			
			<a class="nav-link <?php echo $addActiveHM; ?>" href="/" <?php echo $color; ?>>Home</a>
		</li>
		
		<li class="nav-item">
			<?php
			if($page == "live-campaign") {
				$addActiveLC = "active";
			}
			?>
			<a class="nav-link <?php echo $addActiveLC; ?>" href="live-campaign" <?php echo $color; ?>>Live Campaigns</a>
		</li>
		
		<li class="nav-item">
			<?php
			if($page == "all-campaign") {
				$addActiveAC = "active";
			}
			?>
			<a class="nav-link <?php echo $addActiveAC; ?>" href="all-campaign" <?php echo $color; ?>>All Campaigns</a>
		</li>
		
		<li class="nav-item">
			<?php
			if($page == "drawing") {
				$addActiveDr = "active";
			}
			?>
			<a class="nav-link <?php echo $addActiveDr; ?>" href="drawing" <?php echo $color; ?>>Drawing Page</a>
		</li>
		
		<li class="nav-item">
			<?php
			if($page == "contact") {
				$addActiveCt = "active";
			}
			?>
			<a class="nav-link <?php echo $addActiveCt; ?>" href="contact" <?php echo $color; ?>>Contact Us</a>
		</li>
		
		<?php
		if($getUserID != "") {
		?>
			<li class="nav-item logout">
				<a class="nav-link collapsed"
              data-bs-toggle="collapse"
              data-bs-target="#collapseProfile"
              aria-controls="collapseProfile"
              aria-expanded="false"
              aria-label="Toggle navigation" <?php echo $color; ?>>My Profile</a>
			
				<div class="<?php echo $collapse; ?>" id="collapseProfile">
					<ul>
						<li><a class="nav-link <?php echo $activeD; ?>" href="user-dashboard" >Dashboard</a></li>
						<li><a class="nav-link <?php echo $activeW; ?>" href="wallet" <?php echo $color; ?>>Wallet</a></li>
						<li><a class="nav-link" data-toggle="modal" data-target="#createCampaignModal" id="btnGetStarted" <?php echo $color; ?>>Create Campaign</a></li>
					</ul>
				</div>
			</li>
		<?php
		}
		
		if($getUserRole != 0) {
		?>
			<li class="nav-item">
				<a class="nav-link" href="admin" <?php echo $color; ?>>Admin Page</a>
			</li>
		<?php
		}
		
		if($getUserID != "") {
		?>
			<li class="nav-item logout">
				<a class="nav-link logout-btn" href="functions/logout" style="color: #ffffff;">Logout</a>
			</li>
		<?php
		}
		
		if($getUserID != "") {
		}
		else {
		?>
			<li style="padding-left: 15px; padding-right: 15px;">
				<div class="box-hide">
					<a class="nav-link box-hide menu-hide" href="sign-up">Sign Up</a>
				</div>
			</li>
			<li style="padding-left: 15px; padding-right: 15px;">
				<div class="box-hide-x">
					<a class="nav-link menu-hide" href="sign-in">Login</a>
				</div>
			</li>
		<?php
		}
		?>
	</ul>
</div>

<div class="collapse navbar-collapse justify-content-end">
	<ul class="navbar-nav">
		<li>
			<div class="dropdown">
				<a aria-expanded="false" data-bs-toggle="dropdown" href="#" role="button"><img alt="" src="assets/images/user-icon.png"></a>
				<ul class="dropdown-menu dropdown-menu-end">
					<?php
					if($getUserID != "") {
					?>
						<li style="list-style: none; display: inline;">
							<div class="acc" style="padding-left: 10px; padding-right: 10px; border-bottom: 1px solid #e7e7e7; padding-bottom: 10px;">
								<img alt="" src="assets/images/user-icon.png"> <span><?php echo $getFirstName; ?></span>
							</div>
						</li>
						<li>
							<a class="dropdown-item" href="user-dashboard">My Profile</a>
						</li>
						<li><a style="cursor: pointer;" class="dropdown-item" data-toggle="modal" data-target="#createCampaignModal" id="btnGetStarted6">Create Campaign</a></li>
						<li>
							<a class="dropdown-item" href="wallet">Wallet</a>
						</li><?php
					}
					else {
					?>
						<li style="list-style: none; display: inline">
							<div class="acc" style="padding-left: 10px; padding-right: 10px; padding-bottom: 10px;">
								<img alt="" src="assets/images/user-icon.png"> <span>Account</span>
							</div><a class="btn-login" href="sign-in" style="display: block;">Login</a>
						</li>
						<li>
							<a class="dropdown-item" href="sign-up">Sign Up</a>
						</li>
					<?php
					}

					if($getUserID != "") {
					?>
					<li>
						<a class="dropdown-item" href="functions/logout">Logout</a>
					</li>
					<?php
					}
					?>
				</ul>
			</div>
		</li>
	</ul>
</div>

<script>
$(document).ready(function () {
	$('#pageUrl').on('keypress', function (event) {
		var regex = new RegExp("^[a-z0-9_-]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
		   event.preventDefault();
		   return false;
		}
	});
	
	$("#pageUrl").bind("keyup change", function() {
		var pageUrl = $(this).val();
		
		if(pageUrl != "") {
			$(".url-copy").show();
			$(".url-copy label").text(pageUrl);
		}
		else {
			$(".url-copy").hide();
			$(".url-copy label").text("");
		}
	});
	
	$(document).on('paste', '#pageUrl', function(e) {
		e.preventDefault();
		var PlainText = e.originalEvent.clipboardData.getData('Text');
		PlainText = PlainText.replace(/\s+/g, '').toLowerCase();
		$(this).val(PlainText);
	});
	
	$("#btnCreateCampaign").click(function () {
		var firstName = $("#firstName").val();
		var lastName = $("#lastName").val();
		var email = $("#email").val();
		var phone = $("#phone").val();
		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();
		var startTime = $("#startTime").val();
		var endTime = $("#endTime").val();
		var campaignName = $("#campaignName").val();
		var pageUrl = $("#pageUrl").val();
		var publicOrPrivate = $("#public").is(":checked") ? "true" : "false";

		if(firstName == "" || lastName == "" || email == "" || startDate == "" || endDate == "" || startTime == "" || endTime == "" || campaignName == "" || pageUrl == "" || publicOrPrivate == "") {
			if(firstName == "") {
				$("#firstName").focus();
			}
			else if(lastName == "") {
				$("#lastName").focus();
			}
			else if(email == "") {
				$("#email").focus();
			}
			else if(startDate == "") {
				$("#startDate").focus();
			}
			else if(endDate == "") {
				$("#endDate").focus();
			}
			else if(startTime == "") {
				$("#startTime").focus();
			}
			else if(endTime == "") {
				$("#endTime").focus();
			}
			else if(campaignName == "") {
				$("#campaignName").focus();
			}
			else if(pageUrl == "") {
				$("#pageUrl").focus();
			}
			else if(publicOrPrivate == "") {
				$("#public").focus();
			}
		}
		else {
			if(pageUrl == "drawing" || pageUrl == "admin" || pageUrl == "live-campaign" || pageUrl == "all-campaign" || pageUrl == "contact" || pageUrl == "sign-in" || pageUrl == "sign-up" || pageUrl == "forgot-password") {
				$("#pageUrl").focus();
				$(".url-not-allowed").show();
			}
			else {
				$("#btnCreateCampaign").text("Creating campaign").prop('disabled', true);
				
				$.ajax({
					url: "functions/save-campaign",
					type: "POST",
					data: {
						firstName: firstName,
						lastName: lastName,
						email: email,
						phone: phone,
						startDate: startDate,
						endDate: endDate,
						campaignName: campaignName,
						pageUrl: pageUrl,
						publicOrPrivate: publicOrPrivate
					},
					dataType: "JSON",
					success: function (jsonStr) {
						if(jsonStr.result == "OK") {
							$("#startDate, #endDate, #startTime, #endTime, #campaignName, #pageUrl").val("");
							
							$(".url-copy").hide();
							$(".url-copy label").text("");
							
							var shareURL = "https://thegoral.com/" + pageUrl;
							
							Swal.fire({
								icon: 'success',
								html: '<div class="row"><div class="col-md"><div class="text-cmc">Baltimore Community</div></div></div><div class="row"><div class="col-md"><div class="form-group share-url"><input type="text" class="form-control" id="shareUrl" autocomplete="off" value="' + shareURL + '" readonly/><div class="copy">Copy</div></div></div></div><div class="row"><div class="col-md"><div class="text-cmc">Share This</div></div></div><div class="row"><div class="col-md"><div class="sosmed"><img src="../assets/images/fb.png" alt="" /><img src="../assets/images/ig.png" alt="" /><img src="../assets/images/wa.png" alt="" /><img src="../assets/images/twitter.png" alt="" /><img src="../assets/images/linkedin.png" alt="" /><img src="../assets/images/telegram.png" alt="" /></div></div></div>',
								showConfirmButton: true,
								confirmButtonText: 'Go to My Campaign',
								allowOutsideClick: false,
								showCloseButton: true,
								confirmButtonColor: '#F6AE4E'
							}).then((result) => {
								if (result.isConfirmed) {
									window.location.href = "/" + pageUrl;
								} 
							});
							
							$(".copy").click(function () {
								var $this = $(this);
								var originalText = $this.text();
								$this.text('Copied');
								
								var copyText = document.getElementById("shareUrl");
								navigator.clipboard.writeText(copyText.value);
								
								setTimeout(function() {
									$this.text(originalText)
								}, 2000);
							});
						}
						else if(jsonStr.result == "pageURLExisted") {
							Swal.fire({
								text: "The page URL already existed!",
								icon: "error",
								confirmButtonText: "OK",
							});
						}
						
						$("#btnCreateCampaign").text("Create My Campaign").prop('disabled', false);
					}
				});
			}
		}
	});
});
</script>

 <div
      class="modal fade bd-example-modal-lg"
      id="createCampModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="createCampModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row">
              <div class="col-md modal-title">
                Create My Campaign <button class='closeBtn'><i class="fa-solid fa-circle-xmark"></i></button>
              </div>
            </div>
            <div class="row">
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="firstName"
                    >First Name<small style="color: red">*</small></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="firstName"
					value="<?php echo $getFirstName; ?>"
                    autocomplete="off"
                  />
                </div>
              </div>
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="lastName"
                    >Last Name<small style="color: red">*</small></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="lastName"
					value="<?php echo $getLastName; ?>"
                    autocomplete="off"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="email"
                    >Email<small style="color: red">*</small></label
                  >
                  <input
                    type="email"
                    class="form-control"
                    id="email"
					value="<?php echo $getEmailAddress; ?>"
                    autocomplete="off"
                  />
                </div>
              </div>
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="phone"
                    >Phone</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="phone"
					value="<?php echo $getPhone; ?>"
                    autocomplete="off"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md">
                <div class="form-group">
                  <label for="startDate"
                    >Start Date & Time<small style="color: red">*</small></label
                  >
                  <input
				  	type="datetime-local"
                    class="form-control"
                    id="startDate"
                    autocomplete="off"
					min="<?php echo date('Y-m-d'); ?>"
                  />
                </div>
              </div>
              <div class="col-md">
                <div class="form-group">
                  <label for="endDate"
                    >End Date & Time<small style="color: red">*</small></label
                  >
                  <input
				  type="datetime-local"
                    class="form-control"
                    id="endDate"
                    autocomplete="off"
					min="<?php echo date('Y-m-d'); ?>"
                  />
                </div>
              </div>
            </div>
			            <div class="row">
              <div class="col-md">
                <div class="form-group">
                  <label for="campaignName"
                    >Campaign Name<small style="color: red">*</small></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="campaignName"
                    autocomplete="off"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md">
                <div class="form-group">
                  <label for="pageUrl"
                    >Page URL<small style="color: red">*</small></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="pageUrl"
                    autocomplete="off"
					maxlength="20"
                  />
				  
					<div class="url-copy">
					https://thegoral.com/<label></label>
					</div>
					
					<div class="url-not-allowed" style="color: red; display: none;">
						URL is already created!
					</div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md">
                <div class="text-mmc">Make my campaign</div>
              </div>
            </div>
            <div class="row">
              <div class="col-md">
                <div class="form-check form-switch">
				<label class="switch">
						<input type="checkbox" id="public" checked>
						<span class="slider"></span>
						<span class="text on">Public</span>
						<span class="text off">Private</span>
					</label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md mt-4 mb-2 text-center">
                <button type="submit" class="btnCreateCamp" id="btnCreateCampaign" style="font-weight: bold;">
                  Create My Campaign
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>