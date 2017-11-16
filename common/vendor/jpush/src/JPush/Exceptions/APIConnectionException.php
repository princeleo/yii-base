<?php
namespace app\common\vendor\jpush\src\JPush\Exceptions;

class APIConnectionException extends JPushException {

    function __toString() {
        return "\n" . __CLASS__ . " -- {$this->message} \n";
    }
}
