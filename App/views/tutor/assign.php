<?php
$students = $data['students'] ?? [];
$tutors = $data['tutors'] ?? [];
?>

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
            <label for="student" class="form-label">Select Student</label>
            <select class="form-control" name="student_id" id="student" required>
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= $student['user_id'] ?>"><?= $student['first_name'] . " " . $student['last_name'] ?></option>
                <?php endforeach; ?>
            </select>
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

        <button type="submit" class="btn btn-primary w-100">Assign Tutor</button>
    </form>
</div>
</body>
</html>
