/**
 * resources/js/bgremover/ui.js
 *
 * FIX v3 — ROOT CAUSE: `URL.revokeObjectURL is not a function`
 *
 * Inside an inline HTML event handler attribute (onload="..."), the JS scope
 * chain is: element → document → window.
 * `document.URL` is a string (the current page URL), NOT the URL constructor.
 * So `URL` inside onload="URL.revokeObjectURL(this.src)" resolves to the
 * string `document.URL`, and calling .revokeObjectURL() on a string throws:
 *   TypeError: URL.revokeObjectURL is not a function
 *
 * Fix: Never use `URL.revokeObjectURL` inside innerHTML attribute strings.
 * Create img elements in JS and attach onload listeners via .onload or
 * addEventListener, where `URL` correctly resolves to window.URL.
 *
 * Also fixed:
 *   - setProcessingThumb: track & revoke previous ObjectURL on replace
 *   - buildMultiCard: use JS DOM construction instead of innerHTML for imgs
 *   - setCardDone: use addEventListener instead of onclick strings in innerHTML
 */

import { S, ACCEPTED, MAX_BYTES, PHOTO_SIZES, PF_BG_COLORS } from './state.js';
import { undo, redo, resetToAI }    from './editor/history.js';
import { clearCursor }               from './editor/canvas.js';

/* ── Boot ─────────────────────────────────────────────── */
export function initUI() {
  bindModeSelect();
  bindBgrDropzone();
  bindBgrControls();
  bindPfDropzone();
  bindPfControls();
  bindResultView();
  bindPfResultView();
  bindEditorToolbar();
  bindBulkActions();
  bindKeyboard();
  initCompareSlider();
  setUIState('mode-select');
}

/* ══════════════════════════════════════════════
   STATE MACHINE
══════════════════════════════════════════════ */
const VIEW_IDS = [
  'viewModeSelect',
  'viewUpload','viewProcessing','viewResult','viewEditor','viewMulti',
  'viewPfUpload','viewPfCrop','viewPfProcessing','viewPfResult',
];
const STATE_MAP = {
  'mode-select':'viewModeSelect', 'upload':'viewUpload',
  'processing':'viewProcessing',  'result':'viewResult',
  'editor':'viewEditor',          'multi':'viewMulti',
  'pf-upload':'viewPfUpload',     'pf-crop':'viewPfCrop',
  'pf-processing':'viewPfProcessing', 'pf-result':'viewPfResult',
};
const STEP_STATE = {
  'upload':1,'processing':3,'result':4,'editor':4,'multi':4,
  'pf-upload':1,'pf-crop':2,'pf-processing':3,'pf-result':4,
};
const BGR_LABELS = ['Upload','Opsi','Proses AI','Download'];
const PF_LABELS  = ['Upload','Crop & Atur','Proses AI','Download'];

export function setUIState(state) {
  S.mode = state;
  VIEW_IDS.forEach(id => { const el=document.getElementById(id); if(el) el.hidden=true; });
  const active = document.getElementById(STATE_MAP[state]);
  if (active) active.hidden = false;

  const stepper = document.getElementById('bgrStepper');
  const backBtn = document.getElementById('btnBackToMode');

  if (state === 'mode-select') {
    if (stepper) stepper.hidden = true;
    if (backBtn) backBtn.hidden = true;
    document.querySelectorAll('.bgr-mode-card').forEach(c => c.classList.remove('selected'));
    S.tool = null;
  } else {
    if (stepper) stepper.hidden = false;
    if (backBtn) backBtn.hidden = false;
    const step   = STEP_STATE[state] ?? 1;
    const labels = state.startsWith('pf-') ? PF_LABELS : BGR_LABELS;
    for (let i = 1; i <= 4; i++) {
      const el  = document.getElementById(`stepIndicator${i}`);
      const lbl = document.getElementById(`stepLabel${i}`);
      if (!el) continue;
      if (lbl) lbl.textContent = labels[i-1];
      el.classList.toggle('active', i === step);
      el.classList.toggle('done',   i < step);
    }
  }
  if (state !== 'mode-select') {
    const area = document.querySelector('.bgr-view:not([hidden])');
    if (area) setTimeout(() => area.scrollIntoView({behavior:'smooth',block:'start'}), 50);
  }
}

