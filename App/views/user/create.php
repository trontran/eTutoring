    <?php
    $title = "Add New User";
    ob_start();
    ?>

    <div class="container mt-4">
        <div class="card mx-auto shadow border-0" style="max-width: 600px;">
            <!-- Header Card -->
            <div class="card-header bg-primary text-white text-center">
                <h2 class="mb-0">
                    <i class="bi bi-person-plus-fill"></i> Add New User
                </h2>
            </div>

            <!-- Body Card -->
            <div class="card-body">
                <!-- Hiển thị lỗi nếu có -->
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form action="?url=user/store" method="POST" class="mt-3" onsubmit="return validatePassword()">
                    <!-- First Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">First Name</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <!-- Last Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Last Name</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                        <small class="text-muted">
                            Password must be at least 8 characters and contain at least one uppercase letter.
                        </small>
                    </div>
                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role</label>
                        <select class="form-select" name="role">
                            <option value="student">Student</option>
                            <option value="tutor">Tutor</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Create User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Password Validation -->
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