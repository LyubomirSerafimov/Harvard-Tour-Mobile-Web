<?php

class TestCourseCatalogDataRetriever extends URLDataRetriever implements CourseCatalogDataRetriever
{
    protected $areasParser;
    protected $areasURL;
    protected $coursesParser;
    protected $coursesURL;
    
    protected function setMode($mode) {
        $parserVar = $mode . 'Parser';
        $urlVar = $mode . 'URL';
        $this->setParser($this->$parserVar);
        $this->setBaseURL($this->$urlVar);
    }

    public function searchCourses($searchTerms, $options = array()) {
        if(isset($options['area'])) {
            // retrieve specified area courses
            $area = $this->getCatalogArea($options['area'], $options);
            $courses = array();
            if ($areas = $area->getAreas()) {
	            foreach($areas as $area) {
	                $options['area'] = $area->getCode();
	                $areaCourses = $this->getCourses($options);
	                $courses = array_merge($courses, $areaCourses);
	            }
            } else {
            	$options['area'] = $options['area'];
            	$courses = $this->getCourses($options);
            } 
        }else {
            // retrive all area courses
            $courses = $this->getCourses($options);
        }
        $items = array();
        $filters = explode(" ", trim($searchTerms));
        foreach($courses as $course) {
            if($course->filterItem($filters)) {
                $items[] = $course;
            }
        }
        return $items;
    }
    
    public function getCourses($options = array()) {
        $this->setMode('courses');
        if (isset($options['area'])) {
            $this->setOption('area', $options['area']);
        }

        if (isset($options['term'])) {
            $this->setOption('term', $options['term']);
        }
            
        $courses = $this->getData();
        return $courses;
    }
    
    public function getCatalogArea($area, $options = array()) {
        
        $this->setMode('areas');
        $this->setOption('area', $area);
        if (isset($options['term'])) {
            $this->setOption('term', $options['term']);
        }
        $area = $this->getData();
        return $area;
    }
    
    public function getCatalogAreas($options = array()) {
        $this->setMode('areas');
        if (isset($options['term'])) {
            $this->setOption('term', $options['term']);
        }
        $areas =  $this->getData();
        return $areas;
    }
    
    public function getAvailableTerms() {
        
    }
    
    public function getCourseByCommonId($courseID, $options) {
        $this->setMode('courses');
        $this->setOption('courseID', $courseID);
        $this->setOptions($options);
        return $this->getData();
    }
    
    public function getCourseById($courseNumber) {
        if ($course = $this->getCourses(array('courseNumber' => $courseNumber))) {
            return current($course);
        }
        return false;
    }
    
    protected function init($args) {
        parent::init($args);
        $this->areasParser = DataParser::factory('CourseAreasXMLDataParser', $args);
        $this->coursesParser = DataParser::factory('CoursesXMLDataParser', $args);
        if (!isset($args['COURSES_BASE_URL'])) {
            throw new KurogoConfigurationException("COURSES_BASE_URL not set");
        }
        $this->coursesURL = $args['COURSES_BASE_URL'];

        if (!isset($args['AREAS_BASE_URL'])) {
            throw new KurogoConfigurationException("AREAS_BASE_URL not set");
        }
        $this->areasURL = $args['AREAS_BASE_URL'];
    }
}
