<?php

namespace App\Core;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
            self::normalizeFiles($request->files->all()),
            $request->server->all()
        );
    }

    private static function normalizeFiles(array $files): array
    {
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $files[$key] = self::normalizeFiles($file);
            } elseif ($file instanceof UploadedFile) {
                $files[$key] = $file;
            } else {
                unset($files[$key]);
            }
        }

        return $files;
    }

    public function header($key)
    {
        return $this->headers->get($key);
    }

    public function hasHeader(string $key): bool
    {
        return $this->headers->has($key);
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

    /**
     * Check if the request contains a file with the given key.
     *
     * @param string $key
     * @return bool
     */
    public function hasFile(string $key): bool
    {
        return $this->files->get($key) instanceof UploadedFile;
    }


    /**
     * Retrieve the uploaded file.
     *
     * @param string $key
     * @return UploadedFile|null
     */
    public function file(string $key): ?UploadedFile
    {
        return $this->hasFile($key) ? $this->files->get($key) : null;
    }



    public function validate(array $rules): array
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
        $data = array_merge($this->query->all(), $this->request->all(), $this->files->all());

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
            error_log("Rules on array: " . print_r($rulesArray, true));

            $fieldConstraints = [];
            $isRequired = false;
            $isNullable = false;
            foreach ($rulesArray as $r) {
                if ($r === 'nullable') {
                    $isNullable = true;
                    $fieldConstraints[] = new Assert\Optional();
                    break;
                } elseif ($r === 'required') {
                    if (!$isNullable) {
                        $isRequired = true;
                    }
                } elseif ($r == 'email') {
                    $fieldConstraints[] = new Assert\Email();
                } elseif (preg_match('/min:(\d+)/', $r, $matches)) {
                    $minLength = (int) $matches[1];
                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($minLength) {
                        if (!empty($value) && strlen($value) < $minLength) {
                            $context->buildViolation("This value is too short. It should have $minLength characters or more.")
                                ->addViolation();
                        }
                    });
                } elseif (preg_match('/max:(\d+)/', $r, $matches)) {
                    $maxlength = (int) $matches[1];
                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($maxlength) {
                        if (!empty($value) && strlen($value) > $maxlength) {
                            $context->buildViolation("This value is too long. It should have $maxlength characters only.")
                                ->addViolation();
                        }
                    });
                } elseif ($r === 'image') {
                    $fieldConstraints[] = new Assert\Image([
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg', 'image/jfif', 'image/webp'],
                        'mimeTypesMessage' => "Invalid file type selected",
                    ]);
                } elseif (preg_match('/mimes:([a-zA-Z0-9,_-]+)/', $r, $matches)) {
                    $mimes = explode(',', $matches[1]);
                    $mimes = array_map('trim', $mimes);
                    if (!in_array('image', $rulesArray)) { // Check if "image" rule is already applied
                        $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($mimes, $field) {
                            if ($value && $value instanceof UploadedFile) {
                                $fileMimeType = $value->getMimeType();
                                $fileExtension = explode('/', $fileMimeType)[1] ?? '';
                                if (!in_array($fileExtension, $mimes)) {
                                    $context->buildViolation("Invalid file type selected")
                                        ->addViolation();
                                }
                            }
                        });
                    }
                } elseif (preg_match('/unique:(\w+),(\w+),(\d+)/', $r, $matches)) {
                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($matches) {
                        $table = $matches[1];
                        $column = $matches[2];
                        $excludeId = (int) $matches[3];

                        if (Models::exists($table, $column, $value, $excludeId)) {
                            $context->buildViolation("The $column has already been taken.")
                                ->addViolation();
                        }
                    });
                }
            }

            if ($isRequired && !$isNullable) {
                $fieldConstraints[] = new Assert\NotBlank();
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
            if ($message === "This value should not be blank.") {
                $message = "$field is required";
            }
            if ($message === 'This value is not a valid email address.') {
                $message = "$field is not a valid email address";
            }

            if ($message === "This field is missing.") {
                $message = "$field is required";
            }

            $errors[$field][] = ucfirst($message);
        }
        return $errors;
    }
}
