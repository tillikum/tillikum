<?php

namespace TillikumTest\Fixture;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Tillikum\Entity\Person\Person;
use Tillikum\Entity\Person\Image;
use Tillikum\Entity\Person\Tag;
use Tillikum\Entity\Person\Address;

class PersonData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $at1 = new Address\Type();
        $at1->id = 'campus';
        $at1->name = 'Campus';

        $manager->persist($at1);

        $t1 = new Tag();
        $t1->id = 'tag1';
        $t1->name = 'Tag one';
        $t1->is_active = true;

        $manager->persist($t1);

        $t2 = new Tag();
        $t2->id = 'tag2';
        $t2->name = 'Tag two';
        $t2->is_active = false;

        $manager->persist($t2);

        $p1 = new Person();
        $p1->id = 'p1';
        $p1->given_name = 'p1g';
        $p1->middle_name = 'p1m';
        $p1->family_name = 'p1f';
        $p1->gender = 'F';
        $p1->note = 'test';
        $p1->created_at = new DateTime();
        $p1->created_by = 'test';
        $p1->updated_at = new DateTime();
        $p1->updated_by = 'test';

        $p1->tags->add($t1);

        $manager->persist($p1);

        $i1 = new Image();
        $i1->person = $p1;
        $i1->image = '<PRETEND THIS IS BINARY DATA>';

        $manager->persist($i1);

        $p2 = new Person();
        $p1->id = 'p1';
        $p2->given_name = 'p2g';
        $p2->middle_name = 'p2m second middle name';
        $p2->family_name = 'p2f';
        $p2->gender = 'M';
        $p2->note = 'test';
        $p2->created_at = new DateTime();
        $p2->created_by = 'test';
        $p2->updated_at = new DateTime();
        $p2->updated_by = 'test';

        $manager->persist($p2);

        $ae1 = new Address\Email;
        $ae1->person = $p1;
        $ae1->type = $at1;
        $ae1->value = 'test@test.com';
        $ae1->is_primary = true;
        $ae1->created_at = new DateTime();
        $ae1->created_by = 'test';
        $ae1->updated_at = new DateTime();
        $ae1->updated_by = 'test';

        $manager->persist($ae1);

        $ap1 = new Address\PhoneNumber;
        $ap1->person = $p1;
        $ap1->type = $at1;
        $ap1->value = '123-456-7890';
        $ap1->is_primary = true;
        $ap1->created_at = new DateTime();
        $ap1->created_by = 'test';
        $ap1->updated_at = new DateTime();
        $ap1->updated_by = 'test';

        $manager->persist($ap1);

        $as1 = new Address\Street;
        $as1->person = $p1;
        $as1->type = $at1;
        $as1->street = "1234 Test Lane\nApt. #34";
        $as1->locality = 'Testville';
        $as1->region = 'Testville';
        $as1->postal_code = '12345';
        $as1->country = 'Deutschland';
        $as1->is_primary = true;
        $as1->created_at = new DateTime();
        $as1->created_by = 'test';
        $as1->updated_at = new DateTime();
        $as1->updated_by = 'test';

        $manager->persist($as1);

        $manager->flush();
    }
}
