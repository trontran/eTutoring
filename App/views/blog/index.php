<?php
$title = "Blogs";
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
                <h2><i class="bi bi-journal-richtext"></i> Blogs</h2>
                <?php if ($userRole === 'tutor' || $userRole === 'student' || $userRole === 'staff'): ?>
                    <a href="?url=blog/create" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create New Blog
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($userRole === 'staff'): ?>
            <!-- Search form for staff -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <form action="?url=blog/index" method="GET" class="row g-3">
                        <input type="hidden" name="url" value="blog/index">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="search" placeholder="Search by title, content, or tutor name..."
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

        <!-- Blogs List -->
        <?php if (empty($blogs)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No blogs found.
                <?php if ($userRole === 'student'): ?>
                    Your tutor hasn't created any blogs for you yet.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($blogs as $blog): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-journal-text"></i> <?= htmlspecialchars($blog['title']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-secondary me-1">
                                    <i class="bi bi-person-badge"></i>
                                    <?= htmlspecialchars($blog['tutor_first_name'] . ' ' . $blog['tutor_last_name']); ?>
                                </span>
                                    <span class="badge bg-info">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('M d, Y', strtotime($blog['created_at'])); ?>
                                </span>
                                </div>

                                <p class="card-text">
                                    <?= strlen($blog['content']) > 150 ?
                                        htmlspecialchars(substr($blog['content'], 0, 150)) . '...' :
                                        htmlspecialchars($blog['content']); ?>
                                </p>

                                <div class="d-flex justify-content-between mt-3">
                                    <div>
                                    <span class="badge bg-primary me-1" title="Comments">
                                        <i class="bi bi-chat-dots"></i> <?= $blog['comment_count']; ?>
                                    </span>
                                        <?php if (isset($blog['participant_count'])): ?>
                                            <span class="badge bg-success" title="Participants">
                                            <i class="bi bi-people"></i> <?= $blog['participant_count']; ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="?url=blog/view&id=<?= $blog['blog_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View Full Blog
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>