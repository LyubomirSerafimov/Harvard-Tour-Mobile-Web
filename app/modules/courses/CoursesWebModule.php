<?php
includePackage('Courses');

class CoursesWebModule extends WebModule {
    protected $id = 'courses'; 
    protected $controller;
    protected $courses;
    
    protected function linkforInfo($courseId,$description){
    	$links = array();
    	foreach(array('Roster', 'Course materials', 'Drop Class', 'Description') as $title){
    		$link['title'] = $title;
    		if($title == 'Roster'){
    			$link['url'] = $this->buildBreadcrumbURL('userlist', array('id'=>$courseId), false);
    		}
    		if($title == 'Course materials'){
    			$link['url'] = '#';
    		}//waiting
    		if($title == 'Drop Class'){
    			$link['url'] = $this->buildBreadcrumbURL('dropclass', array('id'=>$courseId), false);
    		}//waiting
    		if($title == 'Description'){
    			$link['subtitle'] = $description;
    		}
    		$links[] = $link;
    		unset($link);
    	}
    	return $links;
    }
    protected function linkForContent($content, $data = array()) {
        $link = array(
            'title' => $content->getTitle(),
            'subtitle' => $content->getSubTitle()
        );
        
        if ($contentID = $content->getGUID()) {
            $type = $content->getType();
            
            $options = array(
                'contentID' => $contentID
            );
            
            foreach (array('section', 'type', 'courseID') as $field) {
                if (isset($data[$field])) {
                    $options[$field] = $data[$field];
                }
            }
            $link['url'] = ($content->getType() == 'link') ? 
                           $content->getFileurl() : 
                           $this->buildBreadcrumbURL($content->getType(), $options, false);
            
        } elseif ($url = $content->getUrl()) {
            $link['url'] = $url;
        }
        
        return $link;
    }
    
    public function linkForResource($resource, $data = array()){
    	$link = array(
            'title' => $resource->getTitle(),
            'subtitle' => $resource->getSubTitle()
        );
        if($resource->getPublishedDate()){
	    	if($resource->getAuthor()){
	    		$link['subtitle'] = 'Updated '. $this->elapsedTime($resource->getPublishedDate()->format('U')) .' by '.$resource->getAuthor();
	    	}else{
	    		$link['subtitle'] = 'Updated '. $this->elapsedTime($resource->getPublishedDate()->format('U'));
	    	}
	    } else {
	    	$link['subtitle'] = $resource->getSubTitle();
	    } 
        if ($contentID = $resource->getGUID()) {
            $type = $resource->getType();
            
            $options = array(
                'contentID' => $contentID
            );
            
            foreach (array('courseID') as $field) {
                if (isset($data[$field])) {
                    $options[$field] = $data[$field];
                }
            }
            $link['url'] = ($resource->getType() == 'link' || $resource->getType() == 'url') ? 
                           $resource->getFileurl() : 
                           $this->buildBreadcrumbURL($resource->getType(), $options, false);
            
        } elseif ($url = $resource->getUrl()) {
            $link['url'] = $url;
        }
        
        return $link;
    }
    public function linkForUpdates($content,$data){
    	$type = array('page' => 'Page',
    				  'link' => 'Link',
    				  'file' => 'Download',
    				  'url' => 'Link',
    				  );
    	if ($contentID = $content->getGUID()) {
	    	$options = array(
	                'contentID' => $contentID
	        );
	    	$link = array(
	    			'title' => $type[$content->getType()].': '.$content->getTitle(),
	    	);
    	    foreach (array('courseID') as $field) {
                if (isset($data[$field])) {
                    $options[$field] = $data[$field];
                }
            }
	    	if($content->getPublishedDate()){
	    		if($content->getAuthor()){
	    			$link['subtitle'] = 'Updated '. $this->elapsedTime($content->getPublishedDate()->format('U')) .' by '.$content->getAuthor();
	    		}else{
	    			$link['subtitle'] = 'Updated '. $this->elapsedTime($content->getPublishedDate()->format('U'));
	    		}
	    	} else {
	    		$link['subtitle'] = $content->getSubTitle();
	    	}
	    	$link['url'] = ($content->getType() == 'url') ? 
	        $content->getFileurl() : 
	        $this->buildBreadcrumbURL($content->getType(), $options, false);
    	} elseif ($url = $content->getUrl()) {
            $link['url'] = $url;
        }
        return $link;
    
    }
    public function linkForCourse(Course $course, $type) {
        $link = array(
            'title' => $course->getTitle(),
            'url'   => $this->buildBreadcrumbURL('course', array('type'=>$type, 'id'=> $course->getID()), false)
        );

        switch ($type)
        {
            case 'content':
                if ($lastUpdateContent = $course->getLastUpdate()) {
                    $link['subtitle'] = $lastUpdateContent->getTitle() . '<br/>'. $this->elapsedTime($lastUpdateContent->getPublishedDate()->format('U'));
                } else {
                    $link['subtitle'] = $this->getLocalizedString('NO_UPDATES');
                }
                break;
        }
        
        return $link;
    }
    
