<?php
/**
 * @name 进制转换
 * @desc 二进制/八进制/十进制/十六进制互转
 * @icon 🔢
 * @category dev
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title>进制转换</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--p:#8b5cf6}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}

.main{width:100%;min-height:100vh;padding:16px;}
.card{background:#fff;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.input-row{display:flex;gap:8px;margin-bottom:10px}
.input-row input{flex:1;padding:11px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;font-family:monospace}
.input-row input:focus{border-color:var(--p)}
.input-row label{padding:11px 12px;background:#f3f4f6;border-radius:10px;font-size:12px;font-weight:600;color:#6b7280;white-space:nowrap}
.result-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f1f3f5}
.result-row:last-child{border-bottom:none}
.result-label{font-size:13px;color:#6b7280;font-weight:500}
.result-val{font-size:14px;font-family:monospace;color:#212529;word-break:break-all}
.copy-btn{padding:4px 10px;background:#f3f4f6;border:none;border-radius:6px;font-size:12px;cursor:pointer;color:#6b7280}
.copy-btn:hover{background:#e5e7eb}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(33,37,41,.9);color:#fff;padding:10px 20px;border-radius:20px;z-index:1000;font-size:13px}
</style>
</head>
<body>
<div id="toast"></div>
<main class="main">
<div class="card">
<div class="input-row">
<label>十进制</label>
<input type="text" id="dec" placeholder="输入十进制数" oninput="convert('dec')">
</div>
<div class="input-row">
<label>二进制</label>
<input type="text" id="bin" placeholder="输入二进制数" oninput="convert('bin')">
</div>
<div class="input-row">
<label>八进制</label>
<input type="text" id="oct" placeholder="输入八进制数" oninput="convert('oct')">
</div>
<div class="input-row">
<label>十六进制</label>
<input type="text" id="hex" placeholder="输入十六进制数" oninput="convert('hex')">
</div>
</div>
</main>
<script>
function toast(m){const t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(()=>t.style.display='none',1500)}
function convert(from){
let dec;
try{
if(from==='dec')dec=parseInt(document.getElementById('dec').value)||0;
else if(from==='bin')dec=parseInt(document.getElementById('bin').value,2)||0;
else if(from==='oct')dec=parseInt(document.getElementById('oct').value,8)||0;
else if(from==='hex')dec=parseInt(document.getElementById('hex').value,16)||0;
}catch(e){return}
if(from!=='dec')document.getElementById('dec').value=dec;
if(from!=='bin')document.getElementById('bin').value=dec.toString(2);
if(from!=='oct')document.getElementById('oct').value=dec.toString(8);
if(from!=='hex')document.getElementById('hex').value=dec.toString(16).toUpperCase();
}
</script>
</body>
</html>
