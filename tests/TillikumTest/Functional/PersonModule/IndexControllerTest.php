<?php

namespace TillikumTest\Functional\PersonModule;

use TillikumTest\Functional\ControllerTestCase;

class IndexControllerTest extends ControllerTestCase
{
    public function testIndex()
    {
        $this->dispatch('/person');

        $this->assertResponseCode(200);

        $this->assertQuery('input#search');
    }
}
