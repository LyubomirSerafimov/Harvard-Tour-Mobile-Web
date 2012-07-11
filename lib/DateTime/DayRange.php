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
  * DayRange: child class of TimeRange describing a full day
  * @package ExternalData
  * @subpackage Calendar
  */
class DayRange extends TimeRange {
    public function __toString()
    {
        return DateFormatter::formatDateRange($this, DateFormatter::MEDIUM_STYLE, DateFormatter::NO_STYLE);
    }
  public function __construct($start, $end=null, $tzid=NULL) {
    if (is_null($end)) {
        $end = $start;
    }
    // use mktime which uses system time zone if tzid is blank or is the same as system time zone TODO: what happens if it's different?
    if (!$tzid || $tzid == date_default_timezone_get()) {
        $this->start = mktime(0, 0, 0, date('m', $start), date('d', $start), date('Y', $start));
        $this->end = mktime(23, 59, 59, date('m', $end), date('d', $end), date('Y', $end));
    } else {
        throw new KurogoException("Timezone set ($tzid), but is not the same as system time zone (" . date_default_timezone_get() . "). This case needs to be handled");
    }
  }
}

