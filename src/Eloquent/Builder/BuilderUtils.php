<?php

namespace Clickbar\Magellan\Eloquent\Builder;

use Clickbar\Magellan\Boxes\Box2D;
use Clickbar\Magellan\Boxes\Box3D;
use Clickbar\Magellan\Geometries\Geometry;
use Clickbar\Magellan\IO\Generator\WKT\WKTGenerator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Config;

class BuilderUtils
{
    public static function buildPostgisFunction(Builder|EloquentBuilder $builder, string $bindingType, ?string $geometryType, string $function, ?string $as = null, ...$params): Expression
    {
        if ($builder instanceof EloquentBuilder) {
            $builder = $builder->getQuery();
        }

        $builder = invade($builder);
        $geometryTypeCastAppend = $geometryType ? "::$geometryType" : '';

        foreach ($params as $i => $param) {
            if ($builder->isQueryable($param)) {
                [$sub, $bindings] = $builder->createSub($param);

                array_splice($params, $i, 1, [new Expression("($sub)$geometryTypeCastAppend")]);

                return BuilderUtils::buildPostgisFunction(
                    $builder->addBinding($bindings, $bindingType), $bindingType, $geometryType, $function, $as, ...$params
                );
            }
            if ($param instanceof BindingExpression) {
                $builder->addBinding([$param->getValue()], $bindingType);
            }
        }

        $wktGenerator = new WKTGenerator();
        $params = array_map(function ($param) use ($geometryTypeCastAppend, $wktGenerator) {
            if ($param instanceof Geometry) {
                return $wktGenerator->toPostgisGeometrySql($param, Config::get('magellan.schema')).$geometryTypeCastAppend;
            }

            if ($param instanceof Box2D) {
                return "'{$param->toString()}'::box2d";
            }

            if ($param instanceof Box3D) {
                return "'{$param->toString()}'::box3d";
            }

            if ($param instanceof Expression) {
                if (is_bool($param->getValue())) {
                    return $param->getValue() ? 'true' : 'false';
                }

                return $param;
            }

            if ($param instanceof BindingExpression) {
                return '?';
            }

            return $param.$geometryTypeCastAppend;
        }, $params);

        $paramString = implode(', ', array_map(fn ($param) => (string) $param, $params));
        $expressionString = "$function($paramString)";

        if ($as) {
            $expressionString .= " AS $as";
        }

        return new Expression($expressionString);
    }

    public static function appendAsBindingExpressionIfNotNull(array &$array, ...$values)
    {
        foreach ($values as $value) {
            if ($value != null) {
                $array[] = new BindingExpression($value);
            }
        }
    }
}
