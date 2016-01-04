<?php
/**
 * @author alex
 * @date 2015-12-31
 *
 */

namespace AppBundle\Facebook;


use FacebookAds\Http\RequestInterface;
use FacebookAds\Http\ResponseInterface;
use FacebookAds\Logger\LoggerInterface;

class FacebookLoggerWrapper implements LoggerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * FacebookLoggerWrapper constructor.
     * @param $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $message = "[FacebookAPI]  $message";
        $this->logger->log($level, $message, $context);
    }

    /**
     * @param string $level
     * @param RequestInterface $request
     * @param array $context
     */
    public function logRequest($level, RequestInterface $request, array $context = array())
    {

        $msg = "[FacebookAPI] [Response] \n";
        $msg = trim($request->getMethod() . ' ' . $request->getUrl());
        foreach ($request->getHeaders() as $name => $value) {
            $msg .= "\r\n{$name}: " . $value;
        }

        $message = "{$msg}\r\n\r\n" . implode("\n", $request->getBodyParams()->export());

        $this->logger->log($level, $message, $context);

    }

    /**
     * @param string $level
     * @param ResponseInterface $response
     * @param array $context
     */
    public function logResponse(
        $level, ResponseInterface $response, array $context = array()) {

        $msg = "[FacebookAPI] [Response]  \n";
        $msg .= $response->getStatusCode() . ' ';
        foreach ($response->getHeaders() as $name => $value) {
            $msg .= "\r\n{$name}: " . $value;
        }
        $message = "{$msg}\r\n\r\n" . $response->getBody();

        $this->logger->log($level, $message, $context);

    }
}