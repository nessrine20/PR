// === 🍔 GESTION DU SIDEBAR ===
 document.addEventListener("DOMContentLoaded", () => {const hamburger = document.getElementById("hamburger");
const sidebar = document.getElementById("sidebar");

if (hamburger && sidebar) {
  hamburger.addEventListener("click", () => {
    sidebar.classList.toggle("active");
  });
}

// === 📘 MENU DÉROULANT ===
const menuItems = document.querySelectorAll(".menu-item");
menuItems.forEach((item) => {
  const title = item.querySelector(".menu-title");
  if (title) {
    title.addEventListener("click", () => {
      item.classList.toggle("open");
    });
  }
});

// === 🌗 MODE SOMBRE ===
const darkModeBtn = document.getElementById("darkModeBtn");
let darkMode = false;

if (darkModeBtn) {
  darkModeBtn.addEventListener("click", () => {
    darkMode = !darkMode;

    const body = document.body;
    const navbar = document.querySelector(".navbar");
    const sidebar = document.querySelector(".sidebar");
    const content = document.querySelector(".content");

    if (darkMode) {
      body.style.background = "#000";
      body.style.color = "#fff";
      if (navbar) navbar.style.background = "linear-gradient(120deg, #3c096c, #7b2ff7)";
      if (sidebar) sidebar.style.background = "linear-gradient(180deg, #5a189a, #3c096c)";
      if (content) content.style.color = "#f0f0f0";
      darkModeBtn.textContent = "☀️";
    } else {
      body.style.background = "linear-gradient(180deg, #f4ecff, #f8f5ff)";
      body.style.color = "#222";
      if (navbar) navbar.style.background = "linear-gradient(120deg, #9c6bff, #c29fff)";
      if (sidebar) sidebar.style.background = "linear-gradient(180deg, #b288ff, #8b5cff)";
      if (content) content.style.color = "#222";
      darkModeBtn.textContent = "🌙";
    }
  });
}

// === 🌍 LANGUE FR/EN ===
const langBtn = document.getElementById("langBtn");
const welcomeTitle = document.getElementById("welcomeTitle");
const welcomeText = document.getElementById("welcomeText");
const profileText = document.querySelector(".profile");

if (langBtn) {
  langBtn.addEventListener("click", () => {
    if (langBtn.textContent === "FR") {
      langBtn.textContent = "EN";
      if (profileText) profileText.textContent = "Profile";
      if (welcomeTitle) welcomeTitle.textContent = "Welcome to your student space 👋";
      if (welcomeText) welcomeText.textContent = "Select an item from the menu to start.";
    } else {
      langBtn.textContent = "FR";
      if (profileText) profileText.textContent = "Profil";
      if (welcomeTitle) welcomeTitle.textContent = "Bienvenue sur votre espace étudiant 👋";
      if (welcomeText) welcomeText.textContent = "Sélectionnez un élément dans le menu pour commencer.";
    }
  });
}

// === 🌈 CURSEUR INTERACTIF CANVAS ===
const canvas = document.getElementById("cursorCanvas");
if (canvas) {
  const ctx = canvas.getContext("2d");

  let width = window.innerWidth;
  let height = window.innerHeight;
  canvas.width = width;
  canvas.height = height;

  let mouseMoved = false;
  const pointer = { x: 0.5 * width, y: 0.5 * height };
  const params = {
    pointsNumber: 40,
    widthFactor: 0.3,
    mouseThreshold: 0.6,
    spring: 0.4,
    friction: 0.5,
  };

  let points = [];

  function setupPoints() {
    points = [];
    for (let i = 0; i < params.pointsNumber; i++) {
      points.push({ x: pointer.x, y: pointer.y, dx: 0, dy: 0 });
    }
  }

  function updateMouse(e) {
    mouseMoved = true;
    pointer.x = e.clientX;
    pointer.y = e.clientY;
  }

  function draw() {
    if (!mouseMoved) return requestAnimationFrame(draw);
    ctx.clearRect(0, 0, width, height);

    points.forEach((p, i) => {
      const prev = i === 0 ? pointer : points[i - 1];
      const spring = params.spring;
      p.dx += (prev.x - p.x) * spring;
      p.dy += (prev.y - p.y) * spring;
      p.dx *= params.friction;
      p.dy *= params.friction;
      p.x += p.dx;
      p.y += p.dy;
    });

    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    for (let i = 1; i < points.length; i++) {
      ctx.lineTo(points[i].x, points[i].y);
    }

    // 🌈 Dégradé dynamique pastel violet
    const gradient = ctx.createLinearGradient(0, 0, width, height);
    gradient.addColorStop(0, "#d6b8ff");
    gradient.addColorStop(0.5, "#f0bfff");
    gradient.addColorStop(1, "#f5c8f1");

    ctx.strokeStyle = gradient;
    ctx.lineWidth = 2.8;
    ctx.shadowColor = "#c7d8ff";
    ctx.shadowBlur = 20;
    ctx.stroke();

    requestAnimationFrame(draw);
  }

  setupPoints();
  draw();

  window.addEventListener("mousemove", updateMouse);
  window.addEventListener("resize", () => {
    width = window.innerWidth;
    height = window.innerHeight;
    canvas.width = width;
    canvas.height = height;
    setupPoints();
  });
}
});