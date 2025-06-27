<?php
session_start();
require __DIR__.'/config.php';
check_suspension($pdo);

try {
    $stmt = $pdo->query("SELECT content FROM page_content WHERE id = 1");
    $raw  = $stmt->fetchColumn();
    $data = json_decode($raw, true);
    if (!is_array($data)) throw new Exception("Bad JSON");
} catch (Exception $e) {
    die("Error loading content: ".$e->getMessage());
}

function e($v){ return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($data['hero']['title'] ?? 'Slump') ?> – Index</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icons/7.0.3/css/flag-icons.min.css" rel="stylesheet"/>

  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <div class="bg-grid"></div>
    <div class="noise-overlay"></div>
    <div class="data-stream"></div>
    <div class="floating-dots"></div>

    <header class="page-transition">
      <div class="logo">Slump<span>.</span></div>
      <nav>
        <ul class="stagger-on-load">
          <li><a href="#home">Home</a></li>
          <li><a href="#skills">Skills</a></li>
          <li><a href="#projects">Projects</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </nav>
        <div class="user-nav page-transition">
          <?php if (!empty($_SESSION['user_id'])): ?>
            <span class="user-welcome">Hello, <?= e($_SESSION['username']) ?></span>
            <a href="admin.php"   class="btn-nav">Admin Panel</a>
            <a href="logout.php" class="btn-nav">Log Out</a>
          <?php else: ?>
            <a href="login.php"  class="btn-nav">Log In</a>
          <?php endif; ?>
        </div>
    </header>

    <main role="main">
      <section id="home" class="hero page-transition">
        <div class="hero-text">
          <h1 style="transition-delay:0.2s;"><?= e($data['hero']['title'] ?? '') ?></h1>
          <?php if (!empty($data['hero']['subtitle'])): ?>
            <p style="transition-delay:0.3s;"><?= e($data['hero']['subtitle']) ?></p>
          <?php endif; ?>
          <p style="transition-delay:0.4s;">
            <?= e($data['hero']['location']) ?>
            <span class="country-flag fi fi-us" aria-label="US flag"></span>
            • Age: <?= e($data['hero']['age']) ?>
          </p>
          <a href="#projects" class="cta-button page-transition" style="transition-delay:0.5s;">View My Projects</a>

          <?php if (!empty($data['terminal']) && is_array($data['terminal'])): ?>
            <div class="terminal page-transition" style="transition-delay:0.6s;">
              <div class="terminal-header">
                <div class="terminal-button terminal-red"></div>
                <div class="terminal-button terminal-yellow"></div>
                <div class="terminal-button terminal-green"></div>
                <div class="terminal-title">admin@slump.dev ~ </div>
              </div>
              <div class="terminal-body">
                <?php foreach($data['terminal'] as $cmd): ?>
                  <div class="command-line">
                    <span class="command-prompt">$</span>
                    <span class="command-text"><?= e($cmd['prompt']) ?></span>
                  </div>
                  <?php foreach($cmd['output'] as $out): ?>
                    <div class="command-output"><?= e($out) ?></div>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section id="skills" class="section page-transition">
        <h2>My Skills</h2>
        <br>
        <div class="skills-container stagger-on-load">
          <?php foreach($data['skills'] as $sk): ?>
            <div class="skill-card">
              <h3><?= e($sk['title']) ?></h3>
              <p><?= e($sk['desc']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="projects" class="section page-transition">
        <h2>Featured Projects</h2>
        <div class="projects-grid stagger-on-load">
          <?php foreach($data['projects'] as $pj): ?>
            <div class="project-card">
              <h3 class="project-title"><?= e($pj['title']) ?></h3>
              <?php if (!empty($pj['subtitle'])): ?>
                <h5 class="project-title2"><?= e($pj['subtitle']) ?></h5>
              <?php endif; ?>
              <p><?= e($pj['desc']) ?></p>
              <?php if (!empty($pj['url'])): ?>
                <div class="button-container">
                  <a href="<?= e($pj['url']) ?>" target="_blank" class="btn-project">VIEW PROJECT</a>
                </div>
              <?php endif; ?>
              <div class="slump-stack">
                <?php foreach($pj['tags'] ?? [] as $tag): ?>
                  <span class="slump-tag"><?= e($tag) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="contact" class="section page-transition">
        <h2>Get In Touch</h2>
        <div class="contact-info stagger-on-load">
          <p><span>Email:</span> <a href="mailto:<?= e($data['contact']['email']) ?>"><?= e($data['contact']['email']) ?></a></p>
          <p><span>Discord:</span> <?= e($data['contact']['discord']) ?></p>
          <p><span>Timezone:</span> <?= e($data['contact']['tz']) ?></p>
        </div>
      </section>
    </main>

    <footer class="page-transition">
      <p>© <?= date('Y') ?> Slump</p>
    </footer>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', ()=>{
      document.body.classList.add('page-loaded');
    });
  </script>
</body>
</html>
