document.addEventListener('DOMContentLoaded', function() {
    const notificationCount = document.getElementById('notification-count');
    const notificationList = document.getElementById('notification-list');

    const fetchNotifications = async () => {
        // Only run if the notification dropdown exists (i.e., for logged-in parents)
        if (!notificationList) return;

        try {
            const response = await fetch(`${urlRoot}/notifications/api_get_unread`);
            const notifications = await response.json();

            // Update count
            if (notifications.length > 0) {
                notificationCount.innerText = notifications.length;
                notificationCount.style.display = 'inline';
            } else {
                notificationCount.style.display = 'none';
            }

            // Clear list
            notificationList.innerHTML = '';

            // Populate list
            if (notifications.length > 0) {
                notifications.forEach(notification => {
                    const item = document.createElement('a');
                    item.classList.add('dropdown-item');
                    item.href = '#'; // Or a link to the relevant page
                    item.dataset.id = notification.id;
                    item.innerText = notification.message;
                    item.addEventListener('click', markAsRead);
                    notificationList.appendChild(item);
                });
            } else {
                const item = document.createElement('span');
                item.classList.add('dropdown-item', 'text-muted');
                item.innerText = 'No new notifications';
                notificationList.appendChild(item);
            }

        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    };

    const markAsRead = async (e) => {
        e.preventDefault();
        const notificationId = e.target.dataset.id;

        try {
            await fetch(`${urlRoot}/notifications/api_mark_as_read/${notificationId}`);
            // Optimistically remove from UI
            e.target.remove();
            const currentCount = parseInt(notificationCount.innerText) - 1;
            if (currentCount > 0) {
                notificationCount.innerText = currentCount;
            } else {
                notificationCount.style.display = 'none';
                const item = document.createElement('span');
                item.classList.add('dropdown-item', 'text-muted');
                item.innerText = 'No new notifications';
                notificationList.appendChild(item);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    // Initial fetch and interval polling
    fetchNotifications();
    setInterval(fetchNotifications, 15000); // Poll every 15 seconds
});