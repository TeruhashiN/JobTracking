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
              <li class="nav-item active">
                <a
                  data-bs-toggle="collapse"
                  href="#dashboard"
                  class="collapsed"
                  aria-expanded="false"
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
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#base">
                  <i class="fas fa-file-signature"></i>
                  <p>Job Applications</p>
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
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changeCurrencyModal">
                          Change Currency<?= isset($_SESSION['currency']) ? ' -  ' . htmlspecialchars($_SESSION['currency']) : '' ?>
                        </a>


                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../account/logout.php">Logout</a>
                      </li>
                        <?php else: ?>
                          <li>
                            <div class="dropdown-divider"></div>
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
            <div
              class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
            >
              <div>
                <h3 class="fw-bold mb-3">Dashboard</h3>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-primary bubble-shadow-small"
                        >
                          <i class="fas fa-hourglass-half"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Pendings</p>
                          <h4 class="card-title">1,294</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-info bubble-shadow-small"
                        >
                          <i class="fas fa-user-tie"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Interviews</p>
                          <h4 class="card-title">1303</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-success bubble-shadow-small"
                        >
                          <i class="fas fa-briefcase"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Offers</p>
                          <h4 class="card-title">$ 1,345</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-secondary bubble-shadow-small"
                        >
                          <i class="fas fa-times-circle"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Rejected</p>
                          <h4 class="card-title">576</h4>
                        </div>
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
                      <select class="form-select" id="currencySelect" name="currency" required>
                        <option value="" disabled selected>Select one...</option>
                        <option value="$">$ - US Dollar</option>
                        <option value="₱">₱ - Philippine Peso</option>
                        <option value="€">€ - Euro</option>
                        <option value="¥">¥ - Japanese Yen</option>
                        <option value="£">£ - British Pound</option>
                        <option value="A$">A$ - Australian Dollar</option>
                        <option value="C$">C$ - Canadian Dollar</option>
                        <option value="CHF">CHF - Swiss Franc</option>
                        <option value="CN¥">CN¥ - Chinese Yuan</option>
                        <option value="₹">₹ - Indian Rupee</option>
                        <option value="₩">₩ - South Korean Won</option>
                        <option value="S$">S$ - Singapore Dollar</option>
                        <option value="NZ$">NZ$ - New Zealand Dollar</option>
                        <option value="R">R - South African Rand</option>
                        <option value="kr">kr - Swedish/Norwegian/Danish Krona</option>
                        <option value="₽">₽ - Russian Ruble</option>
                        <option value="₺">₺ - Turkish Lira</option>
                        <option value="R$">R$ - Brazilian Real</option>
                        <option value="MX$">MX$ - Mexican Peso</option>
                        <option value="RM">RM - Malaysian Ringgit</option>
                        <option value="Rp">Rp - Indonesian Rupiah</option>
                        <option value="฿">฿ - Thai Baht</option>
                        <option value="د.إ">د.إ - UAE Dirham</option>
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

        <!-- Footer -->
        <footer class="footer bg-light py-4">
          <div class="container d-flex justify-content-center">
            <div class="social-icons d-flex gap-4">
              <a href="https://www.linkedin.com/in/heraldo-brylle-justin-5527802ba/" target="_blank" class="text-primary">
                <i class="fab fa-linkedin fa-2x"></i>
              </a>
              <a href="https://github.com/TeruhashiN" target="_blank" class="text-dark">
                <i class="fab fa-github fa-2x"></i>
              </a>
              <a href="https://www.facebook.com/BrylleJustin.Chi" target="_blank" class="text-primary">
                <i class="fab fa-facebook fa-2x"></i>
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

    <!-- Registration Modal Handler -->
    <script>
      const params = new URLSearchParams(window.location.search);
      const registerStatus = params.get("register");

      if (registerStatus === "success") {
        var myModal = new bootstrap.Modal(document.getElementById('successModal'));
        myModal.show();
      } else if (registerStatus === "exists") {
        var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
        myModal.show();
      }
    </script>

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

    <script>
      const loginStatus = new URLSearchParams(window.location.search).get('login');
      if (loginStatus === 'success') {
        new bootstrap.Modal(document.getElementById('loginSuccessModal')).show();
      } else if (loginStatus === 'invalid') {
        new bootstrap.Modal(document.getElementById('loginErrorModal')).show();
      }
    </script>

    
  </body>
</html>
