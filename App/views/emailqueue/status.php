<?php
$title = "Email Queue Status";
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

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-envelope"></i> Email Queue Status</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <p class="display-4"><?= $counts['pending'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Sent</h5>
                                <p class="display-4"><?= $counts['sent'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Failed</h5>
                                <p class="display-4"><?= $counts['failed'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($counts['pending'] > 0): ?>
                    <div class="text-center mt-4">
                        <p>There are <?= $counts['pending'] ?> pending emails in the queue.</p>
                        <a href="?url=emailqueue/process" class="btn btn-primary">
                            <i class="bi bi-envelope"></i> Send Pending Emails
                        </a>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="?url=dashboard/index" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>