// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Chat widget for the Campus Assistant.
 *
 * The configuration and the UI strings are injected by the plugin into
 * window.campusaiConfig and window.campusaiStrings (see hook_callbacks),
 * so no payload needs to be passed through js_call_amd.
 *
 * @module     local_campusai/campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const STRINGS = window.campusaiStrings || {};

let currentConfig = null;
let root = null;
let panel = null;
let conversation = null;
let input = null;
let helpPanel = null;
let isOpen = false;

/**
 * Translates a string key.
 *
 * @param {string} key
 * @param {string} fallback
 * @returns {string}
 */
const t = (key, fallback) => STRINGS[key] || fallback || key;

/**
 * Decodes HTML entities.
 *
 * @param {string} html
 * @returns {string}
 */
const decodeHtml = (html) => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = html;
    return textarea.value;
};

/**
 * Boots the widget if enabled.
 */
const boot = () => {
    const injected = window.campusaiConfig || {};

    if (injected.enabled) {
        mount(injected);
        return;
    }

    const ajaxUrl = injected.ajaxUrl || '/local/campusai/ajax.php';
    const initUrl = ajaxUrl.replace(/ajax\.php$/, 'init.php');
    fetch(initUrl)
        .then((response) => response.json())
        .then((config) => {
            if (config.enabled) {
                mount(config);
            }
            return null;
        })
        .catch(() => {
            // Silently ignore fetch errors.
        });
};

/**
 * Returns the quick action chips for the current user role.
 *
 * @param {Object} config
 * @returns {Array}
 */
const getQuickActions = (config) => {
    const role = config.userRole || 'student';

    if (role === 'admin') {
        return [
            {label: 'quick_campus_stats_label', text: 'quick_campus_stats_text'},
            {label: 'quick_course_list_admin_label', text: 'quick_course_list_admin_text'},
            {label: 'quick_inactive_users_label', text: 'quick_inactive_users_text'},
        ];
    }

    if (role === 'teacher') {
        return [
            {label: 'quick_teaching_courses_label', text: 'quick_teaching_courses_text'},
            {label: 'quick_overdue_label', text: 'quick_overdue_text'},
            {label: 'quick_needing_grading_label', text: 'quick_needing_grading_text'},
        ];
    }

    return [
        {label: 'quick_courses_label', text: 'quick_courses_text'},
        {label: 'quick_exams_label', text: 'quick_exams_text'},
        {label: 'quick_tasks_label', text: 'quick_tasks_text'},
    ];
};

/**
 * Mounts the widget into the page.
 *
 * @param {Object} config
 */
const mount = (config) => {
    if (document.getElementById('campusai-root')) {
        return;
    }

    currentConfig = config;

    root = document.createElement('div');
    root.id = 'campusai-root';
    root.setAttribute('data-position', config.position || 'bottom-right');

    const fab = document.createElement('button');
    fab.id = 'campusai-fab';
    fab.setAttribute('aria-label', config.title || t('widget_title_fallback', 'Campus Assistant'));
    fab.style.backgroundColor = config.color || '#0066CC';

    if (config.iconUrl) {
        const img = document.createElement('img');
        img.src = config.iconUrl;
        img.alt = '';
        fab.appendChild(img);
    } else {
        fab.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"' +
            ' stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
    }

    fab.addEventListener('click', togglePanel);

    panel = document.createElement('div');
    panel.id = 'campusai-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', config.title || t('widget_title_fallback', 'Campus Assistant'));
    panel.hidden = true;

    const header = document.createElement('div');
    header.id = 'campusai-panel-header';

    const titleWrapper = document.createElement('div');
    const title = document.createElement('h3');
    title.textContent = (config.title || t('widget_title_fallback', 'Campus Assistant')) +
        (config.isAdmin ? t('widget_admin_suffix', ' (admin)') : '');
    const subtitle = document.createElement('span');
    subtitle.textContent = t('widget_online', 'Online');
    titleWrapper.appendChild(title);
    titleWrapper.appendChild(subtitle);

    const helpBtn = document.createElement('button');
    helpBtn.id = 'campusai-panel-help';
    helpBtn.type = 'button';
    helpBtn.setAttribute('aria-label', t('widget_help_title', 'What can I ask?'));
    helpBtn.textContent = t('widget_help', '?');
    helpBtn.addEventListener('click', toggleHelp);

    const closeBtn = document.createElement('button');
    closeBtn.id = 'campusai-panel-close';
    closeBtn.type = 'button';
    closeBtn.setAttribute('aria-label', t('widget_close', 'Close'));
    closeBtn.textContent = '×';
    closeBtn.addEventListener('click', togglePanel);

    header.appendChild(titleWrapper);
    header.appendChild(helpBtn);
    header.appendChild(closeBtn);

    conversation = document.createElement('div');
    conversation.className = 'campusai-conversation';

    const welcome = document.createElement('div');
    welcome.className = 'campusai-msg campusai-msg--assistant';
    welcome.textContent = config.welcome || t('default_welcome', 'Hello, how can I help you today?');
    conversation.appendChild(welcome);

    const chips = document.createElement('div');
    chips.className = 'campusai-quick-chips';

    getQuickActions(config).forEach((action) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = t(action.label, action.label);
        btn.addEventListener('click', () => {
            send(t(action.text, action.text));
        });
        chips.appendChild(btn);
    });

    const helpChip = document.createElement('button');
    helpChip.type = 'button';
    helpChip.textContent = t('quick_help_label', 'Help');
    helpChip.addEventListener('click', toggleHelp);
    chips.appendChild(helpChip);

    const inputRow = document.createElement('div');
    inputRow.className = 'campusai-input-row';

    input = document.createElement('input');
    input.type = 'text';
    input.placeholder = t('placeholder', 'Type your question...');
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            send(input.value);
        }
    });

    const sendBtn = document.createElement('button');
    sendBtn.type = 'button';
    sendBtn.setAttribute('aria-label', t('widget_send', 'Send'));
    sendBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"' +
        ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line>' +
        '<polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>';
    sendBtn.addEventListener('click', () => {
        send(input.value);
    });

    inputRow.appendChild(input);
    inputRow.appendChild(sendBtn);

    helpPanel = buildHelpPanel(config);

    panel.appendChild(header);
    panel.appendChild(conversation);
    panel.appendChild(chips);
    panel.appendChild(inputRow);

    root.appendChild(helpPanel);
    root.appendChild(panel);
    root.appendChild(fab);

    document.body.appendChild(root);
};

