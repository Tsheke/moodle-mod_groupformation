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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// TODO noch nicht getestet
// defined('MOODLE_INTERNAL') || die(); -> template
// namespace mod_groupformation\classes\lecturer_settings;
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

// require_once 'storage_manager.php';
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
class mod_groupformation_criterion_calculator {
	private $store;
	private $user_manager;
	private $groupformationid;
	
	// Extraversion | Gewissenhaftigkeit | Vertr�glichkeit | Neurotizismus | Offenheit
	// 12 14 8 17 15 16
	private $BIG5 = array (
			array (
					6 
			),
			array (
					8 
			),
			array (
					2,
					11 
			),
			array (
					9 
			),
			array (
					10 
			) 
	);
	// -7 -9 -13 -10 -11
	private $BIG5Invert = array (
			array (
					1 
			),
			array (
					3 
			),
			array (
					7 
			),
			array (
					4 
			),
			array (
					5 
			) 
	);
	private $BIG5Homogen = array (
			1,
			2 
	);
	// Herausforderung | Interesse | Ergolgswahrscheinlichkeit | Misserfolgsbef�rchtung
	private $FAM = array (
			array (
					6,
					8,
					10,
					15,
					17 
			),
			array (
					1,
					4,
					7,
					11 
			),
			array (
					2,
					3,
					13,
					14 
			),
			array (
					5,
					9,
					12,
					16,
					18 
			) 
	);
	// Konkrete Erfahrung | Aktives Experimentieren | Reflektierte Beobachtung | Abstrakte Begriffsbildung
	private $LEARN = array (
			array (
					1,
					5,
					11,
					14,
					20,
					22 
			),
			array (
					2,
					8,
					10,
					16,
					17,
					23 
			),
			array (
					3,
					6,
					9,
					13,
					19,
					21 
			),
			array (
					4,
					7,
					12,
					15,
					18,
					24 
			) 
	);
	
	/**
	 *
	 * @param unknown $groupformationid        	
	 */
	public function __construct($groupformationid) {
		$this->groupformationid = $groupformationid;
		$this->store = new mod_groupformation_storage_manager ( $groupformationid );
		$this->user_manager = new mod_groupformation_user_manager ( $groupformationid );
	}
	
	/**
	 *
	 * @param number $questionid        	
	 * @param string $category        	
	 * @param number $answer        	
	 * @return number
	 */
	private function invert_answer($questionid, $category, $answer) {
		$max = $this->store->get_max_option_of_catalog_question ( $questionid, $category );
		// because internally we start with 0 instead of 1
		return $max + 1 - $answer;
	}
	
	/**
	 * Determines values in category 'general' chosen by user
	 *
	 * @param number $userid        	
	 * @return string
	 */
	public function get_general_values($userid) {
		$value = $this->user_manager->get_single_answer ( $userid, 'general', 1 );
		
		// array(x,y) with x = ENGLISH and y = GERMAN
		if ($value == 1) {
			$values = array (
					1.0,
					0.0 
			);
		} elseif ($value == 2) {
			$values = array (
					0.0,
					1.0 
			);
		} elseif ($value == 3) {
			$values = array (
					1.0,
					0.5 
			);
		} elseif ($value == 4) {
			$values = array (
					0.5,
					1.0 
			);
		}
		return $values;
	}
	
	/**
	 * Determines all answers for knowledge given by the user
	 *
	 * returns an array of arrays with
	 * position_0 -> knowledge area
	 * position_1 -> answer
	 *
	 * @param int $userid        	
	 * @return multitype:multitype:mixed float
	 */
	public function knowledge_all($userid) {
		$knowledge_values = array ();
		$option_number = 0;
		
		$temp = $this->store->get_knowledge_or_topic_values ( 'knowledge' );
		$xml_content = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
		$options = mod_groupformation_util::xml_to_array ( $xml_content );
		
		foreach ( $options as $option ) {
			$value = floatval ( $this->user_manager->get_single_answer ( $userid, 'knowledge', $option_number ) );
			$knowledge_values [] = $value / 100.0;
			$option_number ++;
		}
		return $knowledge_values;
	}
	
	/**
	 * Determines the average of the answers of the user in the category knowledge
	 *
	 * @param int $userid        	
	 * @return float
	 */
	public function knowledge_average($userid) {
		$total = 0;
		$answers = $this->user_manager->get_answers ( $userid, 'knowledge' );
		$number_of_questions = count ( $answers );
		foreach ( $answers as $answer ) {
			$total = $total + $answer->answer;
		}
		
		if ($number_of_questions != 0) {
			$temp = floatval ( $total ) / ($number_of_questions);
			return floatval ( $temp ) / 100;
		} else {
			return 0.0;
		}
	}
	
