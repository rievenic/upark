<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>U-PARK - Admin Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --unor-blue: #002B49; --unor-gold: #FDB813; }
        body { background-color: #F4F6F9; font-family: 'Segoe UI', sans-serif; }
        .card-auth { border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border-top: 5px solid var(--unor-gold); }
        .btn-unor-gold { background-color: var(--unor-gold); color: var(--unor-blue); font-weight: 700; border: none; }
        .btn-unor-gold:hover { background-color: #d49b0e; color: #fff; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

    <div class="card card-auth bg-white p-4 p-md-5" style="max-width: 480px; width: 100%;">
        <div class="text-center mb-4">
            <i class="fa-solid fa-user-shield text-warning fa-3x mb-2"></i>
            <h3 class="fw-bold" style="color: var(--unor-blue);">Create Admin Account</h3>
            <p class="text-muted small">Authorized personnel only. Enter your registration details.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 small mb-3">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=register">
            
            <div class="mb-3">
                <label class="form-label small fw-bold">FULL NAME</label>
                <input type="text" name="fullname" class="form-control py-2" required placeholder="Full Name">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">USERNAME</label>
                <input type="text" name="username" class="form-control py-2" required placeholder="Choose a username">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">PASSWORD</label>
                <input type="password" name="password" class="form-control py-2" required placeholder="••••••••">
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold"><i class="fa-solid fa-key me-1"></i> ADMIN SECURITY KEY</label>
                <input type="password" name="secret_key" class="form-control py-2" required placeholder="Enter administrative authorization key">
            </div>

            <button type="submit" class="btn btn-unor-gold w-100 py-2 shadow-sm mb-3">
                <i class="fa-solid fa-user-plus me-1"></i> Register Account
            </button>

            <div class="text-center">
                <a href="index.php?action=login" class="text-decoration-none small text-secondary">Already registered? Log in here</a>
            </div>

        </form>
    </div>

</body>
</html>