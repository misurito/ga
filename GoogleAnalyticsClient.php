<?php namespace JC;

use Google_Service_Analytics;
use Google_Client;


class GoogleAnalyticsClient{

    protected $client;

    protected $result;

    protected $metrics; 

    protected $dimensions;

    protected $between;

    protected $id;

    public function __construct($appName, $configPath){

        $this->client = $this->makeAnalyticsInstance($appName, $configPath);
    }

    /**
     * { function_description }
     *
     * @param      <type>  $id     The identifier
     *
     * @return     self    ( description_of_the_return_value )
     */

    public function using($id){
        $this->id = $id;

        return $this;
    }

    /**
     * { function_description }
     *
     * @param      <type>  $ids        The identifiers
     * @param      <type>  $startDate  The start date
     * @param      <type>  $endDate    The end date
     * @param      array   $metrics    The metrics
     * @param      array   $optParams  The option parameters
     */

    public function get($ids, $startDate, $endDate, array $metrics, array $optParams = array()){

        $this->dimensions = $this->metrics = [];

        $this->sequence = null;

        if(isset($optParams['dimensions'])){
            $this->dimensions        = $optParams['dimensions'];
            $optParams['dimensions'] = $this->makeColumns($optParams['dimensions']);
        }

        if(isset($optParams['filters'])){
            $optParams['filters'] = urlencode($this->makeColumns($optParams['filters']));
        }

        if(isset($this->sequenceTemp)){
            $this->sequence = $this->sequenceTemp;

            unset($this->sequenceTemp);

            $optParams['segment'] = (string) $this->sequence;
        }

        $this->metrics = $metrics;

        $metrics = $this->makeColumns($metrics);

        $this->between = (object) [
            'start' => $startDate,
            'end'   => $endDate
        ];

        $this->result = $this->client->data_ga->get('ga:'.$ids, $startDate, $endDate, $metrics, $optParams);

        return $this;
    }

    /**
     * { function_description }
     *
     * @param      <type>  $metric  The metric
     *
     * @return     <type>  ( description_of_the_return_value )
     */

    public function sequence($metric){

        return $this->sequenceTemp = (new SequenceBuilder($metric, $this));
    }

    /**
     * { function_description }
     *
     * @param      <type>  $start  The start
     * @param      <type>  $end    The end
     *
     * @return     self    ( description_of_the_return_value )
     */

    public function between($start, $end){

        $this->between = (object) [
            'start' => $start,
            'end'   => $end
        ];

        return $this;
    }

    /**
     * Gets the sessions.
     *
     * @return     <type>  The sessions.
     */

    public function getSessions(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['sessions']);
    }

    /**
     * Gets the direct visits.
     *
     * @param      <type>  $startDate  The start date
     * @param      <type>  $endDate    The end date
     */

    public function getDirectVisits(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['sessions'], [
            'filters'    => ['source==(direct)']
        ]);
    }

    /**
     * Gets the organic searches.
     *
     * @param      <type>  $startDate  The start date
     * @param      <type>  $endDate    The end date
     */

    public function getOrganicSearches(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['organicSearches']);
    }

    /**
     * Gets the total users.
     *
     * @param      <type>  $startDate  The start date
     * @param      <type>  $endDate    The end date
     */

    public function getAllUsers(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['users'] );
    }

    /**
     * Gets the contact form submissions.
     *
     * @return     <type>  The contact form submissions.
     */

    public function getContactFormSubmissions(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['goalCompletionsAll'], [
            'filters'    => ['goalCompletionLocation=@thank-you']
        ]);
    }

    /**
     * Gets the demo requests.
     *
     * @return     <type>  The demo requests.
     */

    public function getDemoRequests(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['goalCompletionsAll'], [
            'filters'    => ['goalCompletionLocation=@form-submitted']
        ]);
    }

    /**
     * Gets the demo request sequence completions.
     */

    public function getDemoRequestSequenceCompletions(){

        return $this->sequence('users')
            ->then('pagePath=@/demorequest')
            ->then('pagePath=@/thank-you')
            ->getSessions();
    }

    /**
     * Gets the pro upgrades.
     *
     * @return     <type>  The pro upgrades.
     */

    public function getProUpgrades(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['goalCompletionsAll'], [
            'filters'    => ['goalCompletionLocation=@upgrade/checkout']
        ]);
    }

    /**
     * Gets the ad clicks.
     *
     * @return     <type>  The ad clicks.
     */

    public function getAdClicks(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['adClicks'] );
    }

    /**
     * Gets the ctr.
     *
     * @return     <type>  The ctr.
     */

    public function getCTR(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['CTR'] );
    }

    /**
     * Gets the cpc.
     *
     * @return     <type>  The cpc.
     */

    public function getCPC(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['CPC'] );
    }

    /**
     * Gets the cpm.
     *
     * @return     <type>  The cpm.
     */

    public function getCPM(){

        return $this->get( $this->id, $this->between->start, $this->between->end, ['CPM'] );
    }


    /**
     * { function_description }
     *
     * @return     <type>  ( description_of_the_return_value )
     */

    public function results(){

        return $this->result;
    }

    /**
     * { function_description }
     */

    public function data(){

        $data = $this->makeAnalyticResultArray( $this->dimensions, $this->metrics, $this->result);

        if(count($data) == 1 && count($props = get_object_vars($data[0]))){
            return array_values($props)[0];
        }
    }

    /**
     * Makes columns.
     *
     * @param      array   $columns  The columns
     * @param      string  $prefix   The prefix
     *
     * @return     <type>  ( description_of_the_return_value )
     */

    public static function makeColumns( array $columns, $prefix = 'ga:' ){

        return implode(",",array_map(function($col) use($prefix){
            return $prefix.$col;
        }, $columns));
    }

    /**
     * Makes an analytics instance.
     *
     * @param      <type>                    $appName     The application name
     * @param      <type>                    $configPath  The configuration path
     *
     * @return     Google_Service_Analytics  ( description_of_the_return_value )
     */

    public static function makeAnalyticsInstance($appName, $configPath){

        $client = new Google_Client();
        $client->setApplicationName($appName);

        $client->setAuthConfig($configPath);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        return new Google_Service_Analytics($client);
    }

    /**
     * Makes an analytic result array.
     *
     * @param      array   $dimensions  The dimensions
     * @param      array   $columns     The columns
     * @param      <type>  $result      The result
     *
     * @return     array   ( description_of_the_return_value )
     */


    public static function makeAnalyticResultArray( array $dimensions, array $columns, $result ){

        $header = array_merge($dimensions, $columns);

        $rows = [];

        foreach($result->rows as $row){

            $r = [];

            foreach($header as $k => $col){
                $r[$col] = $row[$k];
            }

            $rows[] = (object) $r;
        }

        return $rows;
    }
}