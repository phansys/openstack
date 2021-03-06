<?php

namespace OpenStack\Test\Identity\v3\Models;

use OpenStack\Identity\v3\Api;
use OpenStack\Identity\v3\Models\Catalog;
use OpenStack\Identity\v3\Models\Service;
use OpenCloud\Test\TestCase;
use Prophecy\Argument;

class CatalogTest extends TestCase
{
    private $catalog;

    public function setUp()
    {
        $this->rootFixturesDir = dirname(__DIR__);

        parent::setUp();

        $this->catalog = new Catalog($this->client->reveal(), new Api());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_it_throws_if_no_services_set()
    {
        $this->assertFalse($this->catalog->getServiceUrl('', '', '', ''));
    }

    public function test_it_returns_service_url()
    {
        $url = 'http://example.org';

        $service = $this->prophesize(Service::class);
        $service->getUrl('foo', 'bar', 'baz', '')->shouldBeCalled()->willReturn($url);

        $this->catalog->services = [$service->reveal()];

        $this->assertEquals($url, $this->catalog->getServiceUrl('foo', 'bar', 'baz', ''));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_it_throws_if_no_url_found()
    {
        $service = $this->prophesize(Service::class);
        $service->getUrl(Argument::any(), Argument::cetera())->shouldBeCalled()->willReturn(false);

        $this->catalog->services = [$service->reveal()];

        $this->assertFalse($this->catalog->getServiceUrl('', '', '', ''));
    }
}
