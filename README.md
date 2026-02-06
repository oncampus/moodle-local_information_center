# Information Center

**Information Center** ist ein lokales Plugin für Moodle, welches die Verwaltung und Anzeige von Informationen innerhalb
der Moodle-Plattform ermöglicht.
Es bietet Funktionen zum Erstellen, Aktualisieren und Löschen von Nachrichten sowie zur Definition von
Nachrichtenkategorien und Sichtbarkeitsoptionen
basierend auf Benutzerrollen.

## Features

- APIs um Nachrichten zu erstellen, aktualisieren und löschen
- Definition von Nachrichtenkategorien
- Steuerung der Nachrichtensichtbarkeit basierend auf Benutzerrollen
- Bereinigung alter Nachrichten durch Cronjob
- Bereitstellung von Web-Service-Funktionen zur externen Nachrichtenverwaltung

## Installation

1. Clone das Repository in das `/local/information_center`-Verzeichnis der Moodle-Installation.
2. Ruf' **Website-Administration → Systemnachrichten** auf, um die Installation anzustoßen oder führ'
   `admin/cli/upgrade.php` aus.

### Voraussetzungen

- Moodle 3.x oder höher
- PHP 7.x oder höher

## Konfiguration

Keine Vorhanden

## Nutzung

- Die Nachrichten werden Sichtbar unter `/local/information_center/pages/overview.php`
- Nachrichten können für verschiedene Benutzerrollen sichtbar gemacht werden (z.B. Studenten, Lehrer, Manager,
  Administratoren).
- Das Plugin ermöglicht es Administratoren, wichtige Informationen gezielt an bestimmte Benutzergruppen zu
  kommunizieren.

## Rechte

Dieses Plugin definiert folgende Rechte:

| Name des Rechts                                  | Beschreibung                                                    | Standardrolle           |
|--------------------------------------------------|-----------------------------------------------------------------|-------------------------|
| `local/information_center:read_student_messages` | Erlaubt es dem Nutzer, Nachrichten für Studenten zu lesen       | Authenticated users     |
| `local/information_center:read_teacher_messages` | Erlaubt es dem Nutzer, Nachrichten für Lehrer zu lesen          | Editingteacher, Teacher |
| `local/information_center:read_manager_messages` | Erlaubt es dem Nutzer, Nachrichten für Manager zu lesen         | Manager                 |
| `local/information_center:read_admin_messages`   | Erlaubt es dem Nutzer, Nachrichten für Administratoren zu lesen |                         |
| `local/information_center:create_message`        | Erlaubt es dem Nutzer, Nachrichten zu erstellen                 | Manager                 |

## Cronjobs

Dieses Plugin definiert folgende Cronjobs:

| Task Class                                       | Beschreibung                               | Standardintervall der Ausführung |
|--------------------------------------------------|--------------------------------------------|----------------------------------|
| `local_information_center\tasks\message_cleanup` | Bereinigt alte und abgelaufene Nachrichten | Täglich (um 02:00 Uhr)           |

## Web Services

Dieses Plugin stellt folgende Webservice-Funktionen zur Verfügung:

| Webservice-Funktion                       | Beschreibung                                 |
|-------------------------------------------|----------------------------------------------|
| `local_information_center_create_message` | Sendet eine Nachricht an bestimmte Benutzer. |

## Lizenz

Dieses Plugin ist lizensiert unter [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html).

## Credits

Autor:

- Konrad Ebel ([konrad.ebel@oncampus.de](mailto:konrad.ebel@oncampus.de))
- Jonas Reuter ([jonas.reuter@oncampus.de](mailto:jonas.reuter@oncampus.de))
