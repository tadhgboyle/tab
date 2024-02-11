<?php

namespace App\Casts;

use stdClass;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CategoryType implements CastsAttributes
{
    public const TYPE_PRODUCTS_ACTIVITIES = 1;
    public const TYPE_PRODUCTS = 2;
    public const TYPE_ACTIVITIES = 3;

    public const TYPES = [
        self::TYPE_PRODUCTS_ACTIVITIES => 'Products & Activities',
        self::TYPE_PRODUCTS => 'Products',
        self::TYPE_ACTIVITIES => 'Activities',
    ];

    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $return = new stdClass();
        $return->name = $this->getName((int) $value);
        $return->id = (int) $value;

        return $return;
    }

    private function getName(int $type): string
    {
        return match ($type) {
            self::TYPE_PRODUCTS_ACTIVITIES => 'Products & Activities',
            self::TYPE_PRODUCTS => 'Products',
            self::TYPE_ACTIVITIES => 'Activities',
            default => throw new InvalidArgumentException("Invalid category type: {$type}"),
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
