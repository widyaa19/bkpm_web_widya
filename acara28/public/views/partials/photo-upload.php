<div class="photo-upload">
  <label for="foto-mahasiswa">Foto mahasiswa</label>
  <input
    id="foto-mahasiswa"
    name="foto"
    type="file"
    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
    aria-describedby="foto-mahasiswa-help"
  >
  <small id="foto-mahasiswa-help">JPG atau PNG, maksimal 2 MB. Kosongkan jika tidak ingin mengganti foto.</small>
  <div class="photo-preview" id="foto-preview-container" hidden>
    <img id="foto-preview" alt="" width="96" height="96">
    <span id="foto-preview-label"></span>
  </div>
</div>
