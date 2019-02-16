<?php

namespace mad654\eventstore;


use Psr\Log\LoggerInterface;

interface Logable
{
    public function attachLogger(LoggerInterface $logger);
}