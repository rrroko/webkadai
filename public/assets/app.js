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
