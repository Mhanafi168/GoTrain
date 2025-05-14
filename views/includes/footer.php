    </div>
    
    <script src="../public/assets/js/jquery-3.7.1.min.js"></script>
    <script src="../public/assets/js/popper.min.js"></script>
    <script src="../public/assets/js/bootstrap.js"></script>
    <script src="../public/js/dashboard.js"></script>
    <?php if (isset($additional_scripts)): ?>
        <?= $additional_scripts ?>
    <?php endif; ?>
    <script>
        $('.sidebar-toggle').click(function() {
            $('.sidebar').toggleClass('active');
        });

        $(document).click(function(e) {
            if ($(window).width() < 992) {
                if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('.sidebar-toggle').length) {
                    $('.sidebar').removeClass('active');
                }
            }
        });

        $('.sidebar').click(function(e) {
            e.stopPropagation();
        });

        $(document).ready(function() {
            var currentPath = window.location.pathname;
            $('.nav-link').each(function() {
                if ($(this).attr('href') && currentPath.includes($(this).attr('href'))) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html> 