	/**
	* assign term function
	* this function need improvment this term in some case is select area
	* @author saturn
	*
	*/
    public function assignTerm(){
        $feedTerms = $this->controller->getAvailableTerms();
        $terms = array();
        foreach($feedTerms as $term) {
        	$terms[$term->getID()] = $term->getTitle();
        }
        $term = $this->getArg('term', CoursesDataModel::CURRENT_TERM);
    	if (!$Term = $this->controller->getTerm($term)) {
        	$Term = $this->controller->getCurrentTerm();
        }
        
        if (count($terms)>1) {
        	$this->assign('terms', $terms);
        } else {
        	$this->assign('termTitle', current($terms));
        }
        return $Term;
    }
   	/**
	* assign course title function 
	* $id is course id
	* @author saturn
	*
	*/
    public function assignCourseTitle($id){
    		//$course is courseID
    		$course = $this->controller->getCourse('content', $id);
    		$this->assign('title', $course->getTitle());
    }
    
    protected function getFeedTitle($feed) {
        return isset($this->feeds[$feed]['TITLE']) ? $this->feeds[$feed]['TITLE'] : '';
    }
    
    /* @TODO provide method to get bookmarked courses */
    protected function getBookmarkedCourses() {
        return array();
    }
    
    protected function initialize() {
    	$feeds = $this->loadFeedData();
    	
        if (isset($feeds['catalog'])) {
	        $catalogFeed = $this->getModuleSections('catalog');
	        $feeds['catalog'] = array_merge($feeds['catalog'], $catalogFeed);
        }
        
        $this->feeds = $feeds;
        $this->controller = CoursesDataModel::factory('CoursesDataModel', $this->feeds);
    }
    
