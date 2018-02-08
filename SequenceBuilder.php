<?php namespace JC;

use JC\GoogleAnalyticsClient;

class SequenceBuilder{

    protected $sequence = [];

    /**
     * { function_description }
     *
     * @param      <type>                     $metric  The metric
     * @param      \JC\GoogleAnalyticsClient  $client  The client
     */

    public function __construct($metric, GoogleAnalyticsClient $client){

        $this->metric = $metric;

        $this->client = $client;
    }

    /**
     * { function_description }
     *
     * @param      <type>  $condition  The condition
     *
     * @return     self    ( description_of_the_return_value )
     */

    public function then($condition){

        $condition = is_array($condition) ? implode(";ga:", $condition) : (string) $condition;

        $this->sequence[] = "ga:{$condition}";

        return $this;
    }

    /**
     * { function_description }
     *
     * @param      <type>  $prop   The property
     *
     * @return     <type>  ( description_of_the_return_value )
     */

    public function __get($prop){

        return $this->client->{$prop};
    }

    /**
     * { function_description }
     *
     * @param      <type>  $fn      The function
     * @param      <type>  $params  The parameters
     *
     * @return     <type>  ( description_of_the_return_value )
     */

    public function __call($fn, $params){

        return call_user_func_array([$this->client, $fn], $params);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return     string  String representation of the object.
     */

    public function __toString(){

        return $this->metric."::sequence::".implode(";->>", $this->sequence);
    }

}