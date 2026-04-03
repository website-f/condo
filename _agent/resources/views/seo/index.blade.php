@extends('layouts.app')
@section('title', 'SEO')
@section('page-title', 'SEO Settings')
@section('topbar-actions')
    <a href="{{ route('seo.create') }}" class="btn btn-primary btn-sm">New Setting</a>
@endsection

@section('content')
@if($settings->count())
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Page Type</th><th>Identifier</th><th>Meta Title</th><th>Robots</th><th>Updated</th><th style="width:120px;">Actions</th></tr>
            </thead>
            <tbody>
            @foreach($settings as $seo)
                <tr>
                    <td style="font-weight:500;">{{ $seo->page_type }}</td>
                    <td style="color:var(--text-secondary);">{{ $seo->page_identifier ?? '—' }}</td>
                    <td>{{ Str::limit($seo->meta_title, 40) ?? '—' }}</td>
                    <td><span class="badge badge-default">{{ $seo->robots }}</span></td>
                    <td style="color:var(--text-secondary);">{{ $seo->updated_at?->format('M d, Y') }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('seo.edit', $seo) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <form action="{{ route('seo.destroy', $seo) }}" method="POST" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="pagination">{{ $settings->links('components.pagination') }}</div>
@else
<div class="card">
    <div class="empty-state">
        <p>No SEO settings configured</p>
        <a href="{{ route('seo.create') }}" class="btn btn-primary">Add SEO Setting</a>
    </div>
</div>
@endif
@endsection
