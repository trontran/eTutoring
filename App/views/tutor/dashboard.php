<?php
// Kiểm tra session
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Tutor Dashboard' ?></title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/eTutoring/public/Css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="?url=home/index">
                <i class="bi bi-mortarboard"></i> eTutoring
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="?url=logout">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h1 class="mb-4 text-center">My Tutees</h1>

        <?php if (!empty($tutees)): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr class="text-center">
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tutees as $tutee): ?>
                        <tr class="text-center">
                            <td><?= $tutee['user_id'] ?></td>
                            <td><?= $tutee['first_name'] ?></td>
                            <td><?= $tutee['last_name'] ?></td>
                            <td><?= $tutee['email'] ?></td>
                            <td>
                                <!-- Contact via Email -->
                                <a href="mailto:<?= $tutee['email'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-envelope"></i> Contact
                                </a>

                                <!-- Send Message -->
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#messageModal" data-student-id="<?= $tutee['user_id'] ?>" data-student-name="<?= $tutee['first_name'] . ' ' . $tutee['last_name'] ?>">
                                    <i class="bi bi-chat-dots"></i> Message
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning text-center">You have no assigned tutees.</div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <div class="container text-center my-4">
        <a href="?url=home/index" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Home
        </a>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="messageModalLabel">Send Message to Student</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <textarea class="form-control" name="message" id="messageContent" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
