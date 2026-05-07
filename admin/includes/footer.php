</div><!-- /page-content -->
</main>

<script>
// Clock
(function tick(){
    document.getElementById('clock').textContent =
        new Date().toLocaleTimeString('en-MY',{hour:'2-digit',minute:'2-digit'});
    setTimeout(tick,1000);
})();

// Modals
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target===m) m.classList.remove('open'); });
});

// Image preview
function previewImg(previewId, url) {
    const el = document.getElementById(previewId);
    if (!el) return;
    if (url) { el.src = url; el.style.display='block'; el.onerror=()=>el.style.display='none'; }
    else { el.style.display='none'; }
}
</script>
</body>
</html>
