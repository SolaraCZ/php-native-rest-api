<?php

declare(strict_types=1);

namespace App\Validation;

class TaskValidator
{
    private array $errors = [];

    /**
     * Sanitizuje a validuje data úkolu.
     * Vrací pole očištěných dat nebo vygeneruje chyby.
     */
    public function validate(array $input, bool $isUpdate = false): ?array
    {
        $this->errors = [];
        $sanitized = [];

        // 1. Validace 'title'
        if (!$isUpdate || array_key_exists('title', $input)) {
            $title = isset($input['title']) ? trim(strip_tags((string)$input['title'])) : '';

            if ($title === '') {
                $this->errors['title'] = 'Název úkolu (title) je povinný.';
            } elseif (mb_strlen($title) < 2) {
                $this->errors['title'] = 'Název úkolu musí mít alespoň 2 znaky.';
            } elseif (mb_strlen($title) > 255) {
                $this->errors['title'] = 'Název úkolu nesmí přesáhnout 255 znaků.';
            } else {
                $sanitized['title'] = $title;
            }
        }

        // 2. Validace 'description'
        if (array_key_exists('description', $input)) {
            if ($input['description'] === null) {
                $sanitized['description'] = null;
            } else {
                $desc = trim(strip_tags((string)$input['description']));
                if (mb_strlen($desc) > 1000) {
                    $this->errors['description'] = 'Popis nesmí přesáhnout 1000 znaků.';
                } else {
                    $sanitized['description'] = $desc;
                }
            }
        } elseif (!$isUpdate) {
            $sanitized['description'] = null;
        }

        // 3. Validace 'completed'
        if (array_key_exists('completed', $input)) {
            $completed = filter_var($input['completed'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($completed === null && $input['completed'] !== 0 && $input['completed'] !== 1) {
                $this->errors['completed'] = 'Hodnota completed musí být boolean (true/false, 1/0).';
            } else {
                $sanitized['completed'] = $completed ? 1 : 0;
            }
        } elseif (!$isUpdate) {
            $sanitized['completed'] = 0;
        }

        return empty($this->errors) ? $sanitized : null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}