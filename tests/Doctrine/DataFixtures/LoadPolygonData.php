<?php

namespace Brick\Geo\Tests\Doctrine\DataFixtures;

use Brick\Geo\LineString;
use Brick\Geo\Point;
use Brick\Geo\Polygon;
use Brick\Geo\Tests\Doctrine\Fixtures\PolygonEntity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPolygonData implements FixtureInterface {

    /**
     * {@inheritdoc}
     */
    function load(ObjectManager $manager)
    {
        $point1 = Point::xy(0,0);
        $point2 = Point::xy(1,0);
        $point3 = Point::xy(1,1);
        $point4 = Point::xy(0,1);
        $point5 = Point::xy(0,0);

        $ring = LineString::factory([$point1, $point2, $point3, $point4, $point5]);

        $poly1 = new PolygonEntity();
        $poly1->setPolygon(Polygon::factory([$ring]));

        $manager->persist($poly1);
        $manager->flush();
    }
}
