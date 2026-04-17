@extends('layouts.app')
@section('title', 'Profile')
@section('page-title', 'Profile')

@section('content')
<div class="grid-2" style="align-items:start;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">Personal Information</div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="firstname" class="form-input" value="{{ old('firstname', $agent->detail?->firstname) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="lastname" class="form-input" value="{{ old('lastname', $agent->detail?->lastname) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $agent->detail?->email) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone', $agent->detail?->phone) }}">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">Change Password</div>
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-input" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">Account Details</div>
            <div style="font-size:13px;line-height:2.2;">
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding:2px 0;">
                    <span style="color:var(--text-secondary);">Username</span>
                    <span style="font-weight:500;">{{ $agent->username }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding:2px 0;">
                    <span style="color:var(--text-secondary);">Package</span>
                    <span style="font-weight:500;">{{ $agent->subscription?->display_name ?? 'N/A' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding:2px 0;">
                    <span style="color:var(--text-secondary);">Status</span>
                    <span class="badge {{ $agent->activated ? 'badge-success' : 'badge-danger' }}">{{ $agent->activated ? 'Active' : 'Inactive' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding:2px 0;">
                    <span style="color:var(--text-secondary);">IC Number</span>
                    <span style="font-weight:500;">{{ $agent->detail?->icnumber ?? 'N/A' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding:2px 0;">
                    <span style="color:var(--text-secondary);">Agency</span>
                    <span style="font-weight:500;">{{ $agent->detail?->agencyname ?? 'N/A' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:2px 0;">
                    <span style="color:var(--text-secondary);">Joined</span>
                    <span style="font-weight:500;">{{ $agent->createddate ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
