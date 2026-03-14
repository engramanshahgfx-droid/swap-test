@extends('layouts.app')

@section('title', __('admin.reports.title'))
@section('page-name', 'reports')

@section('content')
    <h1 class="page-title">{{ __('admin.reports.title') }}</h1>
    <p class="page-sub">{{ __('admin.reports.subtitle') }}</p>

    <div class="actions">
        <div class="search-box">
            <input type="text" class="js-table-search" placeholder="{{ __('admin.reports.search') }}" />
        </div>
        <form method="GET" action="{{ route('reports') }}" style="display:contents">
            @if(request('report_id'))
                <input type="hidden" name="report_id" value="{{ request('report_id') }}">
            @endif
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">{{ __('admin.all_statuses') }}</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>{{ __('admin.status_values.pending') }}</option>
                <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>{{ __('admin.status_values.reviewed') }}</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>{{ __('admin.status_values.resolved') }}</option>
            </select>
        </form>
    </div>

    <div class="table-wrap">
        <table id="reports-table">
            <thead>
                <tr>
                    <th>{{ __('admin.reports.reporter') }}</th>
                    <th>{{ __('admin.reports.reported_user') }}</th>
                    <th>{{ __('admin.reports.reason') }}</th>
                    <th>{{ __('admin.reports.details') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('admin.reports.date') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        $rc = match($report->status) {
                            'resolved' => 'badge-success',
                            'reviewed' => 'badge-info',
                            'pending'  => 'badge-warning',
                            default    => 'badge-gray',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">{{ strtoupper(substr($report->reporter->full_name ?? 'U', 0, 1)) }}</div>
                                <div class="user-name">{{ $report->reporter->full_name ?? __('admin.none') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar" style="background:#fee2e2;color:#dc2626">{{ strtoupper(substr($report->reportedUser->full_name ?? 'U', 0, 1)) }}</div>
                                <div class="user-name">{{ $report->reportedUser->full_name ?? __('admin.none') }}</div>
                            </div>
                        </td>
                        <td>{{ $report->reason }}</td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $report->details }}">
                            {{ Str::limit($report->details, 60) }}
                        </td>
                        <td><span class="badge {{ $rc }}">{{ __('admin.status_values.' . $report->status) }}</span></td>
                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="tbl-actions">
                                <a class="tbl-btn tbl-btn-view" href="{{ route('reports', array_filter(['status' => request('status'), 'report_id' => $report->id])) }}">{{ __('admin.reports.open_button') }}</a>
                                <button class="tbl-btn tbl-btn-edit" type="button"
                                    onclick="setReportStatus({{ $report->id }}, '{{ $report->status }}')">
                                    {{ __('admin.reports.update_button') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">{{ __('admin.reports.no_results') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>{{ __('admin.showing_results', ['from' => $reports->firstItem() ?? 0, 'to' => $reports->lastItem() ?? 0, 'total' => $reports->total()]) }}</span>
        <div class="pagination">{{ $reports->links() }}</div>
    </div>

    @if($selectedReport)
        <div class="support-layout" style="margin-top:20px;align-items:flex-start;">
            <section class="support-main" style="min-width:0;">
                <div class="table-wrap" style="padding:16px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <div class="page-sub" style="margin:0 0 8px;">{{ __('admin.reports.selected_report') }}</div>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            <span class="badge badge-info">#{{ $selectedReport->id }}</span>
                            <span class="badge badge-gray">{{ ucfirst(str_replace('_', ' ', $selectedReport->reason)) }}</span>
                            <span class="badge {{ $selectedReport->status === 'resolved' ? 'badge-success' : ($selectedReport->status === 'reviewed' ? 'badge-info' : 'badge-warning') }}">{{ __('admin.status_values.' . $selectedReport->status) }}</span>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
                        <div>
                            <div style="font-weight:700;margin-bottom:8px;">{{ __('admin.reports.reporter') }}</div>
                            <div>{{ $selectedReport->reporter->full_name ?? __('admin.none') }}</div>
                            <div style="color:var(--muted);font-size:13px;">{{ $selectedReport->reporter->email ?? __('admin.none') }}</div>
                            <div style="margin-top:8px;"><a href="{{ route('activation', ['user_id' => $selectedReport->reporter_id]) }}">{{ __('admin.reports.open_reporter_profile') }}</a></div>
                        </div>
                        <div>
                            <div style="font-weight:700;margin-bottom:8px;">{{ __('admin.reports.reported_user') }}</div>
                            <div>{{ $selectedReport->reportedUser->full_name ?? __('admin.none') }}</div>
                            <div style="color:var(--muted);font-size:13px;">{{ $selectedReport->reportedUser->email ?? __('admin.none') }}</div>
                            <div style="margin-top:8px;"><a href="{{ route('activation', ['user_id' => $selectedReport->reported_user_id]) }}">{{ __('admin.reports.open_reported_profile') }}</a></div>
                        </div>
                        <div>
                            <div style="font-weight:700;margin-bottom:8px;">{{ __('admin.reports.review_summary') }}</div>
                            <div>{{ __('admin.reports.reviewed_by') }}: {{ $selectedReport->reviewedBy->full_name ?? __('admin.none') }}</div>
                            <div>{{ __('admin.reports.reviewed_at') }}: {{ $selectedReport->reviewed_at?->format('M d, Y h:i A') ?? __('admin.none') }}</div>
                            <div>{{ __('admin.reports.date') }}: {{ $selectedReport->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>

                    <div>
                        <div style="font-weight:700;margin-bottom:8px;">{{ __('admin.reports.reporter_message') }}</div>
                        <div style="border:1px solid var(--line);border-radius:14px;padding:14px;min-height:96px;background:#fff;white-space:pre-wrap;">{{ $selectedReport->details ?: __('admin.none') }}</div>
                    </div>

                    <form method="POST" action="{{ route('reports.status', $selectedReport) }}" style="display:flex;flex-direction:column;gap:12px;">
                        @csrf
                        @method('PUT')
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
                            <div>
                                <label>{{ __('admin.status') }}</label>
                                <select name="status">
                                    <option value="pending" {{ $selectedReport->status === 'pending' ? 'selected' : '' }}>{{ __('admin.status_values.pending') }}</option>
                                    <option value="reviewed" {{ $selectedReport->status === 'reviewed' ? 'selected' : '' }}>{{ __('admin.status_values.reviewed') }}</option>
                                    <option value="resolved" {{ $selectedReport->status === 'resolved' ? 'selected' : '' }}>{{ __('admin.status_values.resolved') }}</option>
                                </select>
                            </div>
                            <div>
                                <label>{{ __('admin.reports.resolution_note') }}</label>
                                <input type="text" name="resolution" value="{{ old('resolution', $selectedReport->resolution) }}" placeholder="{{ __('admin.reports.resolution_placeholder') }}">
                            </div>
                        </div>
                        <div>
                            <label>{{ __('admin.reports.admin_notes') }}</label>
                            <textarea name="admin_notes" rows="4" placeholder="{{ __('admin.reports.admin_notes_placeholder') }}">{{ old('admin_notes', $selectedReport->admin_notes) }}</textarea>
                        </div>
                        <div class="modal-footer" style="padding:0;">
                            <button type="submit">{{ __('admin.reports.submit_review') }}</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('reports.moderate', $selectedReport) }}" style="display:flex;flex-direction:column;gap:12px;border-top:1px solid var(--line);padding-top:12px;">
                        @csrf
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
                            <div>
                                <label>{{ __('admin.reports.moderation_action') }}</label>
                                <select name="action">
                                    <option value="ban">{{ __('admin.reports.action_ban') }}</option>
                                    <option value="deactivate">{{ __('admin.reports.action_deactivate') }}</option>
                                    <option value="reject">{{ __('admin.reports.action_reject') }}</option>
                                </select>
                            </div>
                            <div>
                                <label>{{ __('admin.reports.ban_duration') }}</label>
                                <select name="ban_duration">
                                    <option value="2_days">{{ __('admin.reports.duration_2_days') }}</option>
                                    <option value="1_week">{{ __('admin.reports.duration_1_week') }}</option>
                                    <option value="1_month">{{ __('admin.reports.duration_1_month') }}</option>
                                    <option value="permanent">{{ __('admin.reports.duration_permanent') }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label>{{ __('admin.reports.reporter_response') }}</label>
                            <textarea name="reporter_message" rows="3" placeholder="{{ __('admin.reports.reporter_response_placeholder') }}"></textarea>
                        </div>
                        <div>
                            <label>{{ __('admin.reports.admin_notes') }}</label>
                            <textarea name="admin_notes" rows="3" placeholder="{{ __('admin.reports.admin_notes_placeholder') }}"></textarea>
                        </div>
                        <div class="modal-footer" style="padding:0;">
                            <button type="submit">{{ __('admin.reports.apply_moderation') }}</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="support-main" style="min-width:0;">
                <div class="table-wrap" style="padding:16px;display:flex;flex-direction:column;gap:12px;min-height:100%;">
                    <div>
                        <div class="page-sub" style="margin:0 0 8px;">{{ __('admin.reports.related_chat') }}</div>
                        @if($relatedConversation)
                            <div style="color:var(--muted);font-size:13px;">{{ ($relatedConversation->userOne->full_name ?? __('admin.none')) . ' ↔ ' . ($relatedConversation->userTwo->full_name ?? __('admin.none')) }}</div>
                        @else
                            <div style="color:var(--muted);font-size:13px;">{{ __('admin.reports.no_related_chat') }}</div>
                        @endif
                    </div>

                    @if($conversationMessages->isEmpty())
                        <div style="color:var(--muted);font-size:13px;">{{ __('admin.reports.no_chat_messages') }}</div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:10px;max-height:560px;overflow:auto;padding-right:4px;">
                            @foreach($conversationMessages as $message)
                                <div style="display:flex;gap:8px;align-items:flex-start;">
                                    <div class="user-avatar" style="font-size:11px;width:28px;height:28px">{{ strtoupper(substr($message->sender->full_name ?? 'U', 0, 1)) }}</div>
                                    <div style="min-width:0;">
                                        <div style="font-size:12px;color:var(--muted);margin-bottom:2px;">
                                            {{ $message->sender->full_name ?? __('admin.unknown') }} · {{ $message->created_at->format('M d, h:i A') }}
                                        </div>
                                        <div style="background:#f3f4f6;padding:8px 12px;border-radius:0 8px 8px 8px;font-size:13px;white-space:pre-wrap;">
                                            {{ $message->body }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    @endif

    {{-- Report status modal --}}
    <div class="modal-backdrop" id="report-status-modal">
        <div class="modal">
            <div class="modal-title">{{ __('admin.reports.modal_title') }}</div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <label>{{ __('admin.status') }}</label>
                <select name="status">
                    <option value="pending">{{ __('admin.status_values.pending') }}</option>
                    <option value="reviewed">{{ __('admin.status_values.reviewed') }}</option>
                    <option value="resolved">{{ __('admin.status_values.resolved') }}</option>
                </select>
                <label>{{ __('admin.reports.resolution_note') }}</label>
                <textarea name="resolution" placeholder="{{ __('admin.reports.resolution_placeholder') }}"></textarea>
                <label>{{ __('admin.reports.admin_notes') }}</label>
                <textarea name="admin_notes" placeholder="{{ __('admin.reports.admin_notes_placeholder') }}"></textarea>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('report-status-modal')">{{ __('admin.cancel') }}</button>
                    <button type="submit">{{ __('admin.update') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
