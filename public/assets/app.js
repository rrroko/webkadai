// 5MB超なら自動縮小（JPEG）
const input = document.getElementById('image');
if (input) {
  input.addEventListener('change', async () => {
    const file = input.files && input.files[0];
    if (!file) return;
    if (file.size <= 5 * 1024 * 1024) return;

    const bitmap = await createImageBitmap(file);
    const maxSide = 1600;
    const scale = Math.min(1, maxSide / Math.max(bitmap.width, bitmap.height));
    const w = Math.round(bitmap.width * scale);
    const h = Math.round(bitmap.height * scale);

    const canvas = Object.assign(document.createElement('canvas'), {width: w, height: h});
    const ctx = canvas.getContext('2d');
    ctx.drawImage(bitmap, 0, 0, w, h);

    let quality = 0.9; let blob;
    for (; quality >= 0.4; quality -= 0.1) {
      blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', quality));
      if (blob && blob.size <= 5 * 1024 * 1024) break;
    }
    if (!blob) return;

    const dt = new DataTransfer();
    const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
    dt.items.add(new File([blob], name, {type: 'image/jpeg'}));
    input.files = dt.files;
    alert('画像を自動縮小しました（' + (blob.size/1024/1024).toFixed(2) + 'MB）');
  });
}


// === Lightbox for images ===
document.addEventListener('click', (e) => {
  const img = e.target.closest('.thumb img, img[src^="/image/"]');
  if (!img) return;
  const src = img.getAttribute('src');
  const ov = document.createElement('div');
  ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;z-index:9999;padding:24px;cursor:zoom-out;';
  const big = new Image();
  big.src = src;
  big.style.cssText = 'max-width:min(1200px,95vw);max-height:90vh;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.5);';
  ov.appendChild(big);
  const close = () => ov.remove();
  ov.addEventListener('click', close);
  document.addEventListener('keydown', function esc(ev){ if(ev.key==='Escape'){ close(); document.removeEventListener('keydown', esc); }});
  document.body.appendChild(ov);
});

// === Drag & drop upload to #post-form ===
(function(){
  const form = document.getElementById('post-form');
  const fileInput = document.getElementById('image');
  if (!form || !fileInput) return;
  ['dragenter','dragover'].forEach(ev => form.addEventListener(ev, e => { e.preventDefault(); form.classList.add('drop'); }));
  ;['dragleave','dragend','drop'].forEach(ev => form.addEventListener(ev, e => { if(ev!=='drop') e.preventDefault(); form.classList.remove('drop'); }));
  form.addEventListener('drop', (e) => {
    e.preventDefault();
    const files = [...(e.dataTransfer?.files||[])].filter(f => /^image\//.test(f.type));
    if (files[0]) {
      const dt = new DataTransfer();
      dt.items.add(files[0]);
      fileInput.files = dt.files;
      fileInput.dispatchEvent(new Event('change'));
    }
  });
})();

// === Reactions (like/stamp) ===
(function(){
  const key = (id, kind) => `reacted:${id}:${kind}`;
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.reactions .react');
    if (!btn) return;
    const wrap = btn.closest('.reactions'); if (!wrap) return;
    const id = wrap.dataset.id;
    const kind = btn.dataset.kind;
    if (!id || !kind) return;
    if (localStorage.getItem(key(id, kind))) return; // already reacted

    btn.disabled = true;
    try{
      const res = await fetch('/react.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({id, kind})
      });
      const json = await res.json();
      if (json && json.ok){
        const cnt = btn.querySelector('.cnt');
        if (cnt) cnt.textContent = String(json.count);
        btn.classList.add('reacted');
        localStorage.setItem(key(id, kind), '1');
      } else {
        alert('送信に失敗しました');
      }
    }catch(err){
      console.error(err);
      alert('通信エラー');
    }finally{
      btn.disabled = false;
    }
  });
})();
