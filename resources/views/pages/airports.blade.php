@extends('layouts.app')

@section('title', __('admin.airports.title'))
@section('page-name', 'airports')

@section('content')
    <h1 class="page-title">Airports</h1>
    <p class="page-sub">Manage all airports in the system</p>

    <div class="form-block" id="create-airport-form" style="display:none; margin-bottom: 16px; max-width: 560px;">
        <form id="airport-create-form-el" method="POST" action="{{ route('airports.store') }}">
            @csrf
            <div class="form-grid two">
                <input type="text" name="name" id="new-airport-name" placeholder="Airport Name" required />
                <input type="text" name="code" id="new-airport-code" placeholder="IATA Code (e.g., JFK)" required />
            </div>
            <div class="form-grid two">
                <input type="text" name="city" id="new-airport-city" placeholder="City" />
                <input type="text" name="country" id="new-airport-country" placeholder="Country" />
            </div>
            <div class="action-end" style="margin-top: 10px;">
                <button class="js-cancel-airport-form" type="button">Cancel</button>
                <button type="submit">Add Airport</button>
            </div>
        </form>
    </div>

    <div class="actions">
        <div class="search-box">
            <input type="text" class="js-table-search" placeholder="Search airports..." />
        </div>
        <button class="add-btn js-open-airport-form" type="button">+ New Airport</button>
    </div>

    <div class="table-wrap">
        <table id="airport-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>IATA Code</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($airports as $airport)
                    <tr>
                        <td><strong>{{ $airport->name }}</strong></td>
                        <td><span class="badge badge-gray">{{ $airport->code }}</span></td>
                        <td>{{ $airport->city ?? '—' }}</td>
                        <td>{{ $airport->country ?? '—' }}</td>
                        <td>{{ $airport->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="tbl-actions">
                                <button class="tbl-btn tbl-btn-edit"
                                    onclick="editAirport({{ $airport->id }}, '{{ addslashes($airport->name) }}', '{{ $airport->code }}', '{{ addslashes($airport->city ?? '') }}', '{{ addslashes($airport->country ?? '') }}')">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('airports.destroy', $airport) }}" onsubmit="return confirm('Delete this airport?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tbl-btn tbl-btn-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">No airports found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>Showing {{ $airports->firstItem() ?? 0 }} to {{ $airports->lastItem() ?? 0 }} of {{ $airports->total() }} airports</span>
        <div class="pagination">{{ $airports->links() }}</div>
    </div>

    {{-- Edit modal --}}
    <div class="modal-backdrop" id="edit-airport-modal">
        <div class="modal">
            <div class="modal-title">Edit Airport</div>
            <form method="POST" action="">
                @csrf
                @method('PUT')
                <label>Name</label>
                <input type="text" name="name" required />
                <label>IATA Code</label>
                <input type="text" name="code" required />
                <label>City</label>
                <input type="text" name="city" />
                <label>Country</label>
                <input type="text" name="country" />
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('edit-airport-modal')">Cancel</button>
                    <button type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle create form
        document.querySelector('.js-open-airport-form')?.addEventListener('click', function() {
            document.getElementById('create-airport-form').style.display = 'block';
        });

        document.querySelector('.js-cancel-airport-form')?.addEventListener('click', function() {
            document.getElementById('create-airport-form').style.display = 'none';
        });

        // Edit airport
        function editAirport(id, name, code, city, country) {
            const form = document.querySelector('#edit-airport-modal form');
            form.action = `/airports/${id}`;
            form.querySelector('input[name="name"]').value = name;
            form.querySelector('input[name="code"]').value = code;
            form.querySelector('input[name="city"]').value = city;
            form.querySelector('input[name="country"]').value = country;
            document.getElementById('edit-airport-modal').style.display = 'block';
        }

        // Close modal
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Search functionality
        document.querySelector('.js-table-search')?.addEventListener('input', function(e) {
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

        // Modal backdrop close
        document.getElementById('edit-airport-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
        document.getElementById('create-airport-form')?.parentElement?.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    </script>
@endsection
