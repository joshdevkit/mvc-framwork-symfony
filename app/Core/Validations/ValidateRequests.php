<?php


namespace joshdevjp\mvccore\Validations;

use joshdevjp\mvccore\Request;

trait ValidateRequests
{
    /**
     * Run the validation routine against the given validator.
     *
     * @param  \Validation\Validator|array  $validator
     * @param  \SymfonyRequest\Http\Request|null  $request
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateWith($validator, ?Request $request = null)
    {
        $request = $request ?: request();

        if (is_array($validator)) {
            $validator = $this->getValidationFactory()->make($request->all(), $validator);
        }

        if ($request->isPrecognitive()) {
            $validator->after(Precognition::afterValidationHook($request))
                ->setRules(
                    $request->filterPrecognitiveRules($validator->getRulesWithoutPlaceholders())
                );
        }

        return $validator->validate();
    }


    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $attributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(
        Request $request,
        array $rules,
        array $messages = [],
        array $attributes = []
    ) {
        $validator = $this->getValidationFactory()->make(
            $request->all(),
            $rules,
            $messages,
            $attributes
        );

        if ($request->isPrecognitive()) {
            $validator->after(Precognition::afterValidationHook($request))
                ->setRules(
                    $request->filterPrecognitiveRules($validator->getRulesWithoutPlaceholders())
                );
        }

        return $validator->validate();
    }
}
