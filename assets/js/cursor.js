// === 🌈 CURSEUR INTERACTIF CANVAS - Violet Pastel ===
document.addEventListener("DOMContentLoaded", () => {
  // Créer le canvas s'il n'existe pas
  let canvas = document.getElementById("cursorCanvas");
  if (!canvas) {
    canvas = document.createElement("canvas");
    canvas.id = "cursorCanvas";
    canvas.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 9999;
    `;
    document.body.appendChild(canvas);
  }

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
});

