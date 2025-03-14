@extends('layouts.default', ['page' => 'activities'])
@section('content')
<x-page-header title="{{ isset($activity) ? $activity->name : 'Create Activity' }}" :actions="isset($activity) ? [
    [
        'label' => 'View',
        'href' => route('activities_view', $activity),
        'can' => hasPermission(\App\Helpers\Permission::ACTIVITIES_VIEW)
    ],
] : []" />

<form action="{{ isset($activity) ? route('activities_update', $activity->id) : route('activities_store') }}" id="activity_form" method="POST" class="form-horizontal">
    @csrf

    @isset($activity)
        @method('PUT')
        <input type="hidden" name="activity_id" id="activity_id" value="{{ $activity->id }}">
    @endif

    <div class="columns box">
        <div class="column is-1"></div>

        <div class="column is-5">
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
{{--                    TODO make textarea--}}
                    <input type="text" name="description" class="input" placeholder="Description" value="{{ $activity->description ?? old('description') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Price<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="price" class="input money-input" placeholder="Price" required value="{{ (isset($activity) ? $activity->price->formatForInput() : null) ?? number_format(old('price'), 2) }}">
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        PST
                        <input type="checkbox" name="pst" {{ (isset($activity) && $activity->pst === true) || old('pst') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>
        </div>
        <div class="column is-4">
            <div class="field">
                <label class="label">Start Time<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="datetime-local" name="start" value="{{ $start }}" id="start" class="input" required>
                </div>
            </div>

            <div class="field">
                <label class="label">End Time<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="datetime-local" name="end" value="{{ $end }}" id="end" class="input" required>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        Unlimited Slots
                        <input type="checkbox" name="unlimited_slots" {{ (isset($activity) && $activity->unlimited_slots) || old('unlimited_slots') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

            <div class="field" id="slots_div" style="display: none;">
                <label class="label">Slots</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-hashtag"></i>
                    </span>
                    <input type="number" step="1.00" name="slots" @unless(isset($activity) && $activity->unlimited_slots) min="1" @endunless placeholder="10" class="input" value="{{ $activity->slots ?? old('slots') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Category<sup style="color: red">*</sup></label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="category_id" required>
                            {!! !isset($activity) ? "<option value=\"\" disabled selected>Select Category...</option>" : '' !!}
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (isset($activity) && $activity->category_id == $category->id) || old('category') == $category->id  ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-2">
            <form>
                <div class="control">
                    <button class="button is-light" type="submit" form="activity_form">
                        💾 Submit
                    </button>
                </div>
            </form>
            <br>
            @isset($activity)
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
            @endisset
        </div>
    </div>
</form>

@isset($activity)
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to delete the activity {{ $activity->name }}?</p>
            <form action="{{ route('activities_delete', $activity->id) }}" id="deleteForm" method="POST">
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

<script type="text/javascript">
    $(document).ready(function() {
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

    @isset($activity)
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }
    @endisset
</script>
@endsection
