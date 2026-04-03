@extends('layouts.app')
@section('title', 'Edit News')
@section('page-title', 'Edit News')
@section('topbar-actions')
    <a href="{{ route('news.show', $article->ID) }}" class="btn btn-secondary btn-sm">View</a>
    <a href="{{ route('news.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('news.update', $article->ID) }}">
    @csrf
    @method('PUT')
    @include('news._form', [
        'submitLabel' => 'Update News Article',
        'cancelRoute' => route('news.show', $article->ID),
    ])
</form>
@endsection
