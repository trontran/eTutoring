<?php
// Giả sử có biến session chứa thông tin người dùng
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'staff';
$username = $isLoggedIn ? $_SESSION['user']['first_name'] : 'Guest';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $title ?? 'eTutoring System' ?></title>

  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <!-- Custom Styles -->
  <style>
    /* Navbar */
    .navbar {
      background-color: #212529;
    }
    .navbar-brand {
      font-weight: bold;
    }
    .nav-link:hover {
      color: #ffc107 !important;
    }

    /* Hero Section */
    .hero-section {
      background: url('https://source.unsplash.com/1600x900/?education,students') no-repeat center center/cover;
      color: white;
      text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
      padding: 100px 0;
    }
    .hero-section h1 {
      font-weight: bold;
    }
    .hero-section p {
      font-size: 1.2rem;
    }

    /* Buttons */
    .btn-custom {
      padding: 12px 25px;
      font-size: 1rem;
      border-radius: 25px;
      transition: 0.3s;
    }
    .btn-custom:hover {
      opacity: 0.9;
    }

    /* Features */
    .feature-icon {
      font-size: 3rem;
      color: #007bff;
    }
    .feature-box {
      transition: 0.3s;
      padding: 20px;
      border-radius: 10px;
    }
    .feature-box:hover {
      transform: scale(1.05);
      background-color: #f8f9fa;
    }

    /* Footer */
    .footer {
      background-color: #212529;
      color: white;
      text-align: center;
      padding: 15px;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="?url=home/index">
        <i class="bi bi-mortarboard"></i> eTutoring
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="?url=home/index">Home</a></li>

          <?php if ($isLoggedIn): ?>
            <li class="nav-item">
                        <a class="nav-link text-danger" href="?url=logout" onclick="return confirm('Are you sure you want to logout?')">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
          <?php else: ?>
            <a class="nav-link" href="http://localhost/eTutoring/public/?url=login">Login</a>

            <!-- <li class="nav-item"><a class="nav-link" href="?url=register">Register</a></li> -->
          <?php endif; ?>

          <?php if ($isAdmin): ?>
            <li class="nav-item"><a class="nav-link btn btn-warning text-dark ms-2 btn-custom" href="?url=user/index"><i class="bi bi-people-fill"></i> Manage Users</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->

  <!-- Hero Section -->
  <section class="hero-section text-center">
    <div class="container">
      <h1>Welcome, <?= $username ?>!</h1>
      <!-- <p>Empowering students and tutors with an interactive and structured learning experience.</p> -->

      <div>
        <?php if (!$isLoggedIn): ?>
          <a href="http://localhost/eTutoring/public/?url=login" class="btn btn-primary btn-lg btn-custom">
            <i class="bi bi-box-arrow-in-right"></i> Get Started
          </a>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <!-- End Hero Section -->

  <!-- Features Section -->
  <section class="py-5">
    <div class="container">
      <div class="row text-center">
        <div class="col-md-4 feature-box">
          <i class="bi bi-person-badge-fill feature-icon"></i>
          <h3>Personal Tutor</h3>
          <p>Each student is assigned a personal tutor for guidance.</p>
        </div>
        <div class="col-md-4 feature-box">
          <i class="bi bi-chat-dots-fill feature-icon"></i>
          <h3>Seamless Communication</h3>
          <p>Interact with your tutors and schedule meetings effortlessly.</p>
        </div>
        <div class="col-md-4 feature-box">
          <i class="bi bi-journal-text feature-icon"></i>
          <h3>Resource Sharing</h3>
          <p>Upload and share learning materials easily.</p>
        </div>
      </div>
    </div>
  </section>
  <!-- End Features Section -->

  <!-- Footer -->
  <!-- <footer class="footer">
    <p>&copy; 2025 eTutoring System | University XYZ</p>
  </footer> -->
  <!-- End Footer -->

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
