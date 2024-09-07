@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($rotation) ? 'Edit' : 'Create' }} Rotation</h2>
@isset($rotation) <h4 class="subtitle"><strong>Rotation:</strong> {{ $rotation->name }}</h4>@endisset
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        <form action="{{ isset($rotation) ? route('settings_rotations_update', $rotation->id) : route('settings_rotations_store') }}" method="POST">
            @csrf
            @isset($rotation)
                @method('PUT')
                <input type="hidden" name="rotation_id" value="{{ $rotation->id }}">
            @endisset

            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Rotation Name" value="{{ $rotation->name ?? old('name') }}" required>
                </div>
            </div>
            <div class="field">
                <label class="label">Start<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="datetime-local" name="start" value="{{ $rotation->start ?? old('start') }}" id="start" class="input" required>
                </div>
            </div>
            <div class="field">
                <label class="label">End<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="datetime-local" name="end" value="{{ $rotation->end ?? old('end') }}" id="end" class="input" required>
                </div>
            </div>
            <div class="control">
                <button class="button is-light" type="submit">
                    💾 Save
                </button>
                <a class="button is-outlined" href="{{ route('settings') }}">
                    <span>Cancel</span>
                </a>
                @isset($rotation)
                <button class="button is-danger is-outlined is-pulled-right" type="button" onclick="openModal();">
                    <span>Delete</span>
                    <span class="icon is-small">
                        <i class="fas fa-times"></i>
                    </span>
                </button>
                @endisset
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>

@isset($rotation)
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to delete the rotation <strong>{{ $rotation->name }}</strong>?</p>
            <form action="{{ route('settings_rotations_delete', $rotation->id) }}" id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" form="deleteForm">Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>
@endisset

<script>
    @isset($rotation)
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }
    @endisset
</script>
@stop
