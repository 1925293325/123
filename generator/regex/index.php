<?php
/**
 * @name 正则测试
 * @desc 在线测试正则表达式
 * @icon 🔍
 * @category dev
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title>正则测试</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--p:#10b981}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}

.main{width:100%;min-height:100vh;padding:16px;}
.card{background:#fff;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.input-row{display:flex;gap:8px;margin-bottom:12px}
.input-row input{flex:1;padding:11px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;font-family:monospace}
.input-row input:focus{border-color:var(--p)}
.flags{padding:8px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:13px;width:60px;outline:none;text-align:center}
textarea{width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;resize:vertical;font-family:monospace;min-height:100px}
textarea:focus{border-color:var(--p)}
.result{margin-top:12px;padding:14px;background:#f9fafb;border-radius:10px;font-size:14px;line-height:1.8;min-height:40px;word-break:break-all}
.match{background:#d1fae5;padding:2px 4px;border-radius:4px}
.error{color:#ef4444}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(33,37,41,.9);color:#fff;padding:10px 20px;border-radius:20px;z-index:1000;font-size:13px}
</style>
</head>
<body>
<div id="toast"></div>
<main class="main">
<div class="card">
<div class="input-row">
<input type="text" id="pattern" placeholder="正则表达式" value="\d+">
<input class="flags" id="flags" value="gi" placeholder="标志">
</div>
<textarea id="text" placeholder="输入测试文本...">订单号：12345，价格：99元，数量：3件，电话：13800138000</textarea>
</div>
<div class="card">
<div style="font-size:13px;color:#6b7280;margin-bottom:8px">匹配结果</div>
<div class="result" id="result">点击测试按钮查看结果</div>
</div>
<button class="btn" style="width:100%;padding:12px;background:var(--p);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer" onclick="test()">测试匹配</button>
</main>
<script>
function toast(m){const t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(()=>t.style.display='none',1500)}
function test(){
const p=document.getElementById('pattern').value;
const f=document.getElementById('flags').value;
const t=document.getElementById('text').value;
if(!p){toast('请输入正则表达式');return}
try{
const r=new RegExp(p,f);
const matches=[...t.matchAll(new RegExp(p,f.includes('g')?f:f+'g'))];
if(matches.length===0){document.getElementById('result').innerHTML='<span style="color:#6b7280">无匹配结果</span>';return}
let html=t;
const sorted=[...matches].sort((a,b)=>b.index-a.index);
sorted.forEach(m=>{
const highlighted='<span class="match">'+m[0]+'</span>';
html=html.substring(0,m.index)+highlighted+html.substring(m.index+m[0].length);
});
document.getElementById('result').innerHTML=html;
}catch(e){document.getElementById('result').innerHTML='<span class="error">错误：'+e.message+'</span>'}
}
</script>
</body>
</html>
