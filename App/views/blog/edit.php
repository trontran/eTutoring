<?php
$title = "Edit Blog";
ob_start();

// Create an array of current participant IDs for easier checking
$currentParticipantIds = array_column($participants, 'student_id');
?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-journal-text"></i> Edit Blog</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="?url=blog/update" method="POST">
                            <input type="hidden" name="blog_id" value="<?= $blog['blog_id']; ?>">

                            <div class="mb-3">
                                <label for="title" class="form-label">Blog Title</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       value="<?= htmlspecialchars($blog['title']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Blog Content</label>
                                <textarea class="form-control" id="content" name="content" rows="15" required><?= htmlspecialchars($blog['content']); ?></textarea>
                            </div>

                            <?php if (isset($tutees) && !empty($tutees)): ?>
                                <div class="mb-3">
                                    <label class="form-label">Student Participants</label>
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
                                                                   id="student_<?= $tutee['user_id']; ?>"
                                                                <?= in_array($tutee['user_id'], $currentParticipantIds) ? 'checked' : ''; ?>>
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
                            <?php endif; ?>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="?url=blog/view&id=<?= $blog['blog_id']; ?>" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Blog
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