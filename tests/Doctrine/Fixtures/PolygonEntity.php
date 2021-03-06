<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Polygon;

/**
 * Class PolygonEntity
 *
 * @Entity
 * @Table(name = "polygons")
 */
class PolygonEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="polygon")
     *
     * @var Polygon
     */
    private $polygon;

    /**
     * @return Polygon
     */
    public function getPolygon()
    {
        return $this->polygon;
    }

    /**
     * @param Polygon $polygon
     */
    public function setPolygon($polygon)
    {
        $this->polygon = $polygon;
    }
}
