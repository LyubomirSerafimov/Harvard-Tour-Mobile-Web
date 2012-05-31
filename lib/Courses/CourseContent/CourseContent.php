<?php

abstract class CourseContent implements KurogoObject {

    protected $id;
    protected $courseID;
    protected $contentRetriever;
    protected $contentCourse = null;
    protected $contentType;
    protected $title;
    protected $description;
    protected $authorID;
    protected $author;
    protected $publishedDate;
    protected $endDate;
    protected $priority;
    protected $viewMode;
    protected $downloadMode;
    protected $attributes=array();
    const MODE_PAGE = 1;
    const MODE_DOWNLOAD = 2;
    const MODE_URL = 3;

    public function filterItem($filters) {
        return true;
    }

    public function getGUID() {
        if ($this->id) {
            return $this->id;
        } elseif ($this->getUrl()) {
            return $this->getUrl();
        }
    }

    public function getSubTitle() {
        return '';
    }

    public function setID($id) {
        $this->id = $id;
    }

    public function getID() {
        return $this->id;
    }

    public function setCourseID($id) {
        $this->courseID = $id;
    }

    public function getCourseID() {
        return $this->courseID;
    }

    public function setContentRetriever(CourseContentDataRetriever $retriever) {
        $this->contentRetriever = $retriever;
    }

    public function getContentRetriever() {
        return $this->contentRetriever;
    }

    public function setContentCourse(CourseContentCourse $contentCourse) {
        $this->contentCourse = $contentCourse;
    }

    public function getContentCourse() {
        //Lazy load the contentCourse
        if(!$this->contentCourse){
            if($retriver = $this->getContentRetriever()){
                if($course = $retriver->getCourseById($this->courseID)){
                    $this->contentCourse = $course;
                }
            }
        }
        return $this->contentCourse;
    }

    public function setContentType($type) {
        $this->contentType = $type;
    }

    public function getContentType() {
        return $this->contentType;
    }

    public function getContentClass(){
        switch ($this->contentType) {
            case 'file':
                return $this->getContentMimeType();
                break;
            default:
                return $this->getContentType();
                break;
        }
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function getAuthor() {
        if (!$this->author) {
            // if the authorID is set then attempt to retrieve the user's name
            if ($this->authorID && $retriever = $this->getContentRetriever()) {
                if ($user = $retriever->getUserByID($this->authorID)) {
                    $this->author = $user->getFullName();
                }
            }
        }
        return $this->author;
    }
    
    public function setAuthorID($authorID) {
        $this->authorID = $authorID;
    }
    
    public function getAuthorID() {
        return $this->authorID;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setPublishedDate($dateTime) {
        $this->publishedDate = $dateTime;
    }

    public function getPublishedDate() {
        return $this->publishedDate;
    }

    public function setEndDate($dateTime) {
        $this->endDate = $dateTime;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public static function getPriorities() {
        return array('none', 'high', 'middle', 'low');
    }

    public function setPriority($priority = '') {
        if (in_array($priority, self::getPriorities())) {
            $this->priority = $priority;
        }
    }

    public function getPriority() {
        return $this->priority ? $this->priority : 'none';
    }

    public function setProperties($properties) {
        $this->properties = $properties;
    }

    public function addProperty($key, $value) {
        $this->properties[$key] = $value;
    }

    public function getProperty($var) {
        return isset($this->properties[$var]) ?  $this->properties[$var] : '';
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
    
    /**
     * Get viewMode.
     *
     * @return viewMode.
     */
    public function getViewMode() {
        if(empty($this->viewMode)) {
            return self::MODE_PAGE;
        }
        return $this->viewMode;
    }

    /**
     * Set viewMode.
     *
     * @param viewMode the value to set.
     */
    public function setViewMode($viewMode) {
        $this->viewMode = $viewMode;
    }

    /**
     * Get downloadMode.
     *
     * @return downloadMode.
     */
    public function getDownloadMode() {
        if(empty($this->downloadMode)) {
            return self::MODE_DOWNLOAD;
        }
        return $this->downloadMode;
    }

    /**
     * Set downloadMode.
     *
     * @param downloadMode the value to set.
     */
    public function setDownloadMode($downloadMode) {
        $this->downloadMode = $downloadMode;
    }

    public function sortBy(){
        return $this->getPublishedDate() ? $this->getPublishedDate()->format('U') : 0;
    }
}
