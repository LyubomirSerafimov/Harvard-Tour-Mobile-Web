<?php

class CalendarCourseContent extends CourseContent
{
    protected $contentType = 'calendar';
    protected $date;
    
    public function setDate(DateTime $date) {
        $this->date = $date;
    }

    public function getDate() {
        return $this->date;
    }
    
    public function getDateTime() {
        if ($date = $this->getDate()) {
            return $data->format('U');
        }
        
        return 0;
    }

    public function sortBy(){
        $sortBy = parent::sortBy();
        if($this->getDate()){
            $sortBy = $this->getDueDate()->format('U');
        }
        return $sortBy;
    }
}
