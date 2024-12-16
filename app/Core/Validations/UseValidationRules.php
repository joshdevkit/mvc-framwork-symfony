<?php


namespace App\Core\Validations;

use App\Core\Models;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\File\UploadedFile;


trait UseValidationRules
{
    protected function parseRules(array $rules): Assert\Collection
    {
        $constraints = [];
        foreach ($rules as $field => $rule) {
            $rulesArray = explode('|', $rule);
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
                    // Consolidating unique validation into a single pattern
                } else if (preg_match('/unique:(\w+),(\w+)(?:,(\d+))?/', $r, $matches)) {
                    $table = $matches[1];
                    $column = $matches[2];
                    $excludeId = isset($matches[3]) ? (int) $matches[3] : null;

                    $fieldConstraints[] = new Assert\Callback(function ($value, $context) use ($table, $column, $excludeId) {
                        if (Models::exists($table, $column, $value, $excludeId)) {
                            $context->buildViolation("The $column has already been taken.")
                                ->addViolation();
                        }
                    });
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
