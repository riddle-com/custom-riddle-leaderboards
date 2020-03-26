<?php

/**
 * @since 1.0
 */

namespace Riddle\Landingpage;

class RiddleData
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

        /**
     * 
     * @return int
     */
    public function getId()
    {
        return (int) $this->data->riddle->id;
    }
    /**
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->data->riddle->title;
    }
    /**
     * 
     * @return object
     */
    public function getLead()
    {
        return isset($this->data->lead2) ? $this->data->lead2 : null;
    }
    /**
     * 
     * @return array
     */
    public function getLeadFields()
    {
        $fields = array();
        foreach (get_object_vars($this->data->lead2) as $_field => $_value) {
            $fields[] = $_field;
        }
        return $fields;
    }
    /**
     * 
     * @return array
     */
    public function getAnswers()
    {
        return isset($this->data->answers) ? $this->data->answers : null;
    }
    /**
     * 
     * @return array
     */
    public function getResult()
    {
        return isset($this->data->result) ? $this->data->result : null;
    }
    
    /**
     * 
     * @return array
     */
    public function getResultData()
    {
        return isset($this->data->resultData) ? $this->data->resultData : null;
    }
    /**
     * 
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    /**
     * 
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getJsonData()
    {
        return json_decode(json_encode($this->data), true);
    }
    
    /**
     * Check if request is from riddle
     */
    private function _checkRequest()
    {
        if ($_SERVER['HTTP_ORIGIN'] !== 'https://www.riddle.com') {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
    }

}