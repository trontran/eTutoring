<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assign Personal Tutor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Assign Personal Tutor</h2>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">Tutor assigned successfully!</div>
    <?php endif; ?>

    <form action="?url=tutor/store" method="POST">
        <div class="mb-3">
            <label class="form-label">Select Students</label>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Select</th>
                        <th>Student ID</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="student_ids[]" value="<?= $student['user_id'] ?>">
                            </td>
                            <td><?= $student['user_id'] ?></td>
                            <td><?= $student['first_name'] . " " . $student['last_name'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <label for="tutor" class="form-label">Select Tutor</label>
            <select class="form-control" name="tutor_id" id="tutor" required>
                <option value="">-- Select Tutor --</option>
                <?php foreach ($tutors as $tutor): ?>
                    <option value="<?= $tutor['user_id'] ?>"><?= $tutor['first_name'] . " " . $tutor['last_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">Assign Tutor</button>

        <!-- Nút Back to Home -->
        <a href="?url=home/index" class="btn btn-secondary w-100">
            <i class="bi bi-arrow-left"></i> Back to Home
        </a>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
