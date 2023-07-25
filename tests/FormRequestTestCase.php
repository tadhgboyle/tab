<?php

namespace Tests;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\FormRequest as FormRequestContract;

class FormRequestTestCase extends TestCase
{
    public function assertHasErrors($errors, FormRequest&FormRequestContract $request): void
    {
        foreach (Arr::wrap($errors) as $key) {
            $validator = Validator::make(
                $request->all(),
                collect($request->rules())->only(Arr::wrap($errors))->toArray(),
            );

            if (method_exists($request, 'withValidator')) {
                $request->withValidator($validator);
            }

            $this->assertContains(
                $key,
                $validator->errors()->keys()
            );
        }
    }

    public function assertNotHaveErrors($errors, FormRequest&FormRequestContract $request): void
    {
        $validator = Validator::make(
            $request->all(),
            collect($request->rules())->only(Arr::wrap($errors))->toArray(),
        );

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        $requestErrors = $validator->errors()->keys();

        $errors = Arr::wrap($errors);
        if (empty($errors)) {
            $this->assertEmpty(
                $requestErrors,
            );
        } else {
            foreach ($errors as $key) {
                $this->assertNotContains(
                    $key,
                    $requestErrors,
                );
            }
        }
    }
}
