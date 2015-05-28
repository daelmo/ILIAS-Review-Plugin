<?php
require_once 'class.ilReviewForm.php';
require_once 'class.ilCycleQuestion.php';

/*
 * Database abstraction class for access of ilDB
 *
 * @var     ilDB        $db                 local reference to the database
 * @var     integer     $obj_id             id of the calling review object
 * @var     array       $review_forms       review form objects
 * @var     array       $cycle_questions    objects of review cycle questions
 */
class ilReviewDBMapper {
    private $obj_id;
    private $db;
    private $review_forms;
    private $cycle_questions;

    /*
     * Constructor
     */
    public function __construct($review_obj) {
        global $ilDB;

        $this->obj_id = $review_obj;
        $this->db = $ilDB;
        $this->review_forms = array();
        $this->cycle_questions = array();
    }

    /*
     * Load all review form objects that belong to the calling review object
     */
    private function loadReviewForms() {
        $result = $this->db->queryF(
            "SELECT id FROM rep_robj_xrev_revi WHERE review_obj = %s",
            array("integer"),
            array($this->obj_id)
        );
        while ($record = $this->db->fetchObject($result)) {
            $review_form = new ilReviewForm($this->db, $this);
            $review_form->loadFromDB($record->id);
            $this->review_forms[] = $review_form;
        }
    }

    /*
     * Load all question objects of questions in the review cycle that belong
     * to the calling review object
     */
    private function loadCycleQuestions() {
        $result = $this->db->queryF(
            "SELECT id FROM rep_robj_xrev_quest WHERE review_obj = %s",
            array("integer"),
            array($this->obj_id)
        );
        while ($record = $this->db->fetchObject($result)) {
            $cycle_question = new ilCycleQuestion($this->db, $this);
            $cycle_question->loadFromDB($record->id);
            $this->cycle_questions[] = $cycle_question;
        }
    }

    /*
     * Get an array of review forms that meet certain conditions
     *
     * @param   array   $conditions     attribute => value
     *
     * @return  array   $result         ilReviewForm objects
     */
    public function getReviewForms($conditions) {
        if (count($this->review_forms) == 0) {
            $this->loadReviewForms();
        }
        return array_filter(
            $this->review_forms,
            function($review_form) use ($conditions) {
                foreach ($conditions as $attribute => $value) {
                    $call = $this->toGetter($attribute);
                    if ($review_form->{$call}() != $value) {
                        return false;
                    }
                }
                return true;
            }
        );
    }

    /*
     * Get an array of cycle questions that meet certain conditions
     *
     * @param   array   $conditions     attribute => value
     *
     * @return  array   $result         ilCycleQuestion objects
     */
    public function getCycleQuestions($conditions) {
        if (count($this->cycle_questions) == 0) {
            $this->loadCycleQuestions();
        }
        return array_filter(
            $this->cycle_questions,
            function($cycle_question) use ($conditions) {
                foreach ($conditions as $attribute => $value) {
                    $call = $this->toGetter($attribute);
                    if ($cycle_question->{$call}() != $value) {
                        return false;
                    }
                }
                return true;
            }
        );

    }

    /*
     * Transform an attribute to the corresponding getter method
     *
     * @param   string  $attribute      attribute name
     *
     * @return  string  $getter         getter method name
     */
    private function toGetter($attribute) {
        $parts = explode("_", $attribute);
        $getter = "get";
        foreach ($parts as $part) {
            $getter .= ucfirst($part);
        }
        return $getter;
    }

    /*
     * Destroy an array of objects loaded from the database because some
     * objects where changed and it is not coherent anymore
     *
     * @param   string  $objects        name of the array
     */
    public function notifyAboutChanges($objects) {
        $this->$objects = array();
    }
}
?>
