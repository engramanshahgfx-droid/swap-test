@extends('frontend.layouts.app')

@section('title', 'Vacation')

@section('content')
@php
    $user = Auth::user();
@endphp

<div class="row">
    <!-- Header -->
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white;">
            <div class="card-body p-4">
                <h3 class="mb-1"><i class="bi bi-calendar-heart"></i> Vacation</h3>
                <p class="mb-0">Publish your vacation availability and browse others</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="col-md-12 mb-3">
        <ul class="nav nav-pills" id="vacationTabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" onclick="showTab('my-vacation')">My Vacation</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showTab('browse')">Browse</a>
            </li>
        </ul>
    </div>

    <!-- My Vacation Tab -->
    <div id="tab-my-vacation" class="col-md-12">
        <div class="card border-0 shadow">
            <div class="card-header" style="background:#2c3e50; color:white;">
                <h5 class="mb-0"><i class="bi bi-send"></i> Publish Vacation</h5>
            </div>
            <div class="card-body">
                <form id="publishVacationForm">
                    @csrf
                    <input type="hidden" id="publisherId" value="{{ $user->id }}">

                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">OFFER (Departure Month)</label>
                            <select class="form-select" id="departureMonth">
                                <option value="JAN">JAN</option>
                                <option value="FEB">FEB</option>
                                <option value="MAR">MAR</option>
                                <option value="APR" selected>APR</option>
                                <option value="MAY">MAY</option>
                                <option value="JUN">JUN</option>
                                <option value="JUL">JUL</option>
                                <option value="AUG">AUG</option>
                                <option value="SEP">SEP</option>
                                <option value="OCT">OCT</option>
                                <option value="NOV">NOV</option>
                                <option value="DEC">DEC</option>
                            </select>
                        </div>

                        <div class="col-md-1 mb-3 text-center">
                            <span class="fw-bold" style="color: #f39c12; font-size: 1.2rem;">FOR</span>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">ASK (Arrival Month)</label>
                            <select class="form-select" id="arrivalMonth">
                                <option value="JAN">JAN</option>
                                <option value="FEB">FEB</option>
                                <option value="MAR">MAR</option>
                                <option value="APR">APR</option>
                                <option value="MAY" selected>MAY</option>
                                <option value="JUN">JUN</option>
                                <option value="JUL">JUL</option>
                                <option value="AUG">AUG</option>
                                <option value="SEP">SEP</option>
                                <option value="OCT">OCT</option>
                                <option value="NOV">NOV</option>
                                <option value="DEC">DEC</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Position (optional)</label>
                            <select class="form-select" id="vacationPosition">
                                <option value="">Any</option>
                                <option value="Captain">Captain</option>
                                <option value="First Officer">First Officer</option>
                                <option value="Purser">Purser</option>
                                <option value="Flight Attendant">Flight Attendant</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">&nbsp;</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" class="form-control" id="vacationNotes" placeholder="Looking for a vacation swap...">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary px-4" onclick="publishVacation()">
                            <i class="bi bi-send"></i> Publish
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('publishVacationForm').reset()">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    </div>
                </form>

                <div id="publishResult" class="mt-3"></div>
            </div>
        </div>

        <!-- My Published Vacations -->
        <div class="card border-0 shadow mt-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:#2c3e50; color:white;">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> My Published Vacations</h5>
                <button class="btn btn-sm btn-light" onclick="loadMyVacations()">Refresh</button>
            </div>
            <div class="card-body">
                <div id="myVacationsList">
                    <p class="text-muted text-center">Click Refresh to load your vacations</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Browse Tab -->
    <div id="tab-browse" class="col-md-12" style="display:none;">
        <div class="card border-0 shadow">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:#2c3e50; color:white;">
                <h5 class="mb-0"><i class="bi bi-search"></i> Browse Available Vacations</h5>
                <button class="btn btn-sm btn-light" onclick="loadBrowseVacations()">Load</button>
            </div>
            <div class="card-body">
                <div id="browseVacationsList">
                    <p class="text-muted text-center">Click Load to browse vacations from other crew members</p>
                </div>
            </div>
        </div>
    </div>
</div>

<meta name="api-token" content="{{ session('api_token') ?? '' }}">

@endsection

@section('extra_css')
<style>
    .vacation-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 12px;
        background: #f8f9fa;
    }
    .month-badge {
        background: #3498db;
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 1rem;
    }
    .month-badge.orange {
        background: #f39c12;
    }
    .nav-pills .nav-link.active {
        background: #3498db;
    }
</style>
@endsection

