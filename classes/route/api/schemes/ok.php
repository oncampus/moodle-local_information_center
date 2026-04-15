<?php

namespace local_information_center\route\api\schemes;

use core\router\schema\response\response;

class ok extends response {
    public function __construct() {
        parent::__construct(
            statuscode: 200,
            description: 'OK',
        );
    }
}