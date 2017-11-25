<?php

class Output
{
    /**
     * Displays the output on success
     * @param  array $output
     * @return void
     */
    public function showSuccess($output)
    {
        $output['status'] = "success";

        http_response_code(200);
        header('Content-Type: application/json');
        die(json_encode($output));
    }

    /**
     * Displays the error
     * @param  int $status_code HTTP status code
     * @param  string $message Error message
     * @return void
     */
    public function showError($status_code, $message)
    {
        $arr = array();
        $arr['status'] = "error";
        $arr['message'] = $message;

        http_response_code($status_code);
        header('Content-Type: application/json');
        die(json_encode($arr));
    }
}