/* ── Mode Select ──────────────────────────────────────── */
function bindModeSelect() {
  document.getElementById('btnModeBgr')?.addEventListener('click', () => {
    S.tool = 'bgremover';
    document.querySelector('[data-mode="bgr"]')?.classList.add('selected');
    setUIState('upload');
  });
  document.getElementById('btnModePf')?.addEventListener('click', () => {
    S.tool = 'pasfoto';
    document.querySelector('[data-mode="pf"]')?.classList.add('selected');
    setUIState('pf-upload');
  });
  document.getElementById('btnBackToMode')?.addEventListener('click', () => {
    _resetPfState(); _resetBgrState(); setUIState('mode-select');
  });
}

function _resetPfState() {
  if (S.pf.cropper) { try { S.pf.cropper.destroy(); } catch(_){} S.pf.cropper = null; }
  S.pf.origFile = S.pf.aiImg = S.pf.resultCanvas = S.pf.resultDataURL = null;
}
function _resetBgrState() {
  if (S.aiBlobUrl)     { URL.revokeObjectURL(S.aiBlobUrl);     S.aiBlobUrl=null; }
  if (S.origObjectUrl) { URL.revokeObjectURL(S.origObjectUrl); S.origObjectUrl=null; }
  S.aiBlob = S.origFile = null;
  S.results.clear();
}

/* ── Progress ─────────────────────────────────────────── */
export function setProgress(pct, label='') {
  const fill=document.getElementById('progressFill');
  const pctEl=document.getElementById('progressPct');
  const lbl=document.getElementById('progressLabel');
  if(fill)  fill.style.width=`${Math.round(pct)}%`;
  if(pctEl) pctEl.textContent=`${Math.round(pct)}%`;
  if(lbl)   lbl.textContent=label;

  if (S.mode==='pf-processing') {
    const pf=document.getElementById('pfProgressFill');
    const pp=document.getElementById('pfProgressPct');
    const pl=document.getElementById('pfProgressLabel');
    if(pf) pf.style.width=`${Math.round(pct)}%`;
    if(pp) pp.textContent=`${Math.round(pct)}%`;
    if(pl) pl.textContent=label;
    ['pfProcStep1','pfProcStep2','pfProcStep3','pfProcStep4'].forEach((id,i)=>{
      const el=document.getElementById(id); if(!el) return;
      const ths=[0,10,60,92];
      el.classList.remove('active','done');
      if(pct>=ths[i]) { el.classList.add(i<3&&pct>=ths[i+1]?'done':'active'); }
    });
  }
  if (S.mode==='processing') {
    [{id:'pmStep1',t:0},{id:'pmStep2',t:25},{id:'pmStep3',t:50},{id:'pmStep4',t:92}]
      .forEach(({id,t},i,a)=>{
        const el=document.getElementById(id); if(!el) return;
        el.classList.remove('active','done');
        if(pct>=t) el.classList.add(pct>=(a[i+1]?.t??101)?'done':'active');
      });
  }
}

/* Thumb ObjectURL tracker — prevents memory leaks */
let _thumbUrl = null;
export function setProcessingThumb(file) {
  if (_thumbUrl) { URL.revokeObjectURL(_thumbUrl); _thumbUrl=null; }
  const src = file instanceof File ? (_thumbUrl=URL.createObjectURL(file)) : file;
  const b=document.getElementById('processingThumb');   if(b) b.src=src;
  const p=document.getElementById('pfProcessingThumb'); if(p) p.src=src;
}

/* ══════════════════════════════════════════════
   BGR — DROPZONE
══════════════════════════════════════════════ */
const MAX_BGR_FILES = 5;

function bindBgrDropzone() {
  const zone=document.getElementById('dropzone');
  const input=document.getElementById('fileInput');
  const browse=document.getElementById('btnBrowse');
  if (!zone) return;
  browse?.addEventListener('click', e=>{e.stopPropagation();input?.click();});
  zone.addEventListener('click', ()=>input?.click());
  input?.addEventListener('change', e=>{if(e.target.files?.length) _handleBgrFiles([...e.target.files]);});
  zone.addEventListener('dragenter', e=>{e.preventDefault();zone.classList.add('drag-over');});
  zone.addEventListener('dragover',  e=>{e.preventDefault();});
  zone.addEventListener('dragleave', e=>{if(!zone.contains(e.relatedTarget)) zone.classList.remove('drag-over');});
  zone.addEventListener('drop', e=>{
    e.preventDefault(); zone.classList.remove('drag-over');
    _handleBgrFiles([...(e.dataTransfer.files||[])]);
  });
  document.addEventListener('paste', e=>{
    if(S.mode!=='upload') return;
    const imgs=[...(e.clipboardData?.items||[])].filter(it=>it.type.startsWith('image/')).map(it=>it.getAsFile());
    if(imgs.length) _handleBgrFiles(imgs);
  });
}

