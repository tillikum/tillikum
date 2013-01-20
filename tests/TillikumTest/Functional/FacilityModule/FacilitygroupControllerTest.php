<?php

namespace TillikumTest\Functional\FacilityModule;

use TillikumTest\Functional\ControllerTestCase;

class FacilitygroupControllerTest extends ControllerTestCase
{
    public function testAutocomplete()
    {
        $this->request->setQuery(array(
            'q' => 'b'
        ));

        $this->dispatch('/facility/facilitygroup/autocomplete');

        $this->assertHeaderContains('Content-Type', 'application/json');

        $decodedBody = json_decode($this->response->getBody(), true);

        $this->assertEquals(1, count($decodedBody));

        $this->assertEquals(4, count($decodedBody[0]));
        $this->assertArrayHasKey('key', $decodedBody[0]);
        $this->assertArrayHasKey('value', $decodedBody[0]);
        $this->assertArrayHasKey('label', $decodedBody[0]);
        $this->assertArrayHasKey('uri', $decodedBody[0]);

        $this->assertEquals('b1 c1', $decodedBody[0]['label']);
    }
}
