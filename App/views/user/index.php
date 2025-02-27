<?php
$title = "User Management";
ob_start();
?>

    <h1 class="mb-4 text-center">User Management</h1>

    <div class="d-flex justify-content-between mb-3">
        <a href="?url=user/create" class="btn btn-success">
            <i class="bi bi-person-plus-fill"></i> Add New User
        </a>
        <a href="?url=home/index" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Home
        </a>
    </div>

    <!-- Kiểm tra nếu có dữ liệu -->
<?php if (!empty($users)): ?>
    <table class="table table-hover table-bordered">
        <thead class="table-dark">
        <tr class="text-center">
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
                    <span class="badge bg-info text-dark"><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
                </td>
                <td>
                    <a href="?url=user/detail&id=<?= $user['user_id'] ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <a href="?url=user/edit&id=<?= $user['user_id'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="?url=user/delete&id=<?= $user['user_id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this user?');">
                        <i class="bi bi-trash"></i> Delete
                    </a>

                    <?php if ($isAdmin && $user['role'] === 'student'): ?>
                        <a href="?url=user/reallocate&id=<?= $user['user_id'] ?>"
                           class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-repeat"></i> Reallocate Tutor
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Hiển thị nút phân trang -->
    <nav aria-label="User pagination">
        <ul class="pagination justify-content-center">
            <!-- Nút Previous -->
            <li class="page-item <?= (isset($currentPage) && $currentPage <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?url=user/index&page=<?= isset($currentPage) ? $currentPage - 1 : 1 ?>" tabindex="-1">Previous</a>
            </li>

            <!-- Hiển thị số trang -->
            <?php $totalPages = isset($totalPages) ? $totalPages : 1; for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= (isset($currentPage) && $currentPage == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?url=user/index&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Nút Next -->
            <li class="page-item <?= (isset($currentPage) && $currentPage >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?url=user/index&page=<?= isset($currentPage) ? $currentPage + 1 : 1 ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php else: ?>
    <div class="alert alert-warning text-center">No users found.</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>