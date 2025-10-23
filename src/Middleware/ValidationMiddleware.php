<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Services\ApiResponseService;

class ValidationMiddleware
{
    private array $rules;
    private array $fieldNames;

    public function __construct(array $rules = [], array $fieldNames = [])
    {
        $this->rules = $rules;
        $this->fieldNames = $fieldNames;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (empty($this->rules)) {
            return $handler->handle($request);
        }

        $body = $request->getBody()->getContents();
        $data = json_decode($body, true) ?? [];

        $errors = $this->validateData($data);

        if (!empty($errors)) {
            $response = new \Slim\Psr7\Response();
            return ApiResponseService::error(
                $response,
                'Dados de entrada inválidos',
                422,
                $errors
            );
        }

        // Adicionar dados validados ao request
        $request = $request->withAttribute('validated_data', $data);

        return $handler->handle($request);
    }

    private function validateData(array $data): array
    {
        $errors = [];

        foreach ($this->rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $fieldName = $this->fieldNames[$field] ?? $field;

            $fieldErrors = $this->validateField($field, $value, $rule, $fieldName);
            if (!empty($fieldErrors)) {
                $errors[$field] = $fieldErrors;
            }
        }

        return $errors;
    }

    private function validateField(string $field, $value, string $rule, string $fieldName): array
    {
        $errors = [];
        $rules = explode('|', $rule);

        foreach ($rules as $singleRule) {
            $ruleParts = explode(':', $singleRule);
            $ruleName = $ruleParts[0];
            $ruleValue = $ruleParts[1] ?? null;

            switch ($ruleName) {
                case 'required':
                    if (empty($value) && $value !== 0) {
                        $errors[] = "O campo {$fieldName} é obrigatório";
                    }
                    break;

                case 'email':
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "O campo {$fieldName} deve ser um email válido";
                    }
                    break;

                case 'min':
                    if (!empty($value) && strlen($value) < (int)$ruleValue) {
                        $errors[] = "O campo {$fieldName} deve ter pelo menos {$ruleValue} caracteres";
                    }
                    break;

                case 'max':
                    if (!empty($value) && strlen($value) > (int)$ruleValue) {
                        $errors[] = "O campo {$fieldName} deve ter no máximo {$ruleValue} caracteres";
                    }
                    break;

                case 'numeric':
                    if (!empty($value) && !is_numeric($value)) {
                        $errors[] = "O campo {$fieldName} deve ser numérico";
                    }
                    break;

                case 'boolean':
                    if (!empty($value) && !is_bool($value) && !in_array($value, ['true', 'false', '1', '0'])) {
                        $errors[] = "O campo {$fieldName} deve ser verdadeiro ou falso";
                    }
                    break;
            }
        }

        return $errors;
    }
}
