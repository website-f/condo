@extends('layouts.app')
@section('title', 'New Listing')
@section('page-title', 'New Listing')
@section('topbar-actions')
    <a href="{{ route('listings.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data">
    @csrf
    @include('listings._form', ['submitLabel' => 'Create Listing'])
</form>
@endsection
