/* ============================================================
   FOTOBOX.JS  —  Version 3.0
   12 templates · 6 captures · 100% client-side
   ============================================================ */
window.FB = (function () {
'use strict';

/* ── Constants ── */
var SHOTS = 6;

/* ── State ── */
var stream = null, captured = [], capIdx = 0, isCapping = false;
var selTpl = null, assigns = [], activeSlot = -1;

/* ── DOM helpers ── */
function $$(id) { return document.getElementById(id); }
function sleep(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

var vid       = $$('fbVid');
var capCvs    = $$('capCvs');
var capCtx    = capCvs.getContext('2d');
var ovCd      = $$('ovCd');
var cdNum     = $$('cdNum');
var flashFx   = $$('flashFx');
var savePill  = $$('savePill');
var statusTxt = $$('statusTxt');
var progFill  = $$('progFill');
var capLbl    = $$('capLbl');
var thumbS    = $$('thumbStrip');
var ovPerm    = $$('ovPerm');
var ovDeny    = $$('ovDeny');
var renderOv  = $$('renderOv');
var shotBadge = $$('shotBadge');
var shotNum   = $$('shotNum');

var screens = {
    land: $$('scr-land'),
    cam:  $$('scr-cam'),
    tpl:  $$('scr-tpl'),
    arr:  $$('scr-arr'),
    res:  $$('scr-res')
};

/* ═══════════════════════════════════════════
   CANVAS HELPERS
═══════════════════════════════════════════ */
function rr(ctx, x, y, w, h, r) {
    r = Math.min(r || 0, Math.min(w, h) / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + w - r, y); ctx.quadraticCurveTo(x + w, y, x + w, y + r);
    ctx.lineTo(x + w, y + h - r); ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    ctx.lineTo(x + r, y + h); ctx.quadraticCurveTo(x, y + h, x, y + h - r);
    ctx.lineTo(x, y + r); ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
}

function imgCover(ctx, img, x, y, w, h, mirror) {
    if (!img || !img.complete || !img.naturalWidth) return;
    var sc = Math.max(w / img.naturalWidth, h / img.naturalHeight);
    var sw = w / sc, sh = h / sc;
    var sx = (img.naturalWidth - sw) / 2, sy = (img.naturalHeight - sh) / 2;
    ctx.save();
    if (mirror !== false) {
        ctx.translate(x + w, y); ctx.scale(-1, 1);
        ctx.drawImage(img, sx, sy, sw, sh, 0, 0, w, h);
    } else {
        ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
    }
    ctx.restore();
}

function drawPh(ctx, img, x, y, w, h, rad, mirror) {
    ctx.save();
    rr(ctx, x, y, w, h, rad || 6); ctx.clip();
    if (img && img.complete && img.naturalWidth) {
        imgCover(ctx, img, x, y, w, h, mirror);
    } else {
        ctx.fillStyle = 'rgba(255,255,255,0.06)'; ctx.fill();
        /* slot number hint */
        ctx.restore(); ctx.save();
        ctx.font = 'bold ' + Math.round(Math.min(w, h) * 0.22) + 'px Nunito,sans-serif';
        ctx.fillStyle = 'rgba(255,255,255,0.22)';
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText('📷', x + w / 2, y + h / 2);
    }
    ctx.restore();
}

function ct(ctx, text, x, y, font, color, align, base) {
    ctx.font = font; ctx.fillStyle = color;
    ctx.textAlign = align || 'center'; ctx.textBaseline = base || 'alphabetic';
    ctx.fillText(text, x, y);
}

function linGrad(ctx, x0, y0, x1, y1, stops) {
    var g = ctx.createLinearGradient(x0, y0, x1, y1);
    stops.forEach(function (s, i) { g.addColorStop(i / (stops.length - 1), s); });
    return g;
}
function radGrad(ctx, cx, cy, r0, r1, stops) {
    var g = ctx.createRadialGradient(cx, cy, r0, cx, cy, r1);
    stops.forEach(function (s, i) { g.addColorStop(i / (stops.length - 1), s); });
    return g;
}

/* ═══════════════════════════════════════════
   TEMPLATES  (12 total, max 6 photos each)
═══════════════════════════════════════════ */
var TPLS = [];

/* ── 1. Classic Film Strip (4 photos, portrait) ── */
TPLS.push({
    id:'strip', name:'Classic Strip', emoji:'🎞️', desc:'4 foto', photoCount:4,
    W:400, H:1300,
    slots:[{x:50,y:72,w:300,h:242},{x:50,y:354,w:300,h:242},{x:50,y:636,w:300,h:242},{x:50,y:918,w:300,h:242}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle='#111'; ctx.fillRect(0,0,W,H);
        ctx.fillStyle='#1e1e1e';
        for (var y=28; y<H; y+=36) {
            ctx.beginPath(); ctx.arc(17,y,7,0,Math.PI*2); ctx.fill();
            ctx.beginPath(); ctx.arc(W-17,y,7,0,Math.PI*2); ctx.fill();
        }
        ctx.strokeStyle='#2a0606'; ctx.lineWidth=2;
        ctx.beginPath(); ctx.moveTo(30,0); ctx.lineTo(30,H); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(W-30,0); ctx.lineTo(W-30,H); ctx.stroke();
        ct(ctx,'✦ FOTOBOX · MEDIATOOLS ✦',W/2,46,'bold 13px monospace','#555');
        var sl=[{x:50,y:72,w:300,h:242},{x:50,y:354,w:300,h:242},{x:50,y:636,w:300,h:242},{x:50,y:918,w:300,h:242}];
        sl.forEach(function(s,i){
            drawPh(ctx,ph[i],s.x,s.y,s.w,s.h,3);
            ctx.strokeStyle='#2e2e2e'; ctx.lineWidth=1; rr(ctx,s.x,s.y,s.w,s.h,3); ctx.stroke();
        });
        ct(ctx,'◼ '+new Date().getFullYear()+' · SWEET MEMORIES ◼',W/2,H-24,'12px monospace','#3a3a3a');
    }
});

/* ── 2. Pastel Dream 2×2 (4 photos) ── */
TPLS.push({
    id:'pastel', name:'Pastel Dream', emoji:'🌸', desc:'4 foto', photoCount:4,
    W:900, H:960,
    slots:[{x:30,y:94,w:400,h:388},{x:470,y:94,w:400,h:388},{x:30,y:514,w:400,h:388},{x:470,y:514,w:400,h:388}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle=linGrad(ctx,0,0,W,H,['#fff0f8','#f0e6ff','#e8f4ff']); ctx.fillRect(0,0,W,H);
        ctx.fillStyle=linGrad(ctx,0,0,W,0,['#ffb0dd','#d8a8ff','#a8ccff']);
        ctx.fillRect(0,0,W,66);
        ctx.save(); ctx.shadowColor='rgba(255,100,160,.5)'; ctx.shadowBlur=8;
        ct(ctx,'🌸 Sweet Moments 🌸',W/2,44,'bold 26px "Nunito",sans-serif','white');
        ctx.restore();
        var sl=[{x:30,y:94,w:400,h:388},{x:470,y:94,w:400,h:388},{x:30,y:514,w:400,h:388},{x:470,y:514,w:400,h:388}];
        ['#ffb0cc','#d0b0ff','#b0d4ff','#b0e8d4'].forEach(function(c,i){
            ctx.fillStyle='rgba(0,0,0,0.06)'; rr(ctx,sl[i].x+5,sl[i].y+5,sl[i].w,sl[i].h,18); ctx.fill();
            ctx.fillStyle='white'; rr(ctx,sl[i].x,sl[i].y,sl[i].w,sl[i].h,18); ctx.fill();
            ctx.strokeStyle=c; ctx.lineWidth=5; rr(ctx,sl[i].x,sl[i].y,sl[i].w,sl[i].h,18); ctx.stroke();
            drawPh(ctx,ph[i],sl[i].x+10,sl[i].y+10,sl[i].w-20,sl[i].h-20,12);
        });
        ct(ctx,'💝 ✨ 💕 ⭐ 💝',W/2,H-22,'24px serif','#cc88aa');
    }
});

/* ── 3. Kawaii Pink (4 photos) ── */
TPLS.push({
    id:'kawaii', name:'Kawaii Pink', emoji:'🎀', desc:'4 foto', photoCount:4,
    W:900, H:960,
    slots:[{x:30,y:110,w:388,h:376},{x:482,y:110,w:388,h:376},{x:30,y:538,w:388,h:376},{x:482,y:538,w:388,h:376}],
    draw: function(ctx,W,H,ph){
        /* Background — soft pink gradient */
        ctx.fillStyle=linGrad(ctx,0,0,W,H,['#ffe4f4','#ffd6ee','#f8d0ff']); ctx.fillRect(0,0,W,H);
        /* Star/heart scatter */
        var deco=[{x:50,y:60,'t':'✦'},{x:W-50,y:55,'t':'♥'},{x:W/2-80,y:H-55,'t':'✿'},{x:W/2+80,y:H-48,'t':'✦'},
                  {x:20,y:H/2,'t':'☆'},{x:W-20,y:H/2+30,'t':'♡'}];
        ctx.font='28px serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
        ctx.fillStyle='rgba(255,100,180,.22)';
        deco.forEach(function(d){ ctx.fillText(d.t,d.x,d.y); });
        /* Header band */
        ctx.fillStyle=linGrad(ctx,0,0,W,0,['#ff8fc8','#d8a0ff','#ffb0e8']);
        ctx.beginPath(); rr(ctx,0,0,W,72,0); ctx.fill();
        ctx.save(); ctx.shadowColor='rgba(255,80,160,.5)'; ctx.shadowBlur=8;
        ct(ctx,'🎀 Kawaii Moments 🎀',W/2,46,'bold 26px "Nunito",sans-serif','white');
        ctx.restore();
        /* Cards */
        var sl=[{x:30,y:110,w:388,h:376},{x:482,y:110,w:388,h:376},{x:30,y:538,w:388,h:376},{x:482,y:538,w:388,h:376}];
        ['#ffb0d8','#e0b0ff','#ffd0f0','#c0d8ff'].forEach(function(c,i){
            ctx.save(); ctx.shadowColor='rgba(255,140,200,.3)'; ctx.shadowBlur=12;
            ctx.fillStyle='white'; rr(ctx,sl[i].x,sl[i].y,sl[i].w,sl[i].h,20); ctx.fill(); ctx.restore();
            ctx.strokeStyle=c; ctx.lineWidth=5; rr(ctx,sl[i].x,sl[i].y,sl[i].w,sl[i].h,20); ctx.stroke();
            drawPh(ctx,ph[i],sl[i].x+10,sl[i].y+10,sl[i].w-20,sl[i].h-20,13);
            /* corner heart */
            ctx.font='18px serif'; ctx.textAlign='right'; ctx.textBaseline='bottom';
            ctx.fillStyle='rgba(255,100,180,.5)';
            ctx.fillText('♥',sl[i].x+sl[i].w-8,sl[i].y+sl[i].h-4);
        });
        ct(ctx,'💖 🌷 💖 🌷 💖',W/2,H-22,'22px serif','#d870b0');
    }
});

/* ── 4. Polaroid Bestie (3 photos, scattered) ── */
TPLS.push({
    id:'polar', name:'Polaroid Bestie', emoji:'📷', desc:'3 foto', photoCount:3,
    W:1000, H:760,
    slots:[{x:70,y:100,w:262,h:238},{x:369,y:80,w:262,h:238},{x:668,y:100,w:262,h:238}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle=linGrad(ctx,0,0,W,H,['#fdf8f2','#f8f0e4']); ctx.fillRect(0,0,W,H);
        ctx.fillStyle='rgba(190,150,100,0.07)';
        for (var px=22; px<W; px+=30) for (var py=22; py<H; py+=30) {
            ctx.beginPath(); ctx.arc(px,py,1.5,0,Math.PI*2); ctx.fill();
        }
        var rots=[-7,2,-5], caps=['✨ bestie!','😂 hehe~','💕 luv ya'];
        [{x:70,y:100,w:262,h:238},{x:369,y:80,w:262,h:238},{x:668,y:100,w:262,h:238}].forEach(function(s,i){
            var cx=s.x+s.w/2, cy=s.y+s.h/2+25;
            ctx.save(); ctx.translate(cx,cy); ctx.rotate(rots[i]*Math.PI/180);
            ctx.fillStyle='rgba(0,0,0,0.1)'; ctx.fillRect(-s.w/2+5,-s.h/2-4+8,s.w,s.h+76);
            ctx.fillStyle='#fffdf8'; ctx.fillRect(-s.w/2,-s.h/2+8,s.w,s.h+76);
            ctx.fillStyle='rgba(255,220,80,0.55)';
            ctx.fillRect(-22,-s.h/2+3,44,13); ctx.fillRect(-s.w/2+10,-s.h/2+3,44,13);
            ctx.save(); ctx.beginPath(); ctx.rect(-s.w/2+10,-s.h/2+18,s.w-20,s.h-16); ctx.clip();
            imgCover(ctx,ph[i],-s.w/2+10,-s.h/2+18,s.w-20,s.h-16,true); ctx.restore();
            ct(ctx,caps[i],0,s.h/2+50,'bold 17px "Nunito",sans-serif','#8a6050');
            ctx.restore();
        });
        ct(ctx,'📸 our moments 📸',W/2,H-22,'bold 26px "Pacifico",cursive','#c07060');
    }
});

/* ── 5. Y2K Babe (6 photos, 2×3) ── */
TPLS.push({
    id:'y2k', name:'Y2K Babe', emoji:'🦋', desc:'6 foto', photoCount:6,
    W:1020, H:760,
    slots:[{x:20,y:66,w:310,h:222},{x:355,y:66,w:310,h:222},{x:690,y:66,w:310,h:222},
           {x:20,y:306,w:310,h:222},{x:355,y:306,w:310,h:222},{x:690,y:306,w:310,h:222}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle=linGrad(ctx,0,0,W,H,['#0d0520','#200a42','#050a1e']); ctx.fillRect(0,0,W,H);
        ctx.save(); ctx.strokeStyle=linGrad(ctx,0,0,W,H,['#ff6b9d','#c17ff5','#6bb5ff']); ctx.lineWidth=4;
        rr(ctx,5,5,W-10,H-10,20); ctx.stroke(); ctx.restore();
        ctx.fillStyle=linGrad(ctx,0,0,W,0,['#ff6b9d','#c17ff5','#6bb5ff']);
        ctx.fillRect(0,0,W,58);
        ct(ctx,'✦ Y2K MEMORIES ✦',W/2,38,'bold 24px "Nunito",sans-serif','white');
        [{x:200,y:640},{x:500,y:610},{x:820,y:640},{x:80,y:560}].forEach(function(s){
            ct(ctx,'✦',s.x,s.y,'20px serif','rgba(255,107,157,0.38)');
        });
        var sl=[{x:20,y:66,w:310,h:222},{x:355,y:66,w:310,h:222},{x:690,y:66,w:310,h:222},
                {x:20,y:306,w:310,h:222},{x:355,y:306,w:310,h:222},{x:690,y:306,w:310,h:222}];
        var bc=['#ff6b9d','#c17ff5','#6bb5ff','#6bb5ff','#c17ff5','#ff6b9d'];
        sl.forEach(function(s,i){
            ctx.save(); ctx.shadowColor=bc[i]; ctx.shadowBlur=10;
            ctx.strokeStyle=bc[i]; ctx.lineWidth=2; rr(ctx,s.x,s.y,s.w,s.h,8); ctx.stroke(); ctx.restore();
            drawPh(ctx,ph[i],s.x+2,s.y+2,s.w-4,s.h-4,7);
        });
        ct(ctx,'🦋 FOTOBOX · MEDIATOOLS 🦋',W/2,H-14,'11px monospace','rgba(193,127,245,0.36)');
    }
});

/* ── 6. Sweet Diary (3 photos, vertical) ── */
TPLS.push({
    id:'diary', name:'Sweet Diary', emoji:'📔', desc:'3 foto', photoCount:3,
    W:620, H:940,
    slots:[{x:76,y:108,w:472,h:214},{x:76,y:378,w:472,h:214},{x:76,y:648,w:472,h:214}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle='#fffbf2'; ctx.fillRect(0,0,W,H);
        ctx.strokeStyle='rgba(180,150,220,0.26)'; ctx.lineWidth=1;
        for (var y=82; y<H; y+=32) { ctx.beginPath(); ctx.moveTo(16,y); ctx.lineTo(W-16,y); ctx.stroke(); }
        ctx.strokeStyle='rgba(255,120,160,0.32)'; ctx.lineWidth=1.5;
        ctx.beginPath(); ctx.moveTo(62,0); ctx.lineTo(62,H); ctx.stroke();
        for (var sy=42; sy<H; sy+=60) {
            ctx.fillStyle='rgba(180,150,220,0.4)'; ctx.beginPath(); ctx.arc(30,sy,9,0,Math.PI*2); ctx.fill();
            ctx.fillStyle='rgba(255,255,255,.75)'; ctx.beginPath(); ctx.arc(30,sy,5.5,0,Math.PI*2); ctx.fill();
        }
        ct(ctx,'📔 my diary ♡',W/2,58,'bold 28px "Pacifico",cursive','#c070a0');
        var sl=[{x:76,y:108,w:472,h:214},{x:76,y:378,w:472,h:214},{x:76,y:648,w:472,h:214}];
        ['✨','💕','🌸'].forEach(function(e,i){
            ctx.fillStyle='rgba(255,220,80,0.48)'; ctx.fillRect(sl[i].x+sl[i].w/2-28,sl[i].y-9,56,13); ctx.fillRect(sl[i].x+14,sl[i].y-9,56,13);
            ctx.save(); ctx.shadowColor='rgba(0,0,0,0.07)'; ctx.shadowBlur=8;
            ctx.fillStyle='white'; ctx.fillRect(sl[i].x,sl[i].y,sl[i].w,sl[i].h+36); ctx.restore();
            drawPh(ctx,ph[i],sl[i].x+8,sl[i].y+8,sl[i].w-16,sl[i].h-16,4);
            ct(ctx,e+' Page '+(i+1),sl[i].x+sl[i].w/2,sl[i].y+sl[i].h+26,'15px "Nunito",sans-serif','#a080c0');
        });
    }
});

/* ── 7. Retro Cinema (4 photos, horizontal) ── */
TPLS.push({
    id:'cinema', name:'Retro Cinema', emoji:'🎬', desc:'4 foto', photoCount:4,
    W:1120, H:460,
    slots:[{x:92,y:74,w:220,h:312},{x:342,y:74,w:220,h:312},{x:592,y:74,w:220,h:312},{x:842,y:74,w:220,h:312}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle=linGrad(ctx,0,0,0,H,['#2a1a08','#14100a']); ctx.fillRect(0,0,W,H);
        ctx.fillStyle='#0c0905'; ctx.fillRect(0,0,W,54); ctx.fillRect(0,H-54,W,54);
        ctx.strokeStyle='#3a2806'; ctx.lineWidth=1.5;
        ctx.beginPath(); ctx.moveTo(0,54); ctx.lineTo(W,54); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(0,H-54); ctx.lineTo(W,H-54); ctx.stroke();
        ctx.fillStyle='#050402';
        for (var fx=18; fx<W; fx+=32) { rr(ctx,fx-5,10,10,22,3); ctx.fill(); rr(ctx,fx-5,H-32,10,22,3); ctx.fill(); }
        ct(ctx,'♦ FOTOBOX CINEMA ♦',W/2,36,'bold 16px monospace','#c8a060');
        [{x:92,y:74,w:220,h:312},{x:342,y:74,w:220,h:312},{x:592,y:74,w:220,h:312},{x:842,y:74,w:220,h:312}].forEach(function(s,i){
            ctx.fillStyle='#8a6020'; ctx.fillRect(s.x-3,s.y-3,s.w+6,s.h+6);
            drawPh(ctx,ph[i],s.x,s.y,s.w,s.h,2);
        });
        ct(ctx,'KODAK GOLD 200 ⚡ EXPIRED',W/2,H-17,'13px monospace','#4a3018');
    }
});

/* ── 8. Spring Bloom (4 photos) ── */
TPLS.push({
    id:'bloom', name:'Spring Bloom', emoji:'🌺', desc:'4 foto', photoCount:4,
    W:920, H:940,
    slots:[{x:46,y:128,w:380,h:306},{x:494,y:128,w:380,h:306},{x:46,y:498,w:380,h:306},{x:494,y:498,w:380,h:306}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle=radGrad(ctx,W/2,H/2,0,W*.85,['#fff8fc','#f2fbf5']); ctx.fillRect(0,0,W,H);
        [[80,80],[W-80,80],[80,H-80],[W-80,H-80]].forEach(function(fp){
            for (var p=0;p<6;p++){
                var a=(p/6)*Math.PI*2, px=fp[0]+Math.cos(a)*28, py=fp[1]+Math.sin(a)*28;
                ctx.fillStyle=p%2===0?'rgba(255,140,180,0.18)':'rgba(150,220,160,0.18)';
                ctx.beginPath(); ctx.arc(px,py,14,0,Math.PI*2); ctx.fill();
            }
            ctx.fillStyle='rgba(255,210,80,0.3)'; ctx.beginPath(); ctx.arc(fp[0],fp[1],10,0,Math.PI*2); ctx.fill();
        });
        ct(ctx,'🌺 Spring Bloom 🌺',W/2,80,'bold 30px "Pacifico",cursive','#d870a0');
        var sl=[{x:46,y:128,w:380,h:306},{x:494,y:128,w:380,h:306},{x:46,y:498,w:380,h:306},{x:494,y:498,w:380,h:306}];
        ['#ffb0c8','#c0e8a8','#a8c8ff','#ffc8a0'].forEach(function(c,i){
            ctx.strokeStyle=c; ctx.lineWidth=5; rr(ctx,sl[i].x-4,sl[i].y-4,sl[i].w+8,sl[i].h+8,16); ctx.stroke();
            drawPh(ctx,ph[i],sl[i].x,sl[i].y,sl[i].w,sl[i].h,11);
        });
        ct(ctx,'🌸 🌼 🌻 🌹',W/2,H-22,'30px serif','#d890b0');
    }
});

/* ── 9. Galaxy Night (4 photos) ── */
TPLS.push({
    id:'galaxy', name:'Galaxy Night', emoji:'🌌', desc:'4 foto', photoCount:4,
    W:920, H:720,
    slots:[{x:26,y:88,w:416,h:264},{x:478,y:88,w:416,h:264},{x:26,y:388,w:416,h:264},{x:478,y:388,w:416,h:264}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle=linGrad(ctx,0,0,W,H,['#04051a','#0e0632','#040518']); ctx.fillRect(0,0,W,H);
        for (var si=0; si<150; si++) {
            var sx=(Math.sin(si*137.5)*W/2+W/2)%W, sy=(Math.cos(si*97.3)*H/2+H/2)%H;
            ctx.fillStyle=['#fff','#ffe8ff','#e8e8ff','#ffd8a0'][si%4];
            ctx.globalAlpha=(si%5+3)/10;
            ctx.beginPath(); ctx.arc(sx,sy,((si%4)/4)*1.7+0.3,0,Math.PI*2); ctx.fill();
        }
        ctx.globalAlpha=1;
        ctx.save(); ctx.shadowColor='#c17ff5'; ctx.shadowBlur=16;
        ct(ctx,'✦ GALAXY NIGHT ✦',W/2,52,'bold 24px "Nunito",sans-serif','#d0a0ff'); ctx.restore();
        var sl=[{x:26,y:88,w:416,h:264},{x:478,y:88,w:416,h:264},{x:26,y:388,w:416,h:264},{x:478,y:388,w:416,h:264}];
        sl.forEach(function(s,i){
            ctx.save(); ctx.shadowColor='#c17ff5'; ctx.shadowBlur=12;
            ctx.strokeStyle='rgba(193,127,245,0.5)'; ctx.lineWidth=2; rr(ctx,s.x,s.y,s.w,s.h,8); ctx.stroke(); ctx.restore();
            drawPh(ctx,ph[i],s.x,s.y,s.w,s.h,8);
        });
        ct(ctx,'🌙 FOTOBOX · MEDIATOOLS 🌙',W/2,H-16,'11px monospace','rgba(200,160,255,0.33)');
    }
});

/* ── 10. Rainbow Mosaic (6 photos) ── */
TPLS.push({
    id:'rainbow', name:'Rainbow Mosaic', emoji:'🌈', desc:'6 foto', photoCount:6,
    W:1010, H:720,
    slots:[{x:14,y:70,w:316,h:258},{x:347,y:70,w:316,h:258},{x:680,y:70,w:316,h:258},
           {x:14,y:380,w:316,h:258},{x:347,y:380,w:316,h:258},{x:680,y:380,w:316,h:258}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle='white'; ctx.fillRect(0,0,W,H);
        var rainG=linGrad(ctx,0,0,W,0,['#ff6b6b','#ffa040','#ffef40','#80e080','#6ab0ef','#9060e0','#ff70c0']);
        ctx.fillStyle=rainG; ctx.fillRect(0,0,W,60);
        ctx.save(); ctx.shadowColor='rgba(0,0,0,0.28)'; ctx.shadowBlur=5;
        ct(ctx,'🌈 Rainbow Memories 🌈',W/2,39,'bold 26px "Nunito",sans-serif','white'); ctx.restore();
        var sl=[{x:14,y:70,w:316,h:258},{x:347,y:70,w:316,h:258},{x:680,y:70,w:316,h:258},
                {x:14,y:380,w:316,h:258},{x:347,y:380,w:316,h:258},{x:680,y:380,w:316,h:258}];
        ['#ff6b6b','#ffa040','#80e080','#6ab0ef','#9060e0','#ff70c0'].forEach(function(c,i){
            ctx.strokeStyle=c; ctx.lineWidth=5; rr(ctx,sl[i].x,sl[i].y,sl[i].w,sl[i].h,10); ctx.stroke();
            drawPh(ctx,ph[i],sl[i].x+5,sl[i].y+5,sl[i].w-10,sl[i].h-10,7);
        });
        ctx.fillStyle=rainG; ctx.fillRect(0,H-40,W,40);
        ct(ctx,'FOTOBOX · MEDIATOOLS',W/2,H-14,'bold 14px "Nunito",sans-serif','white');
    }
});

/* ── 11. Cotton Candy (5 photos: 1 big + 4 small) ── */
TPLS.push({
    id:'candy', name:'Cotton Candy', emoji:'🍬', desc:'5 foto', photoCount:5,
    W:920, H:760,
    slots:[{x:26,y:62,w:868,h:376},{x:26,y:458,w:200,h:200},{x:242,y:458,w:200,h:200},{x:458,y:458,w:200,h:200},{x:674,y:458,w:200,h:200}],
    draw: function(ctx,W,H,ph){
        ctx.fillStyle=linGrad(ctx,0,0,W,H,['#ffd6ec','#e8d0ff','#d0e8ff']); ctx.fillRect(0,0,W,H);
        [[90,72,58],[W-90,60,52],[W/2,H*.7,80],[140,H-80,50],[W-140,H-96,64]].forEach(function(b,i){
            ctx.fillStyle=['rgba(255,180,215,.28)','rgba(190,155,255,.25)','rgba(155,190,255,.22)'][i%3];
            ctx.beginPath(); ctx.arc(b[0],b[1],b[2],0,Math.PI*2); ctx.fill();
        });
        ctx.fillStyle=linGrad(ctx,0,0,W,0,['#ff9dc8','#c8a0ff','#a0c8ff']);
        ctx.fillRect(0,0,W,40);
        ct(ctx,'🍬 Cotton Candy 🍬',W/2,28,'bold 20px "Nunito",sans-serif','white');
        var big={x:26,y:62,w:868,h:376};
        ctx.strokeStyle='#ffb0d8'; ctx.lineWidth=5; rr(ctx,big.x-4,big.y-4,big.w+8,big.h+8,22); ctx.stroke();
        drawPh(ctx,ph[0],big.x,big.y,big.w,big.h,18);
        [{x:26,y:458,w:200,h:200},{x:242,y:458,w:200,h:200},{x:458,y:458,w:200,h:200},{x:674,y:458,w:200,h:200}].forEach(function(s,i){
            ctx.strokeStyle=['#ff9dc8','#c8a0ff','#a0c8ff','#ffc0a0'][i]; ctx.lineWidth=4;
            rr(ctx,s.x,s.y,s.w,s.h,14); ctx.stroke();
            drawPh(ctx,ph[i+1],s.x+4,s.y+4,s.w-8,s.h-8,11);
        });
        ct(ctx,'🍭 🍬 💕 🌟 🍭',W/2,H-16,'22px serif','#c880c0');
    }
});

/* ── 12. Aesthetic Café (4 photos, moody warm) ── */
TPLS.push({
    id:'cafe', name:'Aesthetic Café', emoji:'☕', desc:'4 foto', photoCount:4,
    W:900, H:960,
    slots:[{x:30,y:108,w:396,h:380},{x:474,y:108,w:396,h:380},{x:30,y:546,w:396,h:380},{x:474,y:546,w:396,h:380}],
    draw: function(ctx,W,H,ph){
        /* Warm beige/brown background */
        ctx.fillStyle=linGrad(ctx,0,0,W,H,['#f5ede0','#e8d9c4','#efe3d2']); ctx.fillRect(0,0,W,H);
        /* Subtle grain texture */
        ctx.fillStyle='rgba(160,120,70,0.04)';
        for (var gx=0;gx<W;gx+=4) for (var gy=0;gy<H;gy+=4) {
            if ((gx+gy)%8===0) { ctx.fillRect(gx,gy,2,2); }
        }
        /* Top divider */
        ctx.fillStyle=linGrad(ctx,0,0,W,0,['#c8a06a','#a07040','#c8a06a']);
        ctx.fillRect(0,0,W,4);
        ctx.fillStyle=linGrad(ctx,0,0,W,0,['#e8d0b0','#c8a870','#e8d0b0']);
        ctx.fillRect(0,4,W,52);
        ct(ctx,'☕ aesthetic moments',W/2,38,'600 20px "Nunito",sans-serif','#6a4828');
        /* Corner decorations */
        ctx.font='18px serif'; ctx.fillStyle='rgba(180,140,80,0.3)';
        ctx.textAlign='left'; ctx.textBaseline='top';
        ctx.fillText('✦',14,60); ctx.fillText('✦',14,H-36);
        ctx.textAlign='right';
        ctx.fillText('✦',W-14,60); ctx.fillText('✦',W-14,H-36);
        /* Photo frames with warm tint */
        var sl=[{x:30,y:108,w:396,h:380},{x:474,y:108,w:396,h:380},{x:30,y:546,w:396,h:380},{x:474,y:546,w:396,h:380}];
        sl.forEach(function(s,i){
            /* shadow */
            ctx.save(); ctx.shadowColor='rgba(100,60,20,0.18)'; ctx.shadowBlur=14; ctx.shadowOffsetY=4;
            ctx.fillStyle='#fffff8'; rr(ctx,s.x-8,s.y-8,s.w+16,s.h+52,4); ctx.fill(); ctx.restore();
            /* photo */
            drawPh(ctx,ph[i],s.x,s.y,s.w,s.h,2,true);
            /* warm overlay */
            ctx.save(); rr(ctx,s.x,s.y,s.w,s.h,2); ctx.clip();
            ctx.fillStyle='rgba(180,120,60,0.1)'; ctx.fillRect(s.x,s.y,s.w,s.h);
            ctx.restore();
            /* caption line */
            ct(ctx,['— autumn','— latte','— golden hr','— cozy'][i],s.x+s.w/2,s.y+s.h+30,'italic 14px "Nunito",sans-serif','rgba(100,70,30,0.6)');
        });
        ctx.fillStyle=linGrad(ctx,0,0,W,0,['#e8d0b0','#c8a870','#e8d0b0']);
        ctx.fillRect(0,H-36,W,36);
        ct(ctx,'FOTOBOX · MEDIATOOLS',W/2,H-14,'600 12px monospace','rgba(160,110,50,0.5)');
    }
});

/* ═══════════════════════════════════════════
   THUMBNAIL RENDERER  (fixed 280×210 px for all)
═══════════════════════════════════════════ */
function drawThumb(canvas, tpl) {
    var TW = 280, TH = 210;
    canvas.width  = TW;
    canvas.height = TH;
    var ctx = canvas.getContext('2d');
    /* Fill background first */
    ctx.fillStyle = '#0a0a16';
    ctx.fillRect(0, 0, TW, TH);
    /* Scale template to fit with letterbox */
    var scale = Math.min(TW / tpl.W, TH / tpl.H);
    var dx = (TW - tpl.W * scale) / 2;
    var dy = (TH - tpl.H * scale) / 2;
    ctx.save();
    ctx.translate(dx, dy);
    ctx.scale(scale, scale);
    tpl.draw(ctx, tpl.W, tpl.H, new Array(tpl.photoCount).fill(null));
    ctx.restore();
}

/* ═══════════════════════════════════════════
   SCREEN MANAGEMENT
═══════════════════════════════════════════ */
function show(name) {
    Object.keys(screens).forEach(function (k) { screens[k].classList.remove('active'); });
    screens[name].classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ═══════════════════════════════════════════
   CAMERA INIT & CAPTURE LOOP
═══════════════════════════════════════════ */
async function initCam() {
    show('cam');
    ovPerm.style.display = 'flex';
    ovDeny.style.display = 'none';
    shotBadge.style.display = 'none';
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } },
            audio: false
        });
        vid.srcObject = stream;
        await vid.play();
        ovPerm.style.display = 'none';
        shotBadge.style.display = 'flex';
        setStatus('Bersiap... 📸');
        await sleep(1400);
        setStatus('');
        startLoop();
    } catch (e) {
        console.warn('Camera error:', e);
        ovPerm.style.display = 'none';
        ovDeny.style.display = 'flex';
    }
}

