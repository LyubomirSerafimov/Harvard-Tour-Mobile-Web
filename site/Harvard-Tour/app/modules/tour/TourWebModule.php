<?php

class TourWebModule extends WebModule {
  protected $id = 'tour';
  private $tour = null;
  private $stop = '';
  const CURRENT_STOP_COOKIE = 'currentStop';
  const VISITED_STOPS_COOKIE = 'visitedStops';
  const COOKIE_DURATION = 31536000;  // 1 year
  const NEW_TOUR_PARAM = 'new';
  
  protected function initialize() {
    $stopId = false;
    $seenStopIds = array();
    
    $newTour = $this->getArg(self::NEW_TOUR_PARAM, false);
    if (isset($this->args['id'])) {
      $stopId = $this->args['id']; // user came from a page that set the stop
    }
    
    if (!$newTour) {
      if (!$stopId && isset($_COOKIE[self::CURRENT_STOP_COOKIE]) && $_COOKIE[self::CURRENT_STOP_COOKIE]) {
        $stopId = $_COOKIE[self::CURRENT_STOP_COOKIE]; // cookie is set
      }
    
      if (isset($_COOKIE[self::VISITED_STOPS_COOKIE])) {
        $seenStopIds = explode(',', $_COOKIE[self::VISITED_STOPS_COOKIE]);
      }
      if (isset($this->args['fromId'])) {
        $seenStopIds[] = $this->args['fromId'];
      }
    }
    
    $this->tour = new Tour($stopId, $seenStopIds);
    $this->stop = $this->tour->getStop();
      
    if ($newTour) {
      $expires = time() - 3600; // drop cookies on new tour
      setcookie(self::CURRENT_STOP_COOKIE,  '', $expires, COOKIE_PATH);
      setcookie(self::VISITED_STOPS_COOKIE, '', $expires, COOKIE_PATH);
    
    } else {
      // store new state
      $expires = time() + self::COOKIE_DURATION;
      setcookie(self::CURRENT_STOP_COOKIE,  $this->stop->getId(),       $expires, COOKIE_PATH);
      setcookie(self::VISITED_STOPS_COOKIE, implode(',', $seenStopIds), $expires, COOKIE_PATH);
    }
  }
  
  protected function markerImages() {
    return array(
      'self' => 'http://chart.apis.google.com/chart?'.http_build_query(array(
        'chst' => 'd_simple_text_icon_left',
        'chld' => ' |9|000000|glyphish_walk|24|000000',
      )),
      'current' => 'http://chart.apis.google.com/chart?'.http_build_query(array(
        'chst' => 'd_map_xpin_letter_withshadow',
        'chld' => 'pin||DD0000|DD0000',
      )),
      'visited' => 'http://chart.apis.google.com/chart?'.http_build_query(array(
        'chst' => 'd_map_xpin_icon_withshadow',
        'chld' => 'pin|glyphish_todo|CCCCCC',
      )),
      'other' => 'http://chart.apis.google.com/chart?'.http_build_query(array(
        'chst' => 'd_map_xpin_letter_withshadow',
        'chld' => 'pin||CCCCCC|CCCCCC',
      )),
    );
  }
  
  protected function currentIcon() {
    return 'http://chart.apis.google.com/chart?'.http_build_query(array(
      'chst' => 'd_map_xpin_letter',
      'chld' => 'pin||DD0000|DD0000',
    ));
  }
  
  protected function visitedIcon() {
    return 'http://chart.apis.google.com/chart?'.http_build_query(array(
      'chst' => 'd_simple_text_icon_left',
      'chld' => ' |9|555555|glyphish_todo|16|555555',
    ));
  }
  
  protected function initializeMap() {
    $center = array(
      'lat' => 42.374464, 
      'lon' => -71.117232,
    );
    
    $stopOverviewMode = $this->page != 'approach' ? 'true' : 'false';
    
    // Add prefix to urls which will be set via Javascript
    $tourStops = $this->getAllStopsDetails();
    foreach ($tourStops as $i => $tourStop) {
      $tourStops[$i]['url'] = URL_PREFIX.ltrim($tourStop['url'], '/');
    }
    
    $scriptText = "\n".
      'var centerCoords = '.json_encode($center)."\n".
      'var tourStops = '.json_encode($tourStops).";\n".
      'var tourIcons = '.json_encode($this->markerImages()).";\n";

    $this->addExternalJavascript('http://maps.google.com/maps/api/js?sensor=true');
    $this->addInlineJavascript($scriptText);
    $this->addOnLoad('showMap(centerCoords, tourStops, tourIcons, '.$stopOverviewMode.');');
    $this->addOnOrientationChange('resizeMapOnChange();');
  }
  
  protected function hasTabForKey($tabKey, &$tabJavascripts) {
    return true;
  }
  
  protected function getBriefStopDetails($stop) {
    $coords = $stop->getCoords();
    
    return array(
      'id'        => $stop->getId(),
      'title'     => $stop->getTitle(),
      'subtitle'  => $stop->getSubtitle(),
      'url'       => $this->buildTourURL('approach', array('id' => $stop->getId())),
      'photo'     => $stop->getPhotoSrc(),
      'thumbnail' => $stop->getThumbnailSrc(),
      'lat'       => $coords['lat'],
      'lon'       => $coords['lon'],
      'visited'   => $stop->wasVisited(),
      'current'   => $stop->isCurrent(),
      'lenses'    => array_fill_keys($stop->getAvailableLenses(), array()),
    );
  }
  
