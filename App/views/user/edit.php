<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h1><?= $title ?></h1>
    <form action="/user/update?id=<?= $user['id'] ?>" method="POST">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?= $user['username'] ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password (leave blank if unchanged)</label>
        <input type="password" name="password" class="form-control">
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select name="role" class="form-control" required>
          <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Student</option>
          <option value="tutor" <?= $user['role'] == 'tutor' ? 'selected' : '' ?>>Tutor</option>
          <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-success">Update</button>
      <a href="/user/index" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>