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
    document.addEventListener('DOMContentLoaded', () => {
        (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
            $notification = $delete.parentNode;
            $delete.addEventListener('click', () => {
                $notification.parentNode.removeChild($notification);
            });
            setTimeout(
                function() {
                    $notification.parentNode.removeChild($notification);
                }, 2250);
        });
    });
</script>