	/**
	 * Returns the answer of the n-th grade question
	 *
	 * @param int $questionid        	
	 * @param int $userid        	
	 * @return float
	 */
	public function get_grade($questionid, $userid) {
		$answer = $this->user_manager->get_single_answer ( $userid, 'grade', $questionid );
		return floatval ( $answer / $this->store->get_max_option_of_catalog_question ( $questionid ) );
	}
	
	/**
	 * Returns the answer of the n-th grade question
	 *
	 * @param int $position        	
	 * @param int $userid        	
	 * @return float
	 */
	public function get_points($position, $userid) {
		$max = $this->store->get_max_points ();
		$answer = $this->user_manager->get_single_answer ( $userid, 'points', $position );
		return floatval ( $answer / $max );
	}
	
	/**
	 * Returns the position of the question, which is needed for the grade criterion
	 *
	 * $users are the ids for the variance calculation
	 *
	 * @param unknown $users        	
	 * @return number
	 */
	public function get_grade_position($users) {
		$variance = 0;
		$position = 1;
		$total = 0;
		$totalOptions = 0;
		
		// iterates over three grade questions
		for($i = 1; $i <= 3; $i ++) {
			
			// answers for catalog question in category 'grade'
			$answers = $this->store->get_answers_to_special_question ( 'grade', $i );
			
			// number of options for catalog question
			$totalOptions = $this->store->get_max_option_of_catalog_question ( $i, 'grade' );
			
			//
			$dist = $this->get_initial_array ( $totalOptions );
			
			// iterates over answers for grade questions
			foreach ( $answers as $answer ) {
				
				// checks if answer is relevant for this group of users
				if (in_array ( $answer->userid, $users )) {
					
					// increments count for answer option
					$dist [($answer->answer) - 1] ++;
					
					// increments count for total
					if ($i == 1) {
						$total ++;
					}
				}
			}
			
			// computes tempE for later use
			$tempE = 0;
			$p = 1;
			foreach ( $dist as $d ) {
				$tempE = $tempE + ($p * ($d / $total));
				$p ++;
			}
			
			// computes tempV to find maximal variance
			$temp_variance = 0;
			$p = 1;
			foreach ( $dist as $d ) {
				$temp_variance = $temp_variance + ((pow ( ($p - $tempE), 2 )) * ($d / $total));
				$p ++;
			}
			
			// sets position by maximal variance
			if ($variance < $temp_variance) {
				$variance = $temp_variance;
				$position = $i;
			}
		}
		
		return $position;
	}
	
	/**
	 * Returns the position of the question, which is needed for the points criterion
	 *
	 * $users are the ids for the variance calculation
	 *
	 * @param unknown $users        	
	 * @return number
	 */
	public function get_points_position($users) {
		$variance = 0;
		$position = 1;
		$total = 0;
		$totalOptions = 0;
		
		// iterates over three grade questions
		for($i = 1; $i <= $this->store->get_number ( 'points' ); $i ++) {
			
			// answers for catalog question in category 'grade'
			$answers = $this->store->get_answers_to_special_question ( 'points', $i );
			
			$min_value = 0;
			$max_value = $this->store->get_max_points ();
			
			// number of options for catalog question
			$totalOptions = $this->store->get_max_option_of_catalog_question ( $i, 'points' );
			
			//
			$dist = $this->get_initial_array ( $totalOptions );
			
			// iterates over answers for grade questions
			foreach ( $answers as $answer ) {
				
				// checks if answer is relevant for this group of users
				if (in_array ( $answer->userid, $users )) {
					
					// increments count for answer option
					$dist [($answer->answer) - 1] ++;
					
					// increments count for total
					if ($i == 1) {
						$total ++;
					}
				}
			}
			
			// computes tempE for later use
			$tempE = 0;
			$p = 1;
			foreach ( $dist as $d ) {
				$tempE = $tempE + ($p * ($d / $total));
				$p ++;
			}
			
			// computes tempV to find maximal variance
			$temp_variance = 0;
			$p = 1;
			foreach ( $dist as $d ) {
				$temp_variance = $temp_variance + ((pow ( ($p - $tempE), 2 )) * ($d / $total));
				$p ++;
			}
			
			// sets position by maximal variance
			if ($variance < $temp_variance) {
				$variance = $temp_variance;
				$position = $i;
			}
		}
		
		return $position;
	}
	
