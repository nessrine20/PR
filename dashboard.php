<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    echo "❌ Accès refusé.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Plateforme Étudiante</title>
  <link rel="stylesheet" href="assets/css/dashboard.css" />
</head>

<body>
  <!-- === NAVBAR === -->
  <header class="navbar">
    <div class="logo">🎓 BOOSTSR51</div>
    <div class="nav-actions">
      <button id="darkModeBtn" class="btn">🌙</button>
      <div class="profile-dropdown">
          <button class="profile-btn">
              👤 <?php echo htmlspecialchars($_SESSION['nom']); ?>
              <span class="dropdown-arrow">▼</span>
          </button>
          <div class="profile-menu" id="profileMenu">
              <div class="profile-menu-header">
                  <div class="profile-avatar-small">🎓</div>
                  <div>
                      <div class="profile-menu-name"><?php echo htmlspecialchars($_SESSION['nom']); ?></div>
                      <div class="profile-menu-role">Étudiant</div>
                  </div>
              </div>
              <div class="profile-menu-divider"></div>
              <div class="profile-menu-item">
                  <span class="menu-icon">📧</span>
                  <span><?php echo htmlspecialchars($_SESSION['email'] ?? 'Email non disponible'); ?></span>
              </div>
              <div class="profile-menu-divider"></div>
              <a href="login.html" class="profile-menu-item logout-item">
                  <span class="menu-icon">🚪</span>
                  <span>Déconnexion</span>
              </a>
          </div>
      </div>
    </div>
  </header>

  <!-- === HAMBURGER TOUJOURS VISIBLE === -->
  <div id="hamburger" class="hamburger">☰</div>
  
  <!-- === SIDEBAR === -->
  <aside id="sidebar" class="sidebar">
    <ul class="menu-list">
      <li class="menu-item">
        <a href="Examens.html" class="sidebar-btn">📝 Examens</a>
      </li>
      <li class="menu-item">
        <a href="competitions.php" class="sidebar-btn">🏆 Compétition</a>
      </li>
      <li class="menu-item">
        <a href="#" class="sidebar-btn">📄 Demande de stage</a>
      </li>
    </ul>
  </aside>

  <!-- === CONTENU PRINCIPAL === -->
  <main class="content">
    <h2 class="welcome-title">Bienvenue, <?php echo htmlspecialchars($_SESSION['nom']); ?> ! 🎓</h2>
    
    <div class="grid-container">
      <div class="card">
        <h3>☁️ Cloud</h3>
        <p>Concepts cloud, AWS, infrastructure…</p>
        <a href="cloud.html" class="btn-card">Accéder</a>
      </div>

      <div class="card">
        <h3>💻 Développement Web</h3>
        <p>HTML, CSS, JS, frameworks…</p>
        <a href="developpement_web.html" class="btn-card">Accéder</a>
      </div>

      <div class="card">
        <h3>🔐 Sécurité Réseaux Sans Fil</h3>
        <p>Wifi, 4G/5G, protocoles…</p>
        <a href="securite.html" class="btn-card">Accéder</a>
      </div>

      <div class="card">
        <h3>🤖 Intelligence Artificielle</h3>
        <p>Machine Learning, NN, Deep Learning…</p>
        <a href="ia.html" class="btn-card">Accéder</a>
      </div>

      <div class="card">
        <h3>🛡️ Audit de Sécurité</h3>
        <p>Attaques, ISO, Nessus</p>
        <a href="audit.html" class="btn-card">Accéder</a>
      </div>

      <div class="card">
        <h3>📞 VoIP</h3>
        <p>SIP, RTP, RTCP</p>
        <a href="VOIp.html" class="btn-card">Accéder</a>
      </div>

      <div class="card">
        <h3>🔐 VPN</h3>
        <p>IPSec, PPP, PPTP, cryptographie…</p>
        <a href="vpn.html" class="btn-card">Accéder</a>
      </div>

      <div class="card">
        <h3>🏢 Culture d'Entreprise</h3>
        <p>Droit d'entreprise</p>
        <a href="culture.html" class="btn-card">Accéder</a>
      </div>

      <div class="card">
        <h3>🛡️ CyberOps</h3>
        <p>Linux, Windows, SOC</p>
        <a href="cyberops.html" class="btn-card">Accéder</a>
      </div>

    </div>
  </main>

  <!-- === CANVAS POUR LE CURSEUR === -->
  <canvas id='cursorCanvas'></canvas>

  <!-- SCRIPT DASHBOARD -->
  <script src='assets/js/dashboard.js?v=5' defer></script>
</body>
</html>
