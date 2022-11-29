<?php

namespace App\Elastic;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class FileConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $name = 'file_index';

    /**
     * @var array
     */
    protected $settings = [
        'analysis' => [
            'filter' => [
                'russian_stop' => [
                    'type' => 'stop',
                    'stopwords' => '_russian_',
                ],
                'russian_stemmer' => [
                    'type' => 'stemmer',
                    'language' => 'russian',
                ],
            ],
            'analyzer' => [
                'file_russian' => [
                    'tokenizer' => 'autocomplete_tokenizer',
                    'filter' => [
                        'lowercase',
                        'russian_stop',
                        'russian_stemmer',
                    ],
                    'char_filter' => [
                        'html_strip'
                    ],
                ],
                'file_russian_search' => [
                    'tokenizer' => 'standard',
                    'filter' => [
                        'lowercase',
                        'russian_stop',
                        'russian_stemmer',
                    ],
                ],
            ],
            'tokenizer' => [
                'autocomplete_tokenizer' => [
                    'type' => 'edge_ngram',
                    'min_gram' => 2,
                    'max_gram' => 10,
                    'token_chars' => [
                        'letter',
                        'digit',
                    ]
                ]
            ],
        ],
    ];
}