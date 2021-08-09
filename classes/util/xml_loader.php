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
 * Load the xml-based questions
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');

/**
 * Class mod_groupformation_xml_loader
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_xml_loader {

    /**
     * Returns an array with version and number of answers
     *
     * @param string $category
     * @param string $lang
     * @return array
     * @throws Exception
     */
    public function save($category, $lang) {
        global $CFG;
        $xmlfile = $CFG->dirroot . '/mod/groupformation/xml_question/' . $lang . '_' . $category . '.xml';

        $return = array();
        $questions = array();

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);
            $instruction = trim($xml->INSTRUCTION);
            if ($instruction == ""){
                $instruction = NULL;
            }
            $v = trim($xml->QUESTIONS['VERSION']);
            $return[] = trim($xml->QUESTIONS['VERSION']);
            $numbers = 0;

            foreach ($xml->QUESTIONS->QUESTION as $question) {
                $numbers++;

                $data = new stdClass ();
                $data->category = $category;
                $data->questionid = trim($question['ID']);
                $data->type = trim($question['TYPE']);
                $data->question = trim($question->QUESTIONTEXT);
                $data->optionmax = 0;
                $data->options = "";
                $data->language = $lang;
                $data->position = $numbers;
                $data->version = $v;
                $options = $question->OPTIONS;

                if ($options->count() > 0) {

                    $optionsarray = array();
                    foreach ($options->children() as $key => $option) {
                        if ($key == 'OPTION') {
                            $optionsarray[] = trim($option);
                        } else {
                            $optionsarray[$key] = trim($option);
                        }
                    }
                    $data->options = groupformation_convert_options($optionsarray);
                    $data->optionmax = count($optionsarray);
                }

                $questions[] = $data;
            }

            $return[] = $numbers;
            $return[] = $questions;

            return [$instruction, $return];

        } else {
            throw new Exception("The file $xmlfile cannot be opened or found.");
        }

    }
}
