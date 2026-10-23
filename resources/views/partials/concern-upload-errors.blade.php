@if($errors->has('image'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    ImageUploadFeedback.show({{ \Illuminate\Support\Js::from(implode("\n", $errors->get('image'))) }}, 'Invalid concern image');
});
</script>
@endif
