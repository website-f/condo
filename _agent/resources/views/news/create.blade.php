@extends('layouts.app')
@section('title', 'New News Article')
@section('page-title', 'New News Article')
@section('topbar-actions')
    <a href="{{ route('news.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('news.store') }}">
    @csrf
    @include('news._form', [
        'submitLabel' => 'Create News Article',
        'cancelRoute' => route('news.index'),
    ])
</form>
@endsection