@section('extra_js')
<script>
    function getAuthToken() {
        const metaToken = document.querySelector('meta[name="api-token"]')?.content?.trim();
        if (metaToken) return metaToken;

        const localCandidates = ['api_token', 'auth_token', 'token'];
        for (const key of localCandidates) {
            const token = localStorage.getItem(key);
            if (token && token.trim()) return token.trim();
        }

        for (const key of localCandidates) {
            const token = sessionStorage.getItem(key);
            if (token && token.trim()) return token.trim();
        }

        return '';
    }

    function getAuthHeaders() {
        const token = getAuthToken();
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }

        return headers;
    }

    function renderAuthRequired(elId) {
        document.getElementById(elId).innerHTML = '<div class="alert alert-warning">Your session token is missing or expired. Please log in again.</div>';
    }

    function showTab(tab) {
        document.getElementById('tab-my-vacation').style.display = tab === 'my-vacation' ? 'block' : 'none';
        document.getElementById('tab-browse').style.display = tab === 'browse' ? 'block' : 'none';
        document.querySelectorAll('#vacationTabs .nav-link').forEach((el, i) => {
            el.classList.toggle('active', (i === 0 && tab === 'my-vacation') || (i === 1 && tab === 'browse'));
        });
        if (tab === 'browse') loadBrowseVacations();
        if (tab === 'my-vacation') loadMyVacations();
    }

    function publishVacation() {
        if (!getAuthToken()) {
            renderAuthRequired('publishResult');
            return;
        }

        const publisherId = document.getElementById('publisherId').value;
        const departureMonth = document.getElementById('departureMonth').value;
        const arrivalMonth = document.getElementById('arrivalMonth').value;
        const position = document.getElementById('vacationPosition').value;
        const notes = document.getElementById('vacationNotes').value.trim();

        const body = { publisher_id: parseInt(publisherId), departure_month: departureMonth, arrival_month: arrivalMonth };
        if (position) body.position = position;
        if (notes) body.notes = notes;

        fetch('/api/publish-vacation', {
            method: 'POST',
            headers: getAuthHeaders(),
            body: JSON.stringify(body),
        })
        .then(async r => {
            let data = {};

            try {
                data = await r.json();
            } catch (_) {
                data = { success: false, message: 'Unexpected response from server' };
            }

            if (r.status === 401 || (data.message && data.message.includes('Unauthenticated'))) {
                renderAuthRequired('publishResult');
                return;
            }

            const el = document.getElementById('publishResult');
            if (data.success) {
                el.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Vacation published! ID: ' + data.data.id + ' | ' + data.data.departure_month + ' → ' + data.data.arrival_month + '</div>';
                loadMyVacations();
            } else {
                el.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Failed to publish') + '</div>';
            }
        })
        .catch(e => {
            document.getElementById('publishResult').innerHTML = '<div class="alert alert-danger">Error: ' + e.message + '</div>';
        });
    }

    function loadMyVacations() {
        if (!getAuthToken()) {
            renderAuthRequired('myVacationsList');
            return;
        }

        fetch('/api/my-vacations', {
            headers: getAuthHeaders()
        })
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById('myVacationsList');
            if (data.message && data.message.includes('Unauthenticated')) {
                renderAuthRequired('myVacationsList');
                return;
            }

            if (!data.success || !data.data.items.length) {
                el.innerHTML = '<p class="text-muted text-center">No vacations published yet</p>';
                return;
            }
            el.innerHTML = data.data.items.map(v => `
                <div class="vacation-card">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="month-badge orange">${v.departure_month}</span>
                        <span class="fw-bold text-muted">FOR</span>
                        <span class="month-badge">${v.arrival_month}</span>
                        <span class="badge bg-secondary">${v.position || 'Any'}</span>
                        <span class="badge ${v.status === 'available' ? 'bg-success' : 'bg-secondary'}">${v.status}</span>
                        <span class="text-muted small ms-auto">Expires: ${v.expires_at ? v.expires_at.substring(0, 10) : 'N/A'}</span>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            document.getElementById('myVacationsList').innerHTML = '<div class="alert alert-warning">Could not load vacations. Make sure you are logged in with a token.</div>';
        });
    }

    function loadBrowseVacations() {
        if (!getAuthToken()) {
            renderAuthRequired('browseVacationsList');
            return;
        }

        fetch('/api/browse-vacations', {
            headers: getAuthHeaders()
        })
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById('browseVacationsList');
            if (data.message && data.message.includes('Unauthenticated')) {
                renderAuthRequired('browseVacationsList');
                return;
            }

            if (!data.success || !data.data.items.length) {
                el.innerHTML = '<p class="text-muted text-center">No vacations available from other crew members</p>';
                return;
            }
            el.innerHTML = data.data.items.map(v => `
                <div class="vacation-card">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div>
                            <strong>${v.publisher.name}</strong>
                            <span class="text-muted small">(${v.publisher.employee_id || 'N/A'})</span>
                        </div>
                        <span class="month-badge orange">${v.departure_month}</span>
                        <span class="fw-bold text-muted">FOR</span>
                        <span class="month-badge">${v.arrival_month}</span>
                        <span class="badge bg-secondary">${v.position || 'Any'}</span>
                        ${v.notes ? `<span class="text-muted small">${v.notes}</span>` : ''}
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            document.getElementById('browseVacationsList').innerHTML = '<div class="alert alert-warning">Could not load vacations. Make sure you are logged in with a token.</div>';
        });
    }

    // Auto-load my vacations on page load
    loadMyVacations();
</script>
@endsection
