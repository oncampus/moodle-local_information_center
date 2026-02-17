<?php
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
 * Strings for component 'local_information_center', language 'en'
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  onCampus GmbH, 2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action:delete'] = 'Löschen';
$string['action:edit'] = 'Bearbeiten';

$string['category:administrative'] = 'Verwaltung';
$string['category:all'] = 'Alle';
$string['category:events'] = 'Veranstaltungen';
$string['category:infos'] = 'Informationen';
$string['category:innovations'] = 'Neuerungen';
$string['category:maintenance'] = 'Wartung/Störung';

$string['deletion_success'] = 'Die Benachrichtigung wurde erfolgreich gelöscht';

$string['form:after'] = 'Nach';
$string['form:any'] = 'Beliebig';
$string['form:before'] = 'Vor';
$string['form:category'] = 'Kategorie';
$string['form:category_help'] = (
    '<ul>' .
    '<li><strong>Informationen <i class="fa fa-newspaper"></i>.</strong> Allgemeine Nachrichten und Mitteilungen (Prio Niedrig)</li>' .
    '<li><strong>Veranstaltungen <i class="fa-regular fa-calendar-plus"></i>.</strong> Informationen zu Veranstaltungen, Terminen oder Aktionen (Prio Mittel)' .
    '<li><strong>Verwaltung <i class="fa fa-graduation-cap"></i>.</strong> Offizielle oder organisatorisch wichtige Mitteilungen (Prio Hoch)</li>' .
    '</ul>'
);
$string['form:categoryname'] = 'Kategorie';
$string['form:contains'] = 'Enthält';
$string['form:deleted'] = 'Gelöschte anzeigen';
$string['form:equals'] = 'Gleich';
$string['form:firstname'] = 'Vorname';
$string['form:header'] = 'Neue Benachrichtigung';
$string['form:lastname'] = 'Nachname';
$string['form:message'] = 'Nachricht';
$string['form:renotify'] = 'Erneut benachrichtigen';
$string['form:title'] = 'Titel';
$string['form:validation:date'] = 'Das Enddatum muss nach dem Startdatum liegen';
$string['form:visibility'] = 'Sichtbarkeit';
$string['form:visibility_help'] = (
    'Die Rollen verfügen über eine gestufte Sichtbarkeit. ' .
    'Höherrangige Rollen schließen automatisch alle Sichtbarkeiten der niedrigeren Rollen mit ein:
    <ol>
    <li><strong>Schüler*innen.</strong> sehen nur Ihre Nachrichten.</li>
    <li><strong>Lehrkräfte.</strong> sehen Nachrichten für Lehrkräfte und Schüler*innen.</li>
    <li><strong>Manager*innen.</strong> sehen Nachrichten für Manager*innen, Lehrkräfte und Schüler*innen.</li>
    <li><strong>Administrator*innen.</strong> sehen sämtliche Nachrichten</li>
    </ol>'
);

$string['local/information_center:delete_messages'] = 'Kann Nachrichten löschen';
$string['local/information_center:read_admin_messages'] = 'Kann Nachrichten von Administratoren im Informationszentrum sehen';
$string['local/information_center:read_manager_messages'] = 'Kann Nachrichten von Managern im Informationszentrum sehen';
$string['local/information_center:read_student_messages'] = 'Kann Nachrichten von Studierenden im Informationszentrum sehen';
$string['local/information_center:read_teacher_messages'] = 'Kann Nachrichten von Lehrenden im Informationszentrum sehen';
$string['local/information_center:update_or_create_messages'] = 'Kann Nachrichten editieren und erstellen';

$string['navigationnode:unreadcount'] = '{$a} ungelesene Nachrichten';

$string['overview:external'] = 'Extern (BW)';
$string['overview:internal'] = 'Intern';
$string['overview:sendmessages'] = 'Nachrichten verwalten';
$string['overview:title'] = 'Benachrichtigungen';

$string['page:message_overview'] = 'Benachrichtigungsübersicht';

$string['pluginname'] = 'Informationszentrum';

