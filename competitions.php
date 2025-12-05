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
  <title>Compétitions – BOOSTSR51</title>

  <link rel="stylesheet" href="assets/css/dashboard.css" />
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />

  <style>
   .competitions-title {
  text-align: center;
  font-size: 32px;
  font-weight: bold;
  color: #6a34cc;
  margin-top: 10px; /* ↓ avant : 40px */
}

    .competitions-subtitle {
      text-align: center;
      font-size: 16px;
      color: #7a739f;
      margin-bottom: 5px;
    }

.competitions-wrapper {
  width: 100%;                /* 🟪 Même largeur que la navbar */
  max-width: 1250px;          /* 🟪 Aligné avec le conteneur du dashboard */
  background: white;
  margin: 10px auto;          /* Centré */
  padding: 25px;
  border-radius: 22px;
  box-shadow: 0 15px 35px rgba(157, 139, 211, 0.12);
}


    

    #calendar {
  width: 100%;               /* prend toute la place du wrapper */
  max-width: 1200px;         /* limite propre pour correspondre visuellement */
  margin: 0 auto;            /* centre parfaitement */
  }
  </style>
</head>

<body>

<!-- NAVBAR -->
<header class="navbar">
  <div class="logo">🎓 BOOSTSR51</div>
  <div class="nav-actions">
    <button id="darkModeBtn" class="btn">🌙</button>
<a href="dashboard.php" class="btn btn-back">← Retour</a>
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
        <a href="logout.php" class="profile-menu-item logout-item">
          🚪 Déconnexion
        </a>
      </div>
    </div>
  </div>
</header>

<!-- HAMBURGER -->
<div id="hamburger" class="hamburger">☰</div>

<!-- SIDEBAR -->
<aside id="sidebar" class="sidebar">
  <ul class="menu-list">
    <li><a href="dashboard.php" class="sidebar-btn">🏠 Dashboard</a></li>
    <li><a href="Examens.html" class="sidebar-btn">📝 Examens</a></li>
    <li><a href="competitions.php" class="sidebar-btn">🏆 Compétition</a></li>
    <li><a href="stage.php" class="sidebar-btn">📄 Demande de stage</a></li>
  </ul>
</aside>

<!-- CONTENU -->
<main class="content">

  <h2 class="competitions-title">🏆 Compétitions & Événements</h2>
  <p class="competitions-subtitle">Voici le calendrier des hackathons, concours et événements scolaires.</p>

  <div class="competitions-wrapper">
    <div id="calendar"></div>
  </div>

</main>

<canvas id="cursorCanvas"></canvas>
<script src="assets/js/dashboard.js"></script>

<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'fr',
    height: 600,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,listMonth'
    },
    events: 'load_events.php',
    eventClick: function(info) {
      alert("📌 " + info.event.title);
    }
  });

  calendar.render();
});

/* === SIDEBAR TOGGLE === */
document.getElementById("hamburger").onclick = function () {
  document.getElementById("sidebar").classList.toggle("show");
};

/* === PROFILE MENU === */
document.querySelector(".profile-btn").onclick = function () {
  document.getElementById("profileMenu").classList.toggle("show");
};

/* Fermer si on clique ailleurs */
document.addEventListener("click", function(e) {
  if (!e.target.closest(".profile-dropdown")) {
    document.getElementById("profileMenu").classList.remove("show");
  }
});


</script>

</body>
</html>
