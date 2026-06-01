@extends('layouts.app', ['title' => 'Chat', 'subtitle' => isset($group) && $group ? $group->name : 'Company-Wide Chat'])

@push('head-css')
<style>
    #chat-outer {
        height: calc(100dvh - 210px);
        min-height: 300px;
    }
    #chat-sidebar {
        width: 220px;
        min-width: 220px;
        overflow-y: auto;
        border-right: 1px solid var(--bs-border-color);
    }
    #chat-sidebar a.room-link {
        display: block;
        padding: 10px 14px;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        text-decoration: none;
        color: inherit;
        transition: background .15s;
    }
    #chat-sidebar a.room-link:hover { background: var(--bs-secondary-bg); }
    #chat-sidebar a.room-link.active { background: var(--bs-primary); color: #fff !important; }
    #chat-messages { scroll-behavior: smooth; }
    .msg-bubble { max-width: 65%; word-break: break-word; }
    .msg-bubble .bubble-body { border-radius: 1rem; }
    .msg-time { font-size: 0.68rem; margin-top: 3px; color: #8a9099; }
    #msg-body { resize: none; overflow: hidden; min-height: 38px; max-height: 120px; }
    .reaction-bar { font-size: 13px; }
    .reaction-btn {
        border: 1px solid var(--bs-border-color);
        border-radius: 20px;
        padding: 1px 7px;
        cursor: pointer;
        background: var(--bs-secondary-bg);
        transition: background .12s;
        font-size: 13px;
        line-height: 1.6;
    }
    .reaction-btn.reacted { background: var(--bs-primary); color: #fff; border-color: var(--bs-primary); }
    .reaction-picker { gap: 4px; }
    .reaction-picker button { border: none; background: none; font-size: 18px; cursor: pointer; line-height: 1; padding: 2px 3px; border-radius: 4px; transition: background .1s; }
    .reaction-picker button:hover { background: var(--bs-secondary-bg); }
    #typing-bar { font-size: 0.78rem; color: #8a9099; height: 18px; }
</style>
@endpush

@section('content')

<div id="chat-outer" class="card d-flex flex-row border-0 shadow-sm overflow-hidden p-0">

    {{-- Sidebar --}}
    <div id="chat-sidebar" class="d-flex flex-column">

        {{-- Company-wide room --}}
        <a href="{{ route('chat.index') }}" class="room-link fw-semibold {{ !isset($group) || !$group ? 'active' : '' }}">
            <i class="ti ti-message-filled me-2 fs-16"></i>Company-Wide
        </a>

        {{-- Groups --}}
        @if($groups->isNotEmpty())
        <div class="px-3 pt-2 pb-1 chat-group-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#8a9099;">Groups</div>
        @foreach($groups as $g)
        <a href="{{ route('chat-groups.show', $g) }}"
           class="room-link {{ isset($group) && $group && $group->id === $g->id ? 'active' : '' }}">
            <i class="ti ti-users me-2 fs-15"></i>{{ $g->name }}
        </a>
        @endforeach
        @endif

        {{-- Spacer --}}
        <div class="flex-grow-1"></div>

        {{-- New group button (manager+) --}}
        @if(auth()->user()->isManager())
        <div class="p-2 border-top chat-new-group-btn">
            <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#newGroupModal">
                <i class="ti ti-plus me-1"></i>New Group
            </button>
        </div>
        @endif
    </div>

    {{-- Chat panel --}}
    <div class="flex-grow-1 d-flex flex-column overflow-hidden">

        {{-- Room header --}}
        <div class="px-3 py-2 border-bottom d-flex align-items-center gap-2 flex-shrink-0">
            @if(isset($group) && $group)
                <i class="ti ti-users text-primary fs-18"></i>
                <strong class="me-auto">{{ $group->name }}</strong>
                @if(auth()->user()->isManager())
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#manageMembersModal">
                    <i class="ti ti-settings fs-14 me-1"></i>Members
                </button>
                @endif
            @else
                <i class="ti ti-message-filled text-primary fs-18"></i>
                <strong class="me-auto">Company-Wide Chat</strong>
            @endif
            <span class="badge bg-success-subtle text-success small" id="chat-status">
                <i class="ti ti-circle-filled me-1" style="font-size:.5rem"></i>Live
            </span>
        </div>

        {{-- Messages --}}
        <div id="chat-messages" class="flex-grow-1 overflow-y-auto p-3">
            <div id="no-messages" class="text-center text-muted py-5 {{ count($messages) ? 'd-none' : '' }}">
                <i class="ti ti-message-off fs-1 d-block mb-2 opacity-50"></i>
                No messages yet. Say hello!
            </div>
        </div>

        {{-- Typing indicator --}}
        <div id="typing-bar" class="px-3 d-flex align-items-center"></div>

        {{-- Input --}}
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

    </div>{{-- /chat panel --}}

</div>

{{-- ======================== Modals ======================== --}}

{{-- New Group Modal (manager+ only) --}}
@if(auth()->user()->isManager())
<div class="modal fade" id="newGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('chat-groups.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Chat Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Group name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="100" autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial members</label>
                        <select name="member_ids[]" class="form-select" multiple size="5">
                            @foreach(\App\Models\User::where('id', '!=', auth()->id())->where('is_active', true)->orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role->label() }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Group</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Manage Members Modal (shown only on group pages) --}}
