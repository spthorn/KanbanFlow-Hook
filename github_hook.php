<?php
$kanbanflowSecretToken = "8b6dbe327235e9cf4381e8fa9cb67c1f";
$kanbanflowBoardName = "RetailTrackerOnline";
$taskNumberPrefix = "KF";
$timezoneString = "America/New_York"; // https://php.net/manual/en/timezones.php

$githubPayload = $_REQUEST['payload'];
$payload = json_decode($githubPayload);

$auth_token = base64_encode("apiToken:$kanbanflowSecretToken");

foreach($payload->commits as $commit)
{
    date_default_timezone_set($timezoneString);
    $pushDate = date("m/d/y H:ia", strtotime(str_replace("T", "", $commit->timestamp)));

    $message = $commit->message;
    // See if any commit messages contain our task prefix
    if (preg_match('/#'.$taskNumberPrefix.'([\\d]+)/im', $message, $matches))
    {
        $taskNumber = $matches[0];

        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: Basic '.$auth_token;

        $url = "https://kanbanflow.com/api/v1/tasks?swimlaneName=$kanbanflowBoardName";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $status = curl_exec($ch);
        curl_close($ch);

        $swimlaneColumns = json_decode($status);
        // See if any columns contain tasks with our task number
        foreach($swimlaneColumns as $column)
        {
            foreach($column->tasks as $task)
            {
                if(property_exists($task, "number"))
                {
                    if ("#" . $task->number->prefix . $task->number->value == $taskNumber)
                    {
                        $poststring = '{"text":"Commit pushed to github on ' . $pushDate . ' ' . $commit->url . '"}';
                        $url = "https://kanbanflow.com/api/v1/tasks/$task->_id/comments";
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $poststring);
                        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        $status = curl_exec($ch);
                        curl_close($ch);

                        echo "Returned $status\n";
                    }
                }
            }
        }
    }
}
