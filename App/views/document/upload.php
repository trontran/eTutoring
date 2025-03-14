<?php
$title = "Upload Document";
ob_start();
?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-file-earmark-arrow-up"></i> Upload Document</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="?url=document/store" method="POST" enctype="multipart/form-data">
                            <?php if ($_SESSION['user']['role'] === 'student'): ?>
                                <?php if (isset($tutor) && $tutor): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Your Tutor</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?>" readonly>
                                        <input type="hidden" name="tutor_id" value="<?= $tutor['user_id']; ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        You don't have a tutor assigned yet. Please contact the administrator.
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($_SESSION['user']['role'] === 'tutor'): ?>
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student</label>
                                    <select class="form-select" id="student_id" name="student_id" required>
                                        <option value="" selected disabled>Select a student</option>
                                        <?php if (isset($tutees) && is_array($tutees)): foreach ($tutees as $tutee): ?>
                                            <option value="<?= $tutee['user_id']; ?>">
                                                <?= htmlspecialchars($tutee['first_name'] . ' ' . $tutee['last_name']); ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="document" class="form-label">Select Documents</label>
                                <input type="file" class="form-control" id="document" name="document[]" multiple required>
                                <div class="form-text">
                                    Allowed file types: PDF, DOC, DOCX, and TXT. Maximum size per file: 10 MB.
                                    You can select multiple files to upload at once.
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                The document will be shared with your <?= $_SESSION['user']['role'] === 'student' ? 'tutor' : 'student' ?>.
                                Both of you will be able to comment on the document.
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="?url=document/list" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" <?= ($_SESSION['user']['role'] === 'student' && (!isset($tutor) || !$tutor)) ? 'disabled' : ''; ?>>
                                    <i class="bi bi-upload"></i> Upload Document
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