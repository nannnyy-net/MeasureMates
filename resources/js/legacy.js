// Legacy interactive behaviors for the single volume converter and ingredient notes.
// These helpers are loaded via Vite and kept outside Blade templates.

let lastResult = '';
let lastPhrase = '';

function normalizeUnitOrder() {
    const fromSelect = document.getElementById('fromUnit');
    const toSelect = document.getElementById('toUnit');
    if (!fromSelect || !toSelect) return;

    const fromUnit = fromSelect.value;
    const toUnit = toSelect.value;
    if (!fromUnit || !toUnit || fromUnit === toUnit) return;

    const mlByUnit = {
        ml: 1.0,
        tsp: 4.92892159375,
        tbsp: 14.78676478125,
        floz: 29.5735295625,
        cup: 236.5882365,
        pint: 473.176473015625,
        quart: 946.35294603125,
        liter: 1000.0,
        gallon: 3785.411784125,
    };

    const fromMl = mlByUnit[fromUnit];
    const toMl = mlByUnit[toUnit];
    if (!Number.isFinite(fromMl) || !Number.isFinite(toMl)) return;

    if (fromMl > toMl) {
        const temp = fromSelect.value;
        fromSelect.value = toSelect.value;
        toSelect.value = temp;
    }
}

function swapUnits() {
    const fromSelect = document.getElementById('fromUnit');
    const toSelect = document.getElementById('toUnit');
    if (!fromSelect || !toSelect) return;

    const temp = fromSelect.value;
    fromSelect.value = toSelect.value;
    toSelect.value = temp;
    normalizeUnitOrder();
    showNotification('Units swapped.');
}

function resetConverter() {
    const amountInput = document.getElementById('amount');
    const resultValue = document.getElementById('resultValue');
    const phraseValue = document.getElementById('phraseValue');
    const errorBlock = document.getElementById('converterError');
    if (amountInput) amountInput.value = '1';
    if (resultValue) resultValue.innerText = '--';
    if (phraseValue) phraseValue.innerText = 'Select units and amount to convert.';
    if (errorBlock) errorBlock.classList.add('hidden');

    lastResult = '';
    lastPhrase = '';
    normalizeUnitOrder();
    showNotification('Converter reset.');
}

