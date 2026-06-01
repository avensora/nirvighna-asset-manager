@extends('layouts.app', ['title' => 'Calendar', 'subtitle' => 'Schedule & Events'])

@section('content')

<div class="row">
    {{-- Calendar — first in HTML (mobile top), pushed right on desktop --}}
    <div class="col-xl-9 order-xl-2 mb-3 mb-xl-0">
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    {{-- Legend panel — second in HTML (mobile bottom), pulled left on desktop --}}
    <div class="col-xl-3 order-xl-1">
        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary w-100 mb-3" id="btn-new-event">
                    <i class="ti ti-plus me-2"></i> New Event
                </button>

                <p class="text-muted fw-semibold small text-uppercase mb-2">Legend</p>
                <div class="d-flex flex-column gap-1">
                    <div class="d-flex align-items-center gap-2 py-1">
                        <span class="badge bg-primary-subtle text-primary p-1"><i class="ti ti-circle-filled"></i></span>
                        <span class="small">Reminder / Personal</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 py-1">
                        <span class="badge bg-success-subtle text-success p-1"><i class="ti ti-circle-filled"></i></span>
                        <span class="small">Meeting</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 py-1">
                        <span class="badge bg-info-subtle text-info p-1"><i class="ti ti-circle-filled"></i></span>
                        <span class="small">Deadline</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 py-1">
                        <span class="badge bg-danger-subtle text-danger p-1"><i class="ti ti-circle-filled"></i></span>
                        <span class="small">Urgent</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 py-1">
                        <span class="badge bg-secondary-subtle text-secondary p-1"><i class="ti ti-circle-filled"></i></span>
                        <span class="small">Other</span>
                    </div>
                    <hr class="my-1">
                    <div class="d-flex align-items-center gap-2 py-1">
                        <span class="badge bg-warning-subtle text-warning p-1"><i class="ti ti-circle-filled"></i></span>
                        <span class="small">Invoice Due Date</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Event Modal --}}
<div class="modal fade" id="event-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="forms-event" novalidate>
                @csrf
                <input type="hidden" id="event-id">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal-title">New Event</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div id="event-readonly-info" class="alert alert-warning d-none small mb-3"></div>

                    <div class="mb-3" id="field-title">
                        <label class="form-label" for="event-title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="event-title" required maxlength="255">
                        <div class="invalid-feedback">Title is required.</div>
                    </div>

                    <div class="mb-3" id="field-description">
                        <label class="form-label" for="event-description">Description</label>
                        <textarea class="form-control" id="event-description" rows="2"></textarea>
                    </div>

                    <div class="row g-2 mb-3" id="field-dates">
                        <div class="col-6">
                            <label class="form-label" for="event-start">Start <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="event-start" required>
                            <div class="invalid-feedback">Start date required.</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="event-end">End</label>
                            <input type="date" class="form-control" id="event-end">
                        </div>
                    </div>

                    <div class="row g-2 mb-2" id="field-options">
                        <div class="col-6">
                            <div class="form-check mt-1">
                                <input type="checkbox" class="form-check-input" id="event-all-day" checked>
                                <label class="form-check-label" for="event-all-day">All Day</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="event-color">Event Type</label>
                            <select class="form-select form-select-sm" id="event-color">
                                <option value="bg-primary">Reminder / Personal</option>
                                <option value="bg-success">Meeting</option>
                                <option value="bg-info">Deadline</option>
                                <option value="bg-danger">Urgent</option>
                                <option value="bg-secondary">Other</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto d-none" id="btn-delete-event">
                        <i class="ti ti-trash me-1"></i> Delete
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-event">
                        <i class="ti ti-check me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('boron/assets/vendor/fullcalendar/index.global.min.js') }}"></script>
