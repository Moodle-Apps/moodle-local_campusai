(function() {
    var config = null;
    var conversationOpen = false;
    var isLoading = false;
    var tr = null;
    var isAdmin = false;
    var isTeacher = false;
    var userRole = 'student';
    var helpOpen = false;

    var langs = {
        es: {
            welcome:'¡Hola! ¿En qué puedo ayudarte?', placeholder:'Escribe tu pregunta...',
            exams:'📅 ¿Qué exámenes tengo?', tasks:'📝 ¿Qué me falta entregar?', courses:'📚 Mis cursos',
            err:'Lo siento, no he podido procesar tu solicitud.',
            help:'Ejemplos de preguntas',
            admin: {
                welcome:'Hola. Soy el asistente del campus. ¿Qué necesitas saber?', placeholder:'Consulta sobre el campus...',
                q1:'📊 Estadísticas del campus', q2:'👥 Alumnos inactivos', q3:'📈 Aprobados/Suspensos',
                q1text:'Dame las estadísticas generales del campus',
                q2text:'¿Qué alumnos no han entrado en los últimos 30 días?',
                q3text:'¿Cuál es la tasa de aprobados y suspensos por curso?',
                err:'No he podido procesar la consulta.',
                helpTitle:'Preguntas que puedes hacer como administrador',
                examples:[
                    '¿Cuántos cursos hay en el campus?',
                    '¿Cuántos alumnos están matriculados?',
                    'Dame las estadísticas del campus',
                    '¿Qué cursos tienen más alumnos matriculados?',
                    '¿Cuál es la tasa de aprobados por curso?',
                    '¿Qué alumnos no han entrado en los últimos 30 días?',
                    '¿Qué asignaturas tienen más entregas pendientes?',
                    '¿Qué actividad ha habido en el campus esta semana?',
                    'Lista todos los cursos con sus alumnos',
                    '¿Qué tasa de finalización tenemos?',
                    '¿Cuántos alumnos nuevos este mes?',
                    '¿Se nos está llenando el disco?',
                    '¿Cuándo hay más tráfico?',
                    '¿Cómo se distribuyen por categorías?',
                    '¿Hay profesores sin cursos asignados?',
                    '¿Hay cursos vacíos sin alumnos?',
                    '¿Funciona bien el cron del sistema?',
                    '¿Qué plugins están instalados?'
                ]
            },
            student: {
                helpTitle:'Preguntas que puedes hacer',
                examples:[
                    '¿Qué exámenes tengo próximos?',
                    '¿Qué tareas me falta entregar?',
                    '¿Qué notas tengo?',
                    '¿Qué plazos tengo esta semana?',
                    '¿En qué cursos estoy matriculado?',
                    '¿Qué contenido tiene el curso X?',
                    '¿Quién es mi profesor de X?',
                    '¿Tengo mensajes del foro sin leer?',
                    '¿He entregado la actividad X?',
                    '¿Qué porcentaje del curso X llevo completado?',
                    '¿Qué hay en mi calendario esta semana?',
                    '¿Qué feedback tengo de mis entregas?',
                    '¿Qué anuncios han publicado?',
                    '¿Cómo voy en general?'
                ]
            },
            teacher: {
                welcome:'Hola. Soy tu asistente docente. ¿En qué te puedo ayudar?',
                placeholder:'Consulta sobre tus cursos...',
                helpTitle:'Preguntas que puedes hacer como profesor',
                examples:[
                    '¿Qué cursos imparto?',
                    '¿Qué tengo que corregir?',
                    '¿Cómo va la clase en X?',
                    '¿Quién necesita ayuda?',
                    '¿Cuántas entregas sin corregir?',
                    '¿Quién no ha entrado últimamente?',
                    '¿Qué porcentaje han completado?',
                    '¿Hay preguntas sin responder en el foro?',
                    '¿Cómo van las notas del curso?',
                    '¿Quién ha entregado tarde?',
                    '¿Está participando la clase?'
                ]
            }
        },
        en: {
            welcome:'Hi! How can I help you?', placeholder:'Ask me anything...',
            exams:'📅 What exams do I have?', tasks:'📝 What am I missing?', courses:'📚 My courses',
            err:'Sorry, I could not process your request.',
            help:'Example questions',
            admin: {
                welcome:'Hi. I am the campus assistant. What do you need to know?', placeholder:'Campus query...',
                q1:'📊 Campus statistics', q2:'👥 Inactive students', q3:'📈 Pass/Fail rates',
                q1text:'Give me the overall campus statistics',
                q2text:'Which students have not logged in for 30 days?',
                q3text:'What is the pass and fail rate per course?',
                err:'Could not process the query.',
                helpTitle:'Questions you can ask as administrator',
                examples:[
                    'How many courses are on the campus?',
                    'How many students are enrolled?',
                    'Give me campus statistics',
                    'Which courses have the most students?',
                    'What is the pass rate per course?',
                    'Which students have not logged in recently?',
                    'Which assignments have the most pending submissions?',
                    'What activity has there been this week?',
                    'List all courses with their students',
                    'What is our course completion rate?',
                    'How many new users this month?',
                    'Is disk space running low?',
                    'When is traffic highest?',
                    'How are courses distributed by category?',
                    'Are there teachers without courses?',
                    'Are there empty courses with no students?',
                    'Is the system cron running properly?',
                    'What plugins are installed?'
                ]
            },
            student: {
                helpTitle:'Questions you can ask',
                examples:[
                    'What exams do I have coming up?',
                    'What assignments am I missing?',
                    'What are my grades?',
                    'What deadlines do I have this week?',
                    'What courses am I enrolled in?',
                    'What content is in course X?',
                    'Who is my teacher for X?',
                    'Do I have unread forum messages?',
                    'Have I submitted assignment X?',
                    'What percentage of course X have I completed?',
                    'What\'s on my calendar this week?',
                    'What feedback do I have?',
                    'What announcements have been posted?',
                    'How am I doing overall?'
                ]
            },
            teacher: {
                welcome:'Hi. I am your teaching assistant. How can I help?',
                placeholder:'Query about your courses...',
                helpTitle:'Questions you can ask as a teacher',
                examples:[
                    'What courses do I teach?',
                    'What needs grading?',
                    'How is the class doing in X?',
                    'Who needs help?',
                    'How many ungraded submissions?',
                    'Who hasn\'t logged in recently?',
                    'What completion percentage?',
                    'Are there unanswered forum questions?',
                    'How are grades looking?',
                    'Who submitted late?',
                    'Is the class engaged?'
                ]
            }
        },
        fr: {
            welcome:'Bonjour ! Comment puis-je vous aider ?', placeholder:'Posez votre question...',
            exams:'📅 Mes examens ?', tasks:'📝 Mes devoirs ?', courses:'📚 Mes cours',
            err:'Désolé, je n\u2019ai pas pu traiter votre demande.',
            help:'Exemples de questions',
            admin: {
                welcome:'Bonjour. Je suis l\'assistant du campus.', placeholder:'Requête...',
                q1:'📊 Statistiques', q2:'👥 Étudiants inactifs', q3:'📈 Réussite/Échec',
                q1text:'Donnez-moi les statistiques du campus',
                q2text:'Quels étudiants ne se sont pas connectés depuis 30 jours ?',
                q3text:'Quel est le taux de réussite par cours ?',
                err:'Impossible de traiter.',
                helpTitle:'Questions administrateur',
                examples:[
                    'Combien de cours sur le campus ?',
                    'Combien d\'étudiants inscrits ?',
                    'Statistiques du campus',
                    'Quels cours ont le plus d\'étudiants ?',
                    'Taux de réussite par cours',
                    'Étudiants inactifs récemment',
                    'Activité de la semaine'
                ]
            },
            student: {
                helpTitle:'Questions possibles',
                examples:[
                    'Quels examens à venir ?',
                    'Quels devoirs manquants ?',
                    'Quelles sont mes notes ?',
                    'Mes cours',
                    'Qui est mon professeur ?',
                    'Pourcentage de mon cours'
                ]
            }
        },
        de: {
            welcome:'Hallo! Wie kann ich helfen?', placeholder:'Frage stellen...',
            exams:'📅 Meine Prüfungen?', tasks:'📝 Meine Aufgaben?', courses:'📚 Meine Kurse',
            err:'Ich konnte die Anfrage nicht verarbeiten.',
            help:'Beispielfragen',
            admin: {welcome:'Hallo. Ich bin der Campus-Assistent.',placeholder:'Abfrage...',q1:'📊 Statistiken',q2:'👥 Inaktive Studenten',q3:'📈 Bestanden/Nicht bestanden',q1text:'Gib mir die Campus-Statistiken',q2text:'Welche Studenten waren 30 Tage inaktiv?',q3text:'Wie ist die Bestehensquote pro Kurs?',err:'Abfrage fehlgeschlagen.',helpTitle:'Admin-Fragen',examples:['Wie viele Kurse?','Wie viele Studenten?','Campus-Statistiken','Aktivität diese Woche']},
            student: {helpTitle:'Mögliche Fragen',examples:['Welche Prüfungen?','Welche Aufgaben fehlen?','Meine Noten?','Meine Kurse?','Wie viel vom Kurs komplett?']}
        },
        it: {
            welcome:'Ciao! Come posso aiutarti?', placeholder:'Fai la tua domanda...',
            exams:'📅 I miei esami?', tasks:'📝 Cosa mi manca?', courses:'📚 I miei corsi',
            err:'Spiacente, non ho potuto elaborare la richiesta.',
            help:'Domande di esempio',
            admin: {welcome:'Ciao. Sono l\'assistente del campus.',placeholder:'Query...',q1:'📊 Statistiche',q2:'👥 Studenti inattivi',q3:'📈 Promossi/Bocciati',q1text:'Dammi le statistiche del campus',q2text:'Quali studenti non si collegano da 30 giorni?',q3text:'Qual è il tasso di promossi per corso?',err:'Impossibile elaborare.',helpTitle:'Domande admin',examples:['Quanti corsi?','Quanti studenti?','Statistiche campus','Attività della settimana']},
            student: {helpTitle:'Domande possibili',examples:['Quali esami?','Cosa mi manca?','I miei voti?','I miei corsi?','Percentuale del corso?']}
        },
        pt: {
            welcome:'Olá! Como posso ajudar?', placeholder:'Faz a tua pergunta...',
            exams:'📅 Meus exames?', tasks:'📝 O que falta?', courses:'📚 Meus cursos',
            err:'Desculpe, não consegui processar o pedido.',
            help:'Exemplos de perguntas',
            admin: {welcome:'Olá. Sou o assistente do campus.',placeholder:'Consulta...',q1:'📊 Estatísticas',q2:'👥 Alunos inativos',q3:'📈 Aprovados/Reprovados',q1text:'Dá-me as estatísticas do campus',q2text:'Que alunos não entraram há 30 dias?',q3text:'Qual a taxa de aprovados por curso?',err:'Não foi possível processar.',helpTitle:'Perguntas de admin',examples:['Quantos cursos?','Quantos alunos?','Estatísticas do campus','Atividade da semana']},
            student: {helpTitle:'Perguntas possíveis',examples:['Que exames tenho?','O que me falta?','As minhas notas?','Os meus cursos?','Percentagem do curso?']}
        }
    };

    function init(cfg) {
        config = cfg;
        if (document.getElementById('campusai-root')) return;
        userRole = cfg.userRole || 'student';
        isAdmin = userRole === 'admin';
        isTeacher = userRole === 'teacher';
        tr = langs[cfg.defaultLang || 'es'] || langs['es'];
        buildWidget();
    }

    function buildWidget() {
        var root = document.createElement('div');
        root.id = 'campusai-root';

        var fab = document.createElement('button');
        fab.id = 'campusai-fab';
        fab.className = config.position || 'bottom-right';
        fab.style.cssText = 'width:56px;height:56px;border-radius:50%;background:' + (config.color||'#0066CC') + ';color:#fff;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.25);display:flex;align-items:center;justify-content:center;position:fixed;z-index:99999;' + fabPosition(config.position);
        if (config.iconUrl) {
            fab.innerHTML = '<img src="' + config.iconUrl + '" style="width:30px;height:30px;object-fit:contain;">';
        } else {
            fab.innerHTML = isAdmin ? '🔧' : (isTeacher ? '👨‍🏫' : '🎓');
            fab.style.fontSize = '26px';
        }
        fab.onclick = toggleModal;

        var modal = document.createElement('div');
        modal.id = 'campusai-modal';
        modal.className = (config.position || 'bottom-right');
        modal.style.cssText = 'position:fixed;bottom:90px;' + modalSide(config.position) + ';width:380px;max-width:calc(100vw - 32px);height:520px;max-height:calc(100vh - 120px);background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,0.18);display:none;flex-direction:column;overflow:hidden;z-index:99999;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;font-size:14px;';

        var col = config.color || '#0066CC';
        var cur = isAdmin ? tr.admin : (isTeacher ? tr.teacher : tr);
        var html = '';

        html += '<div style="background:' + col + ';color:#fff;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">';
        html += '<div style="font-weight:600;font-size:15px;">' + (isAdmin ? '🔧' : (isTeacher ? '👨‍🏫' : '🎓')) + ' ' + escHtml(config.title||'Campus Assistant') + '</div>';
        html += '<div style="display:flex;gap:6px;">';
        html += '<button id="campusai-help" title="' + escHtml(tr.help) + '" style="background:rgba(255,255,255,0.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center;">?</button>';
        html += '<button id="campusai-close" style="background:rgba(255,255,255,0.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>';
        html += '</div></div>';

        var helpData = isAdmin ? tr.admin : (isTeacher ? tr.teacher : tr.student);
        html += '<div id="campusai-help-panel" style="display:none;padding:12px 16px;background:#f0f4f8;border-bottom:1px solid #e0e0e0;max-height:300px;overflow-y:auto;">';
        html += '<div style="font-weight:600;font-size:13px;margin-bottom:8px;color:#333;">' + escHtml(helpData.helpTitle) + '</div>';
        for (var i = 0; i < helpData.examples.length; i++) {
            html += '<div class="ca-example" data-q="' + escHtml(helpData.examples[i]) + '" style="padding:6px 10px;margin-bottom:4px;background:#fff;border-radius:8px;cursor:pointer;font-size:13px;color:#444;border:1px solid #e8e8e8;">' + escHtml(helpData.examples[i]) + '</div>';
        }
        html += '</div>';

        html += '<div id="campusai-quick" style="display:flex;flex-wrap:wrap;gap:6px;padding:8px 16px 4px;background:#f8f9fa;">';
        if (isAdmin) {
            html += quickBtn(tr.admin.q1, tr.admin.q1text, col);
            html += quickBtn(tr.admin.q2, tr.admin.q2text, col);
            html += quickBtn(tr.admin.q3, tr.admin.q3text, col);
        } else if (isTeacher) {
            html += quickBtn('📋 Mis cursos', 'What courses do I teach?', col);
            html += quickBtn('✍️ A corregir', 'What submissions need grading?', col);
            html += quickBtn('👥 Alumnos', 'List students in my courses with their last access', col);
        } else {
            html += quickBtn(tr.exams, 'What exams do I have coming up?', col);
            html += quickBtn(tr.tasks, 'What assignments have I not submitted yet?', col);
            html += quickBtn(tr.courses, 'What courses am I enrolled in?', col);
        }
        html += '</div>';

        html += '<div id="campusai-messages" style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;background:#f8f9fa;">';
        html += '<div style="max-width:80%;padding:10px 14px;border-radius:14px;background:#fff;border:1px solid #e0e0e0;align-self:flex-start;border-bottom-left-radius:4px;">' + escHtml(cur.welcome) + '</div>';
        html += '</div>';

        html += '<div id="campusai-input-area" style="padding:12px 16px;border-top:1px solid #e0e0e0;background:#fff;display:flex;gap:8px;flex-shrink:0;">';
        html += '<input type="text" id="campusai-input" placeholder="' + escHtml(cur.placeholder) + '" autocomplete="off" maxlength="2000" style="flex:1;border:1px solid #ddd;border-radius:20px;padding:10px 16px;font-size:14px;outline:none;">';
        html += '<button id="campusai-send" style="width:40px;height:40px;border-radius:50%;background:' + col + ';color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px;">➤</button>';
        html += '</div>';
        html += '<div id="campusai-rl" style="font-size:11px;color:#999;text-align:center;padding:0 0 6px;background:#fff;"></div>';

        modal.innerHTML = html;
        root.appendChild(fab);
        root.appendChild(modal);
        document.body.appendChild(root);

        document.getElementById('campusai-close').onclick = toggleModal;
        document.getElementById('campusai-help').onclick = toggleHelp;
        document.getElementById('campusai-send').onclick = function() { sendMessage(); };
        document.getElementById('campusai-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !isLoading) sendMessage();
        });

        var qbtns = document.querySelectorAll('.ca-quick');
        for (var i = 0; i < qbtns.length; i++) {
            qbtns[i].onclick = function() {
                sendText(this.getAttribute('data-text'));
            };
        }

        var exbtns = document.querySelectorAll('.ca-example');
        for (var i = 0; i < exbtns.length; i++) {
            exbtns[i].onclick = function() {
                toggleHelp();
                sendText(this.getAttribute('data-q'));
            };
        }
    }

    function quickBtn(label, text, col) {
        return '<button class="ca-quick" data-text="' + escHtml(text) + '" style="background:#e8eef5;color:' + col + ';border:none;border-radius:12px;padding:6px 12px;font-size:13px;cursor:pointer;">' + label + '</button>';
    }

    function toggleModal() {
        var m = document.getElementById('campusai-modal');
        conversationOpen = !conversationOpen;
        m.style.display = conversationOpen ? 'flex' : 'none';
        if (conversationOpen) setTimeout(function(){ document.getElementById('campusai-input').focus(); }, 200);
    }

    function toggleHelp() {
        var p = document.getElementById('campusai-help-panel');
        helpOpen = !helpOpen;
        p.style.display = helpOpen ? 'block' : 'none';
    }

    function sendText(text) {
        document.getElementById('campusai-input').value = text;
        sendMessage();
    }

    function sendMessage() {
        var input = document.getElementById('campusai-input');
        var msg = input.value.trim();
        if (!msg || isLoading) return;
        isLoading = true;
        input.value = '';
        document.getElementById('campusai-send').disabled = true;
        document.getElementById('campusai-quick').style.display = 'none';
        document.getElementById('campusai-help-panel').style.display = 'none';
        helpOpen = false;
        addMessage('user', msg);
        showTyping();

        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                hideTyping();
                if (xhr.status === 200) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        addMessage('assistant', res.message || (isAdmin ? tr.admin.err : (isTeacher ? tr.err : tr.err)));
                        if (typeof res.remaining !== 'undefined' && res.remaining <= 3) {
                            document.getElementById('campusai-rl').textContent = res.remaining;
                        }
                    } catch(e) { addMessage('assistant', isAdmin ? tr.admin.err : tr.err); }
                } else { addMessage('assistant', isAdmin ? tr.admin.err : tr.err); }
                isLoading = false;
                document.getElementById('campusai-send').disabled = false;
            }
        };
        xhr.send('message=' + encodeURIComponent(msg) + '&sesskey=' + config.sesskey);
    }

    function addMessage(role, content) {
        var m = document.getElementById('campusai-messages');
        var b = document.createElement('div');
        var u = role === 'user';
        b.style.cssText = 'max-width:80%;padding:10px 14px;border-radius:14px;line-height:1.5;word-wrap:break-word;' + (u ? 'background:'+(config.color||'#0066CC')+';color:#fff;align-self:flex-end;border-bottom-right-radius:4px;' : 'background:#fff;color:#333;border:1px solid #e0e0e0;align-self:flex-start;border-bottom-left-radius:4px;');
        b.innerHTML = content;
        m.appendChild(b);
        m.scrollTop = m.scrollHeight;
    }

    function showTyping() {
        var m = document.getElementById('campusai-messages');
        var i = document.createElement('div');
        i.id = 'campusai-typing';
        i.style.cssText = 'display:flex;gap:4px;padding:10px 14px;background:#fff;border:1px solid #e0e0e0;border-radius:14px;align-self:flex-start;';
        i.innerHTML = '<span style="width:8px;height:8px;background:#aaa;border-radius:50%;animation:ca-pulse 1.4s infinite;"></span><span style="width:8px;height:8px;background:#aaa;border-radius:50%;animation:ca-pulse 1.4s infinite;animation-delay:0.2s;"></span><span style="width:8px;height:8px;background:#aaa;border-radius:50%;animation:ca-pulse 1.4s infinite;animation-delay:0.4s;"></span>';
        m.appendChild(i);
        m.scrollTop = m.scrollHeight;
    }

    function hideTyping() { var t = document.getElementById('campusai-typing'); if (t) t.remove(); }
    function fabPosition(p) { return p === 'bottom-left' ? 'bottom:24px;left:24px;' : 'bottom:24px;right:24px;'; }
    function modalSide(p) { return p === 'bottom-left' ? 'left:24px' : 'right:24px'; }
    function escHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    window.CampusAI = { init: init };
})();
