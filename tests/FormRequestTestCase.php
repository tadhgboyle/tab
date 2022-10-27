<?php

namespace Tests;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\FormRequest as FormRequestContract;

class FormRequestTestCase extends TestCase
{
    public function assertHasErrors($errors, $request): void
    {
        foreach (Arr::wrap($errors) as $key) {
            if (!($request instanceof FormRequest)) {
                $this->fail('Request is not instance of "FormRequest"');
            }

            if (!($request instanceof FormRequestContract)) {
                $this->fail('Request is not instance of "FormRequestContract"');
            }

            $this->assertContains(
                $key,
                Validator::make(
                    $request->all(),
                    collect($request->rules())->only(Arr::wrap($errors))->toArray(),
                )->errors()->keys()
            );
        }
    }

    public function assertNotHaveErrors($errors, FormRequest & FormRequestContract $request): void
    {
        $requestErrors = Validator::make(
            $request->all(),
            collect($request->rules())->only(Arr::wrap($errors))->toArray(),
        )->errors()->keys();

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
