@if (session()->has('success'))
<div class="notification notification-fade is-primary is-light">
    <span>{!! session()->get('success') !!}</span>
    <button class="delete close-notification"></button>
</div>
@endif

@if (session()->has('error'))
    <div class="notification notification-fade is-danger is-light">
        <span>{!! session()->get('error') !!}</span>
        <button class="delete close-notification"></button>
    </div>
@endif

@foreach ($errors->all() as $error)
<div class="notification notification-fade is-danger is-light">
    <span>{!! $error !!}</span>
    <button class="delete close-notification"></button>
</div>
@endforeach

<script>
    $(document).ready(function() {
        setTimeout(function() {
            $('.notification-fade').each(function() {
                $(this).fadeOut(200);
            });
        }, 2250);
    });

    $('.close-notification').click(function() {
        $(this.parentNode).fadeOut(100);
    });
</script>
