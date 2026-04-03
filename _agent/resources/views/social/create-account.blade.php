@extends('layouts.app')
@section('title', 'Connect Social Account')
@section('page-title', 'Connect Social Account')
@section('topbar-actions')
    <a href="{{ route('social.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<div style="max-width:560px;">
    <div class="card">
        <div class="card-header">Account Details</div>
        <form method="POST" action="{{ route('social.account.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Platform</label>
                <select name="platform" class="form-select" required>
                    <option value="">Select platform...</option>
                    <option value="facebook">Facebook</option>
                    <option value="twitter">Twitter / X</option>
                    <option value="instagram">Instagram</option>
                    <option value="linkedin">LinkedIn</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Account Name</label>
                <input type="text" name="account_name" class="form-input" required placeholder="e.g. My Facebook Page">
            </div>
            <div class="form-group">
                <label class="form-label">Access Token</label>
                <input type="text" name="access_token" class="form-input" placeholder="Page access token (for auto-posting)">
                <div class="form-hint">Required for Facebook auto-posting. Get from Facebook Developer Console.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Page ID</label>
                <input type="text" name="page_id" class="form-input" placeholder="Facebook Page ID">
                <div class="form-hint">Required for Facebook. Find in your Page's About section.</div>
            </div>
            <button type="submit" class="btn btn-primary">Connect Account</button>
        </form>
    </div>
</div>
@endsection
