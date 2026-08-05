<?php
// This file is part of Campus Assistant - a Moodle local plugin.
//
// Campus Assistant is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Spanish language strings for Campus Assistant.
 *
 * @package   local_campusai
 * @copyright 2026 Campus Assistant <hola@campusassistant.app>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Asistente del Campus';
$string['pluginname_desc'] = 'Asistente con IA para los estudiantes de tu campus Moodle.';

// Settings.
$string['settings'] = 'Configuración del Asistente del Campus';
$string['settings_provider'] = 'Proveedor de IA';
$string['settings_provider_desc'] = 'Selecciona el proveedor de IA para el asistente.';
$string['settings_apikey'] = 'API Key';
$string['settings_apikey_desc'] = 'Tu clave API para el proveedor seleccionado.';
$string['settings_model'] = 'Modelo';
$string['settings_model_desc'] = 'El modelo específico a utilizar (ej: gpt-4o-mini, gemini-1.5-flash, claude-3-5-haiku-20241022, deepseek-chat).';
$string['settings_systemprompt'] = 'Prompt del Sistema';
$string['settings_systemprompt_desc'] = 'Instrucciones que definen el comportamiento del asistente. Deja el valor por defecto salvo que sepas lo que haces.';
$string['settings_position'] = 'Posición del Botón';
$string['settings_position_desc'] = 'Dónde aparece el botón del asistente en la página.';
$string['settings_color'] = 'Color del Tema';
$string['settings_color_desc'] = 'Color principal del botón y el modal del asistente.';
$string['settings_title'] = 'Título del Asistente';
$string['settings_title_desc'] = 'Título mostrado en la cabecera del modal.';
$string['settings_welcome'] = 'Mensaje de Bienvenida';
$string['settings_welcome_desc'] = 'Primer mensaje que ve el alumno al abrir el asistente.';
$string['settings_ratelimit'] = 'Límite (mensajes por hora)';
$string['settings_ratelimit_desc'] = 'Máximo de mensajes que un alumno puede enviar por hora.';
$string['settings_maxtokens'] = 'Máximo de Tokens por Respuesta';
$string['settings_maxtokens_desc'] = 'Máximo de tokens en la respuesta de la IA. Más alto = más detallado pero más caro.';
$string['settings_auditlog'] = 'Activar Log de Auditoría';
$string['settings_auditlog_desc'] = 'Registrar todas las interacciones de los alumnos para revisión del administrador.';
$string['settings_logretention'] = 'Retención de Logs (días)';
$string['settings_logretention_desc'] = 'Cuánto tiempo conservar los logs antes de borrarlos. 0 = mantener siempre.';
$string['settings_language'] = 'Idioma por defecto';
$string['settings_language_desc'] = 'Idioma por defecto para las respuestas del asistente.';

$string['settings_enabled'] = 'Activar Asistente del Campus';
$string['settings_enabled_desc'] = 'Interruptor principal del asistente.';
$string['settings_hideroles'] = 'Ocultar para Roles';
$string['settings_hideroles_desc'] = 'Selecciona los roles que NO deben ver el asistente (ej: gestores, administradores).';

// Positions.
$string['position_bottom_right'] = 'Abajo a la Derecha';
$string['position_bottom_left'] = 'Abajo a la Izquierda';

// Quick prompts.
$string['quickprompt_exams'] = '📅 ¿Qué exámenes tengo?';
$string['quickprompt_tasks'] = '📝 ¿Qué me falta entregar?';
$string['quickprompt_courses'] = '📚 Mis cursos';

// Privacy.
$string['privacy:metadata:conversation'] = 'Almacena preguntas y respuestas de los estudiantes para auditoría.';
$string['privacy:metadata:conversation:userid'] = 'El ID del usuario que envió el mensaje.';
$string['privacy:metadata:conversation:usermessage'] = 'La pregunta realizada por el alumno.';
$string['privacy:metadata:conversation:assistantmessage'] = 'La respuesta dada por el asistente.';
$string['privacy:metadata:conversation:timecreated'] = 'Cuándo ocurrió la interacción.';

// Capabilities.
$string['campusai:use'] = 'Usar el Asistente del Campus';
$string['campusai:manage'] = 'Gestionar la configuración del Asistente del Campus';

// Errors.
$string['error_disabled'] = 'El Asistente del Campus está desactivado actualmente.';
$string['error_provider'] = 'El proveedor de IA seleccionado no está configurado correctamente.';
$string['error_ratelimit'] = 'Has alcanzado el límite de mensajes. Inténtalo de nuevo más tarde.';
$string['error_generic'] = 'Lo siento, no he podido procesar tu solicitud. Inténtalo de nuevo.';
