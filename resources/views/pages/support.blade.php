@extends('layouts.app')

@section('title', __('admin.support.title'))
@section('page-name', 'support')

@section('content')
    <h1 class="page-title">{{ __('admin.support.title') }}</h1>
    <p class="page-sub">{{ __('admin.support.subtitle') }}</p>

    <div class="support-layout">
        <section class="support-chat" id="support-chat-panel">
            <div class="chat-header">
                <h3>{{ __('admin.support.conversations', ['count' => $conversations->total()]) }}</h3>
            </div>
            <div style="padding:0 12px 10px;">
                <input type="text" class="js-chat-search" placeholder="{{ __('admin.support.search_users') }}" style="width:100%;" />
            </div>
            <div class="chat-list">
                @forelse($conversations as $conv)
                    @php
                        $label = ($conv->userOne->full_name ?? __('admin.unknown')) . ' ↔ ' . ($conv->userTwo->full_name ?? __('admin.unknown'));
                        $initials = strtoupper(substr($conv->userOne->full_name ?? 'U', 0, 1));
                        $isSelected = $selectedConversation && $selectedConversation->id === $conv->id;
                    @endphp
                    <a class="chat-item {{ $isSelected ? 'active' : '' }}"
                       href="{{ route('support', ['conversation_id' => $conv->id]) }}">
                        <div class="avatar-sm">{{ $initials }}</div>
                        <div class="chat-item-info">
                            <div class="chat-item-name">{{ $label }}</div>
                            <div class="chat-item-msg">{{ __('admin.support.messages_count', ['count' => $conv->messages_count]) }}</div>
                        </div>
                    </a>
                @empty
                    <div style="padding:16px;color:var(--muted);font-size:13px">{{ __('admin.support.no_conversations') }}</div>
                @endforelse
            </div>
        </section>

        <section class="support-main">
            @if($selectedConversation && $selectedUser && $secondaryUser)
                <div class="tabs support-tabs-inner" data-tabs>
                    <button type="button" class="active" data-tab="profile">{{ __('admin.support.profile') }}</button>
                    <button type="button" data-tab="trips">{{ __('admin.support.trips') }}</button>
                    <button type="button" data-tab="chat">{{ __('admin.support.chat') }}</button>
                </div>

                <div class="support-content">
                    <div class="tab-pane active" data-pane="profile">
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
                            @foreach([$selectedUser, $secondaryUser] as $participant)
                                @php $uc = match($participant->status ?? 'inactive') { 'active' => 'badge-success', 'blocked' => 'badge-danger', default => 'badge-gray' }; @endphp
                                <div class="table-wrap">
                                    <table>
                                        <tbody>
                                            <tr><th style="width:140px">{{ __('labels.full_name') }}</th><td>{{ $participant->full_name }}</td></tr>
                                            <tr><th>{{ __('labels.employee_id') }}</th><td>{{ $participant->employee_id ?? __('admin.none') }}</td></tr>
                                            <tr><th>{{ __('labels.email') }}</th><td>{{ $participant->email ?? __('admin.none') }}</td></tr>
                                            <tr><th>{{ __('labels.phone') }}</th><td>{{ $participant->phone ?? __('admin.none') }}</td></tr>
                                            <tr><th>{{ __('labels.airline') }}</th><td>{{ $participant->airline->name ?? __('admin.none') }}</td></tr>
                                            <tr><th>{{ __('labels.position') }}</th><td>{{ $participant->position->name ?? __('admin.none') }}</td></tr>
                                            <tr><th>{{ __('admin.status') }}</th><td><span class="badge {{ $uc }}">{{ __('admin.status_values.' . ($participant->status ?? 'inactive')) }}</span></td></tr>
                                            <tr><th>{{ __('admin.support.joined') }}</th><td>{{ $participant->created_at?->format('M d, Y') ?? __('admin.none') }}</td></tr>
                                            <tr><th>{{ __('admin.support.profile_link') }}</th><td><a href="{{ route('activation', ['user_id' => $participant->id]) }}">{{ __('admin.support.open_profile') }}</a></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="tab-pane" data-pane="trips">
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;">
                            @foreach([$selectedUser, $secondaryUser] as $participant)
                                <div class="table-wrap">
                                    <div style="padding:14px 16px 0;font-weight:700;">{{ $participant->full_name }}</div>
                                    @if($participant->trips->isEmpty())
                                        <p style="color:var(--muted);padding:16px;">{{ __('admin.support.no_trips') }}</p>
                                    @else
                                        <table>
                                            <thead>
                                                <tr><th>{{ __('admin.support.flight') }}</th><th>{{ __('admin.support.departure') }}</th><th>{{ __('admin.support.arrival') }}</th><th>{{ __('admin.support.date') }}</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($participant->trips->take(10) as $trip)
                                                    <tr>
                                                        <td>{{ $trip->flight->flight_number ?? __('admin.none') }}</td>
                                                        <td>{{ $trip->flight->departure_airport ?? __('admin.none') }}</td>
                                                        <td>{{ $trip->flight->arrival_airport ?? __('admin.none') }}</td>
                                                        <td>{{ $trip->flight->departure_date ? $trip->flight->departure_date->format('M d, Y') : __('admin.none') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="tab-pane" data-pane="chat">
                        @if($messages->isEmpty())
                            <p style="color:var(--muted)">{{ __('admin.support.no_messages') }}</p>
                        @else
                            <div style="display:flex;flex-direction:column;gap:10px;max-height:460px;overflow:auto;padding-right:4px;">
                                @foreach($messages as $msg)
                                    @php
                                        $isAdminMessage = auth()->id() === $msg->sender_id;
                                    @endphp
                                    <div style="display:flex;gap:8px;align-items:flex-start">
                                        <div class="user-avatar" style="font-size:11px;width:28px;height:28px">
                                            {{ strtoupper(substr($msg->sender->full_name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-size:12px;color:var(--muted);margin-bottom:2px">
                                                {{ $msg->sender->full_name ?? __('admin.unknown') }} &middot; {{ $msg->created_at->diffForHumans() }}
                                            </div>
                                            <div style="background:{{ $isAdminMessage ? '#e0f2fe' : ($msg->sender_id === $selectedUser->id ? '#eff6ff' : '#f3f4f6') }};padding:8px 12px;border-radius:0 8px 8px 8px;font-size:13px;white-space:pre-wrap;">
                                                {{ $msg->body }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('support.reply', $selectedConversation) }}" style="margin-top:14px;display:flex;gap:10px;align-items:flex-end;">
                            @csrf
                            <div style="flex:1;">
                                <label for="support-reply-body" style="display:block;margin-bottom:6px;color:var(--muted);font-size:12px;">{{ __('admin.support.reply_label') }}</label>
                                <textarea id="support-reply-body" name="body" rows="3" placeholder="{{ __('admin.support.reply_placeholder') }}" style="resize:vertical;">{{ old('body') }}</textarea>
                                @error('body')
                                    <div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn-primary">{{ __('admin.support.send_reply') }}</button>
                        </form>
                    </div>
                </div>
            @else
                <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted);font-size:14px">
                    {{ __('admin.support.select_conversation') }}
                </div>
            @endif
        </section>
    </div>
@endsection
