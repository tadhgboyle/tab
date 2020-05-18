@if (Session::has('success'))
<div class="alert alert-success alert-dismissible fade show">
    <span>{!! Session::get('success') !!}</span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
@if (Session::has('error'))
<div class="alert alert-danger alert-dismissable fade show">
    <span>{!! Session::get('error') !!}</span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
<script>
    $(document).ready(function() {
        setTimeout(
            function() {
                $(".alert").alert('close')
            }, 2400);
    });
</script>