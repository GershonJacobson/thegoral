<?php
error_reporting(0);
session_start();

require("../config/session.php");

if($getUserRole != 0) {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="robots" content="noindex, nofollow">
	<link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title>Account Information - Dashboard</title>

    <link
      href="../assets/font/fontawesome/css/all.min.css"
      rel="stylesheet"
      type="text/css"
    />
    <link rel="stylesheet" href="../assets/css/sweetalert.css" />
    <link
      rel="stylesheet"
      href="../assets/css/bootstrap/css/bootstrap.min.css"
    />

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet" />

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sweetalert.min.js"></script>
    <script src="/assets/js/index.js"></script>
    <script src="/admin/js/account-information.js"></script>
  </head>

  <body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
      <!-- Sidebar -->
      <ul
        class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled"
        id="accordionSidebar"
      >
        <!-- Sidebar - Brand -->
        <a
          class="sidebar-brand d-flex align-items-center justify-content-center"
          href="/admin"
        >
          <div class="sidebar-brand-icon rotate-n-15"></div>
          <img
            style="width: 100px; height: 25px"
            src="../assets/images/logo-dark.png"
            alt=""
          />
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0" />

        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
          <a class="nav-link" href="/admin">
            <i class="fa-solid fa-signal"></i>
            <span>Dashboard</span></a
          >
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="account-information">
            <i class="fa-solid fa-user"></i>
            <span>Account Information</span></a
          >
        </li>
        <li class="nav-item">
          <a class="nav-link" href="delegate-access">
            <i class="fa-solid fa-users-gear"></i>
            <span>Delegate Access</span></a
          >
        </li>
        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
          <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
      </ul>
      <!-- End of Sidebar -->

      <!-- Content Wrapper -->
      <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
          <!-- Topbar -->
          <nav
            class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow"
          >
            <!-- Sidebar Toggle (Topbar) -->
            <button
              id="sidebarToggleTop"
              class="btn btn-link d-md-none rounded-circle mr-3"
            >
              <i class="fa fa-bars"></i>
            </button>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
              <!-- Nav Item - Messages -->
              
              <!-- Nav Item - User Information -->
              <li class="nav-item dropdown no-arrow">
                <a
                  class="nav-link dropdown-toggle"
                  href="#"
                  id="userDropdown"
                  role="button"
                  data-toggle="dropdown"
                  aria-haspopup="true"
				  aria-expanded="false" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                >
                  <span
                    class="mr-2 d-none d-lg-inline text-gray-600 small"
                    style="color: #fff !important"
                    ><?php echo $getFirstName." ".$getLastName; ?></span
                  >
                  <img
                    class="img-profile rounded-circle"
                    src="../assets/images/user-icon.png"
                  />
                </a>
                <!-- Dropdown - User Information -->
                 <div
                  class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                  aria-labelledby="userDropdown"
                >
                  <div class="dropdown-divider"></div>
				  <a class="dropdown-item" href="../user-dashboard">
					<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
					Back to User Dashboard
				  </a>
				  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="../functions/logout">
                    <i
                      class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"
                    ></i>
                    Logout
                  </a>
                </div>
              </li>
            </ul>
          </nav>
          <!-- End of Topbar -->

          <!-- Begin Page Content -->
          <div class="container-fluid">
            <!-- Page Heading -->
            <div
              class="d-sm-flex align-items-center justify-content-between mb-4"
            >
              <h1 class="h3 mb-0 text-gray-800">Account Information</h1>
            </div>

            <!-- Content Row -->
            <div class="row">
              <div class="col-xl-12 mb-4">
                <div class="card-info">
                  <input type="text" id="search" placeholder="Search by. Name/Phone/Email Address" style="padding: 10px;" autocomplete="off"/>
                </div>
              </div>
            </div>
            <!-- Content Row -->
            <div class="card-container">
              <div class="row">
				<div class="loading" style="display: none;">Loading</div>
				
				<div class="data-account">
				<?php
					$i = 1;
					$qAccount = mysqli_query($con, "
						SELECT DISTINCT first_name, last_name, email, phone FROM tbl_ticket WHERE purchased_by = 0 ORDER BY first_name ASC
					");
					while($dAccount = mysqli_fetch_array($qAccount)) {
					?>
						<div class="col-xl-12 mb-2">
						  <div class="card-info-edit card-info-edit<?php echo $i; ?>" style="text-align: start !important" data-first-name="<?php echo $dAccount['first_name']; ?>" data-last-name="<?php echo $dAccount['last_name']; ?>" data-email="<?php echo $dAccount['email']; ?>" data-phone="<?php echo $dAccount['phone']; ?>" data-number="<?php echo $i; ?>">
							<span class="user-icon"><img src="../assets/images/user-icon.png" alt=""/></span>
							<span class="text-info-ds" style="font-weight: 700"><?php echo $dAccount['first_name']; ?> <?php echo $dAccount['last_name']; ?></span>
						  </div>
						</div>
					<?php
					$i++;
					}
					?>
				</div>
              </div>
            </div>
          </div>
          <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
          <div class="container my-auto">
            <div class="copyright text-center my-auto">
              <span>Copyright &copy; Thegoral.com 2021</span>
            </div>
          </div>
        </footer>
        <!-- End of Footer -->
      </div>
      <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Account Infromation Modal -->
    <div
      class="modal fade bd-example-modal-lg"
      id="accountInformationModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="profileModalLabel"
      aria-hidden="true"
	  data-modal-index="1"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row">
              <div class="col-md modal-title">
                Account Information
                <button class="closeBtn" id="closeBtn">
                  <i class="fa-solid fa-circle-xmark"></i>
                </button>
              </div>
            </div>
            <!-- profile -->
            <!-- Persolnal Information -->
            <div class="row">
              <div class="col-md-12">
                <div class="title-profile">Profile</div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="box-profile" style="margin-top: 10px">
                  <div class="col-md-1">
                    <div class="profile-icon">
                      <img src="../assets/images/user-icon.png" alt="" />
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="text-profile">
                      <span class="profile-name"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Persolnal Information -->
            <div class="row">
              <div class="col-md-12" style="display: flex; justify-content: space-between;">
                <div class="title-profile">Personal Information</div>
				
				<?php
				if($getUserRole == 1 || $getUserRole == 2) {
				?>
					<div class="btn-edit-profile" style="margin: 10px;">
					  <button type="submit" id="btnEditProfile">
						<i class="fa-solid fa-pen-to-square mr-2"></i>Edit
					  </button>
					</div>
				<?php
				}
				?>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="box-profile">
                  <div class="col-md-6">
                    <div class="text-label">
                      <span>First Name</span>
                      <p class="first-name-text"></p>
                    </div>
                    <div class="text-label">
                      <span>Email</span>
                      <p class="email-text"></p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="text-label">
                      <span>Last Name</span>
                      <p class="last-name-text">Paul</p>
                    </div>
                    <div class="text-label">
                      <span>Phone</span>
                      <p class="phone-text"></p>
                    </div>
                  </div>
				  <input type="hidden" id="id-number"/>
                </div>
              </div>
            </div>
            <!-- Payment History -->
            <div class="row">
              <div class="col-md-12">
                <div class="title-profile">Payment History</div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <table class="tbl-payment-history">
					<thead>
						<tr>
							<th>Date</th>
							<th>Amount</th>
							<th>Method</th>
							<th>Action</th>
						</tr>
					</thead>
					
					<tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Edit Profile Modal -->
    <div
      class="modal fade bd-example-modal-lg"
      id="updateProfile"
      tabindex="-1"
      role="dialog"
      aria-labelledby="createCampModalLabel"
      aria-hidden="true"
	  data-modal-index="2"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row">
              <div class="col-md modal-title">
                Update Profile
                <button class="closeBtn" id="closeBtn2">
                  <i class="fa-solid fa-circle-xmark"></i>
                </button>
              </div>
            </div>
            <div class="row">
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="edtFirstName"
                    >First Name<small style="color: red">*</small></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="edtFirstName"
                    autocomplete="off"
                  />
                </div>
              </div>
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="edtLastName"
                    >Last Name<small style="color: red">*</small></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="edtLastName"
                    autocomplete="off"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="edtEmail"
                    >Email<small style="color: red">*</small></label
                  >
                  <input
                    type="email"
                    class="form-control"
                    id="edtEmail"
                    autocomplete="off"
					readonly
                  />
                </div>
              </div>
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="edtPhone">Phone</label>
                  <input
                    type="text"
                    class="form-control"
                    id="edtPhone"
                    autocomplete="off"
                    value="+11003333"
                  />
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md mt-4 mb-2 text-center">
                <button
                  type="submit"
                  class="btnCreateCamp"
                  id="btnUpdateProfile"
                  style="font-weight: bold"
                >
                  Update Profile
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
	
	<div
      class="modal fade bd-example-modal-lg"
      id="refundModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="createCampModalLabel"
      aria-hidden="true"
	  data-modal-index="2"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row">
              <div class="col-md modal-title">
                Refund
                <button class="closeBtn" id="closeBtn3">
                  <i class="fa-solid fa-circle-xmark"></i>
                </button>
              </div>
            </div>
            <div class="row">
              <div class="col-md col-50-bn">
                <div class="form-group">
                  <label for="edtAmount"
                    >Amount ($)<small style="color: red">*</small></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="edtAmount"
                    autocomplete="off"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md mt-4 mb-2 text-center">
				<input type="hidden" id="ticketID"/>
                <button
                  type="submit"
                  class="btnCreateCamp"
                  id="btnRefund"
                  style="font-weight: bold"
                >
                  Refund
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
  </body>
</html>
<?php
}
else {
	header("Location: ../sign-in");
}
?>