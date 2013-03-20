<?php

/*
 * Copyright © 2010 - 2012 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

/**
  * GTFSDataRetriever
  * @package Transit
  */

class GTFSDataRetriever extends DatabaseDataRetriever
{
    public function init($args) {
        parent::init($args);
        
        $this->setCacheGroup('GTFS');
    }
}
