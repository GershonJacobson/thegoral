<?php
/**
 * Account Information Page
 * Allows admins to view and manage customer account information
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require("../config/session.php");
require("../config/Database.php");

// Verify user is logged in and has admin access
if (!isset($getUserRole) || $getUserRole == 0) {
    header("Location: ../sign-in");
    exit;
}

// Initialize database helper
$db = new Database($con);

// Get initial list of accounts (purchased_by = 0 means direct customers, not referrals)
$sql = "
    SELECT DISTINCT 
        first_name, 
        last_name, 
        email, 
        phone 
    FROM tbl_ticket 
    WHERE purchased_by = 0 
    ORDER BY first_name ASC
";

$accountsResult = $db->query($sql);
$accounts = $db->fetchAll($accountsResult);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />

    <title>Account Information - Dashboard</title>

    <!-- Stylesheets -->
    <link href="../assets/font/fontawesome/css/all.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../assets/css/sweetalert.css" />
    <link rel="stylesheet" href="../assets/css/bootstrap/css/bootstrap.min.css" />
    <link href="css/sb-admin-2.css" rel="stylesheet" />

    <!-- Scripts -->
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sweetalert.min.js"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- ==================== SIDEBAR ==================== -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/admin">
                <img style="width: 100px; height: 25px" src="../assets/images/logo-dark.png" alt="Logo" />
            </a>

            <hr class="sidebar-divider my-0" />

            <li class="nav-item">
                <a class="nav-link" href="/admin">
                    <i class="fa-solid fa-signal"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="account-information">
                    <i class="fa-solid fa-user"></i>
                    <span>Account Information</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="delegate-access">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Delegate Access</span>
                </a>
            </li>

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <!-- ==================== CONTENT WRAPPER ==================== -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TOPBAR -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                               data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small" style="color: #fff !important">
                                    <?php echo htmlspecialchars($getFirstName . " " . $getLastName); ?>
                                </span>
                                <img class="img-profile rounded-circle" src="../assets/images/user-icon.png" alt="Profile" />
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../user-dashboard">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Back to User Dashboard
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../functions/logout">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- PAGE CONTENT -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Account Information</h1>
                    </div>

                    <!-- SEARCH BOX -->
                    <div class="row mb-4">
                        <div class="col-xl-12">
                            <div class="card-info">
                                <input type="text" id="search" placeholder="Search by name, phone, or email" 
                                       style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 4px;" 
                                       autocomplete="off" />
                            </div>
                        </div>
                    </div>

                    <!-- ACCOUNTS LIST -->
                    <div class="card-container">
                        <div class="row">
                            <div class="data-account">
                                <?php if (!empty($accounts)): ?>
                                    <?php foreach ($accounts as $index => $account): ?>
                                        <div class="col-xl-12 mb-2">
                                            <div class="card-info-edit card-info-edit<?php echo $index; ?>" 
                                                 style="text-align: start !important; cursor: pointer; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                                 data-first-name="<?php echo htmlspecialchars($account['first_name']); ?>"
                                                 data-last-name="<?php echo htmlspecialchars($account['last_name']); ?>"
                                                 data-email="<?php echo htmlspecialchars($account['email']); ?>"
                                                 data-phone="<?php echo htmlspecialchars($account['phone']); ?>"
                                                 data-number="<?php echo $index; ?>">
                                                <span class="user-icon">
                                                    <img src="../assets/images/user-icon.png" alt="User Icon" />
                                                </span>
                                                <span class="text-info-ds" style="font-weight: 700;">
                                                    <?php echo htmlspecialchars($account['first_name'] . " " . $account['last_name']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div style="padding: 20px; text-align: center; color: #999;">
                                        No accounts found
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; The Goral 2024</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- ==================== MODALS ==================== -->

    <!-- Account Information Modal -->
    <div class="modal fade bd-example-modal-lg" id="accountInformationModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md modal-title">
                            Account Information
                            <button class="closeBtn" id="closeBtn" style="background: none; border: none; font-size: 24px; cursor: pointer;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Profile Section -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="title-profile">Profile</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box-profile" style="margin-top: 10px;">
                                <div class="col-md-1">
                                    <div class="profile-icon">
                                        <img src="../assets/images/user-icon.png" alt="Profile Icon" />
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

                    <!-- Personal Information Section -->
                    <div class="row mt-4">
                        <div class="col-md-12" style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="title-profile">Personal Information</div>
                            <?php if ($getUserRole == 1 || $getUserRole == 2): ?>
                                <button type="button" id="btnEditProfile" style="background: #4e73df; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                                    <i class="fa-solid fa-pen-to-square mr-2"></i>Edit
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box-profile">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="text-label">
                                            <span style="font-weight: bold;">First Name</span>
                                            <p class="first-name-text"></p>
                                        </div>
                                        <div class="text-label">
                                            <span style="font-weight: bold;">Email</span>
                                            <p class="email-text"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-label">
                                            <span style="font-weight: bold;">Last Name</span>
                                            <p class="last-name-text"></p>
                                        </div>
                                        <div class="text-label">
                                            <span style="font-weight: bold;">Phone</span>
                                            <p class="phone-text"></p>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="id-number" />
                            </div>
                        </div>
                    </div>

                    <!-- Payment History Section -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="title-profile">Payment History</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div style="overflow-x: auto;">
                                <table class="tbl-payment-history" style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #ddd;">
                                            <th style="padding: 10px; text-align: left;">Date</th>
                                            <th style="padding: 10px; text-align: left;">Amount</th>
                                            <th style="padding: 10px; text-align: left;">Method</th>
                                            <th style="padding: 10px; text-align: left;">Action</th>
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
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade bd-example-modal-lg" id="updateProfile" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md modal-title">
                            Update Profile
                            <button class="closeBtn" id="closeBtn2" style="background: none; border: none; font-size: 24px; cursor: pointer;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edtFirstName">First Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="edtFirstName" autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edtLastName">Last Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="edtLastName" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edtEmail">Email <span style="color: red;">*</span></label>
                                <input type="email" class="form-control" id="edtEmail" autocomplete="off" readonly />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edtPhone">Phone</label>
                                <input type="text" class="form-control" id="edtPhone" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary" id="btnUpdateProfile" style="font-weight: bold;">
                                Update Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div class="modal fade bd-example-modal-lg" id="refundModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md modal-title">
                            Process Refund
                            <button class="closeBtn" id="closeBtn3" style="background: none; border: none; font-size: 24px; cursor: pointer;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edtAmount">Refund Amount ($) <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="edtAmount" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center mt-4">
                            <input type="hidden" id="ticketID" />
                            <button type="submit" class="btn btn-primary" id="btnRefund" style="font-weight: bold;">
                                Process Refund
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/sb-admin-2.min.js"></script>
    <script src="/admin/js/account-information.js"></script>
</body>
</html>

<?php
} else {
    header("Location: ../sign-in");
    exit;
}
?>