@extends('layouts.default', ['page' => 'family'])
@section('content')
<h2 class="title has-text-weight-bold">Edit Family Member</h2>
<h4 class="subtitle">
    {{ $user->full_name }} <a href="{{ route('families_member_view', [$familyMember->family, $familyMember]) }}">(View)</a>
</h4>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        <form action="{{ route('families_member_update', [$familyMember->family, $familyMember]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label class="label">Role<sup style="color: red">*</sup></label>
                <div class="control">
                    <div class="select is-fullwidth" id="role">
                        <select name="role" class="input" required>
                            <option value="admin" {{ $familyMember->role === App\Enums\FamilyMemberRole::Admin || old('role') === App\Enums\FamilyMemberRole::Admin ? "selected" : "" }}>
                                Admin
                            </option>
                            <option value="member" {{ $familyMember->role === App\Enums\FamilyMemberRole::Member || old('role') === App\Enums\FamilyMemberRole::Member ? "selected" : "" }}>
                                Member
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="control">
                <button class="button is-light" type="submit">
                    💾 Save
                </button>
                <a class="button is-outlined" href="{{ route('families_member_view', [$familyMember->family, $familyMember]) }}">
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>
@endsection
