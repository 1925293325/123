<?php
/**
 * @name 恋爱记时
 * @desc 记录恋爱天数，甜蜜倒计时
 * @icon 💕
 * @category life
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title>恋爱记时</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--p:#ec4899}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}

.main{width:100%;min-height:100vh;padding:16px;}
.card{background:rgba(255,255,255,.9);backdrop-filter:blur(10px);border-radius:20px;padding:24px;margin-bottom:16px;box-shadow:0 4px 20px rgba(236,72,153,.1);text-align:center}
.heart{font-size:48px;margin-bottom:16px;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}
.days{font-size:64px;font-weight:800;color:var(--p);line-height:1}
.days-label{font-size:16px;color:#9ca3af;margin-top:8px}
.input-row{display:flex;gap:10px;margin-top:20px;align-items:center;justify-content:center}
.input-row input[type="date"]{padding:10px 14px;border:2px solid #f9a8d4;border-radius:12px;font-size:14px;outline:none;background:#fff}
.input-row input[type="date"]:focus{border-color:var(--p)}
.input-row input[type="text"]{padding:10px 14px;border:2px solid #f9a8d4;border-radius:12px;font-size:14px;outline:none;background:#fff;flex:1}
.input-row input[type="text"]:focus{border-color:var(--p)}
.btn{padding:10px 20px;background:var(--p);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer}
.btn:active{opacity:.85}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:20px}
.stat{background:#fff;border-radius:14px;padding:16px 8px;text-align:center}
.stat-num{font-size:28px;font-weight:700;color:var(--p)}
.stat-label{font-size:12px;color:#9ca3af;margin-top:4px}
.couple-name{font-size:20px;font-weight:600;color:#374151;margin-top:8px}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(33,37,41,.9);color:#fff;padding:10px 20px;border-radius:20px;z-index:1000;font-size:13px}
</style>
</head>
<body>
<div id="toast"></div>
<main class="main">
<div class="card">
<div class="heart">💕</div>
<div class="couple-name" id="coupleName">我们</div>
<div class="days" id="days">0</div>
<div class="days-label">天</div>
<div class="stats">
<div class="stat"><div class="stat-num" id="hours">0</div><div class="stat-label">小时</div></div>
<div class="stat"><div class="stat-num" id="minutes">0</div><div class="stat-label">分钟</div></div>
<div class="stat"><div class="stat-num" id="seconds">0</div><div class="stat-label">秒</div></div>
</div>
</div>
<div class="card">
<div style="font-size:14px;font-weight:600;margin-bottom:12px">设置</div>
<input type="text" id="name1" placeholder="你的名字" style="width:100%;margin-bottom:8px;padding:10px 14px;border:2px solid #f9a8d4;border-radius:12px;font-size:14px;outline:none">
<input type="text" id="name2" placeholder="TA的名字" style="width:100%;margin-bottom:8px;padding:10px 14px;border:2px solid #f9a8d4;border-radius:12px;font-size:14px;outline:none">
<input type="date" id="startDate" style="width:100%;margin-bottom:12px;padding:10px 14px;border:2px solid #f9a8d4;border-radius:12px;font-size:14px;outline:none">
<button class="btn" style="width:100%" onclick="save()">保存</button>
</div>
</main>
<script>
function toast(m){const t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(()=>t.style.display='none',1500)}
const KEY='love_timer';
function save(){
const data={
name1:document.getElementById('name1').value,
name2:document.getElementById('name2').value,
startDate:document.getElementById('startDate').value
};
localStorage.setItem(KEY,JSON.stringify(data));
update();
toast('已保存');
}
function update(){
const data=JSON.parse(localStorage.getItem(KEY)||'{}');
if(data.startDate){
const start=new Date(data.startDate);
const now=new Date();
const diff=now-start;
const d=Math.floor(diff/(1000*60*60*24));
const h=Math.floor((diff%(1000*60*60*24))/(1000*60*60));
const m=Math.floor((diff%(1000*60*60))/(1000*60));
const s=Math.floor((diff%(1000*60))/1000);
document.getElementById('days').textContent=d;
document.getElementById('hours').textContent=h;
document.getElementById('minutes').textContent=m;
document.getElementById('seconds').textContent=s;
}
if(data.name1&&data.name2){
document.getElementById('coupleName').textContent=data.name1+' ❤ '+data.name2;
document.getElementById('name1').value=data.name1;
document.getElementById('name2').value=data.name2;
}
if(data.startDate)document.getElementById('startDate').value=data.startDate;
}
update();
setInterval(update,1000);
</script>
</body>
</html>
