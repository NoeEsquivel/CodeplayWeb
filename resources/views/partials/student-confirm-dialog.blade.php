<style>
dialog.student-confirm{width:min(540px,calc(100% - 28px));padding:25px;border:1px solid #69dfba;border-radius:19px;background:linear-gradient(145deg,#0d2b34,#071a22);color:#edf9f6;box-shadow:0 28px 80px #000b;font:16px/1.6 Roboto,system-ui,sans-serif}
dialog.student-confirm::backdrop{background:#020c11d9;backdrop-filter:blur(6px)}
.student-confirm-heading{display:flex;align-items:center;gap:15px}.student-confirm-icon{display:grid;place-items:center;flex-shrink:0;width:56px;height:56px;border-radius:16px;background:linear-gradient(145deg,#ffd463,#ff8a79);color:#281803;font-size:27px;font-weight:900}
.student-confirm-heading small{color:#f2c867;font-size:13px;font-weight:900;letter-spacing:.12em}.student-confirm-heading h2{margin:2px 0;font:500 28px/1.2 Georgia,serif;color:#edf9f6}
.student-confirm>p{margin:20px 0;color:#c8d9d7;overflow-wrap:anywhere}.student-confirm-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap}
.student-confirm-actions button{padding:10px 15px;border:1px solid #526f75;border-radius:10px;background:#102831;color:#eafaf6;font:700 15px/1.4 Roboto,system-ui,sans-serif;cursor:pointer}
.student-confirm-actions .student-confirm-accept{border-color:#ad5961;background:#351f27;color:#ffc8cd}.student-confirm-actions button:focus-visible{outline:2px solid #ffdc86;outline-offset:3px}.student-confirm-actions button:disabled{opacity:.5;cursor:wait}
</style>
<dialog class="student-confirm" id="student-confirm-dialog" aria-labelledby="student-confirm-title" aria-describedby="student-confirm-message">
    <div class="student-confirm-heading"><span class="student-confirm-icon" aria-hidden="true">?</span><div><small>CONFIRMACIÓN</small><h2 id="student-confirm-title"></h2></div></div>
    <p id="student-confirm-message"></p>
    <div class="student-confirm-actions">
        <button type="button" id="student-confirm-cancel" autofocus>Cancelar</button>
        <button type="button" id="student-confirm-accept" class="student-confirm-accept"></button>
    </div>
</dialog>
<script>
(() => {
    const dialog = document.getElementById('student-confirm-dialog');
    const accept = document.getElementById('student-confirm-accept');
    const approved = new WeakSet();
    let pending = null;
    let submitter = null;

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-title')) return;
        if (approved.has(form)) { approved.delete(form); return; }
        event.preventDefault();
        if (dialog.open) return;
        pending = form;
        submitter = event.submitter;
        document.getElementById('student-confirm-title').textContent = form.dataset.confirmTitle;
        document.getElementById('student-confirm-message').textContent = form.dataset.confirmMessage;
        accept.textContent = form.dataset.confirmAccept;
        accept.disabled = false;
        dialog.showModal();
    });

    document.getElementById('student-confirm-cancel').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { pending = null; submitter = null; });
    accept.addEventListener('click', () => {
        if (!pending || accept.disabled) return;
        const form = pending;
        const button = submitter;
        accept.disabled = true;
        dialog.close();
        approved.add(form);
        form.requestSubmit(button || undefined);
    });
})();
</script>