let _bgrFiles=[];

function _handleBgrFiles(files) {
  const valid=files.filter(f=>{
    if(!ACCEPTED.has(f.type)){toast(`Format tidak didukung: ${f.name}`,'error');return false;}
    if(f.size>MAX_BYTES){toast(`File terlalu besar: ${f.name}`,'error');return false;}
    return true;
  });
  const rem=MAX_BGR_FILES-_bgrFiles.length;
  if(valid.length>rem){toast(`Maks. ${MAX_BGR_FILES} file — ${rem} slot tersisa`,'error');valid.splice(rem);}
  _bgrFiles.push(...valid);
  _renderBgrFileList();
}

function _renderBgrFileList() {
  const list=document.getElementById('bgrFileList');
  const procBtn=document.getElementById('btnBgrProcess');
  const label=document.getElementById('btnBgrProcessLabel');
  if(!list) return;

  /* Revoke any still-pending blob URLs before clearing */
  list.querySelectorAll('img[data-blob]').forEach(img=>{ URL.revokeObjectURL(img.dataset.blob); });
  list.hidden = _bgrFiles.length===0;
  list.innerHTML='';

  _bgrFiles.forEach((file,idx)=>{
    const item=document.createElement('div');
    item.className='bgr-file-item';

    /* ─── FIX: Build img via JS, not innerHTML, so onload runs in module
     *   scope where `URL` === window.URL (not document.URL string)       */
    const blobUrl=URL.createObjectURL(file);
    const img=document.createElement('img');
    img.className='bgr-file-thumb';
    img.alt='';
    img.dataset.blob=blobUrl;          // store for cleanup
    img.onload=()=>{
      URL.revokeObjectURL(blobUrl);    // safe here: module scope URL = window.URL
      delete img.dataset.blob;
    };
    img.onerror=()=>{ URL.revokeObjectURL(blobUrl); delete img.dataset.blob; };
    img.src=blobUrl;                   // set AFTER attaching handlers

    const info=document.createElement('div');
    info.className='bgr-file-info';
    const nameDiv=document.createElement('div'); nameDiv.className='bgr-file-name'; nameDiv.textContent=file.name;
    const sizeDiv=document.createElement('div'); sizeDiv.className='bgr-file-size'; sizeDiv.textContent=`${(file.size/1024).toFixed(0)} KB`;
    info.appendChild(nameDiv); info.appendChild(sizeDiv);

    const rm=document.createElement('button');
    rm.className='bgr-file-remove'; rm.type='button'; rm.title='Hapus';
    rm.innerHTML='<i class="fa-solid fa-xmark"></i>';
    rm.addEventListener('click', e=>{
      e.stopPropagation();
      if(img.dataset.blob) URL.revokeObjectURL(img.dataset.blob);
      _bgrFiles.splice(idx,1);
      _renderBgrFileList();
    });

    item.appendChild(img); item.appendChild(info); item.appendChild(rm);
    list.appendChild(item);
  });

  const count=_bgrFiles.length;
  if(procBtn){ procBtn.hidden=count===0; procBtn.disabled=count===0; }
  if(label)  label.textContent=count===1?'Proses Gambar':`Proses ${count} Gambar Sekaligus`;
}

/* ── BGR Controls ────────────────────────────────────── */
function bindBgrControls() {
  document.getElementById('qualityBtns')?.addEventListener('click', e=>{
    const btn=e.target.closest('[data-q]'); if(!btn) return;
    document.querySelectorAll('#qualityBtns .bgr-q-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active'); S.model=btn.dataset.q;
  });
  document.getElementById('bgSwatches')?.addEventListener('click', e=>{
    const sw=e.target.closest('[data-bg]'); if(!sw) return;
    document.querySelectorAll('#bgSwatches .bgr-swatch').forEach(s=>s.classList.remove('active'));
    sw.classList.add('active'); S.bg=sw.dataset.bg;
  });
  document.getElementById('customColor')?.addEventListener('input', e=>{
    S.bg=e.target.value;
    const sw=e.target.closest('.bgr-swatch');
    document.querySelectorAll('#bgSwatches .bgr-swatch').forEach(s=>s.classList.remove('active'));
    if(sw) sw.classList.add('active');
  });
  document.getElementById('btnBgrProcess')?.addEventListener('click', ()=>{
    if(_bgrFiles.length && window._bgrHandleFiles){
      window._bgrHandleFiles([..._bgrFiles]); _bgrFiles=[]; _renderBgrFileList();
    }
  });
}

