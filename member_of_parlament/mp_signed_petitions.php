<?php
require_once '../includes/dbcon.inc.php';
require_once '../includes/fn.inc.php';
include 'header.php';

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit;
}

$mp_id = getUserID($_SESSION['email']); // MP ID

// fetch ONLY petitions created by this MP that have at least one signature
$sql = "
SELECT 
    p.petition_id,
    p.title,
    p.status,
    COUNT(ps.signature_id) AS total_signatures,
    MAX(ps.signed_at) AS last_signed
FROM petitions p
INNER JOIN petition_signatures ps 
    ON ps.petition_id = p.petition_id   /* ensures there’s at least one signature */
WHERE p.mp_id = ?                      /* created by this MP */
GROUP BY p.petition_id, p.title, p.status
HAVING COUNT(ps.signature_id) > 0
ORDER BY total_signatures DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mp_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Signed Petitions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">📄 My Petitions (Signed by Citizens)</h2>

  <!-- Download all petitions with signatures as PDF -->
  <a href="generate_petition_pdf.php?all_my_signed_petitions=1" class="btn btn-danger mb-3">
    Download All as PDF
  </a>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>Title</th>
          <th>Status</th>
          <th>Signatures</th>
          <th>Last Signature</th>
          <th>PDF</th>
        </tr>
      </thead>
      <tbody>
        <?php if($result->num_rows>0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td><?= $row['total_signatures'] ?></td>
              <td><?= $row['last_signed'] ? date('Y-m-d', strtotime($row['last_signed'])) : '-' ?></td>
              <td>
                <a class="btn btn-sm btn-outline-primary" 
                   href="generate_petition_pdf.php?petition_id=<?= $row['petition_id'] ?>">
                  Download PDF
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center">No signed petitions found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
