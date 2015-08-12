<?php
/**
 * Created by PhpStorm.
 * User: eduardgallwas
 * Date: 09.07.15
 * Time: 09:59
 */
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/submit_infos.php');
class mod_groupformation_analysis_controller {
	private $groupformationID;
	
	// state of the controller
	// private $viewState = 0;
	private $store = NULL;
	private $view = NULL;
	private $questionnaire_status;
	private $activity_time;
	private $start_time;
	private $end_time;
	private $time_now;
	// private $activity_status_info;
	// private $activity_status_info_extend;
	private $analyse_infos = NULL;
	private $test;
	private $state;
	/**
	 * Creates instance of analysis controller
	 *
	 * @param int $groupformationID        	
	 */
	public function __construct($groupformationID) {
		$this->groupformationID = $groupformationID;
		$this->store = new mod_groupformation_storage_manager ( $groupformationID );
		$this->view = new mod_groupformation_template_builder ();
		$this->determineStatus ();
		
		$this->analyse_infos = new mod_groupformation_submit_infos ( $groupformationID );
		
		/*
		 * if(($start == 0) && ($end == 0) && ($this->survey_status == true )){
		 * // die Aktivität ist die ganze zeit verfügbar und muss manuel beendet werden
		 * $this->viewState = 0;
		 *
		 *
		 * $this->test = get_string ( 'questionaire_availability_info_future', 'groupformation', $this->activity_time );
		 * } elseif (($start > $this->time_now ) && ($end == 0)) {
		 * // die Aktivität startet am ... und läuft bis sie manuel beendet wird
		 * $this->viewState = 1;
		 *
		 * $this->activity_status_info = 'test1';
		 *
		 * } elseif (($start > $this->time_now) && !($end == 0)) {
		 * // die Aktivität startet am ... und endet am ...
		 * $this->viewState = 2;
		 *
		 * $this->activity_status_info = 'test2';
		 *
		 * } elseif (($start < $this->time_now) && ($end == 0)) {
		 * // die Aktivität läuft bereits seit... und muss manuel gestoppt werden
		 * $this->viewState = 3;
		 *
		 * $this->activity_status_info = 'test3';
		 *
		 * } elseif(($start < $this->time_now) && ($end > $this->time_now)) {
		 * // die Aktivität läuft bereits seit .. und endet am ..
		 * $this->viewState = 4;
		 *
		 * $this->activity_status_info = 'test4';
		 *
		 * } elseif(($end < $this->time_now)) {
		 * // die Aktivität wurde am ... beendet
		 * $this->viewState = 5;
		 *
		 * $this->activity_status_info = 'test5';
		 * }
		 */
	}
	
	/**
	 * Sets start time of questionnaire to now
	 */
	public function start_questionnaire() {
		$this->store->open_questionnaire ();
	}
	
	/**
	 * Sets end time of questionnaire to now
	 */
	public function stop_questionnaire() {
		$this->store->close_questionnaire ();
	}
	
