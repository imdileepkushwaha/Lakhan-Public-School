<?php
if (!defined('ADMIN_SESSION_TIMEOUT')) {
    require_once __DIR__ . '/session_guard.php';
}
?>
    <script>
        (function () {
            var timeoutMs = <?= ADMIN_SESSION_TIMEOUT * 1000 ?>;
            var timer;

            function logoutOnTimeout() {
                window.location.href = 'logout.php?expired=1';
            }

            function resetTimer() {
                clearTimeout(timer);
                timer = setTimeout(logoutOnTimeout, timeoutMs);
            }

            ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function (eventName) {
                document.addEventListener(eventName, resetTimer, { passive: true });
            });

            resetTimer();
        })();
    </script>
