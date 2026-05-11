  </div><!-- /page-content -->
</main>
<script>
(function tick(){const el=document.getElementById('clock');if(el)el.textContent=new Date().toLocaleTimeString('en-MY',{hour:'2-digit',minute:'2-digit'});setTimeout(tick,1000)})();
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-overlay').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
function previewImg(pid,url){const el=document.getElementById(pid);if(!el)return;if(url){el.src=url;el.style.display='block';el.onerror=()=>el.style.display='none'}else el.style.display='none'}
</script>
</body></html>