	/**
	 * Loads status
	 *
	 * @return string
	 */
	private function load_status() {
		$statusAnalysisView = new mod_groupformation_template_builder ();
		$statusAnalysisView->setTemplate ( 'analysis_status' );
		
		$this->questionnaire_status = $this->store->isQuestionaireAvailable ();
		
		$this->activity_time = $this->store->getTime ();
		
		if (intval ( $this->activity_time ['start_raw'] ) == 0) {
			$this->start_time = 'Kein Zeitpunkt festgelegt';
		} else {
			$this->start_time = $this->activity_time ['start'];
		}
		
		if (intval ( $this->activity_time ['end_raw'] ) == 0) {
			$this->end_time = 'Kein Zeitpunkt festgelegt';
		} else {
			$this->end_time = $this->activity_time ['end'];
		}
		
		if ($this->questionnaire_status == true) {
			if ($this->job_state !== "ready") {
				$statusAnalysisView->assign ( 'button', array (
						'type' => 'submit',
						'name' => 'stop_questionnaire',
						'value' => '',
						'state' => 'disabled',
						'text' => 'Aktivität beenden' 
				) );
			} else {
				$statusAnalysisView->assign ( 'button', array (
						'type' => 'submit',
						'name' => 'stop_questionnaire',
						'value' => '',
						'state' => '',
						'text' => 'Aktivität beenden' 
				) );
			}
		} elseif ($this->questionnaire_status == false) {
			
			if ($this->job_state !== "ready") {
				$statusAnalysisView->assign ( 'button', array (
						'type' => 'submit',
						'name' => 'stop_questionnaire',
						'value' => '',
						'state' => 'disabled',
						'text' => 'Aktivität starten' 
				) );
			} else {
				$statusAnalysisView->assign ( 'button', array (
						'type' => 'submit',
						'name' => 'start_questionnaire',
						'value' => '',
						'state' => '',
						'text' => 'Aktivität starten' 
				) );
			}
		}  // zusätzlich schauen, ob Gruppenbildung bereits gestartet, dann button disablen
else {
		}
		
		$info_teacher = mod_groupformation_util::get_info_text_for_teacher ( false, "analysis" );
		
		$statusAnalysisView->assign ( 'info_teacher', $info_teacher );
		$statusAnalysisView->assign ( 'analysis_time_start', $this->start_time );
		$statusAnalysisView->assign ( 'analysis_time_end', $this->end_time );
		
		switch ($this->state) {
			case 1 :
				$statusAnalysisView->assign ( 'analysis_status_info', 'Sie müssen die Aktivität beenden, bevor sie Gruppen bilden können.' );
				break;
			case 2 :
				$statusAnalysisView->assign ( 'analysis_status_info', 'Sie müssen die Aktivität starten, damit Studierende den Fragebogen beantworten können.' );
				break;
			case 3 :
				$statusAnalysisView->assign ( 'analysis_status_info', 'Die Gruppenbildung wurde bereits angestoßen bzw. durchgeführt. Die Aktivität kann nicht mehr gestartet werden' );
				break;
			default :
				$statusAnalysisView->assign ( 'analysis_status_info', 'Useful magic text' );
		}
		
		return $statusAnalysisView->loadTemplate ();
	}
	private function load_statistics() {
		global $PAGE;
		
		$questionnaire_StatisticNumbers = $this->analyse_infos->getInfos ();
		
		$statisticsAnalysisView = new mod_groupformation_template_builder ();
		$statisticsAnalysisView->setTemplate ( 'analysis_statistics' );
		$context = $PAGE->context;
		$count = count ( get_enrolled_users ( $context, 'mod/groupformation:onlystudent' ) );
		
		$statisticsAnalysisView->assign ( 'statistics_enrolled', $count );
		$statisticsAnalysisView->assign ( 'statistics_processed', $questionnaire_StatisticNumbers [0] );
		$statisticsAnalysisView->assign ( 'statistics_submited', $questionnaire_StatisticNumbers [1] );
		$statisticsAnalysisView->assign ( 'statistics_submited_incomplete', $questionnaire_StatisticNumbers [2] );
		$statisticsAnalysisView->assign ( 'statistics_submited_complete', $questionnaire_StatisticNumbers [3] );
		
		return $statisticsAnalysisView->loadTemplate ();
	}
	public function display() {
		$this->view->setTemplate ( 'wrapper_analysis' );
		$this->view->assign ( 'analysis_name', $this->store->getName () );
		$this->view->assign ( 'analysis_status_template', $this->load_status () );
		$this->view->assign ( 'analysis_statistics_template', $this->load_statistics () );
		return $this->view->loadTemplate ();
	}
	public function determineStatus() {
		$this->questionnaire_status = $this->store->isQuestionaireAvailable ();
		$this->state = 1;
		$this->job_state = mod_groupformation_job_manager::get_status ( mod_groupformation_job_manager::get_job ( $this->groupformationID ) );
		if ($this->job_state !== 'ready') {
			$this->state = 3;
		} elseif ($this->questionnaire_status) {
			$this->state = 1;
		} else {
			$this->state = 2;
		}
	}
	
	// echo '<div class="questionaire_status">' . get_string ( 'questionaire_not_available', 'groupformation', $a ) . '</div>';
	// echo '<div class="questionaire_status">' . get_string ( 'questionaire_availability_info_future', 'groupformation', $a ) . '</div>';
	// echo '<div class="questionaire_status">' . get_string ( 'questionaire_not_available', 'groupformation', $a ) . '</div>';
	// echo '<div class="questionair_status">' . get_string ( 'questionaire_availability_info_from', 'groupformation', $a ) . '</div>';
}