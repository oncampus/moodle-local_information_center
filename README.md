# Information Centre

**Information Centre** is a local plugin for Moodle that enables the management and display of information within
the Moodle platform. It offers functions for creating, updating and deleting messages, as well as for defining
message categories and visibility options based on user roles.

## Features

- APIs for creating, updating and deleting messages
- Definition of message categories
- Control of message visibility based on user roles
- Clean-up of old messages via cron job
- Provision of web service functions for external message management

## Installation

1. Clone the repository into the `/local/information_center` directory of the Moodle installation.
2. Go to **Site administration → System messages** to start the installation or run
   `admin/cli/upgrade.php`.

### Requirements

None

## Configuration

None

## Nutzung

- The messages are visible at `/local/information_centre/pages/user_notification_dashboard.php`
- Messages can be made visible to different user roles (e.g. students, teachers, managers, administrators).
- The plugin enables administrators to communicate important information to specific user groups in a targeted manner.

## Rights

This plugin defines the following rights:

| Name                                             | Description                                          | Standard role           |
|--------------------------------------------------|------------------------------------------------------|-------------------------|
| `local/information_center:read_student_messages` | Allows the user to read messages for students        | Authenticated users     |
| `local/information_center:read_teacher_messages` | Allows the user to read messages for teachers        | Editingteacher, Teacher |
| `local/information_center:read_manager_messages` | Allows the user to read messages for managers        | Manager                 |
| `local/information_center:read_admin_messages`   | Allows the user to read messages for administrators  |                         |
| `local/information_center:create_message`        | Allows the user to create messages                   | Manager                 |

## Cron jobs

This plugin defines the following cron jobs:

| Task Class                                       | Description                     | Standard intervall for execution |
|--------------------------------------------------|---------------------------------|----------------------------------|
| `local_information_center\tasks\message_cleanup` | Clears old and expired messages | Daily (at 2:00 a.m.)             |

## Web Services

This plugin provides the following web service functions:

| Web Service Function                      | Description                       |
|-------------------------------------------|-----------------------------------|
| `local_information_center_create_message` | Send a message to specific users. |

## License

This plugin is licensed under [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html).

## Credits

Author:

- Konrad Ebel ([konrad.ebel@oncampus.de](mailto:konrad.ebel@oncampus.de))
- Jonas Reuter ([jonas.reuter@oncampus.de](mailto:jonas.reuter@oncampus.de))
