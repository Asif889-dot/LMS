<?php
require_once 'User.php';
require_once 'config.php';
$user = new User();

// 1. Protect the page: Ensure only logged-in Admins can access
if (!$user->isLoggedIn() || !$user->hasRole('admin')) {
    header("Location: login.php");
    exit;
}

// 2. Fetch stats for the header
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$active_staff = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'];

// 3. Fetch all users
$result = $conn->query("SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management | LMS-PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }

        body {
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        /* Navbar Styling */
        .navbar {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: -1px;
        }

        /* Stats Cards */
        .stat-card {
            border: none;
            border-radius: 1.25rem;
            transition: transform 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        /* Modern Table Card */
        .admin-card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            background: white;
        }

        .table thead th {
            background: #f1f5f9;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1.25rem 1.5rem;
            border: none;
        }

        .table tbody tr {
            transition: background 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Role Badges */
        .badge-admin {
            background: #fee2e2;
            color: #ef4444;
        }

        .badge-teacher {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .badge-student {
            background: #f0fdf4;
            color: #22c55e;
        }

        /* Action Buttons */
        .btn-action {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .btn-toggle-active {
            background: #ecfdf5;
            color: #10b981;
        }

        .btn-toggle-inactive {
            background: #fff7ed;
            color: #f59e0b;
        }

        .btn-delete {
            background: #fef2f2;
            color: #ef4444;
        }

        .btn-action:hover {
            filter: brightness(0.9);
            transform: scale(1.1);
        }

        .search-input {
            padding-left: 2.75rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-white bg-white mb-4 py-3">
        <div class="container">
            <a class="navbar-brand text-primary fs-4" href="#">LMS-PRO</a>

            <div class="ms-auto d-flex align-items-center">
                <div class="d-none d-md-block me-3 text-end">
                    <span class="small d-block fw-bold text-dark"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="text-muted" style="font-size: 0.7rem;">System Administrator</span>
                </div>

                <div class="rounded-circle bg-light p-2 me-3">
                    <i data-lucide="user" class="text-primary" style="width: 20px;"></i>
                </div>

                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold d-flex align-items-center"
                    onclick="return confirm('Log out from the administrative panel?')">
                    <i data-lucide="log-out" class="me-2" style="width: 16px;"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row align-items-end mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold mb-1">Staff Management</h2>
                <p class="text-muted mb-0">Monitor user roles and manage system access.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="admin_create_user.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm" style="background: var(--primary-gradient); border: none;">
                    <i data-lucide="user-plus" class="me-2" style="width: 20px;"></i>Create New User
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card card p-3 bg-white">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3 text-primary"><i data-lucide="users"></i></div>
                        <div>
                            <small class="text-muted d-block">Total Users</small>
                            <h4 class="fw-bold mb-0"><?php echo $total_users; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card p-3 bg-white">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-4 me-3 text-success"><i data-lucide="shield-check"></i></div>
                        <div>
                            <small class="text-muted d-block">Active Accounts</small>
                            <h4 class="fw-bold mb-0"><?php echo $active_staff; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <div class="position-relative col-md-4">
                    <i data-lucide="search" class="position-absolute translate-middle-y top-50 start-0 ms-3 text-muted" style="width: 18px;"></i>
                    <input type="text" class="form-control search-input" placeholder="Search users...">
                </div>
                <button class="btn btn-light btn-sm rounded-pill px-3 border"><i data-lucide="sliders-horizontal" class="me-1" style="width: 14px;"></i>Filter</button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">User Details</th>
                            <th>Role</th>
                            <th>Date Joined</th>
                            <th class="text-end pe-4">Status & Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3 bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['username']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($row['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $badge = 'badge-student';
                                    if ($row['role'] == 'admin') $badge = 'badge-admin';
                                    if ($row['role'] == 'teacher') $badge = 'badge-teacher';
                                    ?>
                                    <span class="badge rounded-pill <?php echo $badge; ?> px-3 py-2">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-dark small fw-medium"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="process_admin_action.php?action=toggle&id=<?php echo $row['id']; ?>"
                                        class="btn-action <?php echo ($row['status'] === 'active') ? 'btn-toggle-active' : 'btn-toggle-inactive'; ?> text-decoration-none me-2"
                                        title="<?php echo ($row['status'] === 'active') ? 'Deactivate Account' : 'Activate Account'; ?>">
                                        <i data-lucide="<?php echo ($row['status'] === 'active') ? 'shield-check' : 'shield-alert'; ?>" style="width: 18px;"></i>
                                    </a>

                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <a href="process_admin_action.php?action=delete&id=<?php echo $row['id']; ?>"
                                            class="btn-action btn-delete text-decoration-none"
                                            onclick="return confirm('Confirm deletion of this user account?')">
                                            <i data-lucide="trash-2" style="width: 18px;"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted fw-normal px-3 py-2 border">Main Admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-top bg-light bg-opacity-50 text-center">
                <small class="text-muted">LMS-PRO Administration Panel • v1.0.4</small>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>