<?php

namespace Drupal\mass_flagging\Service;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\diff\DiffEntityComparison;

/**
 * Class MassFlaggingEntityComparison.
 *
 * @package Drupal\mass_flagging\Service
 */
class MassFlaggingEntityComparison extends DiffEntityComparison {

  /**
   * {@inheritdoc}
   */
  public function getRevisionDescription(ContentEntityInterface $revision, ContentEntityInterface $previous_revision = NULL) {
    // Code is adapted from Diff module prior to patch introduced in this issue:
    // https://www.drupal.org/project/diff/issues/2880936
    $summary_elements = [];
    $revision_summary = '';
    // Check if the revision has a revision log message.
    if ($revision instanceof RevisionLogInterface) {
      $revision_summary = Xss::filter($revision->getRevisionLogMessage());
    }
    // Auto generate the revision log.
    if ($revision_summary == '') {
      // If there is a previous revision, load values of both revisions, loop
      // over the current revision fields.
      if ($previous_revision) {
        $left_values = $this->summary($previous_revision);
        $right_values = $this->summary($revision);
        foreach ($right_values as $key => $value) {
          // Unset left values after comparing. Add right value label to the
          // summary if it is changed or new.
          if (isset($left_values[$key])) {
            if ($value['value'] != $left_values[$key]['value']) {
              $summary_elements[] = $value['label'];
            }
            unset($left_values[$key]);
          }
          else {
            $summary_elements[] = $value['label'];
          }
        }
        // Add the remaining left values if not present in the right entity.
        foreach ($left_values as $key => $value) {
          if (!isset($right_values[$key])) {
            $summary_elements[] = $value['label'];
          }
        }
        if (count($summary_elements) > 0) {
          $revision_summary = 'Changes on: ' . implode(', ', $summary_elements);
        }
        else {
          $revision_summary = 'No changes.';
        }
      }
      else {
        $revision_summary = 'Initial revision.';
      }
    }

    // Add workflow/content moderation state information.
    if ($state = $this->getModerationState($revision)) {
      $revision_summary .= " ($state)";
    }

    return $revision_summary;
  }

}
