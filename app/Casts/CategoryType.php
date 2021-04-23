<?php

namespace App\Casts;

use stdClass;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CategoryType implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $return = new stdClass();
        $return->name = $this->getName(intval($value));
        $return->id = intval($value);

        return $return;
    }

    private function getName($type): string
    {
        switch ($type) {
            case 1:
                return 'Products & Activities';
            case 2:
                return 'Products';
            case 3:
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
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
    
}