async function startLoop() {
    captured = []; capIdx = 0; isCapping = true;
    thumbS.innerHTML = '';
    progFill.style.width = '0%';
    capLbl.textContent = '0 / ' + SHOTS;
    for (var i = 0; i < SHOTS; i++) {
        var d = document.createElement('div'); d.className = 'th-item'; d.id = 'th' + i;
        d.innerHTML = '<div class="th-dot"></div>'; thumbS.appendChild(d);
    }
    while (capIdx < SHOTS && isCapping) {
        await countdown(3);
        if (!isCapping) break;
        await doCapture();
        capIdx++;
        progFill.style.width = (capIdx / SHOTS * 100) + '%';
        capLbl.textContent = capIdx + ' / ' + SHOTS;
        shotNum.textContent = capIdx;
        if (capIdx < SHOTS) {
            setStatus('📸 ' + capIdx + '/' + SHOTS + ' tersimpan!');
            await sleep(500);
            setStatus('');
        }
    }
    if (isCapping) {
        isCapping = false;
        setStatus('✨ Semua foto siap!');
        await sleep(900);
        stopCam();
        show('tpl');
        buildTplGrid();
    }
}

async function countdown(n) {
    ovCd.classList.add('vis');
    for (var i = n; i >= 1; i--) {
        cdNum.textContent = i;
        cdNum.style.animation = 'none'; void cdNum.offsetHeight; cdNum.style.animation = '';
        await sleep(900);
    }
    ovCd.classList.remove('vis');
}

