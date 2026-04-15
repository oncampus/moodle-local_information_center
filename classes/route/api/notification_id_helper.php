<?php

namespace local_information_center\route\api;

use dml_exception;
use moodle_database;

class notification_id_helper {
    public function __construct(
        private moodle_database $db
    ) {
    }

    /**
     * Saves a mapping of a local ID to a remote ID.
     *
     * @param int $localid
     * @param int $remoteid
     * @return bool
     * @throws dml_exception
     */
    public function save_local_id(int $localid, int $remoteid): void {
        $this->db->insert_record(
            'local_information_center_external_ids',
            (object) [
                'messageid' => $localid,
                'externalid' => $remoteid,
            ],
            false
        );
    }

    /**
     * Check for local mapping of the given remote id.
     *
     * @param int $remoteid
     * @return int|null
     * @throws dml_exception
     */
    public function get_local_id(int $remoteid): ?int {
        $localid = $this->db->get_field(
            'local_information_center_external_ids',
            'messageid',
            ['externalid' => $remoteid]
        );

        if ($localid === false) {
            return null;
        }

        return (int)$localid;
    }
}