/* ── BGR Result ──────────────────────────────────────── */
export function setResultView(origUrl, resultUrl, filename) {
  const oi=document.getElementById('compareOrigImg'); if(oi) oi.src=origUrl;
  const ri=document.getElementById('compareResultImg'); if(ri) ri.src=resultUrl;
  const fn=document.getElementById('resultFilename'); if(fn) fn.textContent=filename??'—';
  _setCompareSplit(50);
}
function bindResultView() {
  document.getElementById('btnResultEdit')?.addEventListener('click',()=>{
    if(S.origFile&&S.aiBlob) window._bgrOpenEditor(S.origFile,S.aiBlob,'result');
  });
  document.getElementById('btnResultDownloadPNG')?.addEventListener('click',()=>_dlBgr('png'));
  document.getElementById('btnResultDownloadJPG')?.addEventListener('click',()=>_dlBgr('jpg'));
  document.getElementById('btnResultNew')?.addEventListener('click',()=>{ _resetBgrState(); setUIState('upload'); });
}
function _dlBgr(fmt) {
  if(!S.aiBlob) return;
  const c=document.createElement('canvas');
  const img=new Image();
  img.onload=()=>{
    c.width=img.naturalWidth; c.height=img.naturalHeight;
    const ctx=c.getContext('2d');
    if(fmt==='jpg'&&S.bg&&S.bg!=='transparent'){ctx.fillStyle=S.bg;ctx.fillRect(0,0,c.width,c.height);}
    ctx.drawImage(img,0,0);
    const a=document.createElement('a'); a.download=`mediatools-bgremoved.${fmt}`;
    a.href=c.toDataURL(fmt==='png'?'image/png':'image/jpeg',0.95); a.click();
  };
  img.src=S.aiBlobUrl;
}

/* ── Compare Slider ──────────────────────────────────── */
function initCompareSlider() {
  const wrap=document.getElementById('compareWrap');
  const handle=document.getElementById('compareHandle');
  if(!wrap||!handle) return;
  let dragging=false;
  const move=clientX=>{ const r=wrap.getBoundingClientRect(); _setCompareSplit(Math.max(0,Math.min(100,((clientX-r.left)/r.width)*100))); };
  handle.addEventListener('mousedown',  e=>{dragging=true;e.preventDefault();});
  handle.addEventListener('touchstart', ()=>dragging=true,{passive:true});
  document.addEventListener('mousemove', e=>{if(dragging)move(e.clientX);});
  document.addEventListener('touchmove', e=>{if(dragging)move(e.touches[0].clientX);},{passive:true});
  document.addEventListener('mouseup',  ()=>dragging=false);
  document.addEventListener('touchend', ()=>dragging=false);
  wrap.addEventListener('click', e=>{ if(e.target===handle||handle.contains(e.target))return; move(e.clientX); });
}
function _setCompareSplit(pct) {
  const a=document.getElementById('compareAfter'); if(a) a.style.clipPath=`inset(0 ${100-pct}% 0 0)`;
  const h=document.getElementById('compareHandle'); if(h) h.style.left=`${pct}%`;
}

