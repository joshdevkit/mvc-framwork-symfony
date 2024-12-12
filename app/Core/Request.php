<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;

class Request extends SymfonyRequest
{
    public static function createFromGlobals(): static
    {
        return parent::createFromGlobals();
    }

    public static function createFromSymfonyRequest(SymfonyRequest $request): static
    {
        return new static(
            $request->query->all(),
            $request->request->all(),
            [],
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all()
        );
    }

    public function getMethod(): string
    {
        return $this->server->get('REQUEST_METHOD');
    }

    public function getPath(): string
    {
        return $this->getRequestUri();
    }

    public function validate(array $rules): array
    {
        $validator = Validation::createValidator();
        $constraints = $this->parseRules($rules);
        $data = array_merge($this->query->all(), $this->request->all());

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = $this->formatValidationErrors($violations);
            session(['errors' => $errors]);
            $_SESSION['old_input'] = array_merge($this->query->all(), $this->request->all());

            Redirector::back()->send();
            exit();
        }

        return $data;
    }


    protected function parseRules(array $rules): Assert\Collection
    {
        $constraints = [];
        foreach ($rules as $field => $rule) {
            $rulesArray = explode('|', $rule);
            $fieldConstraints = [];
            foreach ($rulesArray as $r) {
                if ($r === 'required') {
                    $fieldConstraints[] = new Assert\NotBlank();
                } elseif ($r === 'email') {
                    $fieldConstraints[] = new Assert\Email();
                } elseif (preg_match('/min:(\d+)/', $r, $matches)) {
                    $minLength = (int) $matches[1];
                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($field, $minLength) {
                        if (is_string($value) && strlen($value) > 0 && strlen($value) < $minLength) {
                            $context->buildViolation("$field must have at least $minLength characters.")
                                ->addViolation();
                        }
                    });
                } elseif (preg_match('/unique:(\w+),(\w+)/', $r, $matches)) {
                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($matches) {
                        $table = $matches[1];
                        $column = $matches[2];
                        if (Models::exists($table, $column, $value)) {
                            $context->buildViolation("The $column has already been taken.")
                                ->addViolation();
                        }
                    });
                }
            }
            $constraints[$field] = $fieldConstraints;
        }

        return new Assert\Collection($constraints);
    }

    protected function formatValidationErrors(ConstraintViolationList $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $field = trim($violation->getPropertyPath(), '[]');
            $message = $violation->getMessage();

            if ($message === 'This value should not be blank.') {
                $errors[$field] = ["$field is required."];
                continue;
            }

            if (!isset($errors[$field])) {
                $errors[$field] = [];
            }

            $errors[$field][] = "$field $message";
        }

        return $errors;
    }
}
