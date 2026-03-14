@extends('layouts.app')

@section('title', __('admin.swap_vacation.title'))
@section('page-name', 'swap-vacation')

@section('content')
    <h1 class="page-title">{{ __('admin.swap_vacation.title') }}</h1>
    <p class="page-sub">{{ __('admin.swap_vacation.subtitle') }}</p>

    <div class="actions">
        <div class="search-box">
            <input type="text" class="js-table-search" placeholder="{{ __('admin.swap_vacation.search') }}" />
        </div>
        <form method="GET" action="{{ route('swap-vacation') }}" style="display:contents">
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">{{ __('admin.all_statuses') }}</option>
                <option value="available"   {{ request('status') === 'available'   ? 'selected' : '' }}>Available</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>{{ __('admin.swap_vacation.active') }}</option>
                <option value="expired"  {{ request('status') === 'expired'  ? 'selected' : '' }}>{{ __('admin.swap_vacation.expired') }}</option>
                <option value="closed"  {{ request('status') === 'closed'  ? 'selected' : '' }}>Closed</option>
            </select>
        </form>
    </div>

    <div class="table-wrap">
        <table id="swap-vacation-table">
            <thead>
                <tr>
                    <th>{{ __('admin.swap_vacation.user') }}</th>
                    <th>{{ __('admin.swap_vacation.flight') }}</th>
                    <th>{{ __('admin.swap_vacation.airline') }}</th>
                    <th>{{ __('admin.swap_vacation.departure') }}</th>
                    <th>{{ __('admin.swap_vacation.swap_requests') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('admin.swap_vacation.published') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($publishedTrips as $trip)
                    @php
                        $sc = match($trip->status) {
                            'available' => 'badge-info',
                            'active'  => 'badge-success',
                            'closed' => 'badge-info',
                            'expired' => 'badge-gray',
                            default   => 'badge-warning',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">{{ strtoupper(substr($trip->user->full_name ?? 'U', 0, 1)) }}</div>
                                <div>
                                    <div class="user-name">{{ $trip->user->full_name ?? __('admin.none') }}</div>
                                    <div class="user-meta">{{ $trip->user->employee_id ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $trip->flight->flight_number ?? __('admin.none') }}</td>
                        <td>{{ $trip->flight->airline->name ?? __('admin.none') }}</td>
                        <td>{{ $trip->flight->departure_date ? $trip->flight->departure_date->format('M d, Y') : __('admin.none') }}</td>
                        <td>{{ $trip->swap_requests_count }}</td>
                        <td><span class="badge {{ $sc }}">{{ ucfirst($trip->status) }}</span></td>
                        <td>{{ $trip->published_at ? $trip->published_at->format('M d, Y') : $trip->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">{{ __('admin.swap_vacation.no_results') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>{{ __('admin.showing_results', ['from' => $publishedTrips->firstItem() ?? 0, 'to' => $publishedTrips->lastItem() ?? 0, 'total' => $publishedTrips->total()]) }}</span>
        <div class="pagination">{{ $publishedTrips->links() }}</div>
    </div>
@endsection
