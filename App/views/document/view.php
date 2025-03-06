<?php
$title = "View Document";
ob_start();

// Format file size
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Get file icon based on file type
function getFileIcon($fileType) {
    if (strpos($fileType, 'pdf') !== false) {
        return 'bi-file-earmark-pdf';
    } elseif (strpos($fileType, 'word') !== false || strpos($fileType, 'doc') !== false) {
        return 'bi-file-earmark-word';
    } elseif (strpos($fileType, 'text') !== false || strpos($fileType, 'txt') !== false) {
        return 'bi-file-earmark-text';
    } else {
        return 'bi-file-earmark';
    }
}
?>

    <div class="container py-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?url=document/list">Documents</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View Document</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <!-- Document details -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-info"></i> Document Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="bi <?= getFileIcon($document['file_type']); ?> text-primary" style="font-size: 4rem;"></i>
                            <h5 class="mt-3"><?= htmlspecialchars($document['file_name']); ?></h5>
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-person-fill"></i> Student:</span>
                                <span class="fw-bold"><?= htmlspecialchars($document['student_first_name'] . ' ' . $document['student_last_name']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-person-badge-fill"></i> Tutor:</span>
                                <span class="fw-bold"><?= htmlspecialchars($document['tutor_first_name'] . ' ' . $document['tutor_last_name']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-upload"></i> Uploaded by:</span>
                                <span class="fw-bold"><?= htmlspecialchars($document['uploader_first_name'] . ' ' . $document['uploader_last_name']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-calendar"></i> Uploaded on:</span>
                                <span class="fw-bold"><?= date('M d, Y', strtotime($document['uploaded_at'])); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-hdd"></i> Size:</span>
                                <span class="fw-bold"><?= formatFileSize($document['file_size']); ?></span>
                            </li>
                        </ul>

                        <div class="d-grid gap-2 mt-3">
                            <a href="?url=document/download&id=<?= $document['document_id']; ?>" class="btn btn-success">
                                <i class="bi bi-download"></i> Download Document
                            </a>
                            <a href="?url=document/list" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Document List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments section -->
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Discussion and Comments</h5>
                    </div>
                    <div class="card-body">
                        <!-- Form thêm comment -->
                        <div class="mb-4">
                            <form action="?url=document/comment" method="POST">
                                <input type="hidden" name="document_id" value="<?= $document['document_id']; ?>">
                                <div class="mb-3">
                                    <label for="comment_text" class="form-label">Add a Comment</label>
                                    <textarea class="form-control" id="comment_text" name="comment_text" rows="3" required
                                              placeholder="Enter your comment here..."></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-chat-right-text"></i> Post Comment
                                    </button>
                                </div>
                            </form>
                        </div>

                        <hr>

                        <!-- Hiển thị số lượng comment -->
                        <h6 class="mb-3"><?= count($comments); ?> Comments</h6>

                        <!-- QUAN TRỌNG: PHẦN HIỂN THỊ COMMENT -->
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light d-flex justify-content-between">
                                        <div>
                                            <strong><?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></strong>
                                            <span class="badge <?= $comment['role'] === 'tutor' ? 'bg-primary' : 'bg-success'; ?> ms-2">
                                    <?= ucfirst($comment['role']); ?>
                                </span>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('M d, Y g:i A', strtotime($comment['commented_at'])); ?>
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <p><?= nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No comments yet. Be the first to comment!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>