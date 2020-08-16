@if (Session::has('success'))
<div class="notification is-primary is-light">
    <span>{!! Session::get('success') !!}</span>
    <button class="delete"></button>
</div>
@endif

@if (Session::has('error'))
<div class="notification is-danger is-light">
    <span>{!! Session::get('error') !!}</span>
    <button class="delete"></button>
</div>
@endif

@foreach ($errors->all() as $error)
<div class="notification is-danger is-light">
    <span>{!! $error !!}</span>
    <button class="delete"></button>
</div>
@endforeach

<script>
    $(document).ready(function() {
        setTimeout(function() {
            $('.notification').each(function() {
                $(this).hide(200);
            });
        }, 2250);
    });

    $('.delete').click(function() {
        $(this.parentNode).hide(100);
    });
</script>