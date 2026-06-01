<?php
/**
 * @name 摩斯电码
 * @desc 文字与摩斯电码互转
 * @icon 📡
 * @category encode
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title>摩斯电码</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--p:#6366f1}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}

.main{width:100%;min-height:100vh;padding:16px;}
.card{background:#fff;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
textarea{width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;resize:vertical;font-family:inherit;min-height:80px}
textarea:focus{border-color:var(--p)}
.btn-row{display:flex;gap:10px;margin-top:12px}
.btn{flex:1;padding:11px;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer}
.btn-primary{background:var(--p);color:#fff}
.btn-primary:active{opacity:.85}
.btn-secondary{background:#f3f4f6;color:#374151}
.result{margin-top:12px;padding:14px;background:#f9fafb;border-radius:10px;font-family:monospace;font-size:15px;word-break:break-all;line-height:1.6;min-height:40px}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(33,37,41,.9);color:#fff;padding:10px 20px;border-radius:20px;z-index:1000;font-size:13px}
</style>
</head>
<body>
<div id="toast"></div>
<main class="main">
<div class="card">
<textarea id="input" placeholder="输入文字或摩斯电码..."></textarea>
<div class="btn-row">
<button class="btn btn-primary" onclick="encode()">文字转摩斯</button>
<button class="btn btn-secondary" onclick="decode()">摩斯转文字</button>
</div>
</div>
<div class="card">
<div class="result" id="result">结果将在这里显示</div>
<button class="btn btn-secondary" style="width:100%;margin-top:10px" onclick="copyResult()">复制结果</button>
</div>
</main>
<script>
function toast(m){const t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(()=>t.style.display='none',1500)}
function copyResult(){const r=document.getElementById('result').textContent;if(r&&r!=='结果将在这里显示'){navigator.clipboard.writeText(r).then(()=>toast('已复制'))}}
const MORSE={'A':'.-','B':'-...','C':'-.-.','D':'-..','E':'.','F':'..-.','G':'--.','H':'....','I':'..','J':'.---','K':'-.-','L':'.-..','M':'--','N':'-.','O':'---','P':'.--.','Q':'--.-','R':'.-.','S':'...','T':'-','U':'..-','V':'...-','W':'.--','X':'-..-','Y':'-.--','Z':'--..','0':'-----','1':'.----','2':'..---','3':'...--','4':'....-','5':'.....','6':'-....','7':'--...','8':'---..','9':'----.',',':'--..--','.':'.-..-','?':'..--..','!':'-.-.--',' ':'/'};
const REVERSE={};Object.keys(MORSE).forEach(k=>REVERSE[MORSE[k]]=k);
function encode(){const t=document.getElementById('input').value.toUpperCase();const r=t.split('').map(c=>MORSE[c]||c).join(' ');document.getElementById('result').textContent=r}
function decode(){const t=document.getElementById('input').value.trim();const r=t.split(' ').map(c=>REVERSE[c]||c).join('');document.getElementById('result').textContent=r}
</script>
</body>
</html>
