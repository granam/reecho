<?php
namespace Granam\ReEcho;

use Granam\Strict\Object\StrictObject;

class RequestRecorder extends StrictObject
{
    /**
     * @var string
     */
    private $fileToRecordInto;

    /**
     * @param string $fileToRecordInto
     * @throws \Granam\ReEcho\Exceptions\CanNotWriteToFile
     */
    public function __construct(string $fileToRecordInto)
    {
        if (!is_writable($fileToRecordInto)) {
            throw new Exceptions\CanNotWriteToFile('Can not write into ' . $fileToRecordInto);
        }
        $this->fileToRecordInto = $fileToRecordInto;
    }

    /**
     * @return int number of written bytes
     * @throws \Granam\ReEcho\Exceptions\CanNotEncodeCurrentRequestToJson
     * @throws \Granam\ReEcho\Exceptions\CanNotWriteToFile
     */
    public function record()
    {
        $encoded = json_encode($_REQUEST, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            throw new Exceptions\CanNotEncodeCurrentRequestToJson(
                'Encoding current request to JSON failed: ' . json_last_error_msg(),
                json_last_error()
            );
        }
        $writtenBytes = @file_put_contents($this->fileToRecordInto, $encoded);
        if ($writtenBytes === false) {
            throw new Exceptions\CanNotWriteToFile('Can not write into ' . $this->fileToRecordInto);
        }

        return $writtenBytes;
    }
}