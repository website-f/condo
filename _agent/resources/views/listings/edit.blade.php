@extends('layouts.app')
@section('title', 'Edit Listing')
@section('page-title', 'Edit Listing')
@section('topbar-actions')
    <a href="{{ route('listings.show', $listing->id) }}" class="btn btn-secondary btn-sm">View</a>
    <a href="{{ route('listings.index') }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('listings.update', $listing->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('listings._form', ['submitLabel' => 'Update Listing'])
</form>
@endsection
