<script>
    // 通过 Laravel Mix 使用 Vue 组件时，需要定义的csrf值
    window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
    ]); ?>

</script>