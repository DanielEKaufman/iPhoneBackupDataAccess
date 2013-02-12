<?php
include 'messages.php';
include 'contacts.php';

$contacts = new Contacts();
$all_contacts = $contacts->get_contacts();
print_r($all_contacts);

$messages = new Messages();	 
$handle = fopen('message_by_hour.csv','w');
fputcsv($handle, $messages->get_total_message_count_by_hour());
fclose($handle);