async function convertVolume() {
    const amountInput = document.getElementById('amount');
    const fromSelect = document.getElementById('fromUnit');
    const toSelect = document.getElementById('toUnit');
    const errorBlock = document.getElementById('converterError');
    const errorMessage = document.getElementById('converterErrorMessage');
    const submitButton = document.getElementById('converterSubmitButton');

    if (!amountInput || !fromSelect || !toSelect || !errorBlock || !errorMessage || !submitButton) return;
    errorBlock.classList.add('hidden');
    submitButton.disabled = true;
    const originalButtonText = submitButton.innerHTML;
    submitButton.innerHTML = 'Converting...';

    const payload = {
        amount: amountInput.value,
        from_unit: fromSelect.value,
        to_unit: toSelect.value,
    };

    try {
        const response = await fetch('/convert', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Validation error');

        const resultValueEl = document.getElementById('resultValue');
        const phraseValueEl = document.getElementById('phraseValue');
        if (resultValueEl) resultValueEl.innerText = data.result;
        if (phraseValueEl) phraseValueEl.innerText = data.phrase;

        lastResult = data.result;
        lastPhrase = data.phrase;
        showNotification('Conversion complete!');
    } catch (err) {
        errorMessage.innerText = err.message || 'Conversion failed.';
        errorBlock.classList.remove('hidden');

        const resultValueEl = document.getElementById('resultValue');
        const phraseValueEl = document.getElementById('phraseValue');
        if (resultValueEl) resultValueEl.innerText = '--';
        if (phraseValueEl) phraseValueEl.innerText = 'Conversion failed.';
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
}

async function copyText(text, msg) {
    try {
        await navigator.clipboard.writeText(text);
        showNotification(msg);
    } catch {
        showNotification('Unable to copy text.');
    }
}

async function copyResult() {
    if (!lastResult) {
        showNotification('Run a conversion first.');
        return;
    }
    copyText(lastResult, 'Result copied to clipboard!');
}

async function copyPhrase() {
    if (!lastPhrase) {
        showNotification('Run a conversion first.');
        return;
    }
    copyText(lastPhrase, 'Phrase copied to clipboard!');
}

function printConversion() {
    const resultValueEl = document.getElementById('resultValue');
    const phraseValueEl = document.getElementById('phraseValue');
    const amountEl = document.getElementById('amount');
    const fromUnitEl = document.getElementById('fromUnit');
    const toUnitEl = document.getElementById('toUnit');

    const currentResult = (resultValueEl?.innerText || '').trim();
    const currentPhrase = (phraseValueEl?.innerText || '').trim();

    if (!currentResult || currentResult === '--' || !currentPhrase || currentPhrase.includes('Select units')) {
        showNotification('Please run a conversion before printing.');
        return;
    }

    fetch('/recipes/print-log', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify({
            recipe_id: null,
            item_type: 'single',
            item_name: currentPhrase,
        }),
    });

    const now = new Date();
    const printedOn = now.toLocaleString();
    const esc = (str) => String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const printArea = document.getElementById('mmPrintArea');
    if (!printArea) return;

    printArea.querySelector('#mmPrintOriginalValue').innerHTML = esc(amountEl?.value || '—');
    printArea.querySelector('#mmPrintOriginalUnit').innerHTML = esc(fromUnitEl?.value || '—');
    printArea.querySelector('#mmPrintTargetUnit').innerHTML = esc(toUnitEl?.value || '—');
    printArea.querySelector('#mmPrintFinalResult').innerHTML = esc(currentResult || '—');
    printArea.querySelector('#mmPrintIngredient').innerHTML = '—';
    printArea.querySelector('#mmPrintCalculatedAt').innerHTML = esc(printedOn);

    document.body.classList.add('mm-printing-active');
    printArea.classList.remove('hidden');

    const cleanup = () => {
        printArea.classList.add('hidden');
        document.body.classList.remove('mm-printing-active');
    };

    window.onafterprint = cleanup;
    window.print();
    setTimeout(cleanup, 1000);
}

function cookbookGetEditorState() {
    return {
        id: document.getElementById('noteId')?.value || '',
        title: document.getElementById('noteTitle')?.value || '',
        favorite: (document.getElementById('noteFavorite')?.value || '0') === '1',
        body: document.getElementById('noteBody')?.value || '',
    };
}

function cookbookUpdateCharCount() {
    const body = document.getElementById('noteBody');
    const counter = document.getElementById('notesCharCount');
    if (!body || !counter) return;
    counter.innerText = String((body.value || '').length);
}

function notesUpdateCharCount() {
    return cookbookUpdateCharCount();
}

function notesGetEditorState() {
    return cookbookGetEditorState();
}

function notesPersistDraft() {
    return cookbookPersistDraft();
}

function notesRestoreDraft() {
    return cookbookRestoreDraft();
}

function notesSave(syncToServer = true) {
    return cookbookSave(syncToServer);
}

function notesNew() {
    return cookbookNew();
}

function notesClearEditor() {
    return cookbookClearEditor();
}

function notesOnBodyInput() {
    return cookbookOnBodyInput();
}

async function cookbookOnBodyInput() {
    cookbookUpdateCharCount();
    cookbookPersistDraft();
}

async function cookbookNew() {
    if (document.getElementById('noteId')) document.getElementById('noteId').value = '';
    if (document.getElementById('noteTitle')) document.getElementById('noteTitle').value = '';
    if (document.getElementById('noteFavorite')) document.getElementById('noteFavorite').value = '0';
    if (document.getElementById('noteBody')) document.getElementById('noteBody').value = '';
    cookbookUpdateCharCount();
    cookbookPersistDraft();
}

async function cookbookClearEditor() {
    cookbookNew();
}

function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function printIngredientNote(noteId) {
    const titleEl = document.getElementById(`note-name-val-${noteId}`);
    const bodyEl = document.getElementById(`note-text-val-${noteId}`);
    const title = (titleEl?.innerText ?? '').trim() || 'Untitled';
    const body = (bodyEl?.innerText ?? '').trim();
    if (!body) {
        showNotification('This note has no content to print.');
        return;
    }

    const now = new Date();
    const printedOn = now.toLocaleString();
    const html =
        `<!doctype html><html><head><meta charset="utf-8"/><title>Print Ingredient Note</title><style>body{font-family:sans-serif;padding:20px;}h1{border-bottom:1px solid #000;padding-bottom:10px;}</style></head><body><h1>${escapeHtml(title)}</h1><p style="white-space:pre-wrap;">${escapeHtml(body)}</p><p style="margin-top:20px;font-size:10px;">Printed on ${escapeHtml(printedOn)}</p></body></html>`;
    const printWin = window.open('', '_blank');
    if (printWin) {
        printWin.document.open();
        printWin.document.write(html);
        printWin.document.close();
        setTimeout(() => {
            printWin.print();
            printWin.close();
        }, 250);
    }
}

function cookbookPersistDraft() {
    const st = cookbookGetEditorState();
    const payload = {
        id: st.id,
        title: st.title,
        favorite: st.favorite,
        body: st.body,
    };
    localStorage.setItem('mm_ingredient_notes_draft_chef', JSON.stringify(payload));
}

function cookbookRestoreDraft() {
    const raw = localStorage.getItem('mm_ingredient_notes_draft_chef');
    if (!raw) return;
    try {
        const draft = JSON.parse(raw);
        if (document.getElementById('noteId')) document.getElementById('noteId').value = draft.id || '';
        if (document.getElementById('noteTitle')) document.getElementById('noteTitle').value = draft.title || '';
        if (document.getElementById('noteFavorite')) document.getElementById('noteFavorite').value = draft.favorite ? '1' : '0';
        if (document.getElementById('noteBody')) document.getElementById('noteBody').value = draft.body || '';
        notesUpdateCharCount();
    } catch (e) {}
}

async function cookbookSave(syncToServer = true) {
    const st = cookbookGetEditorState();
    const trimmedTitle = (st.title || '').trim();
    const trimmedBody = (st.body || '').trim();
    if (!trimmedTitle && !trimmedBody) {
        showNotification('Add a title or note content before saving.');
        return;
    }

    notesPersistDraft();
    if (!syncToServer) return;

    const payload = {
        ingredient_name: trimmedTitle || '(Untitled)',
        notes: trimmedBody,
        is_favorite: st.favorite,
    };
    const url = st.id ? `/notes/${st.id}` : '/notes';
    const method = st.id ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify(payload),
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Save failed');

        showNotification('Note saved successfully!');
        window.location.reload();
    } catch (e) {
        showNotification(e.message || 'Save failed.');
    }
}

