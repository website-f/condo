@extends('layouts.app')
@section('title', 'New Social Post')
@section('page-title', 'New Social Post')
@section('topbar-actions')
    <a href="{{ route('social.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<div style="max-width:640px;">
    <div class="card">
        <form method="POST" action="{{ route('social.post.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Platform</label>
                    <select name="platform" class="form-select" required>
                        <option value="">Select...</option>
                        <option value="facebook">Facebook</option>
                        <option value="twitter">Twitter / X</option>
                        <option value="instagram">Instagram</option>
                        <option value="linkedin">LinkedIn</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Account</label>
                    <select name="social_account_id" class="form-select">
                        <option value="">Select account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_name }} ({{ ucfirst($account->platform) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-textarea" rows="6" required placeholder="What would you like to share?" maxlength="2000">{{ old('content') }}</textarea>
                <div class="form-hint">Max 2000 characters</div>
            </div>
            <div class="form-group">
                <label class="form-label">Media</label>
                <input type="file" name="media" class="form-input" accept="image/*">
            </div>
            <div class="form-group">
                <label class="form-label">Schedule</label>
                <input type="datetime-local" name="scheduled_at" class="form-input" value="{{ old('scheduled_at') }}">
                <div class="form-hint">Leave empty to publish now or save as draft</div>
            </div>
            <div class="btn-group" style="margin-top:20px;">
                <button type="submit" name="action" value="publish_now" class="btn btn-primary">Publish Now</button>
                <button type="submit" name="action" value="schedule" class="btn btn-secondary">Schedule</button>
                <button type="submit" name="action" value="draft" class="btn btn-secondary">Save Draft</button>
            </div>
        </form>
    </div>
</div>
@endsection
