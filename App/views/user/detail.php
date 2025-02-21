<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">User Details</h2>
        <div class="card shadow-lg p-4">
            <div class="card-body">
                <h4><i class="bi bi-person-circle"></i> <?= $user['first_name'] . ' ' . $user['last_name'] ?></h4>
                <p><strong>Email:</strong> <?= $user['email'] ?></p>
                <p><strong>Role:</strong> <?= ucfirst($user['role']) ?></p>
                <a href="?url=user/index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
    </div>
</body>
</html>
