    </div> <!-- close flex-1 p-8 -->
</div> <!-- close main-content -->

    <script>
        // Load unread notification count for badge using the global API object
        async function updateNotificationBadge() {
            try {
                const res = await API.getNotifications(true);
                const count = res.data ? res.data.length : 0;
                const badge = document.getElementById('headerNotifCount');
                if (badge) {
                    badge.textContent = count;
                    badge.classList.toggle('hidden', count === 0);
                }
            } catch (e) {
                console.error('Failed to update notification badge:', e);
            }
        }

        if (typeof API !== 'undefined') {
            updateNotificationBadge();
            setInterval(updateNotificationBadge, 30000);
        }
    </script>
    </body>

    </html>