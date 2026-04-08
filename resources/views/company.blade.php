<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Management</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('internicon.ico') }}">
</head>

<body>

    <div class="dashboard-wrapper">

        <div class="navbar">
            <ol>
                <li><a href="/dashboard">Dashboard</a></li>
            </ol>
        </div>

        <div class="container">

            <div class="card">
                <h2>🏢 Add New Company</h2>

                <form method="POST" action="{{ route('company.store') }}" class="company-form" id="companyForm">
                    @csrf

                    {{-- Company Name --}}
                    <div class="form-group">
                        <label for="name">Company Name *</label>
                        <input type="text" name="name" id="name" placeholder="Enter company name"
                            value="{{ old('name') }}" required maxlength="255">
                        <small class="form-hint">This will be used to generate your company URL</small>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea name="description" id="description" placeholder="Enter company description" rows="3"
                            required maxlength="1000">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Username --}}
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <div class="username-input-group">
                            <input type="text" name="username" id="username" placeholder="Choose a Username"
                                value="{{ old('username') }}" required minlength="3" maxlength="255"
                                pattern="[a-zA-Z0-9]+" autocomplete="off">
                            <small class="form-hint">Letters and numbers only. This will be used for company login.
                                Always remember this.</small>
                            <button type="button" class="btn-check-username" id="checkUsernameBtn">
                                Check
                            </button>
                        </div>
                        <span class="username-status" id="usernameStatus"></span>

                        @error('username')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" name="password" id="password" placeholder="Create a secure password"
                            required minlength="8">
                        <small class="form-hint">Must include: uppercase, number, and special character
                            (!@#$%^&*)</small>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password *</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            placeholder="Re-enter your password" required>
                        @error('password_confirmation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password Strength Meter --}}
                    <div class="password-strength" id="passwordStrengthContainer" style="display: none;">
                        <div class="strength-meter-container">
                            <div class="strength-meter-track">
                                <div class="strength-meter-fill" id="passwordStrengthMeter"></div>
                            </div>
                            <span class="strength-meter-label" id="passwordStrengthLabel">Strength</span>
                        </div>
                        <div class="password-requirements">
                            <span class="password-requirement" data-rule="length">8+ characters</span>
                            <span class="password-requirement" data-rule="uppercase">1 uppercase letter</span>
                            <span class="password-requirement" data-rule="number">1 number</span>
                            <span class="password-requirement" data-rule="special">1 special symbol</span>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn-primary1" id="submitBtn" disabled>
                        Add Company
                    </button>
                </form>
            </div>

            <div class="card">
                <h3>Company List</h3>

                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if($companies->isEmpty())
                    <p class="empty">No companies added yet.</p>
                @else
                    <!-- Scrollable Table Container -->
                    <div class="table-scroll-container">
                        <table class="company-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)
                                    <tr>
                                        <td>{{ $company->id }}</td>
                                        <td>{{ $company->name }}</td>
                                        <td>{{ Str::limit($company->description, 50) }}</td>
                                        <td class="actions">
                                            {{-- Edit Button (Triggers Modal) --}}
                                            {{-- <button class="btn-edit"
                                                onclick="openEditModal('{{ $company->slug }}', '{{ addslashes($company->name) }}', '{{ addslashes($company->description) }}')">
                                                Edit
                                            </button> --}}

                                            {{-- View Link --}}
                                            {{-- View Button (Triggers Modal) --}}
                                            <button type="button" class="btn-view"
                                                onclick="openCompanyLoginModal('{{ $company->slug }}', '{{ addslashes($company->name) }}')">
                                                View
                                            </button>

                                            {{-- Delete Form --}}
                                            <form method="POST" action="{{ route('company.destroy', $company) }}"
                                                style="display: inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this company?')">
                                                @csrf
                                                @method('DELETE')
                                                {{-- Delete Button (Triggers Modal) --}}
                                                {{-- <button type="button" class="btn-delete"
                                                    onclick="openDeleteModal('{{ $company->slug }}', '{{ addslashes($company->name) }}')">
                                                    Delete
                                                </button> --}}
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="pagination-wrapper">
                        {{ $companies->links('pagination::simple-bootstrap-4') }}
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- =========================================
     🔐 Company Login Modal
     ========================================= -->
    <div id="companyLoginModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔐 Access <span id="modalCompanyName"></span></h3>
                <button type="button" class="modal-close" onclick="closeCompanyLoginModal()">&times;</button>
            </div>

            <form id="companyLoginForm" method="POST" action="{{ route('company.verify') }}">
                @csrf

                <div id="modalError" class="error-message" style="display: none;"></div>

                <input type="hidden" name="company_slug" id="modalCompanySlug">

                <div class="form-group">
                    <label for="modalUsername">Username *</label>
                    <input type="text" name="username" id="modalUsername" placeholder="Enter company username" required
                        autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="modalPassword">Password *</label>
                    <input type="password" name="password" id="modalPassword" placeholder="Enter company password"
                        required autocomplete="current-password">
                </div>

                <div
                    style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 24px; align-items: center;">

                    <button type="button" onclick="closeCompanyLoginModal()" style="
            padding: 12px 22px;
            border: 1px solid #d1d5db;
            border-radius: 30px;
            background: linear-gradient(135deg, #ffffff, #f3f4f6);
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 14px rgba(0,0,0,0.12)'"
                        onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.08)'">
                        Cancel
                    </button>

                    <button type="submit" id="modalSubmitBtn" style="
            padding: 12px 24px;
            border: none;
            border-radius: 30px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8, #3b82f6);
            color: white;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
            transition: all 0.3s ease;
        " onmouseover="this.style.transform='translateY(-2px) scale(1.02)'; this.style.boxShadow='0 8px 20px rgba(37, 99, 235, 0.45)'"
                        onmouseout="this.style.transform='translateY(0px) scale(1)'; this.style.boxShadow='0 6px 16px rgba(37, 99, 235, 0.35)'">
                        <span id="modalBtnText">Access Company</span>
                        <span id="modalBtnLoading" style="display: none; font-weight: 600;">⏳ Verifying...</span>
                    </button>

                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/company.js') }}"></script>

</body>

</html>
</div>
</form>
</div>
</div>

<script src="{{ asset('js/company.js') }}"></script>

</body>

</html>