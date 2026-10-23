@if($errors->getBag('userImport')->any())
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Unable to import users',
        text: {{ \Illuminate\Support\Js::from(implode("\n", $errors->getBag('userImport')->all())) }},
        confirmButtonText: 'Choose another file',
        confirmButtonColor: '#0d6efd'
    }).then(function () {
        const role = {{ \Illuminate\Support\Js::from(old('default_role', '')) }};
        if (['student', 'faculty', 'staff'].includes(role)) {
            selectImportRole(role);
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('importModal')).show();
    });
});
@endif
