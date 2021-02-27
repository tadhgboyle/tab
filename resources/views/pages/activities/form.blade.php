@extends('layouts.default', ['page' => 'activities'])
@section('content')
<h2 class="title has-text-weight-bold">{{ is_null($activity) ? 'Create' : 'Edit' }} Activity</h2>
@if(!is_null($activity)) <h4 class="subtitle"><strong>Activity:</strong> {{ $activity->name }}</h4> @endif
<div class="columns box">
    <div class="column is-1"></div>

    <div class="column is-5">
        @include('includes.messages')
        <form action="{{ is_null($activity) ? route('activities_new_form') : route('activities_edit_form') }}" id="product_form" method="POST"
            class="form-horizontal">
            @csrf
            <input type="hidden" name="id" id="activity_id" value="{{ request()->route('id') }}">

            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Name" required value="{{ $activity->name ?? old('name') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Location</label>
                <div class="control">
                    <input type="text" name="location" class="input" placeholder="Location" value="{{ $activity->location ?? old('location') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Description</label>
                <div class="control">
                    <input type="text" name="description" class="input" placeholder="Description" value="{{ $activity->description ?? old('description') }}">
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
                <div class="control">
                    <label class="checkbox label">
                        Unlimited Slots
                        <input type="checkbox" class="js-switch" name="unlimited_slots" {{ (isset($activity->unlimited_slots) && $activity->unlimited_slots) || old('unlimited_slots') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

    </div>
    <div class="column is-4">
            <div class="field">
                <label class="label">Start Time<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="start" id="start" class="input" required>
                </div>
            </div>

            <div class="field">
                <label class="label">End Time<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="end" id="end" class="input" required value="{{ (isset($activity->end)) ? $activity->end : old('end') }}">
                </div>
            </div>

            <div class="field" id="slots_div" style="display: none;">
                <label class="label">Slots</label>
                <div class="control">
                    <input type="number" step="1.00" name="slots" min="1" class="input" value="{{ isset($activity->slots) ? $activity->slots : old('slots') }}">
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

    let endMinDate = null;

    function startChange(e) {
        endMinDate = new Date(e);
        flatpickr('#end', { enableTime: true, altInput: true, altFormat: 'F j, Y h:i K', minDate: endMinDate });
    }

    $(document).ready(function() {
        const date = new Date('{{ $start }}');
        @if(is_null($activity))
            flatpickr('#start', { defaultDate: date, onChange: startChange, enableTime: true, altInput: true, altFormat: 'F j, Y h:i K', minDate: 'today' });
        @else
            flatpickr('#start', { defaultDate: date, onChange: startChange, enableTime: true, altInput: true, altFormat: 'F j, Y h:i K' });
        @endif
        flatpickr('#end', { enableTime: true, altInput: true, altFormat: 'F j, Y h:i K', minDate: date });
        updatedUnlimitedSlots($('input[type=checkbox][name=unlimited_slots]').prop('checked'));
    });
        
    $('input[type=checkbox][name=unlimited_slots]').change(function() {
        updatedUnlimitedSlots($(this).prop('checked'))
    });

    function updatedUnlimitedSlots(checked) {
        let div = $('#slots_div');
        if (checked) div.hide(200);
        else div.show(200);
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
            var id = '{{ $activity->id }}';
            var url = '{{ route("activities_delete", ":id") }}';
            url = url.replace(':id', id);
            $("#deleteForm").attr('action', url);
            $("#deleteForm").submit();
        }
    @endif
</script>
@endsection