/* ══════════════════════════════════════════════
   MULTI (batch)
══════════════════════════════════════════════ */
export function buildMultiCard(id, file) {
  const grid=document.getElementById('multiGrid'); if(!grid) return null;

  /* FIX: build img in JS so onload runs in module scope */
  const blobUrl=URL.createObjectURL(file);
  const thumbImg=document.createElement('img');
  thumbImg.style.cssText='width:100%;height:100%;object-fit:contain;';
  thumbImg.alt=''; thumbImg.src=blobUrl;

  const spinner=document.createElement('div');
  spinner.className='bgr-mc-status'; spinner.id=`mcs-${id}`;
  spinner.innerHTML=`<div class="bgr-proc-spinner" style="width:40px;height:40px;position:relative;"></div><span style="font-size:11px;color:var(--bgr-text-muted);">Memproses…</span>`;

  const wrap=document.createElement('div');
  wrap.className='bgr-mc-thumb-wrap bgr-checker';
  wrap.appendChild(thumbImg); wrap.appendChild(spinner);

  const footer=document.createElement('div'); footer.className='bgr-mc-footer';
  const fname=document.createElement('div'); fname.className='bgr-mc-fname'; fname.textContent=file.name;
  const btnsWrap=document.createElement('div'); btnsWrap.className='bgr-mc-btns'; btnsWrap.id=`mcb-${id}`;
  footer.appendChild(fname); footer.appendChild(btnsWrap);

  const card=document.createElement('div');
  card.className='bgr-multi-card'; card.id=`mc-${id}`;
  card.appendChild(wrap); card.appendChild(footer);
  grid.appendChild(card);
  return card;
}

export function setCardProgress(id, pct) {
  const el=document.getElementById(`mcs-${id}`); if(!el) return;
  const p=el.querySelector('.mc-pct'); if(p) p.textContent=`${Math.round(pct)}%`;
}

export function setCardDone(id, origFile, aiBlob) {
  const status=document.getElementById(`mcs-${id}`); if(status) status.remove();
  const thumb=document.querySelector(`#mc-${id} .bgr-mc-thumb-wrap img`);
  if(thumb&&aiBlob){ const url=URL.createObjectURL(aiBlob); thumb.src=url; S.results.set(id,{origFile,aiBlob,url}); }

  const btns=document.getElementById(`mcb-${id}`); if(!btns) return;

  /* FIX: use addEventListener instead of onclick string attributes in innerHTML */
  const editBtn=document.createElement('button'); editBtn.className='primary'; editBtn.type='button';
  editBtn.innerHTML='<i class="fa-solid fa-paintbrush"></i> Edit';
  editBtn.addEventListener('click',()=>{ const e=S.results.get(id); if(e) window._bgrOpenEditor(e.origFile,e.aiBlob,'multi'); });

  const pngBtn=document.createElement('button'); pngBtn.type='button'; pngBtn.textContent='PNG';
  pngBtn.addEventListener('click',()=>_dlCard(id,'png'));
  const jpgBtn=document.createElement('button'); jpgBtn.type='button'; jpgBtn.textContent='JPG';
  jpgBtn.addEventListener('click',()=>_dlCard(id,'jpg'));
  btns.appendChild(editBtn); btns.appendChild(pngBtn); btns.appendChild(jpgBtn);
}

export function setCardError(id, msg) {
  const s=document.getElementById(`mcs-${id}`); if(!s) return;
  s.innerHTML=`<i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:20px;"></i><span style="font-size:10px;color:#ef4444;">${(msg??'Error').slice(0,40)}</span>`;
}

function _dlCard(id,fmt) {
  const e=S.results.get(id); if(!e) return;
  const img=new Image();
  img.onload=()=>{
    const c=document.createElement('canvas'); c.width=img.naturalWidth; c.height=img.naturalHeight;
    const ctx=c.getContext('2d');
    if(fmt==='jpg'){ctx.fillStyle=S.bg!=='transparent'?S.bg:'#ffffff';ctx.fillRect(0,0,c.width,c.height);}
    ctx.drawImage(img,0,0);
    const a=document.createElement('a'); a.download=`mediatools-${id}.${fmt}`;
    a.href=c.toDataURL(fmt==='png'?'image/png':'image/jpeg',0.93); a.click();
  };
  img.src=e.url;
}

function bindBulkActions() {
  document.getElementById('btnClearAll')?.addEventListener('click',()=>{
    document.getElementById('multiGrid').innerHTML='';
    S.results.forEach(r=>URL.revokeObjectURL(r.url)); S.results.clear(); setUIState('upload');
  });
  document.getElementById('btnAddMore')?.addEventListener('click',()=>document.getElementById('fileInput')?.click());
  document.getElementById('btnDownloadZip')?.addEventListener('click',_dlZip);
  document.getElementById('btnDownloadPdfAll')?.addEventListener('click',_dlMultiPdf);
}

