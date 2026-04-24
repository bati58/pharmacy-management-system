    </div> <!-- close flex-1 p-8 -->
</div> <!-- close main-content -->

    <script>
        // Load unread notification count for badge using the global API object
        async function updateNotificationBadge() {
            try {
                const res = await API.getNotifications(true);
                const count = res.data ? res.data.length : 0;
                const badges = [
                    document.getElementById('headerNotifCount'),
                    document.getElementById('sidebarNotifCount')
                ];
                
                badges.forEach(badge => {
                    if (badge) {
                        if (count > 0) {
                            badge.textContent = count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                });
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