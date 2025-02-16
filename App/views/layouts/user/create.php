<?php include VIEWS_PATH . 'layouts/default.php'; ?>

<?php block('content') ?>
<div class="container">
    <h2>Create New User</h2>
    <form action="<?php echo APP_URL ?>users/store" method="post"> <div class="form-group">
            <label for="mis_id">MIS ID</label>
            <input type="text" class="form-control" id="mis_id" name="mis_id" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name">
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name">
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role" required>
                <option value="student">Student</option>
                <option value="tutor">Tutor</option>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="<?php echo APP_URL ?>users" class="btn btn-secondary">Cancel</a> </form>
</div>
<?php endblock() ?>