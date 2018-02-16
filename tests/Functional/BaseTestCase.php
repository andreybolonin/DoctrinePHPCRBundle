<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional;

use Doctrine\Bundle\PHPCRBundle\Test\RepositoryManager;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseTestCase extends WebTestCase
{
    /**
     * Overwrite because older versions of Symfony require the kernel path instead of the kernel class.
     *
     * Remove when we drop support for Symfony 2.
     *
     * @return string
     */
    protected static function getKernelClass()
    {
        return Kernel::class;
    }

    protected function getRepositoryManager(): RepositoryManager
    {
        if (!self::$kernel) {
            self::bootKernel();
        }
        if (!self::$kernel->getContainer()) {
            self::$kernel->boot();
        }

        return new RepositoryManager(self::$kernel->getContainer());
    }

    protected function assertResponseSuccess(Response $response)
    {
        libxml_use_internal_errors(true);

        $dom = new \DomDocument();
        $dom->loadHTML($response->getContent());

        $xpath = new \DOMXpath($dom);
        $result = $xpath->query('//div[contains(@class,"text-exception")]/h1');
        $exception = null;
        if ($result->length) {
            $exception = $result->item(0)->nodeValue;
        }

        $this->assertEquals(200, $response->getStatusCode(), $exception ? 'Exception: "'.$exception.'"' : null);
    }
}
