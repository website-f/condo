@extends('layouts.app')
@section('title', 'Edit Listing')
@section('page-title', 'Edit Listing')
@section('topbar-actions')
    <a href="{{ route('listings.show', array_filter(['id' => $listing->id, 'source' => $originalSource === 'ipp' ? null : $originalSource, 'return_source' => $returnSource], static fn ($value) => $value !== null)) }}" class="btn btn-secondary btn-sm">View</a>
    <a href="{{ route('listings.index', ['source' => $returnSource]) }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('listings.update', array_filter(['id' => $listing->id, 'source' => $originalSource === 'ipp' ? null : $originalSource, 'return_source' => $returnSource], static fn ($value) => $value !== null)) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('listings._form', ['submitLabel' => 'Update Listing'])
</form>
@endsection
