@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">{{ is_null($rotation) ? 'Create' : 'Edit' }} Rotation</h2>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        @include('includes.messages')
        <form action="{{ is_null($rotation) ? route('settings_rotations_new_form') : route('settings_rotations_edit_form') }}" method="POST">
            @csrf
            <input type="hidden" name="rotation_id" id="rotation_id" value="{{ $rotation->id ?? null }}">
            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Rotation Name" value="{{ $rotation->name ?? old('name') }}">
                </div>
            </div>
            <div class="field">
                <label class="label">Start<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="start" id="start" class="input" required>
                </div>
            </div>
            <div class="field">
                <label class="label">End<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="end" id="end" class="input" required>
                </div>
            </div>
            <div class="control">
                <button class="button is-success" type="submit">
                    <span class="icon is-small">
                        <i class="fas fa-save"></i>
                    </span>
                    <span>Save</span>
                </button>
                <a class="button is-outlined" href="{{ route('settings') }}">
                    <span>Cancel</span>
                </a>
                @if(!is_null($rotation))
                <button class="button is-danger is-outlined is-pulled-right" type="button" onclick="openModal();">
                    <span>Delete</span>
                    <span class="icon is-small">
                        <i class="fas fa-times"></i>
                    </span>
                </button>
                @endif
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>

@if(!is_null($rotation))
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to delete the rotation <strong>{{ $rotation->name }}</strong>?</p>
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

<script>
    @if(!is_null($rotation))
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }

        function deleteData() {
            let url = '{{ route("settings_rotations_delete", ":id") }}';
            url = url.replace(':id', {{ $rotation->id }});
            $("#deleteForm").attr('action', url);
            $("#deleteForm").submit();
        }
    @endif

    let endMinDate = null;

    function startChange(e) {
        endMinDate = new Date(e);
        flatpickr('#end', { enableTime: true, altInput: true, altFormat: 'F j, Y h:i K', minDate: endMinDate });
    }

    $(document).ready(function() {
        const date = new Date();

        @if(is_null($rotation))
            flatpickr('#start', { defaultDate: date, onChange: startChange, enableTime: true, altInput: true, altFormat: 'F j, Y h:i K', minDate: 'today' });
        @else
            flatpickr('#start', { defaultDate: date, onChange: startChange, enableTime: true, altInput: true, altFormat: 'F j, Y h:i K' });
        @endif

        flatpickr('#end', { enableTime: true, altInput: true, altFormat: 'F j, Y h:i K', minDate: date });
    });
</script>
@stop
