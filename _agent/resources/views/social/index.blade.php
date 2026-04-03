@extends('layouts.app')
@section('title', 'Social Media')
@section('page-title', 'Social Media')
@section('topbar-actions')
    <a href="{{ route('social.post.create') }}" class="btn btn-primary btn-sm">New Post</a>
    <a href="{{ route('social.account.create') }}" class="btn btn-secondary btn-sm">Connect Account</a>
@endsection

@section('content')
<!-- Connected Accounts -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">Connected Accounts</div>
    @if($accounts->count())
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        @foreach($accounts as $account)
        <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:var(--accent-light);border-radius:8px;border:1px solid var(--border);">
            <div>
                <div style="font-size:13px;font-weight:500;">{{ $account->account_name }}</div>
                <div style="font-size:11px;color:var(--text-secondary);">{{ ucfirst($account->platform) }} · {{ $account->is_active ? 'Active' : 'Inactive' }}</div>
            </div>
            <form action="{{ route('social.account.destroy', $account) }}" method="POST" onsubmit="return confirm('Disconnect this account?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm" style="padding:3px 8px;">Remove</button>
            </form>
        </div>
        @endforeach
    </div>
    @else
    <p style="font-size:13px;color:var(--text-secondary);">No accounts connected. <a href="{{ route('social.account.create') }}" style="color:var(--text);">Connect one now.</a></p>
    @endif
</div>

<!-- Scheduled Posts -->
@if($scheduled->count())
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">Scheduled Posts</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Platform</th><th>Content</th><th>Scheduled For</th><th style="width:80px;"></th></tr></thead>
            <tbody>
            @foreach($scheduled as $post)
                <tr>
                    <td><span class="badge badge-default">{{ ucfirst($post->platform) }}</span></td>
                    <td>{{ Str::limit($post->content, 60) }}</td>
                    <td>{{ $post->scheduled_at?->format('M d, Y H:i') }}</td>
                    <td>
                        <form action="{{ route('social.post.destroy', $post) }}" method="POST" onsubmit="return confirm('Cancel this post?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Cancel</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- All Posts -->
<div class="card">
    <div class="card-header">All Posts</div>
    @if($posts->count())
    <div class="table-wrap">
        <table>
            <thead><tr><th>Platform</th><th>Content</th><th>Status</th><th>Date</th><th style="width:80px;"></th></tr></thead>
            <tbody>
            @foreach($posts as $post)
                <tr>
                    <td><span class="badge badge-default">{{ ucfirst($post->platform) }}</span></td>
                    <td>{{ Str::limit($post->content, 50) }}</td>
                    <td>
                        <span class="badge {{ $post->status === 'published' ? 'badge-success' : ($post->status === 'scheduled' ? 'badge-warning' : ($post->status === 'failed' ? 'badge-danger' : 'badge-default')) }}">
                            {{ $post->status }}
                        </span>
                    </td>
                    <td style="color:var(--text-secondary);">{{ $post->created_at?->format('M d, Y') }}</td>
                    <td>
                        <form action="{{ route('social.post.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $posts->links('components.pagination') }}</div>
    @else
    <div class="empty-state"><p>No posts yet</p></div>
    @endif
</div>
@endsection
