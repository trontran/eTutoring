<?php
$title = "Create New Blog";
ob_start();
?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-journal-plus"></i> Create New Blog</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="?url=blog/store" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Blog Title</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       placeholder="Enter blog title...">
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Blog Content</label>
                                <textarea class="form-control" id="content" name="content" rows="15" required
                                          placeholder="Write your blog content here..."></textarea>
                            </div>

                            <!-- Document Upload Section -->
                            <div class="mb-3">
                                <label for="document" class="form-label">Attach Documents (Optional)</label>
                                <input type="file" class="form-control" id="document" name="document[]" multiple>
                                <div class="form-text">
                                    Allowed file types: PDF, DOC, DOCX, and TXT. Maximum size per file: 10 MB.
                                    You can select multiple files to upload at once.
                                </div>
                            </div>

                            <?php if ($_SESSION['user']['role'] === 'tutor' && isset($tutees) && !empty($tutees)): ?>
                                <div class="mb-3">
                                    <label class="form-label">Select Students</label>
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="small text-muted mb-2">
                                                Select the students who should have access to this blog:
                                            </p>
                                            <div class="row">
                                                <?php foreach ($tutees as $tutee): ?>
                                                    <div class="col-md-6">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="student_ids[]" value="<?= $tutee['user_id']; ?>"
                                                                   id="student_<?= $tutee['user_id']; ?>">
                                                            <label class="form-check-label" for="student_<?= $tutee['user_id']; ?>">
                                                                <?= htmlspecialchars($tutee['first_name'] . ' ' . $tutee['last_name']); ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllStudents()">
                                                    Select All
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllStudents()">
                                                    Deselect All
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($_SESSION['user']['role'] === 'tutor' && (!isset($tutees) || empty($tutees))): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    You don't have any tutees assigned to you yet. Please contact the administrator.
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="?url=blog/index" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary"
                                    <?= ($_SESSION['user']['role'] === 'tutor' && (!isset($tutees) || empty($tutees))) ? 'disabled' : ''; ?>>
                                    <i class="bi bi-save"></i> Create Blog
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectAllStudents() {
            const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        function deselectAllStudents() {
            const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    </script>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>