(function () {
    const config = window.notificationConfig || {};
    const studentId = config.studentId || '';
    const announcementStorageKey = studentId ? `announcement_read_${studentId}` : 'announcement_read_guest';

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const overlay = document.getElementById('overlay');
        if (!sidebar || !mainContent || !overlay) return;
        if (window.innerWidth > 992) {
            sidebar.classList.toggle('closed');
            mainContent.classList.toggle('full-width');
        } else {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    }

    function postForm(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data || {})
        }).then(res => res.json());
    }

    function updateBadgeCounts(newCount) {
        const iconBadge = document.querySelector('.notification-badge');
        if (newCount > 0) {
            if (iconBadge) {
                iconBadge.textContent = newCount > 9 ? '9+' : newCount;
            } else {
                const icon = document.querySelector('.header-icon[title="Notifications"]');
                if (icon) {
                    const span = document.createElement('span');
                    span.className = 'notification-badge';
                    span.textContent = newCount > 9 ? '9+' : newCount;
                    icon.appendChild(span);
                }
            }
        } else if (iconBadge) {
            iconBadge.remove();
        }

        const headerChip = document.querySelector('.notification-header-count');
        if (headerChip) {
            if (newCount > 0) {
                headerChip.textContent = `${newCount} new`;
            } else {
                headerChip.remove();
            }
        }
    }

    /* ------------------ Announcements ------------------ */
    function loadReadAnnouncements() {
        try {
            const stored = localStorage.getItem(announcementStorageKey);
            return new Set(stored ? JSON.parse(stored) : []);
        } catch (err) {
            console.error('Error loading read announcements', err);
            return new Set();
        }
    }

    function saveReadAnnouncements(set) {
        try {
            localStorage.setItem(announcementStorageKey, JSON.stringify(Array.from(set)));
        } catch (err) {
            console.error('Error saving read announcements', err);
        }
    }

    function updateAnnouncementBadge(unreadCount) {
        const badge = document.querySelector('.header-icon[title="Messages"] .badge');
        if (unreadCount > 0) {
            if (badge) {
                badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
            } else {
                const icon = document.querySelector('.header-icon[title="Messages"]');
                if (icon) {
                    const span = document.createElement('span');
                    span.className = 'badge';
                    span.textContent = unreadCount > 9 ? '9+' : unreadCount;
                    icon.appendChild(span);
                }
            }
        } else if (badge) {
            badge.remove();
        }
    }

    function refreshAnnouncementState() {
        const items = document.querySelectorAll('.announcement-item');
        if (!items.length) {
            updateAnnouncementBadge(0);
            return;
        }

        const readSet = loadReadAnnouncements();
        let unread = 0;

        items.forEach(item => {
            const id = (item.dataset.id || '').toString();
            if (id && readSet.has(id)) {
                item.classList.remove('unread');
                item.classList.add('read');
            } else {
                item.classList.remove('read');
                item.classList.add('unread');
                unread += 1;
            }
        });

        updateAnnouncementBadge(unread);
    }

    function markAnnouncementRead(itemEl) {
        if (!itemEl) return;
        const id = (itemEl.dataset.id || '').toString();
        if (!id) return;

        const readSet = loadReadAnnouncements();
        const wasUnread = !readSet.has(id);
        if (wasUnread) {
            readSet.add(id);
            saveReadAnnouncements(readSet);
        }

        itemEl.classList.remove('unread');
        itemEl.classList.add('read');

        if (wasUnread) {
            const remainingUnread = document.querySelectorAll('.announcement-item.unread').length;
            updateAnnouncementBadge(remainingUnread);
        }
    }

    function showAnnouncementDetails(title, message, date, type) {
        const modal = document.getElementById('announcementDetailModal');
        if (!modal) return;
        const titleEl = document.getElementById('announcementDetailTitle');
        const messageEl = document.getElementById('announcementDetailMessage');
        const typeEl = document.getElementById('announcementDetailType');
        const dateEl = document.getElementById('announcementDetailDate');
        if (titleEl) titleEl.textContent = title || 'Announcement';
        if (messageEl) messageEl.textContent = message || '';
        if (typeEl) typeEl.textContent = type || 'Announcement';
        if (dateEl) dateEl.textContent = date ? `Published: ${date}` : '';
        modal.style.display = 'flex';
    }

    function closeAnnouncementDetail() {
        const modal = document.getElementById('announcementDetailModal');
        if (modal) modal.style.display = 'none';
    }

    function handleAnnouncementClick(itemEl) {
        if (!itemEl) return;
        markAnnouncementRead(itemEl);
        const title = itemEl.dataset.title || 'Announcement';
        const body = itemEl.dataset.body || '';
        const date = itemEl.dataset.date || '';
        const type = itemEl.dataset.type || 'Announcement';
        showAnnouncementDetails(title, body, date, type);
    }

    function toggleAnnouncementSidebar() {
        const sidebar = document.getElementById('announcementSidebar');
        const overlay = document.getElementById('announcementOverlay');
        if (!sidebar || !overlay) return;
        sidebar.classList.add('active');
        overlay.classList.add('active');
        refreshAnnouncementState();
    }

    function closeAnnouncementSidebar() {
        const sidebar = document.getElementById('announcementSidebar');
        const overlay = document.getElementById('announcementOverlay');
        if (sidebar) sidebar.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
    }

    function markSingleNotificationRead(id, itemEl) {
        if (!id || !itemEl || itemEl.classList.contains('read')) return;
        postForm('mark_notification_read.php', { notification_id: id })
            .then(data => {
                if (data.success) {
                    itemEl.classList.remove('unread');
                    itemEl.classList.add('read');
                    itemEl.style.borderLeftColor = '#cbd5e1';
                    itemEl.style.background = '#fff';
                    itemEl.style.opacity = '1';
                    if (typeof data.remaining !== 'undefined') {
                        updateBadgeCounts(data.remaining);
                    }
                }
            })
            .catch(err => console.error('Error marking notification read:', err));
    }

    function markNotificationsAsRead() {
        if (!document.querySelector('.notification-item.unread')) return;
        postForm('mark_notification_read.php', {})
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        item.classList.add('read');
                        item.style.borderLeftColor = '#cbd5e1';
                        item.style.background = '#fff';
                        item.style.opacity = '1';
                    });
                    const remaining = typeof data.remaining !== 'undefined' ? data.remaining : 0;
                    updateBadgeCounts(remaining);
                }
            })
            .catch(err => console.error('Error marking all read:', err));
    }

    function clearAllNotifications() {
        if (!confirm('Are you sure you want to clear all notifications?')) return;
        postForm('clear_notifications.php', {})
            .then(data => {
                if (data.success) {
                    const notificationList = document.getElementById('notificationList');
                    if (notificationList) {
                        notificationList.innerHTML = `
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <h4>No notifications</h4>
                                <p>You're all caught up!</p>
                            </div>
                        `;
                    }
                    const actionsSection = document.querySelector('.notification-actions');
                    if (actionsSection) actionsSection.remove();
                    const headerBadge = document.querySelector('.notification-header-count');
                    if (headerBadge) headerBadge.remove();
                    const badges = document.querySelectorAll('.notification-badge');
                    badges.forEach(badge => badge.remove());
                }
            })
            .catch(err => console.error('Error clearing notifications:', err));
    }

    function showNotificationDetails(title, message, type, date) {
        const modal = document.getElementById('notificationDetailModal');
        if (!modal) return;
        const titleEl = document.getElementById('detailTitle');
        const messageEl = document.getElementById('detailMessage');
        const typeEl = document.getElementById('detailType');
        const dateEl = document.getElementById('detailDate');
        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.textContent = message;
        if (typeEl) typeEl.textContent = type;
        if (dateEl) dateEl.textContent = 'Received: ' + date;
        modal.style.display = 'flex';
    }

    function closeDetailModal() {
        const modal = document.getElementById('notificationDetailModal');
        if (modal) modal.style.display = 'none';
    }

    function toggleNotificationSidebar() {
        const notifSidebar = document.getElementById('notificationSidebar');
        const notifOverlay = document.getElementById('notificationOverlay');
        if (!notifSidebar || !notifOverlay) return;
        const isActive = notifSidebar.classList.contains('active');
        if (!isActive) {
            notifSidebar.classList.add('active');
            notifOverlay.classList.add('active');
        } else {
            closeNotificationSidebar();
        }
    }

    function closeNotificationSidebar() {
        const notifSidebar = document.getElementById('notificationSidebar');
        const notifOverlay = document.getElementById('notificationOverlay');
        if (notifSidebar) notifSidebar.classList.remove('active');
        if (notifOverlay) notifOverlay.classList.remove('active');
    }

    function handleNotificationClick(id, el, title, message, type, date) {
        markSingleNotificationRead(id, el);
        showNotificationDetails(title, message, type, date);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeNotificationSidebar();
            closeDetailModal();
            closeAnnouncementSidebar();
            closeAnnouncementDetail();
        }
    });

    window.addEventListener('click', function (event) {
        const detailModal = document.getElementById('notificationDetailModal');
        if (event.target === detailModal) {
            closeDetailModal();
        }
        const notifOverlay = document.getElementById('notificationOverlay');
        if (event.target === notifOverlay) {
            closeNotificationSidebar();
        }

        const announcementModal = document.getElementById('announcementDetailModal');
        if (event.target === announcementModal) {
            closeAnnouncementDetail();
        }

        const announcementOverlay = document.getElementById('announcementOverlay');
        if (event.target === announcementOverlay) {
            closeAnnouncementSidebar();
        }
    });

    refreshAnnouncementState();

    window.toggleNotificationSidebar = toggleNotificationSidebar;
    window.closeNotificationSidebar = closeNotificationSidebar;
    window.markNotificationsAsRead = markNotificationsAsRead;
    window.clearAllNotifications = clearAllNotifications;
    window.showNotificationDetails = showNotificationDetails;
    window.closeDetailModal = closeDetailModal;
    window.handleNotificationClick = handleNotificationClick;
    window.toggleSidebar = toggleSidebar;
    window.toggleAnnouncementSidebar = toggleAnnouncementSidebar;
    window.closeAnnouncementSidebar = closeAnnouncementSidebar;
    window.handleAnnouncementClick = handleAnnouncementClick;
    window.closeAnnouncementDetail = closeAnnouncementDetail;
})();
