<?php
/**
 * @name 字符画生成
 * @desc 文字转ASCII字符画
 * @icon 🎨
 * @category text
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title>字符画生成</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--p:#f59e0b}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}

.main{width:100%;min-height:100vh;padding:16px;}
.card{background:#fff;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
input,textarea{width:100%;padding:11px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;font-family:inherit}
input:focus,textarea:focus{border-color:var(--p)}
textarea{min-height:60px;resize:vertical}
.btn{width:100%;padding:12px;background:var(--p);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;margin-top:10px}
.btn:active{opacity:.85}
.result{margin-top:12px;padding:14px;background:#1e293b;border-radius:10px;font-family:monospace;font-size:12px;color:#e2e8f0;line-height:1.3;white-space:pre;overflow-x:auto;min-height:40px}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(33,37,41,.9);color:#fff;padding:10px 20px;border-radius:20px;z-index:1000;font-size:13px}
</style>
</head>
<body>
<div id="toast"></div>
<main class="main">
<div class="card">
<input type="text" id="text" placeholder="输入文字（英文效果最佳）" value="Hello">
<div style="display:flex;gap:8px;margin-top:10px">
<input type="number" id="size" value="5" min="2" max="10" style="width:80px" placeholder="大小">
<select id="char" style="flex:1;padding:11px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none">
<option value="block">方块 █</option>
<option value="hash">井号 #</option>
<option value="star">星号 *</option>
<option value="dot">圆点 ●</option>
</select>
</div>
<button class="btn" onclick="gen()">生成字符画</button>
</div>
<div class="card">
<div class="result" id="result">字符画将在这里显示</div>
<button class="btn" style="background:#475569;margin-top:10px" onclick="copyResult()">复制</button>
</div>
</main>
<script>
function toast(m){const t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(()=>t.style.display='none',1500)}
function copyResult(){const r=document.getElementById('result').textContent;if(r){navigator.clipboard.writeText(r).then(()=>toast('已复制'))}}
const CHARS={block:'█',hash:'#',star:'*',dot:'●'};
function gen(){
const text=document.getElementById('text').value||'Hello';
const size=parseInt(document.getElementById('size').value)||5;
const c=CHARS[document.getElementById('char').value]||'█';
const lines=text.split('');
let result='';
lines.forEach(ch=>{
const code=ch.charCodeAt(0);
const binary=code.toString(2).padStart(8,'0');
for(let i=0;i<size;i++){
let line='';
for(let j=0;j<binary.length;j++){
line+=binary[j]==='1'?c:' ';
}
result+=line+'\n';
}
result+='\n';
});
document.getElementById('result').textContent=result;
}
</script>
</body>
</html>