async function deleteNote(id) {
    const card = document.getElementById(`note-card-${id}`);
    const ok = confirm('Are you sure you want to delete this note?');
    if (!ok) return;

    try {
        const response = await fetch(`/notes/${id}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) throw new Error(data.message || 'Delete failed');

        card?.remove();
        showNotification('Note deleted.');

        if (document.getElementById('noteId')?.value === String(id)) {
            notesNew();
        }

        const list = document.getElementById('notesListContainer');
        if (list) {
            const remaining = list.querySelectorAll('[id^="note-card-"]').length;
            if (remaining === 0) {
                list.innerHTML =
                    '<div id="noNotesMessage" class="text-xs text-text-muted py-6 text-center">No notes found. Create your first one above.</div>';
            }
        }
    } catch (e) {
        showNotification('Error deleting note.');
    }
}

async function notesLoadNoteToEditor(id) {
    const title = document.getElementById(`note-name-val-${id}`)?.innerText || '';
    const body = document.getElementById(`note-text-val-${id}`)?.innerText || '';
    const isFav = document.getElementById(`note-card-${id}`)?.getAttribute('data-favorite') === '1';

    if (document.getElementById('noteId')) document.getElementById('noteId').value = id;
    if (document.getElementById('noteTitle')) document.getElementById('noteTitle').value = title;
    if (document.getElementById('noteBody')) document.getElementById('noteBody').value = body;
    if (document.getElementById('noteFavorite')) document.getElementById('noteFavorite').value = isFav ? '1' : '0';
    notesUpdateCharCount();
}

async function toggleNoteFavorite(noteId) {
    const card = document.getElementById(`note-card-${noteId}`);
    const isFav = card?.getAttribute('data-favorite') === '1';
    const newVal = !isFav;

    const title = document.getElementById(`note-name-val-${noteId}`)?.innerText || '';
    const body = document.getElementById(`note-text-val-${noteId}`)?.innerText || '';

    try {
        const response = await fetch(`/notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({
                ingredient_name: title,
                notes: body,
                is_favorite: newVal ? 1 : 0,
            }),
        });
        if (response.ok) {
            showNotification('Favorite status updated.');
            window.location.reload();
        }
    } catch (e) {
        showNotification('Error updating favorite.');
    }
}

function filterCookbook() {
    const query = (document.getElementById('notesSearch')?.value || '').toLowerCase().trim();
    document.querySelectorAll('#notesListContainer > div[id^="note-card-"]').forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(query) ? 'flex' : 'none';
    });
}

function notesInstallShortcuts() {
    document.addEventListener('keydown', (e) => {
        const ctrlOrCmd = e.ctrlKey || e.metaKey;
        if (ctrlOrCmd && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            notesSave(true);
        }
    });
}

function showNotification(msg) {
    const notification = document.createElement('div');
    notification.className =
        'fixed bottom-5 right-5 bg-accent-primary text-bg-primary text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg z-50 transition-all duration-300';
    notification.innerText = msg;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

cookbookRestoreDraft();
notesInstallShortcuts();
