<?php
/**
 * @name 电子木鱼
 * @desc 在线积攒功德
 * @icon 🙏
 * @category random
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>电子木鱼</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{
  width:100%;height:100%;overflow:hidden;
  font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Helvetica Neue",sans-serif;
  background:#000000;
  color:#ffffff;
}
body{display:flex;flex-direction:column;align-items:center}

/* 顶部返回 */
.back-btn{
  position:fixed;top:16px;left:16px;width:36px;height:36px;
  display:flex;align-items:center;justify-content:center;
  border-radius:50%;background:rgba(255,255,255,.1);backdrop-filter:blur(6px);
  cursor:pointer;z-index:10;text-decoration:none;color:#aaa;transition:all .2s;
}
.back-btn:active{transform:scale(.88);background:rgba(255,255,255,.2)}
.back-btn svg{width:18px;height:18px}
.page-title{
  position:fixed;top:20px;left:50%;transform:translateX(-50%);
  font-size:14px;font-weight:400;color:#666;letter-spacing:3px;
}

/* 功德计数 */
.gongde-counter{
  margin-top:70px;
  text-align:center;
  z-index:2;
  user-select:none;
}
.gongde-num{
  font-size:52px;font-weight:200;color:#ffffff;letter-spacing:6px;
  font-variant-numeric:tabular-nums;line-height:1;
}
.gongde-label{
  font-size:13px;color:#888;margin-top:6px;letter-spacing:4px;font-weight:300;
}

/* Canvas 区域 */
#canvasWrap{
  flex:1;
  width:100%;
  display:flex;align-items:center;justify-content:center;
  position:relative;
  min-height:0;
}
canvas{
  display:block;
  max-width:100%;max-height:100%;
}

/* 底部区域 */
.bottom-area{
  width:100%;
  padding:0 20px 24px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:12px;
  z-index:2;
}
.hint-text{
  font-size:13px;color:#666;letter-spacing:2px;font-weight:300;
  user-select:none;
}
.action-btn{
  padding:8px 28px;border:1px solid rgba(255,255,255,.15);border-radius:20px;
  background:rgba(255,255,255,.08);backdrop-filter:blur(6px);
  font-size:13px;color:#aaa;cursor:pointer;transition:all .2s;letter-spacing:2px;
}
.action-btn:active{transform:scale(.95);background:rgba(255,255,255,.15)}
</style>
</head>
<body>

<a href="/api/" class="back-btn">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
</a>
<div class="page-title">电子木鱼</div>

<!-- 功德计数 -->
<div class="gongde-counter">
  <div class="gongde-num" id="countDisplay">0</div>
  <div class="gongde-label">功 德</div>
</div>

<!-- 木鱼 Canvas -->
<div id="canvasWrap">
  <canvas id="muyuCanvas"></canvas>
</div>

<!-- 底部 -->
<div class="bottom-area">
  <div class="hint-text">点击木鱼 · 积攒功德</div>
  <button class="action-btn" onclick="resetCount()">清零</button>
</div>

<script>
const canvas = document.getElementById('muyuCanvas');
const ctx = canvas.getContext('2d');
const countDisplay = document.getElementById('countDisplay');

// 适配高分辨率屏
const dpr = window.devicePixelRatio || 1;
let W, H;

function resize(){
  const wrap = document.getElementById('canvasWrap');
  W = wrap.clientWidth;
  H = wrap.clientHeight;
  canvas.width = W * dpr;
  canvas.height = H * dpr;
  canvas.style.width = W + 'px';
  canvas.style.height = H + 'px';
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
}
resize();
window.addEventListener('resize', resize);

// ==================== 加载素材 ====================
const muyuImg = new Image();
const gongdeImg = new Image();
muyuImg.src = './muyu.jpg';      // 37082.jpg 重命名
gongdeImg.src = './gongde.jpg';  // 37083.jpg 重命名

let assetsLoaded = 0;
function onAssetLoad(){ assetsLoaded++; }
muyuImg.onload = onAssetLoad;
gongdeImg.onload = onAssetLoad;

// ==================== 状态 ====================
let gongdeCount = parseInt(localStorage.getItem('muyu_gongde') || '0');
countDisplay.textContent = gongdeCount;

