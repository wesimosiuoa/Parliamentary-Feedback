<?php
require_once '../mendla/includes/dbcon.inc.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// if logged in, get user_id
$user_id = null;
if (!empty($_SESSION['email'])) {
  $user_id = getUserID($_SESSION['email']); // your own helper
}

/* --- Fetch suggestions --- */
$sugSql = "SELECT s.suggestion_id, s.content, s.date_posted,
                  u.first_name, u.last_name,
                  (SELECT COUNT(*) FROM votes v WHERE v.suggestion_id=s.suggestion_id) AS votes
           FROM suggestions s
           JOIN users u ON s.user_id = u.user_id
           ORDER BY s.date_posted DESC";
$suggestions = $conn->query($sugSql)->fetch_all(MYSQLI_ASSOC);

/* --- Fetch petitions with signature counts --- */
$petSql = "SELECT p.petition_id, p.title, p.description, p.status, p.created_at,
                  COUNT(ps.signature_id) AS signature_count
           FROM petitions p
           LEFT JOIN petition_signatures ps ON p.petition_id = ps.petition_id
           GROUP BY p.petition_id,p.title,p.description,p.status,p.created_at
           ORDER BY p.created_at DESC";
$petitions = $conn->query($petSql)->fetch_all(MYSQLI_ASSOC);

/* --- Fetch order papers --- */
$opSql = "SELECT order_paper_id,title,session_number,created_at
          FROM order_papers ORDER BY created_at DESC";
$orderPapers = $conn->query($opSql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Public Platform – Lesotho Parliament</title>
  <meta name="description" content="Browse suggestions, petitions and order papers.">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    .hero-section{
      background:linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5)),
                 url('https://images.unsplash.com/photo-1521791136064-7986c2920216') center/cover no-repeat;
      color:#fff;padding:80px 0;text-align:center
    }
    .hero-section h1{font-weight:bold;font-size:2.5rem}
    .content-section{padding:60px 0}
    .card-custom{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.05);
                 padding:20px;margin-bottom:20px}
    .card-custom h5{color:#00209F;font-weight:bold}
    .card-custom small{color:#666}
    .list-group-item{border:none;border-bottom:1px solid #eee}
    @media (max-width:767px){
      .hero-section h1{font-size:2rem}
    }
  </style>
</head>
<body>

<div id="header"></div>

<section class="hero-section">
  <div class="container">
    <h1>Public Platform</h1>
    <p class="lead">Suggestions • Petitions • Order Papers</p>
  </div>
</section>

<section class="content-section">
  <div class="container">
    <div class="row g-4">
      <!-- Suggestions -->
      <div class="col-lg-6">
        <h3 class="mb-4">Recent Suggestions</h3>
        <?php foreach($suggestions as $s): ?>
          <div class="card-custom">
            <p class="mb-1"><?=htmlspecialchars($s['content']);?></p>
            <small>Posted by <?=htmlspecialchars($s['first_name'].' '.$s['last_name']);?>
              on <?=date('j M Y',strtotime($s['date_posted']));?></small>
            <div class="mt-2">
              <button class="btn btn-sm btn-outline-primary vote-btn"
                      data-id="<?=$s['suggestion_id'];?>" <?=!$user_id?'disabled':'';?>>
                <i class="fas fa-thumbs-up"></i> Vote
              </button>
              <span class="ms-2 text-muted"><?=$s['votes'];?> votes</span>
              <?php if(!$user_id):?><small class="text-muted ms-2">Login to vote</small><?php endif;?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Petitions -->
      <div class="col-lg-6">
        <h3 class="mb-4">Petitions</h3>
        <?php foreach($petitions as $p): ?>
          <div class="card-custom">
            <h5><?=htmlspecialchars($p['title']);?></h5>
            <small>Status: <?=$p['status'];?> • Posted on <?=date('j M Y',strtotime($p['created_at']));?></small>
            <p class="mt-2 mb-2"><?=nl2br(htmlspecialchars($p['description']));?></p>
            <button class="btn btn-sm btn-outline-success sign-btn"
                    data-id="<?=$p['petition_id'];?>" <?=!$user_id?'disabled':'';?>>
              <i class="fas fa-pen"></i> Sign Petition
            </button>
            <span class="ms-2 text-muted"><?=$p['signature_count'];?> signatures</span>
            <?php if(!$user_id):?><small class="text-muted ms-2">Login to sign</small><?php endif;?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="row mt-5">
      <div class="col-12">
        <h3 class="mb-4">Order Papers</h3>
        <ul class="list-group">
          <?php foreach($orderPapers as $op): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
              <span>
                <strong><?=htmlspecialchars($op['title']);?></strong><br>
                <small class="text-muted">Order Paper <?=$op['session_number'];?> on <?=date('j M Y',strtotime($op['created_at']));?></small>
              </span>
              <a href="../mendla/includes/generate_order_paper.inc.php?order_paper=<?= (int)$op['order_paper_id']; ?>" 
                 class="btn btn-sm btn-outline-primary mt-2 mt-md-0" download>
                 <i class="fas fa-download"></i> Download
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<footer class="bg-light py-4 text-center">
  <p class="mb-0">&copy; 2025 Government of Lesotho. All rights reserved.</p>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
// vote button
document.querySelectorAll('.vote-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    fetch('vote_suggestion.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'suggestion_id='+btn.dataset.id
    }).then(res=>res.json()).then(r=>{
      if(r.success) location.reload();
    });
  });
});
// sign petition button
document.querySelectorAll('.sign-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    fetch('sign_petition.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'petition_id='+btn.dataset.id
    }).then(res=>res.json()).then(r=>{
      if(r.success) location.reload();
    });
  });
});
// Load header dynamically
fetch('header.html')
  .then(res=>res.text())
  .then(data=>document.getElementById('header').innerHTML=data);
</script>

</body>
</html>
