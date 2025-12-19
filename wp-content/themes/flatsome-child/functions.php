<?php
/**
 * =====================================================
 * CLEAN ADMIN (GIỮ MENU) + MEDIA UX PRO (FULL FINAL)
 * =====================================================
 */


/* =====================================================
 * 1. ẨN MENU ADMIN – GIỮ NGUYÊN LOGIC
 * ===================================================== */
add_action('admin_menu', function () {

	$allowed_menus = [
		'upload.php',          // Media
		'plugins.php',         // Plugins
		'options-general.php', // Settings
		'themes.php',          // Appearance
		'ai1wm_export',        // All-in-One WP Migration
	];

    global $menu;
    foreach ($menu as $item) {
        if (!in_array($item[2], $allowed_menus)) {
            remove_menu_page($item[2]);
        }
    }

    // Appearance – chỉ giữ Theme Editor
    remove_submenu_page('themes.php', 'themes.php');
    remove_submenu_page('themes.php', 'customize.php');
    remove_submenu_page('themes.php', 'widgets.php');
    remove_submenu_page('themes.php', 'nav-menus.php');
    remove_submenu_page('themes.php', 'site-editor.php');

    // Settings – ẩn mục con
    remove_submenu_page('options-general.php', 'options-writing.php');
    remove_submenu_page('options-general.php', 'options-reading.php');
    remove_submenu_page('options-general.php', 'options-discussion.php');
    remove_submenu_page('options-general.php', 'options-media.php');
    remove_submenu_page('options-general.php', 'options-permalink.php');
    remove_submenu_page('options-general.php', 'options-privacy.php');

}, 999);

/* =====================================================
 * 5. MODAL ACTION BUTTON JS
 * ===================================================== */
add_action('admin_footer', function () {
?>
<script>
const mediaModalObserver = new MutationObserver(() => {

    const actions = document.querySelector('.attachment-details .actions');
    if (!actions || actions.querySelector('.media-action-bar')) return;

    const urlInput = document.querySelector('#attachment-details-two-column-copy-link');
    if (!urlInput) return;

    const url = urlInput.value;

    const bar = document.createElement('div');
    bar.className = 'media-action-bar';
    bar.innerHTML = `
        <button class="btn-view">👁 Xem</button>
        <button class="btn-copy">🔗 Sao chép</button>
        <button class="btn-download">⬇️ Tải về</button>
        <button class="btn-delete">🗑 Xóa</button>
    `;
    actions.appendChild(bar);

    bar.querySelector('.btn-view').onclick = () => window.open(url, '_blank');
    bar.querySelector('.btn-download').onclick = () => window.open(url, '_blank');

    bar.querySelector('.btn-copy').onclick = async (e) => {
        await navigator.clipboard.writeText(url);
        e.currentTarget.textContent = '✔ Đã sao chép';
        e.currentTarget.classList.add('is-done');
    };

    bar.querySelector('.btn-delete').onclick = () => {
        document.querySelector('.delete-attachment')?.click();
    };
});

mediaModalObserver.observe(document.body, { childList: true, subtree: true });
</script>
<?php
});

/* =====================================================
 * 6. ẨN TOÀN BỘ ADMIN NOTICE (TGMPA / PLUGIN / UPDATE)
 * ===================================================== */
add_action('admin_head', function () {
?>
<style>
/* WordPress core notices */
.notice,
.notice-warning,
.notice-error,
.notice-success,
.notice-info,
.update-nag,
.updated,
.error {
    display: none !important;
}

/* TGMPA (theme recommend plugin) */
#setting-error-tgmpa {
    display: none !important;
}

/* Plugin specific banners */
.plugin-update-tr,
.update-message,
.wp-adminify-notice,
.adminify-notice {
    display: none !important;
}
</style>
<?php
});

/* =====================================================
 * 7. POLISH UI BUTTON + ẨN TRIỆT ĐỂ THẺ <a>
 * ===================================================== */
add_action('admin_head', function () {
?>
<style>
/* =========================
   ẨN TOÀN BỘ LINK CŨ
========================= */
.attachment-details .actions a,
.attachment-details .actions a *,
.attachment-details .actions .links-separator,
.attachment-details .actions .button-link {
    display: none !important;
    visibility: hidden !important;
}

/* =========================
   BUTTON BAR
========================= */
.media-action-bar {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

/* Button base */
.media-action-bar button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    height: 44px;
    padding: 0 12px;

    font-size: 14px;
    font-weight: 600;
    letter-spacing: .2px;

    border-radius: 12px;
    border: 1px solid transparent;
    cursor: pointer;

    transition: all .15s ease;
}

/* Hover */
.media-action-bar button:hover {
    transform: translateY(-1px);
    filter: brightness(0.97);
}

/* Active */
.media-action-bar button:active {
    transform: translateY(0);
}

/* Disabled (copy done) */
.media-action-bar button.is-done {
    background: #dcfce7 !important;
    color: #166534 !important;
    border-color: #86efac;
    pointer-events: none;
}

/* =========================
   COLOR SYSTEM
========================= */
.btn-view {
    background: #eef2ff;
    color: #3730a3;
}
.btn-view:hover {
    background: #e0e7ff;
}

.btn-copy {
    background: #e0f2fe;
    color: #075985;
}
.btn-copy:hover {
    background: #bae6fd;
}

.btn-download {
    background: #dcfce7;
    color: #166534;
}
.btn-download:hover {
    background: #bbf7d0;
}

.btn-delete {
    background: #fff;
    color: #b91c1c;
    border-color: #fecaca;
}
.btn-delete:hover {
    background: #fee2e2;
}
</style>
<?php
});
