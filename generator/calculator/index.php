<?php
/**
 * @name 计算器
 * @desc 在线科学计算器
 * @icon 🔢
 * @category calc
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title>计算器</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--p:#6366f1}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}

.main{width:100%;min-height:100vh;padding:16px;}
.calc{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08)}
.display{padding:20px;background:#1e293b}
.display input{width:100%;background:none;border:none;color:#fff;font-size:32px;text-align:right;font-family:monospace;outline:none}
.display .expr{color:#94a3b8;font-size:14px;min-height:20px;text-align:right;margin-bottom:4px}
.buttons{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:#f1f5f9}
.btn{padding:18px;border:none;font-size:18px;font-weight:500;cursor:pointer;background:#fff;transition:background .1s}
.btn:active{background:#e2e8f0}
.btn-op{color:var(--p);background:#f8fafc}
.btn-fn{color:#64748b;background:#f8fafc}
.btn-eq{background:var(--p);color:#fff}
.btn-eq:active{background:#4f46e5}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(33,37,41,.9);color:#fff;padding:10px 20px;border-radius:20px;z-index:1000;font-size:13px}
</style>
</head>
<body>
<div id="toast"></div>
<main class="main">
<div class="calc">
<div class="display">
<div class="expr" id="expr"></div>
<input type="text" id="disp" value="0" readonly>
</div>
<div class="buttons">
<button class="btn btn-fn" onclick="clr()">C</button>
<button class="btn btn-fn" onclick="del()">←</button>
<button class="btn btn-fn" onclick="ins('%')">%</button>
<button class="btn btn-op" onclick="ins('/')">÷</button>
<button class="btn" onclick="ins('7')">7</button>
<button class="btn" onclick="ins('8')">8</button>
<button class="btn" onclick="ins('9')">9</button>
<button class="btn btn-op" onclick="ins('*')">×</button>
<button class="btn" onclick="ins('4')">4</button>
<button class="btn" onclick="ins('5')">5</button>
<button class="btn" onclick="ins('6')">6</button>
<button class="btn btn-op" onclick="ins('-')">−</button>
<button class="btn" onclick="ins('1')">1</button>
<button class="btn" onclick="ins('2')">2</button>
<button class="btn" onclick="ins('3')">3</button>
<button class="btn btn-op" onclick="ins('+')">+</button>
<button class="btn" onclick="neg()">±</button>
<button class="btn" onclick="ins('0')">0</button>
<button class="btn" onclick="ins('.')">.</button>
<button class="btn btn-eq" onclick="calc()">=</button>
</div>
</div>
</main>
<script>
function toast(m){const t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(()=>t.style.display='none',1500)}
const disp=document.getElementById('disp');
const expr=document.getElementById('expr');
let val='0';
function update(){disp.value=val}
function ins(v){
if(val==='0'&&v!=='.'&&v!=='%'){val=v}
else{val+=v}
update();
}
function clr(){val='0';expr.textContent='';update()}
function del(){val=val.length>1?val.slice(0,-1):'0';update()}
function neg(){
if(val!=='0'){
if(val.startsWith('-')){val=val.slice(1)}
else{val='-'+val}
update();
}
}
function calc(){
try{
expr.textContent=val;
val=String(eval(val));
if(val==='Infinity'||val==='-Infinity')val='错误';
update();
}catch(e){
val='错误';
update();
setTimeout(()=>{val='0';update()},1000);
}
}
</script>
</body>
</html>
