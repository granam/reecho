<?php
namespace Granam\Tests\ReEcho;

use Granam\ReEcho\RequestRecorder;
use PHPUnit\Framework\TestCase;

class RequestRecorderTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_record_current_request()
    {
        $tempFileName = tempnam(sys_get_temp_dir(), __FUNCTION__);
        $requestRecorder = new RequestRecorder($tempFileName);
        $_REQUEST = [__FUNCTION__ => __CLASS__];
        self::assertGreaterThan(0, $requestRecorder->record());
        $fileContent = file_get_contents($tempFileName);
        unlink($tempFileName);
        self::assertNotEmpty($fileContent);
        $decoded = json_decode($fileContent, true /* as array */);
        self::assertNotFalse($decoded);
        self::assertArrayHasKey(__FUNCTION__, $decoded);
        self::assertSame(__CLASS__, $decoded[__FUNCTION__]);
    }

    /**
     * @test
     * @expectedException \Granam\ReEcho\Exceptions\CanNotWriteToFile
     * @expectedExceptionMessageRegExp ~FooBar~
     */
    public function I_can_not_use_non_writable_file()
    {
        $tempFileName = tempnam(sys_get_temp_dir(), 'FooBar');
        chmod($tempFileName, 0400);
        try {
            new RequestRecorder($tempFileName);
        } catch (\Exception $exception) {
            chmod($tempFileName, 0600);
            unlink($tempFileName);
            throw $exception;
        }
    }

    /**
     * @test
     * @expectedException \Granam\ReEcho\Exceptions\CanNotWriteToFile
     * @expectedExceptionMessageRegExp ~FooBaz~
     */
    public function I_can_not_use_file_non_writable_after_recorder_creation()
    {
        $tempFileName = tempnam(sys_get_temp_dir(), 'FooBaz');
        try {
            $requestRecorder = new RequestRecorder($tempFileName);
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage());

            return;
        }
        chmod($tempFileName, 0400);
        try {
            $requestRecorder->record();
        } catch (\Exception $exception) {
            chmod($tempFileName, 0600);
            unlink($tempFileName);
            throw $exception;
        }
    }

    /**
     * @test
     * @expectedException \Granam\ReEcho\Exceptions\CanNotEncodeCurrentRequestToJson
     * @expectedExceptionMessageRegExp ~JSON.+UTF-8~
     */
    public function I_am_stopped_on_encoding_to_json_failure()
    {
        $tempFileName = tempnam(sys_get_temp_dir(), 'FooQux');
        $requestRecorder = new RequestRecorder($tempFileName);
        /** invalid UTF-8 sequence, taken from @link http://php.net/manual/en/function.json-last-error.php */
        $_REQUEST = [__FUNCTION__ => "\xB1\x31"];
        $requestRecorder->record();
    }

}