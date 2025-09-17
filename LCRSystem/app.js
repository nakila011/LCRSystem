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
