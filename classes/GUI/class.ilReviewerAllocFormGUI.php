<?php

/*
 * GUI class for the List of reviewer allocation matrizes
 */
class ilReviewerAllocFormGUI extends ilPropertyFormGUI {

    public function __construct($members, $phases, $parent) {
        global $lng, $ilCtrl;

        parent::__construct();

        $this->setTitle($lng->txt("phase") . " " . $phase->phase);
        $this->setFormAction($ilCtrl->getFormAction($this));


        $member_names = array();
        foreach ($members as $member) {
            $member->name = $member->firstname . ' ' . $member->lastname;
            $member_names[] = $member->name;
        }
        $member_ids = array();
        foreach ($members as $member) {
            $member_ids[] = $member->id;
        }

        foreach ($phases as $phase) {
            $phase_head = new ilFormSectionHeaderGUI();
            $phase_head->setTitle($lng->txt("phase") . " " . $phase->phase);
            $this->addItem($phase_head);
            $reviewer_head = new ilAspectHeadGUI($member_names);
            $this->addItem($reviewer_head);

            foreach ($members as $member) {
                $matrix_row = new ilCheckMatrixRowGUI($phase->phase, $member, $member_ids);
                $this->addItem($matrix_row);
            }
            $nr_input = new ilNumberInputGUI($lng->txt("nr_reviewers"), "nr_" . $phase->phase);
            $nr_input->setMinValue(1);
            $nr_input->setRequired(true);
            $this->addItem($nr_input);


        }

        $this->addCommandButton("saveAllocateReviewers", $lng->txt("save"));
        $this->addCommandButton("addPhase", $lng->txt("add_phase"));
        $this->addCommandButton("removePhase", $lng->txt("remove_phase"));
        // $this->setFormAction($ilCtrl->getFormActionByClass("ilObjReviewGUI", "allocateReviewers"));
    }
}