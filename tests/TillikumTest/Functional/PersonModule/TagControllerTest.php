<?php

namespace TillikumTest\Functional\PersonModule;

use TillikumTest\Functional\ControllerTestCase;

class TagControllerTest extends ControllerTestCase
{
    public function testIndex()
    {
        $this->dispatch('/person/tag');

        $this->assertResponseCode(200);

        $this->assertQueryCount('table', 1);
    }

    public function testCreate()
    {
        $this->dispatch('/person/tag/create');

        $this->assertResponseCode(200);

        $this->assertQueryCount('section>form', 1);
    }

    public function testDelete()
    {
        $this->dispatch('/person/tag/delete/id/tag1');

        $this->assertResponseCode(200);

        $this->assertQueryCount('section>form', 1);
    }

    public function testDeleteNotFound()
    {
        $this->dispatch('/person/tag/delete/id/notfound');

        $this->assertResponseCode(404);
    }

    public function testEdit()
    {
        $this->dispatch('/person/tag/edit/id/tag1');

        $this->assertResponseCode(200);

        $this->assertQueryCount('section>form', 1);
    }

    public function testEditNotFound()
    {
        $this->dispatch('/person/tag/edit/id/notfound');

        $this->assertResponseCode(404);
    }
}
