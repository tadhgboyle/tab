@extends('layouts.default')
@section('content')
<h2>Settings</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        @if (\Session::has('error'))
        <div class="alert alert-danger">
            <p>{!! \Session::get('error') !!}</p>
        </div>
        @endif
        <form action="/settings/submit" method="POST">
            @csrf
            <?php

            use App\Settings;

            $settings = Settings::all();
            ?>
            GST<input type="number" step="0.01" name="gst" class="form-control" placeholder="GST" value="{{ $settings['0']['value'] }}">
            PST<input type="number" step="0.01" name="pst" class="form-control" placeholder="PST" value="{{ $settings['1']['value'] }}">
            <button type="submit">Submit</button>
        </form>
    </div>
    <div class="col-md-4">
    </div>
</div>
@stop