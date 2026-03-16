@extends('layouts.app')

@section('title', __('admin.dashboard.title'))
@section('page-name', 'dashboard')

@section('content')
    <style>
        .custom-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 12px;
            margin-top: 10px;
        }
        .custom-dashboard-card {
            grid-column: span 12;
            background: rgba(217,225,239,.6);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .custom-dashboard-card.span-8 { grid-column: span 8; }
        .custom-dashboard-card.span-6 { grid-column: span 6; }
        .custom-dashboard-card.span-4 { grid-column: span 4; }
        .dashboard-block-head {
            padding: 14px 16px 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .dashboard-block-head h3 {
            font-size: 22px;
            line-height: 1.1;
            margin: 0;
            color: var(--text);
        }
        .dashboard-block-head p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }
        .dashboard-chip {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #dbe7fb;
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
        }
        .chart-host,
        .heatmap-host {
            padding: 14px 16px 16px;
        }
        .chart-host svg {
            width: 100%;
            display: block;
        }
        .chart-axis-row {
            display: grid;
            gap: 6px;
            margin-top: 10px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }
        .chart-legend-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .chart-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text);
            font-size: 12px;
            font-weight: 600;
        }
        .chart-legend-swatch {
            width: 10px;
            height: 10px;
            border-radius: 999px;
        }
        .weekly-heatmap-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 10px;
        }
        .heatmap-cell {
            border-radius: 14px;
            padding: 12px 10px;
            border: 1px solid rgba(47,102,215,.12);
            text-align: center;
            background: #dae4f4;
        }
        .heatmap-day {
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
            margin-bottom: 4px;
        }
        .heatmap-value {
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
        }
        .table-mini {
            width: 100%;
            border-collapse: collapse;
        }
        .table-mini th,
        .table-mini td {
            padding: 10px 14px;
            border-bottom: 1px solid #c7d4ea;
            font-size: 13px;
        }
        .table-mini th {
            color: var(--primary);
            font-weight: 700;
            background: #d9e1ef;
        }
        .trip-widget-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            padding: 0 16px 14px;
        }
        .trip-widget-stat {
            background: #d2deef;
            border: 1px solid #c1d0e8;
            border-radius: 12px;
            padding: 10px 12px;
        }
        .trip-widget-stat span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .trip-widget-stat strong {
            display: block;
            margin-top: 4px;
            color: var(--text);
            font-size: 20px;
            font-weight: 800;
            line-height: 1;
        }
        @media (max-width: 1100px) {
            .custom-dashboard-card.span-8,
            .custom-dashboard-card.span-6,
            .custom-dashboard-card.span-4 {
                grid-column: span 12;
            }
            .weekly-heatmap-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
            .trip-widget-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 680px) {
            .weekly-heatmap-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .trip-widget-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <h1 class="page-title">{{ __('admin.dashboard.title') }}</h1>
    <p class="page-sub">{{ __('admin.dashboard.subtitle') }}</p>

    <div class="stats-grid" id="dashboard-stats-grid">
        <div
            class="stat-card js-stat-card is-active"
            data-stat-title="{{ __('admin.dashboard.total_flights') }}"
            data-stat-value="{{ number_format($stats['total_flights']) }}"
            data-stat-trend="{{ $trends['flights']['delta'] . ' · ' . $trends['flights']['text'] }}"
            data-stat-trend-class="{{ $trends['flights']['class'] }}"
        >
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24"><path d="M12 3 4 19h16L12 3z"/></svg>
            </div>
            <div class="stat-label">{{ __('admin.dashboard.total_flights') }}</div>
            <div class="stat-value">{{ number_format($stats['total_flights']) }}</div>
            <div class="kpi-trend {{ $trends['flights']['class'] }}">{{ $trends['flights']['delta'] }} {{ $trends['flights']['text'] }}</div>
        </div>
        <div
            class="stat-card js-stat-card"
            data-stat-title="{{ __('admin.dashboard.total_airlines') }}"
            data-stat-value="{{ number_format($stats['total_airlines']) }}"
            data-stat-trend="{{ $trends['airlines']['delta'] . ' · ' . $trends['airlines']['text'] }}"
            data-stat-trend-class="{{ $trends['airlines']['class'] }}"
        >
            <div class="stat-icon purple">
                <svg viewBox="0 0 24 24"><path d="M12 6v12"/><path d="M8 10h8"/><path d="M9 17h6"/></svg>
            </div>
            <div class="stat-label">{{ __('admin.dashboard.total_airlines') }}</div>
            <div class="stat-value">{{ number_format($stats['total_airlines']) }}</div>
            <div class="kpi-trend {{ $trends['airlines']['class'] }}">{{ $trends['airlines']['delta'] }} {{ $trends['airlines']['text'] }}</div>
        </div>
        <div
            class="stat-card js-stat-card"
            data-stat-title="{{ __('admin.dashboard.total_users') }}"
            data-stat-value="{{ number_format($stats['total_users']) }}"
            data-stat-trend="{{ $trends['users']['delta'] . ' · ' . $trends['users']['text'] }}"
            data-stat-trend-class="{{ $trends['users']['class'] }}"
        >
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24"><path d="M16 11a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>
            </div>
            <div class="stat-label">{{ __('admin.dashboard.total_users') }}</div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="kpi-trend {{ $trends['users']['class'] }}">{{ $trends['users']['delta'] }} {{ $trends['users']['text'] }}</div>
        </div>
        <div
            class="stat-card js-stat-card"
            data-stat-title="{{ __('admin.dashboard.total_reports') }}"
            data-stat-value="{{ number_format($stats['total_reports']) }}"
            data-stat-trend="{{ $trends['reports']['delta'] . ' · ' . $trends['reports']['text'] }}"
            data-stat-trend-class="{{ $trends['reports']['class'] }}"
        >
            <div class="stat-icon purple">
                <svg viewBox="0 0 24 24"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/></svg>
            </div>
            <div class="stat-label">{{ __('admin.dashboard.total_reports') }}</div>
            <div class="stat-value">{{ number_format($stats['total_reports']) }}</div>
            <div class="kpi-trend {{ $trends['reports']['class'] }}">{{ $trends['reports']['delta'] }} {{ $trends['reports']['text'] }}</div>
        </div>

        <div
            class="stat-card js-stat-card"
            data-stat-title="Active users"
            data-stat-value="{{ number_format($stats['active_users']) }}"
            data-stat-trend="{{ number_format($stats['active_users']) }} active now"
            data-stat-trend-class="up"
        >
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="stat-label">Active users</div>
            <div class="stat-value">{{ number_format($stats['active_users']) }}</div>
            <div class="kpi-trend up">{{ number_format($stats['active_users']) }} active now</div>
        </div>

        <div
            class="stat-card js-stat-card"
            data-stat-title="Total swap posts"
            data-stat-value="{{ number_format($stats['total_swap_posts']) }}"
            data-stat-trend="{{ $trends['swaps']['delta'] . ' · ' . $trends['swaps']['text'] }}"
            data-stat-trend-class="{{ $trends['swaps']['class'] }}"
        >
            <div class="stat-icon teal">
                <svg viewBox="0 0 24 24"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            </div>
            <div class="stat-label">Total swap posts</div>
            <div class="stat-value">{{ number_format($stats['total_swap_posts']) }}</div>
            <div class="kpi-trend {{ $trends['swaps']['class'] }}">{{ $trends['swaps']['delta'] }} {{ $trends['swaps']['text'] }}</div>
        </div>

        <div
            class="stat-card js-stat-card"
            data-stat-title="Completed swaps"
            data-stat-value="{{ number_format($stats['completed_swaps']) }}"
            data-stat-trend="{{ number_format($stats['pending_swaps']) }} pending review"
            data-stat-trend-class="up"
        >
            <div class="stat-icon orange">
                <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div class="stat-label">Completed swaps</div>
            <div class="stat-value">{{ number_format($stats['completed_swaps']) }}</div>
            <div class="kpi-trend up">{{ number_format($stats['pending_swaps']) }} pending review</div>
        </div>

        <div
            class="stat-card js-stat-card"
            data-stat-title="Pending reports"
            data-stat-value="{{ number_format($stats['open_reports']) }}"
            data-stat-trend="{{ number_format($stats['open_reports']) }} waiting moderation"
            data-stat-trend-class="down"
        >
            <div class="stat-icon red">
                <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="stat-label">Pending reports</div>
            <div class="stat-value">{{ number_format($stats['open_reports']) }}</div>
            <div class="kpi-trend down">{{ number_format($stats['open_reports']) }} waiting moderation</div>
        </div>
    </div>

    <section class="stat-panel" id="dashboard-stat-panel">
        <div class="panel-head">
            <div>
                <h4 id="dashboard-stat-title">{{ __('admin.dashboard.total_flights') }}</h4>
                <p>
                    <span id="dashboard-stat-value">{{ number_format($stats['total_flights']) }}</span>
                    <span class="kpi-trend {{ $trends['flights']['class'] }}" id="dashboard-stat-trend">{{ $trends['flights']['delta'] }} {{ $trends['flights']['text'] }}</span>
                </p>
            </div>
            <button type="button" class="btn-small" id="dashboard-stat-close">Close</button>
        </div>
        <svg viewBox="0 0 720 170" class="panel-chart" aria-hidden="true">
            <path d="M20 152 L95 108 L170 82 L245 67 L320 63 L395 58 L470 52 L545 45 L620 38 L695 20" fill="none" stroke="#2f67d9" stroke-width="3"/>
            <line x1="20" y1="152" x2="700" y2="152" stroke="#cad7ed"/>
        </svg>
    </section>

    <div class="custom-dashboard-grid">
        <div class="custom-dashboard-card span-8">
            <div class="dashboard-block-head">
                <div>
                    <h3>Swap activity chart</h3>
                    <p>Offer posts and ask requests per day for the last week.</p>
                </div>
                <span class="dashboard-chip">{{ number_format($stats['total_swap_posts']) }} posts</span>
            </div>
            <div class="chart-host" id="swap-activity-chart"></div>
        </div>

        <div class="custom-dashboard-card span-4">
            <div class="dashboard-block-head">
                <div>
                    <h3>Swap status</h3>
                    <p>Available, completed, and cancelled swap visibility.</p>
                </div>
            </div>
            <div class="chart-host" id="swap-status-chart"></div>
        </div>

        <div class="custom-dashboard-card span-6">
            <div class="dashboard-block-head">
                <div>
                    <h3>Reports status</h3>
                    <p>Moderation queue across pending, reviewed, and resolved reports.</p>
                </div>
            </div>
            <div class="chart-host" id="report-status-chart"></div>
        </div>

        <div class="custom-dashboard-card span-6">
            <div class="dashboard-block-head">
                <div>
                    <h3>User growth</h3>
                    <p>New users added to the system each day.</p>
                </div>
                <span class="dashboard-chip">{{ number_format($stats['active_users']) }} active users</span>
            </div>
            <div class="chart-host" id="user-growth-chart"></div>
        </div>

        <div class="custom-dashboard-card span-8">
            <div class="dashboard-block-head">
                <div>
                    <h3>Airline activity</h3>
                    <p>Airlines with the highest combined user and flight activity.</p>
                </div>
            </div>
            <div class="chart-host" id="airline-activity-chart"></div>
        </div>

        <div class="custom-dashboard-card span-4">
            <div class="dashboard-block-head">
                <div>
                    <h3>Weekly activity calendar</h3>
                    <p>Combined activity across users, swaps, reports, and messages.</p>
                </div>
            </div>
            <div class="heatmap-host">
                <div class="weekly-heatmap-grid">
                    @foreach($dashboardData['weeklyHeatmap'] as $day)
                        <div class="heatmap-cell">
                            <div class="heatmap-day">{{ $day['label'] }}</div>
                            <div class="heatmap-value">{{ $day['value'] }}</div>
                            <div style="font-size:11px;color:var(--muted)">{{ $day['fullLabel'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="custom-dashboard-card span-12">
            <div class="dashboard-block-head">
                <div>
                    <h3>My trips / Assigned trips</h3>
                    <p>Quick visibility of your own trip posts and current assignment workload.</p>
                </div>
                <span class="dashboard-chip">{{ number_format($tripWidgetStats['my_published_trips']) }} mine</span>
            </div>

            <div class="trip-widget-stats">
                <div class="trip-widget-stat">
                    <span>My Published Trips</span>
                    <strong>{{ number_format($tripWidgetStats['my_published_trips']) }}</strong>
                </div>
                <div class="trip-widget-stat">
                    <span>Total Published Trips</span>
                    <strong>{{ number_format($tripWidgetStats['total_published_trips']) }}</strong>
                </div>
                <div class="trip-widget-stat">
                    <span>Assigned Trips</span>
                    <strong>{{ number_format($tripWidgetStats['assigned_trips']) }}</strong>
                </div>
                <div class="trip-widget-stat">
                    <span>Pending Assignments</span>
                    <strong>{{ number_format($tripWidgetStats['pending_assignments']) }}</strong>
                </div>
            </div>

            <table class="table-mini">
                <thead>
                    <tr>
                        <th>Owner</th>
                        <th>Flight</th>
                        <th>Route</th>
                        <th>Status</th>
                        <th>Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_trips as $trip)
                        <tr>
                            <td>{{ $trip->user->full_name ?? $trip->user->name ?? '—' }}</td>
                            <td>{{ $trip->flight->flight_number ?? '—' }}</td>
                            <td>
                                {{ $trip->flight->departureAirport->code ?? '—' }}
                                →
                                {{ $trip->flight->arrivalAirport->code ?? '—' }}
                            </td>
                            <td><span class="badge badge-gray">{{ ucfirst(str_replace('_', ' ', $trip->status ?? 'unknown')) }}</span></td>
                            <td>{{ (int) $trip->assigned_requests_count }} approved • {{ (int) $trip->pending_requests_count }} pending</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--muted)">No trip records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="custom-dashboard-card span-6">
            <div class="dashboard-card-header">Recent swap requests <span style="font-size:13px;color:var(--muted)">Latest activity</span></div>
            <table class="table-mini">
                <thead>
                    <tr>
                        <th>Requester</th>
                        <th>Flight</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_swaps as $swap)
                        <tr>
                            <td>{{ $swap->requester->full_name ?? '—' }}</td>
                            <td>{{ $swap->publishedTrip->flight->flight_number ?? '—' }}</td>
                            <td><span class="badge badge-gray">{{ str_replace('_', ' ', $swap->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;color:var(--muted)">No swap activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="custom-dashboard-card span-6">
            <div class="dashboard-card-header">Recent reports <span style="font-size:13px;color:var(--muted)">Moderation feed</span></div>
            <table class="table-mini">
                <thead>
                    <tr>
                        <th>Reporter</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_reports as $report)
                        <tr>
                            <td>{{ $report->reporter->full_name ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $report->reason)) }}</td>
                            <td><span class="badge badge-gray">{{ __('admin.status_values.' . $report->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;color:var(--muted)">No reports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="custom-dashboard-card span-12" style="margin-top: 20px;">
            <div class="dashboard-block-head">
                <div>
                    <h3>User Management</h3>
                    <p>Manage all crew members and employees</p>
                </div>
            </div>
            
            <div style="padding: 14px 16px; border-top: 1px solid var(--border);">
                <div class="actions" style="margin-bottom: 14px;">
                    <div class="search-box">
                        <input type="text" class="js-users-search" placeholder="Search by name, email, or employee ID..." />
                    </div>
                </div>

                <div class="table-wrap" style="border-radius: 0; overflow-x: auto;">
                    <table class="table-mini" style="width: 100%; font-size: 13px;">
                        <thead>
                            <tr style="background: rgba(15,23,42,.03);">
                                <th style="padding: 10px 8px; text-align: left;"><input type="checkbox" class="select-all-checkbox" /></th>
                                <th style="padding: 10px 8px; text-align: left;">Employee ID</th>
                                <th style="padding: 10px 8px; text-align: left;">Full Name</th>
                                <th style="padding: 10px 8px; text-align: left;">Email Address</th>
                                <th style="padding: 10px 8px; text-align: left;">Phone Number</th>
                                <th style="padding: 10px 8px; text-align: left;">Airline</th>
                                <th style="padding: 10px 8px; text-align: left;">Plane Type</th>
                                <th style="padding: 10px 8px; text-align: left;">Position</th>
                                <th style="padding: 10px 8px; text-align: left;">Status</th>
                                <th style="padding: 10px 8px; text-align: left;">Verified</th>
                                <th style="padding: 10px 8px; text-align: left;">Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr style="border-bottom: 1px solid var(--border); hover {{ 'background: rgba(217,225,239,.3);' }}">
                                    <td style="padding: 10px 8px;"><input type="checkbox" class="user-checkbox" value="{{ $user->id }}" /></td>
                                    <td style="padding: 10px 8px; color: #2563eb; font-weight: 600;">{{ $user->employee_id ?? '—' }}</td>
                                    <td style="padding: 10px 8px;">{{ $user->full_name ?? '—' }}</td>
                                    <td style="padding: 10px 8px; color: var(--muted);">{{ $user->email ?? '—' }}</td>
                                    <td style="padding: 10px 8px;">{{ $user->phone ?? '—' }}</td>
                                    <td style="padding: 10px 8px;">{{ $user->airline?->name ?? '—' }}</td>
                                    <td style="padding: 10px 8px;">{{ $user->planeType?->name ?? '—' }}</td>
                                    <td style="padding: 10px 8px;">{{ $user->position?->name ?? '—' }}</td>
                                    <td style="padding: 10px 8px;">
                                        <span class="badge {{ $user->status === 'active' ? 'badge-success' : ($user->status === 'blocked' ? 'badge-danger' : 'badge-warning') }}" style="font-size: 11px; padding: 4px 8px;">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 10px 8px;">
                                        {{ $user->phone_verified_at ? '✓ Yes' : '✗ No' }}
                                    </td>
                                    <td style="padding: 10px 8px; color: var(--muted); font-size: 12px;">
                                        {{ $user->created_at?->format('M d, Y') ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" style="padding: 24px 8px; text-align: center; color: var(--muted);">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                        <span style="color: var(--muted);">Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users</span>
                        <div class="pagination" style="margin: 0;">
                            {{ $users->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (() => {
            const dashboardData = @js($dashboardData);

            function renderLineChart(targetId, config) {
                const host = document.getElementById(targetId);
                if (!host) return;

                const width = 720;
                const height = 220;
                const padding = { top: 16, right: 18, bottom: 24, left: 18 };
                const maxValue = Math.max(...config.series.flatMap((series) => series.values), 1);
                const chartWidth = width - padding.left - padding.right;
                const chartHeight = height - padding.top - padding.bottom;
                const stepX = config.labels.length > 1 ? chartWidth / (config.labels.length - 1) : chartWidth;

                const grid = Array.from({ length: 4 }, (_, index) => {
                    const y = padding.top + (chartHeight / 3) * index;
                    return `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="rgba(158,184,236,.35)" stroke-dasharray="4 6" />`;
                }).join('');

                const paths = config.series.map((series) => {
                    const points = series.values.map((value, index) => ({
                        x: padding.left + stepX * index,
                        y: padding.top + chartHeight - ((value / maxValue) * chartHeight),
                        value,
                    }));

                    const d = points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' ');
                    const circles = points.map((point) => `<circle cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4" fill="${series.color}" stroke="#fff" stroke-width="2"></circle>`).join('');
                    return `<path d="${d}" fill="none" stroke="${series.color}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>${circles}`;
                }).join('');

                const axis = `<div class="chart-axis-row" style="grid-template-columns:repeat(${config.labels.length}, minmax(0, 1fr));">${config.labels.map((label) => `<span>${label}</span>`).join('')}</div>`;
                const legend = `<div class="chart-legend-row">${config.series.map((series) => `<span class="chart-legend-item"><span class="chart-legend-swatch" style="background:${series.color}"></span>${series.label}</span>`).join('')}</div>`;

                host.innerHTML = `<svg viewBox="0 0 ${width} ${height}">${grid}${paths}</svg>${axis}${legend}`;
            }

            function renderBarChart(targetId, rows, horizontal = false) {
                const host = document.getElementById(targetId);
                if (!host) return;

                const maxValue = Math.max(...rows.map((row) => row.value), 1);

                if (horizontal) {
                    host.innerHTML = rows.map((row) => `
                        <div style="padding:10px 16px 0;">
                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px;">
                                <span>${row.label}</span>
                                <span>${row.value}</span>
                            </div>
                            <div style="height:12px;background:#ccd7ea;border-radius:999px;overflow:hidden;">
                                <div style="height:100%;width:${(row.value / maxValue) * 100}%;background:${row.color};border-radius:999px;"></div>
                            </div>
                        </div>
                    `).join('');
                    return;
                }

                host.innerHTML = `
                    <div style="display:grid;grid-template-columns:repeat(${rows.length}, minmax(0, 1fr));gap:10px;align-items:end;height:220px;padding:14px 16px 6px;">
                        ${rows.map((row) => `
                            <div style="display:flex;flex-direction:column;justify-content:flex-end;gap:8px;height:100%;text-align:center;">
                                <strong style="font-size:12px;color:var(--text);">${row.value}</strong>
                                <div style="height:${Math.max((row.value / maxValue) * 160, row.value > 0 ? 16 : 0)}px;background:${row.color};border-radius:12px 12px 6px 6px;"></div>
                                <span style="font-size:12px;color:var(--muted);font-weight:600;">${row.label}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            function renderDonutChart(targetId, rows) {
                const host = document.getElementById(targetId);
                if (!host) return;

                const total = rows.reduce((sum, row) => sum + row.value, 0);
                if (!total) {
                    host.innerHTML = '<div style="padding:16px;color:var(--muted);">No data available.</div>';
                    return;
                }

                let cursor = 0;
                const gradient = rows.map((row) => {
                    const next = cursor + ((row.value / total) * 360);
                    const segment = `${row.color} ${cursor}deg ${next}deg`;
                    cursor = next;
                    return segment;
                }).join(', ');

                host.innerHTML = `
                    <div style="display:grid;place-items:center;padding:10px 16px 16px;">
                        <div style="width:150px;height:150px;border-radius:50%;background:conic-gradient(${gradient});display:grid;place-items:center;">
                            <div style="width:86px;height:86px;border-radius:50%;background:#d7e1ef;display:grid;place-items:center;text-align:center;color:var(--text);font-weight:700;">
                                <span>${total}<br><small style="font-size:11px;color:var(--muted);font-weight:600;">items</small></span>
                            </div>
                        </div>
                        <div class="chart-legend-row">${rows.map((row) => `<span class="chart-legend-item"><span class="chart-legend-swatch" style="background:${row.color}"></span>${row.label} (${row.value})</span>`).join('')}</div>
                    </div>
                `;
            }

            function initCustomDashboard() {
                renderLineChart('swap-activity-chart', dashboardData.swapActivity);
                renderDonutChart('swap-status-chart', dashboardData.swapStatus);
                renderBarChart('report-status-chart', dashboardData.reportStatus);
                renderLineChart('user-growth-chart', dashboardData.userGrowth);
                renderBarChart('airline-activity-chart', dashboardData.airlineActivity, true);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCustomDashboard, { once: true });
            } else {
                initCustomDashboard();
            }

            // User search functionality
            const searchInput = document.querySelector('.js-users-search');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
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
            }

            // Select all checkbox functionality
            const selectAllCheckbox = document.querySelector('.select-all-checkbox');
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    userCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }
            
            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(userCheckboxes).some(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                });
            });
        })();
    </script>
@endsection
