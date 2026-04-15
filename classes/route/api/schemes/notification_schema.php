<?php

namespace local_information_center\route\api\schemes;

use core\param;
use core\router\schema\objects\scalar_type;
use core\router\schema\objects\schema_object;

class notification_schema extends schema_object {
    public function __construct() {
        parent::__construct(
            content: [
                'categoryid' => new scalar_type(param::INT, true),
                'fullmessage' => new scalar_type(param::RAW, true),
                'fullmessageformat' => new scalar_type(param::INT, true),
                'smallmessage' => new scalar_type(param::RAW, true),
                'visibility' => new scalar_type(param::TEXT, true),
                'subject' => new scalar_type(param::TEXT, true),
                'timestart' => new scalar_type(param::INT),
                'timeend' => new scalar_type(param::INT),
                'timedeleted' => new scalar_type(param::INT),
            ],
        );
    }
}