async function doCapture() {
    flashFx.classList.add('on');
    setTimeout(function () { flashFx.classList.remove('on'); }, 320);
    var vw = vid.videoWidth || 1280, vh = vid.videoHeight || 960;
    capCvs.width = vw; capCvs.height = vh;
    capCtx.drawImage(vid, 0, 0, vw, vh);
    var url = capCvs.toDataURL('image/jpeg', 0.92);
    savePill.classList.add('vis');
    var img = new Image();
    await new Promise(function (resolve) { img.onload = resolve; img.src = url; });
    captured.push({ url: url, img: img });
    var th = document.getElementById('th' + capIdx);
    if (th) {
        th.innerHTML = '';
        var ti = document.createElement('img'); ti.src = url; th.appendChild(ti);
        th.classList.add('done');
    }
    await sleep(180);
    savePill.classList.remove('vis');
}

function setStatus(m) { statusTxt.textContent = m; }

/* ═══════════════════════════════════════════
   TEMPLATE SCREEN
═══════════════════════════════════════════ */
function buildTplGrid() {
    var g = $$('tplGrid'); g.innerHTML = '';
    TPLS.forEach(function (t, i) {
        var card = document.createElement('div'); card.className = 'tpl-card'; card.onclick = function () { selectTpl(i); };
        var prev = document.createElement('div'); prev.className = 'tpl-preview';
        var cvs = document.createElement('canvas'); cvs.style.cssText = 'width:100%;height:100%;display:block;';
        var nameDiv = document.createElement('div'); nameDiv.className = 'tpl-name'; nameDiv.textContent = t.emoji + ' ' + t.name;
        var metaDiv = document.createElement('div'); metaDiv.className = 'tpl-meta';
        metaDiv.innerHTML = '<i class="fa-solid fa-images" style="color:#c17ff5;font-size:9px;"></i> ' + t.desc;
        prev.appendChild(cvs); card.appendChild(prev); card.appendChild(nameDiv); card.appendChild(metaDiv);
        g.appendChild(card);
        requestAnimationFrame(function () { drawThumb(cvs, t); });
    });
}