$string['privacy:metadata:local_information_center'] = 'Speichert Informationen über Nachrichten, die ein Benutzer im Informationszentrum gelesen hat.';
$string['privacy:metadata:local_information_center:messageid'] = 'Nachricht die gelesen wurde.';
$string['privacy:metadata:local_information_center:userid'] = 'Die ID des Benutzers, der eine Nachricht gelesen hat.';

$string['privacy:metadata:local_information_center_messages'] = 'Speichert Nachrichten, die über das Informationszentrum gesendet wurden.';
$string['privacy:metadata:local_information_center_messages:categoryid'] = 'Die ID der Kategorie, zu der diese Nachricht gehört.';
$string['privacy:metadata:local_information_center_messages:component'] = 'Die Moodle-Komponente oder das Plugin, das die Nachricht generiert hat.';
$string['privacy:metadata:local_information_center_messages:fullmessage'] = 'Der vollständige Nachrichtentext.';
$string['privacy:metadata:local_information_center_messages:fullmessageformat'] = 'Das Format der vollständigen Nachricht (z. B. HTML oder Klartext).';
$string['privacy:metadata:local_information_center_messages:smallmessage'] = 'Eine kurze Zusammenfassung oder Vorschau der Nachricht.';
$string['privacy:metadata:local_information_center_messages:subject'] = 'Der Betreff der Nachricht.';
$string['privacy:metadata:local_information_center_messages:timecreated'] = 'Der Zeitpunkt, zu dem die Nachricht erstellt wurde.';
$string['privacy:metadata:local_information_center_messages:timedeleted'] = 'Der Zeitpunkt, zu dem die Nachricht gelöscht wurde (falls zutreffend).';
$string['privacy:metadata:local_information_center_messages:timeend'] = 'Der Zeitpunkt, ab dem die Nachricht nicht mehr sichtbar ist.';
$string['privacy:metadata:local_information_center_messages:timemodified'] = 'Der Zeitpunkt, zu dem die Nachricht zuletzt bearbeitet wurde.';
$string['privacy:metadata:local_information_center_messages:timestart'] = 'Der Zeitpunkt, ab dem die Nachricht sichtbar ist.';
$string['privacy:metadata:local_information_center_messages:useridfrom'] = 'Die ID des Benutzers, der die Nachricht gesendet hat.';
$string['privacy:metadata:local_information_center_messages:visibility'] = 'Für welche Benutzer die Nachricht sichtbar ist.';

$string['settings:auto_delete'] = 'Automatisch löschen';
$string['settings:auto_delete_desc'] = 'Legt fest, ob eine Benachrichtigung nach Ablauf des Enddatums automatisch gelöscht wird';
$string['settings:auto_delete_duration'] = 'Dauer bis zur automatischen Löschung';
$string['settings:auto_delete_duration_desc'] = 'Legt fest, wie lange nach dem Enddatum die Benachrichtigung gelöscht werden soll';
$string['settings:btn_add'] = 'Neue Benachrichtigung hinzufügen';
$string['settings:enable'] = 'Aktivieren';
$string['settings:enable_desc'] = 'Legt fest, ob alle Benachrichtigungen aktiviert oder deaktiviert sind';

$string['table:createdby'] = 'Erstellt von';
$string['table:createdby:extern'] = 'Extern';
$string['table:enddate'] = 'Enddatum';
$string['table:enddate_help'] = (
'Nachrichten werden nach dem Datum unsichtbar. Man kann sie im Managementbereich sehen.'
);
$string['table:messagecategory'] = 'Kategorie';
$string['table:startdate'] = 'Startdatum';
$string['table:title'] = 'Titel';
$string['table:type'] = 'Typ';
$string['table:visibility'] = 'Sichtbarkeit';

$string['task:message_cleanup'] = 'Nachrichtenbereinigung';

$string['visibility:admin'] = 'Sichtbar für Administrator*innen';
$string['visibility:manager'] = 'Sichtbar für Manager*innen';
$string['visibility:student'] = 'Sichtbar für Schüler*innen';
$string['visibility:teacher'] = 'Sichtbar für Lehrkräfte';
