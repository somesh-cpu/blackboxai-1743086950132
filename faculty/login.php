<?php
require_once __DIR__ . '/../includes/auth.php';

if ($auth->isLoggedIn()) {
    if ($auth->isAdmin()) {
        $auth->redirect('../admin/dashboard.php');
    } else {
        $auth->redirect('dashboard.php');
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if ($auth->login($username, $password)) {
        if ($auth->isAdmin()) {
            $auth->redirect('../admin/dashboard.php');
        } else {
            $auth->redirect('dashboard.php');
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vignan University - Faculty Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
        }
        .login-container {
            max-width: 400px;
            margin-top: 100px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 30px;
            background-color: white;
        }
        .university-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .university-logo img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container mx-auto">
            <div class="university-logo">
                <h3 class="text-primary">Vignan University</h3>
                <p class="text-muted">Faculty Portal</p>
            </div>
            <h4 class="text-center mb-4">Faculty Login</h4>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="../admin/login.php">Admin Login</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>