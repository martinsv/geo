<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryParseException;

/**
 * Reads geometries from the Extended WKT format designed by PostGIS.
 */
class EWKTReader extends WKTAbstractReader
{
    /**
     * @param string $ewkt The EWKT to read.
     *
     * @return \Brick\Geo\Geometry
     *
     * @throws GeometryParseException
     */
    public function read($ewkt)
    {
        $parser = new EWKTParser(strtoupper($ewkt));
        $srid = $parser->getOptionalSRID();
        $geometry = $this->readGeometry($parser, $srid);

        if (! $parser->isEndOfStream()) {
            throw GeometryParseException::invalidEWKT();
        }

        return $geometry;
    }
}
