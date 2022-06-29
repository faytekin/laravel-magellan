<?php

namespace Clickbar\Postgis\IO\Generator\WKB;

use Clickbar\Postgis\Geometries\Geometry;
use Clickbar\Postgis\Geometries\GeometryCollection;
use Clickbar\Postgis\Geometries\LineString;
use Clickbar\Postgis\Geometries\MultiLineString;
use Clickbar\Postgis\Geometries\MultiPoint;
use Clickbar\Postgis\Geometries\MultiPolygon;
use Clickbar\Postgis\Geometries\Point;
use Clickbar\Postgis\Geometries\Polygon;
use Clickbar\Postgis\IO\Dimension;
use Clickbar\Postgis\IO\Generator\BaseGenerator;
use Clickbar\Postgis\IO\Parser\WKB\ByteOrder;
use Clickbar\Postgis\IO\Parser\WKB\WKBGeometryType;

class WKBGenerator extends BaseGenerator
{
    protected ByteStringBuilder $byteStringBuilder;
    protected ByteOrder $byteOrder;
    protected bool $sridAdded = false;

    public function generate(Geometry $geometry)
    {
        $byteOrder = ByteOrder::littleEndian;
        $this->byteStringBuilder = new ByteStringBuilder($byteOrder);
        $this->sridAdded = false;

        return parent::generate($geometry); // TODO: Change the autogenerated stub
    }

    /**
     * Writes all the coordinates of the given point without the byte order and type code
     * @param Point $point
     */
    private function writePointCoordinates(Point $point)
    {
        $this->byteStringBuilder->addDouble($point->getLongitude());
        $this->byteStringBuilder->addDouble($point->getLatitude());
        if ($point->getDimension()->has3Dimensions()) {
            $this->byteStringBuilder->addDouble($point->getAltitude());
        }
    }

    /**
     * Writes the number of coordinates and all coordinates of the given lineString without the byte order and type code
     * @param LineString $lineString
     */
    private function writeLineStringCoordinates(LineString $lineString)
    {
        $this->byteStringBuilder->addUnsignedLong(count($lineString->getPoints()));
        foreach ($lineString->getPoints() as $point) {
            $this->writePointCoordinates($point);
        }
    }

    private function writeByteOrderAndTypeCode(WKBGeometryType $type, Geometry $geometry)
    {
        $this->byteStringBuilder->addByteOrder();
        $dimension = $geometry->getDimension();

        $typeCode = $type->value;
        if (Dimension::DIMENSION_4D === $dimension ||
            Dimension::DIMENSION_3DZ === $dimension) {
            $typeCode |= 0x80000000;
        }

        if (Dimension::DIMENSION_4D === $dimension ||
            Dimension::DIMENSION_3DM === $dimension) {
            $typeCode |= 0x40000000;
        }

        if (! $this->sridAdded && $geometry->getSrid()) {
            $typeCode |= 0x20000000;
        }

        $this->byteStringBuilder->addUnsignedLong($typeCode);
        if (! $this->sridAdded && $geometry->getSrid()) {
            $this->byteStringBuilder->addUnsignedLong($geometry->getSrid());
            $this->sridAdded = true;
        }
    }

    public function generatePoint(Point $point): mixed
    {
        $this->writeByteOrderAndTypeCode(WKBGeometryType::point, $point);
        $this->writePointCoordinates($point);

        return $this->byteStringBuilder->toByteString(true);
    }

    public function generateLineString(LineString $lineString): mixed
    {
        $this->writeByteOrderAndTypeCode(WKBGeometryType::lineString, $lineString);
        $this->byteStringBuilder->addUnsignedLong(count($lineString->getPoints()));
        foreach ($lineString->getPoints() as $point) {
            $this->writePointCoordinates($point);
        }

        return $this->byteStringBuilder->toByteString(true);
    }

    public function generateMultiLineString(MultiLineString $multiLineString): mixed
    {
        $this->writeByteOrderAndTypeCode(WKBGeometryType::multiLineString, $multiLineString);
        $this->byteStringBuilder->addUnsignedLong(count($multiLineString->getLineStrings()));
        foreach ($multiLineString->getLineStrings() as $lineString) {
            $this->generateLineString($lineString);
        }

        return $this->byteStringBuilder->toByteString(true);
    }

    public function generatePolygon(Polygon $polygon): mixed
    {
        $this->writeByteOrderAndTypeCode(WKBGeometryType::polygon, $polygon);
        $this->byteStringBuilder->addUnsignedLong(count($polygon->getLineStrings()));
        foreach ($polygon->getLineStrings() as $lineString) {
            $this->writeLineStringCoordinates($lineString);
        }

        return $this->byteStringBuilder->toByteString(true);
    }

    public function generateMultiPolygon(MultiPolygon $multiPolygon): mixed
    {
        $this->writeByteOrderAndTypeCode(WKBGeometryType::multiPolygon, $multiPolygon);
        $this->byteStringBuilder->addUnsignedLong(count($multiPolygon->getPolygons()));
        foreach ($multiPolygon->getPolygons() as $polygon) {
            $this->generatePolygon($polygon);
        }

        return $this->byteStringBuilder->toByteString(true);
    }

    public function generateMultiPoint(MultiPoint $multiPoint): mixed
    {
        $this->writeByteOrderAndTypeCode(WKBGeometryType::multiPoint, $multiPoint);
        $this->byteStringBuilder->addUnsignedLong(count($multiPoint->getPoints()));
        foreach ($multiPoint->getPoints() as $point) {
            $this->generatePoint($point);
        }

        return $this->byteStringBuilder->toByteString(true);
    }

    public function generateGeometryCollection(GeometryCollection $geometryCollection): mixed
    {
        $this->writeByteOrderAndTypeCode(WKBGeometryType::geometryCollection, $geometryCollection);
        $this->byteStringBuilder->addUnsignedLong(count($geometryCollection->getGeometries()));
        foreach ($geometryCollection->getGeometries() as $geometry) {
            parent::generate($geometry);
        }

        return $this->byteStringBuilder->toByteString(true);
    }
}
