<?php
namespace ER\MSApplicationInsightsMonolog;

use ApplicationInsights\Channel\Contracts\Message_Severity_Level;
use ApplicationInsights\Telemetry_Client;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class MSApplicationInsightsHandler extends AbstractProcessingHandler {
    const InsightToLoggingInterfaceMapping = [
        Logger::EMERGENCY => Message_Severity_Level::CRITICAL,
        Logger::ALERT     => Message_Severity_Level::CRITICAL,
        Logger::CRITICAL  => Message_Severity_Level::CRITICAL,
        Logger::ERROR     => Message_Severity_Level::ERROR,
        Logger::WARNING => Message_Severity_Level::WARNING,
        Logger::NOTICE => Message_Severity_Level::INFORMATION,
        Logger::INFO      => Message_Severity_Level::INFORMATION,
        Logger::DEBUG     => Message_Severity_Level::VERBOSE,
    ];

    /**
     * @var Telemetry_Client
     */
    protected $client;

    public function __construct(Telemetry_Client $client, $level = Logger::ERROR, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record)
    {
        if (isset($record['context']['exception'])) {
            $this->client->trackException($record['context']['exception'], $record);
        }
        else {
            $this->client->trackMessage((string) $record['message'],
                self::InsightToLoggingInterfaceMapping[$record['level']],
                $record);
        }
        $this->client->flush();
    }
}
