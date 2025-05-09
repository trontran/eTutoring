<?php
$title = "User Management";
ob_start();

?>
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success" role="alert">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>
    <h1 class="mb-4 text-center text-primary">
        <i class="bi bi-people-fill"></i> User Management
    </h1>

    <!-- Wrapper for content by card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> User List</h5>
            <div>
                <!-- 'Add New User' button -->
                <a href="?url=user/create" class="btn btn-success me-2">
                    <i class="bi bi-person-plus-fill"></i> Add New User
                </a>
                <!-- 'Back to Home' button -->
                <a href="?url=home/index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>

        <div class="card-body">

            <?php if (!empty($users)): ?>
                <!-- Table responsive to stretch freely on small screen -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary text-center">
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="text-center">
                                <td><?= htmlspecialchars($user['user_id']) ?></td>
                                <td><?= htmlspecialchars($user['first_name']) ?></td>
                                <td><?= htmlspecialchars($user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                <span class="badge bg-info text-dark">
                                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                </span>
                                </td>
                                <td>
                                    <!-- Group of action buttons -->
                                    <div class="btn-group" role="group">
                                        <!-- View button -->
                                        <a href="?url=user/detail&id=<?= $user['user_id'] ?>"
                                           class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <!-- Edit button -->
                                        <a href="?url=user/edit&id=<?= $user['user_id'] ?>"
                                           class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <!-- Delete button -->
                                        <a href="?url=user/delete&id=<?= $user['user_id'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                        <!-- Dashboard button (only for students) -->
                                        <?php if ($user['role'] === 'student'): ?>
                                            <a href="?url=dashboard/student&id=<?= $user['user_id'] ?>"
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-speedometer2"></i> Dashboard
                                            </a>
                                        <?php endif; ?>
                                        <!-- Reallocate button (only display if user is student) -->
                                        <?php if ($isAdmin && $user['role'] === 'student'): ?>
                                            <a href="?url=user/reallocate&id=<?= $user['user_id'] ?>"
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-arrow-repeat"></i> Reallocate Tutor
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="User pagination">
                    <ul class="pagination justify-content-center mt-3">
                        <!-- Previous button -->
                        <li class="page-item <?= (isset($currentPage) && $currentPage <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="?url=user/index&page=<?= isset($currentPage) ? $currentPage - 1 : 1 ?>"
                               tabindex="-1">Previous</a>
                        </li>

                        <!-- Page number links -->
                        <?php $totalPages = isset($totalPages) ? $totalPages : 1; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= (isset($currentPage) && $currentPage == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?url=user/index&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next button -->
                        <li class="page-item <?= (isset($currentPage) && $currentPage >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="?url=user/index&page=<?= isset($currentPage) ? $currentPage + 1 : 1 ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    No users found.
                </div>
            <?php endif; ?>

        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>