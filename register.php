<?php
$pageTitle = "Register Account - GourmetHub";
require_once 'includes/config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$name || !$email || !$password) {
        $errors[] = 'Name, email, and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email is already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $password_md5 = md5($password);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_plain, password_md5, phone, address) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('ssssss', $name, $email, $password, $password_md5, $phone, $address);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now <a href='login.php' class='underline font-bold'>login to your account</a>.";
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-12 flex justify-center items-center flex-1">
    <div class="card bg-base-100 shadow-2xl border border-base-200 w-full max-w-lg p-6 md:p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-secondary text-secondary-content flex items-center justify-center text-xl mx-auto shadow-md">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="text-2xl font-bold font-heading">Create Account</h1>
            <p class="text-xs text-base-content/60">Join GourmetHub for instant online food ordering</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error text-white shadow-md text-xs">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= implode('<br>', $errors); ?></div>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success shadow-md text-xs">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= $success; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Full Name *</label>
                <div class="relative">
                    <input type="text" name="name" required placeholder="John Doe" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-user absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Email Address *</label>
                <div class="relative">
                    <input type="email" name="email" required placeholder="john@example.com" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-envelope absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Password *</label>
                <div class="relative">
                    <input type="password" name="password" required minlength="6" placeholder="Min 6 characters" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label text-xs font-bold">Phone Number</label>
                    <input type="text" name="phone" placeholder="+880 1700-000000" class="input input-bordered w-full text-sm" />
                </div>
                <div class="form-control">
                    <label class="label text-xs font-bold">Delivery Address</label>
                    <input type="text" name="address" placeholder="City, Street" class="input input-bordered w-full text-sm" />
                </div>
            </div>

            <button type="submit" class="btn btn-secondary btn-block shadow-lg gap-2 mt-4">
                <i class="fa-solid fa-user-plus"></i> Register Account
            </button>
        </form>

        <div class="divider text-xs text-base-content/50">OR</div>

        <div class="text-center text-xs text-base-content/70">
            Already have an account? 
            <a href="login.php" class="link link-primary font-bold">Login Here</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
