<?php
session_start();
include "config.php";
$_SESSION['admin_name'] = "Admin";

$view = $_GET["view"] ?? "dashboard";

/* ==========================================================
   🔄 UPDATE STUDENT
========================================================== */
if (isset($_GET['update_student'])) {
    $id = intval($_GET['update_student']);
    $nom = $_POST['nom'];
    $email = $_POST['email'];

    $conn->query("UPDATE users SET nom='$nom', email='$email' WHERE id=$id");
    header("Location: index.php?view=students");
    exit();
}

/* ==========================================================
   ➕ ADD STUDENT
========================================================== */
if (isset($_GET['add_student'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];

    $conn->query("INSERT INTO users(nom, email, role) VALUES('$nom', '$email', 'etudiant')");
    header("Location: index.php?view=students");
    exit();
}

/* ==========================================================
   🔄 UPDATE TEACHER
========================================================== */
if (isset($_GET['update_teacher'])) {
    $id = intval($_GET['update_teacher']);
    $nom = $_POST['nom'];
    $email = $_POST['email'];

    $conn->query("UPDATE users SET nom='$nom', email='$email' WHERE id=$id");
    header("Location: index.php?view=teachers");
    exit();
}

/* ==========================================================
   ➕ ADD TEACHER
========================================================== */
if (isset($_GET['add_teacher'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];

    $conn->query("INSERT INTO users(nom, email, role) VALUES('$nom', '$email', 'prof')");
    header("Location: index.php?view=teachers");
    exit();
}


/* ==========================================================
   ➕ ADD EVENT (TABLE : evenements)
========================================================== */
if (isset($_GET['add_event'])) {

    $titre = $_POST['titre'];
    $type = $_POST['type'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $couleur = $_POST['couleur'];

    $conn->query("
        INSERT INTO evenements(titre, type, date_debut, date_fin, couleur)
        VALUES ('$titre', '$type', '$date_debut', '$date_fin', '$couleur')
    ");

    header("Location: index.php?view=events");
    exit();
}


/* ==========================================================
   📊 DASHBOARD STATS
========================================================== */

$total_students = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='etudiant'")
                       ->fetch_assoc()['total'];

$total_teachers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='prof'")
                       ->fetch_assoc()['total'];

$active_students = $conn->query("
    SELECT COUNT(*) AS total FROM users 
    WHERE role='etudiant' AND last_login >= NOW() - INTERVAL 7 DAY
")->fetch_assoc()['total'];

$active_teachers = $conn->query("
    SELECT COUNT(*) AS total FROM users 
    WHERE role='prof' AND last_login >= NOW() - INTERVAL 7 DAY
")->fetch_assoc()['total'];

$teacher_contribution = $total_teachers > 0 
    ? round(($active_teachers / $total_teachers) * 100)
    : 0;

/* ==========================================================
   📈 ACTIVITY CHART
========================================================== */

$activity_students = [];
$activity_teachers = [];
$days = [];

for ($i = 6; $i >= 0; $i--) {
    $day = date("Y-m-d", strtotime("-$i day"));
    $days[] = $day;

    $s = $conn->query("SELECT COUNT(*) AS total FROM users 
                       WHERE role='etudiant' AND DATE(last_login)='$day'")
                       ->fetch_assoc()['total'];

    $t = $conn->query("SELECT COUNT(*) AS total FROM users 
                       WHERE role='prof' AND DATE(last_login)='$day'")
                       ->fetch_assoc()['total'];

    $activity_students[] = $s;
    $activity_teachers[] = $t;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style_admin.css?v=1000">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<script>
/* ============================
   📊 Activité des utilisateurs
============================ */
const ctx1 = document.getElementById('activityChart');

new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?= json_encode($days) ?>,
        datasets: [
            {
                label: "Étudiants",
                data: <?= json_encode($activity_students) ?>,
                borderColor: "#4b68ff",
                backgroundColor: "rgba(75,104,255,0.2)",
                borderWidth: 2,
                tension: 0.3
            },
            {
                label: "Enseignants",
                data: <?= json_encode($activity_teachers) ?>,
                borderColor: "#ff7b7b",
                backgroundColor: "rgba(255,123,123,0.2)",
                borderWidth: 2,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

/* ============================
   🎯 Répartition Étudiants / Profs
============================ */
const ctx2 = document.getElementById('rolesChart');

new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ["Étudiants", "Enseignants"],
        datasets: [{
            data: [<?= $total_students ?>, <?= $total_teachers ?>],
            backgroundColor: ["#4b68ff", "#ff7b7b"],
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

<body>

<div class="container">

    <!-- ============================= SIDEBAR ============================= -->
    <div class="sidebar">
        <div class="logo">🎓 BOOSTSR51</div>

        <ul class="menu">
            <li><a href="index.php?view=dashboard" class="active">🏠 Dashboard</a></li>
            <li><a href="index.php?view=students">🎓 Students</a></li>
            <li><a href="index.php?view=teachers">📚 Teachers</a></li>
            <li><a href="index.php?view=events">📅 Events</a></li>
        </ul>
    </div>

    <!-- MAIN -->
    <main class="main">

    <div class="topbar">
            <span class="section-title">Dashboard</span>
            <span class="user">👤 Admin</span>
        </div>


<?php if ($view === "dashboard") : ?>

<!-- ============================= CHARTS ============================ -->
<section class="charts-grid">

    <div class="chart-box">
        <h3>📊 Activité des utilisateurs</h3>
        <canvas id="activityChart"></canvas>
    </div>

    <div class="chart-box">
        <h3>📊 Répartition Étudiants / Enseignants</h3>
        <canvas id="rolesChart"></canvas>
    </div>

</section>

<!-- ============================= STAT CARDS ============================ -->
<section class="cards">

    <div class="stat-card">
        <h3>🎓 Total Étudiants</h3>
        <p class="stat-number"><?= $total_students ?></p>
    </div>

    <div class="stat-card">
        <h3>📘 Total Enseignants</h3>
        <p class="stat-number"><?= $total_teachers ?></p>
    </div>

    <div class="stat-card">
        <h3>🔥 Étudiants actifs</h3>
        <p class="stat-number"><?= $active_students ?></p>
    </div>

    <div class="stat-card">
        <h3>🚀 Profs actifs</h3>
        <p class="stat-number"><?= $active_teachers ?></p>
    </div>

    <div class="stat-card">
        <h3>📈 Contribution profs</h3>
        <p class="stat-number"><?= $teacher_contribution ?>%</p>
    </div>

</section>

<?php endif; ?>

       
        <!-- ==========================================================
               👨‍🎓 STUDENTS
        ========================================================== -->
        <?php if ($view === "students") : ?>

        <?php $result = $conn->query("SELECT * FROM users WHERE role='etudiant'"); ?>

        <div class="actions-top">
            <a class="btn-add" href="index.php?view=students&show_add=1">➕ Ajouter</a>
        </div>

        <?php if (isset($_GET['show_add'])) : ?>
        <form class="add-form" method="POST" action="index.php?view=students&add_student=1">
            <div class="form-row"><label>Nom :</label><input type="text" name="nom" required></div>
            <div class="form-row"><label>Email :</label><input type="email" name="email" required></div>
            <button class="save-btn">💾 Ajouter</button>
            <a href="index.php?view=students" class="cancel-btn">❌</a>
        </form>
        <hr>
        <?php endif; ?>

        <table class="table-users">
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Action</th></tr>

            <?php while ($row = $result->fetch_assoc()) : ?>

            <?php if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) : ?>

                <tr>
                    <form method="POST" action="index.php?view=students&update_student=<?= $row['id'] ?>">
                        <td><?= $row['id'] ?></td>
                        <td><input type="text" name="nom" value="<?= $row['nom'] ?>"></td>
                        <td><input type="email" name="email" value="<?= $row['email'] ?>"></td>
                        <td><button class="save-btn">💾</button>
                            <a class="cancel-btn" href="index.php?view=students">❌</a>
                        </td>
                    </form>
                </tr>

            <?php else : ?>

                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nom'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>
                        <a class="edit-btn" href="index.php?view=students&edit=<?= $row['id'] ?>">✏</a>
                        <a class="delete-btn" href="delete_user.php?id=<?= $row['id'] ?>&role=students"
                           onclick="return confirm('Supprimer ?');">❌</a>
                    </td>
                </tr>

            <?php endif; ?>

            <?php endwhile; ?>
        </table>

        <?php endif; ?>


        <!-- ==========================================================
               👨‍🏫 TEACHERS
        ========================================================== -->
        <?php if ($view === "teachers") : ?>

        <?php $res = $conn->query("SELECT * FROM users WHERE role='prof'"); ?>

        <div class="actions-top">
            <a class="btn-add" href="index.php?view=teachers&show_add=1">➕ Ajouter</a>
        </div>

        <?php if (isset($_GET['show_add'])) : ?>
        <form class="add-form" method="POST" action="index.php?view=teachers&add_teacher=1">
            <div class="form-row"><label>Nom :</label><input type="text" name="nom"></div>
            <div class="form-row"><label>Email :</label><input type="email" name="email"></div>
            <button class="save-btn">💾 Ajouter</button>
            <a href="index.php?view=teachers" class="cancel-btn">❌</a>
        </form>
        <hr>
        <?php endif; ?>

        <table class="table-users">
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Action</th></tr>

            <?php while ($row = $res->fetch_assoc()) : ?>

            <?php if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) : ?>

                <tr>
                    <form method="POST" action="index.php?view=teachers&update_teacher=<?= $row['id'] ?>">
                        <td><?= $row['id'] ?></td>
                        <td><input type="text" name="nom" value="<?= $row['nom'] ?>"></td>
                        <td><input type="email" name="email" value="<?= $row['email'] ?>"></td>
                        <td><button class="save-btn">💾</button>
                            <a class="cancel-btn" href="index.php?view=teachers">❌</a>
                        </td>
                    </form>
                </tr>

            <?php else : ?>

                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nom'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>
                        <a class="edit-btn" href="index.php?view=teachers&edit=<?= $row['id'] ?>">✏</a>
                        <a class="delete-btn" href="delete_user.php?id=<?= $row['id'] ?>&role=teachers"
                           onclick="return confirm('Supprimer ?');">❌</a>
                    </td>
                </tr>

            <?php endif; ?>

            <?php endwhile; ?>
        </table>

        <?php endif; ?>


  
<!-- ==========================================================
       📅 EVENTS
========================================================== -->
<?php if ($view === "events") : ?>

<h2>📅 Liste des Événements</h2>

<div class="actions-top">
    <a class="btn-add" href="index.php?view=events&show_add=1">➕ Ajouter un événement</a>
</div>

<?php 
// Charger les événements
$events = $conn->query("SELECT * FROM evenements ORDER BY date_debut ASC"); 
?>

<?php if (isset($_GET['show_add'])) : ?>
<form class="add-form" method="POST" action="index.php?view=events&add_event=1">

    <div class="form-row">
        <label>Titre :</label>
        <input type="text" name="titre" required>
    </div>

    <div class="form-row">
        <label>Type :</label>
        <input type="text" name="type" required>
    </div>

    <div class="form-row">
        <label>Date début :</label>
        <input type="date" name="date_debut" required>
    </div>

    <div class="form-row">
        <label>Date fin :</label>
        <input type="date" name="date_fin" required>
    </div>

    <div class="form-row">
        <label>Couleur :</label>
        <input type="color" name="couleur" required>
    </div>

    <button class="save-btn">💾 Ajouter</button>
    <a href="index.php?view=events" class="cancel-btn">❌ Annuler</a>

</form>

<hr>
<?php endif; ?>


<table class="table-users">
    <tr>
        <th>ID</th>
        <th>Titre</th>
        <th>Type</th>
        <th>Période</th>
        <th>Couleur</th>
    </tr>

    <?php while ($ev = $events->fetch_assoc()) : ?>
        <tr>
            <td><?= $ev['id'] ?></td>
            <td><?= $ev['titre'] ?></td>
            <td><?= $ev['type'] ?></td>
            <td><?= $ev['date_debut'] ?> → <?= $ev['date_fin'] ?></td>
            <td>
                <div style="width:20px;height:20px;border-radius:50%;background:<?= $ev['couleur'] ?>;"></div>
            </td>
        </tr>
    <?php endwhile; ?>
</table>


<?php endif; ?>


<!-- ===========================
      📊 CHARTS - DOIT ÊTRE EN BAS
=========================== -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    /* ============================
       📊 Activité des utilisateurs
    ============================ */
    const ctx1 = document.getElementById('activityChart');

    if (ctx1) {
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?= json_encode($days) ?>,
                datasets: [
                    {
                        label: "Étudiants",
                        data: <?= json_encode($activity_students) ?>,
                        borderColor: "#4b68ff",
                        backgroundColor: "rgba(75,104,255,0.2)",
                        borderWidth: 2,
                        tension: 0.3
                    },
                    {
                        label: "Enseignants",
                        data: <?= json_encode($activity_teachers) ?>,
                        borderColor: "#ff7b7b",
                        backgroundColor: "rgba(255,123,123,0.2)",
                        borderWidth: 2,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    /* ============================
       🎯 Répartition Étudiants / Profs
    ============================ */
    const ctx2 = document.getElementById('rolesChart');

    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ["Étudiants", "Enseignants"],
                datasets: [{
                    data: [<?= $total_students ?>, <?= $total_teachers ?>],
                    backgroundColor: ["#4b68ff", "#ff7b7b"],
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

});
</script>

</body>
</html>
