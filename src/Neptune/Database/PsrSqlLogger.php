<?php

namespace Neptune\Database;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * PsrSqlLogger
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class PsrSqlLogger implements SQLLogger
{

    protected $logger;
    protected $query;
    protected $time;
    protected $params;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = [], array $types = [])
    {
        $this->query = $sql;
        $this->params = $params;
        $this->time = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $time = microtime(true) - $this->time;
        $this->logger->debug($this->query, ['params' => $this->params, 'time' => $time]);
    }

}
