<?php

namespace TillikumTest\Fixture;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Tillikum\Entity\Facility\Facility;
use Tillikum\Entity\FacilityGroup\Building\Building;
use Tillikum\Entity\FacilityGroup\Config\Config as FacilityGroupConfig;
use Tillikum\Entity\FacilityGroup\Config\Building\Building as BuildingConfig;
use Tillikum\Entity\FacilityGroup\FacilityGroup;
use Tillikum\Entity\Facility\Config\Config as FacilityConfig;
use Tillikum\Entity\Facility\Config\Room\Room as RoomConfig;
use Tillikum\Entity\Facility\Config\Room\Type as RoomType;
use Tillikum\Entity\Facility\Room\Room;
use Tillikum\Entity\Person\Image;
use Tillikum\Entity\Person\Tag;
use Tillikum\Entity\Person\Address;

class FacilityData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $fg1 = new FacilityGroup();
        $fg1->id = 'fg1';
        $fg1->created_at = new DateTime();
        $fg1->created_by = 'test';
        $fg1->updated_at = new DateTime();
        $fg1->updated_by = 'test';
        $manager->persist($fg1);

        $fgc1 = new FacilityGroupConfig();
        $fgc1->id = 1;
        $fgc1->facility_group = $fg1;
        $fgc1->name = 'fg1 c1';
        $fgc1->start = new DateTime('2010-01-01');
        $fgc1->end = new DateTime('2020-12-31');
        $fgc1->gender = 'M';
        $fgc1->created_at = new DateTime();
        $fgc1->created_by = 'test';
        $fgc1->updated_at = new DateTime();
        $fgc1->updated_by = 'test';
        $manager->persist($fgc1);

        $f1 = new Facility();
        $f1->facility_group = $fg1;
        $f1->id = 'f1';
        $f1->created_at = new DateTime();
        $f1->created_by = 'test';
        $f1->updated_at = new DateTime();
        $f1->updated_by = 'test';
        $manager->persist($f1);

        $fc1 = new FacilityConfig();
        $fc1->id = 1;
        $fc1->facility = $f1;
        $fc1->name = 'f1 c1';
        $fc1->start = new DateTime('2010-01-01');
        $fc1->end = new DateTime('2020-12-31');
        $fc1->gender = 'M';
        $fc1->capacity = 1;
        $fc1->note = 'Facility 1’s note.';
        $fc1->created_at = new DateTime();
        $fc1->created_by = 'test';
        $fc1->updated_at = new DateTime();
        $fc1->updated_by = 'test';
        $manager->persist($fc1);

        $b1 = new Building();
        $b1->id = 'b1';
        $b1->created_at = new DateTime();
        $b1->created_by = 'test';
        $b1->updated_at = new DateTime();
        $b1->updated_by = 'test';
        $manager->persist($b1);

        $bc1 = new BuildingConfig();
        $bc1->id = 2;
        $bc1->facility_group = $b1;
        $bc1->name = 'b1 c1';
        $bc1->start = new DateTime('2010-01-01');
        $bc1->end = new DateTime('2020-12-31');
        $bc1->gender = 'M';
        $bc1->created_at = new DateTime();
        $bc1->created_by = 'test';
        $bc1->updated_at = new DateTime();
        $bc1->updated_by = 'test';
        $manager->persist($bc1);

        $rt1 = new RoomType();
        $rt1->id = 'SGL';
        $rt1->name = 'Single';
        $rt1->is_active = true;
        $manager->persist($rt1);

        $rt2 = new RoomType();
        $rt2->id = 'DBL';
        $rt2->name = 'Double';
        $rt2->is_active = true;
        $manager->persist($rt2);

        $r1 = new Room();
        $r1->facility_group = $b1;
        $r1->id = 'r1';
        $r1->created_at = new DateTime();
        $r1->created_by = 'test';
        $r1->updated_at = new DateTime();
        $r1->updated_by = 'test';
        $manager->persist($r1);

        $rc1 = new RoomConfig();
        $rc1->id = 1;
        $rc1->facility = $r1;
        $rc1->name = 'r1 c1';
        $rc1->start = new DateTime('2010-01-01');
        $rc1->end = new DateTime('2015-12-31');
        $rc1->gender = 'M';
        $rc1->capacity = 1;
        $rc1->type = $rt1;
        $rc1->note = 'Room 1’s note.';
        $rc1->created_at = new DateTime();
        $rc1->created_by = 'test';
        $rc1->updated_at = new DateTime();
        $rc1->updated_by = 'test';
        $manager->persist($rc1);

        $rc2 = new RoomConfig();
        $rc2->id = 2;
        $rc2->facility = $r1;
        $rc2->name = 'r1 c2';
        $rc2->start = new DateTime('2016-01-01');
        $rc2->end = new DateTime('2020-12-31');
        $rc2->gender = 'M';
        $rc2->capacity = 2;
        $rc2->type = $rt2;
        $rc2->note = 'Room 2’s note.';
        $rc2->created_at = new DateTime();
        $rc2->created_by = 'test';
        $rc2->updated_at = new DateTime();
        $rc2->updated_by = 'test';
        $manager->persist($rc2);

        $manager->flush();
    }
}
