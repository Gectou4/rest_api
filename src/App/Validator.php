<?php

declare(strict_types=1);

namespace G4\Api\App;

/**
 * Validateur léger pour les paramètres d'entrée API.
 *
 * Usage :
 *   $v = new Validator($params);
 *   $v->required('title')->string()->max(255);
 *   $v->optional('status')->int()->in([1, 2, 3, 4, 5]);
 *   if ($v->fails()) { return $this->fail($v->firstError(), 400); }
 *   $title = $v->get('title');
 */
final class Validator
{
    /** @var array<string, mixed> */
    private array $data;

    /** @var list<string> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $validated = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** Indique si au moins une règle a échoué. */
    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** Retourne la première erreur, ou null si aucune. */
    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /** Retourne toutes les erreurs. */
    public function errors(): array
    {
        return $this->errors;
    }

    /** Retourne la valeur validée d'un champ. */
    public function get(string $key): mixed
    {
        return $this->validated[$key] ?? null;
    }

    /** Retourne toutes les données validées. */
    public function validated(): array
    {
        return $this->validated;
    }

    /** Marque un champ comme requis. */
    public function required(string $key): FieldValidator
    {
        if (!array_key_exists($key, $this->data) || $this->data[$key] === '' || $this->data[$key] === null) {
            $this->errors[] = ucfirst($key) . ' is required';
            return new FieldValidator($this, $key, null);
        }
        return new FieldValidator($this, $key, $this->data[$key]);
    }

    /** Marque un champ comme optionnel (vaut null si absent). */
    public function optional(string $key): FieldValidator
    {
        $value = $this->data[$key] ?? null;
        return new FieldValidator($this, $key, $value);
    }
}

/** Validation d'un champ individuel (pattern fluent). */
final class FieldValidator
{
    private Validator $validator;
    private string $key;
    private mixed $value;
    private bool $skip = false;

    public function __construct(Validator $validator, string $key, mixed $value)
    {
        $this->validator = $validator;
        $this->key = $key;
        $this->value = $value;
    }

    /** Le champ doit être une string non vide. */
    public function string(): self
    {
        if ($this->skip) {
            return $this;
        }
        if ($this->value !== null && !\is_string($this->value)) {
            $this->validator->errors[] = ucfirst($this->key) . ' must be a string';
            $this->skip = true;
        }
        return $this;
    }

    /** Le champ doit être un entier. */
    public function int(): self
    {
        if ($this->skip) {
            return $this;
        }
        if ($this->value !== null && !\is_int($this->value)) {
            $this->validator->errors[] = ucfirst($this->key) . ' must be an integer';
            $this->skip = true;
        }
        return $this;
    }

    /** Longueur maximale pour une string. */
    public function max(int $max): self
    {
        if ($this->skip || $this->value === null) {
            return $this;
        }
        if (\is_string($this->value) && strlen($this->value) > $max) {
            $this->validator->errors[] = ucfirst($this->key) . ' must not exceed ' . $max . ' characters';
            $this->skip = true;
        }
        return $this;
    }

    /** La valeur doit être dans la liste donnée. */
    public function in(array $allowed): self
    {
        if ($this->skip || $this->value === null) {
            return $this;
        }
        if (!in_array($this->value, $allowed, true)) {
            $this->validator->errors[] = ucfirst($this->key) . ' must be one of: ' . implode(', ', $allowed);
            $this->skip = true;
        }
        return $this;
    }

    /** Stocke la valeur validée et retourne le validateur parent. */
    public function end(): Validator
    {
        if (!$this->skip) {
            $this->validator->validated[$this->key] = $this->value;
        }
        return $this->validator;
    }
}
