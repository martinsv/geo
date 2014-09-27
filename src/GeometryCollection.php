<?php

namespace Brick\Geo;

use Brick\Geo\Exception\GeometryException;

/**
 * A GeometryCollection is a geometric object that is a collection of some number of geometric objects.
 *
 * All the elements in a GeometryCollection shall be in the same Spatial Reference System. This is also the Spatial
 * Reference System for the GeometryCollection.
 *
 * GeometryCollection places no other constraints on its elements. Subclasses of GeometryCollection may restrict
 * membership based on dimension and may also place other constraints on the degree of spatial overlap between
 * elements.
 *
 * By the nature of digital representations, collections are inherently ordered by the underlying storage mechanism.
 * Two collections whose difference is only this order are spatially equal and will return equivalent results in any
 * geometric-defined operations.
 */
class GeometryCollection extends Geometry implements \Countable, \IteratorAggregate
{
    /**
     * An array of Geometry objects in the collection.
     *
     * @var Geometry[]
     */
    protected $geometries = [];

    /**
     * Whether the geometries have Z coordinates.
     *
     * @var boolean
     */
    protected $is3D;

    /**
     * Whether the geometries have Z coordinates.
     *
     * @var boolean
     */
    protected $isMeasured;

    /**
     * Class constructor. Use the factory methods to obtain an instance.
     *
     * @param Geometry[] $geometries An array of Geometry objects, validated.
     * @param boolean    $is3D       Whether the geometries have Z coordinates.
     * @param boolean    $isMeasured Whether the geometries have M coordinates.
     * @param integer    $srid       The SRID of the geometries, validated.
     */
    protected function __construct(array $geometries, $is3D, $isMeasured, $srid)
    {
        $this->geometries = $geometries;
        $this->is3D       = $is3D;
        $this->isMeasured = $isMeasured;
        $this->srid       = $srid;
    }

    /**
     * @param array $geometries An array of Geometry objects.
     *
     * @return GeometryCollection
     *
     * @throws GeometryException If the array contains objects not of the current type.
     */
    public static function factory(array $geometries)
    {
        $geometryType = static::containedGeometryType();

        foreach ($geometries as $geometry) {
            if (! $geometry instanceof $geometryType) {
                throw GeometryException::unexpectedGeometryType($geometryType, $geometry);
            }
        }

        self::getDimensions($geometries, $is3D, $isMeasured, $srid);

        return new static(array_values($geometries), $is3D, $isMeasured, $srid);
    }

    /**
     * {@inheritdoc}
     */
    public function is3D()
    {
        return $this->is3D;
    }

    /**
     * {@inheritdoc}
     */
    public function isMeasured()
    {
        return $this->isMeasured;
    }

    /**
     * Returns the number of geometries in this GeometryCollection.
     *
     * @return integer
     */
    public function numGeometries()
    {
        return count($this->geometries);
    }

    /**
     * Returns the Nth geometry in this GeometryCollection.
     *
     * The geometry number is 1-based.
     *
     * @param integer $n
     *
     * @return Geometry
     *
     * @throws GeometryException
     */
    public function geometryN($n)
    {
        if (! is_int($n)) {
            throw new GeometryException('The geometry number must be an integer');
        }

        // decrement the index, as our array is 0-based
        $n--;

        if ($n < 0 || $n >= count($this->geometries)) {
            throw new GeometryException('Geometry number out of range');
        }

        return $this->geometries[$n];
    }

    /**
     * @noproxy
     *
     * {@inheritdoc}
     */
    public function geometryType()
    {
        return 'GeometryCollection';
    }

    /**
     * {@inheritdoc}
     *
     * Returns the largest dimension of the geometries of the collection.
     */
    public function dimension()
    {
        $dimension = -1;

        foreach ($this->geometries as $geometry) {
            $dimension = max($dimension, $geometry->dimension());
        }

        if ($dimension === -1) {
            throw new GeometryException('Empty geometries have no dimension.');
        }

        return $dimension;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        foreach ($this->geometries as $geometry) {
            if (! $geometry->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the total number of geometries in this GeometryCollection.
     *
     * Required by interface Countable.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->geometries);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->geometries);
    }

    /**
     * Returns the FQCN of the contained Geometry type.
     *
     * @return string
     */
    protected static function containedGeometryType()
    {
        return Geometry::class;
    }
}
