<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Security\Csrf\CsrfToken;
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

    public function validateCsrfToken(): bool
    {
        $token = $this->request->get('_token');
        $sessionToken = $_SESSION['csrf_tokens']['_token'] ?? null;
        if (!$token) {
            return false;
        }
        return $sessionToken === $token;
    }


    public function validate(array $rules, array $customMessages = []): array
    {
        if (!$this->validateCsrfToken()) {
            http_response_code(403);
            $errorViewPath = __DIR__ . '/../../resources/views/errors/500.php';

            if (file_exists($errorViewPath)) {
                include $errorViewPath;
            } else {
                echo 'Error: CSRF validation failed!';
            }
            exit();
        }

        $validator = Validation::createValidator();
        $constraints = $this->parseRules($rules);
        $data = array_merge($this->query->all(), $this->request->all());

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = $this->formatValidationErrors($violations, $customMessages);
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
        $data = array_merge($this->query->all(), $this->request->all());

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
                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($minLength) {
                        if (!empty($value) && strlen($value) < $minLength) {
                            $context->buildViolation("This value is too short. It should have $minLength characters or more.")
                                ->addViolation();
                        }
                    });
                } elseif (preg_match('/integer/', $r)) {
                    $fieldConstraints[] = new Assert\Type(['type' => 'integer']);
                } elseif (preg_match('/unique:(\w+),(\w+)/', $r, $matches)) {
                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($matches) {
                        $table = $matches[1];
                        $column = $matches[2];

                        if (Models::exists($table, $column, $value)) {
                            $context->buildViolation("The $column has already been taken.")
                                ->addViolation();
                        }
                    });
                } elseif ($r === 'confirmed') {
                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($field, $data) {
                        $confirmField = 'confirm_' . $field;

                        if (!empty($data[$field]) && isset($data[$confirmField]) && $value !== $data[$confirmField]) {
                            $context->buildViolation('Password and confirm password do not match.')
                                ->addViolation();
                        }
                    });
                } elseif (preg_match('/files:(.+)/', $r, $matches)) {
                    $mimeTypes = explode(',', $matches[1]);
                    $fieldConstraints[] = new Assert\File([
                        'mimeTypes' => $mimeTypes,
                        'mimeTypesMessage' => 'Invalid file type provided.',
                    ]);
                }
            }

            $constraints[$field] = $fieldConstraints;
        }

        return new Assert\Collection([
            'fields' => $constraints,
            'allowExtraFields' => true,
        ]);
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
