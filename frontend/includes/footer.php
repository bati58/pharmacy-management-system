    </div> <!-- close .main-content -->

    <script>
        // Load unread notification count for badge using the global API object
        async function updateNotificationBadge() {
            try {
                const res = await API.getNotifications(true);
                const count = res.data ? res.data.length : 0;
                
                const updateBadge = (id) => {
                    const badge = document.getElementById(id);
                    if (badge) {
                        if (count > 0) {
                            badge.textContent = count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                };

                updateBadge('notifCount');
                updateBadge('navNotifBadge');
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