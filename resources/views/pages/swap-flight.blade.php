@extends('layouts.app')

@section('title', __('admin.swap_flight.title'))
@section('page-name', 'swap-flight')

@section('content')
    <h1 class="page-title">{{ __('admin.swap_flight.title') }}</h1>
    <p class="page-sub">{{ __('admin.swap_flight.subtitle') }}</p>

    <div class="actions">
        <div class="search-box">
            <input type="text" class="js-table-search" placeholder="{{ __('admin.swap_flight.search') }}" />
        </div>
        <form method="GET" action="{{ route('swap-flight') }}" style="display:contents">
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">{{ __('admin.all_statuses') }}</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>{{ __('admin.status_values.pending') }}</option>
                <option value="approved_by_owner"  {{ request('status') === 'approved_by_owner'  ? 'selected' : '' }}>{{ __('admin.status_values.approved_by_owner') }}</option>
                <option value="rejected_by_owner"  {{ request('status') === 'rejected_by_owner'  ? 'selected' : '' }}>{{ __('admin.status_values.rejected_by_owner') }}</option>
                <option value="manager_rejected"  {{ request('status') === 'manager_rejected'  ? 'selected' : '' }}>{{ __('admin.status_values.manager_rejected') }}</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('admin.status_values.completed') }}</option>
            </select>
        </form>
    </div>

    <div class="table-wrap">
        <table id="swap-table">
            <thead>
                <tr>
                    <th>{{ __('admin.swap_flight.requester') }}</th>
                    <th>{{ __('admin.swap_flight.responder') }}</th>
                    <th>{{ __('admin.swap_flight.flight_offered') }}</th>
                    <th>{{ __('admin.swap_flight.requester_flight') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('admin.swap_flight.date') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($swaps as $swap)
                    @php
                        $sc = match($swap->status) {
                            'completed' => 'badge-success',
                            'approved_by_owner' => 'badge-info',
                            'pending' => 'badge-warning',
                            'rejected_by_owner', 'manager_rejected', 'rejected' => 'badge-danger',
                            default => 'badge-gray',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">{{ strtoupper(substr($swap->requester->full_name ?? 'U', 0, 1)) }}</div>
                                <div>
                                    <div class="user-name">{{ $swap->requester->full_name ?? '—' }}</div>
                                    <div class="user-meta">{{ $swap->requester->employee_id ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">{{ strtoupper(substr($swap->responder->full_name ?? 'U', 0, 1)) }}</div>
                                <div class="user-name">{{ $swap->responder->full_name ?? '—' }}</div>
                            </div>
                        </td>
                        <td>{{ $swap->publishedTrip->flight->flight_number ?? '—' }}</td>
                        <td>{{ $swap->requesterTrip->flight->flight_number ?? '—' }}</td>
                        <td><span class="badge {{ $sc }}">{{ __('admin.status_values.' . $swap->status) }}</span></td>
                        <td>{{ $swap->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="tbl-actions">
                                <button class="tbl-btn tbl-btn-view"
                                    onclick="setSwapStatus({{ $swap->id }}, '{{ $swap->status }}')">
                                    {{ __('admin.swap_flight.status_button') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">{{ __('admin.swap_flight.no_results') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>{{ __('admin.showing_results', ['from' => $swaps->firstItem() ?? 0, 'to' => $swaps->lastItem() ?? 0, 'total' => $swaps->total()]) }}</span>
        <div class="pagination">{{ $swaps->links() }}</div>
    </div>

    {{-- Status modal --}}
    <div class="modal-backdrop" id="status-modal">
        <div class="modal">
            <div class="modal-title">{{ __('admin.swap_flight.modal_title') }}</div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <label>{{ __('admin.status') }}</label>
                <select name="status">
                    <option value="pending">{{ __('admin.status_values.pending') }}</option>
                    <option value="approved_by_owner">{{ __('admin.status_values.approved_by_owner') }}</option>
                    <option value="rejected_by_owner">{{ __('admin.status_values.rejected_by_owner') }}</option>
                    <option value="manager_rejected">{{ __('admin.status_values.manager_rejected') }}</option>
                    <option value="completed">{{ __('admin.status_values.completed') }}</option>
                </select>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('status-modal')">{{ __('admin.cancel') }}</button>
                    <button type="submit">{{ __('admin.update') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
