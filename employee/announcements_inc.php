<?php
// Shared announcements utilities for employee pages
if (!function_exists('employee_load_announcements')) {
    function employee_load_announcements($conn)
    {
        $count = 0;
        $list = [];
        if ($conn) {
            if ($res = $conn->query("SELECT COUNT(*) AS count FROM announcements")) {
                if ($row = $res->fetch_assoc()) {
                    $count = (int)($row['count'] ?? 0);
                }
                $res->free();
            }
            if ($res = $conn->query("SELECT id, title, description, created_at FROM announcements ORDER BY created_at DESC LIMIT 10")) {
                while ($row = $res->fetch_assoc()) {
                    $list[] = $row;
                }
                $res->free();
            }
        }
        return [$count, $list];
    }
}

if (!function_exists('employee_render_announcement_icon')) {
    function employee_render_announcement_icon($announcement_count)
    {
        ?>
        <div class="header-icon" title="Messages" onclick="toggleAnnouncementSidebar()" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center; background:#f1f5f9; border-radius:50%; cursor:pointer; position:relative; color:var(--primary-color);">
            <i class="fa-regular fa-message"></i>
            <?php if ($announcement_count > 0): ?>
                <span class="badge" style="position:absolute; top:2px; right:2px; background:var(--secondary-color); color:white; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; border:2px solid #fff;">
                    <?php echo $announcement_count > 9 ? '9+' : $announcement_count; ?>
                </span>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('employee_render_announcement_drawer')) {
    function employee_render_announcement_drawer($announcements)
    {
        ?>
        <div class="announcement-overlay" id="announcementOverlay" onclick="closeAnnouncementSidebar()"></div>
        <div class="announcement-sidebar" id="announcementSidebar">
            <div class="announcement-header">
                <h3><i class="fa-regular fa-message"></i> Announcements</h3>
                <button class="announcement-close" onclick="closeAnnouncementSidebar()">&times;</button>
            </div>
            <div class="announcement-content">
                <?php if (empty($announcements)): ?>
                    <div class="announcement-empty">
                        <i class="fa-regular fa-circle-check" style="font-size: 32px; display:block; margin-bottom:8px;"></i>
                        No announcements yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $ann): ?>
                        <div 
                            class="announcement-item unread"
                            data-id="<?php echo (int)($ann['id'] ?? 0); ?>"
                            data-title="<?php echo htmlspecialchars($ann['title'], ENT_QUOTES); ?>"
                            data-body="<?php echo htmlspecialchars($ann['description'], ENT_QUOTES); ?>"
                            data-date="<?php echo date('M d, Y h:i A', strtotime($ann['created_at'])); ?>"
                            data-type="Announcement"
                            onclick="handleAnnouncementClick(this)"
                        >
                            <div class="announcement-title">
                                <span><?php echo htmlspecialchars($ann['title']); ?></span>
                                <span class="announcement-time" style="color: var(--text-gray); font-size: 12px; font-weight: 600;">
                                    <?php echo date('h:i A', strtotime($ann['created_at'])); ?>
                                </span>
                            </div>
                            <div class="announcement-body"><?php echo nl2br(htmlspecialchars($ann['description'])); ?></div>
                            <div class="announcement-meta">
                                <i class="fa-regular fa-clock"></i>
                                <span><?php echo date('M d, Y h:i A', strtotime($ann['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="announcementDetailModal" class="announcement-detail-modal">
            <div class="announcement-detail-content">
                <button class="modal-close" onclick="closeAnnouncementDetail()">&times;</button>
                <h3 class="modal-title" id="announcementDetailTitle">Announcement</h3>
                <div class="announcement-detail-body">
                    <div class="announcement-detail-message" id="announcementDetailMessage"></div>
                    <div class="announcement-detail-info">
                        <span class="announcement-detail-type" id="announcementDetailType">Announcement</span>
                        <span class="announcement-detail-date" id="announcementDetailDate"></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('employee_render_announcement_scripts')) {
    function employee_render_announcement_scripts($employee_id)
    {
        ?>
        <script>
            (function() {
                const announcementStorageKey = `announcement_read_employee_${<?php echo json_encode($employee_id); ?>}`;

                function loadReadAnnouncements() {
                    try {
                        const stored = localStorage.getItem(announcementStorageKey);
                        return new Set(stored ? JSON.parse(stored) : []);
                    } catch (e) {
                        return new Set();
                    }
                }

                function saveReadAnnouncements(set) {
                    try {
                        localStorage.setItem(announcementStorageKey, JSON.stringify(Array.from(set)));
                    } catch (e) {
                        /* ignore */
                    }
                }

                function updateAnnouncementBadge(unreadCount) {
                    const badge = document.querySelector('.header-icon[title="Messages"] .badge');
                    if (unreadCount > 0) {
                        if (badge) {
                            badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }

                function refreshAnnouncementState() {
                    const items = document.querySelectorAll('.announcement-item');
                    if (!items.length) { updateAnnouncementBadge(0); return; }

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
                        const remaining = document.querySelectorAll('.announcement-item.unread').length;
                        updateAnnouncementBadge(remaining);
                    }
                }

                window.handleAnnouncementClick = function(el) {
                    if (!el) return;
                    markAnnouncementRead(el);
                    const title = el.dataset.title || 'Announcement';
                    const body = el.dataset.body || '';
                    const date = el.dataset.date || '';
                    const type = el.dataset.type || 'Announcement';
                    showAnnouncementDetails(title, body, date, type);
                };

                window.toggleAnnouncementSidebar = function() {
                    const sidebar = document.getElementById('announcementSidebar');
                    const overlay = document.getElementById('announcementOverlay');
                    if (!sidebar || !overlay) return;
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                    refreshAnnouncementState();
                };

                window.closeAnnouncementSidebar = function() {
                    const sidebar = document.getElementById('announcementSidebar');
                    const overlay = document.getElementById('announcementOverlay');
                    if (sidebar) sidebar.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                };

                window.showAnnouncementDetails = function(title, message, date, type) {
                    const modal = document.getElementById('announcementDetailModal');
                    if (!modal) return;
                    document.getElementById('announcementDetailTitle').textContent = title || 'Announcement';
                    document.getElementById('announcementDetailMessage').textContent = message || '';
                    document.getElementById('announcementDetailType').textContent = type || 'Announcement';
                    document.getElementById('announcementDetailDate').textContent = date ? `Published: ${date}` : '';
                    modal.style.display = 'flex';
                };

                window.closeAnnouncementDetail = function() {
                    const modal = document.getElementById('announcementDetailModal');
                    if (modal) modal.style.display = 'none';
                };

                document.addEventListener('DOMContentLoaded', refreshAnnouncementState);
            })();
        </script>
        <?php
    }
}
