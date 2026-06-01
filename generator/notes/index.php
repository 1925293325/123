<?php
/**
 * @name 快捷笔记
 * @desc 临时记录文字，自动保存
 * @icon 📝
 * @category tool
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title>快捷笔记</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--p:#0ea5e9}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}

.main{width:100%;min-height:100vh;padding:16px;}
.card{background:#fff;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
textarea{width:100%;padding:14px;border:1px solid #e5e7eb;border-radius:12px;font-size:15px;outline:none;resize:vertical;font-family:inherit;min-height:200px;line-height:1.6}
textarea:focus{border-color:var(--p)}
.status{font-size:12px;color:#9ca3af;text-align:right;margin-top:8px}
.btn-row{display:flex;gap:10px;margin-top:10px}
.btn{flex:1;padding:11px;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer}
.btn-primary{background:var(--p);color:#fff}
.btn-secondary{background:#f3f4f6;color:#374151}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(33,37,41,.9);color:#fff;padding:10px 20px;border-radius:20px;z-index:1000;font-size:13px}
</style>
</head>
<body>
<div id="toast"></div>
<main class="main">
<div class="card">
<textarea id="note" placeholder="在这里写笔记..."></textarea>
<div class="status" id="status"></div>
<div class="btn-row">
<button class="btn btn-primary" onclick="save()">保存</button>
<button class="btn btn-secondary" onclick="clearNote()">清空</button>
</div>
</div>
</main>
<script>
function toast(m){const t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(()=>t.style.display='none',1500)}
const STORAGE_KEY='quick_note';
function save(){
localStorage.setItem(STORAGE_KEY,document.getElementById('note').value);
document.getElementById('status').textContent='已保存 '+new Date().toLocaleTimeString();
toast('已保存');
}
function clearNote(){
if(confirm('确定清空笔记？')){
document.getElementById('note').value='';
localStorage.removeItem(STORAGE_KEY);
document.getElementById('status').textContent='';
toast('已清空');
}
}
window.addEventListener('DOMContentLoaded',()=>{
const saved=localStorage.getItem(STORAGE_KEY);
if(saved)document.getElementById('note').value=saved;
let timer;
document.getElementById('note').addEventListener('input',()=>{
clearTimeout(timer);
timer=setTimeout(()=>{
localStorage.setItem(STORAGE_KEY,document.getElementById('note').value);
document.getElementById('status').textContent='自动保存 '+new Date().toLocaleTimeString();
},1000);
});
});
</script>
</body>
</html>
