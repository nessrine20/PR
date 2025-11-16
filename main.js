function toggleMenu() {
  const menu = document.getElementById("menu");
  menu.style.display = menu.style.display === "flex" ? "none" : "flex";
}

function changeRole(role) {
  const illustration = document.getElementById("illustration");
  const title = document.getElementById("roleTitle");
  const desc = document.getElementById("roleDesc");
  const img = document.getElementById("roleImage");

  if (role === "prof") {
    illustration.style.background = "linear-gradient(120deg, #584e83, #7248ff)";
    title.textContent = "Bienvenue Prof 👩‍🏫";
    desc.textContent = "Gérez vos cours et vos étudiants facilement.";
    roleImage.innerHTML= `<img src="images/teacher.jpeg" alt="prof" class="role-photo">`;
  }

  if (role === "etudiant") {
    illustration.style.background = "linear-gradient(120deg, #9d87b9, #584e83)";
    title.textContent = "Bienvenue Étudiant 🎓";
    desc.textContent = "Accédez à vos cours et ressources en ligne.";
    roleImage.innerHTML = `<img src="images/etudiant.jpeg" alt="etudiant" class="role-photo">`;
  }

  if (role === "admin") {
    illustration.style.background = "linear-gradient(120deg, #8e2de2, #9f57ff)";
    title.textContent = "Bienvenue Admin 🧑‍💻";
    desc.textContent = "Gérez le système et les utilisateurs.";
    roleImage.innerHTML = `<img src="images/admin.jpeg" alt="Admin" class="role-photo">`;
  }

  // Ferme le menu après sélection
  document.getElementById("menu").style.display = "none";
}
window.addEventListener("DOMContentLoaded", () => {
  changeRole("etudiant");
});
