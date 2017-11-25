<?php

class Record extends Output
{
    /**
     * from value of the batch to be fetched
     * @var int
     */
    protected $from;

    /**
     * length of the batch to be fetched
     * @var int
     */
    protected $length;

    /**
     * static instance of Record class
     * @var object
     */
    
    private static $instance = null;

    /**
     * Link for database connection
     * @var object
     */
    private $pdo;

    private function __construct()
    {
        $this->from = 0;
        $this->length = MAX_BATCH_LENGTH;
        $this->property = array();

        $servername = DB_HOST;
        $username = DB_USER;
        $password = DB_PASS;
        $dbname = DB_NAME;

        try {
            $this->pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            //echo $e->getMessage();
            $this->showError(503, "DB server is not ready.");
        }
    }

    /**
     * Creates instance of the class
     * @return object
     */
    public static function createInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Record();
        }

        return self::$instance;
    }

    /**
     * Returns the formatted array to be sent to output
     * @param  array $result
     * @return array
     */
    protected function getOutputArray($result)
    {
        $output_arr = array();
        $output_arr['name'] = array(
            'first' => $result['first'],
            'last' => $result['last']
            );
        
        $output_arr['eyeColor'] = $result['eyeColor'];
        $output_arr['age'] = $result['age'];
        $output_arr['isActive'] = ($result['isActive'])?true:false;
        $output_arr['_id'] = $result['id'];
        return $output_arr;
    }

    /**
     * Checks if the the value is integer
     * @param  mixed  $x
     * @return boolean
     */
    protected function isInt($x)
    {
        return (is_numeric($x) ? intval($x) == $x : false);
    }

    /**
     * Sets the internal variable for fetching batch
     * @param string $batch comma separated value with from and length
     */
    protected function setBatch($batch)
    {
        list($from, $length) = explode(",", $batch);
        $from = trim($from);
        $length = trim($length);

        if ($this->isInt($from) && $from >= 0) {
            $this->from = (int)$from;
        } else {
            $this->showError(400, "$from in batch is not valid");
        }

        if (($this->isInt($length)) && ($length > 0 && $length <= $this->length)) {
            $this->length = (int)$length;
        } else {
            $this->showError(400, "$length in batch is not valid. Value should be between 1 - ".$this->length);
        }
    }

    /**
     * Searches the matching name and display
     * @param  array $input
     * @return void
     */
    public function searchName($input)
    {
        //Validations
        if (!isset($input) || trim($input['keyword']=='')) {
            $this->showError(400, "Keyword is not given.");
        }

        if (isset($input['batch'])) {
            $this->setBatch($input['batch']);
        } else {
            $this->setBatch("0,$this->length");
        }
        
        //Fetch records in batches
        $base_sql = "SELECT * FROM students WHERE MATCH (first, last) AGAINST (:keyword IN NATURAL LANGUAGE MODE)";
        
        $sql = $base_sql." LIMIT ".$this->from.",".$this->length;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':keyword', $input['keyword']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $this->showError(400, "No records found");
        }

        $output_arr = array();

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output_arr['data'][] = $this->getOutputArray($result);
        }

        //Check if next batch exists
        $sql_next = $base_sql." LIMIT ".($this->from + $this->length).", 1";

        $stmt_next = $this->pdo->prepare($sql_next);
        $stmt_next->bindParam(':keyword', $input['keyword']);
        $stmt_next->execute();

        if ($stmt_next->rowCount() > 0) {
            $output_arr['next_link'] = BASE_URL."/search/name/?api_user=".$input['api_user']."&api_pass=".$input['api_pass']."&keyword=".urlencode($input['keyword'])."&batch=".($this->from + $this->length).",".$this->length;
        }

        $this->showSuccess($output_arr);
    }

    /**
     * Searches the matching id and display
     * @param  array $input
     * @return void
     */
    public function searchId($input)
    {
        //Validations
        if (!isset($input) || trim($input['keyword']=='')) {
            $this->showError(400, "Keyword is not given.");
        }

        $sql = "SELECT * FROM students WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $input['keyword']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (empty($result)) {
            $this->showError(400, "No records found");
        } else {
            $this->showSuccess(array('data' => $this->getOutputArray($result)));
        }
    }

    /**
     * Displays all records
     * @param  array $input
     * @return void
     */
    public function searchAll($input)
    {
        if (isset($input['batch'])) {
            $this->setBatch($input['batch']);
        } else {
            $this->setBatch("0,$this->length");
        }
        
        $base_sql = "SELECT * FROM students";
        
        $sql = $base_sql." LIMIT ".$this->from.",".$this->length;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $this->showError(400, "No records found");
        }

        $output_arr = array();

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output_arr['data'][] = $this->getOutputArray($result);
        }

        //Check if next batch exits
        $sql_next = $base_sql." LIMIT ".($this->from + $this->length).", 1";

        $stmt_next = $this->pdo->prepare($sql_next);
        $stmt_next->bindParam(':keyword', $input['keyword']);
        $stmt_next->execute();

        if ($stmt_next->rowCount() > 0) {
            $output_arr['next_link'] = BASE_URL."/search/all/?api_user=".$input['api_user']."&api_pass=".$input['api_pass']."&batch=".($this->from + $this->length).",".$this->length;
        }

        $this->showSuccess($output_arr);
    }
}
