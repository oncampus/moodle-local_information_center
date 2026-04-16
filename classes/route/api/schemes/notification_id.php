<?php

namespace local_information_center\route\api\schemes;

use core\param;
use core\router\schema\parameters\path_parameter;

class notification_id extends path_parameter {
    public function __construct(bool $required) {
        parent::__construct(
            name: 'uuid',
            type: param::ALPHANUMEXT,
            required: $required
        );
    }
}