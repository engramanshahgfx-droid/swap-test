<div class="admin-dashboard-wrapper">
    <style>
        .admin-dashboard {
            --dash-bg: linear-gradient(180deg, #eef4ff 0%, #f8fbff 100%);
            --dash-surface: rgba(255, 255, 255, 0.92);
            --dash-border: rgba(148, 163, 184, 0.24);
            --dash-shadow: 0 18px 42px rgba(37, 99, 235, 0.08);
            --dash-text: #0f172a;
            --dash-muted: #64748b;
            --dash-blue: #2563eb;
            --dash-green: #059669;
            --dash-red: #dc2626;
            --dash-orange: #ea580c;
            display: grid;
            gap: 1.5rem;
            color: var(--dash-text);
        }

        .dash-hero {
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.22), transparent 38%), var(--dash-bg);
            border: 1px solid var(--dash-border);
            border-radius: 24px;
            padding: 1.75rem;
            box-shadow: var(--dash-shadow);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dash-eyebrow {
            font-size: 0.78rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--dash-blue);
            font-weight: 700;
            margin-bottom: 0.6rem;
        }

        .dash-title {
            font-size: clamp(2rem, 4vw, 3.4rem);
            line-height: 0.95;
            font-weight: 900;
            letter-spacing: -0.04em;
            margin: 0;
        }

        .dash-subtitle {
            margin: 0.65rem 0 0;
            font-size: 1rem;
            color: var(--dash-muted);
            max-width: 55rem;
        }

        .dash-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            align-items: center;
            justify-content: flex-end;
        }

        .dash-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 0.9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(37, 99, 235, 0.1);
            font-size: 0.82rem;
            color: #1e3a8a;
            font-weight: 700;
        }

        .dash-card-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .dash-resource-card {
            background: var(--dash-surface);
            border: 1px solid var(--dash-border);
            border-radius: 22px;
            padding: 1.15rem;
            box-shadow: var(--dash-shadow);
            text-decoration: none;
            color: inherit;
            display: grid;
            gap: 0.85rem;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
        }

        .dash-resource-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 45px rgba(37, 99, 235, 0.14);
            border-color: rgba(37, 99, 235, 0.22);
        }

        .dash-resource-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.8rem;
        }

        .dash-resource-icon {
            width: 2.9rem;
            height: 2.9rem;
            border-radius: 16px;
            display: grid;
            place-items: center;
            font-weight: 900;
            color: white;
            font-size: 0.95rem;
        }

        .tone-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .tone-sky { background: linear-gradient(135deg, #0284c7, #0369a1); }
        .tone-violet { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
        .tone-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .tone-emerald { background: linear-gradient(135deg, #10b981, #047857); }
        .tone-rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }
        .tone-indigo { background: linear-gradient(135deg, #6366f1, #4338ca); }
        .tone-red { background: linear-gradient(135deg, #ef4444, #b91c1c); }

        .dash-resource-value {
            font-size: 2.15rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .dash-resource-label {
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #334155;
        }

        .dash-resource-meta {
            color: var(--dash-muted);
            font-size: 0.84rem;
        }

        .dash-resource-trend {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.82rem;
            font-weight: 800;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            width: fit-content;
        }

        .trend-up { background: rgba(16, 185, 129, 0.12); color: #047857; }
        .trend-down { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
        .trend-flat { background: rgba(148, 163, 184, 0.16); color: #475569; }

        .dash-layout {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 1rem;
        }

        .dash-panel {
            background: var(--dash-surface);
            border: 1px solid var(--dash-border);
            border-radius: 24px;
            box-shadow: var(--dash-shadow);
            padding: 1.2rem;
            min-height: 100%;
        }

        .span-12 { grid-column: span 12; }
        .span-8 { grid-column: span 8; }
        .span-6 { grid-column: span 6; }
        .span-4 { grid-column: span 4; }

        .dash-panel-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .dash-panel-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .dash-panel-copy {
            margin: 0.28rem 0 0;
            color: var(--dash-muted);
            font-size: 0.86rem;
        }

        .dash-mini-badge {
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.08);
            color: var(--dash-blue);
            padding: 0.45rem 0.7rem;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .chart-host {
            min-height: 18rem;
            position: relative;
        }

        .chart-host svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .chart-axis-labels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
            gap: 0.3rem;
            margin-top: 0.75rem;
            color: var(--dash-muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.9rem;
        }

        .chart-legend-item {
            display: inline-flex;
            gap: 0.5rem;
            align-items: center;
            font-size: 0.82rem;
            color: #334155;
            font-weight: 700;
        }

        .chart-legend-swatch {
            width: 0.8rem;
            height: 0.8rem;
            border-radius: 999px;
        }

        .dash-list {
            display: grid;
            gap: 0.7rem;
        }

        .dash-list-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.9rem;
            padding: 0.9rem 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        }

        .dash-list-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .dash-list-title {
            margin: 0;
            font-weight: 800;
            color: #0f172a;
        }

        .dash-list-subtitle,
        .dash-list-meta {
            margin: 0.18rem 0 0;
            font-size: 0.82rem;
            color: var(--dash-muted);
        }

        .dash-list-status {
            align-self: center;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.08);
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 800;
            padding: 0.45rem 0.7rem;
            white-space: nowrap;
        }

        .dash-snapshot-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .dash-snapshot-card {
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(241,245,249,0.95));
            border: 1px solid rgba(148, 163, 184, 0.18);
            padding: 1rem;
        }

        .dash-snapshot-value {
            font-size: 1.7rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
        }

        .dash-snapshot-label {
            margin-top: 0.4rem;
            font-size: 0.82rem;
            color: var(--dash-muted);
            font-weight: 700;
        }

        .empty-state {
            display: grid;
            place-items: center;
            min-height: 10rem;
            color: var(--dash-muted);
            font-weight: 700;
            text-align: center;
        }

        @media (max-width: 1280px) {
            .dash-card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .span-8,
            .span-6,
            .span-4 {
                grid-column: span 12;
            }
        }

        @media (max-width: 720px) {
            .dash-card-grid,
            .dash-snapshot-grid {
                grid-template-columns: 1fr;
            }

            .dash-hero {
                padding: 1.2rem;
            }
        }
    </style>

    <div class="admin-dashboard">
        <section class="dash-hero">
            <div>
                <div class="dash-eyebrow">Custom Admin Dashboard</div>
                <h1 class="dash-title">CrewSwap control center</h1>
                <p class="dash-subtitle">
                    This dashboard now pulls real Filament resource data and shows users, flights, airlines, positions,
                    airports, plane types, swaps, reports, and communication activity in one custom HTML, CSS, and JavaScript view.
                </p>
            </div>

            <div class="dash-meta">
                <span class="dash-pill">Resources: {{ count($resourceCards) }}</span>
                <span class="dash-pill">Updated: {{ $lastUpdated }}</span>
            </div>
        </section>

        <section class="dash-card-grid">
            @foreach ($resourceCards as $card)
                <a href="{{ $card['url'] }}" class="dash-resource-card">
                    <div class="dash-resource-top">
                        <div>
                            <div class="dash-resource-label">{{ $card['label'] }}</div>
                            <div class="dash-resource-value">{{ number_format($card['value']) }}</div>
                        </div>
                        <div class="dash-resource-icon tone-{{ $card['tone'] }}">
                            {{ strtoupper(substr($card['label'], 0, 1)) }}
                        </div>
                    </div>

                    <div class="dash-resource-meta">{{ $card['meta'] }}</div>

                    <div class="dash-resource-trend trend-{{ $card['trend'] }}">
                        <span>{{ $card['deltaLabel'] }}</span>
                        <span>{{ $card['deltaText'] }}</span>
                    </div>
                </a>
            @endforeach
        </section>

        <section class="dash-layout">
            <article class="dash-panel span-8">
                <div class="dash-panel-head">
                    <div>
                        <h2 class="dash-panel-title">Platform growth</h2>
                        <p class="dash-panel-copy">Real 7-day creation trends for users, flights, swaps, and reports.</p>
                    </div>
                    <span class="dash-mini-badge">Last 7 days</span>
                </div>
                <div id="growth-chart" class="chart-host"></div>
                <div id="growth-legend" class="chart-legend"></div>
            </article>

            <article class="dash-panel span-4">
                <div class="dash-panel-head">
                    <div>
                        <h2 class="dash-panel-title">Swap workflow</h2>
                        <p class="dash-panel-copy">Pending, owner-approved, completed, and rejected requests.</p>
                    </div>
                </div>
                <div id="swap-status-chart" class="chart-host"></div>
            </article>

            <article class="dash-panel span-4">
                <div class="dash-panel-head">
                    <div>
                        <h2 class="dash-panel-title">Report moderation</h2>
                        <p class="dash-panel-copy">Pending, reviewed, and resolved moderation workload.</p>
                    </div>
                </div>
                <div id="report-status-chart" class="chart-host"></div>
            </article>

            <article class="dash-panel span-8">
                <div class="dash-panel-head">
                    <div>
                        <h2 class="dash-panel-title">Airline activity</h2>
                        <p class="dash-panel-copy">Top airlines ranked by combined crew and flight activity.</p>
                    </div>
                    <span class="dash-mini-badge">Top 6</span>
                </div>
                <div id="airline-activity-chart" class="chart-host"></div>
            </article>

            <article class="dash-panel span-4">
                <div class="dash-panel-head">
                    <div>
                        <h2 class="dash-panel-title">Service snapshot</h2>
                        <p class="dash-panel-copy">Live supporting metrics from communication and trip modules.</p>
                    </div>
                </div>
                <div class="dash-snapshot-grid">
                    @foreach ($serviceStats as $stat)
                        <div class="dash-snapshot-card">
                            <div class="dash-snapshot-value">{{ number_format($stat['value']) }}</div>
                            <div class="dash-snapshot-label">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="dash-panel span-4">
                <div class="dash-panel-head">
                    <div>
                        <h2 class="dash-panel-title">Recent users</h2>
                        <p class="dash-panel-copy">Latest crew accounts entering the platform.</p>
                    </div>
                </div>
                <div class="dash-list">
                    @forelse ($recentUsers as $item)
                        <div class="dash-list-row">
                            <div>
                                <p class="dash-list-title">{{ $item['title'] }}</p>
                                <p class="dash-list-subtitle">{{ $item['subtitle'] }}</p>
                                <p class="dash-list-meta">{{ $item['meta'] }} · {{ $item['timestamp'] }}</p>
                            </div>
                            <div class="dash-list-status">{{ $item['status'] }}</div>
                        </div>
                    @empty
                        <div class="empty-state">No user records yet.</div>
                    @endforelse
                </div>
            </article>

            <article class="dash-panel span-4">
                <div class="dash-panel-head">
                    <div>
                        <h2 class="dash-panel-title">Recent swaps</h2>
                        <p class="dash-panel-copy">Latest swap activity visible from the operations module.</p>
                    </div>
                </div>
                <div class="dash-list">
                    @forelse ($recentSwaps as $item)
                        <div class="dash-list-row">
                            <div>
                                <p class="dash-list-title">{{ $item['title'] }}</p>
                                <p class="dash-list-subtitle">{{ $item['subtitle'] }}</p>
                                <p class="dash-list-meta">{{ $item['meta'] }} · {{ $item['timestamp'] }}</p>
                            </div>
                            <div class="dash-list-status">{{ $item['status'] }}</div>
                        </div>
                    @empty
                        <div class="empty-state">No swap requests yet.</div>
                    @endforelse
                </div>
            </article>

            <article class="dash-panel span-4">
                <div class="dash-panel-head">
                    <div>
                        <h2 class="dash-panel-title">Recent reports</h2>
                        <p class="dash-panel-copy">Latest moderation items coming from the reports resource.</p>
                    </div>
                </div>
                <div class="dash-list">
                    @forelse ($recentReports as $item)
                        <div class="dash-list-row">
                            <div>
                                <p class="dash-list-title">{{ $item['title'] }}</p>
                                <p class="dash-list-subtitle">{{ $item['subtitle'] }}</p>
                                <p class="dash-list-meta">{{ $item['meta'] }} · {{ $item['timestamp'] }}</p>
                            </div>
                            <div class="dash-list-status">{{ $item['status'] }}</div>
                        </div>
                    @empty
                        <div class="empty-state">No reports submitted yet.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>

    <script>
        (() => {
            const dashboardData = @js($dashboardData);

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderLineChart(targetId, legendId, config) {
                const host = document.getElementById(targetId);
                const legend = document.getElementById(legendId);

                if (!host) {
                    return;
                }

                const width = 760;
                const height = 280;
                const padding = { top: 18, right: 18, bottom: 24, left: 18 };
                const allValues = config.series.flatMap((series) => series.values);
                const maxValue = Math.max(...allValues, 1);
                const chartWidth = width - padding.left - padding.right;
                const chartHeight = height - padding.top - padding.bottom;

                const stepX = config.labels.length > 1 ? chartWidth / (config.labels.length - 1) : chartWidth;
                const gridLines = 4;

                const grid = Array.from({ length: gridLines }, (_, index) => {
                    const y = padding.top + (chartHeight / (gridLines - 1)) * index;
                    return `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="rgba(148,163,184,0.25)" stroke-dasharray="4 6" />`;
                }).join('');

                const paths = config.series.map((series) => {
                    const points = series.values.map((value, index) => {
                        const x = padding.left + stepX * index;
                        const y = padding.top + chartHeight - ((value / maxValue) * chartHeight);
                        return { x, y, value };
                    });

                    const d = points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' ');
                    const circles = points.map((point) => `<circle cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4" fill="${series.color}" stroke="#fff" stroke-width="2"></circle>`).join('');

                    return `
                        <path d="${d}" fill="none" stroke="${series.color}" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        ${circles}
                    `;
                }).join('');

                host.innerHTML = `
                    <svg viewBox="0 0 ${width} ${height}" role="img" aria-label="Platform growth chart">
                        ${grid}
                        ${paths}
                    </svg>
                    <div class="chart-axis-labels">
                        ${config.labels.map((label) => `<span>${escapeHtml(label)}</span>`).join('')}
                    </div>
                `;

                if (legend) {
                    legend.innerHTML = config.series.map((series) => `
                        <span class="chart-legend-item">
                            <span class="chart-legend-swatch" style="background:${series.color}"></span>
                            <span>${escapeHtml(series.label)}</span>
                        </span>
                    `).join('');
                }
            }

            function renderBarChart(targetId, rows, options = {}) {
                const host = document.getElementById(targetId);

                if (!host) {
                    return;
                }

                const maxValue = Math.max(...rows.map((row) => row.value), 1);
                const horizontal = options.horizontal === true;

                if (horizontal) {
                    host.innerHTML = `
                        <div style="display:grid;gap:0.9rem;align-content:start;min-height:18rem;">
                            ${rows.map((row) => `
                                <div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.35rem;font-size:0.82rem;font-weight:800;color:#334155;">
                                        <span>${escapeHtml(row.label)}</span>
                                        <span>${row.value}</span>
                                    </div>
                                    <div style="height:12px;border-radius:999px;background:rgba(148,163,184,0.16);overflow:hidden;">
                                        <div style="width:${(row.value / maxValue) * 100}%;height:100%;border-radius:999px;background:${row.color};"></div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    return;
                }

                host.innerHTML = `
                    <div style="display:grid;grid-template-columns:repeat(${Math.max(rows.length, 1)}, minmax(0, 1fr));align-items:end;gap:0.85rem;min-height:18rem;">
                        ${rows.map((row) => `
                            <div style="display:grid;gap:0.55rem;align-items:end;">
                                <div style="text-align:center;font-size:0.8rem;font-weight:900;color:#0f172a;">${row.value}</div>
                                <div style="height:220px;display:flex;align-items:flex-end;justify-content:center;">
                                    <div style="width:100%;height:${Math.max((row.value / maxValue) * 100, row.value > 0 ? 10 : 0)}%;border-radius:18px 18px 8px 8px;background:${row.color};box-shadow:inset 0 -14px 18px rgba(255,255,255,0.14);"></div>
                                </div>
                                <div style="text-align:center;font-size:0.8rem;font-weight:800;color:#64748b;">${escapeHtml(row.label)}</div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            function renderDonutChart(targetId, rows) {
                const host = document.getElementById(targetId);

                if (!host) {
                    return;
                }

                const total = rows.reduce((sum, row) => sum + row.value, 0);

                if (total === 0) {
                    host.innerHTML = '<div class="empty-state">No data available yet.</div>';
                    return;
                }

                let cursor = 0;
                const segments = rows.map((row) => {
                    const size = (row.value / total) * 360;
                    const segment = `${row.color} ${cursor}deg ${cursor + size}deg`;
                    cursor += size;
                    return segment;
                }).join(', ');

                host.innerHTML = `
                    <div style="display:grid;place-items:center;gap:1rem;min-height:18rem;">
                        <div style="width:210px;height:210px;border-radius:50%;background:conic-gradient(${segments});display:grid;place-items:center;box-shadow:inset 0 0 0 1px rgba(255,255,255,0.9);">
                            <div style="width:118px;height:118px;border-radius:50%;background:#fff;display:grid;place-items:center;text-align:center;padding:0.75rem;box-shadow:0 8px 22px rgba(15,23,42,0.08);">
                                <strong style="font-size:2rem;line-height:1;font-weight:900;letter-spacing:-0.04em;">${total}</strong>
                                <span style="font-size:0.8rem;color:#64748b;font-weight:700;">total items</span>
                            </div>
                        </div>
                        <div class="chart-legend">
                            ${rows.map((row) => `
                                <span class="chart-legend-item">
                                    <span class="chart-legend-swatch" style="background:${row.color}"></span>
                                    <span>${escapeHtml(row.label)} (${row.value})</span>
                                </span>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            function initDashboardCharts() {
                renderLineChart('growth-chart', 'growth-legend', dashboardData.growth);
                renderBarChart('swap-status-chart', dashboardData.swapStatus);
                renderDonutChart('report-status-chart', dashboardData.reportStatus);
                renderBarChart('airline-activity-chart', dashboardData.airlineActivity, { horizontal: true });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDashboardCharts, { once: true });
            } else {
                initDashboardCharts();
            }

            document.addEventListener('livewire:navigated', initDashboardCharts);
        })();
    </script>
</div>
