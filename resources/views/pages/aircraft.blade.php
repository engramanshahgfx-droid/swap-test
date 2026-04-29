@extends('layouts.app')

@section('title', __('admin.aircrafts.title'))
@section('page-name', 'aircrafts')

@section('content')
    <h1 class="page-title">{{ __('admin.aircrafts.title') }}</h1>
    <p class="page-sub">{{ __('admin.aircrafts.subtitle') }}</p>

    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-block" id="create-aircraft-form" style="display:none; margin-bottom: 16px; max-width: 760px;">
        <form id="aircraft-create-form-el" method="POST" action="{{ route('aircrafts.store') }}">
            @csrf
            <div class="form-grid two">
                <input type="text" name="name" id="new-aircraft-name" placeholder="{{ __('admin.aircrafts.aircraft_name') }}" required />
                <input type="text" name="code" id="new-aircraft-code" placeholder="{{ __('admin.aircrafts.aircraft_code') }}" required />
            </div>
            <div class="form-grid two" style="margin-top:10px;">
                <select name="airline_id" required>
                    <option value="">{{ __('admin.aircrafts.select_airline') }}</option>
                    @foreach($airlines as $airline)
                        <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="capacity" min="0" placeholder="{{ __('admin.aircrafts.capacity') }}" />
            </div>
            <div style="margin-top:10px; display:flex; align-items:center; gap:10px;">
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="is_active" checked />
                    {{ __('admin.aircrafts.active') }}
                </label>
            </div>
            <div class="action-end" style="margin-top: 10px;">
                <button class="js-cancel-aircraft-form" type="button">{{ __('admin.cancel') }}</button>
                <button type="submit">{{ __('admin.aircrafts.add_aircraft') }}</button>
            </div>
        </form>
    </div>

    <div class="actions">
        <div class="search-box">
            <input type="text" class="js-table-search" placeholder="{{ __('admin.aircrafts.search') }}" />
        </div>
        <button class="add-btn js-open-aircraft-form" type="button">{{ __('admin.aircrafts.new') }}</button>
    </div>

    <div class="table-wrap">
        <table id="aircraft-table">
            <thead>
                <tr>
                    <th>{{ __('admin.aircrafts.name') }}</th>
                    <th>{{ __('admin.aircrafts.code') }}</th>
                    <th>{{ __('admin.aircrafts.airline') }}</th>
                    <th>{{ __('admin.aircrafts.capacity') }}</th>
                    <th>{{ __('admin.aircrafts.status') }}</th>
                    <th>{{ __('admin.aircrafts.created') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aircrafts as $aircraft)
                    <tr>
                        <td><strong>{{ $aircraft->name }}</strong></td>
                        <td><span class="badge badge-gray">{{ $aircraft->code }}</span></td>
                        <td>{{ $aircraft->airline->name ?? __('admin.unknown') }}</td>
                        <td>{{ $aircraft->capacity ?? __('admin.none') }}</td>
                        <td>{{ $aircraft->is_active ? __('admin.aircrafts.active_label') : __('admin.aircrafts.inactive_label') }}</td>
                        <td>{{ $aircraft->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="tbl-actions">
                                <button class="tbl-btn tbl-btn-edit"
                                    onclick="editAircraft({{ $aircraft->id }}, '{{ addslashes($aircraft->name) }}', '{{ $aircraft->code }}', '{{ $aircraft->airline_id }}', '{{ $aircraft->capacity ?? '' }}', '{{ $aircraft->is_active ? 1 : 0 }}')">
                                    {{ __('admin.edit') }}
                                </button>
                                <form method="POST" action="{{ route('aircrafts.destroy', $aircraft) }}" onsubmit="return confirm('{{ __('admin.aircrafts.delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tbl-btn tbl-btn-delete">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">{{ __('admin.aircrafts.no_results') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>{{ __('admin.showing_results', ['from' => $aircrafts->firstItem() ?? 0, 'to' => $aircrafts->lastItem() ?? 0, 'total' => $aircrafts->total()]) }}</span>
        <div class="pagination">{{ $aircrafts->links() }}</div>
    </div>

    {{-- Edit modal --}}
    <div class="modal-backdrop" id="edit-aircraft-modal">
        <div class="modal">
            <div class="modal-title">{{ __('admin.aircrafts.edit_title') }}</div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <label>{{ __('admin.aircrafts.name') }}</label>
                <input type="text" name="name" required />
                <label>{{ __('admin.aircrafts.code') }}</label>
                <input type="text" name="code" required />
                <label>{{ __('admin.aircrafts.airline') }}</label>
                <select name="airline_id" required>
                    @foreach($airlines as $airline)
                        <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                    @endforeach
                </select>
                <label>{{ __('admin.aircrafts.capacity') }}</label>
                <input type="number" name="capacity" min="0" />
                <label>
                    <input type="checkbox" name="is_active" />
                    {{ __('admin.aircrafts.active') }}
                </label>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('edit-aircraft-modal')">{{ __('admin.cancel') }}</button>
                    <button type="submit">{{ __('admin.aircrafts.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('.js-open-aircraft-form')?.addEventListener('click', function() {
            document.getElementById('create-aircraft-form').style.display = 'block';
        });

        document.querySelector('.js-cancel-aircraft-form')?.addEventListener('click', function() {
            document.getElementById('create-aircraft-form').style.display = 'none';
        });

        function editAircraft(id, name, code, airlineId, capacity, isActive) {
            const form = document.querySelector('#edit-aircraft-modal form');
            form.action = `/aircrafts/${id}`;
            form.querySelector('input[name="name"]').value = name;
            form.querySelector('input[name="code"]').value = code;
            form.querySelector('select[name="airline_id"]').value = airlineId;
            form.querySelector('input[name="capacity"]').value = capacity;
            form.querySelector('input[name="is_active"]').checked = isActive === '1';
            document.getElementById('edit-aircraft-modal').style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        document.querySelector('.js-table-search')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                if (row.textContent.toLowerCase().includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        document.getElementById('edit-aircraft-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    </script>
@endsection
