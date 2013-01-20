<?php

namespace TillikumTest\Functional\PersonModule;

use TillikumTest\Functional\ControllerTestCase;

class PersonControllerTest extends ControllerTestCase
{
    public function testAutocomplete()
    {
        $this->request->setQuery(array(
            'q' => 'p1f'
        ));

        $this->dispatch('/person/person/autocomplete');

        $this->assertHeaderContains('Content-Type', 'application/json');

        $decodedBody = json_decode($this->response->getBody(), true);

        $this->assertEquals(1, count($decodedBody));

        $this->assertEquals(4, count($decodedBody[0]));
        $this->assertArrayHasKey('key', $decodedBody[0]);
        $this->assertArrayHasKey('value', $decodedBody[0]);
        $this->assertArrayHasKey('label', $decodedBody[0]);
        $this->assertArrayHasKey('uri', $decodedBody[0]);

        $this->assertEquals('p1f, p1g p1m', $decodedBody[0]['label']);
    }

    public function testView()
    {
        $this->dispatch('/person/person/view/id/p1');

        $this->assertResponseCode(200);

        $this->assertQueryContentContains(
            'div.yui3-u-1-3>section>h1',
            'p1f, p1g p1m'
        );
    }

    public function testViewNotFound()
    {
        $this->dispatch('/person/person/view/id/invalid');

        $this->assertResponseCode(404);
    }
}