let muyuScale = 1.0;
let muyuScaleTarget = 1.0;
let floatingList = [];

let fishX = W / 2;
let fishY = H / 2;
let fishW = 0, fishH = 0;

// ==================== 声音合成 ====================
let audioCtx = null;
function initAudio(){
  if(!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  if(audioCtx.state === 'suspended') audioCtx.resume();
}

function playMuyuSound(){
  initAudio();
  const now = audioCtx.currentTime;

  // 清脆主音 "笃"
  const o1 = audioCtx.createOscillator();
  const g1 = audioCtx.createGain();
  o1.type = 'sine';
  o1.frequency.setValueAtTime(520, now);
  o1.frequency.exponentialRampToValueAtTime(380, now + 0.03);
  g1.gain.setValueAtTime(0.9, now);
  g1.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
  o1.connect(g1).connect(audioCtx.destination);
  o1.start(now); o1.stop(now + 0.12);

  // 中空共鸣
  const o2 = audioCtx.createOscillator();
  const g2 = audioCtx.createGain();
  o2.type = 'sine';
  o2.frequency.setValueAtTime(380, now);
  o2.frequency.exponentialRampToValueAtTime(260, now + 0.15);
  g2.gain.setValueAtTime(0.5, now);
  g2.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
  o2.connect(g2).connect(audioCtx.destination);
  o2.start(now); o2.stop(now + 0.35);

  // 木质泛音
  const o3 = audioCtx.createOscillator();
  const g3 = audioCtx.createGain();
  o3.type = 'triangle';
  o3.frequency.setValueAtTime(850, now);
  o3.frequency.exponentialRampToValueAtTime(600, now + 0.02);
  g3.gain.setValueAtTime(0.25, now);
  g3.gain.exponentialRampToValueAtTime(0.001, now + 0.06);
  o3.connect(g3).connect(audioCtx.destination);
  o3.start(now); o3.stop(now + 0.06);

  // 噪声瞬态
  const bufLen = audioCtx.sampleRate * 0.03;
  const buf = audioCtx.createBuffer(1, bufLen, audioCtx.sampleRate);
  const d = buf.getChannelData(0);
  for(let i=0; i<bufLen; i++) d[i] = (Math.random()*2-1) * Math.pow(1 - i/bufLen, 3);
  const ns = audioCtx.createBufferSource();
  const ng = audioCtx.createGain();
  const nf = audioCtx.createBiquadFilter();
  nf.type = 'bandpass'; nf.frequency.value = 700; nf.Q.value = 1.5;
  ns.buffer = buf;
  ng.gain.setValueAtTime(0.4, now);
  ng.gain.exponentialRampToValueAtTime(0.001, now + 0.04);
  ns.connect(nf).connect(ng).connect(audioCtx.destination);
  ns.start(now);

  // 回音
  const delay = audioCtx.createDelay();
  delay.delayTime.value = 0.18;
  const delayGain = audioCtx.createGain();
  delayGain.gain.value = 0.2;
  const feedback = audioCtx.createGain();
  feedback.gain.value = 0.2;

  const echoOsc = audioCtx.createOscillator();
  const echoG = audioCtx.createGain();
  echoOsc.type = 'sine';
  echoOsc.frequency.setValueAtTime(300, now + 0.1);
  echoOsc.frequency.exponentialRampToValueAtTime(200, now + 0.4);
  echoG.gain.setValueAtTime(0, now);
  echoG.gain.linearRampToValueAtTime(0.15, now + 0.12);
  echoG.gain.exponentialRampToValueAtTime(0.001, now + 0.6);

  echoOsc.connect(echoG).connect(delay).connect(audioCtx.destination);
  delay.connect(delayGain).connect(audioCtx.destination);
  delay.connect(feedback).connect(delay);
  echoOsc.start(now + 0.1); echoOsc.stop(now + 0.6);
}

// ==================== 点击检测 ====================
function isHitFish(x, y){
  const dx = x - fishX;
  const dy = y - fishY;
  const r = Math.max(fishW, fishH) * 0.45;
  return (dx*dx + dy*dy) <= r*r;
}

canvas.addEventListener('pointerdown', (e)=>{
  const rect = canvas.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;

  if(isHitFish(x, y)){
    gongdeCount++;
    localStorage.setItem('muyu_gongde', gongdeCount);
    countDisplay.textContent = gongdeCount;
    muyuScaleTarget = 0.88;
    playMuyuSound();

    // 飘字
    const offsetX = (Math.random() - 0.5) * 80;
    floatingList.push({
      x: fishX + offsetX - (gongdeImg.naturalWidth || 80) / 2,
      y: fishY - fishH * 0.5,
      alpha: 1.0,
      speedY: 80 + Math.random() * 60,
      scale: 0.8 + Math.random() * 0.4
    });
  }
});

// ==================== 动画循环 ====================
let lastTime = performance.now();

function loop(now){
  const dt = Math.min((now - lastTime) / 1000, 0.05);
  lastTime = now;

  // 更新木鱼缩放
  muyuScale += (muyuScaleTarget - muyuScale) * 0.25;
  if(muyuScaleTarget < 1.0 && Math.abs(muyuScale - muyuScaleTarget) < 0.005){
    muyuScaleTarget = 1.0;
  }

  // 更新飘字
  for(let i = floatingList.length - 1; i >= 0; i--){
    const ft = floatingList[i];
    ft.y -= ft.speedY * dt;
    ft.alpha -= 1.2 * dt;
    if(ft.alpha <= 0) floatingList.splice(i, 1);
  }

  // 重新计算木鱼尺寸和位置
  fishX = W / 2;
  fishY = H / 2;
  if(muyuImg.complete && muyuImg.naturalWidth){
    const maxW = Math.min(W * 0.75, 360);
    const scale = maxW / muyuImg.naturalWidth;
    fishW = muyuImg.naturalWidth * scale * muyuScale;
    fishH = muyuImg.naturalHeight * scale * muyuScale;
  } else {
    fishW = 200 * muyuScale;
    fishH = 180 * muyuScale;
  }

  // ===== 绘制 =====
  ctx.clearRect(0, 0, W, H);

  // 木鱼
  if(muyuImg.complete && muyuImg.naturalWidth){
    ctx.save();
    ctx.translate(fishX, fishY);
    ctx.scale(muyuScale, muyuScale);
    const drawW = Math.min(W * 0.75, 360);
    const drawH = drawW * (muyuImg.naturalHeight / muyuImg.naturalWidth);
    ctx.drawImage(muyuImg, -drawW/2, -drawH/2, drawW, drawH);
    ctx.restore();
  } else {
    ctx.save();
    ctx.translate(fishX, fishY);
    ctx.scale(muyuScale, muyuScale);
    ctx.beginPath();
    ctx.ellipse(0, 0, 100, 80, 0, 0, Math.PI*2);
    ctx.fillStyle = '#c9a87c';
    ctx.fill();
    ctx.strokeStyle = '#5a3d22';
    ctx.lineWidth = 3;
    ctx.stroke();
    ctx.restore();
  }

  // 飘字（功德+1）
  if(gongdeImg.complete && gongdeImg.naturalWidth){
    for(const ft of floatingList){
      ctx.save();
      ctx.globalAlpha = Math.max(0, ft.alpha);
      ctx.translate(ft.x, ft.y);
      ctx.scale(ft.scale, ft.scale);
      ctx.drawImage(gongdeImg, 0, 0);
      ctx.restore();
    }
  } else {
    for(const ft of floatingList){
      ctx.save();
      ctx.globalAlpha = Math.max(0, ft.alpha);
      ctx.font = 'bold 24px "PingFang SC", sans-serif';
      ctx.fillStyle = '#aaa';
      ctx.textAlign = 'center';
      ctx.fillText('功德 +1', ft.x, ft.y);
      ctx.restore();
    }
  }

  requestAnimationFrame(loop);
}

requestAnimationFrame(loop);

// ==================== 清零 ====================
function resetCount(){
  if(confirm('确定要清零功德吗？')){
    gongdeCount = 0;
    localStorage.setItem('muyu_gongde', '0');
    countDisplay.textContent = '0';
  }
}
</script>
</body>
</html>