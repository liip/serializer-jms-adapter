<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PhpCsFixer' => true,
            '@PhpCsFixer:risky' => true,
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'array_syntax' => ['syntax' => 'short'],
            'declare_strict_types' => true,
            'list_syntax' => ['syntax' => 'short'],
            'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
            'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
            'native_function_invocation' => [
                'include' => ['@compiler_optimized'],
            ],
            'no_superfluous_phpdoc_tags' => true,
            'php_unit_dedicate_assert' => true,
            'php_unit_expectation' => true,
            'php_unit_mock' => true,
            'php_unit_namespaced' => true,
            'php_unit_no_expectation_annotation' => true,
            'php_unit_test_case_static_method_calls' => [
                'call_type' => 'this',
            ],
            'phpdoc_to_return_type' => true,
            'single_line_throw' => false,
            'static_lambda' => true,
            'ternary_to_null_coalescing' => true,
            'void_return' => true,

            // Don't mark tests as @internal
            'php_unit_internal_class' => false,

            // Don't require @covers in tests
            'php_unit_test_class_requires_covers' => false,

            // Don't require dots in phpdocs
            'phpdoc_annotation_without_dot' => false,
            'phpdoc_summary' => false,

            // Sometimes we need to do non-strict comparison
            'strict_comparison' => false,
        ]
    )
    ->setFinder($finder)
;
