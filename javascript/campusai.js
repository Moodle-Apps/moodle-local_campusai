(function() {
    'use strict';

    var STRINGS = window.campusaiStrings || {};
    var currentConfig = null;

    var root = null;
    var panel = null;
    var conversation = null;
    var input = null;
    var helpPanel = null;
    var isOpen = false;

    /**
     * Translates a string key.
     *
     * @param {string} key
     * @param {string} fallback
     * @return {string}
     */
    function t(key, fallback) {
        return STRINGS[key] || fallback || key;
    }

    /**
     * Decodes HTML entities.
     *
     * @param {string} html
     * @return {string}
     */
    function decodeHtml(html) {
        var textarea = document.createElement('textarea');
        textarea.innerHTML = html;
        return textarea.value;
    }

    /**
     * Boots the widget if enabled.
     */
    function boot() {
        var injected = window.campusaiConfig || {};

        if (injected.enabled) {
            mount(injected);
            return;
        }

        var ajaxUrl = injected.ajaxUrl || '/local/campusai/ajax.php';
        var initUrl = ajaxUrl.replace(/ajax\.php$/, 'init.php');
        fetch(initUrl)
            .then(function(response) {
                return response.json();
            })
            .then(function(config) {
                if (config.enabled) {
                    mount(config);
                }
            })
            .catch(function() {
                // Silently ignore fetch errors.
            });
    }

    /**
     * Returns the quick action chips for the current user role.
     *
     * @param {Object} config
     * @return {Array}
     */
    function getQuickActions(config) {
        var role = config.userRole || 'student';

        if (role === 'admin') {
            return [
                { label: 'quick_campus_stats_label', text: 'quick_campus_stats_text' },
                { label: 'quick_course_list_admin_label', text: 'quick_course_list_admin_text' },
                { label: 'quick_inactive_users_label', text: 'quick_inactive_users_text' },
            ];
        }

        if (role === 'teacher') {
            return [
                { label: 'quick_teaching_courses_label', text: 'quick_teaching_courses_text' },
                { label: 'quick_overdue_label', text: 'quick_overdue_text' },
                { label: 'quick_needing_grading_label', text: 'quick_needing_grading_text' },
            ];
        }

        return [
            { label: 'quick_courses_label', text: 'quick_courses_text' },
            { label: 'quick_exams_label', text: 'quick_exams_text' },
            { label: 'quick_tasks_label', text: 'quick_tasks_text' },
        ];
    }

    /**
     * Mounts the widget into the page.
     *
     * @param {Object} config
     */
    function mount(config) {
        if (document.getElementById('campusai-root')) {
            return;
        }

        currentConfig = config;

        root = document.createElement('div');
        root.id = 'campusai-root';
        root.setAttribute('data-position', config.position || 'bottom-right');

        var fab = document.createElement('button');
        fab.id = 'campusai-fab';
        fab.setAttribute('aria-label', config.title || t('widget_title_fallback', 'Campus Assistant'));
        fab.style.backgroundColor = config.color || '#0066CC';

        if (config.iconUrl) {
            var img = document.createElement('img');
            img.src = config.iconUrl;
            img.alt = '';
            fab.appendChild(img);
        } else {
            fab.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
        }

        fab.addEventListener('click', togglePanel);

        panel = document.createElement('div');
        panel.id = 'campusai-panel';
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-label', config.title || t('widget_title_fallback', 'Campus Assistant'));
        panel.hidden = true;

        var header = document.createElement('div');
        header.id = 'campusai-panel-header';

        var titleWrapper = document.createElement('div');
        var title = document.createElement('h3');
        title.textContent = (config.title || t('widget_title_fallback', 'Campus Assistant')) + (config.isAdmin ? t('widget_admin_suffix', ' (admin)') : '');
        var subtitle = document.createElement('span');
        subtitle.textContent = t('widget_online', 'Online');
        titleWrapper.appendChild(title);
        titleWrapper.appendChild(subtitle);

        var helpBtn = document.createElement('button');
        helpBtn.id = 'campusai-panel-help';
        helpBtn.type = 'button';
        helpBtn.setAttribute('aria-label', t('widget_help_title', 'What can I ask?'));
        helpBtn.textContent = t('widget_help', '?');
        helpBtn.addEventListener('click', toggleHelp);

        var closeBtn = document.createElement('button');
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

        var welcome = document.createElement('div');
        welcome.className = 'campusai-msg campusai-msg--assistant';
        welcome.textContent = config.welcome || t('default_welcome', 'Hello, how can I help you today?');
        conversation.appendChild(welcome);

        var chips = document.createElement('div');
        chips.className = 'campusai-quick-chips';

        getQuickActions(config).forEach(function(action) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = t(action.label, action.label);
            btn.addEventListener('click', function() {
                send(t(action.text, action.text));
            });
            chips.appendChild(btn);
        });

        var helpChip = document.createElement('button');
        helpChip.type = 'button';
        helpChip.textContent = t('quick_help_label', 'Help');
        helpChip.addEventListener('click', toggleHelp);
        chips.appendChild(helpChip);

        var inputRow = document.createElement('div');
        inputRow.className = 'campusai-input-row';

        input = document.createElement('input');
        input.type = 'text';
        input.placeholder = t('placeholder', 'Type your question...');
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                send(input.value);
            }
        });

        var sendBtn = document.createElement('button');
        sendBtn.type = 'button';
        sendBtn.setAttribute('aria-label', t('widget_send', 'Send'));
        sendBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>';
        sendBtn.addEventListener('click', function() {
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
    }

    /**
     * Builds the help panel with example questions.
     *
     * @param {Object} config
     * @return {HTMLElement}
     */
    function buildHelpPanel(config) {
        var container = document.createElement('div');
        container.id = 'campusai-help-panel';
        container.hidden = true;

        var title = document.createElement('h4');
        title.textContent = t('widget_help_title', 'What can I ask?');
        container.appendChild(title);

        var items = config.examples || [];
        if (items.length) {
            var list = document.createElement('ul');
            items.forEach(function(question) {
                var li = document.createElement('li');
                var link = document.createElement('button');
                link.type = 'button';
                link.textContent = question;
                link.addEventListener('click', function() {
                    send(question);
                    toggleHelp();
                });
                li.appendChild(link);
                list.appendChild(li);
            });
            container.appendChild(list);
        }

        var backBtn = document.createElement('button');
        backBtn.type = 'button';
        backBtn.className = 'campusai-help-back';
        backBtn.textContent = t('widget_close', 'Close');
        backBtn.addEventListener('click', toggleHelp);
        container.appendChild(backBtn);

        return container;
    }

    /**
     * Toggles the chat panel.
     */
    function togglePanel() {
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
    }

    /**
     * Toggles the help panel.
     */
    function toggleHelp() {
        if (!helpPanel || !panel) {
            return;
        }
        var visible = !helpPanel.hidden;
        helpPanel.hidden = visible;
        panel.hidden = !visible;
        isOpen = visible;
    }

    /**
     * Renders a message in the conversation.
     *
     * @param {string} role 'user' or 'assistant'.
     * @param {string} text
     */
    function renderMessage(role, text) {
        var msg = document.createElement('div');
        msg.className = 'campusai-msg campusai-msg--' + role;
        msg.textContent = decodeHtml(text);
        conversation.appendChild(msg);
        conversation.scrollTop = conversation.scrollHeight;
    }

    /**
     * Sends a user message.
     *
     * @param {string} text
     */
    function send(text) {
        text = (text || '').trim();
        if (!text) {
            return;
        }

        renderMessage('user', text);
        if (input) {
            input.value = '';
        }

        var formData = new FormData();
        formData.append('message', text);
        formData.append('sesskey', (currentConfig && currentConfig.sesskey) || '');

        fetch((currentConfig && currentConfig.ajaxUrl) || '/local/campusai/ajax.php', {
            method: 'POST',
            body: formData,
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.warnings && data.warnings.length) {
                var warning = data.warnings[0];
                if (warning.warningcode === 'error_ratelimit') {
                    renderMessage('assistant', t('error_ratelimit', 'You have sent too many messages. Please wait a moment.'));
                    return;
                }
            }
            renderMessage('assistant', data.reply || t('error_generic', 'Sorry, something went wrong. Please try again later.'));
        })
        .catch(function() {
            renderMessage('assistant', t('error_generic', 'Sorry, something went wrong. Please try again later.'));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}());
