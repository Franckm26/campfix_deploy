<?php if($paginator->hasPages()): ?>
<nav style="display:flex;align-items:center;justify-content:space-between;margin-top:16px">
    <div style="font-size:12px;color:var(--sa-muted)">
        Showing <?php echo e($paginator->firstItem()); ?>–<?php echo e($paginator->lastItem()); ?> of <?php echo e($paginator->total()); ?>

    </div>
    <div style="display:flex;gap:4px">
        
        <?php if($paginator->onFirstPage()): ?>
            <span style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.04);color:var(--sa-muted);cursor:not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>" style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.06);color:var(--sa-text);text-decoration:none;border:1px solid var(--sa-border)" onmouseover="this.style.background='var(--sa-hover)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>

        
        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <span style="padding:5px 10px;border-radius:6px;font-size:12px;color:var(--sa-muted)">…</span>
            <?php endif; ?>
            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span style="padding:5px 10px;border-radius:6px;font-size:12px;background:var(--sa-accent);color:#fff;font-weight:600"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <a href="<?php echo e($url); ?>" style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.06);color:var(--sa-text);text-decoration:none;border:1px solid var(--sa-border)" onmouseover="this.style.background='var(--sa-hover)'" onmouseout="this.style.background='rgba(255,255,255,.06)'"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>" style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.06);color:var(--sa-text);text-decoration:none;border:1px solid var(--sa-border)" onmouseover="this.style.background='var(--sa-hover)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span style="padding:5px 10px;border-radius:6px;font-size:12px;background:rgba(255,255,255,.04);color:var(--sa-muted);cursor:not-allowed">
                <i class="fas fa-chevron-right"></i>
            </span>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/vendor/pagination/superadmin.blade.php ENDPATH**/ ?>