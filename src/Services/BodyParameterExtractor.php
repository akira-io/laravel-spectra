<?php

declare(strict_types=1);

namespace Akira\Spectra\Services;

use Illuminate\Routing\Route;
use ReflectionClass;
use ReflectionMethod;

final readonly class BodyParameterExtractor
{
    /**
     *
     * @return array<string, array{type: string, required: bool, rules: string}>
     */
    public function extract(Route $route): array
    {
        $action = $route->getAction();
        
        if (!isset($action['controller'])) {
            if (isset($action['uses']) && $action['uses'] instanceof \Closure) {
                return $this->extractFromClosure($action['uses']);
            }
            return [];
        }

        [$controller, $method] = explode('@', $action['controller']);

        if (!class_exists($controller)) {
            return [];
        }

        try {
            $reflection = new ReflectionClass($controller);
            $methodReflection = $reflection->getMethod($method);
            
            $parameters = $this->extractFromFormRequest($methodReflection);
            
            if (!empty($parameters)) {
                return $parameters;
            }
       
            $parameters = $this->extractFromMethodBody($methodReflection);
            
            if (!empty($parameters)) {
                return $parameters;
            }
            
        } catch (\ReflectionException $e) {
            return [];
        }

        return [];
    }

    /**
     * @return array<string, array{type: string, required: bool, rules: string}>
     */
    private function extractFromFormRequest(ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            
            if (!$type || $type->isBuiltin()) {
                continue;
            }
            
            $typeName = $type->getName();
            
            if (!class_exists($typeName)) {
                continue;
            }
            
            try {
                $reflection = new ReflectionClass($typeName);
                
                if (!$reflection->isSubclassOf('Illuminate\Foundation\Http\FormRequest')) {
                    continue;
                }
                
                if ($reflection->hasMethod('rules')) {
                    $instance = $reflection->newInstanceWithoutConstructor();
                    $rules = $instance->rules();
                    
                    foreach ($rules as $field => $rule) {
                        $ruleString = is_array($rule) ? implode('|', $rule) : $rule;
                        $parameters[$field] = [
                            'type' => $this->inferType($ruleString),
                            'required' => $this->isRequired($ruleString),
                            'rules' => $ruleString,
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $parameters;
    }

    private function inferType(string $rules): string
    {
        $rules = strtolower($rules);
        
        if (str_contains($rules, 'integer') || str_contains($rules, 'numeric')) {
            return 'integer';
        }
        
        if (str_contains($rules, 'boolean') || str_contains($rules, 'bool')) {
            return 'boolean';
        }
        
        if (str_contains($rules, 'array')) {
            return 'array';
        }
        
        if (str_contains($rules, 'email') || str_contains($rules, 'url')) {
            return 'string';
        }
        
        if (str_contains($rules, 'date')) {
            return 'string';
        }
        
        return 'string';
    }

    private function isRequired(string $rules): bool
    {
        $rules = strtolower($rules);
        return str_contains($rules, 'required') && !str_contains($rules, 'sometimes');
    }

    /**
     * Extract from closure routes
     * @return array<string, array{type: string, required: bool, rules: string}>
     */
    private function extractFromClosure(\Closure $closure): array
    {
        try {
            $reflection = new \ReflectionFunction($closure);
            return $this->extractFromMethodBody($reflection);
        } catch (\ReflectionException $e) {
            return [];
        }
    }

    /**
     * Extract validation rules from method body using static analysis
     * @return array<string, array{type: string, required: bool, rules: string}>
     */
    private function extractFromMethodBody(\ReflectionFunctionAbstract $reflection): array
    {
        $parameters = [];
        
        try {
            $filename = $reflection->getFileName();
            $startLine = $reflection->getStartLine();
            $endLine = $reflection->getEndLine();
            
            if (!$filename || !$startLine || !$endLine) {
                return [];
            }
            
            $lines = file($filename);
            if ($lines === false) {
                return [];
            }
            
            $methodBody = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));
            
            // Pattern to match $request->validate([...])
            $pattern = '/\$request\s*->\s*validate\s*\(\s*\[(.*?)\]\s*\)/s';
            
            if (preg_match($pattern, $methodBody, $matches)) {
                $rulesArray = $matches[1];
                
                // Parse each rule: 'field' => 'rules'
                $rulePattern = '/[\'"]([a-zA-Z0-9_\.]+)[\'"]\s*=>\s*[\'"](.*?)[\'"]/';
                
                if (preg_match_all($rulePattern, $rulesArray, $ruleMatches, PREG_SET_ORDER)) {
                    foreach ($ruleMatches as $match) {
                        $field = $match[1];
                        $rules = $match[2];
                        
                        $parameters[$field] = [
                            'type' => $this->inferType($rules),
                            'required' => $this->isRequired($rules),
                            'rules' => $rules,
                        ];
                    }
                }
                
                // Also try to match array format: 'field' => ['rule1', 'rule2']
                $arrayPattern = '/[\'"]([a-zA-Z0-9_\.]+)[\'"]\s*=>\s*\[(.*?)\]/s';
                
                if (preg_match_all($arrayPattern, $rulesArray, $arrayMatches, PREG_SET_ORDER)) {
                    foreach ($arrayMatches as $match) {
                        $field = $match[1];
                        $rulesStr = $match[2];
                        
                        // Extract rules from array
                        preg_match_all('/[\'"]([^\'"]+)[\'"]/', $rulesStr, $rulesArray);
                        $rules = implode('|', $rulesArray[1]);
                        
                        if (!isset($parameters[$field])) {
                            $parameters[$field] = [
                                'type' => $this->inferType($rules),
                                'required' => $this->isRequired($rules),
                                'rules' => $rules,
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return [];
        }
        
        return $parameters;
    }
}
