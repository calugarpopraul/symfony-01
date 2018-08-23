<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 8/16/18
 * Time: 1:06 PM
 */

namespace App\Service;


use Psr\Log\LoggerInterface;

class Greeting
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function greet(string $name)
    {
        $this->logger->info("Greeted $name");
        return "Hello $name";
    }

}