@if(isset($group) && $group)
<div class="modal fade" id="manageMembersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Members — {{ $group->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Current members --}}
                <h6 class="text-muted mb-2">Current members</h6>
                @foreach($group->members as $member)
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span>{{ $member->name }} <small class="text-muted">({{ $member->role->label() }})</small></span>
                    <form method="POST" action="{{ route('chat-groups.members.remove', [$group, $member]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                            <i class="ti ti-x fs-13"></i>
                        </button>
                    </form>
                </div>
                @endforeach

                <hr>

                {{-- Add member --}}
                <h6 class="text-muted mb-2">Add member</h6>
                <form method="POST" action="{{ route('chat-groups.members.add', $group) }}" class="d-flex gap-2">
                    @csrf
                    <select name="user_id" class="form-select form-select-sm">
                        @foreach(\App\Models\User::where('is_active', true)->orderBy('name')->get() as $u)
                        @unless($group->members->contains('id', $u->id))
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endunless
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">Add</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endif

@endsection

@push('scripts')
<script>
(function () {
    const storeUrl    = '{{ route("chat.store") }}';
    const pollUrl     = '{{ route("chat.messages") }}';
    const typingUrl   = '{{ route("chat.typing") }}';
    const reactUrl    = '{{ url("chat/messages") }}';
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;
    const currentUser = {{ auth()->id() }};
    const groupId     = {!! isset($group) && $group ? $group->id : 'null' !!};
    const EMOJIS      = ['👍','❤️','😂','😮','😢'];

    const messagesEl    = document.getElementById('chat-messages');
    const noMessages    = document.getElementById('no-messages');
    const typingBar     = document.getElementById('typing-bar');
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

    // --- Initial messages ---
    const initialMessages = @json($messages);
    initialMessages.forEach(appendMessage);
    if (initialMessages.length) lastId = initialMessages[initialMessages.length - 1].id;
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

    // --- Typing indicator (debounced) ---
    let typingTimer = null;
    bodyEl.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(sendTyping, 200);
    });

    function sendTyping() {
        const body = new FormData();
        body.append('_token', csrfToken);
        if (groupId) body.append('group_id', groupId);
        fetch(typingUrl, { method: 'POST', body });
    }

    // --- Attachments ---
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

    // --- Append message ---
    function appendMessage(msg) {
        if (rendered.has(msg.id)) return;
        rendered.add(msg.id);
        noMessages.classList.add('d-none');

        const own  = msg.is_own;
        const wrap = document.createElement('div');
        wrap.id = 'msg-' + msg.id;
        wrap.className = 'd-flex mb-2 ' + (own ? 'justify-content-end' : 'justify-content-start');

        let attachHtml = '';
        if (msg.attachment_url) {
            if (msg.is_image) {
                attachHtml = `<div class="mt-2"><a href="${msg.attachment_url}" target="_blank">
                    <img src="${msg.attachment_url}" class="img-fluid rounded" style="max-width:200px;max-height:150px;object-fit:cover">
                </a></div>`;
            } else {
                attachHtml = `<div class="mt-2">
                    <a href="${msg.attachment_url}" target="_blank" class="${own ? 'text-white' : 'text-primary'}" style="font-size:.82rem">
                        <i class="ti ti-download me-1"></i>${escHtml(msg.attachment_name)}
                    </a></div>`;
            }
        }

        const bodyHtml = msg.body ? `<div>${escHtml(msg.body).replace(/\n/g,'<br>')}</div>` : '';

        // Reaction picker (hidden by default, shown on hover)
        const pickerHtml = `<div class="reaction-picker d-none mt-1 ${own ? 'justify-content-end' : ''} d-flex flex-wrap">
            ${EMOJIS.map(e => `<button type="button" onclick="toggleReact(${msg.id},'${e}')" title="${e}">${e}</button>`).join('')}
        </div>`;

        const reactBarId = 'react-' + msg.id;

        wrap.innerHTML = `
            <div class="msg-bubble">
                ${!own ? `<div class="text-muted fw-semibold mb-1" style="font-size:.78rem">${escHtml(msg.user_name)}</div>` : ''}
                <div class="bubble-body px-3 py-2 ${own ? 'bg-primary text-white' : 'bg-light text-dark'}
                     position-relative" onmouseenter="showPicker(this)" onmouseleave="hidePicker(this)">
                    ${bodyHtml}${attachHtml}
                    ${pickerHtml}
                </div>
                <div id="${reactBarId}" class="reaction-bar d-flex flex-wrap gap-1 mt-1 ${own ? 'justify-content-end' : ''}">
                    ${buildReactionBar(msg.reactions || [], msg.id)}
                </div>
                <div class="msg-time ${own ? 'text-end' : ''}">${msg.created_at}</div>
            </div>`;

        messagesEl.appendChild(wrap);
    }

    function buildReactionBar(reactions, msgId) {
        if (!reactions || !reactions.length) return '';
        return reactions.map(r =>
            `<button type="button" class="reaction-btn ${r.reacted ? 'reacted' : ''}" onclick="toggleReact(${msgId},'${r.emoji}')">
                ${r.emoji} <span>${r.count}</span>
            </button>`
        ).join('');
    }

    // --- Reaction picker show/hide ---
    window.showPicker = function(el) { el.querySelector('.reaction-picker')?.classList.remove('d-none'); };
    window.hidePicker = function(el) { el.querySelector('.reaction-picker')?.classList.add('d-none'); };

    // --- Toggle reaction ---
    window.toggleReact = function(msgId, emoji) {
        fetch(`${reactUrl}/${msgId}/react`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ emoji }),
        })
        .then(r => r.json())
        .then(data => {
            const bar = document.getElementById('react-' + msgId);
            if (bar) bar.innerHTML = buildReactionBar(data.reactions || [], msgId);
        });
    };

    function escHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    // --- Send message ---
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const body = bodyEl.value.trim();
        const file = attachFile.files[0];
        if (!body && !file) return;

        sendBtn.disabled = true;

        const fd = new FormData();
        fd.append('_token', csrfToken);
        if (body)    fd.append('body', body);
        if (file)    fd.append('attachment', file);
        if (groupId) fd.append('group_id', groupId);

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

    // --- Polling (3s) ---
    function poll() {
        const params = new URLSearchParams({ since: lastId });
        if (groupId) params.append('group_id', groupId);

        fetch(pollUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(function (data) {
            const msgs = data.messages || [];
            msgs.forEach(function (msg) {
                if (!rendered.has(msg.id)) {
                    appendMessage(msg);
                    if (isNearBottom) scrollToBottom();
                }
                lastId = Math.max(lastId, msg.id);
            });

            // Typing indicator
            const typists = data.typists || [];
            if (typists.length === 0) {
                typingBar.textContent = '';
            } else if (typists.length === 1) {
                typingBar.textContent = typists[0] + ' is typing…';
            } else {
                typingBar.textContent = typists.slice(0, -1).join(', ') + ' and ' + typists[typists.length - 1] + ' are typing…';
            }
        })
        .catch(() => {});

        setTimeout(poll, 3000);
    }

    setTimeout(poll, 3000);
})();
</script>
@endpush
