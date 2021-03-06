<?php
/*
 * Encapsulated data of the reviewer allocation for a certain phase / author
 *
 * @var     integer         $phase_nr           phase number
 * @var     integer         $author             author for the allocation
 * @var     array           $reviewers          ids of the allocated reviewers
 * @var     integer         $review_obj         id of the calling review object
 * @var     ilDB            $db                 reference to the ILIAS database
 * @var     ilReviewDBMapper    $mapper         reference to the mapper
 */
class ilReviewerAllocation {
    private $phase_nr;
    private $author;
    private $reviewers;
    private $review_obj;
    private $db;
    private $mapper;

    /*
     * Constructor
     */
    public function __construct(
        $db,
        $mapper,
        $phase_nr = "",
        $author = "",
        $reviewers = array(),
        $review_obj = ""
    ) {
        $this->db = $db;
        $this->mapper = $mapper;
        $this->phase_nr = $phase_nr;
        $this->author = $author;
        $this->reviewers = $reviewers;
        $this->review_obj = $review_obj;
    }

    /*
     * Load the data of an allocation object from the database
     *
     * @param   integer     $phase_nr           phase number
     * @param   integer     $review_obj         review obj
     * @param   integer     $author             author
     *
     * @return  boolean     $success            true: operation performed
     */
    public function loadFromDB($phase_nr, $review_obj, $author) {
        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_alloc "
            . "WHERE phase = %s AND review_obj = %s AND author = %s",
            array("integer", "integer", "integer"),
            array($phase_nr, $review_obj, $author)
        );
        $num_reviewers = reset(
            $this->mapper->getCyclePhases(array("phase_nr" => $phase_nr))
        );
        if ($result->numRows() >= $num_reviewers) {
            while ($record = $this->db->fetchObject($result)) {
                $this->phase_nr = $record->phase;
                $this->author = $record->author;
                $this->review_obj = $record->review_obj;
                $this->reviewers[] = $record->reviewer;
            }
            return true;
        }
        return false;
    }

    /*
     * Store the data of an allocation object to the database
     *
     * @return  boolean     $success            true: operation performed
     */
    public function storeToDB() {
        if ($this->phase_nr == "" || $this->review_obj == "") {
            return false;
        }

        $this->db->manipulateF(
            "DELETE FROM rep_robj_xrev_alloc "
            . "WHERE phase = %s AND review_obj = %s AND author = %s",
            array("integer", "integer", "integer"),
            array($this->phase_nr, $this->review_obj, $this->author)
        );
        foreach ($this->reviewers as $reviewer) {
            $this->db->insert(
                "rep_robj_xrev_alloc",
                array(
                    "phase" => array("integer", $this->phase_nr),
                    "author" => array("integer", $this->author),
                    "review_obj" => array("integer", $this->review_obj),
                    "reviewer" => array("integer", $reviewer)
                )
            );
        }
        $this->mapper->notifyAboutChanges("reviewer_allocations");
        return true;
    }

    /*
     * Delete an allocation phase object from the database
     *
     * @return  boolean     $success            true: operation performed
     */
    public function deleteFromDB() {
        if ($this->phase_nr == "" || $this->review_obj == "") {
            return false;
        }
        $this->db->manipulateF(
            "DELETE FROM rep_robj_xrev_alloc "
            . "WHERE phase = %s AND review_obj = %s",
            array("integer", "integer"),
            array($this->phase_nr, $this->review_obj)
        );
        $this->mapper->notifyAboutChanges("reviewer_allocations");
        return true;
    }

    /*
     * Set the author
     *
     * @param   integer     $author             author
     */
    public function setAuthor($author) {
        $this->author = $author;
    }

    /*
     * Set the reviewers
     *
     * @param   array       $reviewers          reviewers
     */
    public function setReviewers($reviewers) {
        $this->reviewers = $reviewers;
    }

    /*
     * Get the author
     *
     * @return  integer     $author             author
     */
    public function getAuthor() {
        return $this->author;
    }

    /*
     * Get the reviewers
     *
     * @return  array       $reviewers          reviewers
     */
    public function getReviewers() {
        return $this->reviewers;
    }

    /*
     * Get the phase number
     *
     * @return  integer     $phase_nr           phase number
     */
    public function getPhaseNr() {
        return $this->phase_nr;
    }

    /*
     * Get the review object
     *
     * @return  integer     $review_obj         review object
     */
    public function getReviewObj() {
        return $this->review_obj;
    }
}
?>
