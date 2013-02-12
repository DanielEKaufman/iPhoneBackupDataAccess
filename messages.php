<?php
date_default_timezone_set('America/Los_Angeles');

class Messages extends SQLite3
{
  function __construct() {
    // This is the most recent file in my backup iOS 6.0 that
    // contains text and iMessages
    $sqllite_file = '../3d0d7e5fb2ce288813306e4d4636395e047a3d28';
    $this->open($sqllite_file);
  }

  // This function returns an array that contains the count 
  // of all messages by the hour they were recieved (1 - 24)
  function get_total_message_count_by_hour() {
    // min/sec
    $result = self::query('SELECT 
                             count(*) as count, 
                             (message.date+978282541)/60/60%24 AS my_date 
                           FROM chat_message_join 
                             INNER JOIN chat 
                             ON chat_message_join.chat_id=chat.ROWID     
                             INNER JOIN message 
                             ON chat_message_join.message_id=message.ROWID
                           GROUP BY my_date;');

    $count_data = array();
    while($res = $result->fetchArray(SQLITE3_ASSOC)){
      $count_data[$res['my_date']] = $res['count'];
    }
    return $count_data;
  }

  // This function returns an array that contains the 
  // count of all messages by the day they were received 
  function get_total_message_count_by_day() {
    $result = self::query('SELECT
                             count(*) as count, 
                             date(message.date+978282541, "unixepoch") AS my_date 
                           FROM chat_message_join 
                             INNER JOIN chat 
                             ON chat_message_join.chat_id=chat.ROWID 
                             INNER JOIN message 
                             ON chat_message_join.message_id=message.ROWID 
                           GROUP BY my_date 
                           ORDER BY my_date;');

    $count_data = array();
    while($res = $result->fetchArray(SQLITE3_ASSOC)){
      $count_data[$res['my_date']] = $res['count'];
    }

    return self::build_array($count_data);
  }

  // This function returns an array that contains the count
  // of all message to a specific number by the day they 
  // were received
  function get_message_count_by_number_by_day($phone_number) {
    $last_four = substr($phone_number, -4);  
    
    // union imessages and regular texts
    $result = self::query('SELECT 
	                     count(*) AS count, 
                   	     my_date FROM (select ROWID, date(date+978307200, "unixepoch") AS my_date, 
	                     date 
                           FROM 
                             message 
	                   WHERE 
	                     date < 380000000 
	                     AND (madrid_handle LIKE "%'.$last_four.'%" OR address LIKE "%'.$last_four.'%") 
                           UNION 
                             SELECT
                               ROWID, 
                               date(date, "unixepoch") AS my_date, 
                               date 
	                     FROM message 
	                     WHERE 
                               date > 380000000 AND (madrid_handle LIKE "%'.$last_four.'%" OR address LIKE "%'.$last_four.'%")) 
                             GROUP BY my_date;');

    $count_data = array();
    while($res = $result->fetchArray(SQLITE3_ASSOC)){
      $count_data[$res['my_date']] = $res['count'];
    }

    return self::build_array($count_data);
  }

  // Creates an array that can easily be used by 
  // Excel to create a graph.
  function build_array($data_array) {
    $first_message_date = '2008-01-01';
    $all_dates = self::createDateRangeArray($first_message_date, date('Y-m-d'));
    $coun_array = array();
    foreach($all_dates as $date) {
      $count = 0;
      if (isset($data_array[$date])) {
        $count = $data_array[$date];
      }
      $count_array[$date] = $count;
    }
    return $count_array;
  }


  // Takes two dates formatted as YYYY-MM-DD and creates an
  // inclusive array of the dates between the from and to dates.
  
  // Could test validity of dates here but I'm already doing
  // that in the main script
  function createDateRangeArray($strDateFrom, $strDateTo)
  {
    $date_array=array();
    
    $unixDateFrom=mktime(1,0,0,substr($strDateFrom,5,2), substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $unixDateTo=mktime(1,0,0,substr($strDateTo,5,2), substr($strDateTo,8,2),substr($strDateTo,0,4));

    if ($unixDateTo>=$unixDateFrom)
      {
        array_push($date_array,date('Y-m-d',$unixDateFrom)); // first entry
        while ($unixDateFrom<$unixDateTo)
	  {
            $unixDateFrom+=86400; // add 24 hours
            array_push($date_array,date('Y-m-d',$unixDateFrom));
	  }
      }
    return $date_array;
  }
}

