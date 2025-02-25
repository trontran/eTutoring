<?php
$title = "Login | eTutoring";
ob_start();
?>

    <main class="form-signin w-100 m-auto">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-lg">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem;"></i>
                                <h1 class="h3 mb-3 fw-normal">eTutoring Login</h1>
                            </div>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form action="?url=login/process" method="POST">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                    <label for="email">Email address</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                    <label for="password">Password</label>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>

                                <button class="btn btn-primary w-100 py-2" type="submit">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </button>

                                <div class="mt-3 text-center">
                                    <a href="?url=forgot-password" class="text-decoration-none">Forgot Password?</a>
                                </div>

                                <div class="mt-3 text-center">
                                    <small class="text-muted">Don't have an account? Please contact your University.</small>
                                </div>
                            </form>
                            <p class="mt-5 mb-3 text-muted text-center">&copy; <?= date('Y') ?> eTutoring</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>