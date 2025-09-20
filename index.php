<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lesotho Parliamentary Feedback Platform</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .hero {
      background: linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5)),
                  url('https://images.unsplash.com/photo-1521791136064-7986c2920216') center/cover no-repeat;
      color: #fff;
      padding: 100px 0;
      text-align: center;
    }
    .section-heading {
      font-weight: bold;
      margin-top: 40px;
      margin-bottom: 20px;
      text-align: center;
    }
    footer {
      background: #222;
      color: #ccc;
      padding: 20px 0;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<div id="header"></div>
<script>
    // Load header.html dynamically (works when served by a web server)
    fetch('header.html')
        .then(res => res.text())
        .then(data => {
            document.getElementById('header').innerHTML = data;
    });
</script>
<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <h1 class="display-4 fw-bold">Lesotho Parliamentary Feedback Platform</h1>
    <p class="lead">Enhancing transparency, bolstering accountability, and stimulating civic engagement.</p>
    <a href="#features" class="btn btn-light btn-lg mt-3">Learn More</a>
  </div>
</section>

<!-- About Section -->
<section id="about" class="container my-5">
  <h2 class="section-heading">About the Platform</h2>
  <p class="text-center">
    This secure online portal allows citizens to send feedback, petitions, and proposals directly to Parliament Members.
    The platform enhances transparency, bolsters accountability, and stimulates continuous civic engagement.
  </p>
</section>

<!-- Features Section -->
<section id="features" class="container my-5">
  <h2 class="section-heading">Key Features</h2>
  <div class="row text-center">
    <div class="col-md-4 mb-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5 class="card-title fw-bold">Secure Submissions</h5>
          <p class="card-text">End-to-end encrypted channels for submitting, tracking, and archiving petitions, comments, and proposals.</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5 class="card-title fw-bold">Public Suggestion Board</h5>
          <p class="card-text">Crowd-sourced input open to insights and votes, driving responsive dialogue and improvement.</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5 class="card-title fw-bold">Real-time Notifications</h5>
          <p class="card-text">On-demand status notifications for each entry, keeping citizens informed at every step.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="container my-5">
  <h2 class="section-heading">Contact Us</h2>
  <p class="text-center">Have questions or feedback? Reach out below:</p>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <form>
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" class="form-control" id="name" placeholder="Your name">
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" placeholder="you@example.com">
        </div>
        <div class="mb-3">
          <label for="message" class="form-label">Message</label>
          <textarea class="form-control" id="message" rows="4" placeholder="Your message"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
      </form>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="text-center">
  <div class="container">
    <p class="mb-0">&copy; 2025 Lesotho Parliamentary Feedback Platform. All rights reserved.</p>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