/**
 * Builds the help panel with example questions.
 *
 * @param {Object} config
 * @returns {HTMLElement}
 */
const buildHelpPanel = (config) => {
    const container = document.createElement('div');
    container.id = 'campusai-help-panel';
    container.hidden = true;

    const title = document.createElement('h4');
    title.textContent = t('widget_help_title', 'What can I ask?');
    container.appendChild(title);

    const items = config.examples || [];
    if (items.length) {
        const list = document.createElement('ul');
        items.forEach((question) => {
            const li = document.createElement('li');
            const link = document.createElement('button');
            link.type = 'button';
            link.textContent = question;
            link.addEventListener('click', () => {
                send(question);
                toggleHelp();
            });
            li.appendChild(link);
            list.appendChild(li);
        });
        container.appendChild(list);
    }

    const backBtn = document.createElement('button');
    backBtn.type = 'button';
    backBtn.className = 'campusai-help-back';
    backBtn.textContent = t('widget_close', 'Close');
    backBtn.addEventListener('click', toggleHelp);
    container.appendChild(backBtn);

    return container;
};

/**
 * Toggles the chat panel.
 */
const togglePanel = () => {
    if (!panel) {
        return;
    }
    isOpen = !isOpen;
    panel.hidden = !isOpen;
    if (helpPanel) {
        helpPanel.hidden = true;
    }
    if (isOpen && input) {
        input.focus();
    }
};

/**
 * Toggles the help panel.
 */
const toggleHelp = () => {
    if (!helpPanel || !panel) {
        return;
    }
    const visible = !helpPanel.hidden;
    helpPanel.hidden = visible;
    panel.hidden = !visible;
    isOpen = visible;
};

/**
 * Renders a message in the conversation.
 *
 * @param {string} role 'user' or 'assistant'.
 * @param {string} text
 */
const renderMessage = (role, text) => {
    const msg = document.createElement('div');
    msg.className = 'campusai-msg campusai-msg--' + role;
    msg.textContent = decodeHtml(text);
    conversation.appendChild(msg);
    conversation.scrollTop = conversation.scrollHeight;
};

/**
 * Sends a user message.
 *
 * @param {string} text
 */
const send = (text) => {
    text = (text || '').trim();
    if (!text) {
        return;
    }

    renderMessage('user', text);
    if (input) {
        input.value = '';
    }

    const formData = new FormData();
    formData.append('message', text);
    formData.append('sesskey', (currentConfig && currentConfig.sesskey) || '');

    fetch((currentConfig && currentConfig.ajaxUrl) || '/local/campusai/ajax.php', {
        method: 'POST',
        body: formData,
    })
    .then((response) => response.json())
    .then((data) => {
        if (data.warnings && data.warnings.length) {
            const warning = data.warnings[0];
            if (warning.warningcode === 'error_ratelimit') {
                renderMessage('assistant',
                    t('error_ratelimit', 'You have sent too many messages. Please wait a moment.'));
                return null;
            }
        }
        renderMessage('assistant',
            data.reply || t('error_generic', 'Sorry, something went wrong. Please try again later.'));
        return null;
    })
    .catch(() => {
        renderMessage('assistant', t('error_generic', 'Sorry, something went wrong. Please try again later.'));
    });
};

/**
 * Initialises the widget once the DOM is ready.
 */
export const init = () => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
};