function selectTpl(i) {
    selTpl = TPLS[i];
    document.querySelectorAll('.tpl-card').forEach(function (c, j) { c.classList.toggle('sel', i === j); });
    $$('btnArr').classList.add('rdy');
}

/* ═══════════════════════════════════════════
   ARRANGE SCREEN
═══════════════════════════════════════════ */
function buildArrange() {
    if (!selTpl) return;
    assigns = new Array(selTpl.photoCount).fill(-1);
    for (var i = 0; i < selTpl.photoCount; i++) { if (i < captured.length) assigns[i] = i; }
    activeSlot = -1;
    var cv = $$('arrCvs'); cv.width = selTpl.W; cv.height = selTpl.H;
    var pg = $$('pickGrid'); pg.innerHTML = '';
    captured.forEach(function (p, i) {
        var d = document.createElement('div'); d.className = 'pi'; d.id = 'pi' + i;
        var img = document.createElement('img'); img.src = p.url;
        d.appendChild(img); d.onclick = function () { assignPhoto(i); }; pg.appendChild(d);
    });
    renderArr();
    setTimeout(buildSlotOverlays, 100);
    updatePickerUI();
}

function buildSlotOverlays() {
    var wrap = $$('arrWrap'), cv = $$('arrCvs');
    wrap.querySelectorAll('.sov').forEach(function (e) { e.remove(); });
    if (!selTpl) return;
    var dw = cv.offsetWidth, dh = cv.offsetHeight;
    if (!dw) return;
    var sx = dw / selTpl.W, sy = dh / selTpl.H;
    selTpl.slots.forEach(function (s, i) {
        var ov = document.createElement('div'); ov.className = 'sov'; ov.id = 'sov' + i;
        ov.style.cssText = 'left:' + (s.x*sx) + 'px;top:' + (s.y*sy) + 'px;width:' + (s.w*sx) + 'px;height:' + (s.h*sy) + 'px;';
        ov.innerHTML = '<div class="sov-badge">' + (i+1) + '</div>';
        ov.onclick = function () { selectSlot(i); };
        wrap.appendChild(ov);
    });
}

