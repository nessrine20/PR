// === 🍔 GESTION DU SIDEBAR ===
document.addEventListener("DOMContentLoaded", () => {
  const hamburger = document.getElementById("hamburger");
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

  // Appliquer le mode sombre au chargement si sauvegardé
  if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    if (darkModeBtn) darkModeBtn.textContent = "☀️";
  }

  if (darkModeBtn) {
    darkModeBtn.addEventListener("click", () => {
      document.body.classList.toggle('dark-mode');

      if (document.body.classList.contains('dark-mode')) {
        localStorage.setItem('darkMode', 'enabled');
        darkModeBtn.textContent = "☀️";
      } else {
        localStorage.setItem('darkMode', 'disabled');
        darkModeBtn.textContent = "🌙";
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
  // === 👤 MENU PROFIL === 
  const profileBtn = document.querySelector(".profile-btn");
  const profileMenu = document.getElementById("profileMenu");

  if (profileBtn && profileMenu) {
    profileBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle("show");
    });

    document.addEventListener("click", (e) => {
      if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
        profileMenu.classList.remove("show");
      }
    });
  }
});

