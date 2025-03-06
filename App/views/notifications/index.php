<?php
$title = "Notifications";
ob_start();
?>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="bi bi-bell-fill"></i> Notifications</h4>
                            <?php if (!empty($notifications)): ?>
                                <a href="?url=notifications/markAllAsRead" class="btn btn-sm btn-light">
                                    <i class="bi bi-check-all"></i> Mark All as Read
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
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

                        <?php if (empty($notifications)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> You don't have any notifications.
                            </div>
                        <?php else: ?>
                            <div class="list-group notification-list">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="list-group-item list-group-item-action <?= $notification['status'] === 'unread' ? 'bg-light' : '' ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <div class="d-flex align-items-start">
                                                <div class="notification-icon me-3">
                                                    <?php if (strpos($notification['notification_text'], 'meeting') !== false || strpos($notification['notification_text'], 'Meeting') !== false): ?>
                                                        <i class="bi bi-calendar-check fs-4 text-primary"></i>
                                                    <?php elseif (strpos($notification['notification_text'], 'message') !== false): ?>
                                                        <i class="bi bi-chat-dots fs-4 text-success"></i>
                                                    <?php elseif (strpos($notification['notification_text'], 'outcomes') !== false || strpos($notification['notification_text'], 'Outcomes') !== false): ?>
                                                        <i class="bi bi-journal-check fs-4 text-info"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-info-circle fs-4 text-secondary"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="mb-1"><?= htmlspecialchars($notification['notification_text']) ?></div>
                                                    <small class="text-muted">
                                                        <?= date('F j, Y g:i A', strtotime($notification['created_at'])) ?>
                                                    </small>
                                                </div>
                                            </div>

                                            <?php if ($notification['status'] === 'unread'): ?>
                                                <a href="?url=notifications/markAsRead&id=<?= $notification['notification_id'] ?>&return=notifications/index" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-check"></i> Mark as Read
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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