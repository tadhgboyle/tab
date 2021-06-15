<?php

namespace App\Casts;

use stdClass;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CategoryType implements CastsAttributes
{
    public const TYPE_PRODUCTS_ACTIVITIES = 1;
    public const TYPE_PRODUCTS = 2;
    public const TYPE_ACTIVITIES = 3;

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $return = new stdClass();
        $return->name = $this->getName(intval($value));
        $return->id = intval($value);

        return $return;
    }

    private function getName(int $type): string
    {
        switch ($type) {
            case self::TYPE_PRODUCTS_ACTIVITIES:
                return 'Products & Activities';
            case self::TYPE_PRODUCTS:
                return 'Products';
            case self::TYPE_ACTIVITIES:
                return 'Activities';
            default:
                return $type;
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
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