async function _dlZip() {
  if(typeof JSZip==='undefined'){toast('JSZip tidak tersedia','error');return;}
  const zip=new JSZip();
  for(const[id,e]of S.results){ const arr=await fetch(e.url).then(r=>r.arrayBuffer()); zip.file(`${e.origFile.name.replace(/\.[^.]+$/,'')}_nobg.png`,arr); }
  const blob=await zip.generateAsync({type:'blob'});
  const a=document.createElement('a'); a.download='mediatools-bgremoved.zip'; a.href=URL.createObjectURL(blob); a.click();
}

async function _dlMultiPdf() {
  if(typeof window.jspdf==='undefined'){toast('jsPDF tidak tersedia','error');return;}
  const {jsPDF}=window.jspdf;
  const doc=new jsPDF({orientation:'portrait',unit:'mm',format:'a4'}); let first=true;
  for(const[,e]of S.results){ if(!first)doc.addPage(); first=false; doc.addImage(e.url,'PNG',5,5,200,287); }
  doc.save('mediatools-bgremoved.pdf');
}

/* ══════════════════════════════════════════════
   PAS FOTO
══════════════════════════════════════════════ */
function bindPfDropzone() {
  const zone=document.getElementById('pfDropzone');
  const input=document.getElementById('pfFileInput');
  const browse=document.getElementById('pfBtnBrowse');
  if(!zone) return;
  browse?.addEventListener('click',e=>{e.stopPropagation();input?.click();});
  zone.addEventListener('click',()=>input?.click());
  input?.addEventListener('change',e=>{if(e.target.files?.[0]) loadPfFile(e.target.files[0]);});
  zone.addEventListener('dragenter',e=>{e.preventDefault();zone.classList.add('drag-over');});
  zone.addEventListener('dragover', e=>e.preventDefault());
  zone.addEventListener('dragleave',e=>{if(!zone.contains(e.relatedTarget))zone.classList.remove('drag-over');});
  zone.addEventListener('drop',e=>{e.preventDefault();zone.classList.remove('drag-over');const f=e.dataTransfer.files?.[0];if(f)loadPfFile(f);});
}

export function loadPfFile(file) {
  if(!ACCEPTED.has(file.type)){toast('Format tidak didukung: '+file.name,'error');return;}
  if(file.size>MAX_BYTES){toast('File terlalu besar (maks 20 MB)','error');return;}
  S.pf.origFile=file;
  const url=URL.createObjectURL(file);
  setProcessingThumb(file);
  setUIState('pf-crop');
  if(S.pf.cropper){try{S.pf.cropper.destroy();}catch(_){}S.pf.cropper=null;}
  const img=document.getElementById('pfCropImg'); if(!img) return;
  img.src=url;
  img.onload=()=>{
    const size=PHOTO_SIZES[S.pf.selectedSize]||PHOTO_SIZES['3x4'];
    S.pf.cropper=new Cropper(img,{aspectRatio:size.ratio,viewMode:1,dragMode:'move',autoCropArea:0.85,restore:false,guides:true,center:true,highlight:false,cropBoxMovable:true,cropBoxResizable:true,toggleDragModeOnDblclick:false,crop(){renderPfLivePreview();}});
  };
}

export function renderPfLivePreview() {
  if(!S.pf.cropper) return;
  const size=PHOTO_SIZES[S.pf.selectedSize]||PHOTO_SIZES['3x4'];
  const W=120, H=Math.round(W/size.ratio);
  const canvas=document.getElementById('pfLivePreview'); if(!canvas) return;
  canvas.width=W; canvas.height=H;
  const cropped=S.pf.cropper.getCroppedCanvas({width:W,height:H}); if(!cropped) return;
  const ctx=canvas.getContext('2d');
  ctx.fillStyle=PF_BG_COLORS[S.pf.selectedBg]?.hex??'#cc0000';
  ctx.fillRect(0,0,W,H); ctx.drawImage(cropped,0,0,W,H);
  const frame=document.getElementById('pfPreviewFrame');
  if(frame){frame.style.width=W+'px';frame.style.height=H+'px';}
}

