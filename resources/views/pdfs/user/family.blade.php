@extends('pdfs.layouts.default')

@section('content')
    @foreach($family->users as $user)
        <div style="@unless($loop->last) page-break-after: always; @endunless">
            @include('pdfs.common.user', ['user' => $user])
        </div>
    @endforeach
@endsection