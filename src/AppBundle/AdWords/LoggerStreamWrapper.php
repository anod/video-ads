<?php
/**
 * @author alex
 * @date 2015-12-23
 *
 */

namespace AppBundle\AdWords;


use AppBundle\Logger\StreamWrapperWriter;
use Psr\Log\LoggerInterface;

class LoggerStreamWrapper implements StreamWrapperWriter
{
    /**
     * @var LoggerInterface
     */
    private static $logger;

    public static function setLogger(LoggerInterface $logger) {
        self::$logger = $logger;
    }

    public static function register() {
        $existed = in_array("logger", stream_get_wrappers());
        if ($existed) {
            stream_wrapper_unregister("logger");
        }
        stream_wrapper_register("logger", LoggerStreamWrapper::class);

        return "logger://log";
    }

    public function stream_close()
    {
       return true;
    }

    public function stream_eof()
    {
        return false;
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        return true;
    }

    public function stream_write($data)
    {
        $meta = substr($data, 0, strpos($data, ']') + 1);
        list($date, $level) = explode(' - ', trim($meta, '[]'));

        $message = trim(substr($data,strlen($meta)));

        $context = [];
        if (strpos($message, "POST") === 0) {
            $parts = explode("\n\n", $message);
            if (count($parts) > 1) {
                $context[] = explode("\r\n", $parts[0]);
                $context[] = $parts[1];
                if (count($parts) > 3) {
                    $context[] = explode("\r\n", $parts[2]);
                    $context[] = $parts[3];
                }
            }
        }

        if ($level == \Logger::$DEBUG) {
            self::$logger->debug($message, $context);
        } elseif ($level == \Logger::$ERROR) {
            self::$logger->error($message, $context);
        } elseif ($level == \Logger::$FATAL) {
            self::$logger->critical($message, $context);
        } else {
            self::$logger->info($message, $context);
        }
    }

}