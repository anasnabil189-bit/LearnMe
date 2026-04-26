{{-- ============================================================
     SHARED BLOCK EDITOR SCRIPTS & STYLES
     Used by create.blade.php and edit.blade.php
     ============================================================ --}}

<style>
.block-wrapper {
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    background: var(--bg2);
    transition: box-shadow 0.2s, border-color 0.2s;
}
.block-wrapper:hover { border-color: var(--primary); box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
.block-header {
    display: flex; align-items: center; gap:10px;
    padding: 10px 16px;
    background: var(--bg3);
    border-bottom: 1px solid var(--border);
    cursor: grab;
    user-select: none;
}
.block-header:active { cursor: grabbing; }
.block-badge {
    font-size: 11px; font-weight: 700; letter-spacing: 0.5px;
    padding: 3px 9px; border-radius: 99px;
}
.badge-text  { background: rgba(20,184,166,0.15); color: var(--primary-light); }
.badge-image { background: rgba(245,158,11,0.15);  color: var(--accent); }
.block-body { padding: 16px; }
.block-textarea {
    width: 100%; min-height: 120px; resize: vertical;
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 10px; color: var(--text);
    font-family: 'Tajawal', sans-serif; font-size: 16px;
    line-height: 1.7; padding: 12px 15px;
    outline: none; transition: border-color 0.2s;
    direction: rtl;
}
.block-textarea:focus { border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(20,184,166,0.15); }
.block-img-preview {
    width: 100%; max-height: 250px; object-fit: contain;
    border-radius: 8px; margin-top: 10px;
    background: var(--bg3); border: 1px solid var(--border);
}
.block-move-btn {
    width: 28px; height: 28px; border-radius: 6px;
    border: 1px solid var(--border); background: var(--bg2);
    color: var(--text-muted); cursor: pointer; font-size: 14px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.block-move-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }
.block-del-btn {
    margin-right: auto;
    width: 28px; height: 28px; border-radius: 6px;
    border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.1);
    color: var(--danger); cursor: pointer; font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.block-del-btn:hover { background: var(--danger); color: white; }
.drag-over { border: 2px dashed var(--primary) !important; opacity: 0.7; }
</style>

<script>
// ─── State ────────────────────────────────────────────────────
let blocks = [];        // { type, content, existing_path, file_index, _previewUrl }
let fileMap = {};       // file_index → File object
let fileCounter = 0;

// ─── Init ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    @if(isset($editMode) && $editMode)
    if (typeof existingBlocks !== 'undefined') {
        blocks = existingBlocks.map(b => ({
            type: b.type,
            content: b.content || '',
            existing_path: b.existing_path || null,
            _previewUrl: b.existing_path ? '/storage/' + b.existing_path : null,
            file_index: null,
        }));
    }
    @else
    // Check for AI Draft in sessionStorage
    const draftJson = sessionStorage.getItem('ai_lesson_draft');
    if (draftJson) {
        try {
            const draft = JSON.parse(draftJson);
            if (draft.title && document.getElementById('title')) {
                document.getElementById('title').value = draft.title;
            }
            if (draft.blocks && Array.isArray(draft.blocks)) {
                blocks = draft.blocks.map(b => ({
                    type: b.type || 'text',
                    content: b.content || '',
                    existing_path: null,
                    _previewUrl: null,
                    file_index: null,
                }));
            }
        } catch(e) {
            console.error('Failed to parse AI draft', e);
        }
        sessionStorage.removeItem('ai_lesson_draft');
    }
    @endif
    renderBlocks();
});

// ─── Add Blocks ──────────────────────────────────────────────
function addTextBlock() {
    blocks.push({ type: 'text', content: '', existing_path: null, file_index: null });
    renderBlocks();
    // Focus last textarea
    setTimeout(() => {
        const areas = document.querySelectorAll('.block-textarea');
        if (areas.length) areas[areas.length - 1].focus();
    }, 50);
}

function addImageBlock() {
    blocks.push({ type: 'image', content: null, existing_path: null, _previewUrl: null, file_index: null });
    renderBlocks();
}

// ─── Delete ──────────────────────────────────────────────────
function deleteBlock(idx) {
    if (blocks[idx].file_index !== null) delete fileMap[blocks[idx].file_index];
    blocks.splice(idx, 1);
    renderBlocks();
}

// ─── Move ────────────────────────────────────────────────────
function moveBlock(idx, dir) {
    const newIdx = idx + dir;
    if (newIdx < 0 || newIdx >= blocks.length) return;
    [blocks[idx], blocks[newIdx]] = [blocks[newIdx], blocks[idx]];
    renderBlocks();
}

