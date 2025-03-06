<?php
//echo "Documents count: " . count($documents ?? []);
//?>
<?php
$title = "My Documents";
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
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-file-earmark-text"></i> Documents</h2>
                <a href="?url=document/upload" class="btn btn-primary">
                    <i class="bi bi-file-earmark-plus"></i> Upload New Document
                </a>
            </div>
        </div>

        <?php if ($userRole === 'staff'): ?>
            <!-- Search form for staff -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <form action="?url=document/list" method="GET" class="row g-3">
                        <input type="hidden" name="url" value="document/list">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="search" placeholder="Search by filename, student or tutor name..."
                                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Documents List -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-files"></i> Document List</h4>
            </div>
            <div class="card-body">
                <?php if (empty($documents)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No documents found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Filename</th>
                                <?php if ($userRole === 'tutor' || $userRole === 'staff'): ?>
                                    <th>Student</th>
                                <?php endif; ?>
                                <?php if ($userRole === 'student' || $userRole === 'staff'): ?>
                                    <th>Tutor</th>
                                <?php endif; ?>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Comments</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($documents as $document): ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                        <?= htmlspecialchars($document['file_name']); ?>
                                    </td>
                                    <?php if ($userRole === 'tutor' || $userRole === 'staff'): ?>
                                        <td>
                                            <?= htmlspecialchars($document['student_first_name'] . ' ' . $document['student_last_name']); ?>
                                        </td>
                                    <?php endif; ?>
                                    <?php if ($userRole === 'student' || $userRole === 'staff'): ?>
                                        <td>
                                            <?= htmlspecialchars($document['tutor_first_name'] . ' ' . $document['tutor_last_name']); ?>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <?= htmlspecialchars($document['uploader_first_name'] . ' ' . $document['uploader_last_name']); ?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($document['uploaded_at'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= $document['comment_count']; ?>
                                            <i class="bi bi-chat-dots ms-1"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?url=document/view&id=<?= $document['document_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="?url=document/download&id=<?= $document['document_id']; ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>