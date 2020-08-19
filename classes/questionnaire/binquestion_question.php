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
 * Prints a particular instance of groupformation questionnaire
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic, Sven Timpe
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/dropdown_question.php');

/**
 * Class mod_groupformation_binquestion_question
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic, Sven Timpe
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_binquestion_question extends mod_groupformation_dropdown_question {

    /**
     * Returns answer
     *
     * @return int|mixed|null
     */
    public function get_answer() {
        $answer = $this->answer;
        return $answer;
    }


    /**
     * Reads answer
     *
     * @return array|null
     * @throws coding_exception
     */
    public function read_answer() {
        $parameter = $this->category . $this->questionid;
        $answer = optional_param($parameter, null, PARAM_RAW);
        if (isset($answer) && $answer != 0) {
            return array('save', $answer);
        } else {
            return array('delete', null);
        }
    }

    /**
     * Returns random answer
     *
     * @return int
     */
    public function create_random_answer() {
        return rand(1, count($this->options));
    }

}