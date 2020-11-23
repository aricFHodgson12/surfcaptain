<?php


namespace App;

use CS_REST_Subscribers;
use Illuminate\Support\Facades\Log;

class CampaignMonitor
{
    public $cmCustomFields = array('JoinDate');

    public $wrap;

    public function __construct()
    {
        $auth = array('api_key' => config('services.campaign_monitor.campaign_monitor_api_key'));
        $listId = config('services.campaign_monitor.campaign_monitor_subscriber_list_id');
        $this->wrap = $wrap = new CS_REST_Subscribers($listId, $auth);
    }

    public function addRecord($data) {

        $data['JoinDate'] = date('Y-m-d');

        if (!isset($data['first_name']))
            $data['first_name'] = '';

        if (!isset($data['last_name']))
            $data['last_name'] = '';

        $dataFields = array('JoinDate');

        $customFields = array();
        foreach ($this->cmCustomFields as $k => $field) {
            if (!isset($data[$dataFields[$k]])) {
                $data[$dataFields[$k]] = '';
            }
            $customFields[] = array('Key' => $field, 'Value' => $data[$dataFields[$k]]);
        }


        //Log::debug('list id: '.$listId);
        //Log::debug('data: '.print_r($data,true).' custom fields: '.print_r($customFields,true));

        $result = $this->wrap->add(
            array(
                'EmailAddress' => $data['email'],
                'Name' => (($data['first_name']) ? $data['first_name'].' '.$data['last_name'] : ''),
                'CustomFields' => $customFields,
                'Resubscribe' => true,
                'ConsentToTrack' => 'Unchanged'
            )
        );

        //Log::debug('result: '.print_r($result,true));

        if($result->was_successful()) {
            return true;
        } else {
            return false;
        }
    }

    public function unsubscribe($email)
    {
        $result = $this->wrap->unsubscribe($email);
        if ($result->was_successful()) {
            return true;
        } else {
            return false;
        }
    }
}
