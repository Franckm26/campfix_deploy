<style>
<?php
$adminCssPath = public_path('css/admin.css');
if (file_exists($adminCssPath)) {
    echo file_get_contents($adminCssPath);
}
?>
</style>
