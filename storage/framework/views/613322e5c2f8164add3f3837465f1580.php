

<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/profile.css')); ?>" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<?php $__env->stopSection(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let cropper;
    const fileInput = document.getElementById('profile_picture');
    const nextBtn = document.getElementById('next-btn');
    const backBtn = document.getElementById('back-btn');
    const cropBtn = document.getElementById('crop-btn');
    const fileStep = document.getElementById('file-selection-step');
    const cropStep = document.getElementById('cropping-step');
    const cropperImg = document.getElementById('cropper-image');

    // When file is selected
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                cropperImg.src = e.target.result;
                nextBtn.disabled = false;
            };
            reader.readAsDataURL(file);
        } else {
            nextBtn.disabled = true;
        }
    });

    // Next button - go to cropping
    nextBtn.addEventListener('click', function() {
        fileStep.style.display = 'none';
        cropStep.style.display = 'block';

        // Initialize cropper
        cropper = new Cropper(cropperImg, {
            aspectRatio: 1,
            viewMode: 1,
            responsive: true,
            restore: false,
            checkCrossOrigin: false,
            checkOrientation: false,
            modal: true,
            guides: true,
            center: true,
            highlight: false,
            background: false,
            autoCrop: true,
            autoCropArea: 0.8,
            dragMode: 'move'
        });
    });

    // Back button - go back to file selection
    backBtn.addEventListener('click', function() {
        cropStep.style.display = 'none';
        fileStep.style.display = 'block';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    // Remove profile picture
    const removeBtn = document.getElementById('remove-btn');
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                fetch('<?php echo e(route("profile.removePicture")); ?>', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        // Close modal and reload
                        const modal = bootstrap.Modal.getInstance(document.getElementById('uploadPictureModal'));
                        modal.hide();
                        location.reload();
                    } else {
                        alert('Failed to remove profile picture');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to remove profile picture');
                });
            }
        });
    }

    // Crop and upload
    cropBtn.addEventListener('click', function() {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300
            });

            canvas.toBlob(function(blob) {
                const formData = new FormData();
                formData.append('profile_picture', blob, 'profile.jpg');

                fetch('<?php echo e(route("profile.uploadPicture")); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        // Close modal and reload
                        const modal = bootstrap.Modal.getInstance(document.getElementById('uploadPictureModal'));
                        modal.hide();
                        location.reload();
                    } else {
                        alert('Upload failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Upload failed');
                });
            }, 'image/jpeg', 0.9);
        }
    });

    // Reset modal when closed
    document.getElementById('uploadPictureModal').addEventListener('hidden.bs.modal', function() {
        fileStep.style.display = 'block';
        cropStep.style.display = 'none';
        fileInput.value = '';
        nextBtn.disabled = true;
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });
});
</script>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-header text-center">
                <div class="profile-avatar-container">
                    <div class="profile-avatar">
                        <?php if($user->profile_picture): ?>
                            <img src="<?php echo e(asset('storage/' . $user->profile_picture)); ?>?t=<?php echo e(time()); ?>" alt="Profile Picture" class="profile-image">
                        <?php else: ?>
                            <?php echo e(substr($user->name, 0, 1)); ?>

                        <?php endif; ?>
                    </div>
                    <!-- Upload Overlay -->
                    <div class="upload-overlay" data-bs-toggle="modal" data-bs-target="#uploadPictureModal">
                        <i class="fas fa-camera"></i>
                        <span>Update</span>
                    </div>
                </div>
                <h3><?php echo e($user->name); ?></h3>
                <p class="mb-0"><?php echo e($user->email); ?></p>
                <span class="badge bg-light text-dark mt-2"><?php echo e(ucfirst(str_replace('_', ' ', $user->role))); ?></span>
            </div>
            
            <div class="card-body p-4">
                <!-- Success Message -->
                <?php if(session('success')): ?>
                    <div class="alert alert-success">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>
                
                <!-- Profile Update Form -->
                <form action="<?php echo e(route('profile.update')); ?>" method="POST" class="mb-4">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <h5 class="mb-3">Profile Information</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($user->name); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo e($user->email); ?>" disabled>
                        <small class="text-muted">Email cannot be changed.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo e($user->phone); ?>" placeholder="09XXXXXXXXX" maxlength="11" pattern="09[0-9]{9}">
                        <small class="text-muted">11-digit PH number (e.g., 09123456789). Used for SMS OTP verification</small>
                    </div>
                    
                    <button type="submit" class="btn btn-update">Update Profile</button>
                 </form>
                
                <hr>
                
                <!-- Password Update Form -->
                <form action="<?php echo e(route('profile.password')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <h5 class="mb-3">Change Password</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" id="currentPassword" required>
                            <button type="button" class="btn btn-outline-secondary" id="toggleCurrentPw">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if($errors->has('current_password')): ?>
                            <span class="text-danger"><?php echo e($errors->first('current_password')); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" class="form-control" id="profileNewPassword" required minlength="8" maxlength="20" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary" id="toggleNewPw">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <!-- Strength bar -->
                        <div id="profileStrengthWrap" class="mt-1" style="display:none">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px">
                                    <div id="profileStrengthBar" class="progress-bar" style="width:0%;transition:width .3s,background .3s"></div>
                                </div>
                                <small id="profileStrengthLabel" class="fw-semibold" style="min-width:52px;font-size:12px"></small>
                            </div>
                        </div>
                        <!-- Requirements -->
                        <div id="profilePwReqs" class="mt-2 p-3 rounded shadow-sm" style="display:none;background:#f8f9fa;font-size:13px;border:1px solid #dee2e6">
                            <div class="fw-semibold mb-2">Password must include:</div>
                            <div id="prof-req-length"  class="req-item"><i class="fas fa-times-circle text-danger me-2"></i>8-20 <strong>Characters</strong></div>
                            <div id="prof-req-upper"   class="req-item mt-1"><i class="fas fa-times-circle text-danger me-2"></i>At least one <strong>capital letter</strong></div>
                            <div id="prof-req-number"  class="req-item mt-1"><i class="fas fa-times-circle text-danger me-2"></i>At least one <strong>number</strong></div>
                            <div id="prof-req-special" class="req-item mt-1"><i class="fas fa-times-circle text-danger me-2"></i>At least one <strong>special character</strong></div>
                            <div id="prof-req-nospace" class="req-item mt-1"><i class="fas fa-times-circle text-danger me-2"></i><strong>No spaces</strong></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password_confirmation" class="form-control" id="profileConfirmPassword" required>
                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPw">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small id="profileMatchMsg" class="mt-1 d-block" style="font-size:12px"></small>
                    </div>
                    
                    <button type="submit" class="btn btn-password">Change Password</button>
                </form>

                <script>
                (function() {
                    // Toggle helpers
                    function togglePw(btnId, inputId) {
                        document.getElementById(btnId).addEventListener('click', function() {
                            const inp = document.getElementById(inputId);
                            const isText = inp.type === 'text';
                            inp.type = isText ? 'password' : 'text';
                            this.querySelector('i').className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
                        });
                    }
                    togglePw('toggleCurrentPw', 'currentPassword');
                    togglePw('toggleNewPw',     'profileNewPassword');
                    togglePw('toggleConfirmPw', 'profileConfirmPassword');

                    // Strength checker
                    const pwInput    = document.getElementById('profileNewPassword');
                    const bar        = document.getElementById('profileStrengthBar');
                    const lbl        = document.getElementById('profileStrengthLabel');
                    const barWrap    = document.getElementById('profileStrengthWrap');
                    const reqs       = document.getElementById('profilePwReqs');
                    const confirmInp = document.getElementById('profileConfirmPassword');
                    const matchMsg   = document.getElementById('profileMatchMsg');

                    function setReq(id, pass) {
                        const el   = document.getElementById(id);
                        const icon = el.querySelector('i');
                        icon.className = pass ? 'fas fa-check-circle text-success me-2' : 'fas fa-times-circle text-danger me-2';
                        el.style.color = pass ? '#198754' : '#dc3545';
                    }

                    pwInput.addEventListener('focus', function() {
                        reqs.style.display = 'block';
                        if (pwInput.value !== '') barWrap.style.display = 'block';
                    });

                    pwInput.addEventListener('input', function() {
                        const val = pwInput.value;
                        const okLength  = val.length >= 8 && val.length <= 20;
                        const okUpper   = /[A-Z]/.test(val);
                        const okNumber  = /[0-9]/.test(val);
                        const okSpecial = /[@$!%*?&]/.test(val);
                        const okNoSpace = val.length > 0 && !/\s/.test(val);

                        setReq('prof-req-length',  okLength);
                        setReq('prof-req-upper',   okUpper);
                        setReq('prof-req-number',  okNumber);
                        setReq('prof-req-special', okSpecial);
                        setReq('prof-req-nospace', okNoSpace);

                        if (val === '') {
                            barWrap.style.display = 'none';
                        } else {
                            barWrap.style.display = 'block';
                            const score  = [okLength, okUpper, okNumber, okSpecial, okNoSpace].filter(Boolean).length;
                            const levels = [
                                { pct: 20,  color: '#dc3545', text: 'Weak'   },
                                { pct: 40,  color: '#fd7e14', text: 'Weak'   },
                                { pct: 60,  color: '#ffc107', text: 'Fair'   },
                                { pct: 80,  color: '#0dcaf0', text: 'Medium' },
                                { pct: 100, color: '#198754', text: 'Strong' },
                            ];
                            const lvl = levels[score - 1] || { pct: 0, color: '#dee2e6', text: '' };
                            bar.style.width      = lvl.pct + '%';
                            bar.style.background = lvl.color;
                            lbl.textContent      = lvl.text;
                            lbl.style.color      = lvl.color;
                        }

                        checkMatch();
                    });

                    confirmInp.addEventListener('input', checkMatch);

                    function checkMatch() {
                        if (confirmInp.value === '') { matchMsg.textContent = ''; return; }
                        if (pwInput.value === confirmInp.value) {
                            matchMsg.textContent = '✓ Passwords match';
                            matchMsg.style.color = '#198754';
                        } else {
                            matchMsg.textContent = '✗ Passwords do not match';
                            matchMsg.style.color = '#dc3545';
                        }
                    }
                })();
                </script>
            </div>
        </div>
    </div>
</div>

<!-- Upload Picture Modal -->
<div class="modal fade" id="uploadPictureModal" tabindex="-1" aria-labelledby="uploadPictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadPictureModalLabel">Upload Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: File Selection -->
                <div id="file-selection-step">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Choose a profile picture</label>
                        <input type="file" class="form-control" id="profile_picture" accept="image/*">
                        <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</small>
                    </div>
                    <div class="text-center d-flex justify-content-between align-items-center">
                        <?php if($user->profile_picture): ?>
                            <button type="button" class="btn btn-danger btn-sm" id="remove-btn">
                                <i class="fas fa-trash"></i> Remove Current
                            </button>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        <button type="button" class="btn btn-primary" id="next-btn" disabled>Next</button>
                    </div>
                </div>

                <!-- Step 2: Cropping -->
                <div id="cropping-step" style="display: none;">
                    <div class="text-center mb-3">
                        <p>Adjust the image to fit your profile</p>
                    </div>
                    <div class="cropper-container">
                        <img id="cropper-image" src="" alt="Image to crop">
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-secondary me-2" id="back-btn">Back</button>
                        <button type="button" class="btn btn-primary" id="crop-btn">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/profile/index.blade.php ENDPATH**/ ?>