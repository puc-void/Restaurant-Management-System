<?php
$pageTitle = "Login - GourmetHub";
require_once 'includes/config.php';

$redirect = $_GET['redirect'] ?? 'index.php';
$error = '';

if (isset($_SESSION['user_id'])) {
    header("Location: " . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (
                (!empty($user['password_hash']) && password_verify($password, $user['password_hash'])) ||
                (!empty($user['password_md5']) && md5($password) === $user['password_md5']) ||
                (!empty($user['password_plain']) && $password === $user['password_plain'])
            ) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                session_regenerate_id(true);

                header("Location: " . $redirect);
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-16 flex justify-center items-center flex-1">
    <div class="card bg-base-100 shadow-2xl border border-base-200 w-full max-w-md p-6 md:p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-primary text-primary-content flex items-center justify-center text-xl mx-auto shadow-md">
                <i class="fa-solid fa-right-to-bracket"></i>
            </div>
            <h1 class="text-2xl font-bold font-heading">Welcome Back</h1>
            <p class="text-xs text-base-content/60">Login to your GourmetHub customer account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error text-white shadow-md text-xs">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php?redirect=<?= urlencode($redirect); ?>" class="space-y-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Email Address</label>
                <div class="relative">
                    <input type="email" name="email" required placeholder="john@example.com" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-envelope absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Password</label>
                <div class="relative">
                    <input type="password" name="password" required placeholder="••••••••" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block shadow-lg gap-2 mt-4">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>

        <div class="divider text-xs text-base-content/50">OR</div>

        <div class="text-center text-xs text-base-content/70">
            Don't have an account? 
            <a href="register.php" class="link link-primary font-bold">Sign Up Free</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
