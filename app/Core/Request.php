<?php

namespace App\Core;

use App\Core\Validations\UseErrors;
use App\Core\Validations\UseValidationRules;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Validator\Validation;


class Request extends SymfonyRequest
{
    use UseErrors, UseValidationRules;

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
            if (config('app.debug')) {
                $this->returnGenericError();
            } else {
                $this->returnForbiddenError();
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
}
