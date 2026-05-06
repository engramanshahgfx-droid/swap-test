@extends('layouts.app')

@section('title', __('admin.activation.title'))
@section('page-name', 'activation')

@section('content')
    <h1 class="page-title">{{ __('admin.activation.title') }}</h1>
    <p class="page-sub">{{ __('admin.activation.subtitle') }}</p>

    <div class="tabs" data-tabs>
        <button type="button" class="active" data-tab="activation">{{ __('admin.activation.tab_activation') }}</button>
        <button type="button" data-tab="profile">{{ __('admin.activation.tab_profile') }}</button>
        <button type="button" data-tab="history">{{ __('admin.activation.tab_history') }}</button>
        <button type="button" data-tab="reports">{{ __('admin.activation.tab_reports') }}</button>
        <button type="button" data-tab="chat">{{ __('admin.activation.tab_chat') }}</button>
    </div>

    <div class="tab-pane active" data-pane="activation">
        <div class="actions">
            <div class="search-box">
                <input type="text" class="js-table-search" placeholder="{{ __('admin.activation.search') }}" />
            </div>
            <form method="GET" action="{{ route('activation') }}" style="display:contents">
                @if(request('user_id'))
                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                @endif
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">{{ __('admin.all_statuses') }}</option>
                    <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>{{ __('admin.status_values.active') }}</option>
                    <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>{{ __('admin.status_values.inactive') }}</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>{{ __('admin.status_values.blocked') }}</option>
                </select>
            </form>
        </div>

        <div class="table-wrap">
            <table id="activation-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.activation.id') }}</th>
                        <th>{{ __('admin.activation.full_name') }}</th>
                        <th>{{ __('admin.activation.email') }}</th>
                        <th>{{ __('admin.activation.phone') }}</th>
                        <th>{{ __('admin.activation.airline') }}</th>
                        <th>{{ __('admin.status') }}</th>
                        <th>{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $uc = match($user->status ?? 'inactive') {
                                'active' => 'badge-success',
                                'blocked' => 'badge-danger',
                                default => 'badge-gray',
                            };
                        @endphp
                        <tr>
                            <td>{{ $user->employee_id ?? $user->id }}</td>
                            <td>{{ $user->full_name ?? __('admin.none') }}</td>
                            <td>{{ $user->email ?? __('admin.none') }}</td>
                            <td>{{ $user->phone ?? __('admin.none') }}</td>
                            <td>{{ $user->airline->name ?? __('admin.none') }}</td>
                            <td><span class="badge {{ $uc }}">{{ __('admin.status_values.' . ($user->status ?? 'inactive')) }}</span></td>
                            <td>
                                <div class="tbl-actions">
                                    <a class="tbl-btn tbl-btn-view" href="{{ route('activation', array_filter(['status' => request('status'), 'user_id' => $user->id])) }}">Open</a>
                                    <button type="button" class="tbl-btn tbl-btn-edit" onclick="setUserStatus({{ $user->id }}, '{{ $user->status ?? 'inactive' }}')">Status</button>
                                    <form method="POST" action="{{ route('activation.destroy', $user) }}" onsubmit="return confirm('{{ __('admin.activation.delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        @if(request('status'))
                                            <input type="hidden" name="status" value="{{ request('status') }}" />
                                        @endif
                                        <button type="submit" class="tbl-btn tbl-btn-delete">{{ __('admin.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">{{ __('admin.activation.no_results') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span>{{ __('admin.showing_results', ['from' => $users->firstItem() ?? 0, 'to' => $users->lastItem() ?? 0, 'total' => $users->total()]) }}</span>
            <div class="pagination">{{ $users->links() }}</div>
        </div>
    </div>

    <div class="tab-pane" data-pane="profile">
        @if($selectedUser)
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
                <div class="table-wrap">
                    <table>
                        <tbody>
                            <tr><th style="width:220px">{{ __('admin.activation.id') }}</th><td>{{ $selectedUser->employee_id ?? $selectedUser->id }}</td></tr>
                            <tr><th>{{ __('admin.activation.full_name') }}</th><td>{{ $selectedUser->full_name }}</td></tr>
                            <tr><th>{{ __('admin.activation.email') }}</th><td>{{ $selectedUser->email ?? __('admin.none') }}</td></tr>
                            <tr><th>{{ __('admin.activation.phone') }}</th><td>{{ $selectedUser->phone ?? __('admin.none') }}</td></tr>
                            <tr><th>{{ __('admin.activation.airline') }}</th><td>{{ $selectedUser->airline->name ?? __('admin.none') }}</td></tr>
                            <tr><th>{{ __('admin.activation.position') }}</th><td>{{ $selectedUser->position->name ?? __('admin.none') }}</td></tr>
                            <tr><th>{{ __('admin.activation.plane_type') }}</th><td>{{ $selectedUser->planeType->name ?? __('admin.none') }}</td></tr>
                            <tr><th>{{ __('admin.activation.base_airport') }}</th><td>{{ $selectedUser->country_base ?? __('admin.none') }}</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-wrap" style="padding:16px;display:flex;flex-direction:column;gap:14px;">
                    <div>
                        <div style="font-weight:700;margin-bottom:8px;">{{ __('admin.activation.account_information') }}</div>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            <span class="badge badge-gray">{{ __('admin.status_values.' . ($selectedUser->status ?? 'inactive')) }}</span>
                            @forelse($selectedUser->getRoleNames() as $roleName)
                                <span class="badge badge-info">{{ $roleName }}</span>
                            @empty
                                <span class="badge badge-gray">{{ __('admin.activation.no_roles') }}</span>
                            @endforelse
                        </div>
                    </div>

                    <form method="POST" action="{{ route('activation.status', $selectedUser) }}" style="display:flex;flex-direction:column;gap:12px;">
                        @csrf
                        @method('PUT')
                        <div>
                            <label>{{ __('admin.status') }}</label>
                            <select name="status">
                                <option value="active" {{ ($selectedUser->status ?? 'inactive') === 'active' ? 'selected' : '' }}>{{ __('admin.status_values.active') }}</option>
                                <option value="inactive" {{ ($selectedUser->status ?? 'inactive') === 'inactive' ? 'selected' : '' }}>{{ __('admin.status_values.inactive') }}</option>
                                <option value="blocked" {{ ($selectedUser->status ?? 'inactive') === 'blocked' ? 'selected' : '' }}>{{ __('admin.status_values.blocked') }}</option>
                            </select>
                        </div>
                        <div style="color:var(--muted);font-size:13px;">{{ __('admin.activation.profile_help') }}</div>
                        <div class="modal-footer" style="padding:0;">
                            <button type="submit">{{ __('admin.activation.save_status') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <p style="color:var(--muted)">Select a user to view profile details.</p>
        @endif
    </div>

    <div class="tab-pane" data-pane="history">
        @if($selectedUser)
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Flight</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($selectedUser->trips as $trip)
                            <tr>
                                <td>{{ $trip->flight->flight_number ?? __('admin.none') }}</td>
                                <td>{{ ($trip->flight->departure_airport ?? '—') . ' → ' . ($trip->flight->arrival_airport ?? '—') }}</td>
                                <td>{{ $trip->flight->departure_date ? $trip->flight->departure_date->format('M d, Y') : __('admin.none') }}</td>
                                <td><span class="badge badge-gray">{{ ucfirst($trip->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">No trip history found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-wrap" style="margin-top:12px;">
                <table>
                    <thead>
                        <tr>
                            <th>Swap flight</th>
                            <th>Counterpart</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($swapHistory as $swap)
                            @php
                                $otherParty = $swap->requester_id === $selectedUser->id ? $swap->responder : $swap->requester;
                            @endphp
                            <tr>
                                <td>{{ $swap->publishedTrip->flight->flight_number ?? __('admin.none') }}</td>
                                <td>{{ $otherParty->full_name ?? __('admin.none') }}</td>
                                <td><span class="badge badge-gray">{{ str_replace('_', ' ', $swap->status) }}</span></td>
                                <td>{{ $swap->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">No swap history found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p style="color:var(--muted)">Select a user to view activity history.</p>
        @endif
    </div>

    <div class="tab-pane" data-pane="reports">
        @if($selectedUser)
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userReports as $report)
                            <tr>
                                <td>Submitted</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $report->reason)) }} · {{ Str::limit($report->details, 60) }}</td>
                                <td><span class="badge badge-gray">{{ __('admin.status_values.' . $report->status) }}</span></td>
                                <td>{{ $report->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">No submitted reports.</td></tr>
                        @endforelse
                        @forelse($reportedAgainst as $report)
                            <tr>
                                <td>Against user</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $report->reason)) }} · {{ Str::limit($report->details, 60) }}</td>
                                <td><span class="badge badge-gray">{{ __('admin.status_values.' . $report->status) }}</span></td>
                                <td>{{ $report->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p style="color:var(--muted)">Select a user to view reports.</p>
        @endif
    </div>

    <div class="tab-pane" data-pane="chat">
        @if($selectedUser)
            <div class="support-main" style="min-height:320px;">
                <div class="support-content">
                    @forelse($chatMessages as $msg)
                        <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;">
                            <div class="user-avatar">{{ strtoupper(substr($msg->sender->full_name ?? 'U', 0, 1)) }}</div>
                            <div>
                                <div style="font-size:12px;color:var(--muted);margin-bottom:2px;">{{ $msg->sender->full_name ?? __('admin.none') }} · {{ $msg->created_at->diffForHumans() }}</div>
                                <div style="background:#f3f4f6;padding:8px 12px;border-radius:0 8px 8px 8px;font-size:13px;">{{ $msg->body }}</div>
                            </div>
                        </div>
                    @empty
                        <p style="color:var(--muted)">No chat messages found.</p>
                    @endforelse
                </div>
            </div>
        @else
            <p style="color:var(--muted)">Select a user to view chat history.</p>
        @endif
    </div>

    {{-- User status modal --}}
    <div class="modal-backdrop" id="user-status-modal">
        <div class="modal">
            <div class="modal-title">{{ __('admin.activation.modal_title') }}</div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <label>{{ __('admin.status') }}</label>
                <select name="status">
                    <option value="active">{{ __('admin.status_values.active') }}</option>
                    <option value="inactive">{{ __('admin.status_values.inactive') }}</option>
                    <option value="blocked">{{ __('admin.status_values.blocked') }}</option>
                </select>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('user-status-modal')">{{ __('admin.cancel') }}</button>
                    <button type="submit">{{ __('admin.update') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
