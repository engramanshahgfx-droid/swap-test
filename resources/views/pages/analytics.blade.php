@extends('layouts.app')

@section('title', __('admin.analytics.title'))
@section('page-name', 'analytics')

@section('content')
    <h1 class="page-title">{{ __('admin.analytics.title') }}</h1>
    <p class="page-sub">{{ __('admin.analytics.subtitle') }}</p>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">{{ __('admin.analytics.total_users') }}</div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">{{ __('admin.analytics.total_swaps') }}</div>
            <div class="stat-value">{{ number_format($stats['total_swaps']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">{{ __('admin.analytics.published_trips') }}</div>
            <div class="stat-value">{{ number_format($stats['published_trips']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">{{ __('admin.analytics.total_flights') }}</div>
            <div class="stat-value">{{ number_format($stats['total_flights']) }}</div>
        </div>
    </div>

    <div class="custom-dashboard-grid" style="margin-top:12px;">
        <div class="custom-dashboard-card span-6">
            <div class="dashboard-card-header">{{ __('admin.analytics.user_growth') }}</div>
            <table class="table-mini">
                <thead><tr><th>Day</th><th>Users</th></tr></thead>
                <tbody>
                    @foreach($userGrowth as $row)
                        <tr><td>{{ $row['label'] }}</td><td>{{ $row['value'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="custom-dashboard-card span-6">
            <div class="dashboard-card-header">{{ __('admin.analytics.swap_volume') }}</div>
            <table class="table-mini">
                <thead><tr><th>Day</th><th>Swaps</th></tr></thead>
                <tbody>
                    @foreach($swapVolume as $row)
                        <tr><td>{{ $row['label'] }}</td><td>{{ $row['value'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="custom-dashboard-card span-6">
            <div class="dashboard-card-header">{{ __('admin.analytics.airline_activity') }}</div>
            <table class="table-mini">
                <thead><tr><th>Airline</th><th>Code</th><th>Score</th></tr></thead>
                <tbody>
                    @forelse($airlineActivity as $row)
                        <tr><td>{{ $row['name'] }}</td><td>{{ $row['code'] }}</td><td>{{ $row['score'] }}</td></tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;color:var(--muted)">{{ __('admin.none') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="custom-dashboard-card span-6">
            <div class="dashboard-card-header">{{ __('admin.analytics.top_airports') }}</div>
            <table class="table-mini">
                <thead><tr><th>Airport</th><th>Flights</th></tr></thead>
                <tbody>
                    @forelse($topAirports as $airport)
                        <tr><td>{{ $airport->airport }}</td><td>{{ $airport->total }}</td></tr>
                    @empty
                        <tr><td colspan="2" style="text-align:center;color:var(--muted)">{{ __('admin.none') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="custom-dashboard-card span-12">
            <div class="dashboard-card-header">{{ __('admin.analytics.top_crew_members') }}</div>
            <table class="table-mini">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Airline</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCrewMembers as $crew)
                        <tr>
                            <td>{{ $crew['name'] }}</td>
                            <td>{{ $crew['employee_id'] ?? __('admin.none') }}</td>
                            <td>{{ $crew['airline'] ?? __('admin.none') }}</td>
                            <td>{{ $crew['score'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:var(--muted)">{{ __('admin.none') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
