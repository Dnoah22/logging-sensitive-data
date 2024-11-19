<?php

namespace App\Logging\src;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogProcessor implements ProcessorInterface
{

    public const SENSITIVE_WORDS = ["api_key", "api key", 'apikey', 'secret_key', 'secret key', 'secretkey', 'password', 'pass_word', 'pass word'];

    /**
     * Mask sensitive data in logs.
     *
     * @param LogRecord $logRecord
     * @return LogRecord
     */
    public function __invoke(LogRecord $logRecord)
    {
        try {
            $record = $logRecord->toArray();
            $record['context'] = $this->hideSensitiveData($record['context']);
            $logRecord->extra = [Str::random('32')];

            return $logRecord->with(
                message: $logRecord->message,
                context: $record['context'],
                level: $logRecord->level,
                channel: $logRecord->channel,
                datetime: $logRecord->datetime,
                extra: $logRecord->extra,
            );
        } catch (\Exception $e) {
            Log::debug("Exception LogProcessor", ['exception' => $e->getMessage()]);
            return $logRecord;
        }
    }

    /**
     * Recursively masks sensitive data in an array.
     *
     * @param array $data
     * @return array
     */
    public function hideSensitiveData(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->hideSensitiveData($value);
            } elseif (is_string($key) && $this->checkKeyIsSensitive($key)) {
                $data[$key] = '******';
            }
        }
        return $data;
    }

    protected function checkKeyIsSensitive($key)
    {
        foreach (self::SENSITIVE_WORDS as $sensitiveWord) {
            if (str_contains(strtolower($key), $sensitiveWord)) {
                return true;
            }
        }
        return false;
    }
}
