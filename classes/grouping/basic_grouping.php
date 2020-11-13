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
 * Basic grouping interface
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/grouping.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');

/**
 * Class mod_groupformation_basic_grouping
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_basic_grouping extends mod_groupformation_grouping {

    /** @var int ID of module instance */
    public $groupformationid;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store;

    /** @var mod_groupformation_groups_manager The manager of groups data */
    private $groupsmanager;

    /** @var mod_groupformation_criterion_calculator The calculator for criteria */
    private $criterioncalculator;

    /**
     * mod_groupformation_basic_grouping constructor.
     *
     * @param int $groupformationid
     * @throws dml_exception
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->usermanager = new mod_groupformation_user_manager($groupformationid);
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager($groupformationid);
        $this->criterioncalculator = new mod_groupformation_criterion_calculator($groupformationid);
        $this->participantparser = new mod_groupformation_participant_parser($groupformationid);
    }

    /**
     * Scientific division of users and creation of participants
     *
     * @param array $users Two parted array - first part is all groupal users, second part are all random users
     * @return array
     * @throws dml_exception
     */
    public function run_grouping($users) {
        $configurations = array(
                "groupal:1" => array(),
        );

        $weights = $this->get_weights(true);
        $configurationkeys = array_keys($configurations);

        // Here only 1 slice (groupal:1).
        $numberofslices = count($configurationkeys);

        $groupsizes = $this->store->determine_group_size($users);

        $cohorts = array();
        if (count($users[0]) >= $numberofslices) {

            // Divide users into n slices.
            $slices = $this->slicing($users[0], $numberofslices);

            for ($i = 0; $i < $numberofslices; $i++) {
                $slice = $slices[$i];
                $configurationkey = $configurationkeys[$i];
                $rawparticipants = $this->participantparser->build_participants($slice, null, $weights);
                $participants = $rawparticipants;
                $cohorts[$configurationkey] = $this->build_cohort($participants, $groupsizes[0], $configurationkey);
            }
        }

        // Handle all users with incomplete or no questionnaire submission.
        $randomkey = "random:1";
        $randomcohort = null;

        if (count($users[1]) > 0) {

            $randomparticipants = $this->participantparser->build_empty_participants($users[1]);
            $randomcohort = $this->build_cohort($randomparticipants, $groupsizes[1], $randomkey);

        }

        $cohorts[$randomkey] = $randomcohort;

        return $cohorts;
    }

    /**
     * Get weigth of binquestion from database
     * and set weight for other criterion to 1
     *
     * @param boolean $extended
     * @return array
     */
    public function get_weights($extended = false) {
        $weights = [];
        $labels = $this->store->get_label_set($extended);
        foreach ($labels as $label) {
            if ($label == "binquestion") {
                $result = $this->store->get_weights();
                $weights[$label] = $result->binquestionimportance;
            } else {
                $weights[$label] = 1;
            }
        }
        return $weights;
    }

}