@extends('layouts.app')

@section('title', 'Staff Messages')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-mail text-primary me-2"></i>Staff Messages & Notes (عملے کے پیغامات)</h3>
        <p class="text-muted mb-0">Internal communications between accountants, cashiers, managers, and store staff.</p>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
            <i class="ti ti-pencil-plus me-1"></i>Compose New Message
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-inbox fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Inbox Messages</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalReceived) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-mail-exclamation fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Unread Messages</span>
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($unreadCount) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-secondary-subtle text-secondary p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-send fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Sent Messages</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalSent) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs and Search Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'inbox' ? 'active' : '' }}" href="{{ route('messages.index', ['tab' => 'inbox']) }}">
                            <i class="ti ti-inbox me-1"></i>Inbox
                            @if($unreadCount > 0)
                                <span class="badge bg-danger ms-1">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'sent' ? 'active' : '' }}" href="{{ route('messages.index', ['tab' => 'sent']) }}">
                            <i class="ti ti-send me-1"></i>Sent Items ({{ $totalSent }})
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <form method="GET" action="{{ route('messages.index') }}" class="d-flex gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search messages..." class="form-control form-control-sm">
                    @if($tab === 'inbox')
                        <div class="form-check form-switch d-flex align-items-center gap-1 text-nowrap">
                            <input class="form-check-input" type="checkbox" name="unread_only" value="1" id="unreadOnly" {{ request('unread_only') ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small" for="unreadOnly">Unread only</label>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-search"></i>
                    </button>
                    <a href="{{ route('messages.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light border" title="Reset">
                        <i class="ti ti-refresh"></i>
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Messages List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;"></th>
                        <th style="width: 220px;">{{ $tab === 'sent' ? 'Recipient (To)' : 'Sender (From)' }}</th>
                        <th>Subject & Message Preview</th>
                        <th style="width: 110px;">Priority</th>
                        <th style="width: 170px;">Date & Time</th>
                        <th class="text-end" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                        <tr class="{{ (!$msg->is_read && $tab === 'inbox') ? 'table-warning-subtle fw-semibold' : '' }}">
                            <td class="text-center">
                                @if($tab === 'inbox')
                                    @if($msg->is_read)
                                        <i class="ti ti-mail-opened text-muted fs-5" title="Read"></i>
                                    @else
                                        <i class="ti ti-mail text-danger fs-5" title="Unread"></i>
                                    @endif
                                @else
                                    @if($msg->is_read)
                                        <i class="ti ti-checks text-success fs-5" title="Read by recipient at {{ $msg->read_at?->format('d M, h:i A') }}"></i>
                                    @else
                                        <i class="ti ti-check text-muted fs-5" title="Delivered, not read yet"></i>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @php
                                    $contactUser = ($tab === 'sent') ? $msg->receiver : $msg->sender;
                                @endphp
                                @if($contactUser)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 11px;">
                                            {{ strtoupper(substr($contactUser->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $contactUser->name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <span class="badge bg-secondary-subtle text-secondary py-0 px-1">{{ $contactUser->role->name ?? 'Staff' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">System</span>
                                @endif
                            </td>
                            <td>
                                <a href="javascript:void(0)" onclick="openMessageModal({{ $msg->id }})" class="text-decoration-none text-dark d-block">
                                    <div class="{{ (!$msg->is_read && $tab === 'inbox') ? 'fw-bold text-dark' : 'fw-medium text-dark' }} small">
                                        {{ $msg->subject }}
                                    </div>
                                    <div class="text-muted text-truncate" style="max-width: 450px; font-size: 12px;">
                                        {{ Str::limit($msg->message, 85) }}
                                    </div>
                                </a>
                            </td>
                            <td>
                                @if($msg->priority === 'urgent')
                                    <span class="badge bg-danger text-white border">
                                        <i class="ti ti-flame me-1"></i>Urgent
                                    </span>
                                @elseif($msg->priority === 'info')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <i class="ti ti-info-circle me-1"></i>Info
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border">Normal</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark">{{ $msg->created_at->format('d M Y, h:i A') }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $msg->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" onclick="openMessageModal({{ $msg->id }})" title="Read Message">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                    @if($tab === 'inbox' && $msg->sender)
                                        <button type="button" class="btn btn-outline-success" onclick="replyToMessage({{ $msg->sender_id }}, '{{ addslashes($msg->sender->name) }}', 'Re: {{ addslashes($msg->subject) }}')" title="Reply">
                                            <i class="ti ti-arrow-back-up"></i>
                                        </button>
                                    @endif
                                    <form action="{{ route('messages.destroy', $msg->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this message?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ti ti-mail-opened fs-1 d-block mb-2 text-muted"></i>
                                No messages found in this folder.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">Showing {{ $messages->firstItem() ?? 0 }} to {{ $messages->lastItem() ?? 0 }} of {{ $messages->total() }} messages</span>
            <div>{{ $messages->links() }}</div>
        </div>
    </div>
</div>

<!-- Compose Message Modal -->
<div class="modal fade" id="composeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="ti ti-pencil-plus text-primary me-2"></i>Compose Staff Message (نیا پیغام لکھیں)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Recipient (کس کو بھیجنا ہے) <span class="text-danger">*</span></label>
                            <select name="receiver_id" id="composeReceiverId" class="form-select" required>
                                <option value="">-- Select Staff Colleague --</option>
                                @foreach($colleagues as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }} — [{{ $c->role->name ?? 'Staff' }}] ({{ $c->username }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Priority Level</label>
                            <select name="priority" id="composePriority" class="form-select">
                                <option value="normal" selected>Normal (عام)</option>
                                <option value="urgent">Urgent (ضروری / فوری)</option>
                                <option value="info">Information (اطلاع)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Subject (موضوع) <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="composeSubject" class="form-control" placeholder="e.g., Al-Madina Mart recovery check, Supplier payment invoice update..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Message Body (تفصیل) <span class="text-danger">*</span></label>
                            <textarea name="message" id="composeBody" rows="5" class="form-control" placeholder="Write internal notes, voucher details, or requests for your colleague..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-send me-1"></i>Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <span id="viewPriorityBadge" class="badge"></span>
                    <h5 class="modal-title fw-bold mb-0" id="viewSubject"></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                    <div>
                        <div class="small text-muted">From: <strong class="text-dark" id="viewSender"></strong> (<span id="viewSenderRole"></span>)</div>
                        <div class="small text-muted">To: <strong class="text-dark" id="viewReceiver"></strong> (<span id="viewReceiverRole"></span>)</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted" id="viewDate"></div>
                    </div>
                </div>
                <div class="p-3 bg-light rounded border text-dark" id="viewBody" style="white-space: pre-wrap; font-size: 14px; min-height: 120px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="viewReplyBtn" onclick="triggerReplyFromView()">
                    <i class="ti ti-arrow-back-up me-1"></i>Reply
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentViewData = null;

function openMessageModal(messageId) {
    fetch('{{ url("messages") }}/' + messageId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            currentViewData = data.message;
            document.getElementById('viewSubject').innerText = data.message.subject;
            document.getElementById('viewSender').innerText = data.message.sender_name;
            document.getElementById('viewSenderRole').innerText = data.message.sender_role;
            document.getElementById('viewReceiver').innerText = data.message.receiver_name;
            document.getElementById('viewReceiverRole').innerText = data.message.receiver_role;
            document.getElementById('viewDate').innerText = data.message.created_at;
            document.getElementById('viewBody').innerText = data.message.body;

            const badge = document.getElementById('viewPriorityBadge');
            if(data.message.priority === 'urgent') {
                badge.className = 'badge bg-danger text-white';
                badge.innerText = 'URGENT';
            } else if(data.message.priority === 'info') {
                badge.className = 'badge bg-info-subtle text-info border border-info-subtle';
                badge.innerText = 'INFO';
            } else {
                badge.className = 'badge bg-light text-secondary border';
                badge.innerText = 'NORMAL';
            }

            const modal = new bootstrap.Modal(document.getElementById('viewMessageModal'));
            modal.show();
        }
    });
}

function replyToMessage(senderId, senderName, subject) {
    document.getElementById('composeReceiverId').value = senderId;
    document.getElementById('composeSubject').value = subject;
    document.getElementById('composeBody').value = '';
    document.getElementById('composePriority').value = 'normal';

    const modal = new bootstrap.Modal(document.getElementById('composeModal'));
    modal.show();
}

function triggerReplyFromView() {
    if(currentViewData) {
        const viewModalEl = document.getElementById('viewMessageModal');
        const viewModal = bootstrap.Modal.getInstance(viewModalEl);
        if(viewModal) viewModal.hide();

        replyToMessage(currentViewData.sender_id, currentViewData.sender_name, 'Re: ' + currentViewData.subject);
    }
}
</script>
@endsection
