@extends('layouts.default')
@section('content')
<h2>Settings</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        @if (\Session::has('success'))
        <div class="alert alert-success">
            <p>{!! \Session::get('success') !!}</p>
        </div>
        @endif
        <form action="/settings/submit" method="POST">
            @csrf
            <?php

            use App\Http\Controllers\SettingsController;
            ?>
            GST<input type="number" step="0.01" name="gst" class="form-control" placeholder="GST" value="{{ SettingsController::getGst() }}">
            PST<input type="number" step="0.01" name="pst" class="form-control" placeholder="PST" value="{{ SettingsController::getPst() }}">
            <button type="submit">Submit</button>
        </form>
    </div>
    <div class="col-md-4">
    </div>
</div>
@stop