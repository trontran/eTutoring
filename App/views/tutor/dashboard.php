<?php
$title = "My Tutees";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to tutors only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'tutor') {
    header("Location: ?url=login");
    exit;
}

$tutees = $tutees ?? [];
ob_start();
?>

    <section class="mb-4">
        <h1 class="mb-4 text-center text-primary"><i class="bi bi-people-fill"></i> My Tutees</h1>

        <!-- Search & Filter -->
        <form method="GET" action="?url=tutor/dashboard" class="d-flex mb-3">
            <input type="hidden" name="url" value="tutor/dashboard">
            <input type="text" name="filter" class="form-control me-2 shadow-sm" placeholder="Search by name or email" value="<?= htmlspecialchars($filter ?? '') ?>">

            <select name="sort_by" class="form-select me-2 shadow-sm">
                <option value="assigned_at" <?= ($sortBy == 'assigned_at') ? 'selected' : '' ?>>Sort by Assigned Date</option>
                <option value="first_name" <?= ($sortBy == 'first_name') ? 'selected' : '' ?>>Sort by Name</option>
                <option value="email" <?= ($sortBy == 'email') ? 'selected' : '' ?>>Sort by Email</option>
            </select>

            <button type="submit" class="btn btn-primary shadow-sm"><i class="bi bi-funnel-fill"></i> Filter</button>
        </form>

        <?php if (!empty($tutees)): ?>
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Tutee List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                            <tr>
                                <th><a href="?url=tutor/dashboard&sort=first_name&order=<?= ($sort === 'first_name' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark">Name</a></th>
                                <th><a href="?url=tutor/dashboard&sort=email&order=<?= ($sort === 'email' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark">Email</a></th>
                                <th><a href="?url=tutor/dashboard&sort=assigned_at&order=<?= ($sort === 'assigned_at' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-dark">Assigned At</a></th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($tutees as $tutee): ?>
                                <tr class="shadow-sm">
                                    <td>
                                        <a href="?url=user/profile&id=<?= htmlspecialchars($tutee['user_id']) ?>" class="text-decoration-none text-dark fw-bold">
                                            <?= htmlspecialchars($tutee['first_name'] . " " . $tutee['last_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($tutee['email']) ?></td>
                                    <td><?= htmlspecialchars($tutee['assigned_at']) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?url=user/profile&id=<?= htmlspecialchars($tutee['user_id']) ?>" class="btn btn-info btn-sm">
                                                <i class="bi bi-person-lines-fill"></i> View
                                            </a>

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

    <!-- Message Modal -->
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
                            <button type="submit" class="btn btn-success"><i class="bi bi-send-fill"></i> Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript to fill modal fields -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.message-btn').forEach(button => {
                button.addEventListener('click', function () {
                    let studentId = this.getAttribute('data-student-id');
                    let studentName = this.getAttribute('data-student-name');

                    document.getElementById('messageStudentId').value = studentId;
                    document.getElementById('messageStudentName').value = studentName;
                });
            });
        });
    </script>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>