	/**
	 * returns an array with n = $total fields
	 *
	 * @param unknown $total        	
	 * @return multitype:array
	 */
	private function get_initial_array($total) {
		$array = array ();
		for($i = 0; $i < $total; $i ++) {
			$array [] = 0;
		}
		return $array;
	}
	
	/**
	 * returns the Big 5 by user
	 *
	 * @param unknown $userid        	
	 * @return multitype:array
	 */
	public function get_big_5($userid) {
		$array = array ();
		$heterogen = array ();
		$homogen = array ();
		$category = 'character';
		
		$count = count ( $this->BIG5 );
		$scenario = $this->store->get_scenario ();
		if ($scenario == 2) {
			$count = $count - 2;
		}
		for($i = 0; $i < $count; $i ++) {
			$temp = 0;
			$max_value = 0;
			foreach ( $this->BIG5 [$i] as $num ) {
				$temp = $temp + $this->user_manager->get_single_answer ( $userid, $category, $num );
				$max_value = $max_value + $this->store->get_max_option_of_catalog_question ( $num, $category );
			}
			foreach ( $this->BIG5Invert [$i] as $num ) {
				$temp = $temp + $this->invert_answer ( $num, $category, $this->user_manager->get_single_answer ( $userid, $category, $num ) );
				$max_value = $max_value + $this->store->get_max_option_of_catalog_question ( $num, $category );
			}
			if (in_array ( $i, $this->BIG5Homogen )) {
				$homogen [] = floatval ( $temp ) / ($max_value);
			} else {
				$heterogen [] = floatval ( $temp ) / ($max_value);
			}
		}
		
		$array [] = $heterogen;
		$array [] = $homogen;
		return $array;
	}
	
	/**
	 * returns the FAM (motivation criterion) of the user specified by �userId
	 *
	 *
	 * @param unknown $userid        	
	 * @return multitype:array
	 */
	public function get_fam($userid) {
		$array = array ();
		$category = 'motivation';
		
		$count = count ( $this->FAM );
		for($i = 0; $i < $count; $i ++) {
			$temp = 0;
			$max_value = 0;
			foreach ( $this->FAM [$i] as $num ) {
				$temp = $temp + $this->user_manager->get_single_answer ( $userid, $category, $num );
				$max_value = $max_value + $this->store->get_max_option_of_catalog_question ( $num, $category );
			}
			$array [] = floatval ( $temp ) / ($max_value);
		}
		
		return $array;
	}
	
	/**
	 * returns the learning criterion of the user specified by �userId
	 *
	 * @param unknown $userid        	
	 * @return multitype:array
	 */
	public function get_learn($userid) {
		$array = array ();
		$category = 'learning';
		
		$count = count ( $this->LEARN );
		for($i = 0; $i < $count; $i ++) {
			$temp = 0;
			$max_value = 0;
			foreach ( $this->LEARN [$i] as $num ) {
				$temp = $temp + $this->user_manager->get_single_answer ( $userid, $category, $num );
				$max_value = $max_value + $this->store->get_max_option_of_catalog_question ( $num, $category );
			}
			$array [] = floatval ( $temp ) / ($max_value);
		}
		
		return $array;
	}
	
	/**
	 * returns the team (Teamorientierung) criterion of the user specified by �userId
	 *
	 *
	 * @param unknown $userid        	
	 * @return multitype:number // later on this will be an array
	 */
	public function get_team($userid) {
		$total = 0.0;
		$max_value = 0.0;
		$array = array ();
		$answers = $this->user_manager->get_answers ( $userid, 'team' );
		$number_of_answers = count ( $answers );
		foreach ( $answers as $answer ) {
			$total = $total + $answer->answer;
			$max_value = $max_value + $this->store->get_max_option_of_catalog_question ( $number_of_answers, 'team' );
		}
		
		if ($number_of_answers != 0) {
			$temp = $total / $number_of_answers;
			$temp_total = $max_value / $number_of_answers;
			$array [] = floatval ( $temp / $temp_total );
		} else {
			$array [] = 0.0;
		}
		
		return $array;
	}
	
	/**
	 * Returns topic answers as a criterion
	 *
	 * @param number $userid        	
	 * @return TopicCriterion
	 */
	public function get_topic($userid) {
		$choices = $this->user_manager->get_answers ( $userid, 'topic', 'questionid', 'answer' );
		
		return new TopicCriterion ( array_keys ( $choices ) );
	}
}