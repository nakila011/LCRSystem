document.addEventListener('DOMContentLoaded', () => {
  const line = document.getElementById('lineChart');
  if (line) drawLineChart(line);

  const donut = document.getElementById('donutChart');
  if (donut) drawDonut(donut, [65, 20, 15]);
});

function drawLineChart(canvas){
  const ctx = canvas.getContext('2d');
  ctx.strokeStyle = "#1d4ed8";
  ctx.beginPath();
  ctx.moveTo(50,200); ctx.lineTo(150,120); ctx.lineTo(250,180); ctx.lineTo(350,100); ctx.stroke();
}

function drawDonut(canvas, values){
  const total = values.reduce((a,b)=>a+b,0);
  const ctx = canvas.getContext('2d');
  const cx = canvas.width/2, cy = canvas.height/2, r = 120;
  let start = -Math.PI/2;
  const colors = ["#1d4ed8","#60a5fa","#bfdbfe"];
  values.forEach((v,i)=>{
    ctx.beginPath();
    ctx.moveTo(cx,cy);
    ctx.arc(cx,cy,r,start,start+2*Math.PI*(v/total));
    ctx.fillStyle = colors[i]; ctx.fill();
    start += 2*Math.PI*(v/total);
  });
  ctx.globalCompositeOperation="destination-out";
  ctx.beginPath(); ctx.arc(cx,cy,r*0.6,0,Math.PI*2); ctx.fill();
  ctx.globalCompositeOperation="source-over";
}


// Fade-in when cards enter the viewport
document.addEventListener('DOMContentLoaded', () => {
  const els = document.querySelectorAll('.appear-on-scroll');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          io.unobserve(entry.target); // animate once
        }
      });
    }, { threshold: 0.15 });
    els.forEach(el => io.observe(el));
  } else {
    // Fallback: reveal immediately
    els.forEach(el => el.classList.add('in-view'));
  }
});

(function () {
  const sidebar = document.querySelector('.sidebar');
  const btn = document.querySelector('.sidebar__toggle');
  if (!sidebar || !btn) return;

  // restore saved state
  const saved = localStorage.getItem('sidebar-collapsed') === 'true';
  sidebar.dataset.collapsed = String(saved);
  btn.setAttribute('aria-pressed', String(saved));

  // toggle
  btn.addEventListener('click', () => {
    const next = sidebar.dataset.collapsed !== 'true';
    sidebar.dataset.collapsed = String(next);
    btn.setAttribute('aria-pressed', String(next));
    localStorage.setItem('sidebar-collapsed', String(next));
  });

  // set active link based on URL (optional)
  const links = [...document.querySelectorAll('.nav__link')];
  const here = location.pathname.split('/').pop() || 'staffPanel.html';
  links.forEach(a => {
    const isHere = a.getAttribute('href') === here;
    a.classList.toggle('is-active', isHere);
    if (isHere) a.setAttribute('aria-current', 'page');
  });
})();