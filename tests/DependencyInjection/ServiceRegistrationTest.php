<?php

namespace Symfony\WebpackEncoreBundle\Tests\DependencyInjection;


use PHPUnit\Framework\TestCase;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;

class ServiceRegistrationTest extends TestCase
{
    public function dataProvider_testLookupServicesCanBeAliased()
    {
        return [
            'lookup' => ['test.lookup', EntrypointLookup::class],
            'lookup_default' => ['test.lookup_default', EntrypointLookup::class],
        ];
    }

    /**
     * @dataProvider dataProvider_testLookupServicesCanBeAliased
     */
    public function testLookupServicesCanBeAliased(string $id, string $class)
    {
        $kernel = new WebpackEncoreServicesTestKernel(__DIR__.'/../fixtures/config/services.xml');
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->assertTrue($container->has($id));

        $service = $container->get($id);
        $this->assertInstanceOf($class, $service);
    }


    public function dataProvider_testLookupServicesCanBeInjected()
    {
        return [
            'dummy1 (with webpack_encore.entrypoint_lookup injected)' => ['test.dummy1'],
            'dummy2 (with webpack_encore.entrypoint_lookup[_default] injected)' => ['test.dummy2'],
        ];
    }

    /**
     * @dataProvider dataProvider_testLookupServicesCanBeInjected
     */
    public function testLookupServicesCanBeInjected(string $id)
    {
        $kernel = new WebpackEncoreServicesTestKernel(__DIR__.'/../fixtures/config/services_injection.xml');
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->assertTrue($container->has($id));

        /** @var DummyService $service */
        $service = $container->get($id);
        $this->assertInstanceOf(DummyService::class, $service);

        $injectedLookupService = $service->getEntryPointLookup();
        $this->assertInstanceOf(EntrypointLookup::class, $injectedLookupService);
    }

}
