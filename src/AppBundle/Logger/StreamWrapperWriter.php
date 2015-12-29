<?php
/**
 * @author alex
 * @date 2015-12-23
 *
 */

namespace AppBundle\Logger;


interface StreamWrapperWriter
{
    public function stream_close();

    public function stream_eof();

    public function stream_open($path, $mode, $options, &$opened_path);

    public function stream_write($data);
}