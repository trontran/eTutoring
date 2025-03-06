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

                        <form action="?url=blog/store" method="POST">
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

                            <?php if (isset($tutor) && $tutor): ?>
                                <div class="mb-3">
                                    <label class="form-label">Your Tutor</label>
                                    <input type="text" class="form-control"
                                           value="<?= htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?>" readonly>
                                    <input type="hidden" name="tutor_id" value="<?= $tutor['user_id']; ?>">
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    You don't have a tutor assigned yet. Please contact the administrator.
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="?url=blog/index" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary"
                                    <?= (!isset($tutor) || !$tutor) ? 'disabled' : ''; ?>>
                                    <i class="bi bi-save"></i> Create Blog
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>