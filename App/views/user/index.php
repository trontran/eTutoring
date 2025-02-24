<?php

// Đảm bảo biến `$users` không bị null
$users = $users ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
  <div class="container mt-5">
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
              <td><?= $user['user_id'] ?></td>
              <td><?= $user['first_name'] ?></td>
              <td><?= $user['last_name'] ?></td>
              <td><?= $user['email'] ?></td>
              <td>
                <span class="badge bg-info text-dark"><?= ucfirst($user['role']) ?></span>
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

                <!-- <a href="?url=user/reset-password&id=<?= $user['user_id'] ?>" class="btn btn-secondary btn-sm">
                  <i class="bi bi-key"></i> Reset Password
                </a> -->
                

              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-warning text-center">No users found.</div>
    <?php endif; ?>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
