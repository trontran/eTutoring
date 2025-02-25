<?php if (!isset($_SESSION['user'])) { header("Location: ?url=login"); exit; } ?>

    <!DOCTYPE html>
    <html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['first_name']) ?></h2>

    <div class="card">
        <div class="card-header">Your Tutor</div>
        <div class="card-body">
            <?php if ($tutor): ?>
                <p>Name: <strong><?= htmlspecialchars($tutor['first_name'] . " " . $tutor['last_name']) ?></strong></p>
                <a href="?url=message/chat&receiver_id=<?= $tutor['user_id'] ?>" class="btn btn-primary">Message Tutor</a>
            <?php else: ?>
                <p>No tutor assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
    </html><?php
