<?php
/**
 * @name 天气预报
 * @desc 全国天气查询
 * @icon 🌤️
 * @category query
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title>天气预报</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{height:100%;overflow:hidden}
body{font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Helvetica Neue",STHeiti,"Microsoft Yahei",sans-serif;color:#fff;transition:all 1s ease}
.main{height:100%;display:flex;flex-direction:column;overflow-hidden;position:relative}
.bg-layer{position:absolute;inset:0;z-index:-2;background-size:cover;background-position:center;transition:all 1s ease}
.bg-overlay{position:absolute;inset:0;z-index:-1;background:rgba(0,0,0,0.55);transition:all 1s ease}
.header{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;z-index:10}
.back-btn{width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.2);border-radius:50%;text-decoration:none;color:#fff;backdrop-filter:blur(10px)}
.back-btn:active{background:rgba(255,255,255,0.3)}
.back-btn svg{width:20px;height:20px}
.content-wrap{flex:1;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;padding-bottom:20px}
.content-wrap::-webkit-scrollbar{display:none}
.current-section{padding:24px 16px 16px;text-align:center}
.city-name{font-size:26px;font-weight:700;margin-bottom:8px;text-shadow:0 2px 12px rgba(0,0,0,0.5)}
.city-region{font-size:13px;color:rgba(255,255,255,0.8);margin-bottom:20px;text-shadow:0 1px 8px rgba(0,0,0,0.4)}
.temp-display{font-size:84px;font-weight:100;letter-spacing:-3px;line-height:1;text-shadow:0 4px 20px rgba(0,0,0,0.4)}
.temp-display .unit{font-size:42px;font-weight:200;margin-left:-4px}
.weather-desc{font-size:18px;color:rgba(255,255,255,0.95);margin-top:10px;font-weight:500;text-shadow:0 2px 12px rgba(0,0,0,0.4)}
.temp-range{font-size:14px;color:rgba(255,255,255,0.85);margin-top:10px;background:rgba(255,255,255,0.15);display:inline-block;padding:8px 18px;border-radius:20px;backdrop-filter:blur(10px)}
.aqi-row{display:flex;justify-content:center;gap:18px;margin-top:24px;flex-wrap:wrap}
.aqi-item{display:flex;flex-direction:column;align-items:center;min-width:70px;background:rgba(255,255,255,0.15);padding:14px 18px;border-radius:18px;backdrop-filter:blur(10px)}
.aqi-label{font-size:12px;color:rgba(255,255,255,0.75);margin-bottom:6px}
.aqi-value{font-size:18px;font-weight:700;text-shadow:0 1px 8px rgba(0,0,0,0.3)}
.hourly-section{margin:20px 0;padding:0 16px}
.section-title{font-size:16px;font-weight:700;margin-bottom:14px;text-shadow:0 2px 10px rgba(0,0,0,0.3)}
.hourly-scroll{display:flex;gap:16px;overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:8px}
.hourly-scroll::-webkit-scrollbar{display:none}
.hourly-item{display:flex;flex-direction:column;align-items:center;min-width:64px;background:rgba(255,255,255,0.15);padding:16px 14px;border-radius:18px;backdrop-filter:blur(10px)}
.hourly-time{font-size:12px;color:rgba(255,255,255,0.8)}
.hourly-icon{font-size:30px;margin:8px 0}
.hourly-temp{font-size:15px;font-weight:700}
.grid-section{padding:18px;background:rgba(255,255,255,0.15);margin:18px 16px;border-radius:18px;backdrop-filter:blur(10px)}
.grid-title{font-size:15px;font-weight:700;margin-bottom:16px;text-shadow:0 1px 8px rgba(0,0,0,0.3)}
.grid-container{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.grid-item{display:flex;flex-direction:column;align-items:center;text-align:center}
.grid-icon{font-size:24px;margin-bottom:8px}
.grid-label{font-size:11px;color:rgba(255,255,255,0.7)}
.grid-value{font-size:15px;font-weight:700}
.forecast-section{padding:0 16px 16px}
.forecast-title{font-size:16px;font-weight:700;margin-bottom:14px;text-shadow:0 2px 10px rgba(0,0,0,0.3)}
.forecast-item{display:flex;align-items:center;justify-content:space-between;padding:16px 14px;margin-bottom:10px;background:rgba(255,255,255,0.15);border-radius:16px;backdrop-filter:blur(10px)}
.forecast-left{display:flex;align-items:center;gap:14px;flex:1}
.date-info{min-width:50px}
.date-main{font-size:16px;font-weight:700}
.date-sub{font-size:12px;color:rgba(255,255,255,0.7);margin-top:3px}
.forecast-center{flex:1;display:flex;flex-direction:column;align-items:center}
.forecast-icon{font-size:30px}
.forecast-text{font-size:12px;color:rgba(255,255,255,0.75);text-align:center;margin-top:5px}
.forecast-right{min-width:60px;text-align:right}
.temp-high{font-size:17px;font-weight:700}
.temp-low{font-size:14px;color:rgba(255,255,255,0.65);margin-top:3px}
.loading-wrap{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:100;background:rgba(0,0,0,0.7)}
.loading-spinner{width:50px;height:50px;border:4px solid rgba(255,255,255,0.15);border-top-color:#fff;border-radius:50%;animation:spin 0.8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.loading-text{margin-top:18px;font-size:15px;color:rgba(255,255,255,0.9)}
.error-wrap{padding:60px 20px;text-align:center}
.error-icon{font-size:56px;margin-bottom:18px;opacity:0.6}
.error-title{font-size:17px;margin-bottom:10px}
.error-desc{font-size:13px;color:rgba(255,255,255,0.7);margin-bottom:26px;line-height:1.7}
.input-box{width:100%;padding:16px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);border-radius:14px;color:#fff;font-size:16px;outline:none;margin-bottom:14px;backdrop-filter:blur(10px)}
.input-box::placeholder{color:rgba(255,255,255,0.5)}
.input-box:focus{border-color:rgba(255,255,255,0.4);background:rgba(255,255,255,0.2)}
.btn-submit{width:100%;padding:16px;background:rgba(255,255,255,0.2);border:none;border-radius:14px;color:#fff;font-size:16px;font-weight:600;cursor:pointer;backdrop-filter:blur(10px)}
.btn-submit:active{background:rgba(255,255,255,0.3)}
.action-row{display:flex;gap:10px;margin-top:20px}
.btn-action{flex:1;padding:14px;background:rgba(255,255,255,0.15);border:none;border-radius:14px;color:#fff;font-size:14px;font-weight:600;cursor:pointer;backdrop-filter:blur(10px)}
.btn-action:active{background:rgba(255,255,255,0.25)}
</style>
</head>
<body>
<div class="main">
<div id="bgLayer" class="bg-layer"></div>
<div id="bgOverlay" class="bg-overlay"></div>
<header class="header">
<a href="/api/" class="back-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
<div style="font-size:17px;font-weight:600;text-shadow:0 2px 10px rgba(0,0,0,0.3)">天气</div>
<div style="width:40px;display:flex;justify-content:flex-end">
<svg onclick="getLocation()" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="cursor:pointer"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
</div>
</header>
<div id="content" class="content-wrap"></div>
</div>
<script>
const bgConfig={
sunny:{bg:'https://images.unsplash.com/photo-1554048612-387768052bf7?w=1200&q=80',overlay:'rgba(20,60,100,0.45)'},
cloudy:{bg:'https://images.unsplash.com/photo-1534088568595-a066f410bcda?w=1200&q=80',overlay:'rgba(40,50,70,0.55)'},
overcast:{bg:'https://images.unsplash.com/photo-1506781961364-63c1744d61af?w=1200&q=80',overlay:'rgba(30,35,45,0.6)'},
rainy:{bg:'https://images.unsplash.com/photo-1515694346937-94d85e41e6f0?w=1200&q=80',overlay:'rgba(15,30,50,0.65)'},
storm:{bg:'https://images.unsplash.com/photo-1563861826100-9cb868c0a97e?w=1200&q=80',overlay:'rgba(10,15,30,0.7)'},
snowy:{bg:'https://images.unsplash.com/photo-1483664852095-d6cc6870705d?w=1200&q=80',overlay:'rgba(30,50,80,0.5)'},
foggy:{bg:'https://images.unsplash.com/photo-1519699047748-de8e457a634e?w=1200&q=80',overlay:'rgba(40,45,55,0.6)'},
night:{bg:'https://images.unsplash.com/photo-1532274402696-5c366089f0b7?w=1200&q=80',overlay:'rgba(10,20,40,0.55)'}
};
const cityMap={
'Dexing':'德兴','Dezhou':'德州','Dongying':'东营','Suzhou':'苏州','Wuxi':'无锡','Changshu':'常熟','Kunshan':'昆山','Taicang':'太仓','Wujiang':'吴江','Zhangjiagang':'张家港',
'Changsha':'长沙','Zhuzhou':'株洲','Xiangtan':'湘潭','Hengyang':'衡阳','Shaoyang':'邵阳','Yueyang':'岳阳','Changde':'常德','Zhangjiajie':'张家界','Yiyang':'益阳','Chenzhou':'郴州','Yongzhou':'永州','Huaihua':'怀化','Loudi':'娄底','Xiangxi':'湘西',
'Guangzhou':'广州','Shenzhen':'深圳','Zhuhai':'珠海','Shantou':'汕头','Foshan':'佛山','Shaoguan':'韶关','Zhanjiang':'湛江','Zhaoqing':'肇庆','Jiangmen':'江门','Maoming':'茂名','Huizhou':'惠州','Meizhou':'梅州','Shanwei':'汕尾','Heyuan':'河源','Yangjiang':'阳江','Qingyuan':'清远','Dongguan':'东莞','Zhongshan':'中山','Chaozhou':'潮州','Jieyang':'揭阳','Yunfu':'云浮',
'Nanjing':'南京','Xuzhou':'徐州','Changzhou':'常州','Nantong':'南通','Lianyungang':'连云港','Huaian':'淮安','Yancheng':'盐城','Yangzhou':'扬州','Zhenjiang':'镇江','Taizhou':'泰州','Suqian':'宿迁',
'Beijing':'北京','Shanghai':'上海','Tianjin':'天津','Chongqing':'重庆',
'Hangzhou':'杭州','Ningbo':'宁波','Wenzhou':'温州','Jiaxing':'嘉兴','Huzhou':'湖州','Shaoxing':'绍兴','Jinhua':'金华','Quzhou':'衢州','Zhoushan':'舟山','Lishui':'丽水',
'Hefei':'合肥','Wuhu':'芜湖','Bengbu':'蚌埠','Huainan':'淮南','Maanshan':'马鞍山','Huaibei':'淮北','Tongling':'铜陵','Anqing':'安庆','Huangshan':'黄山','Chuzhou':'滁州','Fuyang':'阜阳','LuAn':'六安','Bozhou':'亳州','Chizhou':'池州','Xuancheng':'宣城',
'Jiangsu':'江苏','Jiangxi':'江西','Zhejiang':'浙江','Anhui':'安徽','Fujian':'福建','Guangdong':'广东','Guangxi':'广西','Guizhou':'贵州','Hainan':'海南','Hebei':'河北','Henan':'河南','Heilongjiang':'黑龙江','Hubei':'湖北','Hunan':'湖南','Jilin':'吉林','Liaoning':'辽宁','Qinghai':'青海','Shaanxi':'陕西','Shandong':'山东','Shanxi':'山西','Sichuan':'四川','Yunnan':'云南','Gansu':'甘肃','Neimenggu':'内蒙古','Ningxia':'宁夏','Xinjiang':'新疆','Xizang':'西藏',
'China':'中国'
};
function getCityName(data){
var nearest=data.nearest_area[0];
var cityEn=nearest.areaName[0].value;
var regionEn=nearest.region[0].value;
var countryEn=nearest.country[0].value;
var city=cityMap[cityEn]||cityEn;
var region=cityMap[regionEn]||regionEn;
var country=cityMap[countryEn]||countryEn;
if(countryEn.indexOf('China')!==-1||countryEn.indexOf('CN')!==-1)country='中国';
return{city:city,region:region,country:country};
}
function getWeatherInfo(desc){
if(!desc)return{type:'晴',style:'sunny',icon:'☀️'};
if(desc.indexOf('晴')!==-1&&desc.indexOf('多云')===-1)return{type:'晴',style:'sunny',icon:'☀️'};
if(desc.indexOf('多云')!==-1||desc.indexOf('间')!==-1)return{type:'多云',style:'cloudy',icon:'⛅'};
if(desc.indexOf('阴')!==-1)return{type:'阴',style:'overcast',icon:'☁️'};
if(desc.indexOf('雾')!==-1||desc.indexOf('霾')!==-1)return{type:'雾',style:'foggy',icon:'🌫️'};
if(desc.indexOf('暴雨')!==-1||desc.indexOf('大暴雨')!==-1||desc.indexOf('特大暴雨')!==-1)return{type:'暴雨',style:'storm',icon:'☔'};
if(desc.indexOf('雷')!==-1||desc.indexOf('电')!==-1)return{type:'雷阵雨',style:'storm',icon:'⛈️'};
if(desc.indexOf('大雨')!==-1)return{type:'大雨',style:'rainy',icon:'☔'};
if(desc.indexOf('中雨')!==-1)return{type:'中雨',style:'rainy',icon:'🌧️'};
if(desc.indexOf('小雨')!==-1||desc.indexOf('雨')!==-1||desc.indexOf('阵雨')!==-1)return{type:'小雨',style:'rainy',icon:'🌧️'};
if(desc.indexOf('暴雪')!==-1||desc.indexOf('大雪')!==-1)return{type:'大雪',style:'snowy',icon:'❄️'};
if(desc.indexOf('中雪')!==-1)return{type:'中雪',style:'snowy',icon:'🌨️'};
if(desc.indexOf('小雪')!==-1||desc.indexOf('雪')!==-1||desc.indexOf('阵雪')!==-1)return{type:'小雪',style:'snowy',icon:'🌨️'};
return{type:'晴',style:'sunny',icon:'☀️'};
}
function setBackground(style){
var cfg=bgConfig[style]||bgConfig.sunny;
var bgEl=document.getElementById('bgLayer');
var ovEl=document.getElementById('bgOverlay');
if(bgEl)bgEl.style.backgroundImage='url('+cfg.bg+')';
if(ovEl)ovEl.style.background=cfg.overlay;
}
function render(data){
var cur=data.current_condition[0];
var cityInfo=getCityName(data);
var descZh=cur.weatherDesc[0].value;
var weatherInfo=getWeatherInfo(descZh);
setBackground(weatherInfo.style);
var weekDays=['周日','周一','周二','周三','周四','周五','周六'];
var hourData=data.weather[0]&&data.weather[0].hourly?data.weather[0].hourly:[];
var hourlyHtml='';
var hourCount=Math.min(8,hourData.length);
for(var i=0;i<hourCount;i++){
var h=hourData[i];
var hTime=parseInt(h.time)||0;
var timeText='';
if(hTime>=0&&hTime<3)timeText='0 时';
else if(hTime<6)timeText='3 时';
else if(hTime<9)timeText='6 时';
else if(hTime<12)timeText='9 时';
else if(hTime<15)timeText='12 时';
else if(hTime<18)timeText='15 时';
else if(hTime<21)timeText='18 时';
else timeText='21 时';
var hInfo=getWeatherInfo(h.weatherDesc[0].value);
hourlyHtml+='<div class="hourly-item"><div class="hourly-time">'+timeText+'</div><div class="hourly-icon">'+hInfo.icon+'</div><div class="hourly-temp">'+(h.tempC||h.temp_C||cur.temp_C)+'°</div></div>';
}
if(hourCount===0){
for(var ti=0;ti<8;ti++){
var tText=ti*3+'时';
hourlyHtml+='<div class="hourly-item"><div class="hourly-time">'+tText+'</div><div class="hourly-icon">'+weatherInfo.icon+'</div><div class="hourly-temp">'+cur.temp_C+'°</div></div>';
}
}
var forecastHtml='';
for(var j=0;j<data.weather.length&&j<7;j++){
var d=data.weather[j];
var dt=new Date(d.date);
var dateMain=j===0?'今天':(dt.getMonth()+1)+'/'+dt.getDate();
var dateSub=weekDays[dt.getDay()];
var fDesc='';
if(d.hourly&&d.hourly.length>6)fDesc=d.hourly[6].weatherDesc[0].value;
if(!fDesc&&d.hourly&&d.hourly.length>12)fDesc=d.hourly[12].weatherDesc[0].value;
if(!fDesc)fDesc=descZh;
var fInfo=getWeatherInfo(fDesc);
forecastHtml+='<div class="forecast-item"><div class="forecast-left"><div class="date-info"><div class="date-main">'+dateMain+'</div><div class="date-sub">'+dateSub+'</div></div><div class="forecast-center"><div class="forecast-icon">'+fInfo.icon+'</div><div class="forecast-text">'+fInfo.type+'</div></div></div><div class="forecast-right"><div class="temp-high">'+d.maxtempC+'°</div><div class="temp-low">'+d.mintempC+'°</div></div></div>';
}
var html='<div class="current-section">';
html+='<div class="city-name">'+cityInfo.city+'</div>';
if(cityInfo.region&&cityInfo.region!==cityInfo.city)html+='<div class="city-region">'+cityInfo.region+', '+cityInfo.country+'</div>';
html+='<div class="weather-icon" style="font-size:72px;margin-bottom:12px">'+weatherInfo.icon+'</div>';
html+='<div class="temp-display">'+cur.temp_C+'<span class="unit">°</span></div>';
html+='<div class="weather-desc">'+weatherInfo.type+'</div>';
html+='<div class="temp-range">最高'+data.weather[0].maxtempC+'° 最低'+data.weather[0].mintempC+'°</div>';
html+='<div class="aqi-row">';
html+='<div class="aqi-item"><div class="aqi-label">湿度</div><div class="aqi-value">'+cur.humidity+'%</div></div>';
html+='<div class="aqi-item"><div class="aqi-label">体感</div><div class="aqi-value">'+cur.FeelsLikeC+'°</div></div>';
html+='<div class="aqi-item"><div class="aqi-label">风力</div><div class="aqi-value">'+cur.windspeedKmph+'km/h</div></div>';
html+='</div></div>';
html+='<div class="hourly-section"><div class="section-title">逐小时预报</div><div class="hourly-scroll">'+hourlyHtml+'</div></div>';
html+='<div class="grid-section"><div class="grid-title">详细数据</div><div class="grid-container">';
html+='<div class="grid-item"><div class="grid-icon">💧</div><div class="grid-label">降水</div><div class="grid-value">'+cur.precipMM+'mm</div></div>';
html+='<div class="grid-item"><div class="grid-icon">💨</div><div class="grid-label">风速</div><div class="grid-value">'+cur.windspeedKmph+'km/h</div></div>';
html+='<div class="grid-item"><div class="grid-icon">🧭</div><div class="grid-label">风向</div><div class="grid-value">'+(cur.winddir16Point||'未知')+'</div></div>';
html+='<div class="grid-item"><div class="grid-icon">👁️</div><div class="grid-label">能见度</div><div class="grid-value">'+cur.visibility+'km</div></div>';
html+='<div class="grid-item"><div class="grid-icon">📊</div><div class="grid-label">气压</div><div class="grid-value">'+cur.pressure+'hPa</div></div>';
html+='<div class="grid-item"><div class="grid-icon">☁️</div><div class="grid-label">云量</div><div class="grid-value">'+cur.cloudcover+'%</div></div>';
html+='<div class="grid-item"><div class="grid-icon">🌡️</div><div class="grid-label">露点</div><div class="grid-value">'+(cur.DewPointC||cur.temp_C)+'°</div></div>';
html+='<div class="grid-item"><div class="grid-icon">☀️</div><div class="grid-label">紫外</div><div class="grid-value">'+cur.uvIndex+'</div></div>';
html+='</div></div>';
html+='<div class="forecast-section"><div class="forecast-title">7 天预报</div>'+forecastHtml+'</div>';
document.getElementById('content').innerHTML=html;
}
function showError(msg){
document.getElementById('content').innerHTML='<div class="error-wrap"><div class="error-icon">🌧️</div><div class="error-title">'+msg+'</div><div class="error-desc">请检查网络连接后重试</div><button class="btn-submit" onclick="getLocationWithFallback()">重新定位</button><div class="action-row"><button class="btn-action" onclick="showManualInput()">手动输入城市</button></div></div>';
}
function showManualInput(){
document.getElementById('content').innerHTML='<div class="error-wrap"><div class="error-icon">🏙️</div><div class="error-title">输入城市名称</div><input type="text" class="input-box" id="cityInput" placeholder="请输入城市，例如：苏州" autocomplete="off"><button class="btn-submit" onclick="searchCity()">查询</button><div class="action-row"><button class="btn-action" onclick="getLocationWithFallback()">自动定位</button></div></div>';
setTimeout(function(){var inp=document.getElementById('cityInput');if(inp){inp.focus();inp.addEventListener('keypress',function(e){if(e.key==='Enter')searchCity()})}},100);
}
function searchCity(){
var city=document.getElementById('cityInput')?document.getElementById('cityInput').value.trim():'';
if(!city){alert('请输入城市名称');return;}
document.getElementById('content').innerHTML='<div class="loading-wrap"><div class="loading-spinner"></div><div class="loading-text">正在查询'+city+'...</div></div>';
fetch('https://wttr.in/'+encodeURIComponent(city)+'?format=j1&lang=zh').then(function(r){if(!r.ok)throw new Error();return r.json();}).then(function(data){
if(data.current_condition&&data.nearest_area){render(data);}else{showError('未找到该城市');}
}).catch(function(){showError('查询失败');});
}
function getLocationWithFallback(){
if('geolocation' in navigator){
document.getElementById('content').innerHTML='<div class="loading-wrap"><div class="loading-spinner"></div><div class="loading-text">正在获取精确位置...</div></div>';
navigator.geolocation.getCurrentPosition(function(pos){
var lat=pos.coords.latitude;
var lon=pos.coords.longitude;
fetchWeatherByCoords(lat,lon);
},function(err){
console.log('GPS 定位失败，使用 IP 定位:',err.message);
fetchWeatherByIP();
},{enableHighAccuracy:true,timeout:10000,maximumAge:300000});
}else{
fetchWeatherByIP();
}
}
function fetchWeatherByCoords(lat,lon){
document.getElementById('content').innerHTML='<div class="loading-wrap"><div class="loading-spinner"></div><div class="loading-text">正在查询天气...</div></div>';
fetch('https://wttr.in/'+lat+','+lon+'?format=j1&lang=zh').then(function(r){if(!r.ok)throw new Error();return r.json();}).then(function(data){
if(data.current_condition&&data.nearest_area){render(data);}else{showError('定位失败');}
}).catch(function(){showError('查询失败');});
}
function fetchWeatherByIP(){
document.getElementById('content').innerHTML='<div class="loading-wrap"><div class="loading-spinner"></div><div class="loading-text">正在获取位置...</div></div>';
fetch('https://wttr.in/?format=j1&lang=zh').then(function(r){if(!r.ok)throw new Error();return r.json();}).then(function(data){
if(data.current_condition&&data.nearest_area){render(data);}else{showError('定位失败');}
}).catch(function(){showError('获取失败');});
}
function getLocation(){
getLocationWithFallback();
}
getLocationWithFallback();
</script>
</body>
</html>