function selectSlot(i) {
    activeSlot = i;
    document.querySelectorAll('.sov').forEach(function (e, j) { e.classList.toggle('act', j === i); });
    $$('arrTip').innerHTML = 'Slot <strong style="color:#ff6b9d;">' + (i+1) + '</strong> dipilih → klik foto yang mau kamu taruh 👆';
}

function assignPhoto(pi) {
    if (activeSlot < 0) {
        var empty = assigns.indexOf(-1);
        activeSlot = empty >= 0 ? empty : 0; selectSlot(activeSlot);
    }
    assigns = assigns.map(function (v) { return v === pi ? -1 : v; });
    assigns[activeSlot] = pi;
    updatePickerUI(); renderArr();
    var next = -1;
    for (var i = activeSlot + 1; i < assigns.length; i++) { if (assigns[i] === -1) { next = i; break; } }
    if (next >= 0) { selectSlot(next); }
    else {
        activeSlot = -1;
        document.querySelectorAll('.sov').forEach(function (e) { e.classList.remove('act'); });
        $$('arrTip').innerHTML = '✨ Semua slot terisi! Klik <strong style="color:#c17ff5;">Buat Foto!</strong> untuk lanjut.';
    }
}

function updatePickerUI() {
    captured.forEach(function (_, i) {
        var d = $$('pi' + i); if (!d) return;
        var si = assigns.indexOf(i); d.classList.toggle('used', si >= 0);
        var ex = d.querySelector('.pi-badge'); if (ex) ex.remove();
        if (si >= 0) {
            var b = document.createElement('div'); b.className = 'pi-badge'; b.textContent = '#' + (si+1); d.appendChild(b);
        }
    });
}

