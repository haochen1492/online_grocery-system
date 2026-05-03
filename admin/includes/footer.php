</div><!-- /page-content -->
</main>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString('en-MY', {hour:'2-digit',minute:'2-digit'});
}
updateClock();
setInterval(updateClock, 1000);

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if(e.target === m) m.classList.remove('open'); });
});

function confirmDelete(url, msg='Are you sure you want to delete this?') {
    if (confirm(msg)) window.location.href = url;
}
</script>
</body>
</html>
