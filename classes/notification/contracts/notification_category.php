<?php

namespace local_information_center\notification\contracts;

readonly class notification_category {
    public function __construct(
        public int $id,
        public string $name,
        public string $color,
        public string $icon,
    ) {
    }

    public function get_label(): string {
        return get_string(
            'category:' . $this->name,
            'local_information_center'
        );
    }

    public function get_icon(): string {
        return "fa-regular $this->icon";
    }
}
