<?php

class Request extends Output
{
    /**
     * Controller object
     * @var object
     */
    protected $control_obj;

    /**
     * Controller function name
     * @var string
     */
    protected $control_func;
    
    public function __construct()
    {
        $this->api_key = $api_key;
        $this->password = $password;
        $this->control_obj = null;
        $this->control_func = '';
    }

    /**
     * Checks if the given function method is valid or not
     * @return boolean
     */
    protected function isMethodValid()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $tmp_arr = array();
        $tmp_arr = explode("/", $uri);

        $class = "Record";
        $func = strtolower($tmp_arr[1]).ucfirst($tmp_arr[2]);

        $obj = $class::createInstance();

        if (method_exists($obj, $func)) {
            $this->control_obj = $obj;
            $this->control_func = $func;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the input parameter from GET
     * @return array
     */
    protected function getInputArray()
    {

        $input_arr = array();
        foreach ($_GET as $key => $val) {
            $input_arr[strtolower($key)] = $val;
        }

        return $input_arr;
    }

    /**
     * Process the given request
     * @return void
     */
    public function process()
    {
        $input_arr = $this->getInputArray();

        if (!$this->authorizeUser($input_arr['api_user'], $input_arr['api_pass'])) {
            $this->showError(401, "Invalid API user or password.");
        }

        if (!$this->isMethodValid()) {
            $this->showError(404, "Invalid url. Please check the url.");
        }

        call_user_func(array($this->control_obj, $this->control_func), $input_arr);
    }

    /**
     * Check if user is valid
     * @return boolean
     */
    protected function authorizeUser($api_key, $api_pass)
    {
        if ($api_key == API_USER && $api_pass == API_PASS) {
            return true;
        } else {
            return false;
        }
    }
}
