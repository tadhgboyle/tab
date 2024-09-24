@extends('pdfs.layouts.default')

@section('content')
    @include('pdfs.common.user', ['user' => $user])
@endsection
