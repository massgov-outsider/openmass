<?php

/**
 * @file
 * Run updates after updatedb has completed.
 */

use Drupal\Core\Database\Database;

/**
 * Updates a text format for field_rules_section_body to rules_of_court_section.
 */
function mass_fields_post_update_text_format() {
  Database::getConnection()
    ->update('paragraph__field_rules_section_body')
    ->fields(['field_rules_section_body_format' => 'rules_of_court_section'])
    ->execute();

}
