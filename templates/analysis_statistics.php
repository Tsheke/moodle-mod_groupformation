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
 * Analysis statistics template
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $USER;
?>

<div class="gf_pad_header_small">
    <?php echo get_string('statistic', 'groupformation'); ?>
</div>

<div class="gf_pad_content">
    <div class="grid row_highlight">
        <div class="col_m_100"><?php echo get_string('are', 'groupformation'); ?>
            <b>
                <?php echo ' ' . $this->_['statistics_enrolled']; ?>
            </b>
            <?php echo ' ';?>
            <?php if ($this->_['statistics_enrolled'] == 1): ?>
                <?php echo get_string('students_available_single', 'groupformation'); ?>
            <?php else: ?>
                <?php echo get_string('students_available_multiple', 'groupformation'); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="grid row_highlight">
        <div class="col_m_100"><b><?php echo $this->_['statistics_processed']; ?></b>
            <?php if ($this->_['statistics_processed'] == 1): ?>
                <?php echo get_string('students_answered_single', 'groupformation'); ?>
            <?php else: ?>
                <?php echo get_string('students_answered_multiple', 'groupformation'); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="grid row_highlight">
        <div class="col_m_100"><b><?php echo $this->_['statistics_submitted']; ?></b>
            <?php echo ' ' ?>
            <?php if ($this->_['statistics_submitted'] == 1): ?>
                <?php echo get_string('students_committed_single', 'groupformation'); ?>
            <?php else: ?>
                <?php echo get_string('students_committed_multiple', 'groupformation'); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="grid row_highlight">
        <div class="col_m_100"><b><?php echo $this->_['statistics_submitted_complete']; ?></b>
            <?php echo ' ' ?>
            <?php if ($this->_['statistics_submitted_complete'] == 1): ?>
                <?php echo get_string('completed_questionnaire', 'groupformation'); ?>
            <?php else: ?>
                <?php echo get_string('completed_questionnaire', 'groupformation'); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="grid row_highlight">
        <div class="col_m_100"><b><?php echo $this->_['statistics_excluded']; ?></b>
            <?php echo ' ' ?>
            <?php if ($this->_['statistics_excluded'] == 1): ?>
                <?php echo get_string('students_excluded_single', 'groupformation'); ?>
            <?php else: ?>
                <?php echo get_string('students_excluded_multiple', 'groupformation'); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="grid row_highlight">
        <div class="col_m_100"><b><?php echo $this->_['statistics_available']; ?></b>
            <?php echo ' ' ?>
            <?php if ($this->_['statistics_available'] == 1): ?>
                <?php echo get_string('students_available_grouping_single', 'groupformation'); ?>
            <?php else: ?>
                <?php echo get_string('students_available_grouping_multiple', 'groupformation'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>