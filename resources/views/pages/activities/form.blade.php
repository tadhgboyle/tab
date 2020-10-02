@php

use App\Activity;
$activity = Activity::find(request()->route('id'));
$start = request()->route('date');
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">{{ is_null($activity) ? 'Create' : 'Edit' }} Activity</h2>
@if(!is_null($activity)) <h4 class="subtitle"><strong>Activity:</strong> {{ $activity->name }}</h4> @endif
<div class="columns box">
    <div class="column is-1"></div>

    <div class="column is-5">
        @include('includes.messages')
        <form action="/activities/{{ is_null($activity) ? 'new' : 'edit' }}" id="product_form" method="POST"
            class="form-horizontal">
            @csrf
            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
            <input type="hidden" name="product_id" id="product_id" value="{{ $activity->id ?? null }}">

            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Name" required value="{{ $activity->name ?? old('name') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Price<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="price" class="input" required value="{{ isset($activity->price) ? number_format($activity->price, 2) : number_format(old('price'), 2) }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Slots<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="number" step="1.00" name="slots" min="-1" class="input" value="{{ isset($activity->slots) ? $activity->slots : old('slots') }}">
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        PST
                        <input type="checkbox" class="js-switch" name="pst" {{ (isset($activity->pst) && $activity->pst) || old('pst') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>
    </div>
    <div class="column is-4">

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        Start Date
                        
                    </label>
                </div>
            </div>

        <div class="field">
            <div class="control">
                <label class="checkbox label">
                    All Day
                    <input type="checkbox" class="js-switch" name="all_day" {{ (isset($activity->all_day) && $activity->all_day) || old('all_day') ? 'checked' : '' }}>
                </label>
            </div>
        </div>

        <div id="all_day_attr" style="display: none;">

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        End Date
                        
                    </label>
                </div>
            </div>

        </form>
    </div>
    <div class="column is-2">
        <form>
            <div class="control">
                <button class="button is-success" type="submit" form="product_form">
                    <span class="icon is-small">
                        <i class="fas fa-check"></i>
                    </span>
                    <span>Submit</span>
                </button>
            </div>
        </form>
        <br>
        @if(!is_null($activity))
        <div class="control">
            <form>
                <button class="button is-danger is-outlined" type="button" onclick="openModal();">
                    <span>Delete</span>
                    <span class="icon is-small">
                        <i class="fas fa-times"></i>
                    </span>
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

@if(!is_null($activity))
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to delete the activity {{ $activity->name }}?</p>
            <form action="" id="deleteForm" method="GET">
                @csrf
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" onclick="deleteData();">Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>
@endif

<script type="text/javascript">
    const switches = document.getElementsByClassName("js-switch");
    for (var i = 0; i < switches.length; i++) { new Switchery(switches.item(i), {color: '#48C774', secondaryColor: '#F56D71'}) }

    $(document).ready(function() {
        updateAllDay($('input[type=checkbox][name=all_day]').prop('checked'));
    });
        
    $('input[type=checkbox][name=all_day]').change(function() {
        updateAllDay($(this).prop('checked'))
    });
        
    function updateAllDay(checked) {
        let div = $('#all_day_attr');
        if (checked) div.fadeOut(200);
        else div.fadeIn(200);
    }

    @if(!is_null($activity))
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }

        function deleteData() {
            var id = document.getElementById('product_id').value;
            var url = '{{ route("products_delete", ":id") }}';
            url = url.replace(':id', id);
            $("#deleteForm").attr('action', url);
            $("#deleteForm").submit();
        }
    @endif
</script>
@endsection