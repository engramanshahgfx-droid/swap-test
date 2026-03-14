@extends('layouts.app')

@section('title', __('admin.airlines.title'))
@section('page-name', 'airlines')

@section('content')
    <h1 class="page-title">{{ __('admin.airlines.title') }}</h1>
    <p class="page-sub">{{ __('admin.airlines.subtitle') }}</p>

    <div class="form-block" id="create-airline-form" style="display:none; margin-bottom: 16px; max-width: 560px;">
        <form id="airline-create-form-el" method="POST" action="{{ route('airlines.store') }}">
            @csrf
            <div class="form-grid two">
                <input type="text" name="name" id="new-airline-name" placeholder="{{ __('admin.airlines.airline_name') }}" required />
                <input type="text" name="code" id="new-airline-code" placeholder="{{ __('admin.airlines.airline_code') }}" required />
            </div>
            <div style="margin-top:10px">
                <input type="text" name="country" id="new-airline-country" placeholder="{{ __('admin.airlines.country') }}" />
            </div>
            <div class="action-end" style="margin-top: 10px;">
                <button class="js-cancel-airline-form" type="button">{{ __('admin.cancel') }}</button>
                <button type="submit">{{ __('admin.airlines.add_airline') }}</button>
            </div>
        </form>
    </div>

    <div class="actions">
        <div class="search-box">
            <input type="text" class="js-table-search" placeholder="{{ __('admin.airlines.search') }}" />
        </div>
        <button class="add-btn js-open-airline-form" type="button">{{ __('admin.airlines.new') }}</button>
    </div>

    <div class="table-wrap">
        <table id="airline-table">
            <thead>
                <tr>
                    <th>{{ __('admin.airlines.name') }}</th>
                    <th>{{ __('admin.airlines.code') }}</th>
                    <th>{{ __('admin.airlines.country') }}</th>
                    <th>{{ __('admin.airlines.created') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($airlines as $airline)
                    <tr>
                        <td><strong>{{ $airline->name }}</strong></td>
                        <td><span class="badge badge-gray">{{ $airline->code }}</span></td>
                        <td>{{ $airline->country ?? __('admin.none') }}</td>
                        <td>{{ $airline->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="tbl-actions">
                                <button class="tbl-btn tbl-btn-edit"
                                    onclick="editAirline({{ $airline->id }}, '{{ addslashes($airline->name) }}', '{{ $airline->code }}', '{{ addslashes($airline->country ?? '') }}')">
                                    {{ __('admin.edit') }}
                                </button>
                                <form method="POST" action="{{ route('airlines.destroy', $airline) }}" onsubmit="return confirm('{{ __('admin.airlines.delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tbl-btn tbl-btn-delete">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">{{ __('admin.airlines.no_results') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>{{ __('admin.showing_results', ['from' => $airlines->firstItem() ?? 0, 'to' => $airlines->lastItem() ?? 0, 'total' => $airlines->total()]) }}</span>
        <div class="pagination">{{ $airlines->links() }}</div>
    </div>

    {{-- Edit modal --}}
    <div class="modal-backdrop" id="edit-airline-modal">
        <div class="modal">
            <div class="modal-title">{{ __('admin.airlines.edit_title') }}</div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <label>{{ __('admin.airlines.name') }}</label>
                <input type="text" name="name" required />
                <label>{{ __('admin.airlines.code') }}</label>
                <input type="text" name="code" required />
                <label>{{ __('admin.airlines.country') }}</label>
                <input type="text" name="country" />
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('edit-airline-modal')">{{ __('admin.cancel') }}</button>
                    <button type="submit">{{ __('admin.airlines.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
