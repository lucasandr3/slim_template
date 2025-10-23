<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Psr\Http\Message\ServerRequestInterface as Request;
use App\Exceptions\ValidationException;

abstract class FormRequest
{
    protected array $data = [];
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->data = $this->parseData();
        $this->validate();
    }

    /**
     * Parse dos dados da requisição
     */
    protected function parseData(): array
    {
        $data = $this->request->getParsedBody();
        
        if ($data === null) {
            $rawBody = (string)$this->request->getBody();
            $data = json_decode($rawBody, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ValidationException('JSON inválido', [
                    'json' => 'Formato JSON inválido: ' . json_last_error_msg()
                ]);
            }
        }
        
        return $data ?? [];
    }

    /**
     * Executa a validação
     */
    protected function validate(): void
    {
        $rules = $this->rules();
        $errors = [];

        foreach ($rules as $field => $rule) {
            $this->validateField($field, $rule, $errors);
        }

        if (!empty($errors)) {
            throw new ValidationException('Dados de entrada inválidos', $errors);
        }
    }

    /**
     * Valida um campo específico
     */
    protected function validateField(string $field, string $rule, array &$errors): void
    {
        $value = $this->data[$field] ?? null;
        $ruleParts = explode('|', $rule);

        foreach ($ruleParts as $singleRule) {
            $this->applyRule($field, $value, $singleRule, $errors);
        }
    }

    /**
     * Aplica uma regra específica
     */
    protected function applyRule(string $field, mixed $value, string $rule, array &$errors): void
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleValue = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    $errors[$field] = $this->getFieldName($field) . ' é obrigatório';
                }
                break;

            case 'email':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = $this->getFieldName($field) . ' deve ser um email válido';
                }
                break;

            case 'min':
                if ($value !== null && strlen((string)$value) < (int)$ruleValue) {
                    $errors[$field] = $this->getFieldName($field) . " deve ter pelo menos {$ruleValue} caracteres";
                }
                break;

            case 'max':
                if ($value !== null && strlen((string)$value) > (int)$ruleValue) {
                    $errors[$field] = $this->getFieldName($field) . " deve ter no máximo {$ruleValue} caracteres";
                }
                break;

            case 'numeric':
                if ($value !== null && !is_numeric($value)) {
                    $errors[$field] = $this->getFieldName($field) . ' deve ser um número';
                }
                break;

            case 'boolean':
                if ($value !== null && !is_bool($value) && !in_array($value, ['true', 'false', '1', '0', 1, 0], true)) {
                    $errors[$field] = $this->getFieldName($field) . ' deve ser verdadeiro ou falso';
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if ($value !== null && !in_array($value, $allowedValues, true)) {
                    $errors[$field] = $this->getFieldName($field) . ' deve ser um dos valores: ' . implode(', ', $allowedValues);
                }
                break;

            case 'unique':
                $this->validateUnique($field, $value, $ruleValue, $errors);
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== null && ($this->data[$confirmField] ?? null) !== $value) {
                    $errors[$confirmField] = $this->getFieldName($field) . ' não confere';
                }
                break;
        }
    }

    /**
     * Validação de unicidade
     */
    protected function validateUnique(string $field, mixed $value, string $ruleValue, array &$errors): void
    {
        if ($value === null) {
            return;
        }

        $parts = explode(',', $ruleValue);
        $table = $parts[0];
        $column = $parts[1] ?? $field;
        $ignoreId = $parts[2] ?? null;

        // Aqui você pode implementar a verificação no banco de dados
        // Por enquanto, vamos apenas verificar se o valor não está vazio
        if (empty($value)) {
            $errors[$field] = $this->getFieldName($field) . ' não pode estar vazio';
        }
    }

    /**
     * Retorna o nome amigável do campo
     */
    protected function getFieldName(string $field): string
    {
        $names = $this->fieldNames();
        return $names[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Retorna os dados validados
     */
    public function validated(): array
    {
        return $this->data;
    }

    /**
     * Retorna um campo específico
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Retorna todos os dados exceto os especificados
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->data, array_flip($keys));
    }

    /**
     * Retorna apenas os campos especificados
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->data, array_flip($keys));
    }

    /**
     * Regras de validação (deve ser implementado pelas classes filhas)
     */
    abstract protected function rules(): array;

    /**
     * Nomes amigáveis dos campos (opcional)
     */
    protected function fieldNames(): array
    {
        return [];
    }

    /**
     * Mensagens customizadas de erro (opcional)
     */
    protected function messages(): array
    {
        return [];
    }
}
