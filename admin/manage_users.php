<?php
$pageTitle = "Manage Users";
require_once __DIR__ . '/header.php';

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    header("Location: manage_users.php?deleted=1");
    exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<div class="space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-users text-primary"></i> Manage Customer Accounts
            </h1>
            <p class="text-xs text-base-content/70">View registered customer details and manage accounts</p>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-warning shadow-md text-xs">
            <i class="fa-solid fa-trash-can"></i> User account deleted successfully.
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-xs">
                <thead>
                    <tr class="font-bold uppercase bg-base-200">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joined Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr class="hover">
                            <td class="font-bold">#<?= $user['id']; ?></td>
                            <td class="font-bold text-sm"><?= htmlspecialchars($user['name']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= htmlspecialchars($user['phone'] ?? '-'); ?></td>
                            <td class="text-base-content/70"><?= htmlspecialchars($user['address'] ?? '-'); ?></td>
                            <td class="text-base-content/70"><?= date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td class="text-right flex items-center justify-end gap-2">
                                <a href="edit_user.php?id=<?= $user['id']; ?>" class="btn btn-ghost btn-xs text-primary" title="Edit">
                                    <i class="fa-solid fa-user-pen"></i> Edit
                                </a>
                                <a href="manage_users.php?delete_id=<?= $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user account?');" class="btn btn-ghost btn-xs text-error" title="Delete">
                                    <i class="fa-solid fa-user-xmark"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($users->num_rows === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-base-content/60 py-8">
                                No registered users found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
