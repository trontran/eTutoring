<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Add New User</h2>

        <!-- Hiển thị lỗi nếu có -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); // Xóa lỗi sau khi hiển thị ?>
        <?php endif; ?>

        <form action="?url=user/store" method="POST" class="w-50 mx-auto mt-4" onsubmit="return validatePassword()">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" name="first_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" name="last_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
                <small class="text-muted">Password must be at least 8 characters long and contain at least one uppercase letter.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-control" name="role">
                    <option value="student">Student</option>
                    <option value="tutor">Tutor</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success w-100">Create User</button>
        </form>
    </div>

    <script>
        function validatePassword() {
            const password = document.getElementById("password").value;
            const regex = /^(?=.*[A-Z]).{8,}$/;
            if (!regex.test(password)) {
                alert("Password must be at least 8 characters long and contain at least one uppercase letter.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
