<?php
namespace neptune\exceptions;
class ConfigFileException extends \Exception {
    public $fatal = true;
    public $not_loggable = true;

}
?>