function renderArr() {
    var cv = $$('arrCvs'), ctx = cv.getContext('2d');
    selTpl.draw(ctx, selTpl.W, selTpl.H, assigns.map(function (i) { return (i >= 0 && captured[i]) ? captured[i].img : null; }));
}

/* ═══════════════════════════════════════════
   FINAL RENDER & RESULT
═══════════════════════════════════════════ */
function renderFinal() {
    renderOv.classList.add('vis');
    setTimeout(function () {
        var cv = $$('resCvs'); cv.width = selTpl.W; cv.height = selTpl.H;
        selTpl.draw(cv.getContext('2d'), selTpl.W, selTpl.H,
            assigns.map(function (i) { return (i >= 0 && captured[i]) ? captured[i].img : null; }));
        var url = cv.toDataURL('image/jpeg', 0.96);
        var btn = $$('dlBtn'); btn.href = url; btn.download = 'fotobox-mediatools-' + Date.now() + '.jpg';
        renderOv.classList.remove('vis'); show('res');
    }, 150);
}

/* ═══════════════════════════════════════════
   UTILITIES
═══════════════════════════════════════════ */
function stopCam() {
    if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
    vid.srcObject = null;
}

window.addEventListener('resize', function () {
    if ($$('scr-arr').classList.contains('active')) { setTimeout(buildSlotOverlays, 80); }
});

/* ═══════════════════════════════════════════
   PUBLIC API
═══════════════════════════════════════════ */
return {
    start:        function () { initCam(); },
    abort:        function () { isCapping = false; stopCam(); captured = []; capIdx = 0; show('land'); },
    goArrange:    function () { if (!selTpl) return; show('arr'); buildArrange(); },
    goTemplates:  function () { show('tpl'); },
    renderResult: function () { renderFinal(); },
    reset:        function () { stopCam(); captured=[]; capIdx=0; selTpl=null; assigns=[]; activeSlot=-1; isCapping=false; show('land'); }
};

})();