<?php

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');

class mod_groupformation_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_answers_parameters() {
        return new external_function_parameters(
                array(
                        'users' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'userid' => new external_value(PARAM_INT, 'id of user'),
                                                'groupformation' => new external_value(PARAM_TEXT,
                                                        'id of groupformation')
                                        )
                                )
                        )
                )
        );
    }

    public static function delete_answers_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'id' => new external_value(PARAM_INT, 'user id'),
                                'groupformation' => new external_value(PARAM_INT, 'id of groupformation')
                        )
                )
        );
    }

    /**
     * delete answers, set the new answer count and change complete to false
     *
     * @param array $user array of group description arrays (with keys groupname and courseid)
     */
    public static function delete_answers($users) {
        $params = self::validate_parameters(self::delete_answers_parameters(), array('users' => $users));

        foreach ($params['users'] as $user) {
            $user = (object) $user;

            $groupformationid = $user->groupformation;
            $userid = $user->userid;

            $usermanager = new mod_groupformation_user_manager($groupformationid);
            $usermanager->delete_answers($userid, true);
            // set new answer count
            $usermanager->set_answer_count($userid);
            // set completed to false because answered were deleted
            $usermanager->set_complete($userid, 0);

            return array("users" => array("id" => $user->userid, "groupformation" => $user->groupformation));
        }
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function exclude_users_parameters() {
        return new external_function_parameters(
                array(
                        'users' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'userid' => new external_value(PARAM_INT, 'id of user'),
                                                'groupformation' => new external_value(PARAM_TEXT,
                                                        'id of groupformation')
                                        )
                                )
                        )
                )
        );
    }

    public static function exclude_users_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'id' => new external_value(PARAM_INT, 'user id'),
                                'groupformation' => new external_value(PARAM_INT, 'id of groupformation')
                        )
                )
        );
    }

    /**
     * exclude users from questionaire
     *
     * @param array $user array of group description arrays (with keys groupname and courseid)
     */
    public static function exclude_users($users) {
        $params = self::validate_parameters(self::exclude_users_parameters(), array('users' => $users));

        foreach ($params['users'] as $user) {
            $user = (object) $user;

            $groupformationid = $user->groupformation;
            $userid = $user->userid;

            $usermanager = new mod_groupformation_user_manager($groupformationid);
            $usermanager->set_excluded($userid, 1);

            return array("users" => array("id" => $user->userid, "groupformation" => $user->groupformation));
        }
    }

}