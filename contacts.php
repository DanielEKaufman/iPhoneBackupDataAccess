<?php
class Contacts extends SQLite3
{
  function __construct() {
    // path to SQLite file containing contact information
    $sqllite_file = '../31bb7ba8914766d4ba40d6dfb6113c8b614be442';
    $this->open($sqllite_file);
  }

  function get_contacts($first_name = '', $last_name = '') {
    // This section accomidates querys for all users, users where 
    // only the first or last names are known, or users where the
    // first and last names are known.
    if (!empty($first_name) && !(empty($last_name))) {
      $name_query = 'AND (lower(First) = "'.strtolower($first_name).'" AND  lower(Last) = "'.strtolower($last_name).'")';
    } else if (!empty($first_name) || !(empty($last_name))) {
      $name_query = 'AND (lower(First) = "'.strtolower($first_name).'" OR  lower(Last) = "'.strtolower($first_name).'")';
    } else {
      $name_query = '';
    }

    $contacts_results = self::query('SELECT 
                                         ROWID, 
                                         First, 
                                         Last, 
                                         ABMultiValue.value 
                                       FROM 
                                         ABPerson, 
                                         ABMultiValue 
                                       WHERE 
                                         ROWID=record_id '.
				         $name_query
				      .'ORDER BY ROWID');


    $contacts = array();
    while($res = $contacts_results->fetchArray(SQLITE3_ASSOC)){
      $extra_chars = array(" ", "-", "+", "(", ")");
      $number = substr(str_replace($extra_chars,"", $res['value']),-10);

      // Some values will be email addresses, this ensures only phone numbers
      // (Technically this will include fax numbers as well.  If you want to 
      // only have phone numbers you need to specify the correct label in the 
      // query above.)
      if (is_numeric($number)) {
	$contacts[$number] = $res['First'] . ' ' . $res['Last'];
      }
    }
    return $contacts;
  }
}