  protected function getStopDetails($stop) {
    $stopDetails = $this->getBriefStopDetails($stop);
    
    $lenses = $stop->getAvailableLenses();
    foreach ($stopDetails['lenses'] as $lens => $contents) {
      foreach ($stop->getLensContents($lens) as $lensContent) {
        $stopDetails['lenses'][$lens][] = $lensContent->getContent();
      }
    }
    
    return $stopDetails;
  }
  
  protected function getAllStopsDetails() {
    $stopsDetails = array();
    
    foreach ($this->tour->getAllStops() as $stop) {
      $stopsDetails[] = $this->getBriefStopDetails($stop);
    }
    return $stopsDetails;
  }
  
  protected function buildTourURL($page, $args=array()) {
    if (!isset($args['id'])) {
      $args['id'] = $this->stop->getId();
    }
    return $this->buildURL($page, $args);
  }
  
  protected function initializeForPage() {
    $stopInfo = $this->getStopDetails($this->stop);
    
    $showMapLink = true;
    $showHelpLink = true;
    
    switch ($this->page) {
      case 'index':
        if ($this->tour->isInProgress()) {
          $this->assign('resumeURL', $this->buildTourURL('approach', array('stop' => $this->stop->getId())));
        }
        
        $this->assign('startURL', $this->buildTourURL('overview', array(self::NEW_TOUR_PARAM => 1)));
        break;
      
      case 'tourhelp':
        $showHelpLink = false;
        $this->assign('doneURL', $this->getArg('doneURL', $this->buildTourURL('index')));
        break;
      
      case 'finish':
        $showMapLink = false;
        $showHelpLink = false;

        $prevURL = false;
        $prevStop = $this->tour->getLastStop();
        if ($prevStop) {
          $prevURL = $this->buildTourURL('detail', array(
            'id' => $prevStop->getId(),
          ));
        } else {
          $prevURL = $this->buildTourURL('overview');
        }
        $nextURL = false;
        
        $this->assign('prevURL', $prevURL);
        $this->assign('nextURL', $nextURL);
        break;
      
      case 'overview':
        $view = $this->getArg('view', 'map');
        $showMapLink = false;
        
        $this->addInternalJavascript('/common/javascript/lib/ellipsizer.js');
        if ($view == 'map') {
          $this->initializeMap();
          $this->addOnLoad('setupSubtitleEllipsis();');
        } else {
          $this->assign('stops', $this->getAllStopsDetails());
          $this->addOnLoad('setupStopList();');
        }
        
        $args = $this->args;
        $args['view'] = 'map';
        $mapViewURL = $this->buildTourURL($this->page, $args);
        $args['view'] = 'list';
        $listViewURL = $this->buildTourURL($this->page, $args);
        
        $stopInfo['url'] = $this->buildTourURL('approach');
        
        $this->assign('mapViewURL',  $mapViewURL);
        $this->assign('listViewURL', $listViewURL);
        $this->assign('view',        $view);
        $this->assign('newTour',     $this->getArg(self::NEW_TOUR_PARAM, false));
        $this->assign('doneURL',     $this->getArg('doneURL', $this->buildTourURL('index')));
        $this->assign('currentIcon', $this->currentIcon());
        $this->assign('visitedIcon', $this->visitedIcon());
        break;
        
      case 'approach':
        $this->initializeMap();
        $this->addInternalJavascript('/common/javascript/lib/ellipsizer.js');
        $this->addOnLoad('setupSubtitleEllipsis();');
      
        $prevURL = false;
        $prevStop = $this->tour->getPreviousStop();
        if ($prevStop) {
          $prevURL = $this->buildTourURL('detail', array(
            'id' => $prevStop->getId(),
          ));
        } else {
          $prevURL = $this->buildTourURL('overview', array(self::NEW_TOUR_PARAM => 1));
        }
        $nextURL = $this->buildTourURL('detail');
        
        $this->assign('prevURL', $prevURL);
        $this->assign('nextURL', $nextURL);
        break;
        
      case 'detail':
        $detailConfig = $this->loadPageConfigFile('detail', 'detailConfig');        
        $tabKeys = array_keys($stopInfo['lenses']);
        $tabJavascripts = array();
        
        $prevURL = $this->buildTourURL('approach');
        $nextURL = false;
        $nextStop = $this->tour->getNextStop();
        if ($nextStop) {
          $nextURL = $this->buildTourURL('approach', array(
            'id'     => $nextStop->getId(),
            'fromId' => $this->stop->getId(),
          ));
        } else {
          $nextURL = $this->buildTourURL('finish');
        }
        $this->enableTabs($tabKeys, null, $tabJavascripts);

        $this->assign('tabKeys', $tabKeys);
        $this->assign('prevURL', $prevURL);
        $this->assign('nextURL', $nextURL);
        break;
        
    }
    
    $this->assign('stop', $stopInfo);
    
    if ($showMapLink) {
      $this->assign('mapLink', $this->buildTourURL('overview', array(
        'doneURL' => $this->buildTourURL($this->page, $this->args),
      )));
    }
    if ($showHelpLink) {
      $this->assign('helpLink', $this->buildTourURL('tourhelp', array(
        'doneURL' => $this->buildTourURL($this->page, $this->args),
      )));
    }

  }
}
