<?php
$title = "My Tutees";

// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bảo vệ trang, chỉ tutor mới có quyền truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'tutor') {
    header("Location: ?url=login");
    exit;
}

$tutees = $tutees ?? [];
ob_start();
?>

    <section class="mb-4">
        <h1 class="mb-4 text-center"><i class="bi bi-people-fill"></i> My Tutees</h1>

        <form method="GET" action="?url=tutor/dashboard" class="d-flex mb-3">
            <input type="hidden" name="url" value="tutor/dashboard">
            <input type="text" name="filter" class="form-control me-2" placeholder="Search by name or email" value="<?= htmlspecialchars($filter ?? '') ?>">

            <!-- Dropdown chọn kiểu sắp xếp -->
            <select name="sort_by" class="form-select me-2">
                <option value="assigned_at" <?= ($sortBy == 'assigned_at') ? 'selected' : '' ?>>Sort by Assigned Date</option>
                <option value="first_name" <?= ($sortBy == 'first_name') ? 'selected' : '' ?>>Sort by Name</option>
                <option value="email" <?= ($sortBy == 'email') ? 'selected' : '' ?>>Sort by Email</option>
            </select>

            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter</button>
        </form>

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
                                <th><a href="?url=tutor/dashboard&sort=first_name&order=<?= ($sort === 'first_name' && $order === 'ASC') ? 'DESC' : 'ASC' ?>">Name</a></th>
                                <th><a href="?url=tutor/dashboard&sort=email&order=<?= ($sort === 'email' && $order === 'ASC') ? 'DESC' : 'ASC' ?>">Email</a></th>
                                <th><a href="?url=tutor/dashboard&sort=assigned_at&order=<?= ($sort === 'assigned_at' && $order === 'ASC') ? 'DESC' : 'ASC' ?>">Assigned At</a></th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($tutees as $tutee): ?>
                                <tr>
                                    <td>
                                        <a href="?url=user/profile&id=<?= htmlspecialchars($tutee['user_id']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($tutee['first_name'] . " " . $tutee['last_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($tutee['email']) ?></td>
                                    <td><?= htmlspecialchars($tutee['assigned_at']) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
<!--                                            <a href="mailto:--><?php //= htmlspecialchars($tutee['email']) ?><!--" class="btn btn-primary"><i class="bi bi-envelope-fill"></i></a>-->
<!--                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#messageModal"-->
<!--                                                    data-student-id="--><?php //= htmlspecialchars($tutee['user_id']) ?><!--"-->
<!--                                                    data-student-name="--><?php //= htmlspecialchars($tutee['first_name'] . ' ' . $tutee['last_name']) ?><!--">-->
<!--                                                <i class="bi bi-chat-dots-fill"></i>-->
<!--                                            </button>-->
                                            <a href="?url=user/profile&id=<?= htmlspecialchars($tutee['user_id']) ?>" class="btn btn-info">
                                                <i class="bi bi-person-lines-fill"></i>
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

    <!-- Modal gửi tin nhắn -->
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

    <!-- JavaScript để điền dữ liệu vào modal -->
    <script>
        var messageModal = document.getElementById('messageModal');
        messageModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var studentId = button.getAttribute('data-student-id');
            var studentName = button.getAttribute('data-student-name');

            document.getElementById('messageStudentId').value = studentId;
            document.getElementById('messageStudentName').value = studentName;
        });
    </script>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>