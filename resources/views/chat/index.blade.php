@extends('layouts.app', ['title' => 'Team Chat', 'subtitle' => 'Internal messaging'])

@push('head-css')
<style>
    #chat-wrap {
        height: calc(100vh - 230px);
        min-height: 400px;
    }
    #chat-messages {
        scroll-behavior: smooth;
    }
    .msg-bubble {
        max-width: 65%;
        word-break: break-word;
    }
    .msg-bubble .bubble-body {
        border-radius: 1rem;
    }
    .msg-time {
        font-size: 0.68rem;
        margin-top: 3px;
        color: #8a9099;
    }
    #msg-body {
        resize: none;
        overflow: hidden;
        min-height: 38px;
        max-height: 120px;
    }
</style>
@endpush

@section('content')

<div id="chat-wrap" class="card d-flex flex-column border-0 shadow-sm">

    {{-- Header --}}
    <div class="card-header py-2 px-3 d-flex align-items-center border-bottom">
        <i class="ti ti-message-filled text-primary me-2 fs-5"></i>
        <strong class="me-auto">Team Chat</strong>
        <span class="badge bg-success-subtle text-success small" id="chat-status">
            <i class="ti ti-circle-filled me-1" style="font-size:0.5rem"></i>Live
        </span>
    </div>

    {{-- Messages area --}}
    <div id="chat-messages" class="flex-grow-1 overflow-y-auto p-3">
        <div id="no-messages" class="text-center text-muted py-5 {{ count($messages) ? 'd-none' : '' }}">
            <i class="ti ti-message-off fs-1 d-block mb-2 text-muted opacity-50"></i>
            No messages yet. Say hello!
        </div>
    </div>

    {{-- Input area --}}
    <div class="border-top p-3 flex-shrink-0">
        <form id="chat-form" autocomplete="off">
            <div id="attach-preview" class="mb-2 d-none d-flex align-items-center gap-2">
                <i class="ti ti-paperclip text-muted small"></i>
                <span class="small text-truncate" id="attach-name" style="max-width:240px"></span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-auto" id="attach-remove">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" id="attach-btn" title="Attach file (images, PDF, doc, xlsx, zip — max 5 MB)">
                    <i class="ti ti-paperclip"></i>
                </button>
                <input type="file" id="attach-file" class="d-none"
                    accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                <textarea class="form-control" id="msg-body" rows="1"
                    placeholder="Type a message… (Enter to send, Shift+Enter for new line)"></textarea>
                <button type="submit" class="btn btn-primary" id="send-btn">
                    <i class="ti ti-send"></i>
                </button>
            </div>
        </form>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
    const storeUrl     = '{{ route("chat.store") }}';
    const pollUrl      = '{{ route("chat.messages") }}';
    const csrfToken    = document.querySelector('meta[name="csrf-token"]').content;
    const currentUserId = {{ auth()->id() }};

    const messagesEl    = document.getElementById('chat-messages');
    const noMessages    = document.getElementById('no-messages');
    const form          = document.getElementById('chat-form');
    const bodyEl        = document.getElementById('msg-body');
    const attachBtn     = document.getElementById('attach-btn');
    const attachFile    = document.getElementById('attach-file');
    const attachPreview = document.getElementById('attach-preview');
    const attachNameEl  = document.getElementById('attach-name');
    const attachRemove  = document.getElementById('attach-remove');
    const sendBtn       = document.getElementById('send-btn');

    let lastId       = 0;
    let isNearBottom = true;
    const rendered   = new Set();

    // --- Render initial messages ---
    const initialMessages = @json($messages);
    initialMessages.forEach(appendMessage);
    if (initialMessages.length) {
        lastId = initialMessages[initialMessages.length - 1].id;
    }
    scrollToBottom();

    // --- Auto-resize textarea ---
    bodyEl.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    bodyEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit', {cancelable: true}));
        }
    });

    // --- File attachment ---
    attachBtn.addEventListener('click', () => attachFile.click());

    attachFile.addEventListener('change', function () {
        if (this.files.length) {
            attachNameEl.textContent = this.files[0].name;
            attachPreview.classList.remove('d-none');
        }
    });

    attachRemove.addEventListener('click', function () {
        attachFile.value = '';
        attachPreview.classList.add('d-none');
        attachNameEl.textContent = '';
    });

    // --- Near-bottom detection ---
    messagesEl.addEventListener('scroll', function () {
        isNearBottom = this.scrollHeight - this.scrollTop - this.clientHeight < 80;
    });

    // --- Append a message bubble ---
    function appendMessage(msg) {
        if (rendered.has(msg.id)) return;
        rendered.add(msg.id);

        noMessages.classList.add('d-none');

        const own  = msg.is_own;
        const wrap = document.createElement('div');
        wrap.className = 'd-flex mb-3 ' + (own ? 'justify-content-end' : 'justify-content-start');

        let attachHtml = '';
        if (msg.attachment_url) {
            if (msg.is_image) {
                attachHtml = `<div class="mt-2">
                    <a href="${msg.attachment_url}" target="_blank">
                        <img src="${msg.attachment_url}" class="img-fluid rounded" style="max-width:200px;max-height:150px;object-fit:cover">
                    </a>
                </div>`;
            } else {
                attachHtml = `<div class="mt-2">
                    <a href="${msg.attachment_url}" target="_blank" class="${own ? 'text-white' : 'text-primary'}" style="font-size:0.82rem">
                        <i class="ti ti-download me-1"></i>${escHtml(msg.attachment_name)}
                    </a>
                </div>`;
            }
        }

        const bodyHtml = msg.body
            ? `<div>${escHtml(msg.body).replace(/\n/g, '<br>')}</div>`
            : '';

        wrap.innerHTML = `
            <div class="msg-bubble">
                ${!own ? `<div class="text-muted fw-semibold mb-1" style="font-size:0.78rem">${escHtml(msg.user_name)}</div>` : ''}
                <div class="bubble-body px-3 py-2 ${own ? 'bg-primary text-white' : 'bg-light text-dark'}">
                    ${bodyHtml}${attachHtml}
                </div>
                <div class="msg-time ${own ? 'text-end' : ''}">${msg.created_at}</div>
            </div>
        `;

        messagesEl.appendChild(wrap);
    }

    function escHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    // --- Form submit ---
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const body = bodyEl.value.trim();
        const file = attachFile.files[0];
        if (!body && !file) return;

        sendBtn.disabled = true;

        const fd = new FormData();
        fd.append('_token', csrfToken);
        if (body) fd.append('body', body);
        if (file) fd.append('attachment', file);

        fetch(storeUrl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(function (msg) {
                if (msg.error) { alert(msg.error); return; }
                appendMessage(msg);
                lastId = msg.id;
                bodyEl.value = '';
                bodyEl.style.height = 'auto';
                attachFile.value = '';
                attachPreview.classList.add('d-none');
                attachNameEl.textContent = '';
                scrollToBottom();
            })
            .catch(() => alert('Failed to send. Please try again.'))
            .finally(() => { sendBtn.disabled = false; });
    });

    // --- Polling (3s interval) ---
    function poll() {
        fetch(pollUrl + '?since=' + lastId, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(function (msgs) {
            msgs.forEach(function (msg) {
                if (!rendered.has(msg.id)) {
                    appendMessage(msg);
                    if (isNearBottom) scrollToBottom();
                }
                lastId = Math.max(lastId, msg.id);
            });
        })
        .catch(() => {});

        setTimeout(poll, 3000);
    }

    setTimeout(poll, 3000);
})();
</script>
@endpush