function bindPfControls() {
  document.getElementById('pfSizePicker')?.addEventListener('click',e=>{
    const btn=e.target.closest('[data-size]');if(!btn)return;
    document.querySelectorAll('#pfSizePicker .bgr-pf-size-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active'); S.pf.selectedSize=btn.dataset.size;
    if(S.pf.cropper) S.pf.cropper.setAspectRatio(PHOTO_SIZES[S.pf.selectedSize]?.ratio??3/4);
    renderPfLivePreview();
  });
  document.getElementById('pfBgPicker')?.addEventListener('click',e=>{
    const btn=e.target.closest('[data-bg]');if(!btn)return;
    document.querySelectorAll('#pfBgPicker .bgr-pf-bg-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active'); S.pf.selectedBg=btn.dataset.bg; renderPfLivePreview();
  });
  document.getElementById('pfQualityBtns')?.addEventListener('click',e=>{
    const btn=e.target.closest('[data-q]');if(!btn)return;
    document.querySelectorAll('#pfQualityBtns .bgr-q-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active'); S.pf.quality=btn.dataset.q;
  });
  document.getElementById('pfBackToUpload')?.addEventListener('click',()=>{_resetPfState();setUIState('pf-upload');});
  document.getElementById('pfBtnProcess')?.addEventListener('click',()=>{ if(S.pf.cropper&&S.pf.origFile) window._pfStartProcess?.(); });
}

export function setResultViewPf({resultDataURL,resultSizeKB,photoSize,aiImg}) {
  S.pf.aiImg=aiImg; S.pf.resultDataURL=resultDataURL; S.pf.resultSizeKB=resultSizeKB; S.pf.photoSize=photoSize;
  const img=document.getElementById('pfResultImg'); if(img) img.src=resultDataURL;
  const cs=document.getElementById('pfChipSize'); if(cs) cs.textContent=photoSize?.label??'—';
  const ck=document.getElementById('pfChipBytes'); if(ck) ck.textContent=resultSizeKB?`${resultSizeKB} KB`:'—';
  const cb=document.getElementById('pfChipBg'); if(cb) cb.textContent=PF_BG_COLORS[S.pf.selectedBg]?.label??'—';
  _buildPdfGrid(S.pf.selectedSize);
  document.querySelectorAll('#pfResultBgPicker .bgr-pf-bg-btn').forEach(b=>b.classList.toggle('active',b.dataset.bg===S.pf.selectedBg));
}

function _buildPdfGrid(key) {
  const maxMap={'2x3':16,'3x4':9,'4x6':4}; const max=maxMap[key]??9;
  const grid=document.getElementById('pfPdfCountGrid'); if(!grid) return;
  grid.innerHTML='';
  const opts=[1,2,Math.floor(max/2),max].filter((v,i,a)=>a.indexOf(v)===i&&v<=max);
  opts.forEach((count,idx)=>{
    const btn=document.createElement('button');
    btn.className='bgr-pdf-count-btn'+(idx===opts.length-1?' active':'');
    btn.type='button'; btn.dataset.count=count;
    btn.innerHTML=`<span>${count}</span><small>foto</small>`;
    btn.addEventListener('click',()=>{
      document.querySelectorAll('.bgr-pdf-count-btn').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      const l=document.getElementById('pfPdfCountLabel'); if(l) l.textContent=count;
    });
    grid.appendChild(btn);
  });
  const l=document.getElementById('pfPdfCountLabel'); if(l) l.textContent=opts[opts.length-1];
}

function bindPfResultView() {
  document.getElementById('pfResultBgPicker')?.addEventListener('click',e=>{
    const btn=e.target.closest('[data-bg]');if(!btn||!S.pf.aiImg||!S.pf.photoSize)return;
    document.querySelectorAll('#pfResultBgPicker .bgr-pf-bg-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active'); S.pf.selectedBg=btn.dataset.bg;
    const{recompositePreview}=window._pfFlow??{};
    if(recompositePreview){
      const bgHex=PF_BG_COLORS[S.pf.selectedBg]?.hex??'#cc0000';
      const nc=recompositePreview(S.pf.aiImg,bgHex,S.pf.photoSize.width,S.pf.photoSize.height);
      const nd=nc.toDataURL('image/jpeg',0.95);
      S.pf.resultDataURL=nd; S.pf.resultCanvas=nc;
      const img=document.getElementById('pfResultImg'); if(img) img.src=nd;
      const cb=document.getElementById('pfChipBg'); if(cb) cb.textContent=PF_BG_COLORS[S.pf.selectedBg]?.label??'';
    }
  });
  document.getElementById('pfDlJpg')?.addEventListener('click',()=>{
    if(!S.pf.resultDataURL) return;
    const a=document.createElement('a'); a.download=`pasfoto-${S.pf.selectedSize}-mediatools.jpg`; a.href=S.pf.resultDataURL; a.click();
  });
  document.getElementById('pfDlPng')?.addEventListener('click',()=>{
    if(!S.pf.resultCanvas) return;
    const a=document.createElement('a'); a.download=`pasfoto-${S.pf.selectedSize}-mediatools.png`; a.href=S.pf.resultCanvas.toDataURL('image/png'); a.click();
  });
  document.getElementById('pfDlPdf')?.addEventListener('click',()=>{
    const ab=document.querySelector('.bgr-pdf-count-btn.active');
    const count=parseInt(ab?.dataset?.count??1);
    const{generatePasFotoPDF}=window._pfFlow??{};
    if(!generatePasFotoPDF||!S.pf.resultDataURL){toast('PDF generator tidak tersedia','error');return;}
    try{generatePasFotoPDF(S.pf.resultDataURL,S.pf.selectedSize,count,PF_BG_COLORS[S.pf.selectedBg]?.label??'');}
    catch(err){toast(err?.message??'Gagal membuat PDF','error');}
  });
  document.getElementById('pfBackToCrop')?.addEventListener('click',()=>setUIState('pf-crop'));
  document.getElementById('pfNewPhoto')?.addEventListener('click',()=>{_resetPfState();setUIState('pf-upload');});
}

