@extends('layouts.app')

@section('title', __('admin.blogs.title'))
@section('page-name', 'blogs')

@section('content')
    <h1 class="page-title">{{ __('admin.blogs.title') }}</h1>
    <p class="page-sub">{{ __('admin.blogs.subtitle') }}</p>

    <div class="form-block" id="create-blog-form" style="display:none; margin-bottom: 16px; max-width: 760px;">
        <form method="POST" action="{{ route('blogs.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid two">
                <input type="text" name="title" placeholder="{{ __('admin.blogs.title_label') }}" required />
                <input type="text" name="heading" placeholder="{{ __('admin.blogs.heading_label') }}" />
            </div>
            <div style="margin-top:10px;">
                <textarea name="description" rows="8" placeholder="{{ __('admin.blogs.description_label') }}" style="width:100%;"></textarea>
            </div>
            <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <input type="file" name="image" accept="image/*" />
                <label style="display:inline-flex; align-items:center; gap:6px;">
                    <input type="checkbox" name="is_published" value="1" />
                    {{ __('admin.blogs.published') }}
                </label>
            </div>
            <div class="action-end" style="margin-top: 10px;">
                <button class="js-cancel-blog-form" type="button">{{ __('admin.cancel') }}</button>
                <button type="submit">{{ __('admin.blogs.add_blog') }}</button>
            </div>
        </form>
    </div>

    <div class="actions">
        <div class="search-box">
            <input type="text" class="js-table-search" placeholder="{{ __('admin.blogs.search') }}" />
        </div>
        <button class="add-btn js-open-blog-form" type="button">{{ __('admin.blogs.new') }}</button>
    </div>

    <div class="table-wrap">
        <table id="blog-table">
            <thead>
                <tr>
                    <th>{{ __('admin.blogs.title_label') }}</th>
                    <th>{{ __('admin.blogs.heading_label') }}</th>
                    <th>{{ __('admin.blogs.status') }}</th>
                    <th>{{ __('admin.blogs.created') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blogs as $blog)
                    <tr>
                        <td><strong>{{ $blog->title }}</strong></td>
                        <td>{{ Str::limit($blog->heading ?? '', 60) }}</td>
                        <td><span class="badge {{ $blog->is_published ? 'badge-success' : 'badge-gray' }}">{{ $blog->is_published ? __('admin.blogs.published') : __('admin.blogs.draft') }}</span></td>
                        <td>{{ $blog->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="tbl-actions">
                                <button class="tbl-btn tbl-btn-edit js-edit-blog" type="button" data-blog="{{ json_encode([
                                    'id' => $blog->id,
                                    'title' => $blog->title,
                                    'heading' => $blog->heading ?? '',
                                    'description' => $blog->description ?? '',
                                    'is_published' => $blog->is_published,
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) }}">{{ __('admin.edit') }}</button>
                                <form method="POST" action="{{ route('blogs.destroy', $blog) }}" onsubmit="return confirm('{{ __('admin.blogs.delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tbl-btn tbl-btn-delete">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">{{ __('admin.blogs.no_results') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>{{ __('admin.showing_results', ['from' => $blogs->firstItem() ?? 0, 'to' => $blogs->lastItem() ?? 0, 'total' => $blogs->total()]) }}</span>
        <div class="pagination">{{ $blogs->links() }}</div>
    </div>

    <div class="modal-backdrop" id="edit-blog-modal">
        <div class="modal">
            <div class="modal-title">{{ __('admin.blogs.edit_title') }}</div>
            <form method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <label>{{ __('admin.blogs.title_label') }}</label>
                <input type="text" name="title" required />
                <label>{{ __('admin.blogs.heading_label') }}</label>
                <input type="text" name="heading" />
                <label>{{ __('admin.blogs.description_label') }}</label>
                <textarea name="description" rows="8"></textarea>
                <label>{{ __('admin.blogs.image_label') }}</label>
                <input type="file" name="image" accept="image/*" />
                <label style="display:inline-flex; align-items:center; gap:6px;">
                    <input type="checkbox" name="is_published" value="1" />
                    {{ __('admin.blogs.published') }}
                </label>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('edit-blog-modal')">{{ __('admin.cancel') }}</button>
                    <button type="submit">{{ __('admin.blogs.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
