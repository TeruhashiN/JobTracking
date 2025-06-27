<?php
session_start();

// Optional: Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    // header("Location: ../account/login.php"); // Uncomment if you want forced login
    // exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Job Application Tracker - Dashboard</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="../assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="../assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["../assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="../assets/css/demo.css" />

    <!-- Custom CSS for better styling -->
    <style>
      .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
      }
      .status-applied { background-color: #17a2b8; }
      .status-interview { background-color: #fd7e14; }
      .status-accepted { background-color: #28a745; }
      .status-rejected { background-color: #dc3545; }
      .status-onprogress { background-color: #6f42c1; }
      
      .main-panel {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }
      
      .container {
        flex: 1;
      }
      
      .footer {
        margin-top: auto;
      }
      
      .table td {
        vertical-align: middle;
      }
      
      .form-select-sm {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
      }
    </style>
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
            <a href="../function/dashboard.php" class="logo text-white fw-bold fs-4 text-decoration-none">
              Job Tracker
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-item inactive">
                <a
                  href="../function/dashboard.php"
                  class="collapsed"
                >
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Components</h4>
              </li>
              <li class="nav-item active">
                <a href="../function/jobtrack.php">
                  <i class="fas fa-file-signature"></i>
                  <p>Job Tracking</p>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="../function/dashboard.php" class="logo">
                <img
                  src="../../assets/img/kaiadmin/logo_light.svg"
                  alt="navbar brand"
                  class="navbar-brand"
                  height="20"
                />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
          
          <!-- Navbar Header -->
          <nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid">
              <nav
                class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex"
              >
                <div class="input-group">
                  <div class="input-group-prepend">
                    <button type="submit" class="btn btn-search pe-1">
                      <i class="fa fa-search search-icon"></i>
                    </button>
                  </div>
                  <input
                    type="text"
                    placeholder="Search ..."
                    class="form-control"
                  />
                </div>
              </nav>

              <?php
              // Determine profile image based on gender
              $profileImage = '../img/default.jpg'; // Default

              if (isset($_SESSION['gender'])) {
                  if ($_SESSION['gender'] === 'Male') {
                      $profileImage = '../img/male_profile.png';
                  } elseif ($_SESSION['gender'] === 'Female') {
                      $profileImage = '../img/female_profile.png';
                  }
              }
              ?>
              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                  <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" aria-haspopup="true">
                    <i class="fa fa-search"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                      <div class="input-group">
                        <input type="text" placeholder="Search ..." class="form-control" />
                      </div>
                    </form>
                  </ul>
                </li>

                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <div class="avatar-sm">
                      <img src="<?= $profileImage ?>" alt="profile" class="avatar-img rounded-circle" />
                    </div>
                    <span class="profile-username">
                      <?php if (isset($_SESSION['username'])): ?>
                        <span class="op-7">Hi,</span>
                        <span class="fw-bold"><?= htmlspecialchars($_SESSION['username']) ?></span>
                      <?php else: ?>
                        <span class="fw-bold">Guest</span>
                      <?php endif; ?>
                    </span>
                  </a>

                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <?php if (isset($_SESSION['username'])): ?>
                          <div class="user-box">
                            <div class="avatar-lg">
                              <img src="<?= $profileImage ?>" alt="profile" class="avatar-img rounded" />
                            </div>
                            <div class="u-text">
                              <h4><?= htmlspecialchars($_SESSION['username']) ?></h4>
                              <p class="text-muted"><?= htmlspecialchars($_SESSION['role']) ?></p>
                            </div>
                          </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#accountSettingsModal">
                          Account Settings
                        </a>
                        <?php
                        include '../modal/currency.php';
                        $currencySymbol = isset($_SESSION['currency']) ? htmlspecialchars($_SESSION['currency']) : '';
                        ?>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changeCurrencyModal">
                            Change Currency<?= $currencySymbol ? ' - ' . $currencySymbol : '' ?>
                        </a>
                        <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="../account/logout.php">Logout</a>
                      </li>
                        <?php else: ?>
                          <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
                          </li>
                        <?php endif; ?>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>
         
        <div class="container">
          <div class="page-inner">
            <div class="page-header">
              <h3 class="fw-bold mb-3">Job Application Tracker</h3>
              <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                  <a href="../function/dashboard.php">
                    <i class="icon-home"></i>
                  </a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                  <a href="#">Job Tracking</a>
                </li>
              </ul>
            </div>                    
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header">
                    <div class="d-flex align-items-center">
                      <h4 class="card-title">Job Applications Progress</h4>
                      <button
                        class="btn btn-primary btn-round ms-auto"
                        data-bs-toggle="modal"
                        data-bs-target="#addJobModal"
                      >
                        <i class="fa fa-plus"></i>
                        Add Job Application
                      </button>
                    </div>
                  </div>
                  <div class="card-body">
                    <!-- Add Job Application Modal -->
                    <div
                      class="modal fade"
                      id="addJobModal"
                      tabindex="-1"
                      role="dialog"
                      aria-hidden="true"
                    >
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <div class="modal-header border-0">
                            <h5 class="modal-title">
                              <span class="fw-mediumbold">Add New</span>
                              <span class="fw-light">Job Application</span>
                            </h5>
                            <button
                              type="button"
                              class="btn-close"
                              data-bs-dismiss="modal"
                              aria-label="Close"
                            ></button>
                          </div>
                          <div class="modal-body">
                            <p class="small">
                              Track your job application progress by filling out all the required information below.
                            </p>
                            <form id="addJobForm">
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group form-group-default">
                                    <label>Company Name <span class="text-danger">*</span></label>
                                    <input
                                      id="addCompany"
                                      type="text"
                                      class="form-control"
                                      placeholder="Enter company name"
                                      required
                                    />
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group form-group-default">
                                    <label>Position <span class="text-danger">*</span></label>
                                    <input
                                      id="addPosition"
                                      type="text"
                                      class="form-control"
                                      placeholder="Enter job position"
                                      required
                                    />
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group form-group-default">
                                    <label>Applied Date <span class="text-danger">*</span></label>
                                    <input
                                      id="addAppliedDate"
                                      type="date"
                                      class="form-control"
                                      required
                                    />
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group form-group-default">
                                    <label>Application Status <span class="text-danger">*</span></label>
                                    <select id="addStatus" class="form-control" required>
                                      <option value="">Select Status</option>
                                      <option value="Applied">Applied</option>
                                      <option value="Interview">Interview</option>
                                      <option value="On Progress">On Progress</option>
                                      <option value="Accepted">Accepted</option>
                                      <option value="Rejected">Rejected</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group form-group-default">
                                    <label>Interview Date</label>
                                    <input
                                      id="addInterviewDate"
                                      type="datetime-local"
                                      class="form-control"
                                    />
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group form-group-default">
                                    <label>Follow-up Date</label>
                                    <input
                                      id="addFollowupDate"
                                      type="date"
                                      class="form-control"
                                    />
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group form-group-default">
                                    <label>Salary Range</label>
                                    <input
                                      id="addSalary"
                                      type="text"
                                      class="form-control"
                                      placeholder="e.g., ₱50,000 - ₱60,000"
                                    />
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group form-group-default">
                                    <label>Job Type</label>
                                    <select id="addJobType" class="form-control">
                                      <option value="">Select Job Type</option>
                                      <option value="Full-time">Full-time</option>
                                      <option value="Part-time">Part-time</option>
                                      <option value="Contract">Contract</option>
                                      <option value="Freelance">Freelance</option>
                                      <option value="Internship">Internship</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="col-12">
                                  <div class="form-group form-group-default">
                                    <label>Notes</label>
                                    <textarea
                                      id="addNotes"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Additional notes or comments about this application"
                                    ></textarea>
                                  </div>
                                </div>
                              </div>
                            </form>
                          </div>
                          <div class="modal-footer border-0">
                            <button
                              type="button"
                              id="addJobButton"
                              class="btn btn-primary"
                            >
                              <i class="fa fa-plus"></i>
                              Add Application
                            </button>
                            <button
                              type="button"
                              class="btn btn-danger"
                              data-bs-dismiss="modal"
                            >
                              Cancel
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Job Applications Table -->
                    <div class="table-responsive">
                      <table
                        id="job-applications-table"
                        class="display table table-striped table-hover"
                      >
                        <thead>
                          <tr>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Interview Date</th>
                            <th>Follow-up</th>
                            <th>Salary Range</th>
                            <th>Job Type</th>
                            <th style="width: 12%">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- Data will be loaded dynamically via JavaScript -->
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Login Modal -->
        <div class="modal fade" id="loginModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="../account/login.php" method="post">
                <div class="modal-header">
                  <h5 class="modal-title">Login</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="text" name="username" placeholder="Username" class="form-control mb-2" required>
                  <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">Login</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Register Modal -->
        <div class="modal fade" id="registerModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="../account/register.php" method="post">
                <div class="modal-header">
                  <h5 class="modal-title">Register</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="text" name="username" placeholder="Username" class="form-control mb-2" required>
                  <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
                  <select name="gender" class="form-control mb-2">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success">Register</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Account Settings Modal -->
        <div class="modal fade" id="accountSettingsModal" tabindex="-1" aria-labelledby="accountSettingsModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="../account/update_settings.php">
              <div class="modal-header">
                <h5 class="modal-title" id="accountSettingsModalLabel">Account Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <!-- Gender select -->
                <div class="mb-3">
                  <label for="gender" class="form-label">Gender</label>
                  <select class="form-select" name="gender" required>
                    <option value="Male" <?= ($_SESSION['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($_SESSION['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="" <?= empty($_SESSION['gender']) ? 'selected' : '' ?>>Prefer not to say</option>
                  </select>
                </div>

                <!-- Current password -->
                <div class="mb-3">
                  <label for="current_password" class="form-label">Current Password (only if changing password)</label>
                  <input type="password" class="form-control" name="current_password">
                </div>

                <!-- New password -->
                <div class="mb-3">
                  <label for="new_password" class="form-label">New Password</label>
                  <input type="password" class="form-control" name="new_password">
                </div>

              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Change Currency Modal -->
        <div class="modal fade" id="changeCurrencyModal" tabindex="-1" aria-labelledby="changeCurrencyLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="../account/update_currency.php">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="changeCurrencyLabel">Change Currency</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="currencySelect" class="form-label">Select Currency</label>
                    <?php include '../modal/currency.php'; ?>
                    <select class="form-select" id="currencySelect" name="currency" required>
                    <option value="" disabled selected>Select one...</option>
                    <?php foreach ($currencies as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                Registration successful! You may now log in.
              </div>
            </div>
          </div>
        </div>

        <!-- Error Modal -->
        <div class="modal fade" id="errorModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                Username already exists. Please choose another.
              </div>
            </div>
          </div>
        </div>

        <!-- Login Success Modal -->
        <div class="modal fade" id="loginSuccessModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Welcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">Login successful. Welcome back!</div>
            </div>
          </div>
        </div>

        <!-- Login Error Modal -->
        <div class="modal fade" id="loginErrorModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Login Failed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">Invalid username or password.</div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <footer class="footer bg-light py-4">
          <div class="container-fluid">
            <div class="copyright">
              2024, made with <i class="fa fa-heart heart text-danger"></i> by
              <a href="#">Job Tracker Team</a>
            </div>
            <div class="social-icons d-flex gap-3">
              <a href="https://www.linkedin.com/in/heraldo-brylle-justin-5527802ba/" target="_blank" class="text-primary">
                <i class="fab fa-linkedin fa-lg"></i>
              </a>
              <a href="https://github.com/TeruhashiN" target="_blank" class="text-dark">
                <i class="fab fa-github fa-lg"></i>
              </a>
              <a href="https://www.facebook.com/BrylleJustin.Chi" target="_blank" class="text-primary">
                <i class="fab fa-facebook fa-lg"></i>
              </a>
            </div>
          </div>
        </footer>
        
      </div>
    </div>

    <!--   Core JS Files   -->
    <script src="../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    

    <!-- jQuery Scrollbar -->
    <script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="../assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="../assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="../assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="../assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="../assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="../assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="../assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="../assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="../assets/js/kaiadmin.min.js"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="../assets/js/setting-demo.js"></script>
    <script src="../assets/js/demo.js"></script>
    <script src="../js/jobapp.js"></script>


    <!-- Custom Job Tracker JavaScript -->
    <script>                  

      // Registration Modal Handler
      const params = new URLSearchParams(window.location.search);
      const registerStatus = params.get("register");

      if (registerStatus === "success") {
        var myModal = new bootstrap.Modal(document.getElementById('successModal'));
        myModal.show();
      } else if (registerStatus === "exists") {
        var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
        myModal.show();
      }

      // Login Status Handler
      const loginStatus = new URLSearchParams(window.location.search).get('login');
      if (loginStatus === 'success') {
        new bootstrap.Modal(document.getElementById('loginSuccessModal')).show();
      } else if (loginStatus === 'invalid') {
        new bootstrap.Modal(document.getElementById('loginErrorModal')).show();
      }
    </script>
    
  </body>
</html>