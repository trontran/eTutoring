<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'tutor') {
    header("Location: ?url=login");
    exit;
}

$tutees = $tutees ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'My Tutees' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/eTutoring/public/Css/style.css">
     <link rel="icon" href="/eTutoring/public/images/favicon.ico" type="image/x-icon">

</head>
<body>
<div class="wrapper d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="?url=home/index">
                <i class="bi bi-mortarboard-fill"></i> eTutoring
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                     <li class="nav-item">
                            <a class="nav-link" href="?url=home/index"><i class="bi bi-house-door-fill"></i> Home</a>
                        </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user']['first_name']) ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="?url=user/profile"><i class="bi bi-person-fill"></i> Profile</a></li>
                             <li><a class="dropdown-item active" href="?url=tutor/dashboard"><i class="bi bi-people-fill"></i> My Tutees</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="?url=logout" onclick="return confirm('Are you sure you want to logout?')"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4 flex-grow-1">
        <section class="mb-4">
        <h1 class="mb-4 text-center"><i class="bi bi-people-fill"></i> My Tutees</h1>

        <?php if (!empty($tutees)): ?>
          <div class="card shadow">
             <div class="card-header bg-success text-white">
                  <h5 class="mb-0"><i class="bi bi-list-ul"></i> Tutee List</h5>
              </div>
            <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tutees as $tutee): ?>
                        <tr>
                            <td><?= htmlspecialchars($tutee['user_id']) ?></td>
                            <td>
                                 <a href="?url=user/profile&id=<?= htmlspecialchars($tutee['user_id']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($tutee['first_name'] . " " . $tutee['last_name']) ?>
                                 </a>
                            </td>
                            <td><?= htmlspecialchars($tutee['email']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Tutee Actions">
                                <a href="mailto:<?= htmlspecialchars($tutee['email']) ?>" class="btn btn-primary"><i class="bi bi-envelope-fill"></i></a>
                                 <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#messageModal" data-student-id="<?= htmlspecialchars($tutee['user_id']) ?>" data-student-name="<?= htmlspecialchars($tutee['first_name'] . ' ' . $tutee['last_name']) ?>">
                                    <i class="bi bi-chat-dots-fill"></i>
                                </button>
                                <a href="?url=user/profile&id=<?= htmlspecialchars($tutee['user_id']) ?>" class="btn  btn-info"><i class="bi bi-person-lines-fill"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
           </div>
          </div>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
               <i class="bi bi-info-circle-fill"></i> You have no assigned tutees.
            </div>
        <?php endif; ?>
    </section>

    </main>

    <div class="container text-center my-4">
        <a href="?url=home/index" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Home
        </a>
    </div>

    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="messageModalLabel"><i class="bi bi-chat-right-text-fill"></i> Send Message</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="?url=message/send" method="POST">
                        <input type="hidden" name="student_id" id="messageStudentId">
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" id="messageStudentName" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="messageContent" class="form-label">Message</label>
                            <textarea class="form-control" name="message" id="messageContent" rows="4" required placeholder="Enter your message here..."></textarea>
                        </div>
                         <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"> <i class="bi bi-send-fill"></i> Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer bg-dark text-white text-center py-3">
        <p>&copy; <?= date('Y') ?> eTutoring System </p>
    </footer>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        // Populate modal with selected student's data
        var messageModal = document.getElementById('messageModal');
        messageModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var studentId = button.getAttribute('data-student-id');
            var studentName = button.getAttribute('data-student-name');

            document.getElementById('messageStudentId').value = studentId;
            document.getElementById('messageStudentName').value = studentName;
        });
    </script>

</body>
</html>