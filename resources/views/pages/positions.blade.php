@extends('layouts.app')

@section('title', __('admin.positions.title'))
@section('page-name', 'positions')

@section('content')
    <h1 class="page-title">{{ __('admin.positions.title') }}</h1>
    <p class="page-sub">{{ __('admin.positions.subtitle') }}</p>

    <div class="form-block" id="position-create-form" style="display:none; margin-bottom: 14px; max-width: 760px;">
        <form id="position-create-form-el" method="POST" action="{{ route('positions.store') }}">
            @csrf
            <div class="form-grid two">
                <input type="text" name="name" id="new-position-name" placeholder="{{ __('admin.positions.position_name') }}" required />
                <input type="text" name="slug" id="new-position-slug" placeholder="{{ __('admin.positions.position_slug') }}" required />
                <input type="number" name="level" min="1" placeholder="{{ __('admin.positions.level') }}" required />
                <input type="text" value="0 {{ __('admin.positions.users') }}" disabled />
            </div>
            <div style="margin-top:10px;">
                <textarea name="description" rows="3" placeholder="Description"></textarea>
            </div>
            <div class="action-end" style="margin-top: 10px;">
                <button class="js-cancel-position-form" type="button">{{ __('admin.cancel') }}</button>
                <button type="submit">{{ __('admin.create') }}</button>
            </div>
        </form>
    </div>

    <div class="actions">
        <div class="search-box">
            <input type="text" class="js-table-search" placeholder="{{ __('admin.positions.search') }}" />
        </div>
        <button class="add-btn js-open-position-form" type="button">{{ __('admin.positions.new') }}</button>
    </div>

    <div class="table-wrap">
        <table id="position-table">
            <thead>
                <tr>
                    <th>{{ __('admin.positions.name') }}</th>
                    <th>{{ __('admin.positions.slug') }}</th>
                    <th>{{ __('admin.positions.level') }}</th>
                    <th>{{ __('admin.positions.users') }}</th>
                    <th>{{ __('admin.positions.created') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $position)
                    <tr>
                        <td><strong>{{ $position->name }}</strong></td>
                        <td><span class="badge badge-gray">{{ $position->slug }}</span></td>
                        <td>{{ $position->level }}</td>
                        <td>{{ $position->users_count }}</td>
                        <td>{{ $position->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="tbl-actions">
                                <button class="tbl-btn tbl-btn-edit"
                                    onclick="editPosition({{ $position->id }}, '{{ addslashes($position->name) }}', '{{ $position->slug }}', '{{ $position->level }}', '{{ addslashes($position->description ?? '') }}')">
                                    {{ __('admin.edit') }}
                                </button>
                                <form method="POST" action="{{ route('positions.destroy', $position) }}" onsubmit="return confirm('{{ __('admin.positions.delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tbl-btn tbl-btn-delete">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">{{ __('admin.positions.no_results') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>{{ __('admin.showing_results', ['from' => $positions->firstItem() ?? 0, 'to' => $positions->lastItem() ?? 0, 'total' => $positions->total()]) }}</span>
        <div class="pagination">{{ $positions->links() }}</div>
    </div>

    {{-- Edit modal --}}
    <div class="modal-backdrop" id="edit-position-modal">
        <div class="modal">
            <div class="modal-title">{{ __('admin.positions.edit_title') }}</div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <label>{{ __('admin.positions.name') }}</label>
                <input type="text" name="name" required />
                <label>{{ __('admin.positions.slug') }}</label>
                <input type="text" name="slug" required />
                <label>{{ __('admin.positions.level') }}</label>
                <input type="number" name="level" min="1" required />
                <label>Description</label>
                <textarea name="description"></textarea>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('edit-position-modal')">{{ __('admin.cancel') }}</button>
                    <button type="submit">{{ __('admin.positions.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