<script>
(function () {
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
    const modal      = new bootstrap.Modal(document.getElementById('event-modal'), {backdrop: 'static'});
    const form       = document.getElementById('forms-event');
    const titleEl    = document.getElementById('event-title');
    const descEl     = document.getElementById('event-description');
    const startEl    = document.getElementById('event-start');
    const endEl      = document.getElementById('event-end');
    const allDayEl   = document.getElementById('event-all-day');
    const colorEl    = document.getElementById('event-color');
    const eventIdEl  = document.getElementById('event-id');
    const modalTitle = document.getElementById('modal-title');
    const btnDelete  = document.getElementById('btn-delete-event');
    const btnSave    = document.getElementById('btn-save-event');
    const readonlyInfo = document.getElementById('event-readonly-info');

    let calObj = null;

    function setInputTypes(allDay) {
        startEl.type = allDay ? 'date' : 'datetime-local';
        endEl.type   = allDay ? 'date' : 'datetime-local';
    }

    allDayEl.addEventListener('change', function () {
        setInputTypes(this.checked);
    });

    function resetForm() {
        form.reset();
        form.classList.remove('was-validated');
        readonlyInfo.classList.add('d-none');
        readonlyInfo.innerHTML = '';
        btnDelete.classList.add('d-none');
        btnSave.classList.remove('d-none');
        eventIdEl.value = '';
        setInputTypes(true);
    }

    function openNewEvent(dateInfo) {
        resetForm();
        modalTitle.textContent = 'New Event';
        allDayEl.checked = dateInfo?.allDay !== false;
        setInputTypes(allDayEl.checked);
        if (dateInfo?.dateStr) startEl.value = dateInfo.dateStr;
        modal.show();
    }

    function openViewEvent(fcEvent) {
        const props = fcEvent.extendedProps;
        resetForm();
        modalTitle.textContent = fcEvent.title;
        btnSave.classList.add('d-none');
        titleEl.value = fcEvent.title;
        titleEl.disabled = true;
        descEl.disabled = true;
        startEl.disabled = true;
        endEl.disabled = true;
        allDayEl.disabled = true;
        colorEl.disabled = true;
        if (props.url) {
            readonlyInfo.classList.remove('d-none');
            readonlyInfo.innerHTML = '<i class="ti ti-info-circle me-1"></i>Invoice due date. <a href="' + props.url + '" class="alert-link">View Invoice &rarr;</a>';
        }
        modal.show();
        // Re-enable fields when modal closes so next open is clean
        modal._element.addEventListener('hidden.bs.modal', function handler() {
            [titleEl, descEl, startEl, endEl, allDayEl, colorEl].forEach(el => el.disabled = false);
            modal._element.removeEventListener('hidden.bs.modal', handler);
        });
    }

    function openEditEvent(fcEvent) {
        const props = fcEvent.extendedProps;
        resetForm();
        modalTitle.textContent = 'Edit Event';
        eventIdEl.value = props.eventId;
        btnDelete.classList.remove('d-none');

        titleEl.value = fcEvent.title;
        descEl.value  = props.description || '';
        colorEl.value = props.color || 'bg-primary';

        const isAllDay = fcEvent.allDay;
        allDayEl.checked = isAllDay;
        setInputTypes(isAllDay);

        if (isAllDay) {
            startEl.value = fcEvent.startStr.split('T')[0];
            // FullCalendar all-day end is exclusive (+1 day), show user the inclusive end
            if (fcEvent.end) {
                const excEnd = new Date(fcEvent.endStr);
                excEnd.setDate(excEnd.getDate() - 1);
                endEl.value = excEnd.toISOString().split('T')[0];
            }
        } else {
            startEl.value = fcEvent.startStr.slice(0, 16);
            endEl.value   = fcEvent.endStr ? fcEvent.endStr.slice(0, 16) : '';
        }

        modal.show();
    }

    // Init FullCalendar
    const calEl  = document.getElementById('calendar');
    const isMob  = window.innerWidth < 576;
    calObj = new FullCalendar.Calendar(calEl, {
        initialView: isMob ? 'listWeek' : 'dayGridMonth',
        headerToolbar: isMob
            ? { left: 'prev,next today', center: 'title', right: 'listWeek,dayGridMonth' }
            : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth' },
        height: window.innerHeight - 220,
        handleWindowResize: true,
        selectable: true,
        editable: false,
        buttonText: {
            today: 'Today',
            month: 'Month',
            week:  'Week',
            day:   'Day',
            list:  'List',
        },
        events: function (info, success, failure) {
            fetch('/calendar/events?start=' + info.startStr + '&end=' + info.endStr, {
                headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
            })
            .then(r => r.json())
            .then(success)
            .catch(failure);
        },
        dateClick: function (info) {
            openNewEvent(info);
        },
        eventClick: function (info) {
            const props = info.event.extendedProps;
            if (props.type === 'invoice') {
                openViewEvent(info.event);
            } else if (props.canEdit) {
                openEditEvent(info.event);
            }
        },
    });
    calObj.render();

    document.getElementById('btn-new-event').addEventListener('click', function () {
        openNewEvent({allDay: true, dateStr: new Date().toISOString().split('T')[0]});
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        form.classList.add('was-validated');
        if (!form.checkValidity()) return;

        const id      = eventIdEl.value;
        const isAllDay = allDayEl.checked;
        const payload = {
            title:       titleEl.value,
            description: descEl.value || null,
            start_date:  startEl.value,
            end_date:    endEl.value || null,
            all_day:     isAllDay ? 1 : 0,
            color:       colorEl.value,
        };

        const url    = id ? '/calendar/events/' + id : '/calendar/events';
        const method = id ? 'PATCH' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept':       'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(function (data) {
            if (data.success) {
                modal.hide();
                calObj.refetchEvents();
            }
        })
        .catch(function () {
            alert('Something went wrong. Please try again.');
        });
    });

    btnDelete.addEventListener('click', function () {
        const id = eventIdEl.value;
        if (!id || !confirm('Delete this event?')) return;

        fetch('/calendar/events/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept':       'application/json',
            },
        })
        .then(r => r.json())
        .then(function (data) {
            if (data.success) {
                modal.hide();
                calObj.refetchEvents();
            }
        })
        .catch(function () {
            alert('Something went wrong. Please try again.');
        });
    });
})();
</script>
@endpush