/* ── Editor Toolbar ──────────────────────────────────── */
function bindEditorToolbar() {
  document.getElementById('btnEditorBack')?.addEventListener('click',()=>{clearCursor();setUIState(S.editorReturnTo==='multi'?'multi':'result');});
  document.querySelectorAll('[data-tool]').forEach(btn=>{
    btn.addEventListener('click',()=>{document.querySelectorAll('[data-tool]').forEach(b=>b.classList.remove('active'));btn.classList.add('active');S.ed.tool=btn.dataset.tool;});
  });
  const sl=document.getElementById('brushSizeSlider');
  const vl=document.getElementById('brushSizeVal');
  sl?.addEventListener('input',()=>{S.ed.brushSize=parseInt(sl.value);if(vl)vl.textContent=sl.value+'px';});
  document.getElementById('btnUndo')?.addEventListener('click',()=>undo());
  document.getElementById('btnRedo')?.addEventListener('click',()=>redo());
  document.getElementById('btnEditReset')?.addEventListener('click',()=>resetToAI());
  document.getElementById('btnDownloadPNG')?.addEventListener('click',()=>_dlEditor('png'));
  document.getElementById('btnDownloadJPG')?.addEventListener('click',()=>_dlEditor('jpg'));
}

function _dlEditor(fmt) {
  const canvas=document.getElementById('displayCanvas'); if(!canvas) return;
  const tmp=document.createElement('canvas'); tmp.width=canvas.width; tmp.height=canvas.height;
  const ctx=tmp.getContext('2d');
  if(fmt==='jpg'){ctx.fillStyle=S.bg!=='transparent'?S.bg:'#ffffff';ctx.fillRect(0,0,tmp.width,tmp.height);}
  ctx.drawImage(canvas,0,0);
  const a=document.createElement('a'); a.download=`mediatools-edited.${fmt}`;
  a.href=tmp.toDataURL(fmt==='png'?'image/png':'image/jpeg',0.95); a.click();
}

export function updateEditorButtons(canUndo,canRedo) {
  const u=document.getElementById('btnUndo'); if(u) u.disabled=!canUndo;
  const r=document.getElementById('btnRedo'); if(r) r.disabled=!canRedo;
}

/* ── Keyboard ────────────────────────────────────────── */
function bindKeyboard() {
  document.addEventListener('keydown',e=>{
    if(S.mode!=='editor') return;
    if((e.ctrlKey||e.metaKey)&&e.key==='z'){e.preventDefault();undo();}
    if((e.ctrlKey||e.metaKey)&&(e.key==='y'||(e.shiftKey&&e.key==='z'))){e.preventDefault();redo();}
  });
}

/* ── Toast ───────────────────────────────────────────── */
let _toastTimer=null;
export function toast(msg,type='success') {
  const el=document.getElementById('bgrToast');
  const msgEl=document.getElementById('bgrToastMsg');
  const ico=document.getElementById('bgrToastIco');
  if(!el||!msgEl) return;
  msgEl.textContent=msg;
  el.classList.toggle('error',type==='error');
  ico.className=type==='error'?'fa-solid fa-triangle-exclamation':'fa-solid fa-check';
  el.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer=setTimeout(()=>el.classList.remove('show'),3200);
}