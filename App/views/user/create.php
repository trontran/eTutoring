<?php
$title = "Add New User";
ob_start();
?>

    <h2 class="text-center">Add New User</h2>

    <!-- Hiển thị lỗi nếu có -->
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
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

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>