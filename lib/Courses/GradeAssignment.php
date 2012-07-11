<?php

/*
 * Copyright © 2010 - 2012 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class GradeAssignment {
    protected $id;
    protected $title;
    protected $description;
    protected $possiblePoints;
    protected $dateCreated;
    protected $dateModified;
    protected $dueDate;
    protected $gradeScore;
    protected $attributes = array();

    public function getId()
    {
        return $this->id;
    }

    public function setId($newId)
    {
        $this->id = $newId;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($newTitle)
    {
        $this->title = $newTitle;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getPossiblePoints()
    {
        return $this->possiblePoints;
    }

    public function setPossiblePoints($newPossiblePoints)
    {
        $this->possiblePoints = $newPossiblePoints;
        return $this;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = new DateTime('@'.$dateCreated);
        return $this;
    }

    public function getDateModified()
    {
        return $this->dateModified;
    }

    public function setDateModified($dateModified)
    {
        $this->dateModified = new DateTime('@'.$dateModified);
        return $this;
    }

    public function getDueDate()
    {
        return $this->dueDate;
    }

    public function setDueDate($newDueDate)
    {
        $this->dueDate = $newDueDate;
        return $this;
    }

    public function getGrade(){
        return $this->gradeScore;
    }

    public function addGradeScore(GradeScore $gradeScore){
        $this->gradeScore = $gradeScore;
    }

    public function setAttributes($attribs) {
        if (is_array($attribs)) {
            $this->attributes = $attribs;
        }
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }

    public function getAttribute($attrib) {
        return isset($this->attributes[$attrib]) ? $this->attributes[$attrib] : '';
    }
}
