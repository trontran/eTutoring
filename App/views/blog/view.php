<?php
$title = "View Blog";
ob_start();
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
                        <li class="breadcrumb-item"><a href="?url=blog/index">Blogs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View Blog</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <!-- Blog content -->
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-journal-richtext"></i> <?= htmlspecialchars($blog['title'] ?? 'Untitled Blog'); ?></h4>

                        <?php if (($blog['tutor_id'] ?? null) == $userId || $userRole === 'staff' ||
                            (($blog['created_by_student'] ?? null) == $userId)): ?>
                            <div>
                                <a href="?url=blog/edit&id=<?= $blog['blog_id']; ?>" class="btn btn-sm btn-light">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="?url=blog/delete&id=<?= $blog['blog_id']; ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this blog? This action cannot be undone.')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">
                            <i class="bi bi-person-badge"></i>
                            By: <?= htmlspecialchars($blog['tutor_first_name'] . ' ' . $blog['tutor_last_name']); ?>
                        </span>
                            <span class="text-muted">
                            <i class="bi bi-calendar"></i>
                            <?= date('F j, Y g:i A', strtotime($blog['created_at'])); ?>
                        </span>
                        </div>

                        <div class="blog-content mb-4">
                            <?= nl2br(htmlspecialchars($blog['content'])); ?>
                        </div>

                        <hr>

                        <!-- Comments Section -->
                        <!-- Document Section - Enhanced Version -->
                        <?php if (!empty($documents)): ?>
                            <div class="card mb-4 mt-4 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Attached Documents (<?= count($documents) ?>)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                            <tr>
                                                <th>File Name</th>
                                                <th>File Type</th>
                                                <th>Size</th>
                                                <th>Uploaded By</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($documents as $document): ?>
                                                <?php
                                                // Format file size
                                                $fileSize = $document['file_size'];
                                                if ($fileSize >= 1048576) {
                                                    $formattedSize = number_format($fileSize / 1048576, 2) . ' MB';
                                                } elseif ($fileSize >= 1024) {
                                                    $formattedSize = number_format($fileSize / 1024, 2) . ' KB';
                                                } else {
                                                    $formattedSize = $fileSize . ' bytes';
                                                }

                                                // Determine file icon
                                                $iconClass = 'bi-file-earmark';
                                                if (strpos($document['file_type'], 'pdf') !== false) {
                                                    $iconClass = 'bi-file-earmark-pdf';
                                                } elseif (strpos($document['file_type'], 'word') !== false || strpos($document['file_type'], 'doc') !== false) {
                                                    $iconClass = 'bi-file-earmark-word';
                                                } elseif (strpos($document['file_type'], 'text') !== false || strpos($document['file_type'], 'txt') !== false) {
                                                    $iconClass = 'bi-file-earmark-text';
                                                }

                                                // Determine file type label
                                                $fileType = "Unknown";
                                                if (strpos($document['file_type'], 'pdf') !== false) {
                                                    $fileType = "PDF";
                                                } elseif (strpos($document['file_type'], 'word') !== false || strpos($document['file_type'], 'docx') !== false) {
                                                    $fileType = "Word Document";
                                                } elseif (strpos($document['file_type'], 'doc') !== false) {
                                                    $fileType = "Word Document (Old)";
                                                } elseif (strpos($document['file_type'], 'text') !== false || strpos($document['file_type'], 'txt') !== false) {
                                                    $fileType = "Text File";
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi <?= $iconClass ?> text-primary me-2 fs-4"></i>
                                                            <span class="fw-bold text-break" style="max-width: 250px;">
                                                            <?= htmlspecialchars($document['file_name']); ?>
                                                             </span>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                    <span class="badge bg-secondary">
                                                        <?= htmlspecialchars($fileType); ?>
                                                    </span>
                                                    </td>
                                                    <td><?= $formattedSize ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($document['uploader_first_name'] . ' ' . $document['uploader_last_name']); ?>
                                                        <small class="text-muted d-block">
                                                            <?= date('M d, Y', strtotime($document['uploaded_at'])); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="?url=document/download&id=<?= $document['document_id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-download"></i> Download
                                                            </a>
                                                            <a href="?url=document/view&id=<?= $document['document_id']; ?>" class="btn btn-sm btn-secondary">
                                                                <i class="bi bi-eye"></i> View Details
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- Comments Section -->
                        <h5 class="mb-3"><i class="bi bi-chat-dots"></i> Comments (<?= count($comments); ?>)</h5>

                        <div class="mb-4">
                            <form action="?url=blog/comment" method="POST">
                                <input type="hidden" name="blog_id" value="<?= $blog['blog_id']; ?>">
                                <div class="mb-3">
                                <textarea class="form-control" name="comment" rows="3" required
                                          placeholder="Add your comment..."></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-chat-right-text"></i> Post Comment
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <strong><?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></strong>
                                        <?php if (isset($comment['role'])): ?>
                                            <span class="badge bg-<?=
                                            $comment['role'] === 'tutor' ? 'primary' :
                                                ($comment['role'] === 'student' ? 'success' : 'secondary')
                                            ?>">
                                             <?= htmlspecialchars(ucfirst($comment['role'])); ?>
                                             </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <?= nl2br(htmlspecialchars($comment['comment'] ?? $comment['comment_text'] ?? 'No comment text')); ?>
                                        </p>
                                        <small class="text-muted">
                                            <?= date('M d, Y g:i A', strtotime($comment['created_at'] ?? 'now')); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No comments yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Blog Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Author:</span>
                                <span class="fw-bold"><?= htmlspecialchars($blog['tutor_first_name'] . ' ' . $blog['tutor_last_name']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Created:</span>
                                <span class="fw-bold"><?= date('M d, Y', strtotime($blog['created_at'])); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Comments:</span>
                                <span class="fw-bold"><?= count($comments); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <?php if (isset($participants) && !empty($participants)): ?>
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-people"></i> Student Participants</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($participants as $participant): ?>
                                    <li class="list-group-item">
                                        <i class="bi bi-person"></i>
                                        <?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>