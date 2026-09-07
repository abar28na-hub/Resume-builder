<?php
require 'config.php';

$result = $mysql->query('SELECT id, full_name, email, phone, current_job_title, created_at FROM resumes ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applications (Admin)</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 40px 16px; background: #f1f5f9; }
    .wrap { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
    .header { background: #1e293b; padding: 22px 30px; color: #fff; border-bottom: 3px solid #2563eb; }
    .header h1 { margin: 0; font-size: 20px; font-weight: 600; }
    .table-pad { padding: 20px 30px 28px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    th, td { text-align: left; padding: 10px; border-bottom: 1px solid #f1f5f9; }
    th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
    tr:hover { background: #f8fafc; }
    a.view-link {
        background: #1e293b; color: #fff; padding: 5px 14px; border-radius: 4px;
        font-size: 12px; font-weight: 600; text-decoration: none;
    }
    a.view-link:hover { background: #2563eb; }
    .empty { color: #64748b; padding: 20px 30px; }
</style>
</head>
<body>
<div class="wrap">
    <div class="header"><h1>Applications (Admin View)</h1></div>
    <div class="table-pad">
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Applying For</th><th>Submitted</th><th></th></tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['current_job_title'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><a class="view-link" href="resume.php?id=<?= (int)$row['id'] ?>">View Resume</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="empty">No applications yet.</p>
    <?php endif; ?>
    </div>
</div>
</body>
</html>
<?php $mysql->close(); ?>
