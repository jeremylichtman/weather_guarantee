<?php

/**
 * Scraper that determines if the 680 News Weather Guarantee has been
 * broken (a fairly rare occurance), and generates an alert.
 *
 * This script should probably be run towards the end of the day, after
 * temperatures have started declining. Note that there are all kinds
 * of unusual circumstances that could cause the high temp to happen
 * later on in the day, even if the usual high happens around 2pm.
 *
 * Note: this script depends on the html layout of the two pages that
 * it is scraping; if those change, the script will need modification.
 */

// Get the guaranteed high from 680 News.
$guaranteed_high = get_guarantee();

// Get the actual high of the day from Environment Canada.
$actual_high = get_high_temp();

// Determine the difference between the actual and guaranteed high temps.
$diff = abs($guaranteed_high - $actual_high);

// Output T/F depending on whether the difference is greater than 3C.
if ($diff > 3) {
  echo 'T';
}
else {
  echo 'F';
}

// Done. You could modify this script to send an email etc, or just use it
// chained to some other process.
exit;


/**
 * Helper function.
 *
 * Returns 680 news weather guarantee for the day in Celcius.
 */
function get_guarantee() {
  // Get the html returned from the following url.
  $html = file_get_contents('http://680news.com/weather');

  $weather_doc = new DOMDocument();

  // Disable libxml errors.
  libxml_use_internal_errors(TRUE);

  // If any html is actually returned.
  if (!empty($html)) {

    $weather_doc->loadHTML($html);

    // Remove errors for yucky html.
    libxml_clear_errors();

    $weather_xpath = new DOMXPath($weather_doc);

    // Get weather guarantee div.
    $weather_row = $weather_xpath->query("//*[@id='wg-guarantee']");

    if ($weather_row->length > 0) {
      foreach ($weather_row as $row) {
        // Strip out intro text.get_high_temp()
        $temp = str_replace("Guaranteed high: ", "", $row->nodeValue);
        $temp = str_replace("C", "", $temp);
        return $temp;
      }
    }
  }

  // Couldn't retrieve the guaranteed high.
  return false;
}

/**
 * Helper function.
 *
 * Get the actual daily high, if that exists.
 */
function get_high_temp() {
  // The table from Environment Canada contains the past 24 hours of
  // data in a table, in reverse order by time. We need to loop through
  // only the temperatures for the current date (which may or may not
  // have an official high temperature yet).
  $high = false;
  $today = date("d F Y");

  // Get the html returned from the following url.
  $html = file_get_contents('https://weather.gc.ca/past_conditions/index_e.html?station=yyz');

  $weather_doc = new DOMDocument();

  // Disable libxml errors.
  libxml_use_internal_errors(TRUE);

  // If any html is actually returned.
  if (!empty($html)) {

    $weather_doc->loadHTML($html);

    // Remove errors for yucky html.
    libxml_clear_errors();

    $weather_xpath = new DOMXPath($weather_doc);

    // Start at the weather conditions table.
    $tag1 = $weather_doc->getElementsByTagName("tbody")->item(0);

    // Get weather condition rows.
    $weather_row = $weather_xpath->query(".//tr", $tag1);

    if ($weather_row->length > 0) {
      foreach ($weather_row as $index => $row) {
        // Check if this row contains a date. The first time is okay,
        // the second time is the previous day.
        $row_contents = $weather_xpath->query(".//th", $row);
        if ($row_contents->length > 0) {
          $row_date = $row_contents[0]->nodeValue;
          if ($row_date != $today) {
            break;
          }
        }

        // Grab the temperature column.
        $cols = $weather_xpath->query(".//td", $row);
        if ($cols->length > 0) {
          $temp_col = $cols[2];

          // Extract just the temp.
          $current_temps = explode("   ", $temp_col->nodeValue);
          $current_temp = intval(trim($current_temps[0]));

          // Check if it is higher than the previous record high for the day.
          if (($high === FALSE) || ($current_temp > $high)) {
            $high = $current_temp;
          }
        }

      }
    }
  }

  // Couldn't retrieve the guaranteed high.
  return $high;
}