    protected function initializeForPage() {
        switch($this->page) {
        	case 'info':
        		$id = $this->getArg('id');
        		$this->assignCourseTitle($id);
                $this->assignTerm();
                //get single Course
                $course = $this->controller->getCourse('content',$id);
                $courseNumber = $course->getCourseNumber();
                $instructorList = array();
                $instructorList = $course->getInstructors();
                
                $instructorLinks = array();
                foreach ($instructorList as $instructor){
                	$value = $instructor->getFullName();
                	$instructorLinks[] = Kurogo::moduleLinkForValue('people', $value, $this, $instructor);
                }
                $this->assign('instructorLinks',$instructorLinks);
                
                //get the map locations data
                $map = Kurogo::moduleLinkForValue('map', $courseNumber, $this);
                // change tile for the map link
                $mapLink['title'] = $courseNumber;
                $mapLink['url'] = $map['url'];
                $mapLink['class'] = 'map';
                $this->assign('location', array($mapLink));
                
                $links = $this->linkforInfo($id,'description');// waiting description
                $this->assign('links',$links);                
                $this->assign('description','waiting description');
                
                $linkToUpdateTab = $this->buildBreadcrumbURL('course', array('id'=> $id, 'type'=>'content'), false);
            	$this->assign('linkToUpdateTab',$linkToUpdateTab);
            	
            	$linkToResourcesTab = $this->buildBreadcrumbURL('resource',array('id'=> $id,'type'=>'topic'), false);
                $this->assign('linkToResourcesTab',$linkToResourcesTab);
            	break;
        	case 'userlist':
        		$id = $this->getArg('id');
        		$course = $this->controller->getCourse('content',$id);
        		$students = $course->getStudents();
        		$links = array();
        		foreach ($students as $student){
        			$value = $student->getFullName();
        			$links[] = Kurogo::moduleLinkForValue('people', $value, $this, $student);
        		}
        		$this->assign('links',$links);
        		break;
        	case 'dropclass';
        		$id = $this->getArg('id');
        		$course = $this->controller->getCourse('content', $id);
        		$notification = $this->getLocalizedString('NOTIFICATION',$course->getTitle());
        		
        		$links = array(
        			array('title'=>$notification),
        			array('title'=>$this->getLocalizedString('YES_STRING'),'url'=>'#'),
        			array('title'=>$this->getLocalizedString('CANCEL'),'url'=>'#')
        		);
				$this->assign('links',$links);
        	break;
            case 'catalog':
                if ($areas = $this->controller->getCatalogAreas()) {
                    $areasList = array();
                    foreach ($areas as $CourseArea) {
                        $areasList[] = array(
                            'title'=>$CourseArea->getTitle(),
                            'url'=>$this->buildBreadcrumbURL('area',array('area'=>$CourseArea->getCode()), false)
                        );
                    }
                    $this->assign('areas', $areasList);
                }
                
                break;
                
            case 'area':
                $baseArea = '';
                if ($area = $this->getArg('area', '')) {
                    if ($CourseArea = $this->controller->getCatalogArea($area)) {
                        $baseArea = $area . '|';
                        $this->setPageTitles($CourseArea->getTitle());
                    } else {
                        $this->redirectTo('index', array());
                    }
                } else {
                    $this->redirectTo('index', array());
                }

                $areas = $this->controller->getCatalogAreas($area);
                
                $areasList = array();
                foreach ($areas as $CourseArea) {
                    $areasList[] = array(
                        'title'=>$CourseArea->getTitle(),
                        'url'=>$this->buildBreadcrumbURL('area',array('area'=>$baseArea . $CourseArea->getCode()), true)
                    );
                }

                $areas = explode("|", $area);
                $courses = $this->controller->getCatalogCourses(array('area' => end($areas)));
                $coursesList = array();
 
                foreach ($courses as $Course) {
                    $coursesList[] = array(
                        'title'=>$Course->getTitle(),
                        'subtitle'=>$Course->getCourseNumber(),
                        'url'=>$this->buildBreadcrumbURL('course',array('id'=> $Course->getCourseNumber(),'catalog'=>$Course->getCatalogNumber()), true)
                    );
                }
                
                $this->assign('description', $CourseArea->getDescription());
                $this->assign('areas', $areasList);
                $this->assign('courses', $coursesList);
                
                break;
            
            case 'course':
                $id = $this->getArg('id');
                $type = $this->getArg('type');
                
                if (!$course = $this->controller->getCourse($type, $id)) {
                    $this->redirectTo('index');
                }
				
                $this->assignCourseTitle($id);
                $this->assignTerm();
                $this->assign('title', $course->getTitle());
				
                $items = $this->controller->getLastUpdate($id);
                $contents = array();
                foreach ($items as $item){
                	$contents[] = $this->linkForUpdates($item, array('courseID' => $id));
                }
                $this->assign('contents', $contents);
                
                $linkToInfoTab = $this->buildBreadcrumbURL('info',array('id'=> $id), false);
                $this->assign('linkToInfoTab',$linkToInfoTab);
                $linkToResourcesTab = $this->buildBreadcrumbURL('resource',array('id'=> $id,'type'=>'topic'), false);
                $this->assign('linkToResourcesTab',$linkToResourcesTab);
                $linkToInfoTab = $this->buildBreadcrumbURL('info',array('id'=> $id), false);
                $this->assign('linkToInfoTab',$linkToInfoTab);
                             /*              
                $contentTypes = array();
                if ($contents = $this->course->getCourseContentById($id)) {

                    $options = array(
                        'id'      => $id,
                    );
                    $items = array_keys($contents['resource']);
                    
                    foreach ($items as $type) {
                        $options['type'] = $type;
                    
                        $contentType = array(
                            'title' => $this->getLocalizedString(strtoupper($type) .'_TITLE'),
                            'url'   => $this->buildBreadcrumbURL('contents', $options, true)
                        );
                        
                        $contentTypes[] = $contentType;
                    }
                }
                $this->assign('contentTypes', $contentTypes);
                */
                break;
            case 'resource':
            	$id = $this->getArg('id');
                $type = $this->getArg('type');
                $options = array(
                	'courseID' => $id,
                );
                $this->controller->setType($type);
                
                $this->assignCourseTitle($id);
                $this->assignTerm();

                $resources = array();
                $seeAllLinks = array();
                if($items = $this->controller->getResource($id)){
	                foreach ($items as $itemkey => $item){
	                	foreach ($item as $resource){
	                		if(!isset($resources[$itemkey])) 
	                		$seeAllLinks[$itemkey] = $this->buildBreadcrumbURL('resourceSeeAll', array('id'=> $id, 'type'=>$type,'key'=>urlencode($itemkey)), false);;
	                		
	                		// list three line each item may be can use js hidden item if more than three item
	                		//if(isset($resources[$itemkey]) && count($resources[$itemkey])>=3) continue;
	                		$resources[$itemkey][] = $this->linkForResource($resource,$options);
	                	}
	                }
	                $this->assign('seeAllLinks',$seeAllLinks);
	                $this->assign('resources',$resources);
                }
            	$linkToUpdateTab = $this->buildBreadcrumbURL('course', array('id'=> $id, 'type'=>'content'), false);
            	$this->assign('linkToUpdateTab',$linkToUpdateTab);
            	$linkByTopic = $this->buildBreadcrumbURL('resource', array('id'=> $id, 'type'=>'topic'), false);
            	$linkByDate = $this->buildBreadcrumbURL('resource', array('id'=> $id, 'type'=>'date'), false);
            	$this->assign('linkByTopic',$linkByTopic);
            	$this->assign('linkByDate',$linkByDate);
            	$linkToInfoTab = $this->buildBreadcrumbURL('info',array('id'=> $id), false);
                $this->assign('linkToInfoTab',$linkToInfoTab);
            	$this->assign('type', $type);
            	break;
            case 'resourceSeeAll':
            	$id = $this->getArg('id');
                $type = $this->getArg('type');
                $key = urldecode($this->getArg('key'));
                $options = array(
                	'courseID' => $id,
                );
                $this->controller->setType($type);
                $items = $this->controller->getResource($id);
                $resources = array();
                foreach ($items as $itemkey => $item){
                	foreach ($item as $resource){
                		if($key == $itemkey)
                		$resources[$itemkey][] = $this->linkForResource($resource,$options);
                	}
                }
                $this->assign('resources',$resources);
            	break;
            case 'contents':
            // 	$section = $this->getArg('section');
                $id = $this->getArg('id');
                //$courseId = $this->getArg('courseId');
                $type = $this->getArg('type');
                
                
                $items = $this->controller->getCourseContentById($id);
                
                
                if (!isset($items['resource'][$type])) {
                    throw new KurogoConfigurationException('not found the content for type ' . $type);
                }
                
                $options = array(
             //   	'section'  => $section,
             		//'courseId' => $courseId,
                    'type'     => $type,
                    'courseID' => $id
                );
                    
                $contents = array();
                foreach ($items['resource'][$type] as $item) {
                    $content = $this->linkForContent($item, $options);
                    $contents[] = $content;
                }
                $this->setPageTitles($this->getLocalizedString(strtoupper($type) .'_TITLE'));
                $this->assign('contents', $contents);
                break;
            case 'page':
            	$contentID = $this->getArg('contentID', '');
            	$courseID = $this->getArg('courseID', '');
            	$contents = $this->controller->getResource($courseID);
                if (!$content = $this->controller->getContentById($contents,$contentID)) {
                    throw new KurogoConfigurationException('not found the course content');
                }
            	$content = $this->controller->getPageTypeContent($content);
            	$this->assign('content', $content);
            	break;
            case 'file':
                $courseID  = $this->getArg('courseID');
                $type      = $this->getArg('type');
                $contentID = $this->getArg('contentID');
                
				$contents = $this->controller->getResource($courseID);
                if (!$content = $this->controller->getContentById($contents,$contentID)) {
                    throw new KurogoConfigurationException('not found the course content');
                }
                $options[] = array(
                		'url' => $this->controller->getFileUrl($content),
                		'title' => $content->getFileName(),
                		'subtitle' => 'FileSize:' . round($content->getFileSize()/1024,2) .'Kb',
                );
                if($content->getPublishedDate()){
		    		if($content->getAuthor()){
		    			$uploadDate = 'Updated '. $this->elapsedTime($content->getPublishedDate()->format('U')) .' by '.$content->getAuthor();
		    		}else{
		    			$uploadDate = 'Updated '. $this->elapsedTime($content->getPublishedDate()->format('U'));
		    		}
	    		} else {
	    			$uploadDate = $content->getSubTitle();
	    		}
	    		$this->assign('itemName',$content->getTitle());
	    		$this->assign('uploadDate',$uploadDate);
	    		$this->assign('description',$content->getDescription());
                // about the fileSize may i add function about clac filesize in CoursesDatamodel or in some file?
                $this->assign('options',$options);
                $url = $this->controller->getDownLoadTypeContent($content, $courseID);
                $this->assign('url',$url);
                //$this->outputFile($content);
                break;
            case 'index':
                $Term = $this->assignTerm();
                $courses = array();
                $options = array(
                    'term'=>$Term
                );
                $this->assign('hasPersonalizedCourses', $this->controller->canRetrieve('registration') || $this->controller->canRetrieve('content'));
                if ($this->isLoggedIn()) {                
                    if ($items = $this->controller->getCourses('content', $options)) {
                    	foreach ($items as $item) {
                            $course = $this->linkForCourse($item, 'content');
                            $courses[] = $course;
                        }
                    }
                    $this->assign('courses', $courses);
                }
                // do we have a catalog?  catelog just demo and XML file copy from LMS //delete this line after look
                $catalogItems = array();
                if ($this->controller->canRetrieve('catalog')) {
                    $catalogItems[] = array(
                        'title' => $this->getFeedTitle('catalog'),
                        'url'   => $this->buildBreadcrumbURL('catalog', array(), true),
                    );
                    
                    if ($bookmarks = $this->getBookmarkedCourses()) {
                        $catalogItems[] = array(
                            'title' => $this->getLocalizedString('BOOKMARKED_COURSES') . "(" . count($bookmarks) . ")",
                            'url'   => '',
                        );
                    }
                }
                $this->assign('catalogItems', $catalogItems);
                break;
        }
    }
}
 