// ─── Render ──────────────────────────────────────────────────
function renderBlocks() {
    const container = document.getElementById('blocks-container');
    container.innerHTML = '';

    blocks.forEach((block, idx) => {
        const wrap = document.createElement('div');
        wrap.className = 'block-wrapper';
        wrap.draggable = true;
        wrap.dataset.idx = idx;

        // Header
        const hdr = document.createElement('div');
        hdr.className = 'block-header';
        hdr.innerHTML = `
            <i class='bx bx-dots-vertical-rounded' style="font-size:18px; color:var(--text-muted);"></i>
            <span class="block-badge ${block.type === 'text' ? 'badge-text' : 'badge-image'}">
                ${block.type === 'text' ? '📝 Text' : '🖼 Image'}
            </span>
            <button type="button" class="block-move-btn" onclick="moveBlock(${idx},-1)" title="Move Up"><i class='bx bx-up-arrow-alt'></i></button>
            <button type="button" class="block-move-btn" onclick="moveBlock(${idx},1)" title="Move Down"><i class='bx bx-down-arrow-alt'></i></button>
            <button type="button" class="block-del-btn" onclick="deleteBlock(${idx})" title="Delete"><i class='bx bx-trash'></i></button>
        `;

        // Body
        const body = document.createElement('div');
        body.className = 'block-body';

        if (block.type === 'text') {
            const ta = document.createElement('textarea');
            ta.className = 'block-textarea';
            ta.placeholder = 'Write explanation here...';
            ta.value = block.content || '';
            ta.addEventListener('input', e => { blocks[idx].content = e.target.value; });
            body.appendChild(ta);
        } else {
            // Image picker + preview
            const label = document.createElement('label');
            label.style = 'display:flex; align-items:center; gap:12px; padding:14px; background:var(--bg3); border-radius:10px; border:2px dashed var(--border); cursor:pointer; transition:0.2s;';
            label.innerHTML = `<i class='bx bx-image-add' style="font-size:28px; color:var(--accent);"></i>
                <span style="color:var(--text-muted); font-size:14px;">${block._previewUrl ? 'Click to change image' : 'Click to choose image'}</span>`;
            label.style.cssText += 'transition: border-color 0.2s;';
            label.onmouseover = () => label.style.borderColor = 'var(--accent)';
            label.onmouseout  = () => label.style.borderColor = 'var(--border)';

            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.style.display = 'none';
            input.addEventListener('change', e => {
                const file = e.target.files[0];
                if (!file) return;
                const fi = fileCounter++;
                fileMap[fi] = file;
                blocks[idx].file_index = fi;
                blocks[idx]._previewUrl = URL.createObjectURL(file);
                blocks[idx].existing_path = null; // replaced
                renderBlocks();
            });
            label.appendChild(input);
            body.appendChild(label);

            if (block._previewUrl) {
                const img = document.createElement('img');
                img.src = block._previewUrl;
                img.className = 'block-img-preview';
                body.appendChild(img);
            }
        }

        wrap.appendChild(hdr);
        wrap.appendChild(body);

        // Drag-and-drop reorder
        wrap.addEventListener('dragstart', e => { e.dataTransfer.setData('text/plain', idx); wrap.style.opacity = '0.4'; });
        wrap.addEventListener('dragend',   () => { wrap.style.opacity = '1'; });
        wrap.addEventListener('dragover',  e => { e.preventDefault(); wrap.classList.add('drag-over'); });
        wrap.addEventListener('dragleave', () => { wrap.classList.remove('drag-over'); });
        wrap.addEventListener('drop', e => {
            e.preventDefault();
            wrap.classList.remove('drag-over');
            const fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
            const toIdx   = parseInt(wrap.dataset.idx);
            if (fromIdx === toIdx) return;
            const moved = blocks.splice(fromIdx, 1)[0];
            blocks.splice(toIdx, 0, moved);
            renderBlocks();
        });

        container.appendChild(wrap);
    });

    if (blocks.length === 0) {
        container.innerHTML = `<div style="text-align:center; padding:40px; color:var(--text-muted); border:2px dashed var(--border); border-radius:14px;">
            <i class='bx bx-plus-circle' style="font-size:40px; display:block; margin-bottom:10px; opacity:0.4;"></i>
            Start by adding text or an image using the buttons below
        </div>`;
    }
}

// ─── Prepare Submit ─────────────────────────────────────────
function prepareSubmit() {
    // Build blocks_json
    const payload = blocks.map(b => {
        if (b.type === 'text') return { type: 'text', content: b.content };
        return {
            type: 'image',
            file_index: b.file_index,
            existing_path: b.existing_path,
        };
    });
    document.getElementById('blocks_json').value = JSON.stringify(payload);

    // Inject file inputs for each uploaded file
    const fc = document.getElementById('file-inputs-container');
    fc.innerHTML = '';
    Object.entries(fileMap).forEach(([fi, file]) => {
        const dt = new DataTransfer();
        dt.items.add(file);
        const inp = document.createElement('input');
        inp.type = 'file';
        inp.name = `block_images[${fi}]`;
        inp.style.display = 'none';
        inp.files = dt.files;
        fc.appendChild(inp);
    });

    return true;
}
</script>
