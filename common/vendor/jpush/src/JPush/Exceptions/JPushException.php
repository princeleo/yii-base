<?php
namespace app\common\vendor\jpush\src\JPush\Exceptions;

class JPushException extends \Exception {

    function __construct($message) {
        parent::__construct($message);
    }
}
