<?php
error_reporting(0);
session_start();

require("../config/session.php");

if($getUserRole == 1) {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, maximum-scale=1"
    />

    <title>Delegate Access - Dashboard</title>

    <!-- Custom fonts for this template-->
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
    <link
      href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css"
      rel="stylesheet"
      type="text/css"
    />

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet" />

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sweetalert.min.js"></script>
    <script src="/admin/js/delegate-access.js"></script>
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
          href="index.html"
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
        <li class="nav-item">
          <a class="nav-link" href="account-information">
            <i class="fa-solid fa-user"></i>
            <span>Account Information</span></a
          >
        </li>
        <li class="nav-item active">
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
              <!-- Nav Item - Messages --
              <div class="topbar-divider d-none d-sm-block"></div>

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
                  aria-haspopup="true"
                  aria-expanded="false"
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
              <h1 class="h3 mb-0 text-gray-800">Delegate Access</h1>
            </div>

            <!-- Content Row -->
            <div class="row">
              <div class="col-xl-12 mb-4">
                <div class="card-info">
                  <button
                    type="button"
                    class="btnAddUser"
                    id="btnAddUser"
                    data-toggle="modal"
                    data-target="#delegateAccessModal"
                  >
                    Add User
                  </button>
                </div>
              </div>
            </div>
            <!-- Content Row -->
            <div class="card-container mb-2">
              <div class="row">
                <div class="col-xl-12 mb-2">
                  <div style="overflow-x: auto">
                    <table class="tblDelegateAccess">
					<thead>
                      <tr>
                        <th>Email</th>
                        <th>Access</th>
                        <th>Action</th>
                      </tr>
					</thead>
					  
					<tbody>
					  <?php
						$i = 1;
						$qList = mysqli_query($con, "SELECT * FROM tbl_users WHERE admin != 0 ORDER BY delegate_date DESC");
						while($dList = mysqli_fetch_array($qList)) {
						?>
						<tr id="list_<?php echo $dList["user_id"]; ?>">
                        <td><?php echo $dList['email_address']; ?></td>
                        <td class="access<?php echo $dList["user_id"]; ?>">
							<?php
							if($dList['admin'] == 1) {
								echo "Everything the main admins have.";
								
								$role = "full-admin";
							}
							else if($dList['admin'] == 2) {
								echo "Can only edit campaigns and can’t see money.";
								
								$role = "edit-cant-see-money";
							}
							else if($dList['admin'] == 3) {
								echo "Can just view (including money) and not edit anything";
								
								$role = "just-view-cant-edit";
							}
							else {
								echo "-";
							}
							?>
						</td>
                        <td class="filter-by-container3">
                          <button
                            class="btn dropdown-toggle btn-filter"
                            type="button"
                            id="dropdownMenuClickableInside"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            style="background: #e7e7e7"
                          >
                            Action
                          </button>

                          <ul
                            class="dropdown-menu"
                            aria-labelledby="dropdownMenuClickableInside"
                            style="box-shadow: 0 5px 5px -5px #333"
                          >
                            <li>
                              <a class="edit edit<?php echo $dList['user_id']; ?>" id="<?php echo $dList['user_id']; ?>" data-access="<?php echo $role; ?>" data-email="<?php echo $dList["email_address"]; ?>">Edit</a>
                              <a class="remove" id="<?php echo $dList["user_id"]; ?>">Remove</a>
                            </li>
                          </ul>
                        </td>
                      </tr>
					  <?php
					  $i++;
						}
					  ?>
					</tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

        <!-- Delegate Access Modal -->
        <div
          class="modal fade bd-example-modal-lg"
          id="delegateAccessModal"
          tabindex="-1"
          role="dialog"
          aria-labelledby="delegateAccessModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-body">
                <div class="row">
                  <div class="col-md modal-title">
                    Delegate Access
                    <button class="closeBtn">
                      <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div
                      style="
                        border-bottom: 1.5px solid #707070;
                        margin-top: 10px;
                      "
                    ></div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div style="overflow-x: auto">
                      <table class="modalDelegateAccess">
                        <tr>
                          <td>1</td>
                          <td>Everything the main admins have.</td>
                          <td><input type="checkbox" name="delegate-access" class="delegate-access" value="full-admin" onclick="onlyOne(this)"/></td>
                        </tr>
                        <tr>
                          <td>2</td>
                          <td>Can only edit campaigns and can’t see money.</td>
                          <td><input type="checkbox"name="delegate-access" class="delegate-access" value="edit-cant-see-money" onclick="onlyOne(this)"/></td>
                        </tr>
                        <tr>
                          <td>3</td>
                          <td>
                            Can just view (including money) and not edit
                            anything
                          </td>
                          <td><input type="checkbox" name="delegate-access" class="delegate-access" value="just-view-cant-edit" onclick="onlyOne(this)"/></td>
                        </tr>
                      </table>
					  
					  <div class="delegateAccessCantBeEmpty" style="color: red; display: none;">Please choose one!</div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 email">
                        <div class="text-email">
                          Email <small style="color: red">*</small>
                        </div>
                        <input type="text" id="email-address" placeholder="someone@example.com" autocomplete="off"/>
						
						<div class="emailNotValid" style="color: red; display: none;">Email is not valid!</div>
                      </div>
                    </div>
					<div class="message" style="color: red; display: none;"></div>
                    <div class="row">
                      <div class="col-md-12 text-center">
                        <button class="btnSubmit">Submit</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
		
		 <div
          class="modal fade bd-example-modal-lg"
          id="editDelegateAccessModal"
          tabindex="-1"
          role="dialog"
          aria-labelledby="editDelegateAccessModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-body">
                <div class="row">
                  <div class="col-md modal-title">
                    Edit Delegate Access
                    <button class="closeBtn">
                      <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div
                      style="
                        border-bottom: 1.5px solid #707070;
                        margin-top: 10px;
                      "
                    ></div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div style="overflow-x: auto">
                      <table class="modalDelegateAccess">
                        <tr>
                          <td>1</td>
                          <td>Everything the main admins have.</td>
                          <td><input type="checkbox" name="edt-delegate-access" class="edt-delegate-access" value="full-admin" onclick="onlyOne1(this)"/></td>
                        </tr>
                        <tr>
                          <td>2</td>
                          <td>Can only edit campaigns and can’t see money.</td>
                          <td><input type="checkbox"name="edt-delegate-access" class="edt-delegate-access" value="edit-cant-see-money" onclick="onlyOne1(this)"/></td>
                        </tr>
                        <tr>
                          <td>3</td>
                          <td>
                            Can just view (including money) and not edit
                            anything
                          </td>
                          <td><input type="checkbox" name="edt-delegate-access" class="edt-delegate-access" value="just-view-cant-edit" onclick="onlyOne1(this)"/></td>
                        </tr>
                      </table>
					  
					  <div class="delegateAccessCantBeEmptyEdt" style="color: red; display: none;">Please choose one!</div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 email">
                        <div class="text-email">
                          Email <small style="color: red">*</small>
                        </div>
                        <input type="text" id="edt-email-address" placeholder="someone@example.com" autocomplete="off"/>
						
						<div class="emailNotValidEdt" style="color: red; display: none;">Email is not valid!</div>
                      </div>
                    </div>
					<div class="messageEdt" style="color: red; display: none;"></div>
                    <div class="row">
                      <div class="col-md-12 text-center">
						<input type="hidden" id="userID"/>
                        <button class="btnUpdateDelegate">Update</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

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
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
  </body>
</html>
<?php
}
else if($getUserRole == 2 || $getUserRole == 3) {
	header("Location: ../403");
}
else {
	header("Location: ../sign-in");
}
?>